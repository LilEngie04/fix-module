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

namespace KiwiCommerce\CronScheduler\Setup;

use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\Setup\UninstallInterface;

/**
 * Class Uninstall
 * @package KiwiCommerce\CronScheduler\Setup
 */
class Uninstall implements UninstallInterface
{
    /**
     * Module uninstall code
     */
    public function uninstall(
        SchemaSetupInterface $setup,
        ModuleContextInterface $context
    ) {
        $uninstaller = $setup;
        $uninstaller->startSetup();

        $uninstaller->getConnection()->dropColumn($uninstaller->getTable('cron_schedule'), 'pid', null)
        ->getConnection()->dropColumn($uninstaller->getTable('cron_schedule'), 'memory_usage', null)
        ->getConnection()->dropColumn($uninstaller->getTable('cron_schedule'), 'cpu_usage', null)
        ->getConnection()->dropColumn($uninstaller->getTable('cron_schedule'), 'system_usage', null)
        ->getConnection()->dropColumn($uninstaller->getTable('cron_schedule'), 'is_mail_sent', null);

        $uninstaller->endSetup();
    }
}
