<?php

namespace App\Domain\Service\Notice;

use App\Domain\Entity\Notice;

interface NoticeRendererInterface
{
    public function render(Notice $notice): string;
}
