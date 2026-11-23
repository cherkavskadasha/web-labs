<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/authors')]
class AuthorController extends AbstractController
{
    /**
     * @var AuthorRepository
     */
    private AuthorRepository $authorRepository;

    /**
     * @param AuthorRepository $authorRepository
     */
    public function __construct(AuthorRepository $authorRepository)
    {
        $this->authorRepository =$authorRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_CLIENT')]
    public function getAuthors(Request $request): JsonResponse
    {
        $queryParams =$request->query->all();
        $page = max(1, (int)$request->query->get('page', 1));
        $itemsPerPage = max(1, (int)$request->query->get('itemsPerPage', 10));

        $authors = $this->authorRepository->getAuthors($queryParams, $page,$itemsPerPage);

        return $this->json($authors, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_CLIENT')]
    public function getAuthorById(int $id): JsonResponse
    {
        $author = $this->authorRepository->find($id);
        if (!$author) {
            return $this->json(['error' => 'Author not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($author, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function createAuthor(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $author = new Author();
        $author->setName($data['name'] ?? '');
        $author->setBio($data['bio'] ?? null);

        $this->authorRepository->save($author, true);

        return $this->json($author, Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['PATCH'])]
    #[IsGranted('ROLE_MANAGER')]
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

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
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