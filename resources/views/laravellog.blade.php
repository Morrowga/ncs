@extends('layouts.app')

@section('content')
<form action="{{ route('laravellogs') }}">
    <div class="form-group form-search d-flex justify-content-center">
        <input type="date" name="date" class="form-control"
            value="{{ $date ? $date->format('Y-m-d') : today()->format('Y-m-d') }}"> <button
            class="btn btn-info mt-2 ml-2" type="submit">GENERATE</button>
    </div>
</form>

@if(empty($data))
<div class="col-md-12 view_font mt-3">
    <h3 class="view_font">No Logs Found</h3>
</div>
@else
<div class="col-md-12 view_font">
    <pre class="view_font">{{ $data }}</pre>
</div>
@endif

<style>
    .view_font {
        color: black !important;
    }
</style>
@endsection