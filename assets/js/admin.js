jQuery(document).ready(function ($) {

    var WPAS_Admin = {

        init: function () {
            this.bindConfirmLinks();
            this.bindAppointmentFilters();
            this.bindAgendaHelpers();
            this.preventDoubleSubmit();
        },

        bindConfirmLinks: function () {
            $(document).on('click', 'a[data-wpas-confirm]', function (e) {
                var msg = $(this).data('wpas-confirm');
                if (!msg) {
                    return;
                }
                if (!window.confirm(msg)) {
                    e.preventDefault();
                }
            });
        },

        bindAppointmentFilters: function () {
            var $filterForm = $('#wpas-appointments-filter-form');

            if (!$filterForm.length) {
                return;
            }

            $filterForm.find('select, input[type="date"]').on('change', function () {
                $filterForm.trigger('submit');
            });
        },

        bindAgendaHelpers: function () {
            var $dateFrom = $('#date_from');
            var $dateTo = $('#date_to');

            if ($dateFrom.length && $dateTo.length) {
                $dateFrom.on('change', function () {
                    if (!$dateTo.val()) {
                        $dateTo.val($dateFrom.val());
                    }
                });
            }
        },

        preventDoubleSubmit: function () {
            $(document).on('submit', 'form[data-wpas-no-double-submit="1"]', function () {
                var $form = $(this);

                if ($form.data('wpas-submitted')) {
                    return false;
                }

                $form.data('wpas-submitted', true);

                $form.find('button[type="submit"], input[type="submit"]').each(function () {
                    var $btn = $(this);
                    $btn.prop('disabled', true);

                    var originalText = $btn.data('wpas-original-text');
                    if (!originalText) {
                        $btn.data('wpas-original-text', $btn.text());
                        $btn.text($btn.text() + '...');
                    }
                });

                return true;
            });
        }
    };

    WPAS_Admin.init();
});
