<div class="wrap">
    <h1><?php esc_html_e('Configurações do Plugin de Agendamentos', 'wp-appointments-scheduler'); ?></h1>

    <form method="post">
        <?php wp_nonce_field('wpas_save_settings', 'wpas_settings_nonce'); ?>

        <table class="form-table">
            <tr>
                <th><label for="notify_email"><?php esc_html_e('E-mail para notificações', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <input type="email" name="notify_email" id="notify_email" class="regular-text"
                        value="<?php echo esc_attr($settings['notify_email'] ?? ''); ?>">
                    <p class="description">
                        <?php esc_html_e('E-mail que receberá notificações sobre novos agendamentos (opcional).', 'wp-appointments-scheduler'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th><label for="min_cancel_hours"><?php esc_html_e('Horas mínimas para cancelamento', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <input type="number" name="min_cancel_hours" id="min_cancel_hours" class="small-text"
                        value="<?php echo esc_attr($settings['min_cancel_hours'] ?? 12); ?>">
                    <p class="description">
                        <?php esc_html_e('Número de horas de antecedência para o cliente cancelar sem perder prioridade.', 'wp-appointments-scheduler'); ?>
                    </p>
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Excluir dados ao remover o plugin', 'wp-appointments-scheduler'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="delete_data_on_uninstall" value="1"
                            <?php checked(! empty($settings['delete_data_on_uninstall']), 1); ?>>
                        <?php esc_html_e('Sim, excluir todas as tabelas e configurações ao remover o plugin (Uninstall).', 'wp-appointments-scheduler'); ?>
                    </label>
                    <p class="description">
                        <?php esc_html_e('ATENÇÃO: se marcado, ao clicar em "Excluir" no painel de plugins, todos os dados de agendamentos, agenda, profissionais, serviços e configurações serão permanentemente removidos.', 'wp-appointments-scheduler'); ?>
                    </p>
                </td>
            </tr>


        </table>

        <?php submit_button(__('Salvar Configurações', 'wp-appointments-scheduler')); ?>
    </form>


    <hr>

    <h2><?php esc_html_e('Manutenção', 'wp-appointments-scheduler'); ?></h2>

    <form method="post">
        <?php wp_nonce_field('wpas_reconcile_slots', 'wpas_reconcile_slots_nonce'); ?>

        <p class="description">
            <?php esc_html_e('Se você mudou status ou removeu agendamentos e percebeu horários "sumindo", use este botão para reconciliar a agenda (slots) com os agendamentos ativos.', 'wp-appointments-scheduler'); ?>
        </p>

        <?php submit_button(__('Reconciliar horários futuros', 'wp-appointments-scheduler'), 'secondary'); ?>
    </form>
</div>