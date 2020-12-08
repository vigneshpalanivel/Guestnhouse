@extends('emails.template')

@section('emails.main')
<div style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;margin-top:1em">
<div style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;margin-top:1em">
   {{ trans('messages.email.deactivated_list1',[], null, $locale) }} <a href="{{ $url.('rooms/'.$room_id) }}" style="margin:0;padding:0;font-family:&quot;Helvetica Neue&quot;,&quot;Helvetica&quot;,Helvetica,Arial,sans-serif;color:#07c;text-decoration:none" target="_blank"> {{$main_room_name}}</a> ({{$room_name}} - {{$number_of_rooms}} {{ trans('messages.home.rooms',[], null, $locale) }})
  {{ trans('messages.email.deactivated_list2',['site_name'=>$site_name], null, $locale) }} {{ $created_time }}.
</div>

</div>
@stop