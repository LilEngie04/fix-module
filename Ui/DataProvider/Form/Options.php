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

namespace KiwiCommerce\CronScheduler\Ui\DataProvider\Form;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * @package KiwiCommerce\CronScheduler\Ui\DataProvider\Form
 */
class Options implements OptionSourceInterface
{
    public array $options;

    /**
     * Get all options available
     */
    public function toOptionArray(): array
    {
        if ($this->options === null) {
            $this->options = [
                ["label" => __('Enable'),  "value" => 1],
                ["label" => __('Disable'), "value" => 0]
            ];
        }
        return $this->options;
    }
}
