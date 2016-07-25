<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Controller for message template at admin side
 */
class Zalw_Advancemsg_Adminhtml_TemplateController extends Mage_Adminhtml_Controller_Action
{
	public function indexAction()
	{
	/**
	 * this is the action to show the "Advance Messages Templates"
	 */
		//Get current layout state
		$this->loadLayout()->_setActiveMenu('advancemsg/items');
		$this->_initLayoutMessages('adminhtml/session');
		
		$block = $this->getLayout()->createBlock(
		'advancemsg/adminhtml_template',
		'advancemsg.template'
		);		 
		$this->getLayout()->getBlock('content')->append($block);
				
		$this->renderLayout();
	}
	
	public function editAction()
	{
	/**
	 * this is the action to generate the page for "Edit Templates "
	 */		
		$this->loadLayout();
		
		if($this->getRequest()->getParam('id') > 0){
			$currentTemplate=Mage::getModel('advancemsg/template')->getTemplateById($this->getRequest()->getParam('id'));			
			if(!(boolean)$currentTemplate){
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancemsg')->__('Inappropriate template selected.'));
				$this->_redirect("*/*/index");
			}
			Mage::register('_current_template', $currentTemplate);
			$this->getLayout()->getBlock('advancemsg_template_edit')->setEditMode();
		}
		else{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancemsg')->__('Template id not recieved.'));
			$this->_redirect("*/*/");
		}
		
		$this->renderLayout();
	}
	
	public function newAction()
	{
	/**
	 * this is the action to generate the page for "Add New Templates "
	 */		
		$this->loadLayout();
		$currentTemplate=Mage::getModel('advancemsg/template');
		Mage::register('_current_template', $currentTemplate);
		$this->getLayout()->getBlock('advancemsg_template_new')->setEditMode(false);
		$this->renderLayout();
	}	
	
	public function saveAction()
	{
	/**
	 * this is the action to handle the save event of the template
	 */	
		$templateCode =  $this->getRequest()->getParam('code'); 
		$templateName = array();
		$model = Mage::getModel('advancemsg/template');
		$collection = $model->getCollection();
			foreach($collection as $coll)
			{
				$templateName[] = $coll->getTemplateCode();
			}
		$id = $this->getRequest()->getParam('id');
		if($id=='')
		{
			if(in_array($templateCode, $templateName))
			{
				Mage::getSingleton('adminhtml/session')->addError("Template Name already exists");
				$data = array(
					    'template_code' => $this->getRequest()->getParam('code'),
					    'template_subject' => $this->getRequest()->getParam('subject'),
					    'template_sender_name' => $this->getRequest()->getParam('sender_name'),
					    'template_sender_email' => $this->getRequest()->getParam('sender_email'),
					    'template_text' => $this->getRequest()->getParam('text'),
					    'template_styles' => $this->getRequest()->getParam('styles'),
					    'template_type' => ((boolean)$this->getRequest()->getParam('_change_type_flag'))?'1':'2'
					    );		
				Mage::getSingleton('adminhtml/session')->setFormData($data);
				Mage::getSingleton( 'core/session' )->setTemplatearray($data);
				$this->_redirect('*/*/new'); 
				return;				
			}
		}
		if ($this->getRequest()->getParams()) {			
				$data = array(
					      'template_code' => $this->getRequest()->getParam('code'),
					      'template_subject' => $this->getRequest()->getParam('subject'),
					      'template_sender_name' => $this->getRequest()->getParam('sender_name'),
					      'template_sender_email' => $this->getRequest()->getParam('sender_email'),
					      'template_text' => $this->getRequest()->getParam('text'),
					      'template_styles' => $this->getRequest()->getParam('styles'),
					      'template_type' => ((boolean)$this->getRequest()->getParam('_change_type_flag'))?'1':'2'
					      );												
				try {					
					if((boolean)$this->getRequest()->getParam('_save_as_flag') == '1' || !(boolean)$this->getRequest()->getParam('id')){
						if(in_array($data['template_code'], $templateName))
						{
							Mage::getSingleton('adminhtml/session')->addError('Template Name already exists');
							Mage::getSingleton('adminhtml/session')->setFormData($data);
							$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id'))); 
							return;							
						}
						else{
							$model->setData($data);
							$model->setAddedAt(now())
							->setModifiedAt(now());	
						}
												
					}
					else{
						
						$model->load($this->getRequest()->getParam('id'));						
						$model->setModifiedAt(now());
						$model->setTemplateCode($data['template_code']);
						$model->setTemplateSubject($data['template_subject']);
						$model->setTemplateSenderName($data['template_sender_name']);
						$model->setTemplateSenderEmail($data['template_sender_email']);
						$model->setTemplateText($data['template_text']);
						$model->setTemplateStyles($data['template_styles']);
						if((boolean)$this->getRequest()->getParam('_change_type_flag'))		$model->setTemplateType($data['template_type']);
					}					
					
				$model->save();	
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('advancemsg')->__('Template was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);
				$this->_redirect('*/*/index');					
					
				} catch (Exception $e) {
				    Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				    Mage::getSingleton('adminhtml/session')->setFormData($data);
				    $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));                
				}
		}
		else Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancemsg')->__('Template details weren\'t recieved'));		
		
		$this->_redirect('*/*/index');
	}
	
	public function deleteAction()
	{
	/**
	 * this is the action to handle the delete event of the template
	 */
		
		$messageId = $this->getRequest()->getParam('id');		
	
		if(!$messageId) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancemsg')->__('Message id not recieved'));
		} 
		else {
		    try {
			
			$message = Mage::getModel('advancemsg/template')->load($messageId);
			$message->delete();			
			Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('advancemsg')->__('Message was successfully deleted'));
			
		    } catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
			$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
		    }
		}
		$this->_redirect('*/*/index');
	}
}
