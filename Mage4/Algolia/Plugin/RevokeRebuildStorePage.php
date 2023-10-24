<?php

namespace Mage4\Algolia\Plugin;

use Algolia\AlgoliaSearch\Helper\Data;

class RevokeRebuildStorePage
{
    public function aroundRebuildStorePageIndex(Data $subject, $proceed): void
    {
        return;
    }
}