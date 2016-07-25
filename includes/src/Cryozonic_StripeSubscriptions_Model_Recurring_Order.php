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

class Cryozonic_StripeSubscriptions_Model_Recurring_Order
{
    protected $_customer = null;

    protected $_order;
    protected $_originalOrder;
    protected $_storeId;
    protected $_rate;

    public function __construct($params)
    {
        $this->_originalOrder = $order = $params['order'];
        $discount = $params['discount'];

        $this->_storeId = $order->getStoreId();

    	$reservedOrderId = Mage::getSingleton('eav/config')
            ->getEntityType('order')
            ->fetchNewIncrementId($this->_storeId);

        $orderData = $order->getData();
        unset($orderData['entity_id']); // Don't overwrite the existing one
        unset($orderData['protect_code']); // Used in some areas to ensure the order being loaded is the correct one for the guest's cookie value
        unset($orderData['created_at']); // Messes up the order in which it is displayed in the admin sales
        unset($orderData['updated_at']);

        // Set or unset the discount
        if ($order->getCouponCode() && empty($discount))
        {
            $orderData['base_grand_total'] -= $orderData['base_discount_amount'];
            $orderData['grand_total'] -= $orderData['discount_amount'];

            unset($orderData['coupon_code']);
            unset($orderData['base_discount_amount']);
            unset($orderData['discount_amount']);
            unset($orderData['base_discount_inoiced']);
            unset($orderData['discount_invoiced']);
        }

        $this->_order = Mage::getModel('sales/order')
            ->setData($orderData)
            ->setIncrementId($reservedOrderId);

        $this->_rate = $order->getStoreToOrderRate();
    }

    public function getOrder()
    {
        return $this->_order;
    }

    private function getInitialFeeFor($productId)
    {
        $product = Mage::getModel('catalog/product')->load($productId);
        if (empty($product)) return 0;

        $profile = $product->getRecurringProfile();
        if (empty($profile) || empty($profile['init_amount']) || !is_numeric($profile['init_amount'])) return 0;

        return $profile['init_amount'];
    }

    public function setCustomer($customer)
    {
        if ($customer instanceof Mage_Customer_Model_Customer)
            $this->_customer = $customer;

        if (is_numeric($customer))
            $this->_customer = Mage::getModel('customer/customer')->load($customer);

        return $this;
    }

    public function createOrder()
    {
        $transaction = Mage::getModel('core/resource_transaction');

        $store = Mage::getModel('core/store')->load($this->_storeId);
        $convertQuote = Mage::getSingleton('sales/convert_quote');

        // Set the addresses and payment
        if ($this->_customer)
            $this->_order->setCustomer($this->_customer);

        $shippingAddress = Mage::getModel('sales/order_address')->load($this->_originalOrder->getShippingAddressId());
        $quoteShippingAddress = Mage::getModel('sales/quote_address')->setData($shippingAddress->getData());

        $billingAddress = Mage::getModel('sales/order_address')->load($this->_originalOrder->getBillingAddressId());
        $quoteBillingAddress = Mage::getModel('sales/quote_address')->setData($billingAddress->getData());

        $paymentMethod = $this->_originalOrder->getPayment();
        $quotePayment = Mage::getModel('sales/quote_payment')->setData($paymentMethod->getData());

        $this->_order
            ->setBillingAddress($convertQuote->addressToOrderAddress($quoteBillingAddress))
            ->setShippingAddress($convertQuote->addressToOrderAddress($quoteShippingAddress))
            ->setPayment($convertQuote->paymentToOrderPayment($quotePayment));

        // Used as a flag to know that this was not a user generated order
        $this->_order->setRemoteIp('stripe.com');

        // Add order items to the order
        $orderItems = $this->_originalOrder->getAllVisibleItems();
        $parentItem = null;
        $errors = array();
        $items = array();
        foreach ($orderItems as $orderItem)
        {
            $items[] = $item = $this->cloneOrderItem($orderItem);
            Mage::getSingleton('cataloginventory/stock')->registerItemSale($item);

            /**
             * As parent item we should always use the item of first added product
             */
            if (!$parentItem) {
                $parentItem = $item;
            }
            if ($parentItem && $orderItem->getParentProductId()) {
                $item->setParentItem($parentItem);
            }
            /**
             * We specify qty after we know about parent (for stock)
             */
            $item->setQty($item->getQty());

            // Typically we have only one subscription per order so this is ok in this loop
            if ($fee = Mage::helper('cryozonic_stripesubscriptions')->getInitialFeeFor($orderItem->getProductId()))
            {
                // Don't add an initial fee in the email that we will send out later
                $incrementId = $this->_order->getIncrementId();
                Mage::register("recurring_$incrementId", true);
                $this->_order->setGrandTotal($this->_order->getGrandTotal() - $this->_rate * $fee);
                $this->_order->setBaseGrandTotal($this->_order->getBaseGrandTotal() - $fee);
            }

            // collect errors instead of throwing first one
            if ($item->getHasError()) {
                $message = $item->getMessage();
                if (!in_array($message, $errors)) { // filter duplicate messages
                    $errors[] = $message;
                }
            }
        }

        if (!empty($errors))
            Mage::throwException(implode("\n", $errors));

        foreach ($items as $item)
            $this->_order->addItem($item);

        $transaction->addObject($this->_order);
        $transaction->addCommitCallback(array($this->_order, 'save'));
        $transaction->save();

        // Dispatch an event to observers
        Mage::dispatchEvent('sales_order_place_after', array('order' => $this->_order));

        return $this;
    }

    public function setOrderStatus($profile)
    {
        $comment = "Recurring order generated from subscription with ID ".$profile['reference_id'].". ";
        $comment .= "Customer originally subscribed with order #".$profile['increment_id']." on ".$profile['created_at'].". ";
        $comment .= "Changing order status as per Recurring Order Status configuration.";
        $status = Mage::getStoreConfig('payment/cryozonic_stripesubscriptions/recurring_order_status');
        if (empty($status)) $status = Mage_Sales_Model_Order::STATE_COMPLETE;
        $this->_order->addStatusToHistory($status, $comment)->save();
    }

    public function sendEmails()
    {
    	// Send a recurring email
        $sendRecurringEmail = Mage::getStoreConfig('payment/cryozonic_stripesubscriptions/recurring_emails');
        if ($sendRecurringEmail)
        {
            // @todo - Sending custom recurring email templates would be nice - http://excellencemagentoblog.com/blog/2011/09/07/magento-sending-custom-emails/
            $this->_order->sendNewOrderEmail();
        }
    }

    private function cloneOrderItem($item)
    {
        $data = array();
        $fields = array(
            'store_id',
            'product_id',
            'product_type',
            'qty_backordered',
            'total_qty_ordered',
            'qty_ordered',
            'name',
            'sku',
            'price',
            'base_price',
            'original_price',
            'base_original_price',
            'tax_percent',
            'tax_amount',
            'base_tax_amount',
            'tax_invoiced',
            'base_tax_invoiced',
            'row_total',
            'base_row_total',
            'weee_tax_applied',
            'base_weee_tax_disposition',
            'weee_tax_disposition',
            'base_weee_tax_row_disposition',
            'weee_tax_row_disposition',
            'base_weee_tax_applied_amount',
            'base_weee_tax_applied_row_amount',
            'weee_tax_applied_amount',
            'weee_tax_applied_row_amount',
            'product_options'
        );

        foreach ($fields as $field)
            if (isset($item[$field]))
                $data[$field] = $item[$field];

        $clone = Mage::getModel('sales/order_item')
                ->setData($data)
                ->setQuoteItemId(0)
                ->setQuoteParentItemId(NULL);

        return $clone;
    }
}