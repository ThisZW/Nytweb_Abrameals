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


class AW_AdvancedReviews_Block_Socialshare extends Mage_Core_Block_Template
{
    protected $_reviewId;

    public function setReviewId($value)
    {
        $this->_reviewId = $value;
        return $this;
    }

    public function getReviewId()
    {
        return $this->_reviewId;
    }

    public function getReviewTitle($id)
    {
        return Mage::helper('advancedreviews')->getReviewTitle($id);
    }

    public function getReviewUrl($id)
    {
        if (Mage::getStoreConfigFlag('web/seo/use_rewrites')) {
            return Mage::helper('advancedreviews')->getFuReviewUrl($id);
        } else {
            return Mage::helper('advancedreviews')->getReviewUrl($id);
        }
    }

    public function getImageUrl()
    {
        return $this->getSkinUrl('advancedreviews/images/');
    }

    public function getSocialBlock()
    {
        $html = Mage::helper('advancedreviews')->confSocialHtmlBlock();

        $html = str_replace('__URL__', rawurlencode($this->getReviewUrl($this->getReviewId())), $html);
        $html = str_replace('__TITLE__', rawurlencode($this->getReviewTitle($this->getReviewId())), $html);
        $html = str_replace('__IMAGEPATH__', $this->getImageUrl(), $html);

        return $html;
    }
}