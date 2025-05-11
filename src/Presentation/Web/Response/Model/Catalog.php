<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Enum as Enums;
use App\Presentation\Web\OpenApi\Attribute as LOA;
use App\Presentation\Web\Response\Model\Catalog\EnumCase;
use BackedEnum;
use UnitEnum;

readonly class Catalog
{
    public function __construct(
        #[LOA\EnumCases(Enums\RoleEnum::class)]
        public array $roles,
        #[LOA\EnumCases(Enums\SurveyStatusEnum::class)]
        public array $survey_statuses,
        #[LOA\EnumCases(Enums\NoticeChannelEnum::class)]
        public array $notice_channels,
        #[LOA\EnumCases(Enums\NoticeTypeEnum::class)]
        public array $notice_types,
        #[LOA\EnumCases(Enums\SortTypeEnum::class)]
        public array $sort_types,
        #[LOA\EnumCases(Enums\SurveyItemTypeEnum::class)]
        public array $survey_item_types,
        #[LOA\EnumCases(Enums\TeacherSubjectTypeEnum::class)]
        public array $teacher_subject_types,
    ) {
    }

    public static function generate(): self
    {
        return new self(
            self::enum(Enums\RoleEnum::class),
            self::enum(Enums\SurveyStatusEnum::class),
            self::enum(Enums\NoticeChannelEnum::class),
            self::enum(Enums\NoticeTypeEnum::class),
            self::enum(Enums\SortTypeEnum::class),
            self::enum(Enums\SurveyItemTypeEnum::class),
            self::enum(Enums\TeacherSubjectTypeEnum::class),
        );
    }

    /**
     * @param class-string<UnitEnum> $enum
     *
     * @return array
     */
    private static function enum(string $enum): array
    {
        return array_values(array_map(
            static function (UnitEnum $e) {
                $val = $e instanceof BackedEnum ? $e->value : $e->name;
                $name = method_exists($e, 'getName') ? $e->getName() : $e->name;
                return new EnumCase($val, $name);
            },
            $enum::cases(),
        ));
    }
}
