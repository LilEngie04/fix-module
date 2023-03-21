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

use Exception;
use KiwiCommerce\CronScheduler\Helper\Cronjob;
use KiwiCommerce\CronScheduler\Helper\Schedule;
use KiwiCommerce\CronScheduler\Model\Job;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class MassStatusEnable
 * @package KiwiCommerce\CronScheduler\Controller\Adminhtml\Job
 */
class MassStatusEnable extends Action
{
    public Schedule $jobHelper;

    public Job $jobModel;

    public TypeListInterface $cacheTypeList;

    protected string $aclResource = "job_massstatuschange";

    /**
     * Cron job disable status
     */
    const CRON_JOB_ENABLE_STATUS = 1;

    /**
     * Class constructor.
     */
    public function __construct(
        Context $context,
        Job $jobModel,
        TypeListInterface $cacheTypeList,
        Cronjob $jobHelper
    ) {
        $this->jobHelper = $jobHelper;
        $this->jobModel = $jobModel;
        $this->cacheTypeList = $cacheTypeList;
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
        $data = $this->getRequest()->getPostValue();

        if (isset($data['selected'])) {
            $jobCodes = $data['selected'];
        } elseif (!isset($data['selected']) && isset($data['excluded'])) {
            $filters = $data['filters'];
            unset($filters['placeholder']);
            $jobCodes = $this->jobHelper->getAllFilterJobCodes($filters);
        }

        if (empty($jobCodes)) {
            $this->messageManager->addErrorMessage(__('Selected jobs can not be enabled.'));
            return $this->_redirect('*/*/listing');
        }

        try {
            foreach ($jobCodes as $jobCode) {
                $data = $this->jobHelper->getJobDetail($jobCode);
                $this->jobModel->changeJobStatus($data, self::CRON_JOB_ENABLE_STATUS);
            }
            $this->cacheTypeList->cleanType('config');
            $this->messageManager->addSuccessMessage(__('You enabled selected jobs.'));
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('*/*/listing');
        }
        return $this->_redirect('*/*/listing');
    }
}
