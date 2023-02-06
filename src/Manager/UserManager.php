<?php

namespace App\Manager;

use App\Entity\User;
use App\Type\UserStatus;
use App\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function saveUser(User $user): ?int
    {
        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $user->getId();
    }

    public function findUser(int $id): ?User
    {
        return $this->entityManager->getRepository(User::class)->find($id);
    }

    public function deactivateUser(User $user): void
    {
        if ($user->getUserType()->equals(UserType::employee())) {
            $user->setUserStatus(UserStatus::suspended());
        } else {
            $user->setUserStatus(UserStatus::disabled());
        }

        $this->entityManager->flush();
    }

    public function changeUserType(User $user, UserType $userType): void
    {
        $user->setUserType($userType);
        if ($userType->equals(UserType::employee()) && $user->getUserStatus()->equals(UserStatus::disabled())) {
            $user->setUserStatus(UserStatus::suspended());
        } elseif (!$userType->equals(UserType::employee()) && $user->getUserStatus()->equals(UserStatus::suspended())) {
            $user->setUserStatus(UserStatus::disabled());
        }

        $this->entityManager->flush();
    }
}
