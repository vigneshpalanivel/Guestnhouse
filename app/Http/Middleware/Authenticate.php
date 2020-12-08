<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\Factory as Auth;
use Session;

class Authenticate
{
    /**
     * The authentication factory instance.
     *
     * @var \Illuminate\Contracts\Auth\Factory
     */
    protected $auth;

    /**
     * Create a new middleware instance.
     *
     * @param  \Illuminate\Contracts\Auth\Factory  $auth
     * @return void
     */
    public function __construct(Auth $auth)
    {
        $this->auth = $auth;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string[]  ...$guards
     * @return mixed
     *
     * @throws \Illuminate\Auth\AuthenticationException
     */
    public function handle($request, Closure $next, ...$guards)
    {
        $guard = @$guards[0] ?: 'user';

        $redirect_to = 'login';

        if($guard == 'admin') {
            $redirect_to = ADMIN_URL.'/login';
        }
        $is_admin_path = ($request->segment(1) == ADMIN_URL);

        // Redirect to payment for stripe confirmation in mobile payment
        if(isset($request->is_mobile) && session('get_token')) {
            return $next($request);
        }

        if (!$this->auth->guard($guard)->check() && ($guard != 'admin' || $is_admin_path)) {
            // Save the payment data in session
            if($request->route()->uri == 'payments/book/{id?}')
            {
                $s_key = $request->s_key ?: time().$request->id.str_random(4);

                if($request->s_key) {
                    $payment = Session::get('payment.'.$request->s_key);
                }
                else {
                    $payment = array(
                        'payment_room_id' => $request->id, 
                        'payment_checkin' => @$request->checkin,
                        'payment_checkout' => @$request->checkout,
                        'payment_number_of_guests' => @$request->number_of_guests,
                        'payment_booking_type' => @$request->booking_type,
                        'payment_reservation_room_type' => @$request->room_types,
                        'payment_reservation_id' => @$request->reservation_id,
                        'payment_special_offer_id' => @$request->special_offer_id,
                        'payment_cancellation' => $request->cancellation,
                        'payment_adults' => (@$_POST['single_adults'])?@$_POST['single_adults']:1,
                        'payment_childrens' => (@$_POST['single_childrens'])?@$_POST['single_childrens']:0,
                        'payment_infants' => (@$_POST['single_infants'])?@$_POST['single_infants']:0,
                        'payment_sub_room'     => (@$_POST['sub_room'])?@$_POST['sub_room']:'',
                        'payment_number_of_rooms'  => (@$_POST['number_of_rooms'])?@$_POST['number_of_rooms']:'',
                        'payment_partial_check' => (@$_POST['partial_check']=='on' || @$_POST['partial_check']=='Yes')?'Yes':'No',
                        'listing_type' => (@$_POST['listing_type'])?@$_POST['listing_type']:'Single',
                    );
                    Session::put('payment.'.$s_key,$payment);
                }
                Session::put('url.intended', url('payments/book').'?s_key='.$s_key);
            }
            else if(strpos($request->url(), 'manage-listing'))
            {
                $redirect_url='manage-listing/'.$request->id.'/basics';
                if($request->ajax()) {
                    Session::put('ajax_redirect_url',$redirect_url);
                    return response('Unauthorized', 300);
                }else{
                    if($request->type){
                        $url = 'manage-listing/'.$request->id.'/'.$request->page.'?type='.$request->type;
                        Session::put('ajax_redirect_url',$url);
                    }
                    else{
                        $url = 'manage-listing/'.$request->id.'/'.$request->page;
                        Session::put('ajax_redirect_url',$url);
                    }
                }
            }
            else {
                session(['url.intended' => url()->current()]);
            }
            return redirect($redirect_to);
        }
        else if($guard == 'admin' && !$is_admin_path) {
            return redirect('about/'.$request->segment(1));
        }
        if($this->auth->guard($guard)->user()->status == 'Inactive') {
            $this->auth->guard($guard)->logout();
            return redirect($redirect_to);
        }

        return $next($request);
    }
}
