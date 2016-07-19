<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the message log grid
 */
class Zalw_Advancemsg_Block_Adminhtml_Log_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _construct()
    {
        $this->setEmptyText(Mage::helper('advancemsg')->__('No Message Found'));
        $this->setDefaultSort('message_id');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceSingleton('advancemsg/content_collection')
	 ->addFieldToFilter('status',array('gt'=>Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_REMOVE));
        $collection->addFieldToFilter("parent_id", array("eq" => '0'));
	$collection->setOrder('message_id','DESC');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('message_id',
            array(
		'header'=>Mage::helper('advancemsg')->__('Message ID'), 
		'align'=>'center',
		'width' => '10px',
		'index'=>'message_id'));
        $this->addColumn('template_id',
            array(
                'header'=>Mage::helper('advancemsg')->__('Template ID'),
                'index'=>'template_id',
		'align'=>'center',
                'width' => '10px',
        ));
//        $this->addColumn('template_name',
//            array(
//                'header'=>Mage::helper('advancemsg')->__('Template Name'),
//		'width' => '50',
//                'index'=>'template_name'
//        ));
        $this->addColumn('message_title',
            array(
                'header'=>Mage::helper('advancemsg')->__('Message Title'),
		'width' => '50',
                'index'=>'message_title'
        ));
	$this->addColumn('message_content',
            array(
                'header'=>Mage::helper('advancemsg')->__('Message Content'),
		'width' => '50',
                'index'=>'message_content',
		'renderer'  => 'advancemsg/adminhtml_log_grid_renderer_content',
        ));
        $this->addColumn('message_sent_by',
            array(
                'header'=>Mage::helper('advancemsg')->__('Message Sent By'),
		'width' => '50',
                'index'=>'sent_by_username',
		'column_css_class' => 'msg_sent_by',
        ));
//        $this->addColumn('message_sent_by',
//            array(
//                'header'=>Mage::helper('advancemsg')->__('Message Sent By'),
//		'width' => '50',
//                'index'=>'message_content',
//		'renderer'  => 'advancemsg/adminhtml_log_grid_renderer_sender',
//        ));
        $this->addColumn('added_at',
            array(
                'header'=>Mage::helper('advancemsg')->__('Added At'),
                'index'=>'added_at',
		'type' => 'datetime'
        ));
//	$this->addColumn('message_link',
//            array(
//                'header'=>Mage::helper('advancemsg')->__('Message Link'),
//                'index'=>'message_link'
//        ));
//	$this->addColumn('file_name',
//            array(
//                'header'=>Mage::helper('advancemsg')->__('Attachment File'),
//		'width' => '50',
//                'index'=>'file_name',                
//        ));
	
	$this->addColumn('attach', array(
            'header'    => Mage::helper('advancemsg')->__('Attach'),
            'width'     => '5px', 
	    'index'     => 'attach',	  
            'renderer'  => 'advancemsg/adminhtml_log_grid_renderer_attachment',
	    'sortable'	=> 'false',	
	    'type'      => 'options',
            'options'   => array(
			1 => Mage::helper('advancemsg')->__('Yes'),
                        0 => Mage::helper('advancemsg')->__('No'),
                ),		
        ));
	
    	$this->addColumn('action',
            array(
                'header'    =>  Mage::helper('advancemsg')->__('Action'),
                'width'     => '80px',
                'type'      => 'action',
                'getter'    => 'getId',
                'actions'   => array(
                    array(
                        'caption'   => Mage::helper('advancemsg')->__('View'),
                        'url'       => array('base'=> '*/*/adminlogview'),
                        'field'     => 'id'
                    )
                ),
                'filter'    => false,
                'sortable'  => false,
                'index'     => 'stores',
                'is_system' => true,
        ));
      
        return $this;
    }
    
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('message_id');
	
	$this->getMassactionBlock()->addItem('read', array(
             'label'    => Mage::helper('advancemsg')->__('Mark As Read'),
             'url'      => $this->getUrl('*/*/massMarkAsRead'),
        ));
	
	$this->getMassactionBlock()->addItem('unread', array(
             'label'    => Mage::helper('advancemsg')->__('Mark As Unread'),
             'url'      => $this->getUrl('*/*/massMarkAsUnread'),
        ));
	
        $this->getMassactionBlock()->addItem('remove', array(
             'label'    => Mage::helper('advancemsg')->__('Remove'),
             'url'      => $this->getUrl('*/*/massRemoveLog'),
             'confirm'  => Mage::helper('advancemsg')->__('Are you sure?')
        ));
        return $this;
    }
    
    public function getRowUrl($row)
    {
        return $this->getUrl('*/*/adminlogview', array('id'=>$row->getId()));	
    }
    
    public function getRowClass(Varien_Object $_item) {
		//return $row->getStatus() ? 'read' : 'unread';
		$messageIdc = Mage::getModel('advancemsg/content')->getCollection()
						 ->addFieldToFilter("status", array("eq" => '0'))
						 ->addFieldToFilter("parent_id", array("neq" => '0'))
						 ->load();
		 $unreadChildMessages = array();
		
		 foreach ($messageIdc as $_indexc=>$_itemc){
				$unreadChildMessages[] = $_itemc['parent_id'];
		 }
	 
		$status = $_item['status'];
		$messageId = $_item['message_id'];
	   
		if($status== 0 || in_array($messageId, $unreadChildMessages)){
			return "unread";
		}
		else{
			return "read";
		}if($status == 3 ){
			return "read";
		}if($status == 4 ){
		    return "unread";
		}
		
	
    }

}

