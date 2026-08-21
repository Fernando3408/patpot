@props(['title', 'isAjax' => request()->ajax() || request()->header('X-Requested-With') === 'XMLHttpRequest'])

@if($isAjax)
    <form {{ $attributes }}>
        {{ $slot }}
    </form>
@else
    <x-erp-layout :title="$title">
        {{ $slot }}
    </x-erp-layout>
@endif
