<?php

namespace Baytonia\ProductAltUpdate\Observer\Catalog;

class ProductSaveBefore implements \Magento\Framework\Event\ObserverInterface
{
  /**
   * Execute observer
   *
   * @param \Magento\Framework\Event\Observer $observer
   * @return void
   */
  public function execute(
      \Magento\Framework\Event\Observer $observer
  ) {
      $product = $observer->getProduct();
      /*$title = $product->getName();
      $existingMediaGalleryEntries = $product->getMediaGalleryEntries();*/
      /*foreach ($existingMediaGalleryEntries as $key => $entry) {
          $entry->setLabel($title);
      }*/
      //$product->setMediaGalleryEntries($existingMediaGalleryEntries);
  }
}
