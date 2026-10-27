document.addEventListener("DOMContentLoaded", function () {
  var params = new URLSearchParams({ type: "getCurrentMemberRecord" });

  postUrlEncoded("membersServer.php", params)
    .then(function (data) {
      populateForm("#memberInfo", data);
      updateNameDisplay();
      populateEmail(data);
      populateInstrument(data);

      if (data.displayFullName === 1) {
        var displayFullName = document.getElementById("displayFullName");
        if (displayFullName) displayFullName.checked = true;
      }
    })
    .catch(function (error) {
      showAlert('#pageAlert', false, 'Oops! An error occurred opening the form. Please try again later.');
      console.log(error);
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
      var html = createEmailRow(emailCount, {
        placeholderText: 'Email Address ' + emailCount,
        rowClass: 'emailContainers'
      });
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
    memberInfoForm.classList.add("was-validated");
    if (event.defaultPrevented || !memberInfoForm.checkValidity()) {
      event.preventDefault();
      showAlert('#pageAlert', false, "Oops! Looks like you have a validation error. Check for errors in the form.");
    } else {
      event.preventDefault();
      submitForm();
    }
  });
}

function submitForm() {
  if (!memberInfoForm) return;
  var formData = new URLSearchParams(new FormData(memberInfoForm));

  postUrlEncoded('myInfoServer.php', formData)
    .then(function (text) {
      if (text === 'success') {
        showAlert('#pageAlert', true, 'Your information has been updated.');
      } else {
        showAlert('#pageAlert', true, text);
      }
    })
    .catch(function (error) {
      showAlert('#pageAlert', false, "Oops! Looks like you have a validation error. Check for errors in the form.");
      console.log(error);
    });
}

function deleteEmail(emailContainer) {
  deleteEmailRow(emailContainer, {
    minRows: 2,
    errorHandler: function (errorText) {
        showAlert('#pageAlert', true, errorText);
    },
  });
}

function updateNameDisplay() {
  var firstName = document.getElementById('firstName');
  var lastName = document.getElementById('lastName');
  var spanFirstName = document.getElementById('spanFirstname');
  var spanLastInitial = document.getElementById('spanLastInitial');

  if (firstName && spanFirstName) {
    spanFirstName.innerText = firstName.value;
  }
  if (lastName && spanLastInitial) {
    spanLastInitial.textContent = lastName.value.charAt(0);
  }
}

