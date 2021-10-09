@extends('layouts.app')

@section('content')
<div class="col-12">
    <h1 class="page-title">{{ __($title) }}</h1>

    <form action="{{ route('raw_articles.update',$raws->id) }}" method="POST" enctype="multipart/form-data">
        {{ method_field('PUT') }}
        @csrf
        <div class="form-row">
            <div class="form-group col-md-6">
                <label for="source_link"><strong>{{ __('Source Link') }}</strong></label>
                <input type="text" name="source_link" class="form-control rounded-0" placeholder="Source Link .."
                    id="source_link" value="{{ $raws->source_link}}" required>
            </div>
            <div class="form-group col-md-5">
                <label for="website_id"><strong>{{ __('Website') }}</strong></label>
                <select name="website_id" class="form-control rounded-0 s2" id="website_id" required>
                    @foreach ($websites as $website)
                    <option value="{{$website->id}}" {{ $suggest_website == $website->id ? 'selected' : '' }}>
                        {{ $website->title}}
                    </option>
                    @endforeach
                    {{-- <option value="{{ $raws->website->id }}">{{ $raws->website->title}}</option>
                    @foreach($websites as $website)
                    <option value="{{ $website->id }}">{{ $website->title }}</option>
                    @endforeach --}}
                </select>
                <div class="form-row">
                    @if (!empty($suggest_website))
                    <p class="suggest_category">
                        @foreach ((array)$suggest_website as $website)
                        #{{$website}}
                        @endforeach
                    </p>
                    @endif
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-4">
                <label for="publishedDate"><strong>{{ __('Pulished Date') }}</strong></label>
                <input type="text" name="publishedDate" value="{{ $raws->publishedDate }}"
                    class="form-control rounded-0 date-picker" placeholder="Published Date" id="publishedDate" required>
            </div>
            <div class="form-group col-md-4 mr-1">
                <label for="category_id"><strong>{{ __('Category Name') }}</strong></label>
                <select class="category-multiple form-control rounded-0 s2" name="category_id" id="category_id"
                    required>
                    @foreach ($categories as $category)
                    <option value="{{$category->id}}" {{ $suggest_category == $category->id-1 ? 'selected' : '' }}>
                        {{ $category->name}} ({{ $category->nameMm }})
                    </option>
                    @endforeach

                    {{-- @foreach($categories as $category)
                    <option value="{{ $category->id }}">{{ $category->name }}
                    ({{ $category->nameMm }})
                    </option>
                    @endforeach --}}
                </select>
                <div class="form-row">
                    @if (!empty($suggest_category))
                    <p class="suggest_category">
                        #{{$suggest_category}}
                    </p>
                    @endif
                </div>
                <div class="form-row">
                    @if (!empty($suggest_indexing))
                    <p class="suggest_category">
                        #{{$suggest_indexing}}
                    </p>
                    @endif
                </div>
            </div>

            {{-- tags --}}
            <div class="form-group col-md-4">
                <label for="tag"><strong>{{ __('Tags') }}</strong></label>
                <select style="width:417px;" class=" form-control rounded-0 s2s" name="tag[]" multiple id="tag"
                    required>
                    @foreach($tags as $tag)
                    <option value="{{ $tag->id }}" @foreach($suggesting_tags as $sug_tag)
                        {{$sug_tag == $tag->id ? 'selected': ''}} @endforeach>
                        {{ $tag->nameMm }}
                    </option>
                    @endforeach
                    {{-- @foreach($tags as $tag)
                    <option value="{{ $tag->id }}" @foreach($raws->tags as
                    $taglist){{$taglist->pivot->tag_id == $tag->id ? 'selected': ''}} @endforeach> {{ $tag->name }}
                    </option>
                    @endforeach --}}
                </select>
            </div>
        </div>
        <div class="form-row">
            @if (!empty($suggesting_tags))
            <p class="suggest_tag">
                @foreach ((array)$suggesting_tags as $tags)
                #{{$tags}}
                @endforeach
            </p>
            @endif
        </div>
        <div class="form-row">
            @if (!empty($indexing_tags))
            <p class="suggest_tag">
                @foreach ((array)$indexing_tags as $tags)
                #{{$tags}}
                @endforeach
            </p>
            @endif
        </div>

        <div class="form-row">
            <div class="form-group col-12">
                <label for="title"><strong>{{ __('Content Title') }}</strong></label>
                <input type="text" name="title" class="form-control rounded-0" value="{{ $raws->title}}" id="title"
                    required>
                <input type="number" value="1" name="update_status" hidden>
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-12">
                <label for="title"><strong>{{ __('Feature Photo') }}</strong></label>
                <input type="text" name="image" class="form-control rounded-0" value="{{ $raws->image}}" required>
            </div>
        </div>
        <div class="row justify-content-center my-4">
            <a href="{{ url()->previous() }}"
                class="col-12 col-md-3 btn btn-outline-secondary btn-lg rounded-0 mr-md-2 mb-2 mb-md-0">Cancel</a>
            <button type="submit" class="col-12 col-md-3 btn btn-outline-success btn-lg rounded-0">Update
            </button>
        </div>
    </form>

    {{-- <div class="form-row"> --}}
    {{-- <div class="form-group col-md-6">
                <label for="show_content"><strong>{{ __('Body Content (Raw Data)') }}</strong></label>
    <textarea class="summernote rounded-0" id="show_content"></textarea>
</div> --}}

<div class="form-row">
    <div class="form-group col">
        <label for="text_area"><strong>{{ __('Create Body Content') }}</strong></label>
        <form action="{{ Route('contents.store') }}" method="POST">
            @csrf
            <div class="card">
                <div class="card-body">
                    <div class="form-group">
                        <textarea style="height:400px;" class="form-control" name="text_area"></textarea>
                        <input type="text" name="article_id" value="{{ $raws->id }}" class="form-control w-100" hidden>
                    </div>
                </div>
            </div>
            <div class="row justify-content-center mt-3">
                <button type="submit" class="col-12 col-md-3 btn btn-outline-success btn-lg rounded-0">Create
                    Content</button>
            </div>
        </form>
    </div>
</div>
</div>



</div>
@endsection
