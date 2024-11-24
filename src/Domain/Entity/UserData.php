<?php

namespace App\Domain\Entity;

use App\Domain\Enum\RoleEnum;
use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'user_data')]
class UserData
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, nullable: false)]
    private Uuid|null $id = null;
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $firstName;
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $lastName;
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private string|null $patronymic = null;
    #[ORM\Column(name: 'user_id', type: 'uuid', nullable: true)]
    private Uuid|null $userId = null;
    #[ORM\Column(name: 'for_role', type: 'string', length: 255, nullable: false, enumType: RoleEnum::class)]
    private RoleEnum $forRole;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(targetEntity: User::class, inversedBy: 'data')]
    #[ORM\JoinColumn(name: 'user_id', referencedColumnName: 'id', nullable: true, onDelete: 'SET NULL')]
    private User|null $user = null;
    #[ORM\OneToOne(targetEntity: UserDataGroup::class, mappedBy: 'userData', cascade: ['persist'])]
    #[ORM\InverseJoinColumn(name: 'id', referencedColumnName: 'user_data_id')]
    private UserDataGroup|null $group = null;

    public function getId(): Uuid|null
    {
        return $this->id;
    }

    public function setId(Uuid $id): UserData
    {
        $this->id = $id;
        return $this;
    }

    public function getFirstName(): string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): UserData
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): UserData
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getPatronymic(): string|null
    {
        return $this->patronymic;
    }

    public function setPatronymic(string|null $patronymic): UserData
    {
        $this->patronymic = $patronymic;
        return $this;
    }

    public function getUserId(): Uuid|null
    {
        return $this->userId;
    }

    public function setUserId(Uuid|null $userId): UserData
    {
        $this->userId = $userId;
        return $this;
    }

    public function getForRole(): RoleEnum
    {
        return $this->forRole;
    }

    public function setForRole(RoleEnum $forRole): UserData
    {
        $this->forRole = $forRole;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): UserData
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): UserData
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUser(): User|null
    {
        return $this->user;
    }

    public function setUser(User|null $user): UserData
    {
        $this->user = $user;
        return $this;
    }

    public function getGroup(): UserDataGroup|null
    {
        return $this->group;
    }

    public function setGroup(UserDataGroup|null $group): UserData
    {
        $this->group = $group;
        return $this;
    }
}
