<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/authors')]
class AuthorController extends AbstractController
{
    /** @var AuthorRepository */
    private AuthorRepository $authorRepository;

    public function __construct(AuthorRepository $authorRepository)
    {
        $this->authorRepository =$authorRepository;
    }

    #[Route('', methods: ['GET'])]
    public function getAuthors(): JsonResponse
    {
        $authors =$this->authorRepository->findAll();
        return $this->json($authors, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['GET'])]
    public function getAuthorById(int $id): JsonResponse
    {
        $author = $this->authorRepository->find($id);
        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($author, Response::HTTP_OK);
    }

    #[Route('', methods: ['POST'])]
    public function createAuthor(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $author = new Author();
        $author->setName($data['name'] ?? '');
        $author->setBio($data['bio'] ?? null);

        $this->authorRepository->save($author, true);

        return $this->json($author, Response::HTTP_CREATED);
    }

    #[Route('/{id}', methods: ['PATCH'])]
    public function updateAuthor(int $id, Request$request): JsonResponse
    {
        $author = $this->authorRepository->find($id);
        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $author->setName($data['name']);
        }
        if (array_key_exists('bio', $data)) {
            $author->setBio($data['bio']);
        }

        $this->authorRepository->save($author, true);

        return $this->json($author, Response::HTTP_OK);
    }

    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteAuthor(int $id): JsonResponse
    {
        $author = $this->authorRepository->find($id);
        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        $this->authorRepository->remove($author, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}