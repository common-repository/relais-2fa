<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );

if(isset( $_POST['disable'] )) {
	$action = 'disable';
} elseif(isset( $_POST['enable'] )) {
	$action = 'enable';
} elseif(isset( $_POST['remove'] )) {
	$action = 'remove';
} else {
	$action = '';
}


$error = false;
$messages = array();

if($action == "disable") {

	$res = update_user_meta(sanitize_text_field($_POST['userid']), 'rl_active', 0);

	if($res == false) {
		$error = true;
		$messages[] = __('An error has occured, relais was not disabled for this user.', 'relais2fa');
	} else {
		$messages[] = __('Relais was is now disabled for this user.', 'relais2fa');
	}

}

if($action == "enable") {

	$res = update_user_meta(sanitize_text_field($_POST['userid']), 'rl_active', 1);

	if($res == false) {
		$error = true;
		$messages[] = __('An error has occured, relais was not enabled for this user.', 'relais2fa');
	} else {
		$messages[] = __('Relais was is now enabled for this user.', 'relais2fa');
	}

}


if($action == "remove") {

	$res = delete_user_meta(sanitize_text_field($_POST['userid']), 'rl_active');
	if($res != false) {
		$res = delete_user_meta(sanitize_text_field($_POST['userid']), 'rl_uuid');
	}

	if($res == false) {
		$error = true;
		$messages[] = __('An error has occured, relais was not removed for this user.', 'relais2fa');
	} else {
		$messages[] = __('This user cannot use Relais anymore.', 'relais2fa');
	}

}






$users_raw = rl_get_relais_users();
$users = array();

if($users_raw != false) {
	foreach($users_raw as $user) {
		$user->relais_enabled = get_user_meta($user->ID, 'rl_active', true);
		$user->relais_uuid = get_user_meta($user->ID, 'rl_uuid', true);
		$users[] = $user;
	}
}

?>

<div style="margin-top: 20px;"></div>

<?php if(count($messages) > 0) { ?>
<div class="alert alert-<?php echo ($error == true)? 'error': 'success'; ?>">
	<?php
	foreach ($messages as $message) {
		echo $message . '<br>';
	}
	?>
</div>
<?php } ?>


<table>
	<tr>
		<th><?php echo __('User email', 'relais2fa'); ?></th>
		<th><?php echo __('User\'s Relais UUID', 'relais2fa'); ?></th>
		<th><?php echo __('Relais is active?', 'relais2fa'); ?></th>
		<th><?php echo __('Actions', 'relais2fa'); ?></th>
	</tr>
	<?php
	foreach ($users as $user) {
		?>
		<tr class="">
			<td><?php echo $user->user_email; ?></td>
			<td><?php echo $user->relais_uuid; ?></td>
			<td><?php echo ($user->relais_enabled)? __('Yes', 'relais2fa') : __('No', 'relais2fa') ; ?></td>
			<td>
				<form method="post" class="action_form"><input type="hidden" name="userid" value="<?php echo $user->id; ?>">
					<?php if($user->relais_enabled) { ?><input type="submit" name="disable" value="<?php echo __('Disable Relais', 'relais2fa'); ?>"/><?php } ?>
					<?php if(!$user->relais_enabled) { ?><input type="submit" name="enable" value="<?php echo __('Enable Relais', 'relais2fa'); ?>"/><?php } ?>
					<input onclick="" type="submit" name="remove" value="<?php echo __('Remove Relais', 'relais2fa'); ?>"/>
				</form>
			</td>
		</tr>
		<?php
	}
	if(count($users) == 0) { ?>
		<tr><td colspan="4"><?php echo __('No active user for now.', 'relais2fa'); ?></td></tr>
	<?php } ?>
</table>



<style type="text/css">

	.alert {
		padding: 10px;
    	border-radius: 5px;
    	margin-bottom: 20px;
	}

	.alert-error {
		color: #a94442;
    	background-color: #f2dede;
    	border-color: #ebccd1;
	}

	.alert-success {
		color: #3c763d;
		background-color: #dff0d8;
		border-color: #d6e9c6;
	}

	table {
		font-family: arial, sans-serif;
		border-collapse: collapse;
		width: 100%;
	}
	
	td, th {
		border: 1px solid #dddddd;
		text-align: left;
		padding: 8px;
	}
	
	tr:nth-child(even) {
		background-color: #dddddd;
	}

	tr:hover {
		background-color: #aaa;
	}

</style>