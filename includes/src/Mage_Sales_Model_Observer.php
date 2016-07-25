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
		$query = 'SELECT profile_id from sales_recurring_profile WHERE freeze_start_date = "' . $currentDate . '" and state = "active" and freeze_status = 1 ;';
		$ids = $readConnection->fetchCol($query);
		//Mage::log($ids,null,'crontest.log',true);
		if (!empty($ids)){
			foreach ($ids as $profile_id) {
				try{
					Mage::log($profile_id, null, 'crontest.log',true);
					$profile = Mage::getModel('sales/recurring_profile')->load($profile_id);
					$profile->setFreezeStatus(2)->suspend()->save();
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
		$query = 'SELECT profile_id from sales_recurring_profile WHERE freeze_start_date = "' . $currentDate . '" and state = "suspended" and freeze_status = 2 ;';
		$ids = $readConnection->fetchCol($query);
		//Mage::log($ids,null,'crontest.log',true);
		if (!empty($ids)){
			foreach ($ids as $profile_id) {
				try{
					Mage::log($profile_id, null, 'crontest.log',true);
					$profile = Mage::getModel('sales/recurring_profile')->load($profile_id);
					$profile->setFreezeStatus(0)->activate()->save();
				} catch (Exception $e){
					Mage::log($e , null, 'crontest.log' , true);
				}
			}
		}
		return $ids;
	}
	
		
	/**
	 * This Will refresh the customer attribute weekly_meals_left in order to count and restric customers from buying more meal * plans more than they do
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
	
	/**
	 * This will send emails weekly and dynamically to customers with their last week meals as well as filtered meals by their preference for next week. 
	 * 6-29-2016 by Chris
	 * *Cron Job
	 test file code/core/Mage/checkout/cartcontroller.php
	 */
	 
	public function cronJobSendWeeklyEmails(){
		 //customer collection object
		
		$customer_collection = Mage::getModel('customer/customer')->getCollection()->addAttributeToSelect('*')->addFieldToFilter('group_id',4);

		foreach ($customer_collection as $customer){
						 
			//generate a random token
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$randstring = '';
			for ($i = 0; $i < 30; $i++) {
				$randstring = $randstring . $characters[rand(0, strlen($characters))];
			}
			
			$customer->setData('customer_login_token',$randstring)->save();
			
			//Email body generated by function below
			$body = $this->generateBodyHtml($customer);
			print_r($body);
			//email object
			$email_model = Mage::getModel('core/email');

			$email = $customer->getEmail();
			$name = $customer->getName();
			
			$email_model->setSenderName('Abrameals')
						->setToName($name)
						->setToEmail($email)
						->setFromEmail('admin@abrameals.com')
						->setSubject('Subject here......');
			$email_model->setBody($body)
						->setType('html')
						->send();  
			 
			/*$email_model = Mage::getModel('core/email') ->setToName('zw') ->setToEmail('everbread1234@gmail.com') ->setFromEmail('admin@abrameals.com') ->setSubject('Subject here......');
			$email_model->setBody($body) ->setType('html') ->send();*/

		}
		 
		 return 0;
	}
	 
	public function generateBodyHtml($customer){
		
		$email = $customer->getEmail();
		$token = $customer->getData('customer_login_token');

		$head = <<<HTML
<table border="0" cellpadding="0" cellspacing="0" height="100%" width="100%" id="bodyTable">
    <tr>
        <td align="center" valign="top">
            <table border="0" cellpadding="20" cellspacing="0" width="600" id="emailContainer">
                <tr>
				<td>
				Header here..
				</td>
				</tr>
				<td>
                    
HTML;

		$foot = <<<HTML
				<tr>
				<td>Footer here...
				</td>
                </tr>
				</tr>
            </table>
        </td>
    </tr>
</table>




</table>




360b746156fce177b2c220894bc1fa55
JCM3wrfGcZJNp5IADZMpZ378Gh879ZTu


HTML;

		$body_upper = <<<HTML
		
		<tr>
		
HTML;
		$body_lower = <<<HTML
		</tr>
		<tr>
		<td align="center" colspan="3" width="100%">
			<a style="text-align:center" href="http://www.abrameals.com/customer/account/loginFromEmail?username={$email}&token={$token}">
			product review</a>
		</td>
		</tr>
		<tr>
		<td align="center" colspan="3" width="100%">
		<a href="http://www.abrameals.com/menu.html" >
			Add your next week's plan:
		</a>
		<td>
		</tr>
HTML;
		
		$css  = <<<HTML
<style>
.table{
	
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
				$id = $item->getId();
				$i  = Mage::getModel('catalog/product')->load($id);
				$data = array(
					'img' => $i->getImageUrl(),
					'name' => $i ->getName(),
					'category' => $i ->getCategoryIds()
				);
				
				if(in_array(3 ,$data['category'])){
					if($count < 3 ){
						$count++;
						$item_html = $item_html .
						"<td>
						<img style=\"width:190px\" src=\" " .  $data['img'] . "\" />
						<p> " . $data['name'] . "</p>
						</td>";
					}
					
					//echo $body;
				}
			endforeach;
			//die;
		}
		
		return $head.$body_upper.$item_html.$body_lower.$foot;
	}
	
}
