@extends('template')
@section('main')
<main id="site-content" role="main">
	@include('common.subheader')
	<div class="host-dashboard" id="host-dashboard-container">
		<div class="container">
			<div class="row">
				<div class="col-12 col-md-7 col-lg-8 d-md-flex align-items-center dashboard-left py-4 text-center text-md-left">
					<div class="col-md-3 col-lg-2 dashboard-profile p-0 mb-3 mb-md-0">
						<a href="{{ route('show_profile',['id' => $user->id]) }}">
							<img src="{{ $user->profile_picture->src }}">
						</a>
					</div>
					<div class="col-md-9 col-lg-10 dashboard-content">
						<div id="myCarousel" class="carousel slide" data-ride="carousel">
							<div class="carousel-inner">
								<div class="carousel-item active">
									<strong> @lang('messages.host_dashboard.hi_first_name', ['first_name' => $user->first_name]) </strong>
									@lang('messages.host_dashboard.title')
								</div>
								<div class="carousel-item">
									<strong> @lang('messages.host_dashboard.hi_first_name', ['first_name' => $user->first_name]) </strong>
									@lang('messages.host_dashboard.welcome_message')
								</div>
								<div class="carousel-item">
									<strong> @lang('messages.host_dashboard.hi_first_name', ['first_name' => $user->first_name]) </strong>
									@lang('messages.host_dashboard.title')
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="col-12 col-md-5 col-lg-4 dashboard-right p-4">
					<div class="text-center">
						<h2>
						<sup>
						{{ $currency_symbol }}
						</sup>
						<strong>
						{{ $completed_payout  + $future_payouts }}
						</strong>
						</h2>
						<p> @lang('messages.host_dashboard.for_nights_in_month', ['count' => ($completed_nights  +  $future_nights),'count1' => ($total_payout_rooms), 'month' => trans('messages.lys.'.date('F')) ]) </p>
					</div>
					<div class="table-responsive">
						<table class="table borderless">
							<thead>
								<tr>
									<th class="text-center border-0" colspan="2">
										@lang('messages.lys.'.date('F')) @lang('messages.host_dashboard.breakdown')
									</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<td class="text-left">
										@lang('messages.host_dashboard.already_paid_out')
									</td>
									<td class="text-right">
										<strong>
										<sup>
										{{ $currency_symbol }}
										</sup>
										{{ $completed_payout }}
										</strong>
									</td>
								</tr>
								<tr>
									<td class="text-left">
										@lang('messages.host_dashboard.expected_earnings')
									</td>
									<td class="text-right">
										<strong>
										<sup>
										{{ $currency_symbol }}
										</sup>
										{{ $future_payouts }}
										</strong>
									</td>
								</tr>
								<tr class="total">
									<td class="text-left">
										@lang('messages.rooms.total')
										<sup>
										<i class="fa fa-question-circle" title="@lang('messages.host_dashboard.total_details')" rel="tooltip"></i>
										</sup>
									</td>
									<td class="text-right"><strong><sup>{{ $currency_symbol }}</sup>{{ $completed_payout  + $future_payouts}} </strong></td>
								</tr>
								<tr class="total_paid">
									<td class="text-left"> @lang('messages.host_dashboard.total_paid_out_in') {{ date('Y') }} </td>
									<td class="text-right"> <strong> <sup> {{ $currency_symbol }} </sup> {{ $total_payout }} </strong> </td>
								</tr>
							</tbody>
						</table>
					</div>
					<div class="transaction_history">
						<a href="{{ url('users/transaction_history') }}" class="btn w-100">
							@lang('messages.host_dashboard.transaction_history')
						</a>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div class="notify-wrap py-4 py-md-5" ng-init="unread_messages={{ $unread_messages }};pending_messages={{ $pending_messages }};">
		<div class="container">
			<div class="notify-tab">
				<ul role="tablist" class="d-flex tabs align-items-end" ng-init="current_tab=1;tab1=true;tab2=false">
					<li>
						<a href="javascript:void(0);" ng-click="current_tab=1;tab1=true;tab2=false" class="tab-item" role="tab" aria-controls="hdb-tab-standalone-first" aria-selected="@{{tab1}}">
							<span ng-show="pending_messages.length"> (@{{ pending_messages.length }} @lang('messages.dashboard.new')) </span>
							@lang('messages.host_dashboard.Pending_requests_and_inquiries')
						</a>
					</li>
					<li>
						<a href="javascript:void(0);" ng-click="current_tab=2;tab2=true;tab1=false" class="tab-item" role="tab" aria-controls="hdb-tab-standalone-second" aria-selected="@{{tab2}}">
							@lang('messages.host_dashboard.Notifications')
							<i class="alert-count text-center" ng-show="unread_messages.length > 0">
							@{{ unread_messages.length }}
							</i>
						</a>
					</li>
				</ul>
				<div class="notify-list" ng-show="current_tab == 1">
					<ul class="col-12 list-layout" ng-repeat="pending_message in pending_messages">
						<li class="d-flex" id="thread_@{{ pending_message.id }}">
							<div class="col-3 col-md-2 col-lg-1 list-img text-center p-0">
								<a data-popup="true" href="{{ route('show_profile',['id' => '/'])}}/@{{ pending_message.user_from}}">
									<img title="@{{ pending_message.user_name }}" ng-src="@{{ pending_message.reservation.profile_picture }}" class="media-round media-photo" ng-alt="pending_message.user_name">
								</a>
							</div>
							<div class="col-9 col-md-10 col-lg-11 p-0 d-md-flex mt-1 text-md-center">
								<div class="list-name col-12 col-md-3">
									<h3 class="text-truncate">
									@{{ pending_message.user_name }}
									</h3>
									<span class="list-date">
										@{{ pending_message.created_time }}
									</span>
								</div>
								<div class="reserve-link col-12 col-md-6">
									<a href="@{{ pending_message.reservation.inbox_url }}">
										<span class="list-subject unread-message font-weight-bold d-block"> @{{ pending_message.message }} </span>
										<span class="street-address">
											@{{ pending_message.reservation.rooms_address.address_line_1 }} @{{ pending_message.reservation.rooms_address.address_line_2 }},
										</span>
										<span class="locality">
											@{{ pending_message.reservation.rooms_address.city }},
										</span>
										<span class="region">
											@{{ pending_message.reservation.rooms_address.state }}
										</span>
										<span class="check-date d-inline-block" ng-if="pending_message.reservation.status == 'Pending'"> (@{{ pending_message.reservation.dates_subject }}) </span>
										<span class="msg-count" ng-if="pending_message.inbox_thread_count > 1">
											<i class="alert-count1 text-center inbox_message_count"> @{{ pending_message.inbox_thread_count }} </i>
										</span>
									</a>
								</div>
								<div class="list-status col-12 col-md-3">
									<span class="d-block label label-@{{ pending_message.reservation.status_color }}">
										<strong> @{{ pending_message.reservation.status_language }} </strong>
									</span>
									<span>
										{{ $currency_symbol }} @{{ pending_message.reservation.subtotal - pending_message.reservation.host_fee }}
									</span>
								</div>
							</div>
						</li>
					</ul>
					<div class="col-12 text-center">
						<a class="theme-link" href="{{ route('inbox') }}"> @lang('messages.dashboard.all_messages') </a>
					</div>
				</div>
				<!-- notification -->
				<div class="notify-list" ng-show="current_tab==2">
					<ul class="col-12 list-layout">
						<!-- Start Admin Message -->
						<li class="d-flex" id="thread_@{{ unread_message.id }}" ng-repeat="unread_message in unread_messages">
							<div class="col-3 col-md-2 col-lg-1 list-img text-center p-0" ng-if="unread_message.user_from == unread_message.user_to" >
								<a data-popup="true" href="#">
									<img title="{{ $admin_name }}" src="{{ asset('admin_assets/dist/img/avatar04.png') }}" class="media-round media-photo" alt="{{ $admin_name }}" >
								</a>
							</div>
							<div class="col-3 col-md-2 col-lg-1 list-img text-center p-0" ng-if="unread_message.user_from != unread_message.user_to">
								<a data-popup="true" href="{{ route('show_profile',['id' => '/'])}}/@{{ unread_message.user_from}}">
									<img title="@{{ unread_message.user_name }}" ng-src="@{{ unread_message.user_src }}" class="media-round media-photo" alt="@{{ unread_message.user_name }}">
								</a>
							</div>
							<div class="col-9 col-md-10 col-lg-11 p-0 d-md-flex mt-1 text-md-center" ng-if="unread_message.user_from == unread_message.user_to">
								<div class="list-name col-12 col-md-3">
									<h3 class="text-truncate">
									{{ $admin_name }}
									</h3>
									<span class="list-date">
										@{{ unread_message.created_time }}
									</span>
								</div>
								<div class="reserve-link col-12 col-md-6" ng-init="admin_message='{{ route('admin_messages',['id' => '/']) }}/'+unread_message.user_to;resubmit_message='{{ route('admin_resubmit_message',['id' => '/']) }}/'+unread_message.reservation_id;">
									<a href="@{{ (unread_message.room_id == 0) ? admin_message : resubmit_message }}">
										<span class="list-subject unread_message font-weight-bold">
											@{{ unread_message.message }}
										</span>
										<span class="msg-count" ng-show="unread_message.inbox_thread_count > 1">
											<i class="alert-count1 text-center inbox_message_count">
											@{{ unread_message.inbox_thread_count }}
											</i>
										</span>
									</a>
								</div>
							</div>
							<div class="col-9 col-md-10 col-lg-11 p-0 d-md-flex mt-1 text-md-center" ng-if="unread_message.user_from != unread_message.user_to">
								<div class="list-name col-12 col-md-3">
									<h3 class="text-truncate">
									@{{ unread_message.user_name }}
									</h3>
									<span class="list-date">
										@{{ unread_message.created_time }}
									</span>
								</div>
								<div class="reserve-link col-12 col-md-6">
									<a class="link-reset text-muted1" href="@{{ unread_message.reservation.inbox_url }}">
										<span class="list-subject unread_message font-weight-bold d-block">
											@{{ unread_message.message }}
											<span class="msg-count" ng-show="unread_message.inbox_thread_count > 1">
												<i class="alert-count1 text-center inbox_message_count">
												@{{ unread_message.inbox_thread_count }}
												</i>
											</span>
										
										</span>
										<span class="street-address">
											@{{ unread_message.reservation.rooms_address.address_line_1 }}
											@{{ unread_message.reservation.rooms_address.address_line_2 }},
										</span>
										<span class="locality">
											@{{ unread_message.reservation.rooms_address.city }},
										</span>
										<span class="region">
											@{{ unread_message.reservation.rooms_address.state }}
										</span>
										<span class="check-date d-inline-block" ng-if="unread_message.reservation.type != 'contact'"> (@{{ unread_message.reservation.dates_subject }}) </span>
									</a>
								</div>
								<div class="list-status col-12 col-md-3" ng-if="unread_message.reservation.list_type != 'Experiences' || unread_message.reservation.type != 'contact'">
									<span class="d-block label font-weight-bold label-@{{ unread_message.reservation.status_color }}">
										@{{ unread_message.reservation.status_language }}
									</span>
									{{ $currency_symbol }}
									<span ng-show="unread_message.host_check == 1">
										@{{ unread_message.reservation.subtotal - unread_message.reservation.host_fee }}
									</span>
									<span ng-show="unread_message.host_check != 1">
										@{{ unread_message.reservation.total }}
									</span>
								</div>
							</div>
						</li>
					</ul>
					<div class="col-12 text-center">
						<a class="theme-link" href="{{ url('inbox') }}">
							@lang('messages.dashboard.all_messages')
						</a>
					</div>
				</div>
				<div class="invite-wrap text-center mt-4 mt-md-5 py-4">
					<h3 class="font-weight-bolder"> @lang('messages.host_dashboard.earn_Travel') </h3>
					<p> @lang('messages.referrals.earn_up_to') {{ html_string(referral_settings('currency_code')) }}{{ referral_settings('if_friend_guest_credit') + referral_settings('if_friend_host_credit') }} @lang('messages.referrals.everyone_invite'). </p>
					<a href="{{ url('invite') }}" class="btn btn-large btn-primary">
						@lang('messages.host_dashboard.invite_friends')
					</a>
				</div>
			</div>
		</div>
	</div>
</main>
@endsection