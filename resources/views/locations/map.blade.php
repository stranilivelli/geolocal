@extends('layouts.app')

@section('title', 'Strutture sanitarie convenzionate')
@section('description', 'Trova le strutture sanitarie convenzionate con Reciproca SMS vicino a te.')
@section('no-footer')

@push('head')
<style>
/* ── Layout base ─────────────────────────────────────────── */
body { overflow: hidden; }

.map-wrap {
    display: flex;
    height: calc(100dvh - var(--hdr));
    position: relative;
    overflow: hidden;
}

/* ════════════════════════════════════════════════════════════
   DESKTOP (≥ 768px) — sidebar fissa + mappa
   ════════════════════════════════════════════════════════════ */
.sidebar {
    width: var(--sidebar);
    flex-shrink: 0;
    background: var(--surface);
    border-right: 2px solid var(--border);
    display: flex;
    flex-direction: column;
    overflow: hidden;
    z-index: 10;
}

.sidebar-head {
    padding: 1.1rem 1.25rem .85rem;
    border-bottom: 1px solid var(--border2);
    background: var(--surface2);
    flex-shrink: 0;
}

.sidebar-title {
    font-family: var(--f-display);
    font-weight: 800;
    font-size: 1.05rem;
    color: var(--tx);
    letter-spacing: -.02em;
    line-height: 1.2;
}

.sidebar-sub {
    font-size: .8rem;
    color: var(--tx-3);
    margin-top: .2rem;
}

/* ── Filtri ──────────────────────────────────────────────── */
.filters {
    padding: .85rem 1.25rem;
    border-bottom: 1px solid var(--border2);
    display: flex;
    flex-direction: column;
    gap: .6rem;
    flex-shrink: 0;
}

.filter-label {
    font-size: .7rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .09em;
    color: var(--tx-3);
    margin-bottom: .15rem;
}

.filter-group { display: flex; flex-direction: column; gap: .18rem; }

.search-wrap { position: relative; }
.search-wrap svg {
    position: absolute;
    left: .85rem;
    top: 50%;
    transform: translateY(-50%);
    width: 17px; height: 17px;
    color: var(--tx-4);
    pointer-events: none;
}

.search-input,
.filter-select {
    width: 100%;
    font-family: var(--f-body);
    font-size: .95rem;
    color: var(--tx);
    background: var(--surface2);
    border: 1.5px solid var(--border);
    border-radius: var(--r-sm);
    padding: .6rem .9rem;
    outline: none;
    transition: border-color var(--trans), box-shadow var(--trans);
    -webkit-appearance: none;
    appearance: none;
}

.search-input { padding-left: 2.6rem; }
.search-input::placeholder { color: var(--tx-4); }
.search-input:focus,
.filter-select:focus {
    border-color: var(--p);
    box-shadow: 0 0 0 3px var(--p-ring);
    background: var(--surface);
}

.filter-select {
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%236a8070' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpath d='M6 9l6 6 6-6'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right .85rem center;
    padding-right: 2.5rem;
    cursor: pointer;
}

/* Toggle */
.toggle-row {
    display: flex; align-items: center; gap: .7rem;
    cursor: pointer; user-select: none; padding: .1rem 0;
}

.toggle-track {
    position: relative; width: 42px; height: 24px; flex-shrink: 0;
}

.toggle-track input { position: absolute; opacity: 0; width: 0; height: 0; }

.toggle-bg {
    position: absolute; inset: 0;
    border-radius: var(--r-pill);
    background: var(--border);
    transition: background var(--trans);
}

.toggle-track input:checked + .toggle-bg { background: var(--p-btn); }

.toggle-thumb {
    position: absolute;
    top: 3px; left: 3px; width: 18px; height: 18px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 4px rgba(0,0,0,.25);
    transition: transform var(--trans);
    pointer-events: none;
}

.toggle-track input:checked ~ .toggle-thumb { transform: translateX(18px); }
.toggle-label { font-size: .9rem; font-weight: 600; color: var(--tx-2); }

/* ── Result bar ──────────────────────────────────────────── */
.result-bar {
    padding: .5rem 1.25rem;
    display: flex; align-items: center; gap: .55rem;
    border-bottom: 1px solid var(--border2);
    background: var(--surface2);
    flex-shrink: 0;
}

.result-count {
    font-size: .75rem; font-weight: 800;
    color: var(--tx-3);
    text-transform: uppercase; letter-spacing: .06em;
}

.loading-dot {
    width: 7px; height: 7px; border-radius: 50%;
    background: var(--p);
    animation: blink 1s ease-in-out infinite;
}

@keyframes blink { 0%,100%{opacity:.2} 50%{opacity:1} }

/* ── Lista strutture ─────────────────────────────────────── */
.loc-list {
    flex: 1;
    overflow-y: auto;
    overscroll-behavior: contain;
    -webkit-overflow-scrolling: touch;
}

.loc-list::-webkit-scrollbar { width: 4px; }
.loc-list::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

.loc-card {
    padding: 1rem 1.25rem;
    border-bottom: 1px solid var(--border2);
    cursor: pointer;
    transition: background var(--trans);
    -webkit-tap-highlight-color: transparent;
}

.loc-card:active { background: var(--surface2); }

@media (hover: hover) {
    .loc-card:hover { background: var(--surface2); }
}

.loc-card.is-active {
    background: var(--p-subtle);
    border-left: 4px solid var(--p);
    padding-left: calc(1.25rem - 4px);
}

.loc-name {
    font-family: var(--f-display);
    font-size: 1rem;
    font-weight: 700;
    color: var(--tx);
    line-height: 1.3;
    letter-spacing: -.01em;
    margin-bottom: .3rem;
}

.loc-card.is-active .loc-name { color: var(--p); }

.loc-addr {
    font-size: .88rem;
    color: var(--tx-3);
    margin-bottom: .55rem;
    line-height: 1.4;
}

.loc-phone {
    display: inline-flex; align-items: center; gap: .35rem;
    font-size: .9rem; font-weight: 700;
    color: var(--p);
    margin-bottom: .6rem;
    text-decoration: none;
    min-height: 36px;
    transition: color var(--trans);
}

.loc-phone:hover { color: var(--p-hover); }
.loc-phone svg { width: 14px; height: 14px; flex-shrink: 0; }

.loc-badges { display: flex; flex-wrap: wrap; gap: .3rem; }

/* Stato vuoto */
.empty-state {
    padding: 3rem 1.5rem; text-align: center; color: var(--tx-3);
}

.empty-state svg { width: 40px; height: 40px; margin: 0 auto .75rem; display: block; opacity: .3; }
.empty-state p { font-size: .95rem; line-height: 1.55; }

/* ── Mappa ───────────────────────────────────────────────── */
.map-panel {
    flex: 1; position: relative; background: var(--surface2);
}

#google-map { width: 100%; height: 100%; }

.map-placeholder {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    gap: .8rem; color: var(--tx-3); font-size: .95rem;
}

.map-placeholder svg {
    width: 40px; height: 40px; opacity: .3;
    animation: float .9s ease-in-out infinite alternate;
}

@keyframes float { from{transform:translateY(0)} to{transform:translateY(-7px)} }

/* ════════════════════════════════════════════════════════════
   COMPONENTI MOBILE — nascosti su desktop
   ════════════════════════════════════════════════════════════ */

/* Barra ricerca rapida (solo mobile) */
.mobile-search-bar { display: none; }

/* Bottom tab bar (solo mobile) */
.mobile-tabbar { display: none; }

/* Drawer filtri (solo mobile) */
.filter-drawer {
    display: none;
    position: fixed;
    inset: 0;
    z-index: 300;
}

.filter-drawer-overlay {
    position: absolute; inset: 0;
    background: rgba(0,0,0,.45);
}

.filter-drawer-panel {
    position: absolute;
    bottom: 0; left: 0; right: 0;
    background: var(--surface);
    border-radius: var(--r) var(--r) 0 0;
    padding: 1.25rem 1.25rem calc(1.25rem + env(safe-area-inset-bottom));
    box-shadow: var(--sh-lg);
    max-height: 80dvh;
    overflow-y: auto;
}

.drawer-handle {
    width: 40px; height: 4px;
    background: var(--border);
    border-radius: 2px;
    margin: 0 auto 1.25rem;
}

.drawer-title {
    font-family: var(--f-display);
    font-size: 1rem; font-weight: 700;
    color: var(--tx);
    margin-bottom: 1rem;
}

.drawer-filters { display: flex; flex-direction: column; gap: .75rem; }

.btn-apply {
    width: 100%;
    margin-top: .5rem;
    padding: .85rem;
    background: var(--p-btn);
    color: #fff;
    font-family: var(--f-display);
    font-size: .95rem; font-weight: 700;
    border: none; border-radius: var(--r-sm);
    cursor: pointer;
    transition: background var(--trans);
}

.btn-apply:active { background: var(--p-hover); }

/* Bottom card marker selezionato (solo mobile) */
.mobile-bottom-card {
    display: none;
    position: fixed;
    bottom: 56px;
    left: 0; right: 0;
    z-index: 200;
    padding: 0 .75rem calc(.75rem + env(safe-area-inset-bottom));
}

/* Transizioni Alpine per bottom card */
.mbc-in         { transition: opacity .2s ease, transform .2s ease; }
.mbc-in-from    { opacity: 0; transform: translateY(12px); }
.mbc-in-to      { opacity: 1; transform: translateY(0); }
.mbc-out        { transition: opacity .15s ease, transform .15s ease; }
.mbc-out-from   { opacity: 1; transform: translateY(0); }
.mbc-out-to     { opacity: 0; transform: translateY(12px); }

.mbc-inner {
    background: var(--surface);
    border-radius: var(--r);
    box-shadow: var(--sh-lg);
    overflow: hidden;
    border: 1px solid var(--border);
}

.mbc-body { padding: 1rem 1.15rem .9rem; }

.mbc-name {
    font-family: var(--f-display);
    font-size: 1rem; font-weight: 700;
    color: var(--tx);
    line-height: 1.25;
    margin-bottom: .25rem;
}

.mbc-addr {
    font-size: .85rem; color: var(--tx-3); margin-bottom: .7rem;
}

.mbc-badges { display: flex; flex-wrap: wrap; gap: .3rem; margin-bottom: .85rem; }

.mbc-actions {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: .6rem;
}

.mbc-btn {
    display: flex; align-items: center; justify-content: center; gap: .4rem;
    padding: .75rem .5rem;
    border-radius: var(--r-sm);
    font-family: var(--f-display);
    font-size: .88rem; font-weight: 700;
    text-decoration: none;
    min-height: 48px;
    border: none; cursor: pointer;
    transition: background var(--trans), color var(--trans);
    -webkit-tap-highlight-color: transparent;
}

.mbc-btn-phone {
    background: var(--p-subtle);
    color: var(--p);
    border: 1.5px solid var(--p);
}

.mbc-btn-detail {
    background: var(--p-btn);
    color: #fff;
}

.mbc-btn-phone:active  { background: var(--p); color: #fff; }
.mbc-btn-detail:active { background: var(--p-hover); }

.mbc-close {
    position: absolute; top: .75rem; right: .75rem;
    width: 28px; height: 28px;
    border-radius: 50%;
    background: var(--surface2);
    border: 1px solid var(--border);
    display: flex; align-items: center; justify-content: center;
    cursor: pointer; color: var(--tx-3);
    -webkit-tap-highlight-color: transparent;
}

/* ════════════════════════════════════════════════════════════
   MOBILE ≤ 767px
   ════════════════════════════════════════════════════════════ */
@media (max-width: 767px) {

    .map-wrap {
        height: calc(100dvh - var(--hdr) - 56px); /* 56px = tabbar */
        flex-direction: column;
    }

    /* Sidebar = schermata Lista */
    .sidebar {
        position: absolute;
        inset: 0;
        width: 100%;
        border-right: none;
        z-index: 10;
        transition: opacity var(--trans), visibility var(--trans);
    }

    /* Nasconde la testata sidebar (title/sub) su mobile — il titolo è nel tabbar */
    .sidebar-head { display: none; }

    /* Nasconde i filtri desktop dalla sidebar — ci sono nel drawer */
    .sidebar .filters { display: none; }

    /* Mappa = schermata Mappa */
    .map-panel {
        position: absolute;
        inset: 0;
        z-index: 5;
        transition: opacity var(--trans), visibility var(--trans);
    }

    /* Vista MAPPA: sidebar nascosta, mappa visibile */
    [data-view="map"] .sidebar {
        opacity: 0; visibility: hidden; pointer-events: none;
    }

    /* Vista LISTA: mappa nascosta, sidebar visibile */
    [data-view="list"] .map-panel {
        opacity: 0; visibility: hidden; pointer-events: none;
    }

    /* ── Mobile: barra ricerca rapida (sopra lista/mappa) ── */
    .mobile-search-bar {
        display: flex;
        position: absolute;
        top: .65rem;
        left: .65rem; right: .65rem;
        z-index: 20;
        gap: .5rem;
    }

    .mobile-search-bar .search-wrap { flex: 1; }

    .mobile-search-bar .search-input {
        background: var(--surface);
        box-shadow: var(--sh);
        border-color: var(--border);
        font-size: 1rem;
        padding: .7rem 1rem .7rem 2.6rem;
    }

    .btn-filter-mobile {
        flex-shrink: 0;
        display: flex; align-items: center; justify-content: center; gap: .35rem;
        padding: 0 .9rem;
        background: var(--surface);
        border: 1.5px solid var(--border);
        border-radius: var(--r-sm);
        font-family: var(--f-display);
        font-size: .85rem; font-weight: 700;
        color: var(--tx-2);
        cursor: pointer;
        white-space: nowrap;
        box-shadow: var(--sh);
        -webkit-tap-highlight-color: transparent;
        transition: background var(--trans), color var(--trans), border-color var(--trans);
        min-height: 44px;
    }

    .btn-filter-mobile:active,
    .btn-filter-mobile.has-filters {
        background: var(--p-subtle);
        color: var(--p);
        border-color: var(--p);
    }

    .btn-filter-mobile svg { width: 16px; height: 16px; }

    /* Indicatore filtri attivi */
    .filter-dot {
        width: 8px; height: 8px;
        border-radius: 50%;
        background: var(--p);
        flex-shrink: 0;
    }

    /* ── Drawer filtri ── */
    .filter-drawer { display: block; }

    /* ── Bottom tab bar ── */
    .mobile-tabbar {
        display: flex;
        position: fixed;
        bottom: 0; left: 0; right: 0;
        height: calc(56px + env(safe-area-inset-bottom));
        padding-bottom: env(safe-area-inset-bottom);
        background: var(--surface);
        border-top: 1.5px solid var(--border);
        z-index: 150;
        box-shadow: 0 -4px 16px rgba(0,0,0,.1);
    }

    .tab-btn {
        flex: 1;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        gap: .25rem;
        background: none; border: none;
        font-family: var(--f-body);
        font-size: .72rem; font-weight: 700;
        color: var(--tx-3);
        cursor: pointer;
        -webkit-tap-highlight-color: transparent;
        transition: color var(--trans);
        padding: 0;
    }

    .tab-btn svg { width: 22px; height: 22px; }

    .tab-btn.is-active { color: var(--p); }

    .tab-btn.is-active svg { stroke: var(--p); }

    /* Count badge nel tab Lista */
    .tab-count {
        background: var(--p);
        color: #fff;
        border-radius: var(--r-pill);
        font-size: .65rem; font-weight: 800;
        padding: .1rem .45rem;
        margin-left: .2rem;
        line-height: 1.4;
    }

    /* ── Bottom card ── */
    .mobile-bottom-card { display: block; }

    /* ── Card lista mobile più compatta ── */
    .loc-card { padding: .85rem 1rem; }
    .loc-name { font-size: .95rem; }
    .loc-addr { font-size: .82rem; margin-bottom: .45rem; }
    .loc-phone { font-size: .88rem; margin-bottom: .5rem; }

    /* Result bar visibile nella lista mobile */
    .result-bar { padding: .45rem 1rem; }
}
</style>
@endpush

@section('content')
<div
    class="map-wrap"
    x-data="mapApp()"
    x-init="init()"
    :data-view="mobileView"
    x-cloak
>

    {{-- ══ BARRA RICERCA RAPIDA (mobile, fluttua su mappa e lista) ══ --}}
    <div class="mobile-search-bar">
        <div class="search-wrap">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
            <input
                type="search"
                class="search-input"
                placeholder="Cerca struttura o città…"
                x-model.debounce.350ms="filters.search"
                @input="applyFilters()"
                aria-label="Cerca struttura o città"
            >
        </div>
        <button
            class="btn-filter-mobile"
            :class="{ 'has-filters': hasActiveFilters() }"
            @click="showFilterDrawer = true"
            aria-label="Apri filtri"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="4" y1="6" x2="20" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="11" y1="18" x2="13" y2="18"/></svg>
            Filtri
            <template x-if="hasActiveFilters()"><span class="filter-dot"></span></template>
        </button>
    </div>

    {{-- ══ SIDEBAR / LISTA ══ --}}
    <aside class="sidebar" aria-label="Lista strutture">

        <div class="sidebar-head">
            <p class="sidebar-title">Strutture convenzionate</p>
            <p class="sidebar-sub">Reciproca SMS · Mutuo soccorso toscano</p>
        </div>

        {{-- Filtri desktop --}}
        <div class="filters">
            <div class="filter-group">
                <p class="filter-label">Cerca</p>
                <div class="search-wrap">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg>
                    <input
                        type="search"
                        class="search-input"
                        placeholder="Nome o città…"
                        x-model.debounce.350ms="filters.search"
                        @input="applyFilters()"
                        aria-label="Cerca struttura o città"
                    >
                </div>
            </div>
            <div class="filter-group">
                <p class="filter-label">Provincia</p>
                <select class="filter-select" x-model="filters.province" @change="applyFilters()" aria-label="Filtra per provincia">
                    <option value="">Tutte le province</option>
                    <template x-for="p in provinces" :key="p">
                        <option :value="p" x-text="p"></option>
                    </template>
                </select>
            </div>
            <div class="filter-group">
                <p class="filter-label">Specializzazione</p>
                <select class="filter-select" x-model="filters.category_id" @change="applyFilters()" aria-label="Filtra per specializzazione">
                    <option value="">Tutte le specializzazioni</option>
                    <template x-for="cat in categories" :key="cat.id">
                        <option :value="cat.id" x-text="cat.name + ' (' + cat.locations_count + ')'"></option>
                    </template>
                </select>
            </div>
            <label class="toggle-row">
                <span class="toggle-track">
                    <input type="checkbox" x-model="filters.directOnly" @change="applyFilters()">
                    <span class="toggle-bg"></span>
                    <span class="toggle-thumb"></span>
                </span>
                <span class="toggle-label">Solo convenzione diretta</span>
            </label>
        </div>

        <div class="result-bar" aria-live="polite">
            <span class="result-count" x-text="filteredLocations.length + ' strutture'"></span>
            <template x-if="loading"><span class="loading-dot" aria-hidden="true"></span></template>
        </div>

        <div class="loc-list" role="list">
            <template x-if="!loading && filteredLocations.length === 0">
                <div class="empty-state" role="status">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    <p>Nessuna struttura trovata.<br>Prova a modificare i filtri.</p>
                </div>
            </template>

            <template x-for="loc in filteredLocations" :key="loc.id">
                <div
                    class="loc-card"
                    :class="{ 'is-active': selectedId === loc.id }"
                    @click="selectLocation(loc)"
                    role="listitem"
                    tabindex="0"
                    @keydown.enter="selectLocation(loc)"
                >
                    <p class="loc-name" x-text="loc.name"></p>
                    <p class="loc-addr" x-text="[loc.address, loc.city, loc.province].filter(Boolean).join(' · ')"></p>
                    <template x-if="loc.phone">
                        <a :href="'tel:' + loc.phone" class="loc-phone" @click.stop>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8a19.79 19.79 0 01-3.07-8.7A2 2 0 012 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92v2z"/></svg>
                            <span x-text="loc.phone"></span>
                        </a>
                    </template>
                    <div class="loc-badges">
                        <template x-for="cat in (loc.categories||[]).slice(0,2)" :key="cat.id">
                            <span class="badge badge-cat" x-text="cat.name"></span>
                        </template>
                        <template x-if="(loc.categories||[]).length > 2">
                            <span class="badge badge-muted" x-text="'+' + ((loc.categories||[]).length - 2)"></span>
                        </template>
                        <span
                            class="badge"
                            :class="loc.convention_type === 'diretta' ? 'badge-dir' : 'badge-ind'"
                            x-text="loc.convention_type === 'diretta' ? '✓ Diretta' : 'Indiretta'"
                        ></span>
                    </div>
                </div>
            </template>
        </div>
    </aside>

    {{-- ══ MAPPA ══ --}}
    <div class="map-panel" role="region" aria-label="Mappa strutture">
        <div id="google-map"></div>
        <template x-if="!googleMapsLoaded">
            <div class="map-placeholder">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
                Caricamento mappa…
            </div>
        </template>
    </div>

    {{-- ══ BOTTOM CARD (mobile: marker selezionato) ══ --}}
    <div class="mobile-bottom-card"
        x-show="selectedLocation && mobileView === 'map'"
        x-transition:enter="mbc-in"
        x-transition:enter-start="mbc-in-from"
        x-transition:enter-end="mbc-in-to"
        x-transition:leave="mbc-out"
        x-transition:leave-start="mbc-out-from"
        x-transition:leave-end="mbc-out-to"
    >
        <div class="mbc-inner" style="position:relative">
            <button class="mbc-close" @click="clearSelection()" aria-label="Chiudi">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
            <div class="mbc-body">
                <p class="mbc-name" x-text="selectedLocation?.name"></p>
                <p class="mbc-addr" x-text="[selectedLocation?.address, selectedLocation?.city, selectedLocation?.province].filter(Boolean).join(' · ')"></p>
                <div class="mbc-badges">
                    <template x-for="cat in (selectedLocation?.categories||[]).slice(0,2)" :key="cat.id">
                        <span class="badge badge-cat" x-text="cat.name"></span>
                    </template>
                    <span
                        class="badge"
                        :class="selectedLocation?.convention_type === 'diretta' ? 'badge-dir' : 'badge-ind'"
                        x-text="selectedLocation?.convention_type === 'diretta' ? '✓ Diretta' : 'Indiretta'"
                    ></span>
                </div>
                <div class="mbc-actions">
                    <template x-if="selectedLocation?.phone">
                        <a :href="'tel:' + selectedLocation.phone" class="mbc-btn mbc-btn-phone">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8a19.79 19.79 0 01-3.07-8.7A2 2 0 012 0h3a2 2 0 012 1.72 12.84 12.84 0 00.7 2.81 2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45 12.84 12.84 0 002.81.7A2 2 0 0122 14.92v2z"/></svg>
                            Chiama
                        </a>
                    </template>
                    <a :href="'/struttura/' + selectedLocation?.slug" class="mbc-btn mbc-btn-detail">
                        Dettaglio →
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ══ DRAWER FILTRI (mobile) ══ --}}
    <div class="filter-drawer" x-show="showFilterDrawer" x-cloak @click.self="showFilterDrawer = false">
        <div class="filter-drawer-overlay" @click="showFilterDrawer = false"></div>
        <div class="filter-drawer-panel">
            <div class="drawer-handle"></div>
            <p class="drawer-title">Filtra strutture</p>
            <div class="drawer-filters">
                <div class="filter-group">
                    <p class="filter-label">Provincia</p>
                    <select class="filter-select" x-model="filters.province" aria-label="Filtra per provincia">
                        <option value="">Tutte le province</option>
                        <template x-for="p in provinces" :key="p">
                            <option :value="p" x-text="p"></option>
                        </template>
                    </select>
                </div>
                <div class="filter-group">
                    <p class="filter-label">Specializzazione</p>
                    <select class="filter-select" x-model="filters.category_id" aria-label="Filtra per specializzazione">
                        <option value="">Tutte le specializzazioni</option>
                        <template x-for="cat in categories" :key="cat.id">
                            <option :value="cat.id" x-text="cat.name + ' (' + cat.locations_count + ')'"></option>
                        </template>
                    </select>
                </div>
                <label class="toggle-row" style="padding:.25rem 0">
                    <span class="toggle-track">
                        <input type="checkbox" x-model="filters.directOnly">
                        <span class="toggle-bg"></span>
                        <span class="toggle-thumb"></span>
                    </span>
                    <span class="toggle-label">Solo convenzione diretta</span>
                </label>
                <button class="btn-apply" @click="applyFilters(); showFilterDrawer = false">
                    Mostra <span x-text="filteredLocations.length"></span> strutture
                </button>
            </div>
        </div>
    </div>

    {{-- ══ BOTTOM TAB BAR (mobile) ══ --}}
    <div class="mobile-tabbar" role="tablist" aria-label="Visualizzazione">
        <button
            class="tab-btn"
            :class="{ 'is-active': mobileView === 'map' }"
            @click="mobileView = 'map'"
            role="tab"
            :aria-selected="mobileView === 'map'"
            aria-label="Visualizza mappa"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M9 6.75V15m6-6v8.25m.503 3.498l4.875-2.437c.381-.19.622-.58.622-1.006V4.82c0-.836-.88-1.38-1.628-1.006l-3.869 1.934c-.317.159-.69.159-1.006 0L9.503 3.252a1.125 1.125 0 00-1.006 0L3.622 5.689C3.24 5.88 3 6.27 3 6.695V19.18c0 .836.88 1.38 1.628 1.006l3.869-1.934c.317-.159.69-.159 1.006 0l4.994 2.497c.317.158.69.158 1.006 0z"/></svg>
            Mappa
        </button>
        <button
            class="tab-btn"
            :class="{ 'is-active': mobileView === 'list' }"
            @click="mobileView = 'list'; clearSelection()"
            role="tab"
            :aria-selected="mobileView === 'list'"
            aria-label="Visualizza lista"
        >
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><line x1="8" y1="6" x2="21" y2="6"/><line x1="8" y1="12" x2="21" y2="12"/><line x1="8" y1="18" x2="21" y2="18"/><line x1="3" y1="6" x2="3.01" y2="6"/><line x1="3" y1="12" x2="3.01" y2="12"/><line x1="3" y1="18" x2="3.01" y2="18"/></svg>
            Lista
            <template x-if="filteredLocations.length > 0">
                <span class="tab-count" x-text="filteredLocations.length"></span>
            </template>
        </button>
    </div>

</div>
@endsection

@push('scripts')
<script>
function mapApp() {
    return {
        allLocations: [],
        filteredLocations: [],
        categories: [],
        provinces: [],
        loading: true,
        googleMapsLoaded: false,
        selectedId: null,
        selectedLocation: null,
        mobileView: 'map',
        showFilterDrawer: false,
        map: null,
        markers: {},
        infoWindow: null,
        filters: { search: '', province: '', category_id: '', directOnly: false },

        async init() {
            await Promise.all([this.loadCategories(), this.loadProvinces(), this.loadLocations()]);
        },

        async loadLocations() {
            try {
                const r = await fetch('/api/v1/locations?per_page=500');
                const d = await r.json();
                this.allLocations = d.data ?? d;
                this.filteredLocations = [...this.allLocations];
            } finally {
                this.loading = false;
                this.$nextTick(() => this.initMap());
            }
        },

        async loadCategories() {
            const r = await fetch('/api/v1/categories');
            this.categories = await r.json();
        },

        async loadProvinces() {
            const r = await fetch('/api/v1/provinces');
            this.provinces = await r.json();
        },

        hasActiveFilters() {
            return !!(this.filters.province || this.filters.category_id || this.filters.directOnly);
        },

        applyFilters() {
            let res = [...this.allLocations];
            const s = this.filters.search.toLowerCase().trim();
            if (s) res = res.filter(l =>
                l.name.toLowerCase().includes(s) || (l.city||'').toLowerCase().includes(s)
            );
            if (this.filters.province) res = res.filter(l => l.province === this.filters.province);
            if (this.filters.category_id) {
                const id = parseInt(this.filters.category_id);
                res = res.filter(l => (l.categories||[]).some(c => c.id === id));
            }
            if (this.filters.directOnly) res = res.filter(l => l.convention_type === 'diretta');
            this.filteredLocations = res;
            this.updateMarkerVisibility();
        },

        selectLocation(loc) {
            this.selectedId = loc.id;
            this.selectedLocation = loc;
            // Su mobile: switcha alla mappa
            if (window.innerWidth < 768) this.mobileView = 'map';
            if (!this.map || !this.markers[loc.id]) return;
            this.map.panTo({ lat: loc.lat, lng: loc.lng });
            this.map.setZoom(loc.zoom || 15);
            // Desktop: info window; mobile: bottom card (già gestita da x-show)
            if (window.innerWidth >= 768) {
                this.openInfoWindow(loc, this.markers[loc.id]);
            }
            this.updateMarkerIcons();
            this.$nextTick(() => {
                const el = document.querySelector('.loc-card.is-active');
                if (el) el.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            });
        },

        clearSelection() {
            this.selectedId = null;
            this.selectedLocation = null;
            if (this.infoWindow) this.infoWindow.close();
            this.updateMarkerIcons();
        },

        isDark() {
            return document.getElementById('root').getAttribute('data-theme') === 'dark'
                || (localStorage.getItem('theme') === null && window.matchMedia('(prefers-color-scheme: dark)').matches);
        },

        initMap() {
            if (!window.google?.maps) return;
            this.googleMapsLoaded = true;
            this.map = new google.maps.Map(document.getElementById('google-map'), {
                center: { lat: 43.77, lng: 11.25 },
                zoom: 8,
                mapTypeControl: false,
                streetViewControl: false,
                fullscreenControl: window.innerWidth >= 768,
                styles: this.isDark() ? this.darkStyle() : [],
            });
            this.infoWindow = new google.maps.InfoWindow({ maxWidth: 280 });
            // Chiudi bottom card cliccando sulla mappa
            this.map.addListener('click', () => this.clearSelection());
            this.buildMarkers();
        },

        buildMarkers() {
            const arr = [];
            this.allLocations.forEach(loc => {
                if (!loc.lat || !loc.lng) return;
                const marker = new google.maps.Marker({
                    position: { lat: loc.lat, lng: loc.lng },
                    title: loc.name,
                    icon: this.markerIcon(false, loc.featured),
                });
                marker.addListener('click', () => {
                    this.selectedId = loc.id;
                    this.selectedLocation = loc;
                    this.updateMarkerIcons();
                    if (window.innerWidth >= 768) {
                        this.openInfoWindow(loc, marker);
                    }
                });
                this.markers[loc.id] = marker;
                arr.push(marker);
            });

            if (window.markerClusterer) {
                new markerClusterer.MarkerClusterer({ map: this.map, markers: arr });
            } else {
                arr.forEach(m => m.setMap(this.map));
            }

            if (arr.length) {
                const bounds = new google.maps.LatLngBounds();
                arr.forEach(m => bounds.extend(m.getPosition()));
                this.map.fitBounds(bounds);
            }
        },

        updateMarkerVisibility() {
            const visible = new Set(this.filteredLocations.map(l => l.id));
            Object.entries(this.markers).forEach(([id, m]) => m.setVisible(visible.has(parseInt(id))));
        },

        updateMarkerIcons() {
            Object.entries(this.markers).forEach(([id, m]) => {
                const loc = this.allLocations.find(x => x.id === parseInt(id));
                m.setIcon(this.markerIcon(parseInt(id) === this.selectedId, loc?.featured));
            });
        },

        openInfoWindow(loc, marker) {
            const dark = this.isDark();
            const bg   = dark ? '#131f16' : '#fff';
            const tx   = dark ? '#e2efe6' : '#12211a';
            const muted= dark ? '#6fa882' : '#486052';
            const green= dark ? '#72c98a' : '#2f5c3d';
            const dirBg = dark ? '#0d2e1a' : '#d8f4e2';
            const dirTx = dark ? '#7de0a4' : '#1a4d2e';
            const indBg = dark ? '#1f1400' : '#fff3cd';
            const indTx = dark ? '#f6c958' : '#6b3e00';
            const dir = loc.convention_type === 'diretta';
            const addr = [loc.address, loc.city, loc.province].filter(Boolean).join(', ');

            this.infoWindow.setContent(`
                <div style="font-family:'Atkinson Hyperlegible',Georgia,sans-serif;padding:2px 4px;min-width:210px;background:${bg}">
                    <strong style="font-family:Montserrat,sans-serif;font-size:14px;font-weight:700;display:block;margin-bottom:4px;line-height:1.3;color:${tx}">${loc.name}</strong>
                    <span style="font-size:12px;color:${muted};display:block;line-height:1.4">${addr}</span>
                    ${loc.phone ? `<a href="tel:${loc.phone}" style="font-size:13px;font-weight:700;color:${green};display:block;margin-top:6px;text-decoration:none">${loc.phone}</a>` : ''}
                    <div style="margin-top:9px;display:flex;align-items:center;justify-content:space-between;gap:8px">
                        <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:99px;background:${dir?dirBg:indBg};color:${dir?dirTx:indTx}">${dir?'✓ Diretta':'Indiretta'}</span>
                        <a href="/struttura/${loc.slug}" style="font-size:12px;font-weight:700;color:${green};text-decoration:none">Dettaglio →</a>
                    </div>
                </div>
            `);
            this.infoWindow.open(this.map, marker);
        },

        markerIcon(selected, featured) {
            return {
                path: google.maps.SymbolPath.CIRCLE,
                fillColor: selected ? '#1a4d2e' : (featured ? '#6b3e00' : '#2f5c3d'),
                fillOpacity: 1,
                strokeColor: '#fff',
                strokeWeight: selected ? 3 : 2,
                scale: selected ? 12 : 8,
            };
        },

        darkStyle() {
            return [
                {elementType:'geometry',stylers:[{color:'#1a2b1f'}]},
                {elementType:'labels.text.stroke',stylers:[{color:'#0b1610'}]},
                {elementType:'labels.text.fill',stylers:[{color:'#72c98a'}]},
                {featureType:'road',elementType:'geometry',stylers:[{color:'#253c2c'}]},
                {featureType:'road',elementType:'geometry.stroke',stylers:[{color:'#131f16'}]},
                {featureType:'road',elementType:'labels.text.fill',stylers:[{color:'#a8c9b3'}]},
                {featureType:'road.highway',elementType:'geometry',stylers:[{color:'#3a6e4b'}]},
                {featureType:'road.highway',elementType:'labels.text.fill',stylers:[{color:'#e2efe6'}]},
                {featureType:'water',elementType:'geometry',stylers:[{color:'#081009'}]},
                {featureType:'water',elementType:'labels.text.fill',stylers:[{color:'#3a6e4b'}]},
                {featureType:'poi',elementType:'geometry',stylers:[{color:'#162b1d'}]},
                {featureType:'poi.park',elementType:'geometry',stylers:[{color:'#1a3325'}]},
                {featureType:'administrative',elementType:'labels.text.fill',stylers:[{color:'#72c98a'}]},
            ];
        },
    };
}

function initGoogleMap() {
    document.querySelectorAll('[x-data]').forEach(el => {
        if (el._x_dataStack) {
            const d = el._x_dataStack[0];
            if (d && typeof d.initMap === 'function') d.initMap();
        }
    });
}
</script>
<script src="https://unpkg.com/@googlemaps/markerclusterer/dist/index.min.js"></script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ config('filament-google-maps.keys.web_key') }}&callback=initGoogleMap" async defer></script>
@endpush
