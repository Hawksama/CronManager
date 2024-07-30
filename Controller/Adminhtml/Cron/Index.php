<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hawksama\CronManager\Controller\Adminhtml\Cron;

use Magento\Framework\Controller\ResultInterface;
use Hawksama\CronManager\Controller\Adminhtml\AbstractController;

/**
 * This class represents the index controller for the adminhtml cron manager module.
 */
class Index extends AbstractController
{
    /**
     * Execute
     *
     * @return ResultInterface
     */
    public function execute(): ResultInterface
    {
        $resultPage = $this->initializePage();
        $resultPage->getConfig()->getTitle()->prepend(__('Cron Manager'));
        return $resultPage;
    }
}
