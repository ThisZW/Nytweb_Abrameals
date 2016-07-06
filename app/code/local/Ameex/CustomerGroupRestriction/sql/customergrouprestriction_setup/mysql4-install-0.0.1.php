<?php
 
/* @var $installer Mage_Catalog_Model_Resource_Setup */
$installer = $this;
 
$installer->startSetup();
$installer->addAttribute('catalog_product', 'restrict_groups', array(
    'label'                    => 'Hide from customer groups',
    'group'                    => 'General',
    'input'                    => 'multiselect',
    'required'                 => false,
    'source'                   => 'ameex_customergrouprestriction/source_group',
    'backend'                  => 'ameex_customergrouprestriction/backend_group',
    'global'                   => Mage_Catalog_Model_Resource_Eav_Attribute::SCOPE_GLOBAL,
    'used_in_product_listing'  => true,
));
 
$installer->endSetup();
