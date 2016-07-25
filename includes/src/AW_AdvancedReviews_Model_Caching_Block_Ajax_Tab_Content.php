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


class AW_AdvancedReviews_Model_Caching_Block_Ajax_Tab_Content extends AW_AdvancedReviews_Model_Caching_Block_Abstract
{
    protected function _getCacheId()
    {
        return 'AW_AdvancedReviews_Block_Ajax_Tab_Content' . $this->_getIdentifier();
    }

    protected function _renderBlock()
    {
        $layout = $this->_getLayout(array('default', 'catalog_product_view'));

        $blockNode = false;
        $infoNodes = array();
        foreach ($layout->getNode()->reference as $node) {
            if ((string)$node['name'] === 'product.info') {
                $infoNodes[] = $node;
            }
        }
        foreach ($infoNodes as $node) {
            if ((string)$node->block['name'] === 'product.info.advancedreviews_product_additional_data_tab') {
                $blockNode = $node;
            }
        }

        $productId = $this->_getProductId();
        Mage::register('product', Mage::getModel('catalog/product')->load($productId), true);
        Mage::register('current_product', Mage::getModel('catalog/product')->load($productId), true);
        Mage::app()->getRequest()->setParam('id', $productId);

        $html = '';
        if ($blockNode) {
            unset($blockNode->block->action);
            $layout->generateBlocks($blockNode);
            $formKey = $layout->createBlock('core/template', 'formkey');
            $formKey->setTemplate('core/formkey.phtml');
            $html = $layout->getBlock('product.info.advancedreviews_product_additional_data_tab')
                ->setChild('formkey', $formKey)
                ->toHtml();
        }
        return $html;
    }
}