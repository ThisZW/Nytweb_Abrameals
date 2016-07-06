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

/**
 * Implements Email Notification of New Reviews
 */
class AW_AdvancedReviews_Helper_Notification extends Mage_Core_Helper_Abstract
{
    /**
     * Path to switch notification option
     *
     * @var String
     */
    const CONFIG_NOTIFICATION_ENABLED_PATH = 'advancedreviews/email_notification/enabled';
    /**
     * Path to sender option
     *
     * @var String
     */
    const CONFIG_NOTIFICATION_SENDER_PATH = 'advancedreviews/email_notification/sender_email_identity';
    /**
     * Path to recipient option
     *
     * @var String
     */
    const CONFIG_NOTIFICATION_RECIPIENT_PATH = 'advancedreviews/email_notification/recipient_email';
    /**
     * Path to email template option
     *
     * @var String
     */
    const CONFIG_NOTIFICATION_EMAIL_TEMPLATE_PATH = 'advancedreviews/email_notification/email_template';

    /**
     * Returns Notification Enabled Option
     *
     * @return boolean
     */
    public function getEnabled()
    {
        return Mage::getStoreConfig(self::CONFIG_NOTIFICATION_ENABLED_PATH);
    }

    /**
     * Send Notification Email
     *
     * @param Varien_Object $review Review Data
     *
     * @return AW_AdvancedReviews_Helper_Notification
     */
    public function sendNotification($review)
    {
        $product = Mage::getModel('catalog/product')->load($review->getEntityPkValue());
        $recipient = Mage::getStoreConfig(self::CONFIG_NOTIFICATION_RECIPIENT_PATH);
        $sender = Mage::getStoreConfig(self::CONFIG_NOTIFICATION_SENDER_PATH);
        if ($recipient && $sender && $this->getEnabled()) {
            try {
                $email = Mage::getModel('core/email_template');
                $email->setDesignConfig(array('area' => 'frontend', 'store' => Mage::app()->getStore()->getId()))
                    ->sendTransactional(
                        Mage::getStoreConfig(self::CONFIG_NOTIFICATION_EMAIL_TEMPLATE_PATH),
                        $sender,
                        $recipient,
                        '',
                        array(
                             'product_name' => $product->getName(),
                             'review_subject' => $review->getTitle(),
                             'review_body' => nl2br($review->getDetail()),
                             'review_id' => $review->getReviewId(),
                             'nickname' => $review->getNickname(),
                             'review_url' => Mage::getUrl(
                                 'adminhtml/catalog_product_review/edit',
                                 array('id' => $review->getReviewId())
                             ),
                        )
                    );
            } catch (Exception $e) {
                Mage::throwException($e->getMessage());
            }
        }
        return $this;
    }
}