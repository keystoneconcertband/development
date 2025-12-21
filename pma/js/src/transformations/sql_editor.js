/**
 * SQL syntax highlighting transformation plugin js
 *
 * @package PhpMyAdmin
 */
AJAX.registerOnload('transformations/sql_editor.js', function () {
    $('textarea.transform_sql_editor').each(function () {
        Functions.getSqlEditor($(this), {}, 'both');
    });
});


<!-- TODO: Remaining jQuery usages detected in this file. Manually port to vanilla JS or keep jQuery temporarily. -->
