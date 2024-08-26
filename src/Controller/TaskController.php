<?php

namespace App\Controller;

use App\Entity\Task;
use App\Form\TaskType;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use App\Service\ExpiredTaskService;

class TaskController extends AbstractController
{
    private $expiredTaskService;
    public function __construct(ExpiredTaskService $expiredTaskService)
    {
        $this->expiredTaskService = $expiredTaskService;
    }
    /**
     * @Route("/tasks", name="task_list")
     */
    public function listAction(TaskRepository $taskRepository)
    {
        // Supprime les tâches expirées
        $deletedCount = $this->expiredTaskService->deleteExpiredTasks();

        // Si des tâches ont été supprimées, ajoutez un message flash
        if ($deletedCount > 0) {
            $this->addFlash('info', "$deletedCount tâche(s) expirée(s) ont été supprimées.");
        }

        $tasks = $taskRepository->findBy(['isDone' => 0]);
        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }

    /**
     * @Route("/tasks_done", name="task_done")
     */
    public function listActionDone(TaskRepository $taskRepository)
    {
        // Supprime les tâches expirées
        $deletedCount = $this->expiredTaskService->deleteExpiredTasks();

        // Si des tâches ont été supprimées, ajoutez un message flash
        if ($deletedCount > 0) {
            $this->addFlash('info', "$deletedCount tâche(s) expirée(s) ont été supprimées.");
        }

        $tasks = $taskRepository->findBy(['isDone' => 1]);
        return $this->render('task/list.html.twig', ['tasks' => $tasks]);
    }


    /**
     * @Route("/tasks/create", name="task_create")
     */
    public function createAction(Request $request, EntityManagerInterface $entityManager, Security $security)
    {
        $task = new Task();
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user = $security->getUser();
            $task->setUser($user);
            $entityManager->persist($task);
            $entityManager->flush();

            $this->addFlash('success', 'La tâche a été bien été ajoutée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/create.html.twig', ['form' => $form->createView()]);
    }

    /**
     * @Route("/tasks/{id}/edit", name="task_edit")
     */
    public function editAction(Task $task, Request $request)
    {
        $this->denyAccessUnlessGranted('task_edit', $task);
        $form = $this->createForm(TaskType::class, $task);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'La tâche a bien été modifiée.');

            return $this->redirectToRoute('task_list');
        }

        return $this->render('task/edit.html.twig', [
            'form' => $form->createView(),
            'task' => $task,
        ]);
    }

    /**
     * @Route("/tasks/{id}/toggle", name="task_toggle")
     */
    public function toggleTaskAction(Task $task)
    {
        $task->toggle(!$task->isDone());
        $this->getDoctrine()->getManager()->flush();

        $this->addFlash('success', sprintf('La tâche %s a bien été marquée comme faite.', $task->getTitle()));

        return $this->redirectToRoute('task_list');
    }

    /**
     * @Route("/tasks/{id}/delete", name="task_delete")
     */
    public function deleteTaskAction(Task $task)
    {
        $this->denyAccessUnlessGranted('task_delete', $task);
        $em = $this->getDoctrine()->getManager();
        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'La tâche a bien été supprimée.');

        return $this->redirectToRoute('task_list');
    }
}