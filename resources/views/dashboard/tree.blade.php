@extends('layouts.app')

@section('page-title', trans('app.dashboard'))
@section('page-heading', trans('app.dashboard'))

@section('breadcrumbs')
    <li class="breadcrumb-item active">
        @lang('app.dashboard')
    </li>
@stop

@section('content')

<div class="row">
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <a href="{{ route('profile') }}" class="text-center no-decoration">
                    <div class="icon my-3">
                        <i class="fas fa-user fa-2x"></i>
                    </div>
                    <p class="lead mb-0">@lang('app.update_profile')</p>
                </a>
            </div>
        </div>
    </div>

    @if (config('session.driver') == 'database')
        <div class="col-md-3">
            <div class="card">
                <div class="card-body">
                    <a href="{{ route('profile.sessions') }}" class="text-center  no-decoration">
                        <div class="icon my-3">
                            <i class="fa fa-list fa-2x"></i>
                        </div>
                        <p class="lead mb-0">@lang('app.my_sessions')</p>
                    </a>
                </div>
            </div>
        </div>
    @endif

    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <a href="{{ route('profile.activity') }}" class="text-center no-decoration">
                    <div class="icon my-3">
                        <i class="fas fa-server fa-2x"></i>
                    </div>
                    <p class="lead mb-0">@lang('app.activity_log')</p>
                </a>
            </div>
        </div>

    </div>
    <div class="col-md-3">
        <div class="card">
            <div class="card-body">
                <a href="{{ route('auth.logout') }}" class="text-center no-decoration">
                    <div class="icon my-3">
                        <i class="fas fa-sign-out-alt fa-2x"></i>
                    </div>
                    <p class="lead mb-0">@lang('app.logout')</p>
                </a>
            </div>
        </div>

    </div>
</div>

<div class="row">
    <div class="col-md-12">
        <div class="card">
            <div class="panel-heading"></div>
            <div class="card-body">
                <h5 class="card-title">
                   Referral Tree
                </h5>

                <div style="overflow:scroll" class="pt-4 px-3">
                    {{-- tree content --}}
    <div class="col-md-12">
        <div class="form-group">

            <label class="label-control" for="">Your referral url:</label>
            <input id="refId" type="text" value="{{url('/register?ref=')}}{{Auth::user()->referral_id?:''}}" class="form-control">
        </div>
    </div>
    <link rel="stylesheet" href="/assets/css/treant.css">
    <link rel="stylesheet" href="/assets/css/custom.css">

    <div class="chart" id="tree"></div>


                    {{-- /tree content --}}
                </div>
            </div>
        </div>
    </div>
</div>

@stop

@section('scripts')

    <script src="/assets/js/raphael.js"></script>
    <script src="/assets/js/treant.js"></script>

    <script src="/assets/js/custom.js"></script>
        <script>
        // new Treant( chart_config );
    </script>
@stop