<?php

namespace App\Tests\Functional\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->userRepository = static::getContainer()->get(UserRepository::class);
    }

    public function testCreateUser()
    {
        $this->client->request('GET', '/users/create');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Créer un utilisateur');

        $this->client->submitForm('Ajouter l\'utilisateur', [
            'user[username]' => 'newuser',
            'user[email]' => 'newuser@example.com',
            'user[password][first]' => 'password123',
            'user[password][second]' => 'password123',
            'user[roles]' => 'ROLE_USER'
        ]);

        $this->assertResponseRedirects('/users');

        $user = $this->userRepository->findOneByEmail('newuser@example.com');
        $this->assertNotNull($user);
        $this->assertContains('ROLE_USER', $user->getRoles());
    }

    public function testCreateAdminUser()
    {
        $this->client->request('GET', '/users/create');

        $this->client->submitForm('Ajouter l\'utilisateur', [
            'user[username]' => 'newadmin',
            'user[email]' => 'newadmin@example.com',
            'user[password][first]' => 'password123',
            'user[password][second]' => 'password123',
            'user[roles]' => 'ROLE_ADMIN'
        ]);

        $this->assertResponseRedirects('/users');

        $user = $this->userRepository->findOneByEmail('newadmin@example.com');
        $this->assertNotNull($user);
        $this->assertContains('ROLE_ADMIN', $user->getRoles());
    }

    public function testEditUserRole()
    {
        $user = $this->userRepository->findOneByEmail('user@example.com');
        $this->assertNotNull($user);

        $this->client->loginUser($user);

        $this->client->request('GET', '/users/' . $user->getId() . '/edit');

        $this->client->submitForm('Modifier', [
            'user[roles]' => 'ROLE_ADMIN'
        ]);

        $this->assertResponseRedirects('/users');

        $updatedUser = $this->userRepository->find($user->getId());
        $this->assertContains('ROLE_ADMIN', $updatedUser->getRoles());
    }

    public function testAccessRestriction()
    {
        // Test as anonymous user
        $this->client->request('GET', '/users');
        $this->assertResponseRedirects('/login');

        // Test as regular user
        $user = $this->userRepository->findOneByEmail('user@example.com');
        $this->client->loginUser($user);

        $this->client->request('GET', '/users');
        $this->assertResponseStatusCodeSame(Response::HTTP_FORBIDDEN);

        // Test as admin
        $admin = $this->userRepository->findOneByEmail('admin@example.com');
        $this->client->loginUser($admin);

        $this->client->request('GET', '/users');
        $this->assertResponseIsSuccessful();
    }

    public function testDeleteUser()
    {
        $admin = $this->userRepository->findOneByEmail('admin@example.com');
        $this->client->loginUser($admin);

        $userToDelete = $this->userRepository->findOneByEmail('user@example.com');
        $this->assertNotNull($userToDelete);

        $this->client->request('GET', '/users/' . $userToDelete->getId() . '/delete');

        $this->assertResponseRedirects('/users');

        $deletedUser = $this->userRepository->find($userToDelete->getId());
        $this->assertNull($deletedUser);
    }
}