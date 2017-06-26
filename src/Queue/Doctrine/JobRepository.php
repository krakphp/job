<?php

namespace Krak\Job\Queue\Doctrine;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Types\Type;
use Krak\Job;

/** this is the main API for managing the db jobs table/data */
class JobRepository
{
    const JOB_STATUS_CREATED = 'created';
    const JOB_STATUS_PROCESSING = 'processing';
    const JOB_STATUS_COMPLETED = 'completed';

    private $conn;
    private $table_name;

    public function __construct(Connection $conn, $table_name) {
        $this->conn = $conn;
        $this->table_name = $table_name;
    }

    public function getJobById($id) {
        return $this->conn->fetchAssoc(
            "SELECT * FROM {$this->table_name} WHERE id = :id",
            ['id' => $id]
        );
    }

    /** add a new wrapped job */
    public function addJob(Job\WrappedJob $job, $queue) {
        $qb = $this->conn->createQueryBuilder();
        $qb->insert($this->table_name);
        $qb->values([
            'status' => ':status',
            'job' => ':job',
            'name' => ':name',
            'queue' => ':queue',
            'created_at' => ':created_at',
            'available_at' => ':available_at',
        ]);
        $qb->setParameters([
            'status' => self::JOB_STATUS_CREATED,
            'queue' => $queue,
            'job' => (string) $job,
            'name' => $job->getName(),
        ]);
        $qb->setParameter(':created_at', new \DateTime(), Type::DATETIME);
        $available_at = $job->getDelay()
            ? new \DateTime(sprintf('+%d seconds', $job->getDelay()))
            : new \DateTime();
        $qb->setParameter(':available_at', $available_at, Type::DATETIME);
        $qb->execute();

        return $job->withAddedPayload([
            '_doctrine' => [
                'id' => $this->conn->lastInsertId()
            ]
        ]);
    }

    /** complete job */
    public function completeJob(Job\WrappedJob $job) {
        $data = $job->get('_doctrine');
        $qb = $this->conn->createQueryBuilder();
        $qb->update($this->table_name);
        $qb->set('status', ':status');
        $qb->set('completed_at', ':completed_at');
        $qb->where('id = :id');
        $qb->setParameters([
            'id' => $data['id'],
            'status' => self::JOB_STATUS_COMPLETED,
        ]);
        $qb->setParameter('completed_at', new \DateTime(), Type::DATETIME);
        $qb->execute();
    }

    /** process job */
    public function processJob(Job\WrappedJob $job) {
        $data = $job->get('_doctrine');
        $qb = $this->conn->createQueryBuilder();
        $qb->update($this->table_name);
        $qb->set('status', ':status');
        $qb->set('processed_at', ':processed_at');
        $qb->where('id = :id');
        $qb->setParameters([
            'id' => $data['id'],
            'status' => self::JOB_STATUS_PROCESSING,
        ]);
        $qb->setParameter('processed_at', new \DateTime(), Type::DATETIME);
        $qb->execute();
    }

    /** get available jobs */
    public function getAvailableJobs($queue, $max = 10) {
        $qb = $this->conn->createQueryBuilder();
        $qb->select('*');
        $qb->from($this->table_name);
        $qb->where('status = :status AND queue = :queue AND available_at <= :now');
        $qb->orderBy('status');
        $qb->setParameters([
            'status' => self::JOB_STATUS_CREATED,
            'queue' => $queue,
        ]);
        $qb->setParameter('now', new \DateTime(), Type::DATETIME);
        $qb->setMaxResults($max);
        $stmt = $qb->execute();
        return $stmt->fetchAll(\PDO::FETCH_ASSOC);
    }
}
