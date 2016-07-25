<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	To show the advance message grid
 */
class Zalw_Advancemsg_Block_Adminhtml_Advance_Grid extends Mage_Adminhtml_Block_Widget_Grid
{
    protected function _construct()
    {
        $this->setEmptyText(Mage::helper('advancemsg')->__('No Log Found'));
        $this->setDefaultSort('added_at');
        $this->setDefaultDir('DESC');
    }

    protected function _prepareCollection()
    {
        $collection = Mage::getResourceSingleton('advancemsg/content_collection');
        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    protected function _prepareColumns()
    {
        $this->addColumn('message_id',
            array(
				'header'=>Mage::helper('advancemsg')->__('Message ID'), 
				'align'=>'center',
				'width' => '50',
				'index'=>'message_id'));
		$this->addColumn('user_id',
            array(
                'header'=>Mage::helper('advancemsg')->__('Customer ID'),
                'index'=>'user_id',
                'align'=>'center',
                'width' => '50',
        ));
        $this->addColumn('template_id',
            array(
                'header'=>Mage::helper('advancemsg')->__('Template ID'),
                'index'=>'template_id',
                'align'=>'center',
                'width' => '50',
        ));
        $this->addColumn('message_title',
            array(
                'header'=>Mage::helper('advancemsg')->__('Message Title'),
                'index'=>'message_title'
        ));
        

        $this->addColumn('message_content',
            array(
                'header'=>Mage::helper('advancemsg')->__('Message Content'),
                'index'=>'message_content',
                'renderer'  => 'advancemsg/adminhtml_log_grid_renderer_content',
        ));
	
	$this->addColumn('template_attachment',
            array(
                'header'=>Mage::helper('advancemsg')->__('Attachment File'),
                'index'=>'file_name',                
        ));

        $this->addColumn('added_at',
            array(
                'header'=>Mage::helper('advancemsg')->__('Added At'),
                'index'=>'added_at',
        ));
        $this->addColumn('status',
            array(
                'header'=>Mage::helper('advancemsg')->__('Message Status'),
                'index'=>'status',
		'type'      => 'options',
                'options'   => array(
                        0 => Mage::helper('advancemsg')->__('Unread'),
                        1 => Mage::helper('advancemsg')->__('Read'),
                       -2 => Mage::helper('advancemsg')->__('Delete'),
                ),
        ));
        $this->addColumn('message_link',
            array(
                'header'=>Mage::helper('advancemsg')->__('Message Link'),
                'index'=>'message_link',
        ));
        return $this;
    }
	protected function _prepareMassaction()
    {
        $this->setMassactionIdField('log_id');
        $this->getMassactionBlock()->addItem('remove', array(
             'label'    => Mage::helper('advancemsg')->__('Remove'),
             'url'      => $this->getUrl('*/*/massRemoveMessage'),
             'confirm'  => Mage::helper('advancemsg')->__('Are you sure?')
        ));
        return $this;
    }
    
        public function getRowUrl($row)
    {
    }

}

