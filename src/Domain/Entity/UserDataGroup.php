<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: 'user_data_group')]
class UserDataGroup
{
    #[ORM\Id]
    #[ORM\Column(type: UuidType::NAME, nullable: false)]
    private Uuid|null $user_data_id = null;
    #[ORM\Column(type: UuidType::NAME, nullable: false)]
    private Uuid|null $group_id = null;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $updatedAt;

    #[ORM\OneToOne(targetEntity: UserData::class, inversedBy: 'group')]
    #[ORM\JoinColumn(name: 'user_data_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private UserData $userData;

    #[ORM\ManyToOne(targetEntity: Group::class)]
    #[ORM\JoinColumn(name: 'group_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Group $group;

    public function getUserDataId(): Uuid|null
    {
        return $this->user_data_id;
    }

    public function setUserDataId(Uuid|null $user_data_id): UserDataGroup
    {
        $this->user_data_id = $user_data_id;
        return $this;
    }

    public function getGroupId(): Uuid|null
    {
        return $this->group_id;
    }

    public function setGroupId(Uuid|null $group_id): UserDataGroup
    {
        $this->group_id = $group_id;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): UserDataGroup
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): UserDataGroup
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getUserData(): UserData
    {
        return $this->userData;
    }

    public function setUserData(UserData $userData): UserDataGroup
    {
        $this->userData = $userData;
        return $this;
    }

    public function getGroup(): Group
    {
        return $this->group;
    }

    public function setGroup(Group $group): UserDataGroup
    {
        $this->group = $group;
        return $this;
    }
}
