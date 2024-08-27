<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use App\Entity\Task;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
    }

    public function testId()
    {
        $this->assertNull($this->user->getId());
    }

    public function testDefaultRole()
    {
        $this->assertContains('ROLE_USER', $this->user->getRoles());
    }

    public function testAddAdminRole()
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        $this->assertContains('ROLE_ADMIN', $this->user->getRoles());
        $this->assertContains('ROLE_USER', $this->user->getRoles(), 'ROLE_USER should always be present');
    }

    public function testEmail()
    {
        $email = 'test@example.com';
        $this->user->setEmail($email);
        $this->assertEquals($email, $this->user->getEmail());

        $invalidEmail = 'invalid-email';
        $this->user->setEmail($invalidEmail);
        $this->assertEquals($invalidEmail, $this->user->getEmail(), 'The User entity should accept any string as email without validation');
    }

    public function testUsername()
    {
        $username = 'testuser';
        $this->user->setUsername($username);
        $this->assertEquals($username, $this->user->getUsername());
    }

    public function testPassword()
    {
        $password = 'password123';
        $this->user->setPassword($password);
        $this->assertEquals($password, $this->user->getPassword());
    }

    public function testUserIdentifier()
    {
        $email = 'identifier@example.com';
        $this->user->setEmail($email);
        $this->assertEquals($email, $this->user->getUserIdentifier());
    }

    public function testEraseCredentials()
    {
        $this->user->eraseCredentials();
        $this->addToAssertionCount(1);
    }

    public function testGetSalt()
    {
        $this->assertNull($this->user->getSalt());
    }

    public function testTask()
    {
        $this->assertInstanceOf(\Doctrine\Common\Collections\Collection::class, $this->user->getTask());
        $this->assertCount(0, $this->user->getTask());

        $task = $this->createMock(Task::class);
        $this->user->addTask($task);
        $this->assertCount(1, $this->user->getTask());
        $this->assertTrue($this->user->getTask()->contains($task));

        $this->user->removeTask($task);
        $this->assertCount(0, $this->user->getTask());
        $this->assertFalse($this->user->getTask()->contains($task));
    }

    public function testAddTaskTwice()
    {
        $task = $this->createMock(Task::class);
        $task->expects($this->once())
             ->method('setUser')
             ->with($this->equalTo($this->user));

        $this->user->addTask($task);
        $this->user->addTask($task);
        $this->assertCount(1, $this->user->getTask());
    }

    public function testRemoveTaskNotOwner()
    {
        $task = $this->createMock(Task::class);
        $task->expects($this->once())
             ->method('getUser')
             ->willReturn(null);

        $this->user->addTask($task);
        $this->user->removeTask($task);
        $this->assertCount(0, $this->user->getTask());
    }

    public function testMagicCall()
    {
        $this->expectException(\Error::class);
        $this->user->nonExistentMethod();
    }
}