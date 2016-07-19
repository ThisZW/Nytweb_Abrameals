<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Resource file of advancemsg_customermsg
 */
class Zalw_Advancemsg_Model_Mysql4_Customermsg extends Mage_Core_Model_Mysql4_Abstract
{
    /*function initialzes the module ,model and id*/ 	
    public function _construct()
    {    
        
        $this->_init('advancemsg/customermsg', 'message_id');
    }
	
}
