@extends('layouts.front')

@section('title', 'Stock')

@section('content')

    <h3>Detailed <a href="/stock/all/{{ $symbol }}">{{ $symbol }}</a> by Period: ({{ $periodId }}) {{ $from }} - {{ $to }}</h3>
    <h3>Strategy: {{ $strategyNumber }}: {{ $strategyDescription }}</h3>
    <h3>FinalAmount: {{ ceil($finalAmount) }}</h3>

    <table>
        <tr>
            <th>ID</th>
            <th>Date</th>
            <th>Close</th>
            <th>Change</th>
            <th>Buy / Sell</th>
            <th>Price</th>
            <th>Count</th>
            <th>Message</th>
            <th>currentAmount</th>
            <th>totalAmount</th>
        </tr>

        @foreach($timeSeries as $day)
            <tr>
                <td>{{ $day['id'] }}</td>
                <td>{{ $day['date'] }}</td>
                <td>{{ $day['close'] }}</td>
                <td class="{{ ($day['change'] > 0) ? 'green' : 'red' }}">{{ $day['change'] }}</td>
                <td>
                    {{ $day->stockPortfolio->operation ?? null  }}
                </td>
                <td>
                    {{ $day->stockPortfolio->price ?? null  }}
                </td>
                <td>
                    {{ $day->stockPortfolio->count ?? null  }}
                </td>
                <td>{!! $day->stockPortfolio->message ?? null !!}</td>
                <td>{{ ceil($day->stockPortfolio->currentAmount ?? 0) }}</td>
                <td>{{ ceil($day->stockPortfolio->totalAmount ?? 0) }}</td>
            </tr>
        @endforeach
    </table>

@endsection
