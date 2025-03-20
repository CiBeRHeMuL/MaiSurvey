<?php

namespace App\Presentation\Messenger\Scheduler;

use App\Presentation\Messenger\Message\CloseExpiredSurveysMessage;
use Symfony\Component\Scheduler\Attribute\AsSchedule;
use Symfony\Component\Scheduler\RecurringMessage;
use Symfony\Component\Scheduler\Schedule;
use Symfony\Component\Scheduler\ScheduleProviderInterface;

#[AsSchedule('close_active_surveys')]
class CloseExpiredSurveysProvider implements ScheduleProviderInterface
{
    private Schedule $schedule;

    public function getSchedule(): Schedule
    {
        if (!isset($this->schedule)) {
            $schedule = new Schedule();
            $schedule = $schedule->with(
                RecurringMessage::cron(
                    '0 * * * *',
                    new CloseExpiredSurveysMessage(),
                ),
            );
            $this->schedule = $schedule;
        }
        return $this->schedule;
    }
}
