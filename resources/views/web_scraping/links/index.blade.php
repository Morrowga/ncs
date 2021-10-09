@extends('layouts.app')

@section('content')
<style>
    .fast-right-spinner {
        animation: breathe 2000ms infinite !important;
    }
</style>

<div class="col-12">
    <div class="d-flex">
        <h1 class="page-title mb-2 mr-auto">{{ __('Link Lists') }}</h1>

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            <p class="mb-0"><strong></strong></p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        </div>
        @endif

    </div>

    <div class="div-page my-3">
        {!! $links->links() !!}
    </div>

    <div class="row">
        <div class="col-md-12">

            <div class="card shadow">
                <div class="card-body px-0 pb-0">

                    <div class="toolbar px-2 px-md-3">
                        <form class="form" action="{{ route('links.index') }}" method="GET">
                            <div class="form-row">
                                <div class="form-group col-auto">
                                    <label for="search_website" class="sr-only">{{ __('Website') }}</label>
                                    <input type="text" name="search_website" class="form-control rounded-0"
                                        value="{{ $search_website }}" placeholder="{{ __('website') }} ..">
                                </div>
                                <div class="form-group col-auto">
                                    <button type="submit" class="btn btn-outline-primary rounded-0"><i
                                            class="fe fe-zoom-in"></i></button>
                                    <a href="{{ route('links.index') }}" class="btn btn-outline-secondary rounded-0"><i
                                            class="fe fe-rotate-cw"></i></a>
                                </div>
                                <div class="form-group col-auto ml-md-auto">
                                    <a href="{{ route('links.create') }}" class="btn btn-outline-primary rounded-0"><i
                                            class="fe fe-plus-square fe-16"></i> {{ __('Create') }}</a>
                                </div>
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="7%" class="text-center">No:</th>
                                    <th width="25%">Website</th>
                                    <th width="25%">Category</th>
                                    <th>Item Schema</th>
                                    <th>Link</th>
                                    <th width="13%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($links as $link)
                                <tr data-id="{{ $link->id }}" class="over">
                                    <td>{{ $link->id }}</td>
                                    <td>{{ $link->website->title }} </td>
                                    <td><strong><span
                                                class="label label-info">{{ $link->category->name }}</span></strong>
                                    </td>
                                    <td>
                                        <select class="js-example-basic-single selectschema item_schema"
                                            data-id="{{ $link->id }}" data-original-schema="{{$link->item_schema_id}}">
                                            <option value="" disabled selected>Select</option>
                                            @foreach($itemSchemas as $item)
                                            <option value="{{$item->id}}"
                                                {{ $item->id==$link->item_schema_id?"selected":"" }}>{{$item->title}}
                                            </option>
                                            @endforeach
                                        </select>
                                        <button type="button" class="btn btn-success btn-sm btn-apply mt-1"
                                            style="display: none">Apply</button>
                                    </td>
                                    <td>
                                        @if($link->item_schema_id != "" && $link->main_filter_selector != "")
                                        <button type="button" class="btn btn-success btn-scrape"
                                            title="pull the latest items">Scrape <i
                                                class="fas fa-circle-notch fast-right-spinner"
                                                style="display: none"></i></button>
                                        @else
                                        <span style="color: red">fill main filter selector <br> and item schema
                                            first</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="row d-flex justify-content-center">
                                            <a href="{{ route('links.edit', $link->id) }}"><button
                                                    class="btn btn-info"><i
                                                        class="fa fa-pencil mr-2"></i>Edit</button></a>
                                            <form action="{{ route('links.destroy', $link->id) }}" method="POST">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-danger ml-2"><i
                                                        class="fa fa-trash mr-2"></i>Delete</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <th colspan="5" class="text-center">No Items Schema Data ..</th>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <div class="div-page my-3">
        {!! $links->links() !!}
    </div>

</div>
@endsection
