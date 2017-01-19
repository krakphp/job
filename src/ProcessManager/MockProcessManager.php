<?php

namespace Krak\Job\ProcessManager;

use iter;

class MockProcessManager implements ProcessManager
{
    private $procs;

    public function __construct() {
        $this->procs = [];
    }

    public function launch($cmd, $input, $meta = null) {
        $pid = rand();
        $this->procs[] = [$pid, $cmd, $meta];
        return $pid;
    }

    public function reap() {
        $procs = iter\map(function($proc) {
            list($pid, $count, $meta) = $proc;
            $count -= 1;
            return [$pid, $count, $meta];
        }, $this->procs);
        list($this->procs, $done) = iter\reduce(function($acc, $proc) {
            list($running, $done) = $acc;
            list($pid, $count) = $proc;
            if ($count <= 0) {
                $done[] = $proc;
            } else {
                $running[] = $proc;
            }
            return [$running, $done];
        }, $procs, [[], []]);
        return iter\toArray(iter\map(function($proc) {
            list($pid, $count, $meta) = $proc;
            return [1, '', $meta, $pid];
        }, $done));
    }

    public function count() {
        return count($this->procs);
    }
    public function getProcs() {
        return $this->procs;
    }
}
