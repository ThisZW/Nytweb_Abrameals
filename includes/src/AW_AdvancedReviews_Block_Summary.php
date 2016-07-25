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


class AW_AdvancedReviews_Block_Summary extends Mage_Core_Block_Template
{
    public function getSummaryBlock()
    {
        $productId = (int)$this->getRequest()->getParam('id');
        $parametrs = Mage::getModel('rating/rating')->getCollection();
        $ratings = array();
        foreach ($parametrs as $parametr) {
            $ratings[$parametr->getId()]['name'] = $parametr->getRatingCode();

            $votes = Mage::getModel('rating/rating_option_vote')->getCollection();
            $votes->getSelect()
                ->from($this->_name, array('COUNT(*) as qty', 'value as mark'))
                ->join(
                    array('review' => Mage::getSingleton('core/resource')->getTableName('review/review')),
                    'main_table.review_id = review.review_id', array('status_id')
                )
                ->join(
                    array('review_store' => Mage::getSingleton('core/resource')->getTableName('review/review_store')),
                    'main_table.review_id = review_store.review_id', array('store_id')
                )
                ->where('main_table.entity_pk_value = ?', $productId)
                ->where('value > 0')
                ->where('store_id = ?', Mage::app()->getStore()->getId())
                ->where('rating_id = ?', $parametr->getId())
                ->where('review.status_id = 1')
                ->group('value')
                ->order('value DESC')
            ;

            $qtyVotes = 0;
            foreach ($votes as $vote) {
                $ratings[$parametr->getId()]['votes'][$vote->getMark()]['qty'] = $vote->getQty();
                $qtyVotes += $vote->getQty();
            }
            $ratings[$parametr->getId()]['votes_qty'] = $qtyVotes;
            foreach ($votes as $vote) {
                $ratings[$parametr->getId()]['votes'][$vote->getMark()]['qty'] = $vote->getQty();
                $ratings[$parametr->getId()]['votes'][$vote->getMark()]['percent'] = round(
                    $vote->getQty() / $qtyVotes * 100
                );
            }
        }
        return $ratings;
    }
}