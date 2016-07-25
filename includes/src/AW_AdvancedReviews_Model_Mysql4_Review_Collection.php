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


class AW_AdvancedReviews_Model_Mysql4_Review_Collection extends Mage_Review_Model_Mysql4_Review_Collection
{
    const ORDER_BY_DATE = 'by_date';
    const ORDER_BY_RATING = 'by_rating';
    const ORDER_BY_HELPFULNESS = 'by_helpfulness';

    protected $_ordering;
    protected $_sortDir;
    protected $_sortedItems;
    protected $_count;
    protected $_notIsFirst;
    protected $_currentStoreId;
    protected $_currentProductId;

    protected $_prosconsVoteTable;
    protected $_helpfulnessTable;
    protected $_recommendTable;

    public function __construct()
    {
        parent::__construct();
        $this->_prosconsVoteTable = $this->getTableName('advancedreviews/proscons_vote');
        $this->_helpfulnessTable = $this->getTableName('advancedreviews/helpfulness');
        $this->_recommendTable = $this->getTableName('advancedreviews/recommend');
    }

    public function addStoreFilter($storeId)
    {
        $this->_currentStoreId = $storeId;
        return parent::addStoreFilter($storeId);
    }

    public function addEntityFilter($entity, $pkValue)
    {
        if ($entity == 'product') {
            $this->_currentProductId = $pkValue;
        }
        return parent::addEntityFilter($entity, $pkValue);
    }

    public function setCurrentOrdering()
    {
        $this->_ordering = Mage::helper('advancedreviews')->getCurrentOrdering();
        $this->_sortDir = Mage::helper('advancedreviews')->getCurrentSortDir();
    }

    public function clearOrdering()
    {
        $this->_orders = array();
        return $this;
    }

    public function getTableName($model)
    {
        $resources = Mage::getSingleton('core/resource');
        return $resources->getTableName($model);
    }

    public function setDateOrder($dir = 'DESC')
    {
        $this->setCurrentOrdering();

        if ($this->_ordering === self::ORDER_BY_DATE) {
            $this->clearOrdering()->setOrder('main_table.created_at', $this->_sortDir);
        } elseif ($this->_ordering === self::ORDER_BY_RATING) {
            if (!$this->_notIsFirst) {
                $ratingTableName = $this->getTableName('rating/rating_option_vote');
                $this->_select->joinInner(
                    array('ov' => $ratingTableName), '(ov.review_id = main_table.review_id)',
                    array('summa' => 'SUM(percent)')
                )->group('ov.review_id');
                $this->clearOrdering()->setOrder('summa', $this->_sortDir);
                $this->_notIsFirst = true;
            }
        } elseif ($this->_ordering === self::ORDER_BY_HELPFULNESS) {
            if (!$this->_notIsFirst) {
                $this->getSelect()
                    ->joinLeft(
                        array('helpfulness' => $this->_helpfulnessTable),
                        'main_table.review_id = helpfulness.review_id',
                        array('all_count' => 'COUNT(helpfulness.id)', 'yes_count' => 'SUM(helpfulness.value)')
                    )
                    ->group('main_table.review_id');
                $this->clearOrdering()->setOrder('yes_count', $this->_sortDir);
                $this->_notIsFirst = true;
            }
        }
        return $this;
    }

    public function getItems()
    {
        $this->setDateOrder();
        return parent::getItems();
    }

    /*
     * Return count of customer Reviews
     * @var integer
     */
    public function getCustomerReviewsCount($customerId)
    {
        if ($conn = $this->getConnection()) {
            $storeId = Mage::app()->getStore()->getId();
            $select = $conn->select()
                ->from(array('main_table' => $this->_reviewTable), 'COUNT( * )')
                ->joinInner(
                    array('detail' => $this->_reviewDetailTable), 'main_table.review_id = detail.review_id', array()
                )
                ->joinInner(
                    array('store' => $this->_reviewStoreTable), 'main_table.review_id = store.review_id', array()
                )
                ->joinInner(
                    array('status' => $this->_reviewStatusTable), 'main_table.status_id = status.status_id', array()
                )
                ->joinInner(
                    array('entity' => $this->_reviewEntityTable), 'main_table.entity_id = entity.entity_id', array()
                )
                ->where("store.store_id  IN ('{$storeId}')")
                ->where("status.status_code = 'approved'")
                ->where("entity.entity_code = 'product'")
                ->where("detail.customer_id = '{$customerId}'");
        }
        $sql = $select->__toString();
        $count = $this->getConnection()->fetchOne($sql);
        return intval($count);
    }

    public function isValidCustomer($customerId)
    {
        foreach ($this->getItems() as $_review) {
            if ($_review->getCustomerId() === $customerId) {
                return true;
            }
        }
        return false;
    }

    public function getSize()
    {
        if (is_null($this->_totalRecords)) {
            $sql = $this->getSelectCountSql();
            $arr = $this->getConnection()->fetchAll($sql, $this->_bindParams);
            if (count($arr) === 1) {
                $this->_totalRecords = $arr[0]['COUNT(*)'];
            } elseif (count($arr) > 1) {
                $this->_totalRecords = count($arr);
            } else {
                $this->_totalRecords = 0;
            }
        }
        return intval($this->_totalRecords);
    }

    public function addProsconsFilter()
    {
        if ($proscons = Mage::helper('advancedreviews')->getAllProscons()) {
            $subId = 0;
            foreach ($proscons as $prosconsId) {
                $this->getSelect()
                    ->join(
                        array('vote' . $subId => $this->_prosconsVoteTable),
                        '(vote' . $subId . '.review_id = main_table.review_id AND vote' . $subId . '.proscons_id = '
                        . $prosconsId . ')',
                        array()
                    );
                $subId++;
            }
            $this->getSelect()->group('main_table.review_id');
        }
        return $this;
    }

    /*
     * Add to collection helpfulness summary
     */
    public function addHelpfulnessSummary()
    {
        $this->getSelect()
            ->joinLeft(
                array('helpfulness' => $this->_helpfulnessTable),
                'main_table.review_id = helpfulness.review_id',
                array('all_count' => 'COUNT(helpfulness.id)', 'yes_count' => 'SUM(helpfulness.value)')
            )
            ->group('main_table.review_id');

        return $this;
    }

    public function addRecommendSummary()
    {
        $this->getSelect()
            ->join(
                array('recommend' => $this->_recommendTable),
                'main_table.review_id = recommend.review_id',
                array('recommend' => 'value')
            );
        return $this;
    }
}