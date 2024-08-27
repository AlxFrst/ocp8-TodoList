<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;

class SecurityControllerTest extends WebTestCase
{
    private $client;

    protected function setUp(): void
    {
        $this->client = static::createClient();
    }

    public function testLoginAction()
    {
        $crawler = $this->client->request('GET', '/login');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Connexion');
        $this->assertSelectorExists('form');
        $this->assertSelectorExists('input[name="_username"]');
        $this->assertSelectorExists('input[name="_password"]');
    }

    public function testLoginActionWithError()
    {
        $this->client->request('POST', '/login', [
            '_username' => 'wrong_user',
            '_password' => 'wrong_password'
        ]);

        $this->client->followRedirect();

        $this->assertResponseIsSuccessful();
        $this->assertSelectorExists('.alert.alert-danger');
    }

    public function testLoginCheck()
    {
        $this->client->request('GET', '/login_check');

        $this->assertResponseRedirects('/login');
    }

    public function testLogoutCheck()
    {
        $this->client->request('GET', '/logout');

        // Le comportement exact dépend de votre configuration de sécurité,
        // mais généralement, cela redirige vers la page d'accueil ou de connexion
        $this->assertResponseRedirects();
    }

    public function testAuthenticatedUserCannotAccessLoginPage()
    {
        $this->client->loginUser($this->createMock(UserInterface::class));

        $this->client->request('GET', '/login');

        // Selon votre logique, l'utilisateur connecté pourrait être redirigé
        // vers la page d'accueil ou une autre page
        $this->assertResponseRedirects();
    }
}