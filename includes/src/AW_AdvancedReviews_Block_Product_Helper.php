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


class AW_AdvancedReviews_Block_Product_Helper extends Mage_Review_Block_Helper
{
    public function getReviewsUrl()
    {
        if (preg_match('/(-review-)(\d)*/', $this->getRequest()->getOriginalPathInfo())) {
            return $this->getRequest()->getServer('HTTP_REFERER');
        }

        $productId = $this->getProduct()->getId();
        if (Mage::getStoreConfig('catalog/seo/product_use_categories')) {
            $categoryId = $this->getProduct()->getCategoryId();
        } else {
            $categoryId = null;
        }

        $shortLink = Mage::helper('advancedreviews')->getFuReviewsUrl($productId, $categoryId);

        if (Mage::getStoreConfigFlag('web/seo/use_rewrites') && $shortLink) {
            return $shortLink;
        } else {
            return Mage::getUrl(
                'review/product/list',
                array(
                     'id'       => $productId,
                     'category' => $categoryId,
                )
            );
        }
    }
}