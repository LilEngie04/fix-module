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

use KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Tests\NamingConvention\true\string;

/**
 * Class Schedule
 * @package KiwiCommerce\CronScheduler\Helper
 */
class Schedule extends AbstractHelper
{
    /**
     * @var CollectionFactory|null
     */
    public $scheduleCollectionFactory = null;

    /**
     * @var ManagerInterface|null
     */
    public $messageManager = null;

    /**
     * @var ProductMetadata|null
     */
    public $productMetaData = null;

    /**
     * @var DateTime|null
     */
    public $datetime = null;

    public DateTime::format(string $format): string;

    /**
     * Class constructor.
     * @param Context $context
     * @param CollectionFactory $scheduleCollectionFactory
     * @param ManagerInterface $messageManager
     * @param ProductMetadata $productMetaData
     * @param DateTime $datetime
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
     *
     * @param $schedule
     */
    public function setPid(&$schedule): void
    {
        if (function_exists('getmypid')) {
            $schedule->setPid(getmypid());
        }
    }

    /**
     * Calculate actual CPU usage in time ms
     * @param $ru
     * @param $rus
     * @param $schedule
     */
    public function setCpuUsage(array $ru, array $rus, mixed &$schedule): void
    {
        $cpuData = $this->rutime($ru, $rus, 'utime');
        $systemData = $this->rutime($ru, $rus, 'stime');
        $schedule->setCpuUsage($cpuData);
        $schedule->setSystemUsage($systemData);
    }

    /**
     * Get Usage
     *
     * @param $ru
     * @param $rus
     * @param $index
     * @return string|int
     */

    private function rutime(array $ru, array $rus, string $index): int
    {
        return ($ru["ru_$index.tv_sec"]*1000 + intval($ru["ru_$index.tv_usec"]/1000))
            -  ($rus["ru_$index.tv_sec"]*1000 + intval($rus["ru_$index.tv_usec"]/1000));
    }

    /**
     * Save Memory usage.Convert bytes to megabytes.
     * @param $schedule
     */
    public function setMemoryUsage(&$schedule): void
    {
        $memory = (memory_get_peak_usage(false)/1024/1024);

        $schedule->setMemoryUsage($memory);
    }

    /**
     * Generates filtered time input from user to formatted time (YYYY-MM-DD)
     *
     * @param mixed $time
     * @return string
     */

    /*public function filterTimeInput(string $time): string
    {
        preg_match('/(\d+-\d+-\d+)T(\d+:\d+)/', $time, $matches);
        $time = $matches[1] . " " . $matches[2];
        return date_create_from_format('Y-m-d H:i', $time)->format('Y-m-d H:i:00');
    }*/

    public function filterTimeInput($time): string
    {
        $matches = [];
        preg_match('/(\d+-\d+-\d+)T(\d+:\d+)/', $time, $matches);
        $time = $matches[1] . " " . $matches[2];
        return date('%Y-%m-%d %H:%M:00', date($time));
    }

    /**
     * Set last cron status message.
     *
     */

    /*public function getLastCronStatusMessage(): void
    {
        $magentoVersion = $this->getMagentoversion();
        $currentTime = new DateTime();
        if (version_compare($magentoVersion, "2.2.0") >= 0) {
            $currentTime = $this->datetime->date('U');
        } else {
            $currentTime->modify('+' . $this->datetime->getGmtOffset('hours') . ' hours');
            //$currentTime = (int)$this->datetime->date('U') + $this->datetime->getGmtOffset('hours') * 60 * 60;
        }
        $lastCronStatus = strtotime($this->scheduleCollectionFactory->create()->getLastCronStatus());
        //$lastCronStatus = $this->scheduleCollectionFactory->create()->getLastCronStatus();
        if (!empty($lastCronStatus)) {
            $lastCronStatusTime = strtotime($lastCronStatus ?? 'now');
            $diff = floor(($currentTime - $lastCronStatusTime) / 60);
            if ($diff > 5) {
                if ($diff >= 60) {
                    $diff = intdiv($diff, 60);
                    $this->messageManager->addErrorMessage(__("Last cron execution is older than %1 hour%2", $diff, ($diff > 1) ? "s" : ""));
                } else {
                    $this->messageManager->addErrorMessage(__("Last cron execution is older than %1 minute%2", $diff, ($diff > 1) ? "s" : ""));
                }
            }
            else {
                $this->messageManager->addSuccessMessage(__("Last cron execution was %1 minute%2 ago", $diff, ($diff > 1) ? "s" : ""));
            }
        } else {
            $this->messageManager->addErrorMessage(__("No cron execution found"));
        }
    }*/

    public function getLastCronStatusMessage()
    {
        $magentoVersion = $this->getMagentoversion();
        if (version_compare($magentoVersion, "2.2.0") >= 0) {
            $currentTime = $this->datetime->date('U');
        } else {
            $currentTime = (int)$this->datetime->date('U') + $this->datetime->getGmtOffset('hours') * 60 * 60;
        }
        $lastCronStatus = DateTime::format($this->scheduleCollectionFactory->create()->getLastCronStatus());
        if ($lastCronStatus != null) {
            $diff = intdiv(($currentTime - $lastCronStatus), 60);
            if ($diff > 5) {
                if ($diff >= 60) {
                    $diff = intdiv($diff, 60);
                    $this->messageManager->addErrorMessage(__("Last cron execution is older than %1 hour%2", $diff, ($diff > 1) ? "s" : ""));
                } else {
                    $this->messageManager->addErrorMessage(__("Last cron execution is older than %1 minute%2", $diff, ($diff > 1) ? "s" : ""));
                }
            } else {
                $this->messageManager->addSuccessMessage(__("Last cron execution was %1 minute%2 ago", $diff, ($diff > 1) ? "s" : ""));
            }
        } else {
            $this->messageManager->addErrorMessage(__("No cron execution found"));
        }
    }



    /**
     * Get Latest magento Version
     * @return mixed
     */

    public function getMagentoversion(): string
    {
        $explodedVersion = explode("-", $this->productMetaData->getVersion());
        $magentoversion = $explodedVersion[0];

        return $magentoversion;
    }
}
