@if($result->type == 'Multiple' || @$sub_room == 'true')

<div class="mobile_select_subbav room-btnlist sub-room-btns">
  @if(!$sub_room && $result->type == 'Multiple')
  <div class="nav-roomselect {{($main_room_name)? '' : 'd-none'}}" id="main_room_multiple">
    <span id="listing-name">
        <a href="{{url('manage-listing/'.$main_room_id.'/description')}}" class="btn truncate btn-info selectbtn btn-select add-sub-room-btn-cls">
            <span class="main_room_name truncate">
              {{ ($main_room_name) ? $main_room_name : '' }}
            </span>
        </a>
    </span>
  </div>
  @else
    <div class="nav-roomselect">
      <span id="listing-name">
        <a href="{{url('manage-listing/'.$main_room_id.'/description')}}" class="btn btn-select btn-info add-sub-room-btn-cls">
          <span class="main_room_name truncate">
            {{ ($main_room_name) ? $main_room_name : '' }}
          </span>
        </a>
      </span>
    </div>
  @endif
 
   
 @if(count(@$all_rooms))

   <div class="sub_select subnav edit_cal_page main_drop_view select">
      <select name="state" id="listing-name_mul1" class="form-control input-sm minimal selectpicker" ng-show="{{$result->type == 'Multiple' || @$sub_room == 'true'}}" onchange="window.location.href=this.value;">
            <option value="" disabled="" selected="">{{trans('messages.lys.select')}}</option>
            @foreach(@$all_rooms as $key=>$rooms)
            <option value="{{ $main_room_id == $key ? url('manage-listing/'.$key.'/description') : url('manage-listing/'.$key.'/basics?type=sub_room')}}" {{$result->id == $key ? 'selected' : ''}}>
               {{$rooms ? $rooms : trans('messages.rooms.main_room')}}
            </option>
            @endforeach            
      </select>
      </div>


     <span class="subnav-text nav-roomselect" ng-show="{{$result->type == 'Multiple' || @$sub_room == 'true'}}">
      <div class="add_but_see">
        <button type="button" class="btn btn-info room-btn add-sub-room-btn-cls" id="href_add_multiple_room" data-toggle="modal" {{($result->status == 'Listed' || @$sub_room == 'true') ? '' : 'disabled'}}  data-target="#anyroom">{{ trans('messages.lys.add_room') }}</button>
      </div>
    </span>

    <span class="subnav-text {{(count(@$all_rooms)) == '0' ? 'hide' : ''}}" id='duplicate-btn' ng-show="{{@$sub_room == 'true' || $result->type == 'Multiple'}}">
      <div class="add_but_count">
        <span class="count no-count">{{count(@$all_rooms)}}</span>
        <!-- <label class="count">{{count(@$all_rooms)}}</label> -->
      </div>
    </span>
@endif
</div>

@endif