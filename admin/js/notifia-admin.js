(function ($) {
    'use strict';

    $(function () {

        const userEmailField = $('.notifia-signup-email');
        const loginForm = $('#notifia-loginForm');
        const signupForm = $('#notifia-signupForm');
        const googleLoginButton = $('#notifia-google-log-in');
        const loginFormErrorWrapper = $('#nt-login-error-messages');


        window.addEventListener('message', onWindowMessage, false);


        function onWindowMessage(event) {
            const eventDaa = event.data;
            if (eventDaa && eventDaa.token && eventDaa.scriptId) {
                fetchUserData(eventDaa.token);
            }
        }


        /** google login **/

        googleLoginButton.click(function () {
            const features = 'toolbar=yes,top=500,left=500,width=640,height=640';
            window.open(notifiaSettings.googleLoginLink, '_blank', features);
        })

        /** google login **/


        /** login submit **/


        loginForm.submit(function (e) {
            e.preventDefault();
            const actionUrl = loginForm.attr('action');
            const values = $(this).serializeArray();
            const formData = objectifyForm(values);
            loginFormErrorWrapper.addClass('nt-display-none');

            markSendingForm(true);

            $.ajax({
                contentType: "application/json; charset=utf-8",
                method: "POST",
                url: actionUrl,
                data: JSON.stringify(formData)
            })
                .done(function (response) {
                    fetchUserData(response.data.token);
                })
                .fail(function (error) {
                    markSendingForm(false);

                    const errorJson = error.responseJSON.message;
                    let errorHtml = ''
                    loginFormErrorWrapper.removeClass('nt-display-none');

                    if (!errorJson.email && !errorJson.password) {
                        errorHtml = (`${errorJson}`)
                    } else {
                        $.each(errorJson,function(index,value){
                            errorHtml += `${value.msg}<br>`;
                        })
                    }
                    loginFormErrorWrapper.html(errorHtml);


                });

            return false;
        })

        /** end login submit **/


        /** sign up submit **/

        signupForm.submit(function (e) {
            e.preventDefault();
            let url = `${notifiaSettings.signupPath}?email=${userEmailField.val()}`;
            window.open(url, '_blank');


            return false;
        });

        /** end sign up submit **/


        /** fet user data **/

        function fetchUserData(token) {
            $.ajax({
                contentType: "application/json; charset=utf-8",
                method: "GET",
                headers: {"Authorization": token},
                url: notifiaSettings.fetchEndpoint
            })
                .done(function (userInfo) {
                    const responseData = userInfo.data;
                    markSendingForm(false);
                    if (!responseData.subscribed) {
                        alert("Unfortunately you do not have an active account, please visit the Notifia.io and select a plan.")
                        window.open(notifiaSettings.loginPath, '_blank');

                    } else {
                        notifiaSettings.script_id = responseData.scriptId;
                        saveApiKey()
                    }
                })
                .fail(function (response) {
                    markSendingForm(false);
                    console.log(response, 'error response ');

                });

        }

        /** fetch user data **/



        function saveApiKey() {
            if (notifiaSettings.script_id) {
                $.ajax({
                    type: "POST",
                    url: ajaxurl,
                    data: {
                        action: notifiaSettings.success_action,
                        script_id: notifiaSettings.script_id
                    },
                    success: function (data) {
                        const response = JSON.parse(data);
                        if (response.redirect_link) {
                            window.location.href = response.redirect_link;
                        } else {
                            alert('Oops! Something wrong. API key was not found!');
                        }
                    },
                    error: function (e) {
                        alert('Oops! Something wrong. It is impossible to save an API key!');
                    }
                });
            } else {
                alert('Oops! Something wrong. API key was not found!');
            }

        }

        /** logout function **/

        if ($('[notifia-logout]').length) {
            notifiaSettings.script_id = null;
            $.ajax({
                type: "POST",
                url: ajaxurl,
                dataType: 'json',
                data: {
                    action: notifiaSettings.action,
                    notifia_clear_api_key: true
                },
                success: function (data) {
                    if (!data.error) {
                        if (data.redirect_link) {
                            window.location.href = data.redirect_link;
                        } else {
                            window.location.reload();
                        }
                    } else {
                        alert('Oops! Something wrong. Your API key is expired!');
                    }
                },
                error: function (e) {
                    console.log(e);
                    alert('Oops! Something wrong. It is impossible to save an API key! 4 ');
                }
            });
        }

        /** logout function **/


        function markSendingForm(isSending) {
            var button = $(loginForm.find('input[type="submit"]')[0]);
            if (isSending) {
                button.val(button.data('sending-text')).attr('disabled', true);
            } else {
                button.attr('disabled', false).val(button.data('text'));
            }
        }


        function objectifyForm(formArray) {//serialize data function

            var returnArray = {};
            for (var i = 0; i < formArray.length; i++) {
                returnArray[formArray[i]['name']] = formArray[i]['value'];
            }
            return returnArray;
        }


    });

})(jQuery);
