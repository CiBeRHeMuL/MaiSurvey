<?php

namespace App\Domain\Entity;

use App\Domain\Enum\RoleEnum;
use App\Domain\Enum\UserStatusEnum;
use App\Domain\ValueObject\Email;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Bridge\Doctrine\Types\UuidType;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table(name: '"user"')]
#[ORM\UniqueConstraint(fields: ['email'])]
class User
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: UuidType::NAME, nullable: false)]
    private Uuid|null $id = null;
    #[ORM\Column(type: 'string', length: 255, nullable: false, enumType: UserStatusEnum::class)]
    private UserStatusEnum $status;
    #[ORM\Column(type: 'text[]', nullable: false, enumType: RoleEnum::class)]
    private array $roles;
    #[ORM\Column(type: 'email', length: 255, nullable: false)]
    private Email $email;
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $password;
    #[ORM\Column(name: 'access_token', type: 'string', length: 40, nullable: false)]
    private string $accessToken;
    #[ORM\Column(name: 'access_token_expires_at', type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $accessTokenExpiresAt;
    #[ORM\Column(name: 'refresh_token', type: 'string', length: 40, nullable: false)]
    private string $refreshToken;
    #[ORM\Column(name: 'refresh_token_expires_at', type: 'datetime_immutable', nullable: false)]
    private DateTimeImmutable $refreshTokenExpiresAt;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $updatedAt;
    #[ORM\Column(type: 'boolean', nullable: false, options: ['default' => false])]
    private bool $deleted;
    #[ORM\Column(name: 'deleted_at', type: 'datetime_immutable', nullable: true)]
    private DateTimeImmutable|null $deletedAt = null;

    #[ORM\OneToOne(targetEntity: UserData::class, mappedBy: 'user', cascade: ['persist'])]
    private UserData|null $data = null;
    /** @var Collection<UserSubject> $studyingSubjects */
    #[ORM\OneToMany(targetEntity: UserSubject::class, mappedBy: 'user')]
    private Collection $studyingSubjects;
    /** @var Collection<UserSubject> $teachingSubjects */
    #[ORM\OneToMany(targetEntity: UserSubject::class, mappedBy: 'teacher')]
    private Collection $teachingSubjects;

    public function __construct()
    {
        $this->studyingSubjects = new ArrayCollection();
        $this->teachingSubjects = new ArrayCollection();
    }

    public function getId(): Uuid|null
    {
        return $this->id;
    }

    public function setId(Uuid $id): User
    {
        $this->id = $id;
        return $this;
    }

    public function getStatus(): UserStatusEnum
    {
        return $this->status;
    }

    public function setStatus(UserStatusEnum $status): User
    {
        $this->status = $status;
        return $this;
    }

    public function getEmail(): Email
    {
        return $this->email;
    }

    public function setEmail(Email $email): User
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): User
    {
        $this->password = $password;
        return $this;
    }

    public function getAccessToken(): string
    {
        return $this->accessToken;
    }

    public function setAccessToken(string $accessToken): User
    {
        $this->accessToken = $accessToken;
        return $this;
    }

    public function getAccessTokenExpiresAt(): DateTimeImmutable
    {
        return $this->accessTokenExpiresAt;
    }

    public function setAccessTokenExpiresAt(DateTimeImmutable $accessTokenExpiresAt): User
    {
        $this->accessTokenExpiresAt = $accessTokenExpiresAt;
        return $this;
    }

    public function getRefreshToken(): string
    {
        return $this->refreshToken;
    }

    public function setRefreshToken(string $refreshToken): User
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    public function getRefreshTokenExpiresAt(): DateTimeImmutable
    {
        return $this->refreshTokenExpiresAt;
    }

    public function setRefreshTokenExpiresAt(DateTimeImmutable $refreshTokenExpiresAt): User
    {
        $this->refreshTokenExpiresAt = $refreshTokenExpiresAt;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): User
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): User
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function isDeleted(): bool
    {
        return $this->deleted;
    }

    public function setDeleted(bool $deleted): User
    {
        $this->deleted = $deleted;
        return $this;
    }

    public function getDeletedAt(): DateTimeImmutable|null
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(DateTimeImmutable|null $deletedAt): User
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @param RoleEnum[] $roles
     *
     * @return $this
     */
    public function setRoles(array $roles): User
    {
        $this->roles = $roles;
        return $this;
    }

    public function addRole(RoleEnum $role): User
    {
        $this->roles[] = $role;
        $this->roles = array_unique($this->roles);
        return $this;
    }

    public function removeRole(RoleEnum $role): User
    {
        $this->roles = array_diff($this->roles, [$role]);
        return $this;
    }

    public function getData(): UserData|null
    {
        return $this->data;
    }

    public function setData(UserData $data): User
    {
        $this->data = $data;
        return $this;
    }

    public function isActive(): bool
    {
        return $this->getStatus() === UserStatusEnum::Active && $this->isDeleted() === false;
    }

    public function isDraft(): bool
    {
        return $this->getStatus() === UserStatusEnum::Draft && $this->isDeleted() === false;
    }

    public function isStudent(): bool
    {
        return in_array(RoleEnum::Student, $this->getRoles(), true);
    }

    public function isTeacher(): bool
    {
        return in_array(RoleEnum::Teacher, $this->getRoles(), true);
    }

    public function getStudyingSubjects(): Collection
    {
        return $this->studyingSubjects;
    }

    public function setStudyingSubjects(Collection $studyingSubjects): User
    {
        $this->studyingSubjects = $studyingSubjects;
        return $this;
    }

    public function getTeachingSubjects(): Collection
    {
        return $this->teachingSubjects;
    }

    public function setTeachingSubjects(Collection $teachingSubjects): User
    {
        $this->teachingSubjects = $teachingSubjects;
        return $this;
    }
}
