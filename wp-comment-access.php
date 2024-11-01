<?php

/*
Plugin Name: WP_Comment_Access
Plugin URI: http://blog.mcshell.org/
Description: This plugin is used to specify the IP written comments, to reduce comment spam issues, can be used for SEO, managing multiple sites.Please commments.php added before "do_action ('comment_form_before')".By TCreate
Version: 1.1
Author: McShell
Author URI: http://blog.mcshell.org/
*/

add_option ( 'comment_form_access_list', '127.0.0.1' );

add_action ( 'comment_form_before', 'do_comment_form_access',5,0 );


if ( is_admin() ) add_action ('admin_menu', 'comment_form_access_menu');

function comment_form_access_menu(){

	add_options_page ('WP_Comment_Access', 'WP_Comment_Access', 'manage_options', 'WP_Comment_Access', 'comment_form_access_menu_page');

} 

function cfa_filter_list ($list) {

	$ips = array (); $noips = array (); $pips = explode (',', $list); 

	foreach ($pips as $pip) filter_var ($pip, FILTER_VALIDATE_IP) ? $ips[] = $pip : $noips[] = $pip;

	$out[] = implode (',', $ips); $out[] = implode (', ', $noips); return $out;

}

function comment_form_access_menu_page(){

	if ( !current_user_can ('manage_options'))

		wp_die ( __('You do not have sufficient permissions to access this page.') );

	if ( isset ($_POST['cfa_submit'])) {

		list($list,$discarted_inputs) = cfa_filter_list ($_POST['cfa_list']);

		if ( isset ($_POST['cfa_method_list'])) $pcfaml = $_POST['cfa_method_list'];

		if(!empty($pcfaml)){
		$set_cfa_method = 'list';

    }else{
   $set_cfa_method = 'none';
}



		update_option ('comment_form_access_method', $set_cfa_method);

		update_option ('comment_form_access_list', $list);

        }

	$cfa_method = get_option ('comment_form_access_method');

	echo '<div class="wrap"><h2>Comment Form Access</h2><br><form method="post" action="'.$_SERVER['REQUEST_URI'].'">';


      echo '<br><br><br><input type="checkbox" name="cfa_method_list" checked';

        if ( $cfa_method == "list" || $cfa_method == "all" ) echo ' checked'; echo '>';

        echo '&nbsp;List of IP addresses of hosts that can access to the comment form'."/&nbsp;Local ip :$_SERVER[REMOTE_ADDR]"."<br>";

        echo '<textarea style="width:670px;height:100px" name="cfa_list">'. get_option ('comment_form_access_list') 
.'</textarea>';

	echo '<br><br><input type="submit" name="cfa_submit" value="Submit"></form>';

	if ( !empty ($discarted_inputs)) echo "<br><b>Updated with discarded inputs from IP addresses list</b> : ". $discarted_inputs .".";

	echo "</div>";

}

function cfa_no_comment_form () { 
echo "You do not have permission to comment on";
global  $post;
$post->comment_status='closed';

}

function cfa_is_ipaddr_ok () { return (in_array ($_SERVER[REMOTE_ADDR], explode (',', trim (get_option ('comment_form_access_list'))))); }

function do_comment_form_access () {

	switch ( get_option ('comment_form_access_method')) {

		case "none": return; break;

		case "list": if (!cfa_is_ipaddr_ok ()) cfa_no_comment_form (); break;

		default: cfa_no_comment_form (); break;

}

}

	



?>
