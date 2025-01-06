<?php

namespace App\Presentation\Web\Listener;

use App\Presentation\Web\Enum\HttpStatusCodeEnum;
use App\Presentation\Web\Response\Model\Common\DbInfo;
use App\Presentation\Web\Response\Model\Common\Profile;
use App\Presentation\Web\Response\Model\Common\ProfileResponse;
use App\Presentation\Web\Response\Model\Common\Query;
use App\Presentation\Web\Response\Response;
use Doctrine\Bundle\DoctrineBundle\DataCollector\DoctrineDataCollector;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Symfony\Component\VarDumper\Cloner\Data;

class ProfilerListener
{
    public function __construct(
        #[Autowire('@profiler')]
        private Profiler $profiler,
        #[Autowire('%kernel.debug%')]
        private bool $enabled,
        #[Autowire('%kernel.project_dir%')]
        private string $projectDir,
    ) {
    }

    public function __invoke(ResponseEvent $event): void
    {
        if (
            $this->enabled
            && !$event->isPropagationStopped()
            && $event->isMainRequest()
            && $this->profiler->isEnabled()
            && $event->getRequest()->query->has('debug')
            && $this->profiler->has('db')
        ) {
            /** @var DoctrineDataCollector $databaseCollector */
            $databaseCollector = $this->profiler->get('db');

            $collectStack = $event->getRequest()->query->get('debug') === '1';

            $collected = [];
            foreach ($databaseCollector->getQueries() as $connection => $queries) {
                foreach ($queries as $query) {
                    $params = $query['params'];
                    if ($params instanceof Data) {
                        $params = $params->getValue(true);
                    }
                    $collected[] = new Query(
                        $connection,
                        $query['sql'],
                        $query['executionMS'],
                        $params,
                        $collectStack
                            ? array_values(
                                array_filter(
                                    $query['backtrace'],
                                    fn(array $trace) => str_starts_with($trace['file'] ?? '', "$this->projectDir/src"),
                                ),
                            )
                            : null,
                    );
                }
            }
            $event->setResponse(
                Response::profile(
                    new ProfileResponse(
                        new Profile(
                            new DbInfo(
                                $databaseCollector->getQueryCount(),
                                $collected,
                            ),
                        ),
                    ),
                    HttpStatusCodeEnum::from($event->getResponse()->getStatusCode()),
                ),
            );
            $event->stopPropagation();
        }
    }
}
