<?php
/**
 * Plugin Name: mcrbulletin
 * Plugin URI: http://URI_Of_Page_Describing_Plugin_and_Updates
 * Description: Clare MCR Bulletin
 * Version: 0.0.1
 * Author: Richard Gunning
 * Author URI: http://rjgunning.com
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
				'week' => $date->format('W')-2, //MYSQL starts from 0 and Sunday. View previous week
			),
		),
		'orderby' => 'date',
		'order' => 'ASC'
	);
	$query = new WP_Query( $args );
	$message='<ol>';
	$message2='<ol>';
	if ( $query->have_posts() ) :
		while ( $query->have_posts() ) : $query->the_post();
			$content = apply_filters( 'the_content', get_the_content() );
			$content = str_replace( ']]>', ']]&gt;', $content );
			$message.='<li><h2><a href="#'. the_title_attribute('echo=0')  .'" rel="bookmark" title="Anchor Link to '. the_title_attribute('echo=0') .'"> '. get_the_title() .' </a></h2></li>';
			$message2.='<li><a name="'. the_title_attribute('echo=0') .'"></a><h2><a href="'. get_the_permalink() .'" rel="bookmark" title="Permanent Link to '. the_title_attribute('echo=0').'">'. get_the_title().'</a></h2>';
			$message2.= $content .' </li>';
		endwhile;
		$message.='</ol><hr>';
		$message2.='</ol></div>';
	endif;

	if(isset($_POST['submit'])){
		echo "<h3>Email Sent</h3> <br><hr>";
		echo '<img src="'.plugins_url('Files/logo.png',__FILE__ ).'" alt="Logo"><br>'.strip_tags($_POST['header'])."<hr>".$message.$message2 ."<br>";
		email_members('<img src="'.plugins_url('Files/logo.png',__FILE__ ).'" alt="Logo"><br>'.strip_tags($_POST['header']).$message.$message2, strip_tags($_POST['to']), strip_tags($_POST['from']));
	} else {
		echo $message. "</div>";
	}
	?>
	<hr><form method="POST" id="usrform">
		<table>
		<tr><td>To:</td><td><input type="text" name="to" value="clare-mcr@lists.cam.ac.uk" style="width: 300px;" /></td></tr>
		<tr><td>From:</td><td><input type="text" name="from" value="mcr-secretary@clare.cam.ac.uk" style="width: 300px;" /></td></tr>
		<tr><td>Message:</td><td><textarea name="header" form="usrform" style="width: 300px;height:300px">
<p>Dear Clare MCR,</p>
<p>The Weekly Clare MCR Bulletin has been included below.</p>
<p>If you want to have something included in the next bulletin drop me an <a href="mailto:mcr-secretary@clare.cam.ac.uk">email</a>.</p>
<p>A new MCR Bulletin newsletter will be sent out every Thursday with the latest events. View the <a href="http://mcr.clare.cam.ac.uk/category/mcr-bulletin">website</a> to see the full list of bulletin items.</p>

<p>Kind Regards,<br>
Richard Gunning<br>
Clare MCR Secretary</p>

<hr><p style="font-size: smaller;">Clare College MCR hold no responsibility for the content of Bulletin items, see <a href="http://mcr.clare.cam.ac.uk/disclaimer">disclaimer</a>. The MCR bulletin is an unmoderated news feed of adverts sent to us to display. The Clare College MCR does not control, monitor or guarantee the information contained in the MCR bulletin or information contained in links to other external websites, and does not endorse any views expressed or products or services offered therein. All items on the MCR bulletin are viewable via the MCR website, social media and email as part of an effort to engage the broader Cambridge community. If you take offence with any item, please contact us by <a href="mailto:mcr-secretary@clare.cam.ac.uk">email</a> and we will endeavour to remove or edit the item to remove offence after appropriate contact the relevant advertising parties.</p><hr></textarea></td></tr>
		<tr><td><input type="submit" name="submit" value="Send Email"></td></tr></table>
	</form>
<?php }



function email_members($message, $to, $from)  {
        global $wpdb;
         // subject
        $subject = 'MCR Bulletin ' .current_time('d-m-Y');

        // message
        $headers  = "MIME-Version: 1.0" . "\r\n"; 
        $headers .= "Content-type: text/html; charset=UTF-8" . "\r\n"; 

        // Additional headers
        $headers .= "From: Clare MCR secretary <". $from. ">\r\n";

    mail($to, $subject, $message, $headers);
    return TRUE;
}

//add_action('publish_post', 'email_members');
