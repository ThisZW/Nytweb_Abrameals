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

/**
 * Overrides AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tab_Thread for
 * insert parser.phtml to Thread Block.
 * Parser Block will convert "Review Id #id" to link for pleasure approve
 * reviews.
 */
class AW_AdvancedReviews_Block_Adminhtml_Tickets_Thread
    extends AW_Helpdeskultimate_Block_Adminhtml_Tickets_Edit_Tab_Thread
{
    /**
     * Injects parser HTML into output stream
     *
     * @return String
     */
    protected function _toHtml()
    {
        $html = parent::_toHtml();
        $parser = $this->getLayout()
            ->createBlock('advancedreviews/adminhtml_tickets_thread_parser')
            ->setTemplate('advancedreviews/adminhtml/tickets/threadparser.phtml')
            ->toHtml();
        return $html . $parser;
    }
}