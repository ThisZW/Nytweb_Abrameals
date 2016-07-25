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

/**
 * Advanced Reviews Product Review List
 */
class AW_AdvancedReviews_Block_Product_View_List extends Mage_Review_Block_Product_View_List
{
    /**
     * Class constructor
     */
    protected function _construct()
    {
        parent::_construct();

        //Define ProductId for proscons filter
        Mage::register('advancedreviews_proscons_is_product', $this->getProductId());
        $this->_checkProsconsPlace();
    }

    public function renderView()
    {
        $this->setTemplate('advancedreviews/review/product/view/list.phtml');
        return parent::renderView();
    }

    /**
     * Disable native reviews and turn on self to their place
     *
     * @return AW_AdvancedReviews_Block_Product_View_List
     */
    public function disableNativeReviews()
    {
        if (Mage::helper('advancedreviews')->getExtDisabled()) {
            return $this;
        }

        $this->getParentBlock()
            ->unsetChild('product_additional_data')
            ->setChild('product_additional_data', $this);
        return $this;
    }

    /**
     * Retrives reviews colllection
     *
     * @return Mage_Review_Model_Mysql4_Review_Collection
     */
    public function getReviewsCollection()
    {
        if (
            Mage::helper('advancedreviews')->getAllProscons()
            && count(Mage::helper('advancedreviews')->getAllProscons())
        ) {
            return $this->_getFilteredReviewsCollection();
        } else {
            return parent::getReviewsCollection();
        }
    }

    /**
     * Retrives reviews colllection filtered by Pros And Cons Filter
     *
     * @return Mage_Review_Model_Mysql4_Review_Collection
     */
    protected function _getFilteredReviewsCollection()
    {
        if (null === $this->_reviewsCollection) {
            $this->_reviewsCollection = Mage::getModel('review/review')->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addEntityFilter('product', $this->getProduct()->getId())
                ->addProsconsFilter()
                ->setDateOrder()
            ;
        }
        return $this->_reviewsCollection;
    }

    /**
     * Check Pros And Cons for same place
     */
    protected function _checkProsconsPlace()
    {
        if ($place = Mage::helper('advancedreviews')->getProsconsPlace()) {
            if (!($place['place'] == 'list' && $place['id'] == $this->getProductId())) {
                Mage::helper('advancedreviews')->resetProscons()
                    ->setProsconsState('pros', 0)
                    ->setProsconsState('cons', 0)
                ;
                $place['place'] = 'list';
                $place['id'] = $this->getProductId();
                Mage::helper('advancedreviews')->setProsconsPlace($place);
            }
        } else {
            $place['place'] = 'list';
            $place['id'] = $this->getProductId();
            Mage::helper('advancedreviews')->setProsconsPlace($place);
        }
    }

    /**
     * Retrives url to Reviews with $id
     *
     * @param int|string $id Review Id
     *
     * @return string
     */
    public function getReviewUrl($id)
    {
        if (Mage::getStoreConfigFlag('web/seo/use_rewrites')) {
            return Mage::helper('advancedreviews')->getFuReviewUrl(
                str_replace(
                    AW_AdvancedReviews_Model_Urlrewrite::page_suffix, '', $this->getRequest()->getOriginalPathInfo()
                ), $id
            );
        } else {
            return Mage::helper('advancedreviews')->getReviewUrl($id);
        }
    }
}