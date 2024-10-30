<?php  if (!is_user_logged_in() || (is_user_logged_in()&&!current_user_can('administrator'))) {  ?>

<?php esc_html_e('Welcome message for Visitor.'); ?>
<?php }else{ ?>
    <?php esc_html_e('Publication button and instractions for administrator.'); ?>
<?php }  ?>