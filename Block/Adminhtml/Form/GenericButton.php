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

namespace KiwiCommerce\CronScheduler\Block\Adminhtml\Form;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Framework\UrlInterface;

/**
 * Class GenericButton
 * @package KiwiCommerce\CronScheduler\Block\Adminhtml\Form
 */
class GenericButton
{

    /**
     * Constructor
     */
    public function __construct(
        protected UrlInterface $urlBuilder,
        protected Registry $registry
    ) {
    }

    /**
     * Return the synonyms group Id.
     */
    public function getId(): int
    {
        $contact = $this->registry->registry('contact');
        return $contact ?->getId();
    }

    /**
     * Generate url by route and parameters
     */
    public function getUrl(string $route = '', array $params = []): string
    {
        return $this->urlBuilder->getUrl($route, $params);
    }
}
