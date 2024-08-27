<?php

namespace App\Tests\Unit\Entity;

use App\Entity\User;
use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        $this->user = new User();
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
}