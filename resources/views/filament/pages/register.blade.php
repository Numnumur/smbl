<div class="flex min-h-screen">
    <div
        class="bg-white dark:bg-gray-900 flex flex-1 flex-col justify-center py-12 px-4 sm:px-6 lg:flex-none lg:px-20 xl:px-24">
        <div class="mx-auto w-full max-w-sm lg:w-96">
            @php
                $logoPath = null;
                foreach (['png', 'jpg', 'jpeg', 'svg', 'webp'] as $ext) {
                    if (file_exists(public_path("storage/login-page-image/logo.$ext"))) {
                        $logoPath = asset("storage/login-page-image/logo.$ext");
                        break;
                    }
                }

                $title = 'Buat Akun Baru';
            @endphp

            <div>
                <div class="flex justify-center">
                    @if ($logoPath)
                        <img src="{{ $logoPath }}" alt="Logo" class="h-16">
                    @else
                        <span class="font-bold text-center text-2xl text-gray-900 dark:text-white/90">Sistem Manajemen
                            Bisnis
                            Laundry</span>
                    @endif
                </div>
                <h2 class="mt-6 text-3xl font-bold tracking-tight text-gray-900 dark:text-white/90">{{ $title }}
                </h2>
            </div>

            <div class="mt-8">
                <div class="mt-6">
                    <x-filament-panels::form wire:submit="register">
                        {{ $this->form }}

                        <x-filament-panels::form.actions :actions="$this->getCachedFormActions()" :alignment="$this->getFormActionsAlignment()" :full-width="$this->hasFullWidthFormActions()" />
                    </x-filament-panels::form>

                    <p class="font-bold mt-4 text-center text-sm text-gray-900 dark:text-white/90">
                        Sudah punya akun?
                        <a href="{{ route('filament.admin.auth.login') }}" class="text-primary-600 hover:underline">
                            Masuk
                        </a>
                    </p>
                </div>
            </div>
        </div>
    </div>

    @php
        $bgPath = null;
        foreach (['png', 'jpg', 'jpeg', 'webp'] as $ext) {
            if (file_exists(public_path("storage/login-page-image/login-bg.$ext"))) {
                $bgPath = asset("storage/login-page-image/login-bg.$ext");
                break;
            }
        }
    @endphp

    <div class="relative hidden w-0 flex-1 lg:block bg-cover bg-center"
        @if ($bgPath) style="background-image: url('{{ $bgPath }}');" @endif>
        <div class="absolute inset-0 h-full w-full" id="particles"></div>
    </div>

    <x-filament-actions::modals />

    <script src="{{ asset('js/app/particles-bundle.js') }}"></script>
    <script>
        (async () => {
            await loadFireflyPreset(tsParticles);

            await tsParticles.load("particles", {
                preset: "firefly",
            });
        })();
    </script>
</div>
