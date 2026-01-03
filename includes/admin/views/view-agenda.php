<div class="wrap">
    <h1><?php esc_html_e('Agenda de Profissionais', 'wp-appointments-scheduler'); ?></h1>

    <p><?php esc_html_e('Configure os dias e horários de trabalho dos profissionais, intervalos e bloqueios de dias específicos.', 'wp-appointments-scheduler'); ?></p>

    <form method="get" style="margin-bottom: 20px;">
        <input type="hidden" name="page" value="wpas-agenda">
        <table class="form-table">
            <tr>
                <th><label for="professional_id"><?php esc_html_e('Profissional', 'wp-appointments-scheduler'); ?></label></th>
                <td>
                    <select name="professional_id" id="professional_id">
                        <?php if (! empty($professionals)) : ?>
                            <?php foreach ($professionals as $prof) : ?>
                                <option value="<?php echo esc_attr($prof->id); ?>"
                                    <?php selected($selected_professional, $prof->id); ?>>
                                    <?php echo esc_html($prof->name); ?>
                                </option>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <option value=""><?php esc_html_e('Nenhum profissional cadastrado.', 'wp-appointments-scheduler'); ?></option>
                        <?php endif; ?>
                    </select>
                </td>
            </tr>
        </table>
        <?php submit_button(__('Carregar Agenda', 'wp-appointments-scheduler'), 'secondary'); ?>
    </form>

    <?php if ($selected_professional > 0) : ?>

        <?php
        $prev_year  = $year;
        $prev_month = $month - 1;
        if ($prev_month < 1) {
            $prev_month = 12;
            $prev_year--;
        }

        $next_year  = $year;
        $next_month = $month + 1;
        if ($next_month > 12) {
            $next_month = 1;
            $next_year++;
        }

        $base_args = array(
            'page'            => 'wpas-agenda',
            'professional_id' => $selected_professional,
        );
        ?>

        <h2>
            <?php
            printf(
                esc_html__('Agenda de %1$s - %2$s/%3$s', 'wp-appointments-scheduler'),
                esc_html($selected_professional && isset($professionals[0]) ? '' : ''),
                esc_html($month),
                esc_html($year)
            );
            ?>
        </h2>

        <p>
            <a class="button" href="<?php echo esc_url(add_query_arg(array_merge($base_args, array(
                                        'year'  => $prev_year,
                                        'month' => $prev_month,
                                    )), admin_url('admin.php'))); ?>">&laquo; <?php esc_html_e('Mês anterior', 'wp-appointments-scheduler'); ?></a>

            <a class="button" href="<?php echo esc_url(add_query_arg(array_merge($base_args, array(
                                        'year'  => $next_year,
                                        'month' => $next_month,
                                    )), admin_url('admin.php'))); ?>"><?php esc_html_e('Próximo mês', 'wp-appointments-scheduler'); ?> &raquo;</a>
        </p>

        <h2><?php esc_html_e('Gerar horários para um período', 'wp-appointments-scheduler'); ?></h2>

        <form method="post" style="margin-bottom: 30px;">
            <?php wp_nonce_field('wpas_agenda_generate', 'wpas_agenda_generate_nonce'); ?>
            <input type="hidden" name="professional_id" value="<?php echo esc_attr($selected_professional); ?>">

            <table class="form-table">
                <tr>
                    <th><label for="date_from"><?php esc_html_e('Data inicial', 'wp-appointments-scheduler'); ?></label></th>
                    <td><input type="date" name="date_from" id="date_from" required></td>
                </tr>
                <tr>
                    <th><label for="date_to"><?php esc_html_e('Data final', 'wp-appointments-scheduler'); ?></label></th>
                    <td><input type="date" name="date_to" id="date_to" required></td>
                </tr>
                <tr>
                    <th><label for="start_time"><?php esc_html_e('Horário inicial', 'wp-appointments-scheduler'); ?></label></th>
                    <td><input type="time" name="start_time" id="start_time" required></td>
                </tr>
                <tr>
                    <th><label for="end_time"><?php esc_html_e('Horário final', 'wp-appointments-scheduler'); ?></label></th>
                    <td><input type="time" name="end_time" id="end_time" required></td>
                </tr>
                <tr>
                    <th><label for="duration"><?php esc_html_e('Duração de cada slot (minutos)', 'wp-appointments-scheduler'); ?></label></th>
                    <td><input type="number" name="duration" id="duration" value="30" min="5" step="5"></td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Dias da semana', 'wp-appointments-scheduler'); ?></th>
                    <td>
                        <label><input type="checkbox" name="weekdays[]" value="1" checked> <?php esc_html_e('Segunda', 'wp-appointments-scheduler'); ?></label><br>
                        <label><input type="checkbox" name="weekdays[]" value="2" checked> <?php esc_html_e('Terça', 'wp-appointments-scheduler'); ?></label><br>
                        <label><input type="checkbox" name="weekdays[]" value="3" checked> <?php esc_html_e('Quarta', 'wp-appointments-scheduler'); ?></label><br>
                        <label><input type="checkbox" name="weekdays[]" value="4" checked> <?php esc_html_e('Quinta', 'wp-appointments-scheduler'); ?></label><br>
                        <label><input type="checkbox" name="weekdays[]" value="5" checked> <?php esc_html_e('Sexta', 'wp-appointments-scheduler'); ?></label><br>
                        <label><input type="checkbox" name="weekdays[]" value="6"> <?php esc_html_e('Sábado', 'wp-appointments-scheduler'); ?></label><br>
                        <label><input type="checkbox" name="weekdays[]" value="7"> <?php esc_html_e('Domingo', 'wp-appointments-scheduler'); ?></label>
                        <p class="description"><?php esc_html_e('Selecione em quais dias da semana os horários serão criados.', 'wp-appointments-scheduler'); ?></p>
                    </td>
                </tr>
                <tr>
                    <th><?php esc_html_e('Tipo de slot', 'wp-appointments-scheduler'); ?></th>
                    <td>
                        <label>
                            <input type="radio" name="status" value="available" checked>
                            <?php esc_html_e('Disponível (horário de atendimento)', 'wp-appointments-scheduler'); ?>
                        </label><br>
                        <label>
                            <input type="radio" name="status" value="blocked">
                            <?php esc_html_e('Bloqueado (descanso/bloqueio)', 'wp-appointments-scheduler'); ?>
                        </label>
                    </td>
                </tr>
            </table>

            <?php submit_button(__('Gerar horários', 'wp-appointments-scheduler')); ?>
        </form>

        <h2><?php esc_html_e('Calendário do mês', 'wp-appointments-scheduler'); ?></h2>

        <?php
        $first_day_timestamp = mktime(0, 0, 0, $month, 1, $year);
        $days_in_month       = (int) date('t', $first_day_timestamp);
        $first_weekday       = (int) date('w', $first_day_timestamp);
        ?>
        <div class="wpas-table-scroll wpas-table-scroll--calendar">
            <table class="widefat fixed striped">
                <thead>
                    <tr>
                        <th><?php esc_html_e('Dom', 'wp-appointments-scheduler'); ?></th>
                        <th><?php esc_html_e('Seg', 'wp-appointments-scheduler'); ?></th>
                        <th><?php esc_html_e('Ter', 'wp-appointments-scheduler'); ?></th>
                        <th><?php esc_html_e('Qua', 'wp-appointments-scheduler'); ?></th>
                        <th><?php esc_html_e('Qui', 'wp-appointments-scheduler'); ?></th>
                        <th><?php esc_html_e('Sex', 'wp-appointments-scheduler'); ?></th>
                        <th><?php esc_html_e('Sáb', 'wp-appointments-scheduler'); ?></th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <?php
                        for ($i = 0; $i < $first_weekday; $i++) {
                            echo '<td></td>';
                        }

                        $day = 1;
                        $cell = $first_weekday;

                        while ($day <= $days_in_month) {
                            if ($cell === 7) {
                                echo '</tr><tr>';
                                $cell = 0;
                            }

                            $date_str = sprintf('%04d-%02d-%02d', $year, $month, $day);

                            $info = isset($overview[$date_str])
                                ? $overview[$date_str]
                                : array('total' => 0, 'available' => 0, 'blocked' => 0);

                            $view_link = add_query_arg(
                                array_merge($base_args, array(
                                    'year'       => $year,
                                    'month'      => $month,
                                    'view_date'  => $date_str,
                                )),
                                admin_url('admin.php')
                            );

                            $clear_link = wp_nonce_url(
                                add_query_arg(
                                    array_merge($base_args, array(
                                        'year'      => $year,
                                        'month'     => $month,
                                        'clear_day' => $date_str,
                                    )),
                                    admin_url('admin.php')
                                ),
                                'wpas_agenda_clear_day_' . $selected_professional . '_' . $date_str
                            );
                        ?>
                            <td>
                                <strong><?php echo esc_html($day); ?></strong><br>

                                <?php if ($info['total'] > 0) : ?>
                                    <small>
                                        <?php
                                        printf(
                                            esc_html__('%1$d slots (%2$d disp., %3$d bloc.)', 'wp-appointments-scheduler'),
                                            $info['total'],
                                            $info['available'],
                                            $info['blocked']
                                        );
                                        ?>
                                    </small><br>
                                    <a href="<?php echo esc_url($view_link); ?>">
                                        <?php esc_html_e('Ver horários', 'wp-appointments-scheduler'); ?>
                                    </a>
                                    <br>
                                    <a href="<?php echo esc_url($clear_link); ?>"
                                        onclick="return confirm('<?php esc_attr_e('Tem certeza que deseja remover todos os horários deste dia?', 'wp-appointments-scheduler'); ?>');">
                                        <?php esc_html_e('Limpar dia', 'wp-appointments-scheduler'); ?>
                                    </a>
                                <?php else : ?>
                                    <small><?php esc_html_e('Sem horários', 'wp-appointments-scheduler'); ?></small>
                                <?php endif; ?>
                            </td>
                        <?php
                            $day++;
                            $cell++;
                        }

                        while ($cell < 7) {
                            echo '<td></td>';
                            $cell++;
                        }
                        ?>
                    </tr>
                </tbody>
            </table>
        </div>

        <?php if (! empty($view_date)) : ?>
            <h2 style="margin-top: 30px;">
                <?php
                printf(
                    esc_html__('Horários do dia %s', 'wp-appointments-scheduler'),
                    esc_html($view_date)
                );
                ?>
            </h2>

            <div class="wpas-table-scroll wpas-table-scroll--day">
                <table class="widefat fixed striped">
                    <thead>
                        <tr>
                            <th><?php esc_html_e('Início', 'wp-appointments-scheduler'); ?></th>
                            <th><?php esc_html_e('Fim', 'wp-appointments-scheduler'); ?></th>
                            <th><?php esc_html_e('Status', 'wp-appointments-scheduler'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (! empty($day_slots)) : ?>
                            <?php foreach ($day_slots as $slot) : ?>
                                <tr>
                                    <td><?php echo esc_html(substr($slot->start_time, 0, 5)); ?></td>
                                    <td><?php echo esc_html(substr($slot->end_time, 0, 5)); ?></td>
                                    <td><?php echo esc_html($slot->status); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else : ?>
                            <tr>
                                <td colspan="3"><?php esc_html_e('Nenhum horário cadastrado para este dia.', 'wp-appointments-scheduler'); ?></td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

    <?php else : ?>

        <p><?php esc_html_e('Cadastre pelo menos um profissional para começar a configurar a agenda.', 'wp-appointments-scheduler'); ?></p>

    <?php endif; ?>
</div>