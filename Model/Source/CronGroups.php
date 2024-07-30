<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hawksama\CronManager\Model\Source;

use Magento\Cron\Model\ConfigInterface;

/**
 * CronGroups status source
 */
class CronGroups extends AbstractStatus
{
    /**
     * Group constructor.
     *
     * @param ConfigInterface $cronConfig
     */
    public function __construct(
        private readonly ConfigInterface $cronConfig,
        private ?array $options = null
    ) {
    }

    /**
     * @return array
     */
    public function toOptionArray(): array
    {
        if (is_null($this->options)) {
            $this->options = array_map(
                fn($group) => [
                    'label' => $group,
                    'value' => $group
                ],
                array_keys($this->cronConfig->getJobs() ?? [])
            );
        }

        return $this->options;
    }
}
