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


class AW_AdvancedReviews_Block_Adminhtml_Proscons_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected $_storeDetailTable;
    protected $_proscons;
    protected $_itemName;

    /*
     * Define current parametrs
     */
    public function __construct()
    {
        parent::__construct();

        //Set parent options
        $this->setId('prosconsGrid');
        $this->setUseAjax(true);

        $this->setSaveParametersInSession(true);

        //Set this options
        if (Mage::registry(Mage::helper('advancedreviews')->getConstPcRegRef())) {
            $this->_proscons = Mage::registry(Mage::helper('advancedreviews')->getConstPcRegRef());
            Mage::getSingleton('adminhtml/session')->setProsconsOption($this->_proscons);
        } else {
            $this->_proscons = Mage::getSingleton('adminhtml/session')->getProsconsOption();
        }
        $this->_storeDetailTable = Mage::getSingleton('core/resource')
            ->getTableName('advancedreviews/proscons_store');
        $this->_itemName = ($this->_proscons === Mage::helper('advancedreviews')->getConstTypePros()) ? 'Pros' : 'Cons';

        if ($this->_proscons === Mage::helper('advancedreviews')->getConstTypeUser()) {
            $this->setDefaultSort('status');
            $this->setDefaultDir('asc');
        } else {
            $this->setDefaultSort('sort_order');
            $this->setDefaultDir('asc');
        }
    }

    /*
     * Prepare collection: set store filter
     */
    protected function _prepareCollection()
    {
        $collection = Mage::getModel('advancedreviews/proscons')->getCollection();
        $collection->getSelect()
            ->joinLeft(
                array('store' => $this->_storeDetailTable),
                'main_table.id = store.proscons_id'
            )
            ->where('store.store_id IN (' . Mage::helper('advancedreviews')->getStoreIds() . ')');

        if ($this->_proscons !== Mage::helper('advancedreviews')->getConstTypeUser()) {
            $collection->getSelect()->where('main_table.type = ?', $this->_proscons);
        } else {
            $collection->getSelect()->where(
                'main_table.owner = ?', Mage::helper('advancedreviews')->getConstOwnerUser()
            );
        }
        $collection->addStoreData();
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /*
     * Preapere columns
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            array(
                 'header' => Mage::helper('advancedreviews')->__('Id'),
                 'align'  => 'left',
                 'index'  => 'id',
                 'width'  => 50,
                 'type'   => 'number',
            )
        );

        if ($this->_proscons === Mage::helper('advancedreviews')->getConstTypeUser()) {
            $this->addColumn(
                'type',
                array(
                     'header'  => Mage::helper('advancedreviews')->__('Type'),
                     'align'   => 'left',
                     'index'   => 'type',
                     'type'    => 'options',
                     'width'   => 100,
                     'options' => Mage::getSingleton('advancedreviews/proscons_options')->getProsconsOptionArray(),
                )
            );
        }

        $this->addColumn(
            'name',
            array(
                 'header' => Mage::helper('advancedreviews')->__('Name'),
                 'align'  => 'left',
                 'index'  => 'name',
            )
        );

        $this->addColumn(
            'status',
            array(
                 'header'  => Mage::helper('advancedreviews')->__('Status'),
                 'align'   => 'left',
                 'index'   => 'status',
                 'type'    => 'options',
                 'width'   => 100,
                 'options' => Mage::getSingleton('advancedreviews/proscons_options')->getStatusOptionArray(),
            )
        );

        if ($this->_proscons !== Mage::helper('advancedreviews')->getConstTypeUser()) {
            $this->addColumn(
                'owner',
                array(
                     'header'  => Mage::helper('advancedreviews')->__('Owner'),
                     'align'   => 'left',
                     'index'   => 'owner',
                     'type'    => 'options',
                     'width'   => 100,
                     'options' => Mage::getSingleton('advancedreviews/proscons_options')->getOwnerOptionArray(),
                )
            );

            $this->addColumn(
                'sort_order',
                array(
                     'header' => Mage::helper('advancedreviews')->__('Sort Order'),
                     'align'  => 'left',
                     'index'  => 'sort_order',
                     'width'  => 50,
                     'type'   => 'number',
                )
            );
        }
        /**
         * Check is single store mode
         */
        if (!Mage::app()->isSingleStoreMode()) {
            $this->addColumn(
                'stores',
                array(
                     'header'                    => Mage::helper('advancedreviews')->__('Visible In'),
                     'index'                     => 'stores',
                     'type'                      => 'store',
                     'store_all'                 => true,
                     'store_view'                => true,
                     'width'                     => 210,
                     'sortable'                  => false,
                     'filter_condition_callback' => array($this, '_filterStoreCondition'),
                )
            );
        }

        $this->addColumn(
            'action',
            array(
                 'header'    => Mage::helper('advancedreviews')->__('Action'),
                 'width'     => '80',
                 'type'      => 'action',
                 'getter'    => 'getId',
                 'actions'   => array(
                     array(
                         'caption' => Mage::helper('advancedreviews')->__('Delete'),
                         'url'     => array('base' => '*/*/delete', 'params' => array('ref' => $this->_getProsDesc())),
                         'field'   => 'id',
                         'confirm' => Mage::helper('advancedreviews')->__(
                             'Are you sure you want to delete the ' . $this->_itemName . '?'
                         ),
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

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->setMassactionIdFieldOnlyIndexValue(true);
        $this->getMassactionBlock()->setFormFieldName('proscons');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                 'label'   => Mage::helper('advancedreviews')->__('Delete'),
                 'url'     => $this->getUrl('*/*/massDelete', array('ref' => $this->_getProsDesc())),
                 'confirm' => Mage::helper('advancedreviews')->__('Are you sure?'),
            )
        );

        $this->getMassactionBlock()->addItem(
            'update_status',
            array(
                 'label'      => Mage::helper('advancedreviews')->__('Update status'),
                 'url'        => $this->getUrl('*/*/massUpdateStatus', array('ref' => $this->_getProsDesc())),
                 'additional' => array(
                     'status' => array(
                         'name'   => 'status',
                         'type'   => 'select',
                         'class'  => 'required-entry',
                         'label'  => Mage::helper('advancedreviews')->__('Status'),
                         'values' => Mage::getSingleton('advancedreviews/proscons_options')->getStatusOptionArray(),
                     )
                 )
            )
        );
    }

    public function getRowUrl($row)
    {
        return $this->getUrl(
            'adminhtml/awadvancedreviews_proscons/edit',
            array('id' => $row->getId(), 'ref' => $this->_getProsDesc())
        );
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current' => true));
    }

    protected function _getProsDesc()
    {
        if (Mage::helper('advancedreviews')->isPros()) {
            return 'pros';
        } elseif (Mage::helper('advancedreviews')->isCons()) {
            return 'cons';
        } else {
            return 'user';
        }
    }

    protected function _filterStoreCondition($collection, $column)
    {
        if (!$value = $column->getFilter()->getValue()) {
            return;
        }
        $collection->getSelect()->where('store.store_id = ?', $value);
    }
}