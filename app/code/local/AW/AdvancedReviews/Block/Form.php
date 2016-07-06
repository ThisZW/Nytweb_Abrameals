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


class AW_AdvancedReviews_Block_Form extends Mage_Core_Block_Abstract
{
    private $_helper;

    public function awShowReviewFormEvent()
    {
        $this->_helper = Mage::helper('advancedreviews');
        if ($this->_helper->getExtDisabled()) {
            return;
        }

        //Showing additional blocks section
        if (!$this->_helper->confShowOrdering()) {
            $this->disableOrderingBlock();
        }

        //Showing adding review form section
        if (!$this->_helper->confAllowOnlyLogged() && !$this->_helper->confAllowOnlySold()) {
            $this->disableLoginCaption();
            $this->disableNeedBuyCaption();
        } elseif (!$this->_helper->isUserLogged()) {
            $this->disableReviewForm();
            if (!$this->_helper->confAllowOnlySold()) {
                $this->disableNeedBuyCaption();
            }
        } elseif ($this->_helper->isUserLogged() && !$this->_helper->confAllowOnlySold()) {
            $this->disableLoginCaption();
            $this->disableNeedBuyCaption();
        } elseif ($this->_helper->isUserLogged() && !$this->_helper->isProductSold()) {
            $this->disableReviewForm();
            $this->disableLoginCaption();
        } else {
            $this->disableLoginCaption();
            $this->disableNeedBuyCaption();
        }
    }

    private function disableOrderingBlock()
    {
        $this->disableBlock('advancedreviews_ordering');
    }

    private function disableReviewForm()
    {
        $this->disableBlock('review_form');
    }

    private function disableLoginCaption()
    {
        $this->disableBlock('advancedreviews_login');
    }

    private function disableNeedBuyCaption()
    {
        $this->disableBlock('advancedreviews_needbuy');
    }

    private function disableBlock($disableAlias)
    {
        if ($disableAlias) {
            $this->getParentBlock()->unsetChild($disableAlias);
        }
    }
}