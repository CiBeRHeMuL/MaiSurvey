<?php

namespace App\Presentation\Web\Response\Model;

use App\Domain\Entity\UserData as DomainUserData;

readonly class UserData
{
    public function __construct(
        public string $id,
        public string $first_name,
        public string $last_name,
        public string|null $patronymic,
        public UserDataGroup|null $group,
    ) {
    }

    public static function fromData(DomainUserData $data): self
    {
        return new self(
            $data->getId()->toRfc4122(),
            $data->getFirstName(),
            $data->getLastName(),
            $data->getPatronymic(),
            $data->getGroup() !== null
                ? UserDataGroup::fromUserDataGroup($data->getGroup())
                : null,
        );
    }
}
