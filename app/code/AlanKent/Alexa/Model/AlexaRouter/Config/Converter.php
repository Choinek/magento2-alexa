<?php

namespace AlanKent\Alexa\Model\AlexaRouter\Config;

use Magento\Framework\View\Xsd\Media\TypeDataExtractorPool;

/**
 * Class Converter convert xml to appropriate array
 *
 * @package Magento\Framework\Config
 */
class Converter implements \Magento\Framework\Config\ConverterInterface
{
    /**
     * @var \Magento\Framework\View\Xsd\Media\TypeDataExtractorPool
     */
    protected $extractorPool;

    /**
     * @param TypeDataExtractorPool $extractorPool
     */
    public function __construct(TypeDataExtractorPool $extractorPool)
    {
        $this->extractorPool = $extractorPool;
    }

    /**
     * Convert dom node tree to array
     *
     * @param \DOMDocument $source
     * @return array
     * @throws \InvalidArgumentException
     */
    public function convert($source)
    {
        $xpath = new \DOMXPath($source);
        $output = [];
        foreach ($xpath->evaluate('/config/alexaRouter') as $typeNode) {
            if ($typeNode->getAttribute('default')) {
                $output['alexaRouter']['default'] = $typeNode->getAttribute('type');
            }

            foreach ($typeNode->childNodes as $childNode) {
                if ($childNode->nodeType != XML_ELEMENT_NODE) {
                    continue;
                }

                if ($childNode->tagName === 'intents') {
                    foreach ($childNode->childNodes as $node) {
                        if ($node->nodeType != XML_ELEMENT_NODE) {
                            continue;
                        }

                        if ($node->tagName === 'intent') {
                            $output['alexaRouter']['intents'][$node->nodeValue] = $typeNode->getAttribute('type');
                        }
                    }
                }
            }
        }

        return $output;
    }

}
