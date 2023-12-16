@extends('layouts.front')

@section('title', 'Stock')

@section('content')

    <h3>{{ $symbol }}</h3>

    <p>
        <a href="/stock/all/{{ $symbol }}">All Periods</a>
    </p>

    <p>
        <a href="/stock/show/{{ $symbol }}?from=2014-01-01&to=2014-01-10">Show custom period</a>
    </p>

    <p>
        Interday Value Change Sum: {{ $interdayValueChangeSum }}
    </p>

    <div id="companyGraph" data-symbol="{{ $symbol }}"></div>

@endsection
