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

/**
 * Admin configuration helper. Check HDU Link value
 */
class AW_AdvancedReviews_Model_System_Config_Backend_Hducheck extends Mage_Core_Model_Config_Data
{
    /**
     * Check HDU active
     *
     * @return boolean
     */
    protected function _isHDUActive()
    {
        return Mage::helper('advancedreviews/hdu')->isHDUActive();
    }

    /**
     * Check HDU, if it active, we can switch "Yes", else only "No"
     *
     * @return AW_AdvancedReviews_Model_System_Config_Backend_Hducheck
     */
    protected function _beforeSave()
    {
        $value = $this->getValue();
        if (!$this->_isHDUActive() && $value) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('advancedreviews')->__(
                    'Option "Automatically create HDU ticket from incoming reviews" requires Help Desk Ultimate'
                )
            );
            $value = 0;
        }
        $this->setValue($value);
        return $this;
    }
}