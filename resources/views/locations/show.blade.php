@extends('layouts.app')

@section('title', $location->name)
@section('description', strip_tags($location->intro ?? '') ?: "{$location->name} — struttura convenzionata Reciproca SMS a {$location->city}.")

@push('head')
<style>
    .show-wrap {
        max-width: 960px;
        margin: 0 auto;
        padding: 2.25rem 1.5rem 5rem;
    }

    /* ── Breadcrumb ──────────────────────────────────────────── */
    .breadcrumb {
        display: inline-flex;
        align-items: center;
        gap: .45rem;
        font-size: .88rem;
        font-weight: 700;
        color: var(--tx-3);
        margin-bottom: 2rem;
        text-decoration: none;
        transition: color var(--trans);
        border-radius: var(--r-sm);
        padding: .3rem .5rem;
        margin-left: -.5rem;
    }

    .breadcrumb:hover { color: var(--p); text-decoration: none; background: var(--p-subtle); }
    .breadcrumb svg { width: 15px; height: 15px; flex-shrink: 0; }

    /* ── Grid ────────────────────────────────────────────────── */
    .show-grid {
        display: grid;
        grid-template-columns: 1fr 300px;
        gap: 2rem;
        align-items: start;
    }

    @media (max-width: 720px) {
        .show-wrap { padding: 1.25rem 1rem 3.5rem; }
        .show-grid { grid-template-columns: 1fr; }
        .contact-sidebar { order: -1; position: static; }
    }

    /* ── Sezione intestazione ────────────────────────────────── */
    .loc-meta-badges {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-bottom: 1rem;
    }

    .loc-h1 {
        font-family: var(--f-display);
        font-size: clamp(1.5rem, 5vw, 2rem);
        font-weight: 800;
        color: var(--tx);
        line-height: 1.18;
        letter-spacing: -.03em;
        margin-bottom: 1rem;
    }

    .loc-specializations {
        display: flex;
        flex-wrap: wrap;
        gap: .4rem;
        margin-bottom: 1.75rem;
        padding-bottom: 1.75rem;
        border-bottom: 1px solid var(--border2);
    }

    /* ── Banner convenzione ──────────────────────────────────── */
    .conv-banner {
        border-radius: var(--r);
        padding: 1.1rem 1.25rem;
        margin-bottom: 1.5rem;
        border-left: 5px solid;
    }

    .conv-banner.diretta {
        background: var(--dir-bg);
        border-color: var(--dir-tx);
    }

    .conv-banner.indiretta {
        background: var(--ind-bg);
        border-color: var(--ind-tx);
    }

    .conv-title {
        font-family: var(--f-display);
        font-size: 1rem;
        font-weight: 800;
        margin-bottom: .4rem;
    }

    .conv-banner.diretta .conv-title { color: var(--dir-tx); }
    .conv-banner.indiretta .conv-title { color: var(--ind-tx); }

    .conv-body {
        font-size: .95rem;
        line-height: 1.6;
    }

    .conv-banner.diretta .conv-body { color: var(--dir-tx); }
    .conv-banner.indiretta .conv-body { color: var(--ind-tx); }

    /* ── Blocco contenuto ────────────────────────────────────── */
    .content-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r);
        padding: 1.5rem;
        margin-bottom: 1.25rem;
        box-shadow: var(--sh-sm);
    }

    .content-card-label {
        font-family: var(--f-display);
        font-size: .72rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: .09em;
        color: var(--tx-3);
        margin-bottom: .85rem;
        display: flex;
        align-items: center;
        gap: .4rem;
    }

    .content-card-label::after {
        content: '';
        flex: 1;
        height: 1px;
        background: var(--border2);
    }

    /* ── Sidebar contatti ────────────────────────────────────── */
    .contact-sidebar {
        position: sticky;
        top: calc(var(--hdr) + 1.25rem);
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .contact-card {
        background: var(--surface);
        border: 1px solid var(--border);
        border-radius: var(--r);
        overflow: hidden;
        box-shadow: var(--sh-sm);
    }

    .contact-card-header {
        background: var(--p-btn);
        padding: .9rem 1.15rem;
        display: flex;
        align-items: center;
        gap: .55rem;
    }

    .contact-card-header span {
        font-family: var(--f-display);
        font-size: .85rem;
        font-weight: 700;
        color: #ffffff;
        letter-spacing: -.01em;
    }

    .contact-card-header svg { width: 17px; height: 17px; color: rgba(255,255,255,.85); flex-shrink: 0; }

    .contact-body {
        padding: 1.1rem 1.15rem;
        display: flex;
        flex-direction: column;
        gap: .85rem;
    }

    /* Ogni riga contatto */
    .contact-row {
        display: flex;
        gap: .7rem;
        align-items: flex-start;
    }

    .contact-row svg {
        width: 17px;
        height: 17px;
        color: var(--tx-3);
        flex-shrink: 0;
        margin-top: .15rem;
    }

    .contact-text {
        font-size: .9rem;
        color: var(--tx-2);
        line-height: 1.5;
    }

    .contact-action {
        display: flex;
        gap: .7rem;
        align-items: flex-start;
        font-size: .95rem;     /* legge comodo */
        font-weight: 700;
        color: var(--p);
        text-decoration: none;
        transition: color var(--trans);
    }

    .contact-action:hover { color: var(--p-hover); text-decoration: underline; }
    .contact-action svg { width: 17px; height: 17px; flex-shrink: 0; margin-top: .15rem; }

    /* Mappa thumbnail */
    .map-thumb { width: 100%; height: 230px; display: block; background: var(--surface2); }

    /* ── Mobile: link contatti diventano bottoni full-width ── */
    @media (max-width: 720px) {
        .contact-action {
            background: var(--p-subtle);
            border: 1.5px solid var(--border);
            border-radius: var(--r-sm);
            padding: .85rem 1rem;
            min-height: 52px;
            align-items: center;
            transition: background var(--trans), border-color var(--trans);
        }
        .contact-action:active {
            background: var(--p);
            color: #fff;
            border-color: var(--p);
        }
        .contact-action:hover {
            text-decoration: none;
            background: var(--p-subtle);
        }
        .contact-action svg { margin-top: 0; }
        .breadcrumb { font-size: .95rem; min-height: 44px; align-items: center; }
    }
</style>
@endpush

@section('content')
<div class="show-wrap">

    <a href="{{ route('locations.index') }}" class="breadcrumb">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 18l-6-6 6-6"/></svg>
        Torna alla mappa
    </a>

    <div class="show-grid">

        {{-- ── COLONNA PRINCIPALE ────────────────────────────── --}}
        <div>
            {{-- Badge di stato --}}
            <div class="loc-meta-badges">
                @if($location->convention_type === 'diretta')
                    <span class="badge badge-dir">✓ Convenzione diretta</span>
                @else
                    <span class="badge badge-ind">Convenzione indiretta</span>
                @endif
                @if($location->featured)
                    <span class="badge badge-feat">★ In evidenza</span>
                @endif
            </div>

            {{-- Titolo --}}
            <h1 class="loc-h1">{{ $location->name }}</h1>

            {{-- Specializzazioni --}}
            @if($location->categories->count())
            <div class="loc-specializations">
                @foreach($location->categories as $cat)
                    <span class="badge badge-cat">{{ $cat->name }}</span>
                @endforeach
            </div>
            @endif

            {{-- Banner convenzione --}}
            <div class="conv-banner {{ $location->convention_type }}">
                @if($location->convention_type === 'diretta')
                    <p class="conv-title">Convenzione diretta</p>
                    <p class="conv-body">I soci Reciproca SMS non anticipano le spese. Il pagamento avviene direttamente tra la struttura e la mutua, senza alcun esborso da parte del socio.</p>
                @else
                    <p class="conv-title">Convenzione indiretta</p>
                    <p class="conv-body">Il socio paga la prestazione e riceve il rimborso presentando la documentazione di spesa a Reciproca SMS nei tempi previsti dal regolamento.</p>
                @endif
            </div>

            {{-- Descrizione --}}
            @if($location->intro)
            <div class="content-card">
                <p class="content-card-label">Descrizione</p>
                <div class="prose">{!! $location->intro !!}</div>
            </div>
            @endif

            {{-- Orari e tariffe --}}
            @if($location->hours_prices)
            <div class="content-card">
                <p class="content-card-label">Orari e tariffe</p>
                <div class="prose">{!! $location->hours_prices !!}</div>
            </div>
            @endif
        </div>

        {{-- ── SIDEBAR CONTATTI ──────────────────────────────── --}}
        <aside class="contact-sidebar" aria-label="Contatti e mappa">

            <div class="contact-card">
                <div class="contact-card-header">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <span>Contatti e indirizzo</span>
                </div>

                <div class="contact-body">

                    {{-- Indirizzo --}}
                    <div class="contact-row">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/><polyline points="9 22 9 12 15 12 15 22"/></svg>
                        <div class="contact-text">
                            {{ $location->address }}
                            @if($location->address2)<br>{{ $location->address2 }}@endif
                            <br>{{ $location->postal_code }} {{ $location->city }} ({{ $location->province }})
                        </div>
                    </div>

                    @if($location->phone)
                    <a href="tel:{{ $location->phone }}" class="contact-action">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8a19.79 19.79 0 01-3.07-8.7A2 2 0 012 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92v2z"/></svg>
                        {{ $location->phone }}
                    </a>
                    @endif

                    @if($location->email)
                    <a href="mailto:{{ $location->email }}" class="contact-action">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M4 4h16c1.1 0 2 .9 2 2v12c0 1.1-.9 2-2 2H4c-1.1 0-2-.9-2-2V6c0-1.1.9-2 2-2z"/><polyline points="22,6 12,13 2,6"/></svg>
                        {{ $location->email }}
                    </a>
                    @endif

                    @if($location->website)
                    <a href="{{ $location->website }}" target="_blank" rel="noopener noreferrer" class="contact-action">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="10"/><path d="M2 12h20M12 2a15.3 15.3 0 014 10 15.3 15.3 0 01-4 10 15.3 15.3 0 01-4-10 15.3 15.3 0 014-10z"/></svg>
                        Sito web
                    </a>
                    @endif

                </div>

                @if($location->lat && $location->lng)
                <div id="detail-map" class="map-thumb" role="img" aria-label="Posizione su mappa di {{ $location->name }}"></div>
                @endif
            </div>

        </aside>
    </div>
</div>
@endsection

@push('scripts')
@if($location->lat && $location->lng)
<script>
function initDetailMap() {
    const pos = { lat: {{ $location->lat }}, lng: {{ $location->lng }} };
    const dark = document.getElementById('root').getAttribute('data-theme') === 'dark'
        || (localStorage.getItem('theme') === null && window.matchMedia('(prefers-color-scheme: dark)').matches);

    const map = new google.maps.Map(document.getElementById('detail-map'), {
        center: pos,
        zoom: {{ $location->zoom ?? 15 }},
        mapTypeControl: false,
        streetViewControl: false,
        fullscreenControl: false,
        zoomControl: false,
        gestureHandling: 'none',
        styles: dark ? [
            { elementType:'geometry',            stylers:[{color:'#1a2b1f'}] },
            { elementType:'labels.text.stroke',  stylers:[{color:'#0b1610'}] },
            { elementType:'labels.text.fill',    stylers:[{color:'#72c98a'}] },
            { featureType:'road', elementType:'geometry', stylers:[{color:'#253c2c'}] },
            { featureType:'water', elementType:'geometry', stylers:[{color:'#081009'}] },
            { featureType:'poi',  elementType:'geometry', stylers:[{color:'#162b1d'}] },
        ] : [],
    });

    new google.maps.Marker({
        position: pos,
        map,
        title: @json($location->name),
        icon: {
            path: google.maps.SymbolPath.CIRCLE,
            fillColor: '#2f5c3d',
            fillOpacity: 1,
            strokeColor: '#ffffff',
            strokeWeight: 3,
            scale: 10,
        },
    });
}
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ env('GOOGLE_MAPS_PUBLIC_KEY') }}&callback=initDetailMap" async defer></script>
@endif
@endpush
