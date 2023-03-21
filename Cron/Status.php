<?php
/**
 * KiwiCommerce
 *
 * Do not edit or add to this file if you wish to upgrade to newer versions in the future.
 * If you wish to customise this module for your needs.
 * Please contact us https://kiwicommerce.co.uk/contacts.
 *
 * @category   KiwiCommerce
 * @package    KiwiCommerce_CronScheduler
 * @copyright  Copyright (C) 2018 Kiwi Commerce Ltd (https://kiwicommerce.co.uk/)
 * @license    https://kiwicommerce.co.uk/magento2-extension-license/
 */

namespace KiwiCommerce\CronScheduler\Cron;

use Exception;
use Magento\Cron\Model\Schedule;

/**
 * Class Status
 * @package KiwiCommerce\CronScheduler\Cron
 */
class Status
{
    /**
     * Set cron status
     */
    public function checkstatus(Schedule $schedule = null)
    {
        $schedule->setMessages(__("Cron is Working"));
        $schedule->save();
    }
}
