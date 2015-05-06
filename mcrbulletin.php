<?php
/**
 * Plugin Name: mcrbulletin
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Clare MCR Bulletin
 * Version: 0.0.1
 * Author: Richard Gunning
 * Author URI: http://rjgunning.com
 * Text Domain: Optional. Plugin's text domain for localization. Example: mytextdomain
 * Domain Path: Optional. Plugin's relative directory path to .mo files. Example: /locale/
 * Network: Optional. Whether the plugin can only be activated network wide. Example: true
 * License: MIT
 */

/*  The MIT License (MIT)

Copyright (c) 2015 Richard Gunning

Permission is hereby granted, free of charge, to any person obtaining a copy
of this software and associated documentation files (the "Software"), to deal
in the Software without restriction, including without limitation the rights
to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
copies of the Software, and to permit persons to whom the Software is
furnished to do so, subject to the following conditions:

The above copyright notice and this permission notice shall be included in all
copies or substantial portions of the Software.

THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
SOFTWARE.
*/

// If this file is called directly, abort.
if ( ! defined( 'WPINC' ) ) {
        die();
}

/** Step 1. */
function bulletin_plugin_menu() {
        add_menu_page( 'MCR Bulletin', 'MCR Bulletin', 'manage_options', 'clare-mcr-bulletin', 'bulletin_plugin_options', plugins_url('Files/favicon.ico', __FILE__ ) );
}
/** Step 2 (from text above). */
add_action( 'admin_menu', 'bulletin_plugin_menu' );

/** Step 3. */
function bulletin_plugin_options() {
	if ( !current_user_can( 'manage_options' ) )  {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}

	global $wpdb;

	echo '<div class="wrap">';
	echo '<img src="'.plugins_url('Files/logo.png',__FILE__ ).'" alt="Logo">';
	echo '<br style="clear:left;"/>';
	$date = new DateTime();
	$date->sub(new DateInterval('P'.(get_option('start_of_week')-1) .'D')); //Week Starts Monday
	$args = array(
		'category_name' => 'mcr-bulletin',
		'post_status'	=> 'publish',
		'date_query' => array(
			array(
				'year' => date( 'Y' ),
				'week' => $date->format('W')-1, //MYSQL starts from 0 and Sunday. View previous week
			),
		),
		'orderby' => 'date',
		'order' => 'ASC'
	);
	$query = new WP_Query( $args );
	$message='<ol>';
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) : $query->the_post();
			$message.='<li><h2><a href="#'. the_title_attribute('echo=0')  .'" rel="bookmark" title="Anchor Link to '. the_title_attribute('echo=0') .'"> '. get_the_title() .' </a></h2></li>';
		endwhile;
		$message.='</ol><hr>';
		$message2='<ol>';
		$query->rewind_posts();
		while ( $query->have_posts() ) : $query->the_post();
			$message2.='<li><a name="'. the_title_attribute('echo=0') .'"></a><h2><a href="'. get_the_permalink() .'" rel="bookmark" title="Permanent Link to '. the_title_attribute('echo=0').'">'. get_the_title().'</a></h2>';
			#$message2.='<li><a name="'. the_title_attribute('echo=0') .'"></a><h2>'. get_the_title() .'</h2>';
			$message2.= get_the_content() .' </li>';
		endwhile;
	endif;
	$message2.='</ol></div>';

	if(isset($_POST['submit'])){
		echo "<h3>Email Sent</h3> <br><hr>";
		echo '<img src="'.plugins_url('Files/logo.png',__FILE__ ).'" alt="Logo"><br>'.$_POST['header']."<hr>".$message.$message2 ."<br>";
		email_members('<img src="'.plugins_url('Files/logo.png',__FILE__ ).'" alt="Logo"><br>'.$_POST['header'].$message.$message2, $_POST['to'], $_POST['from']);
	} else {
		echo $message. "</div>";
	}
	?>
	<hr><form method="POST" id="usrform">
		<table>
		<tr><td>To:</td><td><input type="text" name="to" value="clare-mcr@lists.cam.ac.uk" style="width: 300px;" /></td></tr>
		<tr><td>From:</td><td><input type="text" name="from" value="mcr-secretary@clare.cam.ac.uk" style="width: 300px;" /></td></tr>
		<tr><td>Message:</td><td><textarea name="header" form="usrform" style="width: 300px;height:300px">
<p>Hi everyone,</p>
<p>Here is the Clare MCR Weekly Bulletin.</p>
<p>If you want to have something included in the next bulletin drop me an <a href="mailto:mcr-secretary@clare.cam.ac.uk">email</a>.</p>
<p>A new MCR Bulletin newsletters will be sent out every Thursday with the latest events. View the <a href="http://mcr.clare.cam.ac.uk/category/mcr-bulletin">website</a> to see the full list of bulletin items.</p>
Richard</textarea></td></tr>
		<tr><td><input type="submit" name="submit" value="Send Email"></td></tr></table>
	</form>
<?php }



function email_members($message, $to, $from)  {
        global $wpdb;
         // subject
        $subject = 'MCR Bulletin' .current_time('d-m-Y');

        // message

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Additional headers
        $headers .= 'From: Clare MCR secretary <'. $from. '>' . "\r\n";

    mail($to, $subject, $message, $headers);
    return TRUE;
}

//add_action('publish_post', 'email_members');
