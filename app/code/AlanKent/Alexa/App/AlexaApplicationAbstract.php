<?php

namespace AlanKent\Alexa\App;


use AlanKent\Alexa\Model\AlexaRouter\Config\Data;
use Magento\Framework\ObjectManagerInterface;

/**
 * Abstract class for another Alexa Application Handlers
 */
abstract class AlexaApplicationAbstract implements AlexaApplicationInterface
{
    /** @var ResponseDataFactory */
    protected $responseDataFactory;

    /** @var Data */
    protected $configData;

    /** @var ObjectManagerInterface */
    protected $objectManager;

    /**
     * Constructor.
     * @param ResponseDataFactory $responseDataFactory
     * @param Data $configData
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ResponseDataFactory $responseDataFactory,
                                Data $configData,
                                ObjectManagerInterface $objectManager)
    {
        $this->responseDataFactory = $responseDataFactory;
        $this->configData = $configData;
        $this->objectManager = $objectManager;
    }

    /**
     * @inheritdoc
     */
    public function launchRequest(SessionDataInterface $sessionData,
                                  CustomerDataInterface $customerData)
    {
        $response = $this->responseDataFactory->create();
        $response->setResponseText("What is your request?");
        $response->setShouldEndSession(false);

        return $response;
    }

    /**
     * @inheritdoc
     */
    public function endSession(SessionDataInterface $sessionData,
                               CustomerDataInterface $customerData,
                               $reason)
    {
        $response = $this->responseDataFactory->create();
        $response->setResponseText("Goodbye.");
        $response->setShouldEndSession(true);

        return $response;
    }
}
