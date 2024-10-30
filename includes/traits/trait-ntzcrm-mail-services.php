<?php
trait ntzCrmMailServices
{

  public function ntzCrmMailServicesSendMail($bemail, $subject, $message, $contentType = 'text/html')
  {
    $headers = [];
       $headers = 'Content-type: text/html;charset=utf-8';
    return wp_mail($bemail, $subject, $message, $headers);
    /*Note we can setup multiple mail service here to send mail. like postmark,sendgrid...etc*/
  }



  /*
   * @params: action=ntzcrmWelcomeMailTemplate 
   * @Function use: ntzcrmWelcomeMailTemplate: Using to generate welcome mail content.
   * @created by: Hitesh verma
   */

  public  function ntzcrmWelcomeMailTemplate($user, $password = '')
  {

    $defaultLoginUrl = (!empty(get_option('ntzcrm_login_url'))) ? get_option('ntzcrm_login_url') : site_url("/crm-memberships-login");
    $userData = $user->data;
    $user_login = $userData->user_login;
    $userEmail = $userData->user_email;
    $displayName = $userData->display_name;
    $key = get_password_reset_key($user);
    $userName = ucfirst($user_login);

    $loginBtnStyle = 'color:#ffffff;background-color:#3869d4;border-top:10px solid #3869d4;border-right:18px solid #3869d4;border-bottom:10px solid #3869d4;border-left:18px solid #3869d4;display:inline-block;text-decoration:none;border-radius:3px;box-sizing:border-box';
    $loginBTn = '<a style="' . $loginBtnStyle . '" class="button" target="_blank" href="' . $defaultLoginUrl . '">Login</a>';
    
    if (!empty(get_option('ntzcrm_welcome_mail_template'))) {
      $message = str_replace(["#USERNAME#", "#LOGINBTN#", "#PASSWORD#"], [$userName, $loginBTn, $password], get_option('ntzcrm_welcome_mail_template'));
    } else {
      // To Do : Design Email Template
      $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
      $message = sprintf(__('"<p>"Welcome, <strong>%s</strong>!</p>'), $userName);
      $message .= __('<p> Thanks for your subscription with ' . $blogname."</p>");
      $message .= __('<p> We are thrilled to have you on board.</p>');
      // $message .= __('Click here to login') . "<br/><br/>";
      // $message .= __($loginBTn) . "<br/>";
      $message .= __("<p> Thanks,</p> $blogname Team");
    }
    return $message;
  }

  /*
   * @params: action=ntzcrmResetPasswordMailTemplate 
   * @Function use: ntzcrmResetPasswordMailTemplate: This use for generate email to reset password
   * @created by: Hitesh verma
   */

  public  function ntzcrmResetPasswordMailTemplate($user, $isViaCrmPlugin = 0)
  {

    $defaultLoginUrl = (!empty(get_option('ntzcrm_login_url'))) ? get_option('ntzcrm_login_url') : site_url("/crm-memberships-login");
    $userData = $user->data;
    $user_login = $userData->user_login;
    $userEmail = $userData->user_email;
    $displayName = $userData->display_name;
    $key = get_password_reset_key($user);
    $userName = ucfirst($user_login);

    // $resetlink = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');

    $resetlink = $defaultLoginUrl . '?action=changepassword&pg=reset&login=' . rawurlencode($user_login) . '&key=' . $key;
    $loginBtnStyle = 'color:#ffffff;background-color:#3869d4;border-top:10px solid #3869d4;border-right:18px solid #3869d4;border-bottom:10px solid #3869d4;border-left:18px solid #3869d4;display:inline-block;text-decoration:none;border-radius:3px;box-sizing:border-box';
    $loginBTn = '<a style="' . $loginBtnStyle . '" class="button" target="_blank" href="' . $resetlink . '">Reset Password</a>';


    if (!empty(get_option('ntzcrm_resetpassword_mail_template'))) {
      $message = str_replace(["#USERNAME#", "#RESTPASSWORDLINK#", "#CREATEPASSWORDLINK#"], [$userName, $loginBTn,$loginBTn], get_option('ntzcrm_resetpassword_mail_template'));
    } else {
      // To Do : Design Email Template
      $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
      $message = sprintf(__('<p>Hi <strong>%s</strong>,</p>'), $userName);
      $message .= __('<p>You have requested to reset password.</p>');
      $message .= __('<p> To reset your password, visit the following address: </p>');
      $message .= __("<br/><p>".$loginBTn."<p/><br/>");
      $message .= __('<p> If this was a mistake, ignore this email and nothing will happen.</p>');
      $message .= __("<p> Thanks,</p> $blogname Team");
    }
    return $message;
  }

  public  function ntzcrmCreatePasswordMailTemplate($user)
  {

    $defaultLoginUrl = (!empty(get_option('ntzcrm_login_url'))) ? get_option('ntzcrm_login_url') : site_url("/crm-memberships-login");
    $userData = $user->data;
    $user_login = $userData->user_login;
    $userEmail = $userData->user_email;
    $displayName = $userData->display_name;
    $key = get_password_reset_key($user);
    $userName = ucfirst($user_login);
    // $resetlink = network_site_url("wp-login.php?action=rp&key=$key&login=" . rawurlencode($user_login), 'login');
    $resetlink = $defaultLoginUrl . '?action=changepassword&pg=create&login=' . rawurlencode($user_login) . '&key=' . $key;
    $loginBtnStyle = 'color:#ffffff;background-color:#3869d4;border-top:10px solid #3869d4;border-right:18px solid #3869d4;border-bottom:10px solid #3869d4;border-left:18px solid #3869d4;display:inline-block;text-decoration:none;border-radius:3px;box-sizing:border-box';
    $loginBTn = '<a style="' . $loginBtnStyle . '" class="button" target="_blank" href="' . $resetlink . '">Create Password</a>';

    if (!empty(get_option('ntzcrmCreatePasswordMailTemplate'))) {
      $message = str_replace(["#USERNAME#","#CREATEPASSWORDLINK#"], [$displayName, $loginBTn], get_option('ntzcrmCreatePasswordMailTemplate'));
    } else {
      // To Do : Design Email Template
      $blogname = wp_specialchars_decode(get_option('blogname'), ENT_QUOTES);
      $message = sprintf(__('<p> Hi <strong>%s</strong>,<p/>'), $userName);
      $message .= __('<p> Your Account has been created.<p/>');
      $message .= __('<p> Click on the below link to create your password.<p/>');
      $message .= __("<br/><p>".$loginBTn."<p/><br/>");
      $message .= __("<p> Thanks,</p> $blogname Team");
    }
    return $message;
  }
}
