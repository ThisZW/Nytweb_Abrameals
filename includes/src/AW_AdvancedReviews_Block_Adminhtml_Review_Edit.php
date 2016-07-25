<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_AdvancedReviews
 * @version    2.3.9
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_AdvancedReviews_Block_Adminhtml_Review_Edit extends Mage_Adminhtml_Block_Review_Edit
{
    public function __construct()
    {
        parent::__construct();

        if ($this->getRequest()->getParam('ret') === 'abuse') {
            $this->_updateButton(
                'back',
                'onclick',
                'setLocation(\'' . $this->getUrl('adminhtml/awadvancedreviews_abuse/') . '\')'
            );

            $deleteUrl = $this->getUrl(
                '*/*/delete',
                array(
                     $this->_objectId => $this->getRequest()->getParam($this->_objectId),
                     'ret'            => 'abuse',
                )
            );
            $confirmationMessage = Mage::helper('review')->__('Are you sure you want to do this?');
            $this->_updateButton(
                'delete',
                'onclick',
                "deleteConfirm('{$confirmationMessage}', '{$deleteUrl}')"
            );
            Mage::register('ret', 'abuse');
        }
    }
}