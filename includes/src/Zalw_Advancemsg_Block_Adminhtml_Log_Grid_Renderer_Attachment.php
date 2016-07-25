<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the message log content
 */
class Zalw_Advancemsg_Block_Adminhtml_Log_Grid_Renderer_Attachment
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
//        $grid = Mage::getBlockSingleton('advancemsg/inbox');
//    	$senderId = $row->getData('sender_id');
//        $senderType = $row->getData('sender_type');
//        if($senderType == 'admin'){
//            $admin = Mage::getModel('admin/user')->load($senderId);
//            $senderName = $admin['firstname']." ".$admin['lastname'];
//	    $senderNameType = $senderName."&nbsp;-&nbsp;".$senderType;
//        }
//        if ($senderType == 'customer'){
//            $customer = Mage::getModel('customer/customer')->load($senderId);
//	    $senderName = $customer['firstname']." ".$customer['lastname'];
//	    $senderNameType = $senderName."&nbsp;-&nbsp;".$senderType;
//            
//        }
//        return $senderNameType;

        $grid = Mage::getBlockSingleton('advancemsg/inbox');
        $attach = $row->getData('attach');
        if($attach == '1'){
        // return "<img widht='16' height='16' src='media/advancemsg/attachment-image.png'>";   
        
        $url = Mage::getBaseUrl('media') . 'advancemsg/attachment-image.png';
        $out = "<img src=". $url ." width='16px' height='16px'/>";
        return $out;
        
        
        }
        

    }
}