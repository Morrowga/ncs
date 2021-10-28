@extends('layouts.app')

@section('content')
<div class="col-12">
    <h1 class="page-title">{{ __('Edit Scraping Link') }}</h1>

    <form action="{{ route('links.update',$link->id) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PATCH')
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="url"><strong>{{ __('URL') }}</strong></label>
                <input type="text" name="url" class="form-control rounded-0" placeholder="Url Link .." id="url"
                    value="{{$link->url}}" required>
            </div>
            <div class="form-group col-md-6">
                <label for="main_filter_selector"><strong>{{ __('MAIN FILTER SELECTOR') }}</strong></label>
                <input type="text" name="main_filter_selector" class="form-control rounded-0"
                    placeholder="Main Filter Link .." id="main_filter_selector" value="{{$link->main_filter_selector}}"
                    required>
            </div>

        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="website_id"><strong>{{ __('Website') }}</strong></label>
                <select name="website_id" class="form-control rounded-0 s2" id="website_id" required>
                    <option value="{{ $link->website_id}}">{{ $link->website->title }}</option>
                    @foreach($websites as $website)
                    <option value="{{ $website->id }}">{{ $website->title }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-6">
                <label for="category_id"><strong>{{ __('Category Name') }}</strong></label>
                <select class="category-multiple form-control rounded-0 s2" name="category_id" id="category_id"
                    required>
                    <option value="{{ $link->category_id}}">{{ $link->category->name }}</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->nameMm }})</option>
                    @endforeach
                </select>
            </div>

        </div>


        <div class="row justify-content-center my-4">
            <a href="{{ route('links.index') }}"
                class="col-12 col-md-3 btn btn-outline-secondary btn-lg rounded-0 mr-md-2 mb-2 mb-md-0">Cancel</a>
            <button type="submit" class="col-12 col-md-3 btn btn-outline-success btn-lg rounded-0">Edit
                Link</button>
        </div>
    </form>

</div>
@endsection
