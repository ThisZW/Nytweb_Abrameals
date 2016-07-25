<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the customer's inbox message grid
 */
class Zalw_Advancemsg_Block_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    public function __construct()
    {
        parent::__construct();
        $this->setId('messageGrid');
        $this->setUseAjax(true);
        $this->setDefaultSort('added_at');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setTemplate('advancemsg/grid.phtml');
		
    }
    
    protected function _prepareCollection()
    {
        $collection = Mage::getResourceModel('advancemsg/content_collection')
            ->addFieldToSelect('*')
            ->addFieldToFilter('customer_status',array('gt'=>Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_REMOVE));
        $collection->addFieldToFilter("parent_id", array("eq" => '0'));
	$collection->getSelect()->where("(sender_id='".Mage::getSingleton('customer/session')->getCustomer()->getId()."' AND sender_type  = 'customer') OR (receiver_id='".Mage::getSingleton('customer/session')->getCustomer()->getId()."' AND receiver_type  = 'customer')");
	$this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
	$this->addColumn('message_id', array(
            'header'    => Mage::helper('advancemsg')->__('Message Id'),
            'width'     => '350px',
            'index'     => 'message_id',
        ));
        $this->addColumn('attach', array(
            'header'    => Mage::helper('advancemsg')->__('Attach'),
            'width'     => '5px', 
	    'index'     => 'attach',	  
            'renderer'  => 'advancemsg/grid_renderer_file',
	    'sortable'	=> 'false',	
	    'type'      => 'options',
                'options'   => array(
			
			1 => Mage::helper('advancemsg')->__('Yes'),
                        0 => Mage::helper('advancemsg')->__('No'),               
						                       
                ),		
        ));
	
        $this->addColumn('message_title', array(
            'header'    => Mage::helper('advancemsg')->__('Title'),
            'width'     => '200px',
            'index'     => 'message_title',
            'renderer'  => 'advancemsg/grid_renderer_title',
        ));
	$this->addColumn('email',
            array(
                'header' => Mage::helper('advancemsg')->__('Message Sent By '),
		'index'=>'sent_by_username',
		// 'renderer'  => 'advancemsg/grid_renderer_sender',
                'align'  => 'center',
                'width'  => '50px',
		'column_css_class' => 'msg_sent_by'
        ));
        $this->addColumn('added_at', array(
            'header'  =>  Mage::helper('advancemsg')->__('Added at'),
            'width'   =>  '50px',
	    'type'    =>  'datetime',
            'index'   =>  'added_at',
        ));
		
        return parent::_prepareColumns();
    }

    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('message_id');
        $this->getMassactionBlock()->setFormFieldName('messageData');
	$this->getMassactionBlock()->setTemplate('advancemsg/grid/massaction.phtml');
        $this->getMassactionBlock()->addItem('mark_as_read', array(
             'label'    => Mage::helper('advancemsg')->__('Mark as Read'),
             'url'      => $this->getUrl('*/*/massMarkAsRead', array('_current'=>true)),
        ));
        $this->getMassactionBlock()->addItem('mark_as_unread', array(
             'label'    => Mage::helper('advancemsg')->__('Mark as Unread'),
             'url'      => $this->getUrl('*/*/massMarkAsUnread', array('_current'=>true)),
        ));
        $this->getMassactionBlock()->addItem('remove', array(
             'label'    => Mage::helper('advancemsg')->__('Remove'),
             'url'      => $this->getUrl('*/*/massRemove'),
             'confirm'  => Mage::helper('advancemsg')->__('Are you sure?')
        ));
        
        return $this;
    }

    public function getGridUrl()
    {
        return $this->getUrl('*/*/grid', array('_current'=> true));
    }

    public function getRowUrl($row)
    {
    }
}

