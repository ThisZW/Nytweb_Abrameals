<?php

class Ameex_CustomerGroupRestriction_Model_Product extends Mage_Catalog_Model_Product
{
    public function isSalable()
    {
        Mage::dispatchEvent('catalog_product_is_salable_before', array(
            'product'   => $this
        ));

        $salable = $this->isAvailable();

        $object = new Varien_Object(array(
            'product'    => $this,
            'is_salable' => $salable
        ));
        Mage::dispatchEvent('catalog_product_is_salable_after', array(
            'product'   => $this,
            'salable'   => $object
        ));
        
        $_product = Mage::getModel('catalog/product')->load($this->getId());
	$groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
	$restrictedCustomerGroups = $_product->getRestrictGroups();
	if(empty($restrictedCustomerGroups) || ((count($restrictedCustomerGroups) == 1) && (($restrictedCustomerGroups[0] == '0') || ($restrictedCustomerGroups[0] == '')))) {
            $allowAddToCart = true;
	}
	else if(in_array($groupId,$restrictedCustomerGroups)) {
            $allowAddToCart = false;
	}
	else {
            $allowAddToCart = true;
	}
        if(!$allowAddToCart) {
            $object->setIsSalable($allowAddToCart);
        }
        
        return $object->getIsSalable();
    }
}

?>
