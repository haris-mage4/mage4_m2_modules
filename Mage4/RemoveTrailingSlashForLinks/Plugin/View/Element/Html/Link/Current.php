<?php

namespace Mage4\RemoveTrailingSlashForLinks\Plugin\View\Element\Html\Link;

/**
 * Class Current
 * @package Mage4\RemoveTrailingSlashForLinks\Plugin\View\Element\Html\Link
 */
class Current
{
    /**
     * Remove trailing slash for all links output via getHref
     * @param $subject
     * @param $result
     * @return string
     */
    public function afterGetHref($subject, $result)
    {
        return rtrim($result, '/');
    }
}
