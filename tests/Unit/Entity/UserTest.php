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

    public function testGetterAndSetterForUsername()
    {
        $this->user->setUsername('testuser');
        $this->assertEquals('testuser', $this->user->getUsername());
    }

    public function testGetterAndSetterForEmail()
    {
        $this->user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $this->user->getEmail());
    }

    public function testGetterAndSetterForPassword()
    {
        $this->user->setPassword('password123');
        $this->assertEquals('password123', $this->user->getPassword());
    }

    public function testGetterAndSetterForRoles()
    {
        $this->user->setRoles(['ROLE_ADMIN']);
        $this->assertEquals(['ROLE_ADMIN', 'ROLE_USER'], $this->user->getRoles());
    }

    public function testGetUserIdentifier()
    {
        $this->user->setEmail('test@example.com');
        $this->assertEquals('test@example.com', $this->user->getUserIdentifier());
    }

    public function testAddAndRemoveTask()
    {
        $task = new Task();
        
        $this->user->addTask($task);
        $this->assertCount(1, $this->user->getTask());
        $this->assertTrue($this->user->getTask()->contains($task));

        $this->user->removeTask($task);
        $this->assertCount(0, $this->user->getTask());
        $this->assertFalse($this->user->getTask()->contains($task));
    }

    public function testEraseCredentials()
    {
        $this->assertNull($this->user->eraseCredentials());
    }

    public function testGetSalt()
    {
        $this->assertNull($this->user->getSalt());
    }
}