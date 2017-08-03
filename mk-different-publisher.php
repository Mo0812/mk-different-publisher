<?php
/**
 * Plugin Name: MK Different Publisher Plugin (dev)
 * Plugin URI:
 * Description: Only other (authorized) user can publish your own posts and vice versa.
 * Version: 1.0
 * Author: Moritz Kanzler
 * Author URI: http://moritzkanzler.de
 * License: GPLv2 or later
 */

function mk_register_settings() {
    add_settings_section(
        'mk_different_publisher_general_options',
        'Different Publisher Einstellungen',
        '',
        'writing'
    );

    add_settings_field(
        'mk_dpp_update_posts_allowed',
        'Post Update für eigene Beiträge erlauben?',
        'mk_register_update_post_allowed_callback',
        'writing',
        'mk_different_publisher_general_options',
        array()
    );


    register_setting(
        'writing',
        'mk_dpp_update_posts_allowed'
    );
}

function mk_register_update_post_allowed_callback($args) {
    $html = '<input type="checkbox" id="mk_dpp_update_posts_allowed" name="mk_dpp_update_posts_allowed" value="1" ' . checked(1, get_option('mk_dpp_update_posts_allowed'), false) . '/>';
    echo $html;
}
add_action('admin_init', 'mk_register_settings');

function mk_hide_publish_button($post) {
    $post_author = $post->post_author;
    $current_user = wp_get_current_user();
    $current_user_id = $current_user->ID;

    $update_allowed = get_option('mk_dpp_update_posts_allowed') == 1 ? true : false;

    if($current_user_id != 0 && $post_author == $current_user_id && !$update_allowed) {
        echo '<script type="text/javascript">';
            echo '
                document.addEventListener("DOMContentLoaded", function(event) { 
                    document.getElementById("publish").disabled = true
                });';
        echo '</script>';
    }
}
add_action('edit_form_after_title', 'mk_hide_publish_button');

function on_post_publish($new_status, $old_status, $post) {
    $post_author = $post->post_author;
    $current_user = wp_get_current_user();
    $current_user_id = $current_user->ID;

    $update_allowed = get_option('mk_dpp_update_posts_allowed') == 1 ? true : false;

    if($current_user_id != 0 && $new_status == "publish") {
        if($current_user_id == $post_author) {
            if($old_status == "publish" && $update_allowed) {

            } else {
                $update_post_info = array(
                    'ID' => $post->ID,
                    'post_status' => 'draft',
                    'post_date_gmt' => '0000-00-00',
                );
                wp_update_post($update_post_info);

                wp_die("Eigene Beiträge können nicht veröffentlicht werden. Bitte einen anderen Redakteur deinen Beitrag zu veröffentlichen.", "Veröffentlichen eigener Beiträge", array(
                    'back_link' => true
                ));
            }
        }
    }
}
add_action('transition_post_status', 'on_post_publish', 10, 3);