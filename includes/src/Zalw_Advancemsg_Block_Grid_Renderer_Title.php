<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Renders message title for customer's inbox 
 */
class Zalw_Advancemsg_Block_Grid_Renderer_Title
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
    	$grid = Mage::getBlockSingleton('advancemsg/inbox');
    	$messageId = $row->getData('message_id');
        switch ($row->getData('customer_status')) {
            case 0:
                $html = "<a href='".$grid->getViewUrl($messageId)."' target='_blank'><strong>".$row->getData($this->getColumn()->getIndex())."</strong></a>";
                break;
            case 1:
                $html = "<a href='".$grid->getViewUrl($messageId)."' target='_blank'>".$row->getData($this->getColumn()->getIndex())."</a>";;
                break;
	    case -2:
                $html = "<a href='".$grid->getViewUrl($messageId)."' target='_blank'><strong>".$row->getData($this->getColumn()->getIndex())."<strong></a>";;
                break;
	    case 3:
                $html = "<a href='".$grid->getViewUrl($messageId)."' target='_blank'><strong>".$row->getData($this->getColumn()->getIndex())."<strong></a>";;
                break;
	    case 4:
                $html = "<a href='".$grid->getViewUrl($messageId)."' target='_blank'><strong>".$row->getData($this->getColumn()->getIndex())."<strong></a>";;
                break;
        }
        return $html;
    }
}
