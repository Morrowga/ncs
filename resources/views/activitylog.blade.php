@extends('layouts.app')

@section('content')

<form action="">
    <div class="form-group form-search d-flex justify-content-center">
        <input type="text" name="q" placeholder="Find" class="form-control search mr-1"/><input type="submit" class="btn btn-info btn-search" value="Search"/>
    </div>
</form>

<table class="table">
  <thead class="thead-dark">
    <tr>
      <th scope="col">ID</th>
      <th scope="col">LOG NAME</th>
      <th scope="col">DATE</th>
      <th scope="col">Time</th>
    </tr>
  </thead>
  <tbody>
    @if(!empty($logs) && $logs->count())
        @foreach($logs as $log)
        <tr>
        <td>{{ $log->id }}</td>
        <td>{{ $log->name }}</td>
        <td>{{ date('h:i A Y-m-d', strtotime($log->created_at)) }}</td>
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

<div class="pg">{!! $logs->links() !!}</div>


<style>
    .pagination{
        text-align: center !important;
        display: flex;
        justify-content: center;
    }
    .pagination > li > span:focus {
        background-color: #000;
        color: #fff;
    }

    .pagination > li > a:hover{
        color: #fff !important;
    }
    .pagination > li > a,.pagination > li, .pagination {
        color : #000 !important;
        border-color: rgb(0,0,0,0.5) !important;
    }

</style>
@endsection