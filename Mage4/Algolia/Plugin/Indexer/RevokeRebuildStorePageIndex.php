<?php

namespace Mage4\Algolia\Plugin\Indexer;

use Algolia\AlgoliaSearch\Model\Indexer\Page;

class RevokeRebuildStorePageIndex
{
    public function aroundExecute(Page $subject, callable $proceed): void
    {
        return;
    }
}