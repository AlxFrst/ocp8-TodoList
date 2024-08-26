<?php

namespace App\Service;

use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;

class ExpiredTaskService
{
    private $taskRepository;
    private $entityManager;

    public function __construct(TaskRepository $taskRepository, EntityManagerInterface $entityManager)
    {
        $this->taskRepository = $taskRepository;
        $this->entityManager = $entityManager;
    }

    public function deleteExpiredTasks()
    {
        $expiredTasks = $this->taskRepository->findExpiredTasks();

        foreach ($expiredTasks as $task) {
            $this->entityManager->remove($task);
        }

        $this->entityManager->flush();

        return count($expiredTasks);
    }
}