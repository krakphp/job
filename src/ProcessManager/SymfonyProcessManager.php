<?php

namespace Krak\Job\ProcessManager;

use Symfony\Component\Process,
    iter;

class SymfonyProcessManager implements ProcessManager
{
    private $procs;

    public function __construct() {
        $this->procs = [];
    }

    public function launch($cmd, $input, $meta = null) {
        $proc = new Process\Process($cmd);
        $proc->setInput($input);
        $proc->start();
        $pid = $proc->getPid();
        $this->procs[] = [$proc, $meta];
        return $pid;
    }

    public function reap() {
        list($this->procs, $finished) = iter\reduce(function($acc, $tup) {
            list($running, $finished) = $acc;
            list($proc, $meta) = $tup;

            if ($proc->isRunning()) {
                $running[] = $tup;
            } else {
                $finished[] = $tup;
            }

            return [$running, $finished];
        }, $this->procs, [[], []]);

        return iter\toArray($finished);
    }

    public function getProcs() {
        return $this->procs;
    }

    public function count() {
        return count($this->procs);
    }
}
