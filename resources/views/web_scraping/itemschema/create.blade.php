@extends('layouts.app')

@section('content')
<div class="col-12">
    <h1 class="page-title">{{ __($title) }}</h1>

    <form action="{{ route('itemschema.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-group row">
            <label for="title" class="col-sm-3 col-form-label">{{ __('Item Schema Title') }}</label>
            <div class="col-sm-9">
                <input type="text" name="title" class="form-control" id="title"
                    placeholder="{{ __('Item Schema Title') }} ..">
            </div>
        </div>
        <div class="form-group row">
            <label for="css_expression" class="col-sm-3 col-form-label">{{ __('Item Schema css_expression') }}</label>
            <div class="col-sm-9">
                <input type="text" name="css_expression" class="form-control" id="css_expression"
                    placeholder="{{ __('Item Schema css_expression') }} ..">
            </div>
        </div>
        <div class="form-group row">
            <label for="is_full_url"
                class="col-sm-3 col-form-label">{{ __('Is Full Url To Article/Partial Url:') }}</label>
            <div class="col-sm-9">
                <input type="checkbox" class="check" id="is_full_url" name="is_full_url" value="1" checked
                    data-checkbox="icheckbox_square-red">
            </div>
        </div>
        <div class="form-group row">
            <label for="full_content_selector"
                class="col-sm-3 col-form-label">{{ __('Item Schema Full Content Selector') }}</label>
            <div class="col-sm-9">
                <input type="text" name="full_content_selector" class="form-control" id="full_content_selector"
                    placeholder="{{ __('Item Schema Full Content Selector') }} ..">
            </div>
        </div>
        <div class="row justify-content-center my-4">
            <a href="{{ route('itemschema.index') }}"
                class="col-12 col-md-3 btn btn-outline-secondary btn-lg rounded-0 mr-md-2 mb-2 mb-md-0">Cancel</a>
            <button type="submit" class="col-12 col-md-3 btn btn-outline-success btn-lg rounded-0">Create
                Content</button>
        </div>
    </form>

</div>
@endsection
