<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Public
{

    private $plugin_name;
    private $version;

    public function __construct($plugin_name, $version)
    {
        $this->plugin_name = $plugin_name;
        $this->version     = $version;
    }

    public function enqueue_styles()
    {
        $ver = $this->version;
        $file = WPAS_PLUGIN_DIR . 'assets/css/public.css';
        if (file_exists($file)) {
            $ver = filemtime($file);
        }

        wp_enqueue_style(
            $this->plugin_name . '-public',
            WPAS_PLUGIN_URL . 'assets/css/public.css',
            array(),
            $ver
        );
    }

    public function enqueue_scripts()
    {
        $public_ver = $this->version;
        $public_file = WPAS_PLUGIN_DIR . 'assets/js/public.js';
        if (file_exists($public_file)) {
            $public_ver = filemtime($public_file);
        }

        $wizard_ver = $this->version;
        $wizard_file = WPAS_PLUGIN_DIR . 'assets/js/booking-wizard.js';
        if (file_exists($wizard_file)) {
            $wizard_ver = filemtime($wizard_file);
        }

        wp_enqueue_script(
            $this->plugin_name . '-public',
            WPAS_PLUGIN_URL . 'assets/js/public.js',
            array('jquery'),
            $public_ver,
            true
        );

        wp_enqueue_script(
            $this->plugin_name . '-booking-wizard',
            WPAS_PLUGIN_URL . 'assets/js/booking-wizard.js',
            array('jquery', $this->plugin_name . '-public'),
            $wizard_ver,
            true
        );

        wp_localize_script(
            $this->plugin_name . '-booking-wizard',
            'WPAS_Booking',
            array(
                'ajax_url' => admin_url('admin-ajax.php'),
                'nonce'    => wp_create_nonce('wpas_booking_nonce'),

                'i18n'     => array(
                    'loading'        => __('Carregando...', 'wp-appointments-scheduler'),
                    'select'         => __('Selecione', 'wp-appointments-scheduler'),
                    'select_time'    => __('Selecione um horário', 'wp-appointments-scheduler'),
                    'error_generic'  => __('Ocorreu um erro. Tente novamente.', 'wp-appointments-scheduler'),
                    'error_required' => __('Preencha os campos obrigatórios.', 'wp-appointments-scheduler'),
                    'success'        => __('Agendamento realizado com sucesso!', 'wp-appointments-scheduler'),
                ),
            )
        );
    }
}
