<x-filament-panels::page>
    <div class="space-y-6">
        {{-- Filter Form --}}
        <x-filament::section>
            <x-slot name="heading">
                Filter Laporan
            </x-slot>

            {{ $this->form }}
        </x-filament::section>

        @if ($this->reportData)
            {{-- Summary Statistics --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-chart-bar class="w-5 h-5" />
                        <div>
                            <div>Ringkasan Laporan</div>
                            <div class="text-xs text-gray-500 dark:text-gray-400 font-normal">
                                Gunakan tombol "Cetak Laporan PDF" di bagian atas untuk mengunduh laporan
                            </div>
                        </div>
                    </div>
                </x-slot>

                @php
                    $summary = $this->getSummaryData();
                @endphp

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Periode</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['period'] }}</dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pelanggan</dt>
                        <dd class="text-sm font-semibold text-purple-600 dark:text-purple-400">
                            {{ $summary['totalCustomers'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pesanan Masuk</dt>
                        <dd class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                            {{ $summary['totalEntry'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Pesanan Keluar</dt>
                        <dd class="text-sm font-semibold text-green-600 dark:text-green-400">
                            {{ $summary['totalExit'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pelanggan Dengan Pesanan Masuk
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['totalCustomersWithEntry'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pelanggan Dengan Pesanan Keluar
                        </dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['totalCustomersWithExit'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pelanggan Terbanyak Masuk</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['pelangganTerbanyakMasuk'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Pelanggan Terbanyak Keluar</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['pelangganTerbanyakKeluar'] }}
                        </dd>
                    </div>
                </div>
            </x-filament::section>

            {{-- Customer Entry Exit Table --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-users class="w-5 h-5" />
                        Detail Keluar Masuk Pesanan Per Pelanggan
                    </div>
                </x-slot>

                <div class="-mx-6 -mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th rowspan="2"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700 w-16">
                                        No
                                    </th>
                                    <th rowspan="2"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                                        Nama Pelanggan
                                    </th>
                                    <th colspan="2"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                                        Jumlah
                                    </th>
                                    <th colspan="2"
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Detail Tanggal
                                    </th>
                                </tr>
                                <tr>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                                        Masuk
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                                        Keluar
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider border-r border-gray-200 dark:border-gray-700">
                                        Masuk
                                    </th>
                                    <th
                                        class="px-6 py-3 text-center text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Keluar
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($this->getCustomerData() as $index => $customer)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-700">
                                            {{ $index + 1 }}
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-700">
                                            <div class="font-medium">{{ $customer['name'] }}</div>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center border-r border-gray-200 dark:border-gray-700">
                                            @if ($customer['entry'] > 0)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200">
                                                    {{ number_format($customer['entry']) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">0</span>
                                            @endif
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center border-r border-gray-200 dark:border-gray-700">
                                            @if ($customer['exit'] > 0)
                                                <span
                                                    class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200">
                                                    {{ number_format($customer['exit']) }}
                                                </span>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500">0</span>
                                            @endif
                                        </td>
                                        <td
                                            class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100 border-r border-gray-200 dark:border-gray-700">
                                            @if (count($customer['entry_dates']) > 0)
                                                <div class="space-y-1">
                                                    @foreach ($customer['entry_dates'] as $date)
                                                        <div class="text-xs">
                                                            <span class="text-gray-500">•</span>
                                                            {{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F Y') }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500 text-xs">-</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            @if (count($customer['exit_dates']) > 0)
                                                <div class="space-y-1">
                                                    @foreach ($customer['exit_dates'] as $date)
                                                        <div class="text-xs">
                                                            <span class="text-gray-500">•</span>
                                                            {{ \Carbon\Carbon::parse($date)->translatedFormat('l, j F Y') }}
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <span class="text-gray-400 dark:text-gray-500 text-xs">-</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="6"
                                            class="px-6 py-8 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <x-heroicon-o-inbox class="w-8 h-8 mb-2" />
                                                Tidak ada data pelanggan
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-filament::section>
        @else
            <x-filament::section>
                <div class="text-center py-12">
                    <x-heroicon-o-arrow-path class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                        Belum Ada Data
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Silakan pilih rentang tanggal untuk melihat laporan keluar masuk pesanan.
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
