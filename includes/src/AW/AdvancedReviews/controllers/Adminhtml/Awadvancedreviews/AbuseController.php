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


class AW_AdvancedReviews_Adminhtml_Awadvancedreviews_AbuseController extends Mage_Adminhtml_Controller_Action
{
    protected function _isAllowed()
    {
        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/advancedreviews/abused_reviews');
    }

    protected function _prepareInit()
    {
        $this->loadLayout()
            ->_setActiveMenu('catalog/reviews_ratings/advancedreviews/abused_reviews')
            ->_addBreadcrumb(
                Mage::helper('advancedreviews')->__('Advanced Reviews'),
                Mage::helper('advancedreviews')->__('Abuse reports')
            );

        if (method_exists($this, '_title')) {
            $this->_title($this->__('Advanced Reviews'))->_title(Mage::helper('advancedreviews')->__('Abuse reports'));
        }

        return $this;
    }

    public function indexAction()
    {
        $this->_prepareInit()->renderLayout();
    }

    public function deleteAbuseAction()
    {
        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('advancedreviews/abuse');
                $model->setId($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')
                        ->__('Report was successfully deleted')
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/');
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('advancedreviews/adminhtml_abuse_grid')->toHtml()
        );
    }
}