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

namespace KiwiCommerce\CronScheduler\Ui\Component\Listing\Column\Code;

use Magento\Cron\Model\ConfigInterface;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * @package KiwiCommerce\CronScheduler\Ui\Component\Listing\Column\Code
 */
class Options implements OptionSourceInterface
{
    public array $options;

    public ConfigInterface $cronConfig;

    /**
     * Options constructor.
     */
    public function __construct(
        ConfigInterface $cronConfig
    ) {
        $this->cronConfig = $cronConfig;
    }

    /**
     * Get all options available
     */
    public function toOptionArray(): array
    {
        $options = [];

        if ($this->options === null) {
            $configJobs = $this->cronConfig->getJobs();
            foreach (array_values($configJobs) as $jobs) {
                foreach (array_keys($jobs) as $code) {
                    $options[] = $code;
                }
            }
        }

        sort($options);
        foreach ($options as $option) {
            $this->options[] = [
                "label" => $option, "value" => $option
            ];
        }
        return $this->options;
    }
}
