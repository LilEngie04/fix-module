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

namespace KiwiCommerce\CronScheduler\Controller\Adminhtml\Schedule;

use Exception;
use KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;

/**
 * Class MassDelete
 * @package KiwiCommerce\CronScheduler\Controller\Adminhtml\Schedule
 */
class MassDelete extends Action
{
    public CollectionFactory|null $scheduleCollectionFactory = null;

    protected Filter $filter;

    protected string $aclResource = "schedule_massdelete";

    /**
     * Class constructor.
     */
    public function __construct(
        Context $context,
        CollectionFactory $scheduleCollectionFactory,
        Filter $filter
    ) {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->filter = $filter;
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
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->scheduleCollectionFactory->create());
            $collectionSize = $collection->getSize();

            foreach ($collection as $job) {
                $job->delete();
            }

            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $collectionSize));
            return $this->_redirect('*/*/listing');
        } catch (Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            return $this->_redirect('*/*/listing');
        }
    }
}
