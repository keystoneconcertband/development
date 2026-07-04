document.addEventListener("DOMContentLoaded", function () {
  var columnsArray = [];

  // Only add actions column for admin/board members
  if (accountType === "1" || accountType === "2") {
    columnsArray.push({
      data: null,
      render: function (data) {
        return '<a href="#nojump"><span class="fa fa-trash-o" onclick="deleteRecord(\'' + data.fullName + '\', ' + data.uid + ')"></span></a>&nbsp;&nbsp;&nbsp;<a href="#nojump"><span class="fa fa-edit" onclick="showEditRecord(' + data.uid + ')"></span></a>';
      },
    });
  }

  columnsArray.push(
    { data: "fullName" },
    {
      data: null,
      render: function (data) {
        if (data.email) {
          var emailArr = data.email.split(",");
          var emailOut = "";
          for (var i = 0; i < emailArr.length; i++) {
            emailOut +=
              '<a href="mailto:' +
              emailArr[i] +
              '">' +
              emailArr[i] +
              "</a><br />";
          }
          return emailOut;
        }
        return "";
      },
    },
    {
      data: null,
      render: function (data) {
        if (data.instrument) {
          return data.instrument.replace(/,/g, "<br/>");
        }
        return "";
      },
    },
    {
      data: null,
      render: function (data) {
        if (data.text) {
          return data.text.replace(/(\d{3})(\d{3})(\d{4})/, "$1-$2-$3");
        }
        return "";
      },
    },
    {
      data: null,
      render: function (data) {
        if (data.address1) {
          var addr = data.address1 + "<br />";
          if (data.address2) {
            addr += data.address2 + "<br />";
          }
          addr += data.city + ", " + data.state + " " + data.zip;
          return addr;
        }
        return "";
      },
    },
    {
      data: null,
      render: function (data) {
        if (data.office) {
          return data.office;
        }
        return "";
      },
    }
  );

  // Only add emergency contact column for admin/board members
  if (accountType === "1" || accountType === "2") {
    columnsArray.push({
      data: null,
      render: function (data) {
        var contact = "";
        if (data.emergency_contact_name) {
          contact = data.emergency_contact_name;
          if (data.emergency_contact_phone) {
            contact += "<br />" + data.emergency_contact_phone.replace(/(\d{3})(\d{3})(\d{4})/, "$1-$2-$3");
          }
        } else if (data.emergency_contact_phone) {
          contact = data.emergency_contact_phone.replace(/(\d{3})(\d{3})(\d{4})/, "$1-$2-$3");
        }
        return contact;
      },
    });
  }

  var table = $("#kcbMemberTable").DataTable({
    order: [1, "asc"],
    responsive: true,
    ajax: {
      url: "membersServer.php",
      dataSrc: "",
    },
    columns: columnsArray,
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
        labelText: "",
        placeholderText: "Email Address " + emailCount,
      });
      lastContainer.insertAdjacentHTML("afterend", html);
      var newContainer = document.getElementById("emailContainer" + emailCount);
      if (newContainer) {
        newContainer.style.display = "block";
      }
    });
  }
});

var formMember = document.getElementById("form_member");
if (formMember) {
  formMember.addEventListener("submit", function (event) {
    formMember.classList.add("was-validated");
    if (event.defaultPrevented || !formMember.checkValidity()) {
      event.preventDefault();
      formError("Check for errors in the form.");
    } else {
      event.preventDefault();
      submitForm();
    }
  });
}

var modalEditDelete = document.getElementById("modal_edit_delete");
if (modalEditDelete) {
  modalEditDelete.addEventListener("show.bs.modal", function () {
    var firstName = document.getElementById("firstName");
    var modalEditDeleteLabel = document.getElementById("modalEditDeleteLabel");
    if (firstName && firstName.value !== "") {
      modalEditDeleteLabel.textContent = "Edit Member";
    } else if (modalEditDeleteLabel) {
      modalEditDeleteLabel.textContent = "Add Member";
    }

    var formAlert = document.getElementById("formAlert");
    if (formAlert) {
      formAlert.className = "alert d-none alert-dismissible fade show";
      formAlert.textContent = "";
    }
  });

  modalEditDelete.addEventListener("hidden.bs.modal", function () {
    if (formMember) {
      formMember.reset();
      formMember.classList.remove("was-validated");
    }
    var uid = document.getElementById("uid");
    if (uid) uid.value = "";
    document.querySelectorAll(".emailContainers").forEach(function (el) {
      el.remove();
    });
    var textContainer = document.getElementById("textContainer");
    if (textContainer) {
      textContainer.insertAdjacentHTML(
        "afterend",
        createEmailRow(1, {
          labelText: "Email Address(es)",
          placeholderText: "Email Address",
        }),
      );
    }
  });
}

function submitForm() {
  if (!formMember) return;
  var uidField = document.getElementById("uid");
  if (uidField && uidField.value !== "") {
    editRecord();
    return;
  }
  addRecord();
}

function addRecord() {
  if (!formMember) return;
  var formData = new URLSearchParams(new FormData(formMember));
  formData.append("type", "add");

  postUrlEncoded("membersServer.php", formData)
    .then(function (text) {
      if (text === "success") {
        formSuccess("User successfully added.");
      } else {
        formError(text);
      }
    })
    .catch(function (error) {
      console.log("Fetch error:", error);
      formError("Oops! An error occurred processing the form. Please try again later.");
    });
}

function showEditRecord(uid) {
  var params = new URLSearchParams({
    type: "getMemberRecord",
    uid: uid.toString(),
  });

  postUrlEncoded("membersServer.php", params)
    .then(function (data) {
      populateForm("#form_member", data);
      populateEmail(data, {
        firstSelector: ".email1",
        containerPrefix: "emailContainer",
        rowClass: "emailContainers",
        labelText: "Email",
      });
      populateInstrument(data);

      if (data.displayFullName === 1) {
        var displayFullName = document.getElementById("displayFullName");
        if (displayFullName) displayFullName.checked = true;
      }
      var uidField = document.getElementById("uid");
      if (uidField) uidField.value = uid;
      var modal = document.getElementById("modal_edit_delete");
      if (modal) {
        var bsModal = bootstrap.Modal.getOrCreateInstance(modal);
        bsModal.show();
      }
    })
    .catch(function (xhr) {
      formError("Oops! An error occurred opening the form. Please try again later.");
      console.log(xhr);
    });
}

function editRecord() {
  if (!formMember) return;
  var formData = new URLSearchParams(new FormData(formMember));
  formData.append("type", "edit");

  postUrlEncoded("membersServer.php", formData)
    .then(function (text) {
      if (text === "success") {
        formSuccess("User successfully modified.");
      } else {
        formError(text);
      }
    })
    .catch(function (error) {
      console.log("Edit Fetch error:", error);
      formError("Oops! An error occurred processing the form. Please try again later.");
    });
}

function deleteRecord(title, uid) {
  if (
    !confirm(
      "Do you want to remove " +
        title +
        " from the band roster and email list?",
    )
  ) {
    return;
  }

  var params = new URLSearchParams({ type: "delete", uid: uid.toString() });

  postUrlEncoded("membersServer.php", params)
    .then(function (text) {
      if (text === "success") {
        formSuccess("User successfully removed.");
      } else {
        formError(text);
      }
    })
    .catch(function (xhr) {
      formError("Oops! An error occurred processing the form. Please try again later.");
      console.log(xhr);
    });
}

function formSuccess(text) {
  showAlert('#pageAlert', true, text);
  var table = $("#kcbMemberTable").DataTable();
  if (table) {
    table.ajax.reload();
  }
  if (formMember) {
    formMember.reset();
    formMember.classList.remove("was-validated");
  }
  var modal = document.getElementById("modal_edit_delete");
  if (modal) {
    var bsModal = bootstrap.Modal.getInstance(modal);
    if (bsModal) bsModal.hide();
  }
}

function formError(text) {
  if (!formMember) return;
  shakeForm(formMember);
  showAlert('#formAlert', false, text);
}

function printMembers() {
  var win = window.open(
    "membersPrint.php",
    "Print Members",
    "menubar=0,location=0,height=700,width=700",
  );
}

var phoneInput = document.getElementById("text");
if (phoneInput) {
  phoneInput.addEventListener("input", function () {
    try {
      var cleaned = this.value.replace(/\D/g, "");
      this.value = cleaned;
    } catch (err) {
      console.error("Error cleaning phone number:", err);
    }
  });
}

