@extends('layouts.app_login')

@section('content')
<div class="wrapper vh-100">
    <div class="row align-items-center w-100 h-100">

        <form method="POST" action="{{ route('login') }}" class="col-lg-3 col-md-4 col-10 mx-auto text-center">
            @csrf
            <a class="navbar-brand mx-auto mt-2 flex-fill text-center" href="{{ url('/') }}">
                <img src="{{ asset('web/assets/favicon_io1/img192.png') }}" alt="">
            </a>

            @if ($errors->any())
            <div class="alert alert-danger rounded-0">
                @foreach ($errors->all() as $error)
                <p class="mb-0"><strong class="mb-0">{{ __($error) }}</strong></p>
                @endforeach
            </div>
            @endif
            
            @if (session('error'))
            <div class="alert alert-danger rounded-0">
                {{ session('error') }}
            </div>
            @endif

            <div class="form-group">
                <label for="username" class="sr-only">Username</label>
                <input type="text" id="username" class="form-control form-control-lg" name="username" value="{{ old('username') }}" placeholder="Username .." autocomplete="username" autofocus="">
            </div>

            <div class="form-group">
                <label for="password" class="sr-only">Password</label>
                <input type="password" id="password" class="form-control form-control-lg" name="password" placeholder="Password ..">
            </div>

            <button class="btn btn-lg btn-primary btn-block" type="submit">Let me in</button>

            <p class="mt-5 mb-3 text-muted">© 2021{{ (date('Y') != '2021') ? "-".date('Y') : "" }}</p>

        </form>

    </div>
</div>
@endsection
