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

namespace KiwiCommerce\CronScheduler\Controller\Adminhtml\Cron;

use Exception;
use KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\Collection;
use KiwiCommerce\CronScheduler\Model\ResourceModel\Schedule\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Cron\Model\Schedule;
use Magento\Framework\App\Area;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\MailException;
use Magento\Framework\Mail\Template\SenderResolverInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Sendemail
 * @package KiwiCommerce\CronScheduler\Controller\Adminhtml\Cron
 */
class Sendemail extends Action
{
    /**
     * Recipient email config path
     */
    const XML_PATH_EMAIL_RECIPIENT = 'cronscheduler/general/cronscheduler_admin_email';

    /**
     * Recipient email enable/disable status
     */
    const XML_PATH_EMAIL_ENABLE_STATUS = 'cronscheduler/general/cronscheduler_email_enabled';

    public CollectionFactory|null $scheduleCollectionFactory = null;

    public TransportBuilder|null $transportBuilder = null;

    public StateInterface|null $inlineTranslation = null;

    public ScopeConfigInterface|null $scopeConfig = null;

    public StoreManagerInterface|null $storeManager = null;

    public SenderResolverInterface $senderResolver;

    public LoggerInterface $logger;

    public DateTime $dateTime;

    /**
     * Test Email Template Name
     */
    const TEST_EMAIL_TEMPLATE = 'cronscheduler_email_template';

    /**
     * Test Email Template Name
     */
    const IS_MAIL_STATUS = 1;

    /**
     * Class constructor.
     */
    public function __construct(
        Context $context,
        CollectionFactory $scheduleCollectionFactory,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime,
        SenderResolverInterface $senderResolver,
        StoreManagerInterface $storeManager,
        LoggerInterface $logger
    ) {
        parent::__construct($context);
        $this->scheduleCollectionFactory = $scheduleCollectionFactory;
        $this->transportBuilder = $transportBuilder;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->dateTime = $dateTime;
        $this->senderResolver = $senderResolver;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
    }

    /**
     * Execute action
     */

    /*public function execute():ResponseInterface|ResultInterface|string
    {
        $emailEnableStatus = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_ENABLE_STATUS, ScopeInterface::SCOPE_STORE);

        if ($emailEnableStatus) {
            $emailItems['errorMessages'] = $this->getFatalErrorOfJobcode();
            $emailItems['missedJobs']    = $this->getMissedCronJob();

            $receiverEmailConfig = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, ScopeInterface::SCOPE_STORE);
            $receiverEmailIds = explode(',', $receiverEmailConfig);

            if (!empty($receiverEmailIds) && (!empty($emailItems['errorMessages']->getData()) || !empty($emailItems['missedJobs']->getData()))) {
                try {
                    $from = $this->senderResolver->resolve('general');

                    $this->sendEmailStatus($receiverEmailIds, $from, $emailItems);
                    $this->updateMailStatus($emailItems);
                } catch (\Exception $e) {
                    $this->logger->critical($e);
                }
            }
        }
    }*/

    public function execute(): ResponseInterface|ResultInterface|string
    {
        $emailEnableStatus = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_ENABLE_STATUS, ScopeInterface::SCOPE_STORE);

        if ($emailEnableStatus) {
            return '';
        }

        $errorMessages = $this->getFatalErrorOfJobcode();
        $missedJobs = $this->getMissedCronJob();

        $receiverEmailConfig = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, ScopeInterface::SCOPE_STORE);
        $receiverEmailIds = explode(',', $receiverEmailConfig);

        if (empty($receiverEmailIds) || (empty($errorMessages->getData()) && empty($missedJobs->getData()))) {
            return '';
        }

        try {
            $from = $this->senderResolver->resolve('general');

            $emailItems = [
                'errorMessages' => $errorMessages,
                'missedJobs' => $missedJobs
            ];

            $this->sendEmailStatus($receiverEmailIds, $from, $emailItems);
            $this->updateMailStatus($emailItems);
        } catch (Exception $e) {
            $this->logger->critical($e);
        }

            $emailItems['errorMessages'] = $this->getFatalErrorOfJobcode();
            $emailItems['missedJobs']    = $this->getMissedCronJob();

            $receiverEmailConfig = $this->scopeConfig->getValue(self::XML_PATH_EMAIL_RECIPIENT, ScopeInterface::SCOPE_STORE);
            $receiverEmailIds = explode(',', $receiverEmailConfig);

            if (!empty($receiverEmailIds) && (!empty($emailItems['errorMessages']->getData()) || !empty($emailItems['missedJobs']->getData()))) {
                try {
                    $from = $this->senderResolver->resolve('general');

                    $this->sendEmailStatus($receiverEmailIds, $from, $emailItems);
                    $this->updateMailStatus($emailItems);
                } catch (Exception $e) {
                    $this->logger->critical($e);
                }
            }
        return '';

    }

    /**
     * Update is mail status after sending an email
     */
    private function updateMailStatus($emailItems)
    {
        if (!empty($emailItems['errorMessages'])) {
            foreach ($emailItems['errorMessages'] as $errorMessage) {
                $collection = $this->scheduleCollectionFactory->create();
                $filters = [
                    'schedule_id' => $errorMessage['max_id'],
                    'job_code' => $errorMessage['job_code'],
                    'status' => Schedule::STATUS_ERROR
                ];
                $collection->updateMailStatusByJobCode(['is_mail_sent' => self::IS_MAIL_STATUS], $filters);
            }
        }

        if (!empty($emailItems['missedJobs'])) {
            foreach ($emailItems['missedJobs'] as $missedJob) {
                $collection = $this->scheduleCollectionFactory->create();
                $filters = [
                    'schedule_id' => $missedJob['max_id'],
                    'job_code' => $missedJob['job_code'],
                    'status' => Schedule::STATUS_MISSED
                ];
                $collection->updateMailStatusByJobCode(['is_mail_sent' => self::IS_MAIL_STATUS], $filters);
            }
        }
    }

    /**
     * Get Missed cron jobs count
     */
    private function getMissedCronJob(): Collection
    {
        $collection = $this->scheduleCollectionFactory->create();
        $collection->getSelect()->where('status = "'. Schedule::STATUS_MISSED.'"')
            ->where('is_mail_sent is NULL')
            ->reset('columns')
            ->columns(['job_code', 'MAX(schedule_id) as max_id', 'COUNT(schedule_id) as totalmissed'])
            ->group(['job_code']);

        return $collection;
    }

    /**
     * Get Each Cron Job Fatal error
     */
    private function getFatalErrorOfJobcode(): Collection
    {
        $collection = $this->scheduleCollectionFactory->create();
        $collection->getSelect()->where('status = "'. Schedule::STATUS_ERROR.'"')
            ->where('error_message is not NULL')
            ->where('is_mail_sent is NULL')
            ->reset('columns')
            ->columns(['job_code', 'error_message','MAX(schedule_id) as max_id'])
            ->group(['job_code']);

        return $collection;
    }

    /**
     * Send Email
     */
    private function sendEmailStatus($to, $from, $items): self
    {
        $templateOptions = ['area' => Area::AREA_FRONTEND, 'store' => $this->storeManager->getStore()->getId()];
        $templateVars = [
            'store' => $this->storeManager->getStore(),
            'items'=> $items,
        ];

        $this->inlineTranslation->suspend();

        $transport = $this->transportBuilder
            ->setTemplateIdentifier(self::TEST_EMAIL_TEMPLATE)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($to)
            ->getTransport();

        $transport->sendMessage();

        $this->inlineTranslation->resume();
        return $this;
    }
}
