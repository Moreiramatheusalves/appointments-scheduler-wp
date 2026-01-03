<div class="wrap">
    <h1><?php esc_html_e('Categorias de Serviços', 'wp-appointments-scheduler'); ?></h1>

    <h2><?php echo $editing_category ? esc_html__('Editar Categoria', 'wp-appointments-scheduler') : esc_html__('Adicionar Categoria', 'wp-appointments-scheduler'); ?></h2>

    <form method="post">
        <?php wp_nonce_field('wpas_save_category', 'wpas_category_nonce'); ?>
        <input type="hidden" name="id" value="<?php echo $editing_category ? esc_attr($editing_category->id) : 0; ?>">

        <table class="form-table">
            <tr>
                <th><label for="name"><?php esc_html_e('Nome', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <input type="text" name="name" id="name" class="regular-text"
                        value="<?php echo $editing_category ? esc_attr($editing_category->name) : ''; ?>" required>
                </td>
            </tr>
            <tr>
                <th><label for="description"><?php esc_html_e('Descrição', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <textarea name="description" id="description" rows="4" class="large-text"><?php
                                                                                                echo $editing_category ? esc_textarea($editing_category->description) : '';
                                                                                                ?></textarea>
                </td>
            </tr>
        </table>

        <?php submit_button($editing_category ? __('Atualizar Categoria', 'wp-appointments-scheduler') : __('Adicionar Categoria', 'wp-appointments-scheduler')); ?>
    </form>

    <hr>

    <h2><?php esc_html_e('Lista de Categorias', 'wp-appointments-scheduler'); ?></h2>
    <div class="wpas-table-scroll wpas-table-scroll--simple">
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Nome', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Descrição', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Ações', 'wp-appointments-scheduler'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($categories)) : ?>
                    <?php foreach ($categories as $cat) : ?>
                        <tr>
                            <td><?php echo esc_html($cat->id); ?></td>
                            <td><?php echo esc_html($cat->name); ?></td>
                            <td><?php echo esc_html($cat->description); ?></td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(array('page' => 'wpas-categories', 'edit' => $cat->id), admin_url('admin.php'))); ?>">
                                    <?php esc_html_e('Editar', 'wp-appointments-scheduler'); ?>
                                </a> |
                                <?php
                                $delete_url = wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'page'                => 'wpas-categories',
                                            'wpas_delete_category' => $cat->id,
                                        ),
                                        admin_url('admin.php')
                                    ),
                                    'wpas_delete_category_' . $cat->id
                                );
                                ?>
                                <a href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('<?php esc_attr_e('Tem certeza que deseja remover esta categoria?', 'wp-appointments-scheduler'); ?>');">
                                    <?php esc_html_e('Remover', 'wp-appointments-scheduler'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="4"><?php esc_html_e('Nenhuma categoria cadastrada.', 'wp-appointments-scheduler'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>