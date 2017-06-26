<?php

namespace Krak\Job\Queue\Doctrine;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\DBAL\Connection;

class JobMigration
{
    private $table_name;

    public function __construct($table_name) {
        $this->table_name = $table_name;
    }

    public function up(Schema $schema) {
        $jobs = $schema->createTable($this->table_name);
        $jobs->addColumn('id', 'integer', ['unsigned' => true, 'autoincrement' => true]);
        $jobs->addColumn('job', 'text');
        $jobs->addColumn('queue', 'string');
        $jobs->addColumn('name', 'string');
        $jobs->addColumn('status', 'string');
        $jobs->addColumn('created_at', 'datetime');
        $jobs->addColumn('available_at', 'datetime');
        $jobs->addColumn('processed_at', 'datetime', ['notnull' => false]);
        $jobs->addColumn('completed_at', 'datetime', ['notnull' => false]);
        $jobs->addIndex(['queue', 'status'], 'queue_status_index');
        $jobs->addIndex(['available_at'], 'available_at_index');
        $jobs->setPrimaryKey(['id']);
    }

    public function down(Schema $schema) {
        $schema->dropTable($this->table_name);
    }

    public function migrateUp(Connection $conn, $dry_run = false) {
        $sm = $conn->getSchemaManager();
        $from_schema = $sm->createSchema();
        $to_schema = clone $from_schema;
        $this->up($to_schema);
        $sql = $from_schema->getMigrateToSql($to_schema, $conn->getDatabasePlatform());
        $this->execSql($conn, $sql, $dry_run);
    }

    public function migrateDown(Connection $conn, $dry_run = false) {
        $sm = $conn->getSchemaManager();
        $from_schema = $sm->createSchema();
        $to_schema = clone $from_schema;
        $this->down($to_schema);
        $sql = $from_schema->getMigrateToSql($to_schema, $conn->getDatabasePlatform());
        $this->execSql($conn, $sql, $dry_run);
    }

    private function execSql($conn, $sql, $dry_run) {
        echo "# Executing SQL\n";
        echo implode(";\n", $sql) . ";\n";

        if ($dry_run) {
            return;
        }

        foreach ($sql as $query) {
            $conn->query($query);
        }
    }
}
