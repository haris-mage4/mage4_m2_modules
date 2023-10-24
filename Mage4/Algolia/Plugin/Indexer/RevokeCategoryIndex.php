<?php

namespace Mage4\Algolia\Plugin\Indexer;

use Algolia\AlgoliaSearch\Model\Indexer\Category;

class RevokeCategoryIndex
{
    public function aroundExecute(Category $subject, callable $proceed): void
    {
        return;
    }
}