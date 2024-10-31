(function($) {
	$(document).ready(function(){
		//$( "#login" ).remove();
		$('#rl_login').hide();
	});

	$('#button_classic').click(function () {
		$('#button_classic').addClass('active');
		$('#button_relais').removeClass('active');
		$('#loginform').show();
		$('#login_relais').hide();
	});

	$('#button_relais').click(function () {
		$('#button_classic').removeClass('active');
		$('#button_relais').addClass('active');
		$('#loginform').hide();
		$('#login_relais').show();
	});



	$( "#relais_loginform" ).submit(function( event ) {
		event.preventDefault();




		$.ajax({
        	url: rl_ajaxurl,
        	type: "POST",
        	data: {
        		'action': 'rl_do_ajax',
        		'rl_action': 'getLoginQrCode',
        		'email': $('#rl_user').val(),
        		'ip': $('#rl_ip').val()
        	}
        }).done(function(response) {
        	var result = JSON.parse(response);
			console.log(result);
			if(result.success == true) {
				console.log(result.data);
				var template = '<img src="'+result.data.filePath+'" alt="QRCode" />';
				$('#qrCodeContainer').append(template);
				//ajax = setInterval(checkStatus,3000);
				start_verification();
				$('#rl-submit').remove();
				lcode = result.data.code;
				$('#rl_manual_infos_container').show();
			} else {

				var template = '<div class="errorMessage" role="alert">'+result.message+'</div>'; //erorrmessage is defined directly in page at top of #rl_login
				$('#messageContainer').append(template);
			}
        });






		//$.ajax({
		//	url: rl_ajax_url // defined in template
		//		+ "?action=getLoginQrCode" 
		//		+ "&email=" + $('#rl_user').val()
		//		+ "&ip=" + $('#rl_ip').val()
		//}).done(function(res) {
		//	var result = JSON.parse(res);
		//	console.log(result);
		//	if(result.success == true) {
		//		console.log(result.data);
		//		var template = '<img src="'+result.data.filePath+'" alt="QRCode" />';
		//		$('#qrCodeContainer').append(template);
		//		ajax = setInterval(checkStatus,3000);
		//		$('#rl-submit').remove();
		//		lcode = result.data.code;
		//		$('#rl_manual_infos_container').show();
		//	} else {
//
		//		var template = '<div class="errorMessage" role="alert">'+result.message+'</div>'; //erorrmessage is defined directly in page at top of #rl_login
		//		$('#messageContainer').append(template);
		//	}
		//});

	});


	$( "#relais_manual_login" ).submit(function( event ) {
		event.preventDefault();

		$.ajax({
			url: rl_ajax_url // defined in template
				+ "?action=connect" 
				+ "&uuid=" + $('#rl_uuid').val()
				+ "&code=" + $('#rl_code').val()
				+ "&generateNonce=true"
		}).done(function(res) {
			var result = JSON.parse(res);
			console.log(result);
			if(result.success == true) {
				redirect(result.data);
			} else {
				var template = '<div class="errorMessage" role="alert">'+result.message+'</div>'; //erorrmessage is defined directly in page at top of #rl_login
				$('#manualMessageContainer').append(template);
			}
		});

	});


}(jQuery));

