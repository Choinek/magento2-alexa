<?php

namespace Global4Net\AlexaReport\App;

use AlanKent\Alexa\App\AlexaApplicationAbstract;
use AlanKent\Alexa\App\AlexaApplicationInterface;
use AlanKent\Alexa\App\CustomerDataInterface;
use AlanKent\Alexa\App\ResponseData;
use AlanKent\Alexa\App\ResponseDataFactory;
use AlanKent\Alexa\App\SessionDataInterface;
use Magento\Customer\Model\ResourceModel\Visitor\CollectionFactory;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Customer\Model\Visitor;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;


/**
 * Sample application to provide various customer report functionality
 */
class AlexaReportApp extends AlexaApplicationAbstract implements AlexaApplicationInterface
{
    /** @var CustomerVisitorCollection */
    private $customerVisitorCollection;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /**
     * @var Visitor
     */
    protected $visitorModel;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * Constructor.
     * @param ResponseDataFactory $responseDataFactory
     * @param CollectionFactory $customerVisitorCollection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param StoreManagerInterface $storeManager
     * @param Visitor $visitorModel
     * @param DateTime $date
     */
    public function __construct(
        ResponseDataFactory $responseDataFactory,
        CollectionFactory $customerVisitorCollection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        StoreManagerInterface $storeManager,
        Visitor $visitorModel,
        DateTime $date
    )
    {
        $this->storeManager = $storeManager;
        $this->responseDataFactory = $responseDataFactory;
        $this->customerVisitorCollection = $customerVisitorCollection;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->visitorModel = $visitorModel;
        $this->date = $date;
    }

    /**
     * @inheritdoc
     */
    public function intentRequest(SessionDataInterface $sessionData,
                                  CustomerDataInterface $customerData,
                                  $intentName,
                                  $slots)
    {
        if ($intentName == 'OnlineCustomersReport') {
            return $this->onlineCustomersReport($slots);
        } elseif ($intentName == 'LoadingTimeReport') {
            return $this->loadingTimeReport();
        } elseif ($intentName == 'SystemReport') {
            return $this->systemReport();
        }

        $response = $this->responseDataFactory->create();
        $response->setShouldEndSession(true);

        return $response;
    }

    /**
     * @param $slots
     * @return ResponseData
     */
    public function onlineCustomersReport($slots)
    {
        $response = $this->responseDataFactory->create();

        if (isset($slots['minutes']) && $slots['minutes'] > 0) {
            $interval = $slots['minutes'] * 60;
            $timeIntervalText = $slots['minutes'] . ' minutes';
        } elseif (isset($slots['hours']) && $slots['hours'] > 0) {
            $interval = $slots['hours'] * 60 * 60;
            $timeIntervalText = $slots['hours'] . ' hours';
        } else {
            $interval = $this->visitorModel->getOnlineInterval() * 60;
            $timeIntervalText = $this->visitorModel->getOnlineInterval() . ' minutes';
        }

        $collection = $this->customerVisitorCollection->create();

        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->date->gmtTimestamp() - $interval);

        $collection->addFieldToFilter('last_visit_at', array('gteq' => $dateTime->format('Y-m-d H:i:s')));

        $customersOnlineCount = $collection->getSize();

        $text = 'In the last %s there were %s people online';

        $text = $customersOnlineCount
            ? sprintf($text, $timeIntervalText, $customersOnlineCount)
            : sprintf($text, $timeIntervalText, 'no');

        $response->setResponseText($text);
        $response->setCardSimple("Report - Customers Online", $text);
        $response->setShouldEndSession(true);

        return $response;
    }

    /**
     * @return ResponseData
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function loadingTimeReport()
    {
        $response = $this->responseDataFactory->create();
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();

        $start = microtime(true);
        $pageContent = file_get_contents($baseUrl);
        $measuredTime = microtime(true) - $start;

        $text = '';
        $textAdd = '';

        if (strpos($pageContent, 'Magento_Ui/js/core/app') !== false) {
            $text .= sprintf(
                'Homepage load time was <emphasis level="strong">%s</emphasis>',
                $this->timePronunciation($measuredTime)
            );

            if ($measuredTime < 0.3) {
                $textAdd .= ' <amazon:effect name="whispered"> It\'s good, your shop is fast... </amazon:effect>';
            }
        } else {
            $text .= sprintf(
                'Warning, homepage might be corrupted. Anyway load time was: %s',
                $this->timePronunciation($measuredTime)
            );
        }

        $response->setResponseSsml('<speak>' . $text . $textAdd . '</speak>');
        $response->setCardSimple("Report - Loading Time", $text);
        $response->setShouldEndSession(true);

        return $response;
    }

    /**
     * @return ResponseData
     */
    public function systemReport()
    {
        $response = $this->responseDataFactory->create();

        $text = '<speak>';
        if ($this->isLinux()) {
            $cpuUsage = sys_getloadavg();
            $text .= ' Processor load: <emphasis level="strong">' . $cpuUsage[0] . '</emphasis>';
            $text .= '<break time="500ms"/>';
            $text .= ' Available memory: <emphasis level="strong">'
                . $this->getServerAvailableMemory() . '</emphasis> percent.';
        } else {
            $text = 'System report is currently supported only in Linux environment.';
        }
        $text .= '</speak>';

        $response->setResponseSsml($text);
        $response->setCardSimple("Report - System", $text);
        $response->setShouldEndSession(true);

        return $response;
    }

    /**
     * Check if current process is running under linux
     * @todo I didn't think about for too long, so it might be false sometimes. :)
     *
     * @return bool
     */
    public function isLinux()
    {
        return (strtolower(substr(PHP_OS, 0, 3)) === 'lin');
    }

    /**
     * Get percentage information about available memory
     * @return float|int
     */
    public function getServerAvailableMemory()
    {
        $free = shell_exec('free');
        $free = (string)trim($free);
        $freeArray = explode("\n", $free);
        $mem = explode(" ", $freeArray[1]);
        $mem = array_filter($mem);
        $mem = array_merge($mem);
        $memoryAvailablePercent = $mem[6] / $mem[1];

        return round($memoryAvailablePercent * 100);
    }

    /**
     * This method represent seconds / microseconds in understandable form
     *
     * @param $time time in seconds
     * @return string
     */
    public function timePronunciation($time)
    {
        if ($time < 1) {
            return sprintf('%s microseconds', round($time * 1000));
        } else {
            $seconds = floor($time);
            $microseconds = round($time - $seconds * 1000);

            return sprintf('%s seconds and %s microseconds', $seconds, $microseconds);
        }
    }
}
