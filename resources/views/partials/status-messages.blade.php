@foreach(['success' => 'success', 'error' => 'danger'] as $key => $style)
    @if(session($key))
        <div class="alert alert-{{ $style }}" role="status">{{ session($key) }}</div>
    @endif
@endforeach
