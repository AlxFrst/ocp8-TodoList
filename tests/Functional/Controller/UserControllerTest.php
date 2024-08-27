<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $passwordHasher;
    private $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()
            ->get('doctrine')
            ->getManager();
        $this->passwordHasher = $this->client->getContainer()
            ->get('security.user_password_hasher');
        $this->userRepository = $this->entityManager
            ->getRepository(User::class);

        // Créer un utilisateur admin pour les tests
        $adminUser = new User();
        $adminUser->setUsername('admin_test');
        $adminUser->setEmail('admin_test@example.com');
        $adminUser->setPassword($this->passwordHasher->hashPassword($adminUser, 'password123'));
        $adminUser->setRoles(['ROLE_ADMIN']);

        $this->entityManager->persist($adminUser);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        // Nettoyer la base de données après chaque test
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testListAction()
    {
        $this->client->loginUser($this->userRepository->findOneBy(['username' => 'admin_test']));
        $crawler = $this->client->request('GET', '/users');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des utilisateurs');
    }

    public function testCreateActionSuccess()
    {
        $this->client->loginUser($this->userRepository->findOneBy(['username' => 'admin_test']));
        $crawler = $this->client->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = 'newuser';
        $form['user[password][first]'] = 'password123';
        $form['user[password][second]'] = 'password123';
        $form['user[email]'] = 'newuser@example.com';
        $form['user[roles]'] = ['ROLE_USER'];

        $this->client->submit($form);

        $this->assertResponseRedirects('/users');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testCreateActionFormInvalid()
    {
        $this->client->loginUser($this->userRepository->findOneBy(['username' => 'admin_test']));
        $crawler = $this->client->request('GET', '/users/create');

        $form = $crawler->selectButton('Ajouter')->form();
        $form['user[username]'] = '';
        $form['user[password][first]'] = '';
        $form['user[password][second]'] = '';
        $form['user[email]'] = 'invalid-email';

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.form-error-message');
    }

    public function testEditActionSuccess()
    {
        $this->client->loginUser($this->userRepository->findOneBy(['username' => 'admin_test']));
        $user = $this->userRepository->findOneBy(['username' => 'admin_test']);

        $crawler = $this->client->request('GET', '/users/' . $user->getId() . '/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = 'updateduser';
        $form['user[password][first]'] = 'newpassword123';
        $form['user[password][second]'] = 'newpassword123';
        $form['user[email]'] = 'updated@example.com';
        $form['user[roles]'] = ['ROLE_ADMIN'];

        $this->client->submit($form);

        $this->assertResponseRedirects('/users');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditActionFormInvalid()
    {
        $this->client->loginUser($this->userRepository->findOneBy(['username' => 'admin_test']));
        $user = $this->userRepository->findOneBy(['username' => 'admin_test']);

        $crawler = $this->client->request('GET', '/users/' . $user->getId() . '/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['user[username]'] = '';
        $form['user[password][first]'] = 'password';
        $form['user[password][second]'] = 'different_password';
        $form['user[email]'] = 'invalid-email';

        $this->client->submit($form);

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.form-error-message');
    }
}