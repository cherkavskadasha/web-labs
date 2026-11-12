<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use App\Repository\CategoryRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api/books')]
class BookController extends AbstractController
{
    /**
     * @var BookRepository
     */
    private BookRepository $bookRepository;

    /**
     * @var AuthorRepository
     */
    private AuthorRepository $authorRepository;

    /**
     * @var CategoryRepository
     */
    private CategoryRepository $categoryRepository;

    /**
     * @param BookRepository $bookRepository
     * @param AuthorRepository $authorRepository
     * @param CategoryRepository $categoryRepository
     */
    public function __construct(
        BookRepository $bookRepository,
        AuthorRepository $authorRepository,
        CategoryRepository $categoryRepository
    ) {
        $this->bookRepository = $bookRepository;
        $this->authorRepository = $authorRepository;
        $this->categoryRepository = $categoryRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['GET'])]
    public function getBooks(Request $request): JsonResponse
    {
        $queryParams = $request->query->all();
        $books = $this->bookRepository->getBooks($queryParams);

        return $this->json($books, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['GET'])]
    public function getBookById(int $id): JsonResponse
    {
        $book = $this->bookRepository->find($id);
        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($book, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['POST'])]
    public function createBook(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $author = $this->authorRepository->find($data['author_id'] ?? 0);
        $category = $this->categoryRepository->find($data['category_id'] ?? 0);

        if (!$author || !$category) {
            return $this->json(['error' => 'Author or Category not found'], Response::HTTP_BAD_REQUEST);
        }

        $book = new Book();
        $book->setTitle($data['title'] ?? '');
        $book->setPublishedYear((int)($data['published_year'] ?? 0));
        $book->setAuthor($author);
        $book->setCategory($category);

        $this->bookRepository->save($book, true);

        return $this->json($book, Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['PATCH'])]
    public function updateBook(int $id, Request $request): JsonResponse
    {
        $book = $this->bookRepository->find($id);
        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['title'])) {
            $book->setTitle($data['title']);
        }
        if (isset($data['published_year'])) {
            $book->setPublishedYear((int)$data['published_year']);
        }
        if (isset($data['author_id'])) {
            $author = $this->authorRepository->find($data['author_id']);
            if ($author) {
                $book->setAuthor($author);
            }
        }
        if (isset($data['category_id'])) {
            $category = $this->categoryRepository->find($data['category_id']);
            if ($category) {
                $book->setCategory($category);
            }
        }

        $this->bookRepository->save($book, true);

        return $this->json($book, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteBook(int $id): JsonResponse
    {
        $book = $this->bookRepository->find($id);
        if (!$book) {
            return $this->json(['error' => 'Book not found'], Response::HTTP_NOT_FOUND);
        }

        $this->bookRepository->remove($book, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}