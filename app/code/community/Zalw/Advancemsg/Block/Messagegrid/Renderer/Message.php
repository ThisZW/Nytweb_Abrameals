<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Renders message
 */
class Zalw_Advancemsg_Block_Messagegrid_Renderer_Message
    extends Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
{
    /**
     * Renders messagegrid column
     *
     * @param   Varien_Object $row
     * @return  string
     */
    public function render(Varien_Object $row)
    {
	$grid = Mage::getBlockSingleton('advancemsg/customermsg');
    	$messageId = $row->getData('message_id');
        switch ($row->getData('status')) {
            case 0:
                $html = "<a href='".$grid->getViewUrl($messageId)."' target='_blank'><strong>".$row->getData($this->getColumn()->getIndex())."</strong></a>";
                break;
            case 1:
                $html = "<a href='".$grid->getViewUrl($messageId)."' target='_blank'>".$row->getData($this->getColumn()->getIndex())."</a>";;
                break;
        }
        return $html;
    }
}