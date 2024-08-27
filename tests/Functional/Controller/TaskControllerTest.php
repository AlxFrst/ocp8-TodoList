<?php

namespace App\Tests\Controller;

use App\Entity\Task;
use App\Entity\User;
use App\Service\ExpiredTaskService;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class TaskControllerTest extends WebTestCase
{
    private $client;
    private $entityManager;
    private $taskRepository;
    private $userRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->entityManager = $this->client->getContainer()->get('doctrine')->getManager();
        $this->taskRepository = $this->entityManager->getRepository(Task::class);
        $this->userRepository = $this->entityManager->getRepository(User::class);

        // Créer un utilisateur pour les tests
        $user = new User();
        $user->setUsername('testuser');
        $user->setEmail('test@example.com');
        $user->setPassword('$2y$13$A8MQM2ZNOi99EW.ML7srhOJsCaybSbexAj/0yXrJs4gQ/2BqMMW2K'); // 'password123'
        $user->setRoles(['ROLE_USER']);

        $this->entityManager->persist($user);
        $this->entityManager->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        $this->entityManager->createQuery('DELETE FROM App\Entity\Task')->execute();
        $this->entityManager->createQuery('DELETE FROM App\Entity\User')->execute();
        $this->entityManager->close();
        $this->entityManager = null;
    }

    public function testListAction()
    {
        $this->client->loginUser($this->userRepository->findOneBy(['username' => 'testuser']));
        $this->client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des tâches');
    }

    public function testListActionDone()
    {
        $this->client->loginUser($this->userRepository->findOneBy(['username' => 'testuser']));
        $this->client->request('GET', '/tasks_done');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('h1', 'Liste des tâches terminées');
    }

    public function testCreateAction()
    {
        $this->client->loginUser($this->userRepository->findOneBy(['username' => 'testuser']));
        $crawler = $this->client->request('GET', '/tasks/create');

        $form = $crawler->selectButton('Ajouter')->form();
        $form['task[title]'] = 'Nouvelle tâche';
        $form['task[content]'] = 'Contenu de la nouvelle tâche';

        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testEditAction()
    {
        $user = $this->userRepository->findOneBy(['username' => 'testuser']);
        $task = new Task();
        $task->setTitle('Tâche à modifier');
        $task->setContent('Contenu initial');
        $task->setUser($user);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->client->loginUser($user);
        $crawler = $this->client->request('GET', '/tasks/' . $task->getId() . '/edit');

        $form = $crawler->selectButton('Modifier')->form();
        $form['task[title]'] = 'Tâche modifiée';
        $form['task[content]'] = 'Nouveau contenu';

        $this->client->submit($form);

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');
    }

    public function testToggleTaskAction()
    {
        $user = $this->userRepository->findOneBy(['username' => 'testuser']);
        $task = new Task();
        $task->setTitle('Tâche à basculer');
        $task->setContent('Contenu de la tâche');
        $task->setUser($user);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/tasks/' . $task->getId() . '/toggle');

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');

        $updatedTask = $this->taskRepository->find($task->getId());
        $this->assertTrue($updatedTask->isDone());
    }

    public function testDeleteTaskAction()
    {
        $user = $this->userRepository->findOneBy(['username' => 'testuser']);
        $task = new Task();
        $task->setTitle('Tâche à supprimer');
        $task->setContent('Contenu de la tâche');
        $task->setUser($user);
        $this->entityManager->persist($task);
        $this->entityManager->flush();

        $this->client->loginUser($user);
        $this->client->request('GET', '/tasks/' . $task->getId() . '/delete');

        $this->assertResponseRedirects('/tasks');
        $this->client->followRedirect();
        $this->assertSelectorExists('.alert.alert-success');

        $deletedTask = $this->taskRepository->find($task->getId());
        $this->assertNull($deletedTask);
    }

    public function testExpiredTaskDeletion()
    {
        $expiredTaskService = $this->createMock(ExpiredTaskService::class);
        $expiredTaskService->expects($this->once())
            ->method('deleteExpiredTasks')
            ->willReturn(2);

        $this->client->getContainer()->set(ExpiredTaskService::class, $expiredTaskService);

        $this->client->loginUser($this->userRepository->findOneBy(['username' => 'testuser']));
        $this->client->request('GET', '/tasks');

        $this->assertResponseIsSuccessful();
        $this->assertSelectorTextContains('.alert.alert-info', '2 tâche(s) expirée(s) ont été supprimées.');
    }
}