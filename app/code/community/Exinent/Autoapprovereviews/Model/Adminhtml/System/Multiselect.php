<?php
class Exinent_Autoapprovereviews_Model_Adminhtml_System_Multiselect
{
   protected $_options;

    public function toOptionArray() {
        if (!$this->_options) {
            $this->_options = Mage::getResourceModel('customer/group_collection')
                            ->setRealGroupsFilter()
                            ->loadData()->toOptionArray();
            array_unshift($this->_options, array('value' => '', 'label' => Mage::helper('adminhtml')->__('No Group Selected')));
        }
        return $this->_options;
    }

}
