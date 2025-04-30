<?php

namespace App\Domain\Entity;

use DateTimeImmutable;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('semester')]
#[ORM\UniqueConstraint(columns: ['year', 'spring'])]
class Semester
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(type: 'integer', nullable: false)]
    private int $year;
    #[ORM\Column(type: 'boolean', nullable: false)]
    private bool $spring;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): Semester
    {
        $this->id = $id;
        return $this;
    }

    public function getYear(): int
    {
        return $this->year;
    }

    public function setYear(int $year): Semester
    {
        $this->year = $year;
        return $this;
    }

    public function isSpring(): bool
    {
        return $this->spring;
    }

    public function setSpring(bool $spring): Semester
    {
        $this->spring = $spring;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): Semester
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getDateStart(): DateTimeImmutable
    {
        return (new DateTimeImmutable())
            ->setDate(
                $this->year,
                $this->spring ? 2 : 9,
                1,
            )
            ->setTime(0, 0, 0);
    }

    public function getDateEnd(): DateTimeImmutable
    {
        return (new DateTimeImmutable())
            ->setDate(
                $this->year,
                $this->spring ? 5 : 12,
                31,
            )
            ->setTime(0, 0, 0);
    }

    public function getName(): string
    {
        return ($this->spring ? 'Весенний' : 'Осенний') . " семестр {$this->getYear()} года";
    }

    public function getShortName(): string
    {
        return ($this->spring ? 'Весна' : 'Осень') . ' ' . $this->getYear();
    }
}
