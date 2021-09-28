@extends('layouts.app')

@section('content')
<div class="col-12">
    <h1 class="page-title">{{ __('Create Scraping Link') }}</h1>

    <form action="{{ route('links.store') }}" method="POST" enctype="multipart/form-data">
        @csrf
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="url"><strong>{{ __('URL') }}</strong></label>
                <input type="text" name="url" class="form-control rounded-0" placeholder="Url Link .." id="url"
                    required>
            </div>
            <div class="form-group col-md-6">
                <label for="main_filter_selector"><strong>{{ __('MAIN FILTER SELECTOR') }}</strong></label>
                <input type="text" name="main_filter_selector" class="form-control rounded-0"
                    placeholder="Main Filter Link .." id="main_filter_selector" required>
            </div>

        </div>

        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="website_id"><strong>{{ __('Website') }}</strong></label>
                <select name="website_id" class="form-control rounded-0 s2" id="website_id" required>
                    <option value="">{{ __('Choose Provider Website Name ..') }}</option>
                    @foreach($websites as $website)
                    <option value="{{ $website->id }}">{{ $website->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="form-group col-md-6">
                <label for="category_id"><strong>{{ __('Category Name') }}</strong></label>
                <select class="category-multiple form-control rounded-0 s2" name="category_id" id="category_id"
                    required>
                    <option value="" aria-readonly="">{{ __('Choose Category Name ..') }}</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }} ({{ $category->name_mm }})</option>
                    @endforeach
                </select>
            </div>

        </div>


        <div class="row justify-content-center my-4">
            <a href="{{ route('links.index') }}"
                class="col-12 col-md-3 btn btn-outline-secondary btn-lg rounded-0 mr-md-2 mb-2 mb-md-0">Cancel</a>
            <button type="submit" class="col-12 col-md-3 btn btn-outline-success btn-lg rounded-0">Create
                Content</button>
        </div>
    </form>

</div>
@endsection
