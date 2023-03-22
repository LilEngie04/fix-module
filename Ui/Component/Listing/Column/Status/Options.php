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

namespace KiwiCommerce\CronScheduler\Ui\Component\Listing\Column\Status;

use KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * @package KiwiCommerce\CronScheduler\Ui\Component\Listing\Column\Status
 */
class Options implements OptionSourceInterface
{
    public array $options;

    public CollectionFactory $scheduleCollectionFactory;

    /**
     * Options constructor.
     */
    public function __construct(
        CollectionFactory $scheduleCollectionFactory
    ) {

        $this->scheduleCollectionFactory = $scheduleCollectionFactory->create();
    }

    /**
     * Get all options available
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $this->options = [];
            $scheduleTaskStatuses = $this->scheduleCollectionFactory->getScheduleTaskStatuses();

            foreach ($scheduleTaskStatuses as $scheduleTaskStatus) {
                $status = $scheduleTaskStatus->getStatus();
                $this->options[] = [
                    "label" => __(strtoupper($status)),
                    "value" => $status
                ];
            }
        }

        return $this->options;
    }
}
