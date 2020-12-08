var daterangepicker_format = $('meta[name="daterangepicker_format"]').attr('content');
var datepicker_format = $('meta[name="datepicker_format"]').attr('content');
var datedisplay_format = $('meta[name="datedisplay_format"]').attr('content');

function initialize() {
    var mapCanvas = document.getElementById('map');
    if(!mapCanvas){
        return false;
    }
    var mapOptions = {
        center: new google.maps.LatLng($('#map').attr('data-lat'), $('#map').attr('data-lng')),
        zoom: 13,
        zoomControl: true,
        scrollwheel: false,
        mapTypeControl: false,
        streetViewControl: false,
        zoomControlOptions: {
            style: google.maps.ZoomControlStyle.SMALL
        },
        panControl: false,
        scaleControl: false,
        mapTypeId: google.maps.MapTypeId.ROADMAP
    }
    var map = new google.maps.Map(mapCanvas, mapOptions);
    var geolocpoint = new google.maps.LatLng($('#map').attr('data-lat'), $('#map').attr('data-lng'));
    map.setCenter(geolocpoint );

    var citymap = {
        center: { lat: parseFloat($('#map').attr('data-lat')), lng: parseFloat($('#map').attr('data-lng')) }
    };

    // Add the circle for this city to the map.
    var cityCircle = new google.maps.Circle({
        strokeColor: '#11848E',
        strokeOpacity: 0.8,
        strokeWeight: 2,
        fillColor: '#7FDDC4',
        fillOpacity: 0.35,
        map: map,
        center: citymap['center'],
        radius: 1000
    });
}

google.maps.event.addDomListener(window, 'load', initialize);

app.controller('rooms_detail', ['$scope', '$http', '$filter', function($scope, $http, $filter) {
$scope.multiple_rows = [];
$scope.multiple_accommodates =[];
$scope.multiple_accommodates1 =[];
$scope.guest = [];
$scope.sub_room_id = [];
$scope.number_of_rooms =[];
$scope.sub_multiple_id = [];


$scope.message_search_guest = 1;
$scope.message_search_children = 0;
$scope.message_search_infant = 0;

$scope.search_guests = [];
$scope.search_childrens = [];
$scope.search_infants = [];

$scope.search_guest2 = [];
$scope.search_children2 = [];
$scope.search_infant2 = [];

$scope.message_search_guests = [];
$scope.message_search_childrens = [];
$scope.message_search_infants = [];

$scope.message_search_guests1 = [];
$scope.message_search_childrens1 = [];
$scope.message_search_infants1 = [];

$scope.search_guests[0] = 1;
$scope.search_childrens[0] = 0;
$scope.search_infants[0] = 0;

$scope.search_guest2[0] = 1;
$scope.search_children2[0] = 0;
$scope.search_infant2[0] = 0;

$scope.message_search_guests[0] = 1;
$scope.message_search_childrens[0] = 0;
$scope.message_search_infants[0] = 0;

$scope.message_search_guests1[0] = 1;
$scope.message_search_childrens1[0] = 0;
$scope.message_search_infants1[0] = 0;

$scope.search_guest = 1;
$scope.search_children = 0;
$scope.search_infant = 0;

    $(document).on('click','.book_button',function(){

        // $('#book_it').animate({
        //     scrollTop: $(".scroll_room").offset().top - 100 // Means Less header height
        // },400);
        $('.scroll_room').animate({
            scrollTop: '-=10000'
        }, 100, 'easeOutQuad');

        var checkin = $('.formatted_checkin').val();
        var checkout =  $('.formatted_checkout').val();
        var sub_room_id = $(this).attr('data-id');
        var newItemNo = $scope.multiple_rows.length;
        $scope.sub_multiple_id = [];
        
        if($scope.multiple_rows.length>0){
            $.map( $scope.multiple_rows, function( n,i ) {
                $scope.sub_multiple_id[i] = n['room_id'];
            });
        }
        
        if(jQuery.inArray( sub_room_id, $scope.sub_multiple_id) !== -1 || (parseInt($('#count_sub_room').val())<=$scope.multiple_rows.length) || (parseInt($('#available_room_count').val())<=$scope.multiple_rows.length)){
            
            return false;
        }
        else{

            var v = 0 ;
             $scope.vals = [];

             var s = jQuery.parseJSON($('#sub_room_data').val());

             $.each(s,function(key,value){
                $scope.vals[v] = key ;
                v++ 
             });

            $scope.multiple_rows.push({'id':'multiple_rows'+newItemNo,'room_id':sub_room_id,'number_of_rooms':1});
            if(!$scope.$$phase){
                $scope.$apply();
            }

            var id = newItemNo;

            $http.post(APP_URL+'/rooms_guest_count', {
                sub_room_id:sub_room_id ,
                checkin    : checkin,
                checkout    : checkout,
            }).then(function(response) {

                accommodates = response.data.accommodates;
                number_of_rooms = response.data.number_of_rooms;
                $scope.multiple_accommodates[id] = accommodates;
                $scope.multiple_accommodates1[id] = accommodates;

                $('.multiple_room_symbol').text(response.data.night);
                $('#room_types').val(response.data.room_types);
                $('#number_of_guests_'+id).find('option').remove().end();
                $('#number_of_rooms_'+id).find('option').remove().end();

                var rooms_text = $('#rooms_text_val').val();
                $('#number_of_rooms_'+id).append('<option value="" disabled="disabled">'+rooms_text+'</option>');
                for(i=1;i<=number_of_rooms;i++){

                    $('#number_of_rooms_'+id).append('<option value="'+i+'">'+i+'</option>');
                }

                if(parseInt($('#count_sub_room').val())>$scope.multiple_rows.length && parseInt($('#available_room_count').val())>$scope.multiple_rows.length){
                    $('#add_another_room').removeClass('hide');
                }
                else{
                    $('#add_another_room').addClass('hide');
                }

                // check if accomodates count less than 0 then set first room count by default
                if($scope.multiple_accommodates[newItemNo] == 0) {
                    $scope.multiple_accommodates[newItemNo] = $('.multiple_accommodates').val();
                    $scope.multiple_accommodates1[newItemNo] = $('.multiple_accommodates').val();
                }

                $scope.search_guests[newItemNo] = 1;
                $scope.search_childrens[newItemNo] = 0;
                $scope.search_infants[newItemNo] = 0;

                $scope.search_guest2[newItemNo] = 1;
                $scope.search_children2[newItemNo] = 0;
                $scope.search_infant2[newItemNo] = 0;
                if(!$scope.$$phase){
                    $scope.$apply();
                }
                if($scope.multiple_rows.length>1){
                    $('.remove_room').removeClass('hide');
                }
                $('html,body').animate({
                scrollTop: $("#book_it").offset().top},
                'slow');
                price_cal1();
                
            });

        }

    });

$scope.multiple_apply_filters = function(index){

    $scope.search_guests[index] = $scope.search_guest2[index];
    $scope.search_childrens[index] = $scope.search_children2[index];
    $scope.search_infants[index] = $scope.search_infant2[index]; 

    var checkin = $('.formatted_checkin').val();
    var checkout =  $('.formatted_checkout').val();

    if(checkin!='' && checkout!=''){
        $('#book_it').addClass('loading');
    }
    setTimeout(function(){
        price_cal1();
    },1000);
    display(index);
    
}

$scope.multiple_reset_filters = function(index){

    $scope.search_guest2[index] = $scope.search_guests[index];
    $scope.search_children2[index] = $scope.search_childrens[index];
    $scope.search_infant2[index] = $scope.search_infants[index];
    display(index);
}

function display(index){
    var key = 'guest_pop_'+index;
    var disp = $('#'+key).css('display');
    if(disp == 'block'){
        $('#'+key).css('display','none');
    }
    if(disp == 'none'){ 
     $('#'+key).css('display','block');   
    }
}

$(document).click('.no_of_guests',function(e){
    var id = e.target.id;
    var key = e.target.getAttribute('data-ref');
    var item = 'guest_button_'+key;
    if(id == item){
        display(key);
   }
});

$(".amenities_trigger1").click(function(){
var id = $(this).attr('data-id1');
$('.rooms_amenities_before_'+id).hide();
$('.rooms_amenities_after_'+id).show();

});

$(document).on('click','.guest_button',function(){
    var id = $(this).attr('data-ref');
    if($('#guest_popup_'+id).hasClass("active")){
        $('#guest_popup_'+id).removeClass('active');
    }
    else{
        $('#guest_popup_'+id).addClass('active');
    }

    var id1 = 0;
    var position = 0;
    if(id>0){
        id1= parseInt(id) + 1;
        position = 280 * parseInt(id1);
    }
    else{
        id1 = id;
        position = $("#guest_popup_"+id).prop("scrollHeight");
    }
        $('.panel-light').animate({
            scrollTop: position
        },'slow');
        });

$(document).on('change',".number_of_rooms",function(){
    var newItemNo = $(this).attr('data-id1');

    $scope.multiple_rows[newItemNo]['number_of_rooms'] = $(this).val();

    $scope.search_guests[newItemNo] = $(this).val() - 0;
    $scope.search_childrens[newItemNo] = 0;

    $scope.search_guest2[newItemNo] = $(this).val() - 0;
    $scope.search_children2[newItemNo] = 0;
    if(!$scope.$$phase){
        $scope.$apply();
    }
    var checkin = $('.formatted_checkin').val();
    var checkout =  $('.formatted_checkout').val();

    if(checkin!='' && checkout!=''){
        $('#book_it').addClass('loading');
    }
    setTimeout(function(){
        price_cal1();     
    },500);
   
});

    $(document).on('change',".sub_room",function(){
        var checkin = $('.formatted_checkin').val();
        var checkout =  $('.formatted_checkout').val();
        var sub_room_id = $(this).val();
        var id = $(this).attr('data-id1');
        $scope.multiple_rows[id]['room_id'] =sub_room_id;
        
        var accommodates = 0;
        var number_of_rooms = 0;
        
        $http.post(APP_URL+'/rooms_guest_count', {
            sub_room_id:sub_room_id ,
            checkin    : checkin,
            checkout    : checkout,
        }).then(function(response) {

            accommodates = response.data.accommodates;
            number_of_rooms = response.data.number_of_rooms;
            $scope.multiple_accommodates[id] = accommodates;
            $scope.multiple_accommodates1[id] = accommodates;

            $scope.search_guests[id] = 1;
            $scope.search_childrens[id] = 0;
            $scope.search_infants[id] = 0;

            $scope.search_guest2[id] = 1;
            $scope.search_children2[id] = 0;
            $scope.search_infant2[id] = 0;
            infants_allowed = response.data.infants_allowed;
            $('.multiple_room_symbol').text(response.data.night);
            $('#room_types').val(response.data.room_types);
            $('#number_of_guests_'+id).find('option').remove().end();
            $('#number_of_rooms_'+id).find('option').remove().end();
            if(infants_allowed=='Yes'){
                $('#infants_allowed_check_'+id).removeClass('hide');
            }
            else{
                $('#infants_allowed_check_'+id).addClass('hide');
            }

            var rooms_text = $('#rooms_text_val').val();
            $('#number_of_rooms_'+id).append('<option value="" disabled="disabled">'+rooms_text+'</option>');
            for(i=1;i<=number_of_rooms;i++){

                $('#number_of_rooms_'+id).append('<option value="'+i+'">'+i+'</option>');
            }

            
        });

        if($('#type_list').val()=='Multiple'){
            for(var i=0;i<$scope.multiple_rows.length;i++){
                $scope.guest[i] =  $("#number_of_guests_"+i).val();
                $scope.sub_room_id[i] = $("#sub_room_"+i).val();
                $scope.number_of_rooms[i] = $("#number_of_rooms_"+i).val();
            }
        }
        else{
            $scope.guest = $('#number_of_guests').val();
            $scope.sub_room_id = $('#sub_room').val();
            $scope.number_of_rooms = '';
        }

        guest = $scope.guest;
        sub_room_ids = $scope.sub_room_id;
        number_of_rooms = $scope.number_of_rooms;
        var partial_check = 'No';
         if($('#partial_check').prop('checked')){
            partial_check = 'Yes';
         }
         else{
            partial_check = 'No';
         }
         
        if(checkin != '' && checkout !='' )
        {
            // $('.js-book-it-status').addClass('loading');
            calculation1(checkout,checkin,guest,sub_room_ids,number_of_rooms,partial_check,'No','multiple_rooms');
        }

    });

    function price_cal1(){
        $scope.sub_room_id = [];
        var checkin = $('.formatted_checkin').val();
        var checkout =  $('.formatted_checkout').val();
        if($('#type_list').val()=='Multiple'){
            for(var i=0;i<$scope.multiple_rows.length;i++){
                
                $scope.guest[i] =  $("#number_of_guests_"+i).val();
                $scope.sub_room_id[i] = $("#sub_room_"+i).val();
                $scope.number_of_rooms[i] = $("#number_of_rooms_"+i).val();

                $scope.multiple_accommodates[i] = $("#number_of_rooms_"+i).val() * $scope.multiple_accommodates1[i];
            }
           
        }
        else{
            $scope.guest = $('#number_of_guests').val();
            $scope.sub_room_id = $('#sub_room').val();
            $scope.number_of_rooms = '';
        }
        var guest = $scope.guest;
        var sub_room_id = $scope.sub_room_id;
        var number_of_rooms = $scope.number_of_rooms;
        setTimeout(function(){
            $('.book_button').removeClass('hide');
        },1500);  
        var partial_check = 'No';
         if($('#partial_check').prop('checked')){
            partial_check = 'Yes';
         }
         else{
            partial_check = 'No';
         }
         
        if(checkin != '' && checkout !='' )
        {
            // $('.js-book-it-status').addClass('loading');
            calculation1(checkout,checkin,guest,sub_room_id,number_of_rooms,partial_check,'No','multiple_rooms');
        }

        if($('#type_list').val()=='Multiple'){
            if(parseInt($('#count_sub_room').val())<=$scope.multiple_rows.length || parseInt($('#available_room_count').val())<=$scope.multiple_rows.length){
                $('#add_another_room').addClass('hide');
            }
            else{
                setTimeout(function(){
                    $('#add_another_room').removeClass('hide');
                    
                },1500);  
            }

            if($scope.multiple_rows.length>1){
                $('.remove_room').removeClass('hide');
            }
            else{
                $('.remove_room').addClass('hide');   
            }
        }
    }

$(document).ready(function() {
  $('.parking_slider').owlCarousel({
      loop:true,
      nav: true,
      margin:0,
      rtl:rtl,
      items: 1,
      responsiveClass:true,
       responsive:{
        0:{
          items:1,
          nav:true
      },
      425:{
          items:1,
          nav:true
      },
      736:{
          items:1,
          nav:true
      },
      992:{
          items:1,
          nav:true
      },
      1024:{
          items:1,
          nav:true
      }
  }
});
});

$(document).on('click','#add_another_room',function(event){

    event.preventDefault();
    
    $('.book_button').addClass('hide');
    var newItemNo = $scope.multiple_rows.length;
    var v = 0 ;

     var room_val = '';
    $.each($scope.vals,function(key1,value1){
            if(!room_val){
                var id = $scope.vals.length + 1;
                  var found = $scope.multiple_rows.some(function (el) {
                    return el.room_id === $scope.vals[key1];
                  });
                  if (!found) { room_val = $scope.vals[key1]; }
            }
            if(!$scope.$$phase){
                $scope.$apply();
            }
    });

    $scope.multiple_rows.push({'id':'multiple_rows'+newItemNo,'room_id':room_val,'number_of_rooms':1});
    
    $scope.multiple_accommodates[newItemNo] = $('.multiple_accommodates').val();
    $scope.multiple_accommodates1[newItemNo] = $('.multiple_accommodates').val();
    $scope.search_guests[newItemNo] = 1;
    $scope.search_childrens[newItemNo] = 0;
    $scope.search_infants[newItemNo] = 0;

    $scope.search_guest2[newItemNo] = 1;
    $scope.search_children2[newItemNo] = 0;
    $scope.search_infant2[newItemNo] = 0;
    if(!$scope.$$phase){
        $scope.$apply();
    }
    if($scope.multiple_rows.length>1){
        $('.remove_room').removeClass('hide');
    }

    $('#add_another_room').addClass('hide');
    setTimeout(function(){
        if(parseInt($('#count_sub_room').val())>$scope.multiple_rows.length && parseInt($('#available_room_count').val())>$scope.multiple_rows.length){
            $('#add_another_room').removeClass('hide');
        }
    },1500);

    room_avai_check();
    //price_cal();
});

$scope.removeRows = function(name) {   
    $('.book_button').addClass('hide');
    var index = name;   
    var comArr = eval( $scope.multiple_rows );
    for( var i = 0; i < comArr.length; i++ ) {
      if( comArr[i].name === name ) {
        index = i;
        break;
      }
    }
    $scope.multiple_accommodates.splice(index,1);
    $scope.multiple_accommodates1.splice(index,1);
    $scope.multiple_rows.splice( index, 1 );
    
    $scope.search_guests.splice( index, 1 );
    $scope.search_childrens.splice( index, 1 );
    $scope.search_infants.splice( index, 1 ); 

    $scope.search_guest2.splice( index, 1 );
    $scope.search_children2.splice( index, 1 );
    $scope.search_infant2.splice( index, 1 );  
    $scope.number_of_rooms.splice(index,1);
    $('#add_another_room').addClass('hide');
    //if($scope.multiple_rows.length<=1){
        $('.remove_room').addClass('hide');
    //}
    setTimeout(function(){
        price_cal1();
    },500);
    
};

function room_avai_check(){
    
    var checkin = $('.formatted_checkin').val();
    var checkout = $('.formatted_checkout').val();
    if(checkin!='' && checkout!=''){
        $('#book_it').addClass('loading');
    }
         $http.post(APP_URL+'/room_available_check', {
            room_id:$('#room_id').val() ,
            checkin    : checkin,
            checkout    : checkout,
        }).then(function(response) {
            var sub_room_id1 = response.data.sub_room_id;
            $('.sub_room option').removeAttr('disabled','disabled');
            $('#available_room_count').val((parseInt($('#count_sub_room').val()) - response.data.sub_room_id.length));
            setTimeout(function(){

                if(sub_room_id1.length){
                    $.each(sub_room_id1,function(key,value) {
                        $('.sub_room option[value="'+value+'"]').attr('disabled','disabled');
                    });
                }
            },2000);

            room_count1();
        });
}

    function room_count1(){
        // alert('room_count');
        if($scope.multiple_rows.length){
            var rooms = $scope.multiple_rows;
            // console.log($scope.multiple_rows);
        
            $.each(rooms,function(key,value){
                var checkin = $('.formatted_checkin').val();
                var checkout = $('.formatted_checkout').val();
                 $http.post(APP_URL+'/rooms_guest_count', {
                    sub_room_id:value['room_id'],   //17
                    checkin    : checkin,
                    checkout    : checkout,
                }).then(function(response) {
                    id = key;
                    accommodates = response.data.accommodates;
                    number_of_rooms = response.data.number_of_rooms;
                    $scope.multiple_accommodates[id] = accommodates;
                    $scope.multiple_accommodates1[id] = accommodates;
                    infants_allowed = response.data.infants_allowed;
                    $('.multiple_room_symbol').text(response.data.night);
                    $('#room_types').val(response.data.room_types);
                    $('#number_of_guests_'+id).find('option').remove().end();
                    $('#number_of_rooms_'+id).find('option').remove().end();
                    if(infants_allowed=='Yes'){
                        $('#infants_allowed_check_'+id).removeClass('hide');
                    }
                    else{
                        $('#infants_allowed_check_'+id).addClass('hide');
                    }

                    var rooms_text = $('#rooms_text_val').val();
                    $('#number_of_rooms_'+id).append('<option value="" disabled="disabled" selected>'+rooms_text+'</option>');
                    for(i=1;i<=number_of_rooms;i++){
                        if(parseInt(value['number_of_rooms'])==parseInt(i)){
                            var selected = 'selected';
                        }
                        else{
                            var selected = '';
                        }
                        $('#number_of_rooms_'+id).append('<option value="'+i+'" '+selected+'>'+i+'</option>');
                    }

                    for(var i=0;i<$scope.multiple_rows.length;i++){
                        $scope.guest[i] =  $("#number_of_guests_"+i).val();
                        $scope.sub_room_id[i] = $("#sub_room_"+i).val();
                        $scope.number_of_rooms[i] = $("#number_of_rooms_"+i).val();
                     }
        
                     var checkin = $('.formatted_checkin').val();
                     var checkout = $('.formatted_checkout').val();
                     var guest      = $scope.guest;
                     var sub_room_id = $scope.sub_room_id;
                     var number_of_rooms = $scope.number_of_rooms;
                     var partial_check = 'No';
                     if($('#partial_check').prop('checked')){
                        partial_check = 'Yes';
                     }
                     else{
                        partial_check = 'No';
                     }
                    
                     if(checkin != '' && checkout !='')
                     {
                         // $('.js-book-it-status').addClass('loading');
                         calculation1(checkout,checkin,guest,sub_room_id,number_of_rooms,partial_check,'Yes','multiple_rooms');

                     }

                    
                });
            });
        }
    }

function calculation1(checkout,checkin,guest,sub_room_id,number_of_rooms,partial_check,check_list_popup,type) {
 // alert('calculation');
    $scope.multiple_room_price = [];
    var room_id = $scope.room_id;

    if(sub_room_id.length){
        var sub_room_id1 = [];
        var number_of_rooms1 = [];
        var guest1 = [];
        $.each(sub_room_id,function(key,value){
            if(value){
                sub_room_id1[key] = value;
                number_of_rooms1[key] = number_of_rooms[key];
                guest1[key] = guest[key];
            }
        });
    }
    else{
        var sub_room_id1 = sub_room_id;
        var number_of_rooms1 = number_of_rooms;
        var guest1 = guest;
    }

    $('.book_button').addClass('hide');
    $('#book_it').addClass('loading');
    $('#add_another_room').addClass('hide');
    // $(".js-subtotal-container").show();
    $http.post('price_calculation', { sub_room_id : sub_room_id1 ,number_of_rooms:number_of_rooms1 ,checkin :checkin,checkout : checkout, guest_count : guest1,   room_id : room_id, partial_check:partial_check, type:type }).then(function(response) 
    {
            $scope.multiple_room_price = [];
            if($scope.multiple_rows.length>1){
                $('.remove_room').removeClass('hide');
            }
            if(response.data.base_rooms_price == undefined) {
                $('#book_it').removeClass('loading');
                return false;
            }
            for(var i=0;i<response.data.base_rooms_price.length;i++){
                if(response.data.base_rooms_price[i]){
                    $scope.multiple_room_price.push({'id':'rows'+i});
                }
                setTimeout(function(){
                    if(parseInt($('#count_sub_room').val())>$scope.multiple_rows.length && parseInt($('#available_room_count').val())>$scope.multiple_rows.length){
                        $('#add_another_room').removeClass('hide');
                    }
                },1500);
                if(response.data.status[i] == "Not available")
                {
                $(".js-subtotal-container").hide();
                $("#book_it_disabled").show();
                $(".js-book-it-btn-container").hide();
                $('.book_it_disabled_msg').hide();
                if(response.data.error[i] =='') {
                    $('#book_it_disabled_message').show();
                }
                else {
                    $('#book_it_error_message').text(response.data.error[i]);   
                    $('#book_it_error_message').show();   
                }
                $('#book_it').removeClass('loading');
                $('.book_button').removeClass('hide');
                $('.js-book-it-status').removeClass('loading');
                if(check_list_popup=='Yes'){
                    $("#list_checkout").datepicker("show");
                }
                return false;
               }
               else
               {
                $(".js-subtotal-container").show();
                $("#book_it_disabled").hide();
                $(".js-book-it-btn-container").show();
               }
               if(response.data.base_rooms_price[i]){
                    $scope.multiple_room_price[i]['status'] = (response.data.status[i])?response.data.status[i]:'';
                    $scope.multiple_room_price[i]['error'] = (response.data.error[i])?response.data.error[i]:'';
                    $scope.multiple_room_price[i]['total_night_price'] = (response.data.total_night_price[i])?response.data.total_night_price[i]:'';
                    $scope.multiple_room_price[i]['service_fee'] = (response.data.service_fee[i])?response.data.service_fee[i]:'';
                    $scope.multiple_room_price[i]['total_nights'] = response.data.total_nights;
                    $scope.multiple_room_price[i]['number_of_rooms'] = response.data.number_of_rooms[i];
                    $scope.multiple_room_price[i]['rooms_price'] = (response.data.rooms_price[i])?response.data.rooms_price[i]:'';
                    $scope.multiple_room_price[i]['per_night'] = (response.data.per_night[i])?response.data.per_night[i]:'';
                    $scope.multiple_room_price[i]['base_rooms_price'] = (response.data.base_rooms_price[i])?response.data.base_rooms_price[i]:'';
                    
                    $scope.multiple_room_price[i]['length_of_stay_type'] = '';
                    $scope.multiple_room_price[i]['length_of_stay_discount'] = '';
                    $scope.multiple_room_price[i]['length_of_stay_discount_price'] = '';
                    $scope.multiple_room_price[i]['booked_period_type'] = '';
                    $scope.multiple_room_price[i]['booked_period_discount'] = '';
                    $scope.multiple_room_price[i]['booked_period_discount_price'] = '';
                    $scope.multiple_room_price[i]['additional_guest'] = '';
                    $scope.multiple_room_price[i]['base_additional_guest'] = '';
                    $scope.multiple_room_price[i]['security_fee'] = '';
                    $scope.multiple_room_price[i]['cleaning_fee'] = '';
                    $scope.multiple_room_price[i]['base_cleaning_fee'] = '';
                    if(response.data.length_of_stay_type[i]){
                        if(response.data.length_of_stay_type[i] == 'weekly') {
                            $scope.multiple_room_price[i]['length_of_stay_type'] = (response.data.length_of_stay_type[i])?response.data.length_of_stay_type[i]:'';
                            $scope.multiple_room_price[i]['length_of_stay_discount'] = (response.data.length_of_stay_discount[i])?response.data.length_of_stay_discount[i]:'';
                            $scope.multiple_room_price[i]['length_of_stay_discount_price'] = (response.data.length_of_stay_discount_price[i])?response.data.length_of_stay_discount_price[i]:'';
                        }
                        else if(response.data.length_of_stay_type[i] == 'monthly'){
                            $scope.multiple_room_price[i]['length_of_stay_type'] = (response.data.length_of_stay_type[i])?response.data.length_of_stay_type[i]:'';
                            $scope.multiple_room_price[i]['length_of_stay_discount'] = (response.data.length_of_stay_discount[i])?response.data.length_of_stay_discount[i]:'';
                            $scope.multiple_room_price[i]['length_of_stay_discount_price'] = (response.data.length_of_stay_discount_price[i])?response.data.length_of_stay_discount_price[i]:'';
                        }
                        else if(response.data.length_of_stay_type[i] == 'custom'){
                            $scope.multiple_room_price[i]['length_of_stay_type'] = (response.data.length_of_stay_type[i])?response.data.length_of_stay_type[i]:'';
                            $scope.multiple_room_price[i]['length_of_stay_discount'] = (response.data.length_of_stay_discount[i])?response.data.length_of_stay_discount[i]:'';
                            $scope.multiple_room_price[i]['length_of_stay_discount_price'] = (response.data.length_of_stay_discount_price[i])?response.data.length_of_stay_discount_price[i]:'';
                        }
                    }

                    if(response.data.booked_period_type[i]){
                        $scope.multiple_room_price[i]['booked_period_type'] = (response.data.booked_period_type[i])?response.data.booked_period_type[i]:'';
                        $scope.multiple_room_price[i]['booked_period_discount'] = (response.data.booked_period_discount[i])?response.data.booked_period_discount[i]:'';
                        $scope.multiple_room_price[i]['booked_period_discount_price'] = (response.data.booked_period_discount_price[i])?response.data.booked_period_discount_price[i]:'';
                    }

                    if(response.data.additional_guest.length>0){
                        $scope.multiple_room_price[i]['additional_guest'] = (response.data.additional_guest[i])?response.data.additional_guest[i]:'';
                        $scope.multiple_room_price[i]['base_additional_guest'] = (response.data.base_additional_guest[i])?response.data.base_additional_guest[i]:'';
                    }

                    if(response.data.security_fee.length>0){
                        $scope.multiple_room_price[i]['security_fee'] = (response.data.security_fee[i])?response.data.security_fee[i]:'';
                    }

                    if(response.data.cleaning_fee.length>0){
                        $scope.multiple_room_price[i]['cleaning_fee'] = (response.data.cleaning_fee[i])?response.data.cleaning_fee[i]:'';
                        $scope.multiple_room_price[i]['base_cleaning_fee'] = (response.data.base_cleaning_fee[i])?response.data.base_cleaning_fee[i]:'';;
                    }
                }
               // $('#total_night_price').text(response.data.total_night_price);
               // $('#service_fee').text(response.data.service_fee);
               // $('#total_night_count').text(response.data.total_nights);
               // $('#rooms_price_amount').text(response.data.rooms_price);
               // $('#rooms_price_amount_1').text(response.data.base_rooms_price);
               
               // if(response.data.length_of_stay_type == 'weekly') {
               //  $(".weekly").show();
               //  $("#weekly_discount").text(response.data.length_of_stay_discount);
               //  $("#weekly_discount_price").text(response.data.length_of_stay_discount_price);
               // }
               // else {
               //  $(".weekly").hide();
               // }
               // if(response.data.length_of_stay_type == 'monthly') {
               //  $(".monthly").show();
               //  $("#monthly_discount").text(response.data.length_of_stay_discount);
               //  $("#monthly_discount_price").text(response.data.length_of_stay_discount_price);
               // }
               // else {
               //  $(".monthly").hide();
               // }
               // if(response.data.length_of_stay_type == 'custom') {
               //  $(".long_term").show();
               //  $("#long_term_discount").text(response.data.length_of_stay_discount);
               //  $("#long_term_discount_price").text(response.data.length_of_stay_discount_price);
               // }
               // else {
               //  $(".long_term").hide();
               // }
               // if(response.data.booked_period_type != '') {
               //  $(".booking_period").hide();
               //  $("."+response.data.booked_period_type).show();
               //  $(".booked_period_discount").text(response.data.booked_period_discount);
               //  $(".booked_period_discount_price").text(response.data.booked_period_discount_price);
               // }
               // else {
               //  $(".booking_period").hide();
               // }
               
               // if(response.data.additional_guest)
               // {
               //  $(".additional_price").show();
               //  $('#additional_guest').text(response.data.additional_guest);
               // }
               // else
               // {
               //  $(".additional_price").hide();
               // }
               

               //  if(response.data.security_fee)
               // {
               //  $(".security_price").show();
               //  $('#security_fee').text(response.data.security_fee);
               // }
               // else
               // {
               //  $(".security_price").hide();
               // }
               //        if(response.data.cleaning_fee)
               // {
               //  $(".cleaning_price").show();
               //  $('#cleaning_fee').text(response.data.cleaning_fee);
               // }
               // else
               // {
               //  $(".cleaning_price").hide();
               // }
            }
            
           $('#total').text(response.data.total);
           $('#multiple_rooms_price_amount').text(response.data.total);
           if(response.data.partial_amount_check=='Yes'){

            $('.partial_amount_check').removeClass('hide');
            $('#partial_amount').text(response.data.partial_amount);
            $('#partial_percentage').text(response.data.partial_percentage);
            $('#remaining_amount').text(response.data.total - response.data.partial_amount);
           }
           else{
            $('.partial_amount_check').addClass('hide');
           }
           $('.multiple_per_night').addClass('hide');
           $('#book_it').removeClass('loading');
           $('.js-book-it-status').removeClass('loading');
           $('.book_button').removeClass('hide');
           if(check_list_popup=='Yes'){
                $("#list_checkout").datepicker("show");
           }
        
     }); 
}


    //restrict tab key when image popup shown
    $(document).on('keydown', function(e) {
        var target = e.target;
        var shiftPressed = e.shiftKey;
        if (e.keyCode == 9) {
            if ($(target).parents('.lg-on').length) {                            
                return false;
            }
        }
        return true;
    });

    // Room Slider
    $scope.detail_slider = function() {
        $('#detail-gallery').lightSlider({
            gallery: false,
            item:1,
            loop: true,
            pager: false,
            thumbItem:9,
            slideMargin:0,
            enableDrag: false,
            enableTouch:false,
            currentPagerPosition:'left',
            onSliderLoad: function(el) {
                el.lightGallery({
                    selector: '#detail-gallery .lslide',
                    subHtmlSelectorRelative:true,
                    mode: 'lg-fade',
                    closable:true,
                    autoWidth:true,
                    mousewheel:false,
                    enableDrag:true,
                    enableSwipe:true,
                    loop: true,
                    hideControlOnEnd:true,
                    slideEndAnimatoin:false,
                    thumbItem: 5,
                    thumbnail:true,
                    animateThumb: true,
                });
            }
        });
    };

    $(document).ready(function() {
        $('.bx-prev').addClass('icon icon-chevron-left icon-gray icon-size-2 ');
        $('.bx-prev').text('');
        $('.bx-next').addClass('icon icon-chevron-right icon-gray icon-size-2 ');
        $('.bx-next').text('');
        $scope.detail_slider();
    });

    $('.open-gallery').click(function() {
        $('#detail-gallery .lslide').trigger('click');
        $scope.detail_slider();
    });

    $('.rooms_amenities_after').hide();

    $(".amenities_trigger").click(function(){
        $('.rooms_amenities_before').hide();
        $('.rooms_amenities_after').show();
    });

    //-------------- date picker block ---------------- //
    setTimeout(function() {
        var data = $scope.room_id;

        $http.post('rooms_calendar', { data:data }).then(function(response) {

            $('#room_blocked_dates').val(response.data.not_avilable);

            $('#calendar_available_price').val(response.data.changed_price);

            $('#room_available_price').val(response.data.price);

            $('#weekend_price_tooltip').val(response.data.weekend);

            var array =  $('#room_blocked_dates').val();

            var price = $('#room_available_price').val();

            var weekend = ($('#weekend_price_tooltip').val()!=0) ? $('#weekend_price_tooltip').val() : $('#room_available_price').val();

            var change_price = $('#calendar_available_price').val();

            var changed_price = response.data.changed_price;
            var tooltip_price = price;
            var currency_symbol = response.data.currency_symbol;

            $('#list_checkin').datepicker({
                minDate: 0,
                dateFormat: datepicker_format,
                beforeShow: function(input, inst) {
                    setTimeout(function() {
                        inst.dpDiv.find('a.ui-state-highlight').removeClass('ui-state-highlight');
                        $('.ui-state-disabled').removeAttr('title');
                        $('.highlight').not('.ui-state-disabled').tooltip({container:'body'});
                    }, 100);
                },
                beforeShowDay: function(date){
                    var string = jQuery.datepicker.formatDate('yy-mm-dd', date);
                    var dayname =jQuery.datepicker.formatDate('DD', date);
                    var now = new Date();
                    now.setDate(now.getDate()-1);

                    if(array.indexOf(string) == -1) {
                        if(typeof changed_price[string] == 'undefined') {
                            //Determine if a date is a Saturday or a Friday and assign values
                            if(dayname =='Friday' || dayname =='Saturday') {
                                changed_price[string] = weekend;
                            }
                            else {
                                changed_price[string] = price;
                            }
                            //end
                            return [array.indexOf(string) == -1, 'highlight', currency_symbol+changed_price[string]];
                        }
                        else if(date > now) {
                            return [ array.indexOf(string) == -1, 'highlight', currency_symbol+changed_price[string] ];
                        }
                        else {
                            return [ array.indexOf(string) == -1 ];    
                        }
                    }
                    else {
                        return [ array.indexOf(string) == -1 ];
                    }
                },
                onSelect: function (date,obj) 
                {
                    var selected_month = obj.selectedMonth + 1;
                    var checkin_formatted_date = obj.selectedDay+'-'+selected_month+'-'+obj.selectedYear;
                    $('.formatted_checkin').val(checkin_formatted_date);
                    var checkout = $('#list_checkin').datepicker('getDate'); 
                    checkout.setDate(checkout.getDate() + 1); 
                    $('#list_checkout').datepicker('option', 'minDate',checkout );
                    $('#list_checkout').datepicker('setDate', checkout);
                    var checkout_date = checkout.getDate();
                    var checkout_month = checkout.getMonth() + 1;
                    var checkout_year = checkout.getFullYear();
                    var checkout_formatted_date = checkout_date+'-'+checkout_month+'-'+checkout_year;
                    $('.formatted_checkout').val(checkout_formatted_date);
                    setTimeout(function(){
                        $("#list_checkout").datepicker("show");
                    },20);

                    var checkin = $('.formatted_checkin').val();
                    var checkout = $('.formatted_checkout').val();
                    var guest =  $("#number_of_guests").val();
                    if(checkin != '' && checkout !='')
                    {
                    if($('#type_list').val()=="Multiple"){
                        // alert($('#type_list').val());
                        /*Check Rooms Availability Start*/
                        $('#book_it').addClass('loading');
                         $http.post(APP_URL+'/room_available_check', {
                            room_id:$('#main_room_id').val() ,
                            checkin    : checkin,
                            checkout    : checkout,
                            room_id     : $('#room_id').val(),
                        }).then(function(response) {
                            // alert('test');
                            var response_room = [];
                            $scope.multiple_rooms_data = response.data.available_rooms;
                            
                            if(!$scope.$$phase){
                                $scope.$apply();
                            }
                            if(response.data.sub_room_id){
                                response_room = response.data.sub_room_id;
                                $.each(response_room,function(key,value){
                                    $('#multiple_room_detail_data_'+value).addClass('hide');
                                    $('#book_button_'+value).css('display','none');
                                    $('#book_date_'+value).css('display','block');
                                });
                            }

                            $('#available_room_count').val((parseInt($('#count_sub_room').val()) - response_room.length));
                            $('.sub_room option').removeAttr('disabled','disabled');
                            var acc = $('.multiple_accommodates').val();
                            
                            $scope.val         = '';
                            var s = '';
                            if($('#sub_room_data').val()) {
                                s = jQuery.parseJSON( $('#sub_room_data').val());
                            }
                             
                             var v = 0 ;
                             $scope.vals = [];
                             if(s){
                                 $.each(s,function(key,value){
                                        
                                        $scope.vals[v] = (jQuery.inArray(parseInt(key), response_room) != -1)?'':key;
                                        if(jQuery.inArray(parseInt(key), response_room) == -1){
                                            v++;
                                        }
                                    

                                 });
                            }


                             if(!$scope.multiple_rows.length){
                                $scope.multiple_accommodates[0] = acc;
                                $scope.multiple_accommodates1[0] = acc;

                                 $scope.multiple_rows.push({'id':'multiple_rows'+0,'room_id':$scope.vals[0],'number_of_rooms':1});
                                 if(!$scope.$$phase){
                                    $scope.$apply();
                                }
                                $('.scroll_room').animate({
                                    scrollTop: '-=10000'
                                }, 500, 'easeOutQuad');

                             }
                             else{
                                if(!$scope.multiple_rows[0]['room_id']){
                                    $scope.multiple_rows[0]['room_id'] = $scope.vals[0];
                                }
                             }

                             var sub_rooms_id = response.data.sub_room_id;

                            if(parseInt($('#count_sub_room').val())>$scope.multiple_rows.length && parseInt($('#available_room_count').val())>$scope.multiple_rows.length){
                                $('#add_another_room').removeClass('hide');
                            }

                            setTimeout(function() {
                                if(sub_rooms_id){
                                    $.each(sub_rooms_id,function(key,value){
                                        $('.sub_room option[value="'+value+'"]').attr('disabled','disabled');
                                    });
                                }
                            },2000);

                            room_count1();
                            $('#book_it').removeClass('loading');
                        }); 
                        /*Check Rooms Availability End*/                       
                    }
                    else{                        
                        $('.js-book-it-status').addClass('loading');
                        calculation(checkout,checkin,guest);
                    }
                    }
                    $('.tooltip').hide();

                    if(date != new Date())
                    {
                        $('.ui-datepicker-today').removeClass('ui-datepicker-today');
                    }
                },
                onChangeMonthYear: function(){
                    setTimeout(function(){
                        $('.highlight').not('.ui-state-disabled').tooltip({container:'body'});
                    },100);  
                }
            });

            $('html body').on('mouseenter', '.ui-datepicker-calendar a.ui-state-hover, .ui-datepicker-calendar a.ui-state-default', function(e){ //console.log(e); 
                $('.highlight').not('.ui-state-disabled').tooltip({container:'body'});
            });

            $('#list_checkout').datepicker({
                minDate: 1,
                dateFormat: datepicker_format,
                beforeShow: function(input, inst) {
                    setTimeout(function() {
                        $('.ui-state-disabled').removeAttr('title');
                        $('.highlight').not('.ui-state-disabled').tooltip({container:'body'});
                    }, 100);
                },
                beforeShowDay: function(date) {
                    var prev_Date = moment(date).subtract(1, 'd');;
                    var string = jQuery.datepicker.formatDate('yy-mm-dd', prev_Date.toDate());
                    var dayname =jQuery.datepicker.formatDate('DD', date);
                    
                    //Determine if a date is a Saturday or a Sunday and assign values
                    if(array.indexOf(string) == -1) {
                        if(typeof changed_price[string] == 'undefined')
                        {
                            if(dayname =='Friday' || dayname =='Saturday')
                            {
                                changed_price[string] = weekend;
                            }
                            else
                            {
                                changed_price[string] = price;
                            }        
                        }
                        return [ array.indexOf(string) == -1, 'highlight', currency_symbol+changed_price[string] ];
                    }
                    else {
                        return [ array.indexOf(string) == -1 ];
                    }
                },
                onSelect: function(date,obj)
                {
                    $('.tooltip').hide();
                    var selected_month = obj.selectedMonth + 1;
                    var checkout_formatted_date = obj.selectedDay+'-'+selected_month+'-'+obj.selectedYear;
                    $('.formatted_checkout').val(checkout_formatted_date);
                    var checkin = $('.formatted_checkin').val();
                    var checkout = $('.formatted_checkout').val();
                    var guest =  $("#number_of_guests").val();

                    if(checkin != '' && checkout !='')
                    {
                        if($('#type_list').val()=="Multiple")
                        {
                            $('.book_button').css('display','block');
                            $('.book_date').css('display','none');
                            $('.multiple_room_detail_data').removeClass('hide');
                            $('#book_it').addClass('loading');
                            var checkin = $('.formatted_checkin').val();
                            var checkout = $('.formatted_checkout').val();
                            if(!checkin){
                                checkin = moment();
                                checkin_date = checkin.format('YYYY-MM-DD');
                                
                                while(!(array.indexOf(checkin_date) == -1))
                                {
                                    checkin = checkin.add('1', 'days');
                                    checkin_date = checkin.format('YYYY-MM-DD');
                                }
                                checkout = checkin.clone().add('1', 'days');

                                $('#list_checkin').datepicker('setDate', checkin.toDate());
                                $('#list_checkout').datepicker('option', 'minDate',checkout.toDate());
                                setTimeout(function(){
                                    $("#list_checkin").datepicker("show");
                                },20);

                                var checkin = $('.formatted_checkin').val();
                                var checkout = $('.formatted_checkout').val();
                            }
                            
                            if(checkin && checkout){
                                 $http.post(APP_URL+'/room_available_check', {
                                    room_id:$('#main_room_id').val() ,
                                    checkin    : checkin,
                                    checkout    : checkout,
                                    room_id     : $('#room_id').val(),
                                }).then(function(response) {
                                    var response_room = [];
                                    $scope.multiple_rooms_data = response.data.available_rooms;
                                    if(!$scope.$$phase){
                                        $scope.$apply();
                                    }
                                    if(response.data.sub_room_id){
                                        response_room = response.data.sub_room_id;
                                        
                                        $.each(response_room,function(key,value){
                                            $('#multiple_room_detail_data_'+value).addClass('hide');
                                            $('#book_button_'+value).css('display','none');
                                            $('#book_date_'+value).css('display','block');
                                        });
                                    }

                                    $('.sub_room option').removeAttr('disabled','disabled');
                                    $('#available_room_count').val((parseInt($('#count_sub_room').val()) - response_room.length));
                                    var sub_room_id1 = response.data.sub_room_id;
                                    setTimeout(function(){
                                        if(sub_room_id1.length){
                                            $.each(sub_room_id1,function(key,value){
                                                $('.sub_room option[value="'+value+'"]').attr('disabled','disabled');
                                            });
                                        }
                                    },2000);

                                    if(checkin != '' && checkout !='') {

                                    }
                                    else
                                    {
                                        checkin = moment();
                                        checkin_date = checkin.format('YYYY-MM-DD');
                                        while(!(array.indexOf(checkin_date) == -1)) {
                                            checkin = checkin.add('1', 'days');
                                            checkin_date = checkin.format('YYYY-MM-DD');
                                        }
                                        checkout = checkin.clone().add('1', 'days');

                                        $('#list_checkin').datepicker('setDate', checkin.toDate());
                                        $('#list_checkout').datepicker('option', 'minDate',checkout.toDate());
                                        setTimeout(function(){
                                            $("#list_checkin").datepicker("show");
                                        },20);
                                        return false;
                                    }
                                    room_count1();
                                });
                            }
                            else{
                                $('#book_it').removeClass('loading');
                            }
                        }                        
                        else{
                            $('.js-book-it-status').addClass('loading');
                            calculation(checkout,checkin,guest);  
                        }

                    }
                    else
                    {
                        checkin = moment();
                        checkin_date = checkin.format('YYYY-MM-DD');

                        while(!(array.indexOf(checkin_date) == -1))
                        {
                            checkin = checkin.add('1', 'days');
                            checkin_date = checkin.format('YYYY-MM-DD');
                        }
                        checkout = checkin.clone().add('1', 'days');

                        $('#list_checkin').datepicker('setDate', checkin.toDate());
                        $('#list_checkout').datepicker('option', 'minDate',checkout.toDate());
                        setTimeout(function(){
                            $("#list_checkin").datepicker("show");
                        },20);
                        return false;   
                    }
                },
                onChangeMonthYear: function(){
                    setTimeout(function(){
                        $('.highlight').not('.ui-state-disabled').tooltip({container:'body'});
                    },100);  
                }
            });

            if($('#url_checkin').val() != '' && $('#url_checkout').val() != '') {

                $("#list_checkin").datepicker('setDate', new Date($('#url_checkin').val()));
                $("#list_checkout").datepicker('setDate', new Date($('#url_checkout').val()));
                $('#number_of_guests').val($('#url_guests').val());

                setTimeout(function(){                
                    $("#message_checkin").datepicker('setDate', new Date($('#url_checkin').val()));
                    $("#message_checkout").datepicker('setDate', new Date($('#url_checkout').val()));
                    $('#message_guests').val($('#url_guests').val());
                },100); 

                var checkin = $('.formatted_checkin').val();
                var checkout = $('.formatted_checkout').val();
                var guest = $('#number_of_guests').val();
                $('.js-book-it-status').addClass('loading');
                if($("#type_list").val() == 'Multiple'){
                    // calculation1(checkout,checkin,guest);
                         $http.post(APP_URL+'/room_available_check', {
                            room_id:$('#main_room_id').val() ,
                            checkin    : checkin,
                            checkout    : checkout,
                            room_id     : $('#room_id').val(),
                        }).then(function(response) {
                            // alert('test');
                            var response_room = [];
                            $scope.multiple_rooms_data = response.data.available_rooms;
                            
                            if(!$scope.$$phase){
                                $scope.$apply();
                            }
                            if(response.data.sub_room_id){
                                response_room = response.data.sub_room_id;
                                $.each(response_room,function(key,value){
                                    $('#multiple_room_detail_data_'+value).addClass('hide');
                                    $('#book_button_'+value).css('display','none');
                                    $('#book_date_'+value).css('display','block');
                                });
                            }

                            $('#available_room_count').val((parseInt($('#count_sub_room').val()) - response_room.length));
                            $('.sub_room option').removeAttr('disabled','disabled');
                            var acc = $('.multiple_accommodates').val();
                            
                            $scope.val         = '';
                            var s = '';
                            if($('#sub_room_data').val()) {
                                s = jQuery.parseJSON( $('#sub_room_data').val());
                            }
                             
                             var v = 0 ;
                             $scope.vals = [];
                             if(s){
                                 $.each(s,function(key,value){
                                        
                                        $scope.vals[v] = (jQuery.inArray(parseInt(key), response_room) != -1)?'':key;
                                        if(jQuery.inArray(parseInt(key), response_room) == -1){
                                            v++;
                                        }
                                    

                                 });
                            }


                             if(!$scope.multiple_rows.length){
                                $scope.multiple_accommodates[0] = acc;
                                $scope.multiple_accommodates1[0] = acc;

                                 $scope.multiple_rows.push({'id':'multiple_rows'+0,'room_id':$scope.vals[0],'number_of_rooms':1});
                                 if(!$scope.$$phase){
                                    $scope.$apply();
                                }
                                $('.scroll_room').animate({
                                    scrollTop: '-=10000'
                                }, 500, 'easeOutQuad');

                             }
                             else{
                                if(!$scope.multiple_rows[0]['room_id']){
                                    $scope.multiple_rows[0]['room_id'] = $scope.vals[0];
                                }
                             }

                             var sub_rooms_id = response.data.sub_room_id;

                            if(parseInt($('#count_sub_room').val())>$scope.multiple_rows.length && parseInt($('#available_room_count').val())>$scope.multiple_rows.length){
                                $('#add_another_room').removeClass('hide');
                            }

                            setTimeout(function() {
                                if(sub_rooms_id){
                                    $.each(sub_rooms_id,function(key,value){
                                        $('.sub_room option[value="'+value+'"]').attr('disabled','disabled');
                                    });
                                }
                            },2000);

                            room_count1();
                            $('#book_it').removeClass('loading');
                        });                     
                }
                else
                    calculation(checkout,checkin,guest);
            }
            else {
                if($('#url_guests').val() != '' ) {
                    $('#number_of_guests').val($('#url_guests').val());
                }
            }
        });
    }, 10);

    //---- date picker block---- //
    $("#number_of_guests").change(function() {
        var guest = $(this).val();
        var checkin = $('.formatted_checkin').val();
        var checkout =  $('.formatted_checkout').val();

        $("#guest_error").hide();
        if(checkin != '' && checkout !='' )
        {
            $('.js-book-it-status').addClass('loading');
            calculation(checkout,checkin,guest);
        }
    });

    //---- Rooms calculation---- //
    $(".additional_price").hide();
    $(".security_price").hide();
    $(".cleaning_price").hide();
    $(".js-subtotal-container").hide();
    $("#book_it_disabled").hide();

    function calculation(checkout,checkin,guest) {

        var room_id = $scope.room_id;
        $http.post('price_calculation', { checkin :checkin,checkout : checkout, guest_count : guest,   room_id : room_id }).then(function(response) {
            if(response.data.status == "Not available") {
                $(".js-subtotal-container").hide();
                $("#book_it_disabled").show();
                $(".js-book-it-btn-container").hide();
                $('.book_it_disabled_msg').hide();
                if(response.data.error =='') {
                    $('#book_it_disabled_message').show();
                }
                else {
                    $('#book_it_error_message').text(response.data.error);   
                    $('#book_it_error_message').show();   
                }
            }
            else {
                $(".js-subtotal-container").show();
                $("#book_it_disabled").hide();
                $(".js-book-it-btn-container").show();
            }

            $('.js-book-it-status').removeClass('loading');
            $('#total_night_price').text(response.data.total_night_price);
            $('#service_fee').text(response.data.service_fee);
            $('#total').text(response.data.total);
            $('#total_night_count').text(response.data.total_nights);
            $('#rooms_price_amount').text(response.data.rooms_price);
            $('#rooms_price_amount_1').text(response.data.base_rooms_price);

            if(response.data.length_of_stay_type == 'weekly') {
                $(".weekly").show();
                $("#weekly_discount").text(response.data.length_of_stay_discount);
                $("#weekly_discount_price").text(response.data.length_of_stay_discount_price);
            }
            else {
                $(".weekly").hide();
            }
            if(response.data.length_of_stay_type == 'monthly') {
                $(".monthly").show();
                $("#monthly_discount").text(response.data.length_of_stay_discount);
                $("#monthly_discount_price").text(response.data.length_of_stay_discount_price);
            }
            else {
                $(".monthly").hide();
            }

            if(response.data.length_of_stay_type == 'custom') {
                $(".long_term").show();
                $("#long_term_discount").text(response.data.length_of_stay_discount);
                $("#long_term_discount_price").text(response.data.length_of_stay_discount_price);
            }
            else {
                $(".long_term").hide();
            }

            if(response.data.booked_period_type != '') {
                $(".booking_period").hide();
                $("."+response.data.booked_period_type).show();
                $(".booked_period_discount").text(response.data.booked_period_discount);
                $(".booked_period_discount_price").text(response.data.booked_period_discount_price);
            }
            else {
                $(".booking_period").hide();
            }

            if(response.data.additional_guest)
            {
                $(".additional_price").show();
                $('#additional_guest').text(response.data.additional_guest);
            }
            else {
                $(".additional_price").hide();
            }

            if(response.data.security_fee) {
                $(".security_price").show();
                $('#security_fee').text(response.data.security_fee);
            }
            else {
                $(".security_price").hide();
            }
            if(response.data.cleaning_fee) {
                $(".cleaning_price").show();
                $('#cleaning_fee').text(response.data.cleaning_fee);
            }
            else {
                $(".cleaning_price").hide();
            }
        }); 
    }

    $('#contact-host-link, #host-profile-contact-btn').click(function() {
        $('.contact-modal').removeClass('d-none');
    });

    setTimeout(function() {

        var data = $scope.room_id;
        var room_id = data;

        $http.post(APP_URL+'/rooms/rooms_calendar', { data:data }).then(function(response) {
            var changed_price = response.data.changed_price;
            var array =  response.data.not_avilable;

            $('#message_checkin').datepicker({
                minDate: 0,
                dateFormat:datepicker_format,
                setDate: new Date($('#message_checkin').val()),
                beforeShowDay: function(date) {
                    var date = jQuery.datepicker.formatDate('yy-mm-dd', date);
                    if($.inArray(date, array) != -1)
                        return [false];
                    else
                        return [true];
                },
                onSelect: function (date,obj) {
                    var selected_month = obj.selectedMonth + 1;
                    var msg_checkout_formatted_date = obj.selectedDay+'-'+selected_month+'-'+obj.selectedYear;
                    $('input[name="message_checkin"]').val(msg_checkout_formatted_date);
                    var checkout = $('#message_checkin').datepicker('getDate');
                    checkout.setDate(checkout.getDate() + 1); 
                    $('#message_checkout').datepicker('option', 'minDate', checkout);
                    $('#message_checkout').datepicker('setDate', checkout);
                    var msg_checkout_date = checkout.getDate();
                    var msg_checkout_month = checkout.getMonth() + 1;
                    var msg_checkout_year = checkout.getFullYear();
                    var msg_checkout_formatted_date = msg_checkout_date+'-'+msg_checkout_month+'-'+msg_checkout_year;
                    $('input[name="message_checkout"]').val(msg_checkout_formatted_date);
                    setTimeout(function() {
                        $("#message_checkout").datepicker("show");
                    },20);

                    var checkin = $('input[name="message_checkin"]').val();
                    var checkout = $('input[name="message_checkout"]').val();
                    var guest =  $("#message_guests").val();
                    calculation_message(checkout,checkin,guest,room_id);

                    if(date != new Date()) {
                        $('.ui-datepicker-today').removeClass('ui-datepicker-today');
                    }
                }
            });

            $('#message_checkout').datepicker({
                minDate: 1,
                dateFormat:datepicker_format,
                setDate: new Date($('#message_checkout').val()),
                beforeShowDay: function(date) {
                    var prev_Date = moment(date).subtract(1, 'd');;
                    var date = jQuery.datepicker.formatDate('yy-mm-dd', prev_Date.toDate());
                    if($.inArray(date, array) != -1)
                        return [false];
                    else
                        return [true];
                },
                onSelect: function(date,obj) {

                    var selected_month = obj.selectedMonth + 1;
                    var msg_checkout_formatted_date = obj.selectedDay+'-'+selected_month+'-'+obj.selectedYear;
                    $('input[name="message_checkout"]').val(msg_checkout_formatted_date);


                    var checkout = $('input[name="message_checkout"]').val();
                    var checkin  = $('input[name="message_checkin"]').val();
                    var guest    = $("#message_guests").val();

                    if(checkin != '') {
                        calculation_message(checkout,checkin,guest,room_id);  
                    }
                    else {
                        $('#message_checkin').datepicker('setDate',  new Date());
                        setTimeout(function(){
                            $("#message_checkin").datepicker("show");
                        },20);
                    }
                }
            });
        });
    }, 10);

    function calculation_message(checkout,checkin,guest,room_id) {
        $http.post(APP_URL+'/rooms/price_calculation', { checkin :checkin,checkout : checkout, guest_count : guest, room_id : room_id }).then(function(response) {
            if(response.data.status == 'Not available') {
                if(response.data.error != '') {
                    $('.contacted-before #error').text(response.data.error);
                    $('.contacted-before #not_available').addClass('d-none');
                    $('.contacted-before #error').removeClass('d-none');
                }
                else {
                    $('.contacted-before #error').addClass('d-none');
                    $('.contacted-before #error').text('');
                    $('.contacted-before #not_available').removeClass('d-none');
                }
                $('.contacted-before').removeClass('d-none');
                $('.contacted-before').removeClass('error-block');
            }
            else {
                $('.contacted-before').addClass('d-none');
                $('.contacted-before').addClass('error-block');
            }
        });
    }

    $(document).on('click', '.rich-toggle-unchecked,.rich-toggle-checked', function() {
        if(typeof USER_ID == 'object') {
            window.location.href = APP_URL+'/login';
            return false;
        }
        $('.add-wishlist').addClass('loading');
        $http.get(APP_URL+"/wishlist_list?id="+$scope.room_id+'&type=Rooms', {  }).then(function(response) {
            $('.add-wishlist').removeClass('loading');
            $('.wl-modal__col:nth-child(2)').removeClass('d-none');
            $scope.wishlist_list = response.data;
        });
    });

    $scope.wishlist_row_select = function(index) {
        $http.post(APP_URL+"/save_wishlist", { data: $scope.room_id, wishlist_id: $scope.wishlist_list[index].id, saved_id: $scope.wishlist_list[index].saved_id }).then(function(response) {
            if(response.data == 'null')
                $scope.wishlist_list[index].saved_id = null;
            else
                $scope.wishlist_list[index].saved_id = response.data;
        });

        if($('#wishlist_row_'+index).hasClass('text-dark-gray'))
            $scope.wishlist_list[index].saved_id = null;
        else
            $scope.wishlist_list[index].saved_id = 1;
    };

    $(document).on('submit', '.wl-modal-form', function(event) {
        event.preventDefault();
        $('.add-wishlist').addClass('loading');
        $http.post(APP_URL+"/wishlist_create", { data: $('.wl-modal-input').val(), id: $scope.room_id }).then(function(response) 
        {
            $('.wl-modal-form').addClass('d-none');
            $('.add-wishlist').removeClass('loading');
            $('.create-wl').removeClass('d-none');
            $scope.wishlist_list = response.data;
            event.preventDefault();
        });
        event.preventDefault();
    });

    $(document).on('click','.detail-sticky li a',function(e) {
        e.preventDefault();
        var target = $(this).attr("href");
        var top = $(target).offset().top - $('header').outerHeight() - $('.detail-sticky').outerHeight();

        $('html, body').stop().animate({
            scrollTop: top
        }, 1000);
    });

    $(window).scroll(function () {
        var scrollDistance = $(window).scrollTop();
        var header_height = $('header').outerHeight();
        var detail_sticky = $('.detail-sticky').outerHeight();
        $('.scroll-section').each(function (i) {
            // Calculate extra height because Map placed outer div
            var extra_height = ($(this).attr('id') == 'detail-map') ? -(header_height + detail_sticky) : 300;
            if ($(this).position().top <= scrollDistance - extra_height) {
                $('.detail-sticky li a.active').removeClass('active');
                $('.detail-sticky li a').eq(i).addClass('active');
            } else {
                $('.detail-sticky li a').eq(i).removeClass('active');
            }
        });
    }).scroll();

    $('.wl-modal-close').click(function() {
        var null_count = $filter('filter')($scope.wishlist_list, {saved_id : null});
        if(null_count.length == $scope.wishlist_list.length)
            $('#wishlist-button').prop('checked', false);
        else
            $('#wishlist-button').prop('checked', true);
    });
}]);

// Similar listing Slider
$(document).ready(function() {
    length = $('#similar-slider').attr('item-length');
    loop = false
    if (length>3) {
        loop = true
    }
    $('#similar-slider').owlCarousel({
        loop: loop,
        autoplay: true,
        margin: 20,
        rtl:rtl,
        nav: true,
        items: 3,
        responsiveClass: true,
        navText:['<i class="icon icon-chevron-right custom-rotate"></i>','<i class="icon icon-chevron-right"></i>'],  
        responsive:{
            0: {
                items: 1
            },
            768: {
                items: 2
            },
            992: {           
                items: 3  
            }
        }
    });
});

//  calendar triggered
$("#view-calendar").click(function(event) {
    $("#list_checkin").datepicker("show");
});

//  calendar triggered
$(".review_link").click(function(event) {
    header_height = $('header').height();
    detail_sticky = $('.detail-sticky').height();
    $(window).scrollTop($('#review-info').offset().top - (header_height+detail_sticky));
});

$("#contact_message_send").click(function() {
    if($('#message_checkin').val()=='') {
        $('#errors').removeClass('d-none');
        return false;
    }
    if($('#message_checkout').val()=='') {
        $('#errors').removeClass('d-none');
        return false;
    }
    if($('#message_checkout').val()!='' && $('#message_checkin').val()!='') {  
        $("#contact_message_send").prop('disabled', true);
        $("#message_form").trigger("submit");
        return false;
    }
});

$(".js-book-it-btn-container").click(function() {
    var checkin = $("#list_checkin").val();
    var checkout =  $("#list_checkout").val();
    var guests = $('#number_of_guests').val();
    var list_type = $("#type_list").val();

    if(checkin == '' || checkout ==''){
        $("#list_checkin").trigger("select");
        return false;
    }
    else if((guests == '' || guests == null) && (list_type != 'Multiple')){
        $("#number_of_guests").focus();
        $("#guest_error").show();
        return false;    
    }
});

