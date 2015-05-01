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
        echo 'hi';
}

function email_members($post_ID)  {
    global $wpdb;
    
    mail("rjgunning@gmail.com", "New WordPress recipe online!", 'A new recipe have been published on http://www.wprecipes.com');
    return $post_ID;
}

add_action('publish_post', 'email_members');
