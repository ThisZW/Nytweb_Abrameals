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


require_once 'Mage/Review/controllers/ProductController.php';

class AW_AdvancedReviews_ProductController extends Mage_Review_ProductController
{
    public function postAction()
    {
        if (Mage::helper('advancedreviews')->getAntiSpamEnabled()) {
            $bot = ($this->getRequest()->getPost('_antispam')) ? false : true;
        } else {
            $bot = false;
        }

        if (!$bot) {
            if ($data = $this->getRequest()->getPost()) {
                //Save PROSCONS data in customer session
                if (isset($data['proscons_items'])) {
                    Mage::register('advancedreviews_proscons_new_items', $data['proscons_items']);
                }
                if (isset($data['user-pros'])) {
                    Mage::register('advancedreviews_proscons_user_pros', $data['user-pros']);
                }
                if (isset($data['user-cons'])) {
                    Mage::register('advancedreviews_proscons_user_cons', $data['user-cons']);
                }
                if (isset($data['recommend'])) {
                    Mage::register('advancedreviews_recommend_value', $data['recommend']);
                }
                if (isset($data['email'])) {
                    Mage::register('advancedreviews_guest_email', $data['email']);
                }
            }

            //Here allow to insert review
            Mage::getSingleton('review/session')->setRedirectUrl(Mage::helper('advancedreviews')->getReviewsBackUrl());
            parent::postAction();
            /*
             * Logical functional finished in AW_AdvancedReviews_Model_Mysql4_Review
             * throw overrided _afterSave() of Mage_Review_Model_Mysql4_Review
             */
        } else {
            Mage::getSingleton('core/session')->addError(
                $this->__('Antispam code is invalid. Please, check if JavaScript is enabled in your browser settings.')
            );
            $this->_redirectReferer();
        }
    }

    public function viewAction()
    {
        $review = $this->_loadReview((int)$this->getRequest()->getParam('id'));
        if (!$review) {
            # in case we are switching store/language, no review most likely means it cannot be accessed in this store/language
            $toStore = $this->getRequest()->getParam('___store');
            if ($toStore) {
                Mage::getSingleton('core/session')
                    ->addError($this->__('Sorry, this review is not accessible in the selected store/language'));
                if ($this->getRequest()->getParam('product_id')) {
                    $this->_forward('list', 'product', 'review', array('id' => $this->getRequest()->getParam('product_id')));
                    return;
                }
            }
            $this->_forward('noroute');
            return;
        }

        $product = $this->_loadProduct($review->getEntityPkValue());
        if (!$product) {
            $this->_forward('noroute');
            return;
        }

        $this->loadLayout();
        $this->_initLayoutMessages('review/session');
        $this->_initLayoutMessages('catalog/session');
        $this->getLayout()->getBlock('head')->setTitle(
            $product->getMetaTitle() . ' - ' . $review->getTitle() . $this->__(' - review by ') . $review->getNickname()
        );
        $this->renderLayout();
    }

    protected function _loadReview($reviewId)
    {
        if (!$reviewId) {
            return false;
        }

        $review = Mage::getModel('review/review')->load($reviewId);
        /* @var $review Mage_Review_Model_Review */
        if (
            !$review->getId() || !$review->getStatusId() == Mage_Review_Model_Review::STATUS_APPROVED
            || !in_array(Mage::app()->getStore()->getId(), (array)$review->getStores())
        ) {
            return false;
        }

        Mage::register('current_review', $review);

        return $review;
    }

    protected function _loadProduct($productId)
    {
        if (!$productId) {
            return false;
        }

        $product = Mage::getModel('catalog/product')
            ->setStoreId(Mage::app()->getStore()->getId())
            ->load($productId);
        /* @var $product Mage_Catalog_Model_Product */
        if (!$product->getId() || !$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
            return false;
        }

        Mage::register('current_product', $product);
        Mage::register('product', $product);

        return $product;
    }
}
