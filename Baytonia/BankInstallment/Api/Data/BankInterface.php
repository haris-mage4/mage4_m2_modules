<?php

namespace Baytonia\BankInstallment\Api\Data;

interface BankInterface
{
    const BASE_MEDIA_PATH = 'baytonia/bankinstallment/images';

    /**
     * get form field html id prefix.
     *
     * @return string
     */
    public function getFormFieldHtmlIdPrefix();

    /**
     * get available slides.
     *
     * @return []
     */
    public function getAvailableSlides();

    /**
     * get store attributes.
     *
     * @return array
     */
    public function getStoreAttributes();

    /**
     * get store view id.
     *
     * @return int
     */
    public function getStoreViewId();

    /**
     * set store view id.
     *
     * @param int $storeViewId
     */
    public function setStoreViewId($storeViewId);

    /**
     * before save.
     */
    public function beforeSave();

    /**
     * after save.
     */
    public function afterSave();

    /**
     * load info multistore.
     *
     * @param mixed  $id
     * @param string $field
     *
     * @return $this
     */
    public function load($id, $field = null);

    /**
     * get store view value.
     *
     * @param string|null $storeViewId
     *
     * @return $this
     */
    public function getStoreViewValue($storeViewId = null);
}
