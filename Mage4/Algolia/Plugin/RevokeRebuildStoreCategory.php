<?php

namespace Mage4\Algolia\Plugin;

use Algolia\AlgoliaSearch\Helper\Data;

class RevokeRebuildStoreCategory
{
    public function aroundRebuildStoreCategoryIndex(Data $subject, callable $proceed): void
    {
        return;
    }
}