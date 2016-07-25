<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Renders date for customer's inbox
 */
class Mage_Adminhtml_Block_Notification_Grid_Renderer_Notice
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
        return '<span class="grid-row-title">' . $row->getTitle() . '</span>'
            . ($row->getDescription() ? '<br />' . $row->getDescription() : '');
    }
}
