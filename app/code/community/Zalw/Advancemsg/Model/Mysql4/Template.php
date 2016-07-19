<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Resource file of advancemsg_template
 */
class Zalw_Advancemsg_Model_Mysql4_Template extends Mage_Core_Model_Resource_Db_Abstract
{
	    protected function _construct()
    {
        $this->_init('advancemsg/template', 'template_id');
    }

    /**
     * Load an object by template code
     *
     * @param Zalw_Advancemsg_Model_Template $object
     * @param string $templateCode
     * @return Zalw_Advancemsg_Model_Resource_Template
     */
    public function loadByCode(Zalw_Advancemsg_Model_Template $object, $templateCode)
    {
        $read = $this->_getReadAdapter();
        if ($read && !is_null($templateCode)) {
            $select = $this->_getLoadSelect('template_code', $templateCode, $object)
                ->where('template_actual = :template_actual');
            $data = $read->fetchRow($select, array('template_actual'=>1));

            if ($data) {
                $object->setData($data);
            }
        }

        $this->_afterLoad($object);

        return $this;
    }

    /**
     * Check usage of template in queue
     *
     * @param Zalw_Advancemsg_Model_Template $template
     * @return boolean
     */
    public function checkUsageInQueue(Zalw_Advancemsg_Model_Template $template)
    {
        if ($template->getTemplateActual() !== 0 && !$template->getIsSystem()) {
            $select = $this->_getReadAdapter()->select()
                ->from($this->getTable('newsletter/queue'), new Zend_Db_Expr('COUNT(queue_id)'))
                ->where('template_id = :template_id');

            $countOfQueue = $this->_getReadAdapter()->fetchOne($select, array('template_id'=>$template->getId()));

            return $countOfQueue > 0;
        } elseif ($template->getIsSystem()) {
            return false;
        } else {
            return true;
        }
    }

    /**
     * Check usage of template code in other templates
     *
     * @param Mage_Newsletter_Model_Template $template
     * @return boolean
     */
    public function checkCodeUsage(Zalw_Advancemsg_Model_Template $template)
    {
        if ($template->getTemplateActual() != 0 || is_null($template->getTemplateActual())) {
            $bind = array(
                'template_id'     => $template->getId(),
                'template_code'   => $template->getTemplateCode(),
                'template_actual' => 1
            );
            $select = $this->_getReadAdapter()->select()
                ->from($this->getMainTable(), new Zend_Db_Expr('COUNT(template_id)'))
                ->where('template_id != :template_id')
                ->where('template_code = :template_code')
                ->where('template_actual = :template_actual');

            $countOfCodes = $this->_getReadAdapter()->fetchOne($select, $bind);

            return $countOfCodes > 0;
        } else {
            return false;
        }
    }

    /**
     * Perform actions before object save
     *
     * @param Mage_Core_Model_Abstract $object
     * @return Mage_Newsletter_Model_Resource_Template
     */
    protected function _beforeSave(Mage_Core_Model_Abstract $object)
    {
        if ($this->checkCodeUsage($object)) {
            Mage::throwException(Mage::helper('advancemsg')->__('Duplicate template code.'));
        }

        if (!$object->hasTemplateActual()) {
            $object->setTemplateActual(1);
        }
        if (!$object->hasAddedAt()) {
            $object->setAddedAt(Mage::getSingleton('core/date')->gmtDate());
        }
        $object->setModifiedAt(Mage::getSingleton('core/date')->gmtDate());

        return parent::_beforeSave($object);
    }
}