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
    ) {}

    /**
     * Executes a cron job based on the provided schedule and job data.
     *
     * @param Schedule $schedule
     * @param array $jobData
     * @return void
     */
    public function executeCronJob(Schedule $schedule, array $jobData): void
    {
        $cron = $this->cronFactory->create();
        $cron->setData($jobData);

        try {
            $cron->executeCron($schedule);
        } catch (Exception $e) {
            $schedule->addData([
                'status'      => Schedule::STATUS_ERROR,
                'messages'    => $e->getMessage(),
                'executed_at' => null,
            ]);

            $this->messageManager->addErrorMessage($e->getMessage());
        }

        try {
            $this->resource->save($schedule);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        }
    }
}
