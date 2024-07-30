<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hawksama\CronManager\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\Page;

/**
 * Class AbstractController
 * @package Hawksama\CronManager\Controller\Adminhtml
 */
abstract class AbstractController extends Action
{
    /**
     * @param PageFactory $pageFactory
     */
    public function __construct(
        Context $context,
        private readonly PageFactory $pageFactory
    ) {
        parent::__construct($context);
    }

    /**
     * Initializes the page by creating a new instance of the Magento framework's view result page and sets the active menu to 'Hawksama_CronManager::cronlist'.
     *
     * @return Page
     */
    protected function initializePage(): Page
    {
        $resultPage = $this->pageFactory->create();
        $resultPage->setActiveMenu('Hawksama_CronManager::cronlist');
        return $resultPage;
    }
}
