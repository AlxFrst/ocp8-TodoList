<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType as FormTaskType;
use App\Repository\TaskRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class TaskController extends AbstractController
{
    
    /**
     * Displays a list of tasks Undone.
     *
     * This function retrieves a list of all tasks from the database and renders the task list view with the obtained data.
     *
     * @param TaskRepository $taskRepository The task repository to fetch tasks from the database.
     * @return Response A response containing the rendered task list view.
     */
    #[Route('/tasks/undone', name: 'task_list_undone')]
    public function listOfUndoneTasks( TaskRepository $taskRepository, Security $security, TranslatorInterface $translator): Response
    {
        $user = $security->getUser();
        if (!$user instanceof UserInterface) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos tâches');
            return $this->redirectToRoute('app_home');
        }

        $taskList =  $taskRepository->findByRoleAndStatus($user, false);
        return $this->render('task/list-undone.html.twig', [
            'controller_name' => 'TaskController',
            'tasks' => $taskList,
        ]);
    }

    /**
     * Displays a list of tasks done.
     *
     * This function retrieves a list of all tasks from the database and renders the task list view with the obtained data.
     *
     * @param TaskRepository $taskRepository The task repository to fetch tasks from the database.
     * @return Response A response containing the rendered task list view.
     */
    #[Route('/tasks/done', name: 'task_list_done')]
    public function listOfDoneTasks( TaskRepository $taskRepository, Security $security, TranslatorInterface $translator): Response
    {
        $user = $security->getUser();
        if (!$user instanceof UserInterface) {
            $this->addFlash('error', 'Vous devez être connecté pour voir vos tâches');
            return $this->redirectToRoute('app_home');
        }

        $taskList =  $taskRepository->findByRoleAndStatus($user, true);
        return $this->render('task/list-done.html.twig', [
            'controller_name' => 'TaskController',
            'tasks' => $taskList,
        ]);
    }

    /**
     * Creates a new task.
     *
     * This function creates a new task based on the form data submitted.
     * If the user does not have the necessary rights to create a task, they are    redirected to the task list with an error message.
     * After creating the task, the user is redirected to the task list with a success  message.
     *
     * @param Request $request The HTTP request.
     * @param EntityManagerInterface $entityManager The entity manager to perform create    operations.
     * @param Security $security The security component to access user information.
     * @return Response A response containing the form to create a task or a redirect   response to the task list.
     */
    #[Route('/tasks/create', name: "task_create")]
    public function createAction(Request $request, EntityManagerInterface $entityManager, Security $security, TranslatorInterface $translator)
    {
        $task = new Task();
        if (!$this->isGranted('TASK_CREATE', $task)) {
            $this->addFlash('error', 'Vous devez être connecté pour créer une tâche');
            return $this->redirectToRoute('task_list_undone');
        }
        $form = $this->createForm(FormTaskType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setCreatedAt(new DateTimeImmutable());
            $task->setIsDone(false);
            $task->setAuthor($security->getUser());
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée');

            return $this->redirectToRoute('task_list_undone');
        }
        return $this->render('task/create.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /**
     * Edits a task.
     *
     * This function allows editing of a specified task based on its identifier.
     * If the user does not have the necessary rights to edit the task, they are    redirected to the task list with an error message.
     * After editing the task, the user is redirected to the task list with a success   message.
     *
     * @param Task $task The task to edit.
     * @param Request $request The HTTP request.
     * @param EntityManagerInterface $entityManager The entity manager to perform edit  operations.
     * @return Response A response containing the form to edit the task or a redirect   response to the task list.
     */
    #[Route('/tasks/{id}/edit', name: "task_edit")]
    public function editAction(Task $task, Request $request, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        if (!$this->isGranted('TASK_EDIT', $task)) {
            $this->addFlash('error', 'Vous ne pouvez pas modifier cette tâche');
            return $this->redirectToRoute('task_list_undone');
        }
        $form = $this->createForm(FormTaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $task->setIsDone(false);
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée');

            return $this->redirectToRoute('task_list_undone');
        }



        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }
    
    /**
     * Toggles the status of a task.
     *
     * This function toggles the status (done or not done) of a specified task based on     its identifier.
     * After toggling the status, the user is redirected to the task list with a success    message.
     *
     * @param Task $task The task to toggle.
     * @param EntityManagerInterface $entityManager The entity manager to perform toggle    operations.
     * @return RedirectResponse A redirect response to the task list.
     */
    #[Route('/tasks/{id}/toggle', name: "task_toggle")]
    public function toggleTaskAction(Task $task, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {
        $task->setIsDone(!$task->isIsDone());
        
        $entityManager->persist($task);
        $entityManager->flush();
        if ($task->isIsDone()) {
            $this->addFlash('success', 'La tâche a bien été marquée comme faite');
            return $this->redirectToRoute('task_list_done');
        } else {
            $this->addFlash('success', 'La tâche a bien été marquée comme non faite');
            return $this->redirectToRoute('task_list_undone');
        }
        

        return $this->redirectToRoute('task_list_undone');
    }

    /**
     * Deletes a task.
     *
     * This function deletes a specified task based on its identifier.
     * If the user does not have the necessary rights to delete the task, they are  redirected to the task list with an error message.
     * After the task is deleted, the user is redirected to the task list with a success    message.
     *
     * @param Task $task The task to delete.
     * @param EntityManagerInterface $entityManager The entity manager to perform delete    operations.
     * @return RedirectResponse A redirect response to the task list.
     */
    #[Route('/tasks/{id}/delete', name: "task_delete")]
    public function deleteTaskAction(Task $task, EntityManagerInterface $entityManager, TranslatorInterface $translator)
    {

        if (!$this->isGranted('TASK_DELETE', $task)) {
            
            $this->addFlash('error', 'Vous ne pouvez pas supprimer cette tâche');
            return $this->redirectToRoute('task_list_undone');
        }
        
        $entityManager->remove($task);
        $entityManager->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée');
        return $this->redirectToRoute('task_list_undone');
    }

}
