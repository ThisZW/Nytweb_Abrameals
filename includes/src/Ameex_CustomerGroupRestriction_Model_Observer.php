<?php
class Ameex_CustomerGroupRestriction_Model_Observer
{
	public function checkoutCustomerRestriction(Varien_Event_Observer $observer) {
		$quote = Mage::getSingleton('checkout/session')->getQuote();
		$_items = $quote->getAllItems();
		$noOfRestrictedProductsInCart = 0;
		$restrictedProductsName = array();
		foreach ($_items as $_item) {
			$groupId = Mage::getSingleton('customer/session')->getCustomerGroupId();
			$_product = Mage::getModel('catalog/product')->loadByAttribute('sku',$_item->getSku());
			$restrictedCustomerGroups = explode(',',$_product->getRestrictGroups());
			if(empty($restrictedCustomerGroups) || ((count($restrictedCustomerGroups) == 1) && ($restrictedCustomerGroups[0] == '0'))) {
				$allowAddToCart = true;
			}
			else if(in_array($groupId,$restrictedCustomerGroups)) {
				$allowAddToCart = false;
			}
			else {
				$allowAddToCart = true;
			}
			if(!$allowAddToCart) {
				$restrictedProductsName[++$noOfRestrictedProductsInCart] = $_product->getName();
			}
        }
        if($noOfRestrictedProductsInCart > 0) {
			$message = "Please remove the following restricted items from your cart:";
			foreach($restrictedProductsName as $key=>$value) {
				$message .= "\n".$key.". ".$value;
			}
			$this->_throwError($observer,$message);
		}
	}
	
	private function _throwError($observer,$message) {
		$controller = $observer->getControllerAction();
		$controller->setFlag('', Mage_Core_Controller_Varien_Action::FLAG_NO_DISPATCH, true);
		$response = array('error' => -1, 'message' => $message);
		return $controller->getResponse()->setBody(Mage::helper('core')->jsonEncode($response));
	}
}
?>
