<?php

// initialize plugin
function relais2fa_do_installation_procedure() {

	global $wpdb;

	$charset_collate = $wpdb->get_charset_collate();
	require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );

	/* -- INSTALL DATABASE -- */
	if($wpdb->get_var("SHOW TABLES LIKE 'rl_codes'") == 'rl_codes') {
		//return;
	} else {
		
		$sql = "CREATE TABLE rl_codes (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  code text DEFAULT '' NOT NULL,
		  user_email text DEFAULT '' NOT NULL,
		  date_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  valid_until datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  is_valid boolean DEFAULT true NOT NULL,
		  user_ip text DEFAULT '' NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";
		
		dbDelta( $sql );
	}


	
	if($wpdb->get_var("SHOW TABLES LIKE 'rl_codes'") == 'rl_nonces') {
		//return;
	} else {
	
		$sql = "CREATE TABLE rl_nonces (
		  id mediumint(9) NOT NULL AUTO_INCREMENT,
		  nonce text DEFAULT '' NOT NULL,
		  user_email text DEFAULT '' NOT NULL,
		  date_created datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  valid_until datetime DEFAULT '0000-00-00 00:00:00' NOT NULL,
		  is_valid boolean DEFAULT true NOT NULL,
		  PRIMARY KEY  (id)
		) $charset_collate;";
		
		dbDelta( $sql );
	}

}

// function that shows admin page
function rl_show_be_administration_page() {
	include 'include/admin_page.php';
}

function rl_add_device_page() {
	include 'include/add_device_page.php';
}

function rl_manage_page() {
	include 'include/manage_page.php';
}

function add_relais_login_form() {
	wp_enqueue_script('jquery');
	wp_enqueue_script('heartbeat');
	wp_enqueue_script( 'login_script', plugin_dir_url(__FILE__) . 'js/login_page.js' );
    wp_enqueue_style( 'login_style', plugin_dir_url(__FILE__) . 'css/login_page.css' ); 
    wp_localize_script( 'ajax-script', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
    ?>

    <?php if( isset($_GET['rl_error']) ) { ?>
    	<div id="error_message">
    		<?php 
    		switch ($_GET['rl_error']) {
    			case 'invalid_nonce':
    				echo __('Nonce was invalid.', 'relais2fa');
    				break;

    			case 'nonce_expired':
    				echo __('Authentication attempt expired.', 'relais2fa');
    				break;

    			case 'invalid_infos':
    				echo __('Missing informations.', 'relais2fa');
    				break;

    			case 'invalid_credentials':
    				echo __('Missing informations.', 'relais2fa');
    				break;

    			default:
    				echo __('An error has occured.', 'relais2fa');
    				break;
    		}

    		?>
    	</div>
    <?php } ?>

	<div class="login_way_buttons">
		<div class="login_way_button active" id="button_classic">
			<label><?php echo __('Classic', 'relais2fa'); ?></label>
		</div>
		<div class="login_way_button" id="button_relais">
			<label><?php echo 'Relais'; ?></label>
		</div>
	</div>

	<div id="login_relais">
		<?php rl_login_form(); ?>
	</div>

	<?php
}


function rl_init(){
	//settings menu
	
	add_action( 'admin_menu', 'rl_addMenuPages');

	// add relais to login page
	if( get_option('rl_disableClassicLogin') != true ) {
		add_action("login_message", "add_relais_login_form", 90);
	} else {
		if( !is_plugin_active('relais-2fa-premium-extension/relais-premium-extension.php') ) {
			add_action("login_message", "add_relais_login_form", 90);
		}
	}
}

function rl_addMenuPages() {

	if( is_plugin_active('relais-2fa-premium-extension/relais-premium-extension.php') && get_option('rl_licence_valid') == true ) {
		add_menu_page('Relais 2FA', 'Relais 2FA', 'edit_pages', 'relais_2fa', 'rlp_admin_page', plugin_dir_url(__FILE__) . '/img/icon-20px.jpg');
	} else {
		add_menu_page('Relais 2FA', 'Relais 2FA', 'edit_pages', 'relais_2fa', 'rl_show_be_administration_page', plugin_dir_url(__FILE__) . '/img/icon-20px.jpg');
	}

	add_submenu_page( 'relais_2fa', __('Manage users', 'relais2fa'), __('Manage users', 'relais2fa'), 'activate_plugins', 'relais_2fa_manage_users', 'rl_manage_page');
	add_submenu_page( 'relais_2fa', __('Pair / Unpair device', 'relais2fa'), __('Pair / Unpair device', 'relais2fa'), 'edit_pages', 'relais_2fa_add_device', 'rl_add_device_page');

	if(is_plugin_active('relais-2fa-premium-extension/relais-premium-extension.php')) {
		add_submenu_page( 'relais_2fa', __('Licence', 'relais2fa'), __('Licence', 'relais2fa'), 'activate_plugins', 'relais_2fa_licence_page', 'rlp_licence_page');
	}

	if(function_exists('rlp_addMenuPages') && get_option('rl_licence_valid') == true) {
		rlp_addMenuPages();
	}

}


// unused
function rl_customLoginPage() { ?>

	<?php
	$login_header_url = __( 'https://wordpress.org/' );
	$login_header_url = apply_filters( 'login_headerurl', $login_header_url );
	$login_header_text = empty( $login_header_title ) ? __( 'Powered by WordPress' ) : $login_header_title;
	$login_header_text = apply_filters( 'login_headertext', $login_header_text );
	?>

	<?php wp_enqueue_script('jquery'); ?>
	<?php wp_enqueue_script( 'login_script', plugin_dir_url(__FILE__) . 'js/login_page.js' ); ?>
    <?php wp_enqueue_style( 'login_style', plugin_dir_url(__FILE__) . 'css/login_page.css' ); ?>

    <script>
    	var rl_ajax_url = '<?php echo rl_external_ajax_url(); ?>'
		var errorMessage = '<?php __('An error has occured, QRCode could not be generated', 'relais2fa'); ?>';
	</script>

	<div id="rl_login">
		<h1><a href="<?php echo esc_url( $login_header_url ); ?>"><?php echo $login_header_text; ?></a></h1>

		<div class="login_way_buttons">
			<div class="login_way_button active" id="button_classic">
				<label><?php echo __('Classic', 'relais2fa'); ?></label>
			</div>
			<div class="login_way_button" id="button_relais">
				<label><?php echo 'Relais'; ?></label>
			</div>
		</div>

		<div id="login_classic">
			<?php wp_login_form(array('redirect' => home_url(), 'id_username' => 'user','id_password' => 'pass')); ?>
		</div>

		<div id="login_relais">
			<?php rl_login_form(); ?>
		</div>

	</div>
 <?php }

function rl_generate_ajaxurl() {
   echo '<script type="text/javascript">
           var rl_ajaxurl = "' . admin_url('admin-ajax.php') . '";
         </script>';
}

function rl_login_form($interim_login = null) { 
	rl_generate_ajaxurl();
	?>

	<script>
    	var rl_ajax_url = '<?php echo rl_external_ajax_url(); ?>'
		var errorMessage = '<?php __('An error has occured, QRCode could not be generated', 'relais2fa'); ?>';
		var ajax;
		var lcode;
	</script>

	<form name="relais_loginform" id="relais_loginform">
		<div id="messageContainer">
			
		</div>

		<p class="login-username">
			<label for="user"><?php echo __('Email address', 'relais2fa'); ?></label>
			<input type="text" name="email" id="rl_user" class="input" value="" size="20">
		</p>
		<input type="hidden" name="ip" id="rl_ip" value="<?php echo $_SERVER['REMOTE_ADDR']; ?>">
		<button id="rl-submit" class="button button-primary"><?php echo __('Log in', 'relais2fa'); ?></button>

		<div id="qrCodeContainer">
			
		</div>
		
	</form>

	<div id="rl_manual_infos_container">

		<p class="rl_or">- OR -</p>

		<form id="relais_manual_login">

			<div id="manualMessageContainer">
			
			</div>

			<label for="rl_code">Code:</label>
			<input type="text" name="code" id="rl_code">

			<label for="rl_uuid">Unique ID:</label>
			<input type="text" name="code" id="rl_uuid">

			<input type="submit" name="manual_validation">
		</form>

	</div>

	<script>

		function start_verification() {
			(function($) {
				
				$.ajax({
        			url: rl_ajaxurl,
        			type: "POST",
        			data: {
        				'action': 'rl_do_ajax',
        				'rl_action': 'verify',
        				'email': $('#rl_user').val(),
        				'ip': $('#rl_ip').val(),
        				'code': lcode
        			}
        		}).done(function(response) {
        			var response = JSON.parse(response);
			        console.log(response);
			        if(response.success == false) {
			        	setTimeout(start_verification, 500);
			        } else {
			        	var is_interim = $('input[name="interim-login"]').length;

			        	if( is_interim == 0 ) {
			        		redirect(response.data);
			        	} else {
			        		validate_login(response.data);
			        	}
			        }

        		});


			}(jQuery));
		}

		function redirect(nonce) {
			(function($) {

				$.ajax({
        			url: rl_ajaxurl,
        			type: "POST",
        			data: {
        				'action': 'rl_do_ajax',
        				'rl_action': 'authenticate',
        				'email': $('#rl_user').val(),
        				'ip': $('#rl_ip').val(),
        				'nonce': nonce
        			}
        		}).done(function(response) {
        			var response = JSON.parse(response);
			        if(response.success == true) {
						window.location = response.data;
			        } else {
			        	//if(response.data == "confirmcountry") {
			        	//	
			        	//}
			        	//alert(response.message);
			        	var link = "<?php echo site_url() . "/rl-confirm-password"; ?>" + "?nonce=" + response.data + "&email=" + $('#rl_user').val() + "&ip=" + $('#rl_ip').val() + encodeURI("&redirect=<?php echo sanitize_text_field($_REQUEST['redirect_to']); ?>");
			        	//console.log(link);
			        	window.location = link;
			        }

        		});


			}(jQuery));

		}

		function validate_login(nonce) {

			(function($) {
				var wrap = $('#wp-auth-check-wrap');

				$.ajax({
        			url: rl_ajaxurl,
        			type: "POST",
        			data: {
        				'action': 'rl_do_ajax',
        				'rl_action': 'authenticate',
        				'email': $('#rl_user').val(),
        				'ip': $('#rl_ip').val(),
        				'nonce': nonce
        			}
        		}).done(function(response) {
        			var response = JSON.parse(response);
			        if(response.success == true) {
			        	alert('Success. You can close the modal now.');
			        } else {
			        	alert(response.message);
			        }

        		});


			}(jQuery));


		}


	</script>


<?php }


function rl_allow_programmatic_login( $user, $username, $password ) {
	return get_user_by( 'login', $username );
}


function rl_activation_form_qr($bypass = false /* unused */) {

	$user = wp_get_current_user();

	if(rl_is_relais_enabled_for_user($user)) {

		$email = $user->data->user_email;
		$nonce = substr(md5(uniqid(mt_rand(), true)) , 0, 12);
		rl_registerNonce($nonce, $email);

		?>
		<h3><?php echo __('Relais is enabled.', 'relais2fa'); ?></h3><br>
		


		<?php

		$qrCodeLink = rl_generate_deactivation_qrCode($email, $nonce);
	
		if($qrCodeLink != false) {
			?>
			<h4><?php echo __("Scan this QR Code to unpair your device.", 'relais2fa'); ?></h4>
			<img src="<?php echo $qrCodeLink; ?>" />
			<?php
		}

		?>

		
		<script>
			function disable_relais_for_user() {
				//document.getElementById('rl_spinner').style.display = 'inline-block';
				var xhr = new XMLHttpRequest();
				xhr.onreadystatechange = function() {
					//console.log(document.getElementById('rl_spinner').style.display);
				    if (xhr.readyState === 4){
				        //document.getElementById('result').innerHTML = xhr.responseText;
				        var response = JSON.parse(xhr.responseText);
				        console.log(response);
				        if(response.success == true) {
				        	document.location.reload(true);
				        } else {
				        	//document.getElementById('rl_spinner').style.display = 'none';
				        	alert('<?php echo __('An error has occured:','relais2fa'); ?>' + " " + response.message);
				        }
				    }
				};
	
				var email = '<?php echo $email; ?>';
				var nonce = '<?php echo $nonce; ?>';

				//xhr.open('GET', '<?php echo rl_external_ajax_url(); ?>rl_action=disableRelaisForUser&email='+email+"&nonce="+nonce);
				//xhr.send();
			}

			setInterval("disable_relais_for_user", 3000);

		</script>

		<?php

	} else {

		$qrCodeLink = rl_generate_activation_qrCode($user->data->user_email);
	
		if($qrCodeLink != false) {
			?>
			<h4><?php echo __("Scan this QR Code to pair your device.", 'relais2fa'); ?></h4>
			<img src="<?php echo $qrCodeLink; ?>" />
			<?php
		}

	}
}


// unused
function rl_activation_form($bypass = false) {

	//wp_enqueue_style( 'login' );
	//do_action( 'login_enqueue_scripts' );
	//do_action( 'login_head' );

	?>
	<div id="login">
		<form class="login" name="loginform" id="loginform" method="post">
			<?php if($bypass == false) { ?>
			<p>
				<label for="user_login"><?php echo __('Email adress', 'relais2fa') ?></label><br>
				<input type="text" name="log" id="user_login" class="input" value="" size="20" autocapitalize="off">
			</p>
	
			<div class="user-pass-wrap">
				<label for="user_pass"><?php echo __('Password', 'relais2fa') ?></label><br>
				<div class="wp-pwd">
					<input type="password" name="pwd" id="user_pass" class="input password-input" value="" size="20">
				</div>
			</div>
			<?php } else { $userinfos = get_currentuserinfo(); ?>

				<input value="<?php echo $userinfos->data->user_email; ?>" type="hidden" name="log" id="user_login" class="input" value="" size="20" autocapitalize="off">

			<?php } ?>

			<p>
				<label for="user_uuid"><?php echo __('Unique ID', 'relais2fa') ?></label><br>
				<input type="text" name="uuid" id="user_uuid" class="input" value="" size="20" autocapitalize="off">
			</p>
			
			<p class="submit">
				<input type="submit" name="wp-submit" id="wp-submit" class="button button-primary button-large" value="<?php echo __('Enable', 'relais2fa'); ?>">
				<input type="hidden" name="bypass" value="<?php echo ($bypass == true) ? 'true' : 'false'; ?>">
			</p>
		</form>
	</div>

	<?php
}

function rl_verifyCode($email, $ip, $code) {

	$check = rl_is_code_valid($code, false);

	if($check[0] == false) {
		rl_result(false, $check[1]);
	}

	$code = $check[1];

	if($code->user_email != $email || $code->user_ip != $ip) {
		rl_result(false, "Invalid user.");
	}

	$nonce = substr(md5(uniqid(mt_rand(), true)) , 0, 12);

	rl_registerNonce($nonce, $email);

	rl_result(true, "Code check passed", $nonce);

	//return rl_is_code_valid($code);
	
}

function rl_registerNonce($nonce, $email="") {

	global $wpdb;

	$startTime = date("Y-m-d H:i:s");
	$endTime = date('Y-m-d H:i:s',strtotime('+3 minutes',strtotime($startTime)));
	
	$query = "INSERT INTO rl_nonces (nonce, user_email, date_created, is_valid, valid_until) VALUES ('$nonce', '$email', '$startTime', true, '$endTime')";

	return $wpdb->query($query);

}

function rl_ip_from_code($code) {
	global $wpdb;

	$q = "SELECT user_ip FROM rl_codes WHERE code = '$code'";
	$res = $wpdb->get_results($q);

	return $res[0]->user_ip;
}

function rl_attemptConnexion($uuid, $code, $generateNonce = false) {

	if(function_exists('rlp_log_action')) {
		$logip = rl_ip_from_code($code);
		$logdate = date('Y-m-d h:i:s');
		$logaction = rlp_log_action(__("Login attempt", 'relais2fa'), $logdate, array( "Ip" => $logip, __("City", 'relais2fa') => rlp_get_ip_city($logip) ));
	}

	$check = rl_is_code_valid($code);

	if($check[0] == false) {
		rl_result(false, $check[1]);
	}

	$code = $check[1];

	$user = get_user_by('email', $code->user_email);
	$user_uuid = get_user_meta($user->ID, ('rl_uuid'));

	if(count($user_uuid) == 0) {
		rl_result(false, __('Code not found', 'relais2fa'));
	}

	$user_uuid = $user_uuid[0];

	if($user_uuid == false) {
		rl_result(false, __('Invaldi User ID', 'relais2fa'));
	}

	if($user_uuid != $uuid) {
		rl_result(false, __('IDs not corresponding', 'relais2fa'));
	}

	$code_deactivated = rl_use_code($code->code);

	if($generateNonce == true) {
		$nonce = substr(md5(uniqid(mt_rand(), true)) , 0, 12);
		rl_registerNonce($nonce, $email);
	}

	if($code_deactivated == true) {
		if(function_exists('rlp_log_action')) {
			rlp_log_action(__("Login success", 'relais2fa'), $logdate, array( "Ip" => $logip, __("City", 'relais2fa') => rlp_get_ip_city($logip) ));
		}
		rl_result(true, "Authentication success.", $nonce);
	} else {
		rl_result(false, "An error has occured. Code could not be used.");
	}

}

function rl_use_code($code) {
	global $wpdb;

	$query = "UPDATE rl_codes SET is_valid = 0 WHERE code = '$code'";

	$res = $wpdb->query($query);

	if($res > 0) {
		return true;
	} else {
		return false;
	}

}


function rl_use_nonce($nonce) {
	global $wpdb;

	$query = "UPDATE rl_nonces SET is_valid = 0 WHERE nonce = '$nonce'";

	$res = $wpdb->query($query);

	if($res > 0) {
		return true;
	} else {
		return false;
	}

}


function rl_is_code_valid($code, $must_be_valid = true) {
	global $wpdb;

	$query = "SELECT * FROM rl_codes WHERE code = '$code'";

	$res = $wpdb->get_results($query);

	if(count($res) == 0) {
		return array(false, __('Code does not exists', 'relais2fa'));
	}

	$code = $res[0];

	if($must_be_valid == true) {
		if($code->is_valid == 0) {
			return array(false, __("Code is not valid anymore", 'relais2fa'));
		}
	} else {
		if($code->is_valid == 1) {
			return array(false, __("Code is still unused", 'relais2fa'));
		}
	}

	$date = date("Y-m-d H:i:s");
	if($date > $code->valid_until) {
		return array(false, __('Code is expired', 'relais2fa'));
	}
	
	if($code == false) {
		return array(false, __("Code does not exists", 'relais2fa'));
	} else {
		return array(true, $code);
	}

}


function rl_is_nonce_valid($nonce) {
	global $wpdb;

	$query = "SELECT * FROM rl_nonces WHERE nonce = '$nonce'";

	$res = $wpdb->get_results($query);

	if(count($res) == 0) {
		return array(false, __('Nonce does not exists', 'relais2fa'));
	}

	$nonce = $res[0];

	if($nonce->is_valid == 0) {
		return array(false, __("Nonce is not valid anymore", 'relais2fa'));
	}

	$date = date("Y-m-d H:i:s");
	if($date > $nonce->valid_until) {
		return array(false, __('Nonce is expired', 'relais2fa'));
	}
	
	if($nonce == false) {
		return array(false, __("Nonce does not exists", 'relais2fa'));
	} else {
		return array(true, $nonce);
	}

}



function rl_get_relais_users() {
	global $wpdb;

	$q = "SELECT * FROM ".$wpdb->prefix."usermeta WHERE meta_key = 'rl_uuid'";
	$res = $wpdb->get_results($q);

	$users = array();
	foreach ($res as $row) {
		$users[] = get_user_by('id', $row->user_id);
	}

	return $users;

}

function rl_generateQrCode($codeContents) {
	
	require_once "lib/phpqrcode/qrlib.php";

	$tempDir = dirname(__FILE__) . "/output/";
    $code = substr(md5(uniqid(mt_rand(), true)) , 0, 8);
    $codeContents .= "&code=$code";

    $fileName = md5($codeContents).'.png';
    $pngAbsoluteFilePath = $tempDir.$fileName;
    $urlRelativeFilePath = plugin_dir_url(__FILE__) .'/output/'.$fileName;
    
    if (!file_exists($pngAbsoluteFilePath)) {
        QRcode::png($codeContents, $pngAbsoluteFilePath);
        return array("code" => $code, "filePath" => $urlRelativeFilePath);
    } else {
        return false;
    }

}


function rl_generate_activation_qrCode($email) {
	$qrCodeDetails = rl_generateQrCode(rl_external_ajax_url() . "rl_action=activation&email=$email");

	if($qrCodeDetails == false) {
		return false;
	} else {
		return $qrCodeDetails['filePath'];
	}

}

function rl_generate_deactivation_qrCode($email, $nonce) {
	$qrCodeDetails = rl_generateQrCode(rl_external_ajax_url() . "rl_action=disableRelaisForUser&email=$email&nonce=$nonce");

	if($qrCodeDetails == false) {
		return false;
	} else {
		return $qrCodeDetails['filePath'];
	}

}

function rl_registerDevice($email, $uuid) {

	if(function_exists('rlp_log_action')) {
		$logdate = date('Y-m-d h:i:s');
		rlp_log_action(__("Device pairing attempt", 'relais2fa'), $logdate, array( __("For email", 'relais2fa') => $email ));
	}

	$user = get_user_by('email', $email);

	if(rl_is_relais_enabled_for_user($user)) {
		rl_result(false, __("Relais is already set for this user.", 'relais2fa'));
	}

	$res = add_user_meta($user->ID, 'rl_uuid', $uuid, true);
	if($res != false) {
		$res = add_user_meta($user->ID, 'rl_active', true, true);
	}

	if($res != false) {
		if(function_exists('rlp_log_action')) {
			rlp_log_action(__("Device pairing success", 'relais2fa'), $logdate, array( __("For email", 'relais2fa') => $email ));
		}
		rl_result(true, __("Device successfully registered!", 'relais2fa'));
	} else {
		rl_result(false, __("Device could not be registered.", 'relais2fa'));
	}

}


function rl_removeDevice($email, $nonce) {

	if(function_exists('rlp_log_action')) {
		$logdate = date('Y-m-d h:i:s');
		rlp_log_action(__("Device unpairing attempt", 'relais2fa'), $logdate, array( __("For email", 'relais2fa') => $email ));
	}

	$user = get_user_by('email', $email);

	$res = rl_is_nonce_valid($nonce);

	if($res[0] == true) {
		$nonce = $res[1];
	} else {
		rl_result(false, __("An error has occured.", 'relais2fa'));
	}

	if($nonce->user_email != $email) {
		rl_result(false, __("An error has occured.", 'relais2fa'));
	}

	$res = delete_user_meta( $user->ID, 'rl_uuid' );
	$res = delete_user_meta( $user->ID, 'rl_active' );

	rl_use_nonce($nonce->nonce);

	if($res != false) {
		if(function_exists('rlp_log_action')) {
			rlp_log_action(__("Device unpairing success", 'relais2fa'), $logdate, array( __("For email", 'relais2fa') => $email ));
		}
		rl_result(true, __("Device successfully removed!", 'relais2fa'));
	} else {
		rl_result(false, __("Device could not be removed.", 'relais2fa'));
	}
}

function rl_external_ajax_url() {
	return admin_url('admin-ajax.php') . "?action=rl_do_ajax&";
}

function rl_generateLoginQrCode($email, $ip) {

	$qrCodeDetails = rl_generateQrCode(rl_external_ajax_url() . "rl_action=connect");

	if($qrCodeDetails == false) {
		rl_result(false, __("Error: QR Code could not be generated.", 'relais2fa'));
	}

    $res = rl_registerCode($qrCodeDetails['code'], $email, $ip);

    if($res == false) { 
    	rl_result(false, __("Code could not be registered", 'relais2fa'));
    } else {
    	rl_result(true, "", $qrCodeDetails);
    }

}

function rl_is_relais_enabled_for_user($user) {

	$uuid = get_user_meta($user->ID, 'rl_uuid', true);

	if($uuid == false) {
		return false;
	} else {
		return $uuid;
	}

}


function rl_test() {
	
}


function rl_registerCode($code, $email, $ip) {

	global $wpdb;

	$startTime = date("Y-m-d H:i:s");
	$endTime = date('Y-m-d H:i:s',strtotime('+5 minutes',strtotime($startTime)));
	
	$query = "INSERT INTO rl_codes (code, date_created, user_email, user_ip, is_valid, valid_until) VALUES ('$code', '$startTime', '$email', '$ip', true, '$endTime')";

	return $wpdb->query($query);

}


function rl_get_user_from_nonce($nonce) {

	global $wpdb;

	$email = false;

	$q = "SELECT user_email FROM rl_nonces WHERE nonce = '$nonce' ";
	$res = $wpdb->get_results($q);

	if(count($res) > 0) {
		$nonce = $res[0];
		$email = $nonce->user_email;
	}

	return $email;
}

function rl_disable_premium_extension($app_uuid) {
	global $wpdb;

	$q = "SELECT * FROM ".$wpdb->prefix."usermeta WHERE meta_key = 'rl_uuid' && meta_value = '$app_uuid'";
	$res = $wpdb->get_results($q);

	if(!is_plugin_active('relais-2fa-premium-extension/relais-premium-extension.php')) {
		rl_result(false, '[premium_extension_already_disabled]');
	}

	if(count($res) == 0) {
		rl_result(false, '[user_not_authorized_for_action]');
	} else {
		rlp_prepare_deactivation();
		deactivate_plugins('relais-2fa-premium-extension/relais-premium-extension.php');
		rl_result(true, '[premium_extension_has_been_disabled]');
	}
	
}

function rl_result($success, $message="", $data=array()) {
	$res = json_encode( array(
		"success" => $success,
		"message" => $message,
		"data" => $data
	) ); 

	echo $res;
	wp_die();
}