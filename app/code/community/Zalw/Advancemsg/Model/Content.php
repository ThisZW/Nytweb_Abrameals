<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Model file of advancemsg_content
 */
class Zalw_Advancemsg_Model_Content extends Mage_Core_Model_Abstract
{
	const MESSAGE_STATUS_READ = 1;
	const MESSAGE_STATUS_UNREAD = 0;
	const MESSAGE_STATUS_REMOVE = -2;
    protected function _construct()
    {
        $this->_init('advancemsg/content');
    }
    
    protected function _getWrite()
	{
		$resource = Mage::getSingleton('core/resource');
	    $writeConnection = $resource->getConnection('core_write');
	    return $writeConnection;
	}
	public function sendMessage($data = array()) 
	{
		if (count($data) && count($data[0])==8) {
			$this->_getWrite()->insertArray(Mage::getSingleton('core/resource')->getTableName('message_content'),array('template_id', 'message_title', 'message_link', 'message_content','user_id','status','added_at','modified_at'),$data);
		}
	}
	public function checkOwner($messageId, $customerId)
	{
		
		if ($messageId && $customerId) {
			
			$collection = $this->getCollection();
			$collection->addFieldToSelect('message_id')
					   ->addFieldToFilter('message_id',(int)$messageId)
					   ->load();
	        if(count($collection)) {
	        	return true;
	        } else {
	        	return false;
	        }
		}
		return false;
	}
	public function getIdsByCustomerId($customerId)
	{
		$collection = $this->getCollection();
		$collection->addFieldToSelect('message_id')
				   ->addFieldToFilter('user_id',(int)$customerId)
				   ->load();
	   if(count($collection)) {
	   		$ids = array();
	   		foreach ($collection as $c) {
	   			array_push($ids, $c->getMessageId());
	   		}
        	return $ids;
        } else {
        	return array();
        }
	}
	public function getUnreadCount($customerId)
	{
		$collection = $this->getCollection();
		$collection->addFieldToSelect('message_id')
				   ->addFieldToFilter('user_id',(int)$customerId)
				   ->addFieldToFilter('status',array('eq'=>Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_UNREAD))
				   ->load();
	   return count($collection);
	}
	
	public function getTotalCount($customerId) 
	{
		$collection = $this->getCollection();
		$collection->addFieldToSelect('message_id')
			   ->addFieldToFilter('customer_status',array('gt'=>Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_REMOVE));
		$collection->addFieldToFilter("parent_id", array("eq" => '0'));  
                $collection->getSelect()->where("(sender_id='".Mage::getSingleton('customer/session')->getCustomer()->getId()."' AND sender_type  = 'customer') OR (receiver_id='".Mage::getSingleton('customer/session')->getCustomer()->getId()."' AND receiver_type  = 'customer')");
	        
	   return count($collection);
	}
}
