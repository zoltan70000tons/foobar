@extends('emails.layouts.systemLayout')

@section('title', $greeting ?? __('Hello!'))

@section('header')
    {{ config('app.name') }}
@endsection

@section('content')
    {{-- Greeting --}}
    @if (! empty($greeting))
        <h1>{{ $greeting }}</h1>
    @else
        <h1>@lang($level === 'error' ? 'Whoops!' : 'Hello!')</h1>
    @endif

    {{-- Intro Lines --}}
    @foreach ($introLines as $line)
        <p>{{ $line }}</p>
    @endforeach

    {{-- Action Button --}}
    @isset($actionText)
        @include('emails.components.button', [
            'url' => $actionUrl,
            'slot' => $actionText
        ])
    @endisset

    {{-- Outro Lines --}}
    @foreach ($outroLines as $line)
        <p>{{ $line }}</p>
    @endforeach
@endsection

@section('footer')
    <p>{{ !empty($salutation) ? $salutation : __('Regards,') }}</p>
    <p>{{ config('app.name') }}</p>

    {{-- Subcopy --}}
    @isset($actionText)
        <p>
            @lang(
                "If you're having trouble clicking the \":actionText\" button, copy and paste the URL below\n".
                'into your web browser:',
                ['actionText' => $actionText]
            )
            <br>
            <a href="{{ $actionUrl }}" style="word-wrap: break-word;">{{ $actionUrl }}</a>
        </p>
    @endisset
@endsection