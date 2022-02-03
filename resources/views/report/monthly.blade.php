@extends('layouts.app')

@section('content')
<div class="col-12">
    <div class="d-flex">
        <h1 class="page-title mb-2 mr-auto">{{ __('Monthly Report List') }}</h1>

    </div>

    <div class="row">
        <div class="col-md-12">
            <div>
                <a href="{{ route('monthly.export')}}" class="btn btn-success">Export Excel</a>
            </div>
            <br>
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0">
                    <thead class="thead-dark">
                        <tr>
                            <th width="5%" class="text-center">UUID</th>
                            <th width="5%" class="text-center">Host</th>
                            <th width="25%%" class="text-center">Title</th>
                            <th width="9%">Myanmar Time</th>
                            <th width="9%" class="text-center">Myanmar Date</th>

                            <th width="9%" class="text-center">Original Category</th>
                            <th width="8%" class="text-center">Lotaya Category</th>
                            {{-- <th width="4%" class="text-center">Reject</th>
                            <th width="5%" class="text-center">Reject Reason</th> --}}
                            <th width="9%" class="text-center">Excel Category</th>

                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($monthly_report as $sent)
                        <tr>
                            <td>{{ $sent->uuid }}</td>
                            <td>{{ $sent->website->host }}</td>
                            <td>{{ Str::limit($sent->title, 80, '...')}}</td>
                            <td>{{ $sent->publishedDate}}</td>
                            <td>{{ $sent->publishedDate}}</td>
                            <td>{{ $sent->website->providerCategory}}</td>
                            <td>{{ $sent->category->name }}</td>
                            <td>
                                <form action="/excel_category" class="form-inline">
                                    @csrf
                                    <div class="form-group mx-sm-3 mb-2">
                                      <label for="excel_pro_category" class="sr-only">Provider Category</label>
                                      <input type="text" class="form-control" id="excel_pro_category" placeholder="{{$sent->excel_pro_category}}">
                                    </div>
                                    <button type="submit" class="btn btn-primary mb-2">Create</button>
                                </form>
                            </td>
                            {{-- <td></td> --}}
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
    {{ $monthly_report->links()}}
</div>

</div>

@endsection
