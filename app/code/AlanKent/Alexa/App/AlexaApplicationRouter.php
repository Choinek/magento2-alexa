<?php

namespace AlanKent\Alexa\App;

use AlanKent\Alexa\Model\AlexaRouter\Config\Data;

/**
 * Used to collect Alexa Listeners and use proper one to handle request
 */
class AlexaApplicationRouter implements AlexaApplicationInterface
{
    /** @var ResponseDataFactory */
    private $responseDataFactory;

    /** @var Data */
    private $configData;

    /**
     * Constructor.
     * @param ResponseDataFactory $responseDataFactory
     * @param Data $configData
     */
    public function __construct(ResponseDataFactory $responseDataFactory,
                                Data $configData)
    {
        $this->responseDataFactory = $responseDataFactory;
        $this->configData = $configData;
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
        $intents = $this->configData->get('alexaRouter/intents');

        if (array_key_exists($intentName, $intents)) {

            /** @todo handle class factory */

        } elseif ($default = $this->configData->get('alexaRouter/default')) {

            /** @todo handle class factory */

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
