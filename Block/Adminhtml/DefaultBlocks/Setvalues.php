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
namespace KiwiCommerce\CronScheduler\Block\Adminhtml\DefaultBlocks;

use Exception;
use Magento\Framework\App\DeploymentConfig\Reader;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

/**
 * Class Setvalues
 * @package KiwiCommerce\CronScheduler\Block\Adminhtml\DefaultBlocks
 */
class Setvalues extends Template
{
    public Reader $configReader;

    public function __construct(
        Context $context,
        Reader $configReader
    ) {
        $this->configReader = $configReader;
        parent::__construct($context);
    }

    public function getAdminBaseUrl(): string
    {
        $config = $this->configReader->load();
        return $this->getBaseUrl() . ($config['backend']['frontName'] ?? '') . '/';
    }
}
