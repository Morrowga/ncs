@extends('layouts.app')

@section('content')
<div class="col-12">
    <div class="d-flex">
        <h1 class="page-title mb-2 mr-auto">{{ __('Lotaya Article List') }}</h1>

        @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show mb-0" role="alert">
            <p class="mb-0"><strong>{{ __(session('success')) }}</strong></p>
            <button type="button" class="close" data-dismiss="alert" aria-label="Close"><span
                    aria-hidden="true">&times;</span></button>
        </div>
        @endif

    </div>

    <div class="row">
        <div class="col-md-12">

            <div class="card shadow">
                <div class="card-body px-0 pb-0">

                    <div class="toolbar px-2 px-md-3">
                        <form class="form" action="{{ route('sent_articles.index') }}" method="GET">
                            <div class="form-row">
                                <div class="form-group col-6 col-md-2">
                                    <label for="search_type" class="sr-only">{{ __('Type') }}</label>
                                    <select name="search_type" class="form-control rounded-0" id="search_type" required>
                                        <option value="">Choose Type to Search ..</option>
                                        <option value="1">ID</option>
                                        <option value="2">UUID</option>
                                        <option value="3">Title</option>
                                        <option value="4">published Date</option>
                                    </select>
                                </div>
                                <div class="form-group col-6 col-md-3">
                                    <input type="text" name="search_data" value="{{ '' }}"
                                        class="form-control rounded-0" placeholder="Type What You Search .." required>
                                </div>
                                <div class="form-group col-6 col-md-2">
                                    <button type="submit" class="btn btn-outline-primary rounded-0"><i
                                            class="fe fe-zoom-in"></i></button>
                                    <a href="{{ route('sent_articles.index') }}"
                                        class="btn btn-outline-secondary rounded-0"><i class="fe fe-rotate-cw"></i></a>
                                </div>
                                {{-- <div class="form-group col-auto ml-md-auto">
                                    <a href="{{ route('sent_articles.create') }}"
                                        class="btn btn-outline-primary rounded-0"><i
                                            class="fe fe-plus-square fe-16"></i>
                                        {{ __('Create') }}</a>
                                </div> --}}
                            </div>
                        </form>
                    </div>

                    <div class="table-responsive">
                        <table class="table table-bordered table-hover mb-0">
                            <thead class="thead-dark">
                                <tr>
                                    <th width="5%" class="text-center">ID</th>
                                    <th width="20%" class="text-center">UUID</th>
                                    <th width="30%">Title</th>
                                    <th width="12%" class="text-center">Published DATE</th>
                                    <th width="3%">status</th>
                                    <th width="10%" class="text-center">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($sent_articles as $sent)
                                <tr>
                                    <td>{{ $sent->id }}</td>
                                    <td>{{ $sent->uuid }}</td>
                                    <td>{{ Str::limit($sent->title, 80, '...')}}</td>
                                    <td>{{ $sent->publishedDate}}</td>
                                    @if ($sent->sent_status == '1')
                                    <td><i class="fe fe-check-circle fe-16 ml-3" style="color:green"></i></td>
                                    @endif
                                    <td>
                                        <a href="{{route('raw_articles.show',$sent->id)}}"
                                            class="btn btn-sm btn-outline-primary rounded-circle pd-2">
                                            <i class="fe fe-eye fe-16"></i></a>
                                        <a href="{{ route('raw_articles.edit',$sent->id)}}"
                                            class="btn btn-sm btn-outline-warning rounded-circle"><i
                                                class="fe fe-edit fe-16"></i>
                                        </a>
                                        <a href="#delete-modal" data-toggle="modal"
                                            class="btn btn-sm btn-outline-danger rounded-circle"
                                            data-route="{{ route('raw_articles.destroy', $sent->id) }}"><i
                                                class="fe fe-trash-2 fe-16"></i></a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <th colspan="5" class="text-center">No Sent Data ..</th>
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
        {{ $sent_articles->links()}}
    </div>

</div>

@endsection
