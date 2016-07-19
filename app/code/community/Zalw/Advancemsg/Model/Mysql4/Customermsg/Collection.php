<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	This .php file Gets the collection of advancemsg_customermsg
 */
class Zalw_Advancemsg_Model_Mysql4_Customermsg_Collection extends  Mage_Core_Model_Mysql4_Collection_Abstract
{
    //function initialses the construct 	
    public function _construct()
    {
        parent::_construct();
        $this->_init('advancemsg/customermsg');
    }
    //function gets the sends	
    public function getInfo($id)
    {
	$this->setConnection($this->getResource()->getReadConnection());
    	$this->getSelect()
    			->from(array('main_table'=>'customermsg'),'*')
    			->where('message_id=?',$id);
    	return $this;
    }	
}
