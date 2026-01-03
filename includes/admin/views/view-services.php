<div class="wrap">
    <h1><?php esc_html_e('Serviços', 'wp-appointments-scheduler'); ?></h1>

    <h2><?php echo $editing_service ? esc_html__('Editar Serviço', 'wp-appointments-scheduler') : esc_html__('Adicionar Serviço', 'wp-appointments-scheduler'); ?></h2>

    <form method="post">
        <?php wp_nonce_field('wpas_save_service', 'wpas_service_nonce'); ?>

        <input type="hidden" name="id" value="<?php echo $editing_service ? esc_attr($editing_service->id) : 0; ?>">

        <table class="form-table">
            <tr>
                <th><label for="name"><?php esc_html_e('Nome', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <input type="text" name="name" id="name" class="regular-text"
                        value="<?php echo $editing_service ? esc_attr($editing_service->name) : ''; ?>" required>
                </td>
            </tr>

            <tr>
                <th><label for="category_id"><?php esc_html_e('Categoria', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <select name="category_id" id="category_id">
                        <option value=""><?php esc_html_e('Sem categoria', 'wp-appointments-scheduler'); ?></option>
                        <?php if (! empty($categories)) : ?>
                            <?php foreach ($categories as $cat) : ?>
                                <option value="<?php echo esc_attr($cat->id); ?>"
                                    <?php selected($editing_service ? $editing_service->category_id : '', $cat->id); ?>>
                                    <?php echo esc_html($cat->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="price"><?php esc_html_e('Preço', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <input type="text" name="price" id="price" class="small-text"
                        value="<?php echo $editing_service ? esc_attr($editing_service->price) : ''; ?>">
                    <span class="description"><?php esc_html_e('Use ponto ou vírgula. Ex: 100,00', 'wp-appointments-scheduler'); ?></span>
                </td>
            </tr>

            <tr>
                <th><label for="duration"><?php esc_html_e('Duração (minutos)', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <input type="number" name="duration" id="duration" class="small-text"
                        value="<?php echo $editing_service ? esc_attr($editing_service->duration) : 30; ?>">
                </td>
            </tr>

            <tr>
                <th><label for="description"><?php esc_html_e('Descrição', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <textarea name="description" id="description" rows="5" class="large-text"><?php
                                                                                                echo $editing_service ? esc_textarea($editing_service->description) : '';
                                                                                                ?></textarea>
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Ativo', 'wp-appointments-scheduler'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="active"
                            <?php checked($editing_service ? $editing_service->active : 1, 1); ?>>
                        <?php esc_html_e('Sim', 'wp-appointments-scheduler'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <?php submit_button($editing_service ? __('Atualizar Serviço', 'wp-appointments-scheduler') : __('Adicionar Serviço', 'wp-appointments-scheduler')); ?>
    </form>

    <hr>

    <h2><?php esc_html_e('Lista de Serviços', 'wp-appointments-scheduler'); ?></h2>

    <div class="wpas-table-scroll wpas-table-scroll--simple">
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Nome', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Categoria', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Preço', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Duração', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Ativo', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Ações', 'wp-appointments-scheduler'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($services)) : ?>
                    <?php
                    $cats_index = array();
                    if (! empty($categories)) {
                        foreach ($categories as $cat) {
                            $cats_index[$cat->id] = $cat->name;
                        }
                    }
                    ?>
                    <?php foreach ($services as $service) : ?>
                        <tr>
                            <td><?php echo esc_html($service->id); ?></td>
                            <td><?php echo esc_html($service->name); ?></td>
                            <td>
                                <?php
                                if ($service->category_id && isset($cats_index[$service->category_id])) {
                                    echo esc_html($cats_index[$service->category_id]);
                                } else {
                                    esc_html_e('Sem categoria', 'wp-appointments-scheduler');
                                }
                                ?>
                            </td>
                            <td><?php echo esc_html(WPAS_Helpers::format_price($service->price)); ?></td>
                            <td><?php echo esc_html($service->duration); ?> min</td>
                            <td><?php echo $service->active ? esc_html__('Sim', 'wp-appointments-scheduler') : esc_html__('Não', 'wp-appointments-scheduler'); ?></td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(array('page' => 'wpas-services', 'edit' => $service->id), admin_url('admin.php'))); ?>">
                                    <?php esc_html_e('Editar', 'wp-appointments-scheduler'); ?>
                                </a> |
                                <?php
                                $delete_url = wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'page'               => 'wpas-services',
                                            'wpas_delete_service' => $service->id,
                                        ),
                                        admin_url('admin.php')
                                    ),
                                    'wpas_delete_service_' . $service->id
                                );
                                ?>
                                <a href="<?php echo esc_url($delete_url); ?>" onclick="return confirm('<?php esc_attr_e('Tem certeza que deseja remover este serviço?', 'wp-appointments-scheduler'); ?>');">
                                    <?php esc_html_e('Remover', 'wp-appointments-scheduler'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="7"><?php esc_html_e('Nenhum serviço cadastrado.', 'wp-appointments-scheduler'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>