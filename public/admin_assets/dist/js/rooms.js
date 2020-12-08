var daterangepicker_format = $('meta[name="daterangepicker_format"]').attr('content');
var datepicker_format = $('meta[name="datepicker_format"]').attr('content');
var datedisplay_format = $('meta[name="datedisplay_format"]').attr('content');

$('#input_dob').datepicker({ 'dateFormat': 'dd-mm-yy'});
         var night_value    = $('#night').val();
         var cleaning_value = $('#cleaning').val();
         var additional     = $('#additional_guest').val();
         var guests         = $('#guests').val();
         var security_fee   = $('#security').val();
         var weekend_price  = $('#weekend').val();
         var week           = $('#week').val();
         var month          = $('#month').val();
         var currency_code  = $("#currency_code").find( "option:selected" ).prop("value");
         
   function step(step)
   {
      $(".frm").hide();
      $("#sf"+step).show();
      $('.tab_btn').removeAttr('disabled');
      $('.tab_btn#tab_btn_'+step).attr('disabled', 'disabled')
   }
   app.filter('objToArray', function() { return function(obj) {
        var array = [];
        for(elem in obj){
          array.push(obj[elem]);
        }
        array.pop();
        return array
    }});
   app.filter('nonZeroElem', function(){
    return function(input, attribute) {
      if (!angular.isObject(input)) return input;
      var array = [];
      for(var objectKey in input) {
        if (input[objectKey][attribute]>0) 
          array.push(input[objectKey]);
      }
      return array;
    }
  });
    app.filter('toArray', function() { return function(obj) {
      if (!(obj instanceof Object)) return obj;
      return _.map(obj, function(val, key) {
          return Object.defineProperty(val, '$key', {__proto__: null, beds_id: key});
      });
    }});
    app.filter('toArrayView', function() { return function(obj) {
      if (!(obj instanceof Object)) return obj;
      return _.map(obj, function(val, key) {
          return Object.defineProperty(val, '$key', {__proto__: null, bed_name: key});
      });
    }});
    app.filter('orderObjectBy', function(){
      return function(input, attribute) {
        if (!angular.isObject(input)) return input;

        var array = [];
        array[0] = '';
        for(var objectKey in input) {
            array.push(input[objectKey]);
        }

        array.sort(function(a, b){
            a = parseInt(a[attribute]);
            b = parseInt(b[attribute]);
            return a - b;
        });
        return array;
      }
    });
app.controller('rooms_admin', ['$scope', '$http', '$rootScope', '$compile', '$filter', function($scope, $http,$rootScope, $compile, $filter) {
  $rootScope.show_bed_room = [];

  $(document).ready(function() {
    $scope.full_calendar();
  });

  $scope.bed_count_valid = function(){
    $("#bedrooms").valid();  // This is not working and is not validating the form
  }

  // Common Function to Handle All post requests
  $scope.http_post = function(url, data, callback) {
        
        data = (!data) ? {} : data;

        $http.post(url,data).then(function(response) {
            if(response.status == 200) {
                if(callback) {
                    callback(response.data);
                }
            }
        }, function(response) {
            if(response.status == '300') {
                window.location = APP_URL + '/login';
            }
            else if(response.status == '500'){
                window.location.reload();
            }
        });
    };

  $scope.date = new Date();

  function strip(html)
  {
     var tmp = document.createElement("DIV");
     tmp.innerHTML = html;
     return tmp.textContent || tmp.innerText || "";
  }

  function getMonthFromString(mon)
  {
    return moment().month(mon).format("MM");
  }

  $scope.full_calendar = function() {
      $('#calendar').fullCalendar({
          selectable: false,
          unselectAuto: false,
          schedulerLicenseKey: 'GPL-My-Project-Is-Open-Source',
          header: {
            left: 'prev,next',
            center: 'title',
            right: 'month,agendaWeek,agendaDay'
          },
          defaultDate: $scope.date,
          firstDay: 1,
          events: $scope.calendar_data,
          eventRender: function(event, element, view) {
              element.attr('id',$scope.changeFormat(event.start));
              if(event.className.length) {
                  element.addClass(event.className);
              }
              $('<div class="fc-bgevent-data" data-price="'+event.price+'" data-notes="'+event.notes+'" data-status="'+event.description+'"> <span class="price">'+ event.title +'</span> <span class="notes">'+ event.notes +'</span> </div>').appendTo(element);
          }
      });
  };

  $scope.changeFormat = function(date,format = 'YYYY-MM-DD') {
      return date.format(format);
  };

  $(document).on('click','.fc-prev-button,.fc-next-button,.fc-today-button',function() {
    
      var month_year = strip($('.fc-center').html());
      month_year =month_year.split(' ');
      var month = getMonthFromString(month_year[0]);
      var year = month_year[1];

      var data_params = {};
      data_params['month'] = month;
      data_params['year'] = year;
      if($('#multiple_calendar').val() == undefined){
        var id = $('#room_id').val();
        data_params['type'] = 'main_room';
      }else{
        var id = $('#multiple_calendar').val();  
        data_params['type'] = 'sub_room';
      }

      var data = JSON.stringify(data_params);
      var url= APP_URL+'/'+ADMIN_URL+'/ajax_calendar/' + id;

      $('#calendar').addClass('loading');

      var callback_function = function(response_data) {
          $scope.calendar_data = response_data;
          $scope.date = year+'-'+ month +'-10';
          $('#calendar').fullCalendar('destroy');
          if(!$scope.$$phase){
              $scope.$apply();
          }
          setTimeout( () => {
            $scope.full_calendar();
            $('#calendar').removeClass('loading');
          } , 10);
      };

      $scope.http_post(url,data,callback_function);
  });
  

  $('#multiple_calendar').change(function(){
    
      var month_year = strip($('.fc-center').html());
      month_year =month_year.split(' ');
      var month = getMonthFromString(month_year[0]);
      var year = month_year[1];

      var data_params = {};
      data_params['month'] = month;
      data_params['year'] = year;
      if($('#multiple_calendar').val() == undefined){
        var id = $('#room_id').val();
        data_params['type'] = 'main_room';
      }else{
        var id = $('#multiple_calendar').val();  
        data_params['type'] = 'sub_room';
      }

      var data = JSON.stringify(data_params);
      var url= APP_URL+'/'+ADMIN_URL+'/ajax_calendar/' + id;

      $('#calendar').addClass('loading');

      var callback_function = function(response_data) {
          $scope.calendar_data = response_data;
          $scope.date = year+'-'+ month +'-10';
          $('#calendar').fullCalendar('destroy');
          if(!$scope.$$phase){
              $scope.$apply();
          }
          setTimeout( () => {
            $scope.full_calendar();
            $('#calendar').removeClass('loading');
          } , 10);
      };
      $scope.http_post(url,data,callback_function);
  })
  var v = $("#add_room_form").validate({

      ignore: ':hidden:not(.do-not-ignore)',
      /*onkeyup: false,
      onfocusout: false,*/
      rules: {
        calendar: { required: true },
        bedrooms: { 
          required: true,
          min: 1,
          bed_count: true 
        },
        beds: { required: true },
        type: { required: true },
        bed_type: { bed_count: true },
        bathrooms: { required: true },
        property_type: { required: true },
        room_type: { required: true },
        accommodates: { required: true },
        "name[]": { required: true },
        "summary[]": { required: true },        
        "language[]": { required: true },
        "rooms_bed_type[]":{required:true},        
        country: { required: true },
        address_line_1: { required: true },
        city: { required: true },
        state: { required: true },
        latitude : {
            required:{ 
              depends: function(element){
                address_line_1 = $("#address_line_1").val();
                if($scope.step_id == '4' && address_line_1){
                  return true;
                }
                else{
                  return false;
                }
              }
            }
          },
        //night validation
        night: { 
                 required: true,
                 digits: true,
                 min: 1,
                },
        //cleaning validation
        cleaning: {
                    /*required: false,*/
                    digits: true,
        },
        //Additional Guest Charge validation
        additional_guest:{
                    /*required: false,*/
                    digits: true,
        },
        //Additional Guests validation
        guests: {
                  /*required: false,*/
                  digits: true,
        },
        //Security Fee validation
        security: {
                    /*required: false,*/
                    digits: true,
        },
        //weekend price validation
        weekend: {
                  /*required: false,*/
                  digits:true,
        },
           //week validation
          week: { 
                 /*required: true,*/
                 digits: true,
        
          },
             //month validation
          month: { 
                 /*required: true,*/
                 digits: true,
          },
        video: { youtube: true },
        'photos[]': { required: { depends: function(element){
          if($('#js-photo-grid li').length == 0){
            return true;
          }
          else{
            return false;
          }
        } } ,extension:"png|jpg|jpeg|gif"},
        'room_accommodates[]': { required: true},
        'room_type_multiple[]' : { required: true},
        'room_name[]': { required: true },
        'room_description[]': { required: true },
        'room_photos[][]': { required: true,extension:"png|jpg|jpeg|gif", },
        'room_guests[]': { required: true },
        'room_bedrooms[]': { required: true },
        'room_beds[]': { required: true },
        'room_bed_type[]': { required: true },
        'room_bathrooms[]': { required: true },
        'room_night[]': { required: true, digits: true,min: 1, },
        'room_cleaning[]': { digits: true,min: 0, },
        'room_security[]': { digits: true,min: 0, },
        'weekend_price[]': { digits: true,min: 0, },
        'room_currency_code[]': {required:true},
        cancel_policy: { required: true },
        user_id: { required: true },
      },
      messages: {
        night : {
          min : jQuery.validator.format("Please enter a value greater than 0")
        },
        latitude : {
            required : "Please choose the address from the google results.",
        },
        bedrooms : {
            min : "Please select at least one bed room.",
        }
      },
      errorElement: "span",
      errorClass: "text-danger",
      errorPlacement: function( label, element ) {
        $('.photos_errors11').css('display','none');
        $('.photos_errors11').text('');
        $('#photos_errors').css('display','none');
        $('#photos_errors').text('');
        if(element.attr("data-error-placement" ) === "container" ){
          container = element.attr('data-error-container');
          $(container).append(label);
        } else {
          label.insertAfter( element ); 
        }
      },
      extension:"Only png file is allowed!"
    });

   $.validator.addMethod("extension", function(value, element, param) {
  param = typeof param === "string" ? param.replace(/,/g, '|') : "png|jpe?g|gif";
  return this.optional(element) || value.match(new RegExp(".(" + param + ")$", "i"));
  }, $.validator.format("Please upload the images like JPG,JPEG,PNG,GIF File Only."));

  $.validator.addMethod("bed_count", function(value, element, param) {
    bed_count = 0
    for (bed_type in $scope.bed_types_name){
      for(beds in $scope.bed_types_name[bed_type]){
        if (typeof $scope.bed_types_name[bed_type][beds]['count'] !== 'undefined') {
          bed_count += $scope.bed_types_name[bed_type][beds]['count']
        }
      }
    }
    return bed_count>0;
  }, $.validator.format("Please select at lease one bed type in bedroom."));

   $('.frm').hide();
   $('.frm#sf1').show();


$(document).on("change", '#upload_photos', function() {
    var fi = document.getElementById('upload_photos');
    $('#photos_errors').css('display','none');
    $('#photos_errors').text('');
    if (fi.files.length > 0) {      // FIRST CHECK IF ANY FILE IS SELECTED.
       
        for (var i = 0; i <= fi.files.length - 1; i++) {

            /*if(fi.files.item(i).size >= 10000000)
            {
              var $el = $('#upload_photos');
               $el.wrap('<form>').closest('form').get(0).reset();
               $el.unwrap();
               $('#photos_errors').css('display','block');
              $('#photos_errors').text('Photos must be less than 10MB.');
              return false;
            }*/
            var fileName, fileExtension, fileSize, fileType, dateModified;

            // FILE NAME AND EXTENSION.
            fileName = fi.files.item(i).name;
            fileExtension = fileName.replace(/^.*\./, '');

            // CHECK IF ITS AN IMAGE FILE.
            // TO GET THE IMAGE WIDTH AND HEIGHT, WE'LL USE fileReader().
            // if (fileExtension == 'png' || fileExtension == 'jpg' || fileExtension == 'jpeg') {
            //    readImageFile(fi.files.item(i));             // GET IMAGE INFO USING fileReader().
            // }
            
        }

        // GET THE IMAGE WIDTH AND HEIGHT USING fileReader() API.
        function readImageFile(file) {
            var reader = new FileReader(); // CREATE AN NEW INSTANCE.

            reader.onload = function (e) {
                var img = new Image();      
                img.src = e.target.result;

                img.onload = function () {
                    var w = this.width;
                    var h = this.height;
                    console.log(w,h);
                    if(w < 1050 || h < 400)
                    {
                      var $el = $('#upload_photos');
                       $el.wrap('<form>').closest('form').get(0).reset();
                       $el.unwrap();
                       $('#photos_errors').css('display','block');
                      $('#photos_errors').text('Photos must be at least 1050x400 pixels.');
                      return false;
                    }
                    else if(w > 1850 || h > 900)
                    {

                      var $el = $('#upload_photos');
                       $el.wrap('<form>').closest('form').get(0).reset();
                       $el.unwrap();
                       $('#photos_errors').css('display','block');
                      $('#photos_errors').text('Photos must be less than 1850x900 pixels.');
                      
                      return false;
                    }
                    // else{
                    //   $('#photos_errors').css('display','none');
                    //   $('#photos_errors').text('');
                    // }
                }
            };
            reader.readAsDataURL(file);
        }
    }
});

$(document).on("change", '.room_photos1', function() {
    var index = $(this).attr('data-index');
    var fi = document.getElementById('upload_photos_'+index);
   $('#photos_errors_'+index).css('display','none');
    $('#photos_errors_'+index).text('');

    var val = $(this).val();
    switch(val.substring(val.lastIndexOf('.') + 1).toLowerCase()){
        case 'gif': case 'jpg': case 'png': case 'jpeg' :
            $('.photos_error_'+index).addClass('hidden');
            break;
        default:
            $('.room_photos_'+index).val('');
            // error message here
            $('.photos_error_'+index).removeClass('hidden');
            break;
    }
    if (fi.files.length > 0) {      // FIRST CHECK IF ANY FILE IS SELECTED.
       
        for (var i = 0; i <= fi.files.length - 1; i++) {

          /*if(fi.files.item(i).size >= 10000000)
          {
            var $el = $('#upload_photos_'+index);
             $el.wrap('<form>').closest('form').get(0).reset();
             $el.unwrap();
             $('#photos_errors_'+index).css('display','block');
            $('#photos_errors_'+index).text('Photos must be less than 10MB.');
            return false;
          }*/
            var fileName, fileExtension, fileSize, fileType, dateModified;

            // FILE NAME AND EXTENSION.
            fileName = fi.files.item(i).name;
            fileExtension = fileName.replace(/^.*\./, '');

            // CHECK IF ITS AN IMAGE FILE.
            // TO GET THE IMAGE WIDTH AND HEIGHT, WE'LL USE fileReader().
            // if (fileExtension == 'png' || fileExtension == 'jpg' || fileExtension == 'jpeg') {
            //    readImageFile(fi.files.item(i));             // GET IMAGE INFO USING fileReader().
            // }
            
        }

        // GET THE IMAGE WIDTH AND HEIGHT USING fileReader() API.
        function readImageFile(file) {
            var reader = new FileReader(); // CREATE AN NEW INSTANCE.

            reader.onload = function (e) {
                var img = new Image();      
                img.src = e.target.result;

                img.onload = function () {
                    var w = this.width;
                    var h = this.height;
                    console.log(w,h);
                    if(w < 1050 || h < 400)
                    {
                      var $el = $('#upload_photos_'+index);
                       $el.wrap('<form>').closest('form').get(0).reset();
                       $el.unwrap();
                       $('#photos_errors_'+index).css('display','block');
                      $('#photos_errors_'+index).text('Photos must be at least 1050x400 pixels.');
                      return false;
                    }
                    else if(w > 1850 || h > 900)
                    {

                      var $el = $('#upload_photos_'+index);
                       $el.wrap('<form>').closest('form').get(0).reset();
                       $el.unwrap();
                       $('#photos_errors_'+index).css('display','block');
                      $('#photos_errors_'+index).text('Photos must be less than 1850x900 pixels.');
                      
                      return false;
                    }
                    // else{
                    //   $('#photos_errors').css('display','none');
                    //   $('#photos_errors').text('');
                    // }
                }
            };
            reader.readAsDataURL(file);
        }
    }
});

   function next(step)
   {
    if(v.form())
    {
      if(step != 11)
      {
        $(".frm").hide();
        $("#sf"+(step+1)).show();
      }
      else
      {
        document.getElementById("add_room_form").submit();
      }
    }
   }

   function back(step)
   {
    $(".frm").hide();
    $("#sf"+(step-1)).show();
   }

/* $scope.type_change = function(type){
    $scope.bedrooms = 0;
    $scope.common_bed = 0;
    $scope.common_beds = '';
    
    $scope.bathrooms = 0;
    $scope.bathroom_shared = 'No';
     
  }*/
 



  $scope.type ="Single";
  $scope.steps = ['1', '2', '3', '4', '5', '6', '7', '8', '12', '13', '9', '10', '11'];
  $scope.add_steps = ['2', '3', '4', '5', '6', '7', '8', '12', '13', '9', '10', '11'];
  

  $("input[name='type'").change(function() {
    if(this.value=="Single"){
      $scope.steps = ['1', '2', '3', '4', '5', '6', '7', '8', '12', '13', '9', '10', '11'];
      $scope.add_steps = ['2', '3', '4', '5', '6', '7', '8', '12', '13', '9', '10', '11'];
    }else{
      $scope.steps = ['2', '3', '4', '6', '7', '14', '9', '10', '11'];
      $scope.add_steps = ['3', '4', '6', '7', '14', '9', '10', '11'];
    }
    $scope.$apply()
});
 
  $scope.step_name = ""; 
  $scope.step = 0;
  $scope.go_to_step = function(step)
  {
    step_id = $scope.steps[step];
    $scope.step_id = step_id; 
    $(".frm").hide();
    $("#sf"+step_id).show();
    $scope.step_name = $("#sf"+step_id).attr('data-step-name');
    $scope.step = step;
    $('#input_current_step_id').val(step_id);
    $('#input_current_step').val(step);
  }
    $scope.go_to_edit_step = function(step)
   {
      $(".frm").hide();
      $("#sf"+step).show();
      $scope.step_id = step;
      $('.tab_btn').removeAttr('disabled');
      $('.tab_btn#tab_btn_'+step).attr('disabled', 'disabled')
   }
  $scope.go_to_step($scope.step);
  $scope.add_room_steps = function()
  {
    $scope.steps = $scope.add_steps;
    $scope.go_to_step($scope.step);
  }
  $scope.next_step =function(step){
    current_step = $scope.steps[step];
   console.log(step);
    if(v.form()){
      if(current_step != '11'){
        $('html, body').animate({
            scrollTop: ($('.content-header').offset().top)
        },500);
        $scope.step = next_step = (step+1);
        $scope.go_to_step(next_step);
      }
      else
      {
        $('.room_add_btn').prop('disabled', true);
        $('#add_room_form').submit();
      }
    }
  }
  $scope.back_step = function(step)
  {
      $scope.step = next_step = (step-1); 
      $scope.go_to_step(next_step);
  }
  $scope.get_step_name = function(step)
  {
    step_id = $scope.steps[step]; 
    step_name = $("#sf"+step_id).attr('data-step-name');
    return step_name;
  }

initAutocomplete(); // Call Google Autocomplete Initialize Function

$scope.rows = [];
$scope.row = [];
$scope.row.push({'id':'rows'+0});

//$(document).on('click', '#check', function()
  $(document).ready(function(){
  var value=$('#room_id').val();
  $http.post(APP_URL+'/get_lang_details/'+value, { }).then(function(response) {
    $scope.rows = response.data;
  $http.post(APP_URL+'/get_lang', { }).then(function(response) {
    $scope.lang_list = response.data;
  });
});
});

$scope.addNewRow = function() {
    var newItemNo = $scope.rows.length+1;
    $scope.rows.push({'id':'rows'+newItemNo});
  };

  $scope.removeRow = function(name) {       
    var index = name;   
    var comArr = eval( $scope.rows );
    for( var i = 0; i < comArr.length; i++ ) {
      if( comArr[i].name === name ) {
        index = i;
        break;
      }
    }
      $scope.rows.splice( index, 1 );   
  };

  $scope.addNewRows = function() {


    //$scope.rooms_early_bird_items1 = [];
    var newItemNo = $scope.row.length+1;
    $scope.row.push({'id':'rows'+newItemNo});
   setTimeout(function(){ $scope.removemultiple(); }, 500);

    infants_check();

  };

  function infants_check(){
    for (var i = 0; i < $scope.row.length; i++) {
      if($('#infants_allowed_'+i).prop('checked')==true){
        $('#infant_allowed_'+i).val('true');
      }
      else{
        $('#infant_allowed_'+i).val('false');
      }
    }
   }


  $scope.removemultiple =  function (){

    $scope.multiple_type = $('input[name=type]:checked').val();

    if($scope.multiple_type=="Multiple")
    {
      $('.multiple_room_type option[value="3"]').hide();
    }
    else
      $('.multiple_room_type option[value="3"]').show();
  }

  $scope.removeRows = function(name) {       
    var index = name;   
    var comArr = eval( $scope.row );
    for( var i = 0; i < comArr.length; i++ ) {
      if( comArr[i].name === name ) {
        index = i;
        break;
      }
    }
      $scope.row.splice( index, 1 );  

      infants_check(); 
  };

  $scope.addNewRooms = function() {

    var newItemNo = $scope.multiple_rooms.length;
    
    $scope.multiple_rooms.push({'id':'rows'+newItemNo,'guests':'1','booking_type':'request_to_book','multiple_rooms_length_of_stay':[]});
    setTimeout(function(){ $scope.removemultiple(); }, 500);
  };

   $scope.removeRooms = function(name,id) {

      $('#delete_room_but').modal('show');
      $('#name_delete').val(name);
      $('#id_delete').val(id);
      
  };

$(document).on('click','#submit_delete',function(){

    var id = $('#id_delete').val();
    var name = $('#name_delete').val();
    $scope.error_remove = '';
      if(parseInt(id)){
        $.ajax({
          type: "get",
            url: APP_URL+'/'+ADMIN_URL+'/delete_mulitple_room/'+id,
            success:function(data){
              $scope.error_remove = data;
            },
        });
      }
      setTimeout(function(){
        if($scope.error_remove!='error' && $scope.error_remove!='error1'){
          $('.remove_errors').addClass('hidden');
          var index = name;   
          var comArr = eval($scope.multiple_rooms);          
          $scope.multiple_rooms.splice( index, 1);
          $scope.$apply();  
        }
        else if($scope.error_remove=='error1'){
          $('.remove_error1_'+name).removeClass('hidden');
        }
        else{
          $('.remove_error_'+name).removeClass('hidden');
        }
        $('#delete_room_but').modal('hide');
      },1000);
  });

// Google Place Autocomplete Code
$scope.location_found = false;
$scope.autocomplete_used = false;
var autocomplete;


$(document).on('click', '.delete-multiple-photo-btn', function()
{
  var id = $(this).attr('data-photo-id');
  var multiple_room_id = $(this).attr('data-multiple-room-id');
  if($('[id^="mul_photo_li_'+multiple_room_id+'_"]').size() > 1)
  {
  $http.post(APP_URL+'/'+ADMIN_URL+'/delete_multiple_photos', { photo_id : id,multiple_room_id : multiple_room_id }).then(function(response) 
  {
    if(response.data.success == 'true')
    {
      $('#mul_photo_li_'+multiple_room_id+'_'+id).remove();
    }
  });
  }
  else
  {
    alert('You cannnot delete last photo. Please upload alternate photos and delete this photo.');
  }
});

function initAutocomplete()
{

  autocomplete = new google.maps.places.Autocomplete(document.getElementById('address_line_1'),{types: ['address']});
  autocomplete.addListener('place_changed', fillInAddress);
}

function fillInAddress() 
{
    $scope.autocomplete_used = true;
    fetchMapAddress(autocomplete.getPlace());
}

$scope.addNewRow = function() {
    var newItemNo = $scope.rows.length+1;
    $scope.rows.push({'id':'rows'+newItemNo});
  };

  $scope.removeRow = function(name) {       
    var index = name;   
    var comArr = eval( $scope.rows );
    for( var i = 0; i < comArr.length; i++ ) {
      if( comArr[i].name === name ) {
        index = i;
        break;
      }
    }
      $scope.rows.splice( index, 1 );   
  };

function fetchMapAddress(data)
{ //console.log(data);
  if(data['types'] == 'street_address')
    $scope.location_found = true;
  var componentForm = {
    street_number: 'short_name',
      route: 'long_name',
      sublocality_level_1: 'long_name',
      sublocality: 'long_name',
      locality: 'long_name',
      administrative_area_level_1: 'long_name',
      country: 'short_name',
      postal_code: 'short_name'
  };

    $('#city').val('');
    $('#state').val('');
    $('#country').val('');
    $('#address_line_1').val('');
    $('#address_line_2').val('');
    $('#postal_code').val('');

    var place = data;
    $scope.street_number = '';
    for (var i = 0; i < place.address_components.length; i++) 
    {
      var addressType = place.address_components[i].types[0];
      if (componentForm[addressType]) 
      {
        var val = place.address_components[i][componentForm[addressType]];
      
      if(addressType       == 'street_number')
        $scope.street_number = val;
      if(addressType       == 'route')
        var street_address = $scope.street_number+' '+val;
        $('#address_line_1').val($.trim(street_address));
        //$('#address_line_1').val(val);
      if(addressType       == 'postal_code')
        $('#postal_code').val(val);
      if(addressType       == 'locality')
        $('#city').val(val);
      if(addressType       == 'administrative_area_level_1')
        $('#state').val(val);
      if(addressType       == 'country')
        $('#country').val(val);
      }
    }

  var address   = $('#address_line_1').val();

  var latitude  = place.geometry.location.lat();
  var longitude = place.geometry.location.lng();

    if($('#address_line_1').val() == '')
      $('#address_line_1').val($('#city').val());

    if($('#city').val() == '')
      $('#city').val('');
    if($('#state').val() == '')
      $('#state').val('');
    if($('#postal_code').val() == '')
      $('#postal_code').val('');

  $('#latitude').val(latitude);
  $('#longitude').val(longitude);
}   

$( "#username" ).autocomplete({
  source: APP_URL+'/'+ADMIN_URL+'/rooms/users_list',
  select: function(event, ui)
  {
    $('#user_id').val(ui.item.id);
  }
});

$(".slide").each(function(i) {
  var item = $(this);
  var item_clone = item.clone();
  item.data("clone", item_clone);
  var position = item.position();
  item_clone
  .css({
    left: position.left,
    top: position.top,
    visibility: "hidden"
  })
    .attr("data-pos", i+1);
  
  $("#cloned-slides").append(item_clone);
});

$(".all-slides").sortable({
  
  axis: "x,y",
  revert: true,
  scroll: false,
  // placeholder: "sortable-placeholder1",
  cursor: "move",

  start: function(e, ui) {
    ui.helper.addClass("exclude-me");
    // $(".all-slides .slide:not(.exclude-me)")
    //   .css("visibility", "hidden");
    ui.helper.data("clone").hide();
    $(".cloned-slides .slide").css("visibility", "visible");
  },

  stop: function(e, ui) {
    $(".all-slides .slide.exclude-me").each(function() {
      var item = $(this);
      var clone = item.data("clone");

      var position = item.position();

      clone.css("left", position.left);
      clone.css("right", position.right);
      clone.css("top", position.top);
      clone.css("bottom", position.bottom);
      clone.show();

      item.removeClass("exclude-me");
    });
    
    $(".all-slides .slide").each(function() {
      var item = $(this);
      var clone = item.data("clone");
      
      clone.attr("data-pos", item.index());
    });
   
    $(".all-slides .slide").css("visibility", "visible");
    $(".cloned-slides .slide").css("visibility", "hidden");
  },

  change: function(e, ui) {
    $(".all-slides .slide:not(.exclude-me)").each(function() {
      var item = $(this);

      var clone = item.data("clone");
     // alert(clone);
      clone.stop(true, false);
      var position = item.position();
      clone.animate({
        left: position.left,
        right: position.right,
        top:position.top,
        bottom:position.bottom
       
      }, 0);
    });
  }
  
});
$(document).on('click', '.delete-photo-btn', function()
{
  var id = $(this).attr('data-photo-id');
  var room_id = $('#room_id').val();
  
  if($('[id^="photo_li_"]').size() > 1)
  {
  $http.post(APP_URL+'/'+ADMIN_URL+'/delete_photo', { photo_id : id,room_id : room_id }).then(function(response) 
  {
    if(response.data.success == 'true')
    {
      $('#photo_li_'+id).remove();
    }
  });
  }
  else
  {
    alert('You cannnot delete last photo. Please upload alternate photos and delete this photo.');
  }
});

$(document).on('click', '.featured-photo-btn', function()
{
  var id = $(this).attr('data-featured-id');
  var room_id = $("input[id=room_id]").val();
  //alert(id + "" + room_id); 
  
  $http.post(APP_URL+'/'+ADMIN_URL+'/featured_image', { id : room_id ,photo_id  : id}).then(function(response) 
  {
    if(response.data.success == 'true')
    {
      alert('success');
    }
  });
 
});

$(document).on('keyup', '.highlights', function()
{
  var value = $(this).val();
  var id = $(this).attr('data-photo-id');
  $('#saved_message').fadeIn();
  $http.post(APP_URL+'/'+ADMIN_URL+'/photo_highlights', { photo_id : id, data : value }).then(function(response)
  {
    $('#saved_message').fadeOut();
  });
});

$(document).on('change', '#additional_guest', function() {
  disableAdditionalGuestCharge();
});
disableAdditionalGuestCharge();
function disableAdditionalGuestCharge() {
  if ($('#additional_guest').val() == "0")
    $('#guests').prop('disabled', true);
  else
    $('#guests').prop('disabled', false);
}

  $.validator.addMethod("youtube", function(value, element) {
    if (value != undefined && value.length > 0) {
      var regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|\&v=|\?v=)([^#\&\?]*).*/;
      var match = value.match(regExp);
      if (match && match[2].length == 11) {
        return true
      }
      else {
          return false;
      }
    }
    return true;
  }, 'Please select a valid youtube url.');
  $.validator.addMethod("maximum_stay_value", function(value, element, param) {
    min_elem = $(element).attr('data-minimum_stay');
    min_value = $(min_elem).val();
    if((min_value-0) > (value-0) && min_value != '' && value != '')
    {
      return false;
    }
    else
    {
      return true;
    }
  }, $.validator.format("Maximum stay must be greater than Minimum stay"));
  $.validator.addClassRules({
   /*multiple_rooms_bed_type_select_req:{
      required : true,
    },*/
    rooms_bed_type:{
      required : true,
    },
    discount: {
      digits : true,
      required : true,
      min: 1,
      max: 99,
    },
    early_bird_period: {
      digits: true,
      required: true,
      min: 30,
      max : 1080,
    },
    last_min_period: {
      digits: true,
      required: true,
      min: 1,
      max : 28,
    },
    minimum_stay: {
      digits: true,
      min: 1,
    },
    maximum_stay: {
      digits: true,
      min: 1,
      maximum_stay_value:true,
    },
    availability_minimum_stay: {
      digits: true,
      min: 1,
    },
    availability_maximum_stay: {
      required: {
        depends: function(element){
          min_elem = $(element).attr('data-minimum_stay');
          min_value = $(min_elem).val();
          return min_value == '';
        }
      },
      digits: true,
      min: 1,
      maximum_stay_value:true,
    }
  });

  $scope.multiple_rooms_bed_type_items = [];
  $scope.multiple_rooms_bed_type_select = '';
  $scope.old_index_multiple = '';
  $scope.multiple_bed_type_items1 = [];


  $(document).on('change','.multiple_rooms_bed_type_select',function(){
      
      var index1 = $(this).attr('data-old_index');

      if($scope.multiple_rooms_bed_type_items[index1]){
        new_period = $('#multiple_rooms_bed_type_select_'+index1).val();
         $scope.multiple_rooms_bed_type_items[index1].push({'bed_type' : new_period-0,'beds':1});
        if(!$scope.$$phase){
          $scope.$apply();
        }
      }
      else{
        if($scope.old_index_multiple!=index1){
          $scope.multiple_bed_type_items1 = [];
        }
        $scope.old_index_multiple = index1;
        new_period = $('#multiple_rooms_bed_type_select_'+index1).val();
        $scope.multiple_bed_type_items1.push({'bed_type' : new_period-0,'beds':1});
        $scope.multiple_rooms_bed_type_items[index1] = $scope.multiple_bed_type_items1;
        if(!$scope.$$phase){
          $scope.$apply();
        }
      }

  });

  $scope.multiple_rooms_bed_type_remove = function(index,index1) {    
    
    var check = '';
    item =$scope.multiple_rooms_bed_type_items[index1][index];
    $('.room_bed_type_remove').addClass('hide');
    $('#multiple_rooms_bed_type_error_'+index1).css('display','none');
    if(item.id != '' && item.id) {
      var room_id = (item.room_id)?item.room_id:'';
      
      $('button[type="submit"]').attr('disabled', true);
      $http.post(APP_URL+'/'+ADMIN_URL+'/rooms/delete_mulitple_room_bed_type/'+item.id, {
        room_id : room_id
      }).then(function(response){
        $('button[type="submit"]').removeAttr('disabled');
        if(response.data.delete_error=='true'){
          $('#multiple_rooms_bed_type_error_'+index1).css('display','block');
          check = 'true';
        }else{
          $('#multiple_rooms_bed_type_error_'+index1).css('display','none');
          check = '';
        }
      });
    }
    setTimeout(function(){
      if(check!='true'){
        $scope.multiple_rooms_bed_type_items[index1].splice(index, 1);
        $('.room_bed_type_remove').removeClass('hide');
      }
      if(!$scope.$$phase){
        $scope.$apply();
      }
    },500);
  }

  $scope.multiple_rooms_bed_type_option_avaialble = function(option,index1) {
    
    var found = $filter('filter')($scope.multiple_rooms_bed_type_items[index1], {'bed_type': option}, true);
    var found_text = $filter('filter')($scope.multiple_rooms_bed_type_items[index1], {'bed_type': ''+option}, true);
    return !found.length && !found_text.length;
  }

  $scope.bed_type_items = [];
  $scope.bed_type_select = '';

  $scope.add_bed_type = function() {
    
    new_period = $scope.bed_type_select;
    $scope.bed_type_items.push({'bed_type' : new_period-0,'beds':1});
    $scope.bed_type_select = '';
    $('#rooms_bed_type_error').css('display','none');
    
  }

  $scope.remove_bed_type = function(index) {    
    
    item =$scope.bed_type_items[index];

    var check = '';
    $('.room_bed_type_remove').addClass('hide');
    $('#rooms_bed_type_error').css('display','none');
    if(item.id != '' && item.id) {
      $('button[type="submit"]').attr('disabled', true);
      $http.post(APP_URL+'/'+ADMIN_URL+'/rooms/delete_rooms_bed_type/'+item.id, {
        room_id : $('#room_id').val(),
      }).then(function(response){
        $('button[type="submit"]').removeAttr('disabled');

        if(response.data.delete_error=='true'){
          $('#rooms_bed_type_error').css('display','block');
          check = 'true';
        }
        else{
          $('#rooms_bed_type_error').css('display','none');
          check = '';
        }
      })
    }
    setTimeout(function(){
      if(check!='true'){
        $scope.bed_type_items.splice(index, 1);
        $('.room_bed_type_remove').removeClass('hide');
      }
      if(!$scope.$$phase){
        $scope.$apply();
      }
    },500);
  }

  $scope.bed_type_option_avaialble = function(option) {
    var found = $filter('filter')($scope.bed_type_items, {'bed_type': option}, true);
    var found_text = $filter('filter')($scope.bed_type_items, {'bed_type': ''+option}, true);
    return !found.length && !found_text.length;
  }

  $scope.add_price_rule = function(type) {
    if(type == 'length_of_stay')
    {
      new_period = $scope.length_of_stay_period_select;
      $scope.length_of_stay_items.push({'period' : new_period-0});
      $scope.length_of_stay_period_select = '';
    }
    else if(type== 'early_bird') 
    {
      $scope.early_bird_items.push({'period' : ''});
    }
    else if(type== 'last_min') 
    {
      $scope.last_min_items.push({'period' : ''});
    }
  }

  $scope.remove_price_rule = function(type, index) {
    if(type == 'length_of_stay') {
      item =$scope.length_of_stay_items[index];
      $scope.length_of_stay_items.splice(index, 1);
    }
    else if(type == 'early_bird') {
      item =$scope.early_bird_items[index];
      $scope.early_bird_items.splice(index, 1);
    }
    else if(type == 'last_min') {
      item =$scope.last_min_items[index];
      $scope.last_min_items.splice(index, 1);
    }
    if(item.id != '' && item.id) {
      $('.'+type+'_wrapper').addClass('loading');
      $('button[type="submit"]').attr('disabled', true);
      $http.post(APP_URL+'/'+ADMIN_URL+'/rooms/delete_price_rule/'+item.id, {}).then(function(response){
        $('.'+type+'_wrapper').removeClass('loading');
        $('button[type="submit"]').removeAttr('disabled');
      })
    }
  }
  $scope.length_of_stay_option_avaialble = function(option) {
    var found = $filter('filter')($scope.length_of_stay_items, {'period': option}, true);
    var found_text = $filter('filter')($scope.length_of_stay_items, {'period': ''+option}, true);
    return !found.length && !found_text.length;
  }

  $scope.rooms_length_of_stay_items = [];
  $scope.rooms_length_of_stay_items1 = [];
  $scope.old_index = '';
  $scope.rooms_length_of_stay_period_select ='';

  $(document).on('change','.rooms_length_of_stay_period_select',function(){

    var type = $(this).attr('data-type');
    var index = $(this).attr('data-old_index');
    $scope.rooms_length_of_stay_period_select = '';
    if(type == 'length_of_stay')
    {
      if($scope.rooms_length_of_stay_items[index]){
        new_period = $('#rooms_length_of_stay_period_select_'+index).val();
        $scope.rooms_length_of_stay_items[index].push({'period' : new_period-0});
        if(!$scope.$$phase){
          $scope.$apply();
        }
      }
      else{
        if($scope.old_index!=index){
          $scope.rooms_length_of_stay_items1 = [];
        }
        $scope.old_index = index;
        new_period = $('#rooms_length_of_stay_period_select_'+index).val();
        $scope.rooms_length_of_stay_items1.push({'period' : new_period-0});
        $scope.rooms_length_of_stay_items[index] = $scope.rooms_length_of_stay_items1;
        if(!$scope.$$phase){
          $scope.$apply();
        }
      }
    }

  });

  $scope.rooms_early_bird_items1 = [];
  $scope.rooms_early_bird_items = [];
  $scope.old_index1 = '';
  $scope.rooms_last_min_items1 = [];
  $scope.rooms_last_min_items = [];
  $scope.old_index2 = '';

  $scope.rooms_remove_price_rule = function(type, index,index1) {
    if(type == 'length_of_stay') {
      item =$scope.rooms_length_of_stay_items[index1][index];
      $scope.rooms_length_of_stay_items[index1].splice(index, 1);
    }
    else if(type == 'early_bird') {
      item =$scope.rooms_early_bird_items[index1][index];
      $scope.rooms_early_bird_items[index1].splice(index, 1);
    }
    else if(type == 'last_min') {
      item =$scope.rooms_last_min_items[index1][index];
      $scope.rooms_last_min_items[index1].splice(index, 1);
    }
    if(item.id != '' && item.id) {
      $('.'+type+'_wrapper').addClass('loading');
      $('button[type="submit"]').attr('disabled', true);
      $http.post(APP_URL+'/'+ADMIN_URL+'/rooms/multiple_delete_price_rule/'+item.id, {}).then(function(response){
        $('.'+type+'_wrapper').removeClass('loading');
        $('button[type="submit"]').removeAttr('disabled');
      })
    }
  }

  $scope.rooms_length_of_stay_option_avaialble = function(option,index) {
    
    var found = $filter('filter')($scope.rooms_length_of_stay_items[index], {'period': option}, true);
    var found_text = $filter('filter')($scope.rooms_length_of_stay_items[index], {'period': ''+option}, true);
    if(found == undefined && found_text==undefined){
      found = [];
      found_text = [];
    }
    
    return !found.length && !found_text.length;
  }


  $(document).on('change','.rooms_length_of_stay_period_select_edit',function(){
    var  type = $(this).attr('data-type');
    var index = $(this).attr('data-old_index');
    if($scope.rooms_length_of_stay_items[index]){
        new_period = $('#rooms_length_of_stay_period_selects_'+index).val();
        $scope.rooms_length_of_stay_items[index].push({'period' : new_period-0});
        $scope.rooms_length_of_stay_period_select = '';
        if(!$scope.$$phase){
          $scope.$apply();
        }
      }
      else{
        if($scope.old_index!=index){
          $scope.rooms_length_of_stay_items1 = [];
        }
        $scope.old_index = index;
        new_period = $('#rooms_length_of_stay_period_selects_'+index).val();
        $scope.rooms_length_of_stay_items1.push({'period' : new_period-0});
        $scope.rooms_length_of_stay_items[index] = $scope.rooms_length_of_stay_items1;
        $scope.rooms_length_of_stay_period_select = '';
        if(!$scope.$$phase){
          $scope.$apply();
        }
      }

  });

  $scope.rooms_add_price_rule_edit = function(type,index,index1) {
    if(type== 'early_bird') 
    {      
      if($scope.rooms_early_bird_items[index1]){
        $scope.rooms_early_bird_items[index1].push({'period' : ''});
      }
      else{
        if(parseInt($scope.old_index1)!=parseInt(index1)){
          $scope.rooms_early_bird_items1 = [];
        }
        $scope.old_index1 = index1;
        $scope.rooms_early_bird_items1.push({'period' : ''});
        $scope.rooms_early_bird_items[index1] =  $scope.rooms_early_bird_items1;
      }
    }
    else if(type== 'last_min') 
    {
      if($scope.rooms_last_min_items[index1]){
        $scope.rooms_last_min_items[index1].push({'period' : ''});
      }
      else{
        if($scope.old_index2!=index1){
          $scope.rooms_last_min_items1 = [];
        }
        $scope.old_index2 = index1;
        $scope.rooms_last_min_items1.push({'period' : ''});
        $scope.rooms_last_min_items[index1] =  $scope.rooms_last_min_items1;
      }
    }
  }

  $scope.rooms_availability_rules = [];
  $scope.rooms_availability_rules1 = [];
  $scope.old_index4 = '';
  $scope.rooms_is_custom_exits = '';

  $scope.rooms_add_availability_rule_edit = function(index) {
    //$scope.rooms_is_custom_exits = true;
    if($scope.rooms_availability_rules[index]){
      $scope.rooms_availability_rules[index].push({'type' : ''});
    }
    else{
      if($scope.old_index4!=index){
        $scope.rooms_availability_rules1 = [];
      }
      $scope.old_index4 = index;

      $scope.rooms_availability_rules1.push({'type' : ''});

      $scope.rooms_availability_rules[index] = $scope.rooms_availability_rules1;
    }
    
    setTimeout(function(){
      $scope.rooms_availability_datepickers(index);
    }, 20);

    // if(!$scope.$$phase){
    //   $scope.$apply();
    // }
  }
  
  $scope.rooms_remove_availability_rule = function(index,index1) {
    item = $scope.rooms_availability_rules[index1][index];
    
    type = 'availability_rules';
    if(item.id) {
      $('.'+type+'_wrapper').addClass('loading');
      $('button[type="submit"]').attr('disabled', true);
      $http.post(APP_URL+'/'+ADMIN_URL+'/rooms/multiple_delete_availability_rule/'+item.id, {}).then(function(response){
        $('.'+type+'_wrapper').removeClass('loading');
        $('button[type="submit"]').removeAttr('disabled');
      })
    }
    $scope.rooms_availability_rules[index1].splice(index, 1); 
    // if($scope.rooms_availability_rules[index1].length<=0){
    //   $scope.rooms_is_custom_exits = false;
    // }
    // if(!$scope.$$phase){
    //   $scope.$apply();
    // }
  }

  $scope.rooms_availability_rules_type_change = function(index,index1) {
    rule = $scope.rooms_availability_rules[index1][index];
    
    if(rule.type != 'custom')
    {
      this_elem = $("#rooms_availability_rules_"+index+"_type option:selected");
      start_date = this_elem.attr('data-start_date');
      end_date = this_elem.attr('data-end_date');
      $scope.rooms_availability_rules[index1][index].start_date = start_date;
      $scope.rooms_availability_rules[index1][index].end_date = end_date;
    }
  }


  $scope.add_availability_rule = function() {
    $scope.availability_rules.push({'type' : ''});
    setTimeout(function(){
      $scope.availability_datepickers();
    }, 20);
  }
  $scope.remove_availability_rule = function(index) {
    item = $scope.availability_rules[index];
    type = 'availability_rules';
    if(item.id != '' && item.id) {
      $('.'+type+'_wrapper').addClass('loading');
      $('button[type="submit"]').attr('disabled', true);
      $http.post(APP_URL+'/'+ADMIN_URL+'/rooms/delete_availability_rule/'+item.id, {}).then(function(response){
        $('.'+type+'_wrapper').removeClass('loading');
        $('button[type="submit"]').removeAttr('disabled');
      })
    }
    $scope.availability_rules.splice(index, 1); 
  }
  $scope.availability_rules_type_change = function(index) {
    rule = $scope.availability_rules[index];
    if(rule.type != 'custom')
    {
      this_elem = $("#availability_rules_"+index+"_type option:selected");
      start_date = this_elem.attr('data-start_date');
      end_date = this_elem.attr('data-end_date');
      $scope.availability_rules[index].start_date = start_date;
      $scope.availability_rules[index].end_date = end_date;
    }
  }
  $scope.availability_datepickers = function() {
    if(!$scope.availability_rules)
    {
      return;
    }
    $.each($scope.availability_rules, function(i, data){
      var start_date_element = $("#availability_rules_"+i+"_start_date");
      var end_date_element = $("#availability_rules_"+i+"_end_date");
      start_date_element.datepicker({
        'minDate':0,
        'dateFormat': datepicker_format,
        onSelect: function(date, obj){
          var start_date = start_date_element.datepicker('getDate'); 
          start_date.setDate(start_date.getDate() + 1); 
          end_date_element.datepicker('option', 'minDate',start_date );
          // end_date_element.trigger('focus');
        }
      })
      end_date_element.datepicker({
        'minDate':1,
        'dateFormat': datepicker_format
      })
      
    });
  }

  $scope.rooms_availability_datepickers = function(index) {
    if(!$scope.rooms_availability_rules[index])
    {
      return;
    }
    $.each($scope.rooms_availability_rules, function(i1, data1){
      $scope.i1 = i1;
      $.each(data1, function(i, data){
        var start_date_element = $("#rooms_availability_rules_"+$scope.i1+'_'+i+"_start_date");
        var end_date_element = $("#rooms_availability_rules_"+$scope.i1+'_'+i+"_end_date");
        start_date_element.datepicker({
          'minDate':0,
          'dateFormat': datepicker_format,
          onSelect: function(date, obj){
            var start_date = start_date_element.datepicker('getDate'); 
            start_date.setDate(start_date.getDate() + 1); 
            end_date_element.datepicker('option', 'minDate',start_date );
            // end_date_element.trigger('focus');
          }
        })
        end_date_element.datepicker({
          'minDate':1,
          'dateFormat': datepicker_format
        })
      });
    });
  }

  $scope.copy_data =function(data) {
    return angular.copy(data);
  }
  $(document).ready(function(){
    $scope.availability_datepickers();
    $scope.rooms_availability_datepickers(0);
  });



  $scope.add_price_rule = function(type) {
    if(type == 'length_of_stay')
    {
      new_period = $scope.length_of_stay_period_select;
      $scope.length_of_stay_items.push({'period' : new_period-0});
      $scope.length_of_stay_period_select = '';
    }
    else if(type== 'early_bird') 
    {
      $scope.early_bird_items.push({'period' : ''});
    }
    else if(type== 'last_min') 
    {
      $scope.last_min_items.push({'period' : ''});
    }
  }
  $scope.remove_price_rule = function(type, index) {
    if(type == 'length_of_stay') {
      item =$scope.length_of_stay_items[index];
      $scope.length_of_stay_items.splice(index, 1);
    }
    else if(type == 'early_bird') {
      item =$scope.early_bird_items[index];
      $scope.early_bird_items.splice(index, 1);
    }
    else if(type == 'last_min') {
      item =$scope.last_min_items[index];
      $scope.last_min_items.splice(index, 1);
    }
    if(item.id != '' && item.id) {
      $('.'+type+'_wrapper').addClass('loading');
      $('button[type="submit"]').attr('disabled', true);
      $http.post(APP_URL+'/'+ADMIN_URL+'/rooms/delete_price_rule/'+item.id, {}).then(function(response){
        $('.'+type+'_wrapper').removeClass('loading');
        $('button[type="submit"]').removeAttr('disabled');
      })
    }
  }
  $scope.length_of_stay_option_avaialble = function(option) {
    var found = $filter('filter')($scope.length_of_stay_items, {'period': option}, true);
    var found_text = $filter('filter')($scope.length_of_stay_items, {'period': ''+option}, true);
    return !found.length && !found_text.length;
  }
  $scope.add_availability_rule = function() {
    $scope.availability_rules.push({'type' : ''});
    setTimeout(function(){
      $scope.availability_datepickers();
    }, 20);
  }
  $scope.remove_availability_rule = function(index) {
    item = $scope.availability_rules[index];
    type = 'availability_rules';
    if(item.id != '' && item.id) {
      $('.'+type+'_wrapper').addClass('loading');
      $('button[type="submit"]').attr('disabled', true);
      $http.post(APP_URL+'/'+ADMIN_URL+'/rooms/delete_availability_rule/'+item.id, {}).then(function(response){
        $('.'+type+'_wrapper').removeClass('loading');
        $('button[type="submit"]').removeAttr('disabled');
      })
    }
    $scope.availability_rules.splice(index, 1); 
  }
  $scope.availability_rules_type_change = function(index) {
    rule = $scope.availability_rules[index];
    if(rule.type != 'custom')
    {
      this_elem = $("#availability_rules_"+index+"_type option:selected");
      start_date = this_elem.attr('data-start_date');
      end_date = this_elem.attr('data-end_date');
      $scope.availability_rules[index].start_date = start_date;
      $scope.availability_rules[index].end_date = end_date;
    }
  }
  $scope.availability_datepickers = function() {
    if(!$scope.availability_rules)
    {
      return;
    }
    $.each($scope.availability_rules, function(i, data){
      var start_date_element = $("#availability_rules_"+i+"_start_date");
      var end_date_element = $("#availability_rules_"+i+"_end_date");
      start_date_element.datepicker({
        'minDate':0,
        'dateFormat': datepicker_format,
        onSelect: function(date, obj){
          var start_date = start_date_element.datepicker('getDate'); 
          start_date.setDate(start_date.getDate() + 1); 
          end_date_element.datepicker('option', 'minDate',start_date );
          // end_date_element.trigger('focus');
        }
      })
      end_date_element.datepicker({
        'minDate':1,
        'dateFormat': datepicker_format
      })
      
    });
  }
  $scope.copy_data =function(data) {
    return angular.copy(data);
  }
  $(document).ready(function(){
    $scope.availability_datepickers();
  });

  $scope.show_bded_room =function(no){
    $scope.common_bed = false;
    angular.forEach($scope.bed_types_name,function(val,key){
        if(val.id==no){
            if($rootScope.show_bed_room[key])
                $rootScope.show_bed_room[key]=false;
            else
                $rootScope.show_bed_room[key]=true;
        }
        else
            $rootScope.show_bed_room[key]=false;
    });
    if($scope.show_bed_room.indexOf(true) !== -1) {
       $('.help_div').hide(); 
        $('.bedroom_div').show();
    }
    else{
        $('.help_div').show();
        $('.bedroom_div').hide();
    }
  }
  $scope.obj_size = function(obj){
    return Object.size(obj)
  }
  $scope.show_bed_icon =function(array,room_no,room){
    //console.log(array);
    var tot = 0;
    var total = 0;
    var j=0;
    var k=0;
    var over_total = 0;
    
    $('.icon_div').html('');
   
     angular.forEach(array,function(val,key){
         var icon = val.icon > 0 ? val.icon:'';
        var counts = val.count > 0 ? val.count:0;
        
        over_total +=counts-0;
     });
     if(over_total == 0){
      $('.no_data').removeClass('hide');
            $('.data_result').addClass('hide');
     }
     else{
        $('.no_data').addClass('hide');
         $('.data_result').removeClass('hide');
         $('.current_bed_room').text(room);
    $('.current_bed_room_count').text(room_no);
     }
    angular.forEach(array,function(val,key){
        var icon = val.icon > 0 ? val.icon:'';
        var counts = val.count > 0 ? val.count:0;
        
        total +=counts-0;
        tot++;
        var show_count = total-2+0;
        //console.log(over_total);
     
    // if(tot < 4){
        if(val.count > 0){
          
            for(j=1;j<=val.count;j++){
                k++;
                if(k < 4){
                    if(k == 3){
                       if(over_total <= 3){
                       $('.icon_div').append('<div class="bed-details_preview_item text-center col-sm-4" aria-hidden="true" role="presentation"><div class="bed-details_preview_item_icon"><img  class="icon_img"  src="'+val.icon+'"></span><span class="d-block">'+val.name+'</span></div></div>');  
                    }  
                    }
                    else{
                     $('.icon_div').append('<div class="bed-details_preview_item text-center col-sm-4" aria-hidden="true" role="presentation"><div class="bed-details_preview_item_icon"><img  class="icon_img"  src="'+val.icon+'"></span><span class="d-block">'+val.name+'</span></div></div>');  
                    }
                       
                   
                 }
                else if(k== 4){
                  $('.icon_div').append('<div class="bed-details__preview_item bed-details__preview_item--ellipsis va-top text-center text-babu col-sm-4"><span class="select_bed_count">'+show_count+'</span>+</div>'); 
                }else if(k >4){
                    $('.select_bed_count').text(show_count); 
                }
                //console.log(k);
            }

          
        }
        else{
            tot=tot-1;  
        }
              
     //}
    // else if(tot == 4 )
    //     if(val.count > 0){
    //          $('.icon_div').append('<div class="bed-details__preview_item bed-details__preview_item--ellipsis va-top text-center text-babu col-sm-4"><span class="select_bed_count">'+show_count+'+</span></div>'); 
    //         //tot++; 
    //     }
    //     else
    //         tot=tot-1; 
    
    // else if(tot > 4 )
    //      $('.select_bed_count').text(show_count); 
         //tot++;   
      //$('.icon_div').append('<div class="bed-details__preview_item bed-details__preview_item--ellipsis va-top text-center text-babu col-sm-4"><span class="select_bed_count">'+show_count+'+</span></div>'); 
        
   
    });
    //console.log(tot);
    return tot;
  }
  $scope.beds1 = [];
  $scope.bed_type_item_available = function(bed_id, array_name)
  {
    var available = true;
    angular.forEach(array_name,function(key,val){
        if(bed_id==key.id){
            available = false; 
        }
    });
    
    return available;
  }

  $scope.add_bed_types = function(n,selected)
  { 
    angular.forEach($scope.all_bed_type, function(item){
    if(item.id==selected){
        $scope.bed_types_name[n].push({'id':item.id,'name':item.name,'count':1,'icon':item.icon});
        //$scope.save_room_types();
    }
    });
    $scope.beds1[n]='';
    //console.log($scope.beds1[n]);
    if(!$scope.$$phase) {
      $scope.$apply();
    }
     // $('#1asfasdbasics-select-bed_type_'+n).val("");
    return false;
  }
  //Add beds in depends on bedrooms
  $scope.total_bed_count =function(array){
    var tot = 0;
    angular.forEach(array,function(val,key){
        var counts = val.count > 0 ? val.count:0;
        tot +=counts-0;
    });
    return tot;
  }
  $scope.show_bded_room =function(no){
      $scope.common_bed = false;

      angular.forEach($scope.bed_types_name,function(val,key){
          if(key==no){
              if($rootScope.show_bed_room[key])
                  $rootScope.show_bed_room[key]=false;
              else
                  $rootScope.show_bed_room[key]=true;
          }
          else
              $rootScope.show_bed_room[key]=false;
      });
      if($scope.show_bed_room.indexOf(true) !== -1) {
         $('.help_div').hide(); 
          $('.bedroom_div').show();
      }
      else{
          $('.help_div').show();
          $('.bedroom_div').hide();
      }
  }
  $scope.bedrooms_changes = function(){
    //alert("ji");
    var sdf= $scope.bed_types_name;
    var size = Object.size(sdf);
    //console.log($scope.bed_types_name);
    if($scope.bedrooms < size){ 
       for (var i = 0; i < size; i++) {
           if(i > ($scope.bedrooms-1) ){
              delete $scope.bed_types_name[i+1];
           }
       }
    }
    else{
        for (var i = 0; i <= $scope.bedrooms; i++) {
            if(i > (size) ){
              $scope.bed_types_name[i]=angular.copy($scope.get_single_bed_type);
            }
        }
         
    } 

  }
  Object.size = function(obj) {
    var size = 0, key;
    for (key in obj) {
        if (obj.hasOwnProperty(key)) size++;
    }
    return size;
  };
  $scope.show_common_bded_room =function(){
      if($scope.common_bed)
          $scope.common_bed = false;
      else
          $scope.common_bed = true;

      angular.forEach($scope.bed_types_name,function(val,key){
           $rootScope.show_bed_room[key]=false;
      })
      if($scope.common_bed) {
         $('.help_div').hide(); 
          $('.bedroom_div').show();
      }
      else{
          $('.help_div').show();
          $('.bedroom_div').hide();
      }
  }
  $scope.add_common_bed_types = function(selected)
  {
    angular.forEach($scope.all_bed_type, function(item){
    if(item.id==selected){
        $scope.get_common_bed_type.push({'id':item.id,'name':item.name,'count':1,'icon':item.icon});
        //$scope.save_common_room_types();
    }
    });
    $scope.common_beds ='';
    //console.log($scope.common_beds);
    if(!$scope.$$phase) {
      $scope.$apply();
    }

   return false;
  }
  $scope.show_common_bded_room =function(){
    if($scope.common_bed)
        $scope.common_bed = false;
    else
        $scope.common_bed = true;

    angular.forEach($scope.bed_types_name,function(val,key){
         $rootScope.show_bed_room[key]=false;
    })
    if($scope.common_bed) {
       $('.help_div').hide(); 
        $('.bedroom_div').show();
    }
    else{
        $('.help_div').show();
        $('.bedroom_div').hide();
    }
  }
}]);