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

namespace KiwiCommerce\CronScheduler\Block\Adminhtml\Dashboard;

use KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\Collection;
use KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Cron\Model\Schedule;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Cronjobs
 * @package KiwiCommerce\CronScheduler\Block\Adminhtml\Dashboard
 */
class Cronjobs extends Template
{
    public CollectionFactory $scheduleCollectionFactory;

    /**
     * Dashboard enable/disable status
     */
    const XML_PATH_DASHBOARD_ENABLE_STATUS = 'cronscheduler/general/cronscheduler_dashboard_enabled';

    /**
     * Display total records on dashboard
     */
    const TOTAL_RECORDS_ON_DASHBOARD = 5;

    public function __construct(
        Context $context,
        CollectionFactory $scheduleCollectionFactory
    ) {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        parent::__construct($context);
    }

    public function getTopRunningJobs()
    {
        $collection = $this->scheduleCollectionFactory->create();

        $collection->addFieldToFilter('status', Schedule::STATUS_SUCCESS)
            ->addExpressionFieldToSelect(
                'timediff',
                fn($field) => 'TIME_TO_SEC(TIMEDIFF(`finished_at`, `executed_at`))',
                []
            )
            ->setOrder('timediff', 'DESC')
            ->setPageSize(self::TOTAL_RECORDS_ON_DASHBOARD)
            ->load();

        return $collection;
    }

    public function isDashboardActive(): bool
    {
        $dashboardEnableStatus = $this->_scopeConfig->getValue(self::XML_PATH_DASHBOARD_ENABLE_STATUS, ScopeInterface::SCOPE_STORE);

        return (bool) $dashboardEnableStatus;
    }
}
