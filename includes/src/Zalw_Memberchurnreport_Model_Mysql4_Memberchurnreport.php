<?php
/**
 * @category    Zalw
 * @package     Zalw_Memberchurnreport
 */
class Zalw_Memberchurnreport_Model_Mysql4_Memberchurnreport extends Mage_Core_Model_Mysql4_Abstract
{
    protected function _construct()
    {
       
    }
    /**
     * Set main table and idField
     * @return Zalw_Memberchurnreport_Model_Mysql4_Memberchurnreport
     */
    public function init($table, $field = 'id')
    {  
        $this->_init($table, $field);
        return $this;
    }
}