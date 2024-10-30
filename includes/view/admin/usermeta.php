<div style="padding:20px;background-color: white;border:1px solid #80808036;">
	<h1 style="padding: 20px 0;border-width: 0 0 1px 0;border-style: solid;border-color: #444444;"><?php esc_html_e('CRM Memberships Subscription Information', 'sfs' ); ?></h1>
	<?php 
		global $wpdb; 
		$contactId = (!empty($user->ID))?get_user_meta($user->ID,'ntzcrm_contact_id',true):'';
		 
	?>
	<table class="form-table">
		<tr> 
		<td>
			<h3><?php echo esc_html_e("Contact Id",NTZCRMPRIFIX); ?></h3>
			<input type="text" id="ntzcrm_contact_id" name="ntzcrm_contact_id" value="<?php echo esc_attr( $contactId); ?>" class="regular-text"/>
		</td>
		<td>
			<div style="background-color: #eafdfd; padding: 10px; border: 1px solid #e4e4e4; margin:3em 10px 10px 10px; display: inline-block; font-size: 12px;">Contains corresponding Salesforce Customer ID .</div>
		</td>
		</tr>
		<tr>
		<td> <h3><?php esc_html_e("All-Access Mode",NTZCRMPRIFIX); ?></h3>
			<?php $all_access =(!empty($user->ID))?get_user_meta($user->ID,'all_access',true):''; ?>
			<label>
				<input type="checkbox" name="all_access"  id="all_access" class="ntzcrmcheckbox"  <?php if(!empty($all_access)&&$all_access=="yes"){ ?>  checked <?php }else{ ?> value="no" <?php } ?> /> Enable All-Access Mode for this user
			</label>  
		</td> 
		 <td>
			<div style="background-color: #eafdfd; padding: 10px; border: 1px solid #e4e4e4; margin: 10px; display: inline-block; font-size: 12px;">Enabling this option will allow this user to see all pages and disregard all conditional redirects.</div>
		</td>
		</tr>
		<tr> 
			<td>
				<h3><?php esc_html_e( 'Access Tags',NTZCRMPRIFIX); ?></h3>
			<?php  
			if(!empty($tags)){
				$html = '<p> 
				<select class="select2" id="ntzcrm_select2_tags" name="usertag[]" multiple="multiple" placeholder=
				"Please select at least one tag." >';
					foreach($tags as $tagId =>$tagName) { 
						$selected = (!empty($userTags)&&in_array($tagId, $userTags))?' selected="selected"':'';
						$html .= '<option value="' . $tagId . '"' . $selected . '>' .$tagName. '</option>';
					}
				
				$html .= '<select></p>';
				echo $html;
			}
			?> </td>
		</tr> 
	</table>
</div>

<div style="padding:20px;background-color: white;border:1px solid #80808036; margin-top: 20px; ">
	<h2 style="padding: 10px 0;border-width: 0 0 1px 0;border-style: solid;border-color: #444444;"><?php echo "User footprint by page/post"; ?></h2> 
	<div style="height: 400px; overflow-y: scroll;">
		<table class="form-table"> 
			<thead>
				<tr>
					<th><?php esc_html_e('Post/Page name',NTZCRMPRIFIX); ?></th>
					<th><?php esc_html_e('View Count',NTZCRMPRIFIX); ?></th>
					<th><?php esc_html_e('Last Access Time',NTZCRMPRIFIX); ?></th>
				</tr>
			</thead>
			<tbody>
				
					<?php
					if(!empty($user->ID)){
					$activites=ntzcrm_dbquery::_getUserActivites($user->ID);  
					 if(!empty($activites)){
					 	foreach ($activites as $key => $activite) { 
					 		if($activite->post_id){
					 		?>
					 		<tr>
							<td><a href="<?php echo esc_url(get_edit_post_link($activite->post_id)); ?>" title=""><?php esc_html_e( get_the_title($activite->post_id)."(".$activite->post_id.")"); ?></a> </td>
							<td><?php esc_html_e($activite->view_count,NTZCRMPRIFIX); ?></td>
							<td><?php echo date("F d, Y h:i:s A",strtotime($activite->modified)); ?></td>
							</tr>
						<?php } } ?>
					<?php }else{?>
						<tr>
						<td><?php esc_html_e('User not visited yet.',NTZCRMPRIFIX); ?></td>
						</tr>
					<?php }
					} ?>
			</tbody>
		</table>
	</div>		
</div>

<div style="padding:20px;background-color: white;border:1px solid #80808036; margin-top: 20px; ">
	<h2 style="padding: 10px 0;border-width: 0 0 1px 0;border-style: solid;border-color: #444444;"><?php echo "User login log"; ?></h2> 
	<div style="height: 400px; overflow-y: scroll;">
		<table class="form-table"> 
			<thead>
				<tr>
					<th>Login Time</th> 
				</tr>
			</thead>
			<tbody>
				
					<?php
					if(!empty($user->ID)){
					$logins=ntzcrm_dbquery::_getUserLoginLog($user->ID); 
					 if(!empty($logins)){
					 	foreach ($logins as $key => $loginTime) { ?>
					 		<tr>
								<td><?php echo date("F d, Y h:i:s A",strtotime($loginTime->login)); ?></td>
							</tr>
						<?php } ?>
					<?php }else{?>
						<tr>
							<td>User not login yet.</td>
						</tr>
					<?php }
					}
					?>
				</tr>
			</tbody>
		</table>				
	</div>

</div>
<script>
jQuery(document).ready(function($) {	
	$(".ntzcrmcheckbox").click(function(){
		if($(this).prop('checked') == true){
			$(this).val("yes");
		}else{
			$(this).val("no");
		}
	 }); 
}); 	
</script>