<?php

namespace App\Domain\Service\Notice;

use App\Domain\Entity\Notice;

interface NoticeRendererFactoryInterface
{
    public function getRenderer(Notice $notice): NoticeRendererInterface;
}
