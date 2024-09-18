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

    public function testConstructor()
    {
        $this->assertInstanceOf(\DateTime::class, $this->task->getCreatedAt());
        $this->assertFalse($this->task->getIsDone());
    }

    public function testGetterAndSetterForTitle()
    {
        $this->task->setTitle('Test Task');
        $this->assertEquals('Test Task', $this->task->getTitle());
    }

    public function testGetterAndSetterForContent()
    {
        $this->task->setContent('This is a test task content.');
        $this->assertEquals('This is a test task content.', $this->task->getContent());
    }

    public function testToggle()
    {
        $this->task->toggle(true);
        $this->assertTrue($this->task->isDone());
        $this->assertTrue($this->task->getIsDone());

        $this->task->toggle(false);
        $this->assertFalse($this->task->isDone());
        $this->assertFalse($this->task->getIsDone());
    }

    public function testGetterAndSetterForIsDone()
    {
        $this->task->setIsDone(true);
        $this->assertTrue($this->task->getIsDone());

        $this->task->setIsDone(false);
        $this->assertFalse($this->task->getIsDone());
    }

    public function testGetterAndSetterForUser()
    {
        $user = new User();
        $this->task->setUser($user);
        $this->assertSame($user, $this->task->getUser());

        // Test that user can only be set once
        $anotherUser = new User();
        $this->task->setUser($anotherUser);
        $this->assertSame($user, $this->task->getUser());
    }

    public function testGetterAndSetterForDeadline()
    {
        $deadline = new \DateTime('2023-12-31');
        $this->task->setDeadline($deadline);
        $this->assertEquals($deadline, $this->task->getDeadline());
    }

    public function testGetterAndSetterForCreatedAt()
    {
        $createdAt = new \DateTime('2023-01-01');
        $this->task->setCreatedAt($createdAt);
        $this->assertEquals($createdAt, $this->task->getCreatedAt());
    }

    public function testGetId()
    {
        // As id is set by the database, it should be null for a new Task
        $this->assertNull($this->task->getId());
    }
}