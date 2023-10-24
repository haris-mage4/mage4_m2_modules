<?php

namespace InformaticsCommerce\RemoveCcSave\Model;


use ParadoxLabs\Authnetcim\Model\ConfigProvider;

class OverrideConfigProvider extends ConfigProvider
{
    /**
     * If card can be saved for further use
     *
     * @return boolean
     */
    public function canSaveCard()
    {
        if ($this->customerSession->isLoggedIn()) {
            return false;
        }

        return false;
    }
}
