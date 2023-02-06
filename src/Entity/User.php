<?php

namespace App\Entity;

use App\Type\UserStatus;
use App\Type\UserType;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Table("`user`")
 * @ORM\Entity()
 */
class User
{
    /**
     * @ORM\Column(type="bigint")
     * @ORM\Id()
     * @ORM\GeneratedValue(strategy="IDENTITY")
     */
    private ?int $id = null;

    /**
     * @ORM\Column(type="string", length="100", nullable=false)
     */
    private string $firstName;

    /**
     * @ORM\Column(type="string", length="100", nullable=false)
     */
    private string $middleName;

    /**
     * @ORM\Column(type="string", length="100", nullable=false)
     */
    private string $lastName;

    /**
     * @ORM\Column(type="smallint", nullable=false, options={"unsigned"=true})
     */
    private int $age;

    /**
     * @ORM\Column(type="user_type", length="10", nullable=false)
     */
    private UserType $userType;

    /**
     * @ORM\Column(type="user_status", length="10", nullable=false)
     */
    private UserStatus $userStatus;

    public function __construct(string $firstName, string $middleName, string $lastName, string $age, UserType $userType)
    {
        $this->firstName = $firstName;
        $this->middleName = $middleName;
        $this->lastName = $lastName;
        $this->age = $age;
        $this->userType = $userType;
        $this->userStatus = UserStatus::active();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function getMiddleName(): string
    {
        return $this->middleName;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function getAge(): string
    {
        return $this->age;
    }

    public function getUserType(): UserType
    {
        return $this->userType;
    }

    public function getUserStatus(): UserStatus
    {
        return $this->userStatus;
    }

    public function setUserType(UserType $userType): void
    {
        $this->userType = $userType;
    }

    public function setUserStatus(UserStatus $userStatus): void
    {
        $this->userStatus = $userStatus;
    }
}
