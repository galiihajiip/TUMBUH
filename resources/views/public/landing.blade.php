<x-layouts.public title="Agar usaha Anda terus tumbuh">
    {{-- HERO --}}
    <section class="relative isolate overflow-hidden bg-ink"
             x-data="{ playing: true }"
             x-init="if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) { $refs.video.pause(); playing = false }">
        {{-- Background video: Mixkit #49287 "Muslim girl managing her business from an outdoor café" (Mixkit Free License, no attribution required). --}}
        <video x-ref="video" class="absolute inset-0 -z-20 h-full w-full object-cover" autoplay muted loop playsinline preload="metadata"
               poster="{{ asset('media/hero-umkm-poster.jpg') }}" aria-hidden="true">
            <source src="{{ asset('media/hero-umkm.mp4') }}" type="video/mp4">
        </video>
        <div class="absolute inset-0 -z-10 bg-[linear-gradient(90deg,rgba(26,29,35,0.93)_0%,rgba(26,29,35,0.8)_48%,rgba(26,29,35,0.4)_100%)]"></div>
        <div class="absolute inset-x-0 bottom-0 -z-10 h-24 bg-gradient-to-t from-ink/60 to-transparent"></div>

        <div class="mx-auto grid max-w-[1200px] items-center gap-12 px-4 py-16 md:px-8 lg:grid-cols-[1.1fr_1fr] lg:py-24">
            <div>
                <h1 class="text-[38px] font-bold leading-[1.12] tracking-[-0.01em] text-white md:text-[50px]">Agar usaha Anda tidak hanya berjalan, tapi <span class="text-[#ff4d55]">terus tumbuh.</span></h1>
                <p class="mt-6 max-w-xl text-[17px] leading-7 text-white/90">Sudah pakai media sosial, QRIS, marketplace, atau berbagai tools digital untuk bisnis? Sekarang saatnya melihat apakah semua itu benar-benar membantu usaha Anda berkembang.</p>
                <p class="mt-4 max-w-xl text-[15px] leading-7 text-white/70">TUMBUH membantu Anda menemukan masalah terbesar dalam bisnis, menentukan langkah yang paling tepat, lalu melihat hasilnya dari waktu ke waktu.</p>
                <div class="mt-8 flex flex-wrap gap-3">
                    <a href="{{ route('register') }}" class="btn-primary h-12 px-6 text-[14px]">Mulai Perjalanan Saya <x-icon name="arrow_forward" size="18" /></a>
                    <a href="#cara-kerja" class="btn h-12 border-[1.5px] border-white/70 px-6 text-[14px] text-white hover:bg-white hover:text-ink"><x-icon name="play_circle" size="18" /> Lihat Cara Kerjanya</a>
                </div>
            </div>

            {{-- PREVIEW DIAGNOSTIC (illustrative) --}}
            <div class="card p-6 shadow-[var(--shadow-modal)]">
                <div class="flex items-start justify-between gap-3">
                    <p class="text-[13px] font-semibold leading-5 text-ink">Kenali kondisi usaha Anda sebelum menentukan langkah berikutnya.</p>
                    <span class="pill shrink-0 bg-[#f5f3ff] text-[#6d28d9]">Contoh ilustratif</span>
                </div>
                <div class="mt-4 flex items-center gap-5">
                    <x-score-ring :value="68" :size="104" label="Cukup Baik" />
                    <div>
                        <div class="eyebrow">Posisi usaha saat ini</div>
                        <div class="mt-1 text-[16px] font-semibold">Productive · Silver</div>
                        <p class="mt-1 text-[12px] leading-5 text-slate">Penjualan sudah memiliki fondasi yang cukup baik, tetapi pertumbuhan masih tertahan karena pelanggan belum cukup sering kembali.</p>
                    </div>
                </div>
                <div class="mt-5 flex items-center justify-between rounded-[12px] border border-line bg-canvas p-3 text-[13px]">
                    <span class="text-slate">Area terkuat</span><span class="font-semibold">Digital Presence · 78</span>
                </div>
                <div class="mt-3 rounded-[12px] border border-brand bg-brand-soft p-4 shadow-[var(--shadow-spotlight)]">
                    <div class="eyebrow-brand flex items-center gap-1"><x-icon name="warning" size="14" fill />Masalah utama</div>
                    <div class="mt-1 text-[16px] font-semibold">Customer Retention · 45</div>
                    <div class="mt-3 grid grid-cols-3 gap-2 text-center">
                        <div class="rounded-[10px] bg-white px-2 py-2"><div class="text-[11px] text-slate">Repeat customer</div><div class="font-display text-[17px] font-bold text-brand-deep">22%</div></div>
                        <div class="rounded-[10px] bg-white px-2 py-2"><div class="text-[11px] text-slate">Target</div><div class="font-display text-[17px] font-bold">30%</div></div>
                        <div class="rounded-[10px] bg-white px-2 py-2"><div class="text-[11px] text-slate">Gap</div><div class="font-display text-[17px] font-bold">8 pp</div><div class="text-[10px] text-slate">poin persentase</div></div>
                    </div>
                </div>
                <div class="mt-3 rounded-[12px] border border-line p-4">
                    <div class="eyebrow">Langkah yang disarankan</div>
                    <p class="mt-1 text-[13px]">Bangun kembali hubungan dengan pelanggan melalui strategi yang lebih tepat.</p>
                    <div class="mt-3 flex flex-wrap items-center justify-between gap-2">
                        <span class="text-[13px] font-semibold">RCS + MyAds + Kuncie</span>
                        <a href="{{ route('register') }}" class="btn-primary btn-sm">Lihat Rekomendasi</a>
                    </div>
                </div>
                <p class="mt-3 text-[11px] text-slate">Contoh di atas merupakan ilustrasi, bukan data bisnis nyata.</p>
            </div>
        </div>
        <div class="absolute bottom-4 left-4 md:left-8">
            <button type="button" @click="playing ? $refs.video.pause() : $refs.video.play(); playing = !playing"
                    class="flex h-9 w-9 items-center justify-center rounded-full border border-white/30 bg-white/10 text-white backdrop-blur hover:bg-white/20"
                    :aria-label="playing ? 'Jeda video latar' : 'Putar video latar'">
                <x-icon name="pause" size="18" x-show="playing" /><x-icon name="play_arrow" size="18" x-show="!playing" x-cloak />
            </button>
        </div>
    </section>

    {{-- CERITA UTAMA --}}
    <section class="mx-auto max-w-[1200px] px-4 py-20 md:px-8">
        <div class="grid gap-12 lg:grid-cols-[1.1fr_1fr]">
            <div>
                <h2 class="text-[30px] font-bold md:text-[36px]">Banyak UMKM sudah go digital. Tapi pertanyaannya, apakah bisnisnya ikut tumbuh?</h2>
                <div class="mt-6 space-y-4 text-[16px] leading-7 text-ink-2">
                    <p>Hari ini, memiliki Instagram, QRIS, marketplace, atau tools digital lainnya sudah menjadi hal yang biasa.</p>
                    <p>Masalahnya muncul setelah itu.</p>
                    <p>Banyak pemilik usaha masih harus membuka banyak aplikasi, mencatat banyak hal secara terpisah, lalu mengambil keputusan berdasarkan perkiraan.</p>
                </div>
            </div>
            <div class="flex flex-col justify-center">
                <p class="eyebrow mb-3">Akhirnya muncul pertanyaan:</p>
                <div class="space-y-3">
                    @foreach (['Mana yang sebenarnya bekerja?', 'Apa yang membuat penjualan saya tertahan?', 'Apa yang harus saya lakukan berikutnya?'] as $q)
                        <div class="card flex items-center gap-3 px-5 py-4"><x-icon name="help" class="text-brand" /><span class="text-[16px] font-semibold">{{ $q }}</span></div>
                    @endforeach
                </div>
                <p class="mt-6 text-[16px] leading-7 text-ink-2"><b class="text-ink">TUMBUH hadir untuk membantu menjawab pertanyaan tersebut.</b> Bukan dengan memberi Anda semakin banyak tools, tetapi dengan membantu Anda memahami bisnis, menentukan prioritas, menjalankan langkah yang tepat, dan melihat hasilnya.</p>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS --}}
    <section id="cara-kerja" class="scroll-mt-20 bg-canvas">
        <div class="mx-auto max-w-[1200px] px-4 py-20 md:px-8">
            <div class="max-w-2xl">
                <div class="eyebrow-brand mb-2">Cara kerja</div>
                <h2 class="text-[30px] font-bold md:text-[36px]">Mulai dari satu pertanyaan sederhana</h2>
                <p class="mt-3 text-[18px] font-semibold text-ink">Apa yang sedang menahan bisnis Anda untuk tumbuh?</p>
                <p class="mt-2 text-slate">TUMBUH membantu Anda menemukan jawabannya melalui perjalanan yang sederhana.</p>
            </div>
            <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-5">
                @foreach ([
                    ['01', 'Kenali', 'troubleshoot', ['Ceritakan kondisi bisnis Anda dan apa yang ingin dicapai.', 'TUMBUH melihat beberapa sisi penting dari usaha Anda, mulai dari pelanggan, penjualan, pemasaran, sampai operasional.']],
                    ['02', 'Temukan', 'target', ['Dari data tersebut, TUMBUH membantu menemukan satu masalah yang paling perlu diperhatikan saat ini.', 'Bukan memberi puluhan saran sekaligus. Fokus pada hal yang paling berdampak.']],
                    ['03', 'Jalankan', 'bolt', ['Setelah tahu masalahnya, Anda mendapatkan langkah yang bisa langsung dijalankan, termasuk solusi Telkomsel yang paling relevan dengan kebutuhan bisnis Anda.']],
                    ['04', 'Lihat hasilnya', 'monitoring', ['Setelah langkah tersebut dijalankan, TUMBUH membantu membandingkan kondisi sebelum dan sesudah.', 'Apakah penjualan bertambah? Apakah pelanggan kembali lebih sering? Apakah pemasaran menjadi lebih efektif?']],
                    ['05', 'Tumbuh lagi', 'all_inclusive', ['Karena bisnis tidak berhenti pada satu masalah. Setelah satu hal membaik, TUMBUH membantu menemukan hal berikutnya yang perlu diperbaiki.', 'Satu langkah selesai, perjalanan berikutnya dimulai.']],
                ] as [$n, $t, $icon, $lines])
                    <div class="card flex flex-col p-5 {{ $loop->last ? 'border-brand bg-brand text-white' : '' }}">
                        <div class="flex items-center justify-between">
                            <span class="font-display text-[13px] font-bold {{ $loop->last ? 'text-white/80' : 'text-brand-deep' }}">{{ $n }}</span>
                            <x-icon :name="$icon" class="{{ $loop->last ? 'text-white' : 'text-slate-2' }}" />
                        </div>
                        <h3 class="mt-3 text-[18px] font-bold {{ $loop->last ? 'text-white' : '' }}">{{ $t }}</h3>
                        @foreach ($lines as $line)
                            <p class="mt-2 text-[13px] leading-6 {{ $loop->parent->last ? 'text-white/90' : 'text-slate' }}">{{ $line }}</p>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 5 VALUE PROPOSITIONS --}}
    <section id="keuntungan" class="scroll-mt-20 mx-auto max-w-[1200px] px-4 py-20 md:px-8">
        <div class="grid gap-10 lg:grid-cols-[1fr_1.4fr]">
            <div>
                <div class="eyebrow-brand mb-2">Keuntungan</div>
                <h2 class="text-[30px] font-bold md:text-[34px]">Bukan sekadar tahu kondisi bisnis. Anda juga tahu harus mulai dari mana.</h2>
            </div>
            <div class="card divide-y divide-line">
                @foreach ([
                    ['Pahami bisnis Anda', 'Lihat kondisi usaha secara lebih utuh, tanpa harus menebak-nebak.', 'visibility', 'Growth Score'],
                    ['Temukan masalah yang paling penting', 'TUMBUH membantu menentukan prioritas agar Anda tidak sibuk memperbaiki semuanya sekaligus.', 'target', 'Bottleneck'],
                    ['Dapatkan langkah yang relevan', 'Setelah tahu masalahnya, Anda mendapatkan rekomendasi yang sesuai dengan kondisi usaha Anda.', 'lightbulb', 'Next Best Action'],
                    ['Buktikan hasilnya', 'Lihat perubahan bisnis sebelum dan sesudah menjalankan suatu aksi.', 'compare_arrows', 'Before vs After'],
                    ['Terus berkembang', 'Setiap kemajuan membawa Anda ke tahap berikutnya, dengan tantangan dan peluang yang juga berbeda.', 'trending_up', 'Growth Passport'],
                ] as $i => [$t, $d, $icon, $tag])
                    <div class="flex items-center gap-4 p-5">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-tint text-brand"><x-icon :name="$icon" /></span>
                        <div class="flex-1"><div class="font-semibold">{{ $i + 1 }}. {{ $t }}</div><div class="text-[13px] text-slate">{{ $d }}</div></div>
                        <span class="pill hidden bg-muted text-slate sm:inline-flex">{{ $tag }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- TELKOMSEL ECOSYSTEM --}}
    <section id="ekosistem" class="scroll-mt-20 bg-canvas">
        <div class="mx-auto max-w-[1200px] px-4 py-20 md:px-8">
            <div class="max-w-3xl">
                <div class="eyebrow-brand mb-2">Ekosistem Telkomsel</div>
                <h2 class="text-[30px] font-bold md:text-[36px]">Tidak perlu mencari semuanya sendiri.</h2>
                <p class="mt-3 text-[16px] leading-7 text-ink-2">Setiap bisnis punya kebutuhan yang berbeda. Karena itu, TUMBUH tidak sekadar menampilkan daftar produk Telkomsel. Platform ini membantu mengarahkan Anda ke solusi yang paling sesuai dengan kondisi bisnis saat itu.</p>
            </div>
            <div class="mt-10 grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                @foreach ([
                    ['Belajar', [['Kuncie', 'school', 'Untuk membantu Anda memahami dan mengembangkan kemampuan bisnis.']]],
                    ['Tetap terhubung', [['UMKM Plus', 'cell_tower', 'Untuk mendukung kebutuhan konektivitas bisnis.']]],
                    ['Dapatkan pelanggan', [['MyAds', 'campaign', 'Untuk membantu menjangkau pelanggan yang lebih relevan.']]],
                    ['Tumbuh lebih jauh', [['DigiAds', 'insights', 'Untuk kebutuhan pemasaran yang lebih advanced.'], ['RCS Business Messaging', 'forum', 'Untuk membangun komunikasi dan hubungan dengan pelanggan.']]],
                    ['Kelola aktivitas bisnis', [['DigiPOS', 'point_of_sale', 'Untuk kebutuhan transaksi digital yang didukung oleh layanannya.'], ['Soundbox', 'volume_up', 'Untuk mendukung pengalaman pembayaran di toko.']]],
                    ['Saat bisnis mulai berkembang', [['MSIGHT', 'query_stats', 'Untuk mendapatkan insight bisnis berbasis data.'], ['SiteSense', 'location_on', 'Untuk membantu mempertimbangkan lokasi dan peluang ekspansi.'], ['DCE', 'rocket_launch', 'Untuk jalur akselerasi dan pengembangan bisnis bagi UMKM yang memenuhi kriteria.']]],
                ] as [$group, $items])
                    <div class="card p-5">
                        <div class="eyebrow-brand mb-3">{{ $group }}</div>
                        <div class="space-y-4">
                            @foreach ($items as [$name, $icon, $desc])
                                <div class="flex gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full bg-brand-tint text-brand"><x-icon :name="$icon" size="18" /></span>
                                    <div><div class="font-semibold">{{ $name }}</div><p class="text-[13px] leading-5 text-slate">{{ $desc }}</p></div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach
            </div>
            <p class="mt-8 max-w-3xl text-[15px] leading-7 text-ink-2">Di dalam TUMBUH, semua kapabilitas tersebut ditempatkan sesuai kebutuhan. Jadi Anda tidak perlu memulai dari produk. <b class="text-ink">Anda memulai dari masalah bisnis Anda.</b></p>
        </div>
    </section>

    {{-- ABOUT THE JOURNEY --}}
    <section class="mx-auto max-w-[1200px] px-4 py-20 md:px-8">
        <div class="max-w-3xl">
            <div class="eyebrow-brand mb-2">Perjalanan bersama TUMBUH</div>
            <h2 class="text-[30px] font-bold md:text-[36px]">Karena tidak semua bisnis membutuhkan hal yang sama.</h2>
            <p class="mt-3 text-[16px] leading-7 text-ink-2">UMKM yang baru mulai digitalisasi tentu memiliki kebutuhan yang berbeda dengan bisnis yang sudah memiliki banyak pelanggan dan siap membuka cabang baru. Karena itu, perjalanan TUMBUH berkembang bersama bisnis Anda.</p>
        </div>
        <div class="mt-10 grid gap-4 md:grid-cols-3">
            @foreach ([
                ['BUILD', 'bronze', ['Pahami bisnis Anda.', 'Temukan masalahnya.', 'Bangun fondasi.']],
                ['GROW', 'silver', ['Mulai menjalankan aksi.', 'Gunakan solusi yang relevan.', 'Ukur hasilnya.']],
                ['SCALE', 'gold', ['Gunakan insight yang lebih dalam.', 'Temukan peluang baru.', 'Siapkan bisnis untuk berkembang lebih jauh.']],
            ] as [$stage, $tier, $lines])
                <div class="card p-6">
                    <div class="flex items-center justify-between"><span class="font-display text-[22px] font-extrabold tracking-[0.02em]">{{ $stage }}</span><x-tier-badge :tier="$tier" /></div>
                    <ul class="mt-4 space-y-2">
                        @foreach ($lines as $l)<li class="flex gap-2 text-[15px]"><x-icon name="check" size="18" class="text-brand" />{{ $l }}</li>@endforeach
                    </ul>
                </div>
            @endforeach
        </div>
    </section>

    {{-- BUSINESS MANAGEMENT --}}
    <section class="bg-canvas">
        <div class="mx-auto grid max-w-[1200px] gap-10 px-4 py-20 md:px-8 lg:grid-cols-[1fr_1.4fr]">
            <div>
                <div class="eyebrow-brand mb-2">Kelola bisnis</div>
                <h2 class="text-[30px] font-bold md:text-[34px]">Semua keputusan akan lebih mudah ketika Anda tahu apa yang sedang terjadi.</h2>
                <p class="mt-3 text-[16px] leading-7 text-ink-2">TUMBUH juga membantu Anda melihat hal-hal penting dalam bisnis secara lebih teratur.</p>
                <p class="mt-4 text-[14px] text-slate">Semua informasi tersebut membantu TUMBUH memberikan rekomendasi yang lebih relevan.</p>
            </div>
            <div class="grid gap-3 sm:grid-cols-2">
                @foreach ([
                    ['Penjualan', 'payments', 'Berapa omzet Anda dan bagaimana perubahannya?'],
                    ['Pelanggan', 'groups', 'Apakah jumlah pelanggan bertambah? Apakah mereka kembali?'],
                    ['Keuangan', 'account_balance_wallet', 'Berapa pemasukan, pengeluaran, dan keuntungan Anda?'],
                    ['Stok', 'inventory_2', 'Apa yang cepat terjual? Apa yang mulai menumpuk?'],
                    ['Pemasaran', 'campaign', 'Apakah aktivitas promosi benar-benar menghasilkan pelanggan?'],
                ] as [$t, $icon, $q])
                    <div class="card flex gap-3 p-5 {{ $loop->last ? 'sm:col-span-2' : '' }}">
                        <span class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-brand-tint text-brand"><x-icon :name="$icon" /></span>
                        <div><div class="font-semibold">{{ $t }}</div><p class="text-[13px] text-slate">{{ $q }}</p></div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- AI SECTION --}}
    <section class="mx-auto max-w-[1200px] px-4 py-20 md:px-8">
        <div class="grid items-center gap-10 lg:grid-cols-2">
            <div>
                <div class="eyebrow-brand mb-2">Asisten pertumbuhan</div>
                <h2 class="text-[30px] font-bold md:text-[34px]">Bukan sekadar chatbot. TUMBUH membantu Anda menentukan langkah berikutnya.</h2>
                <p class="mt-3 text-[16px] leading-7 text-ink-2">Ketika sesuatu berubah dalam bisnis, rekomendasi TUMBUH ikut berubah.</p>
                <div class="mt-5 rounded-[14px] border border-line bg-canvas p-4 text-[14px]">
                    <div class="eyebrow mb-1">Misalnya</div>
                    Penjualan meningkat, tetapi pelanggan baru mulai menurun. TUMBUH akan membantu Anda melihat perubahan tersebut dan mengarahkan perhatian ke masalah yang paling penting.
                </div>
            </div>
            <div class="space-y-3">
                <div class="rounded-[16px] border border-line bg-white p-5 text-slate">
                    <div class="eyebrow mb-2">Bukan hanya</div>
                    <p class="text-[17px] line-through decoration-slate-2">"Ini data bisnis Anda."</p>
                </div>
                <div class="rounded-[16px] border-l-4 border-l-brand bg-white p-5 shadow-[var(--shadow-card)] ring-1 ring-line">
                    <div class="eyebrow-brand mb-2 flex items-center gap-1"><x-icon name="auto_awesome" size="14" fill />Tetapi</div>
                    <ul class="space-y-2 text-[16px] font-semibold">
                        <li>"Ini yang sedang terjadi.</li>
                        <li>Ini kemungkinan penyebabnya.</li>
                        <li>Dan ini langkah berikutnya yang bisa Anda coba."</li>
                    </ul>
                </div>
            </div>
        </div>
    </section>

    {{-- MEASUREMENT --}}
    <section class="bg-canvas">
        <div class="mx-auto max-w-[1200px] px-4 py-20 md:px-8">
            <div class="max-w-2xl">
                <div class="eyebrow-brand mb-2">Pengukuran</div>
                <h2 class="text-[30px] font-bold md:text-[36px]">Karena pertumbuhan harus bisa dibuktikan.</h2>
                <p class="mt-3 text-[16px] leading-7 text-ink-2">Setiap aksi yang dijalankan harus punya tujuan yang jelas. TUMBUH membantu Anda melihat:</p>
            </div>
            <div class="mt-10 grid items-center gap-4 md:grid-cols-[1fr_auto_1fr]">
                <div class="card p-6">
                    <div class="eyebrow">Sebelum</div>
                    <div class="mt-3 space-y-2"><div><span class="metric text-[28px]">Rp20 juta</span> <span class="text-slate">omzet</span></div><div><span class="metric text-[28px]">22%</span> <span class="text-slate">repeat customer</span></div></div>
                </div>
                <div class="flex flex-col items-center gap-1 text-center">
                    <x-icon name="arrow_forward" class="hidden text-slate-2 md:block" /><x-icon name="arrow_downward" class="text-slate-2 md:hidden" />
                    <span class="pill bg-brand text-white">Aksi dijalankan</span>
                    <x-icon name="arrow_forward" class="hidden text-slate-2 md:block" /><x-icon name="arrow_downward" class="text-slate-2 md:hidden" />
                </div>
                <div class="card border-ink p-6">
                    <div class="eyebrow-brand">Sesudah</div>
                    <div class="mt-3 space-y-2"><div><span class="metric text-[28px]">Rp23 juta</span> <span class="text-slate">omzet</span></div><div><span class="metric text-[28px]">27%</span> <span class="text-slate">repeat customer</span></div></div>
                </div>
            </div>
            <p class="mt-8 max-w-2xl text-[15px] leading-7 text-ink-2">Bukan untuk menjanjikan bahwa setiap aksi pasti berhasil. Tetapi untuk membantu Anda <b class="text-ink">belajar dari hasil yang benar-benar terjadi.</b></p>
            <p class="mt-2 text-[12px] text-slate">Angka di atas merupakan contoh ilustrasi.</p>
        </div>
    </section>

    {{-- GROWTH LOOP --}}
    <section id="growth-loop" class="scroll-mt-20 mx-auto max-w-[1200px] px-4 py-20 md:px-8">
        <div class="grid items-center gap-10 lg:grid-cols-2">
            <div>
                <div class="eyebrow-brand mb-2">Growth Loop</div>
                <h2 class="text-[30px] font-bold md:text-[36px]">Bisnis tumbuh bukan dalam satu langkah.</h2>
                <div class="mt-4 space-y-1 text-[16px] leading-7 text-ink-2">
                    <p>Hari ini masalah Anda mungkin pelanggan baru.</p>
                    <p>Besok mungkin konversi.</p>
                    <p>Bulan depan bisa jadi stok atau margin.</p>
                </div>
                <p class="mt-4 text-[16px] leading-7 text-ink-2">Karena itu TUMBUH tidak berhenti setelah memberikan satu rekomendasi.</p>
            </div>
            <div class="card p-6">
                <div class="flex flex-col items-center gap-1.5">
                    @foreach (['Kenali', 'Bertindak', 'Ukur', 'Perbaiki', 'Tumbuh', 'Kenali lagi'] as $s)
                        <div class="w-full max-w-[280px] rounded-full border px-4 py-2 text-center text-[14px] font-semibold {{ $loop->last ? 'border-brand bg-brand text-white' : 'border-line bg-white' }}">{{ $s }}</div>
                        @unless ($loop->last)<x-icon name="south" size="18" class="text-slate-2" />@endunless
                    @endforeach
                </div>
                <p class="mt-5 text-center text-[15px] font-semibold">Tidak ada halaman terakhir. Ada tantangan berikutnya.</p>
            </div>
        </div>
    </section>

    {{-- CTA PENUTUP --}}
    <section class="mx-auto max-w-[1200px] px-4 pb-20 md:px-8">
        <div class="rounded-[24px] bg-brand px-6 py-14 text-center text-white md:px-12">
            <h2 class="mx-auto max-w-2xl text-[30px] font-bold text-white md:text-[36px]">Siap melihat apa yang sebenarnya menahan bisnis Anda?</h2>
            <p class="mx-auto mt-3 max-w-xl text-[16px] text-white/90">Mulai dengan memahami kondisi usaha Anda terlebih dahulu.</p>
            <div class="mx-auto mt-5 flex max-w-xl flex-wrap justify-center gap-x-6 gap-y-2 text-[14px] text-white/85">
                <span class="flex items-center gap-1.5"><x-icon name="check" size="16" />Tidak perlu langsung membeli apa pun.</span>
                <span class="flex items-center gap-1.5"><x-icon name="check" size="16" />Tidak perlu langsung menggunakan semua tools.</span>
            </div>
            <p class="mt-4 text-[16px] font-semibold text-white">Mulai dari masalah bisnis Anda.</p>
            <a href="{{ route('register') }}" class="btn mt-7 h-12 bg-white px-6 text-[14px] text-brand-deep hover:bg-brand-tint">Mulai Perjalanan Saya <x-icon name="arrow_forward" size="18" /></a>
        </div>
    </section>
</x-layouts.public>
