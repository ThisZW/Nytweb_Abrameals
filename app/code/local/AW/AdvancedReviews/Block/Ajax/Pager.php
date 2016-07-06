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


class AW_AdvancedReviews_Block_Ajax_Pager extends Mage_Page_Block_Html_Pager
{
    protected $_currentPage = null;

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('advancedreviews/ajax/pager.phtml');
    }

    public function getPageUrl($page)
    {
        return $page;
    }

    public function getCurrentPage()
    {
        return ($this->_currentPage) ? $this->_currentPage : 1;
    }

    public function setCurrentPage($page)
    {
        $this->_currentPage = $page;
        return $this;
    }

    public function getLimitUrl($limit)
    {
        return '#advancereviews-pager" onclick="getReviews(\'1\',\'' . $limit . '\')';
    }

    public function setCollection($collection)
    {
        $this->_collection = $collection;
        return $this;
    }

    public function getCollection()
    {
        if (!$this->_collection) {
            $collection = Mage::getModel('review/review')->getCollection();
            $collection->getSelect()
                ->where('status_id = 1')
                ->join(
                    array('review_store' => $collection->getTableName('review/review_store')),
                    'main_table.review_id = review_store.review_id AND review_store.store_id = ' . Mage::app()
                        ->getStore()->getId(), array('true_store_id' => 'store_id')
                );
            if ($productID = $this->getRequest()->getParam('id')) {
                $collection->getSelect()->where('entity_pk_value = ?', $productID);
            }
            if ($customerID = $this->getRequest()->getParam('customerId')) {
                $collection->getSelect()->where('customer_id = ?', $customerID);
            }
            $this->_collection = $collection;
            $this->_collection->setPageSize(10);
        }
        return $this->_collection;
    }
}