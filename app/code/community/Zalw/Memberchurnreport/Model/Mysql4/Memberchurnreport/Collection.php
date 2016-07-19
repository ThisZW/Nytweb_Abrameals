<?php

class Zalw_Memberchurnreport_Model_Mysql4_Memberchurnreport_Collection extends Mage_Core_Model_Mysql4_Collection_Abstract
{
    protected $_periodFormat;
    protected $_selectedColumns     = array();
    protected $_from                = null;
    protected $_to                  = null;
    protected $_orderStatus         = null;
    protected $_period              = null;
    protected $_storesIds           = 0;
    protected $_applyFilters        = true;
    protected $_isTotals            = false;
    protected $_isSubTotals         = false;
    protected $_aggregatedColumns   = array();
    protected $_marketplaceuser     = 0;
  
    /**
     * Initialize custom resource model
     */

    public function __construct()
    { 
        parent::_construct();

        $this->setModel('adminhtml/report_item');
        $this->_resource = Mage::getResourceModel('memberchurnreport/memberchurnreport')->init('memberchurnreport/order','order_id');
        $this->setConnection($this->getResource()->getReadConnection());

    }

    protected function _getSelectedColumns()
    {  
        $adapter = $this->getConnection();
        $this->_periodFormat = $adapter->getDateFormatSql('main_table.created_at', '%Y-%m-%d');
        $this->_selectedColumns = array(
            'created_at'          => $this->_periodFormat,           
            'item_id'             => 'product_id',
			'qty_ordered'		  => 'IFNULL(main_table.qty_ordered,0)',
			'base_original_price' => 'IFNULL(main_table.base_original_price,0)',
            'base_discount_amount'=> 'IFNULL(main_table.base_discount_amount,0)',
            'base_amount_refunded'=> 'IFNULL(main_table.base_amount_refunded,0)',
			'total_amount'		  => 'IFNULL(((main_table.base_original_price-main_table.base_discount_amount)-main_table.base_amount_refunded)* main_table.qty_ordered,0)'
        );     
        return $this->_selectedColumns;
    }
  
    /**
     * Add selected data
     *
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    protected function _initSelect()
    {  
        $this->getSelect()->from($this->getResource()->getMainTable().' as main_table', $this->_getSelectedColumns());
        if (!$this->isTotals()) {
            $this->getSelect()->group($this->_periodFormat);
        }
        return $this;
    }
   
    /**
     * Set array of columns that should be aggregated
     *
     * @param array $columns
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function setAggregatedColumns(array $columns)
    {
        $this->_aggregatedColumns = $columns;
        return $this;
    }

    /**
     * Retrieve array of columns that should be aggregated
     *
     * @return array
     */
    public function getAggregatedColumns()
    {
        return $this->_aggregatedColumns;
    }

    /**
     * Set date range
     *
     * @param mixed $from
     * @param mixed $to
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function setDateRange($from = null, $to = null)
    {
    	if ('6' == $this->_period) {
            $d=strtotime("-6 Months");
            $this->_from = date("Y-m-d",$d);
        	$this->_to = date("Y-m-d");
        } elseif ('12' == $this->_period) {
            $d=strtotime("-12 Months");
            $this->_from = date("Y-m-d",$d);
        	$this->_to = date("Y-m-d");
        } else {
        	$d=strtotime("-3 Months");
            $this->_from = date("Y-m-d",$d);
        	$this->_to = date("Y-m-d");
        }
        return $this;
    }

    /**
     * Set period
     *
     * @param string $period
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function setPeriod($period)
    {
        $this->_period = $period;
        return $this;
    }

    /**
     * Apply date range filter
     *
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    protected function _applyDateRangeFilter()
    {
		
       if (!is_null($this->_from)) {
          $this->_from = date('Y-m-d G:i:s', strtotime($this->_from));
          $this->getSelect()->where('main_table.created_at >= ?', $this->_from);
        }
        if (!is_null($this->_to)) {
            $this->_to = date('Y-m-d G:i:s', strtotime($this->_to));
        }
        $this->getSelect()->where('main_table.created_at <= ?', $this->_to);

        return $this;
    }

    /**
     * Set store ids
     *
     * @param mixed $storeIds (null, int|string, array, array may contain null)
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function addStoreFilter($storeIds)
    {
        $this->_storesIds = $storeIds;
        return $this;
    }

    /**
     * Apply stores filter to select object
     *
     * @param Zend_Db_Select $select
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    protected function _applyStoresFilterToSelect(Zend_Db_Select $select)
    {
        $nullCheck = false;
        $storeIds = $this->_storesIds;

        if (!is_array($storeIds)) {
            $storeIds = array($storeIds);
        }

        $storeIds = array_unique($storeIds);

        if ($index = array_search(null, $storeIds)) {
            unset($storeIds[$index]);
            $nullCheck = true;
        }

        $storeIds[0] = ($storeIds[0] == '') ? 0 : $storeIds[0];

        if ($nullCheck) {
            $select->where('main_table.store_id IN(?) OR main_table.store_id IS NULL', $storeIds);
        } else {
            $select->where('main_table.store_id IN(?)', $storeIds);
        }

        return $this;
    }

    /**
     * Apply stores filter
     *
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    protected function _applyStoresFilter()
    {
        return $this->_applyStoresFilterToSelect($this->getSelect());
    }

    /**
     * Set status filter
     *
     * @param string|array $state
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function addOrderStatusFilter($orderStatus)
    {
        $this->_orderStatus = $orderStatus;
        return $this;
    }

    /**
     * Apply order status filter
     *
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    protected function _applyOrderStatusFilter()
    {
		
        if (is_null($this->_orderStatus)) {
            return $this;
        }
        $orderStatus = $this->_orderStatus;
        if (!is_array($orderStatus)) {
            $orderStatus = array($orderStatus);
        }
		$this->getSelect()->where('order_status IN(?)', $orderStatus);
        return $this;
    }

    /**
     * Set apply filters flag
     *
     * @param boolean $flag
     * @return TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function setApplyFilters($flag)
    {
        $this->_applyFilters = $flag;
        return $this;
    }

    /**
     * Getter/Setter for isTotals
     *
     * @param null|boolean $flag
     * @return boolean|TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function isTotals($flag = null)
    {
        if (is_null($flag)) {
            return $this->_isTotals;
        }
        $this->_isTotals = $flag;
        return $this;
    }

    /**
     * Getter/Setter for isSubTotals
     *
     * @param null|boolean $flag
     * @return boolean|TM_Report_Model_Mysql4_Report_Order_Collection
     */
    public function isSubTotals($flag = null)
    {
        if (is_null($flag)) {
            return $this->_isSubTotals;
        }
        $this->_isSubTotals = $flag;
        return $this;
    }

    /**
     * Custom filters application ability
     *
     * @return Mage_Reports_Model_Resource_Report_Collection_Abstract
     */
    protected function _applyCustomFilter()
    {
        $this->getSelect()->join(array('p' =>'sales_flat_order'),"p.entity_id = main_table.order_id",array('gender' => 'p.customer_gender','email' => 'p.customer_email',));
        return $this;
    }
   
    /**
     * Load data
     * Redeclare parent load method just for adding method _beforeLoad
     *
     * @return  Varien_Data_Collection_Db
     */
    public function load($printQuery = false, $logQuery = false)
    {  
        if ($this->isLoaded()) {
            return $this;
        }
        $this->_initSelect();
		if ($this->_applyFilters) {
            $this->_applyDateRangeFilter();
	        $this->_applyStoresFilter();
           	$this->_applyCustomFilter();
        }
        return parent::load($printQuery, $logQuery);
    }
}