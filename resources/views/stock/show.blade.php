@extends('layouts.front')

@section('title', 'Stock')

@section('content')

    <h3>Detailed <a href="/stock/all/{{ $symbol }}">{{ $symbol }}</a> by Period: ({{ $periodId }}) {{ $from }} - {{ $to }}</h3>
{{--    <h3>Initial amount: {{ $initialAmount }}</h3>--}}
    <h3>Result: {{ ceil($finalAmount) }}</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Close</th>
            <th>Change</th>
            <th>Buy / Sell</th>
            <th>Amount</th>
        </tr>

        @foreach($timeSeries as $day)
            <tr>
                <td>{{ $day['id'] }}</td>
                <td>{{ $day['date'] }}</td>
                <td>{{ $day['close'] }}</td>
                <td class="{{ ($day['change'] > 0) ? 'green' : 'red' }}">{{ $day['change'] }}</td>
                <td>
                    @if($day['stockPortfolio'])
                        {{ $day['stockPortfolio']['operation'] .' '. $day['stockPortfolio']['price']  }}
                    {!! $day['stockPortfolio']['message'] !!}
                    @endif
                </td>
                <td>{{ ceil($day['amount']) }}</td>
            </tr>
        @endforeach
    </table>

@endsection
