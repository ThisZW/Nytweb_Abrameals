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
 * @package     Mage_Checkout
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */

/**
 * Shopping cart controller
 */
 
 // 6-14-2016 EDITED AND MODIFIED BY Chris.
class Mage_Checkout_CartController extends Mage_Core_Controller_Front_Action
{
    /**
     * Action list where need check enabled cookie
     *
     * @var array
     */
    protected $_cookieCheckActions = array('add');

    /**
     * Retrieve shopping cart model object
     *
     * @return Mage_Checkout_Model_Cart
     */
    protected function _getCart()
    {
        return Mage::getSingleton('checkout/cart');
    }

    /**
     * Get checkout session model instance
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Get current active quote instance
     *
     * @return Mage_Sales_Model_Quote
     */
    protected function _getQuote()
    {
        return $this->_getCart()->getQuote();
    }

    /**
     * Set back redirect url to response
     *
     * @return Mage_Checkout_CartController
     * @throws Mage_Exception
     */
    protected function _goBack()
    {
        $returnUrl = $this->getRequest()->getParam('return_url');
        if ($returnUrl) {

            if (!$this->_isUrlInternal($returnUrl)) {
                throw new Mage_Exception('External urls redirect to "' . $returnUrl . '" denied!');
            }

            $this->_getSession()->getMessages(true);
            $this->getResponse()->setRedirect($returnUrl);
        } elseif (!Mage::getStoreConfig('checkout/cart/redirect_to_cart')
            && !$this->getRequest()->getParam('in_cart')
            && $backUrl = $this->_getRefererUrl()
        ) {
            $this->getResponse()->setRedirect($backUrl);
        } else {
            if ((strtolower($this->getRequest()->getActionName()) == 'add') && !$this->getRequest()->getParam('in_cart')) {
                $this->_getSession()->setContinueShoppingUrl($this->_getRefererUrl());
            }
            $this->_redirect('checkout/cart');
        }
        return $this;
    }

    /**
     * Initialize product instance from request data
     *
     * @return Mage_Catalog_Model_Product || false
     */
    protected function _initProduct()
    {
        $productId = (int) $this->getRequest()->getParam('product');
        if ($productId) {
            $product = Mage::getModel('catalog/product')
                ->setStoreId(Mage::app()->getStore()->getId())
                ->load($productId);
            if ($product->getId()) {
                return $product;
            }
        }
        return false;
    }

    /**
     * Predispatch: remove isMultiShipping option from quote
     *
     * @return Mage_Checkout_CartController
     */
    public function preDispatch()
    {
        parent::preDispatch();

        $cart = $this->_getCart();
        if ($cart->getQuote()->getIsMultiShipping()) {
            $cart->getQuote()->setIsMultiShipping(false);
        }

        return $this;
    }

    /**
     * Shopping cart display action
     */
    public function indexAction()
    {	
		$group_id = Mage::getSingleton('customer/session')->getCustomerGroupId();
		if(Mage::getSingleton('customer/session')->isLoggedIn() and ($group_id == 4)){
			$cart = $this->_getCart();
			if ($cart->getQuote()->getItemsCount()) {
				$cart->init();
				$cart->save();
				
			$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
			$readConnection = Mage::getSingleton('core/resource')->getConnection('core/read');
			$subscription_weekly_limit = $readConnection->fetchOne("SELECT sub_weekly_limit from sales_recurring_profile WHERE customer_id = " . $customerId . " AND `state` = \"active\" AND billing_amount > 0 ;");
			$subscription_item_name = $readConnection->fetchOne("SELECT schedule_description from sales_recurring_profile WHERE customer_id = " . $customerId . " AND `state` = \"active\" AND billing_amount > 0 ;");
			//print_r ($subscription_item_name);
			//die;
			//$cart->getCheckoutSession()->addNotice('You will only be able to select '.$subscription_weekly_limit.' meals this week as your current subscription plan  "' . $subscription_item_name . '", otherwise we will randomly choose/delete meals for you.');
				if (!$this->_getQuote()->validateMinimumAmount()) {
					$minimumAmount = Mage::app()->getLocale()->currency(Mage::app()->getStore()->getCurrentCurrencyCode())
						->toCurrency(Mage::getStoreConfig('sales/minimum_order/amount'));

					$warning = Mage::getStoreConfig('sales/minimum_order/description')
						? Mage::getStoreConfig('sales/minimum_order/description')
						: Mage::helper('checkout')->__('Minimum order amount is %s', $minimumAmount);

					$cart->getCheckoutSession()->addNotice($warning);
				}
			}

			// Compose array of messages to add
			$messages = array();
			foreach ($cart->getQuote()->getMessages() as $message) {
				if ($message) {
					// Escape HTML entities in quote message to prevent XSS
					$message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
					$messages[] = $message;
				}
			}
			$cart->getCheckoutSession()->addUniqueMessages($messages);

			/**
			 * if customer enteres shopping cart we should mark quote
			 * as modified bc he can has checkout page in another window.
			 */
			$this->_getSession()->setCartWasUpdated(true);

			Varien_Profiler::start(__METHOD__ . 'cart_display');
			$this
				->loadLayout()
				->_initLayoutMessages('checkout/session')
				->_initLayoutMessages('catalog/session')
				->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
			$this->renderLayout();
			Varien_Profiler::stop(__METHOD__ . 'cart_display');
		} else {
			$cart   = $this->_getCart();
			$params = $this->getRequest()->getParams();
			//subscription category id "4"
			$subscription_category 	= Mage::getModel('catalog/category')->load(4);
			$subscription_plans 	= Mage::getResourceModel('catalog/product_collection')
						->setStoreId(Mage::app()->getStore()->getId())
						->addCategoryFilter($subscription_category);
						
			//$subscription_prices = array();
			$plans_index = array();
			
			//cart quote ID 
			$quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
			if (!$quoteId){ $quoteId = 0; }
			
			//sql connection --> write
			$writeConnection = Mage::getSingleton('core/resource')->getConnection('core/write');
			$readConnection = Mage::getSingleton('core/resource')->getConnection('core/read');
			$sub_count = 0;
			$highest_limit = 0;
			foreach($subscription_plans as $subscription_plan){
				//deal with sub plans here.
				$plan_id = $subscription_plan->getId();
				$recurring_profile = Mage::getResourceModel('catalog/product')->getAttributeRawValue($plan_id, 'recurring_profile', $storeId);
				$sub_price = Mage::getResourceModel('catalog/product')->getAttributeRawValue($plan_id, 'price', $storeId);
				//print_r($attr . '<br>');
				$recurring_profile_unserialized = unserialize($recurring_profile);
				//*****Subscription Weekly Limit*******//
				$sub_limit = $recurring_profile_unserialized['sub_weekly_limit'];
				$plans_index[$sub_limit] = $plan_id;
				//check if plan is going to be deleted
				$query_check = 'SELECT `qty` from `sales_flat_quote_item` WHERE `quote_id` = ' . $quoteId . ' and `product_id` = ' . $plan_id .';';
				//$query_check = 'SELECT `qty` from `sales_flat_quote_item` WHERE `quote_id` = 31 and `product_id` = 23 ;';
				$check = $readConnection->fetchOne($query_check);
				if ($check) $sub_count ++ ;
				if ($sub_limit > $highest_limit) $highest_limit = $sub_limit;
			}
			if ($cart->getQuote()->getItemsCount()) {
				$cart->init();			
				$item_qty = $cart->getSummaryQty() - $sub_count;
				//echo $item_qty;
				//die;
				if (!key_exists((int)$item_qty, $plans_index)){
					$cart->getCheckoutSession()->addNotice('"Highest quantity you are able to checkout." ' . $highest_limit);
					$cart->save();
				} else {
					$cart->save();
				}
			}
			$messages = array();
			foreach ($cart->getQuote()->getMessages() as $message) {
				if ($message) {
					// Escape HTML entities in quote message to prevent XSS
					$message->setCode(Mage::helper('core')->escapeHtml($message->getCode()));
					$messages[] = $message;
				}
			}
			$cart->getCheckoutSession()->addUniqueMessages($messages);

			/**
			 * if customer enteres shopping cart we should mark quote
			 * as modified bc he can has checkout page in another window.
			 */
			$this->_getSession()->setCartWasUpdated(true);

			Varien_Profiler::start(__METHOD__ . 'cart_display');
			$this
				->loadLayout()
				->_initLayoutMessages('checkout/session')
				->_initLayoutMessages('catalog/session')
				->getLayout()->getBlock('head')->setTitle($this->__('Shopping Cart'));
			$this->renderLayout();
			Varien_Profiler::stop(__METHOD__ . 'cart_display');
		}
    }

    /**
     * Add product to shopping cart action
     *
     * @return Mage_Core_Controller_Varien_Action
     * @throws Exception
     */
    public function addAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }
		//echo "i am here";
		//group_id is the subscriber group
		//should be able to add freeze group too later.
		$group_id = Mage::getSingleton('customer/session')->getCustomerGroupId();
		if(Mage::getSingleton('customer/session')->isLoggedIn() and ($group_id == 4)){
			$cart   = $this->_getCart();
			$params = $this->getRequest()->getParams();
			try {
				if (isset($params['qty'])) {
					$filter = new Zend_Filter_LocalizedToNormalized(
						array('locale' => Mage::app()->getLocale()->getLocaleCode())
					);
					$params['qty'] = $filter->filter($params['qty']);
				}

				$product = $this->_initProduct();
				$related = $this->getRequest()->getParam('related_product');

				/**
				 * Check product availability
				 */
				if (!$product) {
					$this->_goBack();
					return;
				}

				$cart->addProduct($product, $params);
				if (!empty($related)) {
					$cart->addProductsByIds(explode(',', $related));
				}

				$cart->save();

				$this->_getSession()->setCartWasUpdated(true);

				/**
				 * @todo remove wishlist observer processAddToCart
				 */
				Mage::dispatchEvent('checkout_cart_add_product_complete',
					array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
				);

				if (!$this->_getSession()->getNoCartRedirect(true)) {
					if (!$cart->getQuote()->getHasError()) {
						$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
						$this->_getSession()->addSuccess($message);
					}
					$this->_goBack();
				}
			} catch (Mage_Core_Exception $e) {
				if ($this->_getSession()->getUseNotice(true)) {
					$this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
				} else {
					$messages = array_unique(explode("\n", $e->getMessage()));
					foreach ($messages as $message) {
						$this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
					}
				}

				$url = $this->_getSession()->getRedirectUrl(true);
				if ($url) {
					$this->getResponse()->setRedirect($url);
				} else {
					$this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
				}
			} catch (Exception $e) {
				$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
				Mage::logException($e);
				$this->_goBack();
			}
		} else {
			//echo 'hi';

			//茄子是傻逼
			/*   
			-check of log in
				yes
					-check of subscription if logged in
						yes
							do nothing
						no 
							-load subscription product attribute ->unserialize
							-view subscription price based on qty
				no
					-load subscription product attribute ->unserialize
					-view subscription price based on qty
			*/
			$cart   = $this->_getCart();
			$params = $this->getRequest()->getParams();
			//subscription category id "4"
			$subscription_category 	= Mage::getModel('catalog/category')->load(4);
			$subscription_plans 	= Mage::getResourceModel('catalog/product_collection')
						->setStoreId(Mage::app()->getStore()->getId())
						->addCategoryFilter($subscription_category);
						
			//$subscription_prices = array();
			$plans_index = array();
			
			//cart quote ID 
			$quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
			if (!$quoteId){ $quoteId = 0; }
			
			//sql connection --> write
			$writeConnection = Mage::getSingleton('core/resource')->getConnection('core/write');
			$readConnection = Mage::getSingleton('core/resource')->getConnection('core/read');
			$sub_count = 0;
			foreach($subscription_plans as $subscription_plan){
				//deal with sub plans here.
				$plan_id = $subscription_plan->getId();
				$recurring_profile = Mage::getResourceModel('catalog/product')->getAttributeRawValue($plan_id, 'recurring_profile', $storeId);
				$sub_price = Mage::getResourceModel('catalog/product')->getAttributeRawValue($plan_id, 'price', $storeId);
				//print_r($attr . '<br>');
				$recurring_profile_unserialized = unserialize($recurring_profile);
				//*****Subscription Weekly Limit*******//
				$sub_limit = $recurring_profile_unserialized['sub_weekly_limit'];
				$plans_index[$sub_limit] = $plan_id;
				//check if plan is going to be deleted
				$query_check = 'SELECT `qty` from `sales_flat_quote_item` WHERE `quote_id` = ' . $quoteId . ' and `product_id` = ' . $plan_id .';';
				//$query_check = 'SELECT `qty` from `sales_flat_quote_item` WHERE `quote_id` = 31 and `product_id` = 23 ;';
				$check = $readConnection->fetchOne($query_check);
				if ($check) $sub_count ++ ;
				//remove any subscription in the shopping cart first.
				//$cart->removeItem($plan_id);  --> not useful, using direct sql.
				$query_delete = 'DELETE FROM `sales_flat_quote_item` WHERE `quote_id` = '. $quoteId.' and `product_id` = '.$plan_id.';';
				$writeConnection->query($query_delete);
			}
			
			$cart_total_before = $this->_getCart()->getSummaryQty() - $sub_count;
			echo $cart_total_before;
			//print_r($plans_index);
			//die;
			
			try {
				if (isset($params['qty'])) {
					$filter = new Zend_Filter_LocalizedToNormalized(
						array('locale' => Mage::app()->getLocale()->getLocaleCode())
					);
					$params['qty'] = $filter->filter($params['qty']);
					$cart_total_after = $cart_total_before + $params['qty'];
					//die;
				} else {
					$cart_total_after = $cart_total_before + 1; 
				}
				//echo $cart_total_after;
				//die;
			
				$product = $this->_initProduct();
				$related = $this->getRequest()->getParam('related_product');

				/**
				 * Check product availability
				 */
				if (!$product) {
					$this->_goBack();
					return;
				}

				$cart->addProduct($product, $params);
				if (!empty($related)) {
					$cart->addProductsByIds(explode(',', $related));
				}
				//print_r( $plans_index );
				//echo $cart_total_after;
				//die;
				//AUTOMATICALLY ADD/Change subscription plans in the shopping cart
				if (key_exists((int)$cart_total_after, $plans_index)){
					$cart->addProductsByIds(array($plans_index[$cart_total_after]));
				}
				$cart->save();

				$this->_getSession()->setCartWasUpdated(true);

				/**
				 * @todo remove wishlist observer processAddToCart
				 */
				Mage::dispatchEvent('checkout_cart_add_product_complete',
					array('product' => $product, 'request' => $this->getRequest(), 'response' => $this->getResponse())
				);

				if (!$this->_getSession()->getNoCartRedirect(true)) {
					if (!$cart->getQuote()->getHasError()) {
						$message = $this->__('%s was added to your shopping cart.', Mage::helper('core')->escapeHtml($product->getName()));
						$this->_getSession()->addSuccess($message);
					}
					$this->_goBack();
				}
			} catch (Mage_Core_Exception $e) {
				if ($this->_getSession()->getUseNotice(true)) {
					$this->_getSession()->addNotice(Mage::helper('core')->escapeHtml($e->getMessage()));
				} else {
					$messages = array_unique(explode("\n", $e->getMessage()));
					foreach ($messages as $message) {
						$this->_getSession()->addError(Mage::helper('core')->escapeHtml($message));
					}
				}

				$url = $this->_getSession()->getRedirectUrl(true);
				if ($url) {
					$this->getResponse()->setRedirect($url);
				} else {
					$this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
				}
			} catch (Exception $e) {
				$this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
				Mage::logException($e);
				$this->_goBack();
			}
		}
    }

    /**
     * Add products in group to shopping cart action
     */
    public function addgroupAction()
    {
        $orderItemIds = $this->getRequest()->getParam('order_items', array());

        if (!is_array($orderItemIds) || !$this->_validateFormKey()) {
            $this->_goBack();
            return;
        }

        $itemsCollection = Mage::getModel('sales/order_item')
            ->getCollection()
            ->addIdFilter($orderItemIds)
            ->load();
        /* @var $itemsCollection Mage_Sales_Model_Mysql4_Order_Item_Collection */
        $cart = $this->_getCart();
        foreach ($itemsCollection as $item) {
            try {
                $cart->addOrderItem($item, 1);
            } catch (Mage_Core_Exception $e) {
                if ($this->_getSession()->getUseNotice(true)) {
                    $this->_getSession()->addNotice($e->getMessage());
                } else {
                    $this->_getSession()->addError($e->getMessage());
                }
            } catch (Exception $e) {
                $this->_getSession()->addException($e, $this->__('Cannot add the item to shopping cart.'));
                Mage::logException($e);
                $this->_goBack();
            }
        }
        $cart->save();
        $this->_getSession()->setCartWasUpdated(true);
        $this->_goBack();
    }

    /**
     * Action to reconfigure cart item
     */
    public function configureAction()
    {
        // Extract item and product to configure
        $id = (int) $this->getRequest()->getParam('id');
        $quoteItem = null;
        $cart = $this->_getCart();
        if ($id) {
            $quoteItem = $cart->getQuote()->getItemById($id);
        }

        if (!$quoteItem) {
            $this->_getSession()->addError($this->__('Quote item is not found.'));
            $this->_redirect('checkout/cart');
            return;
        }

        try {
            $params = new Varien_Object();
            $params->setCategoryId(false);
            $params->setConfigureMode(true);
            $params->setBuyRequest($quoteItem->getBuyRequest());

            Mage::helper('catalog/product_view')->prepareAndRender($quoteItem->getProduct()->getId(), $this, $params);
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot configure product.'));
            Mage::logException($e);
            $this->_goBack();
            return;
        }
    }

    /**
     * Update product configuration for a cart item
     */
    public function updateItemOptionsAction()
    {
        $cart   = $this->_getCart();
        $id = (int) $this->getRequest()->getParam('id');
        $params = $this->getRequest()->getParams();

        if (!isset($params['options'])) {
            $params['options'] = array();
        }
        try {
            if (isset($params['qty'])) {
                $filter = new Zend_Filter_LocalizedToNormalized(
                    array('locale' => Mage::app()->getLocale()->getLocaleCode())
                );
                $params['qty'] = $filter->filter($params['qty']);
            }

            $quoteItem = $cart->getQuote()->getItemById($id);
            if (!$quoteItem) {
                Mage::throwException($this->__('Quote item is not found.'));
            }

            $item = $cart->updateItem($id, new Varien_Object($params));
            if (is_string($item)) {
                Mage::throwException($item);
            }
            if ($item->getHasError()) {
                Mage::throwException($item->getMessage());
            }

            $related = $this->getRequest()->getParam('related_product');
            if (!empty($related)) {
                $cart->addProductsByIds(explode(',', $related));
            }

            $cart->save();

            $this->_getSession()->setCartWasUpdated(true);

            Mage::dispatchEvent('checkout_cart_update_item_complete',
                array('item' => $item, 'request' => $this->getRequest(), 'response' => $this->getResponse())
            );
            if (!$this->_getSession()->getNoCartRedirect(true)) {
                if (!$cart->getQuote()->getHasError()) {
                    $message = $this->__('%s was updated in your shopping cart.', Mage::helper('core')->escapeHtml($item->getProduct()->getName()));
                    $this->_getSession()->addSuccess($message);
                }
                $this->_goBack();
            }
        } catch (Mage_Core_Exception $e) {
            if ($this->_getSession()->getUseNotice(true)) {
                $this->_getSession()->addNotice($e->getMessage());
            } else {
                $messages = array_unique(explode("\n", $e->getMessage()));
                foreach ($messages as $message) {
                    $this->_getSession()->addError($message);
                }
            }

            $url = $this->_getSession()->getRedirectUrl(true);
            if ($url) {
                $this->getResponse()->setRedirect($url);
            } else {
                $this->_redirectReferer(Mage::helper('checkout/cart')->getCartUrl());
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update the item.'));
            Mage::logException($e);
            $this->_goBack();
        }
        $this->_redirect('*/*');
    }

    /**
     * Update shopping cart data action
     */
    public function updatePostAction()
    {
        if (!$this->_validateFormKey()) {
            $this->_redirect('*/*/');
            return;
        }

        $updateAction = (string)$this->getRequest()->getParam('update_cart_action');

        switch ($updateAction) {
            case 'empty_cart':
                $this->_emptyShoppingCart();
                break;
            case 'update_qty':
                $this->_updateShoppingCart();
                break;
            default:
                $this->_updateShoppingCart();
        }

        $this->_goBack();
    }

    /**
     * Update customer's shopping cart
     */
    protected function _updateShoppingCart()
    {
        try {
			$group_id = Mage::getSingleton('customer/session')->getCustomerGroupId();
			if(Mage::getSingleton('customer/session')->isLoggedIn() and ($group_id == 4)){
				$cartData = $this->getRequest()->getParam('cart');
				if (is_array($cartData)) {
					$filter = new Zend_Filter_LocalizedToNormalized(
						array('locale' => Mage::app()->getLocale()->getLocaleCode())
					);
					foreach ($cartData as $index => $data) {
						if (isset($data['qty'])) {
							$cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
						}
					}
					$cart = $this->_getCart();
					if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
						$cart->getQuote()->setCustomerId(null);
					}

					$cartData = $cart->suggestItemsQty($cartData);
					$cart->updateItems($cartData)
						->save();
				}
				$this->_getSession()->setCartWasUpdated(true);
			} else {
					/************************************************************************/
					$subscription_category 	= Mage::getModel('catalog/category')->load(4);
					$subscription_plans 	= Mage::getResourceModel('catalog/product_collection')
								->setStoreId(Mage::app()->getStore()->getId())
								->addCategoryFilter($subscription_category);
								
					//$subscription_prices = array();
					$plans_index = array();
					
					//cart quote ID 
					$quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
					if (!$quoteId){ $quoteId = 0; }
					
					//sql connection --> write
					$writeConnection = Mage::getSingleton('core/resource')->getConnection('core/write');
					$readConnection = Mage::getSingleton('core/resource')->getConnection('core/read');
					$sub_count = 0;
					foreach($subscription_plans as $subscription_plan){
						//deal with sub plans here.
						$plan_id = $subscription_plan->getId();
						$recurring_profile = Mage::getResourceModel('catalog/product')->getAttributeRawValue($plan_id, 'recurring_profile', $storeId);
						$sub_price = Mage::getResourceModel('catalog/product')->getAttributeRawValue($plan_id, 'price', $storeId);
						//print_r($attr . '<br>');
						$recurring_profile_unserialized = unserialize($recurring_profile);
						//*****Subscription Weekly Limit*******
						$sub_limit = $recurring_profile_unserialized['sub_weekly_limit'];
						$plans_index[$sub_limit] = $plan_id;
						//check if plan is going to be deleted
						$query_check = 'SELECT `qty` from `sales_flat_quote_item` WHERE `quote_id` = ' . $quoteId . ' and `product_id` = ' . $plan_id .';';
						//$query_check = 'SELECT `qty` from `sales_flat_quote_item` WHERE `quote_id` = 31 and `product_id` = 23 ;';
						$check = $readConnection->fetchOne($query_check);
						if ($check) $sub_count ++ ;
						//remove any subscription in the shopping cart first.
						//$cart->removeItem($plan_id);  --> not useful, using direct sql.
						$query_delete = 'DELETE FROM `sales_flat_quote_item` WHERE `quote_id` = '. $quoteId.' and `product_id` = '.$plan_id.';';
						$writeConnection->query($query_delete);
					} 
					echo $sub_count;

					$cartData = $this->getRequest()->getParam('cart');
					if (is_array($cartData)) {
						$filter = new Zend_Filter_LocalizedToNormalized(
							array('locale' => Mage::app()->getLocale()->getLocaleCode())
						);
						foreach ($cartData as $index => $data) {
							if (isset($data['qty'])) {
								$cartData[$index]['qty'] = $filter->filter(trim($data['qty']));
							}
						}
						$cart_qty = 0;// - $sub_count;
						foreach ($cartData as $data){
							$cart_qty += $data['qty'];
						}
						echo $cart_qty;
						//die;
						//print_r($plans_index);
						//die;
						$cart = $this->_getCart();
						if (key_exists((int)$cart_qty, $plans_index)){
							$cart->addProductsByIds(array($plans_index[$cart_qty]));
						}
						
						if (! $cart->getCustomerSession()->getCustomer()->getId() && $cart->getQuote()->getCustomerId()) {
							$cart->getQuote()->setCustomerId(null);
						}

						$cartData = $cart->suggestItemsQty($cartData);
						$cart->updateItems($cartData)
							->save();
					}
					$this->_getSession()->setCartWasUpdated(true);
					/************************************************************************/
			}
        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError(Mage::helper('core')->escapeHtml($e->getMessage()));
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $this->__('Cannot update shopping cart.'));
            Mage::logException($e);
        }
    }

    /**
     * Empty customer's shopping cart
     */
    protected function _emptyShoppingCart()
    {
        try {
            $this->_getCart()->truncate()->save();
            $this->_getSession()->setCartWasUpdated(true);
        } catch (Mage_Core_Exception $exception) {
            $this->_getSession()->addError($exception->getMessage());
        } catch (Exception $exception) {
            $this->_getSession()->addException($exception, $this->__('Cannot update shopping cart.'));
        }
    }

    /**
     * Delete shoping cart item action
     */
    public function deleteAction()
    {
        if ($this->_validateFormKey()) {
            $id = (int)$this->getRequest()->getParam('id');
            if ($id) {
                try {
				//validate plans when delete.
                    /*ORIGINAL METHOD
					$this->_getCart()->removeItem($id)->save(); */
					/*******************************************************************************/
					$cart   = $this->_getCart();
					//subscription category id "4"
					$subscription_category 	= Mage::getModel('catalog/category')->load(4);
					$subscription_plans 	= Mage::getResourceModel('catalog/product_collection')
								->setStoreId(Mage::app()->getStore()->getId())
								->addCategoryFilter($subscription_category);
								
					//$subscription_prices = array();
					$plans_index = array();
					
					//cart quote ID 
					$quoteId = Mage::getSingleton('checkout/session')->getQuoteId();
					if (!$quoteId){ $quoteId = 0; }
					
					//sql connection --> write
					$writeConnection = Mage::getSingleton('core/resource')->getConnection('core/write');
					//sql connection --> read
					$readConnection = Mage::getSingleton('core/resource')->getConnection('core/read');
					
					foreach($subscription_plans as $subscription_plan){
						//deal with sub plans here.
						$plan_id = $subscription_plan->getId();
						$recurring_profile = Mage::getResourceModel('catalog/product')->getAttributeRawValue($plan_id, 'recurring_profile', $storeId);
						$sub_price = Mage::getResourceModel('catalog/product')->getAttributeRawValue($plan_id, 'price', $storeId);
						//print_r($attr . '<br>');
						$recurring_profile_unserialized = unserialize($recurring_profile);
						//*****Subscription Weekly Limit*******//
						$sub_limit = $recurring_profile_unserialized['sub_weekly_limit'];
						$plans_index[$sub_limit] = $plan_id;
						
						//remove any subscription in the shopping cart first.
						//$cart->removeItem($plan_id);  --> not useful, using direct sql.
						$query_delete = 'DELETE FROM `sales_flat_quote_item` WHERE `quote_id` = '. $quoteId.' and `product_id` = '.$plan_id.';';
						$writeConnection->query($query_delete);
					}
					/*******************************************************************************/
					
					$query_itemqty = 'SELECT `qty` FROM `sales_flat_quote_item` WHERE `quote_id` = '. $quoteId.' and `item_id` = '.$id.';';
					$itemqty = $readConnection->fetchOne($query_itemqty);
					$cart->removeItem($id);
					//AUTOMATICALLY ADD/Change subscription plans in the shopping cart
					$cart_total = $this->_getCart()->getSummaryQty()-$itemqty;
					//echo $cart_total;
					//sdie;
					if (key_exists((int)$cart_total, $plans_index)){
					//if (key_exists((int)$cart_total, $plans_index)){
						$cart->addProductsByIds(array($plans_index[$cart_total]));
					}
					$cart->save();
                } catch (Exception $e) {
                    $this->_getSession()->addError($this->__('Cannot remove the item.'));
                    Mage::logException($e);
                }
            }
        } else {
            $this->_getSession()->addError($this->__('Cannot remove the item.'));
        }

        $this->_redirectReferer(Mage::getUrl('*/*'));
    }

    /**
     * Initialize shipping information
     */
    public function estimatePostAction()
    {
        $country    = (string) $this->getRequest()->getParam('country_id');
        $postcode   = (string) $this->getRequest()->getParam('estimate_postcode');
        $city       = (string) $this->getRequest()->getParam('estimate_city');
        $regionId   = (string) $this->getRequest()->getParam('region_id');
        $region     = (string) $this->getRequest()->getParam('region');

        $this->_getQuote()->getShippingAddress()
            ->setCountryId($country)
            ->setCity($city)
            ->setPostcode($postcode)
            ->setRegionId($regionId)
            ->setRegion($region)
            ->setCollectShippingRates(true);
        $this->_getQuote()->save();
        $this->_goBack();
    }

    /**
     * Estimate update action
     *
     * @return null
     */
    public function estimateUpdatePostAction()
    {
        $code = (string) $this->getRequest()->getParam('estimate_method');
        if (!empty($code)) {
            $this->_getQuote()->getShippingAddress()->setShippingMethod($code)/*->collectTotals()*/->save();
        }
        $this->_goBack();
    }

    /**
     * Initialize coupon
     */
    public function couponPostAction()
    {
        /**
         * No reason continue with empty shopping cart
         */
        if (!$this->_getCart()->getQuote()->getItemsCount()) {
            $this->_goBack();
            return;
        }

        $couponCode = (string) $this->getRequest()->getParam('coupon_code');
        if ($this->getRequest()->getParam('remove') == 1) {
            $couponCode = '';
        }
        $oldCouponCode = $this->_getQuote()->getCouponCode();

        if (!strlen($couponCode) && !strlen($oldCouponCode)) {
            $this->_goBack();
            return;
        }

        try {
            $codeLength = strlen($couponCode);
            $isCodeLengthValid = $codeLength && $codeLength <= Mage_Checkout_Helper_Cart::COUPON_CODE_MAX_LENGTH;

            $this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
            $this->_getQuote()->setCouponCode($isCodeLengthValid ? $couponCode : '')
                ->collectTotals()
                ->save();

            if ($codeLength) {
                if ($isCodeLengthValid && $couponCode == $this->_getQuote()->getCouponCode()) {
                    $this->_getSession()->addSuccess(
                        $this->__('Coupon code "%s" was applied.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                } else {
                    $this->_getSession()->addError(
                        $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->escapeHtml($couponCode))
                    );
                }
            } else {
                $this->_getSession()->addSuccess($this->__('Coupon code was canceled.'));
            }

        } catch (Mage_Core_Exception $e) {
            $this->_getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->_getSession()->addError($this->__('Cannot apply the coupon code.'));
            Mage::logException($e);
        }

        $this->_goBack();
    }

    /**
     * Minicart delete action
     */
    public function ajaxDeleteAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
        }
        $id = (int) $this->getRequest()->getParam('id');
        $result = array();
        if ($id) {
            try {
                $this->_getCart()->removeItem($id)->save();

                $result['qty'] = $this->_getCart()->getSummaryQty();

                $this->loadLayout();
                $result['content'] = $this->getLayout()->getBlock('minicart_content')->toHtml();

                $result['success'] = 1;
                $result['message'] = $this->__('Item was removed successfully.');
                Mage::dispatchEvent('ajax_cart_remove_item_success', array('id' => $id));
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error'] = $this->__('Can not remove the item.');
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }

    /**
     * Minicart ajax update qty action
     */
    public function ajaxUpdateAction()
    {
        if (!$this->_validateFormKey()) {
            Mage::throwException('Invalid form key');
        }
        $id = (int)$this->getRequest()->getParam('id');
        $qty = $this->getRequest()->getParam('qty');
        $result = array();
        if ($id) {
            try {
                $cart = $this->_getCart();
                if (isset($qty)) {
                    $filter = new Zend_Filter_LocalizedToNormalized(
                        array('locale' => Mage::app()->getLocale()->getLocaleCode())
                    );
                    $qty = $filter->filter($qty);
                }

                $quoteItem = $cart->getQuote()->getItemById($id);
                if (!$quoteItem) {
                    Mage::throwException($this->__('Quote item is not found.'));
                }
                if ($qty == 0) {
                    $cart->removeItem($id);
                } else {
                    $quoteItem->setQty($qty)->save();
                }
                $this->_getCart()->save();

                $this->loadLayout();
                $result['content'] = $this->getLayout()->getBlock('minicart_content')->toHtml();

                $result['qty'] = $this->_getCart()->getSummaryQty();

                if (!$quoteItem->getHasError()) {
                    $result['message'] = $this->__('Item was updated successfully.');
                } else {
                    $result['notice'] = $quoteItem->getMessage();
                }
                $result['success'] = 1;
            } catch (Exception $e) {
                $result['success'] = 0;
                $result['error'] = $this->__('Can not save item.');
            }
        }

        $this->getResponse()->setHeader('Content-type', 'application/json');
        $this->getResponse()->setBody(Mage::helper('core')->jsonEncode($result));
    }
	
	/*7-5-2016 by Chris
		
	*/
	public function startupPageSubmitAction(){
		//customer object
		$customer = Mage::getSingleton('customer/session')->getCustomer();
		//get cart obj
		$cart   = $this->_getCart();

		$subscription_plan = $_POST["subscription-plans"];
		switch($subscription_plan){
			case '5-per-week' :
				$plan_id = 41;
			case '3-per-week' :
				$plan_id = 3;
		}
		
		$membership_or_pot = $_POST["membership-or-pot"];
		switch($membership_or_pot){
			case 'membership' :
				$mem_id = 43;
			case 'pot' :
				$mem_id = 44;
		}
		
		$customer_preference = $_POST["customer-preference"];
		foreach($customer_preference as $pref){
			$customer->setData($pref, 1)->save();
		}
		$cart->addProductsByIds(array($plan_id,$mem_id));
		$meals_id = $_POST["meals"];
		foreach($meals_id as $meal_id){
			$meals_qty = $_POST["qty-selector-" . $meal_id];
			for($x = 0; $x < $meals_qty; $x++ ){
				$cart->addProductsByIds($meal_id);
			}
		}
		$cart->save();
		
		$this->_goBack();
		
	}
}
