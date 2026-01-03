<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Shortcodes
{

    public function register_shortcodes()
    {
        add_shortcode('wpas_booking', array($this, 'render_booking_shortcode'));
    }

    public function render_booking_shortcode($atts)
    {
        ob_start();
        include WPAS_PLUGIN_DIR . 'includes/public/views/view-booking-wizard.php';
        return ob_get_clean();
    }
}
