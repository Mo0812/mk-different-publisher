<?php
/**
 * Plugin Name: Different Publisher Plugin
 * Plugin URI: http://moritzkanzler.de/
 * Description: Only other (authorized) user can publish your own posts and vice versa.
 * Version: 1.0
 * Author: Moritz Kanzler
 * Author URI: http://moritzkanzler.de
 * License: GPLv2
 */

function on_post_publish($new_status, $old_status, $post) {
    $post_author = $post->post_author;
    $current_user = wp_get_current_user();
    $current_user_id = $current_user->ID;

    if($current_user_id != 0 && $new_status == "publish") {
        if($current_user_id == $post_author) {
            $update_post_info = array(
                'ID'           => $post->ID,
                'post_status'   => 'draft',
            );
            wp_update_post( $update_post_info );

            wp_die("<b>Fehler:</b> Eigene Beiträge können nicht veröffentlicht werden. Bitte einen anderen Redakteur deinen Beitrag zu veröffentlichen.", "Veröffentlichen eigener Beiträge", array(
                'back_link' => true
            ));
        }
    }
}

add_action('transition_post_status', 'on_post_publish', 10, 3);