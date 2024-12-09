<?php

namespace App\Controller;

use App\Entity\Task;
use App\Repository\TaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class TaskController extends AbstractController
{
    // Endpoint: POST /tasks - Créer une tâche
    #[Route('/tasks', name: 'create_task', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['title']) || empty($data['title'])) {
            return new JsonResponse(['error' => 'Title is required'], JsonResponse::HTTP_BAD_REQUEST);
        }

        $task = new Task();
        $task->setTitle($data['title']);
        $task->setDescription($data['description'] ?? null);
        $task->setStatus($data['status'] ?? 'pending');

        $em->persist($task);
        $em->flush();

        return new JsonResponse(['message' => 'Task created successfully', 'task' => [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
        ]], JsonResponse::HTTP_CREATED);
    }

    // Endpoint: GET /tasks - Lister toutes les tâches
    #[Route('/tasks', name: 'get_tasks', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): JsonResponse
    {
        $tasks = $taskRepository->findAll();

        $data = array_map(function ($task) {
            return [
                'id' => $task->getId(),
                'title' => $task->getTitle(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
            ];
        }, $tasks);

        return new JsonResponse($data, JsonResponse::HTTP_OK);
    }

    // Endpoint: PUT /tasks/{id} - Mettre à jour une tâche spécifique
    #[Route('/tasks/{id}', name: 'update_task', methods: ['PUT'])]
    public function update($id, Request $request, TaskRepository $taskRepository, EntityManagerInterface $em): JsonResponse
    {
        $task = $taskRepository->find($id);

        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);
        if (isset($data['status']) && !empty($data['status'])) {
            $task->setStatus($data['status']);
        }
        if (isset($data['title'])) {
            $task->setTitle($data['title']);
        }
        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }

        $em->flush();

        return new JsonResponse(['message' => 'Task updated successfully', 'task' => [
            'id' => $task->getId(),
            'title' => $task->getTitle(),
            'description' => $task->getDescription(),
            'status' => $task->getStatus(),
        ]], JsonResponse::HTTP_OK);
    }

    // Endpoint: DELETE /tasks/{id} - Supprimer une tâche
    #[Route('/tasks/{id}', name: 'delete_task', methods: ['DELETE'])]
    public function delete($id, TaskRepository $taskRepository, EntityManagerInterface $em): JsonResponse
    {
        $task = $taskRepository->find($id);

        if (!$task) {
            return new JsonResponse(['error' => 'Task not found'], JsonResponse::HTTP_NOT_FOUND);
        }

        $em->remove($task);
        $em->flush();

        return new JsonResponse(['message' => 'Task deleted successfully'], JsonResponse::HTTP_OK);
    }
}
