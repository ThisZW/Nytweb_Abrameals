<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Model file of advancemsg_log
 */
class Zalw_Advancemsg_Model_Log extends Mage_Core_Model_Abstract
{
	protected function _construct()
	{
		$this->_init('advancemsg/log');
	}
	protected function _getWrite()
	{
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core_write');
		return $writeConnection;
	}
	public function addLog($data = array()) 
	{
		if (count($data) && count($data[0])==7) {
			$this->_getWrite()->insertArray(Mage::getSingleton('core/resource')->getTableName('message_log'),array('template_id', 'template_name', 'message_title', 'message_link', 'message_content','user_num', 'log_at'),$data);
		}
	}
}
