/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 */
"use strict";

$(document).ready(function () {
  $("#loading").hide();
});

function showToastMessage(message, type) {
  switch (type) {
    case "error":
      $().ready(
        iziToast.error({
          title: "Error",
          message: message,
          position: "topRight",
          timeout: 10000,
          pauseOnHover: true,
        })
      );
      break;
    case "success":
      $().ready(
        iziToast.success({
          title: "Success",
          message: message,
          position: "topRight",
         

          timeout:15000,
        })
      );
      break;
  }
}
// if ($(".summernotes").length) {
//     tinymce.init({
//       selector: ".summernotes",
//       height: 200,
//       menubar: true,
//       plugins: [
//         "a11ychecker", "advlist", "advcode", "advtable", "autolink", "checklist", "export", "lists", "link", "image", "charmap", "preview", "code", "anchor", "searchreplace", "visualblocks", "powerpaste", "fullscreen", "formatpainter", "insertdatetime", "media", "directionality", "table", "help", "wordcount", "imagetools",
//       ],
//       toolbar:
//         "undo redo | image media | code fullscreen| formatpainter casechange blocks fontsize | bold italic forecolor backcolor | " +
//         "alignleft aligncenter alignright alignjustify | " +
//         "bullist numlist checklist outdent indent | removeformat | ltr rtl |a11ycheck table help",
//       maxlength: null, // Remove text limit
//       images_upload_handler: function (blobInfo, success, failure) {
//         // Simulating image upload delay for demonstration purposes
//         setTimeout(function () {
//           // Simulate successful image upload
//           var uploadedImageUrl = "https://example.com/uploaded-image.jpg";
//           success(uploadedImageUrl);
//           // In case of failure, use the following line:
//           // failure();
//         }, 2000); // Change the delay time as needed
//       },
//       image_uploadtab: true,
//     });
//   }

if ($(".summernotes").length) {
  tinymce.init({
    selector: ".summernotes",
    height: 200,
    menubar: true,
    plugins: [
      "a11ychecker",
      "advlist",
      "advcode",
      "advtable",
      "autolink",
      "checklist",
      "export",
      "lists",
      "link",
      "image",
      "charmap",
      "preview",
      "code",
      "anchor",
      "searchreplace",
      "visualblocks",
      "powerpaste",
      "fullscreen",
      "formatpainter",
      "insertdatetime",
      "media",
      "directionality",
      "table",
      "help",
      "wordcount",
      "imagetools",
    ],
    toolbar:
      "undo redo | image media | code fullscreen| formatpainter casechange blocks fontsize | bold italic forecolor backcolor | " +
      "alignleft aligncenter alignright alignjustify | " +
      "bullist numlist checklist outdent indent | removeformat | ltr rtl |a11ycheck table help",
    maxlength: null, // Remove text limit

    relative_urls: false,
    remove_script_host: false,
    document_base_url: baseUrl,

    file_picker_callback: function (callback, value, meta) {
      // Create a file input element
      var input = document.createElement("input");
      input.setAttribute("type", "file");
      input.setAttribute("accept", "image/*"); // Specify the accepted file types

      // Listen for the change event when a file is selected
      input.addEventListener("change", function (e) {
        var file = e.target.files[0];

        // Use FileReader API to read the selected file
        var reader = new FileReader();
        reader.onload = function () {
          var dataUrl = reader.result;
          // Invoke the callback function with the file data URL
          callback(dataUrl, {
            text: file.name, // Display the file name as the link text
          });
        };
        reader.readAsDataURL(file);
      });

      // Trigger the file input dialog
      input.click();
    },
    image_uploadtab: true,
  });
}

//  if ($(".summernotes").length) {
// tinymce.init({
//     selector: '.summernotes',
//     plugins: [
//         'a11ychecker', 'advlist', 'advcode', 'advtable', 'autolink', 'checklist', 'export',
//         'lists', 'link', 'image', 'charmap', 'preview', 'code', 'anchor', 'searchreplace', 'visualblocks',
//         'powerpaste', 'fullscreen', 'formatpainter', 'insertdatetime', 'media', 'image', 'directionality', 'fullscreen', 'table', 'help', 'wordcount'
//     ],
//     toolbar: 'undo redo | image media | code fullscreen| formatpainter casechange blocks fontsize | bold italic forecolor backcolor | ' +
//         'alignleft aligncenter alignright alignjustify | ' +
//         'bullist numlist checklist outdent indent | removeformat | ltr rtl |a11ycheck table help',

//     font_size_formats: '8pt 10pt 12pt 14pt 16pt 18pt 24pt 36pt 48pt',
//     image_uploadtab: false,
//     images_upload_url: base_url + "admin/media/upload",
//     relative_urls: false,
//     remove_script_host: false,
//     file_picker_types: 'image media',
//     media_poster: false,
//     media_alt_source: false,

// });

function comming_soon(element) {
  console.log("ohh yehg");
}
$(document).ready(function () {
  var check_box = $(".check_box");
  var start_time = $(".start_time");
  var end_time = $(".end_time");
  $(".check_box").on("click", function () {
    for (let index = 0; index < check_box.length; index++) {
      if (!$(check_box[index]).is(":checked")) {
        $(start_time[index]).attr("readOnly", "readOnly");
        $(end_time[index]).attr("readOnly", "readOnly");
      } else {
        $(start_time[index]).removeAttr("readOnly");
        $(end_time[index]).removeAttr("readOnly");
      }
    }
  });
  for (let index = 0; index < check_box.length; index++) {
    if (!$(check_box[index]).is(":checked")) {
      $(start_time[index]).attr("readOnly", "readOnly");
      $(end_time[index]).attr("readOnly", "readOnly");
    } else {
      $(start_time[index]).removeAttr("readOnly");
      $(end_time[index]).removeAttr("readOnly");
    }
  }
});
var order_status_filter = "";
$("#order_status_filter").on("change", function () {
  order_status_filter = $(this).find("option:selected").val();
});

var order_provider_filter = "";
$("#order_provider_filter").on("change", function () {
  order_provider_filter = $(this).find("option:selected").val();
});
$("#filter").on("click", function (e) {
  $("#user_list").bootstrapTable("refresh");
});
// order filter params
function orders_query(p) {
  return {
    // search: p.search,
    search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
    order_status_filter: order_status_filter,
    order_provider_filter: order_provider_filter,
  };
}

// var dashboard_status_filter = "";
// $('#datepicker').on('click', function () {

//     dashboard_status_filter = $(this).val();
//     console.log(dashboard_status_filter);
// });

$("#filter").on("click", function (e) {
  $("#user_list").bootstrapTable("refresh");
});
// order filter params

function fetch_cites(element) {
  $.ajax({
    type: "POST",
    url: "delete_details",
    data: {
      id: $(element).data("id"),
    },
    dataType: "json",
    success: function (result) {
      console.log(result);
      /* setting new CSRF for the next request */
      csrfName = result.csrfName;
      csrfHash = result.csrfHash;
      if (result.error == false) {
        iziToast.success({
          title: "Success",
          message: result.message,
          position: "topRight",
        });
        var tableId = $(element).data("table-id");
        // window.location.reload();
      } else {
        iziToast.error({
          title: "Error",
          message: result.message,
          position: "topRight",
        });
      }
    },
  });
}
function delete_details(element) {
  $.ajax({
    type: "POST",
    url: "delete_details",
    data: {
      id: $(element).data("id"),
      table: $(element).data("table"),
      csrf_test_name: csrfHash,
    },
    dataType: "json",
    success: function (result) {
      console.log(result);
      /* setting new CSRF for the next request */
      csrfName = result.csrfName;
      csrfHash = result.csrfHash;
      if (result.error == false) {
        iziToast.success({
          title: "Success",
          message: result.message,
          position: "topRight",
        });
        var tableId = $(element).data("table-id");
        $("#" + tableId).bootstrapTable("refresh");
        // window.location.reload();
      } else {
        iziToast.error({
          title: "Error",
          message: result.message,
          position: "topRight",
        });
      }
    },
  });
}
function set_locale(language_code) {
  $.ajax({
    url: baseUrl + "/lang/" + language_code,
    type: "GET",
    success: function (result) {},
  }).then(() => {
    location.reload();
  });
}
// change delivery  methods
$("#delivery_charge_method").on("change", function () {
  if ($(this).val() == "fixed_charge") {
    // console.log('hello');
    if ($(".delivery_charge_method_result").hasClass("d-none")) {
      $(".delivery_charge_method_result").removeClass("d-none");
    }
    $(".delivery_charge_method_result").html(
      '<label for="" class="label_title">Fixed charges</label><input type="text" class="form-control" name="fixed_charge" placeholder="fixed charge">'
    );
    $(".range_wise_km").addClass("d-none");
  } else if ($(this).val() == "per_km_charge") {
    if ($(".delivery_charge_method_result").hasClass("d-none")) {
      $(".delivery_charge_method_result").removeClass("d-none");
    }
    $(".delivery_charge_method_result").html(
      '<label for="" class="label_title">Per KM Charges</label><input type="text" class="form-control" name="per_km_charge" placeholder="per km charge">'
    );
    $(".range_wise_km").addClass("d-none");
  } else if ($(this).val() == "range_wise_charges") {
    $(".delivery_charge_method_result").addClass("d-none");
    $(".range_wise_km").removeClass("d-none");
  } else {
    $(".delivery_charge_method_result").addClass("d-none");
    $(".range_wise_km").addClass("d-none");
  }
});
$("#add-city").on("submit", function (e) {
  var formData = new FormData(this);
  formData.append(csrfName, csrfHash);
  e.preventDefault();
  $.ajax({
    type: $(this).attr("method"),
    url: $(this).attr("action"),
    data: formData,
    dataType: "json",
    beforeSend: function () {
      $("#btnAdd").attr("disabled", true);
      $("#btnAdd").removeClass("btn-primary");
      $("#btnAdd").addClass("btn-secondary");
      $("#btnAdd").html(
        '<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
      );
    },
    processData: false,
    contentType: false,
    success: function (result) {
      $("#btnAdd").attr("disabled", false);
      $("#btnAdd").addClass("btn-primary");
      $("#btnAdd").removeClass("btn-secondary");
      $("#btnAdd").html("Add city");
      console.log(result);
      /* setting new CSRF for the next request */
      csrfName = result.csrfName;
      csrfHash = result.csrfHash;
      if (result.error == false) {
        iziToast.success({
          title: "Success",
          message: result.message,
          position: "topRight",
        });
        $(".close").click();
        window.location.reload();
      } else {
        iziToast.error({
          title: "Error",
          message: result.message,
          position: "topRight",
        });
      }
    },
  });
});
/* remove language link */
$(".delete-language-btn").on("click", function (e) {
  e.preventDefault();
  if (confirm("Are you sure want to delete language?")) {
    window.location.href = $(this).attr("href");
  }
});
function active_sub(element) {
  $("#user_id").val($(element).data("uid"));
  $("#id").val($(element).data("sid"));
}
function receipt_check(element) {
  $("#bank_transfer_id").val($(element).data("id"));
  $("#user_id").val($(element).data("uid"));
}
/* bank_transfers query params */
function bank_transfer_params(p) {
  var subscription_id = $("#subscription_id").val()
    ? $("#subscription_id").val()
    : "";
  return {
    user_id: $("#user_id").val(),
    subscription_id: subscription_id,
    is_saved: $("#is_saved").val(),
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
    search: p.search,
  };
}
$(document).ready(function () {
  $(document).on("click", ".view-reciepts", function () {
    var subscription_id = $(this).attr("data-id");
    $("#subscription_id").val(subscription_id);
    $("#bank_transfer").bootstrapTable("refresh");
  });
});
// ajax
// for checking reciept form admin-side
$(document).ready(function () {
  let status = $("input[type=radio][name=pending]");
  $("#reciept_check_form").on("submit", function (e) {
    e.preventDefault();
    if ($("#pending").is(":checked")) {
      swal.fire({
        title: "Status Change",
        text: "you must change status to either accepted or rejected",
        icon: "warning",
      });
      return false;
    }
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);
    $.ajax({
      type: $(this).attr("method"),
      url: $(this).attr("action"),
      data: formData,
      dataType: "json",
      beforeSend: function () {
        $("#update_receipt_btn").attr("disabled", true);
        $("#update_receipt_btn").html("Updating.. .");
      },
      processData: false,
      contentType: false,
      success: function (result) {
        console.log(result);
        /* setting new CSRF for the next request */
        csrfName = result.csrfName;
        csrfHash = result.csrfHash;
        $("#update_receipt_btn").html("Uploading receipt");
        $("#update_receipt_btn").attr("disabled", false);
        if (result.error == false) {
          iziToast.success({
            title: "Success",
            message: result.message,
            position: "topRight",
          });
          $(".close").click();
          window.location.reload();
        } else {
          iziToast.error({
            title: "Error",
            message: result.message,
            position: "topRight",
          });
        }
      },
    });
  });
});
// for uploading reciept form custome-side
$(document).ready(function () {
  $("#upload_form").on("submit", function (e) {
    e.preventDefault();
    let formdata = new FormData(this);
    formdata.append(csrfName, csrfHash);
    console.log(formdata);
    $.ajax({
      type: $(this).attr("method"),
      url: $(this).attr("action"),
      data: formdata,
      dataType: "json",
      cache: false,
      beforeSend: function () {
        $("#update_receipt_btn").attr("disabled", true);
        $("#update_receipt_btn").html("Updating.. .");
      },
      processData: false,
      contentType: false,
      success: function (result) {
        csrfName = result.csrfName;
        csrfHash = result.csrfHash;
        $("#update_receipt_btn").html("Uploading receipt");
        $("#update_receipt_btn").attr("disabled", false);
        if (result.error == false) {
          iziToast.success({
            title: "Success",
            message: result.message,
            position: "topRight",
          });
          $(".close").click();
          window.location.reload();
        } else {
          iziToast.error({
            title: "Error",
            message: result.message,
            position: "topRight",
          });
        }
      },
    });
  });
});
// for active subscription form adminside-side
$(document).ready(function () {
  $("#active_subscriptions_form").on("submit", function (e) {
    e.preventDefault();
    let formdata = new FormData($(this)[0]);
    formdata.append(csrfName, csrfHash);
    console.log(formdata);
    $.ajax({
      type: $(this).attr("method"),
      url: $(this).attr("action"),
      data: formdata,
      dataType: "json",
      beforeSend: function () {
        $("#active_btn").attr("disabled", true);
        $("#active_btn").html("Activating...");
      },
      processData: false,
      contentType: false,
      success: function (result) {
        csrfName = result.csrfName;
        csrfHash = result.csrfHash;
        console.log(result);
        $("#active_btn").html("Uploading receipt");
        $("#active_btn").attr("disabled", false);
        if (result.error == false) {
          iziToast.success({
            title: "Success",
            message: result.message,
            position: "topRight",
          });
          $(".close").click();
          window.location.reload();
        } else {
          iziToast.error({
            title: "Error",
            message: result.message,
            position: "topRight",
          });
        }
      },
    });
  });
});
// user activation and deactivation
function activate_user(element) {
  $("#user_id_active").val($(element).data("uid"));
}
function deactivate_user(element) {
  $("#user_id").val($(element).data("uid"));
}
$(document).ready(function () {
  $("#deactivate_user_form").on("submit", function (e) {
    e.preventDefault();
    let formdata = new FormData(this);
    formdata.append(csrfName, csrfHash);
    console.log(formdata);
    $.ajax({
      type: $(this).attr("method"),
      url: $(this).attr("action"),
      data: formdata,
      dataType: "json",
      cache: false,
      beforeSend: function () {
        $("#deactive_btn").attr("disabled", true);
        $("#deactive_btn").html("Deactivating.. .");
      },
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.error == false) {
          iziToast.success({
            title: "Success",
            message: response.message,
            position: "topRight",
          });
          $("#deactive_btn").attr("disabled", false);
          $("#deactive_btn").html("Deactivate User");
          $(".close").click();
          $("#user_list").bootstrapTable("refresh");
        } else {
          iziToast.error({
            title: "Error",
            message: response.message,
            position: "topRight",
          });
          $(".close").click();
          window.location.reload();
        }
      },
    });
  });
});
$(document).ready(function () {
  $("#activate_user_form").on("submit", function (e) {
    e.preventDefault();
    let formdata = new FormData(this);
    formdata.append(csrfName, csrfHash);
    console.log(formdata);
    $.ajax({
      type: $(this).attr("method"),
      url: $(this).attr("action"),
      data: formdata,
      dataType: "json",
      cache: false,
      beforeSend: function () {
        $("#activate_btn").attr("disabled", true);
        $("#activate_btn").html("Activating.. .");
      },
      processData: false,
      contentType: false,
      success: function (response) {
        if (response.error == false) {
          iziToast.success({
            title: "Success",
            message: response.message,
            position: "topRight",
          });
          $("#activate_btn").attr("disabled", false);
          $("#activate_btn").html("Activated...");
          $(".close").click();
          $("#user_list").bootstrapTable("refresh");
        } else {
          iziToast.error({
            title: "Error",
            message: response.message,
            position: "topRight",
          });
          $(".close").click();
          window.location.reload();
        }
      },
    });
  });
});
// updatiung categories
$(document).ready(function () {
  $("#update_category_process").on("submit", function (e) {
    e.preventDefault();
    let formdata = new FormData($(this)[0]);
    formdata.append(csrfName, csrfHash);
    var name = $("#name").val();
    console.log(formdata, name);
    $.ajax({
      type: $(this).attr("method"),
      url: $(this).attr("action"),
      data: formdata,
      dataType: "json",
      processData: false,
      contentType: false,
      beforeSend: function () {
        $("#Category_btn").attr("disabled", true);
        $("#Category_btn").html("Adding.. .");
      },
      success: function (response) {
        if (response.error == false) {
          iziToast.success({
            title: "Success",
            message: response.message,
            position: "topRight",
          });
          setTimeout(function () {
            location.href = baseUrl + "/admin/categories";
          }, 500);
        } else {
          iziToast.error({
            title: "Error",
            message: response.message,
            position: "topRight",
          });
          setTimeout(function () {
            location.href = baseUrl + "admin/categories";
          }, 500);
        }
      },
    });
  });
});
$(document).ready(function () {
  if ($("#password") != null && $("#confirm_password") != null) {
    $("#confirm_password").on("blur", function (e) {
      if ($("#password").val() == "") {
        $("#password").css("border-color", "#FF3300");
        showToastMessage("Empty Password", "error");
        return false;
      }
    });
    $("#confirm_password").on("blur", function (e) {
      if ($("#confirm_password").val() == "") {
        $("#password").css("border-color", "#FF3300");
        $("#confirm_password").css("border-color", "#FF3300");
        showToastMessage("Empty Confirm Password", "error");
        return false;
      } else if ($("#password").val() != $("#confirm_password").val()) {
        e.preventDefault();
        $("#password").css("border-color", "#FF3300");
        $("#confirm_password").css("border-color", "#FF3300");
        showToastMessage("Mis Match Password", "error");
        return false;
      } else {
        $("#password").css("border-color", "#66FF00");
        $("#confirm_password").css("border-color", "#66FF00");
        return true;
      }
    });
  }
  $(document).on("submit", ".form-submit-event", function (e) {
    e.preventDefault();
    var formData = new FormData(this);
    var form_id = $(this).attr("id");
    var error_box = $("#error_box", this);
    var submit_btn = $(this).find(".submit_btn");

    console.log("submit button clicked");
    var btn_html = $(this).find(".submit_btn").html();
    var btn_val = $(this).find(".submit_btn").val();
    var button_text =
      btn_html != "" || btn_html != "undefined" ? btn_html : btn_val;
    // password section for system users
    formData.append(csrfName, csrfHash);
    $.ajax({
      type: "POST",
      url: $(this).attr("action"),
      data: formData,
      cache: false,
      contentType: false,
      processData: false,
      dataType: "json",
      beforeSend: function () {
        submit_btn.prop("disabled", true);
        submit_btn.removeClass("btn-primary");

        submit_btn.addClass("btn-secondary");
        submit_btn.html(
          '<div class="spinner-border text-light spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
        );
      },
      //  beforeSend: function () {
      //      submit_btn.html('Please Wait..');
      //      submit_btn.attr('disabled', true);
      //  },
      success: function (response) {
        console.log("*****success");
        console.log(response);
        csrfName = response["csrfName"];
        csrfHash = response["csrfHash"];
        if (response.error == false) {
          showToastMessage(response.message, "success");
          location.reload();
          submit_btn.html(button_text);
          $(".close").click();
          $("#user_list").bootstrapTable("refresh");
          $("#slider_list").bootstrapTable("refresh");
          // window.location.reload();
        } else {
          console.log("*****failed");
          if (
            typeof response.message === "object" &&
            !Array.isArray(response.message) &&
            response.message !== null
          ) {
            for (var k in response.message) {
              if (response.message.hasOwnProperty(k)) {
                showToastMessage(response.message[k], "error");
              }
            }
          } else {
            showToastMessage(response.message, "error");
          }
          submit_btn.attr("disabled", false);
          submit_btn.html(button_text);
          // $('.close').click();
          $("#update_modal").bootstrapTable("refresh");
        }
      },
    });
  });
});
function notification_id(element) {
  $("#id").val($(element).data("id"));
  $("#did").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
  // e.preventDefault()
});
$(document).ready(function () {});
function category_id(element) {
  $("#id").val($(element).data("id"));
  $("#did").val($(element).data("id"));
}
function language_id(element) {
  $("#id").val($(element).data("id"));
  $("#did").val($(element).data("id"));
}
//for select notification event
$("#categories_select1").hide();
$("#user_select").hide();
$("#provider_select").hide();
$("#category_select").hide();
$("#url").hide();
$(document).ready(function () {
  $("#type1").change(function (e) {
    if ($("#type1").val() == "general") {
      $("#categories_select").show();
      // $('#user_select').hide();
      $("#provider_select").hide();
      $("#category_select").hide();
      $("#url").hide();
    }
    if ($("#type1").val() == "provider") {
      $("#provider_select").show();
      $("#categories_select").hide();
      $("#category_select").hide();
      $("#url").hide();
      // $('#user_select').hide();
    } else if ($("#type1").val() == "category") {
      $("#provider_select").hide();
      $("#categories_select").hide();
      $("#category_select").show();
      $("#url").hide();
      // $('#user_select').hide();
    } else if ($("#type1").val() == "url") {
      $("#provider_select").hide();
      $("#categories_select").hide();
      $("#category_select").hide();
      $("#url").show();
      // $('#user_select').hide();
    } else {
      // $('#user_select').hide();
      $("#provider_select").hide();
      $("#category_select").hide();
      $("#url").hide();
    }
  });
});
// $(document).ready(function () {
//     $('#type1').change(function (e) {
//        if ($('#type1').val() == "personal")
//         {
//             $('#categories_select').hide();
//             $('#provider_select').hide();
//             $('#user_select').show();
//             $('#category_select').hide();
//             $('#url').hide();
//         } else if ($('#type1').val() == "provider")
//         {
//             $('#provider_select').show();
//             $('#categories_select').hide();
//             $('#user_select').hide();
//             $('#category_select').hide();
//             $('#url').hide();
//         }
//         else if ($('#type1').val() == "category")
//         {
//             $('#provider_select').hide();
//             $('#categories_select').hide();
//             $('#user_select').hide();
//             $('#category_select').show();
//             $('#url').hide();
//         }
//         else if ($('#type1').val() == "url")
//         {
//             $('#provider_select').hide();
//             $('#categories_select').hide();
//             $('#user_select').hide();
//             $('#category_select').hide();
//             $('#url').show();
//         }
//         else
//         {
//             $('#user_select').hide();
//             $('#provider_select').hide();
//             $('#category_select').hide();
//             $('#url').hide();
//         }
//     })
// });
$(document).ready(function () {
  $("#user_type").change(function (e) {
    if ($("#user_type").val() == "all_users") {
      $("#user_select").hide();
    } else if ($("#user_type").val() == "specific_user") {
      $("#user_select").show();
    } else if ($("#user_type").val() == "existing_user") {
      $("#user_select").hide();
      $("#email").prop("required", false);
      $("#name").prop("required", false);
      $("#mobile").prop("required", false);
      $("#password").prop("required", false);
      $("#confirm_password").prop("required", false);
    } else if ($("#user_type").val() == "new_user") {
      $("#user_select").hide();
      $("#email").prop("required", true);
      $("#name").prop("required", true);
      $("#mobile").prop("required", true);
      $("#password").prop("required", true);
      $("#confirm_password").prop("required", true);
    } else {
      $("#user_select").hide();
    }
  });
});

$("#image_checkbox").on("click", function () {
  if (this.checked) {
    $(this).prop("checked", true);
    $(".include_image").removeClass("d-none");
  } else {
    $(this).prop("checked", false);
    $(".include_image").addClass("d-none");
  }
});

// for select event in sliders
$("#categories_select").hide();
$("#services_select").hide();
$(document).ready(function () {
  $("#type").change(function (e) {
    if ($("#type").val() == "default") {
      $("#categories_select").hide();
      $("#services_select").hide();
    } else if ($("#type").val() == "Category") {
      $("#categories_select").show();
      $("#services_select").hide();
    } else if ($("#type").val() == "provider") {
      $("#categories_select").hide();
      $("#services_select").show();
    }
  });
});
function update_slider(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
  // e.preventDefault()
});
$(document).ready(function () {});
// these are for offers only
window.action_events = {
  "click .edit-offer": function (e, value, row, index) {
    $("#id").val(row.id);
    $("#type_1").val(row.type);
    let opv = row.type;
    // console.log(row.type_id);
    // let the operations begin
    var regex = /<img.*?src="(.*?)"/;
    var src = regex.exec(row.offer_image)[1];
    console.log(src);
    $("#id").val(row.id);
    $("#offer_image").attr("src", src);
    if (row.status == "Enable") {
      $(".changer_ed").prop("checked", true);
    }
    $("#categories_select_1").hide();
    $("#services_select_1").hide();
    $(document).ready(function () {
      console.log("lol", $("#type_1").val());
      $("#type_1").change(function (e) {
        if ($("#type_1").val() == "provider") {
          $("#categories_select_1").hide();
          $("#services_select_1").show();
        } else if ($("#type_1").val() == "Category") {
          $("#categories_select_1").show();
          $("#services_select_1").hide();
        } else {
          $("#categories_select_1").hide();
          $("#services_select_1").hide();
        }
      });
    });
  },
  "click .delete-offer": function (e, value, row, index) {
    // $('#id').val(row.id);
    var users_id = row.id;
    // console.log(row.id);
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/offers/delete_offer",
          {
            [csrfName]: csrfHash,
            user_id: users_id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            console.log(data);
            if (data.error == false) {
              showToastMessage(data.message, "success");
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
              return;
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
};
// it ends here
// for categories
window.Category_events = {
  "click .delete-Category": function (e, value, row, index) {
    console.log(row);
    var users_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: "You won't be able to revert this ! Subcategories and services of this category will be deactivated",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/category/remove_category",
          {
            [csrfName]: csrfHash,
            user_id: users_id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            console.log(data);
            if (data.error == false) {
              showToastMessage(data.message, "success");
              setTimeout(() => {
                $("#category_list").bootstrapTable("refresh");
                $("#edit_category_ids")
                  .children("option[value^=" + users_id + "]")
                  .remove();
              }, 2000);
              return;
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
  "click .edite-Category": function (e, value, row, index) {
    $("#edit_category_ids").children("option").show();
    $("#edit_category_ids")
      .children("option[value^=" + row.id + "]")
      .hide();
    // console.log(row);
    $("#id").val(row.id);
    $("#edit_parent_category").val(row.parent_category_name);
    $("#edit_name").val(row.name);
    $("#commision_1").val(row.admin_commission);
    $("#edit_dark_theme_color").val(row.dark_color);
    $("#edit_light_theme_color").val(row.light_color);
    const commissions = row.admin_commission;
    // console.log(commissions.replace(/\s/g, ''));
    $("#commision_1").val(commissions);
    let opv = row.type;
    // $("#parent_id_edit").val(row.parent_id).select2({
    //     placeholder: "Select categories",
    // });
    // console.log(row.type_id);
    // let the operations begin
    var regex = /<img.*?src="(.*?)"/;
    var src = regex.exec(row.category_image)[1];
    $("#id").val(row.id);
    $("#category_image").attr("src", src);
    if (row.parent_id == "0") {
      $("#edit_make_parent").val("0");
      $("#edit_parent").hide();
    } else {
      $("#edit_make_parent").val("1");
      $("#edit_parent").show();
      $("#edit_category_ids").val(row.parent_id);
    }
    if (row.og_status == true) {
      $("#changer_1").prop("checked", true);
      $("#category_para_edit").text("Enable");
    } else {
      $("#changer_1").prop("checked", false);
      $("#category_para_edit").text("Disable");
    }

  
  },
};
// it ends here
// for sliders
function feature_section_id(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
  // e.preventDefault()
});
$(document).ready(function () {});
//order actions
function order_id(element) {
  $("#id").val($(element).data("id"));
}
function view_order(e) {
  var order_id = $(e).attr("data-id");
  $.post(baseUrl + "/admin/orders/view_details", {
    [csrfName]: csrfHash,
    // user_id: users_id,
  });
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
  // e.preventDefault()
});
$(document).ready(function () {});
window.orders_events = {
  "click .delete_orders": function (e, value, row, index) {
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/Orders/delete_orders",
          {
            [csrfName]: csrfHash,
            id: id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            console.log(data.message);
            if (data.error == false) {
              showToastMessage(data.message, "success");
              window.location.reload();
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
};
function services_id(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
  // e.preventDefault()
});
$(document).ready(function () {});
window.services_events = {
  "click .delete-services": function (e, value, row, index) {
    console.log(row.id);
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/services/delete-services",
          {
            [csrfName]: csrfHash,
            id: id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            console.log(data);
            if (data.error == false) {
              showToastMessage(data.message, "success");
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
              return;
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
};
//this is promocodes actions
function promo_codes_id(element) {
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
  // e.preventDefault()
});
// this for display image when selected
function readURL(input) {
  var reader = new FileReader();
  reader.onload = function (e) {
    document
      .querySelector("#service_image")
      .setAttribute("src", e.target.result);
    // console.log(document.querySelector("#update_service_image"));
    if (document.querySelector("#update_service_image") != null) {
      document
        .querySelector("#update_service_image")
        .setAttribute("src", e.target.result);
    }
  };
  reader.readAsDataURL(input.files[0]);
}
function readURLCategory(input) {
  var reader = new FileReader();
  reader.onload = function (e) {
    document
      .querySelector("#catgeory_image")
      .setAttribute("src", e.target.result);
    // console.log(document.querySelector("#update_service_image"));
    if (document.querySelector("#update_service_image") != null) {
      document
        .querySelector("#update_service_image")
        .setAttribute("src", e.target.result);
    }
  };
  reader.readAsDataURL(input.files[0]);
}
//   which ends here
// select 2 js
//features and section for custom services
$("#section_type").on("change", function () {
  console.log($(this).val());
  if ($(this).val() == "partners") {
    $(".Category_item").addClass("d-none");
    $(".partners_ids").removeClass("d-none");
    $(".top_rated_providers").addClass("d-none");
    $(".previous_order").addClass("d-none");
    $(".ongoing_order").addClass("d-none");
  } else if ($(this).val() == "categories") {
    $(".Category_item").removeClass("d-none");
    $(".partners_ids").addClass("d-none");
    $(".top_rated_providers").addClass("d-none");
    $(".previous_order").addClass("d-none");
    $(".ongoing_order").addClass("d-none");
  } else if ($(this).val() == "top_rated_partner") {
    $(".Category_item").addClass("d-none");
    $(".partners_ids").addClass("d-none");
    $(".top_rated_providers").removeClass("d-none");
    $(".previous_order").addClass("d-none");
    $(".ongoing_order").addClass("d-none");
  } else if ($(this).val() == "previous_order") {
    $(".Category_item").addClass("d-none");
    $(".partners_ids").addClass("d-none");
    $(".top_rated_providers").addClass("d-none");
    $(".previous_order").removeClass("d-none");
    $(".ongoing_order").addClass("d-none");
  } else if ($(this).val() == "ongoing_order") {
    $(".Category_item").addClass("d-none");
    $(".partners_ids").addClass("d-none");
    $(".top_rated_providers").addClass("d-none");
    $(".previous_order").addClass("d-none");
    $(".ongoing_order").removeClass("d-none");
  } else {
    $(".partners_ids").addClass("d-none");
    $(".top_rated_providers").addClass("d-none");
    $(".Category_item").addClass("d-none");
    $(".previous_order").addClass("d-none");
    $(".ongoing_order").addClass("d-none");
  }
});
$("#category_item").on("change", function () {
  $(".error").remove();
  $.post(
    baseUrl + "/admin/categories/list",
    {
      [csrfName]: csrfHash,
      id: $(this).val(),
      from_app: true,
    },
    function (data) {
      csrfName = data.csrfName;
      csrfHash = data.csrfHash;
      if (data.error == false) {
        var sub_categories = data.data;
        sub_categories.forEach((element) => {
          Option =
            "<option value='" + element.id + "'>" + element.name + "</option>";
          $("#sub_category").append(Option);
        });
        $("#sub_category").attr("disabled", false);
        $("#sub_category")
          .parent()
          .append('<span class="text-danger error"></span>');
      } else {
        $("#sub_category").empty();
        $("#sub_category").attr("disabled", true);
        $("#sub_category")
          .parent()
          .append(
            '<span class="text-danger error">No Found sub categories on this category Please change categories</span>'
          );
      }
    }
  );
});
// for change while edit
$("#edit_category_item").on("change", function () {
  $(".error").remove();
  $.post(
    baseUrl + "/admin/categories/list",
    {
      [csrfName]: csrfHash,
      id: $(this).val(),
      from_app: true,
    },
    function (data) {
      csrfName = data.csrfName;
      csrfHash = data.csrfHash;
      if (data.error == false) {
        var sub_categories = data.data;
        sub_categories.forEach((element) => {
          Option =
            "<option value='" + element.id + "'>" + element.name + "</option>";
          $("#edit_sub_category").append(Option);
        });
        $("#edit_sub_category").attr("disabled", false);
        $("#edit_sub_category")
          .parent()
          .append('<span class="text-danger error"></span>');
      } else {
        $("#edit_sub_category").empty();
        $("#edit_sub_category").attr("disabled", true);
        $("#edit_sub_category")
          .parent()
          .append(
            '<span class="text-danger error">No Found sub categories on this category Please change categories</span>'
          );
      }
    }
  );
});
// $('#edit_section_type').on('change', function () {
//     if ($(this).val() == 'categories') {
//         $('.edit_category_item').removeClass('d-none');
//         $('.edit_partners_ids').addClass('d-none');
//     } else {
//         $('.edit_category_item').addClass('d-none');
//         $('.edit_partners_ids').removeClass('d-none');
//     }
// });
// $('#edit_section_type').on('change', function () {
//     if ($(this).val() == 'partners') {
//         $('.Category_item').addClass('d-none');
//         $('.partners_ids').removeClass('d-none');
//     } else if ($(this).val() == 'categories') {
//         $('.Category_item').removeClass('d-none');
//         $('.partners_ids').addClass('d-none');
//     } else {
//         $('.partners_ids').addClass('d-none');
//         $('.Category_item').addClass('d-none');
//     }
// });
function faqs_id(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
  // e.preventDefault()
});
$(document).ready(function () {});
window.faqs_events = {
  "click .remove_faqs": function (e, value, row, index) {
    console.log(row.id);
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/faqs/remove_faqs",
          {
            [csrfName]: csrfHash,
            id: id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            console.log(data);
            if (data.error == false) {
              showToastMessage(data.message, "success");
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
              return;
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
  "click .edit_faqs": function (e, value, row, index) {
    console.log(row);
    $("#id").val(row.id);
    $("#edit_question").val(row.question);
    $("#edit_answer").val(row.answer);
  },
};
function taxes_id(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
  // e.preventDefault()
});
$(document).ready(function () {});
window.taxes_events = {
  "click .remove_taxes": function (e, value, row, index) {
    console.log(row.id);
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/tax/remove_taxes",
          {
            [csrfName]: csrfHash,
            id: id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            console.log(data);
            if (data.error == false) {
              showToastMessage(data.message, "success");
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
              return;
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
  "click .edit_taxes": function (e, value, row, index) {
    console.log(row);
    $("#id").val(row.id);
    $("#edit_title").val(row.title);
    $("#edit_percentage").val(row.percentage);
    if (row.og_status == 1) {
      $("#status_edit").prop("checked", true);
      $("#tax_status_edit").text("Enable");
    } else {
      $("#status_edit").prop("checked", false);
      $("#tax_status_edit").text("Disable");
    }
  },
};
function tickets_id(element) {
  $("#id").val($(element).data("id"));
  $("#id").val($(element).data("id"));
}
$("#gen-list a").on("click", function (e) {
  $(this).tab("show");
  // e.preventDefault()
});
$(document).ready(function () {});
window.tickets_events = {
  "click .remove_tickets": function (e, value, row, index) {
    console.log(row.id);
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/tickets/remove_tickets",
          {
            [csrfName]: csrfHash,
            id: id,
          },
          function (data) {
            csrfName = data.csrfName;
            csrfHash = data.csrfHash;
            console.log(data);
            if (data.error == false) {
              showToastMessage(data.message, "success");
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
              return;
            } else {
              return showToastMessage(data.message, "error");
            }
          }
        );
      }
    });
  },
  "click .edit_tickets": function (e, value, row, index) {
    console.log(row);
    $("#id").val(row.id);
    $("#edit_title").val(row.title);
  },
};
// mini map
// code for map start
let update_location = "";
let map_update = "";
let partner_location = "";
let marker = "";
let autocomplete = "";
let add_partner_location = "";
let view_partner_location = "";
let map_view = "";
let map = "";
var latitude = $("#latitude").val();
var longitude = $("#longitude").val();
let center = {
  lat: parseFloat(latitude),
  lng: parseFloat(longitude),
};
// div for maps
var map_location = document.getElementById("map");
var map_location_update = document.getElementById("map_u");
var partner_map = document.getElementById("partner_map");
function initautocomplete() {
  // console.log(document.getElementById('search_places'));
  if (document.getElementById("search_places") != null) {
    autocomplete = new google.maps.places.Autocomplete(
      document.getElementById("search_places"),
      {
        types: ["locality"],
        fields: ["place_id", "geometry", "name"],
      }
    );
    autocomplete.addListener("place_changed", onPlaceChanged);
  }
  $("#update_modal").on("show.bs.modal", function (e) {
    // for update
    if (document.getElementById("search_places_u") != null) {
      update_location = new google.maps.places.Autocomplete(
        document.getElementById("search_places_u"),
        {
          types: ["locality"],
          fields: ["place_id", "geometry", "name"],
        }
      );
    }
  });
  // add
  function onPlaceChanged(e) {
    console.log(e);
    place = autocomplete.getPlace();
    let contentString = "<h6> " + place.name + " </h6>";
    center = {
      lat: place.geometry.location.lat(),
      lng: place.geometry.location.lng(),
    };
    const infowindow = new google.maps.InfoWindow({
      content: contentString,
    });
    map = new google.maps.Map(map_location, {
      center,
      zoom: 10,
    });
    const marker = new google.maps.Marker({
      title: place.name,
      animation: google.maps.Animation.DROP,
      position: center,
      map: map,
    });
    marker.addListener("click", () => {
      infowindow.open({
        anchor: marker,
        map,
        shouldFocus: false,
      });
    });
    $("#latitude").val(latitude);
    $("#longitude").val(longitude);
    $("#city_name").val(place.name);
    console.log(latitude);
    console.log(longitude);
  }
  // for update
  if (document.getElementById("search_places_u") != null) {
    update_location = new google.maps.places.Autocomplete(
      document.getElementById("search_places_u"),
      {
        types: ["locality"],
        componentRestriction: {
          country: ["USA"],
        },
        fields: ["place_id", "geometry", "name"],
      }
    );
    update_location.addListener("place_changed", onUpdatePlace);
  }
  if (document.getElementById("partner_location") != null) {
    add_partner_location = new google.maps.places.Autocomplete(
      document.getElementById("partner_location"),
      {
        types: ["establishment"],
        componentRestriction: {
          country: ["USA"],
        },
        fields: ["place_id", "geometry", "name"],
      }
    );
    add_partner_location.addListener("place_changed", on_add_partner);
  }
  if (autocomplete) {
    var place = autocomplete.getPlace();
  }
  var latitude =
    typeof place != "undefined"
      ? place.geometry.location.lat()
      : parseFloat("23.242697188102483");
  var longitude =
    typeof place != "undefined"
      ? place.geometry.location.lng()
      : parseFloat("69.6639650758625");
  var name =
    typeof place != "undefined" ? place.geometry.location.lng() : "Bhuj";
  center = {
    lat: latitude,
    lng: longitude,
  };
  if (partner_map != null) {
    partner_location = new google.maps.Map(partner_map, {
      center,
      zoom: 4,
    });
    /* add marker on clicked location */
    google.maps.event.addListener(partner_location, "click", function (event) {
      var latitude = event.latLng.lat();
      var longitude = event.latLng.lng();
      console.log(latitude + ", " + longitude);
      set_map_marker_for_partner("", latitude, longitude, "", partner_location);
      $("#partner_latitude").val(latitude);
      $("#partner_longitude").val(longitude);
    }); //end addListener
  }
  function on_add_partner() {
    place = add_partner_location.getPlace();
    let latitude = place.geometry.location.lat();
    let longitude = place.geometry.location.lng();
    set_map_marker_for_partner(place, "", "", "", partner_location);
    console.log(latitude + longitude);
    $("#partner_latitude").val(latitude);
    $("#partner_longitude").val(longitude);
  }
  if (map_location != null) {
    map = new google.maps.Map(map_location, {
      center,
      zoom: 8,
    });
  }
  if (map_location_update != null) {
    map_update = new google.maps.Map(map_location_update, {
      center,
      zoom: 8,
    });
  }
  function onUpdatePlace(e) {
    place = update_location.getPlace();
    let latitude = place.geometry.location.lat();
    let longitude = place.geometry.location.lng();
    set_map_marker(place);
    $("#u_city_name").val(place.name);
    $("#u_latitude").val(latitude);
    $("#u_longitude").val(longitude);
  }
  var info_window = "";
  view_partner_location = document.getElementById("map_tuts");
  if (view_partner_location != null) {
    console.log(view_partner_location);
    var view_latitude = parseFloat($("#lat").val());
    var view_longitude = parseFloat($("#lon").val());
    console.log(view_longitude);
    if (view_latitude != "" && view_longitude != "") {
      center = {
        lat: view_latitude,
        lng: view_longitude,
      };
      map_view = new google.maps.Map(view_partner_location, {
        center,
        zoom: 16,
      });
      const marker = new google.maps.Marker({
        // title: title,
        animation: google.maps.Animation.DROP,
        position: center,
        map: map_view,
      });
      marker.addListener("click", () => {
        info_window.open({
          anchor: marker,
          map_view,
          shouldFocus: false,
        });
      });
    } else {
      $(view_partner_location).text("<h6> No Data passed </h6>");
    }
  } else {
    console.log("view_partner_location is empty");
  }
}
window.initMap = initautocomplete;
// google.maps.event.addDomListener(window, 'load', initAutocomplete);
// mini map ends here
function set_map_marker_for_partner(
  place = "",
  latitude = "",
  longitude = "",
  name = "",
  map = ""
) {
  if (place !== "") {
    latitude = place.geometry.location.lat();
    longitude = place.geometry.location.lng();
  } else {
    latitude = parseFloat(latitude);
    longitude = parseFloat(longitude);
  }
  let title = place.name ? place.name : name;
  let contentString = "<h6> " + title + " </h6>";
  center = {
    lat: place ? place.geometry.location.lat() : latitude,
    lng: place ? place.geometry.location.lng() : longitude,
  };
  const infowindow = new google.maps.InfoWindow({
    content: contentString,
  });
  if (!map) {
    partner_location = new google.maps.Map(partner_map, {
      center,
      zoom: 16,
    });
  } else {
    partner_location = map;
  }
  if (marker == "") {
    marker = new google.maps.Marker({
      title: title,
      animation: google.maps.Animation.DROP,
      position: center,
      map: partner_location,
      // draggable: true
    });
  } else {
    marker.setPosition({ lat: latitude, lng: longitude });
  }
  if (place != "") {
    partner_location.setCenter(center);
    partner_location.setZoom(16);
  }
  marker.addListener("click", () => {
    infowindow.open({
      anchor: marker,
      map: partner_location,
      shouldFocus: false,
    });
  });
}
function set_map_marker(place = "", latitude = "", longitude = "", name = "") {
  if (place !== "") {
    latitude = place.geometry.location.lat();
    longitude = place.geometry.location.lng();
  } else {
    latitude = parseFloat(latitude);
    longitude = parseFloat(longitude);
  }
  let title = place.name ? place.name : name;
  let contentString = "<h6> " + title + " </h6>";
  center = {
    lat: place ? place.geometry.location.lat() : latitude,
    lng: place ? place.geometry.location.lng() : longitude,
  };
  const infowindow = new google.maps.InfoWindow({
    content: contentString,
  });
  map = new google.maps.Map(map_location_update, {
    center,
    zoom: 10,
  });
  const marker = new google.maps.Marker({
    title: title,
    animation: google.maps.Animation.DROP,
    position: center,
    map: map,
  });
  marker.addListener("click", () => {
    infowindow.open({
      anchor: marker,
      map,
      shouldFocus: false,
    });
  });
}
$("#member").hide();
$(document).ready(function () {
  $("#type").on("change", function (e) {
    if ($("#type").val() == "0" || $("#type").val() == "sel") {
      $("#member").hide();
    } else {
      $("#member").show();
    }
  });
});
// updating the city
$("#city_update_modal").hide();
$("#delivery_charge_method_u").on("change", function () {
  if ($(this).val() == "fixed_charge") {
    if ($(".delivery_charge_method_result_u").hasClass("d-none")) {
      $(".delivery_charge_method_result_u").removeClass("d-none");
    }
    $(".delivery_charge_method_result_u").html(
      '<label for="" class="label_title">Fixed charges</label><input type="text" class="form-control" name="fixed_charge" placeholder="fixed charge">'
    );
    $("#range_wise_km").addClass("d-none");
  } else if ($(this).val() == "per_km_charge") {
    if ($(".delivery_charge_method_result_u").hasClass("d-none")) {
      $(".delivery_charge_method_result_u").removeClass("d-none");
    }
    $(".delivery_charge_method_result_u").html(
      '<label for="" class="label_title">Per KM Charges</label><input type="text" class="form-control" name="per_km_charge" placeholder="per km charge">'
    );
    $("#range_wise_km").addClass("d-none");
  } else if ($(this).val() == "range_wise_charges") {
    $(".delivery_charge_method_result_u").addClass("d-none");
    $("#range_wise_km").removeClass("d-none");
  } else {
    $(".delivery_charge_method_result_u").addClass("d-none");
    $("#range_wise_km").addClass("d-none");
  }
});
function scroll_to() {
  $("button.scr").click(function () {
    $("html, body").animate(
      {
        scrollTop: $("#city_update_modal").offset().top,
      },
      100
    );
  });
}
window.City_events = {
  "click .delete-city": function (e, value, row, index) {
    console.log(row);
    console.log(row);
    var id = row.id;
    // console.log(id);
    // return;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        city_id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/cities/remove_city",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#city_list").bootstrapTable("refresh");
              }, 2000);
              // window.location.reload();
              return;
            } else {
              console.log(response);
              setTimeout(() => {
                $("#city_list").bootstrapTable("refresh");
              }, 2000);
              // window.location.reload();
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .edit-city": function (e, value, row, index) {
    $("#city_update_modal").show();
    console.log(row);
    $("#id").val(row.id);
    $("#u_city_name").val(row.name);
    $("#u_latitude").val(row.latitude);
    $("#u_longitude").val(row.longitude);
    set_map_marker("", row.latitude, row.longitude, row.name);
    $("#u_travel").val(row.time_to_travel);
    $("#u_maximum_delivrable_distance").val(row.max_deliverable_distance);
    $("#delivery_charge_method_u")
      .val(row.delivery_charge_method)
      .trigger("change");
  },
};
$(document).ready(function () {
  $("#close-div").click(function (e) {
    $("#city_update_modal").hide();
    $("#close-div").click(function () {
      $("html, body").animate(
        {
          scrollTop: $("#city_list").offset().top,
        },
        100
      );
    });
  });
});
window.payment_events = {
  "click .edit_request": function (e, value, row, index) {
    $("#request_id").val(row.id);
    $("#user_id").val(row.user_id);
    $("#amount").val(row.amount);
  },
};
window.chat_events = {
  "click .chat": function (e, value, row, index) {
    $("#id").val(row.id);
    console.log(row);
    console.log(row.status);
    var status = row.status;
    console.log(status.replace(/<[^>]*>?/gm, ""));
    var selected = status.replace(/<[^>]*>?/gm, "");
    //
    if (row.og_status == "0") {
      $("#ticket-status").val("1").select2({});
    } else {
      $("#ticket-status").val(row.og_status).select2({});
    }
    $("#ticket_type").html(row.title);
    $("#subject").html(row.subject);
    $("#status").html(status);
    $("#date_created").html(row.created_at);
    $("#description").html(row.description);
    $("#email").html(row.email);
    //
    $(".ticket_msg").html("");
    $(".ticket_msg").text("");
    var limit = "05";
    var offset = "00";
    // console.log(row);
    let ticket_type_id = row.ticket_type_id;
    let user_id = row.user_id;
    var user_name = row.username;
    $("#user_chat").html(user_name.toUpperCase());
    $("#user_id").val(row.user_id);
    $("#ticket_id").val(row.ticket_type_id);
    var input_body = {
      [csrfName]: csrfHash,
      user_id: user_id,
      ticket_type_id: ticket_type_id,
      limit: limit,
      offset: offset,
    };
    let message_html;
    $.ajax({
      type: "POST",
      url: baseUrl + "/admin/show_tickets/fetch_chat",
      data: input_body,
      dataType: "json",
      success: function (response) {
        var messages;
        if (response.error == false) {
          // console.log(response.data);
          messages = response.data;
          // console.log(messages);
          get_message(messages);
          $(".ticket_msg").find(".loader").remove();
          $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);
        } else {
          console.log(response);
          window.location.reload();
          return showToastMessage(response.message, "error");
        }
      },
    });
  },
  "click .remove_tickets": function (e, value, row, index) {
    console.log(row);
    var id = row.id;
    // return;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/tickets/remove_tickets",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#ticket_list").bootstrapTable("refresh");
              }, 2000);
              // window.location.reload();
              // return;
            } else {
              console.log(response);
              setTimeout(() => {
                $("#ticket_list").bootstrapTable("refresh");
                return showToastMessage(response.message, "error");
              }, 2000);
              window.location.reload();
            }
          },
        });
      }
    });
  },
};
$("#ticket_modal").on("hide.bs.modal", function () {
  window.location.reload();
});
function get_message(messages) {
  var messages_html;
  var data = JSON.parse(messages);
  // console.log(data['rows']);
  // console.log(data['rows'].length);
  let message_html;
  for (let i = 0; i < data["rows"].length; i++) {
    let element = data["rows"][i];
    var user_type = element["user_type"];
    var user_name = element["username"];
    var updated_at = element["updated_at"];
    var message = element["message"];
    var is_left = user_type == "user" ? "left" : "right";
    var bg_color =
      is_left == "left" ? "bg-primary text-white" : "bg-success text-white";
    var atch_html;
    console.log(element);
    let attachments =
      element["attachments"] != "" ? JSON.parse(element["attachments"]) : null;
    // console.log(attachments);
    // console.log(typeof[]);
    if (attachments != null && attachments.length > 0) {
      // console.log('there is file ');
      attachments.forEach((element) => {
        let attachment = element;
        atch_html =
          "<div class='container-fluid image-upload-section'>" +
          "<a class='btn btn-danger btn-xs mr-1 mb-1' href=' " +
          attachment +
          "'  target='_blank' alt='Attachment Not Found'>Attachment</a>" +
          "<div class='col-md-3 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image d-none'></div>" +
          "</div>";
        messages_html =
          "<div class='direct-chat-msg " +
          is_left +
          "'>" +
          "<div class='direct-chat-infos clearfix'>" +
          "<span class='direct-chat-name float-" +
          is_left +
          "' id='name'> " +
          user_name +
          "</span>" +
          "<span class='direct-chat-timestamp float-" +
          is_left +
          "' id='last_updated'> &nbsp;" +
          updated_at +
          "</span>" +
          "</div>";
        if (message != null) {
          messages_html +=
            "<div class='direct-chat-text " +
            bg_color +
            " float-" +
            is_left +
            "' id=" +
            user_type +
            ">" +
            message +
            "</div> <br> <br>";
        }
        messages_html +=
          "<div class='direct-chat-text  float-" +
          is_left +
          "' id='message'> " +
          atch_html +
          "</div> <br> <br>" +
          "</div>";
      });
    } else {
      messages_html =
        "<div class='direct-chat-msg " +
        is_left +
        "'>" +
        "<div class='direct-chat-infos clearfix'>" +
        "<span class='direct-chat-name float-" +
        is_left +
        "' id='name'> " +
        user_name +
        "</span>" +
        "<span class='direct-chat-timestamp float-" +
        is_left +
        "' id='last_updated'> &nbsp;" +
        updated_at +
        "</span>" +
        "</div>" +
        "<div class='direct-chat-text " +
        bg_color +
        " float-" +
        is_left +
        "' id=" +
        user_type +
        ">" +
        message +
        "</div>  <br> <br>" +
        "</div>";
    }
    $(".ticket_msg").prepend(messages_html);
  }
}
$(document).ready(function () {
  $("#send_message").submit(function (e) {
    e.preventDefault();
    // console.log(this);
    // const files = $('#file')[0].files;
    let message = $("#message").val();
    let ticket_id = $("#ticket_id").val();
    let files = $("#file_chat")[0].files;
    var Data = {
      [csrfName]: csrfHash,
      message,
      ticket_id,
      files,
    };
    // console.log(Data);
    let messages;
    let messages_html;
    let user_type;
    let user_name;
    let updated_at;
    let message_gotten;
    var is_left;
    var bg_color;
    var formData = new FormData(this);
    formData.append(csrfName, csrfHash);
    $.ajax({
      type: "POST",
      url: $(this).attr("action"),
      data: formData,
      dataType: "JSON",
      beforeSend: function () {},
      cache: false,
      contentType: false,
      processData: false,
      success: function (response) {
        // console.log(response);
        if (!response.error) {
          csrfName = response["csrfName"];
          csrfHash = response["csrfHash"];
          let attachment;
          let atch_html;
          message = JSON.parse(response.data);
          // console.log(message['rows']['0']);
          var data = message["rows"]["0"];
          // this is to find new message
          Object.keys(data).forEach((e) => {
            user_type = data["user_type"];
            user_name = data["username"];
            updated_at = data["updated_at"];
            message_gotten = data["message"];
            is_left = user_type == "user" ? "left" : "right";
            bg_color =
              is_left == "left"
                ? "bg-primary text-white"
                : "bg-success text-white";
          });
          var files = JSON.parse(data["attachments"]);
          // console.log(files);
          // console.log();
          // return;
          if (files != null && files.length > 0) {
            // console.log('file fetched');
            files.forEach((element) => {
              var file = element;
              // console.log(file);
              atch_html =
                "<div class='container-fluid image-upload-section'>" +
                "<a class='btn btn-danger btn-xs mr-1 mb-1' href=' " +
                files +
                "'  target='_blank' alt='Attachment Not Found'>Attachment</a>" +
                "<div class='col-md-3 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image d-none'></div>" +
                "</div>";
              messages_html =
                "<div class='direct-chat-msg " +
                is_left +
                "'>" +
                "<div class='direct-chat-infos clearfix'>" +
                "<span class='direct-chat-name float-" +
                is_left +
                "' id='name'> " +
                user_name +
                "</span>" +
                "<span class='direct-chat-timestamp float-" +
                is_left +
                "' id='last_updated'> &nbsp;" +
                updated_at +
                "</span>" +
                "</div>";
              if (message_gotten != null) {
                messages_html +=
                  "<div class='direct-chat-text " +
                  bg_color +
                  " float-" +
                  is_left +
                  " ' id=" +
                  user_type +
                  ">" +
                  message_gotten +
                  "</div>  <br> <br>";
              }
              messages_html +=
                "<div class='direct-chat-text  float-" +
                is_left +
                "' id='message'> " +
                atch_html +
                "</div> <br> <br>" +
                "</div>";
            });
          } else {
            messages_html =
              "<div class='direct-chat-msg " +
              is_left +
              "'>" +
              "<div class='direct-chat-infos clearfix'>" +
              "<span class='direct-chat-name float-" +
              is_left +
              "' id='name'> " +
              user_name +
              "</span>" +
              "<span class='direct-chat-timestamp float-" +
              is_left +
              "' id='last_updated'> &nbsp;" +
              updated_at +
              "</span>" +
              "</div>" +
              "<div class='direct-chat-text " +
              bg_color +
              " float-" +
              is_left +
              " ' id=" +
              user_type +
              ">" +
              message_gotten +
              "</div>  <br> <br>" +
              "</div>";
          }
          // return;
          $(".ticket_msg").append(messages_html);
          $(".message").val("");
          $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);
        } else {
          csrfName = response["csrfName"];
          csrfHash = response["csrfHash"];
        }
      },
    });
  });
});
// ticket status change
$("#ticket-status").on("change", function (e) {
  e.preventDefault();
  console.log($(this).val());
  var id = $("#id").val();
  // console.log(id);
  // return;
  console.log($("#ticket-status option:selected").html());
  let text = $("#ticket-status option:selected").html();
  var value = $(this).val();
  Swal.fire({
    title: "Are you sure?",
    text: "Are you sure you want to change status to " + text,
    icon: "error",
    showCancelButton: true,
    confirmButtonText: "Yes, Proceed!",
  }).then((result) => {
    var input_body = {
      [csrfName]: csrfHash,
      status: value,
      id: id,
    };
    if (result.isConfirmed) {
      $.ajax({
        type: "POST",
        url: baseUrl + "/admin/show_tickets/change_status",
        data: input_body,
        dataType: "json",
        success: function (response) {
          if (response.error == false) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            showToastMessage(response.message, "success");
            setTimeout(() => {
              $("#ticket_list").bootstrapTable("refresh");
            }, 2000);
          } else {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            // console.log(response);
            showToastMessage(response.message, "success");
            setTimeout(() => {
              $("#ticket_list").bootstrapTable("refresh");
            }, 2000);
            window.location.reload();
          }
        },
      });
    }
  });
});
$(document).ready(function () {
  var scrolled;
  if ($("#chat-box").length) {
    $("#chat-box").scrollTop($("#chat-box")[0].scrollHeight);
    $("#chat-box").scroll(function () {
      if ($("#chat-box").scrollTop() == 0) {
        let ticket_id = $("#ticket_id").val();
        // console.log($('.ticket_id'));
        load_messages($(".ticket_msg"), ticket_id);
      }
    });
    $("#chat-box").bind("mousewheel", function (e) {
      if (e.originalEvent.wheelDelta / 120 > 0) {
        if ($(".ticket_msg")[0].scrollHeight < 370 && scrolled == 0) {
          let ticket_id = $("#ticket_id").val();
          let user_id = $("#user_id").val();
          console.log($(".ticket_id"));
          console.log("scrolling up !");
          load_messages($(".ticket_msg"), ticket_id, user_id);
          scrolled = 1;
        }
      }
    });
  }
});
function load_messages(element, id, user_id) {
  var limit = element.data("limit");
  var offset = element.data("offset");
  var user_id = $("#user_id").val();
  // console.log(id);
  element.data("offset", limit + offset);
  // console.log(limit, offset);
  var max_loaded = element.data("max-loaded");
  // console.log(max_loaded);
  var data_to_sent = {
    ticket_type_id: id,
    limit: limit,
    offset: offset,
    user_id: user_id,
  };
  if (max_loaded == false) {
    // console.log("false max load");
    var loader =
      '<div class="loader text-center">Loading Previous Messages  </div>';
    $.ajax({
      type: "POST",
      url: baseUrl + "/admin/show_tickets/fetch_chat",
      data: data_to_sent,
      // data: 'ticket_id=' + id + '&limit=' + limit + '&offset=' + offset + '&user_id=' + user_id,
      dataType: "json",
      beforeSend: function () {
        $(".ticket_msg").prepend(loader);
      },
      cache: false,
      success: function (response) {
        // console.log(response);
        if (response.error == false) {
          let messages = response.data;
          get_extra_messages(element, messages);
          $(".ticket_msg").find(".loader").remove();
        } else {
          element.data("offset", offset);
          element.data("max-loaded", true);
          $(".ticket_msg").find(".loader").remove();
          $(".ticket_msg").prepend(
            '<div class="text-center"> <p>You have reached the top most message!</p></div>'
          );
        }
      },
    });
  }
}
function get_extra_messages(div, messages) {
  var div = div;
  var message = JSON.parse(messages);
  // console.log(message);
  let data = message["rows"];
  for (let i = 0; i < data.length; i++) {
    const element = data[i];
    // console.log(element['user_type']);
    // return;
    var messages_html;
    var user_type = element["user_type"];
    var user_name = element["username"];
    var updated_at = element["updated_at"];
    var message = element["message"];
    // var attachments = element['attachments']
    var is_left = user_type == "user" ? "left" : "right";
    var bg_color =
      is_left == "left" ? "bg-primary text-white" : "bg-success text-white";
    var atch_html;
    // console.log(typeof (element['attachments']));
    // console.log(element['attachments']);
    let attachments =
      element["attachments"] != "" ? JSON.parse(element["attachments"]) : null;
    // console.log(typeof[]);
    if (attachments != null && attachments.length > 0) {
      // console.log('there is file ');
      attachments.forEach((element) => {
        let attachment = element;
        atch_html =
          "<div class='container-fluid image-upload-section'>" +
          "<a class='btn btn-danger btn-xs mr-1 mb-1' href=' " +
          attachment +
          "'  target='_blank' alt='Attachment Not Found'>Attachment</a>" +
          "<div class='col-md-3 col-sm-12 shadow p-3 mb-5 bg-white rounded m-4 text-center grow image d-none'></div>" +
          "</div>";
        messages_html =
          "<div class='direct-chat-msg " +
          is_left +
          "'>" +
          "<div class='direct-chat-infos clearfix'>" +
          "<span class='direct-chat-name float-" +
          is_left +
          "' id='name'> " +
          user_name +
          "</span>" +
          "<span class='direct-chat-timestamp float-" +
          is_left +
          "' id='last_updated'> &nbsp;" +
          updated_at +
          "</span>" +
          "</div>";
        if (message != null) {
          messages_html +=
            "<div class='direct-chat-text " +
            bg_color +
            " float-" +
            is_left +
            "' id=" +
            user_type +
            ">" +
            message +
            "</div> <br> <br>";
        }
        messages_html +=
          "<div class='direct-chat-text  float-" +
          is_left +
          "' id='message'> " +
          atch_html +
          "</div> <br> <br>" +
          "</div>";
      });
    } else {
      messages_html =
        "<div class='direct-chat-msg " +
        is_left +
        "'>" +
        "<div class='direct-chat-infos clearfix'>" +
        "<span class='direct-chat-name float-" +
        is_left +
        "' id='name'> " +
        user_name +
        "</span>" +
        "<span class='direct-chat-timestamp float-" +
        is_left +
        "' id='last_updated'> &nbsp;" +
        updated_at +
        "</span>" +
        "</div>" +
        "<div class='direct-chat-text " +
        bg_color +
        " float-" +
        is_left +
        "' id=" +
        user_type +
        ">" +
        message +
        "</div>  <br> <br>" +
        "</div>";
    }
    $(".ticket_msg").prepend(messages_html);
    $(".ticket_msg").find(".loader").remove();
    $(div).animate({
      scrollTop: $(div).offset().top,
    });
  }
}
function printDiv(divName) {
  var printContents = document.getElementById(divName).innerHTML;
  var originalContents = document.body.innerHTML;
  document.body.innerHTML = printContents;
  window.print();
  document.body.innerHTML = originalContents;
}
$(document).ready(function () {
  $("#old_user").hide();
  $("#new_user").hide();
  $("#user_type").on("change", function (e) {
    // console.log();
    if ($("#user_type").val() == "new_user") {
      $("#old_user").hide();
      $("#new_user").show();
    } else {
      $("#old_user").show();
      $("#new_user").hide();
    }
  });
});
// payment gateway availability
$(document).ready(function () {
  var razorpay = $("#razorpay_status");
  var paystack = $("#paystack_status");
  var flutter = $("#flutter_wave_status");
  var stripe = $("#stripe_status");
  var paypal = $("#paypal_status");

  $(paystack).on("change", function () {
    // console.log('changed');
    if ($(paystack).val() == "enable") {
      $(razorpay).val("disable", "selected");
      $(flutter).val("disable", "selected");
      $(stripe).val("disable", "selected");
      $(paypal).val("disable", "selected");
    }
  });
  $(flutter).on("change", function (e) {
    if ($(flutter).val() == "enable") {
      $(paystack).val("disable", "selected");
      $(razorpay).val("disable", "selected");
      $(stripe).val("disable", "selected");
      $(paypal).val("disable", "selected");
    }
  });
  $(stripe).on("change", function () {
    if ($(stripe).val() == "enable") {
      $(paystack).val("disable", "selected");
      $(razorpay).val("disable", "selected");
      $(flutter).val("disable", "selected");
      $(paypal).val("disable", "selected");
    }
  });
  $(razorpay).on("change", function () {
    if ($(razorpay).val() == "enable") {
      $(paystack).val("disable", "selected");
      $(flutter).val("disable", "selected");
      $(stripe).val("disable", "selected");
      $(paypal).val("disable", "selected");
    }
  });
  $(paypal).on("change", function () {
    if ($(paypal).val() == "enable") {
      $(paystack).val("disable", "selected");
      $(razorpay).val("disable", "selected");
      $(flutter).val("disable", "selected");
      $(stripe).val("disable", "selected");
    }
  });
});
function change_order_Status() {
  var status = $(".update_order_status").val();
  var order_id = $("#order_id").val();
  var input_body = {
    [csrfName]: csrfHash,
    status: status,
    order_id: order_id,
  };
  console.log(input_body);
  $.ajax({
    type: "POST",
    url: baseUrl + "/admin/orders/change_order_status",
    data: input_body,
    dataType: "json",
    success: function (response) {
      csrfName = response["csrfName"];
      csrfHash = response["csrfHash"];
      if (response.error != false) {
        console.log("success");
        showToastMessage(response.message, "success");
        setTimeout(() => {
          window.location.reload();
        }, 2000);
      } else {
        console.log("error");
        setTimeout(() => {
          window.location.reload();
        }, 2000);
        return showToastMessage(response.message, "error");
      }
    },
  });
}
$(window).ready(function () {
  // $(".partner-rating").rateYo();
  const checkDiv = setInterval(() => {
    if ($(".partner-rating").length > 0) {
      // it's better to use id instead of the class as selector
      clearInterval(checkDiv);
      for (let i = 0; i < $(".partner-rating").length; i++) {
        let element = $(".partner-rating")[i];
        let id = $(".partner-rating")[i]["id"];
        let ratings = $(element).attr("data-value");
        $(document).ready(function () {
          $("#" + id).rateYo({
            rating: ratings,
            spacing: "5px",
            readOnly: true,
            starWidth: "15px",
            starHeight: "85px",
          });
        });
      }
    }
  }, 100);
});
// for bt table refresh event
$(window).ready(function () {
  $("#partner_list").on({
    "": function (e) {},
  });
  $("#partner_list").on({
    "load-success.bs.table , page-change.bs.table, check.bs.table, uncheck.bs.table, column-switch.bs.table":
      function (e) {
        for (let i = 0; i < $(".partner-rating").length; i++) {
          let element = $(".partner-rating")[i];
          let id = $(".partner-rating")[i]["id"];
          let ratings = $(element).attr("data-value");
          $(document).ready(function () {
            $("#" + id).rateYo({
              rating: ratings,
              spacing: "5px",
              readOnly: true,
              starWidth: "25px",
              starHeight: "85px",
            });
          });
        }
      },
  });
});
$(document).ready(function () {
  const checkDiv = setInterval(() => {
    if ($(".service-ratings").length > 0) {
      // it's better to use id instead of the class as selector
      clearInterval(checkDiv);
      console.log($(".service-ratings"));
      for (let i = 0; i < $(".service-ratings").length; i++) {
        let element = $(".service-ratings")[i];
        let id = $(".service-ratings")[i]["id"];
        let ratings = $(element).attr("data-value");
        $(document).ready(function () {
          $("#" + id).rateYo({
            rating: ratings,
            spacing: "5px",
            readOnly: true,
            starWidth: "25px",
          });
        });
      }
    }
  }, 1);
  $("#view_rating_model").on("show.bs.modal ", function (e) {
    console.log("abcd");
    $("#rating_table").on({
      "load-success.bs.table , page-change.bs.table, check.bs.table, uncheck.bs.table, column-switch.bs.table":
        function (e) {
          for (let i = 0; i < $(".service-ratings").length; i++) {
            let element = $(".service-ratings")[i];
            let id = $(".service-ratings")[i]["id"];
            let ratings = $(element).attr("data-value");
            $(document).ready(function () {
              $("#" + id).rateYo({
                rating: ratings,
                spacing: "5px",
                readOnly: true,
                starWidth: "25px",
              });
            });
          }
        },
    });
  });
});
$(document).ready(function () {
  $(".fa-search").addClass("d-none");
});
window.customSearchFormatter = function (value, searchText) {
  return value
    .toString()
    .replace(
      new RegExp("(" + searchText + ")", "gim"),
      '<span style="background-color: pink;border: 1px solid red;border-radius:90px;padding:4px">$1</span>'
    );
};
// for make parent
$(document).ready(function () {
  $("#parent").hide();
  var option = $("#make_parent").val();
  $("#make_parent").change(function (e) {
    // console.log('heeeee');
    e.preventDefault();
    if ($(this).val() == 1) {
      $("#parent").show();
    } else {
      $("#parent").hide();
    }
  });
});
// $(document).ready(function () {
//     $('#edit_parent').hide();
//     var option = $('#edit_make_parent').val();
//     $('#edit_make_parent').change(function (e) {
//         console.log('heeeee');
//         e.preventDefault();
//         if ($(this).val() == 1) {
//             $('#edit_parent').show();
//         } else {
//             $('#edit_parent').hide();
//         }
//     });
// });
$(document).ready(function () {
  $("#edit_make_parent").trigger("change");
  $("#edit_parent").hide();
  var option = $("#edit_make_parent").val();
  $("#edit_make_parent").change(function (e) {
    // console.log('heeeee');
    // e.preventDefault();
    if ($(this).val() == "1") {
      $("#edit_parent").show();
    } else {
      $("#edit_parent").hide();
    }
  });
});
$("#rescheduled_form").on("submit", function (e) {
  e.preventDefault();
  console.log(e);
});

$(function () {
  // First register any plugins
  FilePond.registerPlugin(
    FilePondPluginImagePreview,
    FilePondPluginFileValidateSize,
    FilePondPluginFileValidateType,
    FilePondPluginMediaPreview
  );
  // Turn input element into a pond
  $(".filepond").filepond({
    credits: null,
    allowFileSizeValidation: "true",
    maxFileSize: "25MB",
    labelMaxFileSizeExceeded: "File is too large",
    labelMaxFileSize: "Maximum file size is {filesize}",
    allowFileTypeValidation: true,
    acceptedFileTypes: ["image/*", "video/*", "application/pdf"],
    labelFileTypeNotAllowed: "File of invalid type",
    fileValidateTypeLabelExpectedTypes:
      "Expects {allButLastType} or {lastType}",
    storeAsFile: true,
    allowPdfPreview: true,
    pdfPreviewHeight: 320,
    pdfComponentExtraParams: "toolbar=0&navpanes=0&scrollbar=0&view=fitH",
    allowVideoPreview: true, // default true
    allowAudioPreview: true, // default true
  });

  $(".filepond-docs").filepond({
    credits: null,
    allowFileSizeValidation: "true",
    maxFileSize: "25MB",
    labelMaxFileSizeExceeded: "File is too large",
    labelMaxFileSize: "Maximum file size is {filesize}",
    allowFileTypeValidation: true,
    acceptedFileTypes: [
      "application/pdf",
      "application/msword",
      "application/vnd.openxmlformats-officedocument.wordprocessingml.document",
    ],
    labelFileTypeNotAllowed: "File of invalid type",
    fileValidateTypeLabelExpectedTypes:
      "Expects {allButLastType} or {lastType}",
    storeAsFile: true,
    allowPdfPreview: true,
    pdfPreviewHeight: 320,
    pdfComponentExtraParams: "toolbar=0&navpanes=0&scrollbar=0&view=fitH",
    allowVideoPreview: true, // default true
    allowAudioPreview: true, // default true
  });
});

var elems = Array.prototype.slice.call(
  document.querySelectorAll(".status-switch")
);

elems.forEach(function (elem) {
  var switchery = new Switchery(elem, {
    size: "small",
    color: "#47C363",
    secondaryColor: "#EB4141",
    jackColor: "#ffff",
    jackSecondaryColor: "#ffff",
  });
});

var elems1 = Array.prototype.slice.call(
  document.querySelectorAll(".switchery-yes-no")
);

elems1.forEach(function (elems1) {
  var switchery = new Switchery(elems1, {
    size: "small",
    color: "#47C363",
    secondaryColor: "#EB4141",
    jackColor: "#ffff",
    jackSecondaryColor: "#FFFF",
  });
});


// elems.forEach(function () {

//     var init = new Switchery(elems, { size: 'small' ,color: '#47C363', secondaryColor: '#EB4141', jackColor: '#ffff', jackSecondaryColor: '#ffff' });
//     console.log(init);
// });

// var elem = $(".status-switch")[0];

// console.log($(".status-switch"));
// var init = new Switchery(elem, { size: 'small' ,color: '#47C363', secondaryColor: '#EB4141', jackColor: '#ffff', jackSecondaryColor: '#ffff' });

$(document).ready(function () {
  for (let i = 0; i < $(".average_service-ratings").length; i++) {
    console.log($(".average_service-ratings"));
    let element = $(".average_service-ratings")[i];
    let id = $(".average_service-ratingss")[i]["id"];
    let ratings = $(element).attr("data-value");
    $(document).ready(function () {
      $("#" + id).rateYo({
        rating: ratings,
        spacing: "5px",
        readOnly: true,
        starWidth: "25px",
      });
    });
  }
});

var partner_filter = "";
$("#partner_filter_all").on("click", function () {
  partner_filter = "";
  $("#partner_list").bootstrapTable("refresh");
});

$("#partner_filter_active").on("click", function () {
  partner_filter = "1";
  $("#partner_list").bootstrapTable("refresh");
});

$("#partner_filter_deactivate").on("click", function () {
  partner_filter = "0";
  $("#partner_list").bootstrapTable("refresh");
});
// partner list params
function partner_list_query_params(p) {
  return {
    search: p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
    partner_filter: partner_filter,
  };
}

var top_rated_provider_filter = "";
$("#order_status_filter").on("change", function () {
  order_status_filter = $(this).find("option:selected").val();
});
$("#filter").on("click", function (e) {
  $("#user_list").bootstrapTable("refresh");
});

$(".repeat_usage").hide();
if ($("input[name='repeat_usage']").is(":checked")) {
  $(".repeat_usage").show();
}
$("#repeat_usage").on("click", function () {
  $(".repeat_usage").hide();
  if ($("input[name='repeat_usage']").is(":checked")) {
    $(".repeat_usage").show();
  }
});

// for subscription_id

$("#make_payment_for_subscription").on("submit", function (event) {
  console.log("test");
  event.preventDefault();
  // var payment_methods ="stripe";

  // // var payment_methods = $("input[name='payment_method']:checked").val()
  // if (payment_methods == 'stripe') {
  $.post(
    base_url + "/partner/subscription/pre-payment-setup233",
    {
      [csrfName]: csrfHash,
      payment_method: "stripe",
    },
    function (data) {
      $("#stripe_client_secret").val(data.client_secret);
      $("#stripe_payment_id").val(data.id);
      var stripe_client_secret = data.client_secret;
      stripe_payment(stripe1.stripe, stripe1.card, stripe_client_secret);
      csrfName = data.csrfName;
      csrfHash = data.csrfHash;
    },
    "json"
  );
  // }
});

function stripe_payment(stripe, card, clientSecret) {
  console.log(card);
  // Calls stripe.confirmCardPayment
  // If the card requires authentication Stripe shows a pop-up modal to
  // prompt the user to enter authentication details without leaving your page.
  stripe
    .confirmCardPayment(clientSecret, {
      payment_method: {
        card: card,
      },
    })
    .then(function (result) {
      if (result.error) {
        // Show error to your customer
        var errorMsg = document.querySelector("#card-error");
        errorMsg.textContent = result.error.message;
        setTimeout(function () {
          errorMsg.textContent = "";
        }, 4000);
        Toast.fire({
          icon: "error",
          title: result.error.message,
        });
        $("#buy").attr("disabled", false).html("Buy");
      } else {
        // The payment succeeded!
        purchase_subscription().done(function (result) {
          if (result.error == false) {
            setTimeout(function () {
              location.href = base_url + "/payment/success";
            }, 1000);
          }
        });
      }
    });
}

function purchase_subscription() {
  let myForm = document.getElementById("make_payment_for_subscription");
  var formdata = new FormData(myForm);
  return $.ajax({
    type: "POST",
    data: formdata,
    url: base_url + "/partner/subscription-payment",
    dataType: "json",
    cache: false,
    processData: false,
    contentType: false,
    beforeSend: function () {
      $("#buy").attr("disabled", true).html("Please Wait...");
    },
    success: function (data) {
      csrfName = data.csrfName;
      csrfHash = data.csrfHash;
      $("#buy").attr("disabled", false).html("Buy");
      if (data.error == false) {
        Toast.fire({
          icon: "success",
          title: data.message,
        });
      } else {
        Toast.fire({
          icon: "error",
          title: data.message,
        });
      }
    },
  });
}

function custome_export(type, label, table_name) {
  var selector = "#" + table_name; // Create the jQuery selector based on table_name

  if (type === "pdf") {
    $(selector).tableExport({
      fileName: label,
      type: "pdf",
      jspdf: {
        format: "bestfit",
        margins: {
          left: 20,
          right: 10,
          top: 50,
          bottom: 20,
        },
        autotable: {
          styles: {
            overflow: "linebreak",
          },
          tableWidth: "wrap",
          tableExport: {
            onBeforeAutotable: DoBeforeAutotable,
            onCellData: DoCellData,
          },
        },
      },
    });
  } else if (type === "excel") {
    // Excel export using tableExport plugin with 'excel' type
    $(selector).tableExport({
      fileName: label,
      type: "excel",
    });
  } else if (type === "csv") {
    // CSV export using tableExport plugin with 'csv' type
    $(selector).tableExport({
      fileName: label,
      type: "csv",
    });
  }
}

function DoCellData(cell, row, col, data) {}

function DoBeforeAutotable(table, headers, rows, AutotableSettings) {}

function doDocCreated(doc) {
  var PartName = $("#filter_party").find("option:selected").data("name");
  PartName = "WayBill Report | " + PartName + " | " + $("#filter_date").val();
  doc.text(500, 30, PartName); // Example text output
}

var service_custom_provider_filter = "";
$("#service_custom_provider_filter").on("change", function () {
  service_custom_provider_filter = $(this).find("option:selected").val();
  console.log(service_custom_provider_filter);
});

var service_category_custom_filter = "";
$("#service_category_custom_filter").on("change", function () {
  service_category_custom_filter = $(this).find("option:selected").val();
});
$("#service_filter").on("click", function (e) {
  $("#service_list").bootstrapTable("refresh");
});

$("#customSearch").on("keydown", function () {
  $("#service_list").bootstrapTable("refresh");
  $("#partner_list").bootstrapTable("refresh");
  $("#user_list").bootstrapTable("refresh");
});

var service_filter = "";
var service_custom_provider_filter = "";

function service_list_query_params1(p) {
  return {
    search: $("#customSearch").val() ? $("#customSearch").val() : p.search,
    limit: p.limit,
    sort: p.sort,
    order: p.order,
    offset: p.offset,
    service_filter: service_filter,
    service_custom_provider_filter: service_custom_provider_filter,
    service_category_custom_filter: service_category_custom_filter,
  };
}

// Define a reusable function for column visibility toggling
function setupColumnToggle(tableId, columns_name, containerId) {
  $(document).ready(function () {
    var $table = $("#" + tableId);

    // Function to toggle column visibility based on checkbox selections
    function toggleColumnVisibility() {
      $(".column-toggle").each(function () {
        var field = $(this).data("field");
        var isVisible = $(this).prop("checked");
        if (isVisible) {
          $table.bootstrapTable("showColumn", field);
        } else {
          $table.bootstrapTable("hideColumn", field);
        }
      });
    }

    // Initialize column visibility based on the data-visible attribute
    $("#columnToggleContainer").on("change", ".column-toggle", function () {
      toggleColumnVisibility();
    });

    // Generate checkboxes and labels dynamically
    var container = $("#" + containerId);

    var row; // Variable to hold the current row

    $.each(columns_name, function (index, column) {
      if (index % 2 === 0) {
        // Start a new row for every 2 columns
        row = $("<div>").addClass("row");
      }

      var checkbox = $("<input>")
        .attr("type", "checkbox")
        .addClass("column-toggle")
        .data("field", column.field)
        .prop("checked", column.visible !== false); // Set default checked state

      var label = $("<label>")
        .append(checkbox)
        .append(" " + column.label);

      var columnDiv = $("<div>").addClass("col-md-6");
      columnDiv.append(label);

      row.append(columnDiv);

      // Insert the row into the container
      container.append(row);
    });

    // Initialize column visibility based on default checked state
    toggleColumnVisibility();
  });
}
// Define a global function for the drawer functionality with custom IDs
function for_drawer(buttonId, drawerId, backdropId, cancelButtonId) {
  $(buttonId).click(function () {
    $(drawerId).toggleClass("open");
    $(backdropId).toggle(); // Show/hide the backdrop
  });

  // Add event listener for the "cancel" button
  $(cancelButtonId).click(function () {
    $(drawerId).removeClass("open"); // Close the drawer
    $(backdropId).hide(); // Hide the backdrop
  });
}

var filterBackdrop = document.getElementById("filterBackdrop");

// Get a reference to the drawer element
var drawer = document.querySelector(".drawer");

// Add a click event listener to the filterBackdrop element
filterBackdrop.addEventListener("click", function () {
  // Hide the drawer by removing the "open" class
  drawer.classList.remove("open");

  // Hide the filterBackdrop
  filterBackdrop.style.display = "none";
});


    
// Assuming you have a click event handler for the "Apply Filter" button
$("#filter").click(function() {

  $("#filterDrawer").removeClass("open"); // Close the drawer
    $("#filterBackdrop").hide(); // Hide the backdrop
});




function fetchColumns(tableId) {
  var columns = [];


  $('#' + tableId + ' thead th').each(function() {
    var field = $(this).data('field');
    var label = $(this).text().trim();
    var visible = $(this).data('visible') !== false;

    columns.push({
        field: field,
        label: label,
        visible: visible
    });
});

  return columns;
}
