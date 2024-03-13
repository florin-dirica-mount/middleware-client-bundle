<?php
namespace Horeca\MiddlewareClientBundle\DependencyInjection\Repository;



use Horeca\MiddlewareClientBundle\Repository\TaskRepository;
use Symfony\Contracts\Service\Attribute\Required;

trait TaskRepositoryDI
{

    protected TaskRepository $taskRepository;

    #[Required]
    public function setTaskRepository(TaskRepository $taskRepository): void
    {
        $this->taskRepository = $taskRepository;
    }


}
