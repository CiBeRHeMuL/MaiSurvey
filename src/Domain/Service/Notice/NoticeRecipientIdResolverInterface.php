<?php

namespace App\Domain\Service\Notice;

use App\Domain\Entity\User;
use App\Domain\Exception\ErrorException;

interface NoticeRecipientIdResolverInterface
{
    /**
     * @param User $user
     *
     * @return string
     *
     * @throws ErrorException если нет идентификатора, то метод ОБЯЗАН выбросить исключение
     */
    public function getIdentifier(User $user): string;
}
