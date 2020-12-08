@extends('admin.template')
@section('main')
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper" ng-controller="rooms_admin" ng-init="add_room_steps()">
  <!-- Content Header (Page header) -->
  <section class="content-header">
    <h1>
      Add Room
    </h1>
    <ol class="breadcrumb">
      <li>
        <a href="dashboard">
          <i class="fa fa-dashboard">
          </i> Home
        </a>
      </li>
      <li>
        <a href="rooms">Rooms
        </a>
      </li>
      <li class="active">Add
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
            <h3 class="box-title">Add Room Form
            </h3>
          </div>
          <!-- /.box-header -->
          <!-- form start -->
          {!! Form::open(['url' => ADMIN_URL.'/add_room', 'class' => 'form-horizontal', 'id' => 'add_room_form', 'files' => true]) !!}
          <div class="box-body" ng-cloak>
            <div class="box-header with-border">
              <h4 class="box-title">Step @{{step+1}} of @{{steps.length}} - @{{step_name}}</h4>
            </div>
            <p class="text-danger">(*)Fields are Mandatory</p>
            <div id="sf1" class="frm hide" data-step-name="Calendar">
              <fieldset class="box-body">
                <div class="form-group">
                  <label for="calendar" class="col-sm-3 control-label">Calendar
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::select('calendar', ['Always'=>'Always'], '', ['class' => 'form-control', 'id' => 'calendar', 'placeholder' => 'Select...']) !!}
                  </div>
                </div>
              </fieldset>
            </div>

            <div id="sf2" class="frm" data-step-name="Basics" ng-init="type='Single';">
              <fieldset class="box-body">
                <div class="bed_room_types clearfix">

                  <p class="text-success text-bold">Listing
                </p>
                <div class="form-group">
                  <label for="property_type" class="col-sm-3 control-label">Property Type
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::select('property_type', $property_type, '', ['class' => 'form-control', 'id' => 'property_type', 'placeholder' => 'Select...']) !!}
                  </div>
                </div>

                <div class="form-group">
                  <label for="property_type" class="col-sm-3 control-label">Type of Listing
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6 cls_txtadmin">
                          <div class="col-sm-6" style="padding: 5px 0;">
                            <input type="radio" name="type" value="Single" ng-model="type" ng-change="type_change(type);" checked><span>Single</span>
                          </div>
                          <div class="col-sm-6">
                            <input type="radio" name="type" ng-model="type" id="type" value="Multiple" ng-change="type_change(type);"><span>Multiple room </span>
                          </div>
                        </div>
                </div>

                <span ng-hide="type=='Multiple'">
                <div class="form-group" ng-hide="type=='Multiple'">
                  <label for="room_type" class="col-sm-3 control-label">Room Type
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::select('room_type', $room_type, '', ['class' => 'form-control', 'id' => 'room_type', 'placeholder' => 'Select...']) !!}
                  </div>
                </div>

                <div class="form-group" ng-hide="type=='Multiple'">
                  <label for="accommodates" class="col-sm-3 control-label">Accommodates
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::select('accommodates', $accommodates, '', ['class' => 'form-control', 'id' => 'accommodates', 'placeholder' => 'Select...']) !!}
                  </div>
                </div>
                  
                  <div class="form-group" ng-init="bed_types_name ={{json_encode($first_bed_type1)}};bathrooms={{json_encode($get_bathrooms)}};firstbedtypeid={{$firstbedtypeid}};bedrooms={{ @$result->bedrooms ? @$result->bedrooms : '0' }};all_bed_type={{$bed_type}};get_single_bed_type = {{json_encode($get_single_bed_type)}};get_common_bed_type = {{json_encode($get_common_bed_type)}};get_common_bathrooms = {{json_encode($get_common_bathrooms)}}" ng-hide="type=='Multiple'">
                    <label for="bedrooms" class="col-sm-3 control-label">Bedrooms
                      <em class="text-danger">*
                      </em>
                    </label>
                    <div class="col-sm-6" >
                      <select id="bedrooms" name="bedrooms" ng-model="bedrooms" ng-change="bedrooms_changes();">
                        @for($i=0; $i<=10;$i++)
                        <option value="{{$i}}"> {{$i}}</option>
                        @endfor
                      </select>
                    </div>
                  </div>
                
                  <div class="bed_room_types clearfix" ng-if="bedrooms>0"  ng-repeat="(room_no,all_beds) in bed_types_name | orderObjectBy:'room_no'"  ng-init="show_bed_room[room_no]=0;" ng-hide="room_no==0" ng-hide="type=='Multiple'">
                    <div class="form-group" ng-hide="type=='Multiple'">
                      <!--   @{{all_beds}} -->
                      <label for="bedrooms" class="col-sm-3 control-label"> 
                        Bedroom @{{room_no}}
                      </label>
                      <div class="col-sm-6">
                        <span ng-click="show_bded_room(room_no);show_bed_icon(bed_types_name[room_no],room_no,'bedroom');" style="float: right;" class="btn btn-primary">
                          <span ng-if="total_bed_count(bed_types_name[room_no]) > 0" ng-show="!show_bed_room[room_no]"> Edit Beds </span>
                          <span ng-if="total_bed_count(bed_types_name[room_no]) < 1" ng-show="!show_bed_room[room_no]"> Add Beds </span>
                          <span  ng-show="show_bed_room[room_no]"> Done </span>
                        </span>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="bedrooms" class="col-sm-3 control-label pt-0">@{{total_bed_count(bed_types_name[room_no])}} bed<span ng-if="total_bed_count(bed_types_name[room_no]) > 1">s</span> </label>
                      <div class="col-sm-6" ng-show="!show_bed_room[room_no]"> 
                        <span ng-repeat="(ids,bed_name) in bed_types_name[room_no] | nonZeroElem : 'count'" ng-show="bed_name.count" class="cls_bed_span"> <span> </span> &nbsp;@{{bed_name.count}} @{{bed_name.name}}<span ng-hide="$last">, </span> </span>
                      </div>
                    </div>
                    <div class="row-space-2 moreopt" id="beds_show" ng-show="show_bed_room[room_no]" style="border-bottom: 0px solid #ccc;">
                      <div class="form-group label-values" ng-repeat="(key,beds_id) in all_beds">
                        <label for="bedrooms" class="col-sm-3 control-label">
                          @{{ beds_id.name}} 
                        </label>
                        <div class="col-sm-6 text-left cls_mar_left5"  data="@{{beds_id.id}}">
                          <div class="value-btn-wrap">
                            <button ng-disabled="beds_id.count==0" class="value-button" id="decrease" ng-click="beds_id.count=beds_id.count-1;save_room_types();show_bed_icon(bed_types_name[room_no],room_no,'bedroom');bed_count_valid();" value="Decrease Value" type="button">-</button>
                            <input type="hidden" id="bed_count" name="bed_count[]" value="@{{room_no}}">
                            <input type="hidden" id="bed_id" name="bed_id[]" value="@{{beds_id.id}}">
                            <input type="text" class="guest-select" id="bedtype" name="bed_types_name[]" ng-model="beds_id.count"  readonly="readonly" onfocus="this.blur()" />
                            <button ng-disabled="beds_id.count == 5" class="value-button" id="increase" ng-click="beds_id.count=beds_id.count-0+1;save_room_types();show_bed_icon(bed_types_name[room_no],room_no,'bedroom');bed_count_valid();" value="Increase Value" type="button">+</button>
                          </div>
                        </div>
                      </div>
                      <div id="form-group bedtype-select" ng-hide="all_bed_type.length - (obj_size(all_beds)-1) <= 0"> 
                        <label for="bedrooms" class="col-sm-3 control-label"></label>
                        <div class="col-sm-6">
                          <select ng-init="beds1[room_no]='';" ng-change="add_bed_types(room_no,beds1[room_no]);show_bed_icon(bed_types_name[room_no],room_no,'bedroom');" ng-model="beds1[room_no]" id="1asfasdbasics-select-bed_type_@{{room_no}}" name="bed_type1" data-saving="basics1" >
                            <option value="" selected="" disabled="">{{ trans('messages.lys.select') }}...</option>
                            <option ng-repeat="beds in all_bed_type track by $index" value="@{{beds.id }}" ng-if="bed_type_item_available(beds.id,all_beds)" >@{{ beds.name }}@{{key}}</option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>
                </span>

                </div>

                <div class="bed_room_types clearfix" ng-hide="type=='Multiple'">
                  <div class="form-group">
                    <!--   @{{all_beds}} -->
                    <label for="bedrooms" class="col-sm-3 control-label"> 
                      Common Space 
                    </label>
                    <div class="col-sm-6">
                      <span ng-click="show_common_bded_room();show_bed_icon(get_common_bed_type,'','common space');" style="float: right;" class="btn btn-primary">
                        <span ng-if="total_bed_count(get_common_bed_type) > 0" ng-show="!common_bed"> Edit Beds </span>
                        <span ng-if="total_bed_count(get_common_bed_type) < 1" ng-show="!common_bed"> Add Beds </span>
                        <span ng-show="common_bed"> Done </span>
                      </span>
                    </div>
                  </div>
                  <div class="form-group">
                    <label for="bedrooms" class="col-sm-3 control-label pt-0">@{{total_bed_count(get_common_bed_type)}}  bed<span ng-if="total_bed_count(get_common_bed_type) > 1">s</span></label>
                    <div class="col-sm-6" ng-show="!common_bed"> 
                      <span ng-repeat="bed_name in get_common_bed_type | filter:myFilters1  | nonZeroElem : 'count'" > 
                        <span ng-show="bed_name.count" class="cls_bed_span"> @{{bed_name.count}} @{{bed_name.name}}</span><span ng-show="bed_name.count" class="cls_bed_span"><span ng-hide="$last">,</span> </span></span>
                      </div>
                    </div>

                    <div class="row-space-2 moreopt" id="beds_show" ng-init="common_bed=0" ng-show="common_bed" style="border-bottom: 0px solid #ccc;">
                      <div class="form-group m-0">
                        <div class="form-group label-values" ng-repeat="(key,beds_id) in get_common_bed_type | toArray">
                          <label for="bedrooms" class="col-sm-3 control-label">
                            @{{ beds_id.name}} 
                          </label>
                          <div class="col-sm-6 text-left cls_mar_left5"  data="@{{beds_id.id}}">
                            <div class="value-btn-wrap">
                              <button ng-disabled="beds_id.count==0" class="value-button" id="decrease" ng-click="beds_id.count=beds_id.count-1;show_bed_icon(get_common_bed_type,'','common space ');" value="Decrease Value" type="button">-</button>
                              <input type="hidden" id="common_bed_count" name="common_bed_count[]" value="common">
                              <input type="hidden" id="common_bed_id" name="common_bed_id[]" value="@{{beds_id.id}}">
                              <input type="text" class="guest-select" id="bedtype" name="common_bed_types_name[]" ng-model="beds_id.count"  readonly="readonly" onfocus="this.blur()" />
                              <button ng-disabled="beds_id.count == 5" class="value-button" id="increase" ng-click="beds_id.count=beds_id.count-0+1;show_bed_icon(get_common_bed_type,'','common space ');" value="Increase Value" type="button">+</button>
                            </div>
                          </div>
                        </div>

                        <div id="form-group bedtype-select" ng-hide="all_bed_type.length-obj_size(get_common_bed_type) <= 0"> <label for="bedrooms" class="col-sm-3 control-label"></label>
                          <div class="col-sm-6">
                            <select ng-init="common_beds='';" ng-change="add_common_bed_types(common_beds);show_bed_icon(get_common_bed_type,'','common space ');" ng-model="common_beds" id="1asfasdbasics-select-bed_type" name="common_bed_type1" data-saving="basics1">    
                              <option value="" selected="" disabled="">{{ trans('messages.lys.select') }}...</option>
                              <option ng-repeat="beds in all_bed_type track by $index" value="@{{beds.id }}" ng-if="bed_type_item_available(beds.id,get_common_bed_type)" >@{{ beds.name }}@{{key}}</option>
                            </select>
                          </div>
                        </div>
                      </div>
                    </div>
                  </div>

                  <div class="bed_room_types clearfix"  ng-hide="type=='Multiple'">
                    <div class="form-group label-values" ng-init='bathrooms=0'>
                      <label for="bathrooms" class="col-sm-3 control-label">
                        Bathrooms
                      </label>                      
                      <div class="col-sm-6 text-left cls_mar_left5">
                        <div class="value-btn-wrap">
                          <button ng-disabled="bathrooms==0" class="value-button" id="decrease" ng-click="bathrooms=bathrooms-0.5;" value="Decrease Value" type="button">-</button>
                          <input type="text" class="guest-select" id="bathrooms" name="bathrooms" ng-model="bathrooms"  readonly="readonly" onfocus="this.blur()" />
                          <button ng-disabled="bathrooms == 10" class="value-button" id="increase" ng-click="bathrooms=bathrooms+0.5;" value="Increase Value" type="button">+</button>
                        </div>
                      </div>
                    </div>
                    <div class="form-group">
                      <label for="bathrooms" class="col-sm-3 control-label">
                        Are any of the bathrooms private?
                      </label>
                      <div>
                        <div class="col-sm-6 cls_txtadmin">
                          <div class="col-sm-12 text-left cls_mar_left5" style="padding: 5px 0;">
                            <input type="radio" name="bathroom_shared" value="No" checked><span>Yes</span>
                          </div>
                          <div class="col-sm-12 text-left cls_mar_left5" style="padding: 5px 0;">
                            <input type="radio" name="bathroom_shared" value="Yes"><span>No,they're shared</span>
                          </div>
                        </div>
                      </div>
                    </div>


                  </div>
                
                
              </fieldset>
            </div>
            <div id="sf3" class="frm" data-step-name="Description">
              <fieldset class="box-body">
                <div class="form-group" >
                  <label for="language" class="col-sm-3 control-label">Language
                  </label>
                  <div class="col-sm-6">
                    {!! Form::select('language[]', $language, 'en', ['class' => 'form-control check go', 'id' => 'language','disabled']) !!}
                  </div>
                </div>
                <div class="form-group">
                  <label for="name" class="col-sm-3 control-label">Listing Name
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::text('name[]', '', ['class' => 'form-control', 'id' => 'name', 'placeholder' => 'Be clear and descriptive']) !!}
                  </div>
                </div>
                <div class="form-group">
                  <label for="summary" class="col-sm-3 control-label">Summary
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::textarea('summary[]', '', ['class' => 'form-control', 'id' => 'summary', 'placeholder' => 'Tell travelers what you love about the space. You can include details about the decor, the amenities it includes, and the neighborhood.', 'rows' => 5]) !!}
                  </div>
                </div>
                <div class="form-group">
                  <label for="space" class="col-sm-3 control-label">Space
                  </label>
                  <div class="col-sm-6">
                    {!! Form::textarea('space[]', '', ['class' => 'form-control', 'id' => 'space', 'rows' => 5]) !!}
                  </div>
                </div>
                <div class="form-group">
                  <label for="access" class="col-sm-3 control-label">Guest Access
                  </label>
                  <div class="col-sm-6">
                    {!! Form::textarea('access[]', '', ['class' => 'form-control', 'id' => 'access', 'rows' => 5]) !!}
                  </div>
                </div>
                <div class="form-group">
                  <label for="interaction" class="col-sm-3 control-label">Interaction with Guests
                  </label>
                  <div class="col-sm-6">
                    {!! Form::textarea('interaction[]', '', ['class' => 'form-control', 'id' => 'interaction', 'rows' => 5]) !!}
                  </div>
                </div>
                <div class="form-group">
                  <label for="notes" class="col-sm-3 control-label">Other Things to Note
                  </label>
                  <div class="col-sm-6">
                    {!! Form::textarea('notes[]', '', ['class' => 'form-control', 'id' => 'notes', 'rows' => 5]) !!}
                  </div>
                </div>
                <div class="form-group">
                  <label for="house_rules" class="col-sm-3 control-label">House Rules
                  </label>
                  <div class="col-sm-6">
                    {!! Form::textarea('house_rules[]', '', ['class' => 'form-control', 'id' => 'house_rules', 'rows' => 5]) !!}
                  </div>
                </div>
                <div class="form-group">
                  <label for="neighborhood_overview" class="col-sm-3 control-label">Overview
                  </label>
                  <div class="col-sm-6">
                    {!! Form::textarea('neighborhood_overview[]', '', ['class' => 'form-control', 'id' => 'neighborhood_overview', 'rows' => 5]) !!}
                  </div>
                </div>
                <div class="form-group">
                  <label for="transit" class="col-sm-3 control-label">Getting Around
                  </label>
                  <div class="col-sm-6">
                    {!! Form::textarea('transit[]', '', ['class' => 'form-control', 'id' => 'transit', 'rows' => 5]) !!}
                  </div>
                </div>
                <div ng-repeat="choice in rows">
                  <div class="form-group" data-index="@{{ $index }}">
                    <label for="language" class="col-sm-3 control-label">Language
                    </label>
                    <div class="col-sm-6">
                      {!! Form::select('language[]', $language, '', ['class' => 'form-control go', 'id' => 'language@{{ $index }}','placeholder' => 'Select...']) !!}
                    </div>
                  </div>
                  <div class="form-group" data-index="@{{ $index }}">
                    <label for="name" class="col-sm-3 control-label">Listing Name
                      <em class="text-danger">*
                      </em>
                    </label>
                    <div class="col-sm-6">
                      {!! Form::text('name[]', '', ['class' => 'form-control', 'id' => 'name', 'placeholder' => 'Be clear and descriptive']) !!}
                    </div>
                  </div>
                  <div class="form-group" data-index="@{{ $index }}">
                    <label for="summary" class="col-sm-3 control-label">Summary
                      <em class="text-danger">*
                      </em>
                    </label>
                    <div class="col-sm-6">
                      {!! Form::textarea('summary[]', '', ['class' => 'form-control', 'id' => 'summary', 'placeholder' => 'Tell travelers what you love about the space. You can include details about the decor, the amenities it includes, and the neighborhood.', 'rows' => 5]) !!}
                    </div>
                  </div>
                  <div class="form-group" data-index="@{{ $index }}">
                    <label for="space" class="col-sm-3 control-label">Space
                    </label>
                    <div class="col-sm-6">
                      {!! Form::textarea('space[]', '', ['class' => 'form-control', 'id' => 'space', 'rows' => 5]) !!}
                    </div>
                  </div>
                  <div class="form-group" data-index="@{{ $index }}">
                    <label for="access" class="col-sm-3 control-label">Guest Access
                    </label>
                    <div class="col-sm-6">
                      {!! Form::textarea('access[]', '', ['class' => 'form-control', 'id' => 'access', 'rows' => 5]) !!}
                    </div>
                  </div>
                  <div class="form-group" data-index="@{{ $index }}">
                    <label for="interaction" class="col-sm-3 control-label">Interaction with Guests
                    </label>
                    <div class="col-sm-6">
                      {!! Form::textarea('interaction[]', '', ['class' => 'form-control', 'id' => 'interaction', 'rows' => 5]) !!}
                    </div>
                  </div>
                  <div class="form-group" data-index="@{{ $index }}">
                    <label for="notes" class="col-sm-3 control-label">Other Things to Note
                    </label>
                    <div class="col-sm-6">
                      {!! Form::textarea('notes[]', '', ['class' => 'form-control', 'id' => 'notes', 'rows' => 5]) !!}
                    </div>
                  </div>
                  <div class="form-group" data-index="@{{ $index }}">
                    <label for="house_rules" class="col-sm-3 control-label">House Rules
                    </label>
                    <div class="col-sm-6">
                      {!! Form::textarea('house_rules[]', '', ['class' => 'form-control', 'id' => 'house_rules', 'rows' => 5]) !!}
                    </div>
                  </div>
                  <div class="form-group" data-index="@{{ $index }}">
                    <label for="neighborhood_overview" class="col-sm-3 control-label">Overview
                    </label>
                    <div class="col-sm-6">
                      {!! Form::textarea('neighborhood_overview[]', '', ['class' => 'form-control', 'id' => 'neighborhood_overview', 'rows' => 5]) !!}
                    </div>
                  </div>
                  <div class="form-group" data-index="@{{ $index }}">
                    <label for="transit" class="col-sm-3 control-label">Getting Around
                    </label>
                    <div class="col-sm-6">
                      {!! Form::textarea('transit[]', '', ['class' => 'form-control', 'id' => 'transit', 'rows' => 5]) !!}
                    </div>
                  </div>
                  <a class="pull-right btn remove-room-btn" href="javascript:void(0);" ng-click="removeRow($index)">
                    <span> Remove </span>
                  </a>
                  <br>
                </div>
                <a class="btn add-room-btn"  href="javascript:void(0);" ng-click="addNewRow()"> Add
                </a>
              </fieldset>
              <!-- change end -->
            </div>

            <div id="sf4" class="frm" data-step-name="Location">
              <fieldset class="box-body">
                <div class="form-group">
                  <label for="country" class="col-sm-3 control-label">Country
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::select('country', $country, '', ['class' => 'form-control', 'id' => 'country', 'placeholder' => 'Select...']) !!}
                  </div>
                </div> 
                <div class="form-group">
                  <label for="address_line_1" class="col-sm-3 control-label">Address Line 1
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::text('address_line_1', '', ['class' => 'form-control', 'id' => 'address_line_1', 'placeholder' => 'House name/number + street/road', 'autocomplete' => 'off']) !!}
                  </div>
                </div>  
                <div class="form-group">
                  <label for="address_line_2" class="col-sm-3 control-label">Address Line 2
                  </label>
                  <div class="col-sm-6">
                    {!! Form::text('address_line_2', '', ['class' => 'form-control', 'id' => 'address_line_2', 'placeholder' => 'Apt., suite, building access code']) !!}
                  </div>
                </div>    
                <div class="form-group">
                  <label for="city" class="col-sm-3 control-label">City / Town / District
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::text('city', '', ['class' => 'form-control', 'id' => 'city', 'placeholder' => '']) !!}
                  </div>
                </div>     
                <div class="form-group">
                  <label for="state" class="col-sm-3 control-label">State / Province / County / Region
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::text('state', '', ['class' => 'form-control', 'id' => 'state', 'placeholder' => '']) !!}
                  </div>
                </div>     
                <div class="form-group">
                  <label for="postal_code" class="col-sm-3 control-label">ZIP / Postal Code
                  </label>
                  <div class="col-sm-6">
                    {!! Form::text('postal_code', '', ['class' => 'form-control', 'id' => 'postal_code', 'placeholder' => '']) !!}
                  </div>
                </div>  
         <input type="hidden" name="latitude" id="latitude" class="do-not-ignore">         
                <input type="hidden" name="longitude" id="longitude">         
              </fieldset>
            </div>

            <div id="sf5" class="frm" data-step-name="Amenities">
              <fieldset class="box-body">
               @foreach($amenities_type as $amenity_type)
               <h4>{{$amenity_type->name}}</h4>
               <ul class="list-unstyled col-xs-12 col-sm-12 col-md-12" id="triple" style="width:100% !important;margin-bottom: 20px;">
                @foreach($amenities as $row)
                @if($row->type_id == $amenity_type->id)
                <li class="col-xs-12 col-sm-4 col-md-4">
                  <span class="label-large label-inline amenity-label pull-left" style="width:100% !important;">
                    <label>
                      <input class="pull-left" type="checkbox" value="{{ $row->id }}" name="amenities[]">
                      <span class="pull-left" style="margin-left:8px;white-space:normal;"> {{ $row->name }}
                      </span>
                    </label>
                  </span>
                </li>
                  @endif
                  @endforeach
                </ul>
                @endforeach
              </fieldset>
            </div>

            <div id="sf6" class="frm" data-step-name="Photos">
              <fieldset class="box-body">
                <div class="form-group">
                  <label for="night" class="col-sm-3 control-label">Photos
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    <input type="file" name="photos[]" multiple="true" id="upload_photos" >
                  </div>
                </div>
              </fieldset>
            </div>

           


            <div id="sf7" class="frm" data-step-name="Video">
              <fieldset class="box-body">
                <div class="form-group">
                  <label class="col-sm-3 control-label">YouTube URL
                  </label>
                  <div class="col-sm-6">
                    <input type="text" class="form-control" name="video" id="video">
                  </div>
                </div>
              </fieldset>
            </div>
            <div id="sf8" class="frm" data-step-name="Pricing">
              <fieldset class="box-body">
                <div class="form-group">
                  <label for="night" class="col-sm-3 control-label">Night
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::text('night', '', ['class' => 'form-control', 'id' => 'night', 'placeholder' => '']) !!}
                    <span id="price_wrong_message" class="text-danger">
                    </span>
                  </div>
                </div>
                <div class="form-group">
                  <label for="currency_code" class="col-sm-3 control-label">Currency Code
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::select('currency_code', $currency, '', ['class' => 'form-control', 'id' => 'currency_code']) !!}
                  </div>
                </div> 
                <div class="form-group">
                  <label for="cleaning" class="col-sm-3 control-label">Cleaning
                  </label>
                  <div class="col-sm-6">
                    {!! Form::text('cleaning', '', ['class' => 'form-control', 'id' => 'cleaning', 'placeholder' => '']) !!}
                  </div>
                </div> 
                <div class="form-group">
                  <label for="additional_guest" class="col-sm-3 control-label">Additional Guest
                  </label>
                  <div class="col-sm-6">
                    {!! Form::text('additional_guest', '', ['class' => 'form-control', 'id' => 'additional_guest', 'placeholder' => '']) !!}
                  </div>
                </div> 
                <div class="form-group">
                  <label for="guests" class="col-sm-3 control-label">Guests
                  </label>
                  <div class="col-sm-6">
                    {!! Form::select('guests', $accommodates, 1, ['class' => 'form-control', 'id' => 'guests']) !!}
                  </div>
                </div> 
                <div class="form-group">
                  <label for="security" class="col-sm-3 control-label">Security
                  </label>
                  <div class="col-sm-6">
                    {!! Form::text('security', '', ['class' => 'form-control', 'id' => 'security', 'placeholder' => '']) !!}
                  </div>
                </div> 
                <div class="form-group">
                  <label for="weekend" class="col-sm-3 control-label">Weekend
                  </label>
                  <div class="col-sm-6">
                    {!! Form::text('weekend', '', ['class' => 'form-control', 'id' => 'weekend', 'placeholder' => '']) !!}
                  </div>
                </div> 
              </fieldset>
            </div>
            <div id="sf9" class="frm" data-step-name="Booking Type">
              <fieldset class="box-body">
                <div class="form-group">
                  <label for="booking_type" class="col-sm-3 control-label">Booking Type
                  </label>
                  <div class="col-sm-6">
                    {!! Form::select('booking_type', ['request_to_book'=>'Request To Book', 'instant_book'=>'Instant Book'], 'request_to_book', ['class' => 'form-control', 'id' => 'booking_type']) !!}
                  </div>
                </div>
              </fieldset>
            </div>
            <div id="sf10" class="frm" data-step-name="Terms">
              <fieldset class="box-body">
                <div class="form-group">
                  <label for="cancel_policy" class="col-sm-3 control-label">Cancellation Policy
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::select('cancel_policy', ['Flexible'=>'Flexible', 'Moderate'=>'Moderate','Strict'=>'Strict'], '', ['class' => 'form-control', 'id' => 'cancel_policy', 'placeholder' => 'Select...']) !!}
                  </div>
                </div>
              </fieldset>
            </div>
            <div id="sf11" class="frm" data-step-name="User">
              <fieldset class="box-body">
                <div class="form-group">
                  <label for="user_id" class="col-sm-3 control-label">Username
                    <em class="text-danger">*
                    </em>
                  </label>
                  <div class="col-sm-6">
                    {!! Form::select('user_id', $users_list, '', ['class' => 'form-control', 'id' => 'user_id', 'placeholder' => 'Select...']) !!}
                  </div>
                </div>
              </fieldset>
            </div>
            <div id="sf12" class="frm" data-step-name="Price Rules">
              <fieldset class="box-body">
                <div class="row price_rules">
                  <div class="col-md-8 col-md-offset-2" ng-init="length_of_stay_items = [];">
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
                  <div class="col-md-8 col-md-offset-2" ng-init="early_bird_items = {{json_encode([])}};">
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
                  <div class="col-md-8 col-md-offset-2" ng-init="last_min_items = {{json_encode([])}};">
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
            </div>



            <div id="sf13" class="frm" data-step-name="Availability Rules">
              <fieldset class="box-body">
                <div class="row availability_rules">
                  <div class="col-md-8 col-md-offset-2" ng-init="availability_rules = {{json_encode([] )}};">
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
                                  <input type="text" value="" name="minimum_stay" class="form-control minimum_stay" id="minimum_stay" placeholder="Minimum Stay" data-error-placement="container" data-error-container="#minimum_stay_error_container" >
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
                                  <input type="text" value="" name="maximum_stay" class="form-control maximum_stay" id="maximum_stay" data-minimum_stay="#minimum_stay" placeholder="Maximum Stay" data-error-placement="container" data-error-container="#maximum_stay_error_container" >
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
                        <div class="row" ng-init="is_custom_exits = false">
                          <div class="col-md-12 text-center">
                            <a href="javascript:void(0)" class="btn btn-success" ng-click="is_custom_exits = true; add_availability_rule()">
                              Add custom Rule
                            </a>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="panel panel-info" ng-show="is_custom_exits">
                      <div class="panel-body">
                        <div class="row">
                          <div class="col-sm-12 row-space-top-1" ng-repeat="item in availability_rules" ng-init="saved_item = copy_data(item)">
                            <input type="hidden" name="availability_rules[@{{$index}}][id]" value="@{{item.id}}">
                            <input type="hidden" name="availability_rules[@{{$index}}][edit]" value="@{{availability_rules[$index].edit}}">
                            <div class="row" ng-if="item.id" ng-init="availability_rules[$index].edit = item.id != '' ? true : false" ng-show="availability_rules[$index].edit">
                              <div class="col-md-12">
                                <div class="row">
                                  <div class="col-md-8 col-md-offset-2" style="border: 1px solid #cfcfcf; padding: 10px;">
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
                                  <select name="availability_rules[@{{$index}}][type]" class="form-control required" id="availability_rules_@{{$index}}_type" ng-model="availability_rules[$index]['type']" ng-click="availability_rules_type_change($index);" >
                                    <option value="" ng-disabled="item.type != ''" ng-if="!item.id">
                                      Select Dates
                                    </option>
                                    <option value="prev" data-start_date="@{{saved_item.start_date}}" data-end_date="@{{saved_item.end_date}}" ng-if="item.id">
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
                                <div class="col-md-3">
                                  <input type="text" readonly name="availability_rules[@{{$index}}][start_date]" class="form-control required" id="availability_rules_@{{$index}}_start_date" placeholder="Start Date" ng-model="availability_rules[$index]['start_date']" >
                                </div>
                                <div class="col-md-3">
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
            </div>


      <div id="sf14" class="frm multiple_rooms" data-step-name="Add Room">
        <div class="add_room_but">
          <a class="pull-right btn add-room-btn"  href="javascript:void(0);" ng-click="addNewRows()"> Add
          </a>
        </div>
       <!--  <p class="text-danger">Note(photo upload): Maximum 10 MB</p> -->

        <fieldset class="box-body" id="clone_" name="room_fieldset">

          <div ng-repeat="choice in row">
            <div class="form-group edit_room_cot" data-index="@{{ $index }}">
              <label for="room_name" class="col-md-3 control-label" id="room_name">Name<em class="text-danger">*</em></label>
              <div class="col-md-6">
                <p class="text-right">
                  <span ng-bind="35 - multiple_room_name[$index].length">35</span> characters left
                </p>
                {!! Form::text('room_name[]', '', ['class' => 'form-control required', 'id' => 'room_name_@{{$index}}', 'placeholder' => 'Room Name','maxlength'=>'35','ng-model'=>'multiple_room_name[$index]']) !!}
              </div>
            </div> 
            <div class="form-group edit_room_cot" data-index="@{{ $index }}">
              <label for="room_description" class="col-md-3 control-label" id="room_description">Description<em class="text-danger">*</em></label>
              <div class="col-md-6">
                <p class="text-right">
                  <span ng-bind="500 - multiple_room_description[$index].length">500</span> characters left
                </p>
                {!! Form::textarea('room_description[]', '', ['class' => 'form-control required', 'id' => 'room_description_@{{$index}}', 'rows' => 5,'maxlength'=>'500','ng-model'=>'multiple_room_description[$index]']) !!}
              </div>
            </div>
            <div class="form-group" data-index="@{{ $index }}">
              <label for="room_photos" class="col-md-3 control-label">Room Photos<em class="text-danger">*</em></label>
              <div class="col-md-6">
                <input type="file" class="room_photos_@{{ $index }} room_photos1 upload_photos" name="room_photos[@{{ $index }}][]" multiple="true" id="upload_photos_@{{$index}}" data-index="@{{ $index }}" required>
                <span id="photos_errors_@{{$index}}" class="text-danger photos_errors11"></span>
                <span class="hidden photos_error1_@{{ $index }}" style="color:red;display: block;margin-bottom: 10px;">Photos must be at least 720x480 pixels. Please upload a photo of higher quality.</span>
                <span class="hidden photos_error_@{{ $index}}" style="color:red;display: block;margin-bottom: 10px;">Please upload the images like JPG,JPEG,PNG,GIF File Only.</span>
              </div>

            </div>
            <div class="form-group" data-index="@{{ $index }}">
              <label for="accommodates" class="col-md-3 control-label">Room Type<em class="text-danger">*</em></label>
              <div class="col-md-6">
                {!! Form::select('room_type_multiple[]', $room_type_multiple, '', ['class' => 'form-control multiple_room_type required','id' => 'multiple_room_type_@{{$index}}', 'placeholder' => 'Select...']) !!}
              </div>
            </div>
            <div class="form-group" data-index="@{{ $index }}">
              <label for="accommodates" class="col-md-3 control-label">Accommodates<em class="text-danger">*</em></label>
              <div class="col-md-6">
                {!! Form::select('room_accommodates[]', $accommodates, '', ['class' => 'form-control room_accommodates required','id' => 'room_accommodates_@{{$index}}', 'placeholder' => 'Select...']) !!}
              </div>
            </div>
         
            <div class="form-group" data-index="@{{ $index }}">
              <label for="number_of_rooms" class="col-md-3 control-label">Number of Rooms<em class="text-danger">*</em></label>
              <div class="col-md-6">
                {!! Form::number('number_of_rooms[]',  '', ['class' => 'form-control required', 'id' => 'number_of_rooms_@{{$index}}', 'placeholder' => 'Number of Rooms','value'=>'1','min'=>'1','max'=>'100']) !!}
              </div>
            </div>                
            <div class="form-group" data-index="@{{ $index }}">
              <label for="room_bedrooms" class="col-md-3 control-label">Bedrooms<em class="text-danger">*</em></label>
              <div class="col-md-6">
                {!! Form::select('room_bedrooms[]', $bedrooms, '', ['class' => 'form-control room_bedrooms required', 'id' => 'room_bedrooms_@{{$index}}', 'placeholder' => 'Select...']) !!}
              </div>
            </div>
            {{--
            <div class="form-group" data-index="@{{ $index }}">
              <label for="room_beds" class="col-md-3 control-label">Beds<em class="text-danger">*</em></label>
              <div class="col-md-6">
                {!! Form::select('room_beds[]', $beds, '', ['class' => 'form-control room_beds required', 'id' => 'room_beds_@{{$index}}', 'placeholder' => 'Select...']) !!}
              </div>
            </div>
            <div class="form-group" data-index="@{{ $index }}">
              <label for="room_bed_type" class="col-md-3 control-label">Bed Type<em class="text-danger">*</em></label>
              <div class="col-md-6">
                {!! Form::select('room_bed_type[]', $bed_type, '', ['class' => 'form-control room_bed_type required', 'id' => 'room_bed_type_@{{$index}}', 'placeholder' => 'Select...']) !!}
              </div>
            </div>
            --}}
            <div class="form-group" data-index="@{{ $index }}">
              <label for="room_bathrooms" class="col-md-3 control-label">Bathrooms<em class="text-danger">*</em></label>
              <div class="col-md-6">
                {!! Form::select('room_bathrooms[]', $bathrooms1, '', ['class' => 'form-control room_bathrooms required', 'id' => 'room_bathrooms_@{{$index}}', 'placeholder' => 'Select...']) !!}
              </div>
            </div>

            <div class="form-group" data-index="@{{ $index }}" ng-init="index_new = $index ">
              <div class="" ng-init="multiple_rooms_bed_type_options= {{json_encode($bed_types)}};type_beds={{json_encode($beds)}};multiple_rooms_bed_type_options_length={{count($bed_types)}}">
                <label for="guests" class="col-md-3 control-label"> Bed Type & Beds 
                  <em class="text-danger">*
                  </em>
                </label>
                <div class="col-md-6 length_discount" ng-init="multiple_rooms_bed_type_items[index_new] = ''">
                  <div class="row">
                    <div class="col-sm-12" ng-repeat="item in multiple_rooms_bed_type_items[index_new]">
                      <div class="row">
                        <div class="col-md-5">
                          <div class="select_custom">
                  <select name="multiple_rooms_bed_type[@{{$parent.$index}}][@{{$index}}][bed_type]" class="form-control" id="multiple_rooms_bed_type_@{{$index}}" ng-model="item.bed_type">
                              <option disabled>
                                Select
                              </option>
                              <option ng-repeat="option in multiple_rooms_bed_type_options" ng-if="multiple_rooms_bed_type_option_avaialble(option.id,index_new) || option.id == item.bed_type" ng-selected="item.bed_type == option.id" value="@{{option.id}}">
                                @{{option.name}}
                              </option>
                            </select>
                          </div>
                        </div>
                      <div class="col-md-5" id="discount_error_container_@{{index_new}}_@{{$index}}">
                          <div class="select_custom">
                    <select name="multiple_rooms_bed_type[@{{$parent.$index}}][@{{$index}}][beds]" class="form-control" id="multiple_rooms_beds_@{{$index}}" ng-model="item.beds">
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
                          <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="multiple_rooms_bed_type_remove($index,index_new)">
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
                            <select name="" class="form-control multiple_rooms_bed_type_select  @{{(multiple_rooms_bed_type_items[index_new].length<1)? 'multiple_rooms_bed_type_select_req' : ''}}" id="multiple_rooms_bed_type_select_@{{index_new}}" data-old_index="@{{index_new}}">
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
                  </div>
                </div>
              </div>
            </div>


            <div class="form-group" data-index="@{{ $index }}">
              <label for="room_night" class="col-md-3 control-label">Nightly Price<em class="text-danger">*</em></label>
              <div class="col-md-6">
                {!! Form::number('room_night[]',  '', ['class' => 'form-control required', 'id' => 'room_night_@{{$index}}', 'placeholder' => 'Per Night Price']) !!}
              </div>
            </div>
            <div class="form-group" data-index="@{{ $index }}">
              <label for="currency_code" class="col-md-3 control-label">Currency Code<em class="text-danger">*</em></label>
              <div class="col-md-6">
                {!! Form::select('room_currency_code[]', $currency, '', ['class' => 'form-control room_currency_code', 'id' => 'room_currency_code_@{{$index}}', 'placeholder' => 'Select...']) !!}
              </div>
            </div>  
               
            <div class="form-group" data-index="@{{ $index }}">
              <label for="security" class="col-md-3 control-label">Security Deposit</label>
              <div class="col-md-6">
                {!! Form::number('room_security[]',  '',['class' => 'form-control', 'id' => 'room_security_@{{$index}}', 'placeholder' => 'Security Deposit']) !!}
              </div>
            </div>
            
            <div class="form-group" data-index="@{{ $index }}">
              <label for="cleaning" class="col-md-3 control-label">Cleaning Fee   
              </label>
              <div class="col-md-6">
                {!! Form::number('room_cleaning[]',  '',['class' => 'form-control', 'id' => 'room_cleaning_@{{$index}}', 'placeholder' => 'Cleaning Fee']) !!}
                 <small>(This fee will apply to every reservation at your listing.)</small>
              </div>
            </div>
            <div class="form-group" data-index="@{{ $index }}">
              <label for="additional_guest_fee" class="col-md-3 control-label">Additional Guest Charge
              </label>
              <div class="col-md-6">
                {!! Form::number('additional_guest_fee[]',  '',['class' => 'form-control', 'id' => 'additional_guest_fee_@{{$index}}', 'placeholder' => 'Additional Guest Charge']) !!}
                <small>(This fee will apply for each additional guest, for each night of the reservation.)</small>
              </div>
            </div>
            <div class="form-group" data-index="@{{ $index }}">
              <label for="guests" class="col-md-3 control-label">Guests</label>
              <div class="col-md-6">
                {!! Form::select('room_guests[]', $accommodates, 1, ['class' => 'form-control room_guests', 'id' => 'room_guests_@{{$index}}']) !!}
              </div>
            </div>
            <div class="form-group" data-index="@{{ $index }}">
              <label for="room_weekend" class="col-md-3 control-label">Weekend Price
              </label>
              <div class="col-md-6">{!! Form::number("weekend_price[]", "", ["class" => "form-control", "id" => 'weekend_price_@{{$index}}', "placeholder" => "Weekend Price"]) !!}
              <small>(This is a nightly price. It will replace your base price for every Friday and Saturday.)</small>
              </div>
            </div>

            <div class="form-group" data-index="@{{ $index }}" ng-init="index1 = $index ">
              <div class="" ng-init="rooms_length_of_stay_options= {{json_encode($length_of_stay_options)}}">
                <label for="guests" class="col-md-3 control-label"> Length of Stay discounts</label>



                <div class="col-md-6 length_discount" ng-init="rooms_length_of_stay_items[index1] = ''">
                  <div class="row">
                    <div class="col-sm-12" ng-repeat="item in rooms_length_of_stay_items[index1]">
                      <div class="row">
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
                            <input type="text" name="rooms_length_of_stay[@{{index1}}][@{{$index}}][discount]" class="form-control discount" id="rooms_length_of_stay_discount_@{{$index}}" placeholder="Percentage of discount" data-error-placement="container" data-error-container="#discount_error_container_@{{index1}}_@{{$index}}">
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


                    <div class="col-md-12" ng-init="rooms_length_of_stay_period_select = ''" ng-show="rooms_length_of_stay_items[index1].length < rooms_length_of_stay_options.length">
                      <div class="row">
                        <div class="col-md-12" >
                          <div class="select_custom">
                            <select name="" class="form-control rooms_length_of_stay_period_select" id="rooms_length_of_stay_period_select_@{{index1}}" data-type="length_of_stay" data-old_index="@{{index1}}">
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
              <div class="col-md-6 col-md-offset-3 add_room_input">
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
                            <a href="javascript:void(0)" class="btn btn-success btn-sm" ng-click="rooms_add_price_rule_edit('early_bird', $index,index2)">
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

            <div class="form-group" data-index="@{{ $index }}" ng-init="index3 = $index ">
             <div class="col-md-6 col-md-offset-3 add_room_input">
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
                            <input type="text" name="rooms_last_min[@{{index3}}][@{{$index}}][period]" class="form-control last_min_period" id="last_min_period_@{{$index}}" ng-model="rooms_last_min_items[index3][$index].period" placeholder="Number of days" data-error-placement="container" data-error-container="#lm_period_error_container_@{{index3}}_@{{$index}}">
                            <span class="input-suffix">
                              Days
                            </span>
                          </div>
                        </div>
                        <div class="col-md-5" id="lm_discount_error_container_@{{index3}}_@{{$index}}">
                          <div class="input-addon">
                            <input type="text" name="rooms_last_min[@{{index3}}][@{{$index}}][discount]" class="form-control discount" id="last_min_discount_@{{$index}}" ng-model="rooms_last_min_items[index3][$index].discount" placeholder="Percentage of discount" data-error-placement="container" data-error-container="#lm_discount_error_container_@{{index3}}_@{{$index}}">
                            <span class="input-suffix">
                              %
                            </span>
                          </div>
                        </div>
                        <div class="col-md-2">
                          <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="rooms_remove_price_rule('last_min', $index,index3)">
                            <span class="fa fa-trash">
                            </span>
                          </a>
                        </div>
                      </div>
                    </div>
                    <div class="col-md-12 row-space-top-2" >
                      <div class="row">
                        <div class="col-md-4" >
                          <a href="javascript:void(0)" class="btn btn-success btn-sm" ng-click="rooms_add_price_rule_edit('last_min', $index,index3)">
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
           <div class="col-md-6 col-md-offset-3 add_room_input">
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
                          <input type="text" value="" name="rooms_minimum_stay[@{{index4}}]" class="form-control minimum_stay" id="rooms_minimum_stay_@{{index4}}" placeholder="Minimum Stay" data-error-placement="container" data-error-container="#minimum_stay_error_container_@{{index4}}" >
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
                          <input type="text" value="" name="rooms_maximum_stay[@{{index4}}]" class="form-control maximum_stay" id="rooms_maximum_stay_@{{index4}}" data-minimum_stay="#rooms_minimum_stay_@{{index4}}" placeholder="Maximum Stay" data-error-placement="container" data-error-container="#maximum_stay_error_container_@{{index4}}" >
                          <span class="input-suffix">
                            Nights
                          </span>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <div class="panel-footer" ng-show="!rooms_is_custom_exits">
                <div class="row" ng-init="rooms_is_custom_exits = false">
                  <div class="col-md-12 text-center">
                    <a href="javascript:void(0)" class="btn btn-success" ng-click="rooms_is_custom_exits = true; rooms_add_availability_rule_edit(index4)">
                      Add custom Rule
                    </a>
                  </div>
                </div>
              </div>
            </div>
            <div class="panel panel-info" ng-show="rooms_is_custom_exits">
              <div class="panel-body">
                <div class="row">
                  <div class="col-sm-12 row-space-top-1" ng-repeat="item in rooms_availability_rules[index4]" ng-init="rooms_saved_item = copy_data(item)">
                    <input type="hidden" name="rooms_availability_rules[@{{index4}}][@{{$index}}][id]" value="@{{item.id}}">
                    <input type="hidden" name="rooms_availability_rules[@{{index4}}][@{{$index}}][edit]" value="@{{availability_rules[$index].edit}}">
                    <div class="row" ng-if="item.id" ng-init="rooms_availability_rules[index4][$index].edit = item.id != '' ? true : false" ng-show="rooms_availability_rules[index4][$index].edit">
                      <div class="col-md-12">
                        <div class="row">
                          <div class="col-md-6 col-md-offset-3 " style="border: 1px solid #cfcfcf; padding: 10px;">
                            <div class="row" >
                              <div class="col-md-8">
                                <p>
                                  During @{{saved_item.during}},
                                </p>
                                <p ng-if="rooms_saved_item.minimum_stay">
                                  guests stay for minimum @{{saved_item.minimum_stay}} nights
                                </p>
                                <p ng-if="rooms_saved_item.maximum_stay">
                                  guests stay for maximum @{{saved_item.maximum_stay}} nights
                                </p>
                              </div>
                              <div class="col-md-4">
                                <a href="javascript:void(0)" class="btn btn-info btn-xs" ng-click="rooms_availability_rules[index4][$index].edit = false">
                                  <span class="fa fa-edit">
                                  </span>
                                </a>
                                <a href="javascript:void(0)" class="btn btn-danger btn-xs" ng-click="rooms_remove_availability_rule($index,index4)">
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
                              <option value="prev" data-start_date="@{{rooms_saved_item.start_date}}" data-end_date="@{{rooms_saved_item.end_date}}" ng-if="item.id">
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
                        <div class="col-md-3">
                          <input type="text" readonly name="rooms_availability_rules[@{{index4}}][@{{$index}}][start_date]" class="form-control required" id="rooms_availability_rules_@{{index4}}_@{{$index}}_start_date" placeholder="Start Date" ng-model="rooms_availability_rules[index4][$index]['start_date']" >
                        </div>
                        <div class="col-md-3">
                          <input type="text" readonly name="rooms_availability_rules[@{{index4}}][@{{$index}}][end_date]" class="form-control required" id="rooms_availability_rules_@{{index4}}_@{{$index}}_end_date" placeholder="End Date" ng-model="rooms_availability_rules[index4][$index]['end_date']" >
                        </div>
                      </div>
                      <div class="form-group">
                        <label class="control-label col-md-4">
                          Minimum Stay
                        </label>
                        <div class="col-md-6" id="availability_minimum_stay_error_container_@{{index4}}_@{{$index}}">
                          <div class="input-addon">
                            <input type="text" name="rooms_availability_rules[@{{index4}}][@{{$index}}][minimum_stay]" class="form-control availability_minimum_stay" id="rooms_availability_rules_@{{index4}}_@{{$index}}_minimum_stay" placeholder="Minimum Stay" ng-model="rooms_availability_rules[index4][$index]['minimum_stay']" data-error-placement="container" data-error-container="#availability_minimum_stay_error_container_@{{index4}}_@{{$index}}" >
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
                        <div class="col-md-6" id="availability_maximum_stay_error_container_@{{index4}}_@{{$index}}">
                          <div class="input-addon">
                            <input type="text" name="rooms_availability_rules[@{{index4}}][@{{$index}}][maximum_stay]" class="form-control availability_maximum_stay" id="rooms_availability_rules_@{{index4}}_@{{$index}}_maximum_stay" data-minimum_stay="#availability_rules_@{{$index}}_minimum_stay" placeholder="Maximum Stay" ng-model="rooms_availability_rules[index4][$index]['maximum_stay']" data-error-placement="container" data-error-container="#availability_maximum_stay_error_container_@{{index4}}_@{{$index}}" >
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

        <div class="form-group add_ament" data-index="@{{ $index }}">
         <label for="amenities" class="col-sm-3 control-label">Amenities</label>
         <div class="col-sm-9">
          <div class="row">
            <ul class="list-unstyled" id="triple">
              @foreach($amenities as $row)
              <li class="col-xs-12 col-sm-6 col-md-4">
                <label class="label-large label-inline amenity-label">
                  <input type="checkbox" class="amenities_multiple" value="{{ $row->id }}" name="room_amenities[@{{ $index + 1 }}][]">
                  <span class="admin_ameniti">{{ $row->name }}</span>
                </label>
              </li>
              @endforeach
            </ul>
          </div>
          <div>
            <span class="delete_room_but @{{(row.length<=1)?'hidden':''}}">
              <a class="pull-right btn remove-room-btn" href="javascript:void(0);" ng-click="removeRows($index)">
                <span > Delete
                </span>
              </a>
            </span>
          </div>
        </div>
      </div>             
      <hr>
    </div>
  </fieldset>
</div>
          <!-- /.box-body -->
          <div class="box-footer footer-info-cls" ng-cloak>
            <button ng-show="step > 0" class="btn btn-warning back2" type="button" ng-click="back_step(step)"><span class="fa fa-arrow-left"></span> Back</button>
            <button class="btn btn-primary open2 pull-right room_add_btn" type="button" ng-click="next_step(step);" >
              <span ng-if="step_name == 'User'">Submit
              </span> 
              <span ng-if="step_name != 'User'  ">Next
              </span>
              <span class="fa fa-arrow-right" ng-if="step_name != 'User'">
              </span>
            </button>
          </div>
          <!-- /.box-footer -->
          {!! Form::close() !!}
        </div>
        <!-- /.box -->
      </div>
      <!--/.col (right) -->
    </div>
    <!-- /.row -->
  </section>
  <!-- /.content -->
</div>
<!-- /.content-wrapper -->
<!-- hide for admin side room_add page settings/skins problems -->
@stop
@push('scripts')
<style type="text/css">
  .price_rules input, .availability_rules input {
    margin-bottom: 0px;
  }
  .input-suffix {
    padding: 6px 10px;
  } 
  /*.frm{ display: block !important; }*/
</style>
@endpush