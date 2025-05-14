<?php

namespace App\Domain\Enum;

enum ConnectTelegramResultEnum
{
    case Successful;
    case AlreadyConnected;
    case ConnectedToAnother;
}

