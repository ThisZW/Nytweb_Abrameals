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
 * Add Pros and Cons functional to Mage_Review_Model_Mysql4_Review
 */
class AW_AdvancedReviews_Model_Mysql4_Review extends Mage_Review_Model_Mysql4_Review
{
    /**
     * Database Table Name of Pros and Cons Votes
     *
     * @var String
     */
    protected $_prosconsVoteTable;

    /**
     * Votes that had been insetred with current review
     *
     * @var array
     */
    protected $_insertedVotes = array();

    /**
     * Modify constructor to get "advancedreviews/proscons_vote" table name
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_prosconsVoteTable = $this->getTable('advancedreviews/proscons_vote');
    }

    /**
     * Returns max order for new Pros and Cons item
     *
     * @param integer $type AW_AdvancedReviews_Helper_Data::TYPE_PROS
     *                      or AW_AdvancedReviews_Helper_Data::TYPE_CONS
     *                      or AW_AdvancedReviews_Helper_Data::TYPE_USER
     *
     * @return integer
     */
    protected function _getMaxOrder($type)
    {
        $proscons = Mage::getModel('advancedreviews/proscons')
            ->getCollection()
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->setProsconsFilter($type)
        ;

        $max = -99999;
        foreach ($proscons as $item) {
            $max = ($item->getSortOrder() > $max) ? $item->getSortOrder() : $max;
        }
        return ($max === -99999) ? 0 : $max;
    }

    /**
     * Returns Pros and Cons Item Id if it exists
     * else returns 0
     *
     * @param String  $name Name of Pros and Cons Item
     * @param integer $type AW_AdvancedReviews_Helper_Data::TYPE_PROS
     *                      or AW_AdvancedReviews_Helper_Data::TYPE_CONS
     *                      or AW_AdvancedReviews_Helper_Data::TYPE_USER
     *
     * @return integer
     */
    protected function _isProsconsExists($name, $type)
    {
        $proscons = Mage::getModel('advancedreviews/proscons')
            ->getCollection()
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->setProsconsFilter($type)
        ;

        foreach ($proscons as $item) {
            if ($item->getName() === $name) {
                return $item->getId();
            }
        }
        return 0;
    }

    /**
     * Add Pros and Cons item for Saved review to Database
     *
     * @param String                   $name   Name of Pros and Cons Item
     * @param integer                  $type   AW_AdvancedReviews_Helper_Data::TYPE_PROS
     *                                         or AW_AdvancedReviews_Helper_Data::TYPE_CONS
     *                                         or AW_AdvancedReviews_Helper_Data::TYPE_USER
     * @param Mage_Core_Model_Abstract $object Model object with saved review
     *
     * @return AW_AdvancedReviews_Model_Mysql4_Review
     */
    protected function _addUserProscons($name, $type, $object)
    {
        if ($name) {
            //Adding proscons
            $prosconsId = $this->_isProsconsExists($name, $type);
            if (!$prosconsId) {
                $status = Mage::helper('advancedreviews')->confEnableProsconsModeration()
                    ?
                    Mage::helper('advancedreviews')->getConstDisabled()
                    :
                    Mage::helper('advancedreviews')->getConstEnabled();

                $proscons = Mage::getModel('advancedreviews/proscons');

                try {
                    $proscons = Mage::getModel('advancedreviews/proscons');
                    //save Proscons data
                    $proscons
                        ->setName($name)
                        ->setStatus($status)
                        ->setSortOrder($this->_getMaxOrder($type) + 1)
                        ->setOwner(Mage::helper('advancedreviews')->getConstOwnerUser())
                        ->setType($type)
                        ->setStores(array(Mage::app()->getStore()->getId()))
                        ->save();
                } catch (Exception $e) {
                    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                }

                $prosconsId = $proscons->getId();
            }
            if (!in_array($prosconsId, $this->_insertedVotes)) {
                $this->_insertedVotes[] = $prosconsId;
                $voteInsert = array(
                    'proscons_id' => $prosconsId,
                    'review_id'   => $object->getId()
                );

                $this->_getWriteAdapter()->insert($this->_prosconsVoteTable, $voteInsert);
            }
        }
        return $this;
    }

    /**
     * Add HDU integration and Pros and Cons logic after Save of Review
     *
     * @param Mage_Core_Model_Abstract $object Saved Review
     *
     * @return Mage_Review_Model_Mysql4_Review
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        if ($proscons = Mage::registry('advancedreviews_proscons_new_items')) {
            if (count($proscons)) {
                foreach ($proscons as $prosconsId) {
                    $this->_insertedVotes[] = $prosconsId;
                    $voteInsert = array(
                        'proscons_id' => $prosconsId,
                        'review_id'   => $object->getId()
                    );
                    $this->_getWriteAdapter()->insert($this->_prosconsVoteTable, $voteInsert);
                }
            }
        }
        # Add user-defined proscons
        if (Mage::helper('advancedreviews')->confEnableUserDefined()) {
            # Adding pros
            if ($userPros = Mage::registry('advancedreviews_proscons_user_pros')) {
                $userPros = trim($userPros);
                $pros = explode(',', $userPros);

                if (count($pros)) {
                    for ($i = 0; $i < count($pros); $i++) {
                        $pros[$i] = htmlspecialchars(trim($pros[$i]));
                    }
                }
                $pros = array_unique($pros);
                foreach ($pros as $prosName) {
                    $this->_addUserProscons($prosName, Mage::helper('advancedreviews')->getConstTypePros(), $object);
                }
            }
            # Adding cons
            if ($userCons = Mage::registry('advancedreviews_proscons_user_cons')) {
                $userCons = trim($userCons);
                $cons = explode(',', $userCons);

                if (count($cons)) {
                    for ($i = 0; $i < count($cons); $i++) {
                        $cons[$i] = htmlspecialchars(trim($cons[$i]));
                    }
                }
                $cons = array_unique($cons);
                foreach ($cons as $consName) {
                    $this->_addUserProscons($consName, Mage::helper('advancedreviews')->getConstTypeCons(), $object);
                }
            }
        }
        # Add recommend
        if (Mage::helper('advancedreviews')->confEnableUserDefined()
            && ($value = Mage::registry('advancedreviews_recommend_value'))
        ) {
            Mage::getModel('advancedreviews/recommend')->setReviewId($object->getId())->setValue($value)->save();
        }
        # Add HDU Ticket for new review
        $helper = Mage::helper('advancedreviews/hdu');
        if ($helper->isHDUActive() && $helper->isHDUEnabled() && $this->_isNewReview()) {
            try {
                Mage::helper('advancedreviews/hdu')->addTicket(new Varien_Object($object->getData()));
            } catch (Exception $e) {
                //DO Nothing
            }
        }
        # Add email notifications
        $notify = Mage::helper('advancedreviews/notification');
        if ($notify->getEnabled() && $this->_isNewReview()) {
            $notify->sendNotification(new Varien_Object($object->getData()));
        }
        return parent::_afterSave($object);
    }

    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        Mage::unregister('aw_advancdereviews_hduintegrate_review_id');
        Mage::register('aw_advancdereviews_hduintegrate_review_id', $object->getReviewId(), true);
        return parent::_beforeSave($object);
    }

    /**
     * Check for new review
     *
     * @return boolean
     */
    protected function _isNewReview()
    {
        return Mage::registry('aw_advancdereviews_hduintegrate_review_id') == null;
    }

    public function getReviewsByProscons($proscons = null, $storeId = null, $productId = null, $customerId = null)
    {
        $reviews = Mage::getModel('review/review')->getCollection();
        $reviews->getSelect()
            ->joinLeft(
                array('proscons' => $this->getTable('advancedreviews/proscons_vote')),
                'main_table.review_id = proscons.review_id',
                array('proscons_ids' => 'GROUP_CONCAT(proscons.proscons_id)')
            )
            ->group('main_table.review_id')
            ->join(
                array('review_store' => $this->getTable('review/review_store')),
                'main_table.review_id = review_store.review_id AND review_store.store_id = ' . $storeId,
                array('true_store_id' => 'store_id')
            )
            ->having('true_store_id = ?', Mage::app()->getStore()->getId())
            ->where('status_id = 1');

        if ($productId) {
            $reviews->getSelect()->where('entity_pk_value = ?', $productId);
        }

        if ($customerId) {
            $reviews->getSelect()->where('customer_id = ?', $customerId);
        }

        if ($proscons) {
            $tags = explode(',', $proscons);
            foreach ($tags as $id) {
                $reviews->getSelect()->having(
                    '(`proscons_ids`  LIKE \'' . $id . '\') OR (`proscons_ids`  LIKE \'%,' . $id
                    . ',%\') OR (`proscons_ids`  LIKE \'' . $id . ',%\') OR (`proscons_ids`  LIKE \'%,' . $id . '\')'
                );
            }
        }

        $vote = Mage::getModel('advancedreviews/proscons');
        $votesCollection = $vote->getCollection();
        $votesCollection->getSelect()
            ->joinLeft(
                array('proscons_store' => $this->getTable('advancedreviews/proscons_store')),
                'main_table.id = proscons_store.proscons_id', array()
            )
            ->order('sort_order ASC')
            ->where('status = 1')
            ->where('store_id = ?', Mage::app()->getStore()->getId());
        $votes = $votesCollection->getData();

        $filteredReviews = $reviews->getData();
        $filters = array();
        $reviewsIds = array();
        foreach ($filteredReviews as $review) {
            $reviewsIds[] = $review['review_id'];
            $rFilters = explode(',', $review['proscons_ids']);
            foreach ($rFilters as $filter) {
                if (isset($filters[$filter])) {
                    $filters[$filter] += 1;
                } else {
                    $filters[$filter] = 1;
                }
            }
        }

        //sort $filters by sort_order
        foreach ($votes as $key => $vote) {
            if (array_key_exists($vote['id'], $filters)) {
                $votes[$key]['qty'] = $filters[$vote['id']];
            } else {
                unset($votes[$key]);
            }
        }

        return array('reviews' => $reviewsIds, 'votes' => $votes);
    }

    public function getFilteredReviews(
        $product, $reviews, $page = 1, $limit = 10, $sortBy = null, $sortDir = 'DESC', $customerId = null
    )
    {
        if (!$sortBy) {
            $sortBy = Mage::helper('advancedreviews')->confOrderingItemsArray();
            $sortBy = @$sortBy[0]['value'];

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

        $collection = Mage::getModel('review/review')->getCollection();
        $collection->getSelect()
            ->join(
                array('review_store' => $collection->getTableName('review/review_store')),
                'main_table.review_id = review_store.review_id AND review_store.store_id = ' . Mage::app()->getStore()
                    ->getId(), array('true_store_id' => 'store_id')
            )
            //->having('true_store_id = ?',Mage::app()->getStore()->getId())
            ->where('status_id = 1');
        if ($page != '' && is_numeric($page)) {
            $collection->getSelect()
                ->limit($limit, ($page - 1) * $limit);
        }
        if ($customerId != '') {
            $collection->getSelect()->where('customer_id = ?', $customerId);
        }
        if ($product != '' && is_numeric($product)) {
            $collection->getSelect()->where('main_table.entity_pk_value = ?', $product);
        }
        if ($reviews != '') {
            $collection->getSelect()->where('main_table.review_id IN (' . $reviews . ')');
        }

        $collection->getSelect()->reset('order');
        if ($sortBy === AW_AdvancedReviews_Model_Mysql4_Review_Collection::ORDER_BY_DATE) {
            $collection->getSelect()->order('main_table.created_at ' . $this->_getDirection($sortDir));

        } elseif ($sortBy === AW_AdvancedReviews_Model_Mysql4_Review_Collection::ORDER_BY_RATING) {
            $ratingTableName = $collection->getTableName('rating/rating_option_vote');
            $collection->getSelect()->joinLeft(
                array('ov' => $ratingTableName),
                '(ov.review_id = main_table.review_id)',
                array('summa' => 'AVG(percent)')
            )->group('main_table.review_id');
            $collection->getSelect()->order('summa ' . $this->_getDirection($sortDir));
        } elseif ($sortBy === AW_AdvancedReviews_Model_Mysql4_Review_Collection::ORDER_BY_HELPFULNESS) {
            $helpfulnessTable = $collection->getTableName('advancedreviews/helpfulness');
            $collection->getSelect()
                ->joinLeft(
                    array('helpfulness' => $helpfulnessTable),
                    'main_table.review_id = helpfulness.review_id',
                    array('all_count' => 'COUNT(helpfulness.id)',
                          'yes_count' => 'SUM(helpfulness.value)')
                )
                ->group('main_table.review_id');
            $collection->getSelect()->order('yes_count ' . $this->_getDirection($sortDir));
        }
        return $collection;
    }

    protected function _getDirection($dir) {
        if (strtolower($dir) == 'asc') {
            return Zend_Db_Select::SQL_ASC;
        } else {
            return Zend_Db_Select::SQL_DESC;
        }
    }

    public function addRateVotes($id)
    {
        $votesCollection = Mage::getModel('rating/rating_option_vote')
            ->getResourceCollection()
            ->setReviewFilter($id)
            ->setStoreFilter(Mage::app()->getStore()->getId())
            ->addRatingInfo(Mage::app()->getStore()->getId())
            ->load();

        return $votesCollection;
    }
}