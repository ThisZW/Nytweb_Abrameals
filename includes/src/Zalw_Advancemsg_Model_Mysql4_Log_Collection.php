<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	This .php file Gets the collection of advancemsg_log
 */
class Zalw_Advancemsg_Model_Mysql4_Log_Collection extends Mage_Newsletter_Model_Resource_Template_Collection
{
    /**
     * Define resource model and model
     *
     */
    protected function _construct()
    {
        $this->_init('advancemsg/log');
    }
}
