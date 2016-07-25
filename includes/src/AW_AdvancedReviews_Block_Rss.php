<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_AdvancedReviews
 * @version    2.3.9
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_AdvancedReviews_Block_Rss extends Mage_Rss_Block_Abstract
{
    protected function _toHtml()
    {
        $productId = $this->getRequest()->getParam('product');
        $categoryId = $this->getRequest()->getParam('category');

        $title = 'Advanced Reviews';
        $rssObj = Mage::getModel('rss/rss');
        $route = Mage::helper('advancedreviews')->getRssRoute();
        $url = $this->getUrl($route);

        $collection = Mage::getModel('catalog/product')->getCollection()
            ->addAttributeToSelect('name')
            ->addAttributeToSelect('short_description')
            ->addAttributeToSelect('updated_at')
            ->addStoreFilter(Mage::app()->getStore()->getId());


        if ($categoryId) {
            $category = Mage::getModel('catalog/category')->load($categoryId);
            $collection->addCategoryFilter($category);
        }

        if ($productId) {
            $collection->getSelect()->where('e.entity_id = ?', $productId);

            foreach ($collection as $product) {
                $title = $product->getName() . ' - Reviews';
                $collection = $this->getReviews($product->getId());
            }
        }

        $data = array(
            'title'       => $title,
            'description' => $title,
            'link'        => $url,
            'charset'     => 'UTF-8',
        );

        $rssObj->_addHeader($data);

        unset($data);

        if ($collection->getSize() > 0) {
            foreach ($collection as $item) {

                if ($productId) {
                    $data = array(
                        'title'       => $item->getTitle(),
                        'link'        => '',
                        'description' => nl2br($item->getDetail()),
                        'lastUpdate'  => strtotime($item->getCreatedAt()),
                    );
                } else {
                    $reviewsCount = $this->getReviews($item->getId())->getSize();
                    if ($reviewsCount > 0) {
                        $data = array(
                            'title'       => $item->getName() . ' - ' . $reviewsCount . ' Review(s)',
                            'link'        => $this->getUrl($route . "/index/product/" . $item->getId()),
                            'description' => $item->getShortDescription(),
                            'lastUpdate'  => strtotime($item->getUpdatedAt()),
                        );
                    }
                }
                if (isset($data)) {
                    $rssObj->_addEntry($data);
                }
                unset($data);
            }
        }

        return $rssObj->createRssXml();
    }

    public function getReviews($id)
    {

        $reviews = Mage::getModel('review/review')->getResourceCollection();
        $reviews->addStoreFilter(Mage::app()->getStore()->getId())
            ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED);

        $reviews->getSelect()->where('entity_pk_value = ?', $id);
        return $reviews;
    }
}