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


class AW_AdvancedReviews_Model_Mysql4_Proscons extends Mage_Core_Model_Mysql4_Abstract
{
    protected $_storeTableName;

    protected function _construct()
    {
        $this->_init('advancedreviews/proscons', 'id');
        $this->_storeTableName = $this->getTable('advancedreviews/proscons_store');
    }

    /*
     * Save stores after save Proscons
     */
    protected function _afterSave(Mage_Core_Model_Abstract $object)
    {
        /**
         * save stores
         */
        $stores = $object->getStores();
        if (!empty($stores)) {
            $condition = $this->_getWriteAdapter()->quoteInto('proscons_id = ?', $object->getId());
            $this->_getWriteAdapter()->delete($this->_storeTableName, $condition);

            $insertedStoreIds = array();
            foreach ($stores as $storeId) {
                if (in_array($storeId, $insertedStoreIds)) {
                    continue;
                }

                $insertedStoreIds[] = $storeId;
                $storeInsert = array(
                    'store_id'    => $storeId,
                    'proscons_id' => $object->getId()
                );
                $this->_getWriteAdapter()->insert($this->_storeTableName, $storeInsert);
            }
        }
        return $this;
    }

    /**
     * Load stores after load Proscons
     */
    protected function _afterLoad(Mage_Core_Model_Abstract $object)
    {
        parent::_afterLoad($object);
        $select = $this->_getReadAdapter()
            ->select()
            ->from($this->_storeTableName, array('store_id'))
            ->where('proscons_id=?', $object->getId())
        ;

        $stores = $this->_getReadAdapter()->fetchCol($select);

        if (empty($stores)) {
            $object->setStores(array(Mage::app()->getStore(true)->getId()));
        } else {
            $object->setStores($stores);
        }
        return $this;
    }
}