@extends('layouts.app')

@section('content')
<div class="col-12">
    <h1 class="page-title">LOTAYA DASHBOARD</h1>
    <hr>
    <div class="row mt-2">
        <div class="col-6">
            <h2>Pending Articles</h2>
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th width="30%">Title</th>
                        <th width="10%">Website</th>
                        <th width="9%" class="text-center">Time Ago</th>
                        <th width="3%">status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($raw_articles as $raw)
                    <tr>
                        <td>
                            <a href=" {{route('raw_articles.show',$raw->id)}}">

                                {{ Str::limit($raw->title, 80, '...')}}
                            </a>
                        </td>
                        <td>{{ $raw->website->title}}</td>
                        <td>{{ $raw->created_at->diffForHumans() }}</td>
                        @if ($raw->sent_status == '0')
                        <td><i class="fe fe-x-circle fe-16 ml-3" style="color:red"></i></td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <th colspan="5" class="text-center">No Raw Data ..</th>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="col-6">
            <h2>Articles Send To Lotaya</h2>
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th width="30%">Title</th>
                        <th width="10%">Website</th>
                        <th width="9%" class="text-center">Time Ago</th>
                        <th width="3%">status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sent_articles as $raw)
                    <tr>
                        <td>
                            <a href=" {{route('raw_articles.show',$raw->id)}}">

                                {{ Str::limit($raw->title, 80, '...')}}
                            </a>
                        </td>
                        <td>{{ $raw->website->title}}</td>
                        <td>{{ $raw->created_at->diffForHumans() }}</td>
                        @if ($raw->sent_status == '1')
                        <td><i class="fe fe-check-circle fe-16 ml-3" style="color:green"></i></td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <th colspan="5" class="text-center">No Raw Data ..</th>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="row mt-5">
        <div class="col-12">
            <h2>Activity Log</h2>
            <table class="table table-bordered table-hover mb-0">
                <thead class="thead-dark">
                    <tr>
                        <th scope="col" class="text-md-center">ID</th>
                        <th scope="col">LOG NAME</th>
                        <th scope="col">DATE</th>
                        <th scope="col">Time</th>
                    </tr>
                </thead>
                <tbody>
                    @if(!empty($logs) && $logs->count())
                    @foreach($logs as $log)
                    <tr>
                        <td class="text-md-center">{{ $log->id }}</td>
                        <td>{{ $log->name }}</td>
                        <td>{{ date('h:i A d-M-Y', strtotime($log->created_at)) }}</td>
                        <td>{{ $log->created_at->diffForHumans() }}</td>
                    </tr>
                    @endforeach
                    @else
                    <tr>
                        <td colspan="10">There are no data.</td>
                    </tr>
                    @endif
                </tbody>
            </table>
        </div>

    </div>
</div>
@endsection
