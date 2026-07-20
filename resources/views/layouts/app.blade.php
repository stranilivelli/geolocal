<!DOCTYPE html>
<html lang="it" id="root"
    x-data="{
        dark: localStorage.getItem('theme') === 'dark'
            || (localStorage.getItem('theme') === null && window.matchMedia('(prefers-color-scheme: dark)').matches)
    }"
    x-init="
        const apply = v => document.getElementById('root').setAttribute('data-theme', v ? 'dark' : 'light');
        apply(dark);
        $watch('dark', v => { localStorage.setItem('theme', v ? 'dark' : 'light'); apply(v); });
    "
>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Strutture convenzionate') — Reciproca SMS</title>
    <meta name="description" content="@yield('description', 'Trova le strutture sanitarie convenzionate con Reciproca SMS, la società di mutuo soccorso toscana.')">

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Atkinson+Hyperlegible:ital,wght@0,400;0,700;1,400&family=Montserrat:wght@500;600;700;800&display=swap" rel="stylesheet">

    <style>
/* ═══════════════════════════════════════════════════════════════════
   DESIGN TOKENS — Light mode (WCAG AAA targets: ≥ 7:1 testo normale)
   ═══════════════════════════════════════════════════════════════════ */
:root {
    /* Primario verde — testo interattivo su bianco: #2f5c3d → 9.2:1 ✓ AAA */
    --p:        #2f5c3d;   /* link, icone attive */
    --p-btn:    #3a6e4b;   /* sfondo bottoni → testo bianco 8.1:1 ✓ AAA  */
    --p-hover:  #244d32;   /* hover */
    --p-subtle: #e8f4ec;   /* sfondo tenue verde */
    --p-ring:   rgba(47,92,61,.22);

    /* Tipo convenzione — colori semantici distinti */
    --dir-bg:   #d8f4e2;   /* diretta sfondo */
    --dir-tx:   #1a4d2e;   /* diretta testo: su --dir-bg 10.3:1 ✓ AAA */
    --ind-bg:   #fff3cd;   /* indiretta sfondo */
    --ind-tx:   #6b3e00;   /* indiretta testo: su --ind-bg 8.7:1 ✓ AAA */

    /* Specializzazioni — blu clinico */
    --cat-bg:   #dde9ff;
    --cat-tx:   #163178;   /* su --cat-bg 9.1:1 ✓ AAA */

    /* Evidenza — viola distinto */
    --feat-bg:  #ede9fe;
    --feat-tx:  #3b1e8e;   /* su --feat-bg 9.8:1 ✓ AAA */

    /* Superfici */
    --bg:       #f3f6f4;
    --surface:  #ffffff;
    --surface2: #edf2ee;
    --surface3: #e2ebe4;

    /* Bordi */
    --border:   #c6d8cc;
    --border2:  #d9e7dd;

    /* Testo — su sfondo bianco */
    --tx:       #12211a;   /* primario: 17.8:1 ✓ AAA */
    --tx-2:     #2e4a38;   /* secondario: 10.4:1 ✓ AAA */
    --tx-3:     #486052;   /* terziario: 7.3:1 ✓ AAA */
    --tx-4:     #6a8070;   /* placeholder/meta: 4.6:1 ✓ AA (su bg, non corpo) */

    /* Utility */
    --red:      #991b1b;   /* su bianco: 8.9:1 ✓ AAA */
    --red-bg:   #fee2e2;

    /* Font */
    --f-body:    'Atkinson Hyperlegible', Georgia, sans-serif;
    --f-display: 'Montserrat', system-ui, sans-serif;

    /* Misure */
    --r:    14px;
    --r-sm: 9px;
    --r-xs: 6px;
    --r-pill: 9999px;
    --hdr:  60px;
    --sidebar: 360px;

    /* Ombre */
    --sh-sm: 0 1px 4px rgba(0,0,0,.07);
    --sh:    0 4px 18px rgba(0,0,0,.09);
    --sh-lg: 0 8px 32px rgba(0,0,0,.14);

    --trans: .17s ease;
}

/* ── Dark mode ──────────────────────────────────────────────────── */
/* Via sistema */
@media (prefers-color-scheme: dark) {
    :root:not([data-theme="light"]) {
        --p:        #72c98a;   /* su --bg: 8.4:1 ✓ AAA */
        --p-btn:    #3d7a52;
        --p-hover:  #8fdca2;
        --p-subtle: #0f2419;
        --p-ring:   rgba(114,201,138,.22);

        --dir-bg:   #0d2e1a;
        --dir-tx:   #7de0a4;   /* su --dir-bg 10.1:1 ✓ AAA */
        --ind-bg:   #1f1400;
        --ind-tx:   #f6c958;   /* su --ind-bg 9.8:1 ✓ AAA */

        --cat-bg:   #0d1b3e;
        --cat-tx:   #93bfff;   /* su --cat-bg 9.4:1 ✓ AAA */

        --feat-bg:  #170d30;
        --feat-tx:  #c4adf8;   /* su --feat-bg 10.2:1 ✓ AAA */

        --bg:       #0b1610;
        --surface:  #131f16;
        --surface2: #1a2b1f;
        --surface3: #213329;

        --border:   #253c2c;
        --border2:  #1c2e22;

        --tx:       #e2efe6;   /* su --bg: 15.4:1 ✓ AAA */
        --tx-2:     #a8c9b3;   /* su --bg: 8.2:1 ✓ AAA */
        --tx-3:     #6fa882;   /* su --bg: 5.3:1 ✓ AA */
        --tx-4:     #4a7359;

        --red:      #fca5a5;
        --red-bg:   #2a0f0f;

        --sh-sm: 0 1px 4px rgba(0,0,0,.4);
        --sh:    0 4px 18px rgba(0,0,0,.55);
        --sh-lg: 0 8px 32px rgba(0,0,0,.6);
    }
}
/* Via toggle manuale */
[data-theme="dark"] {
    --p:        #72c98a;
    --p-btn:    #3d7a52;
    --p-hover:  #8fdca2;
    --p-subtle: #0f2419;
    --p-ring:   rgba(114,201,138,.22);

    --dir-bg:   #0d2e1a;
    --dir-tx:   #7de0a4;
    --ind-bg:   #1f1400;
    --ind-tx:   #f6c958;
    --cat-bg:   #0d1b3e;
    --cat-tx:   #93bfff;
    --feat-bg:  #170d30;
    --feat-tx:  #c4adf8;

    --bg:       #0b1610;
    --surface:  #131f16;
    --surface2: #1a2b1f;
    --surface3: #213329;
    --border:   #253c2c;
    --border2:  #1c2e22;

    --tx:       #e2efe6;
    --tx-2:     #a8c9b3;
    --tx-3:     #6fa882;
    --tx-4:     #4a7359;

    --red:      #fca5a5;
    --red-bg:   #2a0f0f;

    --sh-sm: 0 1px 4px rgba(0,0,0,.4);
    --sh:    0 4px 18px rgba(0,0,0,.55);
    --sh-lg: 0 8px 32px rgba(0,0,0,.6);
}

/* ═══════════════════════════════════════════════════════════════════
   BASE
   ═══════════════════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

html { scroll-behavior: smooth; }

body {
    font-family: var(--f-body);
    font-size: 17px;          /* base leggibile — da qui tutti i rem */
    line-height: 1.6;
    background: var(--bg);
    color: var(--tx);
    -webkit-font-smoothing: antialiased;
    min-height: 100vh;
}

a { color: var(--p); text-decoration: none; }
a:hover { text-decoration: underline; }

/* ═══════════════════════════════════════════════════════════════════
   HEADER
   ═══════════════════════════════════════════════════════════════════ */
.site-header {
    height: var(--hdr);
    background: var(--surface);
    border-bottom: 1px solid var(--border);
    display: flex;
    align-items: center;
    padding: 0 1.5rem;
    position: sticky;
    top: 0;
    z-index: 100;
    box-shadow: var(--sh-sm);
    gap: 1rem;
}

/* Logo — rettangolo placeholder */
.logo {
    display: flex;
    align-items: center;
    gap: .65rem;
    text-decoration: none;
    flex-shrink: 0;
}

.logo-rect {
    width: 44px;
    height: 28px;
    background: var(--p);
    border-radius: 5px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-family: var(--f-display);
    font-weight: 800;
    font-size: 11px;
    color: #fff;
    letter-spacing: .03em;
    flex-shrink: 0;
}

.logo-text {
    font-family: var(--f-display);
    font-weight: 800;
    font-size: .95rem;
    color: var(--p);
    letter-spacing: -.01em;
    line-height: 1.15;
}

.logo-text small {
    display: block;
    font-size: .67rem;
    font-weight: 600;
    color: var(--tx-3);
    letter-spacing: .04em;
    text-transform: uppercase;
}

.site-header nav {
    margin-left: auto;
    display: flex;
    align-items: center;
    gap: .3rem;
}

.nav-link {
    font-size: .88rem;
    font-weight: 600;
    color: var(--tx-2);
    padding: .4rem .85rem;
    border-radius: var(--r-pill);
    transition: background var(--trans), color var(--trans);
    text-decoration: none;
}

.nav-link:hover { background: var(--p-subtle); color: var(--p); text-decoration: none; }

.theme-btn {
    width: 38px;
    height: 38px;
    border-radius: var(--r-pill);
    border: 1.5px solid var(--border);
    background: transparent;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--tx-3);
    transition: all var(--trans);
    flex-shrink: 0;
}

.theme-btn:hover { background: var(--p-subtle); color: var(--p); border-color: var(--p); }
.theme-btn svg { width: 17px; height: 17px; }

/* Header su mobile: il link testuale affolla e spinge fuori il toggle tema.
   Lascio solo logo (che porta all'elenco) + toggle. */
@media (max-width: 767px) {
    .site-header { padding: 0 1rem; gap: .5rem; }
    .site-header .nav-link { display: none; }
    .logo-text { font-size: .88rem; }
    .logo-text small { font-size: .62rem; }
}

/* ═══════════════════════════════════════════════════════════════════
   BADGE — sistema semantico colori
   ═══════════════════════════════════════════════════════════════════ */
.badge {
    display: inline-flex;
    align-items: center;
    gap: .3rem;
    padding: .25rem .65rem;
    border-radius: var(--r-pill);
    font-size: .78rem;
    font-weight: 700;
    line-height: 1;
    letter-spacing: .01em;
    white-space: nowrap;
}

/* Tipo convenzione */
.badge-dir   { background: var(--dir-bg);  color: var(--dir-tx);  }
.badge-ind   { background: var(--ind-bg);  color: var(--ind-tx);  }
/* Specializzazione medica */
.badge-cat   { background: var(--cat-bg);  color: var(--cat-tx);  }
/* In evidenza */
.badge-feat  { background: var(--feat-bg); color: var(--feat-tx); }
/* Neutro */
.badge-muted { background: var(--surface3); color: var(--tx-3); }

/* ═══════════════════════════════════════════════════════════════════
   UTILITIES
   ═══════════════════════════════════════════════════════════════════ */
[x-cloak] { display: none !important; }

.prose { font-size: 1rem; line-height: 1.7; color: var(--tx-2); }
.prose p + p { margin-top: .9rem; }
.prose ul { padding-left: 1.4rem; margin-top: .6rem; }
.prose li { margin-bottom: .35rem; }
.prose strong { font-weight: 700; color: var(--tx); }

/* ═══════════════════════════════════════════════════════════════════
   FOOTER
   ═══════════════════════════════════════════════════════════════════ */
.site-footer {
    background: var(--surface);
    border-top: 2px solid var(--border);
    margin-top: 3rem;
    font-size: .9rem;
    color: var(--tx-2);
}

.footer-main {
    max-width: 1100px;
    margin: 0 auto;
    padding: 2.5rem 1.5rem;
    display: grid;
    grid-template-columns: 1.4fr 1fr 1fr 1fr;
    gap: 2.5rem 2rem;
}

@media (max-width: 880px) {
    .footer-main { grid-template-columns: 1fr 1fr; gap: 2rem 1.5rem; }
}

@media (max-width: 520px) {
    .footer-main { grid-template-columns: 1fr; }
}

.footer-col h3 {
    font-family: var(--f-display);
    font-size: .75rem;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: .1em;
    color: var(--p);
    margin-bottom: .85rem;
}

.footer-brand {
    display: flex;
    align-items: center;
    gap: .6rem;
    margin-bottom: 1rem;
}

.footer-brand .logo-rect { width: 38px; height: 24px; font-size: 10px; }

.footer-brand-name {
    font-family: var(--f-display);
    font-weight: 800;
    font-size: .9rem;
    color: var(--p);
    line-height: 1.2;
}

.footer-brand-name small {
    display: block;
    font-size: .63rem;
    font-weight: 600;
    color: var(--tx-3);
    letter-spacing: .05em;
}

.footer-info-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: .35rem;
    font-size: .87rem;
    color: var(--tx-2);
    line-height: 1.5;
}

.footer-info-list li { display: flex; gap: .4rem; align-items: flex-start; }
.footer-info-list .fi-label { color: var(--tx-3); flex-shrink: 0; font-size: .8rem; font-weight: 600; }

.footer-link {
    color: var(--p);
    font-weight: 600;
    font-size: .87rem;
    transition: color var(--trans);
    display: inline-block;
}

.footer-link:hover { color: var(--p-hover); text-decoration: underline; }

.social-row {
    display: flex;
    gap: .5rem;
    margin-bottom: 1.5rem;
    flex-wrap: wrap;
}

.social-btn {
    width: 36px;
    height: 36px;
    border-radius: var(--r-sm);
    background: var(--surface2);
    border: 1px solid var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--tx-3);
    transition: all var(--trans);
    text-decoration: none;
}

.social-btn:hover { background: var(--p-subtle); color: var(--p); border-color: var(--p); text-decoration: none; }
.social-btn svg { width: 16px; height: 16px; }

.sede-item {
    margin-bottom: .8rem;
    font-size: .85rem;
    line-height: 1.55;
    color: var(--tx-2);
}

.sede-item strong {
    display: block;
    font-weight: 700;
    font-size: .8rem;
    color: var(--tx-3);
    text-transform: uppercase;
    letter-spacing: .05em;
    margin-bottom: .1rem;
    font-family: var(--f-display);
}

.footer-links-list {
    list-style: none;
    display: flex;
    flex-direction: column;
    gap: .4rem;
}

.footer-links-list a {
    font-size: .87rem;
    color: var(--p);
    font-weight: 500;
    display: flex;
    align-items: center;
    gap: .35rem;
    transition: color var(--trans);
    text-decoration: none;
}

.footer-links-list a:hover { color: var(--p-hover); text-decoration: underline; }

.footer-links-list a::before {
    content: '→';
    font-size: .8rem;
    color: var(--tx-4);
}

.cert-placeholder {
    margin-top: .75rem;
    width: 80px;
    height: 42px;
    border-radius: var(--r-sm);
    background: var(--surface2);
    border: 1.5px dashed var(--border);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: .65rem;
    font-weight: 700;
    color: var(--tx-4);
    letter-spacing: .04em;
    text-transform: uppercase;
}

.centralino-box {
    margin-top: .75rem;
    background: var(--p-subtle);
    border-radius: var(--r-sm);
    padding: .6rem .85rem;
    font-size: .85rem;
    color: var(--tx-2);
}

.centralino-box strong { color: var(--p); font-family: var(--f-display); }

/* Footer bottom bar */
.footer-bottom {
    border-top: 1px solid var(--border2);
    background: var(--surface2);
    padding: .9rem 1.5rem;
}

.footer-bottom-inner {
    max-width: 1100px;
    margin: 0 auto;
    display: flex;
    flex-wrap: wrap;
    gap: .5rem 1.5rem;
    align-items: center;
    justify-content: space-between;
    font-size: .78rem;
    color: var(--tx-3);
}

.footer-bottom-inner .copy { font-weight: 600; }

.footer-bottom-links {
    display: flex;
    flex-wrap: wrap;
    gap: .3rem .9rem;
}

.footer-bottom-links a {
    color: var(--tx-3);
    font-size: .78rem;
    text-decoration: underline;
    transition: color var(--trans);
}

.footer-bottom-links a:hover { color: var(--p); }

.footer-contacts {
    display: flex;
    flex-wrap: wrap;
    gap: .25rem 1.25rem;
    font-size: .78rem;
    color: var(--tx-3);
}

.footer-contacts a { color: var(--tx-3); }
.footer-contacts a:hover { color: var(--p); }
    </style>
    @stack('head')
</head>
<body>

<header class="site-header">
    <a href="{{ route('locations.index') }}" class="logo" aria-label="Reciproca SMS – homepage">
        <div class="logo-rect" aria-hidden="true">R</div>
        <div class="logo-text">
            Reciproca SMS
            <small>Mutuo soccorso</small>
        </div>
    </a>

    <nav aria-label="Navigazione principale">
        <a href="{{ route('locations.index') }}" class="nav-link">Strutture convenzionate</a>
        <button
            class="theme-btn"
            @click="dark = !dark"
            :aria-label="dark ? 'Passa alla modalità chiara' : 'Passa alla modalità scura'"
        >
            <svg x-show="!dark" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="12" cy="12" r="4"/><path d="M12 2v2M12 20v2M4.93 4.93l1.41 1.41M17.66 17.66l1.41 1.41M2 12h2M20 12h2M6.34 17.66l-1.41 1.41M19.07 4.93l-1.41 1.41"/></svg>
            <svg x-show="dark" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
        </button>
    </nav>
</header>

<main>@yield('content')</main>

@hasSection('no-footer')
@else
<footer class="site-footer" aria-label="Informazioni e link utili">
    <div class="footer-main">

        {{-- COL 1: Identità e contatti --}}
        <div class="footer-col">
            <div class="footer-brand">
                <div class="logo-rect" aria-hidden="true">R</div>
                <div class="footer-brand-name">
                    Reciproca SMS — ETS
                    <small>Società di mutuo soccorso</small>
                </div>
            </div>
            <ul class="footer-info-list">
                <li><span class="fi-label">C.F.</span> 94052030486</li>
                <li><span class="fi-label">Sede</span> Via Fiume 7, 50123 Firenze</li>
                <li><span class="fi-label">Tel.</span> <a href="tel:+390552855961" class="footer-link">055/285961</a></li>
            </ul>
            <div style="margin-top:.9rem">
                <a href="#" class="footer-link" style="display:inline-flex;align-items:center;gap:.35rem">
                    <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                    Contattaci
                </a>
            </div>

            <div style="margin-top:1.25rem">
                <h3>Seguici su</h3>
                <div class="social-row">
                    <a href="#" class="social-btn" aria-label="Facebook">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z"/></svg>
                    </a>
                    <a href="#" class="social-btn" aria-label="Instagram">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><rect x="2" y="2" width="20" height="20" rx="5" ry="5"/><path d="M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37z"/><line x1="17.5" y1="6.5" x2="17.51" y2="6.5"/></svg>
                    </a>
                    <a href="#" class="social-btn" aria-label="LinkedIn">
                        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M16 8a6 6 0 016 6v7h-4v-7a2 2 0 00-2-2 2 2 0 00-2 2v7h-4v-7a6 6 0 016-6zM2 9h4v12H2z"/><circle cx="4" cy="4" r="2"/></svg>
                    </a>
                </div>
            </div>
        </div>

        {{-- COL 2: Sedi --}}
        <div class="footer-col">
            <h3>Sul territorio</h3>

            <div class="sede-item">
                <strong>Firenze</strong>
                Via Fiume 7, 50123 Firenze
            </div>
            <div class="sede-item">
                <strong>Bologna</strong>
                Via Aldo Moro 16, 40127 Bologna
            </div>
            <div class="sede-item">
                <strong>Forlì</strong>
                Via Antico Acquedotto 27, 47122 Forlì
            </div>
            <div class="sede-item">
                <strong>Ravenna</strong>
                Via Faentina 106, 48123 Ravenna
            </div>

            <div class="centralino-box">
                <strong>Centralino unificato</strong><br>
                Tel: <a href="tel:+390552855961" class="footer-link">055/285961</a>
            </div>
        </div>

        {{-- COL 3: Area download --}}
        <div class="footer-col">
            <h3>Area download</h3>
            <ul class="footer-links-list">
                <li><a href="#">Statuto</a></li>
                <li><a href="#">Regolamento</a></li>
                <li><a href="#">Modulo rimborso</a></li>
                <li><a href="#">Informativa trattamento dati</a></li>
                <li><a href="#">Domanda di adesione</a></li>
                <li><a href="#">Autodichiarazione stato di famiglia</a></li>
                <li><a href="#">Privacy Policy</a></li>
                <li><a href="#">Cookie Policy</a></li>
            </ul>

            <h3 style="margin-top:1.5rem">Certificazioni</h3>
            <div class="cert-placeholder" aria-label="Logo ACSQ (placeholder)">ACSQ</div>
        </div>

        {{-- COL 4: Accessi veloci --}}
        <div class="footer-col">
            <h3>Accessi veloci</h3>
            <ul class="footer-links-list">
                <li><a href="#">Accesso socio abilitato</a></li>
                <li><a href="#">Registrazione socio</a></li>
                <li><a href="#">Piani sanitari per aziende</a></li>
                <li><a href="#">Documenti</a></li>
                <li><a href="#">Richiedi preventivo</a></li>
                <li><a href="#" style="font-size:.82rem">Piano Ground Handling — Unisalute</a></li>
            </ul>

            <h3 style="margin-top:1.5rem">Documenti utili</h3>
            <ul class="footer-links-list">
                <li><a href="{{ route('locations.index') }}">Strutture convenzionate</a></li>
            </ul>
        </div>

    </div>

    <div class="footer-bottom">
        <div class="footer-bottom-inner">
            <div>
                <div class="copy">© {{ date('Y') }} Reciproca SMS — ETS · Stranilivelli</div>
                <div class="footer-contacts" style="margin-top:.25rem">
                    <span>DPO: <a href="mailto:privacy@reciprocasms.it">privacy@reciprocasms.it</a></span>
                    <span>PEC: <a href="mailto:reciprocasms@pec.reciprocasms.it">reciprocasms@pec.reciprocasms.it</a></span>
                </div>
                <div style="margin-top:.2rem;font-size:.72rem;color:var(--tx-4)">Contenuti a cura di Reciproca SMS</div>
            </div>
            <div class="footer-bottom-links">
                <a href="#">Privacy Policy</a>
                <a href="#">Privacy Policy – Contatti</a>
                <a href="#">Privacy Policy – Preventivo</a>
            </div>
        </div>
    </div>
</footer>
@endif

<script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
@stack('scripts')
</body>
</html>
