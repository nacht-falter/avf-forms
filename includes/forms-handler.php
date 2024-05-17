<?php

class Avf_Forms_Handler {

    public static function register() {
        add_action( 'init', array( __CLASS__, 'handle_form_submission' ) );
    }

    public static function handle_form_submission() {
        if ( isset( $_POST['avf_form_submit'] ) ) {
            global $wpdb;
            $table_name = $wpdb->prefix . 'avf_form_entries';

            $name = sanitize_text_field( $_POST['name'] );
            $email = sanitize_email( $_POST['email'] );
            $message = sanitize_textarea_field( $_POST['message'] );

            $wpdb->insert(
                $table_name,
                array(
                    'name' => $name,
                    'email' => $email,
                    'message' => $message
                )
            );
        }
    }
}
