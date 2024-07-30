<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hawksama\CronManager\Api;

use Magento\Cron\Model\Schedule;
use Hawksama\CronManager\Model\Cron;
use Magento\Framework\Exception\LocalizedException;

/**
 * Interface CronRepositoryInterface
 *
 * This interface defines the contract for a repository that manages cron jobs.
 */
interface CronRepositoryInterface
{
    /**
     * Execute a cron job based on the provided schedule and job data.
     *
     * @param Schedule $schedule
     * @param array $jobData
     * @return void
     * @throws LocalizedException
     */
    public function executeCronJob(Schedule $schedule, array $jobData): void;

    /**
     * Create a Cron instance and set its data.
     *
     * @param array $jobData
     * @return Cron
     */
    public function createCron(array $jobData): Cron;

    /**
     * Handle errors that occur during cron job execution.
     *
     * @param Schedule $schedule
     * @param LocalizedException $e
     * @return void
     */
    public function handleCronError(Schedule $schedule, LocalizedException $e): void;

    /**
     * Save the provided Schedule instance.
     *
     * @param Schedule $schedule
     * @return void
     * @throws LocalizedException
     */
    public function saveSchedule(Schedule $schedule): void;

    /**
     * Handle errors that occur during saving of the schedule.
     *
     * @param LocalizedException $e
     * @return void
     */
    public function handleSaveError(LocalizedException $e): void;

    /**
     * Handle unexpected errors that occur during the operation.
     *
     * @param \Exception $e
     * @return void
     */
    public function handleUnexpectedError(\Exception $e): void;
}
