<?php

namespace App\Domain\Entity;

use App\Domain\Enum\SurveyStatusEnum;
use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\IdGenerator\UuidGenerator;
use Symfony\Component\Uid\Uuid;

#[ORM\Entity]
#[ORM\Table('survey')]
class Survey
{
    #[ORM\Id]
    #[ORM\GeneratedValue(strategy: 'CUSTOM')]
    #[ORM\CustomIdGenerator(UuidGenerator::class)]
    #[ORM\Column(type: 'uuid', nullable: false)]
    private Uuid $id;
    #[ORM\Column(name: 'subject_id', type: 'uuid', nullable: false)]
    private Uuid $subjectId;
    #[ORM\Column(type: 'string', length: 255, nullable: false)]
    private string $title;
    #[ORM\Column(
        type: 'string',
        length: 255,
        nullable: false,
        enumType: SurveyStatusEnum::class,
    )]
    private SurveyStatusEnum $status;
    #[ORM\Column(name: 'actual_to', type: 'datetime_immutable', nullable: true, options: ['default' => null])]
    private DateTimeImmutable|null $actualTo = null;
    #[ORM\Column(name: 'created_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $createdAt;
    #[ORM\Column(name: 'updated_at', type: 'datetime_immutable', nullable: false, options: ['default' => 'CURRENT_TIMESTAMP'])]
    private DateTimeImmutable $updatedAt;

    #[ORM\ManyToOne(targetEntity: Subject::class)]
    #[ORM\JoinColumn(name: 'subject_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private Subject $subject;
    /** @var Collection<SurveyItem> $items */
    #[ORM\OneToMany(targetEntity: SurveyItem::class, mappedBy: 'survey')]
    #[ORM\OrderBy(['position' => 'ASC'])]
    private Collection $items;
    #[ORM\OneToOne(targetEntity: SurveyStat::class, mappedBy: 'survey')]
    private SurveyStat|null $stat;

    public function __construct()
    {
        $this->items = new ArrayCollection();
    }

    public function getId(): Uuid
    {
        return $this->id;
    }

    public function setId(Uuid $id): Survey
    {
        $this->id = $id;
        return $this;
    }

    public function getSubjectId(): Uuid
    {
        return $this->subjectId;
    }

    public function setSubjectId(Uuid $subjectId): Survey
    {
        $this->subjectId = $subjectId;
        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): Survey
    {
        $this->title = $title;
        return $this;
    }

    public function getStatus(): SurveyStatusEnum
    {
        return $this->status;
    }

    public function setStatus(SurveyStatusEnum $status): Survey
    {
        $this->status = $status;
        return $this;
    }

    public function getActualTo(): DateTimeImmutable|null
    {
        return $this->actualTo;
    }

    public function setActualTo(DateTimeImmutable|null $actualTo): Survey
    {
        $this->actualTo = $actualTo;
        return $this;
    }

    public function getCreatedAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(DateTimeImmutable $createdAt): Survey
    {
        $this->createdAt = $createdAt;
        return $this;
    }

    public function getUpdatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(DateTimeImmutable $updatedAt): Survey
    {
        $this->updatedAt = $updatedAt;
        return $this;
    }

    public function getSubject(): Subject
    {
        return $this->subject;
    }

    public function setSubject(Subject $subject): Survey
    {
        $this->subject = $subject;
        return $this;
    }

    public function getItems(): Collection
    {
        return $this->items;
    }

    public function getStat(): ?SurveyStat
    {
        return $this->stat;
    }

    public function setStat(?SurveyStat $stat): Survey
    {
        $this->stat = $stat;
        return $this;
    }

    public function setItems(Collection $items): Survey
    {
        $this->items = $items;
        return $this;
    }

    public function addItem(SurveyItem $item): Survey
    {
        $this->items->add($item);
        return $this;
    }

    public function isActual(): bool
    {
        return $this->getStatus() === SurveyStatusEnum::Active
            && $this->getActualTo() !== null
            && $this->getActualTo()->getTimestamp() > (new DateTimeImmutable())->getTimestamp();
    }

    public function isClosed(): bool
    {
        return $this->getStatus() === SurveyStatusEnum::Closed
            || (
                $this->getActualTo() !== null
                && $this->getActualTo()->getTimestamp() < (new DateTimeImmutable())->getTimestamp()
            );
    }
}
