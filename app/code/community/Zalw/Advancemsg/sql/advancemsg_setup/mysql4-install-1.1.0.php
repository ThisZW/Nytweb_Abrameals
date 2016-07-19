<?php
$installer = $this;
$installer->startSetup();
$installer->run("

DROP TABLE IF EXISTS `{$this->getTable('advancemsg/template')}`;
CREATE TABLE `{$this->getTable('advancemsg/template')}` (
  `template_id` int(10) NOT NULL AUTO_INCREMENT,
  `template_code` varchar(150),
  `template_text` blob, 
  `template_text_preprocessed` varchar(255), 
  `template_styles` blob, 
  `template_type`  int(10),
  `template_subject` varchar(200), 
  `template_sender_name` varchar(200), 
  `template_sender_email` varchar(200), 
  `template_actual` smallint(10)   DEFAULT '1',
  `added_at` timestamp,
  `modified_at` timestamp,	
   PRIMARY KEY (`template_id`)
)  ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


DROP TABLE IF EXISTS `{$this->getTable('advancemsg/content')}`;
CREATE TABLE `{$this->getTable('advancemsg/content')}` (
  `message_id` int(10) NOT NULL AUTO_INCREMENT,
  `template_id` int(10) ,
  `template_name` varchar(255),
  `message_title` varchar(255), 
  `message_link` varchar(255), 
  `message_content` blob,
  `message_text` varchar(255),
  `user_id` int(10) ,
  `status` smallint(10) DEFAULT '0',
  `customer_status` smallint(10) DEFAULT '0', 
  `added_at` timestamp,
  `modified_at` timestamp,	 
  `file_name` varchar(255),
  `attach` tinyint(1) NOT NULL ,
  `parent_id` int(11) DEFAULT '0',
  `user_type` varchar(10),
  `sender_id` int(20) ,
  `sender_type` varchar(10),
  `sent_by_username` varchar(255),
  `receiver_id` int(20) ,
  `receiver_type` varchar(10),
  PRIMARY KEY (`message_id`)
) ENGINE=InnoDB  DEFAULT CHARSET=utf8 ;


");
$installer->endSetup();
