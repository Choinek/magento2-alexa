<?php

namespace AlanKent\Alexa\App;


use AlanKent\Alexa\Model\AlexaRouter\Config\Data;
use Magento\Framework\ObjectManagerInterface;

/**
 * Used to collect Alexa Listeners and use proper one to handle request
 */
class AlexaApplicationRouter implements AlexaApplicationInterface
{
    /** @var ResponseDataFactory */
    private $responseDataFactory;

    /** @var Data */
    private $configData;

    /** @var ObjectManagerInterface */
    private $objectManager;

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
    public function intentRequest(SessionDataInterface $sessionData,
                                  CustomerDataInterface $customerData,
                                  $intentName,
                                  $slots)
    {
        /** @var AlexaApplicationInterface $handler */
        $handler = false;
        if ($intents = $this->configData->get('alexaRouter/intents')) {
            if (array_key_exists($intentName, $intents)) {
                $handler = $this->objectManager->get($intents[$intentName]);
            } elseif ($default = $this->configData->get('alexaRouter/default')) {
                $handler = $this->objectManager->get($default);
            }

            if ($handler) {
                return $handler->intentRequest($sessionData, $customerData, $intentName, $slots);
            }
        }

        $response = $this->responseDataFactory->create();
        $response->setShouldEndSession(true);

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
