<?php

class Zalw_Memberchurnreport_Adminhtml_MemberchurnreportController extends Mage_Adminhtml_Controller_Report_Abstract {

    public function _initAction() {
        $this->loadLayout();
        return $this;
    }

    public function indexAction() {
        /*$this->_initAction()
                ->renderLayout();*/

        $this->_title($this->__('Reports'))->_title($this->__('Sales'))->_title($this->__('Sales'));

        $this->_showLastExecutionTime(Mage_Reports_Model_Flag::REPORT_ORDER_FLAG_CODE, 'sales');

        $this->_initAction()
            ->_setActiveMenu('report/sales/sales')
            ->_addBreadcrumb(Mage::helper('adminhtml')->__('Sales Report'), Mage::helper('adminhtml')->__('Sales Report'));

        $gridBlock = $this->getLayout()->getBlock('adminhtml_memberchurnreport.grid');
        $filterFormBlock = $this->getLayout()->getBlock('grid.filter.form');
        //print_r($gridBlock);
        //print_r($filterFormBlock);
        //die;
        $this->_initReportAction(array(
            $gridBlock,
            $filterFormBlock
        ));

        $this->renderLayout();
    }

    public function exportCsvAction() {
        $fileName = 'memberchurnreport.csv';
        $content = $this->getLayout()->createBlock('memberchurnreport/adminhtml_memberchurnreport_grid')
                        ->getCsv();
        $this->_sendUploadResponse($fileName, $content);
    }

    public function exportXmlAction() {
        $fileName = 'memberchurnreport.xml';
        $content = $this->getLayout()->createBlock('memberchurnreport/adminhtml_memberchurnreport_grid')
                        ->getXml();
        $this->_sendUploadResponse($fileName, $content);
    }

    protected function _sendUploadResponse($fileName, $content, $contentType='application/octet-stream') {
        $response = $this->getResponse();
        $response->setHeader('HTTP/1.1 200 OK', '');
        $response->setHeader('Pragma', 'public', true);
        $response->setHeader('Cache-Control', 'must-revalidate, post-check=0, pre-check=0', true);
        $response->setHeader('Content-Disposition', 'attachment; filename=' . $fileName);
        $response->setHeader('Last-Modified', date('r'));
        $response->setHeader('Accept-Ranges', 'bytes');
        $response->setHeader('Content-Length', strlen($content));
        $response->setHeader('Content-type', $contentType);
        $response->setBody($content);
        $response->sendResponse();
        die;
    }
}