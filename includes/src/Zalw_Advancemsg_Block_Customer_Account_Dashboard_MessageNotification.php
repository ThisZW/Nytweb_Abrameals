<?php

class Zalw_Advancemsg_Block_Customer_Account_Dashboard_MessageNotification extends Mage_Core_Block_Template
{
    public function getNewMessageNotification()
    {
        $customerUserSession =  Mage::getSingleton('customer/session');
	    $customerId = $customerUserSession->getCustomer()->getId();
	    $msgReceived = Mage::getModel('advancemsg/content')->getCollection()
                      ->addFieldToFilter("receiver_type", array("eq" => 'customer'))
			          ->addFieldToFilter("receiver_id", array("eq" => $customerId))
			          ->addFieldToFilter("customer_status", array("eq" => '0'))
			          ->load();
	    $msgCount = $msgReceived->getSize();

	    if ($msgCount == 1 ){
	        $noticeMsg = "You Have"."&nbsp&nbsp".$msgCount."&nbsp&nbsp"."new message";
	    }
	    elseif ($msgCount > 1 ){
	        $noticeMsg = "You Have total"."&nbsp&nbsp".$msgCount."&nbsp&nbsp"."new messages";
	    }else{
		$noticeMsg = '';
	    }
        return $noticeMsg;
    }
}
