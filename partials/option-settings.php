<h1><?php echo esc_html(get_admin_page_title()); ?></h1>
<form name="lh_personal_welcome-backend_form" method="post" action="">
<?php wp_nonce_field( $this->namespace."-backend_nonce", $this->namespace."-backend_nonce", false ); ?>

<table class="form-table">
    
<tr valign="top">
<th scope="row"><label for="<?php echo $this->actionable_roles_field_name; ?>"><?php _e("Wednding a welcome to users given these roles;", $this->namespace ); ?></label></th>
<td><select multiple="multiple" name="<?php echo $this->actionable_roles_field_name; ?>[]" id="<?php echo $this->actionable_roles_field_name; ?>">

<?php

$roles =$this->return_roles();


foreach ($roles as $role ) {

?>
<option value="<?php echo $role['role']; ?>"  <?php if (is_array(get_option($this->actionable_roles_field_name)) and in_array($role['role'], get_option($this->actionable_roles_field_name))) { echo 'selected="selected"';  } ?>  ><?php echo $role['name']; ?></option>
<?php

}

?>

</select></td>
</tr>
<tr valign="top">
<th scope="row"><label for="<?php echo $this->welcome_notification; ?>-subject"><?php _e("Notification Subject;", $this->namespace ); ?></label></th>
<td><input type="text" name="<?php echo $this->welcome_notification; ?>-subject" id="<?php echo $this->welcome_notification; ?>-subject" value="<?php echo $welcome_notification_option['subject']; ?>" size="30" /></td>
</tr>

<tr valign="top">
<th scope="row"><label for="<?php echo $this->welcome_notification; ?>-message"><?php _e('Notification Message: ', $this->namespace); ?></label></th>
<td><?php $settings = array( 'media_buttons' => true, 'textarea_rows' => 10);
 wp_editor( $welcome_notification_option['message'], $this->welcome_notification.'-message', $settings); ?>
 <p>Available placeholders: %first_name% %last_name%, %bloginfo_name%, and %user_email% </p>
</td>
</tr>
</table>

<?php submit_button( 'Save Changes' ); ?>
</form>