<?php
$ntzLoginUrl = (!empty(esc_url(get_option('ntzcrm_login_url')))) ? esc_url(get_option('ntzcrm_login_url')) : site_url("/crm-memberships-login");
$logo = (!empty(get_option('ntzcrm_logo'))) ? esc_url(get_option('ntzcrm_logo')) : '';
$postPermission = new NtzCrmPostPermission();
$request=$postPermission->ntzcrmRequests();
$ntzLoginRedirectPostSlug=(!empty($_REQUEST['redirecto']))?trim($_REQUEST['redirecto']):"";
if (isset($request['action']) && !empty($request['action']) && $request['action'] == "resetpassword") { ?>
	<?php if ($logo != '') { ?>
		<div class="user-image"><img src="<?php echo $logo; ?>" /></div>
	<?php } ?>
	<div class="signupFrm">
		<h1 style="font-size:55px;"><?php esc_html_e('Reset Password'); ?></h1>
		<p><?php esc_html_e('Please enter your email address. You will receive a link to create a new password via email.') ?></p>
		<form class="form-signin" method="post" id="resetpassword" data-url="<?php echo esc_url(admin_url() . 'admin-ajax.php') ?>" action="<?php echo esc_url(site_url('/wp-login.php?action=lostpassword')); ?>">
			<div style="display:none;" id="errormsgbx"> <div class="alert ntzcrm-alert-danger alert-dismissible"> <strong>Error!</strong> <span id="errormsg"></span> </div>
			</div>
			<input type="hidden" name="redirect_to" value="<?php esc_html_e($ntzLoginUrl); ?>">
			<label for="inputEmail" class="sr-only mt-3"><?php esc_html_e('Email address'); ?></label>
			<input type="text" style="width: 100%" id="inputEmail" name="user_login" class="mt-4 form-control" placeholder="Enter Email address" required autofocus>
			<p class="custom-forget">
				<button id="signbtn" class="mt-4 custom-forget-btn btn btn-lg btn-success btn-block" type="submit"><?php esc_html_e('Get New Password');?></button>
			</p>
			<div style="display:none;" id="successMsgBox">
				<div class="alert ntzcrm-alert-success alert-success ">
					<strong style="color:#28a745;"><?php esc_html_e('Password Reset mail has been sent to your email.') ?></strong> <span id="successMsg" style="color:#28a745;"></span>
				</div>
			</div>
			<p class="text-right mt-4 mb-4" style=" clear: both;"><a style="color:#CF1892 !important;" class="ntzcrm-lost-password-link" href="<?php echo esc_url($ntzLoginUrl); ?>" title="Back to Log In"><?php esc_html_e('Back to Log In'); ?></a></p>
			<div style="display: none;" id="loader" class="spinner-border text-danger" role="status">
				<span class="sr-only"><?php esc_html_e('Loading...'); ?></span>
			</div>
			<?php wp_nonce_field('ntzcrm-login-post'); ?>
		</form>
	</div>
	<form style="display: none;" method="post" id="lostpass" data-url="<?php echo esc_url(admin_url() . 'admin-ajax.php'); ?>" action="<?php echo esc_url(site_url('/wp-login.php?action=lostpassword')); ?>">
		<input type="hidden" id="user_login" name="user_login" value="" required autofocus>
		<input type="hidden" name="redirect_to" value="<?php echo esc_attr($ntzLoginUrl); ?>">
		<?php wp_nonce_field('ntzcrm-lostpass-post'); ?>
	</form> 
<?php } else if (isset($request['action']) && !empty($request['action']) && $request['action'] == "changepassword") { ?>

	<?php if ($logo != '') { ?>
		<div class="user-image"><img src="<?php echo $logo?>" /></div>
	<?php } ?>
	<div class="signupFrm">
		<h1 style="font-size:55px;">
		<?php 
			if(!empty($_GET['pg']) && $_GET['pg']=='create'){
				$pageLabel= "Create Password";
			}else if(!empty($_GET['pg']) && $_GET['pg']=='reset'){
				$pageLabel= "Reset Password";
			}else{
				$pageLabel= "Change Password";
			}

			esc_html_e($pageLabel);
			$userLogin=(!empty($_GET['login']))?$_GET['login']:'';
		?>
		</h1>
		<p><?php esc_html_e('Please enter your new password to change password.'); ?></p>
		<form class="form-signin" method="post" id="changepassword" data-url="<?php echo esc_url(admin_url() . 'admin-ajax.php'); ?>" action="<?php echo esc_url($ntzLoginUrl); ?>?action=changepassword">
			<div style="display:none;" id="errormsgbx">
				<div class="alert ntzcrm-alert-danger alert-dismissible"> 
					<strong>Error!</strong> <span id="errormsg"></span>
				</div>
			</div>
			<input type="hidden" name="redirect_to" value="<?php echo esc_attr($ntzLoginUrl); ?>">
			<input type="hidden" name="uemail"  id="uemail" value="<?php echo esc_attr($userLogin); ?>">

			<label for="inputPassword" class="sr-only mt-2"><?php esc_html_e("Password"); ?></label>
			<input type="password" name="password" id="inputPassword" class="form-control" placeholder="Password" maxlength="20" required>

			<label for="inputConfirmPassword" class="sr-only mt-2"><?php esc_html_e("Confirm Password"); ?></label>
			<input type="password" name="confirmpassword" id="inputConfirmPassword" class="form-control" placeholder="Confirm Password" maxlength="20" required>

			<p class="custom-forget">
				<button id="changepasswordbtn" class="mt-4 btn btn-lg btn-success btn-block" style="color: #fff;background: #007cba;border-color: #007cba;" type="submit"><?php esc_html_e($pageLabel) ?></button>
			</p>
		 

			<?php wp_nonce_field('ntzcrm-changepass-post'); ?>
		</form>
	</div> 

<?php }  else { ?>

	<?php if ($logo != '') { ?>
		<div class="user-image"><img src="<?php echo $logo?>" /></div>
	<?php } ?>
	<div class="signupFrm">
		<h1><?php esc_html_e('Login'); ?></h1>
		<form class="form-signin" id="login" data-url="<?php echo esc_url(admin_url() . 'admin-ajax.php'); ?>">
			<div style="display:none;" id="errormsgbx">
				<div class="alert ntzcrm-alert-danger alert-dismissible">
					<strong><?php esc_html_e('Error!'); ?></strong> <span id="errormsg"></span>
				</div>
			</div>

			<?php if(isset($_GET['reset']) && true == $_GET['reset']){?>
			<div>
				<div class="alert ntzcrm-alert-success">
					<strong><?php esc_html_e('Your password successfully changed. Please login with your new password.'); ?></strong> <span></span>
				</div>
			</div>
			<?php }?>
			<input type="hidden" name="redirect_to" id="redirectLoginPostSlug" value="<?php esc_html_e($ntzLoginRedirectPostSlug); ?>">
			<label for="inputEmail" class="sr-only mt-3"><?php esc_html_e("Email address"); ?></label>
			<input type="text" name="user_login" id="inputEmail" class="ntzcrm-mt-2 form-control" placeholder="Enter Email address" required autofocus>
			<label for="inputPassword" class="sr-only mt-2"><?php esc_html_e("Password"); ?></label>
			<input type="password" id="inputPassword" class="form-control" placeholder="Enter Password" maxlength="20" required>
			<div class="custom-checkbox mb-4">
				<input type="checkbox" value="remember-me">
				<label><?php esc_html_e('Remember me'); ?></label>
			</div>
			<button id="signbtn" class="mt-4 btn btn-lg btn-success btn-block" style="color: #fff;background: #007cba;border-color: #007cba;" type="submit"><?php esc_html_e("Login!") ?></button>
			<p class="text-right mt-4 mb-4"><a class="ntzcrm-lost-password-link" href="<?php echo esc_url($ntzLoginUrl); ?>?action=resetpassword" title="Lost Password"> <?php esc_html_e("Lost your password?") ?></a></p>
			<div style="display: none;" id="loader" class="spinner-border text-danger" role="status">
				<span class="sr-only">Loading...</span>
			</div>
			<?php wp_nonce_field('ntzcrm-loginajx-post'); ?>
		</form>
	</div>

<?php } ?>