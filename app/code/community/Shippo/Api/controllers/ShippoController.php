<?php
class Shippo_Api_ShippoController extends Mage_Adminhtml_Controller_Action
{
  const APIUSER = 'shippo';
  const APIMAIL = 'support@goshippo.com';
  const APIROLE = 'shippo';
  const LOGOURL = 'https://shippo-static.s3.amazonaws.com/img/logo/logo-magento.png';
  const SETUPURL = 'https://goshippo.com/magento/setup';

  public function postAction()
  {
    $this->loadLayout();
    $post = $this->getRequest()->getPost();
    $block = $this->getLayout()->createBlock('adminhtml/template');
    try {
      if (empty($post)) {
        Mage::throwException($this->__('Invalid form data.'));
      }
      if($post['confirm'])
      {
        $apikey = $this->_setupAccess();
        $message = $this->__("Credentials successfully created.");
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
        if($apikey) {
          $block->setData("apiuser", self::APIUSER);
          $block->setData("apikey", $apikey);
        }
        else {
          throw new Exception("User was not created: No API key obtained");
        }
        $block->setData('logoUrl', self::LOGOURL);
        $block->setData('setupUrl', self::SETUPURL);
        $block->setTemplate('shippo/active.phtml');
        $this->_addContent($block);
        $this->renderLayout();
        return;
      }
      elseif ($post['remove_access']) {
        $this->_removeAccess();
        $message = 'Shippo credentials successfully removed';
        Mage::getSingleton('adminhtml/session')->addSuccess($message);
      }
    } catch (Exception $e) {
      Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
    }
    $this->_redirect('adminhtml/shippo');
  }

  private function _makeApiKey()
  {
    return Mage::getModel('customer/customer')->generatePassword(12);;
  }

  public function getUserByEmail($email)
  {
    return Mage::getModel('api/user')->load($email, "email");
  }
  public function getRoleByName($name)
  {
    return Mage::getModel('api/roles')->load($name, 'role_name');
  }

  private function _staticUserData()
  {
    return array(
      "username" => self::APIUSER,
      "firstname" => "Shippo",
      "lastname" => "Shippo",
      "email" => self::APIMAIL,
      "password" => '',
      "confirmation" => ''
    );
  }

  private function _removeAccess()
  {
    $role = $this->getRoleByName(self::APIROLE);
    $user = $this->getUserByEmail(self::APIMAIL);
    if($user->getId()) {
      $user->delete();
    }
    if($role->getId()) {
      $role->delete();
    }
    $this->_redirect('adminhtml/shippo');
  }

  private function _setupAccess()
  {
    $apikey = $this->_makeApiKey();
    $userData = $this->_staticUserData();
    $userData['new_api_key'] = $apikey;
    $userData['api_key_confirmation'] = $apikey;

    $resources = explode(",", "__root__,sales,sales/order,sales/order/creditmemo,sales/order/creditmemo/list,sales/order/creditmemo/info,sales/order/creditmemo/cancel,sales/order/creditmemo/comment,sales/order/creditmemo/create,sales/order/invoice,sales/order/invoice/cancel,sales/order/invoice/info,sales/order/invoice/void,sales/order/invoice/capture,sales/order/invoice/comment,sales/order/invoice/create,sales/order/shipment,sales/order/shipment/send,sales/order/shipment/info,sales/order/shipment/track,sales/order/shipment/comment,sales/order/shipment/create,sales/order/info,sales/order/change"); 


    $role = $this->getRoleByName(self::APIROLE);
    $user = $this->getUserByEmail(self::APIMAIL);
    
    if(!$role->getId())
    {
      $role = Mage::getModel('api/roles')->load($roleId);
      $role->setName(self::APIROLE)->setRoleType('G');
      $role->save();
      $roleId = $role->getId();

      Mage::getModel("api/rules")
        ->setRoleId($role->getId())
        ->setResources($resources)
        ->saveRel();
    }


    if(!$user->getId())
    {
     
      $user = Mage::getModel('api/user')->load($userId);

      $user->setData($userData);
      $user->save();
      $userId = $user->getId();

      $user->setRoleIds(array($roleId))
          ->setRoleUserId($user->getUserId())
          ->saveRelations();
      return $apikey;
    }
    return false;
  }

  public function indexAction()
  {
    // let's go
    $this->loadLayout();

    $role = $this->getRoleByName(self::APIROLE);
    $user = $this->getUserByEmail(self::APIMAIL);

    $block = $this->getLayout()->createBlock('adminhtml/template');
    if($user->getId()) {
      $block->setData("apiuser", $user->getUsername());
      $block->setData("apikey", "");
      $block->setData('logoUrl', self::LOGOURL);
      //echo var_dump($user);
      $block->setTemplate('shippo/active.phtml');
    }
    else {
      $block->setTemplate('shippo/form.phtml');
    }
    $this->_addContent($block);
    $this->renderLayout();
  }
}

