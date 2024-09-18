<?php

namespace App\Tests\Service;

use App\Entity\Task;
use App\Repository\TaskRepository;
use App\Service\ExpiredTaskService;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\TestCase;

class ExpiredTaskServiceTest extends TestCase
{
    private $taskRepository;
    private $entityManager;
    private $expiredTaskService;

    protected function setUp(): void
    {
        $this->taskRepository = $this->createMock(TaskRepository::class);
        $this->entityManager = $this->createMock(EntityManagerInterface::class);
        $this->expiredTaskService = new ExpiredTaskService($this->taskRepository, $this->entityManager);
    }

    public function testDeleteExpiredTasks()
    {
        // Créer des tâches expirées fictives
        $expiredTask1 = new Task();
        $expiredTask2 = new Task();
        $expiredTasks = [$expiredTask1, $expiredTask2];

        // Configurer le mock du repository pour renvoyer les tâches expirées
        $this->taskRepository->expects($this->once())
            ->method('findExpiredTasks')
            ->willReturn($expiredTasks);

        // Vérifier que remove() est appelé pour chaque tâche expirée
        $this->entityManager->expects($this->exactly(2))
            ->method('remove')
            ->willReturnCallback(function($task) use ($expiredTask1, $expiredTask2) {
                $this->assertTrue($task === $expiredTask1 || $task === $expiredTask2);
            });

        // Vérifier que flush() est appelé une fois
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Appeler la méthode à tester
        $deletedCount = $this->expiredTaskService->deleteExpiredTasks();

        // Vérifier que le nombre de tâches supprimées est correct
        $this->assertEquals(2, $deletedCount);
    }

    public function testDeleteExpiredTasksWithNoExpiredTasks()
    {
        // Configurer le mock du repository pour ne renvoyer aucune tâche expirée
        $this->taskRepository->expects($this->once())
            ->method('findExpiredTasks')
            ->willReturn([]);

        // Vérifier que remove() n'est pas appelé
        $this->entityManager->expects($this->never())
            ->method('remove');

        // Vérifier que flush() est quand même appelé une fois
        $this->entityManager->expects($this->once())
            ->method('flush');

        // Appeler la méthode à tester
        $deletedCount = $this->expiredTaskService->deleteExpiredTasks();

        // Vérifier que le nombre de tâches supprimées est 0
        $this->assertEquals(0, $deletedCount);
    }
}