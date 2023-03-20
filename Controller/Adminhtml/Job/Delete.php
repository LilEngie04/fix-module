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

use KiwiCommerce\CronScheduler\Helper\Cronjob;
use KiwiCommerce\CronScheduler\Model\Job;
use KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class Delete
 * @package KiwiCommerce\CronScheduler\Controller\Adminhtml\Job
 */
class Delete extends Action
{
    public TypeListInterface $cacheTypeList;

    protected string $aclResource = "job_deletejob";

    public CollectionFactory $scheduleCollectionFactory;

    public Job $jobModel;

    public Cronjob $jobHelper;

    /**
     * Class constructor
     */
    public function __construct(
        Context $context,
        TypeListInterface $cacheTypeList,
        CollectionFactory $scheduleCollectionFactory,
        Cronjob $jobHelper,
        Job $jobModel
    ) {
        $this->cacheTypeList = $cacheTypeList;
        $this->jobHelper = $jobHelper;
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->jobModel = $jobModel;
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
        $jobcode = $this->getRequest()->getParam('job_code');
        $group = $this->getRequest()->getParam('group');

        if (!empty($jobcode) && !empty($group)) {
            if ($this->jobHelper->isXMLJobcode($jobcode, $group)) {
                $this->messageManager->addErrorMessage(__('The cron job can not be deleted.'));
            } else {
                $collection = $this->scheduleCollectionFactory->create();
                $collection->addFieldToFilter('job_code', $jobcode);

                foreach ($collection as $job) {
                    $job->delete();
                }

                $this->jobModel->deleteJob($group, $jobcode);

                $this->cacheTypeList->cleanType('config');
                $this->messageManager->addSuccessMessage(__('A total of 1 record(s) have been deleted.'));
            }
        }

        return $this->_redirect('*/*/listing');
    }
}
