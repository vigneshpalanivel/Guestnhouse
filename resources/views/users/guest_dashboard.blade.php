@extends('template')
@section('main')
<main id="site-content" role="main">
	@include('common.subheader')
	<div class="guest-dashboard my-4" id="guest-dashboard-container">
		<div class="container">
			<div class="row">
				<div class="col-md-4 col-lg-3">
					<div class="profile-img">
						<a href="{{ url('users/show/'.Auth::id()) }}" title="{{ trans('messages.dashboard.view_profile') }}">
							{!! Html::image(Auth::user()->profile_picture->src, Auth::user()->first_name, ['class'=>'img-fluid', 'title' => Auth::user()->first_name]) !!}
						</a>
						<a class="upload-profile-photo btn btn-contrast d-flex align-items-center justify-content-center" href="{{ url('users/edit/media') }}">
							<i class="icon icon-camera mr-2"></i>
							@lang('messages.dashboard.add_profile_photo')
						</a>
					</div>
					<div class="profile-info p-4 border-top-0 text-center">
						<h2> {{ Auth::user()->first_name }} </h2>
						<a class="theme-link" href="{{ url('users/show/'.Auth::id()) }}">
							@lang('messages.dashboard.view_profile')
						</a>
						@if(Auth::user()->profile_picture->src == '' || Auth::user()->about == '')
						<a href="{{ url('users/edit') }}" class="btn btn-primary d-block mt-2" id="edit-profile">
							@lang('messages.dashboard.complete_profile')
						</a>
						@endif
					</div>
					@if(Auth::user()->users_verification->show() || Auth::user()->verification_status == 'Verified')
					<div class="card mt-4 verification-panel">
						<div class="card-header">
							@lang('messages.dashboard.verifications')
						</div>
						<div class="card-body">
							<ul>
								@if(Auth::user()->verification_status == 'Verified')
								<li>
									<i class="icon icon-ok mr-2"></i>
									<div class="media-body">
										<h5> @lang('messages.dashboard.id_verification') </h5>
										<p> @lang('messages.dashboard.verified') </p>
									</div>
								</li>
								@endif
								@if(Auth::user()->users_verification->email == 'yes')
								<li>
									<i class="icon icon-ok mr-2"></i>
									<div class="media-body">
										<h5> @lang('messages.dashboard.email_address') </h5>
										<p> @lang('messages.dashboard.verified') </p>
									</div>
								</li>
								@endif
								@if(Auth::user()->users_verification->phone_number == 'yes')
								<li>
									<i class="icon icon-ok mr-2"></i>
									<div class="media-body">
										<h5> @lang('messages.profile.phone_number') </h5>
										<p> @lang('messages.dashboard.verified') </p>
									</div>
								</li>
								@endif
								@if(Auth::user()->users_verification->facebook == 'yes')
								<li>
									<i class="icon icon-ok mr-2"></i>
									<div class="media-body">
										<h5> Facebook </h5>
										<p> @lang('messages.dashboard.validated') </p>
									</div>
								</li>
								@endif
								@if(Auth::user()->users_verification->google == 'yes')
								<li>
									<i class="icon icon-ok mr-2"></i>
									<div class="media-body">
										<h5> Google </h5>
										<p> @lang('messages.dashboard.validated') </p>
									</div>
								</li>
								@endif
								@if(Auth::user()->users_verification->linkedin == 'yes')
								<li>
									<i class="icon icon-ok mr-2"></i>
									<div class="media-body">
										<h5> LinkedIn </h5>
										<p> @lang('messages.dashboard.validated') </p>
									</div>
								</li>
								@endif
							</ul>
						</div>
					</div>
					@endif
				</div>
				<div class="col-md-8 col-lg-9 notify-msg">
					<div class="card mt-4 mt-md-0">
						<div class="card-header">
							<span> @lang('messages.dashboard.welcome') {{ $site_name }}, </span>
							<strong> {{Auth::user()->first_name }}! </strong>
						</div>
						<div class="card-body">
							<p> @lang('messages.dashboard.welcome_desc')
								@if(Auth::user()->profile_picture->src == '' || Auth::user()->about == '')
								@lang('messages.dashboard.welcome_ask_to_complete_profile')
								@endif
							</p>
							@if(Auth::user()->profile_picture->src == '' || Auth::user()->about == '')
							<div class="mt-3">
								@if(Auth::user()->profile_picture->src == '' || Auth::user()->about == '')
								<strong>
								<a class="theme-link" href="{{ url('users/edit') }}">
									@lang('messages.dashboard.complete_your_profile')
								</a>
								</strong>
								<p>
									@lang('messages.dashboard.complete_your_profile_desc')
								</p>
								@endif
							</div>
							@endif
						</div>
					</div>
					@if(Auth::user()->users_verification->email == 'no')
					<div class="card mt-4">
						<div class="card-header">
							@choice('messages.header.notification',2)
						</div>
						<div class="card-body">
							@if(Auth::user()->users_verification->email == 'no')
							<p>
								@lang('messages.dashboard.confirm_your_email')
								<a class="theme-link" href="{{ url('users/request_new_confirm_email') }}">
									@lang('messages.dashboard.request_confirmation_email')
								</a>
								@lang('messages.login.or')
								<a class="theme-link" href="{{ url('users/edit') }}">
									@lang('messages.dashboard.change_email_address').
								</a>
							</p>
							@endif
						</div>
					</div>
					@endif
					<div class="card mt-4" ng-init="all_messages={{ $all_messages }}">
						<div class="card-header">
							@choice('messages.dashboard.message',2) (@{{all_messages.length }} @lang('messages.dashboard.new'))
						</div>
						<div class="card-body px-0">
							<ul class="col-12 list-layout">
								<li id="thread_@{{ message.id }}" class="d-flex" ng-repeat="message in all_messages">
									<div class="col-3 col-md-2 col-lg-1 list-img text-center p-0" ng-if="message.user_to == message.user_from">
										<a data-popup="true" href="#" class="profile-image">
											<img title="{{ $admin_name }}" src="{{ url('admin_assets/dist/img/avatar04.png') }}" class="media-round media-photo" alt="{{ $admin_name }}">
										</a>
									</div>
									
									<div class="col-3 col-md-2 col-lg-1 list-img text-center p-0" ng-if="message.user_to != message.user_from">
										<a data-popup="true" href="{{ route('show_profile',['id' => '/'])}}/@{{ message.user_from }}" class="profile-image">
											<img title="@{{ message.user_name }}" ng-src="@{{ message.user_src }}" class="media-round media-photo" alt="@{{ message.user_name }}">
										</a>
									</div>
									<div class="col-9 col-md-10 col-lg-11 p-0 d-md-flex mt-1 text-md-center" ng-if="message.user_from == message.user_to">
										<div class="list-name col-12 col-md-3">
											<h3 class="text-truncate"> {{ $admin_name }} </h3>
											<span class="list-date"> @{{ message.created_time }} </span>
										</div>
										<div class="reserve-link col-12 col-md-6" ng-init="admin_message='{{ route('admin_messages',['id' => '/']) }}/'+message.user_to;resubmit_message='{{ route('admin_resubmit_message',['id' => '/']) }}/'+message.reservation_id;">
											<a href="@{{ (message.room_id == 0) ? admin_message : resubmit_message }}">
												<span class="list-subject unread-message d-block">
													@{{ message.message }}
												</span>
												<span class="msg-count" ng-if="message.inbox_thread_count > 1">
													<i class="alert-count1 text-center inbox_message_count"> @{{ message.inbox_thread_count }} </i>
												</span>
											</a>
										</div>
									</div>
									<div class="col-9 col-md-10 col-lg-11 p-0 d-md-flex mt-1 text-md-center" ng-if="message.user_from != message.user_to">
										<div class="list-name col-12 col-md-3">
											<h3 class="text-truncate"> @{{ message.user_name }} </h3>
											<span class="list-date"> @{{ message.created_time }} </span>
										</div>
										<div class="reserve-link col-12 col-md-6" >
											<a href="@{{ message.reservation.inbox_url }}">
												<span class="list-subject unread-message d-block">
													@{{ message.message }}
													<span class="msg-count" ng-if="message.inbox_thread_count > 1">
														<i class="alert-count1 text-center inbox_message_count"> @{{ message.inbox_thread_count }} </i>
													</span>
												
												</span>
												<span class="street-address">
													@{{ message.reservation.rooms_address.address_line_1 }}
													@{{ message.reservation.rooms_address.address_line_2 }},
												</span>
												<span class="locality"> @{{ message.reservation.rooms_address.city }},</span>
												<span class="region"> @{{ message.reservation.rooms_address.state }}</span>
												
												<span class="check-date d-inline-block" ng-if="message.reservation.list_type != 'Experiences' || message.reservation.type != 'contact'">
													(@{{ message.reservation.dates_subject }})
												</span>
											</a>
										</div>
										<div class="list-status col-12 col-md-3" ng-if="message.reservation.list_type != 'Experiences' || message.reservation.type != 'contact'">
											<span class="d-block label label-@{{ message.reservation.status_color }}">
												<strong>  @{{ message.reservation.status_language }} </strong>
											</span>
											<span> {{ $currency_symbol }} @{{ message.reservation.total }} </span>
										</div>
									</div>
								</li>
							</ul>
							<div class="text-center">
								<a class="theme-link" href="{{ url('inbox') }}">
									@lang('messages.dashboard.all_messages')
								</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
</main>
@endsection