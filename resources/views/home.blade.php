@extends('layouts.front')

@section('title', 'Stock')

@section('content')

    <h3>Companies</h3>

    @foreach($companies as $company)
        <a href="/company/{{ $company->symbol }}">{{ $company->symbol }}</a>
    @endforeach

    <h3>Import</h3>

    <p>
        <a href="/import/import_by_symbol?symbol=AAPL&output=compact">Import by symbol [output: compact,full]</a>
    </p>

    <p>
        <a href="/import/import_from_companies">Import companies</a>
    </p>

    <p>
        <a href="/import/update_from_companies">Update companies (last 100 days)</a>
    </p>

    <p>
        <a href="/import/import_companies_from_xls">Reimport companies from xls</a>
    </p>

    <h3>Dev</h3>

    <p>
        <a href="/period/generate">Regenerate periods</a>
    </p>
@endsection
