<?php
if (! defined('ABSPATH')) {
    exit;
}

require_once WPAS_PLUGIN_DIR . 'includes/helpers/class-wpas-helpers.php';
require_once WPAS_PLUGIN_DIR . 'includes/helpers/class-wpas-validator.php';

require_once WPAS_PLUGIN_DIR . 'includes/models/class-wpas-model-professional.php';
require_once WPAS_PLUGIN_DIR . 'includes/models/class-wpas-model-service.php';
require_once WPAS_PLUGIN_DIR . 'includes/models/class-wpas-model-category.php';
require_once WPAS_PLUGIN_DIR . 'includes/models/class-wpas-model-agenda.php';
require_once WPAS_PLUGIN_DIR . 'includes/models/class-wpas-model-appointment.php';

require_once WPAS_PLUGIN_DIR . 'includes/admin/class-wpas-admin.php';
require_once WPAS_PLUGIN_DIR . 'includes/public/class-wpas-public.php';
require_once WPAS_PLUGIN_DIR . 'includes/public/class-wpas-shortcodes.php';
require_once WPAS_PLUGIN_DIR . 'includes/public/class-wpas-ajax.php';

class WPAS_Plugin
{

    protected $loader;
    protected $plugin_name;
    protected $version;

    public function __construct()
    {
        $this->plugin_name = 'wp-appointments-scheduler';
        $this->version     = WPAS_VERSION;

        $this->load_dependencies();
        $this->set_locale();
        $this->define_admin_hooks();
        $this->define_public_hooks();
    }

    private function load_dependencies()
    {
        $this->loader = new WPAS_Loader();
    }

    private function set_locale()
    {
        $this->loader->add_action('plugins_loaded', $this, 'load_textdomain');
    }

    public function load_textdomain()
    {
        load_plugin_textdomain(
            'wp-appointments-scheduler',
            false,
            dirname(plugin_basename(WPAS_PLUGIN_FILE)) . '/languages/'
        );
    }

    private function define_admin_hooks()
    {
        $plugin_admin = new WPAS_Admin($this->plugin_name, $this->version);

        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_styles');
        $this->loader->add_action('admin_enqueue_scripts', $plugin_admin, 'enqueue_scripts');
        $this->loader->add_action('admin_menu', $plugin_admin, 'register_menus');
    }

    private function define_public_hooks()
    {
        $plugin_public   = new WPAS_Public($this->plugin_name, $this->version);
        $plugin_shortcodes = new WPAS_Shortcodes();
        $plugin_ajax     = new WPAS_Ajax();

        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_styles');
        $this->loader->add_action('wp_enqueue_scripts', $plugin_public, 'enqueue_scripts');

        $this->loader->add_action('init', $plugin_shortcodes, 'register_shortcodes');

        $this->loader->add_action('wp_ajax_wpas_get_booking_data', $plugin_ajax, 'get_booking_data');
        $this->loader->add_action('wp_ajax_nopriv_wpas_get_booking_data', $plugin_ajax, 'get_booking_data');

        $this->loader->add_action('wp_ajax_wpas_create_booking', $plugin_ajax, 'create_booking');
        $this->loader->add_action('wp_ajax_nopriv_wpas_create_booking', $plugin_ajax, 'create_booking');

        $this->loader->add_action('wp_ajax_wpas_get_available_slots', $plugin_ajax, 'get_available_slots');
        $this->loader->add_action('wp_ajax_nopriv_wpas_get_available_slots', $plugin_ajax, 'get_available_slots');
    }

    public function run()
    {
        $this->loader->run();
    }
}
