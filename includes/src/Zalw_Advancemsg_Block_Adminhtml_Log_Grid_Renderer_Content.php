<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the message log content
 */
class Zalw_Advancemsg_Block_Adminhtml_Log_Grid_Renderer_Content
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders grid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
        return "<div style='width:200px;max-height:200px;overflow:scroll;'>".$row->getData($this->getColumn()->getIndex())."</div>";
    }
}
