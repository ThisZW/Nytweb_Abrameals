<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the customer send message grid
 */
class Zalw_Advancemsg_Block_Messagegrid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('mymessageGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('date');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setTemplate('advancemsg/messagegrid.phtml');
    }
  
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('advancemsg/customermsg_collection')
            ->addFieldToSelect('*')		
            ->addFieldToFilter('name',$customerName = Mage::getSingleton('customer/session')->getCustomer()->getName());
        $collection->addFieldToFilter("parent_id", array("eq" => '0'));  
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        
	$this->addColumn('messagetitle', array(
            'header'    => Mage::helper('advancemsg')->__('Subject'),
            'width'     => '300px',
            'index'     => 'messagetitle',
            'renderer'  => 'advancemsg/messagegrid_renderer_messagetitle',
        ));
        $this->addColumn('message', array(
            'header'    => Mage::helper('advancemsg')->__('Message'),
            'width'     => '500px',
            'index'     => 'message',
            'renderer'  => 'advancemsg/messagegrid_renderer_message',
        ));
        $this->addColumn('date', array(
            'header'    => Mage::helper('advancemsg')->__('Message Send Date'),
            'width'     => '100',
	    'type' =>'datetime',
            'index'     => 'date',
        ));
		
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('message_id');
        $this->getMassactionBlock()->setFormFieldName('messageData');
	$this->getMassactionBlock()->setTemplate('advancemsg/messagegrid/massaction.phtml');
        
        $this->getMassactionBlock()->addItem('remove', array(
             'label'    => Mage::helper('advancemsg')->__('Remove'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('advancemsg')->__('Are you sure?')
        ));
        
        return $this;
    }

    public function getMessagegridUrl()
    {
        return $this->getUrl('*/*/messagegrid', array('_current'=> true));
    }

    public function getRowUrl($row)
    {
    }
}

