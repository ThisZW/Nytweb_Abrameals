<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Login observer
 */
class Zalw_Advancemsg_Model_Observer
{
    //function sends notification to admin if there is any reply in  message log
    public function onadminloginusersuccess($observer)
    {
	$admin_user_session = Mage::getSingleton('admin/session');
        $adminuserId = $admin_user_session->getUser()->getUserId();
	$msgReceived = Mage::getModel('advancemsg/log')->getCollection()
                         ->addFieldToFilter("receiver_type", array("eq" => 'admin'))
			 ->addFieldToFilter("receiver_id", array("eq" => $adminuserId))
			 ->addFieldToFilter("status", array("eq" => '0'))
			 ->load();
	$msgCount = $msgReceived->getSize();
	
	if ($msgCount == 1) {
	    $noticeMsg = "You Have"."&nbsp&nbsp".$msgCount."&nbsp&nbsp"."New Message in your Message Log";
	    Mage::getSingleton('core/session')->addNotice($noticeMsg);
	}
	if ($msgCount > 1) {
	    $noticeMsg = "You Have total"."&nbsp&nbsp".$msgCount."&nbsp&nbsp"."New Messages in your Message Log";
	    Mage::getSingleton('core/session')->addNotice($noticeMsg);
	}
	
    }
    
    //function that appends custom admin notification template to core's admin notification template

    public function addButtonsHtml(Varien_Event_Observer $observer)
    {
      $event = $observer->getEvent();
      $block = $event->getBlock();
      $transport = $event->getTransport();
      if ($block->getNameInLayout() == 'notification_toolbar') {
      $buttons = Mage::app()->getLayout()->createBlock('advancemsg/adminhtml_notification_toolbar_messageNotification', 'advancemsg', array('template'=>'advancemsg/notification/toolbar/messagenotification.phtml'));
        $html = $observer->getEvent()->getTransport()->getHtml();
	$html .= $buttons->toHtml();
	$transport->setHtml($html);
      }
    }

}
