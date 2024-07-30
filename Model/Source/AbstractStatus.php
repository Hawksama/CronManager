<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hawksama\CronManager\Model\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * AbstractStatus status source
 */
class AbstractStatus implements OptionSourceInterface
{
    /**
     * Get options array
     *
     * @return array
     */
    protected function getOptions(): array
    {
        return [];
    }

    /**
     * Convert options to an array of arrays with 'value' and 'label' keys
     *
     * @return array
     */
    public function toOptionArray(): array
    {
        $result = [];
        foreach ($this->getOptions() as $index => $value) {
            $result[] = ['value' => $index, 'label' => $value];
        }
        return $result;
    }
}
