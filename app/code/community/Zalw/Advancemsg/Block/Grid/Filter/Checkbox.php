<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the check box in inbox grid
 */
class Zalw_Advancemsg_Block_Grid_Filter_Checkbox extends Mage_Adminhtml_Block_Widget_Grid_Column_Filter_Select
{
    public function getHtml()
    {
        return '<span class="head-massactions">' . parent::getHtml() . '</span>';
    }

    protected function _getOptions()
    {
        return array(
            array(
                'label' => Mage::helper('advancemsg')->__('All'),
                'value' => ''
            ),
            array(
                'label' => Mage::helper('advancemsg')->__('Unread'),
                'value' => 0
            ),
            array(
                'label' => Mage::helper('advancemsg')->__('Read'),
                'value' => 1
            ),
        );
    }

    public function getCondition()
    {
        if ($this->getValue()) {
            return $this->getColumn()->getValue();
        }
        else {
            return array(
                array('neq'=>$this->getColumn()->getValue()),
                array('is'=>new Zend_Db_Expr('NULL'))
            );
        }
        
    }
}
