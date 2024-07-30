<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hawksama\CronManager\Controller\Adminhtml\Cron;

use Hawksama\CronManager\Controller\Adminhtml\AbstractController;
use Magento\Framework\View\Result\PageFactory;
use Exception;
use Magento\Backend\App\Action\Context;
use Magento\Cron\Model\Schedule;
use Magento\Cron\Model\ScheduleFactory;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Cron\Model\ResourceModel\Schedule as ScheduleResource;
use Hawksama\CronManager\Model\Repository\CronRepository;
use Hawksama\CronManager\Helper\Data;
use Psr\Log\LoggerInterface;

/**
 * Execute controller
 */
class Execute extends AbstractController
{
    /**
     * @param Context $context
     * @param Data $helper
     * @param ScheduleFactory $scheduleFactory
     * @param TypeListInterface $cacheTypeList
     * @param CronRepository $cronRepository
     * @param ScheduleResource $resource
     * @param LoggerInterface $logger
     */
    public function __construct(
        Context $context,
        PageFactory $pageFactory,
        private readonly Data $helper,
        private readonly ScheduleFactory $scheduleFactory,
        private readonly TypeListInterface $cacheTypeList,
        private readonly CronRepository $cronRepository,
        private readonly ScheduleResource $resource,
        private readonly LoggerInterface $logger
    ) {
        parent::__construct($context, $pageFactory);
    }

    /**
     * @throws Exception
     */
    public function execute(): ResponseInterface
    {
        $requestParams = $this->getRequest()->getParams();
        $result = $this->processSelectedJobs($requestParams);

        $this->handleResult($result);

        return $this->_redirect('*/*/');
    }

    /**
     * Processes the selected cron jobs.
     *
     * @param array $requestParams The request parameters.
     * @return array The result array containing 'success' and 'failure' keys.
     * @throws Exception
     */
    public function processSelectedJobs(array $requestParams): array
    {
        $result = [
            'success' => [
                'total' => 0,
                'crons' => []
            ],
            'failure' => [
                'total' => 0,
                'crons' => []
            ]
        ];

        if (isset($requestParams['selected'])) {
            foreach ($requestParams['selected'] as $name) {
                $cronJob = $this->helper->getCronJobs($name);
                if ($this->isJobExecutable($cronJob)) {
                    $this->createAndExecuteJob($cronJob, $result);
                } else {
                    $this->updateJobResult($result, false, $name);
                }
            }
        }

        return $result;
    }

    /**
     * Handles the result of executing cron jobs.
     *
     * @param array $result The result array containing 'success' and 'failure' keys.
     * @return void
     */
    public function handleResult(array $result): void
    {
        if ($successTotal = $result['success']['total']) {
            $this->cacheTypeList->cleanType('config');
            $successNames = implode(', ', $result['success']['crons']);

            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been executed. Successful cron jobs: %2.', $successTotal, $successNames)
            );

            // Log successful executions
            foreach ($result['success']['crons'] as $name) {
                $this->logger->info(__('CronManager: Cron job %1 has been executed successfully.', $name));
            }
        }

        if ($failureTotal = $result['failure']['total']) {
            $failureNames = implode(', ', $result['failure']['crons']);

            $this->messageManager->addErrorMessage(
                __('A total of %1 record(s) cannot execute. Failed cron jobs: %2.', $failureTotal, $failureNames)
            );

            // Log failed executions
            foreach ($result['failure']['crons'] as $name) {
                $this->logger->error(__('CronManager: Cron job %1 has failed to execute.', $name));
            }
        }
    }

    /**
     * Checks if a job is executable based on the provided job data.
     *
     * @param array $jobData The job data to check.
     * @return bool Returns true if the job is executable, false otherwise.
     */
    private function isJobExecutable(array $jobData): bool
    {
        return isset($jobData['status']) && $jobData['status'];
    }

    /**
     * Creates and executes a job.
     *
     * @param array $jobData The data for the job.
     * @param array &$result The result of the job execution.
     * @throws Exception If an error occurs during job execution.
     * @return void
     */
    public function createAndExecuteJob(array $jobData, array &$result): void
    {
        $schedule = $this->createSchedule($jobData);

        try {
            $this->cronRepository->executeCronJob($schedule, $jobData);
            $this->updateJobResult($result, true, $jobData['name']);
            $this->saveSchedule($schedule);
        } catch (Exception $e) {
            $this->updateJobResult($result, false, $jobData['name']);
            $this->handleExecutionError($schedule, $e);
        }
    }

    /**
     * Creates a new schedule based on the provided job data.
     *
     * @param array $jobData The job data used to create the schedule.
     *                      The 'name' key is used as the job code.
     * @return Schedule The newly created schedule.
     */
    public function createSchedule(array $jobData): Schedule
    {
        $data = [
            'job_code'     => $jobData['name'] ?? '',
            'status'       => Schedule::STATUS_SUCCESS,
            'created_at'   => $this->helper->getTime() ?? '',
            'scheduled_at' => $this->helper->getTime(true) ?? '',
        ];

        return $this->scheduleFactory->create()->setData($data);
    }

    /**
     * Updates the job result array based on the success status.
     *
     * @param array &$result The job result array to be updated.
     * @param bool $success The success status of the job.
     * @param string $name The name of the cron job.
     * @return void
     */
    private function updateJobResult(array &$result, bool $success, string $name): void
    {
        if ($success) {
            $result['success']['total']++;
            $result['success']['crons'][] = $name;
        } else {
            $result['failure']['total']++;
            $result['failure']['crons'][] = $name;
        }
    }

    /**
     * Saves the given schedule to the database.
     *
     * @param Schedule $schedule The schedule to be saved.
     * @throws Exception If an error occurs while saving the schedule.
     * @return void
     */
    public function saveSchedule(Schedule $schedule): void
    {
        try {
            $this->resource->save($schedule);
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->logger->error('CronManager: Error saving schedule', ['exception' => $e]);
        }
    }

    /**
     * Handles an error that occurred during the execution of a schedule.
     *
     * @param Schedule $schedule The schedule that encountered the error.
     * @param Exception $e The exception that was thrown.
     * @return void
     * @throws Exception
     */
    public function handleExecutionError(Schedule $schedule, Exception $e): void
    {
        $this->logger->error('CronManager: Error executing job', ['exception' => $e]);

        $schedule->addData([
            'status'      => Schedule::STATUS_ERROR,
            'messages'    => $e->getMessage(),
            'executed_at' => null,
        ]);

        $this->messageManager->addErrorMessage($e->getMessage());
        $this->saveSchedule($schedule);
    }
}
