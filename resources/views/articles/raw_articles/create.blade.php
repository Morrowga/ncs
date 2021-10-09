@extends('layouts.app')

@section('content')
<div class="col-12">
    <h1 class="page-title">{{ __($title) }}</h1>

    <form action="{{ route('raw_articles.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="source_link"><strong>{{ __('Source Link') }}</strong></label>
                <input type="text" name="source_link" class="form-control rounded-0" placeholder="Source Link .."
                    id="source_link" required>
            </div>
            <div class="form-group col-md-4">
                <label for="website_id"><strong>{{ __('Website') }}</strong></label>
                <select name="website_id" class="form-control rounded-0 s2" id="website_id" required>
                    <option value="">{{ __('Choose Provider Website Name ..') }}</option>
                    @foreach($websites as $website)
                    <option value="{{ $website->id }}">{{ $website->title }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="publishedDate"><strong>{{ __('Pulished Date') }}</strong></label>
                <input type="text" name="publishedDate" value="{{ date('Y-m-d H:i:s') }}"
                    class="form-control rounded-0 date-picker" placeholder="Published Date" id="publishedDate" required>
            </div>
            <div class="form-group col-md-4 ml-1">
                <label for="category_id"><strong>{{ __('Category Name') }}</strong></label>
                <select class="category-multiple form-control rounded-0 s2" name="category_id" id="category_id"
                    required>
                    <option value="" aria-readonly="">{{ __('Choose Category Name ..') }}</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->nameMm }})</option>
                    @endforeach
                </select>
            </div>
            <div class="form-group col-md-4">
                <label for="tag"><strong>{{ __('Tags') }}</strong></label>
                <select class="form-control rounded-0 s2s" name="tag[]" multiple="multiple" id="tag" required>
                    @foreach($tags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-12">
                <label for="title"><strong>{{ __('Content Title') }}</strong></label>
                <input type="text" name="title" class="form-control rounded-0" placeholder="Content Title .." id="title"
                    required>
                <input type="number" value="1" name="update_status" hidden>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-12">
                <label for="title"><strong>{{ __('Feature Photo') }}</strong></label>
                <input type="text" name="image" class="form-control rounded-0" placeholder="Feature Photo .." required>
            </div>
        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="show_content"><strong>{{ __('Body Content (Raw Data)') }}</strong></label>
                <textarea class="summernote rounded-0" id="show_content"></textarea>
            </div>
            <div class="form-group col-md-6">
                <label for="text_area"><strong>{{ __('Body Content') }}</strong></label>
                <textarea style="height:400px;" class="form-control rounded-0" name="text_area" id="text_area"
                    required></textarea>
            </div>
        </div>

        <div class="row justify-content-center my-4">
            <a href="{{ route('raw_articles.index') }}"
                class="col-12 col-md-3 btn btn-outline-secondary btn-lg rounded-0 mr-md-2 mb-2 mb-md-0">Cancel</a>
            <button type="submit" class="col-12 col-md-3 btn btn-outline-success btn-lg rounded-0">Create
                Content</button>
        </div>
    </form>

</div>
@endsection
