<?php

defined( 'ABSPATH' ) or die( 'No script kiddies please!' );



?>


<div>

	<fieldset>
	    <legend><?php echo __('Available in premium version', 'relais2fa'); ?></legend>

	    <input type="checkbox" id="secureLogin" name="secureLogin">
	    <label for="secureLogin"><?php echo __('Prevent connexion from unusual countries', 'relais2fa'); ?></label><br/>
	
	    <input type="checkbox" id="disableClassicLogin" name="disableClassicLogin">
	    <label for="disableClassicLogin"><?php echo __('Disable classic login method', 'relais2fa'); ?></label><br/>

	    <input type="checkbox" id="enableLogs" name="enableLogs">
		<label for="enableLogs"><?php echo __('Enable logs', 'relais2fa'); ?></label><br/>
	</fieldset>

</div>

<style>

	fieldset {
		margin-top: 20px;
		border-radius: 5px;
		padding: 10px;
		border-width: 2px;
    	border-style: groove;
    	border-color: threedface;
    	border-image: initial;
	}

	legend {
		border-radius: 5px;
		background-color: #d00;
		color: #fff;
		padding: 3px 6px;
	}

	fieldset label {
		opacity: .5;
	}

</style>