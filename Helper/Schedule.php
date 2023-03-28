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

namespace KiwiCommerce\CronScheduler\Helper;

use DateTime;
use KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Message\ManagerInterface;

/**
 * Class Schedule
 * @package KiwiCommerce\CronScheduler\Helper
 */
class Schedule extends AbstractHelper
{
    public CollectionFactory|null $scheduleCollectionFactory = null;

    public ManagerInterface|null $messageManager = null;

    public ProductMetadata|null $productMetaData = null;

    public DateTime|null $datetime = null;


    /**
     * Class constructor.
     */
    public function __construct(
        Context $context,
        CollectionFactory $scheduleCollectionFactory,
        ManagerInterface $messageManager,
        ProductMetadata $productMetaData,
        DateTime $datetime
    ) {
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->messageManager = $messageManager;
        $this->productMetaData = $productMetaData;
        $this->datetime = $datetime;

        parent::__construct($context);
    }

    /**
     * Store pid in cron table
     */
    public function setPid(&$schedule)
    {
        if (function_exists('getmypid')) {
            $schedule->setPid(getmypid());
        }
    }

    /**
     * Calculate actual CPU usage in time ms
     */
    public function setCpuUsage($ru, $rus, &$schedule)
    {
        $cpuData = $this->rutime($ru, $rus, 'utime');
        $systemData = $this->rutime($ru, $rus, 'stime');
        $schedule->setCpuUsage($cpuData);
        $schedule->setSystemUsage($systemData);
    }

    /**
     * Get Usage
     */
    private function rutime($ru, $rus, $index): float|int
    {
        return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
            -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
    }

    /**
     * Save Memory usage.Convert bytes to megabytes.
     * @param $schedule
     */
    public function setMemoryUsage(&$schedule)
    {
        $memory = (memory_get_peak_usage(false)/1024/1024);

        $schedule->setMemoryUsage($memory);
    }

    /**
     * Generates filtered time input from user to formatted time (YYYY-MM-DD)
     */
    public function filterTimeInput($time): string
    {
        $matches = [];
        preg_match('/(\d+-\d+-\d+)T(\d+:\d+)/', $time, $matches);
        $time = $matches[1] . " " . $matches[2];
        return DateTime::createFromFormat('Y-m-d H:M:00', date($time));
    }

    /**
     * Set last cron status message.
     *
     */
    public function getLastCronStatusMessage()
    {
        $magentoVersion = $this->getMagentoversion();
        $currentTime = new DateTime();
        $lastCronStatus = $this->scheduleCollectionFactory->create()->getLastCronStatus();
        if (!empty($lastCronStatus)) {
            $lastCronStatusTime = DateTime::createFromFormat('Y-m-d H:i:s', $lastCronStatus);
            $diff = $currentTime->diff($lastCronStatusTime)->i;
            if ($diff > 5) {
                if ($diff >= 60) {
                    $diff = intdiv($diff, 60);
                    $this->messageManager->addErrorMessage(__("Last cron execution is older than %1 hour%2", $diff, ($diff > 1) ? 's' : ''));
                } else {
                    $this->messageManager->addErrorMessage(__("Last cron execution is older than %1 minute%2", $diff, ($diff > 1) ? 's' : ''));
                }
            }
        }
    }

    /**
     * Get Latest magento Version
     */
    public function getMagentoversion(): string
    {
        $explodedVersion = explode("-", $this->productMetaData->getVersion());
        $magentoversion = $explodedVersion[0];

        return $magentoversion;
    }
}
