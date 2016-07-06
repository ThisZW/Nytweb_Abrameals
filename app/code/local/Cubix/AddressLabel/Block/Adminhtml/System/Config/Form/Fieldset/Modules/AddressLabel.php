<?php
/**
 * @author     Harshit Jain <support@cubixws.co.uk>
 * @category   Cubix
 * @package    Cubix_AddressLabel
 */
class Cubix_AddressLabel_Block_Adminhtml_System_Config_Form_Fieldset_Modules_AddressLabel extends Mage_Adminhtml_Block_System_Config_Form_Fieldset {
    
    /**
     * Return header html for fieldset
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderHtml($element)
    {
        $html = parent::_getHeaderHtml($element);
        $html = '<img src="' . $this->getSkinUrl('images/cubix_addresslabel.png') . '" alt="Cubix Address Label explanation" />' . $html;
        return $html;
    }
}