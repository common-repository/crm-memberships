<?php

/**
 * 
 */
class ntzcrm_dbquery
{

	public static function _getMembershipTags($limit = "", $offset = "")
	{
		global $wpdb;
		$tags = get_transient('cache_ntzcrm_tag');
		if (false === $tags) {
			$expiry = 86400 * 10;
			$table_name = $wpdb->prefix . NTZCRMPRIFIX . 'membership_tags';
			$tags = $wpdb->get_results($wpdb->prepare("SELECT * FROM $table_name WHERE 1=%d",1), ARRAY_A);
			set_transient('cache_ntzcrm_ifs_tag_map', $tags, $expiry);
		}
		return $tags;
	}

	public static function _getMembershipTagsList()
	{
		global $wpdb;
		$tag_arr = array();
		delete_transient('cache_ntzcrm_tag_list');
		$isTagCacheExist = get_transient('cache_ntzcrm_tag_list');
		if (empty($isTagCacheExist)) {
			$expiry = 86400 * 10;
			$table_name = $wpdb->prefix . NTZCRMPRIFIX . 'membership_tags';
			$results = $wpdb->get_results($wpdb->prepare("SELECT `id`,`name` FROM $table_name WHERE 1=%d",1));
			if (!empty($results)) {

				foreach ($results as $key => $tag) {
					if (!empty($tag->id) && !empty($tag->name)) {
						$tag_arr[$tag->id] = $tag->name;
					}
				}
			}
			set_transient('cache_ntzcrm_tag_list', $tag_arr, $expiry);
		} else {
			$tag_arr = $isTagCacheExist;
		}
		return $tag_arr;
	}

	public static function _getUserTagList($userId)
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "user_tags";
		$sql = 'SELECT `tag_id` FROM `' . $table_name . '` WHERE `user_id` = %d';
		$results = $wpdb->get_results($wpdb->prepare($sql, array($userId)));

		if (!empty($results)) {
			foreach ($results as $key => $tag) {
				$tag_arr[] = $tag->tag_id;
			}
		}
		return $tag_arr;
	}


	public static function _getPostTagList($post_id)
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "post_tags";
		$sql = 'SELECT `tag_id` FROM `' . $table_name . '` WHERE `post_id` = %d';
		$results = $wpdb->get_results($wpdb->prepare($sql, array($post_id)));
		if (!empty($results)) {
			foreach ($results as $key => $tag) {
				$tag_arr[] = $tag->tag_id;
			}
		}
		return $tag_arr;
	}

	public static function _getPostByTagId($tagIds)
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "post_tags";
		$placeholders = implode(',', array_fill(0, count($tagIds), '%d'));
		$sql = "SELECT * FROM `$table_name` 
				INNER JOIN `$wpdb->posts` ON `post_id`=`$wpdb->posts`.`ID` 
				WHERE `tag_id` IN ({$placeholders})";
		$results = $wpdb->get_results($wpdb->prepare($sql,$tagIds));
		return $results;
	}

	public static function _getPostIdsByAccessTag($tagIds)
	{
		global $wpdb;
		$post_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "post_tags";
		$sql = 'SELECT `post_id` FROM `' . $table_name . '` WHERE `tag_id` IN (' . $tagIds . ');';
		$results = $wpdb->get_results($wpdb->prepare($sql));
		if (!empty($results)) {
			foreach ($results as $key => $post) {
				$post_arr[] = $post->post_id;
			}
		}
		return $post_arr;
	}


	public static function _getParentPageIdsByAccessTag($tagIds)
	{
		global $wpdb;
		$post_arr = array();
		$table = $wpdb->prefix . NTZCRMPRIFIX . "post_tags";
		$table1 = $wpdb->posts;
		$sql = "SELECT * FROM `$table` INNER JOIN $table1 ON `$table`.`post_id`=`$table1`.`ID` WHERE tag_id IN ($tagIds) AND post_parent =0 AND post_status='publish' AND post_type='page'";

		$results = $wpdb->get_results($wpdb->prepare($sql));
		if (!empty($results)) {
			foreach ($results as $key => $post) {
				$post_arr[] = $post->post_id;
			}
		}
		return $post_arr;
	}

	public static function _updateUserTag($tags, $userId)
	{
		global $wpdb;
		if (!empty($tags)) {
			$values = [];
			$table_name = $wpdb->prefix . NTZCRMPRIFIX . "user_tags";
			$userTags = self::_getUserTagList($userId);
			$newTags = array_unique(array_merge($tags, $userTags));
			$countTags = self::_checkUserTag($userId);
			if ($countTags > 0) {
				$wpdb->delete($table_name, array('user_id' => $userId));
			}
			foreach ($newTags as $key => $tagId) {
				$created = date("Y-m-d H:i:s");
				array_push($values, $userId, $tagId, $created);
				$place_holders[] = "('%d', '%d', '%s')";
			}
			$query = "INSERT INTO $table_name (user_id,tag_id,created) VALUES ";
			$query .= implode(', ', $place_holders);
			$wpdb->query($wpdb->prepare("$query ", $values));
		}
	}

	public static function _removeUserTag($tags, $userId)
	{
		global $wpdb;
		if (!empty($tags)) {
			$values = [];
			$table_name = $wpdb->prefix . NTZCRMPRIFIX . "user_tags";
			$countTags = self::_checkUserTag($userId);
			if ($countTags > 0) {
				if ($tags != "all") {
					foreach ($tags as $tagId) {
						$wpdb->query($wpdb->prepare("DELETE FROM $table_name WHERE user_id = %s AND tag_id = %s", array($userId, $tagId)));
					}
				} else {
					$wpdb->delete($table_name, array('user_id' => $userId));
				}
			}
		}
	}

	public static function _checkUserTag($user_id)
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "user_tags";
		$sql = 'SELECT count(`tag_id`) FROM `' . $table_name . '` WHERE `user_id` = %d';
		$results = $wpdb->get_var($wpdb->prepare($sql, array($user_id)));
		return $results;
	}

	public static function _checkPostTag($postId)
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "post_tags";
		$sql = 'SELECT count(`tag_id`) FROM `' . $table_name . '` WHERE `post_id` = %d';
		$results = $wpdb->get_var($wpdb->prepare($sql, array($postId)));
		return $results;
	}

	public static function _getUserTags($user_id)
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "user_tags";
		$membershipTagTabel = $wpdb->prefix . NTZCRMPRIFIX . "membership_tags";
		$sql = 'SELECT tag_id,name  FROM `' . $table_name . '` INNER JOIN `' . $membershipTagTabel . '` ON `' . $table_name . '`.tag_id = `' . $membershipTagTabel . '`.id WHERE `user_id` = %d';
		$results = $wpdb->get_results($wpdb->prepare($sql, array($user_id)));
		if (!empty($results)) {
			foreach ($results as $key => $tag) {
				$tag_arr[$tag->tag_id] = $tag->name;
			}
		}
		return $tag_arr;
	}

	public static function _getPostTags($post_id)
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "post_tags";
		$membershipTagTabel = $wpdb->prefix . NTZCRMPRIFIX . "membership_tags";
		$sql = 'SELECT tag_id,name FROM `' . $table_name . '` INNER JOIN `' . $membershipTagTabel . '` ON `' . $table_name . '`.tag_id = `' . $membershipTagTabel . '`.id WHERE `post_id` = %d';
		$results = $wpdb->get_results($wpdb->prepare($sql, array($post_id)));
		if (!empty($results)) {
			foreach ($results as $key => $tag) {
				$tag_arr[$tag->tag_id] = $tag->name;
			}
		}
		return $tag_arr;
	}

	public static function _getPostTagsId($post_id)
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "post_tags";
		$membershipTagTabel = $wpdb->prefix . NTZCRMPRIFIX . "membership_tags";
		$sql = 'SELECT tag_id,name FROM `' . $table_name . '` INNER JOIN `' . $membershipTagTabel . '` ON `' . $table_name . '`.tag_id = `' . $membershipTagTabel . '`.id WHERE `post_id` = %d';
		$results = $wpdb->get_results($wpdb->prepare($sql, array($post_id)));
		if (!empty($results)) {
			foreach ($results as $key => $tag) {
				// $tag_arr[$tag->tag_id] = $tag->name;
				array_push($tag_arr, $tag->tag_id);
			}
		}
		return $tag_arr;
	}

	public static function _insertTag($tag)
	{
		global $wpdb;
		if (!empty($tag)) {
			$values = [];
			$table_name = $wpdb->prefix . NTZCRMPRIFIX . "membership_tags";
			if (isset($tag['Id']) && !empty($tag['Id'])) {
				if (self::_checkMembershipTag($tag['Id']) > 0) {
					$tagName = trim($tag['TagName']);
					$planLink = trim($tag['planLink']);
					$categoryId = (isset($tag['CategoryId'])) ? trim($tag['CategoryId']) : "0";
					if ($wpdb->update($table_name, array('name' => $tagName, 'plan_link' => $planLink, 'category_id' => $categoryId), array('id' => $tag['Id']))) {
						return true;
					}
				} else {
					$query = "INSERT INTO $table_name (id,name,plan_link,category_id,status) VALUES ";
					$tagName = trim($tag['TagName']);
					$postId = trim($tag['Id']);
					$planLink = trim($tag['planLink']);
					$categoryId = (isset($tag['CategoryId'])) ? trim($tag['CategoryId']) : "0";
					array_push($values, $postId, $tagName, $planLink, $categoryId, 1);
					$place_holders[] = "('%d','%s','%s', '%d','%d')";
				}
			} else {
				$query = "INSERT INTO $table_name (name,plan_link,category_id,status) VALUES ";
				$tagName = sanitize_text_field(trim($tag['TagName']));
				$planLink = sanitize_text_field(trim($tag['plan_link']));
				$categoryId = sanitize_text_field(trim($tag['CategoryId']));

				array_push($values, $tagName, $planLink, $categoryId, 1);
				$place_holders[] = "('%s','%s' ,'%d','%d')";
			}


			$query .= implode(', ', $place_holders);
			$test = $wpdb->prepare("$query ", $values);
			$wpdb->query($wpdb->prepare("$query ", $values));

			// $vart = "teygsvadv";
			return true;
		}
	}

	public static function _getUsers($offset = "0", $limit = "10000")
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->users;
		$sql = "SELECT ID,user_email FROM `$table_name` WHERE 1=%d LIMIT $limit OFFSET $offset";
		$results = $wpdb->get_results($wpdb->prepare($sql,1));
		return $results;
	}
	public static function _getSubscribers($emailText = "")
	{
		global $wpdb;
		$userArr = array();
		$user = $wpdb->users;
		$userMeta = $wpdb->prefix . "usermeta";

		$withText = !empty($emailText) ? $wpdb->prepare("AND `user_email` LIKE %s", '%' . $wpdb->esc_like($emailText) . '%') : "";

		$sql = $wpdb->prepare("
			SELECT $user.ID, $user.user_email, $user.display_name 
			FROM $user
			INNER JOIN $userMeta ON $user.ID = $userMeta.user_id
			WHERE $userMeta.meta_key = %s 
			AND $userMeta.meta_value LIKE %s
			$withText", ['wp_capabilities','%subscriber%']);

		$results = $wpdb->get_results($sql);

		if (!empty($results)) {
			foreach ($results as $key => $user) {
				$displayName = (!empty($user->display_name)) ? "($user->display_name)" : "";;
				$userArr[$user->ID] = $user->user_email . $displayName;
			}
		}
		return $userArr;
	}

	public static function _getPosts($offset = "0", $limit = "10000")
	{
		global $wpdb;
		$tag_arr = array();
		$table = $wpdb->posts;
		$table1 = $wpdb->prefix . "postmeta";
		$sql = 'SELECT ID,`meta_value` FROM `' . $table . '` INNER JOIN `' . $table1 . '` ON `' . $table . '`.`ID`= `' . $table1 . '`.`post_id` WHERE `meta_key` LIKE "_ntzcrm_post_permission" AND (post_type="post" OR post_type="page" ) LIMIT ' . $limit . ' OFFSET ' . $offset;
		$results = $wpdb->get_results($wpdb->prepare($sql));
		return $results;
	}

	public static function _insertPostAccessTag($post_id, $postTags)
	{
		global $wpdb;
		$countTags = self::_checkPostTag($post_id);
		if (!empty($postTags) && $countTags == 0) {
			$values = [];
			$table_name = $wpdb->prefix . NTZCRMPRIFIX . "post_tags";
			$created = date("Y-m-d H:i:s");
			foreach ($postTags as $key => $tagId) {
				if (!empty($tagId)) {
					array_push($values, $post_id, $tagId, $created);
					$place_holders[] = "('%d','%d','%s')";
				}
			}
			$query = "INSERT INTO $table_name (post_id,tag_id,'created') VALUES ";
			$query .= implode(', ', $place_holders);
			$wpdb->query($wpdb->prepare("$query ", $values));
		}
	}

	public static function _insertUserAccessTag($userId, $userTags)
	{
		global $wpdb;
		$newUserTagList = self::_getUserTagList($userId);
		$values = [];
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "user_tags";
		foreach ($userTags as $key => $tagId) {
			$userId = sanitize_text_field(trim($userId));
			$tagId = sanitize_text_field(trim($tagId));
			if (!empty($tagId)) {
				$created = date("Y-m-d H:i:s");
				if (!empty($newUserTagList)) {
					if (!in_array($tagId, $newUserTagList)) {
						array_push($values, $userId, $tagId, $created);
						$place_holders[] = "('%d', '%d', '%s')";
					}
				} else {
					array_push($values, $userId, $tagId, $created);
					$place_holders[] = "('%d','%d','%s')";
				}
			}
			unset($tagId);
		}
		if (!empty($place_holders)) {
			$query = "INSERT INTO $table_name (user_id,tag_id,created) VALUES ";
			$query .= implode(', ', $place_holders);
			$wpdb->query($wpdb->prepare("$query", $values));
		}
	}

	public static function _checkMembershipTag($id)
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "membership_tags";
		$sql = 'SELECT count(`id`) FROM `' . $table_name . '` WHERE `id` = %d';
		$results = $wpdb->get_var($wpdb->prepare($sql, array($id)));
		return $results;
	}

	public static function _svUserLoginTime($userId)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . 'user_logs';
		$values = [];
		$place_holders[] = "('%d','%s')";
		array_push($values, $userId, date("Y-m-d H:i:s"));
		$query = "INSERT INTO $table_name (user_id,login) VALUES ";
		$query .= implode(', ', $place_holders);
		$wpdb->query($wpdb->prepare($query, $values));
	}

	public static function _svUserLogOutTime($userId)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . 'user_logs';
		$check = $wpdb->get_row($wpdb->prepare("SELECT id FROM $table_name where user_id=" . $userId . " ORDER BY DESC id"));
		if (!empty($check)) {
			$wpdb->update($table_name, array('logout' => date("Y-m-d H:i:s")), array('id' => $check->id));
		}
	}

	public static function _svUserActivities($userId, $postId)
	{
		global $wpdb;
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . 'user_activites';
		$sql = 'SELECT * FROM `' . $table_name . '` WHERE `user_id` = %d AND `post_id` = %d';
		$check = $wpdb->get_row($wpdb->prepare($sql, array($userId, $postId)));
		if (!empty($check)) {
			$newCount = $check->view_count + 1;
			$wpdb->update($table_name, array('view_count' => $newCount, 'modified' => date("Y-m-d H:i:s")), array('id' => $check->id));
		} else {
			$values = [];
			$place_holders[] = "('%d','%d','%d','%s','%s')";
			$date = date("Y-m-d H:i:s");
			array_push($values, $userId, $postId, 1, $date, $date);
			$query = "INSERT INTO $table_name (user_id,post_id,view_count,modified,created) VALUES ";
			$query .= implode(', ', $place_holders);
			$wpdb->query($wpdb->prepare($query, $values));
		}
	}

	public static function _getUserLoginLog($user_id, $limit = "10")
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "user_logs";
		$sql = 'SELECT `login` FROM `' . $table_name . '` WHERE `user_id` = %d ORDER BY login DESC LIMIT ' . $limit . ';';
		$results = $wpdb->get_results($wpdb->prepare($sql, array($user_id)));
		return $results;
	}

	public static function _getUserActivites($user_id, $limit = "10")
	{
		global $wpdb;
		$tag_arr = array();
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "user_activites";
		$sql = 'SELECT * FROM `' . $table_name . '` WHERE `user_id` = %d ORDER BY modified DESC LIMIT ' . $limit . '';
		$results = $wpdb->get_results($wpdb->prepare($sql, array($user_id)));
		return $results;
	}
	/*Publication Wizard */
	public static function _insertPostTag($postId, $tags)
	{
		global $wpdb;
		$values = [];

		if (isset($tags) && !empty($tags)) {
			$tags = array_unique($tags);

			$table_name = $wpdb->prefix . NTZCRMPRIFIX . "post_tags";
			$wpdb->delete($table_name, array('post_id' => $postId));
			foreach ($tags as $key => $tagId) {
				array_push($values, $postId, $tagId);
				$place_holders[] = "('%d', '%d')";
			}
			$query = "INSERT INTO $table_name (post_id, tag_id) VALUES ";
			$query .= implode(', ', $place_holders);
			$wpdb->query($wpdb->prepare($query, $values));
		}
	}
	public static function _ntzcrmSvPublications($data)
	{
		if (isset($data['save'])) {
			if (!empty($data["pid"]) && isset($data['step']) && $data['step'] == 2) {

				if (!empty($data["posttag"])) {
					$postId = $data["pid"];
					self::_insertPostTag($postId, $data["posttag"]);
					$publication["ID"] = $data["pid"];
					$publication['post_status'] = "publish";
					$postId = wp_update_post($publication, true);
				}
				$key = "err";
				$filteredURL = preg_replace('~(\?|&)' . $key . '=[^&]*~', '$1', $data["_wp_http_referer"]);
				$redirect = $filteredURL . "&pid=" . $postId . "&step=" . $data['next_step'];
				wp_safe_redirect($redirect, 301);
				die;
			} elseif (!empty($data["pid"]) && isset($data['step']) && $data['step'] == 3) {
				$postId = $data["pid"];
				if (isset($data['is_fronted_publication']) && !empty($data['is_fronted_publication'])) {
					if (!get_post_meta($postId, 'is_fronted_publication', true)) {
						add_post_meta($postId, 'is_fronted_publication', sanitize_text_field(trim($data['is_fronted_publication'])));
					} else {
						update_post_meta($postId, 'is_fronted_publication', sanitize_text_field(trim($data['is_fronted_publication'])));
					}
				} else {
					if (!get_post_meta($postId, 'is_fronted_publication', true)) {
						add_post_meta($postId, 'is_fronted_publication', "no");
					} else {
						update_post_meta($postId, 'is_fronted_publication', "no");
					}
				}
				$key = "err";
				$filteredURL = preg_replace('~(\?|&)' . $key . '=[^&]*~', '$1', $data["_wp_http_referer"]);
				$redirect = admin_url("/admin.php?page=publications");
				wp_safe_redirect($redirect, 301);
				die;
			}

			$key = "err";
			$filteredURL = preg_replace('~(\?|&)' . $key . '=[^&]*~', '$1', $data["_wp_http_referer"]);
			$user_id = get_current_user_id();
			if (!empty($data["pid"]) || !post_exists(wp_strip_all_tags($data['title']))) {
				$publication = array(
					'post_author'  => $user_id,
					'post_title'    => sanitize_text_field(wp_strip_all_tags($data['title'])),
					'post_content'  => wp_strip_all_tags($data['title']),
					'post_type'     => 'page',
				);

				if (!empty($data["pid"])) {
					unset($publication['post_content']);  // Post conent will not update in edit Wizard
					$postId = $publication["ID"] = $data["pid"];
					$postId = wp_update_post($publication, true);
				} else {
					$publication['post_status'] = 'draft';
					$postId = wp_insert_post($publication, true);
				}
				if (!is_wp_error($postId)) {
					if (isset($data['is_' . NTZCRMPRIFIX . 'login_required']) && !empty($data['is_' . NTZCRMPRIFIX . 'login_required'])) {
						if (!get_post_meta($postId, 'is_' . NTZCRMPRIFIX . 'login_required', true)) {
							add_post_meta($postId, 'is_' . NTZCRMPRIFIX . 'login_required', sanitize_text_field(trim($data['is_' . NTZCRMPRIFIX . 'login_required'])));
						} else {
							update_post_meta($postId, 'is_' . NTZCRMPRIFIX . 'login_required', sanitize_text_field(trim($data['is_' . NTZCRMPRIFIX . 'login_required'])));
						}
					}
					if (isset($data['ntzcrm_access_icon']) && !empty($data[NTZCRMPRIFIX . 'access_icon'])) {
						if (!get_post_meta($postId, NTZCRMPRIFIX . 'access_icon', true)) {
							add_post_meta($postId, NTZCRMPRIFIX . 'access_icon', sanitize_text_field(trim($data[NTZCRMPRIFIX . 'access_icon'])));
						} else {
							update_post_meta($postId, NTZCRMPRIFIX . 'access_icon', sanitize_text_field(trim($data[NTZCRMPRIFIX . 'access_icon'])));
						}
					} else {
						if (!get_post_meta($postId, NTZCRMPRIFIX . 'access_icon', true)) {
							add_post_meta($postId, NTZCRMPRIFIX . 'access_icon', "");
						} else {
							update_post_meta($postId, NTZCRMPRIFIX . 'access_icon', "");
						}
					}
					if (isset($data[NTZCRMPRIFIX . 'noaccess_icon']) && !empty($data[NTZCRMPRIFIX . 'noaccess_icon'])) {
						if (!get_post_meta($postId, NTZCRMPRIFIX . 'noaccess_icon', true)) {
							add_post_meta($postId, NTZCRMPRIFIX . 'noaccess_icon', sanitize_text_field(trim($data[NTZCRMPRIFIX . 'noaccess_icon'])));
						} else {
							update_post_meta($postId, NTZCRMPRIFIX . 'noaccess_icon', sanitize_text_field(trim($data[NTZCRMPRIFIX . 'noaccess_icon'])));
						}
					} else {
						if (!get_post_meta($postId, NTZCRMPRIFIX . 'noaccess_icon', true)) {
							add_post_meta($postId, NTZCRMPRIFIX . 'noaccess_icon', "");
						} else {
							update_post_meta($postId, NTZCRMPRIFIX . 'noaccess_icon', "");
						}
					}
					if (!get_post_meta($postId, "is_" . NTZCRMPRIFIX . 'publication', true)) {
						add_post_meta($postId, "is_" . NTZCRMPRIFIX . 'publication', "yes");
					} else {
						update_post_meta($postId, NTZCRMPRIFIX . 'publication', "yes");
					}
					if (!get_post_meta($postId, 'is_fronted_publication', true)) {
						add_post_meta($postId, 'is_fronted_publication', "yes");
					} else {
						update_post_meta($postId, 'is_fronted_publication', "yes");
					}

					$redirect = $filteredURL . "&pid=" . $postId . "&step=" . $data['next_step'];
				} else {
					$error = $postId->get_error_message();
					$redirect = $data["_wp_http_referer"] . "&err=" . $error;
				}
			} else {
				$error = "This publication already exist.";
				$redirect = $data["_wp_http_referer"] . "&err=" . $error;
			}
			wp_safe_redirect($redirect, 301);
			die;
		}
	}

	public static function _getFrontPublications($attr = "")
	{
		global $wpdb;
		$postPermission = new NtzCrmPostPermission();
		$request = $postPermission->ntzcrmRequests();

		$orderby = (isset($attr["orderby"]) && !empty($attr["orderby"])) ? $attr["orderby"] : "post_title";
		$order = (isset($attr["order"]) && !empty($attr["order"])) ? $attr["order"] : "ASC";
		$per_page = (isset($attr["limit"]) && !empty($attr["limit"])) ? $attr["limit"] : "1000";
		$paged = isset($request['paged']) ? ($per_page * max(0, intval($request['paged']) - 1)) : 0;
		
		$postStatus='publish';
		$isNtzcrmPublicationMataValue='is_ntzcrm_publication';
		$isNtzcrmPublication='yes';

		$isFrontedPublicationMataValue='is_fronted_publication';
		$isFrontedPublication='yes';

		// $conditions = "WHERE 
		// `t1`.`meta_key`='is_ntzcrm_publication' 
		// AND `t1`.`meta_value`='yes' 
		// AND `t2`.`meta_key`='is_fronted_publication' 
		// AND `t2`.`meta_value`=%s 
		// AND post_status= %s ";  //AND 

		$conditions = " WHERE 
			`t1`.`meta_key`= %s
			AND `t1`.`meta_value`= %s
			AND `t2`.`meta_key`= %s 
			AND `t2`.`meta_value`= %s 
			AND post_status= %s";  //AND 
			
		$sql = "SELECT `$wpdb->posts`.ID,post_name,post_title,post_status FROM `$wpdb->posts` 
		INNER JOIN `" . $wpdb->postmeta . "` as t1 ON `t1`.`post_id`=`" . $wpdb->posts . "`.`ID` 
		INNER JOIN `" . $wpdb->postmeta . "` as t2 ON `t2`.`post_id`=`" . $wpdb->posts . "`.`ID`  
		$conditions GROUP BY `$wpdb->posts`.ID ORDER BY $orderby $order  LIMIT $paged,$per_page";

		//$total_items = $wpdb->get_var("SELECT count(*) FROM $wpdb->posts $conditions"); 
		$results = $wpdb->get_results($wpdb->prepare($sql,[$isNtzcrmPublicationMataValue,$isNtzcrmPublication,$isFrontedPublicationMataValue,$isFrontedPublication,$postStatus]), ARRAY_A);
		return $results;
	}
	/*End Publication Wizard */


	public static function getMembershipTagPlanLink($planIds = [])
	{
		global $wpdb;
		$tag_arr = array();
		$placeholders = implode(',', array_fill(0, count($planIds), '%d'));
		$table_name = $wpdb->prefix . NTZCRMPRIFIX . "membership_tags";
		$sql = "SELECT `plan_link` FROM `$table_name` WHERE `id` IN ({$placeholders}) AND plan_link IS NOT NULL order by id ASC";
		$results = $wpdb->get_var($wpdb->prepare($sql,$planIds));
		return $results;
	}
}
