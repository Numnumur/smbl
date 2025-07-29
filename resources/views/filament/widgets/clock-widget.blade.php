<x-filament-widgets::widget class="h-full">
    <x-filament::section class="h-full flex items-center justify-center">
        @php
            $localization = \App\Models\Localization::first();
            $timezoneName = $localization?->timezone ?? 'Asia/Makassar';

            $timeFormat = 'id-ID';
            $hour12 = 'false';

            $timezone = new DateTimeZone($timezoneName);
            $offset = $timezone->getOffset(new DateTime('now', $timezone)) / 3600;
            $gmtOffset = 'GMT' . ($offset >= 0 ? '+' : '') . $offset;
        @endphp

        <div x-data="{
            time: '',
            date: '',
            gmtOffset: '',
            updateTime() {
                let formatter = new Intl.DateTimeFormat('{{ $timeFormat }}', {
                    hour: '2-digit',
                    minute: '2-digit',
                    second: '2-digit',
                    hour12: {{ $hour12 }},
                    timeZone: '{{ $timezoneName }}'
                });
                let dateFormatter = new Intl.DateTimeFormat('{{ $timeFormat }}', {
                    weekday: 'long',
                    year: 'numeric',
                    month: 'long',
                    day: 'numeric',
                    timeZone: '{{ $timezoneName }}'
                });
        
                this.time = formatter.format(new Date());
                this.date = dateFormatter.format(new Date());
        
                let offsetMinutes = new Date().getTimezoneOffset();
                let offsetHours = Math.abs(offsetMinutes) / 60;
                let sign = offsetMinutes < 0 ? '+' : '-';
                this.gmtOffset = `GMT${sign}${offsetHours}`;
            },
            init() {
                this.updateTime();
                setInterval(() => {
                    this.updateTime();
                }, 1000);
            }
        }" class="text-center space-y-2">
            <div>
                <span class="text-sm text-gray-400">{{ $timezoneName }} ({{ $gmtOffset }})</span>
                <br>
                <span class="text-2xl font-bold" x-text="time"></span>
                <br>
                <span class="text-gray-400" x-text="date"></span>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
