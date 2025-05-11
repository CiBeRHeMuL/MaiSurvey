<?php

namespace App\Domain\Service\Notice;

use App\Domain\Entity\Notice;

interface NoticeSenderInterface
{
    public function send(Notice $notice): bool;
}
