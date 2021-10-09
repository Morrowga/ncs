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
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcRUalja_K0J5ssmnO_cde83NMbgLuQT9dAXsg&usqp=CAU"
                class="mb-3">
            <hr>
            @endif
            @if (!empty($sensitive))
            <h3>
                Sensitive Keywords Found
                <span class="badge badge-pill badge-danger increase-size  pt-1">{{ count($blacklist) }}</span>
            </h3>
            <p class="blacklist">
                @foreach ($sensitive as $key=>$value)
                #{{$key}}({{$value}})
                @endforeach
            </p>
            <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcQOMWY9p7zi6SPW4Sc-G04uoLqSabkH08U-_A&usqp=CAU"
                class="mb-5">
            <hr>
            @endif

            <div class="row">
                {{-- <a href=""><i class="fe fe-chevron-left"></i></a> --}}
                <h1 class="page-title">{{ __($title) }}</h1>
            </div>
            <hr>
            <h3>{{ $raws->title}}</h3>

            <div class="card mt-3" style="border-radius: 10px;">
                <div class="row ml-3 mt-3">
                    <p>{{ $raws->website->host}} | <i class="fe fe-calendar ml-1" aria-hidden="true"></i>
                        {{$raws->publishedDate}} | {{ $raws->category->name }}</p>
                </div>

                <div class="row ml-3">
                    @foreach ( $raws->tags as $tag)
                    <a type="button" class="btn btn-light" style="border:1px solid #ddd; border-radius:20px;
                    " href="">#{{ $tag->nameMm}}</a>
                    @endforeach
                </div>

                <div class="row my-3">
                    <a href="{{ $raws->source_link}}" style="margin-left:140px;"
                        class="btn btn-secondary mt-1 mb-2">ပင်မအကြောင်းအရာ</a>
                </div>
            </div>


            <div class="row mt-3">
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


        </div>
    </div>
</div>
<div class="col-2 mt-5 button">
    <div class="row">
        <a href={{ url()->previous() }} class="btn btn-outline-secondary cancel rounded-0 mt-3"
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
