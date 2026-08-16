@extends('templatesign')

@section('main')
<style>
    /* Reset and Base Styles */

/* Signin Section */
.cls_signin {
  padding: 80px 0;
  background: var(--color-white);
}

.cls_signin .container {
  max-width: 1200px;
  margin: 0 auto;
  padding: 0 20px;
}

.cls_signin .title {
  font-size: 36px;
  font-weight: 700;
  color: var(--color-as-212121);
  text-align: center;
  margin-bottom: 40px;
}

/* Card Layout */
.cls_signin .col-lg-12 {
  display: flex;
  flex-wrap: wrap;
  gap: 24px;
  justify-content: center;
}

.cls_signin .col-lg-4 {
  flex: 1;
  min-width: 280px;
  max-width: 360px;
}

.cls_signintext {
  background: var(--color-white);
  border: 2px solid var(--color-as-e0e0e0); /* Solid border for Material Design */
  border-radius: 0;
  padding: 24px;
  box-shadow: 0 2px 8px var(--color-as-rgba-0-0-0-0-1); /* Subtle elevation */
  text-align: center;
  transition: transform 0.3s ease, box-shadow 0.3s ease, border-color 0.3s ease;
}

.cls_signintext:hover {
  transform: translateY(-4px);
  box-shadow: 0 4px 12px var(--color-as-rgba-0-0-0-0-15);
  border-color: var(--color-as-0288d1); /* Highlight border on hover */
}

.cls_signintext h4 {
  font-size: 24px;
  font-weight: 600;
  color: var(--color-as-212121);
  margin-bottom: 16px;
}

.cls_signintext p {
  font-size: 16px;
  color: var(--color-as-616161);
  margin-bottom: 24px;
  line-height: 1.5;
}

.cls_signintext a {
  display: inline-flex;
  align-items: center;
  gap: 8px;
  color: var(--color-as-0288d1);
  font-size: 12px;
  font-weight: 500;
  text-decoration: none;
  text-transform: uppercase;
  padding: 12px 12px;
  border: 2px solid var(--color-as-0288d1);
  border-radius: 0;
  transition: background 0.3s ease, color 0.3s ease, border-color 0.3s ease;
}

.cls_signintext a:hover {
  background: var(--color-as-0288d1);
  color: var(--color-white);
  border-color: var(--color-as-0288d1);
}

.cls_signintext a img {
  width: 20px;
  height: auto;
  transition: transform 0.3s ease;
}

.cls_signintext a:hover img {
  transform: translateX(4px);
}

/* Responsive Design */
@media (max-width: 992px) {
  .cls_signin {
    padding: 60px 0;
  }

  .cls_signin .title {
    font-size: 28px;
  }

  .cls_signin .col-lg-4 {
    flex: 100%;
    max-width: 100%;
  }
}

@media (max-width: 576px) {
  .cls_signin {
    padding: 40px 0;
  }

  .cls_signin .title {
    font-size: 24px;
  }

  .cls_signintext {
    padding: 16px;
  }

  .cls_signintext h4 {
    font-size: 20px;
  }

  .cls_signintext p {
    font-size: 14px;
  }

  .cls_signintext a {
    font-size: 14px;
    padding: 10px 20px;
  }
}
</style>
<div class="cls_signin">
  <div class="container">
      <h2 class="title">{{trans('messages.home.siginup')}}</h2>
    <div class="col-lg-12">
      
       @if(Auth::user()==null)
      <div class="col-lg-4">
        <div class="cls_signintext">
          <h4 >{{trans('messages.profile.driver')}}</h4>
          <p >{{trans('messages.profile.track_every')}}</p>
          <a href="{{ url('signup_driver') }}">{{trans('messages.profile.driver_signup')}} <img src="images/new/arrow-right.svg" alt="{{ __('messages.blade_static.arrow_39a531d9') }}">
          </a>
        </div>
      </div>
      <div class="col-lg-4">
        <div class="cls_signintext">
          <h4 >{{trans('messages.profile.rider')}}</h4>
          <p >{{trans('messages.profile.trip_history')}}</p>
          <a href="{{ url('signup_rider') }}">{{trans('messages.profile.rider_signup')}} <img src="images/new/arrow-right.svg" alt="{{ __('messages.blade_static.arrow_39a531d9') }}">
          </a>
        </div>
      </div>  
       @endif
        @if(Auth::guard('company')->user()==null)
         <div class="col-lg-4">
            <div class="cls_signintext">
              <h4 >{{trans('messages.home.company')}}</h4>
              <p >{{trans('messages.home.company_history')}}</p>
              <a href="{{ url('signup_company') }}">{{trans('messages.home.company_signup')}}<img src="images/new/arrow-right.svg" alt="{{ __('messages.blade_static.arrow_39a531d9') }}">
              </a>
            </div>
          </div>  
       @endif
    </div>
  </div>
</div>

@stop