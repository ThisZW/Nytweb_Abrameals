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


class AW_AdvancedReviews_Block_Adminhtml_Abuse_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_reviewDetailTable;

    public function __construct()
    {
        parent::__construct();
        $this->setId('abuseGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('abused_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->_reviewDetailTable = Mage::getSingleton('core/resource')->getTableName('review/review_detail');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getModel('advancedreviews/abuse')->getCollection();
        $collection->getSelect()
            ->joinLeft(
                array('detail' => $this->_reviewDetailTable),
                'main_table.review_id = detail.review_id'
            )
            ->where('main_table.store_id IN (' . Mage::helper('advancedreviews')->getStoreIds() . ')')->__toString();

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn(
            'review_title',
            array(
                 'header' => Mage::helper('advancedreviews')->__('Review title'),
                 'align'  => 'left',
                 'index'  => 'title',
            )
        );

        $this->addColumn(
            'abused_by',
            array(
                 'header' => Mage::helper('advancedreviews')->__('Reported by'),
                 'align'  => 'left',
                 'index'  => 'customer_name',
            )
        );

        $this->addColumn(
            'abused_at',
            array(
                 'header' => Mage::helper('advancedreviews')->__('Report date'),
                 'index'  => 'abused_at',
                 'type'   => 'datetime',
                 'width'  => 120,
            )
        );

        $this->addColumn(
            'action',
            array(
                 'header'    => Mage::helper('advancedreviews')->__('Action'),
                 'width'     => 80,
                 'type'      => 'action',
                 'getter'    => 'getId',
                 'actions'   => array(
                     array(
                         'caption' => Mage::helper('advancedreviews')->__('Delete'),
                         'url'     => array('base' => '*/*/deleteAbuse'),
                         'field'   => 'id',
                         'confirm' => Mage::helper('advancedreviews')->__('Are you sure you want to delete the abuse?'),
                     )
                 ),
                 'filter'    => false,
                 'sortable'  => false,
                 'index'     => 'stores',
                 'is_system' => true,
            )
        );

        return parent::_prepareColumns();
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'adminhtml/catalog_product_review/edit',
            array('id' => $row->getReviewId(), 'ret' => 'abuse')
        );
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }
}