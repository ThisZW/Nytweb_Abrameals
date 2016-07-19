<?php
/**
 * package:   Zalw
 * category:  Zalw_Advancemsg
 */
$installer = $this;
$installer->startSetup();
$installer->run("
ALTER TABLE `{$this->getTable('advancemsg/content')}` MODIFY `message_text` TEXT NOT NULL;
");
$installer->endSetup();