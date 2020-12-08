@extends('emails.template')

@section('emails.main')
<div style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;margin-top:1em;color: #000;">
<div style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;margin-top:1em;color: #000;">
  
    {{ trans('messages.email.congratulations',[], null, $locale) }}! <a href="{{ $url.('rooms/'.$main_room_id) }}" style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;color:#ff0000;text-decoration:none" target="_blank"> {{ trans('messages.email.rooms_for',[], null, $locale) }} {{$main_room_name}} " {{ $room_name }}, {{$number_of_rooms}} {{ trans('messages.home.rooms',[], null, $locale) }} "</a> {{ trans('messages.email.was_listed_on1',['site_name'=>$site_name],null, $locale) }} <span class="aBn" data-term="goog_778163106" tabindex="0"><span class="aQJ"></span></span> 
    @if($main_room_status=='Listed')
    	{{ trans('messages.email.search_result_in',[], null, $locale) }}.
    @else
    	{{ trans('messages.email.search_result_in1',[], null, $locale) }} <span style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;color:#ff0000;text-decoration:none;"> "{{$main_room_name }}" </span> {{ trans('messages.email.search_result_in2',[], null, $locale) }}.
    @endif

</div>

<div style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;margin-top:1em;color: #000;">
  
    {{ trans('messages.email.not_ready_for_guests',[], null, $locale) }} <a href="{{ $url }}manage-listing/{{ $room_id }}/calendar?type=sub_room" style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;color:#ff0000;text-decoration:none" target="_blank"> {{ trans('messages.email.your_calendar',[], null, $locale) }}</a>, {{ trans('messages.email.pervent_people_booking',[], null, $locale) }} <strong style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif">{{ trans('messages.email.not_available',[], null, $locale) }}</strong> {{ trans('messages.email.and_click',[], null, $locale) }} <strong style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif">{{ trans('messages.profile.save',[], null, $locale) }}</strong>.

</div>

<div style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;margin-top:1em">
  
{{ trans('messages.email.please_unlist_it',[], null, $locale) }}
</div>
</div>
@stop

