<?php
 
      class Zalw_Advancemsg_Block_Grid_Renderer_File extends
                 Mage_Adminhtml_Block_Widget_Grid_Column_Renderer_Abstract
      {
  	/**
     	* Renders grid column
     	*
     	* @param   Varien_Object $row
     	* @return  image or not 
     	*/
        public function render(Varien_Object $row)
        {
	  	$grid = Mage::getBlockSingleton('advancemsg/inbox');
		$file = $row->getData('attach');		
          	if($file == '1')
			
			 return "<img src='". Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_MEDIA) ."advancemsg/attachment-image.png' width='16' height='16'/>";
            	else
              		return '';
          
       }
  
	}
