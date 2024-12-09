<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    // Endpoint: GET /tasks - Page pour lister toutes les tâches
    #[Route('/tasks', name: 'task_index', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): Response
    {
        $tasks = $taskRepository->findAll();

        return $this->render('task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    // Endpoint: GET /tasks/create - Formulaire pour ajouter une nouvelle tâche
    #[Route('/tasks/create', name: 'task_create', methods: ['GET'])]
    public function create(): Response
    {
        return $this->render('task/create.html.twig');
    }

    // Endpoint: POST /tasks - Créer une nouvelle tâche (soumission du formulaire)
    #[Route('/tasks', name: 'task_store', methods: ['POST'])]
    public function store(Request $request, EntityManagerInterface $em): Response
    {
        $title = $request->request->get('title');
        $description = $request->request->get('description');
        $status = $request->request->get('status', 'pending');

        if (empty($title)) {
            $this->addFlash('error', 'Title is required.');
            return $this->redirectToRoute('task_create');
        }

        $task = new Task();
        $task->setTitle($title);
        $task->setDescription($description);
        $task->setStatus($status);

        $em->persist($task);
        $em->flush();

        $this->addFlash('success', 'Task created successfully.');
        return $this->redirectToRoute('task_index');
    }

    // Endpoint: GET /tasks/{id}/edit - Formulaire pour modifier une tâche existante
    #[Route('/tasks/{id}/edit', name: 'task_edit', methods: ['GET'])]
    public function edit($id, TaskRepository $taskRepository): Response
    {
        $task = $taskRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        return $this->render('task/edit.html.twig', [
            'task' => $task,
        ]);
    }

    // Endpoint: POST /tasks/{id} - Mettre à jour une tâche (soumission du formulaire)
    #[Route('/tasks/{id}', name: 'task_update', methods: ['POST'])]
    public function update($id, Request $request, TaskRepository $taskRepository, EntityManagerInterface $em): Response
    {
        $task = $taskRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        $task->setTitle($request->request->get('title', $task->getTitle()));
        $task->setDescription($request->request->get('description', $task->getDescription()));
        $task->setStatus($request->request->get('status', $task->getStatus()));

        $em->flush();

        $this->addFlash('success', 'Task updated successfully.');
        return $this->redirectToRoute('task_index');
    }

    // Endpoint: POST /tasks/{id}/delete - Supprimer une tâche
    #[Route('/tasks/{id}/delete', name: 'task_delete', methods: ['POST'])]
    public function delete($id, TaskRepository $taskRepository, EntityManagerInterface $em): Response
    {
        $task = $taskRepository->find($id);

        if (!$task) {
            throw $this->createNotFoundException('Task not found');
        }

        $em->remove($task);
        $em->flush();

        $this->addFlash('success', 'Task deleted successfully.');
        return $this->redirectToRoute('task_index');
    }
}
