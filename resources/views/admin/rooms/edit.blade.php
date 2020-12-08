@extends('admin.template')
@section('main')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" ng-controller="rooms_admin" ng-init="multiple_rooms = {{json_encode($multiple_rooms)}};multiple_room_images = {{json_encode($multiple_room_images)}};amenities = {{json_encode($amenities)}};type='{{ $result->type }}';">
 
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Edit Room
    </h1>
    <ol class="breadcrumb">
      <li>
        <a href="../dashboard">
          <i class="fa fa-dashboard">
          </i>
          Home
        </a>
      </li>
      <li>
        <a href="../rooms">
          Rooms
        </a>
      </li>
      <li class="active">
        Edit
      </li>
    </ol>
  </section>
  <!-- Main content -->
  <section class="content">
    <div class="row">
      <!-- right column -->
      <div class="col-md-12">
        <!-- Horizontal Form -->
        <div class="box box-info">
          <div class="box-header with-border">
            <h3 class="box-title">
              Edit Room Form
            </h3>
          </div>
          <!-- /.box-header -->
          <!-- form start -->
          <div class="box-header with-border">
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_1" ng-click="go_to_edit_step(1)" disabled>
              Calendar
            </a>
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_2" ng-click="go_to_edit_step(2)">
              Basics
            </a>
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_3" ng-click="go_to_edit_step(3)">
              Description
            </a>
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_4" ng-click="go_to_edit_step(4)">
              Location
            </a>
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_5" ng-click="go_to_edit_step(5)" ng-hide="type=='Multiple'">
              Amenities
            </a>
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_6" ng-click="go_to_edit_step(6)">
              Photos
            </a>
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_7" ng-click="go_to_edit_step(7)">
              Video
            </a>
             @if($result->type != 'Multiple')
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_8" ng-click="go_to_edit_step(8)">
              Pricing
            </a>
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_11" ng-click="go_to_edit_step(11)">
              Price Rules
            </a>
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_12" ng-click="go_to_edit_step(12)">
              Availability Rules
            </a>
            @endif
            @if($result->type == 'Multiple')
                        <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_8" ng-click="go_to_edit_step(8)">
                            Multipe Rooms
                        </a>
                        @endif
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_9" ng-click="go_to_edit_step(9)">
              Booking Type
            </a>
            
            <a href="javascript:void(0);" class="btn btn-warning tab_btn" id="tab_btn_10" ng-click="go_to_edit_step(10)">
              Terms
            </a>
          </div>
          {!! Form::open(['url' => ADMIN_URL.'/edit_room/'.$room_id, 'class' => 'form-horizontal', 'id' => 'add_room_form', 'files' => true, 'ng-cloak' => 'ng-cloak']) !!}   
          <input type="hidden" value="{{ $room_id }}" name="room_id" id="room_id">
          
         <!--  <div id="sf1" class="frm">
             <fieldset class="box-body">
              <div id="ajax_container" class="iccon" ng-init="calendar_data={{ json_encode($calendar) }}">
                <div id="calendar" class="calendar">
                </div>
              </div>
            </fieldset>

        </div> -->
<div id="sf1" class="frm">

  <fieldset class="box-body">
    @if($result->type == 'Single')
              <div id="ajax_container" class="iccon" ng-init="calendar_data={{ json_encode($calendar) }}">
                <div id="calendar" class="calendar">
                </div>
              </div>
                @else
                <div class="form-group">
                          <div class="col-md-9 col-sm-7"></div>
                          <div class="col-md-3 col-sm-5">

                            @if($sub_rooms1 != '' && count(@$sub_rooms1)>0)
                            <div class="select_custom">
                                <select name="calendars" id="multiple_calendar" class="form-control input-sm minimal" style="height: 36px!important;" >
                                  @foreach($sub_rooms1 as $key=>$rooms)
                                  <option value="{{$key}}">{{$rooms}}</option>
                                  @endforeach            
                              </select>
                          </div>
                          @endif
                      </div>
                  </div>

        <div id="ajax_container" class="iccon" ng-init="calendar_data={{ json_encode($calendar[0]) }}">
          <div id="calendar" class="calendar">
                </div>
              </div>
                @endif
                
            
            </fieldset>

      
            

</div>
         
          <div id="sf2" class="frm edit-photo-list" >
            <p class="text-danger">
              (*)Fields are Mandatory
            </p>
            <fieldset class="box-body">
              <p class="text-success text-bold">
      Listing
    </p>

    <div class="form-group">
      <label for="property_type" class="col-sm-3 col-xs-6 control-label">
        Property Type
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6 col-xs-6 ">
        {!! Form::select('property_type', $property_type, $result->property_type, ['class' => 'form-control', 'id' => 'property_type', 'placeholder' => 'Select...']) !!}
      </div>
    </div>



     <!-- <div class="form-group">
                  <label for="property_type" class="col-sm-3 control-label">Type of Listing
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6 cls_txtadmin">
                          <div class="col-sm-6" style="padding: 5px 0;">
                            <input type="radio" name="type" value="Single" ng-model="type" @if($result->type=='Single') checked @endif><span>Single</span>
                          </div>
                          <div class="col-sm-6">
                            <input type="radio" name="type" ng-model="type" id="type" @if($result->type=='Multiple') checked @endif value="Multiple"><span>Multiple room </span>
                          </div>
                        </div>
                </div>
 -->
 @if($result->type != 'Multiple')
    <div class="form-group">
      <label for="room_type" class="col-sm-3 col-xs-6 control-label">
        Room Type
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6 col-xs-6 ">
        {!! Form::select('room_type', $room_type, $result->room_type, ['class' => 'form-control', 'id' => 'room_type', 'placeholder' => 'Select...']) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="accommodates" class="col-sm-3 col-xs-6 control-label">
        Accommodates
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6 col-xs-6 ">
        {!! Form::select('accommodates', $accommodates, $result->accommodates, ['class' => 'form-control', 'id' => 'accommodates', 'placeholder' => 'Select...']) !!}
      </div></div>

              <p class="text-success text-bold" ng-hide="type=='Multiple'">
                Rooms and Beds
              </p>

              <div class="form-group" ng-init="bed_types_name ={{json_encode($first_bed_type1)}};bathrooms={{json_encode($get_bathrooms)}};firstbedtypeid={{$firstbedtypeid}};bedrooms={{ $result->bedrooms ? $result->bedrooms : '0' }};all_bed_type={{$bed_type}};get_single_bed_type = {{json_encode($get_single_bed_type)}};get_common_bed_type = {{json_encode($get_common_bed_type)}};get_common_bathrooms = {{json_encode($get_common_bathrooms)}}">
                <label for="bedrooms" class="col-sm-3 col-xs-6 control-label">
                  Bedrooms
                  <em class="text-danger">
                    *
                  </em>
                </label>
                <div class="col-sm-6 col-xs-6" ng-hide="type=='Multiple'">
                  <!-- {!! Form::select('bedrooms', $bedrooms, $result->bedrooms, ['class' => 'form-control', 'id' => 'bedrooms', 'placeholder' => 'Select...','ng-model' => 'bedrooms','ng-change' => 'bedrooms_changes']) !!} -->
                  <select id="bedrooms" name="bedrooms" ng-model="bedrooms" ng-change="bedrooms_changes();">
                    @for($i=0; $i<=10;$i++)
                    <option value="{{$i}}"> {{$i}}</option>
                    @endfor
                  </select>
                </div>
              </div>
              <div class="bed_room_types i" ng-if="bedrooms>0"  ng-repeat="(room_no,all_beds) in bed_types_name | orderObjectBy:'room_no'"  ng-init="show_bed_room[room_no]=0;" ng-hide="room_no==0">
                <div class="form-group">
                  <!--   @{{all_beds}} -->
                  <label for="bedrooms" class="col-sm-3 col-xs-6  control-label"> 
                    Bedroom @{{room_no}}
                  </label>
                  <div class="col-sm-6 col-xs-6">
                    <span ng-click="show_bded_room(room_no);show_bed_icon(bed_types_name[room_no],room_no,'bedroom');" style="float: right;" class="btn btn-primary">

                      <span ng-if="total_bed_count(bed_types_name[room_no]) > 0" ng-show="!show_bed_room[room_no]"> Edit Beds </span>
                      <span ng-if="total_bed_count(bed_types_name[room_no]) < 1" ng-show="!show_bed_room[room_no]"> Add Beds </span>
                      <span  ng-show="show_bed_room[room_no]"> Done </span>
                    </span>
                  </div>
                </div>
                <div class="form-group">
                 <label for="bedrooms" class="col-sm-3 col-xs-6 control-label pt-0">@{{total_bed_count(bed_types_name[room_no])}} bed<span ng-if="total_bed_count(bed_types_name[room_no]) > 1">s</span> </label>
                 <div class="col-sm-6 col-xs-6 " ng-show="!show_bed_room[room_no]"> 
                  <span ng-repeat="(ids,bed_name) in bed_types_name[room_no] | nonZeroElem : 'count'" ng-show="bed_name.count" class="cls_bed_span"> &nbsp;@{{bed_name.count}} @{{bed_name.name}}<span ng-hide="$last">,</span> </span>
                </div>
              </div>

              <div class="row-space-2 moreopt" id="beds_show" ng-show="show_bed_room[room_no]" style="border-bottom: 0px solid #ccc;">
                <div class="form-group">
                  <div class="form-group label-values" ng-repeat="(key,beds_id) in all_beds">
                    <label for="bedrooms" class="col-sm-3 col-xs-6  control-label cls_txtadmin_lable">
                      @{{ beds_id.name}} 
                    </label>
                    <div class="col-sm-6 col-xs-6 text-left cls_mar_left5" data="@{{beds_id.id}}">
                     <div class="value-btn-wrap">
                      <button ng-disabled="beds_id.count==0" class="value-button" id="decrease" ng-click="beds_id.count=beds_id.count-1;save_room_types();show_bed_icon(bed_types_name[room_no],room_no,'bedroom');bed_count_valid();" value="Decrease Value" type="button">-</button>
                      <input type="hidden" id="bed_count" name="bed_count[]" value="@{{room_no}}">
                      <input type="hidden" id="bed_id" name="bed_id[]" value="@{{beds_id.id}}">
                      <input type="text" class="guest-select" id="bedtype" name="bed_types_name[]" ng-model="beds_id.count"  readonly="readonly" onfocus="this.blur()" />
                      <button ng-disabled="beds_id.count == 5" class="value-button" id="increase" ng-click="beds_id.count=beds_id.count-0+1;save_room_types();show_bed_icon(bed_types_name[room_no],room_no,'bedroom');bed_count_valid();" value="Increase Value" type="button">+</button>
                    </div>
                  </div>
                </div>

                <div id="form-group bedtype-select" ng-hide="all_bed_type.length - (obj_size(all_beds)-1) <= 0"> <label for="bedrooms" class="col-sm-3 col-xs-6  control-label"></label><div class="col-sm-6 col-xs-6 ">
                  <select ng-init="beds1[room_no]='';" ng-change="add_bed_types(room_no,beds1[room_no]);show_bed_icon(bed_types_name[room_no],room_no,'bedroom');" ng-model="beds1[room_no]" id="1asfasdbasics-select-bed_type_@{{room_no}}" name="bed_type1" data-saving="basics1">
                    <option value="" selected="" disabled="">{{ trans('messages.lys.select') }}...</option>
                    <option ng-repeat="beds in all_bed_type track by $index" value="@{{beds.id }}" ng-if="bed_type_item_available(beds.id,all_beds)">@{{ beds.name }}@{{key}}</option>
                  </select>
                </div>
              </div>
            </div>
          </div>
        </div>

        <div class="bed_room_types">
         <div class="form-group">
          <!--   @{{all_beds}} -->
          <label for="bedrooms" class="col-sm-3 col-xs-6 control-label"> 
           Common Space 
         </label>
         <div class="col-sm-6 col-xs-6 ">
           <span ng-click="show_common_bded_room();show_bed_icon(get_common_bed_type,'','common space');" style="float: right;" class="btn btn-primary">
            <span ng-if="total_bed_count(get_common_bed_type) > 0" ng-show="!common_bed"> Edit Beds </span>
            <span ng-if="total_bed_count(get_common_bed_type) < 1" ng-show="!common_bed"> Add Beds </span>
            <span  ng-show="common_bed"> Done </span>
          </span>
          </div>
        </div>
        <div class="form-group">
          <div class="form-group">  
            <label for="bedrooms" class="col-sm-3 col-xs-6 control-label pt-0">@{{total_bed_count(get_common_bed_type)}}  bed<span ng-if="total_bed_count(get_common_bed_type) > 1">s</span></label>
            <div class="col-sm-6 col-xs-6" ng-show="!common_bed"> 
              <span ng-repeat="bed_name in get_common_bed_type | filter:myFilters1 |toArrayView | nonZeroElem : 'count'" > 
                <span ng-show="bed_name.count" class="cls_bed_span"> @{{bed_name.count}} @{{bed_name.name}}</span><span ng-show="bed_name.count " class="cls_bed_span"><span ng-hide="$last">,</span> </span></span>
              </div>
            </div>
            <div class="row-space-2 moreopt" id="beds_show" ng-init="common_bed=0" ng-show="common_bed" style="border-bottom: 0px solid #ccc;">
              <div class="form-group">
                <div class="form-group label-values" ng-repeat="(key,beds_id) in get_common_bed_type | toArray">
                  <label for="bedrooms" class="col-sm-3 col-xs-6 control-label cls_txtadmin_lable">
                    @{{ beds_id.name}} 
                  </label>

                  <div class="col-sm-6 col-xs-6 text-left cls_mar_left5"  data="@{{beds_id.id}}">
                   <div class="value-btn-wrap">
                    <button ng-disabled="beds_id.count==0" class="value-button" id="decrease" ng-click="beds_id.count=beds_id.count-1;show_bed_icon(get_common_bed_type,'','common space ');" value="Decrease Value" type="button">-</button>
                    <input type="hidden" id="common_bed_count" name="common_bed_count[]" value="common">
                    <input type="hidden" id="common_bed_id" name="common_bed_id[]" value="@{{beds_id.id}}">
                    <input type="text" class="guest-select" id="bedtype" name="common_bed_types_name[]" ng-model="beds_id.count"  readonly="readonly" onfocus="this.blur()" />
                    <button ng-disabled="beds_id.count == 5" class="value-button" id="increase" ng-click="beds_id.count=beds_id.count-0+1;show_bed_icon(get_common_bed_type,'','common space ');" value="Increase Value" type="button">+</button>
                  </div>
                </div>
              </div>

              <div id="form-group bedtype-select" ng-hide="all_bed_type.length-obj_size(get_common_bed_type) <= 0" > <label for="bedrooms" class="col-sm-3 col-xs-6  control-label"></label><div class="col-sm-6 col-xs-6 ">
                <select ng-init="common_beds='';" ng-change="add_common_bed_types(common_beds);show_bed_icon(get_common_bed_type,'','common space ');" ng-model="common_beds" id="1asfasdbasics-select-bed_type"  name="common_bed_type1" data-saving="basics1">
                  <option value="" selected="" disabled="">{{ trans('messages.lys.select') }}...</option>
                  <option ng-repeat="beds in all_bed_type track by $index" value="@{{beds.id }}" ng-if="bed_type_item_available(beds.id,get_common_bed_type)" >@{{ beds.name }}@{{key}}</option>
                </select>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>

    <div class="bed_room_types">
      <div class="form-group label-values" ng-init='bathrooms={{($result->bathrooms==null?0:$result->bathrooms)}}'>
        <label for="bathrooms" class="col-sm-3 col-xs-6 control-label cls_txtadmin_lable">
          Bathrooms
        </label>
        <div class="col-sm-6 col-xs-6 text-left cls_mar_left5">
         <div class="value-btn-wrap">
          <button ng-disabled="bathrooms==0" class="value-button" id="decrease" ng-click="bathrooms=bathrooms-0.5;" value="Decrease Value" type="button">-</button>
          <input type="text" class="guest-select" id="bathrooms" name="bathrooms" ng-model="bathrooms"  readonly="readonly" onfocus="this.blur()" />
          <button ng-disabled="bathrooms == 10" class="value-button" id="increase" ng-click="bathrooms=bathrooms+0.5;" value="Increase Value" type="button">+</button>
        </div>
      </div>
    </div>
    <div class="form-group">
      <label for="bathrooms" class="col-sm-3 col-xs-6  control-label cls_txtadmin_lable">
        Are any of the bathrooms private?
      </label>
      <div>
        <div class="col-sm-6 col-xs-6  cls_txtadmin">
          <div class="col-sm-12 col-xs-12  text-left cls_mar_left51">
            <input type="radio" name="bathroom_shared" value="No" @if($result->bathroom_shared==null || $result->bathroom_shared=='No') checked @endif>
            <span>Yes </span></div>
            <div class="col-sm-12 col-xs-12 text-left cls_mar_left51">
              <input type="radio" name="bathroom_shared" value="Yes" @if($result->bathroom_shared=='Yes') checked @endif> <span>No,they're shared </span>
            </div>
          </div>
        </div>
      </div>
    </div>
     @endif
    
  </fieldset>
  <div class="box-footer">
    <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel">
      Cancel
    </a>
    <button class="btn btn-info pull-right" type="submit" name="submit" value="basics">
      Submit
    </button>
  </div>
</div>
<div id="sf3" class="frm">
  <p class="text-danger">
    (*)Fields are Mandatory
  </p>
  <fieldset class="box-body">
    <!--change-->
    <div class="form-group" >
      <label for="language" class="col-sm-3 control-label">
        Language
      </label>
      <div class="col-sm-6">
        {!! Form::select('language', $language, 'en', ['class' => 'form-control go', 'id' => 'language','disabled']) !!}
      </div>
    </div>
    <!--end change-->
    <div class="form-group">
      <label for="name" class="col-sm-3 control-label">
        Listing Name
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6">
        {!! Form::text('name[]', $result->name, ['class' => 'form-control', 'id' => 'name', 'placeholder' => 'Be clear and descriptive']) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="summary" class="col-sm-3 control-label">
        Summary
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6">
        {!! Form::textarea('summary[]', $result->summary, ['class' => 'form-control', 'id' => 'summary', 'placeholder' => 'Tell travelers what you love about the space. You can include details about the decor, the amenities it includes, and the neighborhood.', 'rows' => 5]) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="space" class="col-sm-3 control-label">
        Space
      </label>
      <div class="col-sm-6">
        {!! Form::textarea('space[]', $result->rooms_description->space, ['class' => 'form-control', 'id' => 'space', 'rows' => 5]) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="access" class="col-sm-3 control-label">
        Guest Access
      </label>
      <div class="col-sm-6">
        {!! Form::textarea('access[]', $result->rooms_description->access, ['class' => 'form-control', 'id' => 'access', 'rows' => 5]) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="interaction" class="col-sm-3 control-label">
        Interaction with Guests
      </label>
      <div class="col-sm-6">
        {!! Form::textarea('interaction[]', $result->rooms_description->interaction, ['class' => 'form-control', 'id' => 'interaction', 'rows' => 5]) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="notes" class="col-sm-3 control-label">
        Other Things to Note
      </label>
      <div class="col-sm-6">
        {!! Form::textarea('notes[]', $result->rooms_description->notes, ['class' => 'form-control', 'id' => 'notes', 'rows' => 5]) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="house_rules" class="col-sm-3 control-label">
        House Rules
      </label>
      <div class="col-sm-6">
        {!! Form::textarea('house_rules[]', $result->rooms_description->house_rules, ['class' => 'form-control', 'id' => 'house_rules', 'rows' => 5]) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="neighborhood_overview" class="col-sm-3 control-label">
        Overview
      </label>
      <div class="col-sm-6">
        {!! Form::textarea('neighborhood_overview[]', $result->rooms_description->neighborhood_overview, ['class' => 'form-control', 'id' => 'neighborhood_overview', 'rows' => 5]) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="transit" class="col-sm-3 control-label">
        Getting Around
      </label>
      <div class="col-sm-6">
        {!! Form::textarea('transit[]', $result->rooms_description->transit, ['class' => 'form-control', 'id' => 'transit', 'rows' => 5]) !!}
      </div>
    </div>
    <!--change-->
    <div ng-repeat="choice_check in rows">
      <div class="form-group" >
        <label for="language" class="col-sm-3 control-label">
          Language
        </label>
        <div class="col-sm-6">
          <select class="go" ng-model="choice_check.language" name="language[]" id="language@{{ $index }}" data-index="@{{ $index }}">
            <option value="">
              Select
            </option>
            <option ng-repeat="item in lang_list" value="@{{ item.value }}" ng-selected="item.value == choice_check.lang_code" >
              @{{ item.name }}
            </option>
          </select>
        </div>
      </div>
      <div class="form-group">
        <label for="name" class="col-sm-3 control-label">
          Listing Name
          <em class="text-danger">
            *
          </em>
        </label>
        <div class="col-sm-6">
          <input type="text"  class="form-control" id="name" name="name[]" ng-model="choice_check.name" placeholder="Be clear and descriptive" data-index="@{{ $index }}" >
        </div>
      </div>
      <div class="form-group">
        <label for="summary" class="col-sm-3 control-label">
          Summary
          <em class="text-danger">
            *
          </em>
        </label>
        <div class="col-sm-6">
          <textarea name="summary[]"  class="form-control" id="summary" placeholder="Tell travelers what you love about the space. You can include details about the decor, the amenities it includes, and the neighborhood." rows="5" ng-model="choice_check.summary" data-index="@{{ $index }}">
          </textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="space" class="col-sm-3 control-label">
          Space
        </label>
        <div class="col-sm-6">
          <textarea name="space[]"  class="form-control" id="space" rows="5" ng-model="choice_check.space" data-index="@{{ $index }}">
          </textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="access" class="col-sm-3 control-label">
          Guest Access
        </label>
        <div class="col-sm-6">
          <textarea name="access[]"  class="form-control" id="space" rows="5" ng-model="choice_check.access" data-index="@{{ $index }}">
          </textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="interaction" class="col-sm-3 control-label">
          Interaction with Guests
        </label>
        <div class="col-sm-6">
          <textarea name="interaction[]"  class="form-control" id="interaction" rows="5" ng-model="choice_check.interaction" data-index="@{{ $index }}">
          </textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="notes" class="col-sm-3 control-label">
          Other Things to Note
        </label>
        <div class="col-sm-6">
          <textarea name="notes[]"  class="form-control" id="notes" rows="5" ng-model="choice_check.notes" data-index="@{{ $index }}">
          </textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="house_rules" class="col-sm-3 control-label">
          House Rules
        </label>
        <div class="col-sm-6">
          <textarea name="house_rules[]"  class="form-control" id="house_rules" rows="5" ng-model="choice_check.house_rules" data-index="@{{ $index }}">
          </textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="neighborhood_overview" class="col-sm-3 control-label">
          Overview
        </label>
        <div class="col-sm-6">
          <textarea name="neighborhood_overview[]"  class="form-control" id="neighborhood_overview" rows="5" ng-model="choice_check.neighborhood_overview" data-index="@{{ $index }}">
          </textarea>
        </div>
      </div>
      <div class="form-group">
        <label for="transit" class="col-sm-3 control-label">
          Getting Around
        </label>
        <div class="col-sm-6">
          <textarea name="transit[]"  class="form-control" id="transit" rows="5" ng-model="choice_check.transit" data-index="@{{ $index }}">
          </textarea>
        </div>
      </div>
      <a class="pull-right btn remove-room-edit-btn" href="javascript:void(0);" ng-click="removeRow($index)">
        <span> Remove</span>
      </a>
      <br>
    </div>
    <a class="add-room-edit-btn btn"  href="javascript:void(0);" ng-click="addNewRow()">
      Add
    </a>
    <!--end change-->
  </fieldset>
  <div class="box-footer">
    <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel" >
      Cancel
    </a>
    <button class="btn btn-info pull-right" type="submit" name="submit" value="description">
      Submit
    </button>
  </div>
</div>
<div id="sf4" class="frm">
  <p class="text-danger">
    (*)Fields are Mandatory
  </p>
  <fieldset class="box-body">
    <div class="form-group">
      <label for="country" class="col-sm-3 control-label">
        Country
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6">
        {!! Form::select('country', $country, $result->rooms_address->country, ['class' => 'form-control', 'id' => 'country', 'placeholder' => 'Select...']) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="address_line_1" class="col-sm-3 control-label">
        Address Line 1
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6">
        {!! Form::text('address_line_1', $result->rooms_address->address_line_1, ['class' => 'form-control', 'id' => 'address_line_1', 'placeholder' => 'House name/number + street/road', 'autocomplete' => 'off']) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="address_line_2" class="col-sm-3 control-label">
        Address Line 2
      </label>
      <div class="col-sm-6">
        {!! Form::text('address_line_2', $result->rooms_address->address_line_2, ['class' => 'form-control', 'id' => 'address_line_2', 'placeholder' => 'Apt., suite, building access code']) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="city" class="col-sm-3 control-label">
        City / Town / District
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6">
        {!! Form::text('city', $result->rooms_address->city, ['class' => 'form-control', 'id' => 'city', 'placeholder' => '']) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="state" class="col-sm-3 control-label">
        State / Province / County / Region
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6">
        {!! Form::text('state', $result->rooms_address->state, ['class' => 'form-control', 'id' => 'state', 'placeholder' => '']) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="postal_code" class="col-sm-3 control-label">
        ZIP / Postal Code
      </label>
      <div class="col-sm-6">
        {!! Form::text('postal_code', $result->rooms_address->postal_code, ['class' => 'form-control', 'id' => 'postal_code', 'placeholder' => '']) !!}
      </div>
    </div>
    <input type="hidden" name="latitude" id="latitude" class="do-not-ignore">
    <input type="hidden" name="longitude" id="longitude">
  </fieldset>
  <div class="box-footer">
    <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel">
      Cancel
    </a>
    <button class="btn btn-info pull-right" type="submit" name="submit" value="location">
      Submit
    </button>
  </div>
</div>
<div id="sf5" class="frm">
  <fieldset class="box-body">
    @foreach($amenities_type as $amenity_type)
    <h4>{{$amenity_type->name}}</h4>
    <ul class="list-unstyled" id="triple">
      @foreach($amenities as $row)
      @if(@$row->type_id == @$amenity_type->id)
      <li>
        <span class="label-large label-inline amenity-label check-amenity-label pull-left" style="width:100% !important;">
          <label>
            <input class="pull-left" type="checkbox" value="{{ $row->id }}" name="amenities[]" {{ in_array($row->id, $prev_amenities) ? 'checked' : '' }}>
            <span class="pull-left" style="margin-left:8px;white-space:normal;">
              {{ $row->name }}
            </span>
          </label>
        </span>
      </li>
      @endif
      @endforeach
    </ul>
    @endforeach
  </fieldset>
  <div class="box-footer">
    <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel" >
      Cancel
    </a>
    <button class="btn btn-info pull-right" type="submit" name="submit" value="amenities">
      Submit
    </button>
  </div>
</div>
<div id="sf6" class="frm edit-photo-list">
  <p class="text-danger">
    (*)Fields are Mandatory
  </p>
  <fieldset class="box-body">
    <div class="form-group">
      <label for="night" class="col-sm-3 control-label">
        Photos
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6">
        <input type="file" name="photos[]" multiple="true" id="upload_photos" >
      </div>
      <span class="text-success text-bold" style="display:none;" id="saved_message">
        Saved..
      </span>
    </div>
    <ul class="row list-unstyled sortable all-slides" id="js-photo-grid">
      @foreach($rooms_photos as $row)
      <li id="photo_li_{{ $row->id }}" class="col-4 col-lg-3 row-space-4 ng-scope slide photo_drag_item cls_photosize">
        <div class="card photo-item">
          <div id="photo-5" class="photo-size photo-drag-target js-photo-link">
          </div>
          <a href="#" class="media-photo media-photo-block text-center photo-size">
            <input type ='hidden' id="hidden_image" name='hidden_image[]' value="{{ $row->original_name}}">
            <img alt="" class="img-responsive-height" src="{{ $row->name }}">
          </a>
          <button class="delete-photo-btn overlay-btn js-delete-photo-btn" data-photo-id="{{ $row->id }}" type="button">
            <i class="fa fa-trash" style="color:white;">
            </i>
          </button>
          <div class="panel-body panel-condensed">
            <textarea tabindex="1" class="input-large highlights ng-pristine ng-untouched ng-valid" id="hidden_high" name='hidden_high[]' data-photo-id="{{ $row->id }}" placeholder="What are the highlights of this photo?" rows="3" name="5">{{ $row->highlights }}</textarea>
          </div>
        </div>
      </li>
      @endforeach
      <input type ='hidden' id="hidden_image" name='hidden_image[]' value="">
    </ul>
  </fieldset>
  <div class="box-footer">
    <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel" >
      Cancel
    </a>
    <button class="btn btn-info pull-right" type="submit" name="submit" value="photos">
      Submit
    </button>
  </div>
</div>
<div id="sf7" class="frm">
  <div class="js-saving-progress saving-progress" style="display: none;">
    <h5>
      {{ trans('messages.lys.saving') }}...
    </h5>
  </div>
  <div class="js-saving-progress icon-rausch error-value-required row-space-top-1" id="video_error" style="display: none;float:right">
    <h5>
      {{ trans('messages.lys.video_error_msg') }}
    </h5>
  </div>
  <fieldset class="box-body">
    <div class="form-group">
      <label for="video" class="col-sm-3 control-label">
        YouTube URL
      </label>
      <div class="col-sm-6">
        {!! Form::text('video', $result->video, ['class' => 'form-control', 'id' => 'video']) !!}
      </div>
    </div>
  </fieldset>
  <fieldset class="box-body">
    <div class="row">
      <div class="col-md-2">
      </div>
      <div class="col-md-8 @if($result->video == '') hide @endif">
        <iframe src="{{$result->video}}?showinfo=0" style="width:100%; height:250px;" id="rooms_video_preview"   allowfullscreen="allowfullscreen" mozallowfullscreen="mozallowfullscreen" msallowfullscreen="msallowfullscreen" oallowfullscreen="oallowfullscreen" webkitallowfullscreen="webkitallowfullscreen">
        </iframe>
      </div>
      <div class="col-md-2">
      </div>
    </div>
  </fieldset>
  <div class="box-footer">
    <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel" >
      Cancel
    </a>
    <button class="btn btn-info pull-right" type="submit" name="submit" value="video">
      Submit
    </button>
  </div>
</div>
<div id="sf8" class="frm">
  <p class="text-danger">
    (*)Fields are Mandatory
  </p>
  <div class="add_room_but">
    <a class="btn edit-room-add-btn"  href="javascript:void(0);" ng-click="addNewRooms()"> Add
    </a>
  </div>
  @if($result->type == 'Multiple')
        <!--  <p class="text-danger">
            Note(photo upload): Maximum 10 MB
        </p> -->
    @endif
     @if($result->type == 'Single')
  <fieldset class="box-body">
    <div class="form-group">
      <label for="night" class="col-sm-3 control-label">
        Night
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6">
        {!! Form::text('night', $result->rooms_price->original_night, ['class' => 'form-control', 'id' => 'night', 'placeholder' => '']) !!}
        <span id="price_wrong_message" class="text-danger">
        </span>
      </div>
    </div>
    <div class="form-group">
      <label for="currency_code" class="col-sm-3 control-label">
        Currency Code
        <em class="text-danger">
          *
        </em>
      </label>
      <div class="col-sm-6">
        {!! Form::select('currency_code', $currency, $result->rooms_price->currency_code, ['class' => 'form-control', 'id' => 'currency_code', 'placeholder' => 'Select...']) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="cleaning" class="col-sm-3 control-label">
        Cleaning
      </label>
      <div class="col-sm-6">
        {!! Form::text('cleaning', $result->rooms_price->original_cleaning, ['class' => 'form-control', 'id' => 'cleaning', 'placeholder' => '']) !!}
      </div>
    </div>
    <div class="form-group additional_guest_form_group">
      <label for="guests" class="col-sm-3 control-label">
        Additional Guests
      </label>
      <div class="col-sm-6">
        {!! Form::select('guests', $accommodates, $result->rooms_price->guests, ['class' => 'form-control', 'id' => 'guests']) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="security" class="col-sm-3 control-label">
        Security
      </label>
      <div class="col-sm-6">
        {!! Form::text('security', $result->rooms_price->original_security, ['class' => 'form-control', 'id' => 'security', 'placeholder' => '']) !!}
      </div>
    </div>
    <div class="form-group">
      <label for="weekend" class="col-sm-3 control-label">
        Weekend
      </label>
      <div class="col-sm-6">
        {!! Form::text('weekend', $result->rooms_price->original_weekend, ['class' => 'form-control', 'id' => 'weekend', 'placeholder' => '']) !!}
      </div>
    </div>
  </fieldset>

@endif
 @if($result->type == 'Multiple')
    <fieldset class="box-body {{$result->type == 'Multiple' ? '' : 'hide'}}" id="clone_" name="room_fieldset" >  
    
        <input type="hidden" name="count_mulitple_room" id="count_mulitple_room" value="{{ count($multiple_rooms) }}">

        <div ng-repeat="choice in multiple_rooms" class="add_additioanl_block">

            <input type="hidden" name="mulitple_room_id[]" id="mulitple_room_id" value="@{{ choice.id }}">
            <div class="form-group edit_room_cot" data-index="@{{ $index }}">
                <label for="room_name" class="col-md-3 control-label" id="room_name">Name<em class="text-danger">*</em></label>
                <div class="col-md-6">
                  <div class="">
                    <p class="text-right">
                      <span ng-bind="35 - choice.name.length">35</span> characters left
                    </p>
                    <input type="text" name="room_name[]" class="form-control" placeholder="Room Name" id='room_name_@{{$index}}' ng-model="choice.name" data-index="@{{ $index }}" maxlength='35'>
                  </div>
                </div>
            </div> 
            <div class="form-group edit_room_cot" data-index="@{{ $index }}">
                <label for="room_description" class="col-md-3 control-label"id="room_description">Description<em class="text-danger">*</em></label>
                <div class="col-md-6">
                  <div class="">
                     <p class="text-right">
                      <span ng-bind="500 - choice.summary.length">500</span> characters left
                    </p>
                    <textarea name="room_description[]"  class="form-control" id='room_description_@{{$index}}' rows="5" ng-model="choice.summary" data-index="@{{ $index }}" maxlength='500'>
                    </textarea>
                  </div>
                </div>
            </div>
            <div class="form-group" data-index="@{{ $index }}">
                <label for="room_photos" class="col-md-3 control-label">Room Photos</label>
                <div class="col-md-6">

                    <input type="file" class="room_photos_@{{ $index }} room_photos1" name="room_photos@{{ $index }}[]" data-index="@{{ $index }}" multiple="true" id='upload_photos_@{{$index}}'>
                    <span id="photos_errors_@{{$index}}" class="text-danger photos_errors11"></span>
                    <span class="hidden photos_error1_@{{ $index }}" style="color:red;display: block;margin-bottom: 10px;">Photos must be at least 720x480 pixels. Please upload a photo of higher quality.</span>
                    <span class="hidden photos_error_@{{ $index }}" style="color:red;display: block;margin-bottom: 10px;">Please upload the images like JPG,JPEG,PNG,GIF File Only.</span>
                    <ul class="list-unstyled sortable room_sort" id="js-photo-grid" >
                        <div class="row">
                            <span ng-repeat = "row in choice.multiple_room_images" class="col-4 row-space-2">

                                <input type="hidden" name="id_img" id="img{{ $result->id }}" value="@{{ row.id }}">

                                <li style="display: list-item;" id="mul_photo_li_@{{row.multiple_room_id}}_@{{ row.id }}" class="ng-scope" ng-if="row.multiple_room_id==choice.id">
                                    <div class="panel photo-item"> 

                                        <div class="first-photo-ribbon" style="z-index:9; background:none; margin:0; padding:0; " ><input type="radio" name="featured_image_@{{choice.id}}" class="featured1-photo-btn" data-featured-id="@{{ row.id }}" ng-checked="(row.featured == 'Yes') ? true : false"></div>
                                        <div id="photo-5" class="photo-size photo-drag-target js-photo-link"></div>
                                        <a href="#" class="media-photo media-photo-block text-center photo-size">

                                          <img alt="" class="img-responsive-height" ng-src="@{{ row.image_name }}">

                                      </a>
                                      <button class="delete-multiple-photo-btn overlay-btn" data-multiple-room-id="@{{row.multiple_room_id}}" data-photo-id="@{{ row.id }}" type="button" style="z-index: 2;">
                                        <i class="fa fa-trash" style="color:white;"></i>
                                    </button>
                                    <div class="panel-body panel-condensed">
                                        <textarea tabindex="1" class="input-large highlights ng-pristine ng-untouched ng-valid" data-photo-id="@{{ row.id }}" placeholder="What are the highlights of this photo?" rows="3" name="room_highlight" data-type="subroom">@{{ row.highlights }}</textarea>
                                    </div>

                                </div>
                            </li>

                        </span>
                    </div>
                </ul>
            </div>

        </div>
        <div class="form-group" data-index="@{{ $index }}">
          <label for="accommodates" class="col-md-3 control-label">Room Type<em class="text-danger">*</em></label>
          <div class="col-md-6">
            {!! Form::select('room_type_multiple[]', $room_type_multiple, '', ['class' => 'form-control multiple_room_type required','id' => 'multiple_room_type_@{{$index}}', 'placeholder' => 'Select...' ,'ng-model'=>'choice.room_type']) !!}
        </div>
    </div>
    <div class="form-group" data-index="@{{ $index }}">
      <label for="accommodates" class="col-md-3 control-label">Accommodates<em class="text-danger">*</em></label>
      <div class="col-md-6">
        {!! Form::select('room_accommodates[]', $accommodates, '', ['class' => 'form-control room_accommodates required','id' => 'room_accommodates_@{{$index}}', 'placeholder' => 'Select...' ,'ng-model'=>'choice.accommodates']) !!}
    </div>
</div> 

<div class="form-group" data-index="@{{ $index }}">
  <label for="number_of_rooms" class="col-md-3 control-label">Number of Rooms<em class="text-danger">*</em></label>
  <div class="col-md-6">
    {!! Form::number('number_of_rooms[]',  '', ['class' => 'form-control required', 'id' => 'number_of_rooms_@{{$index}}', 'placeholder' => 'Number of Rooms','value'=>'1','min'=>'1','max'=>'100','ng-model'=>'choice.number_of_rooms']) !!}
</div>
</div>                
<div class="form-group" data-index="@{{ $index }}">
    <label for="room_bedrooms" class="col-md-3 control-label">Bedrooms<em class="text-danger">*</em></label>
    <div class="col-md-6">
      {!! Form::select('room_bedrooms[]', $bedrooms, '', ['class' => 'form-control room_bedrooms required', 'id' => 'room_bedrooms_@{{$index}}', 'placeholder' => 'Select...','ng-model'=>'choice.bedrooms']) !!}
  </div>
</div>
{{--
  <div class="form-group" data-index="@{{ $index }}">
    <label for="room_beds" class="col-md-3 control-label">Beds<em class="text-danger">*</em></label>
    <div class="col-md-6">
      {!! Form::select('room_beds[]', $beds, '', ['class' => 'form-control room_beds required', 'id' => 'room_beds_@{{$index}}', 'placeholder' => 'Select...','ng-model'=>'choice.beds']) !!}
  </div>
</div>
<div class="form-group" data-index="@{{ $index }}">
    <label for="room_bed_type" class="col-md-3 control-label">Bed Type<em class="text-danger">*</em></label>
    <div class="col-md-6">
      {!! Form::select('room_bed_type[]', $bed_type, '', ['class' => 'form-control room_bed_type required', 'id' => 'room_bed_type_@{{$index}}', 'placeholder' => 'Select...','ng-model'=>'choice.bed_type']) !!}
  </div>
</div>
--}}
<div class="form-group" data-index="@{{ $index }}">
    <label for="room_bathrooms" class="col-md-3 control-label">Bathrooms<em class="text-danger">*</em></label>
    <div class="col-md-6">
      {!! Form::select('room_bathrooms[]', $bathrooms1, '', ['class' => 'form-control room_bathrooms required', 'id' => 'room_bathrooms_@{{$index}}', 'placeholder' => 'Select...','ng-model'=>'choice.bathrooms']) !!}
  </div>
</div>


<div class="form-group" data-index="@{{ $index }}" ng-init="index_new = $index ">
    <div class="" ng-init="multiple_rooms_bed_type_options= {{json_encode($bed_types)}};type_beds={{json_encode($beds)}};multiple_rooms_bed_type_options_length={{count($bed_types)}}">
      <label for="guests" class="col-md-3 control-label"> Bed Type & Beds 
        <em class="text-danger">*
        </em>
    </label>

    <div class="col-md-6 length_discount" ng-init="multiple_rooms_bed_type_items[index_new] = (choice.rooms_bed_type)?choice.rooms_bed_type:[]">
      <div class="row">
          <div class="col-sm-12" ng-repeat="item in multiple_rooms_bed_type_items[index_new]">

            <input type="hidden" name="multiple_rooms_bed_type[@{{index_new}}][@{{$index}}][id]" value="@{{item.id}}" ng-model="item.id">
            <div class="row">
              <div class="col-md-5">
                <div class="select_custom">
                    <select name="multiple_rooms_bed_type[@{{index_new}}][@{{$index}}][bed_type]" class="form-control" id="multiple_rooms_bed_type_@{{$index}}" ng-model="item.bed_type">
                      <option disabled>
                        Select
                    </option>
                    <option ng-repeat="option in multiple_rooms_bed_type_options" ng-if="multiple_rooms_bed_type_option_avaialble(option.id,index_new) || option.id == item.bed_type" ng-selected="item.bed_type == option.id" value="@{{option.id}}">
                        @{{option.name}}
                    </option>
                </select>
            </div>
        </div>
        <div class="col-md-5" id="discount_error_container__@{{index_new}}_@{{$index}}">
            <div class="select_custom">
                <select name="multiple_rooms_bed_type[@{{index_new}}][@{{$index}}][beds]" class="form-control" id="multiple_rooms_beds_@{{$index}}" ng-model="item.beds">
                  <option disabled>
                    Select
                </option>
                <option ng-repeat="(key,option) in type_beds" ng-selected="item.beds-1 == key" value="@{{key+1}}">
                    @{{option}}
                </option>
            </select>
        </div>
    </div>
    <div class="col-md-2">
        <a href="javascript:void(0)" class="btn btn-danger btn-xs room_bed_type_remove" ng-click="multiple_rooms_bed_type_remove($index,index_new)">
          <span class="fa fa-trash">
          </span>
      </a>
  </div>
</div>
</div>


<div class="col-md-12" ng-init="multiple_rooms_bed_type_select = ''" ng-show="multiple_rooms_bed_type_items[index_new].length < multiple_rooms_bed_type_options.length">
    <div class="row">
      <div class="col-md-12" >
        <div class="select_custom">
      <select name="" class="form-control multiple_rooms_bed_type_select @{{(multiple_rooms_bed_type_items[index_new].length<1) ? 'multiple_rooms_bed_type_select_req' : ''}}" id="multiple_rooms_bed_type_select_@{{index_new}}
 " data-old_index="@{{index_new}}">
              <option value="">
                Select
            </option>
            <option ng-repeat="option in multiple_rooms_bed_type_options" ng-if="multiple_rooms_bed_type_option_avaialble(option.id,index_new)" value="@{{option.id}}">
                @{{option.name}}
            </option>
        </select>
    </div>
</div>
</div>
</div>
<p id="multiple_rooms_bed_type_error_@{{ $index }}" style="display: none;" class="text-danger">Can't delete all bed types. Atleast one bed type is needed.</p>
</div>
</div>
</div>
</div>



<div class="form-group" data-index="@{{ $index }}">
    <label for="room_night" class="col-md-3 control-label">Nightly Price<em class="text-danger">*</em></label>
    <div class="col-md-6">
      {!! Form::number('room_night[]',  '', ['class' => 'form-control required', 'id' => 'room_night_@{{$index}}', 'placeholder' => 'Per Night Price','ng-model'=>'choice.original_night']) !!}
  </div>
</div>
<div class="form-group" data-index="@{{ $index }}">
    <label for="currency_code" class="col-md-3 control-label">Currency Code<em class="text-danger">*</em></label>
    <div class="col-md-6">
      {!! Form::select('room_currency_code[]', $currency, '', ['class' => 'form-control room_currency_code', 'id' => 'room_currency_code_@{{$index}}', 'placeholder' => 'Select...','ng-model'=>'choice.currency_code']) !!}
  </div>
</div> 
              
  <div class="form-group" data-index="@{{ $index }}">
    <label for="security" class="col-md-3 control-label">Security Deposit</label>
    <div class="col-md-6">
      {!! Form::number('room_security[]',  '',['class' => 'form-control', 'id' => 'room_security_@{{$index}}', 'placeholder' => 'Security Deposit','ng-model'=>'choice.original_security']) !!}
  </div>
</div>

<div class="form-group" data-index="@{{ $index }}">
    <label for="cleaning" class="col-md-3 control-label">Cleaning Fee   </label>
    <div class="col-md-6">
      {!! Form::number('room_cleaning[]',  '',['class' => 'form-control', 'id' => 'room_cleaning_@{{$index}}', 'placeholder' => 'Cleaning Fee','ng-model'=>'choice.original_cleaning']) !!}
       <small>(This fee will apply to every reservation at your listing.)</small>
  </div>
</div>
<div class="form-group" data-index="@{{ $index }}">
    <label for="additional_guest_fee" class="col-md-3 control-label">Additional Guest Charge    
    </label>
    <div class="col-md-6">
      {!! Form::number('additional_guest_fee[]',  '',['class' => 'form-control', 'id' => 'additional_guest_fee_@{{$index}}', 'placeholder' => 'Additional Guest Charge','ng-model'=>'choice.original_additional_guest']) !!}
      <small>(This fee will apply for each additional guest, for each night of the reservation.)</small>
  </div>
</div>
<div class="form-group" data-index="@{{ $index }}">
    <label for="guests" class="col-md-3 control-label">Guests</label>
    <div class="col-md-6">
      {!! Form::select('room_guests[]', $accommodates, '', ['class' => 'form-control room_guests', 'id' => 'room_guests_@{{$index}}','ng-model'=>'choice.guests']) !!}
  </div>
</div>
<div class="form-group" data-index="@{{ $index }}">
    <label for="room_weekend" class="col-md-3 control-label">Weekend Price </label>
    <div class="col-md-6">{!! Form::number("weekend_price[]", "", ["class" => "form-control", "id" => 'weekend_price_@{{$index}}', "placeholder" => "Weekend Price",'ng-model'=>'choice.original_weekend']) !!}
    <small>(This is a nightly price. It will replace your base price for every Friday and Saturday.)</small>
    </div>
</div>
<div class="form-group" data-index="@{{ $index }}" ng-init="index1 = $index ">

    <div class="" ng-init="rooms_length_of_stay_options= {{json_encode($length_of_stay_options)}};rooms_length_of_stay_items[index1] = choice.multiple_rooms_length_of_stay">
       <label for="guests" class="col-md-3 control-label"> Length of Stay discounts</label>


       <div class="col-md-6 length_discount">
        <div class="row">
          <div class="col-sm-12" ng-repeat="item in rooms_length_of_stay_items[index1]">
            <div class="row" ng-init="rooms_length_of_stay_items1=item">

              <div class="col-md-5">
                <div class="select_custom">
                    <select name="rooms_length_of_stay[@{{index1}}][@{{$index}}][period]" class="form-control" id="rooms_length_of_stay_period_@{{$index}}" ng-model="item.period">
                      <option disabled>
                        Select nights
                    </option>
                    <option ng-repeat="option in rooms_length_of_stay_options" ng-if="rooms_length_of_stay_option_avaialble(option.nights,index1) || option.nights == item.period" ng-selected="item.period == option.nights" value="@{{option.nights}}">
                        @{{option.text}}
                    </option>
                </select>
            </div>
        </div>
        <div class="col-md-5" id="discount_error_container_@{{index1}}_@{{$index}}">

            <div class="input-addon">
              <input type="text" name="rooms_length_of_stay[@{{index1}}][@{{$index}}][discount]" class="form-control discount" id="rooms_length_of_stay_discount_@{{$index}}" placeholder="Percentage of discount" data-error-placement="container" data-error-container="#discount_error_container_@{{index1}}_@{{$index}}" value="@{{item.discount}}">
              <span class="input-suffix">
                %
            </span>
        </div>
    </div>
    <div class="col-md-2">
        <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="rooms_remove_price_rule('length_of_stay', $index,index1)">
          <span class="fa fa-trash">
          </span>
      </a>
  </div>
</div>
</div>
<div class="col-sm-12" ng-init="rooms_length_of_stay_period_select = ''" ng-show="rooms_length_of_stay_items[index1].length < rooms_length_of_stay_options.length">
    <div class="row">
      <div class="col-md-12" >
        <div class="select_custom">
            <select name="" class="form-control rooms_length_of_stay_period_select_edit" id="rooms_length_of_stay_period_selects_@{{index1}}" ata-type="length_of_stay" data-old_index="@{{index1}}">
              <option value="">
                Select nights
            </option>
            <option ng-repeat="option in rooms_length_of_stay_options" ng-if="rooms_length_of_stay_option_avaialble(option.nights,index1)" value="@{{option.nights}}">
                @{{option.text}}
            </option>
        </select>
    </div>
</div>
</div>
</div>
</div>
</div>
</div>

</div>

<div class="form-group" data-index="@{{ $index }}" ng-init="index2 = $index ">
  <div class="col-md-6 col-md-6 col-md-offset-3 early_bird_wrapper" ng-init="rooms_early_bird_items[index2] = choice.multiple_rooms_early_bird">

    <div class="panel panel-info">
        <div class="panel-header">
            <h4>
                Early Bird Discounts
            </h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-12 row-space-top-1" ng-repeat="item in rooms_early_bird_items[index2]">
                    <div class="row">
                        <input type="hidden" name="rooms_early_bird[@{{index2}}][@{{$index}}][id]" value="@{{item.id}}">
                        <div class="col-md-5" id="eb_period_error_container_@{{index2}}_@{{$index}}">
                            <div class="input-addon">
                                <input type="text" name="rooms_early_bird[@{{index2}}][@{{$index}}][period]" class="form-control early_bird_period" id="rooms_early_bird_period_@{{$index}}" ng-model="rooms_early_bird_items[index2][$index].period" placeholder="Number of days" data-error-placement="container" data-error-container="#eb_period_error_container_@{{index2}}_@{{$index}}">
                                <span class="input-suffix">
                                    Days
                                </span>
                            </div>
                        </div>
                        <div class="col-md-5" id="eb_discount_error_container_@{{index2}}_@{{$index}}">
                            <div class="input-addon">
                                <input type="text" name="rooms_early_bird[@{{index2}}][@{{$index}}][discount]" class="form-control discount" id="rooms_early_bird_discount_@{{$index}}" ng-model="rooms_early_bird_items[index2][$index].discount" placeholder="Percentage of discount" data-error-placement="container" data-error-container="#eb_discount_error_container_@{{index2}}_@{{$index}}">
                                <span class="input-suffix">
                                    %
                                </span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="rooms_remove_price_rule('early_bird', $index,index2)">
                                <span class="fa fa-trash">
                                </span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 row-space-top-2" >
                    <div class="row">
                        <div class="col-md-4" >
                            <a href="javascript:void(0)" class="btn btn-success btn-sm" ng-click="rooms_add_price_rule_edit('early_bird',$index,index2)">
                                <span class="fa fa-plus">
                                </span>
                                Add
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div  class="form-group" data-index="@{{ $index }}" ng-init="index3 = $index ">
  <div class="col-md-6 col-md-offset-3 last_min_wrapper" ng-init="rooms_last_min_items[index3] = choice.multiple_rooms_last_min">
    <div class="panel panel-info">
        <div class="panel-header">
            <h4>
                Last Min Discounts
            </h4>
        </div>
        <div class="panel-body">
            <div class="row">
                <div class="col-sm-12 row-space-top-1" ng-repeat="item in rooms_last_min_items[index3]">
                    <div class="row">
                        <input type="hidden" name="rooms_last_min[@{{index3}}][@{{$index}}][id]" value="@{{item.id}}">
                        <div class="col-md-5" id="lm_period_error_container_@{{index3}}_@{{$index}}">
                            <div class="input-addon">
                                <input type="text" name="rooms_last_min[@{{index3}}][@{{$index}}][period]" class="form-control last_min_period" id="rooms_last_min_period_@{{$index}}" ng-model="rooms_last_min_items[index3][$index].period" placeholder="Number of days" data-error-placement="container" data-error-container="#lm_period_error_container_@{{index3}}_@{{$index}}">
                                <span class="input-suffix">Days</span>
                            </div>
                        </div>
                        <div class="col-md-5" id="lm_discount_error_container_@{{index3}}_@{{$index}}">
                            <div class="input-addon">
                                <input type="text" name="rooms_last_min[@{{index3}}][@{{$index}}][discount]" class="form-control discount" id="rooms_last_min_discount_@{{$index}}" ng-model="rooms_last_min_items[index3][$index].discount" placeholder="Percentage of discount" data-error-placement="container" data-error-container="#lm_discount_error_container_@{{index3}}_@{{$index}}">
                                <span class="input-suffix">%</span>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="rooms_remove_price_rule('last_min', $index,index3)">
                            <span class="fa fa-trash"></span>
                            </a>
                        </div>
                    </div>
                </div>
                <div class="col-md-12 row-space-top-2" >
                    <div class="row">
                        <div class="col-md-4" >
                            <a href="javascript:void(0)" class="btn btn-success btn-sm" ng-click="rooms_add_price_rule_edit('last_min',$index,index3)">
                                <span class="fa fa-plus">
                                </span>
                                Add
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
</div>

<div class="form-group" data-index="@{{ $index }}" ng-init="index4 = $index ">
    <div class="col-md-6 col-md-offset-3 room_avai_rule" ng-init="rooms_availability_rules[index4] = choice.multiple_rooms_availability">
        <div class="panel panel-info">
            <div class="panel-header">
                <h4>
                    Availability Rules
                </h4>
            </div>
            <div class="panel-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="form-group">
                            <label class="control-label col-md-4">
                                Minimum Stay
                            </label>

                            <div class="col-md-6" id="minimum_stay_error_container_@{{index4}}">
                                <div class="input-addon">
                                    <input type="text" value="@{{choice.minimum_stay}}" name="rooms_minimum_stay[@{{index4}}]" class="form-control minimum_stay" id="rooms_minimum_stay_@{{index4}}" placeholder="Minimum Stay" data-error-placement="container" data-error-container="#minimum_stay_error_container_@{{index4}}" >
                                    <span class="input-suffix">
                                        Nights
                                    </span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="control-label col-md-4">
                                Maximum Stay
                            </label>
                            <div class="col-md-6" id="maximum_stay_error_container_@{{index4}}">
                                <div class="input-addon">
                                    <input type="text" value="@{{choice.maximum_stay}}" name="rooms_maximum_stay[@{{index4}}]" class="form-control maximum_stay" id="rooms_maximum_stay_@{{index4}}" data-minimum_stay="#rooms_minimum_stay_@{{index4}}" placeholder="Maximum Stay" data-error-placement="container" data-error-container="#maximum_stay_error_container_@{{index4}}" >
                                    <span class="input-suffix">
                                        Nights
                                    </span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="panel-footer" ng-init="rooms_is_custom_exits = (rooms_availability_rules[index4].length) > 0 ? true : false" >

                <div class="row" ng-show="!rooms_is_custom_exits">
                    <div class="col-md-12 text-center">
                        <a href="javascript:void(0)" class="btn btn-success" ng-click="rooms_is_custom_exits = true; rooms_add_availability_rule_edit(index4);">
                            Add custom Rule
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="panel panel-info availability_rules_wrapper room_avai_rule" ng-show="rooms_is_custom_exits">

            <div class="panel-body">
                <div class="row">
                    <div class="col-sm-12 row-space-top-1" ng-repeat="item in rooms_availability_rules[index4]" ng-init="rooms_saved_item = copy_data(item)">
                        <input type="hidden" name="rooms_availability_rules[@{{index4}}][@{{$index}}][id]" value="@{{item.id}}">
                        <input type="hidden" name="rooms_availability_rules[@{{index4}}][@{{$index}}][edit]" value="@{{rooms_availability_rules[index4][$index].edit}}">
                        <div class="row" ng-if="item.id" ng-init="rooms_availability_rules[index4][$index].edit = item.id != '' ? true : false" ng-show="rooms_availability_rules[index4][$index].edit">
                            <div class="col-md-12">
                                <div class="row">
                                    <div class="col-md-6 col-md-offset-3" style="border: 1px solid #cfcfcf; padding: 10px;">
                                        <div class="row" >
                                            <div class="col-md-8">
                                                <p>
                                                    During @{{rooms_saved_item.during}},
                                                </p>
                                                <p ng-if="rooms_saved_item.minimum_stay">
                                                    guests stay for minimum @{{rooms_saved_item.minimum_stay}} nights
                                                </p>
                                                <p ng-if="rooms_saved_item.maximum_stay">
                                                    guests stay for maximum @{{rooms_saved_item.maximum_stay}} nights
                                                </p>
                                            </div>
                                            <div class="col-md-4">
                                                <a href="javascript:void(0)" class="btn btn-info btn-xs" ng-click="rooms_availability_rules[index4][$index].edit = false">
                                                    <span class="fa fa-edit">
                                                    </span>
                                                </a>
                                                <a href="javascript:void(0)" class="btn btn-danger btn-xs add_stay_delete" ng-click="rooms_remove_availability_rule($index,index4)">
                                                    <span class="fa fa-trash">
                                                    </span>
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row" ng-show="!rooms_availability_rules[index4][$index].edit">
                            <div class="form-group">
                                <label class="control-label col-md-4">
                                    Select Dates
                                </label>
                                <div class="col-md-6" ng-init="item.type = item.id ? 'prev' : ''">
                                    <div class="select_custom">
                                        <select name="rooms_availability_rules[@{{index4}}][@{{$index}}][type]" class="form-control required" id="rooms_availability_rules_@{{index4}}_@{{$index}}_type" ng-model="rooms_availability_rules[index4][$index]['type']" ng-click="rooms_availability_rules_type_change($index,index4);" >
                                            <option value="" ng-disabled="item.type != ''" ng-if="!item.id">
                                                Select Dates
                                            </option>
                                            <option value="prev" data-start_date="@{{rooms_saved_item.start_date_formatted}}" data-end_date="@{{rooms_saved_item.end_date_formatted}}" ng-if="item.id">
                                                @{{item.during}}
                                            </option>
                                            @foreach($availability_rules_months_options as $date => $option)
                                            <option value="month" data-start_date="{{$option['start_date']}}" data-end_date="{{$option['end_date']}}">
                                                {{$option['text']}}
                                            </option>
                                            @endforeach
                                            <option value="custom">
                                                Custom
                                            </option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="rooms_remove_availability_rule($index,index4)">
                                        <span class="fa fa-trash">
                                        </span>
                                    </a>
                                    <br>
                                    <a href="javascript:void(0)" class="btn btn-info btn-xs" ng-click="rooms_availability_rules[index4][$index].edit = true" ng-if="item.id != '' && item.id ">
                                        <span class="fa fa-times">
                                        </span>
                                    </a>
                                </div>
                            </div>
                            <div class="form-group" ng-show="rooms_availability_rules[index4][$index]['type'] == 'custom'">
                                <label class="col-md-4 control-label">
                                    Custom Dates
                                </label>
                                <div class="col-md-3" ng-init="rooms_availability_rules[index4][$index]['start_date'] = rooms_availability_rules[index4][$index]['start_date_formatted']">
                                    <input type="text" readonly name="rooms_availability_rules[@{{index4}}][@{{$index}}][start_date]" class="form-control required" id="rooms_availability_rules_@{{index4}}_@{{$index}}_start_date" placeholder="Start Date" ng-model="rooms_availability_rules[index4][$index]['start_date']" >
                                </div>
                                <div class="col-md-3" ng-init="rooms_availability_rules[index4][$index]['end_date'] = rooms_availability_rules[index4][$index]['end_date_formatted']">
                                    <input type="text" readonly name="rooms_availability_rules[@{{index4}}][@{{$index}}][end_date]" class="form-control required" id="rooms_availability_rules_@{{index4}}_@{{$index}}_end_date" placeholder="End Date" ng-model="rooms_availability_rules[index4][$index]['end_date']" >
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4">
                                    Minimum Stay
                                </label>
                                <div class="col-md-6" id="rooms_availability_minimum_stay_error_container_@{{index4}}_@{{$index}}">
                                    <div class="input-addon">
                                        <input type="text" name="rooms_availability_rules[@{{index4}}][@{{$index}}][minimum_stay]" class="form-control availability_minimum_stay" id="rooms_availability_rules_@{{index4}}_@{{$index}}_minimum_stay" placeholder="Minimum Stay" ng-model="rooms_availability_rules[index4][$index]['minimum_stay']" data-error-placement="container" data-error-container="#rooms_availability_minimum_stay_error_container_@{{index4}}_@{{$index}}" >
                                        <span class="input-suffix">
                                            Nights
                                        </span>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="control-label col-md-4">
                                    Maximum Stay
                                </label>
                                <div class="col-md-6" id="rooms_availability_maximum_stay_error_container_@{{index4}}_@{{$index}}">
                                    <div class="input-addon">
                                        <input type="text" name="rooms_availability_rules[@{{index4}}][@{{$index}}][maximum_stay]" class="form-control availability_maximum_stay" id="rooms_availability_rules_@{{index4}}_@{{$index}}_maximum_stay" data-minimum_stay="#rooms_availability_rules_@{{index4}}_@{{$index}}_minimum_stay" placeholder="Maximum Stay" ng-model="rooms_availability_rules[index4][$index]['maximum_stay']" data-error-placement="container" data-error-container="#rooms_availability_maximum_stay_error_container_@{{index4}}_@{{$index}}" >
                                        <span class="input-suffix">
                                            Nights
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <legend ng-if="$index+1 < rooms_availability_rules[index4].length" class="row-space-top-2">
                        </legend>
                    </div>
                </div>
            </div>
            <div class="panel-footer">
                <div class="row">
                    <div class="col-sm-12 text-center" >
                        <a href="javascript:void(0)" class="btn btn-success btn-sm" ng-click="rooms_add_availability_rule_edit(index4)">
                            <span class="fa fa-plus">
                            </span>
                            Add
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="form-group edit_ament" data-index="@{{ $index }}" ng-init="index1 =  $index">
    <label for="amenities" class="col-md-3 control-label">Amenities</label>
    <div class="col-md-9">

        <ul class="list-unstyled " id="triple">
            <li ng-repeat = "row in amenities">
               <label class="label-large label-inline amenity-label check-amenity-label">
                <input type="checkbox" class="amenities_multiple" value="@{{ row.id }}" name="room_amenities[@{{ index1 }}][]" ng-checked="(row.id | CheckAmenities : choice.checked_amenities) ? true : false">
                <span class="admin_ameniti">@{{ row.name }}</span>
            </label>
        </li>
    </ul>
</div>
<span class="@{{(multiple_rooms.length<=1)?'hide':''}} delete_room_but">
  <a class="pull-right btn edit-room-del-btn" href="javascript:;" ng-click="removeRooms($index,choice.id)" data-id="@{{choice.id}}">
    <span class="">Delete</span>
  </a>
</span>
</div>
<span class="hidden remove_errors remove_error1_@{{ $index }}" style="color:red; text-align: center;display: block;">This room has some reservations. So, you cannot delete this room.</span>

<span class="hidden remove_errors remove_error_@{{ $index }}" style="color:red; text-align: center;display: block;">You cannot delete all rooms. Atleast one room is required.</span>
<span></span>

<hr>
</div>

</fieldset>
@endif
    <div class="box-footer {{$result->type == 'Single' ? '' : 'hide'}}">
    <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel" >
        Cancel
    </a>
    <button class="btn btn-info pull-right" type="submit" name="submit" value="pricing">
        Submit
    </button>
</div>
<div class="box-footer {{$result->type == 'Multiple' ? '' : 'hide'}}">
    <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel" >
        Cancel
    </a>
    <button class="btn btn-info pull-right" type="submit" name="submit" value="multiple_rooms">
        Submit
    </button>
</div>

  



</div>
<div id="sf9" class="frm">
  <fieldset class="box-body">
    <div class="form-group">
      <label for="booking_type" class="col-sm-3 control-label">
        Booking Type
      </label>
      <div class="col-sm-6">
        {!! Form::select('booking_type', ['request_to_book'=>'Request To Book', 'instant_book'=>'Instant Book'], $result->booking_type, ['class' => 'form-control', 'id' => 'booking_type']) !!}
      </div>
    </div>
  </fieldset>
  <div class="box-footer">
    <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel" >
      Cancel
    </a>
    <button class="btn btn-info pull-right" type="submit" name="submit" value="booking_type">
      Submit
    </button>
  </div>
</div>
<div id="sf10" class="frm">
  <fieldset class="box-body">
    <div class="form-group">
      <label for="cancel_policy" class="col-sm-3 control-label">
        Cancellation Policy
      </label>
      <div class="col-sm-6">
        {!! Form::select('cancel_policy', ['Flexible'=>'Flexible', 'Moderate'=>'Moderate','Strict'=>'Strict'], $result->cancel_policy, ['class' => 'form-control', 'id' => 'cancel_policy']) !!}
      </div>
    </div>
  </fieldset>
  <div class="box-footer">
    <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel" >
      Cancel
    </a>
    <button class="btn btn-info pull-right" type="submit" name="submit" value="terms">
      Submit
    </button>
  </div>
</div>
<div id="sf11" class="frm">
  <fieldset class="box-body">
    <div class="row price_rules">
      <div class="col-md-8 col-md-offset-2 length_of_stay_wrapper" ng-init="length_of_stay_items = {{json_encode($result->length_of_stay_rules)}};">
        <div class="panel panel-info" ng-init="length_of_stay_options= {{json_encode($length_of_stay_options)}}">
          <div class="panel-header">
            <h4>
              Length of Stay discounts
            </h4>
          </div>
          <div class="panel-body">
            <div class="row">
              <div class="col-sm-12 row-space-top-1" ng-repeat="item in length_of_stay_items">
                <div class="row">
                  <input type="hidden" name="length_of_stay[@{{$index}}][id]" value="@{{item.id}}">
                  <div class="col-md-4">
                    <select name="length_of_stay[@{{$index}}][period]" class="form-control" id="length_of_stay_period_@{{$index}}" ng-model="length_of_stay_items[$index].period">
                      <option disabled>
                        Select nights
                      </option>
                      <option ng-repeat="option in length_of_stay_options" ng-if="length_of_stay_option_avaialble(option.nights) || option.nights == item.period" ng-selected="item.period == option.nights" value="@{{option.nights}}">
                        @{{option.text}}
                      </option>
                    </select>
                  </div>
                  <div class="col-md-4" id="discount_error_container_@{{$index}}">
                    <div class="input-addon">
                      <input type="text" name="length_of_stay[@{{$index}}][discount]" class="form-control discount" id="length_of_stay_discount_@{{$index}}" ng-model="length_of_stay_items[$index].discount" placeholder="Percentage of discount" data-error-placement="container" data-error-container="#discount_error_container_@{{$index}}">
                      <span class="input-suffix">
                        %
                      </span>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="remove_price_rule('length_of_stay', $index)">
                      <span class="fa fa-trash">
                      </span>
                    </a>
                  </div>
                </div>
              </div>
              <div class="col-md-12" ng-init="length_of_stay_period_select = ''" ng-show="length_of_stay_items.length < length_of_stay_options.length">
                <div class="row">
                  <div class="col-md-4" >
                    <select name="" class="form-control" id="length_of_stay_period_select" ng-model="length_of_stay_period_select" ng-change="add_price_rule('length_of_stay')">
                      <option value="">
                        Select nights
                      </option>
                      <option ng-repeat="option in length_of_stay_options" ng-if="length_of_stay_option_avaialble(option.nights)" value="@{{option.nights}}">
                        @{{option.text}}
                      </option>
                    </select>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-8 col-md-offset-2 early_bird_wrapper" ng-init="early_bird_items = {{json_encode($result->early_bird_rules->count() ? $result->early_bird_rules : [])}};">
        <div class="panel panel-info">
          <div class="panel-header">
            <h4>
              Early Bird Discounts
            </h4>
          </div>
          <div class="panel-body">
            <div class="row">
              <div class="col-sm-12 row-space-top-1" ng-repeat="item in early_bird_items">
                <div class="row">
                  <input type="hidden" name="early_bird[@{{$index}}][id]" value="@{{item.id}}">
                  <div class="col-md-4" id="eb_period_error_container_@{{$index}}">
                    <div class="input-addon">
                      <input type="text" name="early_bird[@{{$index}}][period]" class="form-control early_bird_period" id="early_bird_period_@{{$index}}" ng-model="early_bird_items[$index].period" placeholder="Number of days" data-error-placement="container" data-error-container="#eb_period_error_container_@{{$index}}">
                      <span class="input-suffix">
                        Days
                      </span>
                    </div>
                  </div>
                  <div class="col-md-4" id="eb_discount_error_container_@{{$index}}">
                    <div class="input-addon">
                      <input type="text" name="early_bird[@{{$index}}][discount]" class="form-control discount" id="early_bird_discount_@{{$index}}" ng-model="early_bird_items[$index].discount" placeholder="Percentage of discount" data-error-placement="container" data-error-container="#eb_discount_error_container_@{{$index}}">
                      <span class="input-suffix">
                        %
                      </span>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="remove_price_rule('early_bird', $index)">
                      <span class="fa fa-trash">
                      </span>
                    </a>
                  </div>
                </div>
              </div>
              <div class="col-md-12 row-space-top-2" >
                <div class="row">
                  <div class="col-md-4" >
                    <a href="javascript:void(0)" class="btn btn-success btn-sm" ng-click="add_price_rule('early_bird')">
                      <span class="fa fa-plus">
                      </span>
                      Add
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
      <div class="col-md-8 col-md-offset-2 last_min_wrapper" ng-init="last_min_items = {{json_encode($result->last_min_rules->count() ? $result->last_min_rules : [])}};">
        <div class="panel panel-info">
          <div class="panel-header">
            <h4>
              Last Min Discounts
            </h4>
          </div>
          <div class="panel-body">
            <div class="row">
              <div class="col-sm-12 row-space-top-1" ng-repeat="item in last_min_items">
                <div class="row">
                  <input type="hidden" name="last_min[@{{$index}}][id]" value="@{{item.id}}">
                  <div class="col-md-4" id="lm_period_error_container_@{{$index}}">
                    <div class="input-addon">
                      <input type="text" name="last_min[@{{$index}}][period]" class="form-control last_min_period" id="last_min_period_@{{$index}}" ng-model="last_min_items[$index].period" placeholder="Number of days" data-error-placement="container" data-error-container="#lm_period_error_container_@{{$index}}">
                      <span class="input-suffix">
                        Days
                      </span>
                    </div>
                  </div>
                  <div class="col-md-4" id="lm_discount_error_container_@{{$index}}">
                    <div class="input-addon">
                      <input type="text" name="last_min[@{{$index}}][discount]" class="form-control discount" id="last_min_discount_@{{$index}}" ng-model="last_min_items[$index].discount" placeholder="Percentage of discount" data-error-placement="container" data-error-container="#lm_discount_error_container_@{{$index}}">
                      <span class="input-suffix">
                        %
                      </span>
                    </div>
                  </div>
                  <div class="col-md-4">
                    <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="remove_price_rule('last_min', $index)">
                      <span class="fa fa-trash">
                      </span>
                    </a>
                  </div>
                </div>
              </div>
              <div class="col-md-12 row-space-top-2" >
                <div class="row">
                  <div class="col-md-4" >
                    <a href="javascript:void(0)" class="btn btn-success btn-sm" ng-click="add_price_rule('last_min')">
                      <span class="fa fa-plus">
                      </span>
                      Add
                    </a>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </fieldset>
  <div class="box-footer">
    <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel" >
      Cancel
    </a>
    <button class="btn btn-info pull-right" type="submit" name="submit" value="price_rules">
      Submit
    </button>
  </div>
</div>

<div id="sf12" class="frm">
    <fieldset class="box-body">
        <div class="row availability_rules">
            <div class="col-md-6 col-md-offset-3" ng-init="availability_rules = {{json_encode($result->availability_rules->count() ? $result->availability_rules : [] )}};">
                <div class="panel panel-info">
                    <div class="panel-header">
                        <h4>
                            Availability Rules
                        </h4>
                    </div>
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="form-group">
                                    <label class="control-label col-md-4">
                                        Minimum Stay
                                    </label>
                                    <div class="col-md-6" id="minimum_stay_error_container">
                                        <div class="input-addon">
                                            <input type="text" value="{{$result->type=='Single'?$result->rooms_price->minimum_stay:''}}" name="minimum_stay" class="form-control minimum_stay" id="minimum_stay" placeholder="Minimum Stay" data-error-placement="container" data-error-container="#minimum_stay_error_container" >
                                            <span class="input-suffix">
                                                Nights
                                            </span>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label class="control-label col-md-4">
                                        Maximum Stay
                                    </label>
                                    <div class="col-md-6" id="maximum_stay_error_container">
                                        <div class="input-addon">
                                            <input type="text" value="{{$result->type=='Single'?$result->rooms_price->maximum_stay:''}}" name="maximum_stay" class="form-control maximum_stay" id="maximum_stay" data-minimum_stay="#minimum_stay" placeholder="Maximum Stay" data-error-placement="container" data-error-container="#maximum_stay_error_container" >
                                            <span class="input-suffix">
                                                Nights
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer" ng-show="!is_custom_exits">
                        <div class="row" ng-init="is_custom_exits = {{$result->availability_rules->count() > 0 ? 'true' : 'false'}}">
                            <div class="col-md-12 text-center">
                                <a href="javascript:void(0)" class="btn btn-success" ng-click="is_custom_exits = true; add_availability_rule();">
                                    Add custom Rule
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="panel panel-info availability_rules_wrapper room_avai_rule" ng-show="is_custom_exits">
                    <div class="panel-body">
                        <div class="row">
                            <div class="col-sm-12 row-space-top-1" ng-repeat="item in availability_rules" ng-init="saved_item = copy_data(item)">
                                <input type="hidden" name="availability_rules[@{{$index}}][id]" value="@{{item.id}}">
                                <input type="hidden" name="availability_rules[@{{$index}}][edit]" value="@{{availability_rules[$index].edit}}">
                                <div class="row" ng-if="item.id" ng-init="availability_rules[$index].edit = item.id != '' ? true : false" ng-show="availability_rules[$index].edit">
                                    <div class="col-md-12">
                                        <div class="row">
                                            <div class="col-md-6 col-md-offset-3" style="border: 1px solid #cfcfcf; padding: 10px;">
                                                <div class="row" >
                                                    <div class="col-md-8">
                                                        <p>
                                                            During @{{saved_item.during}},
                                                        </p>
                                                        <p ng-if="saved_item.minimum_stay">
                                                            guests stay for minimum @{{saved_item.minimum_stay}} nights
                                                        </p>
                                                        <p ng-if="saved_item.maximum_stay">
                                                            guests stay for maximum @{{saved_item.maximum_stay}} nights
                                                        </p>
                                                    </div>
                                                    <div class="col-md-4">
                                                        <a href="javascript:void(0)" class="btn btn-info btn-xs" ng-click="availability_rules[$index].edit = false">
                                                            <span class="fa fa-edit">
                                                            </span>
                                                        </a>
                                                        <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="remove_availability_rule($index)">
                                                            <span class="fa fa-trash">
                                                            </span>
                                                        </a>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="row" ng-show="!availability_rules[$index].edit">
                                    <div class="form-group">
                                        <label class="control-label col-md-4">
                                            Select Dates
                                        </label>
                                        <div class="col-md-6" ng-init="item.type = item.id ? 'prev' : ''">
                                            <div class="select_custom">
                                                <select name="availability_rules[@{{$index}}][type]" class="form-control required" id="availability_rules_@{{$index}}_type" ng-model="availability_rules[$index]['type']" ng-click="availability_rules_type_change($index);" >
                                                    <option value="" ng-disabled="item.type != ''" ng-if="!item.id">
                                                        Select Dates
                                                    </option>
                                                    <option value="prev" data-start_date="@{{saved_item.start_date_formatted}}" data-end_date="@{{saved_item.end_date_formatted}}" ng-if="item.id">
                                                        @{{item.during}}
                                                    </option>
                                                    @foreach($availability_rules_months_options as $date => $option)
                                                    <option value="month" data-start_date="{{$option['start_date']}}" data-end_date="{{$option['end_date']}}">
                                                        {{$option['text']}}
                                                    </option>
                                                    @endforeach
                                                    <option value="custom">
                                                        Custom
                                                    </option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="col-md-2">
                                            <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="remove_availability_rule($index)">
                                                <span class="fa fa-trash">
                                                </span>
                                            </a>
                                            <br>
                                            <a href="javascript:void(0)" class="btn btn-info btn-xs" ng-click="availability_rules[$index].edit = true" ng-if="item.id != '' && item.id ">
                                                <span class="fa fa-times">
                                                </span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="form-group" ng-show="availability_rules[$index]['type'] == 'custom'">
                                        <label class="col-md-4 control-label">
                                            Custom Dates
                                        </label>
                                        <div class="col-md-3" ng-init="availability_rules[$index]['start_date'] = availability_rules[$index]['start_date_formatted']">
                                            <input type="text" readonly name="availability_rules[@{{$index}}][start_date]" class="form-control required" id="availability_rules_@{{$index}}_start_date" placeholder="Start Date" ng-model="availability_rules[$index]['start_date']" >
                                        </div>
                                        <div class="col-md-3" ng-init="availability_rules[$index]['end_date'] = availability_rules[$index]['end_date_formatted']">
                                            <input type="text" readonly name="availability_rules[@{{$index}}][end_date]" class="form-control required" id="availability_rules_@{{$index}}_end_date" placeholder="End Date" ng-model="availability_rules[$index]['end_date']" >
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4">
                                            Minimum Stay
                                        </label>
                                        <div class="col-md-6" id="availability_minimum_stay_error_container_@{{$index}}">
                                            <div class="input-addon">
                                                <input type="text" name="availability_rules[@{{$index}}][minimum_stay]" class="form-control availability_minimum_stay" id="availability_rules_@{{$index}}_minimum_stay" placeholder="Minimum Stay" ng-model="availability_rules[$index]['minimum_stay']" data-error-placement="container" data-error-container="#availability_minimum_stay_error_container_@{{$index}}" >
                                                <span class="input-suffix">
                                                    Nights
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="control-label col-md-4">
                                            Maximum Stay
                                        </label>
                                        <div class="col-md-6" id="availability_maximum_stay_error_container_@{{$index}}">
                                            <div class="input-addon">
                                                <input type="text" name="availability_rules[@{{$index}}][maximum_stay]" class="form-control availability_maximum_stay" id="availability_rules_@{{$index}}_maximum_stay" data-minimum_stay="#availability_rules_@{{$index}}_minimum_stay" placeholder="Maximum Stay" ng-model="availability_rules[$index]['maximum_stay']" data-error-placement="container" data-error-container="#availability_maximum_stay_error_container_@{{$index}}" >
                                                <span class="input-suffix">
                                                    Nights
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <legend ng-if="$index+1 < availability_rules.length" class="row-space-top-2">
                                </legend>
                            </div>
                        </div>
                    </div>
                    <div class="panel-footer">
                        <div class="row">
                            <div class="col-sm-12 text-center" >
                                <a href="javascript:void(0)" class="btn btn-success btn-sm" ng-click="add_availability_rule()">
                                    <span class="fa fa-plus">
                                    </span>
                                    Add
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </fieldset>
    <div class="box-footer">
        <a href="{{url(ADMIN_URL.'/rooms')}}" class="btn btn-default cancel" >
            Cancel
        </a>
        <button class="btn btn-info pull-right" type="submit" name="submit" value="availability_rules">
            Submit
        </button>
    </div>
</div>


<!-- /.box-body -->
<!-- /.box-footer -->
{!! Form::close() !!}
</div>
<!-- /.box -->
</div>
<!--/.col (right) -->
</div>
<!-- /.row -->
<div aria-hidden="true" style="" class="modal delete_popup_room" id="delete_room_but" >
    <div class="modal-content del_content_cls">
        <div class="delete_content">
            <div class="modal-header">
              <i class="icon-remove-1 rm_lg close" data-dismiss="modal"></i>
          </div>
          <div class="modal-body">
            <h6>Are you sure want to delete this room?</h6>
        </div>
        <input type="hidden" id="name_delete" value="">
        <input type="hidden" id="id_delete" value="">
        <div class="modal-footer">
           <button type="submit" class="btn btn-default" data-dismiss="modal"> No </button>
           <button type="submit" class="btn btn-default" id="submit_delete"> Yes </button>
       </div>
   </div>
</div>
</div>
</section>
<!-- /.content -->
</div>
<!-- /.content-wrapper -->
<style type="text/css">
  ul.list-unstyled {
    width:100%;
    margin-bottom:20px;
    overflow:hidden;
  }
  .list-unstyled > li{
    line-height:1.5em;
    float:left;
    display:inline;
  }
  .price_rules input, .availability_rules input {
    margin-bottom: 0px;
  }
  .input-suffix {
    padding: 6px 10px;
  }
  #double li  {
    width:50%;
  }
  #triple li  {
    width:33.333%;
  }
  #quad li {
    width:25%;
  }
  #six li {
    width:16.666%;
  }

  @media (max-width: 760px) {
    #triple li{
      width: 100% !important;
    }
  }
  @media (min-width: 765px) and (max-width: 1000px) {
    #triple li{
      width: 50% !important;
    }
  }
  @media (min-width: 1280px) and (max-width: 2000px) {
    .sidebar {
      position: relative !important; top: 0px !important;
    }
  }

  #ajax_container {
    float: none !important; 
  }
  .btn-warning{
    margin-bottom: 10px;
  }
  .sortable-placeholder1 {
    float: left;
    position: relative;
    min-height: 1px;
    padding-left: 12.5px;
    padding-right: 12.5px;
    width: 200px;
    border: 1px dashed #82888a;
    height: 255px;
  }
  .hiddenEvent{
    display: none;
  }
  .fc-other-month .fc-day-number {
    display:none;
  }
  td.fc-other-month .fc-day-number {
    visibility: hidden;
  }
  .status-r {
    background: brown;
  }
  .status-b {
    background: blue;
  }
</style>
@endsection 
