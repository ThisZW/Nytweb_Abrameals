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


class AW_AdvancedReviews_HelpfulnessController extends Mage_Core_Controller_Front_Action
{
    public function postAction()
    {
        $actionName = $this->getRequest()->getParam('actionName');
        $reviewId = $this->getRequest()->getParam('reviewId');
        $real = $this->getRequest()->getParam('real');
        $session = Mage::getSingleton('core/session');

        if ($real) {
            if (
                Mage::helper('advancedreviews')->confAllowOnlyLoggedToVote()
                && !Mage::helper('advancedreviews')->isUserLogged()
            ) {
                $session->addError($this->__('You need log in to vote review'));
            } elseif (Mage::helper('advancedreviews')->isHelpfulnessRegistered($reviewId)) {
                $session->addError($this->__('You can vote only once for the same review'));
            } elseif ($actionName == 'Yes') {
                $session->addSuccess($this->__('Thank you for your vote'));
                Mage::helper('advancedreviews')->yesHelpfulness($reviewId);
                Mage::helper('advancedreviews')->registerHelpfulness($reviewId);
            } elseif ($actionName == 'No') {
                $session->addSuccess($this->__('Thank you for your vote'));
                Mage::helper('advancedreviews')->noHelpfulness($reviewId);
                Mage::helper('advancedreviews')->registerHelpfulness($reviewId);
            }
        }
        $this->_redirectReferer(Mage::helper('advancedreviews')->getReviewsBackUrl());
    }

    public function postajaxAction()
    {
        $actionName = $this->getRequest()->getParam('actionName');
        $reviewId = $this->getRequest()->getParam('reviewId');

        if (
            Mage::helper('advancedreviews')->confAllowOnlyLoggedToVote()
            && !Mage::helper('advancedreviews')->isUserLogged()
        ) {
            return $this->getResponse()->setBody(
                Zend_Json::encode(array('type' => 'error', 'message' => $this->__('You need log in to vote review')))
            );
        } elseif (Mage::helper('advancedreviews')->isHelpfulnessRegistered($reviewId)) {
            return $this->getResponse()->setBody(
                Zend_Json::encode(
                    array('type' => 'error', 'message' => $this->__('You can vote only once for the same review'))
                )
            );
        } elseif ($actionName == 'Yes') {
            Mage::helper('advancedreviews')->yesHelpfulness($reviewId);
            Mage::helper('advancedreviews')->registerHelpfulness($reviewId);
            return $this->getResponse()->setBody(
                Zend_Json::encode(array('type' => 'success', 'message' => $this->__('Thank you for your vote')))
            );
        } elseif ($actionName == 'No') {
            Mage::helper('advancedreviews')->noHelpfulness($reviewId);
            Mage::helper('advancedreviews')->registerHelpfulness($reviewId);
            return $this->getResponse()->setBody(
                Zend_Json::encode(array('type' => 'success', 'message' => $this->__('Thank you for your vote')))
            );
        }
    }
}