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
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Email(message: 'Неверный формат почты')]
        public string $email,
        /** Пароль */
        #[SensitiveParameter]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        public string $password,
        /** Роль */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[LAssert\EnumChoice(enum: RoleEnum::class, message: 'Значение должно входить в список допустимых')]
        public string $role,
        /** Имя */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $first_name,
        /** Фамилия */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым')]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string $last_name,
        /** Отчество */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым', allowNull: true)]
        #[Assert\Length(max: 255, maxMessage: 'Значение должно быть короче 255 символов')]
        public string|null $patronymic,
        /** Группа */
        #[Assert\Type('string', message: 'Значение должно быть строкой')]
        #[Assert\NotBlank(message: 'Значение не должно быть пустым', allowNull: true)]
        #[Assert\Uuid(message: 'Значение должно быть корректным uuid')]
        public string|null $group_id,
    ) {
    }
}
