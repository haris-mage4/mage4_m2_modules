<?php


namespace Baytonia\FrontendSorting\Model\ResourceModel\Fulltext;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Api\Search\SearchResultInterface;
use Magento\Framework\Api\FilterBuilder;
use Magento\Search\Api\SearchInterface;
use Magento\Catalog\Model\Layer\Filter\Dynamic\AlgorithmFactory;
use Magento\Store\Model\ScopeInterface;
use Mageplaza\LayeredNavigation\Model\Search\SearchCriteriaBuilder;
use Magento\Catalog\Model\ResourceModel\Product\Collection as ParentsCollection;

class Collection extends \Mageplaza\LayeredNavigation\Model\ResourceModel\Fulltext\Collection
{
    /** @var SearchResultInterface */
    private $searchResult;

    /** @var FilterBuilder */
    private $filterBuilder;

    /** @var SearchInterface */
    private $search;

    /** @var string */
    private $queryText;

    /** @var SearchCriteriaBuilder */
    private $searchCriteriaBuilder;

    /** @var string */
    private $searchRequestName;

    /**
     * @var array
     */
    private $searchOrders;

    protected function ElasticSearchApply()
    {
        $this->searchResult = ObjectManager::getInstance()->get(SearchResultInterface::class);
        if (empty($this->searchResult->getItems())) {
            $this->getSelect()->where('NULL');

            return;
        }
        $ids = [];
        foreach ($this->searchResult->getItems() as $item) {
            $ids[] = (int) $item->getId();
        }

        $this->getSelect()->where('e.entity_id IN (?)', $ids);
        $this->getSelect()->reset(Select::ORDER);
        $this->getSelect()->order(new Zend_Db_Expr('FIELD(e.entity_id,' . implode(',', $ids) . ')'));
    }

    /**
     * @throws LocalizedException
     * @throws Zend_Db_Exception
     */
    protected function _renderFiltersBefore()
    {
        $this->searchRequestName = 'catalog_view_container';
        $this->getCollectionClone();

        $this->getSearchCriteriaBuilder();
        $this->getFilterBuilder();
        $this->getSearch();

        if ($this->queryText) {
            $this->filterBuilder->setField('search_term');
            $this->filterBuilder->setValue($this->queryText);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        }

        $priceRangeCalculation = $this->_scopeConfig->getValue(
            AlgorithmFactory::XML_PATH_RANGE_CALCULATION,
            ScopeInterface::SCOPE_STORE
        );
        if ($priceRangeCalculation) {
            $this->filterBuilder->setField('price_dynamic_algorithm');
            $this->filterBuilder->setValue('auto');
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        }
        if ($this->request->getFullActionName() === 'catalogsearch_result_index') {
            $this->searchRequestName = 'quick_search_container';
            $this->filterBuilder->setField('visibility');
            $this->filterBuilder->setValue([3, 4]);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        }

        $searchCriteria = $this->searchCriteriaBuilder->create();
        $searchCriteria->setRequestName($this->searchRequestName);
        $searchCriteria->setSortOrders($this->searchOrders);
        $searchCriteria->setCurrentPage((int) $this->_curPage);

        try {
            $this->searchResult = ObjectManager::getInstance()->get(SearchResultInterface::class);
            $this->searchResult = $this->getSearch()->search($searchCriteria);
        } catch (Exception $e) {
            throw new LocalizedException(__('Sorry, something went wrong. You can find out more in the error log.'));
        }

        if (strpos($this->getSearchEngine(), 'elasticsearch') !== false) {
            $this->ElasticSearchApply();
        } else {
            $this->CatalogSearchApply();
        }
        
        ParentsCollection::_renderFiltersBefore();
    }

    /**
     * Return field faceted data from faceted search result
     *
     * @param string $field
     *
     * @return array
     * @throws StateException
     */
    public function getFacetedData($field)
    {
        $this->_renderFilters();
        $result = [];

        $aggregations = $this->searchResult->getAggregations();
        // This behavior is for case with empty object when we got EmptyRequestDataException
        if (null !== $aggregations) {
            $bucket = $aggregations->getBucket($field . RequestGenerator::BUCKET_SUFFIX);
            if ($bucket) {
                foreach ($bucket->getValues() as $value) {
                    $metrics                   = $value->getMetrics();
                    $result[$metrics['value']] = $metrics;
                }
            } else {
                throw new StateException(__('Bucket does not exist'));
            }
        }

        return $result;
    }


    /**
     * @return FilterBuilder
     * @deprecated
     */
    private function getFilterBuilder()
    {
        $this->filterBuilder = ObjectManager::getInstance()->get(FilterBuilder::class);
        return $this->filterBuilder;
    }

     /**
     * @return SearchInterface
     * @deprecated
     */
    private function getSearch()
    {
        $this->search = ObjectManager::getInstance()->get(SearchInterface::class);
        return $this->search;
    }

     /**
     * @return SearchCriteriaBuilder
     * @deprecated
     */
    public function getSearchCriteriaBuilder()
    {
        $this->searchCriteriaBuilder = ObjectManager::getInstance()
                ->get(SearchCriteriaBuilder::class);
        return $this->searchCriteriaBuilder;
    }

     /**
     * Apply attribute filter to facet collection
     *
     * @param string $field
     * @param null $condition
     *
     * @return $this
     */
    public function addFieldToFilter($field, $condition = null)
    {
        if ($this->searchResult !== null) {
            throw new RuntimeException('Illegal state');
        }

        $this->getSearchCriteriaBuilder();
        $this->getFilterBuilder();

        if (isset($condition['in'])
            && (strpos($this->getSearchEngine(), 'elasticsearch') !== false
                || $this->getSearchEngine() === 'amasty_elastic')
        ) {
            $this->filterBuilder->setField($field);
            $this->filterBuilder->setValue($condition['in']);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        } elseif (!is_array($condition) || !in_array(key($condition), ['from', 'to'])) {
            $this->filterBuilder->setField($field);
            $this->filterBuilder->setValue($condition);
            $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
        } else {
            if (!empty($condition['from'])) {
                $this->filterBuilder->setField("{$field}.from");
                $this->filterBuilder->setValue($condition['from']);
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            }
            if (!empty($condition['to'])) {
                $this->filterBuilder->setField("{$field}.to");
                $this->filterBuilder->setValue($condition['to']);
                $this->searchCriteriaBuilder->addFilter($this->filterBuilder->create());
            }
        }

        return $this;
    }

     /**
     * MP LayerNavigation Clone collection
     *
     * @return Collection
     */
    public function getCollectionClone()
    {
        if ($this->collectionClone === null) {
            $this->collectionClone = clone $this;
            $this->searchCriteriaBuilder = ObjectManager::getInstance()
                ->get(SearchCriteriaBuilder::class);
            $this->collectionClone->setSearchCriteriaBuilder($this->searchCriteriaBuilder->cloneObject());
        }

        $searchCriterialBuilder = $this->collectionClone->getSearchCriteriaBuilder()->cloneObject();

        /** @var Collection $collectionClone */
        $collectionClone = clone $this->collectionClone;
        $collectionClone->setSearchCriteriaBuilder($searchCriterialBuilder);

        return $collectionClone;
    }

}