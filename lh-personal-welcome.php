<?php
/**
 * Plugin Name: LH Personal Welcome
 * Plugin URI: https://lhero.org/portfolio/lh-personal-welcome/
 * Description: Send personalised welcome messages to users who have been allocated a certain role
 * Version: 1.00
 * Author: Peter Shaw
 * Author URI: https://shawfactor.com/
 * Tags: buddypress, welcome, email, personal, greeting
*/


if (!class_exists('LH_Personal_welcome_plugin')) {
    
    


class LH_Personal_welcome_plugin {
    
    
    var $filename;
    
    var $namespace = 'lh_personal_welcome';
    
    var $actionable_roles_field_name = 'lh_personal_welcome-action_roles';
    var $welcome_notification = 'lh_personal_welcome-welcome_notification';
    
    private static $instance;
    
    
private function return_roles() {
    $editable_roles = get_editable_roles();
    foreach ($editable_roles as $role => $details) {
        $sub['role'] = esc_attr($role);
        $sub['name'] = translate_user_role($details['name']);
        $roles[] = $sub;
    }
    return $roles;
}

private function maybe_email_user($user_id, $role){
    
if (is_array(get_option($this->actionable_roles_field_name)) and in_array($role, get_option($this->actionable_roles_field_name))) {
    

  
//this checks if the welcome has already been sent  
$sent = get_user_meta($user_id, $this->namespace.'-sent_check', true);



if (!isset($sent) or empty($sent)){
    
$userdata = get_user_by( 'ID', $user_id );

$welcome_notification_option = get_option($this->welcome_notification);

$subject = $this->personalise_message($welcome_notification_option['subject'], $userdata);

$message = wpautop(do_shortcode($welcome_notification_option['message']));
$message = $this->personalise_message($message, $userdata);
$message = $this->use_email_template($subject, $message);

wp_mail($userdata->user_email, $subject, $message, array('Content-Type: text/html; charset=UTF-8'));

//the welcome has been sent so we dont want to send it again
add_user_meta( $user_id, $this->namespace.'-sent_check', true, true );

}

}
    
}

private function use_email_template( $subject, $message ) {

if (file_exists(get_stylesheet_directory().'/'.$this->namespace.'-template.php')){

ob_start();

include( get_stylesheet_directory().'/'.$this->namespace.'-template.php');

$message = ob_get_contents();

ob_end_clean();


} else {

ob_start();

include( plugin_dir_path( __FILE__ ).'/'.$this->namespace.'-template.php');

$message = ob_get_contents();

ob_end_clean();


}


if (!class_exists('LH_Css_To_Inline_Styles')) {


require_once('includes/lh-css-to-inline-styles-class.php');


}


$doc = new DOMDocument();

$doc->loadHTML($message);

// create instance
$lh_css_to_inline_styles = new LH_Css_To_Inline_Styles();

$lh_css_to_inline_styles->setHTML($message);

$lh_css_to_inline_styles->setCSS($doc->getElementsByTagName('style')->item(0)->nodeValue);

// output

$message = $lh_css_to_inline_styles->convert(); 

return $message;

}

private function personalise_message($message, $user){

$message = str_replace('%first_name%', $user->first_name, $message);
$message = str_replace('%last_name%', $user->last_name, $message);
$message = str_replace('%user_email%', $user->user_email, $message);
$message = str_replace('%user_login%', $user->user_login, $message);
$message = str_replace('%bloginfo_name%',get_bloginfo('name','display'), $message);

return $message;

}
  

    
public function plugin_menu() {
add_options_page(__('LH Personal Welcome Options', $this->namespace ), __('Personal Welcome', $this->namespace ), 'manage_options', $this->filename, array($this,"plugin_options"));

}

public function plugin_options() {

if (!current_user_can('manage_options'))  {
		wp_die( __('You do not have sufficient permissions to access this page.') );
	}
	
		if( isset($_POST[ $this->namespace."-backend_nonce" ]) && wp_verify_nonce($_POST[ $this->namespace."-backend_nonce" ], $this->namespace."-backend_nonce" )) {
		    
		    
if (isset($_POST[$this->actionable_roles_field_name])){

//need to add some appropriate sanity check here

$actionable_roles_field_name_add = $_POST[ $this->actionable_roles_field_name ];


if (update_option( $this->actionable_roles_field_name, $actionable_roles_field_name_add  )){




?>
<div class="updated"><p><strong><?php _e('Roles Saved', $this->namespace ); ?></strong></p></div>
<?php

}

}
		    
		    if ($_POST[ $this->welcome_notification.'-subject'] != ""){
    
    $welcome_notification_add['subject'] = sanitize_text_field($_POST[ $this->welcome_notification.'-subject']);
    
}

if ($_POST[ $this->welcome_notification.'-message'] != ""){
    
    $welcome_notification_add['message'] = $_POST[ $this->welcome_notification.'-message'];
    
}

if (update_option( $this->welcome_notification, $welcome_notification_add, false )){





?>
<div class="updated"><p><strong><?php _e('Welcome Notification updated', $this->namespace ); ?></strong></p></div>
<?php

    } 
		    
		    
}

$welcome_notification_option = get_option($this->welcome_notification);
	
	
// Now display the settings editing screen

include ('partials/option-settings.php');
	
	
}

public function add_user_role($user_id, $role){
    
$this->maybe_email_user($user_id, $role);    
    
    
}

public function set_user_role($user_id, $role, $old_roles){
    

    $this->maybe_email_user($user_id, $role);     
    
    
}



    
    
    /**
     * Gets an instance of our plugin.
     *
     * using the singleton pattern
     */
    public static function get_instance(){
        if (null === self::$instance) {
            self::$instance = new self();
        }
 
        return self::$instance;
    }
    
    public function __construct() {
        
        $this->filename = plugin_basename( __FILE__ );
        
    //provide options for the plugin
    add_action('admin_menu', array($this,"plugin_menu"));
    
   //send welcome when a role has beeen added
    add_action('add_user_role', array($this,"add_user_role"),10,2);
    
    //send welcome when a role has been set
    add_action('set_user_role', array($this,"set_user_role"),10,3);
    
        
    }
    
    
}

$lh_peronal_welcome_instance = LH_Personal_welcome_plugin::get_instance();

}

?>