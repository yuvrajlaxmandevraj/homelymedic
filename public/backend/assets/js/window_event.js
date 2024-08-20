/**
 *
 * You can write your JS code here, DO NOT touch the default style file
 * because it will make it harder for you to update.
 *
 * Register your select2 elements here
 * this will make it easier for you tu find components
 */

"use strict";

$(document).on("submit", ".update-form-submit-event", function (e) {
  e.preventDefault();
  var formData = new FormData(this);
  var form_id = $(this).attr("id");
  var error_box = $("#error_box", this);
  var submit_btn = $(this).find(".submit_btn");
  var btn_html = $(this).find(".submit_btn").html();
  var btn_val = $(this).find(".submit_btn").val();
  var button_text =
    btn_html != "" || btn_html != "undefined" ? btn_html : btn_val;

  // console.log(formData.set);
  formData.append(csrfName, csrfHash);
  // return;
  $.ajax({
    type: "POST",
    url: $(this).attr("action"),
    data: formData,
    cache: false,
    contentType: false,
    processData: false,
    dataType: "json",
    beforeSend: function () {
      submit_btn.html("Please Wait..");
      submit_btn.attr("disabled", true);
    },

    success: function (response) {
      csrfName = response["csrfName"];
      csrfHash = response["csrfHash"];
      if (response.error == false) {
        console.log(response.message);
        iziToast.success({
          title: "Success",
          message: response.message,
          position: "topRight",
        });
        submit_btn.attr("disabled", false);
        submit_btn.html(button_text);
        $(".close").click();
        $("#user_list").bootstrapTable("refresh");
        $("#slider_list").bootstrapTable("refresh");
        window.location.reload();
      } else {
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

// activate and deactivate user
window.user_events = {
  "click .deactivate_user": function (e, value, row, index) {
    console.log(row);
    var user_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_deactivate_this_user,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/users/deactivate",
          data: input_body,
          dataType: "json",

          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },

  "click .activate_user": function (e, value, row, index) {
    // console.log(row);
    var user_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_activate_this_user,
      icon: "warning",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/users/activate",
          data: input_body,
          dataType: "json",

          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              // console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              setTimeout(() => {
                $("#user_list").bootstrapTable("refresh");
              }, 2000);
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .delete-user": function (e, value, row, index) {
    console.log(row);
    var user_id = row.id;
    // return;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_delete_this_user,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/system_users/delete_user",
          data: input_body,
          dataType: "json",

          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              console.log(response);
              // window.location.reload();

              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },

  "click .edit-user": function (e, value, row, index) {
    console.log(
      "clicked**********************************************************"
    );
    $("#id").val(row.id);
    $(document).ready(function () {
      $("#edit_role").val(row.role_a).trigger("change");
      if ($("#edit_role").val() == 1) {
        $("#permissions").hide();
      } else {
        $("#permissions").show();
      }
    });
    var permissions = JSON.parse(row.permissions);

    let values;
    var data = permissions != null ? true : false;
    if (data) {
      Object.keys(permissions).forEach((key) => {
        let single_object = permissions[key];
        if (key == "create") {
          console.log(single_object);
          Object.keys(single_object).forEach((val) => {
            // create

            if (single_object.order == 0) {
              let order = $("#orders_create_edit")[0];
              $(order).attr("checked", true);
            }
            if (single_object.category == 1) {
              let category = $("#categories_create_edit")[0];
              $(category).attr("checked", true);
            }
            if (single_object.subscription == 1) {
              let subscription = $("#subscription_create_edit")[0];
              $(subscription).attr("checked", true);
            }
            if (single_object.sliders == 1) {
              let object = $("#sliders_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.tax == 1) {
              let object = $("#tax_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.sliders == 1) {
              let object = $("#sliders_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.services == 1) {
              let object = $("#services_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.promo_code == 1) {
              let object = $("#promo_code_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.featured_section == 1) {
              let object = $("#featured_section_create_edit")[0];
              $(object).attr("checked", true);
            } //
            if (
              single_object.partner == 1 ||
              single_object.partner != undefined
            ) {
              let object = $("#partner_create_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.notification == 1) {
              let object = $("#send_notification_create_edit")[0];
              $(object).attr("checked", true);
            } //

            if (single_object.faq == 1) {
              let object = $("#faq_create_edit")[0];
              $(object).attr("checked", true);
            }

            if (single_object.settings == 1) {
              let object = $("#settings_create_edit")[0];
              $(object).attr("checked", true);
            }
          });
        } else if (key == "read") {
          Object.keys(single_object).forEach((val) => {
            if (single_object.order == 0) {
              let object = $("#orders_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.category == 1) {
              let object = $("#categories_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.subscription == 1) {
              let object = $("#subscription_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.sliders == 1) {
              let object = $("#sliders_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.tax == 1) {
              let object = $("#tax_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.services == 1) {
              let object = $("#services_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.promo_code == 1) {
              let object = $("#promo_code_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.featured_section == 1) {
              let object = $("#featured_section_read_edit")[0];
              $(object).attr("checked", true);
            } //
            if (
              single_object.partner == 1 ||
              single_object.partner != undefined
            ) {
              let object = $("#partner_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (
              single_object.customer != undefined &&
              single_object.customer == 1
            ) {
              let object = $("#customers_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.notification == 1) {
              let object = $("#send_notification_read_edit")[0];
              $(object).attr("checked", true);
            } //

            if (single_object.faq == 1) {
              let object = $("#faq_read_edit")[0];
              $(object).attr("checked", true);
            }

            if (single_object.settings == 1) {
              let object = $("#settings_read_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.system == 1) {
              let object = $("#system_user_read_edit")[0];
              $(object).attr("checked", true);
            }
          });
        } else if (key == "update") {
          // console.log(single_object);
          Object.keys(single_object).forEach((val) => {
            if (single_object.order == 0) {
              let object = $("#orders_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.category == 1) {
              let object = $("#categories_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.subscription == 1) {
              let object = $("#subscription_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.sliders == 1) {
              let object = $("#sliders_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.tax == 1) {
              let object = $("#tax_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.services == 1) {
              let object = $("#services_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.promo_code == 1) {
              let object = $("#promo_code_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.featured_section == 1) {
              let object = $("#featured_section_update_edit")[0];
              $(object).attr("checked", true);
            } //
            if (
              single_object.partner == 1 ||
              single_object.partner != undefined
            ) {
              let object = $("#partner_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (
              single_object.customer != undefined &&
              single_object.customer == 1
            ) {
              let object = $("#customers_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.notification == 1) {
              let object = $("#send_notification_update_edit")[0];
              $(object).attr("checked", true);
            } //

            if (single_object.faq == 1) {
              let object = $("#faq_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.system == 1) {
              let object = $("#system_update_update_edit")[0];
              $(object).attr("checked", true);
            }

            if (single_object.settings == 1) {
              let object = $("#settings_update_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.system_user == 1) {
              let object = $("#system_user_update_edit")[0];
              $(object).attr("checked", true);
            }
          });
        } else if (key == "delete") {
          // console.log(single_object);
          Object.keys(single_object).forEach((val) => {
            if (single_object.order == 0) {
              let object = $("#orders_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.category == 1) {
              let object = $("#categories_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.subscription == 1) {
              let object = $("#subscription_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.sliders == 1) {
              let object = $("#sliders_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.tax == 1) {
              let object = $("#tax_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.services == 1) {
              let object = $("#services_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.promo_code == 1) {
              let object = $("#promo_code_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.featured_section == 1) {
              let object = $("#featured_section_delete_edit")[0];
              $(object).attr("checked", true);
            } //
            if (
              single_object.partner == 1 ||
              single_object.partner != undefined
            ) {
              let object = $("#partner_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (
              single_object.customer != undefined &&
              single_object.customer == 1
            ) {
              let object = $("#customers_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.notification == 1) {
              let object = $("#send_notification_delete_edit")[0];
              $(object).attr("checked", true);
            } //

            if (single_object.faq == 1) {
              let object = $("#faq_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.system == 1) {
              let object = $("#system_update_delete_edit")[0];
              $(object).attr("checked", true);
            }

            if (single_object.settings == 1) {
              let object = $("#settings_delete_edit")[0];
              $(object).attr("checked", true);
            }
            if (single_object.system_user == 1) {
              let object = $("#system_user_delete_edit")[0];
              $(object).attr("checked", true);
            }
          });
        }
      });
    }
  },
};

// slider events

$("#type_1").change(function () {
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

let source = "";
window.slider_events = {
  "click .edite-slider": function (e, value, row, index) {
    $("#id").val(row.id);
    $("#type_1").val(row.type);
    console.log(row);
    if (row.type == "provider") {
      $("#service_item_1").val(row.type_id);
    }
    if (row.type == "Category") {
      $("#Category_item_1").val(row.type_id);
    }

    var regex = /<img.*?src="(.*?)"/;
    var src = regex.exec(row.slider_image)[1];

    source = src;
    console.log(src);
    $("#id").val(row.id);
    $("#offer_image").attr("src", src);
    setTimeout(function () {
      if (row.og_status == "1") {
        $(".editInModel").prop("checked", false).trigger("click");
      } else {
        $(".editInModel").prop("checked", true).trigger("click");
      }
    }, 600);

    $("#categories_select_1").hide();
    $("#services_select_1").hide();

    $("#type_1").val(row.type).trigger("change");
  },

  "click .delete-slider": function (e, value, row, index) {
    var users_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/sliders/delete_sliders",
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
                $("#slider_list").bootstrapTable("refresh");
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

//  featured section

$(document).ready(function () {
  $("#edit_section_type").change(function (e) {
    e.preventDefault();
    if ($(this).val() == "categories") {
      $(".edit_category_item").removeClass("d-none");
      $(".edit_partners_ids").addClass("d-none");
    } else {
      $(".edit_partners_ids").removeClass("d-none");
      $(".edit_category_item").addClass("d-none");
    }
  });
});

window.featured_section_events = {
  "click .delete-featured_section": function (e, value, row, index) {
    // console.log(row);
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
          baseUrl + "/admin/featured_sections/delete_featured_section",
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
  "click .update_featured_section": function (e, value, row, index) {
    var category, partner, i, previous_order;
    console.log(row);
    $("#id").val(row.id);
    $("#edit_title").val(row.title);
    // $('input[name=e]').prop('checked', true);
    if (row.status == "1") {
      $("#edit_status_active").prop("checked", true);
    } else {
      $("#edit_status_deactive").prop("checked", true);
    }

    $("#edit_section_type").val(row.section_type).trigger("change");
    if ($("#edit_section_type").val() == "top_rated_partner") {
      $(".edit_partners_ids").addClass("d-none");
      $(".edit_category_item").addClass("d-none");
      $(".edit_ongoing_order").addClass("d-none");
    }

    if ($("#edit_section_type").val() == "top_rated_service") {
      $(".edit_partners_ids").addClass("d-none");
      $(".edit_category_item").addClass("d-none");
      $(".edit_ongoing_order").addClass("d-none");
    }

    if ($("#edit_section_type").val() == "edit_previous_order") {
      $(".edit_partners_ids").addClass("d-none");
      $(".edit_category_item").addClass("d-none");
      $(".edit_previous_order").addClass("d-none");
      $(".edit_ongoing_order").addClass("d-none");
    }

    if ($("#edit_section_type").val() == "ongoing_order") {
      $(".edit_partners_ids").addClass("d-none");
      $(".edit_category_item").addClass("d-none");
      $(".edit_previous_order").addClass("d-none");
      $(".edit_ongoing_order").removeClass("d-none");
    }

    setTimeout(function () {
      if (row.status == "1") {
        $(".editInModel").prop("checked", false).trigger("click");
      } else {
        $(".editInModel").prop("checked", true).trigger("click");
      }
    }, 600);

    if (row.section_type == "categories") {
      category = row.category_ids.split(",");
      var value_given = row.category_ids.split(",");
      console.log();
      $(document).ready(function () {
        $("#edit_Category_item").val(row.category_ids.split(",")).select2({
          placeholder: "Select Categories",
        });
      });
    } else if (row.section_type == "previous_order") {
      $("#edit_previoud_order_limit").val(row.limit);
    } else if (row.section_type == "ongoing_order") {
      $("#edit_ongoing_order_limit").val(row.limit);
    } else {
      if (row.partners_ids != null) {
        partner = row.partners_ids.split(",");
        parseInt(row.partners_ids);
        console.log(typeof row.partners_ids.split(","));
      }
      $(document).ready(function () {
        $("#edit_partners_ids").val(partner).select2({
          placeholder: "Select Partners",
        });
      });
    }
  },
};

$(document).ready(function () {
  $("#edit_section_type").on("change", function () {
    if ($(this).val() == "categories") {
      $(".edit_category_item").removeClass("d-none");
      $(".edit_partners_ids").addClass("d-none");
      $(".edit_previous_order").addClass("d-none");
      $(".edit_ongoing_order").addClass("d-none");
    } else if ($(this).val() == "partner" || $(this).val() == "partners") {
      $(".edit_category_item").addClass("d-none");
      $(".edit_partners_ids").removeClass("d-none");
      $(".edit_previous_order").addClass("d-none");
      $(".edit_ongoing_order").addClass("d-none");
    } else if (
      $(this).val() == "top_rated_partner" ||
      $(this).val() == "top_rated_service"
    ) {
      $(".edit_partners_ids").addClass("d-none");
      $(".edit_category_item").addClass("d-none");
      $(".edit_previous_order").addClass("d-none");
      $(".edit_ongoing_order").addClass("d-none");
    } else if (
      $(this).val() == "previous_order" ||
      $(this).val() == "previous_order"
    ) {
      $(".edit_partners_ids").addClass("d-none");
      $(".edit_category_item").addClass("d-none");
      $(".edit_previous_order").removeClass("d-none");
      $(".edit_ongoing_order").addClass("d-none");
    } else if (
      $(this).val() == "ongoing_order" ||
      $(this).val() == "ongoing_order"
    ) {
      $(".edit_partners_ids").addClass("d-none");
      $(".edit_category_item").addClass("d-none");
      $(".edit_previous_order").addClass("d-none");
      $(".edit_ongoing_order").removeClass("d-none");
    } else {
      $(".edit_partners_ids").addClass("d-none");
      $(".edit_category_item").addClass("d-none");
      $(".edit_previous_order").addClass("d-none");
      $(".edit_ongoing_order").addClass("d-none");
    }
  });
});

// promo code event

window.promo_codes_events = {
  "click .delete-promo_codes": function (e, value, row, index) {
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
        var input_body = {
          [csrfName]: csrfHash,
          id: id,
        };
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/promo_codes/delete",
          data: input_body,
          dataType: "json",

          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#promo_code_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              setTimeout(() => {
                $("#promo_code_list").bootstrapTable("refresh");
              }, 2000);
              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
  "click .edit": function (e, value, row, index) {

    $("#image_edit").html("");

    e.preventDefault();
    var img = row.image;
    $('input[name="promo_id"]').val(row.id);
    $('input[name="promo_code"]').val(row.promo_code);
    $("#partner").val(row.partner_id);

    $('input[name="start_date"]').val(row.start_date);
    $('input[name="end_date"]').val(row.end_date);
    $('textarea[name="message"]').val(row.message);
    $('input[name="discount"]').val(row.discount);
    $('input[name="max_discount_amount"]').val(row.max_discount_amount);
    $('input[name="minimum_order_amount"]').val(row.minimum_order_amount);
    $("#discount_type").val(row.discount_type).trigger("change");


    setTimeout(function () {
      if (row.status == "Active") {
        $(".editInModel").prop("checked", false).trigger("click");
      } else {
        $(".editInModel").prop("checked", true).trigger("click");
      }

      if (row.repeat_usage == 1) {
  
        $("#repeat_usage").prop("checked", false).trigger("click");
        $(".repeat_usage").show();

        $('input[name="no_of_repeat_usage"]').val(row.no_of_repeat_usage);
      } else {
  
        $("#repeat_usage").prop("checked", true).trigger("click");
        $(".repeat_usage").hide();
      }
    }, 600);



    $('input[name="no_of_users"]').val(row.no_of_users);
    $("#image_edit").append(img);
  },
};

window.services_events_admin = {
  "click .delete": function (e, value, row, index) {
    console.log(row);
    var id = row.id;

    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        var input_body = {
          [csrfName]: csrfHash,
          id: id,
        };
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/services/delete_service",
          data: input_body,
          dataType: "json",

          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              // console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#service_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              showToastMessage(response.message, "error");
              setTimeout(() => {
                $("#service_list").bootstrapTable("refresh");
              }, 2000);
            }
          },
        });
      }
    });
  },

  "click .edit": function (e, value, row, index) {
    console.log(row);
    $("#service_id").val(row.id);
    // To select partner
    $("#edit_partner").val(row.user_id);

    // For title of the service
    $("#edit_title").val(row.title);

    // for category and sub-category
    $("#edit_category_item").val(row.category_id).trigger("change");

    // for edit tags
    $("#edit_service_tags").val(row.tags);

    // for edit tax
    // console.log(row.tax_type.trim());
    $("#edit_tax_type").val(row.tax_type.trim());

    // for edit tax id
    $("#edit_tax").val(row.tax_id);
    // $('#edit_tax').val(row.tax);
    if (row.status_number == "1") {
      $("#edit_status_active").prop("checked", true);
    } else {
      $("#edit_status_deactive").prop("checked", true);
    }

    // for old_image

    var regex = /<img.*?src="(.*?)"/;
    if (
      row.image_of_the_service != null &&
      row.image_of_the_service != "nothing found"
    ) {
      var src = regex.exec(row.image_of_the_service)[1];
      source = src;
      $("#edit_service_image").attr("src", source);
    }
    // console.log(source);

    // edit price
    $("#edit_price").val(row.price);
    // edit discount
    $("#edit_discounted_price").val(row.discounted_price);

    // switch button events
    if (row.on_site_allowed == "Allowed" || row.on_site_allowed == "allowed") {
      // for on site
      $("#edit_on_site").attr("checked", true);
    }

    if (
      row.is_pay_later_allowed == "Allowed" ||
      row.is_pay_later_allowed == "allowed" ||
      row.is_pay_later_allowed == "1"
    ) {
      // for Pay Later

      $("#edit_pay_later").attr("checked", true);
    } else {
      $("#edit_pay_later").attr("checked", false);
    }

    if (row.cancelable == "1" || row.cancelable == "1") {
      console.log("first ");

      $("#edit_is_cancelable").prop("checked", true);
      $("#edit_cancelable_till_value").val(row.cancelable_till);
    } else {
      $("#edit_is_cancelable").prop("checked", false);
      $("#edit_cancelable_till").hide();
      $("#edit_cancelable_till_value").val("empty");
    }

    if (row.cancelable == "1" || row.cancelable == "1") {
      console.log("first ");

      $("#edit_is_cancelable").prop("checked", true);
      $("#edit_cancelable_till_value").val(row.cancelable_till);
    } else {
      $("#edit_is_cancelable").prop("checked", false);
      $("#edit_cancelable_till").hide();
      $("#edit_cancelable_till_value").val("empty");
    }

    // $("#edit_is_cancelable")
    //   .on("change", function () {

    //     if (this.checked) {
    //       $("#edit_cancelable_till").show();
    //       $()
    //     } else {
    //       $("#edit_cancelable_till").hide();
    //       $("#edit_cancelable_till_value").val("empty");
    //     }
    //   })
    //   .change();

    // for members
    $("#edit_members").val(row.number_of_members_required);
    $("#edit_duration").val(row.duration);
    $("#edit_max_qty").val(row.max_quantity_allowed);
    $("#edit_description").text(row.description);

    //
  },
};

window.subscription_events_admin = {
  "click .delete": function (e, value, row, index) {
    console.log(row);
    var id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        var input_body = {
          [csrfName]: csrfHash,
          id: id,
        };
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/subscription/delete_subscription",
          data: input_body,
          dataType: "json",

          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              // console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#subscription_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              showToastMessage(response.message, "error");
              setTimeout(() => {
                $("#subscription_list").bootstrapTable("refresh");
              }, 2000);
            }
          },
        });
      }
    });
  },
};

// $(document).ready(function () {
//     $('.edit_cancelable-till').hide();

//     $('input[type=checkbox][name=edit_is_cancelable]').change(function () {
//         if ($(this).is(':checked'))
//         {

//             // console.log('bhai');
//             $(".edit_cancelable_till").show();

//         } else {
//             $('.edit_cancelable_till').hide();
//         }
//     });

// });
// display newly selected image here for edit service only
function loadFile(event) {
  var image = document.getElementById("edit_service_image");
  image.src = URL.createObjectURL(event.target.files[0]);
}

window.payment_events_admin = {
  "click .delete": function (e, value, row, index) {
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
        var input_body = {
          [csrfName]: csrfHash,
          id: id,
        };
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partners/delete_request",
          data: input_body,
          dataType: "json",

          success: function (response) {
            csrfName = response["csrfName"];
            csrfHash = response["csrfHash"];
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#payment_request_list").bootstrapTable("refresh");
              }, 2000);
            } else {
              showToastMessage(response.message, "error");
              setTimeout(() => {
                $("#payment_request_list").bootstrapTable("refresh");
              }, 2000);
            }
          },
        });
      }
    });
  },
};

window.system_user_events = {
  "click .deactivate-user": function (e, value, row, index) {
    console.log(row);
    var user_id = row.id;
    // return;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_deactivate_this_user,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/system_users/deactivate_user",
          data: input_body,
          dataType: "json",
          timeout: 5000, // Timeout set to 5 seconds (in milliseconds)
          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 5000);

              return;
            } else {
              console.log(response);

              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },

  "click .activate-user": function (e, value, row, index) {
    console.log(row);
    var user_id = row.id;
    // return;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_activate_this_user,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/system_users/activate_user",
          data: input_body,
          dataType: "json",

          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              console.log(response);
              window.location.reload();

              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },

  "click .delete-user": function (e, value, row, index) {
    e.preventDefault();
    console.log(row);
    var user_id = row.id;
    // return;
    Swal.fire({
      title: are_your_sure,
      text: are_you_sure_you_want_to_delete_this_user,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        user_id: user_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/system_users/delete_user",
          data: input_body,
          dataType: "json",

          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              console.log(response);

              // window.location.reload();

              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },

  "click .edit-user": function (e, value, row, index) {
    $("#id").val(row.id);
    if (row.role_a == "1") {
      $("#edit_role").val("1");
    } else if (row.role_a == "2") {
      $("#edit_role").val("2");
    } else if (row.role_a == "3") {
      $("#edit_role").val("3");
    }
    $(document).ready(function () {
      // $("#edit_role").val(row.role_a).trigger("change");
      if ($("#edit_role").val() == 1) {
        $("#permissions").hide();
      } else {
        $("#permissions").show();
      }
    });
    var permissions = JSON.parse(row.permissions);

    var data = permissions != null ? true : false;
    if (data) {
      Object.keys(permissions).forEach((key) => {
        let single_object = permissions[key];

        if (key == "create") {
          Object.keys(single_object).forEach((key) => {
            // create

            if (single_object[key] == 1) {
              $("#" + key + "_create_edit").attr("checked", true);
            } else {
              $("#" + key + "_create_edit").attr("checked", false);
            }
          });
        } else if (key == "read") {
          Object.keys(single_object).forEach((key) => {
            if (single_object[key] == 1) {
              $("#" + key + "_read_edit").attr("checked", true);
            } else {
              $("#" + key + "_read_edit").attr("checked", false);
            }
          });
        } else if (key == "update") {
          Object.keys(single_object).forEach((key) => {
            if (single_object[key] == 1) {
              $("#" + key + "_update_edit").attr("checked", true);
            } else {
              $("#" + key + "_update_edit").attr("checked", false);
            }
          });
        } else if (key == "delete") {
          Object.keys(single_object).forEach((key) => {
            if (single_object[key] == 1) {
              $("#" + key + "_delete_edit").attr("checked", true);
            } else {
              $("#" + key + "_delete_edit").attr("checked", false);
            }
          });
        }
      });
    }
  },
};

function set_attribute_checked(ids) {
  for (let i = 0; i < Object.keys(ids).length; i++) {
    const element = ids[i];
    // console.log(element[0]);
    $(element[0]).attr("checked", true);
  }
}

$("#permissions").show();

$(document).ready(function () {
  $("#role").on("change", function (e) {
    let role = $(this).val();
    if (role == "1") {
      $("#permissions").hide();
    } else {
      $("#permissions").show();
    }
  });
});
$("#edit_role").on("change", function (e) {
  let role = $(this).val();
  if (role == "1") {
    $("#permissions").hide();
  } else {
    $("#permissions").show();
  }
});

window.commission_events = {
  "click .pay-out": function (e, value, row, index) {
    console.log(row);

    $("#partner_id").val(row.partner_id);
  },
};

window.notification_event = {
  "click .delete-notification": function (e, value, row, index) {
    console.log(row);
    var users_id = row.id;
    Swal.fire({
      title: are_your_sure,
      text: you_wont_be_able_to_revert_this,
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      if (result.isConfirmed) {
        $.post(
          baseUrl + "/admin/notification/delete_notification",
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

// partner windows

// partner events

window.partner_events = {
  "click .deactivate_partner": function (e, value, row, index) {
    // console.log(row);
    var id = row.partner_id;
    // console.log(id);
    // return;

    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to deactivate this provider",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partner/deactivate_partner",
          data: input_body,
          dataType: "json",

          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              console.log(response);
              window.location.reload();

              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },

  "click .activate_partner": function (e, value, row, index) {
    console.log(row);
    var id = row.partner_id;
    // console.log(id);
    // return;

    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to activate this provider",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partner/activate_partner",
          data: input_body,
          dataType: "json",

          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              console.log(response);
              window.location.reload();

              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },

  "click .approve_partner": function (e, value, row, index) {
    ``;
    // console.log(row);
    var id = row.partner_id;
    // console.log(id);
    // return;

    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to approve this provider",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partner/approve_partner",
          data: input_body,
          dataType: "json",

          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();
              return;
            } else {
              console.log(response);
              window.location.reload();

              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },

  "click .disapprove_partner": function (e, value, row, index) {
    var id = row.partner_id;

    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to disapprove this provider",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partner/disapprove_partner",
          data: input_body,
          dataType: "json",

          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();

              return;
            } else {
              // console.log(response);
              window.location.reload();

              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },

  "click .delete_partner": function (e, value, row, index) {
    console.log("delete clicked");
    var id = row.partner_id;
    // console.log(id);
    // return;

    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to delete this provider",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        partner_id: id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/partner/delete_partner",
          data: input_body,
          dataType: "json",

          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#partner_list").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();

              return;
            } else {
              console.log(response);
              window.location.reload();

              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },

  "click .view_rating": function (e, value, row, index) {
    // console.log(row);
    $("#partner_id").val(row.partner_id);
    var id = row.partner_id;

    $("#rating_table").bootstrapTable("refresh", {
      url: baseUrl + "/admin/partners/view_ratings/" + id,
    });

    // console.log($('#rating_table').attr('data-url'));
  },

  "click .edit": function (e, value, row, index) {
    // console.log(row);
    $("#company_name").val(row.company_name);
    if (row.type == "Individual") {
      $("#type").val(0);
    } else {
      $("#type").val(1);
    }

    $("#partner_id").val(row.partner_id);

    $("#about").val(row.about);
    $("#visiting_charges").val(row.visiting_charges);
    $("#advance_booking_days").val(row.advance_booking_days);
    $("#number_of_members").val(row.number_of_members);
    $("#city").val(row.city);
    $("#partner_latitude").val(row.latitude);
    $("#partner_longitude").val(row.longitude);
    $("#address").val(row.address);

    $("#username").val(row.partner_name);
    $("#email").val(row.email);
    $("#phone").val(row.mobile);
    // $('#password').val(row.address);
    $("#admin_commission").val(row.admin_commission);
    $("#tax_name").val(row.tax_name);
    $("#tax_number").val(row.tax_number);
    $("#account_number").val(row.account_number);
    $("#account_name").val(row.account_name);
    $("#bank_code").val(row.bank_code);
    $("#bank_name").val(row.bank_name);
    $("#swift_code").val(row.swift_code);

    $("#image_preview").attr("src", row.image);
    $("#banner_image_preview").attr("src", row.banner_edit);
    $("#national_id_preview").attr("src", row.national_id);
    $("#passport_preview").attr("src", row.passport);
    $("#address_id_preview").attr("src", row.address_id);

    if (row.is_approved_edit == "1") {
      $("#is_approved_partner").prop("checked", true);
    } else {
      $("#is_disapproved_partner").prop("checked", true);
    }

    if (row.monday_is_open == 1) {
      $("#monday_opening_time").val(row.monday_opening_time);
      $("#monday_closing_time").val(row.monday_closing_time);
      $("#monday").prop("checked", true);
      $("#monday_opening_time").removeAttr("readOnly");
      $("#monday_closing_time").removeAttr("readOnly");
    } else {
      $("#monday_opening_time").val();
      $("#monday_closing_time").val();
      $("#monday").prop("checked", false);
      $("#monday_opening_time").attr("readOnly", "readOnly");
      $("#monday_closing_time").attr("readOnly", "readOnly");
    }

    if (row.tuesday_is_open == 1) {
      $("#tuesday_opening_time").val(row.tuesday_opening_time);
      $("#tuesday_closing_time").val(row.tuesday_closing_time);
      $("#tuesday").prop("checked", true);
      $("#tuesday_opening_time").removeAttr("readOnly");
      $("#tuesday_closing_time").removeAttr("readOnly");
    } else {
      $("#tuesday_opening_time").val();
      $("#tuesday_closing_time").val();
      $("#tuesday").prop("checked", false);
      $("#tuesday_opening_time").attr("readOnly", "readOnly");
      $("#tuesday_closing_time").attr("readOnly", "readOnly");
    }

    if (row.wednesday_is_open == 1) {
      console.log("q347384");
      $("#wednesday_opening_time").val(row.wednesday_opening_time);
      $("#wednesday_closing_time").val(row.wednesday_closing_time);
      $("#wednesday").prop("checked", true);
      $("#wednesday_opening_time").removeAttr("readOnly");
      $("#wednesday_closing_time").removeAttr("readOnly");
    } else {
      $("#wednesday_opening_time").val();
      $("#wednesday_closing_time").val();
      $("#wednesday").prop("checked", false);
      $("#wednesday_opening_time").attr("readOnly", "readOnly");
      $("#wednesday_closing_time").attr("readOnly", "readOnly");
    }

    if (row.thursday_is_open == 1) {
      $("#thursday_opening_time").val(row.thursday_opening_time);
      $("#thursday_closing_time").val(row.thursday_closing_time);
      $("#thursday").prop("checked", true);
      $("#thursday_opening_time").removeAttr("readOnly");
      $("#thursday_closing_time").removeAttr("readOnly");
    } else {
      $("#thursday_opening_time").val();
      $("#thursday_closing_time").val();
      $("#thursday").prop("checked", false);
      $("#thursday_opening_time").attr("readOnly", "readOnly");
      $("#thursday_closing_time").attr("readOnly", "readOnly");
    }
    if (row.friday_is_open == 1) {
      $("#friday_opening_time").val(row.friday_opening_time);
      $("#friday_closing_time").val(row.friday_closing_time);
      $("#friday").prop("checked", true);
      $("#friday_opening_time").removeAttr("readOnly");
      $("#friday_closing_time").removeAttr("readOnly");
    } else {
      $("#friday_opening_time").val();
      $("#friday_closing_time").val();
      $("#friday").prop("checked", false);
      $("#friday_opening_time").attr("readOnly", "readOnly");
      $("#friday_closing_time").attr("readOnly", "readOnly");
    }

    if (row.saturday_is_open == 1) {
      $("#saturday_opening_time").val(row.saturday_opening_time);
      $("#saturday_closing_time").val(row.saturday_closing_time);
      $("#saturday").prop("checked", true);
      $("#saturday_opening_time").removeAttr("readOnly");
      $("#saturday_closing_time").removeAttr("readOnly");
    } else {
      $("#saturday_opening_time").val();
      $("#saturday_closing_time").val();
      $("#saturday").prop("checked", false);
      $("#saturday_opening_time").attr("readOnly", "readOnly");
      $("#saturday_closing_time").attr("readOnly", "readOnly");
    }

    if (row.sunday_is_open == 1) {
      $("#sunday_opening_time").val(row.sunday_opening_time);
      $("#sunday_closing_time").val(row.sunday_closing_time);
      $("#sunday").prop("checked", true);
      $("#sunday_opening_time").removeAttr("readOnly");
      $("#sunday_closing_time").removeAttr("readOnly");
    } else {
      $("#sunday_opening_time").val();
      $("#sunday_closing_time").val();
      $("#sunday").prop("checked", false);
      $("#sunday_opening_time").attr("readOnly", "readOnly");
      $("#sunday_closing_time").attr("readOnly", "readOnly");
    }

    $("#number_of_members").attr("readOnly", "readOnly");
    $("#type").change(function () {
      var doc = document.getElementById("type");

      if (doc.options[doc.selectedIndex].value == 0) {
        // console.log('0 selectc');
        $("#number_of_members").val("1");

        $("#number_of_members").attr("readOnly", "readOnly");
      } else if (doc.options[doc.selectedIndex].value == 1) {
        $("#number_of_members").val("");
        $("#number_of_members").removeAttr("readOnly");
      }
      // alert("You selected " + doc.options[doc.selectedIndex].value);
    });

    // console.log($('#rating_table').attr('data-url'));
  },
};

window.rating_event = {
  "click .delete_rating": function (e, value, row, index) {
    console.log(row);
    var id = row.id;
    // console.log(id);
    // return;

    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to delete this rating",
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
          url: baseUrl + "/admin/partners/delete_rating",
          data: input_body,
          dataType: "json",

          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#rating_table").bootstrapTable("refresh");
              }, 2000);
              window.location.reload();

              return;
            } else {
              console.log(response);
              window.location.reload();

              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
};

window.order_service_events = {
  "click .cancel_order": function (e, value, row, index) {
    console.log(row);
    var id = row.id;
    var service_id = row.service_id;
    Swal.fire({
      title: are_your_sure,
      text: "Are you sure you want to cancel this service",
      icon: "error",
      showCancelButton: true,
      confirmButtonText: yes_proceed,
    }).then((result) => {
      var input_body = {
        [csrfName]: csrfHash,
        id: id,
        service_id: service_id,
      };
      if (result.isConfirmed) {
        $.ajax({
          type: "POST",
          url: baseUrl + "/admin/orders/cancel_order_service",
          data: input_body,
          dataType: "json",
          success: function (response) {
            if (response.error == false) {
              console.log(response);
              showToastMessage(response.message, "success");
              setTimeout(() => {
                $("#ordered_services_list").bootstrapTable("refresh");
              }, 2000);
              // window.location.reload();

              // return;
            } else {
              console.log(response);
              setTimeout(() => {
                $("#ordered_services_list").bootstrapTable("refresh");
              }, 2000);

              return showToastMessage(response.message, "error");
            }
          },
        });
      }
    });
  },
};

function cancel_service(e) {
  console.log(e);
  // e.preventDefault();
  var id = $(e).data("id");
  var service_id = $(e).data("service_id");

  console.log(id, service_id);

  Swal.fire({
    title: are_your_sure,
    text: "Are you sure you want to cancel this service",
    icon: "error",
    showCancelButton: true,
    confirmButtonText: yes_proceed,
  }).then((result) => {
    var input_body = {
      [csrfName]: csrfHash,
      id: id,
      service_id: service_id,
    };
    if (result.isConfirmed) {
      $.ajax({
        type: "POST",
        url: baseUrl + "/admin/orders/cancel_order_service",
        data: input_body,
        dataType: "json",
        success: function (response) {
          if (response.error == false) {
            console.log(response);
            showToastMessage(response.message, "success");
            setTimeout(() => {
              $("#ordered_services_list").bootstrapTable("refresh");
            }, 2000);
            window.location.reload();

            // return;
          } else {
            console.log(response);
            setTimeout(() => {
              $("#ordered_services_list").bootstrapTable("refresh");
            }, 2000);
            window.location.reload();
            return showToastMessage(response.message, "error");
          }
        },
      });
    }
  });
}

$(document).ready(function () {
  $("#available-slots").hide();
  $(".rescheduled_date").hide();
  $(".work_started_proof").hide();
  $(".work_completed_proof").hide();

  $("#status").change(function (e) {
    e.preventDefault();
    var status = $("#status").val();

    if (status === "rescheduled") {
      $("#available-slots").show();
      $(".rescheduled_date").show();
      $(".work_started_proof").hide();
      $(".work_completed_proof").hide();
    } else {
      $("#available-slots").hide();
      $(".rescheduled_date").hide();
      $(".work_started_proof").hide();
      $(".work_completed_proof").hide();
    }

    if (status == "started") {
      $(".work_started_proof").show();
    } else {
      $(".work_started_proof").hide();
    }

    if (status == "completed") {
      $(".work_completed_proof").show();
    } else {
      $(".work_completed_proof").hide();
    }
  });

  $("#rescheduled_date").change(function (e) {
    $("#available-slots").empty();
    var weekday = new Array(7);
    e.preventDefault();
    var date = $("#rescheduled_date").val();
    var d = new Date(date);
    var id = $("#order_id").val();
    var input_body = {
      [csrfName]: csrfHash,
      id: id,
      date: date,
    };
    $.ajax({
      type: "POST",
      url: baseUrl + "/admin/orders/get_slots",
      data: input_body,
      dataType: "JSON",
      success: function (response) {
        if (response.error == false) {
          var slots = response.available_slots;
          var slot_selector = "";
          if (slots == "") {
            slot_selector += `   <div class="col-md-12 form-group">
                                       <div class="selectgroup">
                                           <label class="selectgroup-item">
                                           <span class="text-danger">There is no slot available on this date!</span>
                                           </label>                                    
                                       </div>
                                   </div>
                                    `;
          } else {
            slots.forEach((element) => {
              slot_selector += `   <div class="col-md-2 form-group">
                <div class="selectgroup">
                    <label class="selectgroup-item">
                        <input type="radio" name="reschedule" value="${element}" class="selectgroup-input">
                        <span class="selectgroup-button selectgroup-button-icon">
                            <i class="fas fa-sun"></i> &nbsp; 
                            <div class="text-dark">${element}</div>
                        </span>
                    </label>                                    
                </div>
            </div>`;
            });
          }

          $("#available-slots").append(slot_selector);
        } else {
          var slot_selector = "";
          if (response.error == true) {
            slot_selector +=
              `   <div class="col-md-12 form-group">
                                       <div class="selectgroup">
                                           <label class="selectgroup-item">
                                               <span class="text-danger">` +
              response.message +
              `</span>
                                           </label>                                    
                                       </div>
                                   </div>
                                    `;
          }
          console.log(slot_selector);
          $("#available-slots").append(slot_selector);
          setTimeout(() => {
            $("#ordered_services_list").bootstrapTable("refresh");
          }, 2000);
        }
      },
    });
  });

  $("#change_status").on("click", function (e) {
    e.preventDefault();
    var status = $("#status").val();
    var order_id = $("#order_id").val();
    var date = $("#rescheduled_date").val();
    var is_otp_enable = $("#is_otp_enable").val();
    var selected_time = "";
    var formdata = new FormData($("#myForm")[0]);
    if ($(".selectgroup-input").length > 1) {
      selected_time = $('input[name="reschedule"]:checked').val();
    }
    if (is_otp_enable == 1) {
      if (status == "completed") {
        Swal.fire({
          title: are_your_sure,
          text: you_wont_be_able_to_revert_this,
          icon: "error",
          input: "text",
          // inputValue: "Settlement",
          inputPlaceholder: "Enter OTP here",
          inputAttributes: {
            autocapitalize: "off",
            required: "true",
          },
          showCancelButton: true,
          confirmButtonText: yes_proceed,
        }).then((result) => {
          if (result.value) {
            formdata.append("otp", result.value);
            $.ajaxSetup({
              headers: {
                "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
              },
            });
            $.ajax({
              url: baseUrl + "/admin/orders/change_order_status",
              data: formdata,
              processData: false,
              contentType: false,
              type: "post",
              dataType: "json",
              beforeSend: function () {
                $("#change_status").attr("disabled", true);
                $("#change_status").removeClass("btn-primary");
                $("#change_status").addClass("btn-secondary");
                $("#change_status").html(
                  '<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
                );
              },
              success: function (response) {
                //  console.log(response);
                if (response.error == false) {
                  showToastMessage(response.message, "success");
                  window.location.reload(true);
                } else {
                  showToastMessage(response.message, "error");
                  window.location.reload(true);
                }
                return;
              },
              error: function (response) {
                showToastMessage(response.message, "error");
                window.location.reload(true);
              },
            });
          }
        });
      } else {
        $.ajaxSetup({
          headers: {
            "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
          },
        });
        $.ajax({
          url: baseUrl + "/admin/orders/change_order_status",
          data: formdata,
          type: "post",
          dataType: "json",
          processData: false,
          contentType: false,
          beforeSend: function () {
            $("#change_status").attr("disabled", true);
            $("#change_status").removeClass("btn-primary");
            $("#change_status").addClass("btn-secondary");
            $("#change_status").html(
              '<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
            );
          },
          success: function (response) {
            console.log("success");
            if (response.error == false) {
              showToastMessage(response.message, "success");
              window.location.reload(true);
            } else {
              showToastMessage(response.message, "error");
              window.location.reload(true);
            }
            return;
          },
          error: function (xhr) {
            showToastMessage(response.message, "error");
            window.location.reload(true);
          },
        });
      }
    } else {
      $.ajaxSetup({
        headers: {
          "X-CSRF-TOKEN": $('meta[name="csrf-token"]').attr("content"),
        },
      });
      $.ajax({
        url: baseUrl + "/admin/orders/change_order_status",
        data: formdata,
        processData: false,
        contentType: false,
        type: "post",
        dataType: "json",
        beforeSend: function () {
          $("#change_status").attr("disabled", true);
          $("#change_status").removeClass("btn-primary");
          $("#change_status").addClass("btn-secondary");
          $("#change_status").html(
            '<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>'
          );
        },
        success: function (response) {
          console.log(response);
          if (response.error == false) {
            showToastMessage(response.message, "success");
            window.location.reload(true);
          } else {
            showToastMessage(response.message, "error");
            window.location.reload(true);
          }
          return;
        },
        error: function (response) {
          showToastMessage(response.message, "error");
          window.location.reload(true);
        },
      });
    }
  });
});

window.cash_collection_events = {
  "click .edit_cash_collection": function (e, value, row, index) {
    console.log(row);
    $("#partner_id").val(row.partner_id);
    $("#amount").val(row.payable_commision);
  },
};
