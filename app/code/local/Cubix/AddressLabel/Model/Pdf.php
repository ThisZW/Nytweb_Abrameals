<?php
/**
 * @author     Harshit Jain <support@cubixws.co.uk>
 * @category   Cubix
 * @package    Cubix_AddressLabel
 * @copyright  Cubix Web Solutions <http://www.cubixws.co.uk>
 */
class Cubix_AddressLabel_Model_Pdf extends Mage_Sales_Model_Order_Pdf_Abstract {

    const XML_FONTSIZE = 'cubix/addresslabel/font_size';
    const XML_PAGEWIDTH = 'cubix/addresslabel/page_width';
    const XML_PAGEHEIGHT = 'cubix/addresslabel/page_height';
    const XML_TOPMARGIN = 'cubix/addresslabel/top_margin';
    const XML_SIDEMARGIN = 'cubix/addresslabel/side_margin';
    const XML_NUMBERDOWN = 'cubix/addresslabel/number_down';
    const XML_NUMBERACROSS = 'cubix/addresslabel/number_across';
    const XML_VERTICALPITCH = 'cubix/addresslabel/vertical_pitch';
    const XML_HORIZONTALPITCH = 'cubix/addresslabel/horizontal_pitch';
    const XML_BOLDNAME = 'cubix/addresslabel/bold_name';
    const XML_STARTFROM = 'cubix/addresslabel/start_from';
    const XML_TOPPADDING = 'cubix/addresslabel/top_padding';
    const XML_LEFTPADDING = 'cubix/addresslabel/left_padding';
    
    protected $_configSettings = array();
    protected $_currLabel;
    protected $_currRow;
    protected $_currColumn;
    protected $x;


    /**
     * Get store config
     * 
     * @param string $config
     * @return string 
     */
    protected function _config($config) {
        if (!isset($this->_configSettings[$config])) {
            $this->_configSettings[$config] = Mage::getStoreConfig($config);
        }
        return $this->_configSettings[$config];
    }
    
    /**
     * Convert to postscript points
     * 
     * @param int $inch
     * @return int 
     */
    protected function _convertInchToPoints($inch) {
        return floor($inch * 72);
    }
    
    protected function _setFontRegular($object, $size = 8)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA); 
        $object->setFont($font, $size);
        return $font;
    }

    protected function _setFontBold($object, $size = 8)
    {
        $font = Zend_Pdf_Font::fontWithName(Zend_Pdf_Font::FONT_HELVETICA_BOLD); 
        $object->setFont($font, $size);
        return $font;
    }
    
    /**
     * Validate all configuration settings
     * 
     * @return mixed
     */
    public function validate() {
        $helper = Mage::helper('cubix_addresslabel');
        if ((float)$this->_config(self::XML_FONTSIZE) < 1) {
            return $helper->__('Invalid setting: Font Size. Go to System > Configuration > Address Labels');
        }
        if ((float)$this->_config(self::XML_PAGEWIDTH) < 1) {
            return $helper->__('Invalid setting: Page Width. Go to System > Configuration > Address Labels');
        }
        if ((float)$this->_config(self::XML_PAGEHEIGHT) < 1) {
            return $helper->__('Invalid setting: Page Height. Go to System > Configuration > Address Labels');
        }
        if ((float)$this->_config(self::XML_TOPMARGIN) < 0) {
            return $helper->__('Invalid setting: Top Margin. Go to System > Configuration > Address Labels');
        }
        if ((float)$this->_config(self::XML_SIDEMARGIN) < 0) {
            return $helper->__('Invalid setting: Side Margin. Go to System > Configuration > Address Labels');
        }
        if ((float)$this->_config(self::XML_LEFTPADDING) < 0) {
            return $helper->__('Invalid setting: Left Padding. Go to System > Configuration > Address Labels');
        }
        if ((float)$this->_config(self::XML_TOPPADDING) < 0) {
            return $helper->__('Invalid setting: Top Padding. Go to System > Configuration > Address Labels');
        }
        if ((int)$this->_config(self::XML_NUMBERDOWN) < 1) {
            return $helper->__('Invalid setting: Number Down. Go to System > Configuration > Address Labels');
        }
        if ((int)$this->_config(self::XML_NUMBERACROSS) < 1) {
            return $helper->__('Invalid setting: Number Across. Go to System > Configuration > Address Labels');
        }
        if ((float)$this->_config(self::XML_VERTICALPITCH) < 1) {
            return $helper->__('Invalid setting: Vertical Pitch. Go to System > Configuration > Address Labels');
        }
        if ((float)$this->_config(self::XML_HORIZONTALPITCH) < 1) {
            return $helper->__('Invalid setting: Horizontal Pitch. Go to System > Configuration > Address Labels');
        }
        $startFrom = trim($this->_config(self::XML_STARTFROM));
        if ($startFrom != "") {
            if (!preg_match('/^\d*,\d*$/', $startFrom)) {
                return $helper->__('Invalid setting: Start printing from label. Use format row,column. Go to System > Configuration > Address Labels');
            }
            list($startRow, $startCol) = explode(',', $startFrom);
            if ($startRow > (int)$this->_config(self::XML_NUMBERDOWN) || $startCol > (int)$this->_config(self::XML_NUMBERACROSS)) {
                return $helper->__('Invalid setting: Start printing from label. Start row and column need to be less or equal to Number Down and Number Across settings respectively. Go to System > Configuration > Address Labels');
            }
        }
        return true;
    }
    
    /**
     * Config font size
     * 
     * @return int
     */
    protected function _getConfigFontSize() {
        return (float)$this->_config(self::XML_FONTSIZE);
    }
    
    /**
     * Move one label row down and reset x position to first column
     * 
     * @return \Cubix_AddressLabel_Model_Pdf 
     */
    protected function _moveRow($keepColPos = false) {
        $this->_currRow++;
        $this->y -= floor($this->_convertInchToPoints((float)$this->_config(self::XML_VERTICALPITCH))) - $this->_getConfigFontSize();
        if (!$keepColPos) {
            $this->x = floor($this->_convertInchToPoints((float)$this->_config(self::XML_SIDEMARGIN)));
        }
        return $this;
    }

    /**
     * Move one label column right
     * 
     * @return \Cubix_AddressLabel_Model_Pdf 
     */
    protected function _moveColumn() {
        $this->_currColumn++;
        $this->x += floor($this->_convertInchToPoints($this->_config(self::XML_HORIZONTALPITCH)));
        return $this;
    }
    
    /**
     * New page
     * 
     * @param array $settings
     * @return Zend_Pdf_Page
     */
    public function newPage(array $settings = array()) {
        $pageWidth = $this->_convertInchToPoints((float)$this->_config(self::XML_PAGEWIDTH));
        $pageHeight = $this->_convertInchToPoints((float)$this->_config(self::XML_PAGEHEIGHT));
        $settings = array('page_size' => $pageWidth.":".$pageHeight.":");
        $page = parent::newPage($settings);
        $this->x = floor($this->_convertInchToPoints((float)$this->_config(self::XML_SIDEMARGIN)));
        $this->y = floor($pageHeight - $this->_convertInchToPoints((float)$this->_config(self::XML_TOPMARGIN))) - $this->_getConfigFontSize();
        $page->setFillColor(new Zend_Pdf_Color_GrayScale(0));
        $this->_setFontRegular($page, $this->_getConfigFontSize());
        $this->_currLabel = 1;
        $this->_currRow = 1;
        $this->_currColumn = 1;
        return $page;
    }
    
    /**
     * Print labels
     * 
     * @param array $orderIds
     * @return Zend_Pdf 
     */
    public function getPdf($orderIds = array()) {
        $fontSize = $this->_getConfigFontSize();
        $this->_beforeGetPdf();
        $pdf = new Zend_Pdf();
        $this->_setPdf($pdf);
        $style = new Zend_Pdf_Style();
        $this->_setFontRegular($style, $fontSize);
        $page = $this->newPage();
        $numberAcross = (int)$this->_config(self::XML_NUMBERACROSS);
        $numberDown = (int)$this->_config(self::XML_NUMBERDOWN);
        $totalInSheet = $numberAcross * $numberDown;
        $startRow = 1;
        $startCol = 1;
        $startFrom = trim($this->_config(self::XML_STARTFROM));
        if ($startFrom != "") {
            list($startRow, $startCol) = explode(',', $startFrom);
        }
        $adjusted = false;
        foreach ($orderIds as $orderId) {
            $order = Mage::getModel('sales/order')->load($orderId);
            if ($order->getIsVirtual()) {
                continue;
            }
            
            /**
             * Keep moving till you reach your start position 
             */
            $adjusted1 = false;
            if (!$adjusted) {
                while ($this->_currRow < $startRow || $this->_currColumn < $startCol) {
                    if ($this->_currRow < $startRow) {
                        $this->_moveRow(true);
                        $this->_currLabel += $numberAcross;
                    }
                    if ($this->_currColumn < $startCol) {
                        $this->_moveColumn();
                        $this->_currLabel += 1;
                    }
                    if ($this->_currLabel > 1000) {
                        Mage::throwException('Error');
                    }
                    $adjusted = $adjusted1 = true;
                }
            }
            
            /*
             * Start new page
             */
            if ($this->_currLabel > $totalInSheet) {
                $page = $this->newPage();
            }
            
            /* If number across done then move row */
            if (!$adjusted1 && $this->_currLabel > 1 && ($this->_currLabel - 1) % $numberAcross == 0) {
                $this->_moveRow();
            }

            /* Draw shiping address */
            $tempY = $this->y;
            $tempX = $this->x;
            $shippingAddress = $this->_formatAddress($order->getShippingAddress()->format('addresslabel'));
            $this->y -= $this->_convertInchToPoints((float)$this->_config(self::XML_TOPPADDING));
            $this->x += $this->_convertInchToPoints((float)$this->_config(self::XML_LEFTPADDING));
            $firstLine = true;
            foreach ($shippingAddress as $value){
                if ($this->_config(self::XML_BOLDNAME)) {
                    if ($firstLine) {
                        $this->_setFontBold($page, $this->_getConfigFontSize());
                        $firstLine = false;
                    }
                    else {
                        $this->_setFontRegular($page, $this->_getConfigFontSize());
                    }
                }
                if ($value!=='') {
                    $page->drawText(strip_tags(ltrim($value)), $this->x, $this->y, 'UTF-8');
                    $this->y -= $fontSize + ($fontSize * 0.2);
                }
            }
            $this->y = $tempY;
            $this->x = $tempX;
            
            /* Move column */
            $this->_moveColumn();

            /* Increase label count */
            $this->_currLabel++;
        }
        $this->_afterGetPdf();
        return $pdf;
    }
}