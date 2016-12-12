<?php

namespace Krak\Job\ScheduleLoop;

interface ScheduleLoop {
    /** @return bool false means to stop the schedule loop */
    public function __invoke(ScheduleLoopParams $params);
}
