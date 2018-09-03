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
    private $visitorModel;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * Constructor.
     * @param ResponseDataFactory $responseDataFactory
     * @param CollectionFactory $customerVisitorCollection
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Visitor $visitorModel
     * @param DateTime $date
     */
    public function __construct(
        ResponseDataFactory $responseDataFactory,
        CollectionFactory $customerVisitorCollection,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Visitor $visitorModel,
        DateTime $date

    )
    {
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
            $minutesInterval = $slots['minutes'];
            $text = 'In the last ' . (int)$slots['minutes'] . ' minutes there were %s people online';
        } elseif (isset($slots['hours']) && $slots['hours'] > 0) {
            $minutesInterval = $slots['hours'] * 60;
            $text = 'In the last ' . (int)$slots['hours'] . ' hours there were %s people online';
        } else {
            $minutesInterval = $this->visitorModel->getOnlineInterval();
            $text = 'In the last ' . (int)$minutesInterval . ' minutes there were %s people online';
        }

        $collection = $this->customerVisitorCollection->create();

        $dateTime = new \DateTime();
        $dateTime->setTimestamp($this->date->gmtTimestamp() - ($minutesInterval * 60));

        $collection->addFieldToFilter('last_visit_at', array('gteq' => $dateTime->format('Y-m-d H:i:s')));

        $customersOnlineCount = $collection->getSize();

        $text = $customersOnlineCount ? sprintf($text, $customersOnlineCount) : sprint($text, 'no');

        $response->setResponseText($text);
        $response->setCardSimple("Customers online", $text);
        $response->setShouldEndSession(true);

        return $response;
    }
}
