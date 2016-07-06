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
 * Pros and Cons Collection
 */
class AW_AdvancedReviews_Model_Mysql4_Proscons_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    /**
     * Cached Pros and Cons Table name
     *
     * @var string
     */
    protected $_prosconsTable;

    /**
     * Cached Pros and Cons Store Table name
     *
     * @var string
     */
    protected $_prosconsStoreTable;

    /**
     * Cached Pros and Cons Votes Table name
     *
     * @var string
     */
    protected $_prosconsVoteTable;

    /**
     * Cached Review Table name
     *
     * @var string
     */
    protected $_reviewTable;

    /**
     * Cached Review Entity Table name
     *
     * @var string
     */
    protected $_reviewEntityTable;

    /**
     * Cached Review Summary Table name
     *
     * @var string
     */
    protected $_reviewEntitySummaryTable;

    /**
     * Cahced Review Details Table name
     *
     * @var string
     */
    protected $_reviewDetailTable;

    /**
     * Review Join Flag
     *
     * @var boolean
     */
    protected $_joinReviewOnce;

    /**
     * Add Store Data Flag
     *
     * @var boolean
     */
    protected $_addStoreDataFlag = false;

    /**
     * Class constrictor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_init('advancedreviews/proscons');
        $this->_prosconsTable = $this->getTable('advancedreviews/proscons');
        $this->_prosconsStoreTable = $this->getTable('advancedreviews/proscons_store');
        $this->_prosconsVoteTable = $this->getTable('advancedreviews/proscons_vote');
        $this->_reviewTable = $this->getTable('review/review');
        $this->_reviewEntityTable = $this->getTable('review/review_entity');
        $this->_reviewEntitySummaryTable = $this->getTable('review/review_aggregate');
        $this->_reviewDetailTable = $this->getTable('review/review_detail');

        $this->_joinReviewOnce = true;
    }

    /**
     * Add Approved Reviews Filter to Collection
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function useFilteredReviews()
    {
        if ($proscons = Mage::helper('advancedreviews')->getAllProscons()) {
            $reviews = Mage::getModel('review/review')->getCollection();

            $reviews
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter('approved')
                ->addProsconsFilter();

            if ($customerId = Mage::registry('advancedreviews_proscons_is_customer')) {
                $reviews->addCustomerFilter($customerId);
            } elseif ($productId = Mage::registry('advancedreviews_proscons_is_product')) {
                $reviews->addEntityFilter('product', $productId);
            }

            $isFirst = true;
            $reveiwIds = '';
            foreach ($reviews as $review) {
                $reveiwIds .= $isFirst ? '' : ',';
                $reveiwIds .= "'" . $review->getId() . "'";
                $isFirst = false;
            }
            $this->getSelect()->where('vote.review_id IN (' . $reveiwIds . ')');

        }
        return $this;
    }

    /**
     * Set Up Pros Filter to Collection
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function setProsFilter()
    {
        $this->getSelect()->where('main_table.type = ?', Mage::helper('advancedreviews')->getConstTypePros());
        return $this;
    }

    /**
     * Set Up Cons Filter to Collection
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function setConsFilter()
    {
        $this->getSelect()->where('main_table.type = ?', Mage::helper('advancedreviews')->getConstTypeCons());
        return $this;
    }

    /**
     * Set Filter to Collection
     *
     * @param string $type Pros or Cons Filter value
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function setProsconsFilter($type)
    {
        $this->getSelect()->where('main_table.type = ?', $type);
        return $this;
    }

    /**
     * Set Product Filter to Collection
     * [Product] ==> [Review] ==> [ProsAndCons]
     *
     * @param integer|string $productId Product Id
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function setProductFilter($productId)
    {
        if (!$this->_joinReviewOnce) {
            $this->getSelect()->join(
                array('review' => $this->_reviewTable), 'vote.review_id=review.review_id', array()
            );
            $this->_joinReviewOnce = true;
        }
        $this->getSelect()->where('review.entity_pk_value = ?', $productId);
        return $this;
    }

    /**
     * Add Customer filter to Collection
     * [Customer] ==> [Review] ==> [ProsAndCons]
     *
     * @param integer|string $customerId Custormer Id
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function setCustomerFilter($customerId)
    {
        if (!$this->_joinReviewOnce) {
            $this->getSelect()->join(
                array('review' => $this->_reviewTable), 'vote.review_id=review.review_id', array()
            );
            $this->_joinReviewOnce = true;
        }
        $this->getSelect()->join(
            array('detail' => $this->_reviewDetailTable), 'detail.review_id=review.review_id', array()
        )
            ->where('detail.customer_id = ?', $customerId);
        return $this;
    }

    /**
     * Add Store Filter to Collection
     *
     * @param integer|string $storeId Store Id
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function setStoreFilter($storeId)
    {
        $this->getSelect()->join(
            array('store' => $this->_prosconsStoreTable), 'main_table.id=store.proscons_id', array()
        );
        $this->getSelect()->where('store.store_id IN (?)', $storeId);
        return $this;
    }

    /**
     * Add ProsAndCons Status Filter to Collection
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function setStatusFilter()
    {
        $this->getSelect()->where('main_table.status = ?', Mage::helper('advancedreviews')->getConstEnabled());
        return $this;
    }

    /**
     * Add Review Filter to Collection
     *
     * @param integer|string $reviewId Review Id
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function setReviewFilter($reviewId)
    {
        $this->getSelect()->join(array('vote' => $this->_prosconsVoteTable), 'main_table.id=vote.proscons_id', array());
        $this->getSelect()->where('vote.review_id = ?', $reviewId);
        return $this;
    }

    /**
     * Add Votes data to Collection
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function addVoteCount()
    {
        if (!$this->_joinReviewOnce) {
            $this->getSelect()->join(
                array('review' => $this->_reviewTable), 'vote.review_id=review.review_id', array()
            );
            $this->_joinReviewOnce = true;
        }
        $this->getSelect()->join(
            array('vote' => $this->_prosconsVoteTable), 'main_table.id=vote.proscons_id',
            array('vote_count' => 'count( vote.proscons_id )')
        )
            ->group('vote.proscons_id')
            //Add join with reviews by status 'approved'(status_id = 1)
            //!!! Not stable! May be errors
            ->join(array('review' => $this->_reviewTable), 'vote.review_id=review.review_id', array())
            ->where('review.status_id = ?', 1);
        return $this;
    }

    /**
     * Add Ordering by Sort Order
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function addOrdering()
    {
        $this->getSelect()->order('main_table.sort_order ASC');
        return $this;
    }

    /**
     * Load Collection
     *
     * @param boolean $printQuery
     * @param boolean $logQuery
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function load($printQuery = false, $logQuery = false)
    {
        if ($this->isLoaded()) {
            return $this;
        }
        parent::load($printQuery, $logQuery);

        if ($this->_addStoreDataFlag) {
            $this->_addStoreData();
        }
    }

    /**
     * Add Store Data to Collection
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    public function addStoreData()
    {
        $this->_addStoreDataFlag = true;
        $this->getSelect()->group('main_table.id');
        return $this;
    }

    /**
     * Add Store Data to Collection
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Proscons_Collection
     */
    protected function _addStoreData()
    {
        $prosconsIds = $this->getColumnValues('id');

        $stores = array();
        if (count($prosconsIds) > 0) {
            $select = $this->getConnection()->select()
                ->from($this->_prosconsStoreTable)
                ->where('proscons_id IN (?)', $prosconsIds);
            $result = $this->getConnection()->fetchAll($select);
            if (count($result)) {
                foreach ($result as $row) {
                    if (!isset($stores[$row['proscons_id']])) {
                        $stores[$row['proscons_id']] = array();
                    }
                    $stores[$row['proscons_id']][] = $row['store_id'];
                }
            }
        }
        foreach ($this as $item) {
            if (isset($stores[$item->getId()])) {
                $item->setStores($stores[$item->getId()]);
            } else {
                $item->setStores(array());
            }
        }
    }

    /**
     * Retrives Count of Collection Rows
     *
     * @return integer
     */
    public function getSize()
    {
        $select = clone $this->getSelect();
        $select->reset(Zend_Db_Select::GROUP);
        $select->reset(Zend_Db_Select::LIMIT_COUNT);
        $select->reset(Zend_Db_Select::LIMIT_OFFSET);
        $select->reset(Zend_Db_Select::ORDER);
        $select->reset(Zend_Db_Select::COLUMNS);
        $select->columns('main_table.id');
        $select->group('main_table.id');
        return ($count = count($this->getConnection()->fetchAll($select))) ? (int)$count : 0;
    }
}