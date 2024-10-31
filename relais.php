<?php

/**

 * Plugin Name: Relais 2FA

 * Plugin URI: https://mobisoft.fr

 * Description: This plugin allows you to use 2FA (Two Facto Authantication) to login.

 * Version: 1.0

 * Author: Mobisoft

 * Author URI: https://mobisoft.fr

 */


defined( 'ABSPATH' ) or die( 'No script kiddies please!' );
require_once(plugin_dir_path(__FILE__) . 'relais-functions.php');

define('RELAIS2FA_PLUGIN_PATH', plugin_dir_url(__FILE__));



register_activation_hook( __FILE__, 'relais2fa_do_installation_procedure' );

add_action('init','rl_init');


//add_action('wp_head', 'rl_generate_ajaxurl');



add_action( 'wp_ajax_rl_do_ajax', 'rl_do_ajax' );
add_action( 'wp_ajax_nopriv_rl_do_ajax', 'rl_do_ajax' );

function rl_do_ajax() {
	if( count($_POST) > 0 ) {
		extract($_POST);
	}
	if( count($_GET) > 0 ) {
		extract($_GET);
	}
	
	switch ($rl_action) {
		
		//ok
		case 'getLoginQrCode':
			if( !isset($email) || !isset($ip) ) 
				rl_result(false, __('Invalid field.', 'relais2fa'));
	
			$email = sanitize_email($email);
			$ip = sanitize_text_field($ip);
	
			$user = get_user_by('email', $email);
			
			$is_active = get_user_meta($user->ID, 'rl_active', true);

			if( !rl_is_relais_enabled_for_user($user) || $is_active == false ) {
				rl_result(false,  __('Relais is not enabled for this user.', 'relais2fa'));
			} else {
				rl_generateLoginQrCode($email, $ip);
			}
			break;
	
		//ok
		case 'activation':
			if( !isset($app_uuid) || !isset($email) ) {
				rl_result(false, "Invalid informations");
			}
	
			rl_registerDevice(sanitize_email($email), htmlspecialchars($app_uuid));
	
			break;
	
		//ok
		case 'connect':
			if( !isset($uuid) || !isset($code) ) {
				rl_result(false, "Invalid informations");
			}
	
			$uuid = htmlspecialchars($uuid);
			$code = htmlspecialchars($code);

			if( isset($_GET['generateNonce']) ) {
				$generateNonce = true;
			} else {
				$generateNonce = false;
			}
	
			rl_attemptConnexion($uuid, $code, $generateNonce);
			break;
	
		//ok
		case 'disableRelaisForUser':
			if(!isset($email) || !isset($nonce) ) {
				rl_result(false, "Invalid informations");
			}
			rl_removeDevice( sanitize_email($email), htmlspecialchars($nonce) );
			break;
	
		//ok
		case 'authenticate':
			if( !isset($nonce) ) {
				rl_result(false, __("Invalid nonce", 'relais2fa'));
			}

			$nonce = htmlspecialchars($nonce);
			
			$check = rl_is_nonce_valid($nonce);
			
			if($check[0] == false) {
				result(false, __("Nonce is expired", 'relais2fa'));
			}
			
			$nonce = $check[1];
			
			$bypass = false;
			

			if( is_plugin_active('relais-2fa-premium-extension/relais-premium-extension.php') ) {
				if(get_option('rl_secureLogin') == true) {
					$isAllowedCountry = rlp_check_country(sanitize_email($email), htmlspecialchars($ip));
					if(!$isAllowedCountry) {
						$res = false;
						$bypass = true;
						//rlp_confirm_password(htmlspecialchars(sanitize_email($email)), htmlspecialchars($ip));
						rl_result(false, __('You are trying to log in from an unusual country. Please verify your password.', 'relais2fa'), $nonce->nonce);
					}
				}
			}

			$res = rl_use_nonce($nonce->nonce);
			
			if($res == true) {
			
				$user = get_user_by('email', $nonce->user_email );
				
				if ( !is_wp_error( $user ) ) {
				    wp_clear_auth_cookie();
				    wp_set_current_user ( $user->ID );
				    wp_set_auth_cookie  ( $user->ID );
				
				    $redirect_to = user_admin_url();
				    rl_result(true, "", $redirect_to);
				}
			
			} else {
				rl_result(false, "Nonce error");
			}
			break;

		//ok
		case 'disable_premium_extension':

			if( !isset($app_uuid) ) {
				rl_result(false, "[cannot_remove_premium_extension]");
			} else {
				$app_uuid = htmlspecialchars($app_uuid);
			}
	
			rl_disable_premium_extension($app_uuid);
			break;
		
		//ok
		case 'verify':
			if( !isset($email) || !isset($code) || !isset($ip) ) {
				rl_result(false, "Invalid informations");
			}
			rl_verifyCode(sanitize_email($email), htmlspecialchars($ip), htmlspecialchars($code));
			break;
	
		//ok
		case 'is_premium_enabled':
			$res = is_plugin_active('relais-2fa-premium-extension/relais-premium-extension.php');
			if($res == true) {
				echo "true";
			} else {
				echo "false";
			}
			break;
	
		//ok
		case 'disable_premium_extension':
	
			if( !isset($app_uuid) ) {
				rl_result(false, "[cannot_remove_premium_extension]");
			}
	
			$app_uuid = htmlspecialchars($app_uuid);

			rl_disable_premium_extension(htmlspecialchars($app_uuid));
			break;
	
		case 'test':
			//rlp_log_action("test_action", date('Y-m-d h:i:s'), array("ip" => rl_ip_from_code('adea0f00')));
			break;
		
		default:
			rl_result(false, "No action");
			break;
	}

	wp_die();
}