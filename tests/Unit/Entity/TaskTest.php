<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    public function testTaskCreationWithUser()
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getUsername')->willReturn('john_doe');

        $task = new Task();
        $task->setTitle('Test Task');
        $task->setContent('This is a test task.');
        $task->setUser($user);

        $this->assertEquals('Test Task', $task->getTitle());
        $this->assertEquals('This is a test task.', $task->getContent());
        $this->assertSame($user, $task->getUser());
        $this->assertEquals('john_doe', $task->getUser()->getUsername());
    }

    public function testTaskCreationWithoutUser()
    {
        $task = new Task();
        $task->setTitle('Anonymous Task');
        $task->setContent('This is an anonymous task.');

        $this->assertEquals('Anonymous Task', $task->getTitle());
        $this->assertEquals('This is an anonymous task.', $task->getContent());
        $this->assertNull($task->getUser());
    }

    public function testCannotChangeTaskAuthor()
    {
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);

        $task = new Task();
        $task->setUser($user1);

        $this->assertSame($user1, $task->getUser());

        // Tentative de changement d'auteur
        $task->setUser($user2);

        // Vérification que l'auteur n'a pas changé
        $this->assertSame($user1, $task->getUser());
        $this->assertNotSame($user2, $task->getUser());
    }

    public function testTaskToggle()
    {
        $task = new Task();
        
        // Par défaut, la tâche ne devrait pas être marquée comme faite
        $this->assertFalse($task->isDone());

        // Basculer l'état de la tâche
        $task->toggle(true);
        $this->assertTrue($task->isDone());

        // Basculer à nouveau
        $task->toggle(false);
        $this->assertFalse($task->isDone());
    }

    public function testTaskCreationDate()
    {
        $task = new Task();
        $createdAt = $task->getCreatedAt();

        $this->assertInstanceOf(\DateTimeInterface::class, $createdAt);
        $this->assertEqualsWithDelta(new \DateTime(), $createdAt, 1); // 1 seconde de tolérance
    }
}