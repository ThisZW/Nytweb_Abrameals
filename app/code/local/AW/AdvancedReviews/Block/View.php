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


class AW_AdvancedReviews_Block_View extends Mage_Review_Block_View
{
    /**
     * Class constructor
     */
    public function __construct()
    {
        parent::__construct();
        $this->_storeBackUrl();
    }

    /**
     * Retrives review_id if it's not setted up
     *
     * @return int
     */
    public function getReviewId()
    {
        if (!$this->getData('review_id')) {
            $this->setData('review_id', $this->getReviewData()->getReviewId());
        }
        return $this->getData('review_id');
    }

    /**
     * Retrives back url
     *
     * @return string
     */
    public function getBackUrl()
    {
        if (Mage::getStoreConfigFlag('web/seo/use_rewrites')) {
            return Mage::helper('advancedreviews')->getFuReviewsUrl($this->getProductData()->getId());
        } else {
            return Mage::helper('advancedreviews')->getReviewsBackUrl();
        }
    }

    /**
     * Store back url in session
     */
    protected function _storeBackUrl()
    {
        $url = Mage::helper('advancedreviews')->getReviewsBackUrl();
        $id = $this->getRequest()->getParam('id');
        if ($pack = Mage::helper('advancedreviews')->getViewBackPack()) {
            if ($pack['id'] !== $id) {
                $pack = array('id' => $id, 'url' => $url);
                Mage::helper('advancedreviews')->setViewBackPack($pack);
            }
        } else {
            $pack = array('id' => $id, 'url' => $url);
            Mage::helper('advancedreviews')->setViewBackPack($pack);
        }
    }

    /**
     * Retrives back url from session
     *
     * @return string
     */
    protected function _getBackUrl()
    {
        if ($pack = Mage::helper('advancedreviews')->getViewBackPack()) {
            return $pack['url'];
        }
        return '#';
    }

    /**
     * Set up template before render the view
     *
     * @return Mage_Core_Block_Abstract
     */
    public function renderView()
    {
        $this->setTemplate('advancedreviews/review/view.phtml');
        return parent::renderView();
    }

    /**
     * Disable native view interface
     */
    public function disableNativeView()
    {
        if (!Mage::helper('advancedreviews')->getExtDisabled()) {
            $this->getParentBlock()->unsetChild('review_view');
        }
    }
}