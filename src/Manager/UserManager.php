<?php

namespace App\Manager;

use App\Entity\User;
use App\Type\UserStatus;
use App\Type\UserType;
use Doctrine\ORM\EntityManagerInterface;

class UserManager
{
    public function __construct(private readonly EntityManagerInterface $entityManager)
    {
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
        if ($user->getUserType()->equals(\App\Type\UserType::employee)) {
            $user->setUserStatus(\App\Type\UserStatus::suspended);
        } else {
            $user->setUserStatus(\App\Type\UserStatus::disabled);
        }

        $this->entityManager->flush();
    }

    public function changeUserType(User $user, UserType $userType): void
    {
        $user->setUserType($userType);
        if ($userType->equals(\App\Type\UserType::employee) && $user->getUserStatus()->equals(\App\Type\UserStatus::disabled)) {
            $user->setUserStatus(\App\Type\UserStatus::suspended);
        } elseif (!$userType->equals(\App\Type\UserType::employee) && $user->getUserStatus()->equals(\App\Type\UserStatus::suspended)) {
            $user->setUserStatus(\App\Type\UserStatus::disabled);
        }

        $this->entityManager->flush();
    }
}
