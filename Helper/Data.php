<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hawksama\CronManager\Helper;

use Magento\Cron\Model\ConfigInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\AbstractHelper;

/**
 * Class Data
 * @package Hawksama\CronManager\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @param Context $context
     * @param ConfigInterface $cronConfig
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        private readonly ConfigInterface $cronConfig,
        private readonly TimezoneInterface $timezone,
    ) {

        parent::__construct($context);
    }

    /**
     * Retrieve cron jobs.
     *
     * @param string|null $jobName
     * @return array
     * @throws Exception
     */
    public function getCronJobs(?string $jobName = null): array
    {
        $cronJobs = $this->cronConfig->getJobs();

        if (empty($cronJobs)) {
            throw new Exception('No cron jobs found');
        }

        return $this->filterCronJobs($cronJobs, $jobName);
    }

    /**
     * Filter and retrieve specific cron jobs based on job name.
     *
     * @param array $cronJobs
     * @param string|null $jobName
     * @return array
     */
    private function filterCronJobs(array $cronJobs, ?string $jobName): array
    {
        $data = [];

        foreach ($cronJobs as $group => $jobs) {
            foreach ((array) $jobs as $code => $job) {
                if (!$jobName || $jobName === $code) {
                    $data[$code] = $this->getCronJobData($job, $code, $group);
                    if ($jobName === $code) {
                        return $data[$code];
                    }
                }
            }
        }

        return $data;
    }

    /**
     * Prepare cron job data.
     *
     * @param array $jobData
     * @param string $jobCode
     * @param string $jobGroup
     * @return array
     */
    private function getCronJobData(array $jobData, string $jobCode, string $jobGroup): array
    {
        $jobData['name'] = $jobCode;
        $jobData['group'] = $jobGroup;
        $jobData['schedule'] = $this->getJobSchedule($jobData);
        $jobData['status'] = $jobData['status'] ?? '1';
        $jobData['is_user'] = $jobData['is_user'] ?? '0';

        return $jobData;
    }

    /**
     * Get the schedule of the cron job.
     *
     * @param array $jobData
     * @return string
     */
    private function getJobSchedule(array $jobData): string
    {
        if (isset($jobData['config_path'])) {
            return $this->scopeConfig->getValue($jobData['config_path'], ScopeInterface::SCOPE_STORE) ?? '';
        }

        return $jobData['schedule'] ?? '';
    }

    /**
     * Get the current time.
     *
     * @param bool $isFloor
     * @return string
     */
    public function getTime(bool $isFloor = false): string
    {
        $time = $this->timezone->scopeTimeStamp();
        $format = $isFloor ? 'Y-m-d H:i:00' : 'Y-m-d H:i:s';

        return date($format, $time);
    }
}
