<?php

namespace App\Controller;

use App\Entity\Reader;
use App\Repository\ReaderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/readers')]
class ReaderController extends AbstractController
{
    /**
     * @var ReaderRepository
     */
    private ReaderRepository $readerRepository;

    /**
     * @param ReaderRepository $readerRepository
     */
    public function __construct(ReaderRepository $readerRepository)
    {
        $this->readerRepository =$readerRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['GET'])]
    #[IsGranted('ROLE_CLIENT')]
    public function getReaders(Request $request): JsonResponse
    {
        $queryParams =$request->query->all();
        $page = max(1, (int)$request->query->get('page', 1));
        $itemsPerPage = max(1, (int)$request->query->get('itemsPerPage', 10));

        $readers = $this->readerRepository->getReaders($queryParams, $page,$itemsPerPage);

        return $this->json($readers, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['GET'])]
    #[IsGranted('ROLE_CLIENT')]
    public function getReaderById(int $id): JsonResponse
    {
        $reader = $this->readerRepository->find($id);
        if (!$reader) {
            return $this->json(['error' => 'Reader not found'], Response::HTTP_NOT_FOUND);
        }

        return $this->json($reader, Response::HTTP_OK);
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['POST'])]
    #[IsGranted('ROLE_MANAGER')]
    public function createReader(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        $reader = new Reader();
        $reader->setFullName($data['full_name'] ?? '');
        $reader->setEmail($data['email'] ?? '');

        $this->readerRepository->save($reader, true);

        return $this->json($reader, Response::HTTP_CREATED);
    }

    /**
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['PATCH'])]
    #[IsGranted('ROLE_MANAGER')]
    public function updateReader(int $id, Request$request): JsonResponse
    {
        $reader = $this->readerRepository->find($id);
        if (!$reader) {
            return $this->json(['error' => 'Reader not found'], Response::HTTP_NOT_FOUND);
        }

        $data = json_decode($request->getContent(), true);

        if (isset($data['full_name'])) {
            $reader->setFullName($data['full_name']);
        }
        if (isset($data['email'])) {
            $reader->setEmail($data['email']);
        }

        $this->readerRepository->save($reader, true);

        return $this->json($reader, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['DELETE'])]
    #[IsGranted('ROLE_ADMIN')]
    public function deleteReader(int $id): JsonResponse
    {
        $reader = $this->readerRepository->find($id);
        if (!$reader) {
            return $this->json(['error' => 'Reader not found'], Response::HTTP_NOT_FOUND);
        }

        $this->readerRepository->remove($reader, true);

        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
}