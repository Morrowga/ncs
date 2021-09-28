@extends('layouts.app')

@section('content')
<div class="col-12">
    <h1 class="page-title">{{ __($title) }}</h1>

    <div class="row">
        <div class="col-md-12">

            <div class="card shadow">
                <div class="card-body">
                    <div class="container">
                        @if ($errors->any())
                        <div class="alert alert-danger alert-dismissible fade show rounded-0">
                            @foreach ($errors->all() as $error)
                            <p class="mb-0">{{ __($error) }}</p>
                            @endforeach
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                                    aria-hidden="true"></span>&times;</button>
                        </div>
                        @endif

                        @if (isset($website))
                        <form action="{{ route('website.update', $website->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            @else
                            <form action="{{ route('website.store') }}" method="POST">
                                @csrf
                                @endif
                                <div class="form-group row">
                                    <label for="name" class="col-sm-3 col-form-label">{{ __('Website Name') }}</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="name"
                                            value="{{ (isset($website)) ? $website->name : '' }}" class="form-control"
                                            id="name" placeholder="{{ __('Website Name') }} ..">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="url" class="col-sm-3 col-form-label">{{ __('Website Url') }}</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="url"
                                            value="{{ (isset($website)) ? $website->url : '' }}" class="form-control"
                                            id="url" placeholder="{{ __('Website Url') }} ..">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="host" class="col-sm-3 col-form-label">{{ __('Host Name') }}</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="host"
                                            value="{{ (isset($website)) ? $website->host : '' }}" class="form-control"
                                            id="host" placeholder="{{ __('Host Name') }} ..">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="provider_category"
                                        class="col-sm-3 col-form-label">{{ __('Provider Category Name') }}</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="provider_category"
                                            value="{{ (isset($website)) ? $website->provider_category : '' }}"
                                            class="form-control" id="provider_category"
                                            placeholder="{{ __('Provider Category Name') }} ..">
                                    </div>
                                </div>
                                <div class="row justify-content-center my-5">
                                    <a href="{{ route('website.index') }}"
                                        class="col-2 mr-1 mr-md-2 btn btn-outline-secondary rounded-0"><i
                                            class="fe fe-x-arrow-left fe-16 mr-2"></i> {{ __('Cancel') }}</a>
                                    <button type="reset"
                                        class="col-2 mr-1 mr-md-2 btn btn-outline-secondary rounded-0"><i
                                            class="fe fe-x-circle fe-16 mr-2"></i> {{ __('Clear') }}</button>
                                    @if (isset($website))
                                    <button type="submit" class="col-2 btn btn-outline-success rounded-0"><i
                                            class="fe fe-check fe-16 mr-2"></i> {{ __('Edit') }}</button>
                                    @else
                                    <button type="submit" class="col-2 btn btn-outline-success rounded-0"><i
                                            class="fe fe-check fe-16 mr-2"></i> {{ __('Create') }}</button>
                                    @endif
                                </div>
                            </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>
@endsection
