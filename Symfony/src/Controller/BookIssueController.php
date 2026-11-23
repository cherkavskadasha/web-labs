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
use Symfony\Component\Security\Http\Attribute\IsGranted;
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
        $this->bookIssueRepository =$bookIssueRepository;
        $this->bookRepository =$bookRepository;
        $this->readerRepository =$readerRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_CLIENT')]
    public function getBookIssues(Request $request): JsonResponse
    {
        $queryParams =$request->query->all();
        $page = max(1, (int)$request->query->get('page', 1));
        $itemsPerPage = max(1, (int)$request->query->get('itemsPerPage', 10));

        $issues = $this->bookIssueRepository->getBookIssues($queryParams, $page,$itemsPerPage);

        return $this->json($issues, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_CLIENT')]
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
    #[IsGranted('ROLE_MANAGER')]
    public function createBookIssue(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $book =$this->bookRepository->find($data['book_id'] ?? 0);$reader = $this->readerRepository->find($data['reader_id'] ?? 0);

        if (!$author || !$category) {
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
    #[IsGranted('ROLE_MANAGER')]
    public function updateBookIssue(int $id, Request$request): JsonResponse
    {
        $issue = $this->bookIssueRepository->find($id);
        if (!$issue) {
            return $this->json(['error' => 'Book Issue not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['book_id'])) {$book = $this->bookRepository->find($data['book_id']);
            if ($book) {
                $issue->setBook($book);
            }
        }

        if (isset($data['reader_id'])) {$reader = $this->readerRepository->find($data['reader_id']);
            if ($reader) {
                $issue->setReader($reader);
            }
        }

        try {
            if (isset($data['issue_date'])) {
                $issue->setIssueDate(new \DateTime($data['issue_date']));
            }
            if (array_key_exists('return_date', $data)) {$issue->setReturnDate($data['return_date'] ? new \DateTime($data['return_date']) : null);
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
    #[IsGranted('ROLE_ADMIN')]
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