@extends('layouts.app')

@section('content')
<div class="col-12">
    <div class="d-flex">
        <h1 class="page-title mb-2 mr-auto">{{ __($title) }}</h1>

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            <p class="mb-0"><strong>{{ __(session('success')) }}</strong></p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        </div>
        @endif

    </div>

    <div class="div-page my-3">
        {!! $websites->links() !!}
    </div>

    <div class="row">
        <div class="col-md-12">

            <div class="card shadow">
                <div class="card-body px-0 pb-0">

                    <div class="toolbar px-2 px-md-3">
                        <form class="form" action="{{ route('website.index') }}" method="GET">
                            <div class="form-row">
                                <div class="form-group col-auto">
                                    <label for="search" class="sr-only">{{ __('Name') }}</label>
                                    <input type="text" name="search_name" class="form-control rounded-0"
                                        value="{{ $search_name }}" placeholder="{{ __('Name') }} ..">
                                </div>
                                <div class="form-group col-auto">
                                    <button type="submit" class="btn btn-outline-primary rounded-0"><i
                                            class="fe fe-zoom-in"></i></button>
                                    <a href="{{ route('website.index') }}"
                                        class="btn btn-outline-secondary rounded-0"><i class="fe fe-rotate-cw"></i></a>
                                </div>
                                <div class="form-group col-auto ml-md-auto">
                                    <a href="{{ route('website.create') }}" class="btn btn-outline-primary rounded-0"><i
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
                                    <th width="25%">Host Name</th>
                                    <th>Name</th>
                                    <th>Category</th>
                                    <th width="13%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($websites as $ws)
                                <tr>
                                    <td class="text-center">{{ __(++$i) }}</td>
                                    <td>{{ __($ws->host) }}</td>
                                    <td class="text-capitalize">{{ $ws->name ? __($ws->name) : '-' }}</td>
                                    <td class="text-capitalize">{{ __($ws->provider_category) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('website.edit', $ws->id) }}"
                                            class="btn btn-sm btn-outline-warning rounded-circle"><i
                                                class="fe fe-edit fe-16"></i></a>
                                        <a href="#delete-modal" data-toggle="modal"
                                            class="btn btn-sm btn-outline-danger rounded-circle"
                                            data-route="{{ route('website.destroy', $ws->id) }}"><i
                                                class="fe fe-trash-2 fe-16"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <th colspan="5" class="text-center">No Provider Data ..</th>
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
        {!! $websites->links() !!}
    </div>

</div>
@endsection
