@extends('template')
@section('main')
<main id="site-content" role="main" ng-controller="conversation">
    @include('common.subheader')
    <div class="guest-conversation my-4 my-md-5">
        <div class="container">
            <div class="row">
            <div class="col-12 col-md-7 messaging-thread-main order-md-2" ng-init="user_id={{$user_id}};instant_message={{$instant_message}};today='{{$today}}';reservation_id={{$messages[0]->reservation->id}};profile_picture='{{$profile_picture}}'">
            <input type="hidden" value="{{ $messages[0]->reservation_id }}" id="reservation_id">
                    
        <div ng-repeat="messages in instant_message | limitTo : 1">
            <div ng-if="messages.message_type == 1" class="banner-status mb-3 text-center">
                <h4 ng-if="messages.reservation.status == 'Expired'">
                 {{ trans('messages.inbox.reservation_expired') }}
                </h4>
                <div ng-if="messages.reservation.status != 'Expired'">
                    <h4>{{ trans('messages.payments.request_sent') }} </h4>
                    <p> {{ trans('messages.inbox.reservation_isnot_confirmed') }} </p>
                </div>
            </div>
            <div ng-if="messages.message_type == 2" class="banner-status mb-3 text-center">
                <h4>
                    {{ trans('messages.inbox.reservation_confirmed_place') }}
                    @{{ messages.reservation.rooms_address.city }},
                    @{{ messages.reservation.rooms_address.country_name }}
                </h4>
                <a href="{{ url('/') }}/reservation/itinerary?code=@{{ messages.reservation.code }}" class="btn mt-2">
                <span> {{ trans('messages.your_trips.view_itinerary') }} </span>
                </a>
            </div>
                
            <div ng-if="messages.message_type == 3 || messages.message_type == 8" class="banner-status mb-3 text-center">
                <h4> {{ trans('messages.inbox.request_declined') }} </h4>
                <p> {{ trans('messages.inbox.more_places_available') }} </p>
                <a class="btn mt-2" href="{{ url('/') }}/s?location=@{{ messages.reservation.rooms_address.city }}">
                    <span> {{ trans('messages.inbox.keep_searching') }}</span>
                </a>
            </div>
            
        <div ng-if="messages.reservation.special_offer" class="card action-status mb-3">
            <div class="card-body text-center">
                <h5>
                 @{{ messages.reservation.host_name }}
                 {{ trans('messages.inbox.pre_approved_trip') }}
                </h5>
            
        <div class="my-3">
            <div ng-if="messages.message_type!=8">
                <div ng-if="messages.reservation.avablity ==0 || messages.reservation.special_offer.avablity ==0">
                <div ng-if="messages.reservation.special_offer.checkin >= today">
                <div ng-if="messages.reservation.special_offer.is_booked">
                    <a href="{{ url('/') }}/payments/book?checkin=@{{ messages.reservation.special_offer.checkin }}&amp;checkout=@{{ messages.reservation.special_offer.checkout }}&amp;room_id=@{{ messages.reservation.special_offer.room_id }}&amp;number_of_guests=@{{ messages.reservation.special_offer.number_of_guests }}&amp;ref=qt2_preapproved&amp;special_offer_id=@{{ messages.reservation.special_offer.id }}" class="btn btn-primary @{{ (messages.reservation.special_offer.id) ? '':'prefer' }}">
                    <span>{{ trans('messages.inbox.book_now') }}</span>
                </a>
                </div>
                                                
                <div ng-if="messages.reservation.special_offer.id != '' && messages.reservation.special_offer.type == 'special_offer'">
                    <div class="my-3">
                        <div class="special-offer-info">
                            <div class="my-1">
                                <span>{{ trans('messages.lys.listing_name') }}</span>
                            </div>
                        <a class="theme-color" href="@{{messages.reservation.special_offer.link }}">
                         @{{ messages.reservation.special_offer.name }}</a>
                        </div>
                                                        
                    <div class="special-offer-info">
                        <div class="my-1">
                            <span>{{ trans('messages.your_reservations.checkin') }}</span>
                        </div>
                        <h5>@{{messages.reservation.special_offer.checkin_arrive}}</h5>
                     </div>
                    
                    <div class="special-offer-info">
                        <div class="my-1">
                            <span>{{ trans('messages.your_reservations.checkout') }}</span>
                        </div>
                    <h5> @{{messages.reservation.special_offer.checkout_depart}}</h5>
                    </div>
                
                    <div class="special-offer-info">
                        <div class="my-1"><span>Guest</span></div>
                         <h5> @{{messages.reservation.special_offer.number_of_guests}}</h5>
                    </div>
                    
                    <div class="reservation-info-section">
                        <div class="my-1">
                            <span> {{ trans('messages.inbox.special_offer') }}  </span>
                        </div>
                        <h5>
                            @{{ messages.reservation.currency.symbol}}
                            @{{ messages.reservation.special_offer.price }}
                        </h5>
                    </div>
                </div>
            </div>
        </div>
            
        <span class="label label-info" ng-if="messages.reservation.special_offer.checkin <= today">
             {{trans('messages.dashboard.Expired')}}
        </span>
        </div>
        
            <div ng-if="messages.reservation.avablity > 0 &&  messages.reservation.special_offer.avablity > 0">
                <div ng-if="messages.reservation.special_offer.checkin >= today">
                    <span ng-if="messages.reservation.special_offer.is_booked" class="error-msg" id="al_res@{{ messages.reservation.id }}">
                        {{ trans('messages.inbox.already_booked') }}
                    </span>
                </div>
            <div ng-if="messages.reservation.special_offer.checkin <= today">
                    {{trans('messages.dashboard.Expired')}}
            </div>
            </div>
         </div>
        </div>
        </div>
        </div>
                        <div ng-if="messages.reservation.status==Pre-Accepted" class="card action-status mb-3">
                            <div class="card-body text-center">
                                <h5>
                                @{{ messages.reservation.host_name }}
                                {{ trans('messages.inbox.preaccept_booking') }}
                                </h5>
                                <div class="mt-3">
                                    <div ng-if="messages.message_type!=8">
                                        <div ng-if="messages.reservation.avablity==0">
                                            <div ng-if="messages.reservation.checkin >= today">
                                                <a href="{{ url('/') }}/payments/book?reservation_id=@{{messages.reservation.id}}" class="btn btn-primary text-nowrap">
                                                    <p hidden="hidden" class="pending_id">@{{messages.reservation.id}}</p>
                                                    <span>{{ trans('messages.inbox.book_now') }}</span>
                                                </a>
                                            </div>
                                            <div ng-if="messages.reservation.checkin <= today">
                                                <span class="label label-info">
                                                    {{trans('messages.dashboard.Expired')}}
                                                </span>
                                            </div>
                                        </div>
                                        <div ng-if="messages.reservation.avablity!=0">
                                            <span ng-if="messages.reservation.checkin >= today" class="error-msg" id="al_res@{{messages.reservation.id}}">
                                                {{ trans('messages.inbox.already_booked') }}
                                            </span>
                                            <span ng-if="messages.reservation.checkin <= today" class="label label-info">
                                                {{trans('messages.dashboard.Expired')}}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div id="post_message_box" data-key="guest_conversation" class="row mb-3 send-message-box">
                        <div class="col-10">
                            <div class="card">
                                <div class="card-body p-0">
                                    <textarea rows="3" placeholder="" class="send-message-textarea border-0" id="message_text" name="message"></textarea>
                                </div>
                                <div class="card-footer text-right">
                                    <button class="btn btn-primary" id="reply_message" ng-click="reply_message('guest_conversation')">
                                    {{ trans('messages.your_reservations.send_message') }}
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="col-2 profile-img pl-0">
                            <img class="img-fluid w-100" src="{{ Auth::user()->profile_picture->src }}">
                        </div>
                    </div>
                    
                    <div id="thread-list">
                        <div ng-repeat="messages in instant_message ">
                            <div ng-if="messages.message_type == 12" class="inline-status py-4">
                                <span>{{ trans('messages.inbox.preaccept_booking') }} </span>
                                <span>@{{ messages.created_time }}</span>
                            </div>
                            <div ng-if="messages.message_type == 9" class="inline-status py-4">
                                <span>{{ trans('messages.inbox.contact_request_sent') }} </span>
                                <span>@{{ messages.created_time }}</span>
                            </div>
                            <div ng-if="messages.message_type == 2" class="inline-status py-4">
                                <span>{{ trans('messages.inbox.reservation_confirmed') }} </span>
                                <span>@{{ messages.created_time }}</span>
                            </div>
                            <div ng-if="messages.message_type == 3" class="inline-status py-4">
                                <span>{{ trans('messages.inbox.reservation_declined') }} </span>
                                <span>@{{ messages.created_time }}</span>
                            </div>
                            <div ng-if="messages.message_type == 4" class="inline-status py-4">
                                <span>{{ trans('messages.inbox.reservation_expired') }} </span>
                                <span>@{{ messages.created_time }}</span>
                            </div>
                            <div ng-if="messages.message_type == 6" class="inline-status py-4">
                                <span>
                                    @{{ messages.reservation.host_name }} {{ trans('messages.inbox.pre_approved_you') }}
                                </span>
                                <span>@{{ messages.created_time }}</span>
                            </div>
                            <div ng-if="messages.message_type == 7" class="inline-status py-4">
                                <span>
                                    @{{ messages.reservation.host_name }} {{ trans('messages.inbox.sent_special_offer') }}
                                </span>
                                <span>
                                    @{{ messages.special_offer.currency.symbol }}
                                    @{{ messages.special_offer.price}}
                                    Guest
                                </span>
                            </div>
                            <div ng-if="messages.message_type == 11 || messages.message_type == 8" class="inline-status py-4">
                                <span>
                                    {{ trans('messages.inbox.reservation_declined') }}
                                </span>
                                <span>@{{ messages.created_time }}</span>
                            </div>
                <div class="row my-3" ng-if="messages.user_from != user_id && messages.message !=''">
                     <div class="col-2 profile-img pr-0">
                        <img ng-src="@{{ messages.reservation.host_profile_picture }}">
                    </div>
                     <div class="col-10">
                        <div class="card">
                                <div class="card-body custom-arrow left">
                                    <a data-prevent-default="true" title="Report this message" class="flag-trigger" href="#"></a>
                                     <span class="message-text">
                                         @{{ messages.message }}
                                     </span>
                                </div>
                        </div>
                        <div class="my-2 time text-right">
                            <span>@{{ messages.created_time }}</span>
                        </div>
                    </div>
                </div>
                            <div class="row my-4" ng-if="messages.user_from == user_id">
                                <div class="col-10">
                                    <div class="card">
                                        <div class="card-body custom-arrow right">
                                            <span class="message-text">
                                                @{{ messages.message }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="my-2 time text-right">
                                        <span>
                                            @{{ messages.created_time }}
                                        </span>
                                    </div>
                                </div>
                                <div class="col-2 pl-0 profile-img">
                                    <img ng-src="@{{profile_picture}}" class="user-profile-photo">
                                </div>
                            </div>
                        </div>
                    </div>
                    
                </div>
                <div class="col-12 col-md-5 mt-4 mt-md-0 order-md-1">
                    <form accept-charset="UTF-8" action="{{ url('/') }}/messaging/qt_reply/{{ $messages[0]->reservation_id }}" method="post">
                        <div class="mini-profile card p-4">
                            <div class="profile-image text-center">
                                <a href="{{ url('/') }}/users/show/{{ $messages[0]->reservation->rooms->users->id }}">
                                    <img src="{{ $messages[0]->reservation->rooms->users->profile_picture->src }}" alt="{{ $messages[0]->reservation->rooms->users->first_name }}">
                                </a>
                            </div>
                            <div class="mt-3 text-center">
                                <a class="theme-link" href="{{ url('/') }}/users/show/{{ $messages[0]->reservation->rooms->users->id }}">
                                    {{ $messages[0]->reservation->rooms->users->first_name }}
                                </a>
                                <div class="mt-1">
                                    {{ $messages[0]->reservation->rooms->users->live }}
                                </div>
                            </div>
                            @if($messages[0]->reservation->rooms->users->about)
                            <div class="mt-1 text-center">
                                <div class="expandable expandable-trigger-more expanded">
                                    <div class="expandable-content">
                                        <p>
                                            {{ $messages[0]->reservation->rooms->users->about }}
                                        </p>
                                        <div class="expandable-indicator expandable-indicator-light"></div>
                                    </div>
                                    <!-- <a class="expandable-trigger-more" href="#">
                                        <strong>
                                        + {{ trans('messages.profile.more') }}
                                        </strong>
                                    </a> -->
                                </div>
                            </div>
                            @endif
                            @if($messages[0]->reservation->status == 'Accepted')
                            <div class="mt-3 pt-3 border-top text-left">
                                <h5>
                                {{ trans('messages.login.email') }}
                                </h5>
                                <span>
                                    {{ $messages[0]->reservation->rooms->users->email }}
                                </span>
                            </div>
                            @endif
                            @if($messages[0]->reservation->status == 'Accepted' && $messages[0]->reservation->host_users->primary_phone_number != '' )
                            <div class="mt-3 text-left">
                                <div class="text-medium-gray">
                                    {{ trans('messages.profile.phone_number') }}
                                </div>
                                <div class="mt-1">
                                    {{ $messages[0]->reservation->host_users->primary_phone_number }}
                                </div>
                            </div>
                            @endif
                        </div>
                        @if($messages[0]->reservation->list_type == 'Rooms' || ($messages[0]->reservation->list_type == 'Experiences' && $messages[0]->reservation->type != 'contact' ))
                        <div class="mt-5 mb-4 pb-4 border-bottom reservation-info">
                            <a class="theme-link room-link" href="{{$messages[0]->reservation->rooms->link }}">
                                {{ $messages[0]->reservation->rooms->name }}
                            </a>
                            <div class="reservation-info-section d-flex mt-3 row flex-wrap">
                                <div class="col-12 col-md-6 p-0 d-flex align-items-center d-md-block">
                                    <div class="col-6 col-md-12">
                                        <span>
                                            {{ trans('messages.your_reservations.checkin') }}
                                        </span>
                                    </div>
                                    <div class="col-6 col-md-12">
                                        <h5>
                                        {{$messages[0]->reservation->checkin_formatted}}
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-12 col-md-6 p-0 d-flex align-items-center d-md-block">
                                    <div class="col-6 col-md-12">
                                        <span>
                                            {{ trans('messages.your_reservations.checkout') }}
                                        </span>
                                    </div>
                                    <div class="col-6 col-md-12">
                                        <h5>
                                        {{ $messages[0]->reservation->checkout_formatted}}
                                        </h5>
                                    </div>
                                </div>
                                <div class="col-12 mt-2 p-0 d-flex align-items-center d-md-block">
                                    <div class="col-6 col-md-12">
                                        <span>
                                            {{ trans_choice('messages.home.guest',$messages[0]->reservation->number_of_guests ) }}
                                        </span>
                                    </div>
                                    <div class="col-6 col-md-12">
                                        <h5>
                                        {{ $messages[0]->reservation->number_of_guests }}
                                        </h5>
                                    </div>
                                </div>
                            </div>
                        </div>
                        @endif
                        @if($messages[0]->reservation->list_type == 'Rooms' || ($messages[0]->reservation->list_type == 'Experiences' && $messages[0]->reservation->type != 'contact' ))
                        <div class="guest-payment-info my-4">
                            <h4>
                            {{ trans('messages.payments.payment') }}
                            </h4>
                            <div class="mt-4">
                                <div class="d-flex my-2 row">
                                    <div class="col-8 text-left">
                                        <span>
                                            {{ html_string($messages[0]->reservation->currency->symbol) }}{{ $messages[0]->reservation->base_per_night }}
                                        </span>
                                        <span>
                                            x {{ $messages[0]->reservation->subtotal_multiply_text }}
                                        </span>
                                    </div>
                                    <div class="col-4 text-right">
                                        <span>
                                            {{ html_string($messages[0]->reservation->currency->symbol) }}{{ $messages[0]->reservation->base_per_night*($messages[0]->reservation->list_type == 'Experiences' ? $messages[0]->reservation->number_of_guests : $messages[0]->reservation->nights) }}
                                        </span>
                                    </div>
                                </div>
                                @foreach($messages[0]->reservation->discounts_list as $list)
                                <div class="d-flex my-2 row">
                                    <div class="col-8 text-left">
                                        <span>
                                            {{@$list['text']}}
                                        </span>
                                    </div>
                                    <div class="col-4 text-right">
                                        <span>
                                            -{{ html_string($messages[0]->reservation->currency->symbol) }}{{ @$list['price'] }}
                                        </span>
                                    </div>
                                </div>
                                @endforeach
                                @if($messages[0]->reservation->additional_guest != 0 )
                                <div class="d-flex my-2 row">
                                    <div class="col-8 text-left">
                                        <span>{{ trans('messages.rooms.addtional_guest_fee') }}</span>
                                    </div>
                                    <div class="col-4 text-right">
                                        <span>
                                            {{ html_string($messages[0]->reservation->currency->symbol) }}{{ $messages[0]->reservation->additional_guest }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($messages[0]->reservation->cleaning != 0 )
                                <div class="d-flex my-2 row">
                                    <div class="col-8 text-left">
                                        <span>{{ trans('messages.rooms.cleaning_fee') }}</span>
                                    </div>
                                    <div class="col-4 text-right">
                                        <span>
                                            {{ html_string($messages[0]->reservation->currency->symbol) }}{{ $messages[0]->reservation->cleaning }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($messages[0]->reservation->service != 0)
                                <div class="d-flex my-2 row">
                                    <div class="col-8 text-left">
                                        <span>
                                            {{ trans('messages.rooms.service_fee') }}
                                        </span>
                                    </div>
                                    <div class="col-4 text-right">
                                        <span>
                                            {{ html_string($messages[0]->reservation->currency->symbol) }}{{ $messages[0]->reservation->service }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                                @if($messages[0]->reservation->coupon_amount != 0)
                                <div class="d-flex my-2 row">
                                    <div class="col-8 text-left">
                                        <span>
                                            @if($messages[0]->reservation->coupon_code == 'Travel_Credit')
                                            {{ trans('messages.referrals.travel_credit') }}
                                            @else
                                            {{ trans('messages.payments.coupon_amount') }}
                                            @endif
                                        </span>
                                    </div>
                                    <div class="col-4 text-right">
                                        <span>
                                            -{{ html_string($messages[0]->reservation->currency->symbol) }}{{ $messages[0]->reservation->coupon_amount }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                                <div class="d-flex mt-3 pt-3 border-top row">
                                    <div class="col-8 text-left">
                                        <span class="font-weight-bold">
                                            @lang('messages.rooms.total')
                                        </span>
                                    </div>
                                    <div class="col-4 text-right">
                                        <strong>
                                        <span>
                                            {{ html_string($messages[0]->reservation->currency->symbol) }}{{ $messages[0]->reservation->total }}
                                        </span>
                                        </strong>
                                    </div>
                                </div>
                                @if($messages[0]->reservation->security > 0)
                                <div class="d-flex my-2 row">
                                    <div class="col-8 text-left">
                                        <span>
                                            @lang('messages.rooms.security_fee')
                                        </span>
                                    </div>
                                    <div class="col-4 text-right">
                                        <span>
                                            {{ html_string($messages[0]->reservation->currency->symbol) }}{{ $messages[0]->reservation->security }}
                                        </span>
                                    </div>
                                </div>
                                @endif
                            </div>
                            <div class="my-4">
                                <span>
                                    {{ trans('messages.inbox.protect_your_payments') }}
                                </span>
                                <span>
                                    {{ trans('messages.inbox.never_pay_outside',['site_name'=>$site_name]) }}
                                </span>
                                <span class="custom-tooltip d-block d-md-inline-block">
                                    <i class="icon icon-question tns-payment-tooltip-trigger tool-amenity2"></i>
                                    <div class="tooltip-wrap tooltip-amenity2 mt-3" data-sticky="true" aria-hidden="true">
                                        <div class="tooltip-info custom-arrow top">
                                            <span>
                                                {{ trans('messages.inbox.never_pay_outside',['site_name'=>$site_name]) }}
                                            </span>
                                        </div>
                                    </div>
                                </span>
                            </div>
                        </div>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
</main>
@endsection