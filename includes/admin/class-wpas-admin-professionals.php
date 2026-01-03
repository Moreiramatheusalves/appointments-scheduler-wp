<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Admin_Professionals
{

    public function render()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        if (
            isset($_GET['wpas_delete_professional'], $_GET['_wpnonce']) &&
            wp_verify_nonce($_GET['_wpnonce'], 'wpas_delete_professional_' . (int) $_GET['wpas_delete_professional'])
        ) {
            $id = (int) $_GET['wpas_delete_professional'];

            if ($id > 0) {
                WPAS_Model_Professional::delete($id);
                echo '<div class="updated"><p>' . esc_html__('Profissional removido.', 'wp-appointments-scheduler') . '</p></div>';
            }
        }

        if (isset($_POST['wpas_professional_nonce']) && wp_verify_nonce($_POST['wpas_professional_nonce'], 'wpas_save_professional')) {

            $id   = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            $data = array(
                'name'   => $_POST['name'] ?? '',
                'email'  => $_POST['email'] ?? '',
                'phone'  => $_POST['phone'] ?? '',
                'active' => isset($_POST['active']) ? 1 : 0,
            );

            if ($id > 0) {
                WPAS_Model_Professional::update($id, $data);
                echo '<div class="updated"><p>' . esc_html__('Profissional atualizado com sucesso.', 'wp-appointments-scheduler') . '</p></div>';
            } else {
                WPAS_Model_Professional::create($data);
                echo '<div class="updated"><p>' . esc_html__('Profissional criado com sucesso.', 'wp-appointments-scheduler') . '</p></div>';
            }
        }

        $professionals = WPAS_Model_Professional::get_all();

        $editing_professional = null;
        if (isset($_GET['edit'])) {
            $editing_professional = WPAS_Model_Professional::get((int) $_GET['edit']);
        }

        include WPAS_PLUGIN_DIR . 'includes/admin/views/view-professionals.php';
    }
}
