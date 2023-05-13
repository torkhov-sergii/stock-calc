@extends('layouts.front')

@section('title', 'Stock')

@section('content')

    <h3>Calc {{ $symbol }} by periods</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Period From</th>
            <th>Period To</th>
            <th>Period Years</th>
            <th>Stock Price From</th>
            <th>Stock Price To</th>
{{--            <th>Initial amount</th>--}}
            <th>Change Per Year, %</th>
            <th>Hold, $</th>
            <th>Active, $</th>
        </tr>

        @foreach($periodResults as $period)
            <tr>
                <td><a href="/stock/show/{{ $symbol }}?period={{ $period['id'] }}">{{ $period['id'] }}</a></td>
                <td>{{ $period['from'] }}</td>
                <td>{{ $period['to'] }}</td>
                <td>{{ round($period['periodDays'] / 365, 1) }}</td>
                <td>{{ $period['stockPriceFrom'] }}</td>
                <td>{{ $period['stockPriceTo'] }}</td>
{{--                <td>{{ $period['initialAmount'] }}</td>--}}
                <td>{{ ($period['changePerYear']) }}</td>
                <td class="{{ ($period['holdAmount'] > $period['initialAmount']) ? 'green' : 'red' }}">{{ ceil($period['holdAmount']) }}</td>
                <td class="{{ ($period['finalAmount'] > $period['initialAmount']) ? 'green' : 'red' }}">{{ ceil($period['finalAmount']) }}</td>
            </tr>
        @endforeach

        <tr>
            <th></th>
            <th></th>
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
