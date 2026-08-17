@extends('templatesign')

@section('main')
<div class="container-fluid fixed-header" style="background:var(--color-as-rgb-248-248-249);z-index:99;">
<ul class="sub-header">
<li><a href="{{ url('safety') }}">{{trans('messages.footer.safety')}}</a></li>
<li><a href="{{ url('how_it_works') }}">{{trans('messages.header.how_it_works')}}</a></li>
</ul>
</div>
<div class="container-fluid ride-div-main" style="padding:0px !important;">
<div style="position:relative;float:left;width:100%;">
<div class="slide-img slide-img-ride"></div>

<div class="pattern" style="height: 520px; width: 274px; right: 0px; position: absolute; z-index: 10;"><div style="background-color: var(--color-as-a6daec); height: 100%; overflow: hidden;"><div aria-label="{{ __('messages.blade_static.decorative_pattern_b3870f86') }}" style="height: 100%;"><div class="isvg loaded" style="height: 100%;"><img src="{{url('images/icon/patten_274_520.jpg')}}">
</div>
</div>
</div>
</div>
@if(Auth::user()==null)
<div class="mini-green ride-mini-green" >
    <div href="#" class="_style_4jQAPw green-mini-div" style="width: 206px; padding: 32px 20px 20px 32px; display: block; position: relative; height: 206px; background-color: var(--color-as-ffc107)">
    <div class="_style_1PPmFR" style="font-weight: 500; color: var(--color-as-rgb-255-255-255);font-size: 21px; line-height: 1.4;">{{trans('messages.ride.ride_with_Cabme',['site_name'=>$site_name])}}</div>
<a class="btn btn--primary btn--arrow position--relative error-retry-btn" href="{{ url('signup_rider') }}" style= "background: transparent !important;     border: none !important;    float: right;margin-top: 55px;    margin-right: -16px;">
<div class="block-context soft-small--right">{{trans('messages.home.siginup')}}</div>
<i class="icon_right-arrow-thin icon transition delta position--absolute"></i>
</a>
    </div>
    </div>
@endif
</div>
</div>
<div class="container-fluid pad-sm-20 height-fluid" style="padding:0px;background:var(--color-white) !important;">
<div class="col-lg-10 col-lg-push-2 col-md-12 col-sm-12 col-xs-12 ride-always">
<div class="pad-44">
<h1 class="slide-head ride-head">{{trans('messages.ride.always_ride')}}</h1><p class="ride-content slide-content">
{{trans('messages.ride.get_whatever')}}</p>
<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12  column-content" style="padding:0px;">
	<div class="arrive-content">
<div style="position: relative !important;"><p class="_style_ZJW1y" style="
    margin-bottom: 25px !important;">{{trans('messages.ride.tap_button')}}</p>
</div><div><p class="cmln__paragraph">{{trans('messages.ride.tap_button_content')}}</p>
</div></div>
	</div>
	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12   column-content" style="padding:0px;">
	<div class="arrive-content">
<div style="position: relative !important;"><p class="_style_ZJW1y" style="
    margin-bottom: 25px !important;">{{trans('messages.ride.always_on')}}</p>
</div><div><p class="cmln__paragraph">{{trans('messages.ride.always_on_content')}}</p>
</div></div>
	</div>
	<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12  column-content" style="padding:0px;">

	<div class="arrive-content">
<div style="position: relative !important;"><p class="_style_ZJW1y" style="
    margin-bottom: 25px !important;">{{trans('messages.ride.we_listen')}} </p>
</div><div><p class="cmln__paragraph">{{trans('messages.ride.we_listen_content')}}</p>
</div></div>
	</div>
</div>
</div>
</div>
<div class="container-fluid" style="padding:0px;background:var(--color-as-f8f8f9)!important">
<div class="col-lg-10 col-lg-push-2 col-md-12 col-sm-12 col-xs-12 ride-always" style="background:var(--color-as-f8f8f9)!important;margin-top:0px !important;padding: 0px !important;padding-bottom: 0px !important;">
<div class="pad-44">
<h1 class="slide-head ride-slide">{{trans('messages.ride.every_price')}}</h1><p class="slide-content">
{{trans('messages.ride.any_occasion')}}</p>
<div class="col-lg-11 col-md-11 col-sm-12 col-xs-12" id="pad-sm-zero">
   <div id="mySliderTabsContainer" style="   margin-top: 25px;">
           
            <div id="mySliderTabs">
              <ul>
                <li><a href="#CabmeGO">{{trans('messages.ride.economy')}}</a></li> 
                <li><a href="#CabmeX">{{trans('messages.ride.premium')}}</a></li> 
                <li><a href="#CabmeXL">{{trans('messages.ride.accessibility')}}</a></li> 
              </ul>
              <div id="CabmeGO">
                <img src="{{ url('images/icon/slider_eco.jpg')}}">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;margin-top:50px;">
                </div>
              </div>     
              <div id="CabmeX">
                <img src="{{ url('images/icon/slider_pre.png')}}">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;margin-top:50px;">
                </div>
              </div>     
              <div id="CabmeXL">
                <img src="{{ url('images/icon/slider_acc.jpg')}}">
                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;margin-top:50px;">
                </div>
              </div>     
            </div>

            <div class="clear: both;">

            </div>
          </div>
</div>
</div>
</div>
</div>
	
	<div class="contianer-fluid" style="    background-color: var(--color-as-f8f8f9)!important;">
	<div class="container ash-cont">
	<p class="_style_ZJW1y" style="
    margin-bottom: 25px !important;">{{trans('messages.footer.safety')}}</p>
    <h1 class="slide-head ride-head">{{trans('messages.ride.start_finish')}}</h1><p class="ride-content slide-content">
{{trans('messages.ride.your_safety')}}</p>
<a href="{{ url('safety') }}" class="btn btn--link hard--bottom rider-signup text--uppercase primary-font--bold  borderless--left" href="#">{{trans('messages.ride.keep_safe')}}
<i class="icon icon_right-arrow push-tiny--left"></i></a>
	</div>
	</div>
	<div class="container-fluid" style="padding:0px;">
	<div class="col-lg-11 col-lg-push-1 col-sm-push-1 col-md-12 col-sm-11 col-xs-12 pattern-ride" >
	<div class="pattern" id="pattern" style="display:block !important;height: 100% !important; left: 0px; position: absolute; z-index: 10;">
	<div style="background-color:var(--color-as-a6daec); height: 100%; overflow: hidden;"><div aria-label="{{ __('messages.blade_static.decorative_pattern_b3870f86') }}" style="height: 100%;"><div class="isvg loaded" style="height: 100%;">
<img src="{{url('images/icon/patten_90_238.jpg')}}">
</div>
</div>
</div>
</div>
<div class="col-lg-9 col-md-10 col-sm-12 col-xs-11 pad-ride-red">
<p class="ride-content slide-content col-lg-6 col-md-6 col-sm-6 col-xs-12">
{{trans('messages.ride.your_first')}} {{$site_name}} {{trans('messages.ride.min_away')}}</p>
@if(Auth::user()==null)
<a class="pull-right btn btn--primary btn--arrow position--relative error-retry-btn width-sm mar-top-37" href="{{ url('signup_rider') }}">
<div class="block-context soft-small--right" style="    width: 180px;    font-size: 13px !important;">{{trans('messages.footer.siginup_ride')}}</div>
<i class="icon_right-arrow-thin icon transition delta position--absolute"></i>
</a>
@endif
</div>
	</div>
	</div>
	<div class="container-fluid con-back-div" style="background-color: var(--color-as-f1f1f1)!important;">
	<div class="col-lg-10 col-lg-push-2 col-md-12 col-sm-12 col-xs-12" style="overflow:hidden;padding:0px;">
<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12" style="padding:0px;">
<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 mar-height ride-three" style="padding-right: 20px; padding-left: 20px;">
<img src="images/icon/suitcase.png" class="cont-img">
<div class="arrive-content">
<div style="position: relative !important;">
<a style="text-decoration:none !important;font-size: 15px;">{{trans('messages.ride.business_travel')}}</a>
<p class="_style_ZJW1y" style="margin: 10px 0px 15px !important; min-height: unset;">{{trans('messages.ride.trip_seperate')}}</p>
</div><div><p class="cmln__paragraph">{{trans('messages.ride.trip_seperate_content')}} {{$site_name}} {{trans('messages.ride.hard_as')}}</p>
</div></div>
</div>
<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 mar-height ride-three" style="padding-right: 20px; padding-left: 20px;">
<img src="images/icon/two_person.png" class="cont-img">
<div class="arrive-content">
<div style="position: relative !important;">
<a style="text-decoration:none !important;font-size: 15px;">{{$site_name}} {{trans('messages.ride.pool')}}</a>
<p class="_style_ZJW1y" style="margin: 10px 0px 15px !important; min-height: unset;">{{trans('messages.ride.share_save')}}</p>
</div><div><p class="cmln__paragraph">{{$site_name}} {{trans('messages.ride.pool_content')}} {{$site_name}}. {{trans('messages.ride.ride_adds')}} </p>
</div></div>
</div>
<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 mar-height ride-three" style="padding-right: 20px; padding-left: 20px;">
<img src="images/icon/half_plane.png" class="cont-img">
<div class="arrive-content">
<div style="position: relative !important;">
<a style="text-decoration:none !important;font-size: 15px;">{{trans('messages.ride.airport')}}</a>
<p class="_style_ZJW1y" style="margin: 10px 0px 15px !important; min-height: unset;">{{trans('messages.ride.shuttle')}}</p>
</div><div><p class="cmln__paragraph">{{trans('messages.ride.shuttle_content')}}</p>
</div></div>
</div>
</div>

</div>
</div>

</main>
@stop
<style type="text/css">
/* Reset and Base Styles */
body {
  font-family: 'Roboto', -apple-system, BlinkMacSystemFont, sans-serif;
  background: var(--color-as-f5f5f5);
  color: var(--color-as-212121);
  margin: 0;
  line-height: 1.6;
}

/* Fixed Header */
.fixed-header {
  background: var(--color-white);
  box-shadow: 0 2px 4px var(--color-as-rgba-0-0-0-0-1);
  position: sticky;
  top: 0;
  z-index: 100;
  padding: 12px 0;
}

.sub-header {
  list-style: none;
  margin: 0;
  padding: 0;
  display: flex;
  justify-content: center;
  gap: 24px;
}

.sub-header li a {
  color: var(--color-as-212121);
  text-decoration: none;
  font-size: 16px;
  font-weight: 500;
  padding: 8px 16px;
  transition: color 0.3s ease, background 0.3s ease;
}

.sub-header li a:hover {
  color: var(--color-as-0288d1);
  background: var(--color-as-e3f2fd);
  border-radius: 0;
}

/* Hero Section (ride-div-main) */
.ride-div-main {
  position: relative;
  background: var(--color-as-0288d1);
  padding: 0;
  overflow: hidden;
}

.slide-img-ride {
  background: url('{{url("images/icon/hero-bg.jpg")}}') no-repeat center/cover;
  height: 520px;
  width: 100%;
  position: relative;
  z-index: 1;
  filter: brightness(0.8);
}

.pattern {
  position: absolute;
  right: 0;
  top: 0;
  width: 274px;
  height: 520px;
  z-index: 10;
  background: transparent;
}

.pattern img {
  width: 100%;
  height: 100%;
  object-fit: cover;
  opacity: 0.2;
}

/* Mini Green Signup Card */
.mini-green {
  position: absolute;
  bottom: -80px;
  right: 10%;
  z-index: 20;
}

.green-mini-div {
  background: var(--color-as-0288d1) !important;
  border-radius: 0;
  padding: 24px;
  box-shadow: 0 4px 12px var(--color-as-rgba-0-0-0-0-2);
  width: 240px;
  height: auto;
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.green-mini-div:hover {
  transform: translateY(-4px);
  box-shadow: 0 6px 16px var(--color-as-rgba-0-0-0-0-3);
}

.green-mini-div ._style_1PPmFR {
  font-size: 20px;
  font-weight: 600;
  color: var(--color-white);
  line-height: 1.4;
  margin-bottom: 16px;
}

.green-mini-div .btn--primary {
  background: transparent;
  border: none;
  color: var(--color-white);
  font-size: 16px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  margin-top: 20px;
}

.green-mini-div .btn--primary:hover {
  color: var(--color-as-e3f2fd);
}

.green-mini-div .icon_right-arrow-thin {
  font-size: 20px;
  transition: transform 0.3s ease;
}

.green-mini-div .btn--primary:hover .icon_right-arrow-thin {
  transform: translateX(4px);
}

/* Always Ride Section */
.ride-always {
  padding: 60px 0;
  background: var(--color-white);
}

.pad-44 {
  padding: 44px 20px;
}

.slide-head.ride-head {
  font-size: 36px;
  font-weight: 700;
  color: var(--color-as-212121);
  margin-bottom: 16px;
  text-align: center;
}

.slide-content {
  font-size: 18px;
  color: var(--color-as-616161);
  text-align: center;
  max-width: 800px;
  margin: 0 auto 40px;
}

.column-content {
  padding: 20px;
}

.arrive-content {
  background: var(--color-white);
  border-radius: 0;
  padding: 24px;
  box-shadow: 0 2px 8px var(--color-as-rgba-0-0-0-0-1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
  text-align: center;
}

.arrive-content:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px var(--color-as-rgba-0-0-0-0-15);
}

.arrive-content ._style_ZJW1y {
  font-size: 22px;
  font-weight: 600;
  color: var(--color-as-212121);
  margin-bottom: 16px;
}

.arrive-content .cmln__paragraph {
  font-size: 16px;
  color: var(--color-as-616161);
  line-height: 1.5;
}

/* Slider Section */
.ride-slide {
  font-size: 36px;
  font-weight: 700;
  color: var(--color-as-212121);
  margin-bottom: 16px;
  text-align: center;
}

#mySliderTabsContainer {
  background: var(--color-as-f8f8f9);
  padding: 40px 0;
}

#mySliderTabs {
  max-width: 1200px;
  margin: 0 auto;
}

#mySliderTabs ul {
  list-style: none;
  display: flex;
  justify-content: center;
  gap: 16px;
  margin-bottom: 24px;
}

#mySliderTabs ul li a {
  display: block;
  padding: 12px 24px;
  background: var(--color-white);
  border-radius: 0;
  text-decoration: none;
  color: var(--color-as-212121);
  font-size: 16px;
  font-weight: 500;
  box-shadow: 0 2px 4px var(--color-as-rgba-0-0-0-0-1);
  transition: background 0.3s ease, color 0.3s ease;
}

#mySliderTabs ul li a:hover,
#mySliderTabs ul li.ui-tabs-active a {
  background: var(--color-as-0288d1);
  color: var(--color-white);
}

#mySliderTabs img {
  width: 100%;
  max-width: 600px;
  height: auto;
  border-radius: 0;
  margin: 0 auto;
  display: block;
  box-shadow: 0 4px 12px var(--color-as-rgba-0-0-0-0-1);
}

/* Safety Section */
.ash-cont {
  padding: 60px 20px;
  text-align: center;
}

.ash-cont ._style_ZJW1y {
  font-size: 18px;
  font-weight: 500;
  color: var(--color-as-0288d1);
  margin-bottom: 16px;
}

.ash-cont .slide-head {
  font-size: 36px;
  font-weight: 700;
  color: var(--color-as-212121);
  margin-bottom: 16px;
}

.ash-cont .slide-content {
  font-size: 18px;
  color: var(--color-as-616161);
  max-width: 800px;
  margin: 0 auto 24px;
}

.ash-cont .btn--link {
  color: var(--color-as-0288d1);
  font-size: 16px;
  font-weight: 600;
  text-transform: uppercase;
  text-decoration: none;
  display: inline-flex;
  align-items: center;
  gap: 8px;
  transition: color 0.3s ease;
}

.ash-cont .btn--link:hover {
  color: var(--color-as-01579b);
}

.ash-cont .icon_right-arrow {
  font-size: 18px;
  transition: transform 0.3s ease;
}

.ash-cont .btn--link:hover .icon_right-arrow {
  transform: translateX(4px);
}

/* Pattern Ride Section */
.pattern-ride {
  position: relative;
  padding: 60px 0;
}

.pattern-ride .pattern {
  left: 0;
  width: 90px;
  height: 238px;
  z-index: 10;
}

.pattern-ride .pattern img {
  opacity: 0.3;
}

.pad-ride-red {
  display: flex;
  align-items: center;
  gap: 24px;
  padding: 40px 20px;
  background: var(--color-white);
  border-radius: 0;
  box-shadow: 0 4px 12px var(--color-as-rgba-0-0-0-0-1);
}

.pad-ride-red .slide-content {
  font-size: 18px;
  color: var(--color-as-616161);
  flex: 1;
  text-align: left;
}

.pad-ride-red .btn--primary {
  background: var(--color-as-0288d1);
  color: var(--color-white);
  border: none;
  border-radius: 0;
  padding: 12px 24px;
  font-size: 16px;
  font-weight: 500;
  display: flex;
  align-items: center;
  gap: 8px;
  transition: background 0.3s ease, transform 0.3s ease;
}

.pad-ride-red .btn--primary:hover {
  background: var(--color-as-01579b);
  transform: translateY(-2px);
}

.pad-ride-red .icon_right-arrow-thin {
  font-size: 18px;
}

/* Ride Three Section */
.con-back-div {
  padding: 60px 0;
  background: var(--color-as-f1f1f1);
}

.ride-three {
  padding: 20px;
  text-align: center;
}

.ride-three .cont-img {
  width: 80px;
  height: auto;
  margin-bottom: 16px;
}

.ride-three .arrive-content {
  background: var(--color-white);
  border-radius: 0;
  padding: 24px;
  box-shadow: 0 2px 8px var(--color-as-rgba-0-0-0-0-1);
  transition: transform 0.3s ease, box-shadow 0.3s ease;
}

.ride-three .arrive-content:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px var(--color-as-rgba-0-0-0-0-15);
}

.ride-three .arrive-content a {
  font-size: 18px;
  font-weight: 600;
  color: var(--color-as-212121);
  text-decoration: none;
}

.ride-three .arrive-content ._style_ZJW1y {
  font-size: 20px;
  font-weight: 500;
  color: var(--color-as-212121);
  margin: 10px 0 15px;
}

.ride-three .arrive-content .cmln__paragraph {
  font-size: 16px;
  color: var(--color-as-616161);
}

/* Responsive Design */
@media (max-width: 992px) {
  .ride-always,
  .con-back-div {
    padding: 40px 0;
  }

  .slide-head.ride-head,
  .ride-slide {
    font-size: 28px;
  }

  .slide-content {
    font-size: 16px;
  }

  .mini-green {
    right: 5%;
    bottom: -60px;
  }

  .green-mini-div {
    width: 200px;
    padding: 20px;
  }

  .pad-ride-red {
    flex-direction: column;
    text-align: center;
  }

  .pad-ride-red .slide-content {
    text-align: center;
  }

  .pad-ride-red .btn--primary {
    margin: 0 auto;
  }
}

@media (max-width: 768px) {
  .sub-header {
    flex-direction: column;
    gap: 12px;
    padding: 12px 0;
  }

  .slide-img-ride,
  .pattern {
    height: 400px;
  }

  .column-content,
  .ride-three {
    margin-bottom: 20px;
  }

  #mySliderTabs ul {
    flex-direction: column;
    align-items: center;
  }

  #mySliderTabs ul li a {
    width: 100%;
    text-align: center;
  }

  .pad-ride-red {
    padding: 20px;
  }
}

@media (max-width: 576px) {
  .slide-head.ride-head,
  .ride-slide {
    font-size: 24px;
  }

  .slide-content {
    font-size: 14px;
  }

  .green-mini-div {
    width: 100%;
    max-width: 180px;
    padding: 16px;
  }

  .green-mini-div ._style_1PPmFR {
    font-size: 18px;
  }

  .arrive-content {
    padding: 16px;
  }

  .arrive-content ._style_ZJW1y {
    font-size: 18px;
  }

  .arrive-content .cmln__paragraph {
    font-size: 14px;
  }
}
</style>