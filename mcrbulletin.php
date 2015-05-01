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
         // subject
        $subject = 'Birthday Reminders for August';

        // message
        $message = '
        <html>
        <head>
          <title>Birthday Reminders for August</title>
        </head>
        <body>
          <p>Here are the birthdays upcoming in August!</p>
          <table>
            <tr>
              <th>Person</th><th>Day</th><th>Month</th><th>Year</th>
            </tr>
            <tr>
              <td>Joe</td><td>3rd</td><td>August</td><td>1970</td>
            </tr>
            <tr>
              <td>Sally</td><td>17th</td><td>August</td><td>1973</td>
            </tr>
          </table>
        </body>
        </html>
        ';

        $headers  = 'MIME-Version: 1.0' . "\r\n";
        $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

        // Additional headers
        $headers .= 'From: Clare MCR secretary <mcr-secretary@clare.cam.ac.uk>' . "\r\n";
        
    mail("rjgunning@gmail.com", $Subject, $message, $headers);
    return $post_ID;
}

add_action('publish_post', 'email_members');
