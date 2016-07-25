<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Resource file of advancemsg_content
 */
class Zalw_Advancemsg_Model_Mysql4_Content extends Mage_Core_Model_Resource_Db_Abstract
{
    protected function _construct()
    {
        $this->_init('advancemsg/content', 'message_id');
    }
}
