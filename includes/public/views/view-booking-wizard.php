<div class="wpas-booking-wrapper">
    <button type="button" class="wpas-open-booking-button">
        <?php esc_html_e('Agendar Agora', 'wp-appointments-scheduler'); ?>
    </button>

    <div class="wpas-booking-dialog" style="display:none;">
        <div class="wpas-booking-dialog-content">
            <button type="button" class="wpas-booking-dialog-close">&times;</button><br><br>

            <div class="wpas-booking-steps">
                <span class="step step-1 active"><?php esc_html_e('Dados do Cliente', 'wp-appointments-scheduler'); ?></span>
                <span class="step step-2"><?php esc_html_e('Profissional & Serviço', 'wp-appointments-scheduler'); ?></span>
                <span class="step step-3"><?php esc_html_e('Confirmação', 'wp-appointments-scheduler'); ?></span>
            </div>

            <form id="wpas-booking-form">

                <div class="wpas-step-content wpas-step-1 active">
                    <h3><?php esc_html_e('Dados do Cliente', 'wp-appointments-scheduler'); ?></h3>

                    <p>
                        <label><?php esc_html_e('Nome', 'wp-appointments-scheduler'); ?><br>
                            <input type="text" name="customer_name" required maxlength="80" autocomplete="name">
                        </label>
                    </p>

                    <p>
                        <label><?php esc_html_e('Contato (telefone)', 'wp-appointments-scheduler'); ?><br>
                            <input
                                type="tel"
                                name="customer_phone"
                                id="wpas-customer-phone"
                                inputmode="numeric"
                                autocomplete="tel"
                                maxlength="20"
                                placeholder="(77) 99999-9999">
                        </label>
                    </p>

                    <p>
                        <label><?php esc_html_e('E-mail', 'wp-appointments-scheduler'); ?><br>
                            <input type="email" name="customer_email" maxlength="120" autocomplete="email">
                        </label>
                    </p>

                    <div class="wpas-booking-actions">
                        <button type="button" class="button button-primary wpas-next-step" data-next="2">
                            <?php esc_html_e('Continuar', 'wp-appointments-scheduler'); ?>
                        </button>
                    </div>
                </div>

                <div class="wpas-step-content wpas-step-2">
                    <h3><?php esc_html_e('Profissional, Serviço e Horário', 'wp-appointments-scheduler'); ?></h3>

                    <p>
                        <label><?php esc_html_e('Profissional', 'wp-appointments-scheduler'); ?><br>
                            <select name="professional_id" id="wpas-professional" required>
                                <option value=""><?php esc_html_e('Selecione', 'wp-appointments-scheduler'); ?></option>
                            </select>
                        </label>
                    </p>

                    <p>
                        <label><?php esc_html_e('Serviço', 'wp-appointments-scheduler'); ?><br>
                            <select name="service_id" id="wpas-service" required>
                                <option value=""><?php esc_html_e('Selecione', 'wp-appointments-scheduler'); ?></option>
                            </select>
                        </label>
                    </p>

                    <div class="wpas-field-row wpas-field-row--2">
                        <p>
                            <label><?php esc_html_e('Data', 'wp-appointments-scheduler'); ?><br>
                                <input type="date" name="date" id="wpas-date" required>
                            </label>
                        </p>

                        <p>
                            <label><?php esc_html_e('Horário', 'wp-appointments-scheduler'); ?><br>
                                <select name="time_slot" id="wpas-time-slot" required>
                                    <option value=""><?php esc_html_e('Selecione um horário', 'wp-appointments-scheduler'); ?></option>
                                </select>
                            </label>
                        </p>
                    </div>


                    <div class="wpas-booking-actions">
                        <button type="button" class="button wpas-prev-step" data-prev="1">
                            <?php esc_html_e('Voltar', 'wp-appointments-scheduler'); ?>
                        </button>

                        <button type="button" class="button button-primary wpas-next-step" data-next="3">
                            <?php esc_html_e('Continuar', 'wp-appointments-scheduler'); ?>
                        </button>
                    </div>

                </div>

                <div class="wpas-step-content wpas-step-3">
                    <h3><?php esc_html_e('Confirmação do Agendamento', 'wp-appointments-scheduler'); ?></h3>

                    <p class="wpas-confirmation-summary">
                    </p>

                    <p class="wpas-confirmation-warning">
                        <?php esc_html_e('É muito importante comparecer no horário agendado para não tirar a oportunidade de outro cliente. Caso não possa comparecer, avise o profissional com pelo menos 12 horas de antecedência. Em caso de faltas recorrentes sem aviso, o cliente pode perder prioridade em novos agendamentos.', 'wp-appointments-scheduler'); ?>
                    </p>

                    <div class="wpas-booking-actions">
                        <button type="button" class="button wpas-prev-step" data-prev="2">
                            <?php esc_html_e('Voltar', 'wp-appointments-scheduler'); ?>
                        </button>

                        <button type="submit" class="button button-primary">
                            <?php esc_html_e('Finalizar', 'wp-appointments-scheduler'); ?>
                        </button>
                    </div>

                </div>
            </form>

            <div class="wpas-booking-result" style="display:none;"></div>
        </div>
    </div>
</div>