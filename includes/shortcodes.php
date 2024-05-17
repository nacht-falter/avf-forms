<?php

class Avf_Forms_Shortcodes {

    public static function register() {
        add_shortcode( 'avf_form', array( __CLASS__, 'render_form' ) );
    }

    public static function render_form() {
        ob_start();
        ?>
        <form method="post" action="">
            <label for="name">Name</label>
            <input type="text" name="name" id="name" required>

            <label for="email">Email</label>
            <input type="email" name="email" id="email" required>

            <label for="message">Message</label>
            <textarea name="message" id="message" required></textarea>

            <input type="submit" name="avf_form_submit" value="Submit">
        </form>
        <?php
        return ob_get_clean();
    }
}
