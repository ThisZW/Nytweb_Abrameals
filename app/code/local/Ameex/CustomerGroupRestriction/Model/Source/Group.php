<?php
 
class Ameex_CustomerGroupRestriction_Model_Source_Group extends Mage_Eav_Model_Entity_Attribute_Source_Abstract
{
 
    public function getAllOptions()
    {
        $groups = Mage::getResourceModel('customer/group_collection')
            ->addFieldToFilter('customer_group_id', array('gt'=> 0))
            ->load()
            ->toOptionArray();
 
        array_unshift($groups, array(
            'value' => 0,
            'label' => Mage::helper('core')->__('None')
        ));
 
        return $groups;
    }
}
