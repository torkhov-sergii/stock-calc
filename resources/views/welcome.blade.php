@extends('layouts.front')

@section('title', 'Stock')

@section('content')

    <h3>Periods</h3>

    <p>
        <a href="/stock/all/TSLA">All TSLA Periods</a>
    </p>

    <p>
        <a href="/stock/show/TSLA?from=2014-01-01&to=2014-01-10">Show custom TSLA period</a>
    </p>

    <h3>Dev</h3>

    <p>
        <a href="/period/generate">Regenerate periods</a>
    </p>

@endsection
