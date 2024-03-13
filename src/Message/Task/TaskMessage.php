<?php

namespace Horeca\MiddlewareClientBundle\Message\Task;

use Horeca\MiddlewareClientBundle\Entity\Task;

class TaskMessage
{

    public string $taskId;

    public function __construct(Task $task)
    {
        $this->taskId = $task->getId();
    }


}
