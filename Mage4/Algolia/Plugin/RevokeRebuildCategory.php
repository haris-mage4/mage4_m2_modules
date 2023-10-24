<?php

namespace Mage4\Algolia\Plugin;

use Algolia\AlgoliaSearch\Helper\Data;

class RevokeRebuildCategory
{
    public function aroundRebuildCategoryIndex(Data $subject, callable $proceed): void
    {
        return;
    }
}