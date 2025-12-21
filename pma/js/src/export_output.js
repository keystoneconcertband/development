AJAX.registerOnload('export_output.js', function () {
    $(document).on('keydown', function (e) {
        if ((e.which || e.keyCode) === 116) {
            e.preventDefault();
            $('#export_refresh_form').trigger('submit');
        }
    });

    $('.export_refresh_btn').on('click', function (e) {
        e.preventDefault();
        $('#export_refresh_form').trigger('submit');
    });
});


<!-- TODO: Remaining jQuery usages detected in this file. Manually port to vanilla JS or keep jQuery temporarily. -->
