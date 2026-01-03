<div class="wrap">
    <h1><?php esc_html_e('Profissionais', 'wp-appointments-scheduler'); ?></h1>

    <h2>
        <?php
        echo $editing_professional
            ? esc_html__('Editar Profissional', 'wp-appointments-scheduler')
            : esc_html__('Adicionar Profissional', 'wp-appointments-scheduler');
        ?>
    </h2>

    <form method="post">
        <?php wp_nonce_field('wpas_save_professional', 'wpas_professional_nonce'); ?>

        <input type="hidden" name="id" value="<?php echo $editing_professional ? esc_attr($editing_professional->id) : 0; ?>">

        <table class="form-table">
            <tr>
                <th><label for="name"><?php esc_html_e('Nome', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <input type="text" name="name" id="name" class="regular-text" required
                        value="<?php echo $editing_professional ? esc_attr($editing_professional->name) : ''; ?>">
                </td>
            </tr>
            <tr>
                <th><label for="email"><?php esc_html_e('Email', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <input type="email" name="email" id="email" class="regular-text"
                        value="<?php echo $editing_professional ? esc_attr($editing_professional->email) : ''; ?>">
                </td>
            </tr>
            <tr>
                <th><label for="phone"><?php esc_html_e('Contato', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <input type="text" name="phone" id="phone" class="regular-text"
                        value="<?php echo $editing_professional ? esc_attr($editing_professional->phone) : ''; ?>">
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Ativo', 'wp-appointments-scheduler'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="active"
                            <?php checked($editing_professional ? $editing_professional->active : 1, 1); ?>>
                        <?php esc_html_e('Sim', 'wp-appointments-scheduler'); ?>
                    </label>
                </td>
            </tr>
        </table>

        <?php
        submit_button(
            $editing_professional
                ? __('Atualizar Profissional', 'wp-appointments-scheduler')
                : __('Adicionar Profissional', 'wp-appointments-scheduler')
        );
        ?>
    </form>

    <hr>

    <h2><?php esc_html_e('Lista de Profissionais', 'wp-appointments-scheduler'); ?></h2>
    <div class="wpas-table-scroll wpas-table-scroll--simple">
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('ID', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Nome', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Email', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Contato', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Ativo', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Ações', 'wp-appointments-scheduler'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($professionals)) : ?>
                    <?php foreach ($professionals as $prof) : ?>
                        <tr>
                            <td><?php echo esc_html($prof->id); ?></td>
                            <td><?php echo esc_html($prof->name); ?></td>
                            <td><?php echo esc_html($prof->email); ?></td>
                            <td><?php echo esc_html($prof->phone); ?></td>
                            <td><?php echo $prof->active ? esc_html__('Sim', 'wp-appointments-scheduler') : esc_html__('Não', 'wp-appointments-scheduler'); ?></td>
                            <td>
                                <a href="<?php echo esc_url(add_query_arg(array(
                                                'page' => 'wpas-professionals',
                                                'edit' => $prof->id,
                                            ), admin_url('admin.php'))); ?>">
                                    <?php esc_html_e('Editar', 'wp-appointments-scheduler'); ?>
                                </a>
                                |
                                <?php
                                $delete_url = wp_nonce_url(
                                    add_query_arg(
                                        array(
                                            'page'                   => 'wpas-professionals',
                                            'wpas_delete_professional' => $prof->id,
                                        ),
                                        admin_url('admin.php')
                                    ),
                                    'wpas_delete_professional_' . $prof->id
                                );
                                ?>
                                <a href="<?php echo esc_url($delete_url); ?>"
                                    onclick="return confirm('<?php esc_attr_e('Tem certeza que deseja remover este profissional?', 'wp-appointments-scheduler'); ?>');">
                                    <?php esc_html_e('Remover', 'wp-appointments-scheduler'); ?>
                                </a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="6"><?php esc_html_e('Nenhum profissional cadastrado.', 'wp-appointments-scheduler'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>