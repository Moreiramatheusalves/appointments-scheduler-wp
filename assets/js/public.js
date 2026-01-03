jQuery(document).ready(function ($) {

    var $dialog = $('.wpas-booking-dialog');

    if (!$dialog.length) {
        return;
    }

    function openDialog() {
        $dialog.show();
        $('body').addClass('wpas-dialog-open');

        $(document).trigger('wpas_booking_opened');
    }

    function closeDialog() {
        $dialog.hide();
        $('body').removeClass('wpas-dialog-open');
        $(document).trigger('wpas_booking_closed');
    }

    $(document).on('click', '.wpas-open-booking-button', function () {
        openDialog();
    });

    $(document).on('click', '.wpas-booking-dialog-close', function () {
        closeDialog();
    });

    $dialog.on('click', function (e) {
        if ($(e.target).is('.wpas-booking-dialog')) {
            closeDialog();
        }
    });

    $(document).on('keyup', function (e) {
        if (e.key === 'Escape' || e.keyCode === 27) {
            if ($dialog.is(':visible')) {
                closeDialog();
            }
        }
    });
});
