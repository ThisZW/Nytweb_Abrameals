<?php
/**
 * Invitereferrals_Campaign extension
 * 
 * NOTICE OF LICENSE
 * 
 * This source file is subject to the MIT License
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/mit-license.php
 * 
 * @category       Invitereferrals
 * @package        Invitereferrals_Campaign
 * @copyright      Copyright (c) 2014
 * @license        http://opensource.org/licenses/mit-license.php MIT License
 */
/**
 * Campaign default helper
 *
 * @category    Invitereferrals
 * @package     Invitereferrals_Campaign
 * @author      Ultimate Module Creator
 */
class Invitereferrals_Campaign_Helper_Data extends Mage_Core_Helper_Abstract {
    public function isActive()
    {
        $campaignActive = Mage::getStoreConfig('general/Invitereferrals_campaign/active');
        if(!empty($campaignActive))
            return true;
        else
            return false;
    }
	
	public function debug()
	{
        $debug = Mage::getStoreConfig('general/Invitereferrals_campaign/debug');
		if(!empty($debug))
            return true;
        else
            return false;
	}
    
    public function getKey()
    {
        return Mage::getStoreConfig('general/Invitereferrals_campaign/apikey');
    }
    
	public function getBrandid()
    {
        return Mage::getStoreConfig('general/Invitereferrals_campaign/brandid');
    }
	
    public function getScript()
    {
        $request = Mage::app()->getRequest();
        $module = $request->getModuleName();
        $controller = $request->getControllerName();
        $action = $request->getActionName();
		$flag = false;
		$scriptAppend2='';
		$fname='';
		$emial='';
		$active=$this->isActive();
		
		$enckey = $this->getKey();
		$brandid = $this->getBrandid();
		
		if($active==false)
			return;
		
		
		
		//$script = "<script>var apiKey = '".$this->getKey()."';</script>"."\n";
        if (($module == 'checkout' && $controller == 'onestep' && $action == 'success')
            || ($module == 'checkout' && $controller == 'onepage' && $action == 'success')
            || ($module == 'securecheckout' && $controller == 'index' && $action == 'success')
            || ($module == 'customdownloadable' && $controller == 'onepage' && $action == 'success')
            || ($module == 'onepagecheckout' && $controller == 'index' && $action == 'success')
            || ($module == 'onestepcheckout' && $controller == 'index' && $action == 'success'))
        {
            $order = new Mage_Sales_Model_Order();
            $orderId = Mage::getSingleton('checkout/session')->getLastRealOrderId();
            $order->loadByIncrementId($orderId);    // Load order details
            $order_total = round($order->getGrandTotal(), 2); // Get grand total
            //$order_coupon = $order->getCouponCode();    // Get coupon used
            
			if(!empty($order->getCustomerName()))
            $order_name = $order->getCustomerName(); // Get customer's name
			
			if(!empty($order->getCustomerEmail()))
            $order_email = $order->getCustomerEmail(); // Get customer's email id
		
			//print_r($order->getShippingAddress());die;
			if(is_object($order->getShippingAddress()))
			{
			$order_phone = $order->getShippingAddress()->getTelephone();
			}
			 else
			{
			$order_phone ='';
			}
				
                
            // Call invoiceInvitereferrals function
            $scriptAppend2 = "<img style='position:absolute; visibility:hidden' src='https://www.ref-r.com/campaign/t1/settings?bid_e=".$enckey."&bid=".$brandid."&t=420&event=sale&email=".$order_email."&orderID=".$orderId."&purchaseValue=".$order_total."&fname=".$order_name."&mobile=".$order_phone."' />";
			
			
        }
		
		
		
		if (Mage::getSingleton('customer/session')->isLoggedIn()) {
 
    // Load the customer's data
    $customer = Mage::getSingleton('customer/session')->getCustomer();
     
    $fname=$customer->getName(); // Full Name
	// All other customer data   
    $emial=$customer->getEmail();
    
}

        /*
		$setUserEmail = $customerData['email'];
		$fname = $customerData['firstname'];
		$lname = $customerData['lastname'];
		*/
		
        $script .= "<div id='invtrflfloatbtn'></div>
				<script>
				var invite_referrals = window.invite_referrals || {}; (function() { 
				invite_referrals.auth = { bid_e : '".$enckey."', bid : '".$brandid."', t : '420', email : '".$emial."', mobile : '".$phone."', userParams : {'fname': '".$fname."'}};	
				var script = document.createElement('script');script.async = true;
				script.src = (document.location.protocol == 'https:' ? '//d11yp7khhhspcr.cloudfront.net' : '//cdn.invitereferrals.com') + '/js/invite-referrals-1.0.js';
				var entry = document.getElementsByTagName('script')[0];entry.parentNode.insertBefore(script, entry); })();
				</script>"."\n";
        
		
		return $script.$scriptAppend2;
    }
}
