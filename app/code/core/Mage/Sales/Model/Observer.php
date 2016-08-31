<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Sales
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


/**
 * Sales observer
 *
 * @category   Mage
 * @package    Mage_Sales
 * @author     Magento Core Team <core@magentocommerce.com>
 */
class Mage_Sales_Model_Observer
{
    /**
     * Expire quotes additional fields to filter
     *
     * @var array
     */
    protected $_expireQuotesFilterFields = array();

    /**
     * Clean expired quotes (cron process)
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function cleanExpiredQuotes($schedule)
    {
        Mage::dispatchEvent('clear_expired_quotes_before', array('sales_observer' => $this));

        $lifetimes = Mage::getConfig()->getStoresConfigByPath('checkout/cart/delete_quote_after');
        foreach ($lifetimes as $storeId=>$lifetime) {
            $lifetime *= 86400;

            /** @var $quotes Mage_Sales_Model_Mysql4_Quote_Collection */
            $quotes = Mage::getModel('sales/quote')->getCollection();

            $quotes->addFieldToFilter('store_id', $storeId);
            $quotes->addFieldToFilter('updated_at', array('to'=>date("Y-m-d", time()-$lifetime)));
            $quotes->addFieldToFilter('is_active', 0);

            foreach ($this->getExpireQuotesAdditionalFilterFields() as $field => $condition) {
                $quotes->addFieldToFilter($field, $condition);
            }

            $quotes->walk('delete');
        }
        return $this;
    }

    /**
     * Retrieve expire quotes additional fields to filter
     *
     * @return array
     */
    public function getExpireQuotesAdditionalFilterFields()
    {
        return $this->_expireQuotesFilterFields;
    }

    /**
     * Set expire quotes additional fields to filter
     *
     * @param array $fields
     * @return Mage_Sales_Model_Observer
     */
    public function setExpireQuotesAdditionalFilterFields(array $fields)
    {
        $this->_expireQuotesFilterFields = $fields;
        return $this;
    }

    /**
     * When deleting product, substract it from all quotes quantities
     *
     * @throws Exception
     * @param Varien_Event_Observer
     * @return Mage_Sales_Model_Observer
     */
    public function substractQtyFromQuotes($observer)
    {
        $product = $observer->getEvent()->getProduct();
        Mage::getResourceSingleton('sales/quote')->substractProductFromQuotes($product);
        return $this;
    }

    /**
     * When applying a catalog price rule, make related quotes recollect on demand
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Sales_Model_Observer
     */
    public function markQuotesRecollectOnCatalogRules($observer)
    {
        $product = $observer->getEvent()->getProduct();

        if (is_numeric($product)) {
            $product = Mage::getModel("catalog/product")->load($product);
        }
        if ($product instanceof Mage_Catalog_Model_Product) {
            $childrenProductList = Mage::getSingleton('catalog/product_type')->factory($product)
                ->getChildrenIds($product->getId(), false);

            $productIdList = array($product->getId());
            foreach ($childrenProductList as $groupData) {
                $productIdList = array_merge($productIdList, $groupData);
            }
        } else {
            $productIdList = null;
        }

        Mage::getResourceSingleton('sales/quote')->markQuotesRecollectByAffectedProduct($productIdList);
        return $this;
    }

    /**
     * Catalog Product After Save (change status process)
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Sales_Model_Observer
     */
    public function catalogProductSaveAfter(Varien_Event_Observer $observer)
    {
        $product = $observer->getEvent()->getProduct();
        if ($product->getStatus() == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return $this;
        }

        Mage::getResourceSingleton('sales/quote')->markQuotesRecollect($product->getId());

        return $this;
    }

    /**
     * Catalog Mass Status update process
     *
     * @param Varien_Event_Observer $observer
     * @return Mage_Sales_Model_Observer
     */
    public function catalogProductStatusUpdate(Varien_Event_Observer $observer)
    {
        $status     = $observer->getEvent()->getStatus();
        if ($status == Mage_Catalog_Model_Product_Status::STATUS_ENABLED) {
            return $this;
        }
        $productId  = $observer->getEvent()->getProductId();
        Mage::getResourceSingleton('sales/quote')->markQuotesRecollect($productId);

        return $this;
    }

    /**
     * Refresh sales order report statistics for last day
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function aggregateSalesReportOrderData($schedule)
    {
        Mage::app()->getLocale()->emulate(0);
        $currentDate = Mage::app()->getLocale()->date();
        $date = $currentDate->subHour(25);
        Mage::getResourceModel('sales/report_order')->aggregate($date);
        Mage::app()->getLocale()->revert();
        return $this;
    }

    /**
     * Refresh sales shipment report statistics for last day
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function aggregateSalesReportShipmentData($schedule)
    {
        Mage::app()->getLocale()->emulate(0);
        $currentDate = Mage::app()->getLocale()->date();
        $date = $currentDate->subHour(25);
        Mage::getResourceModel('sales/report_shipping')->aggregate($date);
        Mage::app()->getLocale()->revert();
        return $this;
    }

    /**
     * Refresh sales invoiced report statistics for last day
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function aggregateSalesReportInvoicedData($schedule)
    {
        Mage::app()->getLocale()->emulate(0);
        $currentDate = Mage::app()->getLocale()->date();
        $date = $currentDate->subHour(25);
        Mage::getResourceModel('sales/report_invoiced')->aggregate($date);
        Mage::app()->getLocale()->revert();
        return $this;
    }

    /**
     * Refresh sales refunded report statistics for last day
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function aggregateSalesReportRefundedData($schedule)
    {
        Mage::app()->getLocale()->emulate(0);
        $currentDate = Mage::app()->getLocale()->date();
        $date = $currentDate->subHour(25);
        Mage::getResourceModel('sales/report_refunded')->aggregate($date);
        Mage::app()->getLocale()->revert();
        return $this;
    }

    /**
     * Refresh bestsellers report statistics for last day
     *
     * @param Mage_Cron_Model_Schedule $schedule
     * @return Mage_Sales_Model_Observer
     */
    public function aggregateSalesReportBestsellersData($schedule)
    {
        Mage::app()->getLocale()->emulate(0);
        $currentDate = Mage::app()->getLocale()->date();
        $date = $currentDate->subHour(25);
        Mage::getResourceModel('sales/report_bestsellers')->aggregate($date);
        Mage::app()->getLocale()->revert();
        return $this;
    }

    /**
     * Add the recurring profile form when editing a product
     *
     * @param Varien_Event_Observer $observer
     */
    public function prepareProductEditFormRecurringProfile($observer)
    {
        // replace the element of recurring payment profile field with a form
        $profileElement = $observer->getEvent()->getProductElement();
        $block = Mage::app()->getLayout()->createBlock('sales/adminhtml_recurring_profile_edit_form',
            'adminhtml_recurring_profile_edit_form')->setParentElement($profileElement)
            ->setProductEntity($observer->getEvent()->getProduct());
        $observer->getEvent()->getResult()->output = $block->toHtml();

        // make the profile element dependent on is_recurring
        $dependencies = Mage::app()->getLayout()->createBlock('adminhtml/widget_form_element_dependence',
            'adminhtml_recurring_profile_edit_form_dependence')->addFieldMap('is_recurring', 'product[is_recurring]')
            ->addFieldMap($profileElement->getHtmlId(), $profileElement->getName())
            ->addFieldDependence($profileElement->getName(), 'product[is_recurring]', '1')
            ->addConfigOptions(array('levels_up' => 2));
        $observer->getEvent()->getResult()->output .= $dependencies->toHtml();
    }

    /**
     * Block admin ability to use customer billing agreements
     *
     * @param Varien_Event_Observer $observer
     */
    public function restrictAdminBillingAgreementUsage($observer)
    {
        $methodInstance = $observer->getEvent()->getMethodInstance();
        if (!($methodInstance instanceof Mage_Sales_Model_Payment_Method_Billing_AgreementAbstract)) {
            return;
        }
        if (!Mage::getSingleton('admin/session')->isAllowed('sales/order/actions/use')) {
            $observer->getEvent()->getResult()->isAvailable = false;
        }
    }

    /**
     * Set new customer group to all his quotes
     *
     * @param  Varien_Event_Observer $observer
     * @return Mage_Sales_Model_Observer
     */
    public function customerSaveAfter(Varien_Event_Observer $observer)
    {
        /** @var $customer Mage_Customer_Model_Customer */
        $customer = $observer->getEvent()->getCustomer();

        if ($customer->getGroupId() !== $customer->getOrigData('group_id')) {
            /**
             * It is needed to process customer's quotes for all websites
             * if customer accounts are shared between all of them
             */
            $websites = (Mage::getSingleton('customer/config_share')->isWebsiteScope())
                ? array(Mage::app()->getWebsite($customer->getWebsiteId()))
                : Mage::app()->getWebsites();

            /** @var $quote Mage_Sales_Model_Quote */
            $quote = Mage::getSingleton('sales/quote');

            foreach ($websites as $website) {
                $quote->setWebsite($website);
                $quote->loadByCustomer($customer);

                if ($quote->getId()) {
                    $quote->setCustomerGroupId($customer->getGroupId());
                    $quote->collectTotals();
                    $quote->save();
                }
            }
        }

        return $this;
    }

    /**
     * Set Quote information about MSRP price enabled
     *
     * @param Varien_Event_Observer $observer
     */
    public function setQuoteCanApplyMsrp(Varien_Event_Observer $observer)
    {
        /** @var $quote Mage_Sales_Model_Quote */
        $quote = $observer->getEvent()->getQuote();

        $canApplyMsrp = false;
        if (Mage::helper('catalog')->isMsrpEnabled()) {
            foreach ($quote->getAllAddresses() as $adddress) {
                if ($adddress->getCanApplyMsrp()) {
                    $canApplyMsrp = true;
                    break;
                }
            }
        }

        $quote->setCanApplyMsrp($canApplyMsrp);
    }

    /**
     * Add VAT validation request date and identifier to order comments
     *
     * @param Varien_Event_Observer $observer
     * @return null
     */
    public function addVatRequestParamsOrderComment(Varien_Event_Observer $observer)
    {
        /** @var $orderInstance Mage_Sales_Model_Order */
        $orderInstance = $observer->getOrder();
        /** @var $orderAddress Mage_Sales_Model_Order_Address */
        $orderAddress = $this->_getVatRequiredSalesAddress($orderInstance);
        if (!($orderAddress instanceof Mage_Sales_Model_Order_Address)) {
            return;
        }

        $vatRequestId = $orderAddress->getVatRequestId();
        $vatRequestDate = $orderAddress->getVatRequestDate();
        if (is_string($vatRequestId) && !empty($vatRequestId) && is_string($vatRequestDate)
            && !empty($vatRequestDate)
        ) {
            $orderHistoryComment = Mage::helper('customer')->__('VAT Request Identifier')
                . ': ' . $vatRequestId . '<br />' . Mage::helper('customer')->__('VAT Request Date')
                . ': ' . $vatRequestDate;
            $orderInstance->addStatusHistoryComment($orderHistoryComment, false);
        }
    }

    /**
     * Retrieve sales address (order or quote) on which tax calculation must be based
     *
     * @param Mage_Core_Model_Abstract $salesModel
     * @param Mage_Core_Model_Store|string|int|null $store
     * @return Mage_Customer_Model_Address_Abstract|null
     */
    protected function _getVatRequiredSalesAddress($salesModel, $store = null)
    {
        $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType($store);
        $requiredAddress = null;
        switch ($configAddressType) {
            case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                $requiredAddress = $salesModel->getShippingAddress();
                break;
            default:
                $requiredAddress = $salesModel->getBillingAddress();
        }
        return $requiredAddress;
    }

    /**
     * Retrieve customer address (default billing or default shipping) ID on which tax calculation must be based
     *
     * @param Mage_Customer_Model_Customer $customer
     * @param Mage_Core_Model_Store|string|int|null $store
     * @return int|string
     */
    protected function _getVatRequiredCustomerAddress(Mage_Customer_Model_Customer $customer, $store = null)
    {
        $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType($store);
        $requiredAddress = null;
        switch ($configAddressType) {
            case Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING:
                $requiredAddress = $customer->getDefaultShipping();
                break;
            default:
                $requiredAddress = $customer->getDefaultBilling();
        }
        return $requiredAddress;
    }

    /**
     * Handle customer VAT number if needed on collect_totals_before event of quote address
     *
     * @param Varien_Event_Observer $observer
     */
    public function changeQuoteCustomerGroupId(Varien_Event_Observer $observer)
    {
        /** @var $addressHelper Mage_Customer_Helper_Address */
        $addressHelper = Mage::helper('customer/address');

        $quoteAddress = $observer->getQuoteAddress();
        $quoteInstance = $quoteAddress->getQuote();
        $customerInstance = $quoteInstance->getCustomer();
        $isDisableAutoGroupChange = $customerInstance->getDisableAutoGroupChange();

        $storeId = $customerInstance->getStore();

        $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType($storeId);

        // When VAT is based on billing address then Magento have to handle only billing addresses
        $additionalBillingAddressCondition = ($configAddressType == Mage_Customer_Model_Address_Abstract::TYPE_BILLING)
            ? $configAddressType != $quoteAddress->getAddressType() : false;
        // Handle only addresses that corresponds to VAT configuration
        if (!$addressHelper->isVatValidationEnabled($storeId) || $additionalBillingAddressCondition) {
            return;
        }

        /** @var $customerHelper Mage_Customer_Helper_Data */
        $customerHelper = Mage::helper('customer');

        $customerCountryCode = $quoteAddress->getCountryId();
        $customerVatNumber = $quoteAddress->getVatId();

        if ((empty($customerVatNumber) || !Mage::helper('core')->isCountryInEU($customerCountryCode))
            && !$isDisableAutoGroupChange
        ) {
            $groupId = ($customerInstance->getId()) ? $customerHelper->getDefaultCustomerGroupId($storeId)
                : Mage_Customer_Model_Group::NOT_LOGGED_IN_ID;

            $quoteAddress->setPrevQuoteCustomerGroupId($quoteInstance->getCustomerGroupId());
            $customerInstance->setGroupId($groupId);
            $quoteInstance->setCustomerGroupId($groupId);

            return;
        }

        /** @var $coreHelper Mage_Core_Helper_Data */
        $coreHelper = Mage::helper('core');
        $merchantCountryCode = $coreHelper->getMerchantCountryCode();
        $merchantVatNumber = $coreHelper->getMerchantVatNumber();

        $gatewayResponse = null;
        if ($addressHelper->getValidateOnEachTransaction($storeId)
            || $customerCountryCode != $quoteAddress->getValidatedCountryCode()
            || $customerVatNumber != $quoteAddress->getValidatedVatNumber()
        ) {
            // Send request to gateway
            $gatewayResponse = $customerHelper->checkVatNumber(
                $customerCountryCode,
                $customerVatNumber,
                ($merchantVatNumber !== '') ? $merchantCountryCode : '',
                $merchantVatNumber
            );

            // Store validation results in corresponding quote address
            $quoteAddress->setVatIsValid((int)$gatewayResponse->getIsValid())
                ->setVatRequestId($gatewayResponse->getRequestIdentifier())
                ->setVatRequestDate($gatewayResponse->getRequestDate())
                ->setVatRequestSuccess($gatewayResponse->getRequestSuccess())
                ->setValidatedVatNumber($customerVatNumber)
                ->setValidatedCountryCode($customerCountryCode)
                ->save();
        } else {
            // Restore validation results from corresponding quote address
            $gatewayResponse = new Varien_Object(array(
                'is_valid' => (int)$quoteAddress->getVatIsValid(),
                'request_identifier' => (string)$quoteAddress->getVatRequestId(),
                'request_date' => (string)$quoteAddress->getVatRequestDate(),
                'request_success' => (boolean)$quoteAddress->getVatRequestSuccess()
            ));
        }

        // Magento always has to emulate group even if customer uses default billing/shipping address
        if (!$isDisableAutoGroupChange) {
            $groupId = $customerHelper->getCustomerGroupIdBasedOnVatNumber(
                $customerCountryCode, $gatewayResponse, $customerInstance->getStore()
            );
        } else {
            $groupId = $quoteInstance->getCustomerGroupId();
        }

        if ($groupId) {
            $quoteAddress->setPrevQuoteCustomerGroupId($quoteInstance->getCustomerGroupId());
            $customerInstance->setGroupId($groupId);
            $quoteInstance->setCustomerGroupId($groupId);
        }
    }

    /**
     * Restore initial customer group ID in quote if needed on collect_totals_after event of quote address
     *
     * @param Varien_Event_Observer $observer
     */
    public function restoreQuoteCustomerGroupId($observer)
    {
        $quoteAddress = $observer->getQuoteAddress();
        $configAddressType = Mage::helper('customer/address')->getTaxCalculationAddressType();
        // Restore initial customer group ID in quote only if VAT is calculated based on shipping address
        if ($quoteAddress->hasPrevQuoteCustomerGroupId()
            && $configAddressType == Mage_Customer_Model_Address_Abstract::TYPE_SHIPPING
        ) {
            $quoteAddress->getQuote()->setCustomerGroupId($quoteAddress->getPrevQuoteCustomerGroupId());
            $quoteAddress->unsPrevQuoteCustomerGroupId();
        }
    }
	
	/**
 	 *6-28-2016 by Chris
 	 *This will run the cron job every sunday and freeze accounts that has been scheduled to be suspended 
	 *
	 */
	public function cronJobFreezeAccountsOnTheList(){
		//Mage::log("cron-testing", null, 'crontest.log');	
		//print_r($this->getRequest()->getParam('profile'));
		$currentDate = date("Y-m-d");
		
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core/read');
		
		$query_cid = 'SELECT customer_id from sales_recurring_profile WHERE freeze_start_date = "' . $currentDate . '" and state = "active" and freeze_status = 1 ;';
		$c_id = $readConnection->fetchOne($query_cid);
		
		$customer = Mage::getModel('customer/customer')->load($c_id);
		
		$customer->setData('freeze_status',1)->save();
		
		$query = 'SELECT profile_id from sales_recurring_profile WHERE freeze_start_date = "' . $currentDate . '" and state = "active" and freeze_status = 1 ;';
		$ids = $readConnection->fetchCol($query);
		//Mage::log($ids,null,'crontest.log',true);
		if (!empty($ids)){
			foreach ($ids as $profile_id) {
				try{
					Mage::log('Profile ID' . $profile_id . 'is now suspended' , null, 'crontest.log',true);
					$profile = Mage::getModel('sales/recurring_profile')->load($profile_id);
					$profile->setFreezeStatus(2)->suspend();
				} catch (Exception $e){
					Mage::log($e , null, 'crontest.log' , true);
				}
			}
		}
		return $ids;
	}
	
	/**
 	 *6-28-2016 by Chris
 	 *This will run the cron job every sunday and unfreeze accounts that has is being frozen and scheduled to unfreeze.
	 *
	 */
	public function cronJobUnfreezeAccountsOnTheList(){
		//Mage::log("cron-testing", null, 'crontest.log');	
		//print_r($this->getRequest()->getParam('profile'));
		$currentDate = date("Y-m-d");
		
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core/read');
		
		$query_cid = 'SELECT customer_id from sales_recurring_profile WHERE freeze_start_date = "' . $currentDate . '" and state = "suspended" and freeze_status = 2 ;';
		$c_id = $readConnection->fetchOne($query_cid);
		
		$customer = Mage::getModel('customer/customer')->load($c_id);
		
		$customer->setData('freeze_status',0)->save();
		
		$query = 'SELECT profile_id from sales_recurring_profile WHERE freeze_start_date = "' . $currentDate . '" and state = "suspended" and freeze_status = 2 ;';
		
		$ids = $readConnection->fetchCol($query);
		//Mage::log($ids,null,'crontest.log',true);
		if (!empty($ids)){
			foreach ($ids as $profile_id) {
				try{
					Mage::log('Profile ID' . $profile_id . 'is now active.', null, 'crontest.log',true);
					$profile = Mage::getModel('sales/recurring_profile')->load($profile_id);
					$profile->setFreezeStatus(0)->activate();
				} catch (Exception $e){
					Mage::log($e , null, 'crontest.log' , true);
				}
			}
		}
		return $ids;
	}
	
		
	/**
	 * This Will refresh the customer attribute weekly_meals_left in order to count and restrict customers from buying more meal * plans more than they do
	 * 6-29-2016 by Chris
	 * *Cron Job
	 */
	public function cronJobRefreshWeeklyMealsLeft(){
		
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core/read');
		$collection = Mage::getModel('customer/customer')
						->getCollection()
						->addAttributeToSelect('*');
		//Mage::log('collection:\n'.$collection, null, 'crontest.log', true);
		foreach ($collection as $customer){
			$customer_id = $customer->getId();
			//Mage::log('object:  '. print_r($customer), null, 'crontest.log', true);
			//Mage::log('id:'.$customer_id, null, 'crontest.log' , true);
			$query = 'SELECT sub_weekly_limit from sales_recurring_profile WHERE customer_id = ' . $customer_id . ' and state = "active" ;';
			//Mage::log('query:'.$query, null, 'crontest.log', true);
			$limits = $readConnection->fetchCol($query);
			$total = 0;
			foreach ($limits as $limit){
				$total += $limit;
				Mage::log('total:'.$total, null, 'crontest.log', true);
			}
			$customer->setWeeklyMealsLeft($total)->setCurrentPlanPerWeek($total)->save();
		}
	}
	//8-17-2016 by Chris
	public function cronJobCreateSuggestedMealOrders(){
		$customer_collection = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('group_id',4)-> addFieldToFilter('freeze_status', 0);
		foreach ($customer_collection as $customer){
		    //get customer id
			$c_id = $customer->getData('entity_id');
            //get customer latest order
            $orders = Mage::getResourceModel('sales/order_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_id', $c_id)
            ->addAttributeToSort('created_at', 'DESC')
            ->setPageSize(1);
            $last_order_date = $orders->getFirstItem()->getcreated_at();
            // customer who did not place any order within one week
            $timediff = time() - strtotime($last_order_date);
            if ($timediff >= 604800) {
                //get transaction model
                $transaction = Mage::getModel('core/resource_transaction');
                //get array of default items
                $c = Mage::getModel('customer/customer')->load($c_id);
                $p_ids = $c->getData('default_meals_next_week');
                $p_ids_array = explode(',',$p_ids);

                $order = Mage::getModel('sales/order');
                //set customer info
                $order->setCustomer_email($customer->getEmail())
                ->setCustomerFirstname($customer->getFirstname())
                ->setCustomerLastname($customer->getLastname())
                ->setCustomerGroupId($customer->getGroupId())
                ->setCustomer_is_guest(0)
                ->setCustomer($customer);

                //set billing
                $billing = $customer->getDefaultBillingAddress();
                $billingAddress = Mage::getModel('sales/order_address')
                ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_BILLING)
                ->setCustomerId($customer->getId())
                ->setCustomerAddressId($customer->getDefaultBilling())
                ->setCustomer_address_id($billing->getEntityId())
                ->setPrefix($billing->getPrefix())
                ->setFirstname($billing->getFirstname())
                ->setMiddlename($billing->getMiddlename())
                ->setLastname($billing->getLastname())
                ->setSuffix($billing->getSuffix())
                ->setCompany($billing->getCompany())
                ->setStreet($billing->getStreet())
                ->setCity($billing->getCity())
                ->setCountry_id($billing->getCountryId())
                ->setRegion($billing->getRegion())
                ->setRegion_id($billing->getRegionId())
                ->setPostcode($billing->getPostcode())
                ->setTelephone($billing->getTelephone())
                ->setFax($billing->getFax());
                $order->setBillingAddress($billingAddress);
                //set payment
                $orderPayment = Mage::getModel('sales/order_payment')
                ->setCustomerPaymentId(0)
                ->setMethod('purchaseorder')
                ->setPo_number(' â€“ ');
                $order->setPayment($orderPayment);
			     //set shipping
                $shipping = $customer->getDefaultShippingAddress();
                $shippingAddress = Mage::getModel('sales/order_address')
                ->setAddressType(Mage_Sales_Model_Quote_Address::TYPE_SHIPPING)
                ->setCustomerId($customer->getId())
                ->setCustomerAddressId($customer->getDefaultShipping())
                ->setCustomer_address_id($shipping->getEntityId())
                ->setPrefix($shipping->getPrefix())
                ->setFirstname($shipping->getFirstname())
                ->setMiddlename($shipping->getMiddlename())
                ->setLastname($shipping->getLastname())
                ->setSuffix($shipping->getSuffix())
                ->setCompany($shipping->getCompany())
                ->setStreet($shipping->getStreet())
                ->setCity($shipping->getCity())
                ->setCountry_id($shipping->getCountryId())
                ->setRegion($shipping->getRegion())
                ->setRegion_id($shipping->getRegionId())
                ->setPostcode($shipping->getPostcode())
                ->setTelephone($shipping->getTelephone())
                ->setFax($shipping->getFax());
                 
                $order->setShippingAddress($shippingAddress);
                $subTotal = 0;
				foreach($p_ids_array as $id){
					
					$_product = Mage::getModel('catalog/product')->load($id);
                    $rowTotal = $_product->getPrice();
                    $orderItem = Mage::getModel('sales/order_item')
                    ->setQuoteItemId(0)
                    ->setQuoteParentItemId(NULL)
                    ->setProductId($id)
                    ->setProductType(1)
                    ->setQtyBackordered(NULL)
                    ->setTotalQtyOrdered(1)
                    ->setQtyOrdered(1)
                    ->setName($_product->getName())
                    ->setSku($_product->getSku())
                    ->setPrice($_product->getPrice())
                    ->setBasePrice($_product->getPrice())
                    ->setOriginalPrice($_product->getPrice())
                    ->setRowTotal($rowTotal)
                    ->setBaseRowTotal($rowTotal);

                    $subTotal += $rowTotal;
                    $order->addItem($orderItem);
				}
                //add total to order
                $order->setSubtotal($subTotal)
                ->setBaseSubtotal($subTotal)
                ->setGrandTotal($subTotal)
                ->setBaseGrandTotal($subTotal);
                 //launch transaction
                $transaction->addObject($order);
                $transaction->addCommitCallback(array($order, 'place'));
                $transaction->addCommitCallback(array($order, 'save'));
                $transaction->save();
			} 
		}	
	}	

	
	
	/**
	 * This will send emails weekly and dynamically to customers with their last week meals as well as filtered meals by their preference for next week. 
	 * 6-29-2016 by Chris
	 * *Cron Job
	 test file code/core/Mage/checkout/cartcontroller.php
	 */
	

	public function cronJobSendWeeklyEmails(){
		 //customer collection object
		$customer_collection = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('group_id',4)-> addFieldToFilter('freeze_status', 0);

		foreach ($customer_collection as $customer){

            $email = $customer->getEmail();//generate a random token;
            $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randstring = '';
			for ($i = 0; $i < 30; $i++) {
				$randstring = $randstring . $characters[rand(0, strlen($characters))];
			}
			
			$customer->setData('customer_login_token',$randstring)->save();

			//Email body generated by function below
			$body = $this->generateBodyHtml($customer, $randstring);
			print_r($body);
			//email object
			$email_model = Mage::getModel('core/email');
			
			$name = $customer->getName();

			$email_model->setSenderName('Abrameals')
						->setToName($name)
						->setToEmail($email)
						->setFromEmail('admin@abrameals.com')
						->setSubject('Your coming meals for the following week');
			$email_model->setBody($body)
						->setType('html')
						->send();  
			sleep(1);
		}
		 
		 return 0;
	}
	 
	public function generateBodyHtml($customer , $key){ 
		
		$email = $customer->getEmail();
		$token = $customer->getData('customer_login_token');

		$head = <<<HTML
		<table align="center">
		<tbody><tr><td>
		<div class="background">
			<table border="0" style="align:center" cellpadding="0" cellspacing="0" background="http://www.abrameals.com/media/email/bg.jpg" width="600"  id="bodyTable">
				<tr>
				<td align="center" valign="top">
				<table border="0" cellpadding="" cellspacing="0" width="" id="emailContainer">
                <tr>
					<td class="header-content" height="80px" colspan="3">
						<div style="text-align:center">
							<img src="http://abrameals.com/media/email/abra_logo.png"/>
						</div>
					</td>
				</tr>
				<tr>
					<td height="60px" colspan="3">
						<div style="text-align:center">
							<img src="http://abrameals.com/media/email/abrameal_0000_recommend_360.png" />					
						</div>
					</td>
				</tr>
				<td>
				<td>
                    
HTML;

		$foot = <<<HTML
				<tr>
				<td>&nbsp;<br>&nbsp;
				</td>
                </tr>
				</tr>
            </table>
        </td>
    </tr>
</table>
</table>
</div>
</div>
</td></tr></tbody></table>



HTML;

		$body_upper = <<<HTML
		
		<tr>
		
HTML;
		$body_lower = <<<HTML
		</tr>
		<tr>
		<td align="center" colspan="3" width="100%">
			<a style="text-decoration:none; text-align:center" href="http://www.abrameals.com/customer/account/loginFromEmail?username={$email}&token={$token}&redirect_method=order_history">
			<div style="Color: white;
						display: inline-block;
						margin-top: 20px;
						padding: 10px;
						border: 2px solid green;
						border-radius: 6px;
						background: #2b7927;"> 
				Review Your Meals
			</div>
			</a>
		</td>
		</tr>

HTML;
		
		$css  = <<<HTML
<style>
.background{
	background:url("http://www.abrameals.com/media/email/bg.jpg") no-repeat top center;
	width:600px;
	align:center;
}
.p-name{
	font-size:10px;
	height:30px;
	overflow:hidden;
}
a{
	text-decoration:none;
}
.p-name{
	color:black;
}






</style>
HTML;

		
		//echo $customer->getId();
		$orders = Mage::getModel("sales/order")->getCollection()
                       ->addAttributeToSelect('*')
                       ->addFieldToFilter('customer_id', $customer->getId());
		$count = 0;
		$item_html = '';
		
		foreach($orders as $order){
			$items = $order->getAllVisibleItems();
			foreach ($items as $item):
			//	print_r($item->getData());
				$id = $item->getProductId();
				$i  = Mage::getModel('catalog/product')->load($id);
				$data = array(
					'img' => $i->getImageUrl(),
					'name' => $i ->getName(),
					'category' => $i ->getCategoryIds(),
					'url' => $i->getProductUrl()
				);
				//var_dump($data['url']);
				//print_r($data['category']);
				if(in_array(3 ,$data['category'])){
					if($count < 3 ){
						$count++;
						$item_html = $item_html .
						"<td width=\"33%\" align=\"center\">
						<a style=\"text-decoration:none\" href=\"".$data['url']."\"><img style=\"width:155px\" align=\"center\" src=\" " .  $data['img'] . "\" />
						<p style=\"font-size:12px; color:black;; padding: 0 20px; \" class=\"p-name\"> " . $data['name'] . "</p></a>
						</td>";
					}	
					//echo $body;
				}
			endforeach;
			//die;
		}
		
		$item_html2 = '';
		$count2 = 0;
		$item_skus = Mage::getStoreConfig('suggestmealsforemail_options/section_one/custom_field_one', Mage::app()->getStore());
		$skus = explode(",",$item_skus);
		$suggested_ids = array();
		
		$preference_list = array(
			'preference_all_veggies',
			'preference_no_beef',
			'preference_no_fish',
			'preference_no_lamb',
			'preference_no_pork',
			'preference_no_poultry',
			'preference_no_shrimp'
		);
			
		$prof = Mage::getModel('sales/recurring_profile')->getCollection()
            ->addFieldToFilter('customer_id', $customer->getId())->addFieldToFilter('state','active')
            ->setOrder('profile_id', 'desc');
			foreach ($prof as $p){
				$p_limit = $p->getSubWeeklyLimit();
				break;
			}
			
		foreach ($skus as $sku){
			$suggested_product = Mage::getModel("catalog/product")->loadByAttribute('sku', $sku);

			$data = array(
				'img' => $suggested_product->getImageUrl(),
				'name' => $suggested_product ->getName(),
				'category' => $suggested_product ->getCategoryIds(),
				'url' => $suggested_product ->getProductUrl(),
				'id' => $suggested_product ->getId(),
			);
			
			$c_pref = array();
			
			$validate = true;
			
			foreach ($preference_list as $pref){
				if ($customer->getData($pref) == 1){
					if($suggested_product->getData($pref) == 1){
						$validate = false;
					}
				}
			}
			
			if ($validate == true){
				if($count2 < $p_limit ){
					$count2++;
					array_push($suggested_ids , $data['id']);
					$item_html2 = $item_html2 .
					"<td width=\"33%\" align=\"center\">
					<a style=\"text-decoration:none\" href=\"".$data['url']."\"> <img style=\"width:155px\" src=\" " .  $data['img'] . "\" />
					<p style=\"color:black; font-size:12px; padding: 0 20px; \" class=\"p-name\"> " . $data['name'] . "</p></a>
					</td>";
				}
				
				if( $count2 % 3 == 0 ){
					$item_html2 = $item_html2 . "</tr><tr>";
				}
				
			}
		}
		
		$suggested_ids_string = implode ("," ,$suggested_ids);
		//Filter by preference ..
		//8-17-2016 by Chris
		$customer->setData('default_meals_next_week', $suggested_ids_string)->save();
		
		$content_between_items = <<<HTML
		</tr>		<tr>
		<td align="center" colspan="3" width="100%">
			<table>
				<tr>
					<td>
					<a style="text-decoration:none" href="http://www.abrameals.com/customer/account/loginFromEmail?username={$email}&token={$token}&redirect_method=menu_add_suggested_plans&suggested_plans={$suggested_ids_string}">
					<div style="Color: white;
						display: inline-block;
						margin-top: 20px;
						padding: 10px;
						border: 2px solid green;
						border-radius: 6px;
						background: #2b7927;" >Choose my own meals</div>
					</td>
					<td>
					<a style="text-decoration:none" href="http://www.abrameals.com/customer/account/loginFromEmail?username={$email}&token={$token}&redirect_method=freeze_subscription">
					<div style="Color: white;
						display: inline-block;
						margin-top: 20px;
						padding: 10px;
						border: 2px solid green;
						border-radius: 6px;
						background: #2b7927;" >Freeze for next week</div>
					</td>
				</tr>
			</table>
		<td>
		</tr> 
		
		<tr><td colspan="3" style="text-align:center; height:80px" >
			<img src="http://www.abrameals.com/media/email/abrameal_0001_meals_360.png"/>
		</td></tr>
		<tr>
HTML;
		//echo 'test';
		//print_r($item_html2);
		//echo 'test end';
		
		return $head.$body_upper.$item_html2.$content_between_items.$item_html.$body_lower.$foot.$css;
	}

	
}
