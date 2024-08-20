"use strict";

// use this for events only

$(document).ready(function () {
    $('#available-slots').hide();
    $('.rescheduled_date').hide();
    $('.work_started_proof').hide();
    $('.work_completed_proof').hide();
    $('#status').change(function (e) {
        e.preventDefault();
        var status = $('#status').val();
        if (status === 'rescheduled')
        {
            $('#available-slots').show();
            $('.rescheduled_date').show();
            $('.work_started_proof').hide();
            $('.work_completed_proof').hide();
        } else
        {
            $('#available-slots').hide();
            $('.rescheduled_date').hide();
            $('.work_started_proof').hide();
            $('.work_completed_proof').hide();
        }
        if (status == "started")
        {
            $('.work_started_proof').show();
            

        } else
        {
            $('.work_started_proof').hide();
     
        }
        if (status == "completed")
        {

            $('.work_completed_proof').show();

        } else
        {
            $('.work_completed_proof').hide();

        }
    });

    $('#rescheduled_date').change(function (e) {

        console.log('rescheduled_date called');

        $('#available-slots').empty();
        var weekday = new Array(7);
        e.preventDefault();
        var date = $('#rescheduled_date').val()
        var d = new Date(date)
        var id = $('#order_id').val();
        var input_body = {
            [csrfName]: csrfHash,
            'id': id,
            'date': date
        };
        $.ajax({
            type: "POST",
            url: baseUrl + "/partner/orders/get_slots",
            data: input_body,
            dataType: "JSON",
            success: function (response) {
                if (response.error == false)
                {
                    var slots = response.available_slots;
                    var slot_selector = "";
                    slots.forEach(element => {

                      
                        slot_selector +=
                            `  <div class="col-md-2 form-group">
                            <div class="selectgroup">
                                <label class="selectgroup-item">
                                    <input type="radio" name="reschedule" value="${element}" class="selectgroup-input">
                                    <span class="selectgroup-button selectgroup-button-icon">
                                        <i class="fas fa-sun "></i> &nbsp; 
                                        <div class="text-dark">${element}</div>
                                    </span>
                                </label>                                    
                            </div>
                        </div>`;
                    });

                    $('#available-slots').append(slot_selector);
                    
                } else
                {
                    setTimeout(() => {
                        $('#ordered_services_list').bootstrapTable('refresh')
                    }, 2000);
                }
            }
        });

    });

    
 
  

    // $('#change_status').on('click', function (e) {
    //     e.preventDefault();
    //     var status = $('#status').val();
    //     var order_id = $('#order_id').val();
    //     var date = $('#rescheduled_date').val();
    //     var is_otp_enable = $('#is_otp_enable').val();
    //     var selected_time = '';
    //     var formdata = new FormData($('#myForm')[0]);
    //     if ($('.selectgroup-input').length > 1)
    //     {
    //         selected_time = $('input[name="reschedule"]:checked').val();
    //     }
    //     if (is_otp_enable == 1)
    //     {
    //         if (status == "completed")
    //         {
    //             Swal.fire({
    //                 title: are_your_sure,
    //                 text: you_wont_be_able_to_revert_this,
    //                 icon: 'error',
    //                 input: 'text',
    //                 // inputValue: "Settlement",
    //                 inputPlaceholder: 'Enter OTP here',
    //                 inputAttributes: {
    //                     autocapitalize: 'off',
    //                     required: 'true',
    //                 },
    //                 showCancelButton: true,
    //                 confirmButtonText: yes_proceed
    //             }).then((result) => {
    //                 if (result.value)
    //                 {
    //                     formdata.append('otp', result.value);
    //                     $.ajaxSetup({
    //                         headers: {
    //                             'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //                         }
    //                     });
    //                     $.ajax({
    //                         url: baseUrl + "/partner/orders/update_order_status",
    //                         data: formdata,
    //                         processData: false,
    //                         contentType: false,
    //                         type: 'post',
    //                         dataType: "json",
    //                         beforeSend: function () {
    //                             $("#change_status").attr("disabled", true);
    //                             $("#change_status").removeClass("btn-primary");
    //                             $("#change_status").addClass("btn-secondary");
    //                             $("#change_status").html('<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>');
    //                         },
                           
    //                         success: function (response) {
    //                             //  console.log(response);
    //                             if (response.error == false)
    //                             {
    //                                 showToastMessage(response.message, "success");
    //                                 window.location.reload(true);
    //                             } else
    //                             {
    //                                  showToastMessage(response.message, "error");
    //                                  window.location.reload(true);
    //                             }
    //                             return;
    //                         },
    //                         error: function (response) {
    //                             window.location.reload(true);
    //                             console.log(response);
    //                             // return showToastMessage(response.message, "error");
    //                         }
    //                     })
    //                 }
    //             })
    //         } else
    //         {
    //             $.ajaxSetup({
    //                 headers: {
    //                     'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //                 }
    //             });
    //             $.ajax({
    //                 url:baseUrl + "/partner/orders/update_order_status",
    //                 data: formdata,
    //                 type: 'post',
    //                 dataType: "json",
    //                 processData: false,
    //                 contentType: false,
    //                 beforeSend: function () {
    //                     $("#change_status").attr("disabled", true);
    //                     $("#change_status").removeClass("btn-primary");
    //                     $("#change_status").addClass("btn-secondary");
    //                     $("#change_status").html('<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>');
    //                 },
                   
    //                 success: function (response) {
    //                     console.log('success');
    //                     if (response.error == false)
    //                     {
    //                         showToastMessage(response.message, "success");
    //                         window.location.reload(true);
    //                     } else
    //                     {
    //                         showToastMessage(response.message, "error");
    //                         window.location.reload(true);
    //                     }
    //                     return;
    //                 },
    //                 error: function (xhr) {
    //                     alert('Request Status: ' + xhr.status + ' Status Text: ' + xhr.statusText + ' ' + xhr.responseText);
    //                 }
    //             });
    //         }
    //     }
    //     else
    //     {
    //         $.ajaxSetup({
    //             headers: {
    //                 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
    //             }
    //         });
    //         $.ajax({
    //             url: baseUrl + "/partner/orders/update_order_status",
    //             data: formdata,
    //             processData: false,
    //             contentType: false,
    //             type: 'post',
    //             dataType: "json",
    //             beforeSend: function () {
    //                 $("#change_status").attr("disabled", true);
    //                 $("#change_status").removeClass("btn-primary");
    //                 $("#change_status").addClass("btn-secondary");
    //                 $("#change_status").html('<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>');
    //             },
               
    //             success: function (response) {
    //                 console.log(response);
    //                 if (response.error == false)
    //                 {
    //                     showToastMessage(response.message, "success");
    //                     window.location.reload(true);
    //                 } else
    //                 {
    //                     showToastMessage(response.message, "error");
    //                     window.location.reload(true);
    //                 }
    //                 return;
    //             },
    //             error: function (response) {
    //                 return showToastMessage(response.message, "error");
    //             }
    //         });
    //     }
    // })
    $('#change_status').on('click', function (e) {
        e.preventDefault();
        var status = $('#status').val();
        var order_id = $('#order_id').val();
        var date = $('#rescheduled_date').val();
        var is_otp_enable = $('#is_otp_enable').val();
        var selected_time = '';
        var formdata = new FormData($('#myForm')[0]);
        if ($('.selectgroup-input').length > 1)
        {
            selected_time = $('input[name="reschedule"]:checked').val();
        }
        if (is_otp_enable == 1)
        {
            if (status == "completed")
            {
                Swal.fire({
                    title: are_your_sure,
                    text: you_wont_be_able_to_revert_this,
                    icon: 'error',
                    input: 'text',
                    // inputValue: "Settlement",
                    inputPlaceholder: 'Enter OTP here',
                    inputAttributes: {
                        autocapitalize: 'off',
                        required: 'true',
                    },
                    showCancelButton: true,
                    confirmButtonText: yes_proceed
                }).then((result) => {
                    if (result.value)
                    {
                        formdata.append('otp', result.value);
                        $.ajaxSetup({
                            headers: {
                                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                            }
                        });
                        $.ajax({
                            url:baseUrl + "/partner/orders/update_order_status",
                            data: formdata,
                            processData: false,
                            contentType: false,
                            type: 'post',
                            dataType: "json",
                            beforeSend: function () {
                                $("#change_status").attr("disabled", true);
                                $("#change_status").removeClass("btn-primary");
                                $("#change_status").addClass("btn-secondary");
                                $("#change_status").html('<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>');
                            },
                            success: function (response) {
                                //  console.log(response);
                                if (response.error == false)
                                {
                                    showToastMessage(response.message, "success");
                                    window.location.reload(true);
                                } else
                                {
                                    showToastMessage(response.message, "error");
                                    window.location.reload(true);
                                }
                                return;
                            },
                            error: function (response) {
                                // showToastMessage(response.message, "error");
                                console.log(response);
                                    //  window.location.reload(true);
                            }
                        })
                    }
                })
            } else
            {
                $.ajaxSetup({
                    headers: {
                        'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                    }
                });
                $.ajax({
                    url: baseUrl + "/partner/orders/update_order_status",
                    data: formdata,
                    type: 'post',
                    dataType: "json",
                    processData: false,
                    contentType: false,
                    beforeSend: function () {
                        $("#change_status").attr("disabled", true);
                        $("#change_status").removeClass("btn-primary");
                        $("#change_status").addClass("btn-secondary");
                        $("#change_status").html('<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>');
                    },
                    success: function (response) {
                        console.log('success');
                        if (response.error == false)
                        {
                            showToastMessage(response.message, "success");
                            window.location.reload(true);
                        } else
                        {
                            showToastMessage(response.message, "error");
                            window.location.reload(true);
                        }
                        return;
                    },
                    error: function (xhr) {
                        showToastMessage(response.message, "error");
                                     window.location.reload(true);
                    }
                });
            }
        }
        else
        {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.ajax({
                url: baseUrl + "/partner/orders/update_order_status",
                data: formdata,
                processData: false,
                contentType: false,
                type: 'post',
                dataType: "json",
                beforeSend: function () {
                    $("#change_status").attr("disabled", true);
                    $("#change_status").removeClass("btn-primary");
                    $("#change_status").addClass("btn-secondary");
                    $("#change_status").html('<div class="spinner-border text-primary spinner-border-sm mx-3" role="status"><span class="visually-hidden"></span></div>');
                },
                success: function (response) {
                    console.log(response);
                    if (response.error == false)
                    {
                        showToastMessage(response.message, "success");
                        window.location.reload(true);
                    } else
                    {
                        showToastMessage(response.message, "error");
                        window.location.reload(true);
                    }
                    return;
                },
                error: function (response) {
                    showToastMessage(response.message, "error");
                    window.location.reload(true);
                }
            });
        }
    })
});

window.order_service_event = {
    'click .cancel_service': function (e, value, row, index) {
        console.log(row);
    }
}


