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

namespace KiwiCommerce\CronScheduler\Ui\DataProvider;

use KiwiCommerce\CronScheduler\Helper\Cronjob;
use Magento\Cron\Model\ConfigInterface;
use Magento\Framework\Api\Filter;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Directory\ReadFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Class JobProvider
 * @package KiwiCommerce\CronScheduler\Ui\DataProvider
 */
class JobProvider extends AbstractDataProvider
{
    protected int $size = 20;

    protected int $offset = 1;

    protected array $likeFilters = [];

    protected array $rangeFilters = [];

    protected string $sortField = 'code';

    protected string $sortDir = 'asc';

    private DirectoryList $directoryList;

    private ReadFactory $directoryRead;

    public Cronjob $jobHelper;

    /**
     * Class constructor
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        ReadFactory $directoryRead,
        DirectoryList $directoryList,
        Cronjob $jobHelper,
        array $meta = [],
        array $data = []
    ) {
        $this->directoryRead = $directoryRead;
        $this->directoryList = $directoryList;
        $this->jobHelper = $jobHelper;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * Set the limit of the collection
     */
    public function setLimit(
        $offset,
        $size
    ) {
        $this->size = $size;
        $this->offset = $offset;
    }

    /**
     * Get the collection
     */
    public function getData(): array
    {
        $data = array_values($this->jobHelper->getJobData());

        $totalRecords = count($data);

        #sorting
        $sortField = $this->sortField;
        $sortDir = $this->sortDir;
        usort($data, fn($a, $b) => match ($sortDir) {
            "asc" => $a[$sortField] <=> $b[$sortField],
            default => $b[$sortField] <=> $a[$sortField]
        });

        #filters
        foreach ($this->likeFilters as $column => $value) {
            $data = array_filter($data, function ($item) use ($column, $value) {
                return stripos($item[$column], $value) !== false;
            });
        }

        #pagination
        $data = array_slice($data, ($this->offset - 1) * $this->size, $this->size);

        return [
            'totalRecords' => $totalRecords,
            'items' => $data,
        ];
    }

    /**
     * Add filters to the collection
     */
    public function addFilter(Filter $filter)
    {
        if ($filter->getConditionType() == "like") {
            $this->likeFilters[$filter->getField()] = substr($filter->getValue(), 1, -1);
        } elseif ($filter->getConditionType() == "eq") {
            $this->likeFilters[$filter->getField()] = $filter->getValue();
        } elseif ($filter->getConditionType() == "gteq") {
            $this->rangeFilters[$filter->getField()]['from'] = $filter->getValue();
        } elseif ($filter->getConditionType() == "lteq") {
            $this->rangeFilters[$filter->getField()]['to'] = $filter->getValue();
        }
    }

    /**
     * Set the order of the collection
     */
    public function addOrder(
        $field,
        $direction
    ) {
        $this->sortField = $field;
        $this->sortDir = strtolower($direction);
    }
}
