<?php
/**
 * @category    Zalw
 * @package     Zalw_Advancemsg
 * @author      Zalw
 * @use	   	Model file of advancemsg_template
 */
class Zalw_Advancemsg_Model_Template extends Mage_Core_Model_Template
{
    /**
     * Template Text Preprocessed flag
     *
     * @var bool
     */
    protected $_preprocessFlag = false;


    protected function _construct()
    {
        $this->_init('advancemsg/template');
    }

	public function getTemplatesOtionArray()
	{
		$collection = $this->getCollection();
	    $collection->addFieldToSelect('template_id')
				   ->addFieldToSelect('template_code');
	    $optionArray = array('' => '- - '.Mage::helper('advancemsg')->__('Please Choose Template').' - - ');
	    if (count($collection)) {
	    	foreach($collection as $_c) {
	   			$optionArray[$_c['template_id']] = $_c['template_code'];
	   		}
	    }
	    return $optionArray;
	}
	public function getTemplateById($templateId = '')
	{
		if($templateId) {
			$collection = $this->getCollection();
			$collection->addFieldToSelect('*')
					   ->addFieldToFilter('template_id',$templateId);
	        if(count($collection)) {
	        	foreach($collection as $c) {
	        		return $c;
	        		break;
	        	}
	        	
	        }
		}
		return false;
	}
	
	
    public function validate()
    {
        $validators = array(
            'template_code'         => array(Zend_Filter_Input::ALLOW_EMPTY => false),
            'template_type'         => 'Int',
            'template_sender_email' => 'EmailAddress',
            'template_sender_name'  => array(Zend_Filter_Input::ALLOW_EMPTY => false)
        );
        $data = array();
        foreach (array_keys($validators) as $validateField) {
            $data[$validateField] = $this->getDataUsingMethod($validateField);
        }

        $validateInput = new Zend_Filter_Input(array(), $validators, $data);
        if (!$validateInput->isValid()) {
            $errorMessages = array();
            foreach ($validateInput->getMessages() as $messages) {
                if (is_array($messages)) {
                    foreach ($messages as $message) {
                        $errorMessages[] = $message;
                    }
                }
                else {
                    $errorMessages[] = $messages;
                }
            }

            Mage::throwException(join("\n", $errorMessages));
        }
    }

    /**
     * Processing object before save data
     *
     * @return Mage_Newsletter_Model_Template
     */
    protected function _beforeSave()
    {
        $this->validate();
        return parent::_beforeSave();
    }

    /**
     * Load template by code
     *
     * @param string $templateCode
     * @return Mage_Newsletter_Model_Template
     */
    public function loadByCode($templateCode)
    {
        $this->_getResource()->loadByCode($this, $templateCode);
        return $this;
    }

    /**
     * Getter for template type
     *
     * @return int|string
     */
    public function getType(){
        return $this->getTemplateType();
    }

    /**
     * Check is Preprocessed
     *
     * @return bool
     */
    public function isPreprocessed()
    {
        return strlen($this->getTemplateTextPreprocessed()) > 0;
    }

    /**
     * Check Template Text Preprocessed
     *
     * @return bool
     */
    public function getTemplateTextPreprocessed()
    {
        if ($this->_preprocessFlag) {
            $this->setTemplateTextPreprocessed($this->getProcessedTemplate());
        }

        return $this->getData('template_text_preprocessed');
    }

    /**
     * Retrieve processed template
     *
     * @param array $variables
     * @param bool $usePreprocess
     * @return string
     */
    public function getProcessedTemplate(array $variables = array(), $usePreprocess = false)
    {
        /* @var $processor Mage_Newsletter_Model_Template_Filter */
        $processor = Mage::helper('advancemsg')->getTemplateProcessor();

        if (!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        if (Mage::app()->isSingleStoreMode()) {
            $processor->setStoreId(Mage::app()->getStore());
        } else {
            $processor->setStoreId(Mage::app()->getRequest()->getParam('store_id'));
        } 

        $processor
            ->setIncludeProcessor(array($this, 'getInclude'))
            ->setVariables($variables);

        if ($usePreprocess && $this->isPreprocessed()) {
            return $processor->filter($this->getPreparedTemplateText(true));
        }

        return $processor->filter($this->getPreparedTemplateText());
    }

    /**
     * Makes additional text preparations for HTML templates
     *
     * @param bool $usePreprocess Use Preprocessed text or original text
     * @return string
     */
    public function getPreparedTemplateText($usePreprocess = false)
    {
        $text = $usePreprocess ? $this->getTemplateTextPreprocessed() : $this->getTemplateText();

        if ($this->_preprocessFlag || $this->isPlain() || !$this->getTemplateStyles()) {
            return $text;
        }
        // wrap styles into style tag
        $html = "<style type=\"text/css\">\n%s\n</style>\n%s";
        return sprintf($html, $this->getTemplateStyles(), $text);
    }

    /**
     * Retrieve included template
     *
     * @param string $templateCode
     * @param array $variables
     * @return string
     */
    public function getInclude($templateCode, array $variables)
    {
        return Mage::getModel('advancemsg/template')
            ->loadByCode($templateCode)
            ->getProcessedTemplate($variables);
    }


    /**
     * Prepare Process (with save)
     *
     * @return Mage_Newsletter_Model_Template
     * @deprecated since 1.4.0.1
     */
    public function preprocess()
    {
        $this->_preprocessFlag = true;
        $this->save();
        $this->_preprocessFlag = false;
        return $this;
    }

    /**
     * Retrieve processed template subject
     *
     * @param array $variables
     * @return string
     */
    public function getProcessedTemplateSubject(array $variables)
    {
        $processor = new Varien_Filter_Template();

        if (!$this->_preprocessFlag) {
            $variables['this'] = $this;
        }

        $processor->setVariables($variables);
        return $processor->filter($this->getTemplateSubject());
    }

    /**
     * Retrieve template text wrapper
     *
     * @return string
     */
    public function getTemplateText()
    {
        if (!$this->getData('template_text') && !$this->getId()) {
            $this->setData('template_text',
                Mage::helper('advancemsg')->__('')
            );
        }

        return $this->getData('template_text');
    }
}
