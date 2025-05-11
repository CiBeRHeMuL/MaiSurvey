<?php

namespace App\Application\Dto\Me;

use App\Application\OpenApi\Attribute as LOA;
use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\NoticeChannelEnum;
use App\Domain\Enum\NoticeTypeEnum;
use Symfony\Component\Validator\Constraints as Assert;

readonly class UpdateMeDto
{
    public function __construct(
        /** Имя */
        #[Assert\Type('string')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $first_name,
        /** Фамилия */
        #[Assert\Type('string')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $last_name,
        /** Отчество */
        #[Assert\Type(['string', 'null'])]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string|null $patronymic,
        /** Включить уведомления */
        #[Assert\Type('bool', message: 'Значение должно быть булевым')]
        public bool $notices_enabled = false,
        /** Типы уведомлений */
        #[LOA\EnumItems(NoticeTypeEnum::class)]
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
        ])]
        #[LAssert\EnumChoice(enum: NoticeTypeEnum::class, multiple: true, message: 'Значение должно входить в список допустимых')]
        public array $notice_types = [],
        /** Способы уведомлений */
        #[LOA\EnumItems(NoticeChannelEnum::class)]
        #[Assert\All([
            new Assert\Type('string', message: 'Значение должно быть строкой'),
        ])]
        #[LAssert\EnumChoice(enum: NoticeChannelEnum::class, multiple: true, message: 'Значение должно входить в список допустимых')]
        public array $notice_channels = [],
    ) {
    }
}
