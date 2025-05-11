<?php

namespace App\Domain\Enum;

enum NoticeStatusEnum: string
{
    case Created = 'created';
    case Sending = 'sending';
    case Sent = 'sent';
    case Delivered = 'delivered';
    case SendFailed = 'send_failed';
}

