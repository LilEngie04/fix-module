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

use KiwiCommerce\CronScheduler\Helper\Cronjob;
use Magento\Cron\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\RequestInterface;

/**
 * Class CronJobDataProvider
 * @package KiwiCommerce\CronScheduler\Ui\DataProvider\Form
 */
class CronJobDataProvider extends AbstractDataProvider
{
    public array $loadedData;

    public Cronjob $jobHelper;

    public RequestInterface $request;
    public ScopeConfigInterface $scopeConfig;

    /**
     * Class constructor.
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Cronjob $jobHelper,
        ScopeConfigInterface $scopeConfig,
        RequestInterface $request,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->jobHelper = $jobHelper;
        $this->request = $request;
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * {@inheritdoc}
     */
    public function getData(): array
    {
        if ($this->loadedData) {
            return $this->loadedData;
        }

        $this->loadedData = $this->jobHelper->getJobData();
        $jobCode = $this->request->getParam('job_code');
        if (!empty($jobCode)) {
            if (isset($this->loadedData[$jobCode])) {
                if (isset($this->loadedData[$jobCode]['config_path']) && $configPath = $this->loadedData[$jobCode]['config_path']) {
                    $this->loadedData[$jobCode]['schedule'] = $this->scopeConfig->getValue($configPath);
                }

                $this->loadedData[$jobCode]['oldexpressionvalue'] = $this->loadedData[$jobCode]['schedule'];
            }
        }

        return $this->loadedData;
    }
}
