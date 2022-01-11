@foreach ($sites as $site)
    <p>{{ $site->url }}</p>
    <p>{{ $site->name }}</p>
    @if ($site->is_online === false)
        <p>Offline</p>
    @else
        <p>Online</p>
    @endif
@endforeach