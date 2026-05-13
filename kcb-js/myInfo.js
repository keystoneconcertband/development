document.addEventListener("DOMContentLoaded", function () {
  var params = new URLSearchParams({ type: "getCurrentMemberRecord" });

  fetch("membersServer.php", {
    method: "POST",
    body: params,
    headers: {
      Accept: "application/json",
    },
  })
    .then(function (response) {
      if (!response.ok) {
        throw new Error("HTTP error! status: " + response.status);
      }
      return response.json();
    })
    .then(function (data) {
      populateForm("#memberInfo", data);
      populateEmail(data);
      populateInstrument(data);

      if (data.displayFullName === 1) {
        var displayFullName = document.getElementById("displayFullName");
        if (displayFullName) displayFullName.checked = true;
      }
    })
    .catch(function (xhr) {
      submitMSG(
        false,
        "Oops! An error occurred opening the form. Please try again later.",
      );
      console.log(xhr);
    });

  var addRowButton = document.getElementById("addRow");
  if (addRowButton) {
    addRowButton.addEventListener("click", function () {
      var emailContainers = document.querySelectorAll(".emailContainers");
      if (!emailContainers.length) return;
      var lastContainer = emailContainers[emailContainers.length - 1];
      var lastId = lastContainer.id.replace("emailContainer", "");
      var lastIdInt = parseInt(lastId, 10);
      var emailCount = lastIdInt + 1;
      var html =
        '<div class="row mb-3 emailContainers" id="emailContainer' +
        emailCount +
        '" style="display:none"><div class="col-sm-12"><div class="input-group"><input type="email" class="form-control" name="email[]" id="email[]" placeholder="Email Address ' +
        emailCount +
        '" maxlength="100" value=""><span class="input-group-text"><a href="#noscroll" id="email' +
        emailCount +
        '" onclick="deleteEmail(\'emailContainer' +
        emailCount +
        '\');"><span class="fa fa-remove"></span></a></span></div></div></div>';
      lastContainer.insertAdjacentHTML("afterend", html);
      var newContainer = document.getElementById("emailContainer" + emailCount);
      if (newContainer) {
        newContainer.style.display = "block";
      }
    });
  }
});

var memberInfoForm = document.getElementById("memberInfo");
if (memberInfoForm) {
  memberInfoForm.addEventListener("submit", function (event) {
    if (event.defaultPrevented || !memberInfoForm.checkValidity()) {
      event.preventDefault();
      formError();
      submitMSG(
        false,
        "Oops! Looks like you have a validation error. Check for errors in the form.",
      );
    } else {
      event.preventDefault();
      submitForm();
    }
  });
}

function submitForm() {
  if (!memberInfoForm) return;
  var formData = new URLSearchParams(new FormData(memberInfoForm));

  fetch("myInfoServer.php", {
    method: "POST",
    body: formData,
    headers: {
      Accept: "application/json",
    },
  })
    .then(function (response) {
      return response.json();
    })
    .then(function (text) {
      console.log(text);
      if (text === "success") {
        formSuccess();
      } else {
        formError(text);
      }
    })
    .catch(function (xhr) {
      submitMSG(
        false,
        "Oops! An error occurred processing the form. Please try again later.",
      );
      console.log(xhr);
    });
}

function formSuccess() {
  var pageAlert = document.getElementById("pageAlert");
  if (pageAlert) {
    pageAlert.className = "alert alert-success alert-dismissible fade show";
    pageAlert.innerHTML =
      'Your information has been updated!<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
    pageAlert.setAttribute("role", "alert");
    window.scrollTo(0, 0);
  }
}

function formError(text) {
  if (!memberInfoForm) return;
  memberInfoForm.classList.add("shake", "animated");
  function removeClasses() {
    memberInfoForm.classList.remove("shake", "animated");
    memberInfoForm.removeEventListener("animationend", removeClasses);
    memberInfoForm.removeEventListener("webkitAnimationEnd", removeClasses);
    memberInfoForm.removeEventListener("mozAnimationEnd", removeClasses);
    memberInfoForm.removeEventListener("MSAnimationEnd", removeClasses);
    memberInfoForm.removeEventListener("oanimationend", removeClasses);
  }
  memberInfoForm.addEventListener("animationend", removeClasses);
  memberInfoForm.addEventListener("webkitAnimationEnd", removeClasses);
  memberInfoForm.addEventListener("mozAnimationEnd", removeClasses);
  memberInfoForm.addEventListener("MSAnimationEnd", removeClasses);
  memberInfoForm.addEventListener("oanimationend", removeClasses);

  submitMSG(false, text);
}

function submitMSG(valid, msg) {

  var formAlert = document.getElementById("pageAlert");
  if (!formAlert) return;

  var alertClasses = valid
    ? "alert alert-success alert-dismissible fade show"
    : "alert alert-danger alert-dismissible fade show";
  formAlert.className = alertClasses;
  formAlert.innerHTML =
    msg +
    '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
  formAlert.setAttribute("role", "alert");
  window.scrollTo(0, 0);
}

function deleteEmail(emailContainer) {
  var numItems = document.querySelectorAll(".emailContainers").length;
  if (numItems < 2) {
    formError("You must keep at least one email address.");
  } else {
    var el = document.getElementById(emailContainer);
    if (el) el.remove();
  }
}

function populateForm(frm, data) {
  var form = document.querySelector(frm);
  if (!form) return;
  Object.keys(data).forEach(function (key) {
    var field = form.querySelector('[name="' + key + '"]');
    if (field) field.value = data[key];
  });

  var firstName = document.getElementById("firstName");
  var lastName = document.getElementById("lastName");
  var spanFirstName = document.getElementById("spanFirstname");
  var spanLastInitial = document.getElementById("spanLastInitial");
  spanFirstName.innerText = firstName.value;
  spanLastInitial.textContent = lastName.value.charAt(0);
}

function populateEmail(data) {
  var email = data.email;
  if (email !== null && email !== "") {
    if (email.indexOf(",") !== -1) {
      var arr = email.split(",");
      for (var i = 0; i < arr.length; i++) {
        var emailCount = i + 1;
        if (i === 0) {
          var email1 = document.querySelector(".email1");
          if (email1) email1.value = arr[i];
        } else {
          var container = document.getElementById("emailContainer" + i);
          if (container) {
            container.insertAdjacentHTML(
              "afterend",
              '<div class="row mb-3 emailContainers" id="emailContainer' +
              emailCount +
              '"><div class="col-sm-12"><div class="input-group"><input type="email" class="form-control" name="email[]" id="email[]" placeholder="Email Address ' +
              emailCount +
              '" maxlength="100" value="' +
              arr[i] +
              '"><span class="input-group-text"><a href="#noscroll" id="email' +
              emailCount +
              '" onclick="deleteEmail(\'emailContainer' +
              emailCount +
              '\');"><span class="fa fa-remove"></span></a></span></div></div></div>',
            );
          }
        }
      }
    } else {
      var email1 = document.querySelector(".email1");
      if (email1) email1.value = email;
    }
  }
}

function populateInstrument(data) {
  if (data.instrument) {
    var arr = data.instrument.split(",");
    for (var i = 0; i < arr.length; i++) {
      var checkbox = document.getElementById(arr[i]);
      if (checkbox) checkbox.checked = true;
    }
  }
}
