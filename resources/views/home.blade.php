<!DOCTYPE html>
<html lang="id" class="scroll-smooth">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sinar Laundry - Solusi Laundry Terpercaya</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>


<body class="antialiased text-gray-800">

    {{-- 1. HEADER --}}
    <header class="bg-white shadow sticky top-0 z-50">
        <div class="container mx-auto px-4 py-4 flex justify-between items-center">
            @php
                $logoPath = null;
                foreach (['png', 'jpg', 'jpeg', 'svg', 'webp'] as $ext) {
                    if (file_exists(storage_path("app/public/login-page-image/logo.$ext"))) {
                        $logoPath = asset("storage/login-page-image/logo.$ext");
                        break;
                    }
                }
            @endphp

            <a href="#hero" class="flex items-center space-x-2 scroll-smooth">
                @if ($logoPath)
                    <img src="{{ $logoPath }}" alt="Logo" class="h-10">
                @else
                    <span class="text-xl font-bold text-blue-600">Sinar Laundry</span>
                @endif
            </a>

            <nav class="flex items-center space-x-4">
                <a href="#tentang" class="text-gray-700 hover:text-blue-600">Tentang</a>
                <a href="#layanan" class="text-gray-700 hover:text-blue-600">Layanan</a>
                <a href="#paket" class="text-gray-700 hover:text-blue-600">Paket</a>
                <a href="#kontak" class="text-gray-700 hover:text-blue-600">Kontak</a>

                {{-- Tombol Masuk --}}
                <a href="{{ route('filament.admin.auth.login') }}"
                    class="ml-4 inline-block bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 transition">
                    Masuk
                </a>
            </nav>
        </div>
    </header>


    {{-- 2. HERO SECTION --}}
    <section class="bg-blue-50 py-16 text-center scroll-mt-24" id="hero">
        <div class="container mx-auto px-4">
            {{-- Nama Laundry --}}
            <h1 class="text-4xl md:text-5xl font-extrabold text-blue-700">Sinar Laundry</h1>

            {{-- Slogan --}}
            <p class="mt-2 text-2xl md:text-3xl font-semibold text-gray-900">Cepat, Bersih, dan Wangi</p>

            {{-- Deskripsi --}}
            <p class="mt-4 text-lg text-gray-700">Harga terjangkau dengan layanan lengkap dan gratis antar
                jemput<br>Kami siap memberikan pengerjaan yang terbaik untuk pesanan anda</p>

            {{-- Tombol --}}
            <div class="flex flex-col items-center gap-4 mt-6">
                <a href="https://wa.me/{{ $whatsapp }}" target="_blank"
                    class="bg-green-500 text-white px-6 py-3 rounded-lg shadow hover:bg-green-600 transition">
                    Hubungi via WhatsApp
                </a>

                <span class="text-gray-500 font-medium">atau</span>

                <a href="{{ route('filament.admin.auth.register') }}"
                    class="bg-blue-500 text-white px-6 py-3 rounded-lg shadow hover:bg-blue-600 transition">
                    Daftar
                </a>
            </div>

            {{-- Statistik --}}
            <p class="mt-8 text-gray-600 text-sm">
                Telah dipercaya oleh <strong>{{ $customerCount }}</strong> orang dan menerima
                <strong>{{ $orderCount }}</strong> total pesanan bulan ini ({{ now()->translatedFormat('F Y') }})
            </p>
        </div>
    </section>


    {{-- 3. TENTANG KAMI --}}
    <section class="py-16 bg-white scroll-mt-24" id="tentang">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-3xl font-bold text-gray-800 mb-6">Tentang Kami</h2>
            <p class="text-gray-600 max-w-2xl mx-auto">
                Kami adalah layanan laundry terpercaya yang <strong>berlokasi di Banjarbaru</strong>, dan telah berdiri
                sejak <strong>2014</strong>.
                Dengan pengalaman lebih dari satu dekade, kami berkomitmen memberikan hasil cucian
                terbaik — <strong>bersih</strong>,
                <strong>wangi</strong>, dan <strong>bebas bau apek</strong>. Proses <strong>setrika uap</strong> kami
                memastikan hasil yang rapi dan lembut,
                sementara penggunaan <strong>mesin pengering</strong> modern memungkinkan pesanan tetap selesai tepat
                waktu, meskipun cuaca kurang mendukung.
            </p>
        </div>
    </section>

    {{-- 4. LAYANAN KAMI --}}
    <section class="py-16 bg-gray-50 scroll-mt-24" id="layanan">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-10">Layanan Kami</h2>

            <div class="relative w-full max-w-4xl mx-auto overflow-hidden rounded-lg shadow-lg" x-data="carousel()"
                x-init="start()" @mouseenter="stop()" @mouseleave="start()">

                {{-- Carousel Container --}}
                <div class="relative h-80">
                    {{-- Carousel Track --}}
                    <div class="flex transition-transform duration-500 ease-in-out h-full"
                        :style="`transform: translateX(-${currentIndex * 100}%)`">
                        <template x-for="(item, index) in items" :key="index">
                            <div class="min-w-full h-full relative flex-shrink-0"
                                :style="`background-image: url('${item.image}'); background-size: cover; background-position: center;`">
                                {{-- Dark Overlay --}}
                                <div class="absolute inset-0 bg-black bg-opacity-50 flex items-center justify-center">
                                    <h3 class="text-3xl md:text-4xl font-bold text-white text-center px-4"
                                        x-text="item.name"></h3>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                {{-- Previous Button --}}
                <button @click="prev()"
                    class="absolute top-1/2 left-4 -translate-y-1/2 bg-white bg-opacity-90 hover:bg-opacity-100 rounded-full w-12 h-12 flex items-center justify-center shadow-lg transition-all duration-300 z-10 group">
                    <svg class="w-6 h-6 text-gray-700 group-hover:text-gray-900 transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7">
                        </path>
                    </svg>
                </button>

                {{-- Next Button --}}
                <button @click="next()"
                    class="absolute top-1/2 right-4 -translate-y-1/2 bg-white bg-opacity-90 hover:bg-opacity-100 rounded-full w-12 h-12 flex items-center justify-center shadow-lg transition-all duration-300 z-10 group">
                    <svg class="w-6 h-6 text-gray-700 group-hover:text-gray-900 transition-colors" fill="none"
                        stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </button>

                {{-- Indicators --}}
                <div class="absolute bottom-4 left-1/2 -translate-x-1/2 flex space-x-2">
                    <template x-for="(item, index) in items" :key="index">
                        <button @click="goToSlide(index)" class="w-3 h-3 rounded-full transition-all duration-300"
                            :class="currentIndex === index ? 'bg-white bg-opacity-100' :
                                'bg-white bg-opacity-50 hover:bg-opacity-75'">
                        </button>
                    </template>
                </div>
            </div>

            {{-- Service Type Cards --}}
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mt-10">
                <template x-for="(item, index) in items" :key="index">
                    <div class="bg-white p-6 rounded-lg shadow hover:shadow-md transition-shadow duration-300 text-center group cursor-pointer"
                        @click="goToSlide(index)">
                        <div
                            class="w-16 h-16 mx-auto mb-4 bg-gray-100 rounded-lg flex items-center justify-center group-hover:bg-blue-50 transition-colors duration-300">
                            <img :src="item.image" :alt="item.name" class="w-10 h-10 object-contain">
                        </div>
                        <div class="text-blue-600 text-lg font-semibold group-hover:text-blue-700 transition-colors duration-300"
                            x-text="item.name"></div>
                    </div>
                </template>
            </div>
        </div>
    </section>

    <script>
        function carousel() {
            return {
                currentIndex: 0,
                interval: null,
                items: [{
                        name: 'Kiloan',
                        image: '{{ asset('icons/tipe-kiloan.svg') }}'
                    },
                    {
                        name: 'Lembaran',
                        image: '{{ asset('icons/tipe-lembaran.svg') }}'
                    },
                    {
                        name: 'Satuan',
                        image: '{{ asset('icons/tipe-satuan.svg') }}'
                    },
                    {
                        name: 'Karpet',
                        image: '{{ asset('icons/tipe-karpet.svg') }}'
                    }
                ],

                next() {
                    this.currentIndex = (this.currentIndex + 1) % this.items.length;
                },

                prev() {
                    this.currentIndex = (this.currentIndex - 1 + this.items.length) % this.items.length;
                },

                goToSlide(index) {
                    this.currentIndex = index;
                },

                start() {
                    if (this.items.length > 1) {
                        this.interval = setInterval(() => {
                            this.next();
                        }, 4000);
                    }
                },

                stop() {
                    if (this.interval) {
                        clearInterval(this.interval);
                        this.interval = null;
                    }
                }
            }
        }
    </script>

    {{-- 5. PAKET PESANAN --}}
    <section class="py-16 bg-white scroll-mt-24" id="paket">
        <div class="container mx-auto px-4">
            <h2 class="text-3xl font-bold text-center text-gray-800 mb-10">Paket Pesanan</h2>

            @foreach ($groupedPackages as $type => $packages)
                <div x-data="{ open: true }" class="mt-10 border border-gray-300 rounded-xl shadow-sm">
                    <div @click="open = !open"
                        class="flex justify-between items-center bg-blue-50 px-6 py-4 cursor-pointer rounded-t-xl">
                        <h3 class="text-xl font-semibold text-blue-800">{{ $type }}</h3>
                        <svg :class="{ 'rotate-180': open }"
                            class="w-5 h-5 text-blue-800 transition-transform duration-300" fill="none"
                            stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7" />
                        </svg>
                    </div>

                    <div x-show="open" x-transition class="px-6 pb-6 pt-4">
                        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-6">
                            @foreach ($packages as $package)
                                <div
                                    class="bg-white border border-gray-200 rounded-xl shadow-sm hover:shadow-md transition-all duration-300 p-6">
                                    <div class="flex items-center justify-between mb-2">
                                        <h4 class="text-lg font-bold text-gray-800">{{ $package->name }}</h4>
                                        @if ($package->active_discount)
                                            <span
                                                class="text-sm bg-green-100 text-green-700 px-2 py-1 rounded-lg font-semibold">
                                                Diskon
                                            </span>
                                        @endif
                                    </div>

                                    <p class="text-gray-700 text-base font-semibold">
                                        Rp {{ number_format($package->price, 0, ',', '.') }}
                                    </p>

                                    @if ($package->active_discount)
                                        @php $discount = $package->active_discount; @endphp
                                        <div class="mt-3 text-sm text-green-700 bg-green-50 p-3 rounded-lg">
                                            <p>
                                                <strong>Diskon:</strong>
                                                @if ($discount->type === 'Persentase')
                                                    {{ $discount->value }}% dari total harga pesanan
                                                @else
                                                    Rp {{ number_format($discount->value, 0, ',', '.') }} dari total
                                                    harga pesanan
                                                @endif
                                            </p>
                                            <p class="text-xs text-green-600 mt-1 italic">
                                                Berlaku hingga
                                                {{ \Carbon\Carbon::parse($discount->end_date)->translatedFormat('d F Y') }}
                                            </p>
                                        </div>
                                    @endif
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </section>

    {{-- 6. FOOTER --}}
    <footer class="bg-gray-900 text-white py-12 scroll-mt-24" id="kontak">
        <div class="container mx-auto px-4">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 text-sm">
                <div>
                    <h5 class="text-lg font-semibold mb-2">Jam Operasional</h5>
                    <p>Senin – Minggu<br>07.00 – 20.00<br>7 Pagi hingga 8 Malam</p>
                </div>
                <div>
                    <h5 class="text-lg font-semibold mb-2">Kontak</h5>
                    <p class="flex items-center gap-2">
                        <img src="{{ asset('icons/WhatsApp.webp') }}" alt="WhatsApp" class="w-5 h-5">
                        WhatsApp: +{{ $whatsapp }}
                    </p>
                    <a href="https://wa.me/{{ $whatsapp }}" target="_blank"
                        class="inline-block mt-6 bg-green-500 text-white px-6 py-3 rounded-lg shadow hover:bg-green-600 transition">
                        Hubungi Sekarang
                    </a>
                </div>
                <div>
                    <h5 class="text-lg font-semibold mb-2">Lokasi</h5>
                    <p>Jl. Kasturi 2, RT.038/RW.006, Syamsudin Noor, Kec. Landasan Ulin, Kota Banjar Baru, Kalimantan
                        Selatan 70724</p>
                    <div class="mt-2">
                        <iframe src="{{ $mapsUrl }}" width="100%" height="150" style="border:0;"
                            allowfullscreen="" loading="lazy"></iframe>
                    </div>
                </div>
            </div>
            <p class="text-center text-gray-400 mt-10">&copy; {{ date('Y') }} Sinar Laundry</p>
        </div>
    </footer>
    <script src="{{ asset('js/alpine.min.js') }}" defer></script>
</body>

</html>
