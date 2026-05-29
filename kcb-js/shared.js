function kcbFetch(url, options) {
  options = options || {};
  options.headers = options.headers || {};
  if (!options.headers.Accept) {
    options.headers.Accept = 'application/json';
  }

  return fetch(url, options).then(function (response) {
    if (!response.ok) {
      throw new Error('Network response was not ok');
    }

    var contentType = response.headers.get('content-type') || '';
    if (contentType.indexOf('application/json') !== -1) {
      return response.json().then(function (body) {
        return typeof body === 'string' ? body.trim() : body;
      });
    }
    return response.text().then(function (text) {
      var trimmed = text.trim();
      if (!trimmed) {
        return trimmed;
      }

      if ((trimmed[0] === '"' && trimmed[trimmed.length - 1] === '"') ||
          (trimmed[0] === '{' && trimmed[trimmed.length - 1] === '}') ||
          (trimmed[0] === '[' && trimmed[trimmed.length - 1] === ']')) {
        try {
          var parsed = JSON.parse(trimmed);
          return typeof parsed === 'string' ? parsed.trim() : parsed;
        } catch (e) {
          return trimmed;
        }
      }

      return trimmed;
    });
  });
}

function kcbFetchJson(url, options) {
  options = options || {};
  options.headers = options.headers || {};
  options.headers.Accept = 'application/json';
  return kcbFetch(url, options);
}

function postFormData(url, form) {
  if (!form) {
    return Promise.reject(new Error('Missing form element'));
  }
  return kcbFetch(url, {
    method: 'POST',
    body: new FormData(form),
  });
}

function postUrlEncoded(url, params) {
  return kcbFetch(url, {
    method: 'POST',
    body: params,
  });
}

function bindValidatedFormSubmit(formSelector, onSubmit, onInvalid) {
  var form = document.querySelector(formSelector);
  if (!form) return null;

  form.addEventListener('submit', function (event) {
    form.classList.add('was-validated');

    if (event.defaultPrevented || !form.checkValidity()) {
      event.preventDefault();
      if (typeof onInvalid === 'function') {
        onInvalid(event);
      }
      return;
    }

    event.preventDefault();
    if (typeof onSubmit === 'function') {
      onSubmit(event);
    }
  });

  return form;
}

function shakeForm(form) {
  if (!form) return;
  form.classList.add('shake', 'animated');

  function removeClasses() {
    form.classList.remove('shake', 'animated');
    form.removeEventListener('animationend', removeClasses);
    form.removeEventListener('webkitAnimationEnd', removeClasses);
    form.removeEventListener('mozAnimationEnd', removeClasses);
    form.removeEventListener('MSAnimationEnd', removeClasses);
    form.removeEventListener('oanimationend', removeClasses);
  }

  form.addEventListener('animationend', removeClasses);
  form.addEventListener('webkitAnimationEnd', removeClasses);
  form.addEventListener('mozAnimationEnd', removeClasses);
  form.addEventListener('MSAnimationEnd', removeClasses);
  form.addEventListener('oanimationend', removeClasses);
}

function showAlert(selector, valid, msg) {
  var element = document.querySelector(selector);
  if (!element) return;

  // clear any existing auto-hide timer
  if (element._hideTimeout) {
    clearTimeout(element._hideTimeout);
    element._hideTimeout = null;
  }

  element.className = valid
    ? 'alert alert-success alert-dismissible fade show'
    : 'alert alert-danger alert-dismissible fade show';

  element.innerHTML =
    msg +
    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
  element.setAttribute('role', 'alert');
  element.classList.remove('d-none');

  // Auto-hide success alerts after 5 seconds. Errors remain until dismissed.
  if (valid) {
    element._hideTimeout = setTimeout(function () {
      element.classList.remove('alert', 'alert-success', 'alert-dismissible', 'fade', 'show');
      element.classList.add('d-none');
      element.innerHTML = '';
      element.removeAttribute('role');
      element._hideTimeout = null;
    }, 5000);
  }
}

function showMessage(selector, valid, msg, duplicateSelectors) {
  var element = document.querySelector(selector);
  var classes = valid ? 'h4 tada animated text-success' : 'h4 text-danger';

  if (element) {
    element.className = classes;
    element.textContent = msg;
  }

  if (Array.isArray(duplicateSelectors)) {
    duplicateSelectors.forEach(function (duplicateSelector) {
      var duplicate = document.querySelector(duplicateSelector);
      if (duplicate) {
        duplicate.className = classes;
        duplicate.textContent = msg;
      }
    });
  }
}

function populateForm(frm, data) {
  var form = document.querySelector(frm);
  if (!form || typeof data !== 'object' || data === null) return;

  Object.keys(data).forEach(function (key) {
    var field = form.querySelector('[name="' + key + '"]');
    if (field) {
      field.value = data[key];
    }
  });
}

function formatSizeUnits(bytes) {
  if (bytes >= 1073741824) {
    return (bytes / 1073741824).toFixed(2) + ' GB';
  }
  if (bytes >= 1048576) {
    return (bytes / 1048576).toFixed(2) + ' MB';
  }
  if (bytes >= 1024) {
    return (bytes / 1024).toFixed(2) + ' KB';
  }
  if (bytes > 1) {
    return bytes + ' bytes';
  }
  if (bytes === 1) {
    return '1 byte';
  }
  return '0 bytes';
}

function createEmailRow(emailCount, options) {
  options = options || {};
  var labelText = options.labelText || 'Email';
  var placeholderText = options.placeholderText || 'Email Address ' + emailCount;
  var rowClass = options.rowClass || 'emailContainers';
  var containerPrefix = options.containerPrefix || 'emailContainer';
  var labelHtml = labelText ? '<label for="email" class="form-label">' + labelText + '</label>' : '';
  var inputClass = 'form-control';
  if (emailCount === 1) {
    inputClass += ' email1';
  }

  return (
    '<div class="row mb-3 ' + rowClass + '" id="' + containerPrefix + emailCount + '">' +
      '<div class="col-sm-12">' +
        labelHtml +
        '<div class="input-group">' +
          '<input type="email" class="' + inputClass + '" name="email[]" id="email[]" placeholder="' + placeholderText + '" maxlength="100" value="">' +
          '<span class="input-group-text">' +
            '<a href="#noscroll" id="email' + emailCount + '" onclick="deleteEmail(\'' + containerPrefix + emailCount + '\');">' +
              '<span class="fa fa-remove"></span>' +
            '</a>' +
          '</span>' +
        '</div>' +
      '</div>' +
    '</div>'
  );
}

function deleteEmailRow(emailContainer, options) {
  options = options || {};
  var rowClass = options.rowClass || 'emailContainers';
  var minRows = typeof options.minRows === 'number' ? options.minRows : 2;
  var errorHandler = typeof options.errorHandler === 'function' ? options.errorHandler : null;

  var emailContainers = document.querySelectorAll('.' + rowClass);
  if (emailContainers.length < minRows) {
    if (errorHandler) {
      errorHandler('You must keep at least one email address.');
    }
    return;
  }

  var el = document.getElementById(emailContainer);
  if (el) {
    el.remove();
  }
}

function deleteEmail(emailContainer) {
  deleteEmailRow(emailContainer);
}

function populateEmail(data, options) {
  if (!data || typeof data.email !== 'string') return;

  options = options || {};
  var firstSelector = options.firstSelector || '.email1';
  var containerPrefix = options.containerPrefix || 'emailContainer';
  var rowClass = options.rowClass || 'emailContainers';
  var labelText = options.labelText || 'Email';

  var email = data.email;
  if (!email) return;

  var arr = email.split(',');
  var firstEmailField = document.querySelector(firstSelector);
  if (!firstEmailField) {
    firstEmailField = document.querySelector('input[name="email[]"]');
  }
  if (arr.length > 0 && firstEmailField) {
    firstEmailField.value = arr[0];
  }

  for (var i = 1; i < arr.length; i++) {
    var emailCount = i + 1;
    var container = document.getElementById(containerPrefix + i);
    var placeholderText = labelText ? labelText + ' ' + emailCount : 'Email Address ' + emailCount;
    var labelTextValue = labelText ? labelText + ' ' + emailCount : '';

    var labelHtml = labelTextValue
      ? '<label for="email" class="form-label">' + labelTextValue + '</label>'
      : '';

    var html =
      '<div class="row mb-3 ' + rowClass + '" id="' + containerPrefix + emailCount + '">' +
        '<div class="col-sm-12">' +
          labelHtml +
          '<div class="input-group">' +
            '<input type="email" class="form-control" name="email[]" id="email[]" placeholder="' + placeholderText + '" maxlength="100" value="' + arr[i] + '">' +
            '<span class="input-group-text">' +
              '<a href="#noscroll" id="email' + emailCount + '" onclick="deleteEmail(\'' + containerPrefix + emailCount + '\');">' +
                '<span class="fa fa-remove"></span>' +
              '</a>' +
            '</span>' +
          '</div>' +
        '</div>' +
      '</div>';

    if (container) {
      container.insertAdjacentHTML('afterend', html);
    }
  }
}

function populateInstrument(data) {
  if (!data || !data.instrument) return;

  var arr = data.instrument.split(',');
  arr.forEach(function (instrument) {
    var checkbox = document.getElementById(instrument);
    if (checkbox) {
      checkbox.checked = true;
    }
  });
}
