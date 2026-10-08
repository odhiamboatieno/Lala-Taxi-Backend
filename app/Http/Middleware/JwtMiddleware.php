<?php

namespace App\Http\Middleware;

use Tymon\JWTAuth\Http\Middleware\BaseMiddleware;
use Closure;
use JWTAuth;
use App;
use Auth;

class JwtMiddleware extends BaseMiddleware
{
	/**
	* Handle an incoming request.
	*
	* @param  \Illuminate\Http\Request  $request
	* @param  \Closure  $next
	* @return mixed
	*/
	public function handle($request, Closure $next)
	{
		$validate_token = $this->validateToken($request);

		if($validate_token) {
			if ($request->is('api/corporate/*') && $validate_token->getStatusCode() === 401) {
				return response()->json(['status_code' => '0', 'status_message' => __('corporate.authentication_required')], 401);
			}
			return $validate_token;
		}

		return $next($request);
	}

	protected function validateToken($request)
	{
		try {
			$user_details = JWTAuth::setRequest($request)->parseToken()->authenticate();
			if($user_details === '') {
				return response()->json(['status' => 'Token is Invalid'],401);
			}

			if(!$user_details) {
				return response()->json(['status' => 'user_not_found'], $request->is('api/corporate/*') ? 401 : 200);
			}

			if($user_details->status == 'Suspended') {
				return response()->json(['status' => 'Your account has been suspended, contact support.'],401);
			}
			if($user_details->status == 'Inactive') {
				return response()->json(['status' => 'Inactive User'],401);
			}
			if($user_details->company && $user_details->company->status != 'Active') {
				return response()->json(['status' => 'Your Comapany Inactivated'],401);
			}
			Auth::setUser($user_details);
			$request->attributes->set('verified_jwt_user', $user_details);

			if ($user_details && @$user_details->language !== null) {
				session(['language' => $user_details->language]);
				App::setLocale($user_details->language);
			}
			else if(isset($request->language)) {
				session(['language' => $request->language]);
				App::setLocale($request->language);
			}
			else {
				App::setLocale('en');
			}
		}
		catch (\Exception $e) {
			if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenInvalidException) {
				return response()->json(['status' => 'Token is Invalid'],401);
			}
			else if ($e instanceof \Tymon\JWTAuth\Exceptions\TokenExpiredException) {
				return $this->getRefreshToken($request->token);
			}
			else {
				return response()->json(['status' => 'Authorization Token not found'],401);
			}
		}
		return false;
	}

	protected function getRefreshToken($token)
	{
		try {
			$refreshed = JWTAuth::refresh($token);
		}
		catch (\Exception $e) {
			return response()->json(['status' => 'Token is Invalid'],401);
		}

		return response()->json([
			'status_message' 	=> "Token Expired",
			'status_code' 		=> "0",
			'refresh_token' 	=> $refreshed,
		]);
	}
}
