<?php
/**
 * aheadWorks Co.
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the EULA
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://ecommerce.aheadworks.com/AW-LICENSE.txt
 *
 * =================================================================
 *                 MAGENTO EDITION USAGE NOTICE
 * =================================================================
 * This software is designed to work with Magento community edition and
 * its use on an edition other than specified is prohibited. aheadWorks does not
 * provide extension support in case of incorrect edition use.
 * =================================================================
 *
 * @category   AW
 * @package    AW_AdvancedReviews
 * @version    2.3.9
 * @copyright  Copyright (c) 2010-2012 aheadWorks Co. (http://www.aheadworks.com)
 * @license    http://ecommerce.aheadworks.com/AW-LICENSE.txt
 */


class AW_AdvancedReviews_Adminhtml_Awadvancedreviews_ProsconsController extends Mage_Adminhtml_Controller_Action
{
    protected $_currentRef;

    protected function _isAllowed()
    {
        $params = $this->getRequest()->getParams();
        switch($this->getRequest()->getActionName()){
            case 'pros':
                return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/advancedreviews/pros_menu');
            case 'cons':
                return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/advancedreviews/cons_menu');
            case 'user':
                return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/advancedreviews/user_defined_menu');
            case 'edit':
            case 'new':
            case 'save':
            case 'delete':
            case 'massDelete':
            case 'massUpdateStatus':
                switch($params['ref']){
                    case 'pros':
                        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/advancedreviews/pros_menu');
                    case 'cons':
                        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/advancedreviews/cons_menu');
                    case 'user':
                        return Mage::getSingleton('admin/session')->isAllowed('catalog/reviews_ratings/advancedreviews/user_defined_menu');
                    default:
                        return false;
                }
                break;
            default:
                return false;
        }
    }

    protected function _getRef()
    {
        if ($this->_currentRef) {
            return $this->_currentRef;
        } else {
            return $this->_currentRef = $this->getRequest()->getParam('ref');
        }
    }

    protected function _getItemName()
    {
        if ($ref = $this->_getRef()) {
            if ($ref === 'pros') {
                return Mage::helper('advancedreviews')->__('Pros');
            } elseif ($ref === 'cons') {
                return Mage::helper('advancedreviews')->__('Cons');
            } else {
                return Mage::helper('advancedreviews')->__('Item');
            }
        } else {
            return Mage::helper('advancedreviews')->__('Item');
        }
        return '';
    }

    protected function _getMenuToActivate()
    {
        if ($ref = $this->_getRef()) {
            if ($ref === 'pros') {
                return 'catalog/reviews_ratings/advancedreviews/pros_menu';
            } elseif ($ref === 'cons') {
                return 'catalog/reviews_ratings/advancedreviews/cons_menu';
            }
        }
        return 'catalog/reviews_ratings/advancedreviews';
    }

    protected function _getTitleToActiavte()
    {
        if ($ref = $this->_getRef()) {
            if ($ref === 'pros') {
                return Mage::helper('advancedreviews')->__('Pros');
            } elseif ($ref === 'cons') {
                return Mage::helper('advancedreviews')->__('Cons');
            } elseif ($ref === 'user') {
                return Mage::helper('advancedreviews')->__('User-defined Pros and Cons');
            } else {
                return Mage::helper('advancedreviews')->__('Item');
            }
        }
    }

    protected function _prepareInit()
    {
        $this->_setGlobalItemName();
        $this->loadLayout()
            ->_setActiveMenu($this->_getMenuToActivate())
            ->_addBreadcrumb($this->_getTitleToActiavte(), $this->_getTitleToActiavte());
        if (method_exists($this, '_title')) {
            $this->_title($this->__('Advanced Reviews'))->_title($this->_getTitleToActiavte());
        }

        return $this;
    }

    /**
     * This function set in registry type of Proscons
     * that we work now.
     */
    protected function _setGlobalType($value)
    {
        Mage::register(Mage::helper('advancedreviews')->getConstPcRegRef(), $value);
        if (Mage::helper('advancedreviews')->isPros()) {
            $this->_currentRef = 'pros';
        } elseif (Mage::helper('advancedreviews')->isCons()) {
            $this->_currentRef = 'cons';
        } else {
            $this->_currentRef = 'user';
        }
        return $this;
    }

    protected function _setGlobalItemName()
    {
        Mage::register('advancedreviews_proscons_itemname', $this->_getItemName());
    }

    protected function _getRedirectLink($type)
    {
        if ($type === Mage::helper('advancedreviews')->getConstTypePros()) {
            return 'adminhtml/awadvancedreviews_proscons/pros';
        } elseif ($type === Mage::helper('advancedreviews')->getConstTypeCons()) {
            return 'adminhtml/awadvancedreviews_proscons/cons';
        } elseif ($type === Mage::helper('advancedreviews')->getConstTypeUser()) {
            return 'adminhtml/awadvancedreviews_proscons/user';
        }
        return 'adminhtml/awadvancedreviews_proscons/404';
    }

    public function prosAction()
    {
        $this->_setGlobalType(Mage::helper('advancedreviews')->getConstTypePros());
        $this->indexAction();
    }

    public function consAction()
    {
        $this->_setGlobalType(Mage::helper('advancedreviews')->getConstTypeCons());
        $this->indexAction();
    }

    public function userAction()
    {
        $this->_setGlobalType(Mage::helper('advancedreviews')->getConstTypeUser());
        $this->_prepareInit()->renderLayout();
    }

    public function indexAction()
    {
        $this->_prepareInit()->renderLayout();
    }

    public function gridAction()
    {
        $this->loadLayout();
        $this->getResponse()->setBody(
            $this->getLayout()->createBlock('advancedreviews/adminhtml_proscons_grid')->toHtml()
        );
    }

    public function newAction()
    {
        $this->_forward('edit');
    }

    /**
     * Edit ProsConsItem
     */
    public function editAction()
    {
        $this->_prepareInit();
        $id = $this->getRequest()->getParam('id');
        $model = Mage::getModel('advancedreviews/proscons')->load($id);

        if ($model->getId() || $id == 0) {
            $data = Mage::getSingleton('adminhtml/session')->getFormData(true);

            if (!empty($data)) {
                $model->setData($data);
            }

            Mage::register('advancedreviews_proscons_data', $data);

            $this->loadLayout();
            $this->_setActiveMenu($this->_getMenuToActivate());

            // here add breadcrumbs (TODO)

            $this->getLayout()->getBlock('head')->setCanLoadExtJs(true);

            $this->_addContent($this->getLayout()->createBlock('advancedreviews/adminhtml_proscons_edit'));

            $this->renderLayout();
        } else {
            if (Mage::helper('advancedreviews')->isPros()) {
                $redirect = '*/*/pros';
            } elseif (Mage::helper('advancedreviews')->isPros()) {
                $redirect = '*/*/cons';
            } else {
                $redirect = '*/*/user';
            }
            Mage::getSingleton('adminhtml/session')->addError(
                $this->_getItemName() . " " . Mage::helper('advancedreviews')->__('does not exist')
            );
            $this->_redirect($redirect);
        }
    }

    public function saveAction()
    {
        if ($data = $this->getRequest()->getPost()) {
            if ($ref = $this->getRequest()->getParam('ref')) {
                if ($ref === 'pros') {
                    $type = Mage::helper('advancedreviews')->getConstTypePros();
                } elseif ($ref === 'cons') {
                    $type = Mage::helper('advancedreviews')->getConstTypeCons();
                } else {
                    $type = Mage::helper('advancedreviews')->getConstTypeUser();
                }
            }
            $proscons = Mage::getModel('advancedreviews/proscons');

            //if it was edit process load our record
            if ($id = $this->getRequest()->getParam('id')) {
                $proscons->load($id);
                $owner = $proscons->getOwner();
                $isInsert = false;
            } else {
                $owner = Mage::helper('advancedreviews')->getConstOwnerAdmin();
                $isInsert = true;
            }

            //save Proscons data
            $proscons
                ->setName($data['name'])
                ->setStatus($data['status'])
                ->setSortOrder($data['sort_order'])
                ->setStores($data['stores']);

            if ($isInsert) {
                $proscons
                    ->setType($type)
                    ->setOwner($owner);
            }

            $proscons->save();

            if ($isInsert) {
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(
                        $this->_getItemName() . " " .
                        Mage::helper('adminhtml')->__('was successfully added')
                    );
            } else {
                Mage::getSingleton('adminhtml/session')
                    ->addSuccess(
                        $this->_getItemName() . " " .
                        Mage::helper('adminhtml')->__('was successfully saved')
                    );
            }
        }
        $this->_redirect($this->_getRedirectLink($type));
    }

    protected function _getGlobalType()
    {
        if ($ref = $this->getRequest()->getParam('ref')) {
            if ($ref === 'pros') {
                return Mage::helper('advancedreviews')->getConstTypePros();
            } elseif ($ref === 'cons') {
                return Mage::helper('advancedreviews')->getConstTypeCons();
            } else {
                return Mage::helper('advancedreviews')->getConstTypeUser();
            }
        } else {
            return 'pros';
        }
    }

    public function deleteAction()
    {
        $type = $this->_getGlobalType();

        if ($id = $this->getRequest()->getParam('id')) {
            try {
                $model = Mage::getModel('advancedreviews/proscons');
                $model->setId($id)->delete();
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    $this->_getItemName() . " " .
                    Mage::helper('adminhtml')->__('was successfully deleted')
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect($this->_getRedirectLink($type));
    }

    public function massDeleteAction()
    {
        $type = $this->_getGlobalType();
        $proscons = $this->getRequest()->getParam('proscons');
        if (!is_array($proscons)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('adminhtml')->__('Please select ') . $this->_getItemName()
            );
        } else {
            try {
                foreach ($proscons as $prosconsId) {
                    $model = Mage::getModel('advancedreviews/proscons')->load($prosconsId);
                    $model->delete();
                }
                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('advancedreviews')->__(
                        'Total of %d record(s) were successfully deleted', count($proscons)
                    )
                );
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }

        $this->_redirect($this->_getRedirectLink($type));
    }

    public function massUpdateStatusAction()
    {
        $type = $this->_getGlobalType();
        $proscons = $this->getRequest()->getParam('proscons');
        if (!is_array($proscons)) {
            Mage::getSingleton('adminhtml/session')->addError(
                Mage::helper('advancedreviews')->__('Please select items(s)')
            );
        } else {
            $session = Mage::getSingleton('adminhtml/session');
            try {
                $status = $this->getRequest()->getParam('status');
                foreach ($proscons as $prosconsId) {
                    $model = Mage::getModel('advancedreviews/proscons')->load($prosconsId);
                    $model->setStatus($status)
                        ->save();
                }
                $session->addSuccess(
                    Mage::helper('advancedreviews')->__(
                        'Total of %d record(s) were successfully updated', count($proscons)
                    )
                );
            } catch (Mage_Core_Exception $e) {
                $session->addException($e->getMessage());
            } catch (Exception $e) {
                $session->addError(
                    Mage::helper('adminhtml')->__('Error while updating selected items(s). Please try again later.')
                );
            }
        }
        $this->_redirect($this->_getRedirectLink($type));
    }
}