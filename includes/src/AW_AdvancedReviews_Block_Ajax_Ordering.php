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


class AW_AdvancedReviews_Block_Ajax_Ordering extends Mage_Core_Block_Template
{
    protected $_collection = null;
    protected $_orderByVarName = 'orderby';
    protected $_sortVarName = 'sort';
    protected $_descriptionAsc = 'Ascend sorting';
    protected $_descriptionDesc = 'Descend sorting';

    protected function _construct()
    {
        parent::_construct();
        $this->setTemplate('advancedreviews/ajax/ordering.phtml');
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
    }

    public function getCollection()
    {
        if (null === $this->_collection) {
            $this->_collection = Mage::getModel('review/review')->getCollection()
                ->addStoreFilter(Mage::helper('core')->getStoreId())
                ->addStatusFilter(Mage_Review_Model_Review::STATUS_APPROVED)
                ->addEntityFilter('product', Mage::registry('product')->getId())
            ;
        }
        return $this->_collection;
    }

    public function getOrderingArray()
    {
        return Mage::helper('advancedreviews')->confOrderingItemsArray();
    }

    public function setCollection($collection)
    {
        if (isset($collection)) {
            $this->_collection = $collection;
        }

        return $this;
    }

    public function getDescriptionAsc()
    {
        return $this->_descriptionAsc;
    }

    public function setDescriptionAsc($varName)
    {
        if ($varName) {
            $this->_descriptionAsc = $varName;
        }
        return $this;
    }

    public function getDescriptionDesc()
    {
        return $this->_descriptionDesc;
    }

    public function setDescriptionDesc($varName)
    {
        if ($varName) {
            $this->_descriptionDesc = $varName;
        }
        return $this;
    }

    public function getNextDescription()
    {
        if ($this->getSort() == 'asc') {
            return $this->getDescriptionDesc();
        } else {
            return $this->getDescriptionAsc();
        }
    }

    public function getSortVarName()
    {
        return $this->_sortVarName;
    }

    public function setSortVarName($varName)
    {
        if ($varName) {
            $this->_sortVarName = $varName;
        }
        return $this;
    }

    public function getOrderByVarName()
    {
        return $this->_orderByVarName;
    }

    public function setOrderByVarName($varName)
    {
        if ($varName) {
            $this->_orderByVarName = $varName;
        }
        return $this;
    }

    public function getNextSortUrl()
    {
        if ($this->getSort() == 'asc') {
            return $this->getSortUrl('desc');
        } else {
            return $this->getSortUrl('asc');
        }
    }

    public function getSort()
    {
        $sort = $this->getRequest()->getParam($this->getSortVarName());
        if (!$sort) {
            $sort = 'desc';
        }

        return $sort;
    }

    public function getOrdering()
    {
        $ordering = $this->getRequest()->getParam($this->getOrderByVarName());
        if (!$ordering) {
            $orderingArray = $this->getOrderingArray();
            return $orderingArray[0]['value'];
        } else {
            return $ordering;
        }

        return $this->getRequest()->getParam($this->getOrderByVarName());
    }

    public function isCurrentOrdering($ordering)
    {
        return $ordering === $this->getOrdering();
    }

    public function getOptionUrl($orderby)
    {
        return $this->getOrderingUrl(array($this->getOrderByVarName() => $orderby));
    }

    public function getSortUrl($sorting)
    {
        return $this->getOrderingUrl(array($this->getSortVarName() => $sorting));
    }

    public function getOrderingUrl($params = array())
    {
        $urlParams = array();
        $urlParams['_current'] = true;
        $urlParams['_escape'] = true;
        $urlParams['_use_rewrite'] = true;
        $urlParams['_query'] = $params;
        return $this->getUrl('*/*/*', $urlParams);
    }
}