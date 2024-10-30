<?php
class NtzCrmApi extends NtzCrmPostPermission
{
  use ntzCrmMailServices;
  public $postPermission;
  public function __construct()
  {
    parent::__construct();
  }


  public function _ntzCallApi($method)
  {
    add_action("wp_ajax_$method", array($this, "$method"));
    add_action("wp_ajax_nopriv_$method", array($this, "$method"));
  }
  public function ntzcrm_login()
  {
    $status = "failed";
    $request = $this->ntzcrmRequests();
    try {
      if (!empty($request['user_login']) && !empty($request['password'])) {
        $userArr = $creds = array();
        
        $creds['user_login'] = strtolower(trim($request['user_login']));
        $creds['user_password'] = trim($request['password']);
        $redirectTo = (!empty($request['redirect'])) ? trim($request['redirect']) : "";
        $user = wp_signon($creds, false);

        if (!is_wp_error($user) && !empty($user)) {
          $status = "success";
          $svedFirstName = get_user_meta($user->ID, 'first_name', true);
          // echo json_encode($firstName);
          // die;
          if(!empty($svedFirstName)){
            $firstName=get_user_meta($user->ID, 'firstName', true);
            if (!empty($firstName)) { // true
              update_user_meta($user->ID, 'firstName', $firstName);
            } else {
              add_user_meta($user->ID, 'firstName', $firstName);
            }

          }
          $message = (!empty(get_option('ntzcrm_log_redirect_url'))) ? esc_url(get_option('ntzcrm_log_redirect_url')) : esc_url(site_url("/crm-memberships-publications"));
          $message = (!empty($redirectTo)) ? site_url('/' . $redirectTo) : esc_url($message);
        } else {
          if (! filter_var(trim(trim($request['user_login'])), FILTER_VALIDATE_EMAIL)) {
            $message = "Please enter valid email address.";
          }else{
            $message = "Invalid email address OR Password.";
          }
          
        }
      } else {
        $message = "Please enter the email OR password.";
      }
    } catch (Exception $exc) {
      $message = $exc->getMessage();
    }
    $this->createJson($status, $message);
  }

  public function ntzcrm_changepassword()
  {
    $status = "failed";
    $request = $this->ntzcrmRequests();
    // $message = $request;
    try {
      if (!empty($request['password']) && !empty($request['confirmpassword'])) {

        if ($request['password'] == $request['confirmpassword']) {
          $pass = trim($request['password']);
          $user_id = get_current_user_id();

          if(empty($user_id) && !empty($request['login'])){
            $login = trim($request['login']);
            $user = get_user_by('email', $login);
            $user_id = $user->ID;
          }

          if (!is_wp_error($user_id) && !empty($user_id)) {
            $status = "success";
              wp_set_password($pass, $user_id);
              wp_logout();
              wp_set_current_user(0);
            $message = (!empty(get_option('ntzcrm_login_url'))) ? esc_url(get_option('ntzcrm_login_url')) . '/?reset=1' : esc_url(site_url("/crm-memberships-login") . '/?reset=1');
          } else {
            throw new Exception("User not found.");
          }
        } else {
          // $message = "Password & confirm password does not match.";
          throw new Exception("Password & confirm password does not match.");
        }
      } else {
        // $message = "Please enter the password & confirm password.";
        throw new Exception("Please enter the password & confirm password.");
      }
    } catch (Exception $exc) {
      $message = $exc->getMessage();
    }
    $this->createJson($status, $message);
  }

  public function ntzcrm_resetpassword()
  {
    $status = "success";
    $request = $this->ntzcrmRequests();

    try {
      $tagError = $errComma = $tagNames = $comma = "";
      $tagStatus = $userStatus = "failed";
      $tagMessage = "Tag is empty.";
      $userMessage = "";

      if (empty($request['email'])) {
        throw new Exception("Please enter the email.");
      }
      $username = $bemail = trim($request['email']);
      $user = get_user_by('email', $username);
      if (empty($user)) {
        throw new Exception("There is no account with that email address.");
      }

      $subject = sprintf(__('[%s] Rest Password'), $this->blogname);
      $message = $this->ntzcrmResetPasswordMailTemplate($user, 1);
      $isMailSent = $this->ntzCrmMailServicesSendMail($bemail, $subject, $message);
      if ($isMailSent == false) {
        $this->sfNtzCrmMembershipErrorRequestLog("ntzcrm_create_user: The e-mail could not be sent. Possible reason: your host may have disabled the mail() function.");
      }

      $msg = (!empty(get_option('ntzcrm_login_url'))) ? esc_url(get_option('ntzcrm_login_url')) : esc_url(site_url("/crm-memberships-login"));
    } catch (Exception $e) {
      $status = 'failed';
      $msg = $e->getMessage();
      $this->sfNtzCrmMembershipErrorRequestLog($msg, $request);
    }

    $this->createJson($status, $msg);
    exit;

  }

  public function ntzcrm_create_user()
  {
    $status = 'success';
    $request = $this->ntzcrmJsonRequests();
    $user_id = null;
    try {
      $tagError = $errComma = $tagNames = $comma = "";
      $tagStatus = $userStatus = "failed";
      $tagMessage = "Tag is empty.";
      $userMessage = "";
      $api_token = trim($request["api_token"]); //trim($request["genpass"]); //
      $api_key = trim($request["api_key"]);
      $check = $this->ntzcrmCheckToken($api_token, $api_key);
      if (!$check) {
        throw new Exception("Api key or token not valid. Please contact with administrator.");
      }
      if (empty($request['person_email'])) {
        throw new Exception("Please enter the email.");
      }

      if (email_exists($request['person_email']) == false) {

        $username = $bemail = trim($request['person_email']);
        $password = (isset($request['password']) && !empty($request['password'])) ? trim($request['password']) : '';
        if (empty($password)) {
          throw new Exception("Password should not be blank.");
        }
        $user_id = wp_create_user($username, $password, $bemail);
        $user = get_user_by('email', $username);
        $defaultLoginUrl = (!empty(get_option('ntzcrm_login_url'))) ? esc_url(get_option('ntzcrm_login_url')) : esc_url(site_url("/crm-memberships-login"));

        $key = get_password_reset_key($user);
        $defaultLoginUrl = $defaultLoginUrl . '?action=changepassword&createNew=yes&login=' . rawurlencode($username) . '&key=' . $key;

        /* Send Welcome Mail */
        $enableTosendWelcomeMailOnUserCreation = esc_html(get_option('ntzcrmEnableTosendWelcomeMailOnUserCreation'));
        
        if (!empty($enableTosendWelcomeMailOnUserCreation)) {
          $subject = sprintf(__('Welcome to  %s!'), $this->blogname);
          $message = $this->ntzcrmWelcomeMailTemplate($user, $password);
          $isMailSent = $this->ntzCrmMailServicesSendMail($bemail, $subject, $message);
          if ($isMailSent == false) {
            $this->sfNtzCrmMembershipErrorRequestLog("ntzcrm_create_user: The e-mail could not be sent. Possible reason: your host may have disabled the mail() function.");
          }
        }

        /* Sending Reset password mail  */
        $enableTosendResetPasswordMailOnUserCreation = esc_html(get_option('ntzcrmEnableTosendCreatePasswordMailOnUserCreation'));
        if (!empty($enableTosendResetPasswordMailOnUserCreation)) {
          $createPassworedSubject = sprintf(__('[%s] Create Password'), $this->blogname);
          $createPassworedMessage = $this->ntzcrmCreatePasswordMailTemplate($user);
          $isMailSent = $this->ntzCrmMailServicesSendMail($bemail, $createPassworedSubject, $createPassworedMessage);
          if ($isMailSent == false) {
            $this->sfNtzCrmMembershipErrorRequestLog("ntzcrm_create_user: The e-mail could not be sent. Possible reason: your host may have disabled the mail() function.");
          }
        }



        add_user_meta($user_id, 'ntzcrm_user_tag_sync', "yes");
        update_user_meta($user_id, 'first_name', sanitize_text_field(trim($request['first_name'])));
        update_user_meta($user_id, 'last_name', sanitize_text_field(trim($request['last_name'])));
        update_user_meta($user_id, 'ntzcrm_contact_id', sanitize_text_field(trim($request['crm_contact_id'])));
        update_user_meta($user_id, 'phone', sanitize_text_field(trim($request['phone'])));
        $userMessage = $msg = "User created successfully on " . NTZCRM_DOMAIN . ". ";
        if (isset($request['tag_ids']) && !empty($request['tag_ids'])) {
          $tagids = $request['tag_ids'];
          $geTags = ntzcrm_dbquery::_getMembershipTagsList();
          foreach ($tagids as $index => $tid) {
            if (!empty($tid) && !empty($geTags[$tid])) {
              $tagNames .= $comma . $geTags[$tid];
              $comma = ", ";
            } else {
              $tagError .= $errComma . $tid;
              $errComma = ',';
              unset($tagids[$index]);
            }
          }
          ntzcrm_dbquery::_updateUserTag($tagids, $user_id);
          $msg .= (!empty($tagNames)) ? 'Membership(s) successfully applied for ' . $tagNames . "." : '';
          $msg .= (!empty($tagError)) ? "Did not find membership for tag ids : " . $tagError . "." : '';
          $tagStatus = (!empty($tagError)) ? "failed" : "success";
          if (!empty($tagError)) {
            $tagMessage = "Did not find membership for tag ids : " . $tagError . ".";
          } elseif (!empty($tagNames)) {
            $tagMessage = 'Membership(s) successfully applied for ' . $tagNames . ".";
          }
        }

        $userStatus = "success";
      } else {
        if (isset($request["tag_ids"]) && !empty($request["tag_ids"])) {
          $user = get_user_by('email', trim($request['person_email']));
          $userMessage = $errorMsg = "User already exists on " . NTZCRM_DOMAIN . ". ";
          $geTags = ntzcrm_dbquery::_getMembershipTagsList();
          $tagError = $errComma = $tagNames = $comma = "";
          $tagids = $request["tag_ids"];
          foreach ($tagids as $index => $tid) {
            if (!empty($tid) && !empty($geTags[$tid])) {
              $tagNames .= $comma . $geTags[$tid];
              $comma = ", ";
            } else {
              $tagError .= $errComma . $tid;
              $errComma = ',';
              unset($tagids[$index]);
            }
          }
          ntzcrm_dbquery::_updateUserTag($tagids, $user->ID);
          $errorMsg .= (!empty($tagNames)) ? 'Membership(s) successfully applied for ' . $tagNames . "." : '';
          $errorMsg .= (!empty($tagError)) ? "Did not find membership for tag ids : " . $tagError . "." : '';
          $tagStatus = (!empty($tagError)) ? "failed" : "success";
          if (!empty($tagError)) {
            $tagMessage = $errorMsg;
          } elseif (!empty($tagNames)) {
            $tagMessage = 'Membership(s) successfully applied for ' . $tagNames . ".";
          }
          throw new Exception($errorMsg);
        } else {
          $userMessage = "User already exists on " . NTZCRM_DOMAIN . ".";
          throw new Exception("User already exists on " . NTZCRM_DOMAIN . ".");
        }
      }
    } catch (Exception $e) {
      $status = 'failed';
      $msg = $e->getMessage();
      $this->sfNtzCrmMembershipErrorRequestLog($msg, $request);
    }
    $response = array('status' => $status, 'message' => $msg, 'userStatus' => $userStatus, 'userMessage' => $userMessage, 'tagStatus' => $tagStatus, 'tagMessage' => $tagMessage, 'user_id' => $user_id);
    echo json_encode($response);
    exit;
  }

  /*
   * @params: action=ntzcrm_reset_password,api_token,api_key,person_email,password,crm_contact_id
   * @Function use: ntzcrm_reset_password: This api use to reset user password
   * @created by: Hitesh verma
   * @Created: 28-04-2020
   */

  public function ntzcrm_reset_password()
  {
    $status = 'success';
    try {
      $request = $this->ntzcrmJsonRequests();
      $data = array();
      $api_token = trim($request["api_token"]);
      $api_key = trim($request["api_key"]);
      $check = $this->ntzcrmCheckToken($api_token, $api_key);
      if (!$check) {
        throw new Exception("Api key or token not valid. Please contact with administrator.");
      }
      if (empty($request['person_email'])) {
        throw new Exception("Please enter the email.");
      }

      $uemail = trim($request['person_email']);
      $ipcid = trim($request['crm_contact_id']);
      if (!empty($uemail) && !filter_var($uemail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter valid email.");
      }
      $user = get_user_by('email', $uemail);
      if (!$user) {
        throw new Exception("User doesn`t exist on " . NTZCRM_DOMAIN . ".");
      }

      $subject = sprintf(__('[%s] Password Reset'), $this->blogname);
      $message = $this->ntzcrmResetPasswordMailTemplate($user, 1);
      $isMailSent = $this->ntzCrmMailServicesSendMail($uemail, $subject, $message);

      if ($isMailSent == false) {
        throw new Exception("The e-mail could not be sent. Possible reason: your host may have disabled the mail() function.");
      }
      $msg = "Please check you mail to reset password.";
    } catch (Exception $e) {
      $status = 'failed';
      $msg = $e->getMessage();
      $this->sfNtzCrmMembershipErrorRequestLog($msg, $request);
    }
    $response = array('status' => $status, 'message' => $msg);
    echo json_encode($response);
    exit;
  }


  /*
   * @params: action=ntzcrm_update_user_tags,api_token,api_key,uemail,tagids=55,68,22
   * @Function use: ntzcrm_update_user_tags: This use for update users purchsed tag this api hit from SF. 
   * @created by: Hitesh verma
   */
  public function ntzcrm_update_user_tags()
  {
    $status = 'success';
    try {
      $tagError = $errComma = $tagNames = $comma = "";
      $tagStatus = "failed";
      $userStatus = "";
      $userMessage = "";
      $tagMessage = "Tag is empty.";

      $request = $this->ntzcrmJsonRequests();
      $data = array();
      $api_token = trim($request["api_token"]);
      $api_key = trim($request["api_key"]);
      $check = $this->ntzcrmCheckToken($api_token, $api_key);
      if (!$check) {
        throw new Exception("Api key or token not valid. Please contact with administrator.");
      }
      if (empty($request['person_email'])) {
        throw new Exception("Please enter the email.");
      }
      $uemail = trim($request["person_email"]);
      if (!empty($uemail) && !filter_var($uemail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter valid email.");
      }
      $user = get_user_by('email', $uemail);
      if (!$user) {
        throw new Exception("User doesn`t exist on " . NTZCRM_DOMAIN . ".");
      }

      // $requestTags=array('3152');
      if (empty($request['tag_ids'])) {
        $tagMessage = "Tag is empty.";
        throw new Exception("The tag should not be blank.");
      }
      $tagids = $request['tag_ids'];
      $geTags = ntzcrm_dbquery::_getMembershipTagsList();
      ksort($geTags);
      $tagError = $errComma = $tagNames = $comma = "";
      foreach ($tagids as $index => $tid) {
        if (!empty($tid) && !empty($geTags[$tid])) {
          $tagNames .= $comma . $geTags[$tid];
          $comma = ", ";
        } else {
          $tagError .= $errComma . $tid;
          $errComma = ',';
          unset($tagids[$index]);
        }
      }
      ntzcrm_dbquery::_updateUserTag($tagids, $user->ID);
      $msg = (!empty($tagNames)) ? 'Membership(s) successfully applied for ' . $tagNames . "." : '';
      $msg .= (!empty($tagError)) ? "Did not find membership for tag ids : " . $tagError . "." : '';

      $tagStatus = (!empty($tagError)) ? "failed" : "success";
      if (!empty($tagError)) {
        $tagMessage = $msg;
      } elseif (!empty($tagNames)) {
        $tagMessage = 'Membership(s) successfully applied for ' . $tagNames . ".";
      }
    } catch (Exception $e) {
      $status = 'failed';
      $msg = $e->getMessage();
      $this->sfNtzCrmMembershipErrorRequestLog($msg, $request);
    }
    $response = array('status' => $status, 'message' => $msg, 'userStatus' => $userStatus, 'userMessage' => $userMessage, 'tagStatus' => $tagStatus, 'tagMessage' => $tagMessage);
    echo json_encode($response);
    exit;
  }

  /*
   * @params: action=ntzcrm_update_user_tags,api_token,api_key,uemail,tagids=55,68,22
   * @Function use: ntzcrm_istag_exist: This use for update users purchsed tag this api hit from SF. 
   * @created by: Hitesh verma
   * @Created: 26-04-2020
   */

  public function ntzcrm_check_user_tags()
  {
    $status = 'success';
    try {
      $msg = "success";
      $request = $this->ntzcrmJsonRequests();
      $data = array();
      $api_token = trim($request["api_token"]);
      $api_key = trim($request["api_key"]);
      $check = $this->ntzcrmCheckToken($api_token, $api_key);
      if (!$check) {
        throw new Exception("Api key or token not valid. Please contact with administrator.");
      }
      if (empty($request['person_email'])) {
        throw new Exception("Please enter the email.");
      }
      $uemail = trim($request["person_email"]);
      if (!empty($uemail) && !filter_var($uemail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter valid email.");
      }

      $user = get_user_by('email', $uemail);
      if (!$user) {
        throw new Exception("User doesn`t exist on " . NTZCRM_DOMAIN . ".");
      }

      // $requestTags=array('3152');
      if (empty($request["tag_ids"])) {
        throw new Exception("The tag should not be blank.");
        /*id formate 1,2,55,66,*/
      }
      $tagids = $request["tag_ids"];
      $geTags = ntzcrm_dbquery::_getUserTags($user->ID);

      $tagErrorArr = $tagNameArr = array();

      $tagError = $tagNames = $errComma = $comma = "";
      if (!empty($tagids)) {
        $existKey = $nonexistKey = 0;
        foreach ($tagids as $key => $tid) {
          if (isset($geTags[$tid])) {
            $tagNameArr[$existKey]['id'] = $tid;
            $existKey++;
            $tagNames .= $comma . $geTags[$tid];
            $comma = ", ";
          } else {
            $tagErrorArr[$nonexistKey]['id'] = $tid;
            $tagError .= $errComma . $tid;
            $errComma = ',';
            $nonexistKey++;
          }
        }
        $msg = (!empty($tagNames)) ? "$tagNames tag id(s) exist." : '';
        $msg .= (!empty($tagError)) ? "$tagError tag id(s) does not exist." : '';

        $data['exist'] = $tagNameArr;
        $data['notExist'] = $tagErrorArr;
      } else {
        $msg = (!empty($tagError)) ? "$tagids tag id(s) does not exist." : '';
        $data['exist'] = array();
        $data['notExist'] = (!empty($tagids)) ? explode(",", $tagids) : array();
      }
    } catch (Exception $e) {
      $status = 'failed';
      $msg = $e->getMessage();
      $this->sfNtzCrmMembershipErrorRequestLog($msg, $request);
    }
    $response = array('status' => $status, 'message' => $msg, "data" => $data);
    echo json_encode($response);
    exit;
  }
  /*
 * @params: action=ntzcrm_remove_user_tags,api_token,api_key,uemail,tagids=55,68,22
 * @Function use: ntzcrm_remove_user_tags: This use for update users purchsed tag this api hit from SF. 
 * @created by: Hitesh verma
 * @Created: 18-04-2020
 */

  public function ntzcrm_remove_user_tags()
  {
    $status = 'success';
    try {
      $request = $this->ntzcrmJsonRequests();
      $api_token = trim($request["api_token"]);
      $api_key = trim($request["api_key"]);
      $check = $this->ntzcrmCheckToken($api_token, $api_key);
      if (!$check) {
        throw new Exception("Api key or token not valid. Please contact with administrator.");
      }
      if (empty($request['person_email'])) {
        throw new Exception("Please enter the email.");
      }
      $uemail = trim($request["person_email"]);
      if (!empty($uemail) && !filter_var($uemail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter valid email.");
      }

      $user = get_user_by('email', $uemail);
      if (!$user) {
        throw new Exception("User doesn`t exist on " . NTZCRM_DOMAIN . ".");
      }
      if (empty($request["tag_ids"])) {
        throw new Exception("Please tag ids should not be blank.");
        /*id formate 1,2,55,66,*/
      }
      $requestTags = $request["tag_ids"];

      ntzcrm_dbquery::_removeUserTag($requestTags, $user->ID);

      $msg = ($requestTags == 'all') ? "All tags are removed successfully." : "Tag(s) are removed successfully.";
    } catch (Exception $e) {
      $status = 'failed';
      $msg = $e->getMessage();
      $this->sfNtzCrmMembershipErrorRequestLog($msg, $request);
    }
    $response = array('status' => $status, 'message' => $msg);
    echo json_encode($response);
    exit;
  }

  public function ntzcrm_add_tag()
  {
    $msg = $status = 'success';
    $requestJson = "";
    try {
      $request = $this->ntzcrmRequests();
      if (!isset($request['submit'])) {
        $request = $this->ntzcrmJsonRequests();

        $api_token = trim($request["api_token"]);
        $api_key = trim($request["api_key"]);
        $check = $this->ntzcrmCheckToken($api_token, $api_key);

        $request['Id'] = $request["tag_id"];
        $request['CategoryId'] = "0";
        // $request['TagName'] = $request['TagName'];

        if (!$check) {
          throw new Exception("Api key or token not valid. Please contact with administrator.");
        }
      }

      ntzcrm_dbquery::_insertTag($request);
      if (isset($request['submit'])) {
        wp_redirect(admin_url('/admin.php?page=add-new-tag&clearcache=' . rand(1, 100)));
      }
      $msg = "Tag successfully added.";
    } catch (Exception $e) {
      $status = 'failed';
      $msg = $e->getMessage();
      $this->sfNtzCrmMembershipErrorRequestLog($msg, $requestJson);
    }
    $response = array('status' => $status, 'message' => $msg);
    echo json_encode($response);
    exit;
  }


  /*Add and remove tag functioality */
  public function ntzcrm_sanity_users()
  {
    $msg = $status = 'success';
    $data = array();
    try {
      $request = $this->ntzcrmJsonRequests();
      $api_token = trim($request["api_token"]);
      $api_key = trim($request["api_key"]);
      $check = $this->ntzcrmCheckToken($api_token, $api_key);
      if (!$check) {
        throw new Exception("Api key or token not valid. Please contact with administrator.");
      }
      global $wpdb;
      $results = $wpdb->get_results($wpdb->prepare("SELECT ID,user_login,user_email,display_name FROM $wpdb->users WHERE `user_login`!=`user_email`"));
      if (!empty($results)) {
        foreach ($results as $key => $result) {
          $data[$key]['id'] = $result->ID;
          $data[$key]['user_login'] = $result->user_login;
          $data[$key]['user_email'] = $result->user_email;
          $data[$key]['display_name'] = $result->display_name;
        }
      }
    } catch (Exception $e) {
      $status = 'failed';
      $msg = $e->getMessage();
      $this->sfNtzCrmMembershipErrorRequestLog($msg, $request);
    }
    $response = array('status' => $status, 'message' => $msg, 'data' => $data);
    echo json_encode($response);
    exit;
  }

  /*
   * @params: action=ntzcrm_check_black_listed_emails,api_token,api_key,uemail
   * @Function use: This use for wheter the email already exists or not?
   * @created by: Sheetal Rathore
   * @Created: 31-7-2020
   */

  public function ntzcrm_check_black_listed_emails()
  {
    $status = 'success';
    try {
      $msg = "success";
      $data = array();
      $request = $this->ntzcrmJsonRequests();
      $api_token = sanitize_text_field(trim($request['api_token']));
      $api_key = sanitize_text_field(trim($request['api_key']));
      $check = $this->ntzcrmCheckToken($api_token, $api_key);
      if (!$check) {
        throw new Exception("Api key or token not valid. Please contact with administrator.");
      }
      if (empty($request['person_email'])) {
        throw new Exception("Please enter the email.");
      }

      $uemail = sanitize_text_field(trim($request['person_email']));
      if (!empty($uemail) && !filter_var($uemail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter valid email.");
      }


      global $wpdb;
      $table_name = $wpdb->users;
      $sql="SELECT count(ID) FROM $wpdb->users WHERE `user_email`= '%s'";
      $count = $wpdb->get_var($wpdb->prepare($sql,[$uemail]));
      $data['is_exist'] = ($count == 1) ? "yes" : "no";
    } catch (Exception $e) {
      $status = 'failed';
      $msg = $e->getMessage();
      $this->sfNtzCrmMembershipErrorRequestLog($msg, $request);
    }
    $response = array('status' => $status, 'message' => $msg, "data" => $data);
    echo json_encode($response);
    exit;
  }

  public function export_tag()
  {
    $tags = ntzcrm_dbquery::_getMembershipTagsList();
    $delimiter = ",";
    $filename = "tags.csv";
    $f = fopen('php://memory', 'w');
    $fields = array('S/N', 'Tag Id', ' Tag Name');
    fputcsv($f, $fields);
    $count = 0;
    foreach ($tags as $tagId => $tagName) {
      $lineData = array($count + 1, $tagId, $tagName);
      fputcsv($f, $lineData, $delimiter);
      $count++;
    }
    fseek($f, 0);
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename="' . $filename . '";');
    fpassthru($f);
    exit;
  }

  /*
   * @params: action=ntzcrm_create_users_and_apply_tags,api_token,api_key,person_email,firstname,lastname,id,phone
   * @Function use: ntzcrm_create_users_and_apply_tags: This api use to create salesforce user in bulk 
   * @created by: Hitesh verma
   * @Created: 20-04-2020
   */

  public function ntzcrm_create_users_and_apply_tags()
  {
    $status = 'success';
    $request = $this->ntzcrmJsonRequests();
    $response = [];
    $tagError = $errComma = $tagNames = $comma = "";
    $tagStatus = $userStatus = "failed";
    $tagMessage = "";
    $userMessage = "";
    $api_token = trim($request["api_token"]);
    $api_key = trim($request["api_key"]);
    $check = $this->ntzcrmCheckToken($api_token, $api_key);
    if (!$check) {
      throw new Exception("Api key or token not valid. Please contact with administrator.");
    }
    if (!empty($request['data'])) {
      foreach ($request['data'] as $key => $reqData) {
        try {
          $geTags = ntzcrm_dbquery::_getMembershipTagsList();
          if (empty($reqData['person_email'])) {
            throw new Exception("Please enter the email.");
          }
          if (email_exists($reqData['person_email']) == false) {
            $username = $bemail = trim($reqData['person_email']);
            $password = (isset($reqData['password']) && !empty($reqData['password'])) ? trim($reqData['password']) : '';
            if (empty($password)) {
              throw new Exception("Password should not be blank.");
            }
            $display_name = ucfirst(trim($reqData['first_name'])) . ' ' . trim($reqData['last_name']);
            $user_id = wp_create_user($username, $password, $bemail);
            add_user_meta($user_id, 'ntzcrm_user_tag_sync', "yes");
            update_user_meta($user_id, 'first_name', sanitize_text_field(trim($reqData['first_name'])));
            update_user_meta($user_id, 'last_name', sanitize_text_field(trim($reqData['last_name'])));
            update_user_meta($user_id, 'ntzcrm_contact_id', sanitize_text_field(trim($reqData['crm_contact_id'])));
            update_user_meta($user_id, 'phone', sanitize_text_field(trim($reqData['phone'])));
            $userMessage = $msg = "User created successfully on " . NTZCRM_DOMAIN . ". ";
            if (isset($reqData['tag_ids']) && !empty($reqData['tag_ids'])) {
              $tagids = $reqData['tag_ids'];

              $tagError = $errComma = $tagNames = $comma = "";
              foreach ($tagids as $index => $tid) {
                if (!empty($tid) && !empty($geTags[$tid])) {
                  $tagNames .= $comma . $geTags[$tid];
                  $comma = ", ";
                } else {
                  $tagError .= $errComma . $tid;
                  $errComma = ',';
                  unset($tagids[$index]);
                }
              }
              ntzcrm_dbquery::_updateUserTag($tagids, $user_id);
              $msg .= (!empty($tagNames)) ? 'Membership(s) successfully applied for ' . $tagNames . "." : '';
              $msg .= (!empty($tagError)) ? "Did not find membership for tag ids : " . $tagError . "." : '';
              $tagStatus = (!empty($tagError)) ? "failed" : "success";
              if (!empty($tagError)) {
                $tagMessage = "Did not find membership for tag ids : " . $tagError . ".";
              } elseif (!empty($tagNames)) {
                $tagMessage = 'Membership(s) successfully applied for ' . $tagNames . ".";
              }
            } else {
              $tagMessage = "Tag is empty.";
            }
            $userStatus = "success";
          } else {
            if (isset($reqData['tag_ids']) && !empty($reqData['tag_ids'])) {
              $user = get_user_by('email', $reqData['person_email']);
              $userMessage = $errorMsg = "User already exists on " . NTZCRM_DOMAIN . ". ";
              if (isset($reqData['tag_ids']) && !empty($reqData['tag_ids'])) {
                $tagids = $reqData['tag_ids'];
                foreach ($tagids as $index => $tid) {
                  if (!empty($tid) && !empty($geTags[$tid])) {
                    $tagNames .= $comma . $geTags[$tid];
                    $comma = ", ";
                  } else {
                    $tagError .= $errComma . $tid;
                    $errComma = ',';
                    unset($tagids[$index]);
                  }
                }
                ntzcrm_dbquery::_updateUserTag($tagids, $user->ID);
                $errorMsg .= (!empty($tagNames)) ? 'Membership(s) successfully applied for ' . $tagNames . "." : '';
                $errorMsg .= (!empty($tagError)) ? "Did not find membership for tag ids : " . $tagError . "." : '';
                $tagStatus = (!empty($tagError)) ? "failed" : "success";
                if (!empty($tagError)) {
                  $tagMessage = $errorMsg;
                } elseif (!empty($tagNames)) {
                  $tagMessage = 'Membership(s) successfully applied for ' . $tagNames . ".";
                }
              }
              throw new Exception($errorMsg);
            } else {
              $tagMessage = "Tag is empty.";
              $userMessage = "User already exists on " . NTZCRM_DOMAIN . ".";
              throw new Exception("User already exists on " . NTZCRM_DOMAIN . ".");
            }
          }
        } catch (Exception $e) {
          $status = 'failed';
          $msg = $e->getMessage();
          $this->sfNtzCrmMembershipErrorRequestLog($msg, $request);
        }
        $response[] = array('status' => $status, 'message' => $msg, 'person_email' => $reqData['person_email'], 'userStatus' => $userStatus, 'userMessage' => $userMessage, 'tagStatus' => $tagStatus, 'tagMessage' => $tagMessage);
      }
    }
    echo json_encode($response);
    exit;
  }

  public function ntzcrm_get_users()
  {
    $status = "failed";
    $response = [];
    try {
      $request = $this->ntzcrmRequests();
      if (isset($request["term"]) & !empty($request["term"])) {
        $term = trim($request["term"]);
        $term = str_replace(" ", "+", $term);
        $result = ntzcrm_dbquery::_getSubscribers($term);
        if ($result) {
          $count = 0;
          foreach ($result as $id => $value) {
            $response[$count]["id"] = $id;
            $response[$count]["label"] = $value;
            $response[$count]["value"] = $value;
            $count++;
          }
          echo json_encode($response);
          die;
        }
      } else {
        $message = "Recird not found.";
      }
    } catch (Exception $exc) {
      $message = $exc->getMessage();
      echo $message;
      die;
    }
  }


  /*Publication Wizard*/
  public function ntzcrm_add_new_tag()
  {
    $status = 'success';
    try {
      $request = $this->ntzcrmRequests();
      if (isset($request['tag_name']) && !empty($request['tag_name'])) {
        $tag['TagName'] = wp_strip_all_tags(sanitize_text_field($request['tag_name']));
        $tag['plan_link'] = wp_strip_all_tags(sanitize_text_field($request['plan_link']));
        ntzcrm_dbquery::_insertTag($tag);
      }
      $msg = "Tag successfully added.";
    } catch (Exception $e) {
      $status = 'failed';
      $msg = $e->getMessage();
    }
    $response = array('status' => $status, 'message' => $msg);
    echo json_encode($response);
    exit;
  }


  public function get_membership_tags()
  {
    $message = $status = 'success';
    try {
      $request = $this->ntzcrmJsonRequests();
      $tagError = $errComma = $tagNames = $comma = "";
      $tagStatus = $userStatus = "failed";
      $api_token = trim($request["api_token"]);
      $api_key = trim($request["api_key"]);
      $check = $this->ntzcrmCheckToken($api_token, $api_key);

      if (!$check) {
        throw new Exception("Api key or token not valid. Please contact with administrator.");
      }

      $tags = ntzcrm_dbquery::_getMembershipTagsList();
      if (empty($tags)) {
        throw new Exception("Api key or token not valid. Please contact with administrator.");
      }
    } catch (Exception $e) {
      $status = 'failed';
      $message = $e->getMessage();
    }
    $this->createJson($status, $message, $tags);
  }

  public function ntzcrm_get_user_tags()
  {
    $status = 'success';
    $request = $this->ntzcrmJsonRequests();
    try {
      $message = "success";

      $data = array();
      $api_token = trim($request["api_token"]);
      $api_key = trim($request["api_key"]);
      $check = $this->ntzcrmCheckToken($api_token, $api_key);
      if (!$check) {
        throw new Exception("Api key or token not valid. Please contact with administrator.");
      }

      $uemail = trim($request["person_email"]);
      if (!empty($uemail) && !filter_var($uemail, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("Please enter valid email.");
      }

      $user = get_user_by('email', $uemail);
      if (!$user) {
        throw new Exception("User doesn`t exist on " . NTZCRM_DOMAIN . ".");
      }

      $tagids = $request["tag_ids"];
      $tags = ntzcrm_dbquery::_getUserTags($user->ID);
      if (empty($tags)) {
        throw new Exception("Membership Tags not found for this user.");
      }
    } catch (Exception $e) {
      $status = 'failed';
      $msg = $e->getMessage();
      $this->sfNtzCrmMembershipErrorRequestLog($msg, $request);
    }
    $this->createJson($status, $message, $tags);
  }


  function testMailTemplate(){
      $status = 'success';
      $request = $this->ntzcrmJsonRequests();
      try {
        $message = "success";
  
        $data = array();
        $api_token = trim($request["api_token"]);
        $api_key = trim($request["api_key"]);
        $check = $this->ntzcrmCheckToken($api_token, $api_key);
        if (!$check) {
          throw new Exception("Api key or token not valid. Please contact with administrator.");
        }
  
        $uemail = trim($request["person_email"]);
        $templateMethod = trim($request["email_template"]);
        $subject = trim($request["subject"]);

        if (!empty($uemail) && !filter_var($uemail, FILTER_VALIDATE_EMAIL)) {
          throw new Exception("Please enter valid email.");
        }
  
        $user = get_user_by('email', $uemail);
        if (!$user) {
          throw new Exception("User doesn`t exist on " . NTZCRM_DOMAIN . ".");
        }
        if($templateMethod=="reset"){
          $message = $this->ntzcrmResetPasswordMailTemplate($user);  
        }elseif($templateMethod=="create"){
          $message = $this->ntzcrmCreatePasswordMailTemplate($user);  
        }elseif($templateMethod=="welcome"){
          $message = $this->ntzcrmWelcomeMailTemplate($user);  
        }else{
          $message = $this->ntzcrmWelcomeMailTemplate($user);  
        } 
        $isMailSent = $this->ntzCrmMailServicesSendMail($uemail, $subject, $message);
        if ($isMailSent == false) {
          throw new Exception("The e-mail could not be sent. Possible reason: your host may have disabled the mail() function.");
        }

      } catch (Exception $e) {
        $status = 'failed';
        $message = $e->getMessage(); 
      }
      $this->createJson($status, $message);

     
  }
}
