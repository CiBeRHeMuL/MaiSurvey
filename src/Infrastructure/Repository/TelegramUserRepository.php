<?php

namespace App\Infrastructure\Repository;

use App\Domain\Dto\TelegramUser\ChatId;
use App\Domain\Entity\TelegramUser;
use App\Domain\Repository\TelegramUserRepositoryInterface;
use Qstart\Db\QueryBuilder\Query;

class TelegramUserRepository extends Common\AbstractRepository implements TelegramUserRepositoryInterface
{
    public function findByChatId(ChatId $chatId): TelegramUser|null
    {
        $q = Query::select()
            ->from($this->getClassTable(TelegramUser::class))
            ->where(['chat_id' => $chatId->getId()]);
        return $this->findOneByQuery($q, TelegramUser::class, ['user']);
    }
}
