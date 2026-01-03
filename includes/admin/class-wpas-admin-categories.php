<?php
if (! defined('ABSPATH')) {
    exit;
}

class WPAS_Admin_Categories
{

    public function render()
    {
        if (! current_user_can('manage_options')) {
            return;
        }

        if (
            isset($_GET['wpas_delete_category'], $_GET['_wpnonce']) &&
            wp_verify_nonce($_GET['_wpnonce'], 'wpas_delete_category_' . (int) $_GET['wpas_delete_category'])
        ) {

            $id = (int) $_GET['wpas_delete_category'];
            if ($id > 0) {
                WPAS_Model_Category::delete($id);
                echo '<div class="updated"><p>' . esc_html__('Categoria removida.', 'wp-appointments-scheduler') . '</p></div>';
            }
        }

        if (isset($_POST['wpas_category_nonce']) && wp_verify_nonce($_POST['wpas_category_nonce'], 'wpas_save_category')) {
            $id   = isset($_POST['id']) ? (int) $_POST['id'] : 0;
            $data = array(
                'name'        => $_POST['name'] ?? '',
                'description' => $_POST['description'] ?? '',
            );

            if ($id > 0) {
                WPAS_Model_Category::update($id, $data);
                echo '<div class="updated"><p>' . esc_html__('Categoria atualizada com sucesso.', 'wp-appointments-scheduler') . '</p></div>';
            } else {
                WPAS_Model_Category::create($data);
                echo '<div class="updated"><p>' . esc_html__('Categoria criada com sucesso.', 'wp-appointments-scheduler') . '</p></div>';
            }
        }

        $categories       = WPAS_Model_Category::get_all();
        $editing_category = null;

        if (isset($_GET['edit'])) {
            $editing_category = WPAS_Model_Category::get((int) $_GET['edit']);
        }

        include WPAS_PLUGIN_DIR . 'includes/admin/views/view-categories.php';
    }
}
