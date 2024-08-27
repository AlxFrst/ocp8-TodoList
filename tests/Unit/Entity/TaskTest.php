<?php

namespace App\Tests\Unit\Entity;

use App\Entity\Task;
use App\Entity\User;
use PHPUnit\Framework\TestCase;

class TaskTest extends TestCase
{
    private Task $task;

    protected function setUp(): void
    {
        parent::setUp();
        $this->task = new Task();
    }

    public function testTaskCreationWithUser()
    {
        $user = $this->createMock(User::class);
        $user->method('getId')->willReturn(1);
        $user->method('getUsername')->willReturn('john_doe');

        $this->task->setTitle('Test Task');
        $this->task->setContent('This is a test task.');
        $this->task->setUser($user);

        $this->assertEquals('Test Task', $this->task->getTitle());
        $this->assertEquals('This is a test task.', $this->task->getContent());
        $this->assertSame($user, $this->task->getUser());
        $this->assertEquals('john_doe', $this->task->getUser()->getUsername());
    }

    public function testTaskCreationWithoutUser()
    {
        $this->task->setTitle('Anonymous Task');
        $this->task->setContent('This is an anonymous task.');

        $this->assertEquals('Anonymous Task', $this->task->getTitle());
        $this->assertEquals('This is an anonymous task.', $this->task->getContent());
        $this->assertNull($this->task->getUser());
    }

    public function testCannotChangeTaskAuthor()
    {
        $user1 = $this->createMock(User::class);
        $user2 = $this->createMock(User::class);

        $this->task->setUser($user1);
        $this->assertSame($user1, $this->task->getUser());

        // Tentative de changement d'auteur
        $this->task->setUser($user2);

        // Vérification que l'auteur n'a pas changé
        $this->assertSame($user1, $this->task->getUser());
        $this->assertNotSame($user2, $this->task->getUser());
    }

    public function testTaskToggle()
    {
        $this->assertFalse($this->task->isDone());
        $this->assertFalse($this->task->getIsDone());

        $this->task->toggle(true);
        $this->assertTrue($this->task->isDone());
        $this->assertTrue($this->task->getIsDone());

        $this->task->toggle(false);
        $this->assertFalse($this->task->isDone());
        $this->assertFalse($this->task->getIsDone());

        $this->task->setIsDone(true);
        $this->assertTrue($this->task->getIsDone());
    }

    public function testTaskCreationDate()
    {
        $createdAt = $this->task->getCreatedAt();
        $this->assertInstanceOf(\DateTimeInterface::class, $createdAt);
        $this->assertEqualsWithDelta(new \DateTime(), $createdAt, 1);

        $newDate = new \DateTime('2023-01-01');
        $this->task->setCreatedAt($newDate);
        $this->assertEquals($newDate, $this->task->getCreatedAt());
    }

    public function testTaskId()
    {
        $this->assertNull($this->task->getId());
    }

    public function testTaskDeadline()
    {
        $this->assertNull($this->task->getDeadline());

        $deadline = new \DateTime('tomorrow');
        $this->task->setDeadline($deadline);
        $this->assertEquals($deadline, $this->task->getDeadline());

        $this->task->setDeadline(null);
        $this->assertNull($this->task->getDeadline());
    }
}