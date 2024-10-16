<?php

namespace Horeca\MiddlewareClientBundle\Enum;

final class TaskStatus
{

    const STATUS_QUEUED = 'STATUS_QUEUED';
    const STATUS_RUNNING = 'STATUS_RUNNING';
    const STATUS_FINISHED = 'STATUS_FINISHED';
    const STATUS_FAILED = 'STATUS_FAILED';
    const STATUS_CANCELLED = 'STATUS_CANCELLED';

}
