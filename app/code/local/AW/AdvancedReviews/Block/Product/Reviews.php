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


class AW_AdvancedReviews_Block_Product_Reviews extends Mage_Core_Block_Template
{
    /**
     * Reviews Collection
     *
     * @var Mage_Core_Model_Mysql4_Collection_Abstract
     */
    protected $_collection;

    /**
     * Current Product
     *
     * @var Mage_Catalog_Model_product
     */
    protected $_product;

    /**
     * Returns product
     *
     * @return Mage_Catalog_Model_Product
     */
    public function getProduct()
    {
        if (!$this->_product) {
            $this->_product = Mage::registry('current_product');
        }
        return $this->_product;
    }

    /**
     * Create child blocks we need
     *
     * @return AW_AdvancedReviews_Block_Product_Reviews
     */
    protected function _beforeToHtml()
    {
        # Register childs that we need here
        # Insert: type, name, alias, template
        $this->registerChild(
            'advancedreviews/allmylink',
            'aw.advancedreviews.allmylink',
            'advancedreviews_allmylink',
            'advancedreviews/allmylink.phtml'
        );
        $this->registerChild(
            'advancedreviews/recommend_indicator',
            'aw.advancedreviews.recommend.indicator',
            'advancedreviews_recommend_indicator',
            'advancedreviews/recommend/indicator.phtml'
        );
        $this->registerChild(
            'advancedreviews/proscons_indicator',
            'aw.advancedreviews.proscons.indicator',
            'advancedreviews_proscons_indicator',
            'advancedreviews/proscons/indicator.phtml'
        );
        $footer = $this->registerChild(
            'advancedreviews/footer',
            'aw.advancedreviews.footer',
            'advancedreviews_footer',
            'advancedreviews/footer.phtml'
        );
        $this->registerSubChild(
            $footer,
            'advancedreviews/helpfulness',
            'aw.advancedreviews.helpfulness',
            'advancedreviews_helpfulness',
            'advancedreviews/helpfulness.phtml'
        );
        $this->registerSubChild(
            $footer,
            'advancedreviews/reportabuse',
            'aw.advancedreviews.reportabuse',
            'advancedreviews_reportabuse',
            'advancedreviews/reportabuse.phtml'
        );
        $this->registerSubChild(
            $footer,
            'advancedreviews/socialshare',
            'aw.advancedreviews.socialshare',
            'advancedreviews_socialshare',
            'advancedreviews/socialshare.phtml'
        );
        parent::_beforeToHtml();
        return $this;
    }

    /**
     * Register child for instance of block
     *
     * @param string $type
     * @param string $name
     * @param string $alias
     * @param string $template
     *
     * @return Mage_Core_Block_Abstract
     */
    public function registerChild($type, $name, $alias, $template)
    {
        $block = $this->getLayout()->createBlock($type);
        $this->setChild($alias, $block);
        if ($block) {
            $block->setTemplate($template);
            $block->setName($name);
        }
        return $block;
    }

    /**
     * Register child for instance $block variable
     *
     * @param $block
     * @param $type
     * @param $name
     * @param $alias
     * @param $template
     *
     * @return Mage_Core_Block_Abstract
     */
    public function registerSubChild($block, $type, $name, $alias, $template)
    {
        $sub = $this->getLayout()->createBlock($type);
        if ($block && $sub) {
            $block->setChild($alias, $sub);
            if ($sub) {
                $sub->setTemplate($template);
                $sub->setName($name);
            }
        }
        return $sub;
    }

    /**
     * Set ordering for collection
     *
     * @param $collection
     *
     * @return AW_AdvancedReviews_Block_Product_Reviews
     */
    protected function _setOrdering($collection)
    {
        $ordering = Mage::getStoreConfig('advancedreviews/productpage_options/ordering');
        $collection->getSelect()->reset(Zend_Db_Select::ORDER);
        if ($ordering === AW_AdvancedReviews_Model_Mysql4_Review_Collection::ORDER_BY_DATE) {
            $collection->getSelect()->order('main_table.created_at DESC');
        } elseif ($ordering === AW_AdvancedReviews_Model_Mysql4_Review_Collection::ORDER_BY_RATING) {
            $ratingTableName = $collection->getTableName('rating/rating_option_vote');
            $collection->getSelect()->joinInner(
                array('ov' => $ratingTableName),
                '(ov.review_id = main_table.review_id)',
                array('summa' => 'AVG(percent)')
            )->group('ov.review_id');
            $collection->getSelect()->order('summa DESC');
        } elseif ($ordering === AW_AdvancedReviews_Model_Mysql4_Review_Collection::ORDER_BY_HELPFULNESS) {
            $helpfulnessTable = $collection->getTableName('advancedreviews/helpfulness');
            $collection->getSelect()
                ->joinLeft(
                    array('helpfulness' => $helpfulnessTable),
                    'main_table.review_id = helpfulness.review_id',
                    array('all_count' => 'COUNT(helpfulness.id)',
                          'yes_count' => 'SUM(helpfulness.value)')
                )
                ->group('main_table.review_id');
            $collection->getSelect()->order('yes_count DESC');
        }
        return $this;
    }

    /**
     * Returns reviews collection with Rating Summary
     *
     * @return Mage_Review_Model_Review_Collection
     */
    public function getCollection()
    {
        if (!$this->_collection) {
            $this->_collection = Mage::getModel('review/review')->getCollection()
                ->addStoreFilter(Mage::app()->getStore()->getId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addEntityFilter('product', $this->getProduct()->getId())
                ->setPageSize(Mage::getStoreConfig('advancedreviews/productpage_options/count'));
            $this->_setOrdering($this->_collection);
            $this->_collection->addRateVotes();
        }

        return $this->_collection;
    }

    /**
     * Flag to show reviews block at the product page
     *
     * @return boolean
     */
    public function getShowReviews()
    {
        return ($this->getProduct() && count($this->getCollection()));
    }

    /**
     * Retrives Review url
     *
     * @param int|string $id Review Id
     *
     * @return string
     */
    public function getReviewUrl($id)
    {
        if (Mage::getStoreConfigFlag('web/seo/use_rewrites')) {
            return Mage::helper('advancedreviews')->getFuReviewUrl($id);
        } else {
            return Mage::helper('advancedreviews')->getReviewUrl($id);
        }
    }
}