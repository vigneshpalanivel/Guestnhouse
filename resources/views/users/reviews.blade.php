@extends('template')
@section('main')
<main id="site-content" role="main" ng-controller="reviews">      
  @include('common.subheader')  
  <div class="review-content my-4 my-md-5">
    <div class="container">
      <div class="row">
        <div class="col-md-4 col-lg-3 side-nav">
          @include('common.sidenav')
          <a href="{{ url('users/show/'.Auth::user()->id) }}" class="btn btn-primary">
            {{ trans('messages.dashboard.view_profile') }}
          </a>
        </div>
        <div class="reviews custom-tabs col-md-8 col-lg-9 mt-4 mt-md-0">      
          <ul role="tablist" class="tabs">
            <li>
              <a aria-selected="true" aria-controls="received" role="tab" href="javascript:void(0);" class="tab-item">
                {{ trans('messages.reviews.reviews_about_you') }}
              </a>
            </li>
            <li>
              <a aria-selected="false" aria-controls="sent" role="tab" class="tab-item" href="javascript:void(0);">
                {{ trans('messages.reviews.reviews_by_you') }}
                @if($reviews_to_write_count)
                <i class="alert-count position-super">
                  {{ $reviews_to_write_count }}
                </i>
                @endif
              </a>
            </li>
          </ul>
          
          <div class="mt-4" id="reviews">
            <div aria-hidden="false" id="received" role="tabpanel" class="tab-panel">
              <div class="card">
                <div class="card-header">
                  <h3>
                    {{ trans_choice('messages.header.review',2) }}
                  </h3>
                </div>
                @if($reviews_about_you->count())
                <div class="card-body">
                  <p>
                    {{ trans('messages.reviews.reviews_about_you_desc') }}
                  </p>
                  <ul class="list-layout reviews-list mt-3">
                    @for($i=0; $i<$reviews_about_you->count(); $i++)
                    @if(!$reviews_about_you[$i]->hidden_review)
                    <li class="media reviews-list-item mt-3">
                      <div class="media-rev-img text-center pr-3 pr-md-4">
                        <a class="profile-img" href="{{ url('/') }}/users/show/{{ $reviews_about_you[$i]->user_from }}">
                          <img width="68" height="68" title="{{ $reviews_about_you[$i]->users_from->first_name }}" src="{{ $reviews_about_you[$i]->users_from->profile_picture->src }}" alt="{{ $reviews_about_you[$i]->users_from->first_name }}">
                        </a>                  
                        <div class="name">
                          <a href="{{ url('/') }}/users/show/{{ $reviews_about_you[$i]->user_from }}">
                            {{ $reviews_about_you[$i]->users_from->full_name }}
                          </a>
                        </div>
                      </div>
                      <div class="media-body response">
                        <p>
                          {{ $reviews_about_you[$i]->comments }}
                        </p>
                        <hr>
                        @if($reviews_about_you[$i]->reservation->host_id == Auth::user()->id)
                        @if($reviews_about_you[$i]->reservation->list_type=="Rooms")
                        <div class="mt-3">
                          <p>
                            <strong>
                              <i aria-label="Private" data-behavior="tooltip" class="icon icon-lock icon-rausch"></i>
                              {{ trans('messages.reviews.love_comments',['first_name'=>$reviews_about_you[$i]->users_from->first_name]) }}:
                            </strong>
                            <br>
                            {{ $reviews_about_you[$i]->love_comments }}
                          </p>
                        </div>
                        <div class="mt-3">
                          <p>
                            <strong>
                              <i aria-label="Private" data-behavior="tooltip" class="icon icon-lock icon-rausch"></i>
                              {{ trans('messages.reviews.improve_comments',['first_name'=>$reviews_about_you[$i]->users_from->first_name]) }}:
                            </strong>
                            <br>
                            {{ $reviews_about_you[$i]->improve_comments }}
                          </p>
                        </div>
                        <div class="mt-3">
                          <p>
                            <strong>
                              <i aria-label="Private" data-behavior="tooltip" class="icon icon-lock"></i>
                              {{ trans('messages.reviews.accuracy_comments') }}:
                            </strong>
                            <br>
                            {{ $reviews_about_you[$i]->accuracy_comments }}
                          </p>
                        </div>
                        <div class="mt-3">
                          <p>
                            <strong>
                              <i aria-label="Private" data-behavior="tooltip" class="icon icon-lock"></i>
                              {{ trans('messages.reviews.cleanliness_comments') }}:
                            </strong>
                            <br>
                            {{ $reviews_about_you[$i]->cleanliness_comments }}
                          </p>
                        </div>

                        <div class="mt-3">
                          <p>
                            <strong>
                              <i aria-label="Private" data-behavior="tooltip" class="icon icon-lock"></i>
                              {{ trans('messages.reviews.arrival_comments') }}:
                            </strong>
                            <br>
                            {{ $reviews_about_you[$i]->checkin_comments }}
                          </p>
                        </div>

                        <div class="mt-3">
                          <p>
                            <strong>
                              <i aria-label="Private" data-behavior="tooltip" class="icon icon-lock"></i>
                              {{ trans('messages.reviews.amenities_comments') }}:
                            </strong>
                            <br>
                            {{ $reviews_about_you[$i]->amenities_comments }}
                          </p>
                        </div>

                        <div class="mt-3">
                          <p>
                            <strong>
                              <i aria-label="Private" data-behavior="tooltip" class="icon icon-lock"></i>
                              {{ trans('messages.reviews.communication_comments') }}:
                            </strong>
                            <br>
                            {{ $reviews_about_you[$i]->communication_comments }}
                          </p>
                        </div>

                        <div class="mt-3">
                          <p>
                            <strong>
                              <i aria-label="Private" data-behavior="tooltip" class="icon icon-lock"></i>
                              {{ trans('messages.reviews.location_comments') }}:
                            </strong>
                            <br>
                            {{ $reviews_about_you[$i]->location_comments }}
                          </p>
                        </div>

                        <div class="mt-3">
                          <p>
                            <strong>
                              <i aria-label="Private" data-behavior="tooltip" class="icon icon-lock"></i>
                              {{ trans('messages.reviews.value_comments') }}:
                            </strong>
                            <br>
                            {{ $reviews_about_you[$i]->value_comments }}
                          </p>
                        </div>
                        @endif

                        @else
                        <div class="mt-3">
                          <p>
                            <strong>
                              <i aria-label="Private" data-behavior="tooltip" class="icon icon-lock"></i>
                              {{ trans('messages.reviews.private_feedback',['first_name'=>$reviews_about_you[$i]->users_from->first_name]) }}:
                            </strong>
                            <br>
                            {{ $reviews_about_you[$i]->private_feedback }}
                          </p>
                        </div>
                        @endif

                        <p>
                          {{ $reviews_about_you[$i]->date_fy }}
                        </p>
                      </div>
                    </li>
                    @else

                    <li class="media reviews-list-item mt-4">
                      <div class="media-rev-img text-center pr-3 pr-md-4">
                        <a class="profile-img" href="{{ url('/') }}/users/show/{{ $reviews_about_you[$i]->user_from }}">
                          <img width="68" height="68" title="{{ $reviews_about_you[$i]->users_from->first_name }}" src="{{ $reviews_about_you[$i]->users_from->profile_picture->src }}" alt="{{ $reviews_about_you[$i]->users_from->first_name }}">
                        </a>                  
                        <div class="name">
                          <a href="{{ url('/') }}/users/show/{{ $reviews_about_you[$i]->user_from }}">
                            {{ $reviews_about_you[$i]->users_from->full_name }}
                          </a>
                        </div>
                      </div>
                      <div class="media-body response">
                        @if($reviews_about_you[$i]->hidden_review)
                        <div class="double-blind-hidden">
                          <div class="label label-info">
                            {{ trans('messages.reviews.review_is_hidden') }}
                          </div>
                          <p>
                            {{ trans('messages.reviews.pls_complete_your_part') }}.
                          </p>
                          @if($reviews_about_you[$i]->reservation->list_type=="Rooms")
                          <a href="{{ url('/') }}/reviews/edit/{{ $reviews_about_you[$i]->reservation_id }}" class="btn ml-md-auto d-table">
                            @else
                            <a class="btn ml-md-auto d-table" href="{{ url('/') }}/host_experience_reviews/edit/{{ $reviews_about_you[$i]->reservation_id }}">
                              @endif
                              {{ trans('messages.reviews.complete_review') }}
                            </a>
                          </div>
                          @endif
                        </div>
                      </li>
                      @endif
                      @endfor
                    </ul>
                  </div>
                  @else

                  <div class="card-body">
                    <p>
                      {{ trans('messages.reviews.no_review_desc',['site_name'=>$site_name]) }}
                    </p>
                    <ul class="list-layout reviews-list mt-3">
                      <li class="reviews-list-item">
                        {{ trans('messages.reviews.no_review') }}
                      </li>
                    </ul>
                  </div>
                  @endif
                </div>
              </div>

              <div id="sent" aria-hidden="true" role="tabpanel" class="tab-panel">
                <div class="card">
                  <div class="card-header">
                    <h3>
                      {{ trans('messages.reviews.reviews_to_write') }}
                    </h3>
                  </div>
                  @if($reviews_to_write_count)
                  <div class="card-body">
                    <p>
                      {{ trans('messages.reviews.reviews_written_after_checkout') }}
                    </p>
                    <ul class="list-layout reviews-list">
                      @for($i=0; $i<$reviews_to_write->count(); $i++)
                      @php $write = 0; @endphp
                      @if($reviews_to_write[$i]->review_days > 0 && $reviews_to_write[$i]->reviews->count() < 2)
                      @if(@$reviews_to_write[$i]->reviews->count() == 0)
                      @php $write = 1; @endphp
                      @endif
                      @for($j=0; $j<$reviews_to_write[$i]->reviews->count(); $j++)
                      @if(@$reviews_to_write[$i]->reviews[$j]->user_from != Auth::user()->id)
                      @php $write = 1; @endphp
                      @endif
                      @endfor
                      @endif
                      @if(@$write == 1)
                      <li class="media reviews-list-item mt-3">
                        <div class="media-rev-img text-center pr-3 pr-md-4">
                          <a class="profile-img" href="{{ url('/') }}/users/show/{{ $reviews_to_write[$i]->review_user(Auth::user()->id)->id }}">
                            <img width="68" height="68" title="{{ $reviews_to_write[$i]->review_user(Auth::user()->id)->first_name }}" src="{{ $reviews_to_write[$i]->review_user(Auth::user()->id)->profile_picture->src }}" alt="{{ $reviews_to_write[$i]->review_user(Auth::user()->id)->first_name }}">
                          </a>
                          <div class="name">
                            <a href="{{ url('/') }}/users/show/{{ $reviews_to_write[$i]->review_user(Auth::user()->id)->id }}">
                              {{ $reviews_to_write[$i]->review_user(Auth::user()->id)->full_name }}
                            </a>
                          </div>
                        </div>
                        <div class="media-body">
                          <p>
                            {{ trans('messages.reviews.you_have') }} <b>{{ str_replace('+','',$reviews_to_write[$i]->review_days) }} {{ ($reviews_to_write[$i]->review_days > 1) ? trans_choice('messages.reviews.day',2) : trans_choice('messages.reviews.day',1) }}</b> {{ trans('messages.reviews.to_submit_public_review') }} 
                            <a href="{{ url('/') }}/users/show/{{ $reviews_to_write[$i]->review_user(Auth::user()->id)->id }}">
                              {{ $reviews_to_write[$i]->review_user(Auth::user()->id)->full_name }}.
                            </a>
                          </p>
                          <ul>
                            <li>
                              @if($reviews_to_write[$i]->list_type=="Rooms")
                              <a class="theme-link" href="{{ url('/') }}/reviews/edit/{{ $reviews_to_write[$i]->id }}">
                                {{ trans('messages.reviews.write_a_review') }}
                              </a>
                              @else
                              <a class="theme-link" href="{{ url('/') }}/host_experience_reviews/edit/{{ $reviews_to_write[$i]->id }}">
                                {{ trans('messages.reviews.write_a_review') }}
                              </a>
                              @endif
                            </li>
                            <li>
                              <a class="theme-link" href="{{ url('/') }}/reservation/itinerary?code={{ $reviews_to_write[$i]->code }}">
                                {{ trans('messages.your_trips.view_itinerary') }}
                              </a>
                            </li>
                          </ul>
                        </div>
                      </li>
                      @endif
                      @endfor
                    </ul>
                  </div>
                  @else
                  <div class="card-body">
                    <ul class="list-layout reviews-list">
                      <li class="reviews-list-item">
                        {{ trans('messages.reviews.nobody_to_review') }}
                      </li>
                    </ul>
                  </div>
                  @endif
                </div>

                <div class="card mt-4">
                  <div class="card-header">
                    <h3>
                      {{ trans('messages.reviews.past_reviews_written') }}
                    </h3>
                  </div>
                  @if($reviews_by_you->count())
                  <div class="card-body">
                    <ul class="list-layout reviews-list">
                      @for($i=0; $i < $reviews_by_you->count(); $i++)
                      <li class="reviews-list-item media mt-2">
                        <div class="media-rev-img text-center pr-3 pr-md-4">
                          <a class="profile-img" href="{{ url('/') }}/users/show/{{ $reviews_by_you[$i]->user_to }}">
                            <img width="68" height="68" title="{{ $reviews_by_you[$i]->users->first_name }}" src="{{ $reviews_by_you[$i]->users->profile_picture->src }}" alt="{{ $reviews_by_you[$i]->users->first_name }}">         
                          </a>     
                          @if(@$expired_reviews[$i])
                          <p class="text_clip">
                            {{ @$expired_reviews[$i]->review_user(Auth::user()->id)->full_name }}
                          </p>
                          @endif 
                        </div>
                        <div class="media-body">
                          <h5>
                            {{ trans('messages.reviews.review_for') }} 
                            <a href="{{ url('/') }}/users/show/{{ $reviews_by_you[$i]->user_to }}">
                              {{ $reviews_by_you[$i]->users->first_name }}
                            </a>
                          </h5>
                          <p>
                            {{ $reviews_by_you[$i]->comments }}
                          </p>
                          @if($reviews_by_you[$i]->reservation->review_days > 0)
                          <div class="my-2">
                            @if($reviews_by_you[$i]->reservation->list_type=="Rooms")
                            <a class="theme-link" class="edit" href="{{ url('/') }}/reviews/edit/{{ $reviews_by_you[$i]->reservation_id }}">
                              {{ trans('messages.reviews.edit') }}
                            </a>
                            @else
                            <a class="theme-link" href="{{ url('/') }}/host_experience_reviews/edit/{{ $reviews_by_you[$i]->reservation_id }}">
                              {{ trans('messages.reviews.edit') }}
                            </a>
                            @endif
                            ({{ str_replace('+','',$reviews_by_you[$i]->reservation->review_days) }} {{ ($reviews_by_you[$i]->reservation->review_days > 1) ? trans_choice('messages.reviews.day',2) : trans_choice('messages.reviews.day',1) }} {{ trans('messages.reviews.left_to_edit') }})
                          </div>
                          @endif
                          <p>
                            {{ $reviews_by_you[$i]->date_fy }}
                          </p>
                        </div>
                      </li>
                      @endfor
                    </ul>
                  </div>
                  @else
                  <div class="card-body">
                    {{ trans('messages.reviews.past_no_reviews_written') }}
                  </div>
                  @endif
                </div>
                @if($expired_reviews_count)
                <div class="card mt-4" id="expired-reviews">
                  <div class="card-header">
                    <h3>
                      {{ trans('messages.reviews.expired_reviews') }}
                    </h3>
                  </div>
                  <div class="card-body">
                    <p class="text-lead">
                      {{ trans('messages.reviews.expired_reviews_desc') }}
                    </p>
                    <ul class="list-layout reviews-list mt-4">
                      @for($i=0; $i<$expired_reviews->count(); $i++)
                      @php $expired = 0; @endphp
                      @if($expired_reviews[$i]->review_days <= 0 && $expired_reviews[$i]->reviews->count() < 2)
                      @if(@$expired_reviews[$i]->reviews->count() == 0)
                      @php $expired = 1; @endphp
                      @endif
                      @for($j=0; $j<$expired_reviews[$i]->reviews->count(); $j++)
                      @if(@$expired_reviews[$i]->reviews[$j]->user_from != Auth::user()->id)
                      @php $expired = 1; @endphp
                      @endif
                      @endfor
                      @endif
                      @if(@$expired == 1)
                      <li class="media reviews-list-item align-items-center mt-4">
                        <div class="media-rev-img text-center pr-3 pr-md-4">
                          <a class="profile-img" href="{{ url('/') }}/users/show/{{ $expired_reviews[$i]->review_user(Auth::user()->id)->id }}">
                            <img width="68" height="68" title="{{ $expired_reviews[$i]->review_user(Auth::user()->id)->first_name }}" src="{{ $expired_reviews[$i]->review_user(Auth::user()->id)->profile_picture->src }}" alt="{{ $expired_reviews[$i]->review_user(Auth::user()->id)->first_name }}">
                          </a>          
                        </div>    
                        <div class="media-body response">
                          {{ trans('messages.reviews.your_time_to_write_review') }} 
                          <a href="{{ url('/') }}/users/show/{{ $expired_reviews[$i]->review_user(Auth::user()->id)->id }}">
                            {{ $expired_reviews[$i]->review_user(Auth::user()->id)->full_name }}
                          </a> 
                          {{ trans('messages.reviews.has_expired') }}
                          <div>
                            <a class="theme-link" href="{{ url('/') }}/reservation/itinerary?code={{ $expired_reviews[$i]->code }}">
                              {{ trans('messages.your_trips.view_itinerary') }}
                            </a>
                          </div>
                        </div>
                      </li>
                      @endif
                      @endfor
                    </ul>
                  </div>
                </div>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>
  @stop