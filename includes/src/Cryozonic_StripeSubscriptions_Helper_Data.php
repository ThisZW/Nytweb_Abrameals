<?php
/**
 * Cryozonic
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Single Domain License
 * that is available through the world-wide-web at this URL:
 * http://cryozonic.com/licenses/stripe.html
 * If you are unable to obtain it through the world-wide-web, 
 * please send an email to info@cryozonic.com so we can send 
 * you a copy immediately.
 *
 * @category   Cryozonic
 * @package    Cryozonic_StripeSubscriptions
 * @copyright  Copyright (c) Cryozonic Ltd (http://cryozonic.com)
 */

class Cryozonic_StripeSubscriptions_Helper_Data extends Mage_Payment_Helper_Data
{
    private function convertMultiCurrency($discount)
    {
        if (empty($discount) || !is_numeric($discount)) return 0;

        $quote = Mage::getSingleton('checkout/session')->getQuote();
        $rate = $quote->getStoreToQuoteRate();

        return $discount * $rate;
    }

    public function getDiscountAmountFor($couponCode, $regularPayment)
    {
        $coupon = Mage::getModel('salesrule/coupon')->load($couponCode, 'code');
        $rule = Mage::getModel('salesrule/rule')->load($coupon->getRuleId());
        $discount = $rule->getDiscountAmount();
        $action = $rule->getSimpleAction();

        if ($action == 'by_percent')
        {
            return $regularPayment * $discount / 100;
        }
        else if ($action == 'by_fixed')
        {
            return $this->convertMultiCurrency($discount);
        }
        else
        {
            // Other magento discount rules are not supported
            return 0;
        }
    }

    // This is shared between the email totals and the admin totals
    // Magento doesn't add initial fees for us when it comes to recurring profiles :-(
    public function addInitialFeeTo(&$totalsBlock)
    {
        if (isset($totalsBlock->_totals['initial'])) return;

        $order = $totalsBlock->getSource();

        // Don't add an initial fee in the email if this is a recurring order
        $incrementId = $order->getIncrementId();
        if (Mage::registry("recurring_$incrementId"))
            return Mage::unregister("recurring_$incrementId");

        $items = $order->getAllVisibleItems();
        foreach ($items as $item)
        {
            $product = $item->getProduct(); // Magento 1.9
            if (!$product)
                $product = Mage::getModel('catalog/product')->load($item->getProductId());

            if ($product->getIsRecurring())
            {
                $profile = $product->getRecurringProfile();
                if ($profile)
                {
                    // Add the initial fee as a total
                    $initAmount = $profile['init_amount'] * $order->getStoreToOrderRate();
                    if (is_numeric($initAmount) && $initAmount > 0)
                    {
                        $fee = new Varien_Object(array(
                            'code'  => 'initial',
                            'field' => 'initial_amount',
                            'value' => $initAmount,
                            'base_value'=> $profile['init_amount'],
                            'label' => $totalsBlock->__('Initial Fee')
                        ));

                        $totalsBlock->addTotalBefore($fee, 'shipping');
                    }
                }
            }
        }
    }

    public function addInitialFeeToInvoice(&$totalsBlock)
    {
        if (isset($totalsBlock->_totals['initial'])) return;

        $invoice = $totalsBlock->getSource();
        $order = $invoice->getOrder();

        // Don't add an initial fee in the email if this is a recurring order
        $incrementId = $order->getIncrementId();
        if (Mage::registry("recurring_$incrementId"))
            return Mage::unregister("recurring_$incrementId");

        $items = $invoice->getAllItems();
        foreach ($items as $item)
        {
            $product = $item->getProduct(); // Magento 1.9
            if (!$product)
                $product = Mage::getModel('catalog/product')->load($item->getProductId());

            if ($product->getIsRecurring())
            {
                $profile = $product->getRecurringProfile();
                if ($profile)
                {
                    // Add the initial fee as a total
                    $initAmount = $profile['init_amount'] * $order->getStoreToOrderRate();
                    if (is_numeric($initAmount) && $initAmount > 0)
                    {
                        $fee = new Varien_Object(array(
                            'code'  => 'initial',
                            'field' => 'initial_amount',
                            'value' => $initAmount,
                            'base_value'=> $profile['init_amount'],
                            'label' => $totalsBlock->__('Initial Fee')
                        ));

                        $totalsBlock->addTotalBefore($fee, 'shipping');
                    }
                }
            }
        }
    }

    public function getInitialFeeFor($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        if (empty($product)) return 0;

        $profile = $product->getRecurringProfile();
        if (empty($profile) || empty($profile['init_amount']) || !is_numeric($profile['init_amount'])) return 0;

        return $profile['init_amount'];
    }

    public function hasRecurringProducts($items)
    {
        foreach ($items as $item)
        {
            $product = $item->getProduct(); // Magento 1.9
            if (!$product)
                $product = Mage::getModel('catalog/product')->load($item->getProductId());

            if ($product->getIsRecurring())
                return true;
        }

        return false;
    }

    public function isRecurringOrder($order)
    {
        return ($order->getRemoteIp() == 'stripe.com');
    }

    public function invoice($order)
    {
        $invoice = Mage::getModel('sales/service_order', $order)->prepareInvoice();
        $invoice->setGrandTotal($order->getGrandTotal());
        $invoice->setBaseGrandTotal($order->getBaseGrandTotal());
        $invoice->setRequestedCaptureCase(Mage_Sales_Model_Order_Invoice::CAPTURE_OFFLINE);
        $invoice->capture()->register();
        $transactionSave = Mage::getModel('core/resource_transaction')
            ->addObject($invoice)
            ->addObject($invoice->getOrder());
        $transactionSave->save();
    }

    public function setCustomerGroup($groupId, $customerId = null)
    {
        if (is_numeric($groupId) && $groupId > 0)
        {
            try
            {
                // This works only from the front-end. Subscriptions cannot be created from the back office.
                if (is_numeric($customerId) && $customerId > 0)
                    $magentoCustomer = Mage::getModel('customer/customer')->load($customerId);
                else
                    $magentoCustomer = Mage::getSingleton('customer/session')->getCustomer();

                if ($magentoCustomer && $magentoCustomer->getEmail()) // Email is not available on guest checkout registrations
                {
                    $magentoCustomer->setGroupId($groupId);
                    $magentoCustomer->save();
                }
            }
            catch (Exception $e)
            {
                $this->log('Could not set customer group: '.$e->getMessage());
            }
        }
    }
}
