<?php
/**
 * Plugin Name: mcrbulletin
 * Plugin URI: http://github.com/Clare-MCR/mcrbulletin
 * Description: Clare MCR Bulletin
 * Version: 1.1.0
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
function mcrbulletin_plugin_menu() {
	add_menu_page( 'MCR Bulletin', 'MCR Bulletin', 'manage_options', 'clare-mcr-bulletin', 'mcrbulletin_frontend', plugins_url( 'Files/favicon.ico', __FILE__ ) );
}

/** Step 2 (from text above). */
add_action( 'admin_menu', 'mcrbulletin_plugin_menu' );


function mcrbulletin_frontend() {
	if ( ! current_user_can( 'manage_options' ) ) {
		wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
	}
	list ( $postlist, $message_long ) = mcrbulletin_getposts();
	echo '<div class="wrap">';
	echo '<img src="' . plugins_url( 'Files/logo.png', __FILE__ ) . '" alt="Logo">';
	echo '<br style="clear:left;"/>';
	echo $postlist . "</div>";

	$editor_id   = "mcrbulletin_header";
	$settings = array(
		'wpautop'          => true,
		// Whether to use wpautop for adding in paragraphs. Note that the paragraphs are added automatically when wpautop is false.
		'media_buttons'    => true,
		// Whether to display media insert/upload buttons
		'textarea_name'    => $editor_id,
		// The name assigned to the generated textarea and passed parameter when the form is submitted.
		'textarea_rows'    => get_option( 'default_post_edit_rows', 10 ),
		// The number of rows to display for the textarea
		'tabindex'         => '',
		// The tabindex value used for the form field
		'editor_css'       => '',
		// Additional CSS styling applied for both visual and HTML editors buttons, needs to include <style> tags, can use "scoped"
		'editor_class'     => '',
		// Any extra CSS Classes to append to the Editor textarea
		'teeny'            => false,
		// Whether to output the minimal editor configuration used in PressThis
		'dfw'              => false,
		// Whether to replace the default fullscreen editor with DFW (needs specific DOM elements and CSS)
		'tinymce'          => true,
		// Load TinyMCE, can be used to pass settings directly to TinyMCE using an array
		'quicktags'        => true,
		// Load Quicktags, can be used to pass settings directly to Quicktags using an array. Set to false to remove your editor's Visual and Text tabs.
		'drag_drop_upload' => true
		// Enable Drag & Drop Upload Support (since WordPress 3.9)
	);
	$content  = "";
	include( "Templates/defaultEmail.php" );
	?>
    <br>
    <table>
        <tr>
            <td><label for="mcrbulletin_to">To:</label></td>
            <td><input type="text" id="mcrbulletin_to" name="mcrbulletin_to" value="clare-mcr@lists.cam.ac.uk"
                       style="width: 300px;"/></td>
        </tr>
        <tr>
            <td><label for="mcrbulletin_from">From:</label></td>
            <td><input type="text" id="mcrbulletin_from" name="mcrbulletin_from" value="mcr-secretary@clare.cam.ac.uk"
                       style="width: 300px;"/></td>
        </tr>
    </table>
	<?php
	wp_editor( $content, $editor_id, $settings );
	submit_button( 'Submit' );
	// add javaScript to handle the submit button click,
	// and send the form data to WP backend,
	// then refresh on success.
	?>
    <script>
        (function ($) {
            $('#submit').on('click', function (e) {
                var content = $('#mcrbulletin_header').val();
                var to = $('#mcrbulletin_to').val();
                var from = $('#mcrbulletin_from').val();
                $.post('<?php echo get_admin_url( null, '/admin-post.php' ) ?>',
                    {
                        action: 'mcrbulletin',
                        content: content,
                        to: to,
                        from: from
                    },
                    function (response) {

                        // looks good
                        console.log(response);

                        // reload the latest content
                        window.location.reload();
                    });
            });
        })(jQuery);
    </script>
	<?php
}

function mcrbulletin_getposts() {
	global $wpdb;
	$message  = "";
	$message2 = "";
	$now      = new DateTime();
//	$date->sub(new DateInterval('P'.(get_option('start_of_week')-1) .'D')); //Week Starts Monday
	$date = new DateTime();
	$date->setTimestamp( mktime( 0, 0, 0, date( "m" ), date( "d" ) - 7, date( "Y" ) ) );
	$args   = array(
		'category_name'  => 'mcr-bulletin',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'orderby'        => 'date',
		'order'          => 'ASC',
		'date_query'     => array(
			'after' => array(
				'year'  => $date->format( 'Y' ),
				'month' => $date->format( 'm' ),
				'day'   => $date->format( 'd' )
			)
		)
	);
	$args2  = array(
		'category_name'  => 'mcr-bulletin',
		'post_status'    => 'publish',
		'posts_per_page' => - 1,
		'orderby'        => 'date',
		'order'          => 'ASC',
		'meta_query'     => array(
			'relation' => 'AND',
			array( 'key' => 'end_date', 'value' => $now->format( "Ymd" ), 'type' => 'NUMERIC', 'compare' => '>' ),
			array( 'key' => 'repeat_post', 'value' => 1, 'type' => 'NUMERIC', 'compare' => '=' )
		),
		'date_query'     => array(
			'before' => array(
				'year'  => $date->format( 'Y' ),
				'month' => $date->format( 'm' ),
				'day'   => $date->format( 'd' )
			)
		)

	);
	$query  = new WP_Query( $args );
	$query2 = new WP_Query( $args2 );
	if ( $query->have_posts() || $query2->have_posts() ) :
		$message  = '<ol>';
		$message2 = '<ol>';
		while ( $query->have_posts() ) : $query->the_post();
			$content  = apply_filters( 'the_content', get_the_content() );
			$content  = str_replace( ']]>', ']]&gt;', $content );
			$message  .= '<li><h2><a href="#' . preg_replace( '/\s+/', '', the_title_attribute( 'echo=0' ) ) . '" rel="bookmark" title="Anchor Link to ' . the_title_attribute( 'echo=0' ) . '"> ' . get_the_title() . ' </a></h2></li>';
			$message2 .= '<li><a name="' . preg_replace( '/\s+/', '', the_title_attribute( 'echo=0' ) ) . '"></a><h2><a href="' . get_the_permalink() . '" rel="bookmark" title="Permanent Link to ' . the_title_attribute( 'echo=0' ) . '">' . get_the_title() . '</a></h2>';
			$message2 .= $content . ' </li>';
		endwhile;
		while ( $query2->have_posts() ) : $query2->the_post();
			$content  = apply_filters( 'the_content', get_the_content() );
			$content  = str_replace( ']]>', ']]&gt;', $content );
			$message  .= '<li><h2><a href="#' . preg_replace( '/\s+/', '', the_title_attribute( 'echo=0' ) ) . '" rel="bookmark" title="Anchor Link to ' . the_title_attribute( 'echo=0' ) . '"> ' . get_the_title() . ' </a></h2></li>';
			$message2 .= '<li><a name="' . preg_replace( '/\s+/', '', the_title_attribute( 'echo=0' ) ) . '"></a><h2><a href="' . get_the_permalink() . '" rel="bookmark" title="Permanent Link to ' . the_title_attribute( 'echo=0' ) . '">' . get_the_title() . '</a></h2>';
			$message2 .= $content . ' </li>';
		endwhile;
		$message  .= '</ol><hr>';
		$message2 .= '</ol></div>';
	endif;

	return array( $message, $message2 );
}


add_action( 'admin_post_mcrbulletin', 'mcrbulletin_admin_submit' );

function mcrbulletin_admin_submit() {

	status_header( 200 );
	list ( $postlist, $message_long ) = mcrbulletin_getposts();

	$content = wp_kses_post( $_POST['content'] );
	$from    = sanitize_email( $_POST['from'] );
	$to      = sanitize_email( $_POST['to'] );

	$message = '<img src="' . plugins_url( 'Files/logo.png', __FILE__ ) . '" alt="Logo">';
	$message .= '<br>' . $content . $postlist . $message_long;
	mcrbulletin_email_members( $message, $to, $from );
	die( "Email Sent" );
}


function mcrbulletin_email_members( $message, $to, $from ) {
	// subject
	$subject = 'MCR Bulletin ' . current_time( 'd-m-Y' );

	// message
	$headers = "MIME-Version: 1.0" . "\r\n";
	$headers .= "Content-type: text/html; charset=UTF-8" . "\r\n";

	// Additional headers
	$headers .= "From: Clare MCR secretary <" . $from . ">\r\n";

	wp_mail( $to, $subject, $message, $headers );

	return true;
}

//add_action('publish_post', 'email_members');
