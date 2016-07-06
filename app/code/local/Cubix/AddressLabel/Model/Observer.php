<?php
/**
 * @author     Harshit Jain <support@cubixws.co.uk>
 * @category   Cubix
 * @package    Cubix_AddressLabel
 */
class Cubix_AddressLabel_Model_Observer extends Mage_Core_Model_Abstract {
    
    /**
     * Add address label massaction
     * 
     * @param Varien_Event_Observer $observer
     * @return Cubix_AddressLabel_Model_Observer 
     */
    public function addMassaction($observer) {
        if (!($observer->getEvent()->getBlock() instanceof Mage_Adminhtml_Block_Sales_Order_Grid)) {
            return $this;
        }
        $block = $observer->getEvent()->getBlock();
        $block->getMassactionBlock()->addItem('print_addresslabel', array(
            'label'=> Mage::helper('cubix_addresslabel')->__('Print Address Label'),
            'url'  => Mage::getUrl('addresslabeladmin/adminhtml_pdf/printLabel'),
        ));
    }
}