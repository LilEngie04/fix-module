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

use DateTimeZone;
use Exception;
use KiwiCommerce\CronScheduler\Helper\Cronjob;
use KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cron\Model\Schedule;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class MassScheduleNow
 * @package KiwiCommerce\CronScheduler\Controller\Adminhtml\Job
 */
class MassScheduleNow extends Action
{
    public CollectionFactory|null $scheduleCollectionFactory = null;

    public TimezoneInterface $timezone;

    public DateTime $dateTime;

    public Schedule|null $scheduleHelper = null;

    public Cronjob|null $jobHelper = null;

    protected string $aclResource = "job_massschedule";

    /**
     * Class constructor.
     */
    public function __construct(
        Context           $context,
        CollectionFactory $scheduleCollectionFactory,
        TimezoneInterface $timezone,
        DateTime          $dateTime,
        Schedule          $scheduleHelper,
        Cronjob           $jobHelper
    )
    {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->timezone = $timezone;
        $this->dateTime = $dateTime;
        $this->scheduleHelper = $scheduleHelper;
        $this->jobHelper = $jobHelper;

        parent::__construct($context);
    }

    /**
     * Is action allowed?
     */
    protected function _isAllowed(): bool
    {
        return $this->_authorization->isAllowed('KiwiCommerce_CronScheduler::' . $this->aclResource);
    }

    /**
     * Execute action
     */

    public function execute(): ResponseInterface|ResultInterface
    {
        $data = $this->getRequest()->getPostValue();

        if (isset($data['selected'])) {
            $jobCodes = $data['selected'];
        } elseif (!isset($data['selected']) && isset($data['excluded'])) {
            $filters = $data['filters'];
            unset($filters['placeholder']);
            $jobCodes = $this->jobHelper->getAllFilterJobCodes($filters);
        }

        if (empty($jobCodes)) {
            $this->messageManager->addErrorMessage(__('Selected jobs can not be scheduled now.'));
            return $this->redirect('*/*/listing');
        }

        try {
            foreach ($jobCodes as $jobCode) {
                $job_status = $this->jobHelper->isJobActive($jobCode);
                if ($job_status) {
                    $collection = $this->scheduleCollectionFactory->create()->getNewEmptyItem();

                    $magentoVersion = $this->scheduleHelper->getMagentoversion();
                    $createdAt = $this->timezone->scopeTimestampToDateTime()->format('Y-m-d H:i:s');
                    $scheduleAt = $this->scheduleHelper->filterTimeInput($this->timezone->scopeTimestampToDateTime());

                    if (version_compare($magentoVersion, "2.2.0") >= 0) {
                        $scheduleAt = $scheduleAt->format('Y-m-d\TH:i:s');
                    } else {
                        $createdAt = $createdAt->format('Y-m-d H:i:s');
                        $scheduleAt = $scheduleAt->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d\TH:i:s');
                    }

                    $collection->setData('job_code', $jobCode);
                    $collection->setData('status', Schedule::STATUS_PENDING);
                    $collection->setData('created_at', $createdAt);
                    $collection->setData('scheduled_at', $scheduleAt);
                    $collection->save();
                    $success[] = $jobCode;
                }
            }
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->redirect('*/*/listing');
        }

        if (isset($success) && !empty($success)) {
            $this->messageManager->addSuccessMessage(__('You scheduled selected jobs now.'));
        }

        return $this->redirect('*/*/listing');
    }
}
