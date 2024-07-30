<?php
declare(strict_types=1);

namespace Hawksama\CronManager\Model;

use Magento\Cron\Model\Schedule;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection\AbstractDb as AbstractDbCollection;
use Magento\Framework\ObjectManagerInterface;
use Hawksama\CronManager\Model\ResourceModel\Cron as CronResource;
use Hawksama\CronManager\Helper\Data;
use Exception;

/**
 * Cron Model
 */
class Cron extends AbstractModel
{
    /**
     * @return void
     */
    protected function _construct(): void
    {
        $this->_init(CronResource::class);
    }

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ObjectManagerInterface $objectManager
     * @param Data $helper
     * @param AbstractDb|null $resource
     * @param AbstractDbCollection|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        private readonly ObjectManagerInterface $objectManager,
        private readonly Data $helper,
        AbstractDb $resource = null,
        AbstractDbCollection $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @param Schedule $schedule
     *
     * @return $this
     * @throws Exception
     */
    public function executeCron(Schedule $schedule): static
    {
        $instance = $this->getInstance();
        $method = $this->getMethod();

        if (empty($instance) || empty($method)) {
            throw new Exception((string) __('No callbacks found'));
        }

        // Create an instance of the specified class using the object manager because the specified class is dynamic
        $callback = [$this->objectManager->create($instance), $method];

        if (!is_callable($callback)) {
            throw new Exception(sprintf('Invalid callback: %s::%s can\'t be called', $instance, $method));
        }

        $schedule->setExecutedAt($this->helper->getTime());

        try {
            ($callback)($schedule);
        } catch (Exception $e) {
            throw new Exception(sprintf('An error occurred while executing %s::%s', $instance, $method), 0, $e);
        } finally {
            $schedule->setFinishedAt($this->helper->getTime());
        }

        return $this;
    }
}
