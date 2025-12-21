/* eslint-disable no-unused-vars */
/**
 * Dummy implementation of the ajax page loader
 */
var AJAX = {
  registerOnload: function (idx, func) {
    $(func);
  },
  registerTeardown: function (idx, func) {}
};

<!-- TODO: Remaining jQuery usages detected in this file. Manually port to vanilla JS or keep jQuery temporarily. -->
