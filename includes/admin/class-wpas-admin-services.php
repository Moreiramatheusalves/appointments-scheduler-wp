<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Admin_Services
{

    public function render()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        if (
            isset($_GET['wpas_delete_service'], $_GET['_wpnonce']) &&
            wp_verify_nonce($_GET['_wpnonce'], 'wpas_delete_service_' . (int) $_GET['wpas_delete_service'])
        ) {

            $id = (int) $_GET['wpas_delete_service'];
            if ($id > 0) {
                WPAS_Model_Service::delete($id);
                echo '<div class="updated"><p>' . esc_html__('Serviço removido.', 'wp-appointments-scheduler') . '</p></div>';
            }
        }

        if (isset($_POST['wpas_service_nonce']) && wp_verify_nonce($_POST['wpas_service_nonce'], 'wpas_save_service')) {

            $id   = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            $data = array(
                'category_id' => $_POST['category_id'] ?? '',
                'name'        => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
                'price'       => $_POST['price'] ?? '',
                'duration'    => $_POST['duration'] ?? '',
                'active'      => isset($_POST['active']) ? 1 : 0,
            );

            if ($id > 0) {
                WPAS_Model_Service::update($id, $data);
                echo '<div class="updated"><p>' . esc_html__('Serviço atualizado com sucesso.', 'wp-appointments-scheduler') . '</p></div>';
            } else {
                WPAS_Model_Service::create($data);
                echo '<div class="updated"><p>' . esc_html__('Serviço criado com sucesso.', 'wp-appointments-scheduler') . '</p></div>';
            }
        }

        $categories = WPAS_Model_Category::get_all();
        $services   = WPAS_Model_Service::get_all();

        $editing_service = null;
        if (isset($_GET['edit'])) {
            $editing_service = WPAS_Model_Service::get((int) $_GET['edit']);
        }

        include WPAS_PLUGIN_DIR . 'includes/admin/views/view-services.php';
    }
}
