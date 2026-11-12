<?php

namespace App\Controller;

use App\Entity\BookIssue;
use App\Repository\BookIssueRepository;
use App\Repository\BookRepository;
use App\Repository\ReaderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Exception;

#[Route('/api/book-issues')]
class BookIssueController extends AbstractController
{
    /**
     * @var BookIssueRepository
     */
    private BookIssueRepository $bookIssueRepository;

    /**
     * @var BookRepository
     */
    private BookRepository $bookRepository;

    /**
     * @var ReaderRepository
     */
    private ReaderRepository $readerRepository;

    /**
     * @param BookIssueRepository $bookIssueRepository
     * @param BookRepository $bookRepository
     * @param ReaderRepository $readerRepository
     */
    public function __construct(
        BookIssueRepository $bookIssueRepository,
        BookRepository $bookRepository,
        ReaderRepository $readerRepository
    ) {
        $this->bookIssueRepository = $bookIssueRepository;
        $this->bookRepository = $bookRepository;
        $this->readerRepository = $readerRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['GET'])]
    public function getBookIssues(Request $request): JsonResponse
    {
        $queryParams = $request->query->all();
        $issues = $this->bookIssueRepository->getBookIssues($queryParams);

        return $this->json($issues, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['GET'])]
    public function getBookIssueById(int $id): JsonResponse
    {
        $issue = $this->bookIssueRepository->find($id);
        if (!$issue) {
            return $this->json(['error' => 'Book Issue not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($issue, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['POST'])]
    public function createBookIssue(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $book = $this->bookRepository->find($data['book_id'] ?? 0);
        $reader = $this->readerRepository->find($data['reader_id'] ?? 0);

        if (!$book || !$reader) {
            return $this->json(['error' => 'Book or Reader not found'], Response::HTTP_BAD_REQUEST);
        }

        $issue = new BookIssue();
        $issue->setBook($book);
        $issue->setReader($reader);

        try {
            $issueDate = new \DateTime($data['issue_date'] ?? 'now');
            $issue->setIssueDate($issueDate);

            if (!empty($data['return_date'])) {
                $issue->setReturnDate(new \DateTime($data['return_date']));
            }
        } catch (Exception $e) {
            return $this->json(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
        }

        $this->bookIssueRepository->save($issue, true);

        return $this->json($issue, Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['PATCH'])]
    public function updateBookIssue(int $id, Request $request): JsonResponse
    {
        $issue = $this->bookIssueRepository->find($id);
        if (!$issue) {
            return $this->json(['error' => 'Book Issue not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['book_id'])) {
            $book = $this->bookRepository->find($data['book_id']);
            if ($book) {
                $issue->setBook($book);
            }
        }

        if (isset($data['reader_id'])) {
            $reader = $this->readerRepository->find($data['reader_id']);
            if ($reader) {
                $issue->setReader($reader);
            }
        }

        try {
            if (isset($data['issue_date'])) {
                $issue->setIssueDate(new \DateTime($data['issue_date']));
            }
            if (array_key_exists('return_date', $data)) {
                $issue->setReturnDate($data['return_date'] ? new \DateTime($data['return_date']) : null);
            }
        } catch (Exception $e) {
            return $this->json(['error' => 'Invalid date format'], Response::HTTP_BAD_REQUEST);
        }

        $this->bookIssueRepository->save($issue, true);

        return $this->json($issue, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['DELETE'])]
    public function deleteBookIssue(int $id): JsonResponse
    {
        $issue = $this->bookIssueRepository->find($id);
        if (!$issue) {
            return $this->json(['error' => 'Book Issue not found'], Response::HTTP_NOT_FOUND);
        }

        $this->bookIssueRepository->remove($issue, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}