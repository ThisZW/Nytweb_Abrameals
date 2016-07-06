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


class AW_AdvancedReviews_Block_Helpfulness extends Mage_Core_Block_Template
{
    protected $_reviewId;
    protected $_collection;

    protected function _construct()
    {
        parent::_construct();
    }

    public function getCollection()
    {
        if (!isset($this->_collection)) {
            $this->_collection = Mage::getModel('review/review')
                ->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addHelpfulnessSummary()
            ;
            if ($customerId = Mage::registry('advancedreviews_proscons_is_customer')) {
                $this->_collection->addCustomerFilter($customerId);
            } elseif ($productId = Mage::registry('advancedreviews_proscons_is_product')) {
                $this->_collection->addEntityFilter('product', $productId);
            }
        }
        return $this->_collection;
    }

    public function getRedirectBackUrl()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['id'])) {
            unset($params['id']);
        }
        if (isset($params['category'])) {
            unset($params['category']);
        }
        $urlParams = array();
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }

    public function getHelpfulness()
    {
        return $this->getHelpfulnessById($this->_reviewId);
    }

    public function getReviewId()
    {
        return $this->_reviewId;
    }

    public function getFilledReviewById($reviewId)
    {
        foreach ($this->getCollection() as $review) {
            if ($review->getId() === $reviewId) {
                return $review;
            }
        }
        return null;
    }

    public function GetAction($actionName = 'None')
    {
        return Mage::getUrl(
            'advancedreviews/helpfulness/postajax', array('reviewId' => $this->_reviewId, 'actionName' => $actionName)
        );
    }

    public function setReviewId($reviewId)
    {
        $this->_reviewId = $reviewId;
        return $this;
    }

    public function getYesCount()
    {
        if (($review = $this->getFilledReviewById($this->_reviewId)) !== null) {
            return $review->getYesCount();
        }
        return 0;
    }

    public function getAllCount()
    {
        if (($review = $this->getFilledReviewById($this->_reviewId)) !== null) {
            return $review->getAllCount();
        }
        return 0;
    }

    public function canShowHelpfulnessLink()
    {
        if (Mage::helper('advancedreviews')->confAllowOnlyLoggedToVote()) {
            return (Mage::helper('advancedreviews')->isUserLogged()
                && !(Mage::helper('advancedreviews')->isHelpfulnessRegistered($this->_reviewId)));
        } else {
            return !(Mage::helper('advancedreviews')->isHelpfulnessRegistered($this->_reviewId));
        }
    }
}