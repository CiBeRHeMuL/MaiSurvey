<?php

namespace App\Application\Dto\User;

use App\Application\Validator\Constraints as LAssert;
use App\Domain\Enum\RoleEnum;
use SensitiveParameter;
use Symfony\Component\Validator\Constraints as Assert;

readonly class CreateFullUserDto
{
    public function __construct(
        /** Почта */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Email(message: 'Неверный формат почты')]
        public string $email,
        /** Пароль */
        #[SensitiveParameter]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        public string $password,
        /** Роль */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[LAssert\EnumChoice(enum: RoleEnum::class, message: 'Значение должно входить в список допустимых')]
        public string $role,
        /** Имя */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $first_name,
        /** Фамилия */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $last_name,
        /** Отчество */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string|null $patronymic,
        /** Группа */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string|null $group,
    ) {
    }
}
