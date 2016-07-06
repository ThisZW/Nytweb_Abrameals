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

class Cryozonic_StripeSubscriptions_WebhooksController extends Mage_Core_Controller_Front_Action
{
    private function hasRecurringProducts($order)
    {
        $items = $order->getAllVisibleItems();
        foreach ($items as $item) {
            if ($item->getData('is_nominal'))
                return true;
        }
        return false;
    }

    private function reOrder($profile, $discount = null)
    {
        $orderId = $profile['increment_id'];
        $order = Mage::getModel('sales/order')->loadByIncrementId($orderId);

        try {
            if ($this->hasRecurringProducts($order))
            {
                $params = array('order' => $order, 'discount' => $discount);
                $recurringOrder = Mage::getModel('cryozonic_stripesubscriptions/recurring_order', $params);
                $recurringOrder->setCustomer($order->getCustomerId());
                $recurringOrder->createOrder();
                Mage::helper('cryozonic_stripesubscriptions')->invoice($recurringOrder->getOrder());
                $recurringOrder->setOrderStatus($profile);
                $recurringOrder->sendEmails();

                // $queue = new Mage_Core_Model_Email_Queue();
                // $queue->send();
            }
        }
        catch (Exception $e)
        {
            Mage::log($e->getMessage());
        }
    }

    private function getProfileByReferenceId($referenceId)
    {
        if (empty($referenceId))
            return null;

        $resource = Mage::getSingleton('core/resource');
        $connection = $resource->getConnection('core_read');
        $tablePrefix = (string)Mage::getConfig()->getTablePrefix();
        $query = $connection->select()
            ->from($tablePrefix.'sales_recurring_profile', array('*'))
            ->joinLeft(array('o' => $tablePrefix.'sales_recurring_profile_order'), $tablePrefix.'sales_recurring_profile.profile_id = o.profile_id', array('order_id'))
            ->joinLeft(array('s' => $tablePrefix.'sales_flat_order'), 'order_id = s.entity_id', array('increment_id'))
            ->where('reference_id=?', $referenceId);

        return $connection->fetchRow($query);
    }

    private function setCustomerGroup($customerId, $groupId)
    {
        if (!is_numeric($customerId) || $customerId <= 0)
            return;

        if (is_numeric($groupId) && $groupId > 0)
        {
            try
            {
                $magentoCustomer = Mage::getModel('customer/customer')->load($customerId);
                if ($magentoCustomer)
                {
                    $magentoCustomer->setGroupId($groupId);
                    $magentoCustomer->save();
                }
            }
            catch (Exception $e)
            {
                Mage::log('Could not set customer group: '.$e->getMessage());
            }
        }
    }

    private function minutesFromNow($strTime)
    {
        $time = strtotime($strTime);
        $now = time();
        return round(abs($now - $time) / 60,0);
    }

    private function getCouponID($event)
    {
        try
        {
            $discount = $event->data->object->discount;
            if (!empty($discount) && !empty($discount->coupon) && !empty($discount->coupon->id))
                return $discount->coupon->id;
        }
        catch (Exception $e)
        {
            Mage::log($e->getMessage());
        }
        return false;
    }

    public function indexAction()
    {
        // return $this->reOrder(array(
        //     'increment_id' => '145001143',
        //     'reference_id' => 'sub_7NbhqYSsOYyPeV',
        //     'created_at' => '2015-11-19 11:21:43'
        // ), 'asd');

        // Retrieve the request's body and parse it as JSON
        $body = @file_get_contents('php://input');
        $event = json_decode($body);
        if (!empty($event->data->object->lines->data[0]->id))
        {
            $id = $event->data->object->lines->data[0]->id;
            $profile = $this->getProfileByReferenceId($id);
            if (empty($profile['created_at']))
                return;

            // The first time that the subscription is created, an invoice.payment_succeeded is created.
            // Ignore this event by checking it's creation time
            $age = $this->minutesFromNow($profile['created_at']);
            if ($age < 3) return;

            switch ($event->type)
            {
                case 'invoice.payment_succeeded':
                    $discount = $event->data->object->discount;
                    // Create a new order
                    if (!empty($profile) && !empty($profile['increment_id']))
                        $this->reOrder($profile, $discount);
                    break;
                case 'invoice.payment_failed':
                    // Switch the customer to a different group
                    $group = Mage::getStoreConfig("payment/cryozonic_stripesubscriptions/failed_payments_group");
                    $this->setCustomerGroup($profile['customer_id'], $group);
                    break;
                default:
                    break;
            }
        }
    }
}