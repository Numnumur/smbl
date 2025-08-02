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
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Penggunaan Diskon</dt>
                        <dd class="text-sm font-semibold text-blue-600 dark:text-blue-400">
                            {{ $summary['totalUsage'] }} kali
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Potongan Harga</dt>
                        <dd class="text-sm font-semibold text-green-600 dark:text-green-400">
                            {{ $summary['totalDiscount'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Jenis Diskon</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['totalDiscountTypes'] }}</dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Total Paket Dengan Diskon</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['totalPackageDiscounts'] }}
                        </dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Diskon Terpopuler</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['diskonTerpopuler'] }}</dd>
                    </div>

                    <div class="space-y-2">
                        <dt class="text-sm font-medium text-gray-500 dark:text-gray-400">Paket Terbanyak Diskon</dt>
                        <dd class="text-sm text-gray-900 dark:text-gray-100">{{ $summary['paketTerbanyakDiskon'] }}</dd>
                    </div>
                </div>
            </x-filament::section>

            {{-- Discount Usage Table --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-receipt-percent class="w-5 h-5" />
                        Penggunaan Berdasarkan Jenis Diskon
                    </div>
                </x-slot>

                <div class="-mx-6 -mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">
                                        No
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Nama Diskon
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                        Jumlah Penggunaan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total Potongan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($this->getDiscountData() as $index => $discount)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800 dark:bg-purple-900 dark:text-purple-200">
                                                {{ $discount['name'] }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-gray-100">
                                            {{ number_format($discount['count']) }} kali
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($discount['total_value'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-6 py-8 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <x-heroicon-o-inbox class="w-8 h-8 mb-2" />
                                                Tidak ada data diskon
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-filament::section>

            {{-- Package Discount Table --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-cube class="w-5 h-5" />
                        Pemberian Diskon Berdasarkan Paket Pesanan
                    </div>
                </x-slot>

                <div class="-mx-6 -mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">
                                        No
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Nama Diskon
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tipe
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Nilai
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Nama Paket
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                        Jumlah Penggunaan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total Potongan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($this->getPackageData() as $index => $package)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $package['discount'] }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if ($package['type'] === 'Persentase') bg-orange-100 text-orange-800 dark:bg-orange-900 dark:text-orange-200
                                                @elseif($package['type'] === 'Langsung') bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ $package['type'] }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-gray-100">
                                            @if ($package['type'] === 'Persentase')
                                                {{ number_format($package['value']) }}%
                                            @elseif ($package['type'] === 'Langsung')
                                                Rp {{ number_format($package['value'], 0, ',', '.') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            {{ $package['package'] }}
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-gray-100">
                                            {{ number_format($package['count']) }} kali
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($package['total_value'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7"
                                            class="px-6 py-8 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <x-heroicon-o-inbox class="w-8 h-8 mb-2" />
                                                Tidak ada data paket dengan diskon
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </x-filament::section>

            {{-- Type Discount Table --}}
            <x-filament::section>
                <x-slot name="heading">
                    <div class="flex items-center gap-2">
                        <x-heroicon-o-squares-2x2 class="w-5 h-5" />
                        Pemberian Diskon Berdasarkan Tipe Pesanan
                    </div>
                </x-slot>

                <div class="-mx-6 -mb-6">
                    <div class="overflow-x-auto">
                        <table class="w-full divide-y divide-gray-200 dark:divide-gray-700">
                            <thead class="bg-gray-50 dark:bg-gray-800">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-16">
                                        No
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Tipe Pesanan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider w-32">
                                        Jumlah Penggunaan
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">
                                        Total Potongan
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white dark:bg-gray-900 divide-y divide-gray-200 dark:divide-gray-700">
                                @forelse($this->getTypeData() as $index => $type)
                                    <tr>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            {{ $index + 1 }}
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900 dark:text-gray-100">
                                            <span
                                                class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium
                                                @if ($type['type'] === 'Kiloan') bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200
                                                @elseif($type['type'] === 'Karpet') bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200
                                                @elseif($type['type'] === 'Lembaran') bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200
                                                @else bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-200 @endif">
                                                {{ $type['type'] }}
                                            </span>
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-center text-gray-900 dark:text-gray-100">
                                            {{ number_format($type['count']) }} kali
                                        </td>
                                        <td
                                            class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-gray-100">
                                            Rp {{ number_format($type['total_value'], 0, ',', '.') }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4"
                                            class="px-6 py-8 whitespace-nowrap text-sm text-center text-gray-500 dark:text-gray-400">
                                            <div class="flex flex-col items-center">
                                                <x-heroicon-o-inbox class="w-8 h-8 mb-2" />
                                                Tidak ada data tipe dengan diskon
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
                    <x-heroicon-o-receipt-percent class="mx-auto h-12 w-12 text-gray-400" />
                    <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">
                        Belum Ada Data
                    </h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Silakan pilih rentang tanggal untuk melihat laporan pemberian diskon.
                    </p>
                </div>
            </x-filament::section>
        @endif
    </div>
</x-filament-panels::page>
