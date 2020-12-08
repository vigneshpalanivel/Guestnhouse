<?php

/**
 * AppService Provider
 *
 * @package     Makent
 * @subpackage  Provider
 * @category    Service
 * @author      Trioangle Product Team
 * @version     2.2
 * @link        http://trioangle.com
 */

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use DB;
use Config;
use Schema;
use Validator;
use App\Models\SiteSettings;
use View;
use App\Http\Helper\FacebookHelper;
use Illuminate\Support\Collection;

class AppServiceProvider extends ServiceProvider
{

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        foreach(glob(app_path() . '/Helpers/*.php') as $file) {
            require_once $file;
        }
        
        Collection::macro('paginate', function($perPage, $total = null, $page = null, $pageName = 'page') {
            $page = $page ?: \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPage($pageName);

            return new \Illuminate\Pagination\LengthAwarePaginator($this->forPage($page, $perPage), $total ?: $this->count(), $perPage, $page, [
                'path' => \Illuminate\Pagination\LengthAwarePaginator::resolveCurrentPath(),
                'pageName' => $pageName,
            ]);
        });

        // Enable Implode macro for collection
        if (!Collection::hasMacro('implode')) {
            Collection::macro('implode', function($glue) {
                return implode($this,$glue);
            });
        }
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        
        /*logger('requested URL : '.request()->fullUrl());
        if(request()->isMethod('POST')) {
            logger('Post Method Params : '.json_encode(request()->post()));
        }*/
        
        // Configuration Setup for Social Media Services
        if(env('DB_DATABASE') != '') {
            if(Schema::hasTable('api_credentials')) {
                $google_result = DB::table('api_credentials')->where('site','Google')->get();
                $linkedin_result = DB::table('api_credentials')->where('site','LinkedIn')->get();
                $fb_result = DB::table('api_credentials')->where('site','Facebook')->get();
                $google_map_result = DB::table('api_credentials')->where('site','GoogleMap')->get();
                $nexmo_result = DB::table('api_credentials')->where('site','Nexmo')->get();
                $cloudinary_result = DB::table('api_credentials')->where('site','Cloudinary')->get();
            
                Config::set(['services.google' => [
                        'client_id' => $google_result[0]->value,
                        'client_secret' => $google_result[1]->value,
                        'redirect' => url('/googleAuthenticate'),
                    ]
                ]);
                Config::set(['services.linkedin' => [
                        'client_id' => $linkedin_result[0]->value,
                        'client_secret' => $linkedin_result[1]->value,
                        'redirect' => url('/linkedinConnect'),
                    ]
                ]);

                Config::set(['facebook' => [
                        'client_id' => $fb_result[0]->value,
                        'client_secret' => $fb_result[1]->value,
                        'redirect' => url('/facebookAuthenticate'),
                    ]
                ]);
                
                //FCM Configuration
                $fcm_result = DB::table('api_credentials')->where('site','FCM')->get();
                Config::set(['fcm.http' => [
                        'server_key' => $fcm_result[0]->value,
                        'sender_id' => $fcm_result[1]->value,
                        'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
                        'server_group_url' => 'https://android.googleapis.com/gcm/notification',                
                        'timeout' => 30,
                    ]
                ]);

                /*Set Cloudinary configuration*/
                Config::set(['cloudder' => [
                    'cloudName' => $cloudinary_result[0]->value,
                    'apiKey' => $cloudinary_result[1]->value,
                    'apiSecret' => $cloudinary_result[2]->value,
                    'baseUrl' => $cloudinary_result[3]->value.$cloudinary_result[0]->value,
                    'secureUrl' => $cloudinary_result[4]->value.$cloudinary_result[0]->value,
                    'apiBaseUrl' => $cloudinary_result[5]->value.$cloudinary_result[0]->value,
                    ]
                ]);

                Config::set('cloudder.scaling', array());

                View::share('google_client_id', $google_result[0]->value);

                View::share('map_key', $google_map_result[0]->value);

                $fb = new FacebookHelper;
                View::share('fb_url',url('/facebooklogin'));

                View::share('map_server_key', $google_map_result[1]->value);

                if(count($nexmo_result) > 2) {
                    View::share('nexmo_key', $nexmo_result[0]->value);
                    View::share('nexmo_secret', $nexmo_result[1]->value);
                    View::share('nexmo_from', $nexmo_result[2]->value);
                }

            }
        }

        // Custom Validation for CreditCard is Expired or Not
        Validator::extend('expires', function($attribute, $value, $parameters, $validator) 
        {
            $input    = $validator->getData();

            $expiryDate = gmdate('Ym', gmmktime(0, 0, 0, (int) array_get($input, $parameters[0]), 1, (int) array_get($input, $parameters[1])));
            
            return ($expiryDate >= gmdate('Ym')) ? true : false;
        });

        // Custom Validation for CreditCard is Valid or Not
        Validator::extend('validateluhn', function($attribute, $value, $parameters) 
        {
            if((is_numeric($value))) {
                $str = '';
                foreach (array_reverse(str_split($value)) as $i => $c) {
                    $str .= $i % 2 ? $c * 2 : $c;

                }

                return array_sum(str_split($str)) % 10 === 0;
            }
            return false;            
        });

        // Custom Validation for File Extension
        Validator::extend('extensionval', function($attribute, $value, $parameters) 
        {
            $ext = strtolower($value->getClientOriginalExtension());
            if($ext =='jpg' || $ext == 'jpeg' || $ext =='png'){
                return true;
            }
            return false;
        });

        // Custom Validation for txt File Extension
        Validator::extend('valid_extensions', function($attribute, $value, $parameters) 
        {
            if(count($parameters) == 0) {
                return false;
            }
            $ext = strtolower($value->getClientOriginalExtension());
            
            return in_array($ext,$parameters);
        });

        // Custom Validation for Min field may not be greater than max field value
        Validator::extend('maxmin', function($attribute, $value, $parameters) 
        { 
            $param=preg_replace('/\D/', '', $parameters);
            $maximum_value =  @$param? $param[0] : null;
            if($maximum_value != null && $value > $maximum_value) {
                return false;
            }
            return true;
        });

        // Custom Validation for Min field may not be greater than max field value for Minimum and maximum price calculation
        Validator::extend('maxminstrict', function($attribute, $value, $parameters) 
        { 
            $param=preg_replace('/\D/', '', $parameters);
            $maximum_value =  @$param ? $param[0] : null;
            if($value > $maximum_value) {
                return false;
            }
            return true;
        });

        if(env('DB_DATABASE') != '') {
            // Configuration Setup for Email Settings
            if(Schema::hasTable('email_settings')) {
                $result = DB::table('email_settings')->get();
                       
                Config::set([
                    'mail.driver'     => $result[0]->value,
                    'mail.host'       => $result[1]->value,
                    'mail.port'       => $result[2]->value,
                    'mail.from'       => ['address' => $result[3]->value,
                                          'name'    => $result[4]->value ],
                    'mail.encryption' => $result[5]->value,
                    'mail.username'   => $result[6]->value,
                    'mail.password'   => $result[7]->value
                ]);

                if($result[0]->value=='mailgun'){
                    Config::set([
                        'services.mailgun.domain'     => $result[8]->value,
                        'services.mailgun.secret'       => $result[9]->value,
                    ]);
                }

                Config::set([
                    'laravel-backup.notifications.mail.from' => $result[3]->value,
                    'laravel-backup.notifications.mail.to'   => $result[3]->value
                ]);
            }

            if(Schema::hasTable('site_settings')) {
                $site_settings = SiteSettings::all();
                $customer=$site_settings[21]->value;

                View::share('customer_support', $customer);

                Config::set([
                    'laravel-backup.backup.name'             => $site_settings[0]->value,
                    'laravel-backup.monitorBackups.name'     => $site_settings[0]->value,
                ]);
                
                Config::set([
                    'swap.providers' => [
                        "google_finance" => true
                    ]
                ]);
            }
        }
    }
}