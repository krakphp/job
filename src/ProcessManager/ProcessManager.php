<?php

namespace Krak\Job\ProcessManager;

interface ProcessManager extends \Countable {
    /** launch the cmd with input and attach any meta data along with the process. Return the pid */
    public function launch($cmd, $input, $meta = null);
    /** Go through and remove all of the jobs that have finished. Returns an array of 3-tuples
        `[$success, $output, $meta, $pid]`.

        - `$success` is flag of true for success or false for error.
        - `$output` is just the raw string content
    */
    public function reap();
    public function getProcs();
}
