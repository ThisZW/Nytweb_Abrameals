<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Renders message
 */
class Zalw_Advancemsg_Block_Messagegrid_Renderer_Messagetitle
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
    	$messagetitle = $row->getData('messagetitle');
        
        return $messagetitle;
    }
}
