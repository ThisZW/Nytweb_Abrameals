<?php

class Exinent_Autoapprovereviews_Helper_Data extends Mage_Core_Helper_Abstract
{   

const XML_REVIEW_REG_ALLOW = 'autoapprovereviews_settings/autoapprovegroup/autoapprove_enabled';
const XML_REVIEW_CUSTOMER_LOGGEDIN = 'autoapprovereviews_settings/autoapprovegroup/login_review';
public function isAutoApproveEnabled() {

return Mage::getStoreConfigFlag(self::XML_REVIEW_REG_ALLOW);

}

public function customerGroup() {
    
    $groups = explode(',', Mage::getStoreConfig(self::XML_REVIEW_CUSTOMER_LOGGEDIN));

return $groups;

}
}
