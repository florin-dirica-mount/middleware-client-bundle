<?php

namespace Horeca\MiddlewareClientBundle\Message\Task;

use Horeca\MiddlewareClientBundle\Entity\AbstractTask;

class TaskMessage
{

    public string $taskId;

    public function __construct(AbstractTask $task)
    {
        $this->taskId = $task->getId();
    }


}
