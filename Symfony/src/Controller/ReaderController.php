<?php

namespace App\Controller;

use App\Entity\Reader;
use App\Repository\ReaderRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

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
        $this->readerRepository = $readerRepository;
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    #[Route('', methods: ['GET'])]
    public function getReaders(Request $request): JsonResponse
    {
        $queryParams = $request->query->all();
        $readers = $this->readerRepository->getReaders($queryParams);

        return $this->json($readers, Response::HTTP_OK);
    }

    /**
     * @param int $id
     * @return JsonResponse
     */
    #[Route('/{id}', methods: ['GET'])]
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
    public function updateReader(int $id, Request $request): JsonResponse
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