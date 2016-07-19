<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the admin inbox grid
 */  	
class Zalw_Advancemsg_Block_Adminhtml_Customermsg_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
  //function initializes id,session
  public function __construct()
  {
      parent::__construct();
      $this->setDefaultSort('date');
      $this->setDefaultDir('Desc');
      $this->setSaveParametersInSession(true);
  }
  //function prepares collection and sets collection	
  protected function _prepareCollection()
  {
	  $collection = Mage::getModel('advancemsg/customermsg')->getCollection();
	  $collection->addFieldToFilter("parent_id", array("eq" => '0'));
	  $this->setCollection($collection);
	  
	  return parent::_prepareCollection();
  }
  //makes grid columns
  protected function _prepareColumns()
  {
	//me id column
      	$this->addColumn('message_id', array(
          'header'    => Mage::helper('advancemsg')->__('Message Id'),
          'align'     =>'right',
          'width'     => '10px',
          'index'     => 'message_id',
      	));
	//Customer Name column
      	$this->addColumn('name', array(
          'header'    => Mage::helper('advancemsg')->__('Customer Name'),
          'align'     =>'left',
	  'width'     => '150px',	
          'index'     => 'name',
      	));
	//message column  
	$this->addColumn('messagetitle', array(
          'header'    => Mage::helper('advancemsg')->__('Subject'),
          'align'     =>'left',
	  'width'     => '300px',	
          'index'     => 'messagetitle',
      	));
	//Date column
	$this->addColumn('date', array(
          'header'    => Mage::helper('advancemsg')->__('Message Recieve Date'),
          'align'     =>'left',
	  'type'      => 'datetime',
          'align'     => 'center',
	  'width'     => '150px',
          'index'     => 'date',
      	));	 
	//status
	$this->addColumn('status', 
          array(
                'header'=>Mage::helper('advancemsg')->__('Message Status'),
                'index'=>'status',
		'width'     => '70px',
		'type'      => 'options',
                'options'   => array(
                        0 => Mage::helper('advancemsg')->__('Unread'),
                        1 => Mage::helper('advancemsg')->__('Read'),                       
                ),
      	));
	//actions  
        $this->addColumn('action',
            array(
                'header'    =>  Mage::helper('advancemsg')->__('Action'),
                'width'     => '80px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('advancemsg')->__('View'),
                        'url'       => array('base'=> '*/*/edit'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
	$this->addExportType('*/*/exportCsv', Mage::helper('advancemsg')->__('CSV'));
	$this->addExportType('*/*/exportXml', Mage::helper('advancemsg')->__('XML'));	
		  
      	return parent::_prepareColumns();
  	}

	/*Function for Mass section*/
        protected function _prepareMassaction()
        {
        $this->setMassactionIdField('message_id');
        $this->getMassactionBlock()->setFormFieldName('message_id');

        $this->getMassactionBlock()->addItem('delete', array(
             'label'    => Mage::helper('advancemsg')->__('Delete'),
             'url'      => $this->getUrl('*/*/massDelete'),
             'confirm'  => Mage::helper('advancemsg')->__('Are you sure?')
        ));
	$this->getMassactionBlock()->addItem('read', array(
             'label'    => Mage::helper('advancemsg')->__('Read'),
             'url'      => $this->getUrl('*/*/status/opId/1'),  
        ));
        $this->getMassactionBlock()->addItem('unread', array(
             'label'    => Mage::helper('advancemsg')->__('Unread'),
             'url'      => $this->getUrl('*/*/status/opId/0'),
        ));

        return $this;
    }
    
    public function getRowUrl($row)
    {        
        return $this->getUrl('*/*/edit', array('id'=>$row->getId()));
    }
	
}
