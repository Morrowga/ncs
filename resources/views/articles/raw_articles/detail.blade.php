@extends('layouts.app')

@section('content')
<div class="col-10">
    <div class="row">
        <div class="col-2"></div>
        <div class="col-md-8 article">
            @if (!empty($blacklist))
            <h3>
                Blacklist Keywords Found
                <span class="badge badge-pill badge-danger increase-size  pt-1">{{ count($blacklist) }}</span>
            </h3>
            <p class="blacklist">
                @foreach ($blacklist as $key=>$value)
                #{{$key}}({{$value}})
                @endforeach
            </p>
            @endif

            <div class="row">
                {{-- <a href=""><i class="fe fe-chevron-left"></i></a> --}}
                <h1 class="page-title">{{ __($title) }}</h1>
            </div>

            <hr>

            <h3>{{ $raws->title}}</h3>
            <p>{{ $raws->website->host}} | <i class="fe fe-calendar m-1" aria-hidden="true"></i>
                {{$raws->published_date}}</p>
            <br>
            <div class="row">
                @if (!empty($raws->image_thumbnail))
                <div class="col-md-8">
                    <img src="{{ $raws->image_thumbnail}}" alt="" width="100%" height="320" style="margin-left: 100px;">
                </div>
                @else
                <div class="col-md-8">
                    <img src="{{ $raws->image}}" alt="" width="100%" height="320" style="margin-left: 100px;">
                </div>
                @endif
            </div>
            <br>
            @foreach ($contents as $content)

            @if ($content->content_text)
            <p>{{ $content->content_text }}</p>
            @endif

            @if ($content->content_image)
            <div class="text-center">
                <img src="{{ $content->content_image }}" alt="" class="img-thumbnail" width="50%" height="150">
            </div>
            @endif
            @if($content->content_link)
            <p>{{ $content->content_link }}</p>

            @endif
            @endforeach

            @foreach ( $raws->tags as $tag)
            <a type="button" class="btn btn-light" style="border:1px solid #ddd; border-radius:20px;"
                href="{{ $tag->id}}">#{{ $tag->name}}</a>
            @endforeach

        </div>
    </div>
</div>
<div class="col-2 mt-5 button">
    <div class="row">
        <a href={{ route("raw_articles.index")}} class="btn btn-outline-secondary cancel rounded-0 mt-3"
            style="width:120px; height:40px;">Back</a>
    </div>
    <div class="row">
        <a href="{{ route("raw_articles.edit",$raws->id)}}" class="btn btn-outline-warning edit rounded-0 mt-3 pt-2"
            style="width:120px; height:40px;">Edit</a>
    </div>
    <hr class="mr-5">
    <div class="row">
        <form action="{{ route('raw_articles.sent_lotaya',$raws->id)}}" method="POST">
            @method('PUT')
            @csrf
            <button class="btn btn-outline-primary send rounded-0 mt-3" style="width:120px; height:40px;" type="submit"
                onclick="return alert('Are you sure to send for lotaya?');">Send</button>
        </form>
    </div>

    <div class="row">
        <form action="{{ route('raw_articles.duplicate',$raws->id)}}" method="POST">
            @method('PUT')
            @csrf
            <button class="btn btn-outline-warning duplicate rounded-0 mt-3" style="width:120px; height:40px;"
                type="submit" onclick="return alert('Are you sure to duplicate article?');">Duplicate</button>
        </form>
    </div>
    <div class="row">
        <form action="{{ route('raw_articles.blacklist',$raws->id)}}" method="POST">
            @method('PUT')
            @csrf
            <button class="btn btn-outline-danger blacklist rounded-0 mt-3" style="width:120px; height:40px;"
                type="submit" onclick="return alert('Are you sure to blacklist article?');">Blacklist</button>
        </form>
    </div>

</div>
</div>
@endsection
