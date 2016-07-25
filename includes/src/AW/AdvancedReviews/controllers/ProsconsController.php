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


class AW_AdvancedReviews_ProsconsController extends Mage_Core_Controller_Front_Action
{
    public function checkbyprosconsAction()
    {
        $block = $this->getLayout()->createBlock('advancedreviews/ajax_proscons')->setTemplate(
            'advancedreviews/ajax/proscons.phtml'
        );
        return $this->getResponse()->setBody($block->toHtml());
    }

    public function updatepagerAction()
    {
        $page = $this->getRequest()->getParam('page');
        $limit = $this->getRequest()->getParam('limit');
        $reviews = $this->getRequest()->getParam('reviews');
        $product = $this->getRequest()->getParam('product');

        $block = Mage::helper('advancedreviews')->getPagerToolbar($product, $reviews, $page, $limit);
        return $this->getResponse()->setBody($block);
    }

    public function getfilteredreviewsAction()
    {
        if (!$this->_isAjax()) {
            return $this->_redirectReferer();
        }
        $product = $this->getRequest()->getParam('product');
        $customer = $this->getRequest()->getParam('customer');
        $reviews = $this->getRequest()->getParam('reviews');
        $page = $this->getRequest()->getParam('page');
        $limit = $this->getRequest()->getParam('limit');
        $sortBy = $this->getRequest()->getParam('type');
        $sortDir = $this->getRequest()->getParam('dir');
        $block = Mage::helper('advancedreviews')->getFilteredReviews(
            $product, $reviews, $page, $limit, $sortBy, $sortDir, $customer
        );
        return $this->getResponse()->setBody($block);
    }

    public function checkAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            if (isset($data['proscons_items'])) {
                Mage::helper('advancedreviews')->setAllProscons($data['proscons_items']);
            } else {
                $this->resetAction();
            }
            Mage::helper('advancedreviews')->setProsconsState('pros', $data['pros-state']);
            Mage::helper('advancedreviews')->setProsconsState('cons', $data['cons-state']);
        }
        $this->_redirectReferer(Mage::helper('advancedreviews')->getReviewsBackUrl());
    }

    public function resetAction()
    {
        Mage::helper('advancedreviews')->resetProscons();
        Mage::helper('advancedreviews')->setProsconsState('pros', 0);
        Mage::helper('advancedreviews')->setProsconsState('cons', 0);
        $this->_redirectReferer(Mage::helper('advancedreviews')->getReviewsBackUrl());
    }

    public function deleteAction()
    {
        if ($prosconsId = $this->getRequest()->getParam('prosconsId')) {
            Mage::helper('advancedreviews')->unregisterProscons($prosconsId);
        }
        $this->_redirectReferer(Mage::helper('advancedreviews')->getReviewsBackUrl());
    }

    protected function _isAjax()
    {
        $request = $this->getRequest();
        if ($request->isXmlHttpRequest() || $request->getParam('ajax') || $request->getParam('isAjax')) {
            return true;
        } else {
            return false;
        }
    }
}