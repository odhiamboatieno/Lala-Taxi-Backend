	<footer>
		@php
			$footerAppLinks = $app_links ?? collect();
			$footerLanguageOptions = $language ?? collect(['en' => 'English']);
			$footerDefaultLanguage = $default_language[0] ?? (object) ['value' => 'en', 'name' => 'English'];
			$footerCurrencyOptions = $currency_select ?? collect(['USD']);
			$footerCompanyPages = $company_pages ?? collect();
			$footerJoinUs = $join_us ?? collect();
			$riderAppStore = optional($footerAppLinks->get(0))->value ?? '';
			$driverAppStore = optional($footerAppLinks->get(1))->value ?? '';
			$riderPlayStore = optional($footerAppLinks->get(2))->value ?? '';
			$driverPlayStore = optional($footerAppLinks->get(3))->value ?? '';
		@endphp
		<div class="cls_footer">
			<div class="container">
				<div class="row">
					<div class="col-lg-3">
						<div class="footerlogo">
							<a href="{{ url('/') }}"><img class="" src="{{ url(PAGE_LOGO_URL)}}"></a>
						</div>
						<div class="threelinks">
							<li>
								<a href="{{ url('ride') }}" class="city-link">{{trans('messages.footer.ride')}}
								</a>
							</li>
							<li>
								<a href="{{ url('drive') }}" class="city-link">{{trans('messages.footer.drive')}}
								</a>
							</li>
							<li>
								<a href="{{ url('safety') }}" class="city-link">{{trans('messages.footer.safety')}}
								</a>
							</li>
						</div>
						
					</div>
					<div class="col-lg-9">
						<div class="footertwo">
							<div class="footertwolinks">
								<ul>
									@if (in_array(Route::current()->uri(),['/','ride','signup_rider','ride','signin_rider']) || Auth::check() && Auth::user()->user_type='Rider')
										<li>
											@if($riderAppStore != '' )
												<a class="googleplay_class" href="{{ $riderAppStore }}" target="_blank">
													<img src="{{ url('images/new/app.png') }}" alt="{{ __('messages.blade_static.download_on_the_appstore_160bf3a7') }}" class="CToWUd">
												</a>
											@endif
										</li>
										<li>
											@if($riderPlayStore != '' )
												<a href="{{ $riderPlayStore }}" target="_blank" class="ios_class">
													<img src="{{ url('images/new/google.png') }}" alt="{{ __('messages.blade_static.get_it_on_googleplay_88be4aa2') }}" class="CToWUd bot_footimg">
												</a>
											@endif
										</li>
									@else
										<li>
											@if($driverAppStore != '' )
												<a class="googleplay_class" href="{{ $driverAppStore }}" target="_blank">
													<img src="{{ url('images/new/app.png') }}" alt="{{ __('messages.blade_static.download_on_the_appstore_160bf3a7') }}" class="CToWUd">
												</a>
											@endif
										</li>
										<li>
											@if($driverPlayStore != '' )
												<a href="{{ $driverPlayStore }}" target="_blank" class="ios_class">
													<img src="{{ url('images/new/google.png') }}" alt="{{ __('messages.blade_static.get_it_on_googleplay_88be4aa2') }}" class="CToWUd bot_footimg">
												</a>
											@endif
										</li>

									@endif


									<li style="padding: 0">
										<div class="currency_select">
											{!! Form::select('language',$footerLanguageOptions, (Session::get('language')) ? Session::get('language') : $footerDefaultLanguage->value, ['class' => 'cls_lang', 'aria-labelledby' => 'language-selector-label', 'id' => 'js-language-select']) !!}
										</div>
									</li>
									<li style="padding: 0;margin: 0;">
										<div class="currency_select">
											<select id="js-currency-select" class="cls_lang">
												@foreach($footerCurrencyOptions as $code)
												<option value="{{$code}}" @if(session('currency') == $code ) selected="selected" @endif >{{$code}}
												</option>
												@endforeach
											</select>
										</div>
									</li>
								</ul>
							</div>
							<div class="footertwolinks1">
								<ul class="">
									@foreach($footerCompanyPages as $company_page)
									<li>
										<a class="" href="{{ url($company_page->url) }}">
											{{ $company_page->name }}
										</a>
									</li>
									@endforeach
								</ul>
							</div>
							<div class="footertwolinks2 displayflex">
								<div class="social-icons">
									<div class="foot_soc">
										@for($i=0; $i < $footerJoinUs->count(); $i++)
										@if($footerJoinUs[$i]->value)
										<a href="{{ $footerJoinUs[$i]->value }}" target="_blank"> 
											<span class="fa fa-{{ str_replace('_','-',$footerJoinUs[$i]->name) }}"></span>
										</a>        
										@endif
										@endfor
									</div>
								</div>
								@if(!Auth::user())
									<div class="threelinks1">
										<li><a href="{{ url('signup_rider') }}">{{trans('messages.footer.siginup_ride')}}</a></li>
										<li><a href="{{ url('signup_driver') }}">{{trans('messages.footer.driver')}}</a></li>
										@if(Auth::guard('company')->user()==null)
										<li><a href="{{ url('signup_company') }}">{{trans('messages.home.become_company')}}</a></li>
										@endif
									</div>
									@endif
							</div>
						</div>
					</div>
				</div>	
				<div class="row text-center" style="margin-top: 40px;color: white;">
					<div class="pull-right hidden-xs" >
				    </div>
				    <strong>Copyright &copy; {{$copyright_year}} <a href="{{$copyright_url}}" style="color: white;"> {{$copyright_name}} </a>.</strong> {{ __('messages.blade_static.all_rights_reserved_3639ec0f') }}
			    </div>
			</div>
		</div>

	</footer>
