<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>User Guide — Blue Arrow Portal</title>
<style>
  :root {
    --bg:          #f5f0e8;
    --surface:     #ffffff;
    --surface-alt: #ede8de;
    --gold:        #a8822a;
    --gold-light:  #f0e8d0;
    --ink:         #1c2333;
    --body:        #3a4455;
    --muted:       #7a8599;
    --border:      #ddd6c8;
    --step-bg:     #f8f4ed;
    --tag-bg:      #eee8da;
    --tag-text:    #7a6240;
    --note-bg:     #fef9ee;
    --sidebar-w:   240px;
  }

  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }

  body {
    font-family: system-ui, -apple-system, sans-serif;
    font-size: 15px;
    line-height: 1.7;
    color: var(--body);
    background: var(--bg);
    min-height: 100vh;
  }

  /* ── Top bar ── */
  .topbar {
    position: fixed;
    top: 0; left: 0; right: 0;
    height: 48px;
    background: var(--ink);
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 1.5rem;
    z-index: 100;
  }

  .topbar-left {
    display: flex;
    align-items: center;
    gap: 1rem;
  }

  .topbar a.back {
    color: rgba(255,255,255,0.6);
    text-decoration: none;
    font-size: 0.8rem;
    display: flex;
    align-items: center;
    gap: 0.4rem;
    transition: color 0.15s;
  }
  .topbar a.back:hover { color: #fff; }

  .topbar-title {
    color: rgba(255,255,255,0.85);
    font-size: 0.85rem;
    font-family: Georgia, serif;
  }

  .btn-pdf {
    display: flex;
    align-items: center;
    gap: 0.4rem;
    background: var(--gold);
    color: #fff;
    border: none;
    padding: 0.4rem 1rem;
    font-size: 0.8rem;
    font-weight: 600;
    cursor: pointer;
    border-radius: 4px;
    letter-spacing: 0.02em;
    transition: background 0.15s;
  }
  .btn-pdf:hover { background: #8a6a20; }

  /* ── Layout ── */
  .layout {
    display: grid;
    grid-template-columns: var(--sidebar-w) 1fr;
    padding-top: 48px;
    min-height: 100vh;
  }

  /* ── Sidebar ── */
  .sidebar {
    position: fixed;
    top: 48px;
    bottom: 0;
    width: var(--sidebar-w);
    overflow-y: auto;
    background: var(--surface);
    border-right: 1px solid var(--border);
    padding: 2rem 0;
  }

  .sidebar-brand {
    padding: 0 1.5rem 1.5rem;
    border-bottom: 1px solid var(--border);
    margin-bottom: 1.25rem;
  }
  .sidebar-brand .wordmark {
    font-family: Georgia, serif;
    font-size: 0.9rem;
    color: var(--ink);
    letter-spacing: 0.02em;
  }
  .sidebar-brand .subtitle {
    font-size: 0.68rem;
    color: var(--muted);
    text-transform: uppercase;
    letter-spacing: 0.1em;
    margin-top: 2px;
  }

  .nav-section { padding: 0 1.5rem; margin-bottom: 1.25rem; }
  .nav-label {
    font-size: 0.65rem;
    text-transform: uppercase;
    letter-spacing: 0.12em;
    color: var(--muted);
    font-weight: 600;
    margin-bottom: 0.4rem;
  }
  .nav-links { list-style: none; }
  .nav-links li a {
    display: block;
    padding: 0.3rem 0;
    font-size: 0.82rem;
    color: var(--body);
    text-decoration: none;
    border-left: 2px solid transparent;
    padding-left: 0.5rem;
    margin-left: -0.5rem;
    transition: color 0.15s, border-color 0.15s;
  }
  .nav-links li a:hover, .nav-links li a.active {
    color: var(--gold);
    border-left-color: var(--gold);
  }
  .nav-links .sub {
    font-size: 0.77rem;
    color: var(--muted);
    padding-left: 1rem;
    margin-left: -0.5rem;
  }

  /* ── Main content ── */
  .main {
    padding: 3rem 3.5rem 6rem;
    max-width: 780px;
  }

  .page-header {
    margin-bottom: 3rem;
    padding-bottom: 2rem;
    border-bottom: 1px solid var(--border);
  }
  .guide-eyebrow {
    font-size: 0.72rem;
    text-transform: uppercase;
    letter-spacing: 0.14em;
    color: var(--gold);
    font-weight: 600;
    margin-bottom: 0.75rem;
  }
  h1 {
    font-family: Georgia, serif;
    font-size: 2rem;
    font-weight: normal;
    color: var(--ink);
    line-height: 1.25;
    margin-bottom: 0.75rem;
  }
  .page-header p { color: var(--muted); font-size: 0.9rem; }

  .section { margin-bottom: 3.5rem; scroll-margin-top: 4rem; }
  .section-header {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-bottom: 1.75rem;
    padding-bottom: 0.75rem;
    border-bottom: 2px solid var(--border);
  }
  .section-letter {
    width: 36px; height: 36px;
    background: var(--gold);
    color: white;
    font-family: Georgia, serif;
    font-size: 1.1rem;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
  }
  h2 { font-family: Georgia, serif; font-size: 1.3rem; font-weight: normal; color: var(--ink); }
  h3 { font-family: Georgia, serif; font-size: 1rem; font-weight: normal; color: var(--ink); margin: 1.75rem 0 0.75rem; }
  h4 { font-size: 0.78rem; text-transform: uppercase; letter-spacing: 0.1em; font-weight: 600; color: var(--muted); margin: 1.25rem 0 0.5rem; }
  p { margin-bottom: 0.75rem; }

  .steps { margin: 1rem 0 1.5rem; }
  .step { display: grid; grid-template-columns: 28px 1fr; gap: 0 1rem; margin-bottom: 1rem; }
  .step-num {
    width: 28px; height: 28px;
    background: var(--step-bg);
    border: 1px solid var(--border);
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.72rem; font-weight: 700; color: var(--gold);
    flex-shrink: 0; margin-top: 0.15rem;
  }
  .step-body strong { color: var(--ink); font-size: 0.9rem; display: block; }
  .step-body p { margin: 0.2rem 0 0; font-size: 0.88rem; }

  .wizard-steps {
    border: 1px solid var(--border);
    border-radius: 6px;
    overflow: hidden;
    margin: 1rem 0 1.5rem;
    font-size: 0.85rem;
    overflow-x: auto;
  }
  .wizard-steps table { width: 100%; border-collapse: collapse; min-width: 500px; }
  .wizard-steps thead tr { background: var(--surface-alt); }
  .wizard-steps th {
    padding: 0.6rem 1rem; text-align: left;
    font-size: 0.7rem; text-transform: uppercase; letter-spacing: 0.1em;
    font-weight: 600; color: var(--muted);
  }
  .wizard-steps td { padding: 0.6rem 1rem; border-top: 1px solid var(--border); vertical-align: top; }
  .wizard-steps td:first-child { font-variant-numeric: tabular-nums; color: var(--gold); font-weight: 600; width: 40px; }
  .wizard-steps td:nth-child(2) { font-weight: 600; color: var(--ink); width: 130px; }
  .wizard-steps td:last-child { color: var(--body); }

  .note {
    background: var(--note-bg);
    border-left: 3px solid var(--gold);
    border-radius: 0 4px 4px 0;
    padding: 0.75rem 1rem;
    margin: 1rem 0;
    font-size: 0.85rem;
  }
  .note strong {
    color: var(--gold); font-size: 0.7rem;
    text-transform: uppercase; letter-spacing: 0.1em;
    display: block; margin-bottom: 0.2rem;
  }

  .tag {
    display: inline-block;
    background: var(--tag-bg); color: var(--tag-text);
    font-size: 0.7rem; font-weight: 600;
    padding: 0.15rem 0.5rem; border-radius: 3px;
    text-transform: uppercase; letter-spacing: 0.06em;
    vertical-align: middle; margin-left: 0.25rem;
  }

  .field-list { list-style: none; margin: 0.75rem 0 1rem; }
  .field-list li {
    display: flex; gap: 0.75rem;
    padding: 0.45rem 0; border-bottom: 1px solid var(--border);
    font-size: 0.86rem; align-items: baseline;
  }
  .field-list li:last-child { border-bottom: none; }
  .field-name {
    font-family: ui-monospace, monospace; font-size: 0.78rem;
    color: var(--ink); background: var(--surface-alt);
    padding: 0.1rem 0.4rem; border-radius: 3px;
    white-space: nowrap; flex-shrink: 0;
  }

  .invoice-types { display: grid; grid-template-columns: repeat(3,1fr); gap: 1rem; margin: 1rem 0 1.75rem; }
  .inv-card { border: 1px solid var(--border); border-radius: 6px; padding: 1rem; background: var(--surface); }
  .inv-card .inv-icon { font-size: 1.3rem; margin-bottom: 0.4rem; }
  .inv-card .inv-name { font-family: Georgia, serif; font-size: 0.95rem; color: var(--ink); margin-bottom: 0.2rem; }
  .inv-card .inv-desc { font-size: 0.78rem; color: var(--muted); line-height: 1.5; }

  .divider { border: none; border-top: 1px solid var(--border); margin: 2rem 0; }

  /* ── Print-only elements (hidden on screen) ── */
  .print-cover, .print-toc, .print-section-link { display: none; }

  /* ── Print / PDF ── */
  @media print {
    @page { size: A4; margin: 18mm 22mm; }
    @page :first { margin-top: 0; margin-bottom: 0; }

    :root {
      --bg:          #ffffff;
      --surface:     #ffffff;
      --surface-alt: #f2f2f2;
      --gold:        #8a6a20;
      --ink:         #000000;
      --body:        #222222;
      --muted:       #555555;
      --border:      #cccccc;
      --step-bg:     #f5f5f5;
      --tag-bg:      #eeeeee;
      --tag-text:    #555555;
      --note-bg:     #fafafa;
    }

    .topbar, .sidebar, .btn-pdf { display: none !important; }

    body { background: #fff; font-size: 10.5pt; line-height: 1.6; }

    .layout { display: block; padding-top: 0; }
    .main   { padding: 0; max-width: 100%; }

    /* ── Cover page ── */
    .print-cover {
      display: flex !important;
      flex-direction: column;
      justify-content: space-between;
      min-height: 100vh;
      page-break-after: always;
      padding: 0;
    }

    .print-cover .cover-top {
      background: #1c2333;
      padding: 2.5cm 2.2cm 2cm;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    .print-cover .cover-top .letterhead-img {
      max-width: 220px;
      max-height: 70px;
      object-fit: contain;
      filter: brightness(0) invert(1);
      display: block;
      margin-bottom: 0.4cm;
    }

    .print-cover .cover-top .brand-text {
      font-family: Georgia, serif;
      font-size: 18pt;
      color: #ffffff;
      letter-spacing: 0.02em;
    }

    .print-cover .cover-top .brand-sub {
      font-size: 9pt;
      color: rgba(255,255,255,0.55);
      letter-spacing: 0.12em;
      text-transform: uppercase;
      margin-top: 3px;
    }

    .print-cover .cover-body {
      padding: 2cm 2.2cm;
      flex: 1;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }

    .print-cover .cover-eyebrow {
      font-size: 8pt;
      text-transform: uppercase;
      letter-spacing: 0.14em;
      color: #8a6a20;
      font-weight: 700;
      margin-bottom: 0.4cm;
    }

    .print-cover .cover-title {
      font-family: Georgia, serif;
      font-size: 28pt;
      color: #1c2333;
      line-height: 1.2;
      margin-bottom: 0.5cm;
    }

    .print-cover .cover-desc {
      font-size: 10.5pt;
      color: #555;
      max-width: 38em;
      line-height: 1.6;
    }

    .print-cover .cover-footer {
      padding: 1cm 2.2cm;
      border-top: 1px solid #ddd;
      display: flex;
      justify-content: space-between;
      align-items: center;
      font-size: 8.5pt;
      color: #888;
    }

    /* ── Table of Contents page ── */
    .print-toc {
      display: block !important;
      page-break-before: always;
      page-break-after: always;
      padding: 1.5cm 0 2cm;
    }

    .print-toc h2 {
      font-family: Georgia, serif;
      font-size: 16pt;
      color: #1c2333;
      margin-bottom: 0.8cm;
      padding-bottom: 0.3cm;
      border-bottom: 2px solid #ddd;
    }

    .print-toc .toc-section { margin-bottom: 0.6cm; }

    .print-toc .toc-section-label {
      display: flex;
      align-items: center;
      gap: 0.4cm;
      margin-bottom: 0.2cm;
    }

    .print-toc .toc-letter {
      width: 22px; height: 22px;
      background: #8a6a20;
      color: white;
      font-family: Georgia, serif;
      font-size: 10pt;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
      flex-shrink: 0;
    }

    .print-toc .toc-section-title {
      font-family: Georgia, serif;
      font-size: 12pt;
      color: #1c2333;
    }

    .print-toc .toc-links { list-style: none; padding-left: 1.5cm; margin-top: 0.15cm; }

    .print-toc .toc-links li { margin-bottom: 0.15cm; }

    .print-toc .toc-links a {
      font-size: 9.5pt;
      color: #1c2333;
      text-decoration: none;
    }

    .print-toc .toc-links a:hover { color: #8a6a20; }

    /* ── Running page header (every page after cover) ── */
    .print-section-link {
      display: flex !important;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 0.5cm;
      padding-bottom: 0.3cm;
      border-bottom: 1px solid #ddd;
      font-size: 8pt;
      color: #999;
    }

    .print-section-link a {
      color: #8a6a20;
      text-decoration: none;
      font-size: 8pt;
    }

    /* ── Sections — each on its own page ── */
    .section {
      page-break-before: always;
    }

    .section:first-of-type { page-break-before: auto; }

    .wizard-steps { overflow: visible; page-break-inside: auto; }
    .wizard-steps table { min-width: 0; }
    .wizard-steps tr { page-break-inside: avoid; }
    .step { page-break-inside: avoid; }

    h1 { font-size: 20pt; }
    h2 { font-size: 13pt; }
    h3 { font-size: 11pt; margin-top: 1rem; }
    h4 { font-size: 8.5pt; }

    .section-letter {
      background: #8a6a20 !important;
      -webkit-print-color-adjust: exact;
      print-color-adjust: exact;
    }

    /* Preserve gold and background tints */
    .note { background: #fafafa !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
    .wizard-steps thead tr { background: #f2f2f2 !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }

    /* Make anchor links visible in PDF */
    a[href^="#"] {
      color: #8a6a20;
      text-decoration: underline;
    }

    .invoice-types { page-break-inside: avoid; }
  }

  @media (max-width: 720px) {
    .layout { grid-template-columns: 1fr; }
    .sidebar { position: relative; top: auto; width: 100%; height: auto; border-right: none; border-bottom: 1px solid var(--border); }
    .main { padding: 2rem 1.25rem 4rem; }
    .invoice-types { grid-template-columns: 1fr; }
  }
</style>
</head>
<body>

{{-- ═══ PRINT COVER PAGE (hidden on screen) ═══ --}}
@php $letterheadPath = storage_path('app/public/certificate/letterhead.png'); @endphp
<div class="print-cover">
  <div class="cover-top">
    @if(file_exists($letterheadPath))
      <img src="{{ asset('storage/certificate/letterhead.png') }}" class="letterhead-img" alt="Blue Arrow">
    @else
      <div class="brand-text">Blue Arrow</div>
      <div class="brand-sub">Compliance &amp; AML Solutions</div>
    @endif
  </div>
  <div class="cover-body">
    <div class="cover-eyebrow">Company Portal</div>
    <div class="cover-title">User Guide</div>
    <div class="cover-desc">Step-by-step instructions for onboarding clients and creating invoices within the Blue Arrow company portal. This guide covers client record generation and the accounting module.</div>
  </div>
  <div class="cover-footer">
    <span>Blue Arrow Compliance Portal</span>
    <span>Issued {{ now()->format('d M Y') }}</span>
  </div>
</div>

{{-- ═══ PRINT TABLE OF CONTENTS (hidden on screen) ═══ --}}
<div class="print-toc" id="toc">
  <h2>Contents</h2>

  <div class="toc-section">
    <div class="toc-section-label">
      <span class="toc-letter">A</span>
      <span class="toc-section-title">Client Record Generation</span>
    </div>
    <ul class="toc-links">
      <li><a href="#before-you-start">Before you start — documents to prepare</a></li>
      <li><a href="#corporate-client">Creating a corporate client (9 steps)</a></li>
      <li><a href="#individual-client">Creating an individual client (6 steps)</a></li>
      <li><a href="#client-tips">Tips &amp; shortcuts — auto-fill, draft save, duplicates</a></li>
    </ul>
  </div>

  <div class="toc-section">
    <div class="toc-section-label">
      <span class="toc-letter">B</span>
      <span class="toc-section-title">Accounting — Creating Invoices</span>
    </div>
    <ul class="toc-links">
      <li><a href="#sale-invoice">Sale invoice — selling metal to the client</a></li>
      <li><a href="#purchase-invoice">Purchase invoice — buying metal from the client</a></li>
      <li><a href="#exchange-invoice">Metal exchange invoice</a></li>
      <li><a href="#invoice-tips">Tips &amp; shortcuts — unfixed pricing, VAT, payments</a></li>
    </ul>
  </div>
</div>

{{-- Top bar --}}
<div class="topbar">
  <div class="topbar-left">
    <a href="{{ route('dashboard') }}" class="back">
      <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
      Dashboard
    </a>
    <span style="color:rgba(255,255,255,0.25)">|</span>
    <span class="topbar-title">User Guide</span>
  </div>
  <button class="btn-pdf" onclick="window.print()">
    <svg width="14" height="14" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
    Download PDF
  </button>
</div>

<div class="layout">

  {{-- Sidebar --}}
  <nav class="sidebar">
    <div class="sidebar-brand">
      <div class="wordmark">Blue Arrow Portal</div>
      <div class="subtitle">User Guide</div>
    </div>
    <div class="nav-section">
      <div class="nav-label">Section A</div>
      <ul class="nav-links">
        <li><a href="#client-overview">Client Records</a></li>
        <li><a href="#before-you-start" class="sub">Before you start</a></li>
        <li><a href="#corporate-client" class="sub">Corporate client</a></li>
        <li><a href="#individual-client" class="sub">Individual client</a></li>
        <li><a href="#client-tips" class="sub">Tips &amp; shortcuts</a></li>
      </ul>
    </div>
    <div class="nav-section">
      <div class="nav-label">Section B</div>
      <ul class="nav-links">
        <li><a href="#accounting-overview">Accounting</a></li>
        <li><a href="#sale-invoice" class="sub">Sale invoice</a></li>
        <li><a href="#purchase-invoice" class="sub">Purchase invoice</a></li>
        <li><a href="#exchange-invoice" class="sub">Metal exchange invoice</a></li>
        <li><a href="#invoice-tips" class="sub">Tips &amp; shortcuts</a></li>
      </ul>
    </div>
  </nav>

  {{-- Main --}}
  <main class="main">

    <div class="print-header">
      <div class="ph-brand">Blue Arrow Portal — User Guide</div>
      <div class="ph-date">{{ now()->format('d M Y') }}</div>
    </div>

    <header class="page-header">
      <div class="guide-eyebrow">Blue Arrow Compliance Portal</div>
      <h1>User Guide</h1>
      <p>Step-by-step instructions for onboarding clients and creating invoices within the company portal.</p>
    </header>

    {{-- ═══════ SECTION A ═══════ --}}
    <section class="section" id="client-overview">
      <div class="print-section-link">
        <span>Section A — Client Record Generation</span>
        <a href="#toc">↑ Back to contents</a>
      </div>
      <div class="section-header">
        <div class="section-letter">A</div>
        <h2>Client Record Generation</h2>
      </div>

      <p>Every client — whether a company or an individual — must be onboarded through a structured multi-step form. The form collects identity, compliance, and risk information required by AML regulations. Draft progress is saved automatically in your browser, so you can leave and return without losing data.</p>

      <div id="before-you-start">
        <h3>Before You Start</h3>
        <p>Gather the following before opening the form:</p>

        <h4>For corporate clients</h4>
        <ul class="field-list">
          <li><span class="field-name">Trade licence</span><span>Number, issuing authority, issue and expiry dates</span></li>
          <li><span class="field-name">Certificate of incorporation</span><span>Country of incorporation, legal status</span></li>
          <li><span class="field-name">Signatory details</span><span>Full name, passport number, nationality, date of birth</span></li>
          <li><span class="field-name">Shareholders / UBOs</span><span>Name and ownership % for any shareholder above 25%</span></li>
          <li><span class="field-name">Supporting documents</span><span>Trade licence, MoA, passport copies (PDF or image)</span></li>
        </ul>

        <h4>For individual clients</h4>
        <ul class="field-list">
          <li><span class="field-name">Passport or Emirates ID</span><span>At least one is required</span></li>
          <li><span class="field-name">Contact details</span><span>Email, phone, address</span></li>
          <li><span class="field-name">Source of funds</span><span>Occupation, employer, income range</span></li>
        </ul>
      </div>

      <hr class="divider">

      <div id="corporate-client">
        <h3>Creating a Corporate Client <span class="tag">9 steps</span></h3>
        <p>Navigate to <strong>Clients → New Client</strong> and select <strong>Corporate</strong>. Work through each tab in order — the <strong>Next</strong> button validates the current step before advancing.</p>

        <div class="wizard-steps">
          <table>
            <thead><tr><th>#</th><th>Step</th><th>What to complete</th></tr></thead>
            <tbody>
              <tr><td>1</td><td>Profile</td><td>Company name, trade licence number, issuing authority, issue and expiry dates, country of incorporation, legal status, contact email, phone, and address. Select the client's risk tier and CDD type.</td></tr>
              <tr><td>2</td><td>Signatories</td><td>Add at least one authorised signatory. For each: full name, passport number, nationality, date of birth, and role (e.g. Director, Authorised Signatory). Click <strong>+ Add signatory</strong> to add more.</td></tr>
              <tr><td>3</td><td>Shareholders</td><td>Add all shareholders holding 25% or more. Enter name, nationality, and ownership percentage. Leave blank if ownership is fully disclosed through a listed entity.</td></tr>
              <tr><td>4</td><td>AML / CDD</td><td>Business activity, source of funds, estimated transaction volumes, PEP status, and country risk notes. This section drives the risk rating calculation.</td></tr>
              <tr><td>5</td><td>Documents</td><td>Upload supporting documents: trade licence, Memorandum of Association, passport copies for signatories, proof of address. Each document can be labelled and given an expiry date.</td></tr>
              <tr><td>6</td><td>Undertaking</td><td>Three sets of yes/no compliance questions — EU conduct standards, due diligence programme, and counterparty profile. Answer all questions that apply to the client's business.</td></tr>
              <tr><td>7</td><td>Declarations</td><td>Client declarations confirming the accuracy of the information provided and consent to AML checks. Review with the client before ticking.</td></tr>
              <tr><td>8</td><td>Screening</td><td>Run a sanctions and PEP screen on the client and its key persons. Results are logged automatically. Add manual notes if needed.</td></tr>
              <tr><td>9</td><td>Review</td><td>Summary of all entered data. Review for accuracy, then click <strong>Submit</strong> to create the record. You can go back to any step to correct information.</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div id="individual-client">
        <h3>Creating an Individual Client <span class="tag">6 steps</span></h3>
        <p>Select <strong>Individual</strong> at the top of the new client form. The process is shorter but follows the same validation logic.</p>

        <div class="wizard-steps">
          <table>
            <thead><tr><th>#</th><th>Step</th><th>What to complete</th></tr></thead>
            <tbody>
              <tr><td>1</td><td>Profile</td><td>Full name, passport number and/or Emirates ID number (at least one required), nationality, date of birth, contact email, phone, address, and occupation.</td></tr>
              <tr><td>2</td><td>AML / CDD</td><td>Source of funds, estimated transaction value, PEP declaration, and country of residence risk tier.</td></tr>
              <tr><td>3</td><td>Documents</td><td>Upload passport copy and/or Emirates ID, plus any additional identity documents required for enhanced due diligence.</td></tr>
              <tr><td>4</td><td>Declarations</td><td>Client declaration of accuracy and AML consent.</td></tr>
              <tr><td>5</td><td>Screening</td><td>Sanctions and PEP screen. Results are stored on the client record.</td></tr>
              <tr><td>6</td><td>Review</td><td>Final review. Click <strong>Submit</strong> to save the record.</td></tr>
            </tbody>
          </table>
        </div>
      </div>

      <div id="client-tips">
        <h3>Tips &amp; Shortcuts</h3>
        <div class="steps">
          <div class="step">
            <div class="step-num">①</div>
            <div class="step-body">
              <strong>Auto-fill from a document</strong>
              <p>At the top of the new client form there is an <em>Auto-fill from document</em> panel. Upload a passport, Emirates ID, or trade licence and the AI will extract and pre-fill all matching fields. Always verify the extracted data before proceeding.</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">②</div>
            <div class="step-body">
              <strong>Draft auto-save</strong>
              <p>Your progress is saved to the browser automatically. If you close the tab, reopening the New Client form will restore your draft. Drafts are stored per device and browser.</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">③</div>
            <div class="step-body">
              <strong>Duplicate detection</strong>
              <p>The form checks for existing clients with the same passport number, Emirates ID, or trade licence. If a duplicate is found, you will be shown a link to the existing record so you can add a transaction there instead.</p>
            </div>
          </div>
          <div class="step">
            <div class="step-num">④</div>
            <div class="step-body">
              <strong>Editing after creation</strong>
              <p>Open the client profile and click <strong>Edit</strong> on any section. Document uploads, signatories, and shareholders can all be updated after the record is created.</p>
            </div>
          </div>
        </div>
        <div class="note">
          <strong>Note</strong>
          Submitting a client record does not approve them. The record remains in <em>Pending</em> status until reviewed by your compliance officer, who can change the status to <em>Active</em> or <em>Rejected</em>.
        </div>
      </div>
    </section>

    {{-- ═══════ SECTION B ═══════ --}}
    <section class="section" id="accounting-overview">
      <div class="print-section-link">
        <span>Section B — Accounting</span>
        <a href="#toc">↑ Back to contents</a>
      </div>
      <div class="section-header">
        <div class="section-letter">B</div>
        <h2>Accounting — Creating Invoices</h2>
      </div>

      <p>Invoices are created per client from the <strong>Accounting</strong> module. There are three invoice types, each reflecting the direction and nature of the metal transaction:</p>

      <div class="invoice-types">
        <div class="inv-card">
          <div class="inv-icon">↑</div>
          <div class="inv-name">Sale</div>
          <div class="inv-desc">You are selling metal to the client. The client is the buyer.</div>
        </div>
        <div class="inv-card">
          <div class="inv-icon">↓</div>
          <div class="inv-name">Purchase</div>
          <div class="inv-desc">You are buying metal from the client. The client is the seller.</div>
        </div>
        <div class="inv-card">
          <div class="inv-icon">⇄</div>
          <div class="inv-name">Exchange</div>
          <div class="inv-desc">Metal is exchanged — one metal type against another or against cash settlement.</div>
        </div>
      </div>

      <div class="note">
        <strong>Before creating an invoice</strong>
        The client must already exist in the system and be in <em>Active</em> status. Navigate to <strong>Accounting → Invoices → New Invoice</strong>, or open the client's profile and click <strong>New Invoice</strong> from their accounting tab.
      </div>

      <hr class="divider">

      <div id="sale-invoice">
        <h3>Sale Invoice</h3>
        <p>Use a Sale invoice when your company is selling gold, silver, or other precious metals to the client.</p>
        <div class="steps">
          <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Select the client</strong><p>Type to search by company or individual name. The client must be active.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Set Invoice type to Sale</strong><p>Choose <em>Sale</em> from the Invoice type dropdown.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Choose pricing method</strong><p><em>Fixed</em> — the price per gram is agreed and locked at invoice creation. Enter the spot price per troy oz (USD) and the USD/AED rate; the system calculates the AED rate per gram automatically. <em>Unfixed</em> — price will be confirmed later; a placeholder invoice is created and the price applied when the trade is fixed.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Enter invoice details</strong><p>Set the invoice date, currency (default AED), exchange rate if non-AED, and an optional party reference (your client's own reference number).</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><strong>Add line items</strong><p>Click <strong>+ Add line</strong> for each batch of metal. Enter metal type, purity (e.g. 999.9), gross weight in grams, and number of pieces. Pure weight and line amount are calculated automatically from the metal rate.</p></div></div>
          <div class="step"><div class="step-num">6</div><div class="step-body"><strong>Apply premium or discount</strong><p>Enter a premium or discount amount in AED if applicable. These adjust the invoice total and appear as separate line items on the printed invoice.</p></div></div>
          <div class="step"><div class="step-num">7</div><div class="step-body"><strong>Save the invoice</strong><p>Click <strong>Save Invoice</strong>. The system assigns an invoice number and sets status to <em>Pending</em>. The invoice can be printed or emailed from the invoice detail page.</p></div></div>
        </div>
      </div>

      <hr class="divider">

      <div id="purchase-invoice">
        <h3>Purchase Invoice</h3>
        <p>Use a Purchase invoice when your company is buying metal from the client — i.e., the client is delivering metal to you.</p>
        <div class="steps">
          <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Select client and set type to Purchase</strong><p>The client here is your supplier. Set Invoice type to <em>Purchase</em>.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Set pricing and rates</strong><p>Choose Fixed or Unfixed, enter the spot price per oz (USD) and the USD/AED rate. For a purchase, the rate applied should reflect the agreed buy price.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Add line items for metal received</strong><p>For each parcel received, enter metal type, purity, gross weight, and piece count. The system calculates the amount payable to the client based on the metal rate.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Apply refining charges or discounts</strong><p>If a refining charge or assay fee applies, enter this as a negative premium (discount). This reduces the net amount payable to the client.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><strong>Save and issue</strong><p>Save the invoice. A purchase invoice acts as a receipt of goods and a commitment to pay. Link an outgoing payment to this invoice once settlement is made.</p></div></div>
        </div>
        <div class="note"><strong>Tip</strong>If the client delivered metal and is expecting payment, go to the client's <em>Payments</em> tab and record an outgoing payment against this purchase invoice to mark it settled.</div>
      </div>

      <hr class="divider">

      <div id="exchange-invoice">
        <h3>Metal Exchange Invoice</h3>
        <p>Use an Exchange invoice when the transaction involves swapping one metal for another, or exchanging metal against a different denomination or form — for example, bullion bars against coins, or gold against silver at an agreed rate.</p>
        <div class="steps">
          <div class="step"><div class="step-num">1</div><div class="step-body"><strong>Select client and set type to Exchange</strong><p>Set Invoice type to <em>Exchange</em>.</p></div></div>
          <div class="step"><div class="step-num">2</div><div class="step-body"><strong>Set the metal rates for all metals involved</strong><p>If the exchange involves more than one metal (e.g. gold and silver), enter the spot rate per oz and USD/AED rate for each metal separately.</p></div></div>
          <div class="step"><div class="step-num">3</div><div class="step-body"><strong>Add outgoing metal lines</strong><p>Add line items for the metal your company is delivering to the client. Label each line clearly with metal type and purity.</p></div></div>
          <div class="step"><div class="step-num">4</div><div class="step-body"><strong>Add incoming metal lines</strong><p>Add line items for the metal your company is receiving from the client. The system nets the two sides to show a cash settlement amount, if any.</p></div></div>
          <div class="step"><div class="step-num">5</div><div class="step-body"><strong>Apply a cash settlement if needed</strong><p>If the two sides do not balance exactly, use the premium/discount field to record the difference and annotate the reason in the party reference field.</p></div></div>
          <div class="step"><div class="step-num">6</div><div class="step-body"><strong>Save the invoice</strong><p>Save and issue. Both parties should sign the exchange confirmation before the metals are transferred.</p></div></div>
        </div>
      </div>

      <hr class="divider">

      <div id="invoice-tips">
        <h3>Tips &amp; Shortcuts</h3>
        <div class="steps">
          <div class="step"><div class="step-num">①</div><div class="step-body"><strong>Unfixed invoices</strong><p>Create an Unfixed invoice to record the transaction before the price is agreed. Once the trade is fixed, open the invoice, click <strong>Fix price</strong>, enter the agreed rate, and save. The invoice updates to Fixed status automatically.</p></div></div>
          <div class="step"><div class="step-num">②</div><div class="step-body"><strong>VAT on metal lines</strong><p>Investment-grade gold (purity ≥ 99%) is typically zero-rated. For silver, platinum, or fabricated gold, tick the <em>Metal VAT</em> checkbox on the line and enter the applicable rate. The system adds VAT to the line subtotal.</p></div></div>
          <div class="step"><div class="step-num">③</div><div class="step-body"><strong>Linking payments</strong><p>After saving an invoice, go to the client's <em>Payments</em> tab to record a payment. Select the invoice from the dropdown to link it directly. Once the full amount is received or paid, the invoice status changes to <em>Settled</em>.</p></div></div>
          <div class="step"><div class="step-num">④</div><div class="step-body"><strong>Printing and PDF export</strong><p>Open any invoice and click <strong>Print / Export PDF</strong>. The printed invoice shows your company letterhead, the client's details, all line items, the metal rate used, and the total in AED.</p></div></div>
        </div>
        <div class="note">
          <strong>Important</strong>
          All invoice amounts are stored and displayed in AED. If a transaction is agreed in USD, enter the USD/AED rate at the time of the deal. The rate is locked on the invoice and cannot be changed after saving.
        </div>
      </div>

    </section>

  </main>
</div>

<script>
  const links = document.querySelectorAll('.nav-links a');
  const targets = [...links].map(a => document.querySelector(a.getAttribute('href'))).filter(Boolean);
  const observer = new IntersectionObserver(entries => {
    entries.forEach(e => {
      if (e.isIntersecting) {
        links.forEach(l => l.classList.remove('active'));
        const active = [...links].find(l => l.getAttribute('href') === '#' + e.target.id);
        if (active) active.classList.add('active');
      }
    });
  }, { rootMargin: '-10% 0px -70% 0px' });
  targets.forEach(t => observer.observe(t));
</script>

</body>
</html>
