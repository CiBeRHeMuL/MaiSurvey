<?php

namespace App\Domain\Service\Notice;

use App\Domain\Entity\Notice;

interface NoticeSenderFactoryInterface
{
    public function getSender(Notice $notice): NoticeSenderInterface;
}
