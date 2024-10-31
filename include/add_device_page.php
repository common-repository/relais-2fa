<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if(count($_POST) > 0) {

	if( !is_user_logged_in() && (!isset($_POST['log']) || !isset($_POST['pwd'])) ) {
		?>
		<div  class="error-message" role="alert">
			<?php echo __('Invalid fields.', 'relais2fa'); ?>
		</div>
		<?php

		if( is_user_logged_in() ) {
			rl_activation_form_qr(true);
		} else {
			rl_activation_form_qr();
		}
	} else {
		if(is_user_logged_in()) {
			$usr = get_user_by('email', sanitize_email($_POST['log']));
			if($usr != false) {
				$log = sanitize_email($_POST['log']);
				$pwd = false;
			}
		} else {
			$log = sanitize_email($_POST['log']);
			$pwd = $_POST['pwd'];
		}
	}

	if( !isset($log) && !isset($pwd) ) {
		?>
		<div  class="error-message" role="alert">
			<?php echo __('Invalid fields.', 'relais2fa'); ?>
		</div>
		<?php

		if( is_user_logged_in() ) {
			rl_activation_form_qr(true);
		} else {
			rl_activation_form_qr();
		}
	} else {
		$email = sanitize_email($_POST['log']);
		$password = $_POST['pwd'];
		$uuid = sanitize_text_field($_POST['uuid']);
		$user = get_user_by('email', $email);

		$isEnabledForUser = rl_is_relais_enabled_for_user($user);

		if($pwd != false) {
			$isCombinationValid = wp_check_password($password, $user->user_pass, $user->ID);
		} else {
			$isCombinationValid = true;
		}

		if($isEnabledForUser != false) {
			?>
			<div  class="error-message" role="alert">
				<?php echo __('Relais is already enabled for this user.', 'relais2fa'); ?>
			</div>
			<?php
			if( is_user_logged_in() ) {
				rl_activation_form_qr(true);
			} else {
				rl_activation_form_qr();
			}
		}

		if($isCombinationValid == false) {
			?>
			<div  class="error-message" role="alert">
				<?php echo __('Invalid credentials.', 'relais2fa'); ?>
			</div>
			<?php
			if( is_user_logged_in() ) {
				rl_activation_form_qr(true);
			} else {
				rl_activation_form_qr();
			}
		}

		if($isEnabledForUser == false && $isCombinationValid == true) {
			$qrCodeLink = generateActivationQrCode($email, $uuid);

			if($qrCodeLink != false) { 
			?>
				<div id="qrCodeContainer">
					<h4><?php echo __("Scan this QR Code to finalize the activation.", 'relais2fa'); ?></h4>
					<img src="<?php echo $qrCodeLink; ?>" /><br>
					<!--<a style="font-size: 13px;padding: 0 24px 0;text-decoration: none;color: #6d6d6d;" href="<?php echo wp_login_url(); ?>"><?php echo __('← Return to login page', 'relais2fa') ?></a>-->
				</div>
			<?php
			} else {
				?>
				<div  class="error-message" role="alert">
					<?php echo __('An error has occured: QR Code could not be generated.', 'relais2fa'); ?>
				</div>
				<?php
				if( is_user_logged_in() ) {
					rl_activation_form_qr(true);
				} else {
					rl_activation_form_qr();
				}
			}

		}

	}

} else {
	if( is_user_logged_in() ) {
		rl_activation_form_qr(true);
	} else {
		rl_activation_form_qr();
	}
}

?>