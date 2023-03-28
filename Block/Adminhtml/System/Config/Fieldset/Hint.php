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

namespace KiwiCommerce\CronScheduler\Block\Adminhtml\System\Config\Fieldset;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Data\Form\Element\Renderer\RendererInterface;
use Magento\Framework\Module\ModuleList;

/**
 * Class Hint
 * @package KiwiCommerce\CronScheduler\Block\Adminhtml\System\Config\Fieldset
 */
class Hint extends Template implements RendererInterface
{
    private ModuleList $moduleList;

    /**
     * Class constructor.
     */
    public function __construct(
        Context $context,
        ModuleList $moduleList,
        array $data = []
    ) {
        $this->_template = 'KiwiCommerce_CronScheduler::system/config/fieldset/hint.phtml';
        parent::__construct($context, $data);
        $this->moduleList = $moduleList;
    }

    public function render(AbstractElement $element): string
    {
        $_element = $element;
        return $this->toHtml();
    }

    public function getModuleVersion(): string|null
    {
        return $this->moduleList->getOne('KiwiCommerce_CronScheduler')['setup_version'];
    }
}
