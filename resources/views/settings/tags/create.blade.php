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

                        @if (isset($tag))
                        <form action="{{ route('tag.update', $tag->id) }}" method="POST">
                            @csrf
                            @method('PATCH')
                            @else
                            <form action="{{ route('tag.store') }}" method="POST">
                                @csrf
                                @endif
                                <div class="form-group row">
                                    <label for="name" class="col-sm-3 col-form-label">{{ __('Tag Name') }}</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="name" value="{{ (isset($tag)) ? $tag->name : '' }}"
                                            class="form-control" id="name"
                                            placeholder="{{ __('Tag Name (Optional)') }} ..">
                                    </div>
                                </div>
                                <div class="form-group row">
                                    <label for="nameMm"
                                        class="col-sm-3 col-form-label">{{ __('Tag Name (Myanmar)') }}</label>
                                    <div class="col-sm-9">
                                        <input type="text" name="nameMm" value="{{ (isset($tag)) ? $tag->nameMm : '' }}"
                                            class="form-control" id="nameMm"
                                            placeholder="{{ __('Tag Name (Myanmar)') }} ..">
                                    </div>
                                </div>
                                <div class="row justify-content-center my-5">
                                    <a href="{{ route('tag.index') }}"
                                        class="col-2 mr-1 mr-md-2 btn btn-outline-secondary rounded-0"><i
                                            class="fe fe-x-arrow-left fe-16 mr-2"></i> {{ __('Cancel') }}</a>
                                    <button type="reset"
                                        class="col-2 mr-1 mr-md-2 btn btn-outline-secondary rounded-0"><i
                                            class="fe fe-x-circle fe-16 mr-2"></i> {{ __('Clear') }}</button>
                                    @if (isset($tag))
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
