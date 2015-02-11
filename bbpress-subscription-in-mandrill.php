<?php
/*
Plugin Name: bbPress - Subscription in Mandrill
Plugin URI: https://github.com/sudar/bbpress-subscription-in-mandrill/blob/master/bbpress-subscription-in-mandrill.php
Description: Allows you to send topic subscription emails from bbPress through wpMandrill
Plugin URI: http://sudarmuthu.com/wordpress/
Version: 0.1
Author: Sudar
Author URI: http://bulkwp.com
*/

/**
 * Send topic subscription emails from bbPress through wpMandrill
 *
 * Code explanation at http://bulkwp.com/blog/using-wpmandrill-to-send-subscription-notification-from-bbpress/
 *
 * @author Sudar
 */
function bbp_sim_process_bcc( $mail_info ) {
    // Let's go down the rabbit hole
    $trace  = debug_backtrace();
    $level  = 5;
    $function = $trace[$level]['function'];

    // Making a huge assumption here
    if ( $function == 'bbp_notify_subscribers' ) {
        $processed_to = array();

        // handle existing to field
        if ( array_key_exists( 'to', $mail_info ) ) {
            if( !is_array( $mail_info['to'] ) ) {
                $old_to = explode( ',', $mail_info['to'] );
            } else {
                $old_to = $mail_info['to'];
            }

            foreach ( $old_to as $email ) {
                if ( is_array($email) ) {
                    $processed_to[] = $email;
                } else {
                    $processed_to[] = array( 'email' => $email );
                }
            }
        }

        // process bcc fields present in header
        if ( array_key_exists( 'headers', $mail_info ) ) {
            $new_header = array();

            foreach ( $mail_info['headers'] as $header ) {
                $split = explode( ':', trim( $header, "\"'" ) );
                if ( 'bcc' == strtolower( $split[0] ) ) {
                    $processed_to[] = array( 'email' => $split[1], 'type' => 'bcc' );
                } else {
                    $new_header[] = $header;
                }
            }

            $mail_info['headers'] = $new_header;
        }

        $mail_info['to'] = $processed_to;

        // send separate emails
        $mail_info['preserve_recipients'] = false;
        error_log(var_export($mail_info, true));
    }

    return $mail_info;
}

add_filter( 'wp_mail', 'bbp_sim_process_bcc' );
