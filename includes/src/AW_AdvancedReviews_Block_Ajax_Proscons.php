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


class AW_AdvancedReviews_Block_Ajax_Proscons extends Mage_Core_Block_Template
{
    protected $_collection;

    public function __construct()
    {
        $this->setTemplate('advancedreviews/ajax/proscons.phtml');
        parent::__construct();
    }

    protected function _toHtml()
    {
        return $this->renderView();
    }

    public function getReviewsAndFilters()
    {
        $productId = $this->getRequest()->getParam('id');
        $customerId = $this->getRequest()->getParam('customerId');
        $proscons = $this->getRequest()->getParam('proscons');
        $reviews = Mage::getResourceModel('advancedreviews/review')->getReviewsByProscons(
            $proscons, Mage::app()->getStore()->getId(), $productId, $customerId
        );
        return $reviews;
    }

    public function getSelectedFilters()
    {
        return explode(',', $this->getRequest()->getParam('proscons'));
    }
}