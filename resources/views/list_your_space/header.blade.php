<div class="manage-listing-header" id="js-manage-listing-header">
  <ul class="listing-nav d-none d-md-flex align-items-center">
    @include('list_your_space.sub_room')
    <li id="collapsed-nav" class="d-none">
      <a href="javascript:void(0)" data-prevent-default="" class="listing-nav-item show-collapsed-nav-link d-flex align-items-center" id="price-id">
        <i class="icon icon-reorder mr-2"></i>
        <span>
          {{ trans('messages.lys.pricing_listing_details') }}…
        </span>
      </a>
    </li>
    <li>

 @if($result->type == 'Single')
       <span id="listing-name">
        <span ng-hide="name">
          {{ ($result->name == '') ? $result->sub_name : $result->name }}
        </span>
        <span ng-show="name">
          <span ng-bind="name"></span>
        </span>
      </span> 
  @endif

      <span class="see-all-listings-link ml-1">
        (<a href="{{ url('rooms') }}">
        {{ trans('messages.lys.see_all_listings') }}
      </a>)
    </span>
  </li>   
  <li class="ml-auto">
    
    <a href="{{ url('rooms/'.$main_room_id.'?preview') }}" data-track="preview" class="listing-nav-item d-flex align-items-center" id="preview-btn" title="Preview your listing as it will appear when active." target="_blank">
      <i class="icon icon-eye mr-2"></i>
      <span>
        {{ ($result->status == NULL) ? trans('messages.lys.preview') : trans('messages.lys.view') }}
      </span>
    </a>


  </li>
</ul>
<ul class="listing-nav has-collapsed-nav d-md-none">
  <li class="show-if-collapsed-nav" id="collapsed-nav">
    <a href="javascript:void(0)" data-prevent-default="" class="listing-nav-item show-collapsed-nav-link d-flex align-items-center">
      <i class="icon icon-reorder mr-2"></i>
      <span>
        {{ trans('messages.lys.pricing_listing_details') }}…
      </span>
    </a>
  </li>
</ul>
</div>
<input type="hidden" id="room_data_type" value="{{$result->type}}">

<div aria-hidden="true" style="" class="modal multiple_room_type add-room-popup-cls" id="anyroom"data-backdrop="static" data-keyboard="false">
  <div class="modal-table">
    <div class="modal-cell">
      {!! Form::open(['url' => 'sub_rooms/create', 'class' => 'host-onboarding-form sub_lys_new', 'accept-charset' => 'UTF-8' , 'name' => 'sub_lys_new', 'id'=>'sub_room_create_form']) !!}
      <!-- Modal content-->
      {!! Form::hidden('sub_title', '', ['id' => 'room_type', 'ng-model' => 'sub_title', 'required' => 'required', 'ng-value' => 'sub_title']) !!}
      <input type="hidden" name="room_id" value="{{$main_room_id }}">
      <div class="modal-content room-content-cls">
        <div class="panel-header add-room-popup-header">
          <h4 class="modal-title m-0 text-center">{{ trans('messages.lys.add_room') }}</h4>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <!-- <span aria-hidden="true">&times;</span> -->
        </button>
        </div>
        <div class="panel-body">
          <div class="form-group clearfix popup-align">
            <!-- <label class="hide delete_text">{{ trans('messages.lys.delete')}}</label> -->
            <label class="col-md-2 control-label title_fild" for="name">{{ trans('messages.wishlist.title')}}</label>
            <div class="col-md-9 inner-popup-cls">
              <input id="name" name="name" placeholder="{{ trans('messages.wishlist.title')}}" class="form-control sub_room_create_name" type="text" ng-model="sub_title" maxlength="35">
                <span ng-bind="35 - sub_title.length" class="h6 title-char-cls">35</span>
                <span class="h6 title-char-cls"> {{ trans('messages.lys.characters_left') }}</span>
            </div>
            <!-- <div class="col-md-9"> 
              <input id="name" name="name" placeholder="{{ trans('messages.wishlist.title')}}" class="form-control sub_room_create_name" type="text" ng-model="sub_title" maxlength="35">
              <span ng-bind="35 - sub_title.length" class="h6">35</span><span class="h6"> {{ trans('messages.lys.characters_left') }}</span>
            </div> -->
          </div>
        </div>
        <div class="panel-footer add-room-submit">
          <button type="submit" id="sub_room_create_button" class="btn btn-primary" ng-disabled="(sub_lys_new.$invalid || sub_title == '')">{{trans('messages.account.submit')}}</button>
        </div>
      </div>
      {!! Form::close() !!}      
    </div>
  </div>
</div>