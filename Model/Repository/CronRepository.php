<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hawksama\CronManager\Model\Repository;

use Exception;
use Hawksama\CronManager\Api\CronRepositoryInterface;
use Hawksama\CronManager\Model\Cron;
use Hawksama\CronManager\Model\CronFactory;
use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResource;
use Magento\Cron\Model\Schedule;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

/**
 * Cron RepositoryModel
 */
class CronRepository implements CronRepositoryInterface
{

    /**
     * @param CronFactory $cronFactory
     * @param ManagerInterface $messageManager
     * @param ScheduleResource $resource
     */
    public function __construct(
        private readonly CronFactory $cronFactory,
        private readonly ManagerInterface $messageManager,
        private readonly ScheduleResource $resource
    ) {
    }

    /**
     * Executes a cron job based on the provided schedule and job data.
     *
     * @param Schedule $schedule
     * @param array $jobData
     * @return void
     * @throws LocalizedException
     * @throws Exception
     */
    public function executeCronJob(Schedule $schedule, array $jobData): void
    {
        $cron = $this->createCron($jobData);

        try {
            $cron->executeCron($schedule);
        } catch (LocalizedException $e) {
            $this->handleCronError($schedule, $e);
        } catch (Exception $e) {
            $this->handleUnexpectedError($e);
        }

        try {
            $this->saveSchedule($schedule);
        } catch (LocalizedException $e) {
            $this->handleSaveError($e);
        }
    }

    /**
     * Creates a new Cron instance and sets its data.
     *
     * @param array $jobData
     * @return Cron
     */
    public function createCron(array $jobData): Cron
    {
        $cron = $this->cronFactory->create();
        $cron->setData($jobData);
        return $cron;
    }

    /**
     * Handles a cron error by updating the schedule and adding an error message.
     *
     * @param Schedule $schedule
     * @param LocalizedException $e
     * @return void
     * @throws LocalizedException
     */
    public function handleCronError(Schedule $schedule, LocalizedException $e): void
    {
        $schedule->addData([
            'status' => Schedule::STATUS_ERROR,
            'messages' => $e->getMessage(),
            'executed_at' => null,
        ]);

        throw $e;
    }

    /**
     *  Save the provided Schedule instance.
     *
     * @param Schedule $schedule
     * @return void
     * @throws AlreadyExistsException
     */
    public function saveSchedule(Schedule $schedule): void
    {
        $this->resource->save($schedule);
    }

    /**
     * Handles the error that occurs when saving a cron schedule.
     *
     * @param LocalizedException $e
     * @return void
     */
    public function handleSaveError(LocalizedException $e): void
    {
        $this->messageManager->addErrorMessage(
            __('Error saving cron schedule: %1', $e->getMessage())
        );

        throw $e;
    }

    /**
     * Handles unexpected errors that occur during the operation.
     *
     * @param Exception $e
     * @return void
     */
    public function handleUnexpectedError(Exception $e): void
    {
        $this->messageManager->addErrorMessage(
            __('An unexpected error occurred while saving the cron schedule: %1', $e->getMessage())
        );

        throw $e;
    }
}
