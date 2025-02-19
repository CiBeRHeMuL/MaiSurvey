<?php

namespace App\Infrastructure\Messenger\Handler;

use App\Infrastructure\Messenger\Message\RefreshViewsMessage;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Messenger\Attribute\AsMessageHandler;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Throwable;

#[AsMessageHandler]
class RefreshMessageHandler
{
    public const string CACHE_PREFIX = 'refresh_view_last_time_';

    public function __construct(
        private EntityManagerInterface $em,
        private LoggerInterface $logger,
        private CacheInterface $cache,
    ) {
    }

    public function __invoke(RefreshViewsMessage $message): void
    {
        try {
            foreach ($message->getViews() as $view) {
                /** @var DateTimeImmutable|null $lastUpdated */
                $lastUpdated = $this->cache->get(
                    self::CACHE_PREFIX . $view,
                    function (ItemInterface $item) {
                        $item->expiresAt((new DateTimeImmutable())->modify('+5 minutes'));
                        $item->set(new DateTimeImmutable());
                        return null;
                    },
                );
                if ($lastUpdated === null) {
                    $this
                        ->em
                        ->getConnection()
                        ->executeStatement(
                            "REFRESH MATERIALIZED VIEW $view",
                        );
                    $this->logger->info("VIEW $view SUCCESSFULLY REFRESHED");
                }
            }
        } catch (Throwable $e) {
            $this->logger->error($e);
        }
    }
}
