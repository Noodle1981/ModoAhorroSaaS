@props(['messages', 'class' => 'mt-2'])

@if ($messages)
    <ul {{ $attributes->merge(['class' => 'text-sm text-red-600 ' . $class]) }}>
        @foreach ((array) $messages as $message)
            <li>{{ $message }}</li>
        @endforeach
    </ul>
@endif
