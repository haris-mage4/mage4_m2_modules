<?php

namespace Baytonia\CloudflareSXAdapterExtended\Block\Backend;

class Cache extends \Skynix\CloudflareSXAdapter\Block\Backend\Cache
{
    /**
     * Cache constructor.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_controller = 'cache';
        $this->_headerText = __('Cache Storage Management');
        parent::_construct();
        $this->buttonList->remove('add');

        if ($this->_authorization->isAllowed('Magento_Backend::flush_magento_cache')) {
            $this->buttonList->add(
                'flush_magento',
                [
                    'label' => __('Flush Magento Cache'),
                    'onclick' => 'setLocation(\'' . $this->getFlushSystemUrl() . '\')',
                    'class' => 'primary flush-cache-magento'
                ]
            );
        }

        if ($this->_authorization->isAllowed('Magento_Backend::flush_cache_storage')) {
            $message = __('The cache storage may contain additional data. Are you sure that you want to flush it?');
            $this->buttonList->add(
                'flush_system',
                [
                    'label' => __('Flush Cache Storage'),
                    'onclick' => 'confirmSetLocation(\'' . $message . '\', \'' . $this->getFlushStorageUrl() . '\')',
                    'class' => 'flush-cache-storage'
                ]
            );
        }

        if ($this->_authorization->isAllowed('Skynix_CloudflareSXAdapter::flush_cache_cloudflare')) {
            $this->buttonList->add(
                'purge_cloudflare',
                [
                    'label' => __('Purge Cache Cloudflare'),
                    'onclick' => 'setLocation(\'' . $this->getPurgeCacheCloudflareUrl() . '\')',
                    'class' => 'flush-cache-storage'
                ]
            );
        }
    }

    /**
     * Get url for clean cache storage
     *
     * @return string
     */
    public function getFlushStorageUrl()
    {
        return $this->getUrl('adminhtml/*/flushAll');
    }

    /**
     * Get url for clean cache storage
     *
     * @return string
     */
    public function getFlushSystemUrl()
    {
        return $this->getUrl('adminhtml/*/flushSystem');
    }

    /**
     * Get url for clean cache cloudflare
     *
     * @return string
     */
    public function getPurgeCacheCloudflareUrl()
    {
        return $this->getUrl('bay_cloudflare/system_config/purge');
    }
}
