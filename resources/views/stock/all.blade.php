@extends('layouts.front')

@section('title', 'Stock')

@section('content')

    <h3>Calc {{ $symbol }} by periods</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Period From</th>
            <th>Period To</th>
            <th>Stock price From</th>
            <th>Stock price To</th>
{{--            <th>Initial amount</th>--}}
            <th>Hold amount</th>
            <th>Result</th>
        </tr>

        @foreach($periodResults as $period)
            <tr>
                <td><a href="/stock/show/TSLA?period={{ $period['id'] }}">{{ $period['id'] }}</a></td>
                <td>{{ $period['from'] }}</td>
                <td>{{ $period['to'] }}</td>
                <td>{{ $period['stockPriceFrom'] }}</td>
                <td>{{ $period['stockPriceTo'] }}</td>
{{--                <td>{{ $period['initialAmount'] }}</td>--}}
                <td>{{ ceil($period['holdAmount']) }}</td>
                <td>{{ ceil($period['finalAmount']) }}</td>
            </tr>
        @endforeach

        <tr>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th></th>
            <th>{{ ceil($averageHoldAmount) }}</th>
            <th>{{ ceil($averageFinalAmount) }}</th>
        </tr>
    </table>

@endsection
