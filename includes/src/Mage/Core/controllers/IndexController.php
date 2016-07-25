<?php
/**
 * Magento
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the Open Software License (OSL 3.0)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://opensource.org/licenses/osl-3.0.php
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to license@magento.com so we can send you a copy immediately.
 *
 * DISCLAIMER
 *
 * Do not edit or add to this file if you wish to upgrade Magento to newer
 * versions in the future. If you wish to customize Magento for your
 * needs please refer to http://www.magento.com for more information.
 *
 * @category    Mage
 * @package     Mage_Core
 * @copyright  Copyright (c) 2006-2016 X.commerce, Inc. and affiliates (http://www.magento.com)
 * @license    http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
 */


class Mage_Core_IndexController extends Mage_Core_Controller_Front_Action {

    function indexAction()
    {
        //$this->_forward('noRoute');
    }
	
	//tester
	
		/**
	 * This will send emails weekly and dynamically to customers with their last week meals as well as filtered meals by their preference for next week. 
	 * 6-29-2016 by Chris
	 * *Cron Job
	 */
	 
	 public function testAction(){
		 //customer collection object
		 $collection = Mage::getModel('customer/customer')->getCollection();
		 //customer emails
		 echo 'hi';
		 //
		 
	 }
	
	
	
}
