<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Model file of advancemsg_customermsg
 */
/*This class initializes the model of customermsg module*/
class Zalw_Advancemsg_Model_Customermsg extends Mage_Core_Model_Abstract
{
	//function as a constructor    
	public function _construct()
    	{
        parent::_construct();
	//initializes a customermsg model
        $this->_init('advancemsg/customermsg');
    	}
	
	
}
