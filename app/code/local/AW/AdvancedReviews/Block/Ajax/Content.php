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


class AW_AdvancedReviews_Block_Ajax_Content extends Mage_Core_Block_Template
{
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

    public function disableNativeReviewsTab()
    {
        if (Mage::helper('advancedreviews')->getExtDisabled()) {
            return $this;
        }

        $this->getParentBlock()->unsetChild('reviews');
        return $this;
    }

    public function getFilteredAction()
    {
        $url = 'advancedreviews/proscons/checkbyproscons';

        if ($id = $this->getRequest()->getParam('id')) {
            $url .= '/id/' . $id . '/';
        }
        if ($customerId = $this->getCustomerId()) {
            $url .= '/customerId/' . $customerId . '/';
        }
        if ($page = $this->getPage()) {
            $url .= 'p/' . $page . '/';
        }

        return $this->getUrl($url);
    }

    public function getUpdatePagerAction()
    {
        $url = 'advancedreviews/proscons/updatepager';
        return $this->getUrl($url);
    }

    public function getFilteredReviewsAction()
    {
        $url = 'advancedreviews/proscons/getfilteredreviews';
        return $this->getUrl($url);
    }
}