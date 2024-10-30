<?php
global $wpdb;
$table_name = $wpdb->users;
$postPermission = new NtzCrmPostPermission();
$request = $postPermission->ntzcrmRequests();


$orderby = (isset($request['orderby']) && in_array($request['orderby'], array_keys($this->get_sortable_columns()))) ? $request['orderby'] : 'user_registered';
$order = (isset($request['order']) && in_array($request['order'], array('asc', 'desc'))) ? ' ' . strtoupper($request['order']) : ' DESC';
$dateFrom = (isset($request['datefrom']) && !empty($request['datefrom'])) ? sanitize_text_field($request['datefrom']) : date("Y-m-d");
$dateTo = (isset($request['dateto']) && !empty($request['dateto'])) ? sanitize_text_field($request['dateto']) : date("Y-m-d");

$placeHolderValue = [];
$conditions = "WHERE 1=%d ";
$placeHolderValue[] = 1;
if (isset($request['tag']) && !empty($request['tag'])) {
  $conditions .= "AND  tag_id = %d";
  $placeHolderValue[] = sanitize_text_field($request['tag']);
} else {
  $conditions .= "AND `user_registered` BETWEEN %s AND %s";
  $placeHolderValue[] = $dateFrom . " 00:00:00";
  $placeHolderValue[] = $dateTo . " 23:59:59";
}

if (isset($request['s']) && !empty($request['s'])) {
  $and = ' AND ';
  $conditions .= " $and (`display_name` LIKE '%" . $wpdb->esc_like($request['s']) . "%' OR `user_email` LIKE '%" . $wpdb->esc_like($request['s']) . "%')";
}

$userTag = $wpdb->prefix . NTZCRMPRIFIX . "user_tags";
$membershipTagTabel = $wpdb->prefix . NTZCRMPRIFIX . "membership_tags";

$sql = 'SELECT * FROM `' . $userTag . '` 
INNER JOIN `' . $membershipTagTabel . '` ON `' . $userTag . '`.`tag_id` = `' . $membershipTagTabel . '`.`id` 
INNER JOIN `' . $table_name . '` ON `' . $userTag . '`.`user_id` = `' . $table_name . '`.`ID` ' . $conditions . ' 
GROUP BY user_id ORDER BY ' . $orderby . ' ' . $order ; // . ' LIMIT ' . $paged . ',' . $per_page

$countSql = 'SELECT count(*) FROM `' . $userTag . '` 
INNER JOIN `' . $membershipTagTabel . '` ON `' . $userTag . '`.`tag_id` = `' . $membershipTagTabel . '`.`id` 
INNER JOIN `' . $table_name . '` ON `' . $userTag . '`.`user_id` = `' . $table_name . '`.`ID` ' . $conditions;

// $total_items = $wpdb->get_var($wpdb->prepare($countSql, $placeHolderValue));
$users = $wpdb->get_results($wpdb->prepare($sql, $placeHolderValue));

// pr($users);die;
$delimiter = ",";
if (isset($request['dateto']) && !empty($request['dateto'])) {
  $filename = "subscribers_from_" . $dateFrom . "_to_" . $dateTo . ".csv";
} else {
  $filename = "subscribers_" . $dateFrom . ".csv";
}

$f = fopen('php://memory', 'w');

$fields = array('S/N', 'Name', 'Email', 'Registration date', "Contact ID", "Membership");
fputcsv($f, $fields);
foreach ($users as $count => $user) {
  $contactId = get_user_meta($user->ID, 'ntzcrm_contact_id', true);
  if (!$contactId) {
    $contactId = get_user_meta($user->ID, '_ntzcrm_user_contact_id', true);
  }
  $userTags = ntzcrm_dbquery::_getUserTags($user->ID);
  $tagNames = $comma = "";
  if (!empty($userTags)) { 
      $tagNames = $comma = "";
      foreach ($userTags as $tid => $tName) {
        $tagNames .= $comma . $tid . " : " . $tName;
        $comma = " | ";
      } 
  } 
  $lineData = array($count + 1, $user->display_name, $user->user_email, $user->user_registered, $contactId, $tagNames);
  fputcsv($f, $lineData, $delimiter);
  unset($tagNames);
  unset($contactId);
}
fseek($f, 0);
header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="' . $filename . '";');

fpassthru($f);
die;
