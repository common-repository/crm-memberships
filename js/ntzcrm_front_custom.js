function chmShowLoadingIndicator() { 
	let html = '<div class="ntzcrm-spinner-container"><div class="loader-main"><div class="cssload-loader"><div class="cssload-inner cssload-one"></div><div class="cssload-inner cssload-two"></div><div class="cssload-inner cssload-three"></div></div><p>Please wait ...</p></div></div>'; 
	 jQuery('body.page-template-default ').append(html);
   }
   
   function chmhideLoadingIndicator() {
	 jQuery('.ntzcrm-spinner-container').remove();
   } 

   
jQuery(document).ready(function ($) {

	$(".logout-link a").attr("href", logouturl);

	$("#login").submit(function (event) {
		
		$("#signbtn").attr("disabled", "disabled");
		ntzcrmAjaxUrl = $(this).data('url'); 
		$email = $('#inputEmail').val();
		$password = $('#inputPassword').val();
		$redirectTo = $('#redirectLoginPostSlug').val();
		event.preventDefault();
		var data = {
			'action': 'ntzcrm_login',
			'user_login': $email,
			'password': $password,
			'redirect': $redirectTo,
		};
		chmShowLoadingIndicator();
		jQuery.post(ntzcrmAjaxUrl, data, function (res) {
			chmhideLoadingIndicator();
			$("#signbtn").removeAttr("disabled");
			var obj = $.parseJSON(res); 
			// console.log(obj); return false;
			if (obj.status == "success") {
				window.location = obj.message;
			} else {
				$("#errormsg").text(obj.message);
				$("#errormsgbx").show();
			}
		});
	});

	$("#changepassword").submit(function (event) {
		$("#changepasswordbtn").attr("disabled", "disabled");
		ntzcrmAjaxUrl = $(this).data('url');
		var $password = $('#inputPassword').val();
		var $confirmpassword = $('#inputConfirmPassword').val();
		var $uemail = $('#uemail').val();
		event.preventDefault();
		var data = {
			'action': 'ntzcrm_changepassword',
			'password': $password,
			'confirmpassword': $confirmpassword,
			'login': $uemail
		};
		// console.log(ntzcrmAjaxUrl);
		// console.log(data);
		// return false;
		chmShowLoadingIndicator();
		jQuery.post(ntzcrmAjaxUrl, data, function (res) {
			$("#changepasswordbtn").removeAttr("disabled");
			// console.log(res); return false;
			var obj = $.parseJSON(res);
			// console.log(obj); return false;
			if (obj.status == "success") {
				window.location = obj.message;
			} else {
				$("#errormsg").text(obj.message);
				$("#errormsgbx").show();
			}
			chmhideLoadingIndicator();
		});
	});

	$("#resetpassword").submit(function (event) {
		event.preventDefault();
		
		$("#signbtn").attr("disabled", "disabled");
		ntzcrmAjaxUrl = $(this).data('url');
		$email = $('#inputEmail').val();
		$password = $('#inputPassword').val();

		var data = {
			'action': 'ntzcrm_resetpassword',
			'email': $email,
		};
		chmShowLoadingIndicator();
		jQuery.post(ntzcrmAjaxUrl, data, function (res) {
			$("#loader").hide();
			$("#signbtn").removeAttr("disabled");
			var obj = $.parseJSON(res);
			chmhideLoadingIndicator();
			if (obj.status == "success") {
				$("#user_login").val($email);
				// $("#lostpass").submit();
				// window.location = obj.message;
				// $("#successMsgBox").text('Please check your mail to reset password. ').append($('<a>').text('Click here').attr('href', obj.message)).append(' to login').css('color', '#CF1892');
				// $("#errormsgbx").text('Please check you mail to reset password. ');
				// $("#successMsgBox").append('').css('color', '#28a745;');
				$("#successMsgBox").show();
			} else {
				$("#errormsg").text(obj.message);
				$("#errormsgbx").show();
			}
		});
	});
});