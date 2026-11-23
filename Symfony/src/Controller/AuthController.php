<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/api')]
final class AuthController extends AbstractController
{
    private const ALLOWED_ROLES = ['ROLE_CLIENT', 'ROLE_MANAGER', 'ROLE_ADMIN'];

    public function __construct(
        private EntityManagerInterface $entityManager,
        private UserPasswordHasherInterface $passwordHasher
    ) {}

    #[Route('/register', name: 'app_register', methods: ['POST'])]
    public function register(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (empty($data['email']) || empty($data['password'])) {
            return $this->json(
                ['message' => 'Email and password are required'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $existingUser = $this->entityManager->getRepository(User::class)
            ->findOneBy(['email' => $data['email']]);

        if ($existingUser) {
            return $this->json(
                ['message' => 'User with this email already exists'],
                Response::HTTP_CONFLICT
            );
        }

        $role = $data['role'] ?? 'ROLE_CLIENT';
        if (!in_array($role, self::ALLOWED_ROLES, true)) {
            return $this->json(
                ['message' => 'Invalid role. Allowed: ROLE_CLIENT, ROLE_MANAGER, ROLE_ADMIN'],
                Response::HTTP_BAD_REQUEST
            );
        }

        $user = new User();
        $user->setEmail($data['email']);
        $user->setRoles([$role]);
        $user->setPassword($this->passwordHasher->hashPassword($user, $data['password']));

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/me', name: 'app_me', methods: ['GET'])]
    public function me(): JsonResponse
    {
        /** @var User $user */
        $user = $this->getUser();

        return $this->json([
            'id' => $user->getId(),
            'email' => $user->getEmail(),
            'roles' => $user->getRoles(),
        ], Response::HTTP_OK);
    }
}