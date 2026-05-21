@php
    use Botble\Base\Enums\BaseStatusEnum;
    use Botble\Hotel\Models\Room;
    use Botble\Media\Facades\RvMedia;
    use Illuminate\Support\Arr;

    $rooms = Room::query()
        ->where('status', BaseStatusEnum::PUBLISHED)
        ->with('category')
        ->orderBy('order')
        ->latest('id')
        ->take(6)
        ->get();

    $siteName = theme_option('site_name') ?: setting('site_title') ?: config('app.name');
    $heroDescription = $page?->description ?: theme_option('seo_description') ?: 'Pilih bilik, semak harga, dan tempah homestay anda dengan lebih mudah.';
    $roomsUrl = \Illuminate\Support\Facades\Route::has('public.rooms') ? route('public.rooms') : url('/');
    $phoneNumber = theme_option('hotline');
    $email = theme_option('email');
    $address = theme_option('address');

    $paymentMethods = collect([
        'cod' => 'Bayar Semasa Check-in',
        'bank_transfer' => 'Bank Transfer',
        'paypal' => 'PayPal',
        'stripe' => 'Stripe',
        'razorpay' => 'Razorpay',
        'paystack' => 'Paystack',
        'sslcommerz' => 'SSLCommerz',
    ])->filter(fn ($label, $method) => (int) get_payment_setting('status', $method) === 1)
        ->map(fn ($label, $method) => get_payment_setting('name', $method, $label))
        ->values();
@endphp

<style>
    .homestay-home {
        background: #f7f5f1;
    }

    .homestay-home .hero {
        background: linear-gradient(135deg, rgba(46, 25, 19, 0.95), rgba(100, 66, 34, 0.88));
        color: #fff;
        padding: 110px 0 80px;
    }

    .homestay-home .hero-badge,
    .homestay-home .method-badge,
    .homestay-home .info-badge {
        background: rgba(255, 255, 255, 0.14);
        border-radius: 999px;
        display: inline-block;
        font-size: 13px;
        letter-spacing: 0.08em;
        padding: 8px 16px;
        text-transform: uppercase;
    }

    .homestay-home .hero h1 {
        color: #fff;
        font-size: 56px;
        line-height: 1.15;
        margin: 20px 0;
    }

    .homestay-home .hero p {
        color: rgba(255, 255, 255, 0.85);
        font-size: 18px;
        margin-bottom: 30px;
        max-width: 680px;
    }

    .homestay-home .hero-actions .btn,
    .homestay-home .room-card .btn {
        min-width: 180px;
    }

    .homestay-home .hero-summary,
    .homestay-home .quick-box,
    .homestay-home .room-card,
    .homestay-home .contact-card {
        background: #fff;
        border-radius: 18px;
        box-shadow: 0 18px 45px rgba(46, 25, 19, 0.1);
    }

    .homestay-home .hero-summary {
        color: #2e1913;
        padding: 32px;
    }

    .homestay-home .hero-summary h4,
    .homestay-home .contact-card h4 {
        margin-bottom: 15px;
    }

    .homestay-home .section-wrap {
        padding: 70px 0;
    }

    .homestay-home .section-head {
        margin-bottom: 30px;
    }

    .homestay-home .section-head p {
        margin-bottom: 0;
        max-width: 680px;
    }

    .homestay-home .quick-box,
    .homestay-home .contact-card {
        height: 100%;
        padding: 28px;
    }

    .homestay-home .quick-box h5,
    .homestay-home .room-card h5 {
        margin-bottom: 12px;
    }

    .homestay-home .room-card {
        height: 100%;
        overflow: hidden;
    }

    .homestay-home .room-card img {
        display: block;
        height: 240px;
        object-fit: cover;
        width: 100%;
    }

    .homestay-home .room-card-content {
        padding: 24px;
    }

    .homestay-home .room-meta,
    .homestay-home .room-extra {
        align-items: center;
        display: flex;
        gap: 12px;
        justify-content: space-between;
    }

    .homestay-home .room-meta {
        margin-bottom: 14px;
    }

    .homestay-home .room-category {
        color: #644222;
        font-size: 14px;
        font-weight: 600;
        letter-spacing: 0.08em;
        text-transform: uppercase;
    }

    .homestay-home .room-price {
        color: #2e1913;
        font-size: 20px;
        font-weight: 700;
    }

    .homestay-home .room-description {
        min-height: 72px;
    }

    .homestay-home .room-extra {
        color: #6d5b52;
        font-size: 14px;
        margin: 18px 0 24px;
    }

    .homestay-home .method-list {
        display: flex;
        flex-wrap: wrap;
        gap: 10px;
        margin-top: 18px;
    }

    .homestay-home .method-badge {
        background: #f2ebe5;
        color: #644222;
        letter-spacing: normal;
        text-transform: none;
    }

    .homestay-home .contact-list {
        display: grid;
        gap: 16px;
        margin-top: 18px;
    }

    .homestay-home .contact-item strong {
        color: #2e1913;
        display: block;
        margin-bottom: 4px;
    }

    @media (max-width: 991px) {
        .homestay-home .hero {
            padding: 90px 0 60px;
        }

        .homestay-home .hero h1 {
            font-size: 40px;
        }

        .homestay-home .hero-summary {
            margin-top: 30px;
        }
    }

    @media (max-width: 575px) {
        .homestay-home .hero h1 {
            font-size: 32px;
        }

        .homestay-home .hero-actions .btn,
        .homestay-home .room-card .btn {
            min-width: auto;
            width: 100%;
        }

        .homestay-home .room-meta,
        .homestay-home .room-extra {
            align-items: flex-start;
            flex-direction: column;
        }
    }
</style>

<div class="homestay-home">
    <section class="hero">
        <div class="container">
            <div class="row align-items-center gy-4">
                <div class="col-lg-7">
                    <span class="hero-badge">Homestay Booking</span>
                    <h1>{{ $siteName }}</h1>
                    <p>{{ $heroDescription }}</p>

                    <div class="hero-actions d-flex flex-wrap gap-3">
                        <a href="{{ $roomsUrl }}" class="btn ss-btn">Lihat Bilik</a>
                        @if ($phoneNumber)
                            <a href="tel:{{ preg_replace('/[^0-9+]/', '', $phoneNumber) }}" class="btn ss-btn">Hubungi Kami</a>
                        @endif
                    </div>
                </div>

                <div class="col-lg-5">
                    <div class="hero-summary">
                        <span class="info-badge">Ringkas dan mudah</span>
                        <h4 class="mt-3">Sesuai untuk sistem homestay yang lebih kemas</h4>
                        <p class="mb-3">Homepage ini terus fokus pada bilik, harga, gambar, kaedah bayaran, dan maklumat asas laman.</p>
                        <ul class="mb-0">
                            <li>Papar senarai bilik terus dari modul bilik sedia ada.</li>
                            <li>Harga dan gambar bilik dikemaskini dari panel admin.</li>
                            <li>Kaedah pembayaran ikut gateway yang diaktifkan.</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-wrap">
        <div class="container">
            <div class="section-head">
                <span class="hero-badge" style="background:#efe2d6;color:#644222;">Cara Tempahan</span>
                <h2 class="mt-3">Aliran booking yang terus jelas</h2>
                <p>Pelanggan hanya perlu pilih bilik, semak harga, dan teruskan pembayaran menggunakan kaedah yang anda aktifkan.</p>
            </div>

            <div class="row g-4">
                <div class="col-lg-4 col-md-6">
                    <div class="quick-box">
                        <h5>1. Pilih bilik</h5>
                        <p class="mb-0">Bilik dipaparkan bersama gambar, kapasiti, dan harga supaya pelanggan terus nampak pilihan yang ada.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="quick-box">
                        <h5>2. Isi maklumat tempahan</h5>
                        <p class="mb-0">Pelanggan pilih tarikh, jumlah tetamu, dan isi maklumat asas untuk meneruskan proses booking.</p>
                    </div>
                </div>
                <div class="col-lg-4 col-md-6">
                    <div class="quick-box">
                        <h5>3. Buat pembayaran</h5>
                        <p class="mb-0">Tempahan boleh dipadankan dengan payment gateway atau kaedah manual seperti bank transfer dan bayar semasa check-in.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section-wrap pt-0">
        <div class="container">
            <div class="section-head">
                <span class="hero-badge" style="background:#efe2d6;color:#644222;">Senarai Bilik</span>
                <h2 class="mt-3">Bilik yang tersedia untuk ditempah</h2>
                <p>Semua bilik di bawah datang terus daripada data modul hotel, jadi bila anda tambah atau ubah bilik di admin, homepage ini ikut berubah.</p>
            </div>

            <div class="row g-4">
                @forelse ($rooms as $room)
                    <div class="col-lg-4 col-md-6">
                        <div class="room-card">
                            <a href="{{ $room->url }}">
                                <img src="{{ RvMedia::getImageUrl($room->image ?: Arr::first($room->images), 'medium', false, RvMedia::getDefaultImage()) }}" alt="{{ $room->name }}">
                            </a>

                            <div class="room-card-content">
                                <div class="room-meta">
                                    <span class="room-category">{{ $room->category->name ?: 'Bilik' }}</span>
                                    <span class="room-price">{{ format_price($room->price) }}</span>
                                </div>

                                <h5><a href="{{ $room->url }}">{{ $room->name }}</a></h5>
                                <p class="room-description">{{ $room->description ?: 'Maklumat bilik boleh dikemaskini terus dari panel admin.' }}</p>

                                <div class="room-extra">
                                    <span>{{ $room->number_of_rooms ?: 1 }} unit tersedia</span>
                                    <span>Sehingga {{ $room->max_adults ?: 1 }} tetamu</span>
                                </div>

                                <a href="{{ $room->url }}" class="btn ss-btn">Semak Bilik</a>
                            </div>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <div class="quick-box">
                            <h5>Belum ada bilik dipaparkan</h5>
                            <p class="mb-0">Tambah bilik di panel admin untuk paparkan harga, gambar, dan pautan tempahan di homepage ini.</p>
                        </div>
                    </div>
                @endforelse
            </div>
        </div>
    </section>

    <section class="section-wrap pt-0">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-6">
                    <div class="contact-card">
                        <span class="hero-badge" style="background:#efe2d6;color:#644222;">Payment</span>
                        <h4 class="mt-3">Kaedah bayaran</h4>
                        <p class="mb-0">Pilihan pembayaran di bawah ikut gateway yang sedang aktif dalam panel admin.</p>

                        <div class="method-list">
                            @forelse ($paymentMethods as $paymentMethod)
                                <span class="method-badge">{{ $paymentMethod }}</span>
                            @empty
                                <span class="method-badge">Aktifkan payment method dari panel admin</span>
                            @endforelse
                        </div>
                    </div>
                </div>

                <div class="col-lg-6">
                    <div class="contact-card">
                        <span class="hero-badge" style="background:#efe2d6;color:#644222;">Site Setting</span>
                        <h4 class="mt-3">Maklumat homestay</h4>
                        <p class="mb-0">Telefon, email, dan alamat ini diambil daripada tetapan tema supaya mudah diubah tanpa sentuh kod.</p>

                        <div class="contact-list">
                            @if ($phoneNumber)
                                <div class="contact-item">
                                    <strong>Telefon</strong>
                                    <span>{{ $phoneNumber }}</span>
                                </div>
                            @endif

                            @if ($email)
                                <div class="contact-item">
                                    <strong>Email</strong>
                                    <span>{{ $email }}</span>
                                </div>
                            @endif

                            @if ($address)
                                <div class="contact-item">
                                    <strong>Alamat</strong>
                                    <span>{{ $address }}</span>
                                </div>
                            @endif

                            @if (! $phoneNumber && ! $email && ! $address)
                                <div class="contact-item">
                                    <strong>Maklumat belum diisi</strong>
                                    <span>Kemaskini nombor telefon, email, dan alamat melalui menu theme options atau site setting.</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>
