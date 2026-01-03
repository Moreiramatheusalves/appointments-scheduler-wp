<?php
if (! defined('ABSPATH')) {
    exit;
}

require_once WPAS_PLUGIN_DIR . 'includes/admin/class-wpas-admin-professionals.php';
require_once WPAS_PLUGIN_DIR . 'includes/admin/class-wpas-admin-services.php';
require_once WPAS_PLUGIN_DIR . 'includes/admin/class-wpas-admin-categories.php';
require_once WPAS_PLUGIN_DIR . 'includes/admin/class-wpas-admin-agenda.php';
require_once WPAS_PLUGIN_DIR . 'includes/admin/class-wpas-admin-appointments.php';
require_once WPAS_PLUGIN_DIR . 'includes/admin/class-wpas-admin-settings.php';

class WPAS_Admin
{

    private $plugin_name;
    private $version;

    private $page_professionals;
    private $page_services;
    private $page_categories;
    private $page_agenda;
    private $page_appointments;
    private $page_settings;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;

        $this->page_professionals = new WPAS_Admin_Professionals();
        $this->page_services      = new WPAS_Admin_Services();
        $this->page_categories    = new WPAS_Admin_Categories();
        $this->page_agenda        = new WPAS_Admin_Agenda();
        $this->page_appointments  = new WPAS_Admin_Appointments();
        $this->page_settings      = new WPAS_Admin_Settings();
    }

    public function enqueue_styles($hook)
    {
        if (strpos($hook, 'wpas-appointments') === false) {
            return;
        }

        $ver = $this->version;
        $file = WPAS_PLUGIN_DIR . 'assets/css/admin.css';
        if (file_exists($file)) {
            $ver = filemtime($file);
        }

        wp_enqueue_style(
            $this->plugin_name . '-admin',
            WPAS_PLUGIN_URL . 'assets/css/admin.css',
            array(),
            $ver
        );
    }

    public function enqueue_scripts($hook)
    {
        if (strpos($hook, 'wpas-appointments') === false) {
            return;
        }

        $ver = $this->version;
        $file = WPAS_PLUGIN_DIR . 'assets/js/admin.js';
        if (file_exists($file)) {
            $ver = filemtime($file);
        }

        wp_enqueue_script(
            $this->plugin_name . '-admin',
            WPAS_PLUGIN_URL . 'assets/js/admin.js',
            array('jquery'),
            $ver,
            true
        );
    }

    public function register_menus()
    {

        $capability = 'manage_options';

        $main_slug = 'wpas-appointments';

        add_menu_page(
            __('Agendamentos', 'wp-appointments-scheduler'),
            __('Agendamentos', 'wp-appointments-scheduler'),
            $capability,
            $main_slug,
            array($this->page_appointments, 'render'),
            'dashicons-calendar-alt'
        );

        add_submenu_page(
            $main_slug,
            __('Agendamentos', 'wp-appointments-scheduler'),
            __('Agendamentos', 'wp-appointments-scheduler'),
            $capability,
            $main_slug,
            array($this->page_appointments, 'render')
        );

        add_submenu_page(
            $main_slug,
            __('Profissionais', 'wp-appointments-scheduler'),
            __('Profissionais', 'wp-appointments-scheduler'),
            $capability,
            'wpas-professionals',
            array($this->page_professionals, 'render')
        );

        add_submenu_page(
            $main_slug,
            __('Serviços', 'wp-appointments-scheduler'),
            __('Serviços', 'wp-appointments-scheduler'),
            $capability,
            'wpas-services',
            array($this->page_services, 'render')
        );

        add_submenu_page(
            $main_slug,
            __('Categorias', 'wp-appointments-scheduler'),
            __('Categorias', 'wp-appointments-scheduler'),
            $capability,
            'wpas-categories',
            array($this->page_categories, 'render')
        );

        add_submenu_page(
            $main_slug,
            __('Agenda', 'wp-appointments-scheduler'),
            __('Agenda', 'wp-appointments-scheduler'),
            $capability,
            'wpas-agenda',
            array($this->page_agenda, 'render')
        );

        add_submenu_page(
            $main_slug,
            __('Configurações', 'wp-appointments-scheduler'),
            __('Configurações', 'wp-appointments-scheduler'),
            $capability,
            'wpas-settings',
            array($this->page_settings, 'render')
        );
    }
}
