<?php
/**
 * Cryozonic
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Single Domain License
 * that is available through the world-wide-web at this URL:
 * http://cryozonic.com/licenses/stripe.html
 * If you are unable to obtain it through the world-wide-web,
 * please send an email to info@cryozonic.com so we can send
 * you a copy immediately.
 *
 * @category   Cryozonic
 * @package    Cryozonic_StripeSubscriptions
 * @copyright  Copyright (c) Cryozonic Ltd (http://cryozonic.com)
 */

class Cryozonic_StripeSubscriptions_Model_Recurring_Profile extends Mage_Sales_Model_Recurring_Profile
{
    public function isValid()
    {
    	parent::isValid();
    	unset($this->_errors['trial_billing_amount']);
    	unset($this->_errors['trial_period_max_cycles']);
    	return empty($this->_errors);
    }
	
	
	/**
     * Store data into database and wait for cron to suspend/reactivate data using xxxxxxxxxxx
     * 6-8-2016 by Chris
     */
	public function scheduleToFreeze($id, $startDate, $endDate)
	{
		//connect MYSQL
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core/write');
		//$readConnection = $resource->getConnection('core/read');
		
		/*convert dates
		$startDate = strtotime(preg_replace("-","/",$startDate));
		$startDateFormated = date('m-d-Y',$startDate);
		$endDate = strtotime(preg_replace("-","/",$endDate));
		$endFormated = date('m-d-Y',$endDate);*/
		
		$query_freeze = "UPDATE `sales_recurring_profile` SET `freeze_status` = 1, `freeze_start_date` = STR_TO_DATE('".$startDate."', '%m-%d-%Y'), `freeze_end_date` = STR_TO_DATE('".$endDate."', '%m-%d-%Y') WHERE profile_id= " . (int)$id;
		
		//Mage::log($query_freeze,null,'test.log',true);
		$writeConnection->query($query_freeze);
	}
	/**
     * Store data into database and wait for cron to suspend/reactivate data using xxxxxxxxxxx
     * 6-8-2016 by Chris
     */
	public function scheduleToFreezeCancel($id)
	{
		//connect MYSQL
		$resource = Mage::getSingleton('core/resource');
		$writeConnection = $resource->getConnection('core/write');

		$query_freeze_cancel = "UPDATE `sales_recurring_profile` SET `freeze_status` = 0, `freeze_start_date` = 0, `freeze_end_date` = 0 WHERE profile_id= " . (int)$id;
		
		//Mage::log($query_freeze,null,'test.log',true);
		$writeConnection->query($query_freeze_cancel);
	}
	
	/**
     * Check if this subscription has a freeze schedule
     * 6-8-2016 by Chris
     */
	public function hasFreezeSchedule($id){
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core/read');
		$query = 'SELECT freeze_status from `sales_recurring_profile` WHERE profile_id = '. $id ; 
		//Freeze status is a column that define whether this subscription has a plan of freezing or not.
		$freeze_status = $readConnection->fetchOne($query);
		return (int)$freeze_status;
	}
	/**
     * if this subscription has a freeze schedule, output the data
     * 6-8-2016 by Chris
     */
	public function freezeSchedule($id){
		$resource = Mage::getSingleton('core/resource');
		$readConnection = $resource->getConnection('core/read');
		$query_start_date = "SELECT DATE_FORMAT(freeze_start_date, '%m-%d-%Y') from `sales_recurring_profile` WHERE profile_id = ". $id ; 
		$query_end_date = "SELECT DATE_FORMAT(freeze_end_date , '%m-%d-%Y') from `sales_recurring_profile` WHERE profile_id = ". $id ; 
		$freeze_start_date = $readConnection->fetchOne($query_start_date);
		$freeze_end_date = $readConnection->fetchOne($query_end_date);
		return $freeze_start_date . " to " . $freeze_end_date . ".";
	}
	
	public function cancelToRedirect(){
		return Mage::app()->getResponse()->setRedirect(Mage::getBaseUrl().'subscription-plans.html');
		die;
	}
	

}