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

namespace KiwiCommerce\CronScheduler\Model;

use Magento\Framework\App\Config\ConfigResource\ConfigInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * Class Job
 * @package KiwiCommerce\CronScheduler\Model
 */
class Job extends AbstractModel
{
    public string $cronExprTemplate = 'crontab/{$group}/jobs/{$jobcode}/schedule/cron_expr';

    public string $cronModelTemplate = 'crontab/{$group}/jobs/{$jobcode}/run/model';

    public string $cronStatusTemplate = 'crontab/{$group}/jobs/{$jobcode}/is_active';

    public string $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT;

    public ConfigInterface $configInterface;

    /**
     * class constructor
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ConfigInterface $configInterface,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->configInterface = $configInterface;
    }


    /**
     * Save cron job for given data and expression
     */
    public function saveJob($data, $cronExpr, $jobCode)
    {
        #Cron Expression
        $vars = [
            '{$group}' => $data['group'],
            '{$jobcode}' => $jobCode
        ];

        $cronExprString   = strtr($this->cronExprTemplate, $vars);
        $cronModelString  = strtr($this->cronModelTemplate, $vars);
        $cronStatusString = strtr($this->cronStatusTemplate, $vars);
        $cronModelValue   = $data['instance'] . "::" . $data['method'];
        $cronStatusValue  = $data['is_active'];

        $this->configInterface
            ->saveConfig($cronExprString, $cronExpr, $this->scope, 0);
        $this->configInterface
            ->saveConfig($cronModelString, $cronModelValue, $this->scope, 0);
        $this->configInterface
            ->saveConfig($cronStatusString, $cronStatusValue, $this->scope, 0);
    }

    /**
     * Delete the job
     */
    public function deleteJob($group, $jobCode)
    {
        $vars = [
            '{$group}' => $group,
            '{$jobcode}' => $jobCode
        ];
        $cronExprString = strtr($this->cronExprTemplate, $vars);
        $cronModelString = strtr($this->cronModelTemplate, $vars);
        $cronStatusString = strtr($this->cronStatusTemplate, $vars);

        $this->configInterface
            ->deleteConfig($cronExprString, $this->scope, 0);
        $this->configInterface
            ->deleteConfig($cronModelString, $this->scope, 0);
        $this->configInterface
            ->deleteConfig($cronStatusString, $this->scope, 0);
    }

    /**
     * Change job Status
     */
    public function changeJobStatus($jobData, $status)
    {
        $vars = [
            '{$group}' => $jobData['group'],
            '{$jobcode}' => $jobData['code']
        ];
        $cronStatusString = strtr($this->cronStatusTemplate, $vars);
        $cronStatusValue  = $status;

        $this->configInterface
            ->saveConfig($cronStatusString, $cronStatusValue, $this->scope, 0);
    }
}
