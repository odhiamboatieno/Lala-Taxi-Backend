<?php

namespace App\Providers;

use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;
use App\Http\Helper\FacebookHelper;
use App\Models\Admin;
use App\Models\CarType;
use App\Models\Language;
use App\Models\SiteSettings;
use App\Models\EmailSettings;
use App\Models\ApiCredentials;
use App\Models\PaymentGateway;
use App\Models\Country;
use App\Models\Fees;
use App\Models\ReferralSetting;
use App\Models\Support;
use App\Models\Banner;
use Illuminate\Support\Collection;
use Illuminate\Pagination\LengthAwarePaginator;
use Config;
use DB;
use Session;
use App;
use View;

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

        foreach(glob(app_path() . '/Constants/*.php') as $file) {
            require_once $file;
        }

        logger('requested URL : '.request()->fullUrl());
		if(request()->isMethod('POST')) {
			logger('Post Method Params : '.json_encode(request()->post()));
		}

        $this->app->singleton('google_service', function($app) {
            $google_service = new \App\Services\GoogleAPIService;
            return $google_service;
        });
    }

	/**
	 * Bootstrap any application services.
	 *
	 * @return void
	 */
	public function boot()
	{
	   //$this->app['request']->server->set('HTTPS', true);
       	
		if (!defined('EMAIL_LOGO_URL')) {
			define('EMAIL_LOGO_URL', 'images/logo.png');
		}
		if (!defined('LOGIN_USER_TYPE')) {
			define('LOGIN_USER_TYPE', request()->segment(1));
		}
		if (!defined('PHP_DATE_FORMAT')) {
			define('PHP_DATE_FORMAT','Y-m-d');
		}

		View::share('logo_url', url('images/logos/logo.png'));
		View::share('favicon', url('favicon.ico'));
		View::share('site_name', config('app.name', 'Laravel'));
		View::share('map_key', '');
		View::share('version', config('app.version') ?? '1');
		View::share('banner_links', collect());
		View::share('support_links', collect());
		View::share('admin_email', '');


		Schema::defaultStringLength(191);

		if (env('DB_DATABASE') != '') {
			try {
				$this->bindModels();
				$this->shareCommonData();

	
			// Configuration for data table pdf export
            config(['datatables-buttons.pdf_generator' => 'snappy']);

            if(!in_array(request()->segment(1),['admin','company','install']) && !\App::runningInConsole()) {
            	Config::set(['cache.default' => 'file']);
            }

			if (Schema::hasTable('site_settings')) {
				$site_settings = DB::table('site_settings')->get();
				$logoFile = optional($site_settings->where('name', 'logo')->first())->value ?: 'logo.png';
				$pageLogoFile = optional($site_settings->where('name', 'page_logo')->first())->value ?: $logoFile;
				$faviconFile = optional($site_settings->where('name', 'favicon')->first())->value ?: $logoFile;
				$siteName = optional($site_settings->where('name', 'site_name')->first())->value ?: config('app.name', 'Laravel');
				$manualBookContact = optional($site_settings->where('name', 'admin_contact')->first())->value ?: '';
				$manualBookCountryId = optional($site_settings->where('name', 'admin_country_code')->first())->value;
				$siteUrl = optional($site_settings->where('name', 'site_url')->first())->value ?: url('/');
				$driverKm = optional($site_settings->where('name', 'driver_km')->first())->value ?: 0;
				View::share('logo_url', url('images/logos/' . $logoFile).'?v='.str_random(4));
				if (!defined('LOGO_URL')) {
					define('LOGO_URL', 'images/logos/' . $logoFile);
				}
				if (!defined('PAGE_LOGO_URL')) {
					define('PAGE_LOGO_URL', 'images/logos/' . $pageLogoFile);
				}
				View::share('favicon', url('images/logos/' . $faviconFile).'?v='.str_random(5));
				View::share('site_name', $siteName);
				if (!defined('SITE_NAME')) {
					define('SITE_NAME', $siteName);
				}

				$findCountryCode = optional(Country::where('id', $manualBookCountryId)->first())->phone_code;
				if (!defined('MANUAL_BOOK_CONTACT')) {
					define('MANUAL_BOOK_CONTACT', trim(($findCountryCode ? '+'.$findCountryCode.' ' : '').$manualBookContact));
				}

				if (!defined('PAYPAL_CURRENCY_CODE')) {
					define('PAYPAL_CURRENCY_CODE', $site_settings[1]->value);
				}
				if (!defined('SITE_URL')) {
					define('SITE_URL',$siteUrl);
				}
				if (!defined('Driver_Km')) {
					define('Driver_Km', $driverKm);
				}
			}
			if (Schema::hasTable('country')) {
				$country = DB::table('country')->get();
				View::share('country', $country);

			}
			if (Schema::hasTable('car_type')) {
				$car_type = CarType::where('status', 'Active')->get();
				View::share('car_type', $car_type);

			}

			if (Schema::hasTable('api_credentials')) {

				$api_credentials = app()->bound('api_credentials') ? resolve('api_credentials') : ApiCredentials::get();

				// For Google Key
				$google_map_result = $api_credentials->where('site', 'GoogleMap');
				$googleMapKey = optional($google_map_result->where('name','key')->first())->value ?? '';
				$googleMapServerKey = optional($google_map_result->where('name','server_key')->first())->value ?? '';
				if (!defined('MAP_KEY')) {
					define('MAP_KEY', $googleMapKey);
				}
				if (!defined('MAP_SERVER_KEY')) {
					define('MAP_SERVER_KEY', $googleMapServerKey);
				}
				View::share('map_key', $googleMapKey);

				//For facebook
				$facebook_result = $api_credentials->where('site', 'Facebook');
				if (!defined('FB_CLIENT_ID')) {
					define('FB_CLIENT_ID', optional($facebook_result->where('name','client_id')->first())->value ?? '');
				}

	        	// Share Google Credentials
	        	$google_result =  $api_credentials->where('site','Google');
	        	if (!defined('GOOGLE_CLIENT_ID')) {
	        		define('GOOGLE_CLIENT_ID', optional($google_result->where('name','client_id')->first())->value ?? '');
	        	}

		
				// For FCM Key
				$fcm_result = $api_credentials->where('site', 'FCM');
				Config::set(['fcm.http' => [
						'server_send_url' => 'https://fcm.googleapis.com/fcm/send',
						'server_group_url' => 'https://android.googleapis.com/gcm/notification',
						'timeout' => 10,
					],
				]);

				// For Facebook app id and secret
				$fb_result = $api_credentials->where('site', 'Facebook');
				Config::set([
					'facebook' => [
						'client_id' => optional($fb_result->where('name','client_id')->first())->value ?? '',
						'client_secret' => optional($fb_result->where('name','client_secret')->first())->value ?? '',
						'redirect' => url('/facebookAuthenticate'),
					],
				]);

				$fb = new FacebookHelper;
				View::share('fb_url', $fb->getUrlLogin());
				if (!defined('FB_URL')) {
					define('FB_URL', $fb->getUrlLogin());
				}
			}

			if(Schema::hasTable('admin')) {
				$admin_email = Admin::first()->email;
				View::share('admin_email', $admin_email);
			}

			if(Schema::hasTable('banners')) {
				$banner = Banner::active()->get();
				View::share('banner_links', $banner);
			}

			if(Schema::hasTable('supports')) {
				$support = Support::active()->get();
				View::share('support_links', $support);
			}

			if (Schema::hasTable('payment_gateway')) {
				$this->setPaymentConfig();
			}

			// Configure Email settings from email_settings table
	            if(Schema::hasTable('email_settings'))
	        {
	            $result = DB::table('email_settings')->get();

	            Config::set([
                    'mail.default' => email_settings('driver'),
                    'mail.mailers.smtp.host' 		=> email_settings('host'),
                    'mail.mailers.smtp.port'       	=> email_settings('port'),
                    'mail.mailers.smtp.encryption' 	=> email_settings('encryption'),
                    'mail.mailers.smtp.username'   	=> email_settings('username'),
                    'mail.mailers.smtp.password'   	=> email_settings('password'),
                    'mail.from' => [
                    	'address' => email_settings('from_address'),
                    	'name'    => email_settings('from_name')
                    ],
                ]);

	            if(email_settings('driver') == 'mailgun') {
		            Config::set([
	                    'services.mailgun.domain'     => email_settings('domain'),
	                    'services.mailgun.secret'     => email_settings('secret'),
	                ]);
	           	}

	            Config::set([
                    'laravel-backup.notifications.mail.from' => email_settings('from_address'),
                    'laravel-backup.notifications.mail.to'   => email_settings('from_address'),
	            ]);
	        }
			}
			catch (\Throwable $exception) {
				if ($this->app->runningInConsole() || $this->app->environment('testing')) {
					logger('Skipping database-bound AppServiceProvider boot logic: '.$exception->getMessage());
				}
				else {
					throw $exception;
				}
			}
		}

		if (!defined('LOGO_URL')) {
			define('LOGO_URL', 'images/logos/logo.png');
		}
		if (!defined('PAGE_LOGO_URL')) {
			define('PAGE_LOGO_URL', defined('LOGO_URL') ? LOGO_URL : 'images/logos/logo.png');
		}
		if (!defined('SITE_NAME')) {
			define('SITE_NAME', config('app.name', 'Laravel'));
		}
		if (!defined('MAP_KEY')) {
			define('MAP_KEY', '');
		}
		if (!defined('MAP_SERVER_KEY')) {
			define('MAP_SERVER_KEY', '');
		}
		if (!defined('MANUAL_BOOK_CONTACT')) {
			define('MANUAL_BOOK_CONTACT', '');
		}
		if (!defined('PAYPAL_CURRENCY_CODE')) {
			define('PAYPAL_CURRENCY_CODE', 'USD');
		}
		if (!defined('SITE_URL')) {
			define('SITE_URL', url('/'));
		}
		if (!defined('Driver_Km')) {
			define('Driver_Km', 0);
		}
		if (!defined('FB_CLIENT_ID')) {
			define('FB_CLIENT_ID', '');
		}
		if (!defined('GOOGLE_CLIENT_ID')) {
			define('GOOGLE_CLIENT_ID', '');
		}
		if (!defined('FB_URL')) {
			define('FB_URL', url('/'));
		}

		View::share('logo_url', View::shared('logo_url') ?? url(LOGO_URL));
		View::share('favicon', View::shared('favicon') ?? url('favicon.ico'));
		View::share('site_name', View::shared('site_name') ?? SITE_NAME);
		View::share('map_key', View::shared('map_key') ?? MAP_KEY);
		View::share('version', View::shared('version') ?? (config('app.version') ?? '1'));

		// Enable pagination
	    if (!Collection::hasMacro('paginate')) {
	    	Collection::macro('paginate', function($perPage, $total = null, $page = null, $pageName = 'page') {
	            $page = $page ?: LengthAwarePaginator::resolveCurrentPage($pageName);

	            return new LengthAwarePaginator($this->forPage($page, $perPage), $total ?: $this->count(), $perPage, $page, [
	                'path' => LengthAwarePaginator::resolveCurrentPath(),
	                'pageName' => $pageName,
	            ]);
	        });
	    }

	    // Append Array to laravel Collection through map
	    if (!Collection::hasMacro('setAppends')) {
	    	Collection::macro('setAppends', function ($attributes) {
			    return $this->map(function ($item) use ($attributes) {
			        return $item->setAppends($attributes);
			    });
			});
	    }

	    // Append Array to laravel Collection through transform
	    if (!Collection::hasMacro('transformWithAppends')) {
	    	Collection::macro('transformWithAppends', function ($attributes) {
	    		return $this->transform(function ($item) use ($attributes) {
	    			foreach ($attributes as $attribute) {
	    				$item[$attribute] = $item->$attribute;
	    			}
	    			return $item;
			    });
			});
	    }

	    // Custom Validation for File Extension
        \Validator::extend('valid_extensions', function($attribute, $value, $parameters) 
        {
            if(count($parameters) == 0) {
                return false;
            }
            $ext = strtolower($value->getClientOriginalExtension());
            
            return in_array($ext,$parameters);
        });
	}

	protected function bindModels()
	{
		if (Schema::hasTable('site_settings')) {
			$this->app->singleton('site_settings', function ($app) {
	            $site_settings = SiteSettings::get();
	            return $site_settings;
	        });
		}

		if (Schema::hasTable('email_settings')) {
			$this->app->singleton('email_settings', function ($app) {
	            $email_settings = EmailSettings::get();
	            return $email_settings;
	        });
		}

		if (Schema::hasTable('api_credentials')) {
			$this->app->singleton('api_credentials', function ($app) {
	            $api_credentials = ApiCredentials::get();
	            return $api_credentials;
	        });
		}

		if (Schema::hasTable('payment_gateway')) {
			$this->app->singleton('payment_gateway', function ($app) {
	            $payment_gateway = PaymentGateway::get();
	            return $payment_gateway;
	        });
		}

		if (Schema::hasTable('referral_settings')) {
			$this->app->singleton('referral_settings', function ($app) {
	            $referral_settings = ReferralSetting::get();
	            return $referral_settings;
	        });
		}

		if (Schema::hasTable('fees')) {
			$this->app->singleton('fees', function ($app) {
	            $fees = Fees::get();
	            return $fees;
	        });
		}

		View::share('map_key', defined('MAP_KEY') ? MAP_KEY : '');
		View::share('site_name', defined('SITE_NAME') ? SITE_NAME : config('app.name', 'Laravel'));
		View::share('favicon', View::shared('favicon') ?? url('favicon.ico'));
		View::share('logo_url', View::shared('logo_url') ?? url(LOGO_URL));
		View::share('version', View::shared('version') ?? (config('app.version') ?? '1'));

		if (Schema::hasTable('vehicle_type')) {
			$this->app->singleton('vehicle_type', function ($app) {
	            $car_types = \App\Models\CarType::get();
	            return $car_types;
	        });
		}

		$this->app->bind(\App\Contracts\ImageHandlerInterface::class,\App\Services\LocalImageHandler::class);
		$this->app->bind(\App\Contracts\SMSInterface::class,\App\Services\SMS\TwillioSms::class);
	}

	protected function shareCommonData()
	{
		$acceptable_mimes = [
			'image/jpeg',
			'image/jpg',
			'image/gif',
			'image/png',
		];

		View::share('acceptable_mimes',$acceptable_mimes);
	}

	protected function setPaymentConfig()
	{
		$paypal_mode  = payment_gateway('mode','Paypal');
		
		if (!defined('PAYPAL_ID')) {
			define('PAYPAL_ID', payment_gateway('mode','paypal_id'));
		}
		if (!defined('PAYPAL_MODE')) {
			define('PAYPAL_MODE', ($paypal_mode == 'sandbox') ? 0 : 1);
		}
		if (!defined('PAYPAL_CLIENT_ID')) {
			define('PAYPAL_CLIENT_ID', payment_gateway('client','Paypal'));
		}

if (!defined('STRIPE_KEY')) {
            define('STRIPE_KEY', payment_gateway('publish','Stripe'));
        }
        if (!defined('STRIPE_SECRET')) {
            define('STRIPE_SECRET', payment_gateway('secret','Stripe'));
        }

        $this->app->bind('paypal', function($app) {
        	$gateway = \Omnipay\Omnipay::create('PayPal_Rest');

			$gateway->initialize([
				'clientId' 	=> payment_gateway('client','Paypal'),
				'secret' 	=> payment_gateway('secret','Paypal'),
				'testMode' 	=> (payment_gateway('mode','Paypal') == 'sandbox'),
			]);

			return $gateway;
        });
    }
}
