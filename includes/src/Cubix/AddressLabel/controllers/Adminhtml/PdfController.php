<?php
/**
 * @author     Harshit Jain <support@cubixws.co.uk>
 * @category   Cubix
 * @package    Cubix_AddressLabel
 */
class Cubix_AddressLabel_Adminhtml_PdfController extends Mage_Adminhtml_Controller_Action {
    
    /**
     * Create PDF for label
     * 
     * @return PDF 
     */
    public function printLabelAction() {
        $orderIds = $this->getRequest()->getPost('order_ids');
        $model = Mage::getModel('cubix_addresslabel/pdf');
        if (!empty($orderIds)) {
            $validate = $model->validate();
            if ($validate !== true) {
                $this->_getSession()->addError($validate);
            }
            else {
                $pdf = $model->getPdf($orderIds);
                return $this->_prepareDownloadResponse('label-'.Mage::getSingleton('core/date')->date('Y-m-d_H-i-s').'.pdf', $pdf->render(), 'application/pdf');
            }
        }
        $this->_getSession()->addError($this->__('Unable to print address labels'));
        $this->_redirect('adminhtml/sales_order');
    }
}