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

namespace KiwiCommerce\CronScheduler\Controller\Adminhtml\Job;

use KiwiCommerce\CronScheduler\Helper\Schedule;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Listing
 * @package KiwiCommerce\CronScheduler\Controller\Adminhtml\Job
 */
class Listing extends Action
{
    public Schedule|null $scheduleHelper = null;

    protected string $aclResource = "job_listing";

    /**
     * Class constructor.
     */
    public function __construct(
        Context $context,
        Schedule $scheduleHelper
    ) {
        $this->scheduleHelper = $scheduleHelper;
        parent::__construct($context);
    }

    /**
     * Is action allowed?
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('KiwiCommerce_CronScheduler::'.$this->aclResource);
    }

    /**
     * Execute action
     */

    public function execute(): ResponseInterface|ResultInterface
    {
        $this->scheduleHelper->getLastCronStatusMessage();
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu("Magento_Backend::system")
        ->getConfig()->getTitle()->prepend(__('Cron Jobs'))
        ->addBreadcrumb(__('Cron Scheduler'), __('Cron Scheduler'));
        return $resultPage;
    }
}
