<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hawksama\CronManager\Ui\DataProvider;

use Magento\Ui\DataProvider\AbstractDataProvider;
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
     * @param string $field
     * @param string $direction
     */
    public function addOrder($field, $direction): void
    {
        $this->sortDir   = strtolower($direction);
        $this->sortField = $field;
    }

    /**
     * @param int $offset
     * @param int $size
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
     * @throws \Exception
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
