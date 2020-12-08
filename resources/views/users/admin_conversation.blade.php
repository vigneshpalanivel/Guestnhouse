@extends('template')
@section('main')
<main id="site-content" role="main" ng-controller="conversation">
  @include('common.subheader')
  <div class="container">
    <h1 class="h2 row-space-4 conversation_head">
      {{ trans('messages.inbox.resubmit_reasons')}}:
    </h1>
    <p>
      <a href="{{ url('users/edit_verification')}}"> 
        {{ trans('messages.inbox.click_here_to_resubmit')}} 
      </a>
    </p>
    <div class="row">
      <div class="col-12 col-md-12 col-lg-8 host_conver">
        <ul class="list-unstyled host_ul">
          @foreach($messages as $message)
          <div id="thread-list">
            <li id="question2_post_11" class="thread-list-item">
              <div class="row row-condensed">
                <div class="col-sm-10 col-md-10">
                  <div class="row-space-4">
                    <div class="panel panel-quote panel-quote-flush panel-quote-right">
                      <div class="panel-body">
                        <div class="message-text">
                          <b>
                            {{ trans('messages.inbox.'.$message->message_type_reason) }} :
                          </b>
                          <p class="trans">
                            {{ $message->message }}
                          </p>
                        </div>
                      </div>
                    </div>
                    <div class="time-container text-muted text-right">
                      <small title="{{ $message->created_time }}" class="time">
                        {{ $message->created_time }}
                      </small>
                      <small class="exact-time d-none">
                        {{ $message->created_time }}
                      </small>
                    </div>
                  </div>
                </div>
                <div class="col-2 text-center">
                  <a aria-label="Test" data-behavior="tooltip" class="media-photo media-round" href="#">
                    <img width="36" height="36" title="{{ $message->admin_name }}" src="{{ url('admin_assets/dist/img/avatar04.png') }}" alt="{{ $message->admin_name }}">
                  </a>
                </div>
              </div>
            </li>
          </div>
          @endforeach
        </ul>
      </div>
      <div class="col-md-5 col-lg-4 host-mini"></div>
    </div>
  </div>
</main>
@stop