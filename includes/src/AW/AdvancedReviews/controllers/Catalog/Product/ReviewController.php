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
 * In this controller we override review controller (only admin part) for
 * editind and deleting our abused reviews (still only them!!!)
 */
require_once 'Mage/Adminhtml/controllers/Catalog/Product/ReviewController.php';

class AW_AdvancedReviews_Catalog_Product_ReviewController extends Mage_Adminhtml_Catalog_Product_ReviewController
{
    protected function _needReturnToAbused()
    {
        return ($this->getRequest()->getParam('ret') === 'abuse');
    }

    public function saveAction()
    {
        if ($this->_needReturnToAbused()) {
            $this->_newSaveAction();
        } else {
            parent::saveAction();
        }
    }

    protected function _newSaveAction()
    {
        $reviewId = $this->getRequest()->getParam('id', false);
        if ($data = $this->getRequest()->getPost()) {
            $review = Mage::getModel('review/review')->load($reviewId)->addData($data);
            try {
                $review->setId($reviewId)
                    ->save();

                $arrRatingId = $this->getRequest()->getParam('ratings', array());
                $votes = Mage::getModel('rating/rating_option_vote')
                    ->getResourceCollection()
                    ->setReviewFilter($reviewId)
                    ->addOptionInfo()
                    ->load()
                    ->addRatingOptions();
                foreach ($arrRatingId as $ratingId => $optionId) {
                    if ($vote = $votes->getItemByColumnValue('rating_id', $ratingId)) {
                        Mage::getModel('rating/rating')
                            ->setVoteId($vote->getId())
                            ->setReviewId($review->getId())
                            ->updateOptionVote($optionId);
                    } else {
                        Mage::getModel('rating/rating')
                            ->setRatingId($ratingId)
                            ->setReviewId($review->getId())
                            ->addOptionVote($optionId, $review->getEntityPkValue());
                    }
                }

                $review->aggregate();

                Mage::getSingleton('adminhtml/session')->addSuccess(
                    Mage::helper('catalog')->__('Review was saved successfully')
                );

                //Make redirect to abused reviews
                $this->getResponse()->setRedirect($this->getUrl('adminhtml/awadvancedreviews_abuse/'));
                return;
            } catch (Exception $e) {
                Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
            }
        }
        $this->_redirect('adminhtml/awadvancedreviews_abuse/');
    }

    public function deleteAction()
    {
        if ($this->_needReturnToAbused()) {
            $this->_newDeleteAction();
        } else {
            parent::deleteAction();
        }
    }

    protected function _newDeleteAction()
    {
        $reviewId = $this->getRequest()->getParam('id', false);

        try {
            Mage::getModel('review/review')->setId($reviewId)
                ->aggregate()
                ->delete();

            $this->getResponse()->setRedirect($this->getUrl('adminhtml/awadvancedreviews_abuse/'));
            return;
        } catch (Exception $e) {
            Mage::getSingleton('adminhtml/session')->addError($e->getMessage());
        }

        $this->_redirect('adminhtml/awadvancedreviews_abuse/');
    }
}