@extends('layouts.app')

@section('content')
<div class="col-12">
    <div class="d-flex">
        <h1 class="page-title mb-2 mr-auto">{{ __('Category List') }}</h1>

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            <p class="mb-0"><strong>{{ __(session('success')) }}</strong></p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        </div>
        @endif

    </div>

    <div class="div-page my-3">
        {!! $categories->links() !!}
    </div>

    <div class="row">
        <div class="col-md-12">

            <div class="card shadow">
                <div class="card-body px-0 pb-0">

                    <div class="toolbar px-2 px-md-3">
                        <form class="form" action="{{ route('category.index') }}" method="GET">
                            <div class="form-row">
                                <div class="form-group col-auto">
                                    <label for="search" class="sr-only">{{ __('Name') }}</label>
                                    <input type="text" name="search_name" class="form-control rounded-0"
                                        value="{{ $search_name }}" placeholder="{{ __('Name') }} ..">
                                </div>
                                <div class="form-group col-auto">
                                    <label for="search" class="sr-only">{{ __('Name MM') }}</label>
                                    <input type="text" name="search_name_mm" class="form-control rounded-0"
                                        value="{{ $search_name_mm }}" placeholder="{{ __('Name MM') }} ..">
                                </div>
                                <div class="form-group col-auto">
                                    <button type="submit" class="btn btn-outline-primary rounded-0"><i
                                            class="fe fe-zoom-in"></i></button>
                                    <a href="{{ route('category.index') }}"
                                        class="btn btn-outline-secondary rounded-0"><i class="fe fe-rotate-cw"></i></a>
                                </div>
                                <div class="form-group col-auto ml-md-auto">
                                    <a href="{{ route('category.create') }}"
                                        class="btn btn-outline-primary rounded-0"><i
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
                                    <th width="25%">ID</th>
                                    <th width="25%">Name</th>
                                    <th>Name MM</th>
                                    <th width="13%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($categories as $ctg)
                                <tr>
                                    <td class="text-center">{{ __(++$i) }}</td>
                                    <td>{{ __($ctg->name) }}</td>
                                    <td class="text-capitalize">{{ __($ctg->name) }}</td>
                                    <td>{{ __($ctg->name_mm) }}</td>
                                    <td class="text-center">
                                        <a href="{{ route('category.edit', $ctg->id) }}"
                                            class="btn btn-sm btn-outline-warning rounded-circle"><i
                                                class="fe fe-edit fe-16"></i></a>
                                        <a href="#delete-modal" data-toggle="modal"
                                            class="btn btn-sm btn-outline-danger rounded-circle"
                                            data-route="{{ route('category.destroy', $ctg->id) }}"><i
                                                class="fe fe-trash-2 fe-16"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <th colspan="5" class="text-center">No Category Data ..</th>
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
        {!! $categories->links() !!}
    </div>

</div>
@endsection
