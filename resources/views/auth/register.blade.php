@extends('layouts.auth')

@section('page-title', trans('app.sign_up'))

@if (settings('registration.captcha.enabled'))
    <script src='https://www.google.com/recaptcha/api.js'></script>
@endif

@section('content')

    <div class="col-md-10 col-lg-10 col-xl-10 mx-auto my-10p">
        <div class="text-center">
            <img src="{{ url('assets/img/vanguard-logo.png') }}" alt="{{ settings('app_name') }}" height="50">
        </div>

        @include('partials/messages')

        <div class="card mt-5">
            <div class="card-body">
                <h5 class="card-title text-center mt-4 text-uppercase">
                    @lang('app.register')
                </h5>

                <div class="p-4">
                    {{-- @include('auth.social.buttons') --}}

                    <style>
                    </style>
                    <form role="form" action="<?= url('register') ?>" method="post" id="registration-form" autocomplete="off" class="mt-3 row">
                        <input type="hidden" value="<?= csrf_token() ?>" name="_token">
                        <div class="form-group col-md-12">
                            <input type="text" name="fname" id="fname" class="form-control" placeholder="First Name" value="{{ old('fname') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <input type="text" name="lname" id="lname" class="form-control" placeholder="Last Name" value="{{ old('lname') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <input type="text" name="sponsor_id" id="sponsor_id" class="form-control" placeholder="Sponsor ID" value="{{ old('sponsor_id') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <input type="email" name="email" id="email" class="form-control" placeholder="@lang('app.email')" value="{{ old('email') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <input type="text" name="phone" id="phone" class="form-control" placeholder="Phone" value="{{ old('phone') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <input type="text" name="birth" id="birth" class="form-control" placeholder="Date of Birth (dd/mm/yyy)" value="{{ old('birth') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <input type="text" name="company" id="company" class="form-control" placeholder="Company (Optional)" value="{{ old('company') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <select name="country" id="country" class="form-control">
                                <option value="USA">United States</option>
                            </select>
                        </div>
                        <div class="form-group col-md-12">
                            <input type="text" name="username" id="username" class="form-control" placeholder="@lang('app.username')"  value="{{ old('username') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <input type="text" name="street_address" id="street_address" class="form-control" placeholder="Street Address"  value="{{ old('street_address') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <input type="text" name="apartment" id="apartment" class="form-control" placeholder="Apartment, suite, unit, etc. (optional)"  value="{{ old('apartment') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <input type="text" name="city" id="city" class="form-control" placeholder="City"  value="{{ old('city') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <input type="text" name="zip" id="zip" class="form-control" placeholder="Zip Code"  value="{{ old('zip') }}">
                        </div>
                        <div class="form-group col-md-12">
                            <input type="password" name="password" id="password" class="form-control" placeholder="@lang('app.password')">
                        </div>
                         <div class="form-group col-md-12">
                            <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" placeholder="@lang('app.confirm_password')">
                        </div>

                        @if (settings('tos'))
                            <div class="custom-control custom-checkbox" style="margin-left:15px">
                                <input type="checkbox" class="custom-control-input" name="tos" id="tos" value="1"/>
                                <label class="custom-control-label font-weight-normal" for="tos">
                                    @lang('app.i_accept')
                                    <a href="#tos-modal" data-toggle="modal">@lang('app.terms_of_service')</a>
                                </label>
                            </div>
                        @endif

                        {{-- Only display captcha if it is enabled --}}
                        @if (settings('registration.captcha.enabled'))
                            <div class="form-group col-md-12 my-4">
                                {!! app('captcha')->display() !!}
                            </div>
                        @endif
                        {{-- end captcha --}}

                        <div class="form-group col-md-12 mt-4">
                            <button type="submit" class="btn btn-primary btn-lg btn-block" id="btn-login">
                                @lang('app.register')
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <div class="text-center text-muted">
            @if (settings('reg_enabled'))
                @lang('app.already_have_an_account')
                <a class="font-weight-bold" href="<?= url("login") ?>">@lang('app.login')</a>
            @endif
        </div>

    </div>

    @if (settings('tos'))
        <div class="modal fade" id="tos-modal" tabindex="-1" role="dialog" aria-labelledby="tos-label">
            <div class="modal-dialog" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="tos-label">@lang('app.terms_of_service')</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body">
                        <h4>1. Terms</h4>

                        <p>
                         I understand that I am becoming a member of a reward based private Crowd-funding club.
                        </p>

                        <p>
                         The reward is driving a new car of my choice every 2 years depending on the qualification criteria.
                        </p>
                        <p>
                            I also understand that my monthly donation (Subscription) will form a part of a pool that will be used to assist others to get their reward and the monthly donations of my group members will also be used to assist me to receive the same reward.
                        </p>

                        <p>
                        I understand that there is a 14 day cooling off period from the date of my joining in which I can apply for a full refund.
                        </p>
                        <p>
                         I also understand that after 14 days no refunds will be given.
                        </p>
                        <p>
                        I understand that my monthly subscription will be taken from the same card used for signing up on the same day each month unless I request to have it changed via registered email or phone.
                        </p>

                        {{-- <h4>2. Use License</h4> --}}

                        {{-- <ol type="a">
                            <li>
                                Aenean vehicula erat eu nisi scelerisque, a mattis purus blandit. Curabitur congue
                                ollis nisl malesuada egestas. Lorem ipsum dolor sit amet, consectetur adipiscing elit:
                            </li>
                        </ol>

                        <p>...</p> --}}

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">@lang('app.close')</button>
                    </div>
                </div>
            </div>
        </div>
    @endif

@stop

@section('scripts')
    {!! JsValidator::formRequest('Vanguard\Http\Requests\Auth\RegisterRequest', '#registration-form') !!}
@stop