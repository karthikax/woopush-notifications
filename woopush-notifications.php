<?php
/*
 * Plugin Name: WooPush Notifications
 * Plugin URI: http://mangolore.in/woopush-notifications-plugin
 * Description: This plugin sends notification to your devices on woocommerce events such as new order, add to cart, comment etc, using Pushbullet.  
 * Version: 1.0
 * Author: Karthik Bhat
*/

/**
 * Add options for woopush notification in wordpress options table
 *
 * @return void
 */
function wpn_woopush_init(){
	register_setting('wpn_options','wpn_access_token');
	register_setting('wpn_options','wpn_option_new_order');
	register_setting('wpn_options','wpn_option_new_cart');
	register_setting('wpn_options','wpn_option_backorder');
	register_setting('wpn_options','wpn_option_low_stock');
	register_setting('wpn_options','wpn_option_no_stock');
}
add_action('admin_init','wpn_woopush_init');

/**
 * Woopush Notifications setting page
 *
 * @return void
 */
function wpn_option_page(){
	?>
	<div class="wrap">
	<h2>Pushbullet Notifications for Woocommerce</h2>
	<form action="options.php" method="post" id="woopush-options">
		<?php settings_fields( 'wpn_options' ); ?>
		<?php do_settings_sections( 'wpn_options' ); ?>
		<table class="form-table">
			<tr valign="top">
			<th scope="row"><label for="wpn_access_token">Pushbullet Access Token</label></th>
			<td><input type="text" id="wpn_access_token" name="wpn_access_token" value="<?php echo esc_attr( get_option('wpn_access_token') ); ?>" />
			<br /><small>To view your access token visit <a href="https://www.pushbullet.com/account" target="_blank">pushbullet account settings page</a></small></td>
			</tr>

			<tr valign="top">
			<th />
			<td><input id="wpn-test-button" type="button" class="button-primary" value="Send a Test notification" /><br /><br /><div id="wpn-test-result"></div></td>
			</tr>

			<tr valign="top">
			<th colspan="2">Check Events when you want Notifications to be sent:</th>
			</tr>

			<tr valign="top">
			<th scope="row">New Order</th>
			<td><input type="checkbox" name="wpn_option_new_order" value="1" <?php checked( get_option('wpn_option_new_order'), '1', true ); ?>" /></td>
			</tr>

			<tr valign="top">
			<th scope="row">New Cart</th>
			<td><input type="checkbox" name="wpn_option_new_cart" value="1" <?php checked( get_option('wpn_option_new_cart'), '1', true ); ?>" /></td>
			</tr>

			<tr valign="top">
			<th scope="row">Backorder</th>
			<td><input type="checkbox" name="wpn_option_backorder" value="1" <?php checked( get_option('wpn_option_backorder'), '1', true ); ?>" /></td>
			</tr>

			<tr valign="top">
			<th scope="row">Low Stock</th>
			<td><input type="checkbox" name="wpn_option_low_stock" value="1" <?php checked( get_option('wpn_option_low_stock'), '1', true ); ?>" /></td>
			</tr>

			<tr valign="top">
			<th scope="row">No Stock</th>
			<td><input type="checkbox" name="wpn_option_no_stock" value="1" <?php checked( get_option('wpn_option_no_stock'), '1', true ); ?> /></td>
			</tr>
		</table>
		<?php submit_button(); ?>
	</form>
	</div>
	<?php
}

/**
 * Add Woopush Notifications setting page to Wordpress settings menu
 *
 * @return void
 */
function wpn_plugin_menu(){
	add_options_page('WooPush Notifications Settings', 'Woopush Settings','manage_options','wpn-plugin','wpn_option_page');
}
add_action('admin_menu','wpn_plugin_menu');

// Add actions according to the options set
if ( get_option('wpn_option_new_order') ) {
	add_action('woocommerce_thankyou', 'wpn_new_order');
}

if ( get_option('wpn_option_new_cart') ) {
	add_action('woocommerce_add_to_cart', 'wpn_new_cart');
}

if ( get_option('wpn_option_backorder') ) {
	add_action( 'woocommerce_product_on_backorder', 'wpn_backorder' );
}

if ( get_option('wpn_option_low_stock') ) {
	add_action( 'woocommerce_notify_low_stock', 'wpn_low_stock' );
}

if ( get_option('wpn_option_no_stock') ) {
	add_action( 'woocommerce_notify_no_stock', 'wpn_no_stock' );
}

/**
 * Notification options for new order
 * @param  int $order_id wc order id
 * 
 * @return void
 */
function wpn_new_order( $order_id ){
	global $woocommerce;
	$order = new WC_Order( $order_id );
	$title = sprintf( __( 'New Order #%d', 'wpn_pushbullet'), $order_id );
	$body = sprintf(
		__( '%1$s ordered %2$s for %3$s %4$s', 'wpn_pushbullet'),
		$order->billing_first_name . " " . $order->billing_last_name,
		implode(', ', wp_list_pluck( $order->get_items(), 'name' ) ),
		$order->order_total,
		"\nAddress: " . $order->billing_address_1 . " " . $order->billing_address_2 . "\n" . $order->billing_city . ", " . $order->billing_state . "\nPhone: " . $order->billing_phone
		);
	$args = array( 'title' => $title , 'message' => $body , $order_id );
	wpn_send_notification( $args );
}

/**
 * Notification options for new cart
 * @param  int $order_id wc order id
 * 
 * @return void
 */
function wpn_new_cart() {
	$title = sprintf( __( 'New cart') );
	$body = sprintf( __( 'Someone added an Item to cart') );
	$args = array( 'title' => $title , 'message' => $body  );
	wpn_send_notification( $args );
}

/**
 * Notification options for Backorder
 * @param  array $args with product data
 * 
 * @return void
 */
function wpn_backorder( $args ) {
	global $woocommerce;

	$product = $args['product'];
	$title = sprintf( __( 'Product Backorder', 'wpn_pushbullet'), $order_id );
	$body = sprintf( __( 'Product (#%d %s) is on backorder.', 'wpn_pushbullet'), $product->id, $product->get_title() );
	$args = array( 'title' => $title , 'message' => $body );
	wpn_send_notification( $args );
}

/**
 * Notification options for Out of stock
 * @param  obj $product product data
 * 
 * @return void
 */
function wpn_no_stock( $product ) {
	global $woocommerce;

	$title = __( 'Product Out of Stock', 'wpn_pushbullet');
	$body = sprintf( __( 'Product %s %s is now out of stock.', 'wpn_pushbullet'), $product->id, $product->get_title()  );
	$args = array( 'title' => $title , 'message' => $body );
	wpn_send_notification( $args );
}

/**
 * Notification options for Low stock
 * @param  obj $product product data
 * 
 * @return void
 */
function wpn_low_stock( $product ) {
	global $woocommerce;

	$title   = __( 'Product Low Stock', 'wpn_pushbullet');
	$body = sprintf( __( 'Product %s %s now has low stock.', 'wpn_pushbullet'), $product->id, $product->get_title() );
	$args = array( 'title' => $title , 'message' => $body );
	wpn_send_notification( $args );
}

/**
 * Send Notification function
 * @param  array $args message data
 * 
 * @return void
 */
function wpn_send_notification( $args ){
	$token = get_option('wpn_access_token');

	$req_args = array(
		'headers' => array('Authorization' => 'Basic ' . base64_encode( $token .':')),
		'timeout' => 50,
		'sslverify' =>FALSE,
		'method' => 'post',
		'body'=>array('type' => 'note',
		'device_id'=> '', 
		'device_iden'=> '', 
		'title' => $args['title'],
		'body'=>$args['message'],
		'url'=> '')
	);
	$response = wp_remote_post( 'https://api.pushbullet.com/v2/pushes', $req_args );
}

/**
 * Add test script to only our page
 * 
 * @return void
 */
function wpn_test_script(){
	wp_enqueue_script("wpn_test_script", path_join(WP_PLUGIN_URL, basename( dirname( __FILE__ )) . "/scripts/test_ajax.js"), array('jquery'));
}
add_action('admin_print_scripts-settings_page_wpn-plugin','wpn_test_script');
