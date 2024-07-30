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
     * @param array|null $options
     */
    public function __construct(
        private readonly ConfigInterface $cronConfig,
        private ?array $options = null
    ) {
    }

    /**
     *  Returns an array of options for the group column.
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $this->options = array_map(
                fn($group) => [
                    'label' => $group,
                    'value' => $group
                ],
                array_keys($this->cronConfig->getJobs())
            );
        }

        return $this->options;
    }
}
