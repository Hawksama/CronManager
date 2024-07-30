<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hawksama\CronManager\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\Exception\LocalizedException;
use Hawksama\CronManager\Helper\Data;

/**
 * CronProvider DataProvider for Ui Component
 */
class CronProvider extends AbstractDataProvider
{
    /**
     * Class constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param Data $helper
     * @param array $meta
     * @param array $data
     * @param int $offset
     * @param int $size
     * @param string $sortDir
     * @param string $sortField
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        private readonly Data $helper,
        array $meta = [],
        array $data = [],
        protected int $offset = 1,
        protected int $size = 20,
        protected string $sortDir = 'asc',
        protected string $sortField = 'name'
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     *  Method to set the sort field and direction for data retrieval.
     *
     * @param string $field
     * @param string $direction
     * @return void
     */
    public function addOrder($field, $direction): void
    {
        $this->sortDir   = strtolower($direction);
        $this->sortField = $field;
    }

    /**
     * Sets the limit for the data provider.
     *
     * @param int $offset
     * @param int $size
     * @return void
     */
    public function setLimit($offset, $size): void
    {
        $this->size   = $size;
        $this->offset = $offset;
    }

    /**
     * Retrieves data from the helper and returns it in an array with total records and items.
     *
     * @return array{totalRecords: int, items: array}
     * @throws LocalizedException
     */
    public function getData(): array
    {
        $cronJobs = $this->helper->getCronJobs();
        $totalRecords = count($cronJobs);
        $offset = $this->offset - 1;
        $itemsArray = array_slice($cronJobs, $offset * $this->size, $this->size);

        // Convert items from an associative array to an indexed array
        $items = array_values($itemsArray);

        return ['totalRecords' => $totalRecords, 'items' => $items];
    }
}
