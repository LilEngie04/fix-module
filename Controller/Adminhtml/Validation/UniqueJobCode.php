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

namespace KiwiCommerce\CronScheduler\Controller\Adminhtml\Validation;

use KiwiCommerce\CronScheduler\Helper\Cronjob;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class UniqueJobCode
 * @package KiwiCommerce\CronScheduler\Controller\Adminhtml\Validation
 */
class UniqueJobCode extends Action
{
    public Cronjob|null $jobHelper = null;

    /**
     * Class constructor.
     */
    public function __construct(
        Context $context,
        Cronjob $jobHelper
    ) {
        $this->jobHelper = $jobHelper;
        parent::__construct($context);
    }

    /**
     * Execute action
     */
    public function execute(): ResponseInterface|ResultInterface
    {
        if ($this->getRequest()->isXmlHttpRequest()) {
            $data = $this->getRequest()->getPostValue();
            $jobcode = trim($data['jobcode']);

            $data = array_values($this->jobHelper->getJobData());
            $existingjobcode = array_column($data, 'code');

            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $result = false;

            if (!empty($jobcode)) {
                if (in_array($jobcode, $existingjobcode)) {
                    $result = true;
                }
            }

            return $resultJson->setData(['success' => $result]);
        }

        return $this->_redirect('*/*/listing');
    }
}
