<?php 

if ( !defined('WP_UNINSTALL_PLUGIN') )
	exit();
delete_option('wpn_access_token');
delete_option('wpn_option_new_order');
delete_option('wpn_option_new_cart');
delete_option('wpn_option_backorder');
delete_option('wpn_option_low_stock');
delete_option('wpn_option_no_stock');