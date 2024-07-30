<?php
/**
 * Copyright © Alexandru-Manuel Carabus All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Hawksama\CronManager\Model\ResourceModel;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;

/**
 * Cron ResourceModel
 */
class Cron extends AbstractDb
{
    protected function _construct(): void
    {
        $this->_init('cron_schedule', 'schedule_id');
    }
}
