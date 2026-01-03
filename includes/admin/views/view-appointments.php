<div class="wrap">
    <h1><?php esc_html_e('Agendamentos', 'wp-appointments-scheduler'); ?></h1>

    <p><?php esc_html_e('Aqui você acompanha e gerencia os agendamentos realizados pelos clientes.', 'wp-appointments-scheduler'); ?></p>

    <?php
    if (!empty($notice)) {
        $is_ok = in_array($notice, array('status_updated', 'deleted', 'manual_created', 'manual_updated'), true);
        $class = $is_ok ? 'updated' : 'error';
        $msg = '';

        switch ($notice) {
            case 'status_updated':
                $msg = __('Status do agendamento atualizado com sucesso.', 'wp-appointments-scheduler');
                break;
            case 'status_error':
                $msg = __('Não foi possível atualizar o status do agendamento.', 'wp-appointments-scheduler');
                break;
            case 'deleted':
                $msg = __('Agendamento removido.', 'wp-appointments-scheduler');
                break;
            case 'manual_created':
                $msg = $notice_message ?: __('Agendamento criado com sucesso.', 'wp-appointments-scheduler');
                break;
            case 'manual_updated':
                $msg = $notice_message ?: __('Agendamento atualizado.', 'wp-appointments-scheduler');
                break;
            case 'manual_error':
            default:
                $msg = $notice_message ?: __('Ocorreu um erro.', 'wp-appointments-scheduler');
                break;
        }

        echo '<div class="' . esc_attr($class) . '"><p>' . esc_html($msg) . '</p></div>';
    }
    ?>

    <h2><?php echo $editing ? esc_html__('Editar agendamento', 'wp-appointments-scheduler') : esc_html__('Novo agendamento manual', 'wp-appointments-scheduler'); ?></h2>

    <form method="post" style="margin-bottom: 25px;" data-wpas-no-double-submit="1">
        <?php if ($editing && $edit_appointment) : ?>
            <?php wp_nonce_field('wpas_manual_update_' . (int) $edit_appointment->id, 'wpas_manual_update_nonce'); ?>
            <input type="hidden" name="appointment_id" value="<?php echo esc_attr((int) $edit_appointment->id); ?>">
        <?php else : ?>
            <?php wp_nonce_field('wpas_manual_create', 'wpas_manual_create_nonce'); ?>
        <?php endif; ?>

        <table class="form-table">
            <tr>
                <th><label for="customer_name"><?php esc_html_e('Cliente', 'wp-appointments-scheduler'); ?></label></th>
                <td><input type="text" name="customer_name" id="customer_name" class="regular-text" required value="<?php echo esc_attr($editing && $edit_appointment ? $edit_appointment->customer_name : ''); ?>"></td>
            </tr>
            <tr>
                <th><label for="customer_email"><?php esc_html_e('E-mail', 'wp-appointments-scheduler'); ?></label></th>
                <td><input type="email" name="customer_email" id="customer_email" class="regular-text" value="<?php echo esc_attr($editing && $edit_appointment ? $edit_appointment->customer_email : ''); ?>"></td>
            </tr>
            <tr>
                <th><label for="customer_phone"><?php esc_html_e('Telefone', 'wp-appointments-scheduler'); ?></label></th>
                <td><input type="text" name="customer_phone" id="customer_phone" class="regular-text" value="<?php echo esc_attr($editing && $edit_appointment ? $edit_appointment->customer_phone : ''); ?>"></td>
            </tr>
            <tr>
                <th><label for="professional_id"><?php esc_html_e('Profissional', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <select name="professional_id" id="professional_id" required>
                        <option value=""><?php esc_html_e('Selecione', 'wp-appointments-scheduler'); ?></option>
                        <?php foreach ($professionals as $prof) : ?>
                            <option value="<?php echo esc_attr($prof->id); ?>" <?php selected($editing && $edit_appointment ? (int) $edit_appointment->professional_id : 0, (int) $prof->id); ?>>
                                <?php echo esc_html($prof->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="service_id"><?php esc_html_e('Serviço', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <select name="service_id" id="service_id" required>
                        <option value=""><?php esc_html_e('Selecione', 'wp-appointments-scheduler'); ?></option>
                        <?php foreach ($services as $serv) : ?>
                            <option value="<?php echo esc_attr($serv->id); ?>" <?php selected($editing && $edit_appointment ? (int) $edit_appointment->service_id : 0, (int) $serv->id); ?>>
                                <?php echo esc_html($serv->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <p class="description"><?php esc_html_e('O horário final será calculado automaticamente pela duração do serviço.', 'wp-appointments-scheduler'); ?></p>
                </td>
            </tr>
            <tr>
                <th><label for="date"><?php esc_html_e('Data', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <input type="date" name="date" id="date" required value="<?php echo esc_attr($editing && $edit_appointment ? $edit_appointment->date : current_time('Y-m-d')); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="start_time"><?php esc_html_e('Horário inicial', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <input type="time" name="start_time" id="start_time" required value="<?php echo esc_attr($editing && $edit_appointment ? substr($edit_appointment->start_time, 0, 5) : ''); ?>">
                </td>
            </tr>
            <tr>
                <th><label for="status_manual"><?php esc_html_e('Status', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <select name="status" id="status_manual">
                        <?php foreach ($statuses as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($editing && $edit_appointment ? $edit_appointment->status : 'pending', $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>
            <tr>
                <th><label for="notes"><?php esc_html_e('Notas', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <textarea name="notes" id="notes" rows="3" class="large-text"><?php echo esc_textarea($editing && $edit_appointment ? $edit_appointment->notes : ''); ?></textarea>
                </td>
            </tr>
            <tr>
                <th><?php esc_html_e('Encaixe', 'wp-appointments-scheduler'); ?></th>
                <td>
                    <label>
                        <input type="checkbox" name="force_fit" value="1">
                        <?php esc_html_e('Forçar encaixe se não houver slot disponível (cria/ajusta slots como booked).', 'wp-appointments-scheduler'); ?>
                    </label>
                    <p class="description"><?php esc_html_e('Use com cuidado: pode gerar conflito se já existir agendamento no mesmo horário.', 'wp-appointments-scheduler'); ?></p>
                </td>
            </tr>
        </table>

        <?php
        if ($editing) {
            submit_button(__('Salvar alterações', 'wp-appointments-scheduler'));
            echo ' <a class="button" href="' . esc_url(add_query_arg(array('page' => 'wpas-appointments'), admin_url('admin.php'))) . '">' . esc_html__('Cancelar', 'wp-appointments-scheduler') . '</a>';
        } else {
            submit_button(__('Criar agendamento', 'wp-appointments-scheduler'));
        }
        ?>
    </form>

    <hr>

    <h2><?php esc_html_e('Filtrar agendamentos', 'wp-appointments-scheduler'); ?></h2>

    <form method="get" id="wpas-appointments-filter-form" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="wpas-appointments">

        <table class="form-table">
            <tr>
                <th><label for="filter_professional_id"><?php esc_html_e('Profissional', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <select name="professional_id" id="filter_professional_id">
                        <option value=""><?php esc_html_e('Todos', 'wp-appointments-scheduler'); ?></option>
                        <?php foreach ($professionals as $prof) : ?>
                            <option value="<?php echo esc_attr($prof->id); ?>" <?php selected($filters['professional_id'], $prof->id); ?>>
                                <?php echo esc_html($prof->name); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th><label for="filter_status"><?php esc_html_e('Status', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <select name="status" id="filter_status">
                        <option value=""><?php esc_html_e('Todos', 'wp-appointments-scheduler'); ?></option>
                        <?php foreach ($statuses as $key => $label) : ?>
                            <option value="<?php echo esc_attr($key); ?>" <?php selected($filters['status'], $key); ?>>
                                <?php echo esc_html($label); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </td>
            </tr>

            <tr>
                <th><?php esc_html_e('Período', 'wp-appointments-scheduler'); ?></th>
                <td>
                    <label>
                        <?php esc_html_e('De', 'wp-appointments-scheduler'); ?>
                        <input type="date" name="date_from" value="<?php echo esc_attr($filters['date_from']); ?>">
                    </label>
                    &nbsp;&nbsp;
                    <label>
                        <?php esc_html_e('Até', 'wp-appointments-scheduler'); ?>
                        <input type="date" name="date_to" value="<?php echo esc_attr($filters['date_to']); ?>">
                    </label>
                    <p class="description"><?php esc_html_e('Por padrão, esta tela carrega apenas o dia de hoje.', 'wp-appointments-scheduler'); ?></p>
                </td>
            </tr>
        </table>

        <?php submit_button(__('Filtrar', 'wp-appointments-scheduler'), 'secondary'); ?>
    </form>
    <div class="wpas-table-scroll wpas-table-scroll--appointments">
        <table class="widefat fixed striped">
            <thead>
                <tr>
                    <th><?php esc_html_e('Código', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Data', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Horário', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Cliente', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Profissional', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Serviço', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Status', 'wp-appointments-scheduler'); ?></th>
                    <th><?php esc_html_e('Ações', 'wp-appointments-scheduler'); ?></th>
                </tr>
            </thead>
            <tbody>
                <?php if (! empty($appointments)) : ?>
                    <?php foreach ($appointments as $app) : ?>
                        <tr>
                            <td>
                                <?php
                                $code = !empty($app->public_id) ? $app->public_id : $app->id;
                                echo esc_html($code);
                                ?>
                            </td>
                            <td><?php echo esc_html($app->date); ?></td>
                            <td><?php echo esc_html(substr($app->start_time, 0, 5) . ' - ' . substr($app->end_time, 0, 5)); ?></td>
                            <td>
                                <?php echo esc_html($app->customer_name); ?><br>

                                <?php if (!empty($app->customer_email)) : ?>
                                    <small>
                                        <a href="mailto:<?php echo esc_attr($app->customer_email); ?>">
                                            <?php echo esc_html($app->customer_email); ?>
                                        </a>
                                    </small><br>
                                <?php endif; ?>

                                <?php if (!empty($app->customer_phone)) : ?>
                                    <?php
                                    $phone_digits = preg_replace('/\D+/', '', (string) $app->customer_phone);

                                    if (strlen($phone_digits) === 10 || strlen($phone_digits) === 11) {
                                        $phone_digits = '55' . $phone_digits;
                                    }

                                    $wa_link = 'https://wa.me/' . $phone_digits;
                                    ?>
                                    <small>
                                        <a href="<?php echo esc_url($wa_link); ?>" target="_blank" rel="noopener noreferrer">
                                            <?php echo esc_html($app->customer_phone); ?>
                                        </a>
                                    </small>
                                <?php endif; ?>
                            </td>

                            <td>
                                <?php
                                echo isset($prof_index[$app->professional_id])
                                    ? esc_html($prof_index[$app->professional_id])
                                    : esc_html__('N/D', 'wp-appointments-scheduler');
                                ?>
                            </td>
                            <td>
                                <?php
                                echo isset($serv_index[$app->service_id])
                                    ? esc_html($serv_index[$app->service_id])
                                    : esc_html__('N/D', 'wp-appointments-scheduler');
                                ?>
                            </td>
                            <td>
                                <?php
                                echo isset($statuses[$app->status])
                                    ? esc_html($statuses[$app->status])
                                    : esc_html($app->status);
                                ?>
                            </td>
                            <td>
                                <?php
                                $preserve = array(
                                    'page'            => 'wpas-appointments',
                                    'professional_id' => $filters['professional_id'],
                                    'status'          => $filters['status'],
                                    'date_from'       => $filters['date_from'],
                                    'date_to'         => $filters['date_to'],
                                );

                                $edit_url = add_query_arg(array_merge($preserve, array(
                                    'action' => 'edit',
                                    'appointment_id' => (int) $app->id,
                                )), admin_url('admin.php'));
                                echo '<a class="button button-small" href="' . esc_url($edit_url) . '">' . esc_html__('Editar', 'wp-appointments-scheduler') . '</a> ';

                                echo '<div style="margin-top:8px;">';
                                foreach ($statuses as $status_key => $status_label) {
                                    if ($status_key === $app->status) {
                                        continue;
                                    }

                                    $status_url = add_query_arg(
                                        array_merge($preserve, array(
                                            'wpas_change_status' => (int) $app->id,
                                            'new_status'        => $status_key,
                                        )),
                                        admin_url('admin.php')
                                    );

                                    $status_url = wp_nonce_url(
                                        $status_url,
                                        'wpas_change_status_' . (int) $app->id . '_' . $status_key
                                    );

                                    echo '<a href="' . esc_url($status_url) . '">'
                                        . sprintf(
                                            esc_html__('Marcar como %s', 'wp-appointments-scheduler'),
                                            esc_html($status_label)
                                        )
                                        . '</a><br>';
                                }
                                echo '</div>';

                                $delete_url = add_query_arg(
                                    array_merge($preserve, array(
                                        'wpas_delete_appointment' => (int) $app->id,
                                    )),
                                    admin_url('admin.php')
                                );
                                $delete_url = wp_nonce_url($delete_url, 'wpas_delete_appointment_' . (int) $app->id);

                                echo '<a href="' . esc_url($delete_url) . '" data-wpas-confirm="' . esc_attr__('Tem certeza que deseja remover este agendamento?', 'wp-appointments-scheduler') . '">'
                                    . esc_html__('Remover', 'wp-appointments-scheduler')
                                    . '</a>';
                                ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                <?php else : ?>
                    <tr>
                        <td colspan="8"><?php esc_html_e('Nenhum agendamento encontrado.', 'wp-appointments-scheduler'); ?></td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>