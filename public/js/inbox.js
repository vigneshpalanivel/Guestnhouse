var daterangepicker_format = $('meta[name="daterangepicker_format"]').attr('content');
var datepicker_format = $('meta[name="datepicker_format"]').attr('content');


app.directive('inboxPagination', function() {
    return {
        restrict: 'E',
        template: '<ul class="pagination">' +
            '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="messages_result(1)">&laquo;</a></li>' +
            '<li ng-show="currentPage != 1"><a href="javascript:void(0)" ng-click="messages_result(currentPage-1)">&lsaquo; ' + $('#pagin_prev').val() + ' </a></li>' +
            '<li ng-repeat="i in range" ng-class="{active : currentPage == i}">' +
            '<a href="javascript:void(0)" ng-click="messages_result(i)">{{i}}</a>' +
            '</li>' +
            '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="messages_result(currentPage+1)"> ' + $('#pagin_next').val() + ' &rsaquo;</a></li>' +
            '<li ng-show="currentPage != totalPages"><a href="javascript:void(0)" ng-click="messages_result(totalPages)">&raquo;</a></li>' +
            '</ul>'
    };
}).controller('inbox', ['$scope', '$http','$rootScope', function($scope, $http,$rootScope) {
    $scope.today = new Date();

    setTimeout(function() {

        $scope.totalPages = 0;
        $scope.currentPage = 1;
        $scope.range = [];

        pageNumber = 1;

        if (pageNumber === undefined) {
            pageNumber = '1';
        }

        var type = $('#inbox_filter_select').val();

        var data = $scope.user_id;

        $http.post('inbox/message_by_type', {
            data: data,
            type: type
        }).then(function(response) {
            $('.inbox-list').removeClass('loading');
            $('.inbox-list'). removeAttr("style");
            $scope.message_result = response.data;
            $scope.totalPages = response.data.last_page;
            $scope.currentPage = response.data.current_page;
            // Pagination Range
            var pages = [];

            for (var i = 1; i <= response.data.last_page; i++) {
                pages.push(i);
            }

            $scope.range = pages;


        });

        $http.post('inbox/message_count', {
            data: data,
            type: type
        }).then(function(response) {
            $scope.message_count = response.data;

        });

        $scope.messages_result = function(pageNumber) {

            if (pageNumber === undefined) {
                pageNumber = '1';
            }

            var type = $('#inbox_filter_select').val();

            var data = $scope.user_id;


            // setGetParameter('page', pageNumber);



            $http.post('inbox/message_by_type?page=' + pageNumber, {
                    data: data,
                    type: type
                })
                .then(function(response) {


                    $scope.message_result = response.data;
                    $scope.totalPages = response.data.last_page;
                    $scope.currentPage = response.data.current_page;
                    // Pagination Range
                    var pages = [];

                    for (var i = 1; i <= response.data.last_page; i++) {
                        pages.push(i);
                    }

                    $scope.range = pages;


                });
        };


        $scope.archive = function(index, id, msg_id, type) {

            var data = $scope.user_id;
            $http.post('inbox/archive', {
                id: id,
                msg_id: msg_id,
                type: type
            }).then(function(response) {
                if (type == "Archive")
                    $scope.message_result.data[index].archive = 1;

                if (type == "Unarchive")
                    $scope.message_result.data[index].archive = 0;

                $http.post('inbox/message_count', {
                    data: data,
                    type: type
                }).then(function(response) {
                    $scope.message_count = response.data;
                    // Update inbox count Globally
                    $rootScope.inbox_count = response.data.unread_count; 
                    var type = $('#inbox_filter_select').val();
                    var data = $scope.user_id;
                    var pageNumber = $scope.currentPage
                    $http.post('inbox/message_by_type?page=' + pageNumber, {
                        data: data,
                        type: type
                    }).then(function(response) {
                        $scope.message_result = response.data;
                        $scope.type = type;
                        $scope.totalPages = response.data.last_page;
                        $scope.currentPage = response.data.current_page;
                        
                        var pages = [];

                        for (var i = 1; i <= response.data.last_page; i++) {
                            pages.push(i);
                        }

                        $scope.range = pages;
                    });
                });
            });

        };

        $scope.star = function(index, id, msg_id, type) {

            $http.post('inbox/star', {
                id: id,
                msg_id: msg_id,
                type: type
            }).then(function(response) {
                if (type == "Star")
                    $scope.message_result.data[index].star = 1;

                if (type == "Unstar")
                    $scope.message_result.data[index].star = 0;

                $http.post('inbox/message_count', {
                    data: data,
                    type: type
                }).then(function(response) {
                    $scope.message_count = response.data;
                    // Update inbox count Globally
                    $rootScope.inbox_count = response.data.unread_count; 
                    // call message result after star the message
                    $scope.messages_result($scope.currentPage);
                });
            });
        };

        $("#inbox_filter_select").change(function() {

            var type = this.value;

            var data = $scope.user_id;

            $http.post('inbox/message_by_type', {
                data: data,
                type: type
            }).then(function(response) {
                $scope.message_result = response.data;
                $scope.type = type;
                $scope.totalPages = response.data.last_page;
                $scope.currentPage = response.data.current_page;
                // Pagination Range
                var pages = [];

                for (var i = 1; i <= response.data.last_page; i++) {
                    pages.push(i);
                }

                $scope.range = pages;


                $http.post('inbox/message_count', {
                    data: data,
                    type: type
                }).then(function(response) {
                    $scope.message_count = response.data;

                });

            });
        });

    }, 10);

}]);

app.controller('conversation', ['$scope', '$http', '$rootScope', function($scope, $http, $rootScope) {

    $scope.calculation_status = '';
    $scope.range = function(min, max){
        var input = [];
        for (var i = min; i <= max; i++) input.push(i);
        return input;
    };

    //Update Messages Using WebSocket
    $(document).ready(function() {
        var socket = io.connect('http://'+CURRENT_IP_ADDR+':7000');
        
        socket.on("connect", function() {
            console.log("connected to "+CURRENT_IP_ADDR);
        });

        socket.on("disconnect", function() {
            console.log("disconnected");
        });
        
         socket.on('message_count', function (data) {
            if(data['reservation_id'] != $scope.reservation_id){
                if(data['guest_id'] == USER_ID){
                    $('.inbox-count').text('');
                    $('.inbox-count').text(data['guest_count']);
                    if(data['guest_count'] == '0'){
                        $('.inbox-count').addClass('fade');
                    }else{
                        $('.inbox-count').removeClass('fade');
                    }
                }
                if(data['host_id'] == USER_ID){
                    $('.inbox-count').text('');
                    $('.inbox-count').text(data['host_count']);
                    if(data['host_count'] == '0'){
                        $('.inbox-count').addClass('fade');
                    }else{
                        $('.inbox-count').removeClass('fade');
                    }
                }
            }else{
                setTimeout(function(){
                    $http.post(APP_URL + '/update_inbox_count', {
                        reservation_id: $scope.reservation_id,
                        user_id: USER_ID,
                    }).then(function(response) {});
                }, 60)
            }
        });

        socket.on('message_'+$scope.reservation_id, function (data) {
            if(data) {
                if(data['reservation_id'] == $scope.reservation_id) {
                    if(data['type']=='add') {
                    var created_time = moment().tz(USER_TZ).format('LT');
                    data['instant_message']['created_time'] = created_time;
                        $scope.instant_message.unshift(data['instant_message']);
                    }
                    if(data['type']=='remove') {
                        for (var e in $scope.instant_message) {
                            if ($scope.instant_message[e]['id'] === data['instant_message']) {
                                const index = $scope.instant_message.indexOf($scope.instant_message[e]);
                                if (index > -1) {
                                    $scope.instant_message.splice(index, 1);
                                }
                           }
                        }
                    }
                    if(!$scope.$$phase) {
                        $scope.$apply();
                    }
                }
            }
        });
    });

    $scope.reply_message = function(value) {

        var message = $('[data-key="' + value + '"] textarea[name="message"]').val();
        $('.message_error_box').addClass('d-none')
        /*if (!message) {
            $('[data-key="' + value + '"] textarea[name="message"]').siblings('.message_error_box').removeClass('d-none')
            return false;
        }*/
        $('[data-key="' + value + '"] textarea[name="message"]').val('');
        var template = $('[data-key="' + value + '"] input[name="template"]').val();
        var template_message = $('[data-key="' + value + '"] input[name="template"]').data('message');
        if (template == 2) {
            if ($('#pricing_start_date').val() == '' || $('#pricing_end_date').val() == '') {
                $("#availability_warning").removeClass('d-none');
                return '';
            } else if ($('#pricing_price').val() == '') {
                $("#availability_warning1").removeClass('d-none');
                return '';
            } else {
                $("#availability_warning").addClass('d-none');
                $("#availability_warning1").addClass('d-none');
            }
        }
        if(template == 9 ){
            $("li[data-tracking-section='decline']").addClass('d-none');
        }
        $http.post(APP_URL + '/messaging/qt_reply/' + $('#reservation_id').val(), {
            message: message,
            template: template,
            template_message: template_message,
            pricing_room_id: $('#pricing_room_id').val(),
            pricing_checkin: $('#pricing_start_date').val(),
            pricing_checkout: $('#pricing_end_date').val(),
            pricing_guests: $('#pricing_guests').val(),
            pricing_price: $('#pricing_price').val()
        }).then(function(response) {
            if (response.data.success != 'false') {
                if(value == 'guest_conversation') {
                    $('#thread-list').prepend(response.data);
                }
                else {
                    // var selector = '#question2_post_'+$scope.last_message_id;
                    $(response.data).insertAfter('#post_message_box');
                }

                $('[data-key="' + value + '"] textarea[name="message"]').val('');
                $('.inquiry-form-fields').addClass('d-none');
                $('[data-tracking-section="accept"] ul').addClass('d-none');
                $('[data-tracking-section="decline"] ul').addClass('d-none');
                $('[data-tracking-section="discussion"] ul').addClass('d-none');
            } else {
                $('[data-error="price"]').html(response.data.msg);
            }
        });
    }

    $(document).on('change', '#month-dropdown', function() {
        var year_month = $(this).val();
        var year = year_month.split('-')[0];
        var month = year_month.split('-')[1];
        var data_params = {};
        data_params['month'] = month;
        data_params['year'] = year;
        data_params['reservation_id'] = $('#reservation_id').val();
        data_params['room_id'] = $('#hosting').val();

        var data = JSON.stringify(data_params);

        $('#calendar-container').addClass('loading');

        $http.post(APP_URL + '/inbox/calendar', {
            data: data
        }).then(function(response) {
            $('#calendar-container').removeClass('loading');
            $("#calendar-container").html(response.data);
        });
        return false;
    });

    $(document).on('click', '.nextMonth, .previousMonth', function() {
        var month = $(this).attr('data-month');
        var year = $(this).attr('data-year');

        var data_params = {};

        data_params['month'] = month;
        data_params['year'] = year;
        data_params['reservation_id'] = $('#reservation_id').val();
        data_params['room_id'] = $('#hosting').val();

        var data = JSON.stringify(data_params);

        $('#calendar-container').addClass('loading');

        $http.post(APP_URL + '/inbox/calendar', {
            data: data
        }).then(function(response) {
            $('#calendar-container').removeClass('loading');
            $("#calendar-container").html(response.data);
        });
        return false;
    });

    $(document).on('change', '#hosting', function() {
        var data_params = {};

        var year_month = $('#month-dropdown').val();
        var year = year_month.split('-')[0];
        var month = year_month.split('-')[1];

        data_params['month'] = month;
        data_params['year'] = year;
        data_params['reservation_id'] = $('#reservation_id').val();
        data_params['room_id'] = $(this).val();

        var data = JSON.stringify(data_params);

        $('#calendar-container').addClass('loading');

        $http.post(APP_URL + '/inbox/calendar', {
            data: data
        }).then(function(response) {
            $('#calendar-container').removeClass('loading');
            $("#calendar-container").html(response.data);
        });
        list_type = $('#edit_calendar_url').attr('data-type');
        if(list_type == 'Experiences')
        {
            $('#edit_calendar_url').attr('href', APP_URL + '/host/manage_experience/' + $(this).val() + '?step_num=1');
        }
        else
        {
            $('#edit_calendar_url').attr('href', APP_URL + '/manage-listing/' + $(this).val() + '/calendar');
        }
        return false;
    });

    $('#month-dropdown').val($('#month-dropdown_value').val());
    $('#hosting').val($('#room_id').val());

    $(document).on('click', '.attach-offer', function() {
        $('.inquiry-form-fields').removeClass('d-none');
        $('[data-tracking-section="accept"] ul').removeClass('d-none');
        $('[data-tracking-section="accept"] input[name="template"][value=2]').prop('checked', true);
        $('[data-key="special_offer"] .drawer').removeClass('d-none');
        var key = $('[data-tracking-section="accept"] input[name="template"]:checked').closest().data('key');
        $('[data-key="' + key + '"] .drawer').removeClass('d-none');
    });

    $(document).on('click', '.pre-approve', function() {
        $('.inquiry-form-fields').removeClass('d-none');
        $('[data-tracking-section="accept"] ul').removeClass('d-none');
        $('[data-tracking-section="accept"] input[name="template"][value=1]').prop('checked', true);
        var key = $('[data-tracking-section="accept"] input[name="template"]:checked').closest().data('key');
        $('[data-key="' + key + '"] .drawer').removeClass('d-none');
    });

    $(document).on('click', '.option-list a', function() {
        var track = $(this).parent().data('tracking-section');
        $('[data-tracking-section] ul').addClass('d-none');
        $('[data-tracking-section="' + track + '"] ul').removeClass('d-none');
        var key = $('[data-tracking-section="' + track + '"] input[name="template"]:checked').closest().data('key');
        $('[data-key="' + key + '"] .drawer').removeClass('d-none');
    });

    $(document).on('click', 'input[name="template"]', function() {
        $('[data-key] .drawer').addClass('d-none');
        $(this).parent().parent().addClass('active');
        var key = $(this).parent().parent().data('key');
        $('[data-key="' + key + '"] .drawer').removeClass('d-none');
    });

    // Update start date and end date DatePickers
    function update_calendar(changed_price,array,can_destroy) {
        if(can_destroy) {
            $('#pricing_start_date').datepicker("destroy");
            $('#pricing_end_date').datepicker("destroy");
        }
        $('#pricing_start_date').datepicker({
                minDate: 0,
                dateFormat: datepicker_format,
                beforeShowDay: function(date) {
                    var date = jQuery.datepicker.formatDate('yy-mm-dd', date);
                    if ($.inArray(date, array) != -1)
                        return [false];
                    else
                        return [true];
                },
                onSelect: function(date,obj) {
                    var selected_month = obj.selectedMonth + 1;
                    var pricing_start_formatted_date = obj.selectedDay+'-'+selected_month+'-'+obj.selectedYear;
                    $('input[name="pricing[start_date]"]').val(pricing_start_formatted_date);
                    var checkout = $('#pricing_start_date').datepicker('getDate');
                    checkout.setDate(checkout.getDate() + 1); 
                    $('#pricing_end_date').datepicker('option', 'minDate', checkout);
                    $('#pricing_end_date').datepicker('setDate', checkout);
                    var pricing_end_date = checkout.getDate();
                    var pricing_end_month = checkout.getMonth() + 1;
                    var pricing_end_year = checkout.getFullYear();
                    var pricing_end_formatted_date = pricing_end_date+'-'+pricing_end_month+'-'+pricing_end_year;
                    $('input[name="pricing[end_date]"]').val(pricing_end_formatted_date);
                    setTimeout(function() {
                        $("#pricing_end_date").datepicker("show");
                    },20);

                    var checkin = $('input[name="pricing[start_date]"]').val();
                    var checkout = $('input[name="pricing[end_date]"]').val();
                    var guest = $("#pricing_guests").val();
                    var room_id = $('#pricing_room_id').val();
                    calculation(checkout, checkin, guest, room_id);

                    if (date != new Date()) {
                        $('.ui-datepicker-today').removeClass('ui-datepicker-today');
                    }
                }
            });

            jQuery('#pricing_end_date').datepicker({
                minDate: 1,
                dateFormat: datepicker_format,
                beforeShowDay: function(date) {
                    var prev_Date = moment(date).subtract(1, 'd');;
                    var date = jQuery.datepicker.formatDate('yy-mm-dd', prev_Date.toDate());
                    if ($.inArray(date, array) != -1)
                        return [false];
                    else
                        return [true];
                },
                onSelect: function(date,obj){
                    var selected_month = obj.selectedMonth + 1;
                    var pricing_end_formatted_date = obj.selectedDay+'-'+selected_month+'-'+obj.selectedYear;
                    $('input[name="pricing[end_date]"]').val(pricing_end_formatted_date);

                    var checkin = $('#pricing_start_date').datepicker('getDate');
                    var checkout = $('#pricing_end_date').datepicker('getDate');

                    if (checkout <= checkin && $('#pricing_start_date').val() != '') {
                        var minDate = $('#pricing_end_date').datepicker('option', 'minDate');
                        $('#pricing_end_date').datepicker('setDate', minDate);
                    }

                    var checkin = $('input[name="pricing[start_date]"]').val();
                    var checkout = $('input[name="pricing[end_date]"]').val();
                    var guest = $("#pricing_guests").val();
                    var room_id = $('#pricing_room_id').val();
                    if (checkin != '') {
                        calculation(checkout, checkin, guest, room_id);
                    }
                }
            });
            // reset to default value when accomodates less than previous selected guest
            if($('#pricing_guests').val() > $scope.accomodates )
                $('#pricing_guests option[value=1]').prop('selected', true);
    }

    setTimeout(function() {

        var data = $('#pricing_room_id').val();
        var room_id = data;

        $http.post('../../rooms/rooms_calendar', {
            data: data
        }).then(function(response) {
            update_calendar(response.data.changed_price,response.data.not_avilable,false);
            $scope.accomodates = response.data.room_accomodates;
            $('#pricing_room_id').change(function() {
                var room_id = $(this).val();
                if (room_id != '') {
                    changeroom(room_id);
                }
            });

            $('#pricing_guests').change(function() {
                var checkin = $('input[name="pricing[start_date]"]').val();
                var checkout = $('input[name="pricing[end_date]"]').val();
                var guest = $("#pricing_guests").val();
                var room_id = $('#pricing_room_id').val();
                if (checkin != '' && checkout != '') {
                    calculation(checkout, checkin, guest, room_id);
                }
                else {
                    $("#pricing_start_date").trigger("select");
                }

            });
        });
    }, 10);

    function calculation(checkout, checkin, guest, room_id) {
        $('.special-offer-date-fields').addClass('loading');
        $http.post('../../rooms/price_calculation', {
            checkin: checkin,
            checkout: checkout,
            guest_count: guest,
            room_id: room_id
        }).then(function(response) {
            $('.special-offer-date-fields').removeClass('loading');
            if (response.data.status == 'Not available') {
                if(response.data.error != '') {
                    $('#availability_warning #error').text(response.data.error);
                    $('#availability_warning #not_available').addClass('d-none');
                    $('#availability_warning #error').removeClass('d-none');
                }
                else{
                    $('#availability_warning #error').addClass('d-none');
                    $('#availability_warning #error').text('');
                    $('#availability_warning #not_available').removeClass('d-none');
                }
                $('#availability_warning').removeClass('d-none');
            } else {
                $('#availability_warning').addClass('d-none');
            }
            $('#pricing_price').val(response.data.subtotal);
            $("#availability_status").val(response.data.status);
        });
    }

    //change room details
    function changeroom(roomid) {
        var data = roomid;
        $('.special-offer-date-fields').addClass('loading');
        $http.post('../../rooms/rooms_calendar', {
            data: data
        }).then(function(response) {
            update_calendar(response.data.changed_price,response.data.not_avilable,true);
            $scope.accomodates = response.data.room_accomodates;
            $('.special-offer-date-fields').removeClass('loading');
            var checkin = $('input[name="pricing[start_date]"]').val();
            var checkout = $('input[name="pricing[end_date]"]').val();
            var guest = $("#pricing_guests").val();
            var room_id = $('#pricing_room_id').val();
            if (checkin != '' && checkout != '') {
                calculation(checkout, checkin, guest, room_id);
            }
        });
    }
}]);

$(document).on('contextmenu', 'a[data-method="post"]', function() {
    return false;
});

$(document).on('click', 'a[data-method="post"]', function() {
    $('a[data-method="post"]').attr('disabled', 'disabled');
});