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

namespace KiwiCommerce\CronScheduler\Block\Adminhtml\Schedule;

use Exception;
use KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Magento\Cron\Model\Schedule;
use Magento\Framework\App\ProductMetadata;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

/**
 * Class Timeline
 * @package KiwiCommerce\CronScheduler\Block\Adminhtml\Schedule
 */
class Timeline extends Template
{
    public DateTime|null $datetime = null;

    public Schedule|null $scheduleHelper = null;

    public CollectionFactory|null $collectionFactory = null;

    public TimezoneInterface $timezone;
    private ProductMetadata $productMetaData;

    public function __construct(
        Context $context,
        DateTime $datetime,
        Schedule $scheduleHelper,
        CollectionFactory $collectionFactory,
        ProductMetadata $productMetaData,
        TimezoneInterface $timezone,
        array $data = []
    ) {
        $this->datetime = $datetime;
        $this->scheduleHelper = $scheduleHelper;
        $this->collectionFactory = $collectionFactory;
        $this->productMetaData = $productMetaData; // todo need to this un-used variable
        parent::__construct($context, $data);
        $this->timezone = $timezone;
    }

    /**
     * Get the data to construct the timeline
     */
    public function getCronJobData(): array
    {
        $data = [];
        $schedules = $this->collectionFactory->create();
        $schedules->getSelect()->order('job_code');

        foreach ($schedules as $schedule) {
            $start = $this->timezone->date($schedule->getData('executed_at'))->format('Y-m-d H:i:s');
            $end = $this->timezone->date($schedule->getData('finished_at'))->format('Y-m-d H:i:s');
            $status = $schedule->getStatus();

            if ($start == null) {
                $start = $end = $schedule->getData('scheduled_at');
            }

            if ($status == Schedule::STATUS_RUNNING) {
                $end = $this->timezone->date()->format('Y-m-d H:i:s');
            }

            if ($status == Schedule::STATUS_ERROR && $end == null) {
                $end = $start;
            }
            $level   = $this->getStatusLevel($status);
            $tooltip = $this->getToolTip($schedule, $level, $status, $start, $end);

            $data[] = [
                $schedule->getJobCode(),
                $status,
                $tooltip,
                $this->getNewDateForJs($start),
                $this->getNewDateForJs($end),
                $schedule->getScheduleId()
            ];
        }

        return $data;
    }

    /**
     * Generate js date format for given date
     */
    private function getNewDateForJs($date): string
    {
        return "new Date(" . $this->datetime->date('Y,', $date) . ($this->datetime->date('m', $date) - 1) . $this->datetime->date(',d,H,i,s,0', $date) . ")";
    }

    /**
     * Get Status Level
     */
    private function getStatusLevel($status): string
    {
        switch ($status) {
            case Schedule::STATUS_ERROR:
            case Schedule::STATUS_MISSED:
                $level = 'major';
                break;
            case Schedule::STATUS_RUNNING:
                $level = 'running';
                break;
            case Schedule::STATUS_PENDING:
                $level = 'minor';
                break;
            case Schedule::STATUS_SUCCESS:
                $level = 'notice';
                break;
            default:
                $level = 'critical';
        }

        return $level;
    }

    /**
     * Get tooltip text for each cron job
     */
    private function getToolTip($schedule, $level, $status, $start, $end): string
    {
        $tooltip = "<table class=>"
            . "<tr><td colspan='2'>"
            . $schedule->getJobCode()
            . "</td></tr>"
            . "<tr><td>"
            . "Id"
            . "</td><td>"
            . $schedule->getId() . "</td></tr>"
            . "<tr><td>"
            . "Status"
            . "</td><td>"
            . "<span class='grid-severity-" . $level . "'>" . $status . "</span>"
            . "</td></tr>"
            . "<tr><td>"
            . "Created at"
            . "</td><td>"
            . $this->timezone->date($schedule->getData('created_at'))->format('Y-m-d H:i:s')
            . "</td></tr>"
            . "<tr><td>"
            . "Scheduled at"
            . "</td><td>"
            . $this->timezone->date($schedule->getData('scheduled_at'))->format('Y-m-d H:i:s')
            . "</td></tr>"
            . "<tr><td>"
            . "Executed at"
            . "</td><td>"
            . ($start != null ? $start : "")
            . "</td></tr>"
            . "<tr><td>"
            . "Finished at"
            . "</td><td>"
            . ($end != null ? $end : "")
            . "</td></tr>";

        if ($status== "success") {
            $timeFirst  = strtotime($start);
            $timeSecond = strtotime($end);
            $differenceInSeconds = $timeSecond - $timeFirst;

            $tooltip .= "<tr><td>"
                . "CPU Usage"
                . "</td><td>"
                . $schedule->getCpuUsage()
                . "</td></tr>"
                . "<tr><td>"
                . "System Usage"
                . "</td><td>"
                . $schedule->getSystemUsage()
                . "</td></tr>"
                . "<tr><td>"
                . "Memory Usage"
                . "</td><td>"
                . $schedule->getMemoryUsage()
                . "</td></tr>"
                . "<tr><td>"
                . "Total Executed Time"
                . "</td><td>"
                . $differenceInSeconds
                . "</td></tr>";
        }
        $tooltip .= "</table>";

        return $tooltip;
    }

    /**
     * Get the current date for javascript
     */
    public function getDateWithJs(): string
    {
        $current = $this->datetime->date('U') + $this->datetime->getGmtOffSet('seconds');
        return "new Date(" . $this->datetime->date("Y,", $current) . ($this->datetime->date("m", $current) - 1) . $this->datetime->date(",d,H,i,s", $current) . ")";
    }
}
