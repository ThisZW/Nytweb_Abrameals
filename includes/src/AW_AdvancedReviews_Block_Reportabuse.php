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


class AW_AdvancedReviews_Block_Reportabuse extends Mage_Core_Block_Template
{
    protected $_reviewId;

    protected function _construct()
    {
        parent::_construct();
    }

    public function setReviewId($reviewId)
    {
        $this->_reviewId = $reviewId;
        return $this;
    }

    public function getRedirectBackUrl()
    {
        $params = $this->getRequest()->getParams();

        if (Mage::getStoreConfigFlag('web/seo/use_rewrites') && isset($params['id'])) {
            return Mage::helper('advancedreviews')->getFuReviewsUrl(
                $params['id'], (isset($params['category']) ? $params['category'] : null)
            );
        } else {
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
    }

    public function getReviewId()
    {
        return $this->_reviewId;
    }

    public function getAbuseUrl()
    {
        if ($this->_reviewId) {
            return Mage::getUrl('advancedreviews/abuse/reportajax', array('reviewId' => $this->_reviewId));
        } else {
            return '#';
        }
    }
}