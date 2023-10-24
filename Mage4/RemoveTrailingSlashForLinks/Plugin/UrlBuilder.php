<?php

namespace Mage4\RemoveTrailingSlashForLinks\Plugin;

use Magento\Framework\Url as UrlFramework;

/**
 * Class UrlBuilder
 * @package Mage4\RemoveTrailingSlashForLinks\Plugin
 */
class UrlBuilder
{
    /**
     * Remove trailing slash for all links output via getUrl
     * @param $subject
     * @param $result
     * @return string
     */
    public function afterGetRouteUrl(UrlFramework $subject, $result)
    {
        if (empty($result) || !is_string($result)) {
            return $result;
        }
        return rtrim($result, '/');
    }
}
