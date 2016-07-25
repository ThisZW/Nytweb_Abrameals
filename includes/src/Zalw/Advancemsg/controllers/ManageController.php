<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Controller for manage message at frontend 
 */
class Zalw_Advancemsg_ManageController extends Mage_Core_Controller_Front_Action
{
	public function indexAction()
	{
	/**
	 * to generate the page for the message inbox specific to the particular user
	 */
	
		// if the customer session is not set then redirect the user to login page 
		if(!Mage::getSingleton('customer/session')->getCustomer()->getId())	$this->_redirect('customer/account/login');
		
		//Get current layout state
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->renderLayout();		
	}
	
	public function sendAction()
	{
	/**
	 * to generate the page for the customer to send message to admin
	 */
	
		// if the customer session is not set then redirect the user to login page 
		if(!Mage::getSingleton('customer/session')->getCustomer()->getId())	$this->_redirect('customer/account/login');
		
		//Get current layout state
		$this->loadLayout();
		$this->_initLayoutMessages('customer/session');
		$this->renderLayout();		
	}
	

	public function viewAction()
	{
	/**
	 * to view a selected message for the particular user
	 */
	
		// if the customer session is not set then redirect the user to login page
		if(!Mage::getSingleton('customer/session')->getCustomer()->getId())	$this->_redirect('customer/account/login');
		$this->loadLayout();
		$this->renderLayout();
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
	    $contentmodel->setCustomerStatus(1);
	    $contentmodel->save();
	    
	    $replyThreads = $contentmodel->getCollection()
	            ->addFieldToFilter("parent_id", array("eq" => $id))
	            ->load();
		
	    foreach($replyThreads as $items){
			$items->setCustomerStatus('1');
			 $items->save();
            }
	}
	
	public function previewAction()
	{
	/**
	 * to generate preview page when the user or the admin wants to preview any message
	 */
		$this->loadLayout();
		$this->getLayout()->getBlock('advancemsg.preview')->setFormData($this->getRequest()->getParams());
		$this->renderLayout();
		
	}

	public function customermsgAction()
	{
		$this->loadLayout();
		$this->renderLayout();
	}
	
	/*public function sendmessageAction()
	{	

		// if the customer session is not set then redirect the user to login page
		if(!Mage::getSingleton('customer/session')->getCustomer()->getId())	$this->_redirect('customer/account/login');	
		$this->loadLayout();
		$this->renderLayout();
		//gets sends,date
						
		$message=$this->getRequest()->getParam('message');		
		$messagetitle=$this->getRequest()->getParam('messagetitle');		
		$fileFlag = true;
		$fileName = '';
		$successmessage='';
		if(Mage::getSingleton('customer/session')->isLoggedIn()){		
			$name=Mage::getSingleton('customer/session')->getCustomer()->getName();
			$customerId=Mage::getSingleton('customer/session')->getCustomer()->getId();
			$customerName=Mage::getSingleton('customer/session')->getCustomer()->getName();
		} else {
			$name='Guest';
		}
		try{
		if($message!='')
		{
		$obj=Mage::getModel('advancemsg/content')
			->setMessageText($message)
			->setMessageTitle($messagetitle)
			->setAddedAt(now())
			->setStatus('0')
			->setCustomerStatus('1')
			->setParentId('0')
			->setSenderId($customerId)
			->setSenderType('customer')
			->setSentByUsername($customerName)
			->setReceiverId('1')
			->setReceiverType('admin');

			if (isset($_FILES['template_attachment']['name']) && $_FILES['template_attachment']['name'] != '') {
				$obj->setAttach('1');
			} else {
				$obj->setAttach('0');	
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
							$obj->setFileName($fileName);
							$obj->save();
							Mage::log('filename'.$fileName,null,'filename.log');
							
							$successmessage = Mage::helper('advancemsg')->__('Message was sent successfully to %d customer(s)', count($customerIds));
							$message='';
						} catch (Exception $e) {
							 $message = $e->getMessage();
						}
					}
	}
		}
				catch (Exception $e) {
			Mage::getSingleton('core/session')->addError($e->getMessage());
		}
		Mage::getSingleton('core/session')->addSuccess(
			    Mage::helper('advancemsg')->__(
				'Message has been successfully sent'
			    )
		);
		
	*/
	//	$this->_redirect('*/*/customermsg');
		
	//} 
	
	public function sendmessageAction()
	{
		// if the customer session is not set then redirect the user to login page
		if(!Mage::getSingleton('customer/session')->getCustomer()->getId())	$this->_redirect('customer/account/login');	
		$this->loadLayout();
		$this->renderLayout();
		//gets sends,date
						
		$message=$this->getRequest()->getParam('message');		
		$messagetitle=$this->getRequest()->getParam('messagetitle');		
		if(Mage::getSingleton('customer/session')->isLoggedIn()){		
			$name=Mage::getSingleton('customer/session')->getCustomer()->getName();
			$customerId=Mage::getSingleton('customer/session')->getCustomer()->getId();
			$customerName=Mage::getSingleton('customer/session')->getCustomer()->getName();
		}
		
		else
		{
			$name='Guest';
		}
		try{
		//stores if message is not null		
		if($message!='')
		{
		$obj=Mage::getModel('advancemsg/content')
			->setMessageText($message)
			->setMessageTitle($messagetitle)
			->setAddedAt(now())
			->setStatus('0')
			->setCustomerStatus('1')
			->setParentId('0')
			->setSenderId($customerId)
			->setSenderType('customer')
			->setSentByUsername($customerName)
			->setReceiverId('1')
			->setReceiverType('admin')
			if (isset($_FILES['template_attachment']['name']) && $_FILES['template_attachment']['name'] != '') {$obj->setAttach('1');}
			else{$obj->setAttach('0');}
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
					$obj->setFileName($fileName);
					$obj->save();
					Mage::log('filename'.$fileName,null,'filename.log');
					
					$successmessage = Mage::helper('advancemsg')->__('Message was sent successfully to %d customer(s)', count($customerIds));
					$message='';
				} catch (Exception $e) {
					 $message = $e->getMessage();
				}
					}
					else{
						
						$obj->save();
						$successmessage = Mage::helper('advancemsg')->__('Message was sent successfully to %d customer(s)', count($customerIds));
						$message='';
				
					}
			
			
		Mage::getSingleton('core/session')->addSuccess(
			    Mage::helper('advancemsg')->__(
				'Message has been successfully sent'
			    )
		);
		} catch (Exception $e) {
			Mage::getSingleton('core/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*/customermsg');
		
	}
	
	
	public function massRemoveAction()
	{
	/**
	 * this handles the removing of selected message(s) for the particular user from its message box
	 */
		$messageIdsString = $this->getRequest()->getParam('messageData');
		$messageIds=explode(",",$messageIdsString);

		if(!is_array($messageIds)) {
				Mage::getSingleton('core/session')->addError(Mage::helper('advancemsg')->__('Please select message(s)'));
				Mage::getSingleton('core/session')->addError(Mage::helper('advancemsg')->__('Please select message(s)'));
		} 
		else {
		    try {
		/**
		 * set the status of all the selected message to MESSAGE_STATUS_REMOVE
		 */
			foreach ($messageIds as $messageId) {
				$message= Mage::getModel('advancemsg/content')
					->load($messageId)
					->setCustomerStatus(Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_REMOVE)
					->setIsMassupdate(true)
					->save();
			   
			}
			Mage::getSingleton('core/session')->addSuccess(
			    Mage::helper('advancemsg')->__(
				'Total of %d message(s) were successfully deleted', count($messageIds)
			    )
			);
		    } catch (Exception $e) {
			Mage::getSingleton('core/session')->addError($e->getMessage());
		    }
		}
		$this->_redirect('*/*/index');
	}

	public function massDeleteAction()
	{
	/**
	 * this handles the removing of selected message(s) for the particular user from its sent message box
	 */
		$messageIdsString = $this->getRequest()->getParam('messageData');
		$messageIds=explode(",",$messageIdsString);
		
		if(!is_array($messageIds)) {
				Mage::getSingleton('customer/session')->addError(Mage::helper('advancemsg')->__('Please select message(s)'));
		} 
		else {
		    try {
		/**
		 * delete the messages if user chooses to delete the message
		 */
			foreach ($messageIds as $messageId) {
				$message= Mage::getModel('advancemsg/customermsg')
					->load($messageId)
					->delete();
			 			}
			Mage::getSingleton('core/session')->addSuccess(
			    Mage::helper('advancemsg')->__(
				'Total of %d message(s) were successfully deleted', count($messageIds)
			    )
			);
		    } catch (Exception $e) {
			Mage::getSingleton('core/session')->addError($e->getMessage());
		    }
		}
		$this->_redirect('*/*/customermsg');
	}	

	public function massMarkAsReadAction()
	{
	/**
	 * this handles the marking of selected message(s) as read for the particular user from its message box
	 */
		$messageIdsString = $this->getRequest()->getParam('messageData');
		$messageIds=explode(",",$messageIdsString);

		if(!is_array($messageIds))
		{
			Mage::getSingeton('customer/session')->addError($this->__('Please select message(s)'));
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
					//->setCustomerStatus(Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_READ)
					->setCustomerStatus('3')
					->setIsMassupdate(true)
					->save();
				
					$collectionData = Mage::getModel('advancemsg/content')->getCollection()
						  ->addFieldToFilter("parent_id", array("eq" => $messageId))
						 ->load();
				
					foreach($collectionData as $items)
					{
						$items->setCustomerStatus('1');
						$items->save();
					}
				}		
			
			} catch (Exception $e) {
				Mage::getSingleton('customer/session')->addError($e->getMessage());
		    	}
		}
		$this->_redirect('*/*/index');
	}
	
	public function massMarkAsUnreadAction()
	{
	/**
	 * this handles the marking of selected message(s) as un-read for the particular user from its message box
	 */
		$messageIdsString = $this->getRequest()->getParam('messageData');
		$messageIds=explode(",",$messageIdsString);

		if(!is_array($messageIds))
		{
			Mage::getSingleton('customer/session')->addError($this->__('Please select message(s)'));
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
					//->setCustomerStatus(Zalw_Advancemsg_Model_Content::MESSAGE_STATUS_UNREAD)
					->setCustomerStatus('4')
					->setIsMassupdate(true)
					->save();
					
				    $collectionData = Mage::getModel('advancemsg/content')->getCollection()
			              ->addFieldToFilter("parent_id", array("eq" => $messageId))
			               ->load();
			
					foreach($collectionData as $items)
					{
						$items->setCustomerStatus('0');
						$items->save();
					}
				}		
				
			} catch (Exception $e) {
				Mage::getSingleton('customer/session')->addError($e->getMessage());
		    	}
		}
		$this->_redirect('*/*/index');
	}
	
	public function gridAction()
	{
	/**
	 * this generates the grid containing the listing of messages for the particular user
	 */
		if(!Mage::getSingleton('customer/session')->getCustomer()->getId())	$this->_redirect('customer/account/login');
		$this->loadLayout()->renderLayout();		
	}

	public function messagegridAction()
	{
	/**
	 * this generates the messagegrid containing the listing of messages for the particular user
	 */
		if(!Mage::getSingleton('customer/session')->getCustomer()->getId())	$this->_redirect('customer/account/login');
		$this->loadLayout()->renderLayout();		
	}


        public function customerreplyAction()
	{
	/**
	 * to give a reply from inbox items
	 */
		if(!Mage::getSingleton('customer/session')->getCustomer()->getId()) $this->_redirect('customer/account/login');
		$data = $this->getRequest()->getParams();
		$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		$customerName = Mage::getSingleton('customer/session')->getCustomer()->getName();
		$this->loadLayout()->renderLayout();
		  
	    $messageId = Mage::getModel('advancemsg/content')->getCollection()
                         ->addFieldToFilter("message_id", array("eq" => $data['id']))
                         ->load();
		
		foreach($messageId as $items){
				$adminStatus = $items->getadminStatus();
				if($adminStatus == '0'){
					$items->setStatus('0');
					$items->save();
				}if($adminStatus == '1'){
					$items->setStatus('1');
					$items->save();
				}if($adminStatus == '-2'){
					$items->setStatus('1');
					$items->save();
				}if($adminStatus == '3'){
					$items->setStatus('1');
					$items->save();
				}if($adminStatus == '4'){
					$items->setStatus('0');
					$items->save();
				}
            }
			 
	        foreach($messageId as $key=>$object) {
			    if($object['receiver_type'] == 'admin'){
		        $adminData = Mage::getModel('admin/user')->load($object['receiver_id']);
	            $adminIds = $adminData->getId();	
	        	}
	        	if($object['sender_type'] == 'admin'){
	        	$adminData = Mage::getModel('admin/user')->load($object['sender_id']);
	            $adminIds = $adminData->getId();	
	        	}
	        }
		
		try{
		//if($messageText!='') {
			
			$obj= Mage::getModel('advancemsg/content')
				->setUserId($customerId)
				->setMessageText($data['message'])
				//->setAddedAt(date("Y-M-d H:i:s", Mage::getModel('core/date')->timestamp(time())))
				->setAddedAt(now())
				->setParentId($data['id'])
				->setStatus('0')
				->setUserType('customer')
				->setSenderId($customerId)
			        ->setSenderType('customer')
				->setSentByUsername($customerName)
				->setReceiverId($adminIds)
				->setReceiverType('admin') 
				->setModifiedAt(now())
				->save();
		//}
		Mage::getSingleton('core/session')->addSuccess(
			    Mage::helper('advancemsg')->__(
				'Message has been successfully sent', count($messageId)
			    )
		);
		} catch (Exception $e) {
			Mage::getSingleton('core/session')->addError($e->getMessage());
		}
		$this->_redirect('*/*/view/id/'.$data['id']);
	}
	
	public function customersentviewAction()
	{	
	/**
	 * to view a selected message for the particular user
	 */
		//if(!Mage::getSingleton('customer/session')->getCustomer()->getId())	$this->_redirect('customer/account/login');
		//$this->loadLayout();
		//$this->renderLayout();
	if(!Mage::getSingleton('customer/session')->getCustomer()->getId())	$this->_redirect('customer/account/login');
            // advancemsg_content model
	    $id     = $this->getRequest()->getParam('id');
	    $logmodel  = Mage::getModel('advancemsg/content');
	    $logmodel->load($id);
	    //$logmodel->setStatus(1);
	    $item->setCustomerStatus('1');
	    $logmodel->save();
	    
	    $replyThread = $logmodel->getCollection()
	            ->addFieldToFilter("parent_id", array("eq" => $id))
	            ->load();
		
	    foreach($replyThread as $item){
			//$item->setStatus('1');
			$item->setCustomerStatus('1');
			$item->save();
            }
	    
	     $this->loadLayout();
	     $this->renderLayout();
	}
	
	public function customersentreplyAction()
	{
	/**
	 * to give a reply from sent items
	 */
		if(!Mage::getSingleton('customer/session')->getCustomer()->getId()) $this->_redirect('customer/account/login');
		$data = $this->getRequest()->getParams();
		$customerName = Mage::getSingleton('customer/session')->getCustomer()->getName();
		$customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
		$this->loadLayout()->renderLayout();
		
		$messageId = Mage::getModel('advancemsg/customermsg')->getCollection()
			->addFieldToFilter("message_id", array("eq" => $data['id']))
			->load();

	        foreach($messageId as $key=>$object) {
		$adminData = Mage::getModel('admin/user')->load($object['sender_id']);
	        $adminId = $adminData->getId();
	        }
		
		if($data['message']!='') {
		    $obj=Mage::getModel('advancemsg/customermsg')
		    ->setName($customerName)
		    ->setMessage($data['message'])			
		    //->setDate(date("d-m-Y H:i:s",  Mage::getModel('core/date')->timestamp(time())))
		    ->setDate(now())
		    ->setStatus('0')
		    ->setParentId($data['id'])
		    ->setUserType('customer')
		    ->setSenderId($customerId)
		    ->setSenderType('customer')
		    ->setReceiverId($adminId)
		    ->setReceiverType('admin') 
		    ->save();
		}
	}
	
	//7-25-2016 file attachment handler for advancedmsg
	public function saveFormAttachment($attach){
		$a = $attach['template_attachment'];
		print_r($a);
		$target_dir = $_SERVER["DOCUMENT_ROOT"] . "/media/customer_upload/";
		$target_file = $target_dir . basename($a["name"]);
		$uploadOk = 1;
		
		$imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
		$acceptedFileType = array(
			'jpg',
			'png',
			'jpeg',
			'pdf',
			'doc',
			'xls',
			'csv',
			'docx',
		)
		
		if(!in_array($imageFileType,$acceptedFileType)){
			$uploadOk = 0;
		}
		
		if ($a['size'] > 2000000){
			echo "<br> Sorry, your file is too large.";
			$uploadOk = 0;
		}
		
		if ($a["size"] == 0) {
			return 0;
		}
		
		
	}
	
	
	
}