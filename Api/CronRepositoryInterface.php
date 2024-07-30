<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hawksama\CronManager\Api;

use Magento\Cron\Model\Schedule;
use Hawksama\CronManager\Model\Cron;

/**
 * Cron RepositoryInterface
 */
interface CronRepositoryInterface
{
    /**
     * Execute a cron job.
     *
     * @param Schedule $schedule
     * @param array $jobData
     * @return void
     */
    public function executeCronJob(Schedule $schedule, array $jobData): void;
}
