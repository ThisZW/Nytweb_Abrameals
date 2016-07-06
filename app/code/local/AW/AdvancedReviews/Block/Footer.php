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


class AW_AdvancedReviews_Block_Footer extends Mage_Core_Block_Template
{
    protected $_reviewId;

    public function setReviewId($reviewId)
    {
        $this->_reviewId = $reviewId;
        return $this;
    }

    public function getReviewId()
    {
        return $this->_reviewId;
    }

    public function canShowAbuse()
    {
        if (Mage::helper('advancedreviews')->confAllowOnlyLoggedToAbuse()) {
            return (Mage::helper('advancedreviews')->isUserLogged()
                && Mage::helper('advancedreviews')->confShowReportAbuse()
                && !(Mage::helper('advancedreviews')->isAbuseRegistered($this->_reviewId)));
        } else {
            return (Mage::helper('advancedreviews')->confShowReportAbuse()
                && !(Mage::helper('advancedreviews')->isAbuseRegistered($this->_reviewId)));
        }
    }

    public function canShowSocial()
    {
        return Mage::helper('advancedreviews')->confShowSocialShare();
    }

    public function canShowHelpfulness()
    {
        return Mage::helper('advancedreviews')->confShowHelpfulness();
    }
}