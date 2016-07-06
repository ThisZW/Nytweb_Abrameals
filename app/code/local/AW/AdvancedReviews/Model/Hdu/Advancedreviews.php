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
 * Implements Source for HDU Proto
 */
class AW_AdvancedReviews_Model_Hdu_Advancedreviews extends AW_Helpdeskultimate_Model_Proto_Source_Abstract
{
    /**
     * Returns founded customer or preset entity
     *
     * @param AW_Helpdeskultimate_Model_Proto $proto Proto with sender data
     *
     * @return Varien_Object
     */
    public function getCustomer(AW_Helpdeskultimate_Model_Proto $proto)
    {
        $protoCustomer = $this->_findCustomer($proto->getFromEmail());
        if ($proto->getFromEmail() == Mage::getStoreConfig('trans_email/ident_general/email') || !$protoCustomer) {
            $customer = new Varien_Object(
                array(
                     'email' => Mage::registry('advancedreviews_guest_email'),
                     'name'  => Mage::helper('advancedreviews')->__('Guest'),
                     'id'    => 0,
                )
            );
            return $customer;
        } else {
            return $this->_findCustomer($proto->getFromEmail());
        }
    }
}