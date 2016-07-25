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


class AW_AdvancedReviews_Block_Addrss extends Mage_Core_Block_Template
{
    protected function _prepareLayout()
    {
        if (Mage::helper('advancedreviews')->getRssEnabled()) {
            $category = '';
            $product = '';

            switch ($this->getRequest()->getControllerName()) {
                case 'category':
                    $category = $this->getRequest()->getParam('id');
                    break;
                case 'product':
                    $product = $this->getRequest()->getParam('id');
                    break;
            }
            $route = Mage::helper('advancedreviews')->getRssRoute($category, $product);
            $path = $this->getUrl($route);

            $title = 'Advanced Reviews';

            $headBlock = $this->getLayout()->getBlock('head');
            Mage::helper('advancedreviews')->addRss($headBlock, $path, $title);
        }
        return $this;
    }
}