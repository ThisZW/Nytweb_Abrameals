<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Controller to manage message send by admin to customer at admin side 
 */
class Zalw_Advancemsg_Adminhtml_ManageController extends Mage_Adminhtml_Controller_Action
{
	public function newAction()
	{
	/**
	 * this action generates the page for "Send new messages"
	 */		
		$this->loadLayout()->_setActiveMenu('advancemsg/items');
		$this->_initLayoutMessages('adminhtml/session');
		
		$block = $this->getLayout()->createBlock(
		'advancemsg/adminhtml_manage',
		'advancemsg.manage'
		);		 
		$this->getLayout()->getBlock('content')->append($block);
				
		$this->renderLayout();
	}

	public function indexAction()
	{
	/**
	 * this action generates the page for "Messages Templates"
	 */		
		$this->loadLayout()->renderLayout();
	}
	
	public function logAction()
	{
	/**
	 * this action generates the page for "Messages Log"
	 */
		$this->loadLayout()->_setActiveMenu('advancemsg/items');
		$this->_initLayoutMessages('adminhtml/session');
		
		$block = $this->getLayout()->createBlock(
		'advancemsg/adminhtml_log',
		'advancemsg.log'
		);		 
		$this->getLayout()->getBlock('content')->append($block);
				
		$this->renderLayout();
	}
	
	public function advanceAction()
	{
	/**
	 * this action generates the page for "Advanced Messages Management"
	 */		
		$this->loadLayout();
		$this->_initLayoutMessages('adminhtml/session');
		
		$block = $this->getLayout()->createBlock(
		'advancemsg/adminhtml_advance',
		'advancemsg.advance'
		);		 
		$this->getLayout()->getBlock('content')->append($block);
				
		$this->renderLayout();
	}	
		
	public function sendAction()
	{
	/**
	 * this action handles the send message(s) event to the customer(s) selected
	 */		
		$data = $this->getRequest()->getParams();		
		if (isset($data['checked_string'])) {
			$customerIds = explode(',', $data['checked_string']);
		} else {
			$customerIds = $this->getRequest()->getParam('customer');
		}
		$fileFlag = true;
		$fileName = '';
		$message='';$successmessage='';
		$adminId = Mage::getSingleton('admin/session')->getUser()->getId();
		//$adminName = Mage::getSingleton('admin/session')->getUser()->getUsername();   
		    try
			{
				$messageDetails=Mage::getModel('advancemsg/template')->load($data['template_id']);
				
				foreach ($customerIds as $customerId)
				{
					$messageContent= Mage::getModel('advancemsg/content')						
						->setTemplateId($data['template_id'])
						->setTemplateName($messageDetails->getTemplateSubject())
						->setMessageTitle($data['title'])
						->setMessageLink($data['link'])
						->setUserId($customerId)						
						->setMessageContent($messageDetails->getTemplateText())
						->setAddedAt(now())
						->setMessageText($data['message_text'])
						->setStatus('1')
						->setCustomerStatus('0')
						->setParentId('0')
						->setSenderId($adminId)
						->setSenderType('admin')
						->setSentByUsername('Admin')
						->setReceiverId($customerId)
						->setReceiverType('customer')
						->setModifiedAt(now());
					if (isset($_FILES['template_attachment']['name']) && $_FILES['template_attachment']['name'] != '') {$messageContent->setAttach('1');}				    
					else{
					$messageContent->setAttach('0');	
					}
					if (isset($_FILES['template_attachment']['name']) && $_FILES['template_attachment']['name'] != '') {
						try {
							if($fileFlag){
								$uploader = new Varien_File_Uploader('template_attachment');
								$uploader->setAllowedExtensions(array('jpg','jpeg','gif','png','pdf','doc','xls','csv','docx'));		
								
								$uploader->setFilesDispersion(false);						    
								
								$path = Mage::getBaseDir('media') . DS . 'advancemsg' . DS;
								$attachName = explode(".",$_FILES['template_attachment']['name']);
								$extension = end($attachName);
								$file = implode(explode(".",$_FILES['template_attachment']['name'],-1));
								$today = date("F j, Y, g i a");
								$fileName = $file . $today . "." . $extension;
														
								$uploader->save($path, $fileName);
								
								$fileFlag = false;					
							}
							else{
								$path = Mage::getBaseDir('media') . DS . 'advancemsg' . DS;
								$fileNameExp = explode(".",$fileName);	
								$extension = end($fileNameExp);
								$fileNameTmp = $file . $today . "." . $extension;
								copy($path . DS . $fileName, $path . DS . $fileNameTmp);
								$fileName = $fileNameTmp;				
								$uploader->save($path, $fileName);	
							}
							
							$fileName = str_replace(",", "", $fileName);
							$fileName = str_replace(" ", "_", $fileName);
							$messageContent->setFileName($fileName);
							$messageContent->save();
							Mage::log('filename'.$fileName,null,'filename.log');
							
							$successmessage = Mage::helper('advancemsg')->__('Message was sent successfully to %d customer(s)', count($customerIds));
							$message='';
						} catch (Exception $e) {
							 $message = $e->getMessage();
						}
					}
					else{
						
						$messageContent->save();
						$successmessage = Mage::helper('advancemsg')->__('Message was sent successfully to %d customer(s)', count($customerIds));
						$message='';
				
					}
				}	
				
			} catch (Exception $e) {
				$message = $e->getMessage();
		    	}
				
				if($successmessage!=''){
					Mage::getSingleton('adminhtml/session')->addSuccess($successmessage);
				}
				if($message!=''){
					Mage::getSingleton('adminhtml/session')->addError($message);
				}

		$this->_redirect('*/*/new');
	}
	
	public function ajaxAction(){
		
		$template = Mage::getModel('advancemsg/template')->load($this->getRequest()->getParam('id'));
		
		$response = array (
				   'text'=> $template->getTemplateText(),
				   'styles'=> $template->getTemplateStyles()
				   );
		
		print json_encode($response);
	}
	
	public function massRemoveLogAction()
	{
	/**
	 * this action handles the remove message(s) event from the "advancemsg_log" table
	 */	
		$messageIds = $this->getRequest()->getParam('massaction');
	
		if(!is_array($messageIds)) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancemsg')->__('Please select message(s)'));
		} 
		else {
		    try {
		/**
		 * delete the selected message(s) from "advancemsg_log" table
		 */
			foreach ($messageIds as $messageId) {
			    //$message = Mage::getModel('advancemsg/content')->load($messageId);
			    //$message->delete();
			    $message= Mage::getModel('advancemsg/content')
				->load($messageId)
				->setStatus(Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_REMOVE)
				->setIsMassupdate(true)
				->save();
			}
			Mage::getSingleton('adminhtml/session')->addSuccess(
			    Mage::helper('advancemsg')->__(
				'Total of %d message(s) were successfully deleted from log', count($messageIds)
			    )
			);
		    } catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		    }
		}
		$this->_redirect('*/*/log');
	}
	
	public function massRemoveMessageAction()
	{
	/**
	 * this action handles the remove message(s) event from the "advancemsg_content" table
	 */		
		$messageIds = $this->getRequest()->getParam('massaction');		
	
		if(!is_array($messageIds)) {
				Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancemsg')->__('Please select message(s)'));
		} 
		else {
		    try {
		/**
		 * delete the selected message(s) from "advancemsg_content" table
		 */
			foreach ($messageIds as $messageId) {
			    $message = Mage::getModel('advancemsg/content')->load($messageId);
			    if($message->getFileName() != '') unlink(Mage::getBaseDir('media') . DS . 'advancemsg' . DS . $message->getFileName());
			    $message->delete();
			}
			
		    } catch (Exception $e) {
			Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		    }
		    Mage::getSingleton('adminhtml/session')->addSuccess(
			    Mage::helper('advancemsg')->__(
				'Total of %d message(s) were successfully deleted from messages', count($messageIds)
			    )
			);
		}
		$this->_redirect('*/*/advance');
	}
	
	
	public function massMarkAsReadAction()
	{
	/**
	 * this handles the marking of selected message(s) as read for the particular user from its message box
	 */
		$messageIds = $this->getRequest()->getParam('massaction');
		//$messageIds=explode(",",$messageIdsString);

		if(!is_array($messageIds))
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancemsg')->__('Please select message(s)'));
		}
		else
		{
		    try
			{
		/**
		 * set the status of all the selected message to MESSAGE_STATUS_READ
		 */
				foreach ($messageIds as $messageId)
				{
				    $message= Mage::getModel('advancemsg/content')
					->load($messageId)
					//->setStatus(Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_READ)
					->setStatus('3')
					->setIsMassupdate(true)
					->save();
					
					$collectionData = Mage::getModel('advancemsg/content')->getCollection()
						  ->addFieldToFilter("parent_id", array("eq" => $messageId))
						 ->load();
				
					foreach($collectionData as $items)
					{
						$items->setStatus('1');
						$items->save();
					}
					
				}		
			
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		    	}
		}
		$this->_redirect('*/*/log');
	}
	
	public function massMarkAsUnreadAction()
	{
	/**
	 * this handles the marking of selected message(s) as un-read for the particular user from its message box
	 */
		$messageIds = $this->getRequest()->getParam('massaction');
		//$messageIds=explode(",",$messageIdsString);

		if(!is_array($messageIds))
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancemsg')->__('Please select message(s)'));
		}
		else
		{
		    try
			{
		/**
		 * set the status of all the selected message to MESSAGE_STATUS_UNREAD
		 */
				foreach ($messageIds as $messageId)
				{
				    $message= Mage::getModel('advancemsg/content')
					->load($messageId)
					//->setStatus(Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_UNREAD)
					->setStatus('4')
					->setIsMassupdate(true)
					->save();
					
					$collectionData = Mage::getModel('advancemsg/content')->getCollection()
			              ->addFieldToFilter("parent_id", array("eq" => $messageId))
			               ->load();
			
					foreach($collectionData as $items)
					{
						$items->setStatus('0');
						$items->save();
					}
					
				}		
				
			} catch (Exception $e) {
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
		    	}
		}
		$this->_redirect('*/*/log');
	}
	
	
	
	public function gridAction()
        {
            $this->loadLayout(false);
            $this->renderLayout();
        }
    
       	public function adminlogviewAction()
	{
	/**
	* This Action is used to view the selected message in message log section of admin. 
	*/
	    //gets message id
	    $data   = $this->getRequest()->getParams();
	    $id     = $data['id'];
	    
	    //Set Status to read
    
            // advancemsg_content model
	    $contentmodel = Mage::getModel('advancemsg/content');
	    $contentmodel->load($id);
	    $contentmodel->setStatus(1);
	    $contentmodel->save();
	    
	    $replyThreads = $contentmodel->getCollection()
	            ->addFieldToFilter("parent_id", array("eq" => $id))
	            ->load();
		
	    foreach($replyThreads as $items){
			$items->setStatus('1');
			 $items->save();
            }
	    
	    $this->loadLayout()->_setActiveMenu('advancemsg/items');
	    $contactEditBlock = $this->getLayout()->createBlock('advancemsg/adminhtml_log_edit');
	    $this->getLayout()->getBlock('content')->append($contactEditBlock);    	
            $this->renderLayout(); 
	}
	
	public function adminlogreplyAction()
	{
	/**
	* This Action is executed when admin replies from message log section. 
	*/
	    $this->loadLayout();
	    $this->renderLayout();
	    $data = $this->getRequest()->getParams();
	    $parentMessageId = $data['id'];
	    $messageText = $data['message_text'];
	    $user = Mage::getSingleton('admin/session');
	    $adminId = $user->getUser()->getId();
	    //$adminName = $user->getUser()->getUsername();
	    
	    $messageId = Mage::getModel('advancemsg/content')->getCollection()
                         ->addFieldToFilter("message_id", array("eq" => $parentMessageId))
                         ->load();
	   
	    foreach($messageId as $items){
			$customerStatus = $items->getCustomerStatus();
			if($customerStatus == '0'){
			    $items->setCustomerStatus('0');
				$items->save();
			}if($customerStatus == '1'){
				$items->setCustomerStatus('1');
				$items->save();
			}if($customerStatus == '-2'){
				$items->setCustomerStatus('1');
				$items->save();
			}if($customerStatus == '3'){
				$items->setCustomerStatus('1');
				$items->save();
			}if($customerStatus == '4'){
				$items->setCustomerStatus('0');
				$items->save();
			}
        }
			 
	    foreach($messageId as $key=>$object) {
		
		if($object['receiver_type'] == 'customer'){
		$customerData = Mage::getModel('customer/customer')->load($object['receiver_id']);
	        $customerId = $customerData->getId();	
		}
		if($object['sender_type'] == 'customer'){
		$customerData = Mage::getModel('customer/customer')->load($object['sender_id']);
	        $customerId = $customerData->getId();	
		}
	    }	
  
            try{
	    if($messageText!='') {	
		    
		$msgContent= Mage::getModel('advancemsg/content')
			    ->setUserId($adminId)
			    ->setMessageText($messageText)
			   // ->setAddedAt(date("Y-M-d H:i:s", Mage::getModel('core/date')->timestamp(time())))
			    ->setAddedAt(now())
			    ->setModifiedAt(now())
			    ->setParentId($parentMessageId)
			    ->setStatus('0')
			    ->setCustomerStatus('0')
			    ->setUserType('admin')
			    ->setSenderId($adminId)
			    ->setSenderType('admin')
			    ->setSentByUsername('Admin')
			    ->setReceiverId($customerId)
			    ->setReceiverType('customer') 
			    ->save();	
	   }
	   
	   Mage::getSingleton('core/session')->addSuccess(
			    Mage::helper('advancemsg')->__(
				'Message has been successfully sent', count($messageId)
			    )
		);
	  
	   } catch (Exception $e) {
			Mage::getSingleton('core/session')->addError($e->getMessage());
	     }
	     $this->_redirect('*/*/adminlogview/id/'.$data['id']);
	   
	}

}
