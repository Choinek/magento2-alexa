<?php

namespace AlanKent\Alexa\Model\AlexaRouter\Config;

use Magento\Framework\Serialize\SerializerInterface;

/**
 * Provides catalog attributes configuration
 */
class Data extends \Magento\Framework\Config\Data
{
    /**
     * Constructor
     *
     * @param Reader $reader
     * @param \Magento\Framework\Config\CacheInterface $cache
     * @param string|null $cacheId
     * @param SerializerInterface|null $serializer
     */
    public function __construct(
        Reader $reader,
        \Magento\Framework\Config\CacheInterface $cache,
        $cacheId = 'alexa_routers',
        SerializerInterface $serializer = null
    )
    {
        parent::__construct($reader, $cache, $cacheId, $serializer);
    }
}
