<?php

class Zalw_Advancemsg_Block_Adminhtml_Notification_Toolbar_MessageNotification extends Mage_Core_Block_Template
{
    public function getNewMessageNotification()
    {
		if(Mage::getSingleton('admin/session')->isAllowed('advancemsg')){
			$customerUserSession = Mage::getSingleton('admin/session');
			$customerId = $customerUserSession->getUser()->getUserId();
			$msgReceived = Mage::getModel('advancemsg/content')->getCollection()
					   ->addFieldToFilter("receiver_type", array("eq" => 'admin'))
					   //->addFieldToFilter("receiver_id", array("eq" => $customerId))
					   ->addFieldToFilter("status", array("eq" => '0'))
					   ->load();
			$msgCount = $msgReceived->getSize();
	
			if ($msgCount == 1){
				$noticeMsg = "You Have"."&nbsp&nbsp".$msgCount."&nbsp&nbsp"."new message";
			}
			elseif ($msgCount > 1){
				$noticeMsg = "You Have total"."&nbsp&nbsp".$msgCount."&nbsp&nbsp"."new messages";
			}else{
			$noticeMsg = '';
			}
			return $noticeMsg;
		}
    }
}
