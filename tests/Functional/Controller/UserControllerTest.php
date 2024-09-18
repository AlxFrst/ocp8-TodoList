<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;

    protected function setUp(): void
    {
    $this->client = static::createClient();
    $this->entityManager = $this->client->getContainer()
        ->get('doctrine')
        ->getManager();
    $this->clearDatabase();
    }

    private function clearDatabase()
    {
    $connection = $this->entityManager->getConnection();
    $platform = $connection->getDatabasePlatform();

    $connection->executeStatement('SET FOREIGN_KEY_CHECKS=0');
    $connection->executeStatement($platform->getTruncateTableSQL('user', true));
    $connection->executeStatement('SET FOREIGN_KEY_CHECKS=1');
    }

    public function testListAction()
    {
    $this->loginAsAdmin();

    $crawler = $this->client->request('GET', '/users');

    $this->assertResponseIsSuccessful();
    $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }

    public function testCreateAction()
    {
    $this->loginAsAdmin();

    $crawler = $this->client->request('GET', '/users/create');

    $this->assertResponseIsSuccessful();

    $form = $crawler->selectButton('Ajouter')->form([
        'user[username]' => 'newuser',
        'user[password][first]' => 'password123',
        'user[password][second]' => 'password123',
        'user[email]' => 'newuser@example.com',
        'user[roles]' => 'ROLE_USER',
    ]);

    $this->client->submit($form);

    $this->assertResponseRedirects('/users');
    $this->client->followRedirect();

    $this->assertSelectorTextContains('.alert.alert-success', "L'utilisateur a bien été ajouté.");
    }

    public function testEditAction()
    {
    $this->loginAsAdmin();

    $user = $this->createUser('testuser', ['ROLE_USER']);

    $crawler = $this->client->request('GET', '/users/'.$user->getId().'/edit');

    $this->assertResponseIsSuccessful();

    $form = $crawler->selectButton('Modifier')->form([
        'user[username]' => 'updateduser',
        'user[password][first]' => 'newpassword123',
        'user[password][second]' => 'newpassword123',
        'user[email]' => 'updateduser@example.com',
        'user[roles]' => 'ROLE_ADMIN',
    ]);

    $this->client->submit($form);

    $this->assertResponseRedirects('/users');
    $this->client->followRedirect();

    $this->assertSelectorTextContains('.alert.alert-success', "L'utilisateur a bien été modifié");
    }

    private function loginAsAdmin()
    {
    $user = $this->createUser('admin', ['ROLE_ADMIN']);
    $this->client->loginUser($user);
    }

    private function createUser($username = 'testuser', $roles = ['ROLE_USER'])
    {
    $user = new User();
    $user->setUsername($username);
    $user->setPassword('password123');
    $user->setEmail($username.'@example.com');
    $user->setRoles($roles);

    $passwordHasher = $this->client->getContainer()->get(UserPasswordHasherInterface::class);
    $hashedPassword = $passwordHasher->hashPassword($user, $user->getPassword());
    $user->setPassword($hashedPassword);

    $this->entityManager->persist($user);
    $this->entityManager->flush();

    return $user;
    }

    protected function tearDown(): void
    {
    parent::tearDown();

    $this->entityManager->close();
    $this->entityManager = null;
    $this->client = null;
    }
}
