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


class AW_AdvancedReviews_Model_Caching_Block_Abstract extends Enterprise_PageCache_Model_Container_Abstract
{
    protected $_layout = null;

    protected function _getIdentifier()
    {
        return microtime();
    }

    protected function _renderBlock()
    {
    }

    protected function _saveCache($data, $id, $tags = array(), $lifetime = null)
    {
        return false;
    }

    protected function _getLayout($handlers = array('default'))
    {
        if ($this->_layout === null) {
            $package = Mage::getSingleton('core/design_package');
            $layout = Mage::getModel('core/layout');
            $layout->getUpdate()->addHandle($handlers);
            $layout->getUpdate()->addHandle('STORE_'.Mage::app()->getStore()->getCode());
            $layout->getUpdate()->addHandle(
                'THEME_'.$package->getArea().'_'.$package->getPackageName().'_'.$package->getTheme('layout')
            );
            $layout->getUpdate()->load();
            $layout->generateXml();

            $this->_layout = $layout;
        }
        return $this->_layout;
    }
}