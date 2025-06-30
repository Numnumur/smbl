@php
    $logoPath = null;
    foreach (['png', 'jpg', 'jpeg', 'svg', 'webp'] as $ext) {
        if (file_exists(storage_path("app/public/login-page-image/logo.$ext"))) {
            $logoPath = asset("storage/login-page-image/logo.$ext");
            break;
        }
    }
@endphp

<div class="flex justify-center">
    @if ($logoPath)
        <img src="{{ $logoPath }}" alt="Logo" class="h-11">
    @else
        <span class="font-bold text-2xl text-gray-900 dark:text-white/90">smbl</span>
    @endif
</div>
