/**
 * JSON syntax highlighting transformation plugin
 *
 * @package PhpMyAdmin
 */
AJAX.registerOnload('transformations/json_editor.js', function () {
  $('textarea.transform_json_editor').each(function () {
    CodeMirror.fromTextArea(this, {
      lineNumbers: true,
      matchBrackets: true,
      indentUnit: 4,
      mode: 'application/json',
      lineWrapping: true
    });
  });
});

<!-- TODO: Remaining jQuery usages detected in this file. Manually port to vanilla JS or keep jQuery temporarily. -->
