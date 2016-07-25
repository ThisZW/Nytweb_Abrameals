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


class AW_AdvancedReviews_Helper_Data extends Mage_Core_Helper_Abstract
{
    /*
     * Proscons registry reference
     */
    const PC_REG_REF = 'advancedreviews_proscons_ref';

    const TYPE_PROS = 1;
    const TYPE_CONS = 2;
    const TYPE_USER = 3;

    const STATUS_DISABLED = 0;
    const STATUS_ENABLED = 1;

    const OWNER_ADMIN = 1;
    const OWNER_CUSTOMER = 2;

    /*
     * Compare param $version with magento version
     */

    public function checkVersion($version)
    {
        return version_compare(Mage::getVersion(), $version, '>=');
    }

    public function getprosconsItemName($type = null)
    {
        if ($type === null) {
            $index = Mage::helper('advancedreviews')->getConstPcRegRef();
        } else {
            $index = $type;
        }

        switch ($index) {
            case Mage::helper('advancedreviews')->getConstTypePros() :
                //pros
                $itemName = Mage::helper('advancedreviews')->__('Pros');
                break;

            case Mage::helper('advancedreviews')->getConstTypeCons() :
                //Cons
                $itemName = Mage::helper('advancedreviews')->__('Con');
                break;

            default:
                //Nothing
                $itemName = 'Same';
        }
        return $itemName;
    }

    public function getProsconsItemNameById($id)
    {
        if ($type = Mage::getModel('advancedreviews/proscons')->load($id)->getType()) {
            return $this->getprosconsItemName($type);
        }
        return 'Same';
    }

    public function getprosconsValue()
    {
        return Mage::registry(self::PC_REG_REF);
    }

    public function ispros()
    {
        return (Mage::registry(self::PC_REG_REF) === self::TYPE_PROS);
    }

    public function isCons()
    {
        return (Mage::registry(self::PC_REG_REF) === self::TYPE_CONS);
    }

    public function isUser()
    {
        return (Mage::registry(self::PC_REG_REF) === self::TYPE_USER);
    }

    public function getConstEnabled()
    {
        return self::STATUS_ENABLED;
    }

    public function getConstDisabled()
    {
        return self::STATUS_DISABLED;
    }

    public function getConstTypePros()
    {
        return self::TYPE_PROS;
    }

    public function getConstTypeCons()
    {
        return self::TYPE_CONS;
    }

    public function getConstTypeUser()
    {
        return self::TYPE_USER;
    }

    public function getConstOwnerAdmin()
    {
        return self::OWNER_ADMIN;
    }

    public function getConstOwnerUser()
    {
        return self::OWNER_CUSTOMER;
    }

    public function getConstPcRegRef()
    {
        return self::PC_REG_REF;
    }

    public function confAllowOnlyLogged()
    {
        return !Mage::getStoreConfig('catalog/review/allow_guest');
    }

    public function confAllowOnlyLoggedToVote()
    {
        return Mage::getStoreConfig('advancedreviews/access_options/allow_only_logged_vote');
    }

    public function confAllowOnlySold()
    {
        return Mage::getStoreConfig('advancedreviews/access_options/allow_only_sold');
    }

    public function confAllowOnlyLoggedToAbuse()
    {
        return Mage::getStoreConfig('advancedreviews/access_options/allow_only_logged_abuse');
    }

    public function confShowOrdering()
    {
        return Mage::getStoreConfig('advancedreviews/display_options/show_ordering')
        && Mage::getStoreConfig('advancedreviews/ordering_options/ordering_items');
    }

    public function confShowHelpfulness()
    {
        return Mage::getStoreConfig('advancedreviews/display_options/show_helpfulness');
    }

    public function confShowReportAbuse()
    {
        return Mage::getStoreConfig('advancedreviews/display_options/show_reportabuse');
    }

    public function confShowSocialShare()
    {
        return Mage::getStoreConfig('advancedreviews/display_options/show_socialshare');
    }

    public function confShowAllMyLinks()
    {
        return Mage::getStoreConfig('advancedreviews/display_options/show_allmylinks');
    }

    public function confShowProscons()
    {
        return Mage::getStoreConfig('advancedreviews/display_options/show_prosandcons');
    }

    public function confShowRecommend()
    {
        return Mage::getStoreConfig('advancedreviews/display_options/show_recommend');
    }

    public function confSocialHtmlBlock()
    {
        return Mage::getStoreConfig('advancedreviews/social_options/social_block_text');
    }

    public function confProsDisplayCount()
    {
        return Mage::getStoreConfig('advancedreviews/proscons_options/pros_display_count');
    }

    public function confConsDisplayCount()
    {
        return Mage::getStoreConfig('advancedreviews/proscons_options/cons_display_count');
    }

    public function confEnableUserDefined()
    {
        return Mage::getStoreConfig('advancedreviews/proscons_options/enable_user_defined');
    }

    public function confEnableProsconsModeration()
    {
        return Mage::getStoreConfig('advancedreviews/proscons_options/enable_proscons_moderation');
    }

    public function confOrderingItemsKeysArray()
    {
        return explode(',', Mage::getStoreConfig('advancedreviews/ordering_options/ordering_items'));
    }

    public function confRecommendItemsArray()
    {
        $resArray = array();
        if (Mage::getStoreConfig('advancedreviews/recommend_options/recommend_field1')) {
            $resArray[] = array(
                'value' => 1,
                'label' => Mage::getStoreConfig('advancedreviews/recommend_options/recommend_field1')
            );
        }
        if (Mage::getStoreConfig('advancedreviews/recommend_options/recommend_field2')) {
            $resArray[] = array(
                'value' => 2,
                'label' => Mage::getStoreConfig('advancedreviews/recommend_options/recommend_field2')
            );
        }
        if (Mage::getStoreConfig('advancedreviews/recommend_options/recommend_field3')) {
            $resArray[] = array(
                'value' => 3,
                'label' => Mage::getStoreConfig('advancedreviews/recommend_options/recommend_field3')
            );
        }
        if (Mage::getStoreConfig('advancedreviews/recommend_options/recommend_field4')) {
            $resArray[] = array(
                'value' => 4,
                'label' => Mage::getStoreConfig('advancedreviews/recommend_options/recommend_field4')
            );
        }
        if (Mage::getStoreConfig('advancedreviews/recommend_options/recommend_field5')) {
            $resArray[] = array(
                'value' => 5,
                'label' => Mage::getStoreConfig('advancedreviews/recommend_options/recommend_field5')
            );
        }
        return $resArray;
    }

    public function confOrderingItemsArray()
    {
        if ($this->confOrderingItemsAnyoneSelected()) {
            $keysArray = $this->confOrderingItemsKeysArray();
            $resArray = array();
            foreach (Mage::getModel('advancedreviews/system_config_source_ordering_items')->toOptionArray() as $item) {
                if (in_array($item['value'], $keysArray)) {
                    $resArray[] = array('value' => $item['value'], 'label' => $this->__($item['label']));
                }
            }
            return $resArray;
        } else {
            return array();
        }
    }

    public function confOrderingItemsAnyoneSelected()
    {
        return count($this->confOrderingItemsKeysArray());
    }

    public function isUserLogged()
    {
        return Mage::getSingleton('customer/session')->isLoggedIn();
    }

    public function isProductSold()
    {
        if (!Mage::registry('product')) {
            return false;
        }

        $orders = Mage::getResourceModel('sales/order_collection')
            ->addAttributeToSelect('*')
            ->addAttributeToFilter('customer_id', Mage::getSingleton('customer/session')->getCustomer()->getId())
            ->addAttributeToFilter(
                'state', array('in' => Mage::getSingleton('sales/order_config')->getVisibleOnFrontStates())
            )
            ->addAttributeToFilter('status', array('in' => explode(',', $this->getOrderStats())))
            ->addAttributeToSort('created_at', 'desc')
        ;

        $product = Mage::registry('product');
        $productIds = array_merge(array($product->getId()), $this->_getProductChildrenIds($product));
        foreach ($orders as $order) {
            $items = $order->getItemsCollection();
            foreach ($items as $item) {
                if (in_array($item->getProductId(), $productIds)) {
                    return true;
                }

            }
        }
        return false;
    }

    /**
     * @param Mage_Catalog_Model Product $product
     *
     * @return array
     */
    protected function _getProductChildrenIds($product) {
        $childrenIds = array();
        foreach ($product->getTypeInstance()->getChildrenIds($product->getId()) as $groupedChildIds) {
            $childrenIds = array_merge($childrenIds, $groupedChildIds);
        }
        return $childrenIds;
    }

    public function getCurrentOrdering()
    {
        $instance = new AW_AdvancedReviews_Block_Ordering;
        $ordering = $instance->getOrdering();
        unset($instance);
        return $ordering;
    }

    public function getCurrentSortDir()
    {
        $instance = new AW_AdvancedReviews_Block_Ordering;
        $sortDir = strtoupper($instance->getSort());
        unset($instance);
        return $sortDir;
    }

    public function setViewBackPack($value)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $session->setViewBackPack($value);
        return $this;
    }

    public function getViewBackPack()
    {
        return Mage::getSingleton('customer/session', array('name' => 'frontend'))->getViewBackPack();
    }

    public function setReviewsBackUrl($value)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $session->setReviewsBackUrl($value);
        return $this;
    }

    public function getReviewsBackUrl()
    {
        return Mage::getSingleton('customer/session', array('name' => 'frontend'))->getReviewsBackUrl();
    }

    public function noHelpfulness($reviewId)
    {
        Mage::getModel('advancedreviews/helpfulness')
            ->setReviewId($reviewId)
            ->setValue(0)
            ->setCustomerId(Mage::getSingleton('customer/session')->getId())
            ->save()
        ;
        return $this;
    }

    public function yesHelpfulness($reviewId)
    {
        Mage::getModel('advancedreviews/helpfulness')
            ->setReviewId($reviewId)
            ->setValue(1)
            ->setCustomerId(Mage::getSingleton('customer/session')->getId())
            ->save()
        ;
        return $this;
    }

    public function addAbuse($reviewId, $customerId = null)
    {
        if ($customerId === null) {
            $customerName = $this->__('Guest');
        } else {
            $customerName = $this->getCustomerNicknameById($customerId);
        }

        $abuse = Mage::getModel('advancedreviews/abuse');
        $abuse->setReviewId($reviewId)->setCustomerName($customerName)
            ->setCustomerId($customerId)
            ->setStoreId(Mage::helper('core')->getStoreId())
            ->setAbusedAt(now())
            ->save();

        return $this;
    }

    public function getProsconsState($key)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $pcState = $session->getProsconsState();
        if (isset($pcState[$key])) {
            return $pcState[$key];
        } else {
            return 0;
        }
    }

    public function setProsconsState($key, $value)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $pcState = $session->getProsconsState();
        if ($pcState === null) {
            $pcState = array();
        }
        $pcState[$key] = $value;
        $session->setProsconsState($pcState);
        return $this;
    }

    public function resetProscons()
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $session->setProscons(array());
        return $this;
    }

    public function registerProscons($prosconsId)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $proscons = $session->getProscons();

        if (count($proscons)) {
            $proscons[] = $prosconsId;
        } else {
            $proscons = array($prosconsId);
        }
        $session->setProscons($proscons);

        return $this;
    }

    public function unregisterProscons($prosconsId)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $proscons = $session->getProscons();
        if (isset($proscons)) {
            if (in_array($prosconsId, $proscons)) {
                foreach ($proscons as $key => $prosconsItem) {
                    if ($prosconsItem == $prosconsId) {
                        $index = $key;
                    }
                }
                if (isset($proscons[$index])) {
                    unset($proscons[$index]);
                }
            }
            $session->setProscons($proscons);
        }
        return $this;
    }

    public function getAllProscons()
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        return $session->getProscons();
    }

    public function setAllProscons($proscons)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $session->setProscons($proscons);
        return $this;
    }

    public function isProsconsRegistered($prosconsId)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $proscons = $session->getProscons();
        if (isset($proscons)) {
            return in_array($prosconsId, $proscons);
        } else {
            return false;
        }
    }

    public function registerAbuse($reviewId, $customerId)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $reviews = $session->getAbuseReviews();

        if (count($reviews)) {
            $reviews[] = $customerId . ':' . $reviewId;
        } else {
            $reviews = array($customerId . ':' . $reviewId);
        }
        $session->setAbuseReviews($reviews);

        return $this;
    }

    public function isAbuseRegistered($reviewId)
    {
        if ($userId = Mage::getSingleton('customer/session')->getCustomer()->getId()) {
            $abuse = Mage::getSingleton('advancedreviews/abuse')->getCollection();
            $abuse
                ->getSelect()
                ->where('customer_id = ?', $userId)
                ->where('review_id = ?', $reviewId);
            return count($abuse->getData());
        } else {
            return $this->isMarkedAbuse($reviewId);
        }
    }

    public function registerHelpfulness($reviewId)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $reviews = $session->getHelpfulnessReviews();

        if (count($reviews)) {
            $reviews[] = $reviewId;
        } else {
            $reviews = array($reviewId);
        }
        $session->setHelpfulnessReviews($reviews);

        return $this;
    }

    public function isHelpfulnessRegistered($reviewId)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $reviews = $session->getHelpfulnessReviews();
        if (isset($reviews)) {
            return in_array($reviewId, $reviews);
        } else {
            if ($userId = Mage::getSingleton('customer/session')->getCustomer()->getId()) {
                $helpfulness = Mage::getSingleton('advancedreviews/helpfulness')->getCollection();
                $helpfulness
                    ->getSelect()
                    ->where('customer_id = ?', $userId)
                    ->where('review_id = ?', $reviewId);
                return count($helpfulness->getData());
            } else {
                return false;
            }
        }
    }

    public function getCustomerNicknameById($customerId)
    {
        if ($customerId) {
            if ($customer = Mage::getModel('customer/customer')->load($customerId)) {
                return $customer->getFirstname() . " " . $customer->getLastname();
            }
            return null;
        } else {
            return null;
        }
    }

    public function getReviewUrl($id)
    {
        return Mage::getUrl('review/product/view', array('id' => $id));
    }

    public function getReviewTitle($id)
    {
        return Mage::getModel('review/review')->load($id)->getTitle();
    }

    /**
     * Get current websites IDs string '1','2',etc for current store
     * @return string
     */
    public function getStoreIds()
    {
        $str = '';
        $isNotFirst = false;
        if ($stores = Mage::app()->getStores()) {
            foreach ($stores as $store) {
                if ($isNotFirst) {
                    $str .= ',';
                }
                $str .= $store->getStoreId();
                $isNotFirst = true;
            }
        }
        return $str;
    }

    public function getProsconsIds()
    {
        $str = '';
        $isNotFirst = false;
        if ($proscons = $this->getAllProscons()) {
            foreach ($proscons as $prosconsId) {
                if ($isNotFirst) {
                    $str .= ',';
                }
                $str .= $prosconsId;
                $isNotFirst = true;
            }
        }
        return $str;
    }

    public function setProsconsPlace($place)
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        $session->setProsconsPlace($place);
        return $this;
    }

    public function getProsconsPlace()
    {
        $session = Mage::getSingleton('customer/session', array('name' => 'frontend'))->start();
        return $session->getProsconsPlace();
    }

    /**
     * Retrieves Advanced Reviews Disabled Flag
     *
     * @return boolean
     */
    public function getExtDisabled()
    {
        return Mage::getStoreConfig('advanced/modules_disable_output/AW_AdvancedReviews');
    }

    public function getOrderStats()
    {
        return Mage::getStoreConfig('advancedreviews/access_options/order_status');
    }

    public function getFuReviewUrl($reviewId)
    {
        $productId = Mage::getSingleton('review/review')->load($reviewId)->getEntityPkValue();

        $url = $this->getProductRewriteUrl($productId);
        if (strpos($url, '.') !== false) {
            list($productLink, $ext) = explode('.', $url);
            $reviewUrl = $productLink . AW_AdvancedReviews_Model_Urlrewrite::review_suffix . $reviewId . '.' . $ext;
        } else {
            $reviewUrl = $url . AW_AdvancedReviews_Model_Urlrewrite::review_suffix . $reviewId;
        }

        $url = Mage::app()->getStore()->getBaseUrl() . $reviewUrl;
        return $url;
    }

    public function getFuReviewsUrl($productId, $categoryId = null)
    {
        if ($path = $this->getProductRewriteUrl($productId, $categoryId)) {
            if (strpos($path, '.') !== false) {
                return Mage::app()->getStore()->getBaseUrl() . substr($path, 0, strpos($path, '.'))
                . AW_AdvancedReviews_Model_Urlrewrite::page_suffix . strstr($path, '.');
            } else {
                return Mage::app()->getStore()->getBaseUrl() . $path . AW_AdvancedReviews_Model_Urlrewrite::page_suffix;
            }
        } else {
            return false;
        }
    }

    public function getProductRewriteUrl($productId, $categoryId = null)
    {
        $collection = Mage::getModel('core/url_rewrite')->getCollection();
        $collection->getSelect()
            ->where('product_id = ?', $productId)
            ->where('store_id = ?', Mage::app()->getStore()->getId());
        if ($categoryId) {
            $collection->getSelect()->where('category_id = ?', $categoryId);
        } else {
            $collection->getSelect()->where('category_id IS NULL');
        }

        $pathArray = Mage::app()->getRequest()->getAliases();
        if (isset($pathArray['rewrite_request_path'])) {
            $collection->getSelect()->where('request_path=?', $pathArray['rewrite_request_path']);
        }

        if ($collection->getData()) {
            foreach ($collection->getData() as $rule) {
                $path = $rule['request_path'];
            }
            return $path;
        }
        return '';
    }

    public function getRssEnabled()
    {
        return (Mage::getStoreConfigFlag('advancedreviews/rss_options/enabled')
            && Mage::getStoreConfigFlag(
                'rss/config/active'
            )) ? true : false;
    }

    public function getRssRoute($categoryId = null, $productId = null)
    {
        $route = 'advancedreviews/rss';

        if ($productId) {
            $route = 'advancedreviews/rss/index/product/' . $productId . '/';
        }
        if ($categoryId) {
            $route = 'advancedreviews/rss/index/category/' . $categoryId . '/';
        }

        return $route;
    }

    public function addRss($head, $path, $title)
    {
        if ($head instanceof Mage_Page_Block_Html_Head) {
            $head->addItem("rss", $path, 'title="' . $title . '"');
        }
    }

    public function getSummaryBlockEnabled()
    {
        return Mage::getStoreConfigFlag('advancedreviews/summary_block/enabled');
    }

    public function getAntiSpamEnabled()
    {
        return Mage::getStoreConfigFlag('advancedreviews/antispam/enabled');
    }

    public function markAbused($reviewId)
    {
        $cookies = Mage::getModel('core/cookie');
        $abuse = $cookies->get('abuse');
        $abuse = explode(',', $abuse);

        if (!in_array($reviewId, $abuse)) {
            $abuse = implode(',', $abuse);
            $cookies->set('abuse', $abuse . ($abuse ? ',' : '') . $reviewId, '315360000');
        }
    }

    public function isMarkedAbuse($reviewId)
    {
        $cookies = Mage::getModel('core/cookie');
        $abuse = explode(',', $cookies->get('abuse'));
        return in_array($reviewId, $abuse);
    }

    public function getPagerToolbar($product = null, $reviews = null, $page = null, $limit = null)
    {
        $block = Mage::app()->getLayout()->createBlock('advancedreviews/ajax_pager')->setTemplate(
            'advancedreviews/ajax/pager.phtml'
        );
        $collection = Mage::getModel('review/review')->getCollection();
        $collection->getSelect()->where('status_id = 1');
        if ($product) {
            $collection->getSelect()->where('entity_pk_value = ?', $product);
        }

        if (is_array($reviews)) {
            $reviews = implode(',', $reviews);
        }

        //FIX FOR ADVANCEDREVIEWS-181
        $reviews = explode(',', $reviews);
        $filteredReviews = array();
        if (count($reviews) > 0) {
            foreach ($reviews as $review) {
                if ((int)$review != 0) {
                    $filteredReviews[] = (int)$review;
                }
            }
        }
        $reviews = implode(',', $filteredReviews);
        //END FIX

        if ($reviews) {
            $collection->getSelect()->where('main_table.review_id IN (' . $reviews . ')');
        }
        if ($page) {
            $block->setCurrentPage($page);
        }

        $collection->setCurPage($page);
        $collection->setPageSize($limit);
        $block->setCollection($collection);
        return $block->toHtml();
    }

    public function getFilteredReviews(
        $product, $reviews, $page = 1, $limit = 10, $sortBy = null, $sortDir = 'DESC', $customer = null
    )
    {
        $block = Mage::app()->getLayout()->createBlock('advancedreviews/ajax_reviews')->setTemplate(
            'advancedreviews/ajax/reviews.phtml'
        );
        $collection = Mage::getResourceModel('advancedreviews/review')->getFilteredReviews(
            $product, $reviews, $page, $limit, $sortBy, $sortDir, $customer
        );
        $block->setCollection($collection);

        return $block->toHtml();
    }

    public function getSortBar()
    {
        $block = Mage::app()->getLayout()->createBlock('advancedreviews/ajax_ordering')->setTemplate(
            'advancedreviews/ajax/ordering.phtml'
        );
        return $block->toHtml();
    }
}