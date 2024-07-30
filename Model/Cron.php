<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hawksama\CronManager\Model;

use Magento\Cron\Model\Schedule;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Registry;
use Magento\Framework\Data\Collection\AbstractDb as AbstractDbCollection;
use Magento\Framework\ObjectManagerInterface;
use Hawksama\CronManager\Model\ResourceModel\Cron as CronResource;
use Hawksama\CronManager\Helper\Data;
use Hawksama\CronManager\Api\Data\CronInterface;
use Psr\Log\LoggerInterface;

/**
 * Cron Model
 */
class Cron extends AbstractModel implements CronInterface
{
    /**
     * Initializes the object by calling the `_init` method with the `CronResource` class as an argument.
     *
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
     * @param LoggerInterface $logger
     * @param AbstractDb|null $resource
     * @param AbstractDbCollection|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        private readonly ObjectManagerInterface $objectManager,
        private readonly Data $helper,
        private readonly LoggerInterface $logger,
        AbstractDb $resource = null,
        AbstractDbCollection $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * Executes the cron job.
     *
     * @param Schedule $schedule
     * @throws LocalizedException
     * @throws \Exception
     * @return static
     */
    public function executeCron(Schedule $schedule): static
    {
        $instance = $this->getInstance();
        $method = $this->getMethod();

        if (empty($instance) || empty($method)) {
            throw new NoSuchEntityException(__('No callbacks found'));
        }

        // Create an instance of the specified class using the object manager because the specified class is dynamic
        $callback = [$this->objectManager->create($instance), $method];

        if (!is_callable($callback)) {
            throw new LocalizedException(__('Invalid callback: %1::%2 can\'t be called', [$instance, $method]));
        }

        $schedule->setExecutedAt($this->helper->getTime());

        try {
            ($callback)($schedule);
        } catch (LocalizedException $e) {
            throw new LocalizedException(
                __('An error occurred while executing %1::%2. Error: %3', [$instance, $method, $e->getMessage()]),
                $e
            );
        } catch (\Exception $e) {
            $this->logger->critical($e);
            throw new \Exception(
                sprintf('An error occurred while executing %s::%s. Error: %s', $instance, $method, $e->getMessage())
            );
        } finally {
            $schedule->setFinishedAt($this->helper->getTime());
        }

        return $this;
    }

    /**
     * Get the instance class name.
     *
     * @return string
     */
    public function getInstance(): string
    {
        return $this->getData(CronInterface::INSTANCE) ?? '';
    }

    /**
     * Get the method name.
     *
     * @return string
     */
    public function getMethod(): string
    {
        return $this->getData(CronInterface::METHOD) ?? '';
    }

    /**
     * Set the instance class name.
     *
     * @param string $instance
     * @return void
     */
    public function setInstance(string $instance): void
    {
        $this->setData(CronInterface::INSTANCE, $instance);
    }

    /**
     * Set the method name.
     *
     * @param string $method
     * @return void
     */
    public function setMethod(string $method): void
    {
        $this->setData(CronInterface::METHOD, $method);
    }
}
