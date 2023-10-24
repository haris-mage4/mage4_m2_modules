require(['jquery'], function($) {
    $(document).ready(function() {

        // Prevent the login form from submitting
        $('#login-form').submit(function(e) {

            if (!$("input[name='found_custom_store']").length) {
                e.preventDefault();

                jQuery.ajax({
                    type: "GET",
                    url: "/customlogin/getstore/byemail",
                    data: { email: jQuery("#email").val() },
                    success: function (result) {

                        $('#login-form').append("<input type='hidden' name='found_custom_store' value='" + result + "' />");

                        var action = $('#login-form').attr("action");

                        var actionParts = action.split("/");

                        actionParts[3] = result;

                        var newAction = actionParts.join("/");

                        $('#login-form').attr("action", newAction);

                        jQuery.ajax({
                            type: 'POST',
                            url: newAction,
                            data: $('#login-form').serialize(),
                            success: function(response) {
                                // Handle the successful response
                                console.log('Form submitted successfully');
                                $('#login-form').submit();
                            },
                            error: function() {
                                // Handle the error by submitting the form normally
                                console.log('AJAX request failed, submitting form normally');
                                $('#login-form').submit();
                            }
                        });

                    }
                });

            }
        });
    });
});