<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Collection file of advancemsg_content
 */
class Zalw_Advancemsg_Model_Mysql4_Content_Collection extends Mage_Newsletter_Model_Resource_Template_Collection
{
    /**
     * Define resource model and model
     *
     */
    protected function _construct()
    {
        $this->_init('advancemsg/content');
    }
}
