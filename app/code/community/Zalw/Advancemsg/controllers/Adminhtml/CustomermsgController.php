<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Controller manages the admin inbox
 */
class Zalw_Advancemsg_Adminhtml_CustomermsgController extends Mage_Adminhtml_Controller_Action
{
	//function initilizes the menu
	protected function _initAction() 
	{
		$this->loadLayout()->_setActiveMenu('advancemsg/items');
		
		return $this;
	}   
 	//function renders the layout
	public function indexAction() 
	{
		$this->_initAction()
			->renderLayout();
	}

	//function provides edit 
	public function editAction() 
	{
		//gets message id
		$id     = $this->getRequest()->getParam('id');
		$model  = Mage::getModel('advancemsg/customermsg')->load($id);
		
		//Set Status to read
		$model->setStatus(1);
		$model->save();
		if ($model->getId() || $id == 0) 
		{	
			
			//maintains session with messageid
			//$data = Mage::getSingleton('adminhtml/session')->getFormData(true);
			//
			//if (!empty($data)) 
			//{
			//	$model->setData($data);
			//}
			////makes registry of Form data
			//Mage::register('message_data', $model);
			////sets up the menu
			//$this->loadLayout();
			//$this->_setActiveMenu('customermsg/items');
			//
			//$this->getLayout()->getBlock('head')->setCanLoadExtJs(true);
			////adds up the edit
			//$this->_addContent($this->getLayout()->createBlock('advancemsg/adminhtml_customermsg_edit'))
			//->_addLeft($this->getLayout()->createBlock('advancemsg/adminhtml_customermsg_edit_tabs'));
			//
			//$this->loadLayout()->_setActiveMenu('advancemsg/items');
			$contactEditBlock = $this->getLayout()->createBlock(
			'advancemsg/adminhtml_customermsg_edit'
			);
			 $this->loadLayout()
			     ->_addContent($contactEditBlock)
			     ->renderLayout();
		} 
		else 
		{
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancemsg')->__('Message does not exist'));
			$this->_redirect('*/*/');
		}
	}

	
 	//provides edit and save message information 
	public function saveAction() 
	{	$data = $this->getRequest()->getPost();
			$model = Mage::getModel('advancemsg/customermsg');		
			$id     = $this->getRequest()->getParam('id');
			if($id==0){
						
			$model->setData($data)
				->setDate(date("d-m-Y H:i:s",Mage::getModel('core/date')->timestamp(time())))
				->setMessageId($this->getRequest()->getParam('id'));
			}
			else{
			$model->setData($data)
				
				->setMessageId($this->getRequest()->getParam('id'));
			}
			try {
			
				$model->save();
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('advancemsg')->__('Message was successfully saved'));
				Mage::getSingleton('adminhtml/session')->setFormData(false);

				if ($this->getRequest()->getParam('back')) {
					$this->_redirect('*/*/edit', array('id' => $model->getId()));
					return;
				}
				$this->_redirect('*/*/');
				return;
            		 } catch (Exception $e) {
                	 Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
                	 Mage::getSingleton('adminhtml/session')->setFormData($data);
                	 $this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
                	 return;
            		 }
        	         Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancemsg')->__('Unable to find message to save'));
		         $this->_redirect('*/*/');
	}
	//function deletes a message information
	public function deleteAction() 
	{
		//checks if messageid is not negative
		$data = $this->getRequest()->getPost();
		$id=$this->getRequest()->getParam('message_id');
		
		if( $this->getRequest()->getParam('id') > 0 ) {
			try 
			{
				//gets model
				$model = Mage::getModel('advancemsg/customermsg');
				//deletes the information of selected id 
				$model->setMessageId($this->getRequest()->getParam('id'))
					->delete();
					 
				Mage::getSingleton('adminhtml/session')->addSuccess(Mage::helper('advancemsg')->__('Message successfully deleted'));
				$this->_redirect('*/*/');
			} 
			catch (Exception $e) 
			{
				//gets error message
				Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
				//redirects to edit page
				$this->_redirect('*/*/edit', array('id' => $this->getRequest()->getParam('id')));
			}
		}
		$this->_redirect('*/*/');
	}
	//exports csv
    public function exportCsvAction()
    {
       $fileName   = 'customermsg.csv';
        $content    = $this->getLayout()->createBlock('advancemsg/adminhtml_customermsg_grid')
            ->getCsvFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }
	
	//function for mass delete
	 public function massDeleteAction() {
       
		$messageIds = $this->getRequest()->getParam('message_id');
		
        	if(!is_array($messageIds)) {
			Mage::getSingleton('adminhtml/session')->addError(Mage::helper('advancemsg')->__('Please select messages(s)'));
        } else {
            try {
                foreach ($messageIds as $messageId) {
                    $message = Mage::getModel('advancemsg/customermsg')->load($messageId);
                    $message->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('adminhtml')->__(
                        'Total of %d message(s) were successfully deleted', count($messageIds)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
		
    
        $this->_redirect('*/*/index');
	}	
	//exports xml
   	public function exportXmlAction()
    {
       $fileName   = 'customermsg.xml';
        $content    = $this->getLayout()->createBlock('advancemsg/adminhtml_customermsg_grid')
            ->getExcelFile();

        $this->_prepareDownloadResponse($fileName, $content);
    }

    	protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream')
    	{
        	$response = $this->getResponse();
        	$response->setHeader('HTTP/1.1 200 OK','');
        	$response->setHeader('Pragma', 'public', true);
        	$response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        	$response->setHeader('Content-Disposition', 'attachment; filename='.$fileName);
        	$response->setHeader('Last-Modified', date('r'));
        	$response->setHeader('Accept-Ranges', 'bytes');
        	$response->setHeader('Content-Length', strlen($content));
        	$response->setHeader('Content-type', $contentType);
        	$response->setBody($content);
        	$response->sendResponse();
        	
    	}
	
	 public function statusAction()
    {
        $messageIds = $this->getRequest()->getParam('message_id');
	
	$operationId = $this->getRequest()->getParam('opId');
		
		Mage::log('status change');
		Mage::log($messageIds);
		
		
		if(!is_array($messageIds)) {
            Mage::getModel('adminhtml/session')->addError($this->__('Please select message(s)'));
        } else {
            try {
                
				foreach ($messageIds as $messageId) {
			if($operationId=='1')
			{
                    $message = Mage::getSingleton('advancemsg/customermsg')
                        ->load($messageId)
                        ->setStatus('1')
                        ->save();
			}
			if($operationId=='0')
			{
                    $message = Mage::getSingleton('advancemsg/customermsg')
                        ->load($messageId)
                        ->setStatus('0')
                        ->save();
			}
                }

            } catch (Exception $e) {
                $this->_getSession()->addError($e->getMessage());
            }
        }
        $this->_redirect('*/*/index');
    }
    
        public function replyAction()
        {
	/*
	* Reply from admin inbox
	*/
	$user = Mage::getSingleton('admin/session');
	$adminuserId = $user->getUser()->getUserId();
	$userName = $user->getUser()->getUsername();
	$this->loadLayout();
	$this->renderLayout();
	$parentMessageId = $this->getRequest()->getParam('id');		
	$message=$this->getRequest()->getParam('message_text');
	$messagess= Mage::getModel('advancemsg/customermsg');
	
	$messageId = Mage::getModel('advancemsg/customermsg')->getCollection()
	         ->addFieldToFilter("message_id", array("eq" => $parentMessageId))
	         ->load();
	
	foreach($messageId as $key=>$object) {
		$customer = Mage::getModel('customer/customer')->load($object['sender_id']);
	        $customerId = $customer->getId();
	}
	
		if($message!='') {
		    $obj=Mage::getModel('advancemsg/customermsg')
			->setName($userName)
			->setMessage($message)			
			//->setDate(date("Y-M-d H:i:s", Mage::getModel('core/date')->timestamp(time())))
			->setDate(now())
			->setStatus('0')
			->setParentId($parentMessageId)
			->setUserType('admin')
			->setSenderId($adminuserId)
			->setSenderType('admin')
			->setReceiverId($customerId)
			->setReceiverType('customer') 
			->save();
		}
    }

}
