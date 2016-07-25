<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	This .php file Gets the collection of advancemsg_template
 */
class Zalw_Advancemsg_Model_Mysql4_Template_Collection extends Mage_Newsletter_Model_Resource_Template_Collection
{
    /**
     * Define resource model and model
     *
     */
    protected function _construct()
    {
        $this->_init('advancemsg/template');
    }

    /**
     * Load only actual template
     *
     * @return Mage_Newsletter_Model_Resource_Template_Collection
     */
    public function useOnlyActual()
    {
        $this->addFieldToFilter('template_actual', 1);

        return $this;
    }

    /**
     * Returns options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_toOptionArray('template_id', 'template_code');
    }
}
