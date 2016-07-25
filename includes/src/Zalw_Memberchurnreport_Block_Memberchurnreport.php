<?php
class Zalw_Memberchurnreport_Block_Memberchurnreport extends Mage_Core_Block_Template {

	public function _prepareLayout() {
	    return parent::_prepareLayout();
	}

	public function getMemberchurnreport() {
	    if (!$this->hasData('memberchurnreport')) {
	        $this->setData('memberchurnreport', Mage::registry('memberchurnreport'));
	    }
	    return $this->getData('memberchurnreport');
	}
}