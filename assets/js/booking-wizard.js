jQuery(document).ready(function ($) {

    var $dialog = $('.wpas-booking-dialog');
    if (!$dialog.length) return;

    function i18n(key, fallback) {
        if (typeof WPAS_Booking !== 'undefined' && WPAS_Booking.i18n && WPAS_Booking.i18n[key]) {
            return WPAS_Booking.i18n[key];
        }
        return fallback;
    }

    function changeStep(step) {
        $('.wpas-step-content').removeClass('active');
        $('.wpas-step-' + step).addClass('active');

        $('.wpas-booking-steps .step').removeClass('active');
        $('.wpas-booking-steps .step-' + step).addClass('active');
    }

    function resetWizardState() {
        changeStep(1);

        $('.wpas-booking-result').hide().empty();
        $('#wpas-booking-form').show();

        $('#wpas-booking-form')[0].reset();

        clearValidation($('#wpas-booking-form'));

        $('#wpas-professional').empty().append('<option value="">' + i18n('select', 'Selecione') + '</option>');
        $('#wpas-service').empty().append('<option value="">' + i18n('select', 'Selecione') + '</option>');

        $('#wpas-time-slot')
            .empty()
            .append('<option value="">' + i18n('select_time', 'Selecione um horário') + '</option>')
            .prop('disabled', true);
    }

    function loadInitialData() {
        var $prof = $('#wpas-professional');
        var $serv = $('#wpas-service');

        $prof.prop('disabled', true).empty().append('<option value="">' + i18n('loading', 'Carregando...') + '</option>');
        $serv.prop('disabled', true).empty().append('<option value="">' + i18n('loading', 'Carregando...') + '</option>');

        $.post(WPAS_Booking.ajax_url, {
            action: 'wpas_get_booking_data',
            nonce: WPAS_Booking.nonce
        }, function (response) {

            if (!response || response === '-1' || response === '0' || !response.success) {
                $prof.prop('disabled', true).empty().append('<option value="">' + i18n('error_generic', 'Ocorreu um erro. Tente novamente.') + '</option>');
                $serv.prop('disabled', true).empty().append('<option value="">' + i18n('error_generic', 'Ocorreu um erro. Tente novamente.') + '</option>');
                return;
            }

            var data = response.data || {};

            $prof.prop('disabled', false).empty().append('<option value="">' + i18n('select', 'Selecione') + '</option>');
            $serv.prop('disabled', false).empty().append('<option value="">' + i18n('select', 'Selecione') + '</option>');

            $.each((data.professionals || []), function (i, p) {
                $prof.append('<option value="' + p.id + '">' + p.name + '</option>');
            });

            $.each((data.services || []), function (i, s) {
                $serv.append(
                    '<option value="' + s.id + '" data-price="' + (s.price || '') + '" data-duration="' + (s.duration || '') + '">' +
                    s.name +
                    '</option>'
                );
            });


        }).fail(function () {
            $prof.prop('disabled', true).empty().append('<option value="">' + i18n('error_generic', 'Ocorreu um erro. Tente novamente.') + '</option>');
            $serv.prop('disabled', true).empty().append('<option value="">' + i18n('error_generic', 'Ocorreu um erro. Tente novamente.') + '</option>');
        });
    }

    function loadTimeSlots() {
        var professionalId = $('#wpas-professional').val();
        var serviceId = $('#wpas-service').val();
        var date = $('#wpas-date').val();
        var $timeSelect = $('#wpas-time-slot');

        if (!professionalId || !serviceId || !date) {
            $timeSelect
                .empty()
                .append('<option value="">' + i18n('select_time', 'Selecione um horário') + '</option>')
                .prop('disabled', true);
            return;
        }

        $timeSelect
            .prop('disabled', true)
            .empty()
            .append('<option value="">' + i18n('loading', 'Carregando...') + '</option>');

        $.post(WPAS_Booking.ajax_url, {
            action: 'wpas_get_available_slots',
            nonce: WPAS_Booking.nonce,
            professional_id: professionalId,
            service_id: serviceId,
            date: date
        }, function (response) {
            $timeSelect.empty();

            if (!response || response === '-1' || response === '0') {
                $timeSelect
                    .append('<option value="">' + i18n('error_generic', 'Ocorreu um erro. Tente novamente.') + '</option>')
                    .prop('disabled', true);
                return;
            }

            if (!response.success || !response.data || !response.data.slots || !response.data.slots.length) {
                $timeSelect
                    .append('<option value="">' + 'Nenhum horário disponível' + '</option>')
                    .prop('disabled', true);
                return;
            }

            $timeSelect.append('<option value="">' + i18n('select_time', 'Selecione um horário') + '</option>');
            $.each(response.data.slots, function (i, slot) {
                $timeSelect.append('<option value="' + slot.value + '">' + slot.label + '</option>');
            });

            $timeSelect.prop('disabled', false);
        }).fail(function () {
            $timeSelect
                .empty()
                .append('<option value="">' + 'Erro ao carregar horários' + '</option>')
                .prop('disabled', true);
        });
    }

    function digitsOnly(str) {
        return (str || '').toString().replace(/\D+/g, '');
    }

    function normalizePhoneDigits(raw) {
        var d = digitsOnly(raw);

        if (d.length > 11 && d.indexOf('55') === 0) {
            d = d.substring(2);
        }

        if (d.length > 11) d = d.substring(0, 11);

        return d;
    }

    function formatPhoneBR(d) {
        d = normalizePhoneDigits(d);

        if (d.length === 0) return '';

        if (d.length <= 2) {
            return '(' + d;
        }

        var dd = d.substring(0, 2);
        var rest = d.substring(2);

        if (rest.length > 8) {
            return '(' + dd + ') ' + rest.substring(0, 5) + '-' + rest.substring(5);
        }

        if (rest.length >= 5) {
            return '(' + dd + ') ' + rest.substring(0, 4) + '-' + rest.substring(4);
        }

        return '(' + dd + ') ' + rest;
    }

    function isValidEmail(email) {
        email = (email || '').trim();
        if (!email) return true;
        return /^[^\s@]+@[^\s@]+\.[^\s@]{2,}$/.test(email);
    }

    function isValidPhone(phoneMaskedOrRaw) {
        var d = normalizePhoneDigits(phoneMaskedOrRaw);
        if (!d) return true;
        return (d.length === 10 || d.length === 11);
    }

    function clearFieldError($field) {
        $field.removeClass('wpas-field-error');
        $field.closest('p').find('.wpas-field-error-message').remove();
    }

    function setFieldError($field, message) {
        $field.addClass('wpas-field-error');

        var $p = $field.closest('p');
        $p.find('.wpas-field-error-message').remove();

        if (message) {
            $('<span class="wpas-field-error-message"></span>')
                .text(message)
                .appendTo($p);
        }
    }

    function clearValidation($scope) {
        $scope.find('.wpas-field-error').removeClass('wpas-field-error');
        $scope.find('.wpas-field-error-message').remove();
    }

    function validateStep1() {
        var ok = true;

        var $name = $('#wpas-booking-form input[name="customer_name"]');
        var $email = $('#wpas-booking-form input[name="customer_email"]');
        var $phone = $('#wpas-booking-form input[name="customer_phone"]');

        clearFieldError($name);
        clearFieldError($email);
        clearFieldError($phone);

        var name = ($name.val() || '').trim();
        var email = ($email.val() || '').trim();
        var phoneDigits = normalizePhoneDigits($phone.val() || '');

        if (name.length < 3) {
            ok = false;
            setFieldError($name, 'Informe seu nome (mínimo 3 caracteres).');
        }

        if (!email && !phoneDigits) {
            ok = false;
            setFieldError($email, 'Informe e-mail ou telefone.');
            setFieldError($phone, 'Informe e-mail ou telefone.');
        }

        if (email && !isValidEmail(email)) {
            ok = false;
            setFieldError($email, 'E-mail inválido.');
        }

        if (phoneDigits && !isValidPhone(phoneDigits)) {
            ok = false;
            setFieldError($phone, 'Telefone inválido. Use DDD + número.');
        }

        return ok;
    }

    function validateStep2() {
        var ok = true;

        var $prof = $('#wpas-professional');
        var $serv = $('#wpas-service');
        var $date = $('#wpas-date');
        var $time = $('#wpas-time-slot');

        clearFieldError($prof);
        clearFieldError($serv);
        clearFieldError($date);
        clearFieldError($time);

        if (!$prof.val()) { ok = false; setFieldError($prof, 'Selecione o profissional.'); }
        if (!$serv.val()) { ok = false; setFieldError($serv, 'Selecione o serviço.'); }
        if (!$date.val()) { ok = false; setFieldError($date, 'Selecione a data.'); }
        if (!$time.val()) { ok = false; setFieldError($time, 'Selecione um horário.'); }

        return ok;
    }

    function formatDateBR(isoDate) {
        isoDate = (isoDate || '').toString().trim();
        if (!isoDate) return '';

        var m = isoDate.match(/^(\d{4})-(\d{2})-(\d{2})$/);
        if (!m) return isoDate;

        var y = m[1], mo = m[2], d = m[3];
        return d + '/' + mo + '/' + y;
    }

    function fillConfirmation() {
        var name = ($('#wpas-booking-form input[name="customer_name"]').val() || '').trim();
        var email = ($('#wpas-booking-form input[name="customer_email"]').val() || '').trim();
        var phone = ($('#wpas-booking-form input[name="customer_phone"]').val() || '').trim();

        var profText = $('#wpas-professional option:selected').text();
        var servText = $('#wpas-service option:selected').text();

        var $servOpt = $('#wpas-service option:selected');
        var servPrice = $servOpt.data('price') || '';

        var dateISO = $('#wpas-date').val();
        var date = formatDateBR(dateISO);

        var timeLabel = $('#wpas-time-slot option:selected').text();

        var parts = [];
        parts.push('<strong>Cliente:</strong> ' + escapeHtml(name));
        if (email) parts.push('<strong>E-mail:</strong> ' + escapeHtml(email));
        if (phone) parts.push('<strong>Telefone:</strong> ' + escapeHtml(phone));
        parts.push('<strong>Profissional:</strong> ' + escapeHtml(profText));

        var serviceLine = servText + (servPrice ? ' (' + servPrice + ')' : '');
        parts.push('<strong>Serviço:</strong> ' + escapeHtml(serviceLine));

        parts.push('<strong>Data:</strong> ' + escapeHtml(date));
        parts.push('<strong>Horário:</strong> ' + escapeHtml(timeLabel));

        $('.wpas-confirmation-summary').html(parts.join('<br>'));
    }


    function escapeHtml(str) {
        return (str || '').toString()
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function submitBooking() {
        if (!validateStep1()) { changeStep(1); return; }
        if (!validateStep2()) { changeStep(2); return; }

        var formData = $('#wpas-booking-form').serializeArray();

        for (var i = 0; i < formData.length; i++) {
            if (formData[i].name === 'customer_phone') {
                formData[i].value = normalizePhoneDigits(formData[i].value);
            }
            if (formData[i].name === 'customer_name') {
                formData[i].value = (formData[i].value || '').toString().substring(0, 80);
            }
            if (formData[i].name === 'customer_email') {
                formData[i].value = (formData[i].value || '').toString().substring(0, 120);
            }
        }

        formData.push({ name: 'action', value: 'wpas_create_booking' });
        formData.push({ name: 'nonce', value: WPAS_Booking.nonce });

        $.post(WPAS_Booking.ajax_url, formData, function (response) {
            var $result = $('.wpas-booking-result');

            if (response && response.success) {
                $result.text((response.data && response.data.message) ? response.data.message : i18n('success', 'Agendamento realizado com sucesso!')).show();
                $('#wpas-booking-form').hide();
            } else {
                var msg = (response && response.data && response.data.message) ? response.data.message : 'Erro ao agendar.';
                $result.text(msg).show();
            }
        }).fail(function () {
            $('.wpas-booking-result').text('Erro ao agendar.').show();
        });
    }

    $(document).on('wpas_booking_opened', function () {
        resetWizardState();
        loadInitialData();
    });

    $(document).on('input', '#wpas-customer-phone', function () {
        var masked = formatPhoneBR($(this).val());
        $(this).val(masked);
        clearFieldError($(this));
    });

    $(document).on('input change', '#wpas-booking-form input, #wpas-booking-form select', function () {
        clearFieldError($(this));
    });

    $(document).on('click', '.wpas-next-step', function () {
        var next = $(this).data('next');

        if (parseInt(next, 10) === 2) {
            if (!validateStep1()) return;
        }

        if (parseInt(next, 10) === 3) {
            if (!validateStep2()) return;
            fillConfirmation();
        }

        changeStep(next);
    });

    $(document).on('click', '.wpas-prev-step', function () {
        var prev = $(this).data('prev');
        changeStep(prev);
    });

    $(document).on('submit', '#wpas-booking-form', function (e) {
        e.preventDefault();
        submitBooking();
    });

    $(document).on('change', '#wpas-professional, #wpas-service, #wpas-date', function () {
        loadTimeSlots();
    });
});
