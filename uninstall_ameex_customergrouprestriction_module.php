<?php
	require_once 'app/Mage.php';
	umask(0);
	Mage::app('default');
	
	$setup = Mage::getModel('eav/entity_setup',  'core_setup');
	$setup->startSetup();
	$setup->removeAttribute('catalog_product', 'restrict_groups');
	
	$setup->run("
	  DELETE FROM {$setup->getTable('core/resource')}
		WHERE code = 'customergrouprestriction_setup'");
	$setup->endSetup();
	
	echo 'Product Restriction Based On Customer Group has been uninstalled completely';
