// Daily regulatory check for the policy manuals.
// Reads the official sources, compares them with the last known state and reports changes to the
// portal's "Regulatory updates" page. It never edits a manual.
//
// Runs on the administrator's PC (a real browser is needed for some sources):
//   node watch.cjs            normal run
//   node watch.cjs --dry      show what would be reported, change nothing
//
// Files next to this script:
//   config.json   { "endpoint": "https://.../api/v1/policy-watch", "token": "..." }
//   state.json    last known state of every source (created by the first run)
//   run.log       one line per run
const fs = require('fs');
const path = require('path');
const crypto = require('crypto');
const { spawnSync } = require('child_process');

const HOME = __dirname;
const DRY = process.argv.includes('--dry');
const CFG = JSON.parse(fs.readFileSync(path.join(HOME, 'config.json'), 'utf8'));
const STATE_FILE = path.join(HOME, 'state.json');
const state = fs.existsSync(STATE_FILE) ? JSON.parse(fs.readFileSync(STATE_FILE, 'utf8')) : {};
const UA = 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/130.0.0.0 Safari/537.36';

const MOET = 'https://www.moet.gov.ae';
const EOCN_GUIDANCE = 'https://www.uaeiec.gov.ae/API/Upload/DownloadFile?FileID=82395142-93a0-4d4e-b1b6-55be17db7688';
const NC_PAGE = 'https://namlcftc.gov.ae/en/resources/high-risk-jurisdictions/';
const FATF_INDEX = 'https://www.fatf-gafi.org/en/publications/High-risk-and-other-monitored-jurisdictions.html';

const sha = (s) => crypto.createHash('sha256').update(s).digest('hex');
const log = (m) => console.log(m);

function decode(s) {
  return s.replace(/&nbsp;/g, ' ').replace(/&amp;/g, '&').replace(/&quot;/g, '"').replace(/&#39;|&apos;/g, "'")
    .replace(/&lt;/g, '<').replace(/&gt;/g, '>').replace(/&rsquo;|&#8217;/g, "'").replace(/&ndash;|&#8211;/g, '-')
    .replace(/&#(\d+);/g, (_, n) => String.fromCharCode(+n));
}
const strip = (h) => decode(h.replace(/<script[\s\S]*?<\/script>/gi, ' ').replace(/<style[\s\S]*?<\/style>/gi, ' ').replace(/<[^>]+>/g, '\n'));
const clean = (s) => s.replace(/\s+/g, ' ').trim();

async function fetchBuf(url) {
  const ctl = new AbortController();
  const timer = setTimeout(() => ctl.abort(), 90000);
  try {
    const r = await fetch(url, { headers: { 'User-Agent': UA, Accept: '*/*', 'Accept-Language': 'en-US,en;q=0.9' }, signal: ctl.signal, redirect: 'follow' });
    if (!r.ok) throw new Error('HTTP ' + r.status);
    return Buffer.from(await r.arrayBuffer());
  } finally { clearTimeout(timer); }
}

function findChrome() {
  const c = [
    'C:/Program Files/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Google/Chrome/Application/chrome.exe',
    'C:/Program Files (x86)/Microsoft/Edge/Application/msedge.exe',
  ];
  return c.find((p) => fs.existsSync(p));
}

// Some official sites refuse plain HTTP clients but serve a real (headless) browser.
function browserDom(url) {
  const chrome = findChrome();
  if (!chrome) throw new Error('no Chrome or Edge found');
  const r = spawnSync(chrome, [
    '--headless=new', '--disable-gpu', '--no-sandbox', '--disable-blink-features=AutomationControlled',
    '--user-data-dir=' + path.join(HOME, 'browser-profile'), '--user-agent=' + UA,
    '--virtual-time-budget=15000', '--dump-dom', url,
  ], { encoding: 'utf8', timeout: 150000, maxBuffer: 64 * 1024 * 1024, windowsHide: true });
  const dom = r.stdout || '';
  if (dom.length < 2000) throw new Error('empty page');
  if (/<title>\s*Just a moment/i.test(dom)) throw new Error('blocked by a bot check');
  return dom;
}

const item = (source_key, key, title, change_type, summary, extra = {}) => ({
  source_key, fingerprint: sha(source_key + '|' + key), title, change_type, summary, ...extra,
});

// ── Sources ───────────────────────────────────────────────────────────────
const SOURCES = [
  {
    key: 'moet_aml',
    title: 'MoET AML page (legislation, circulars, guidance)',
    affects: 'AML Manual 1.3 (legal framework) and the sections the item covers; Supply Chain Policy 1.3',
    async run(prev) {
      const html = (await fetchBuf(MOET + '/en/aml')).toString('utf8');
      const cur = {};
      for (const m of html.matchAll(/<a\b[^>]*href="([^"]*\/documents\/[^"]+)"[^>]*>([\s\S]*?)<\/a>/gi)) {
        const href = decode(m[1]);
        const p = decodeURIComponent(href.split('?')[0]).replace(/\/[0-9a-f-]{36}$/i, '');
        const t = (href.match(/[?&]t=(\d+)/) || [])[1] || '';
        let title = clean(strip(m[2])).replace(/\s*(View (Guide|Document|Report)|Read More)\s*[^\w]*$/i, '').trim();
        if (!title) title = decodeURIComponent(p.split('/').pop()).replace(/\.pdf$/i, '').replace(/\+/g, ' ');
        if (!cur[p] || title.length > cur[p].title.length) cur[p] = { title, t, href: href.startsWith('http') ? href : MOET + href };
      }
      if (Object.keys(cur).length < 10) throw new Error('page layout changed (' + Object.keys(cur).length + ' documents found)');
      const items = [];
      if (prev) {
        for (const [p, v] of Object.entries(cur)) {
          if (!prev[p]) items.push(item('moet_aml', 'new|' + p, 'New on the MoET AML page: ' + v.title, 'new', 'A document has been added to the MoET AML page.', { url: v.href }));
          else if (prev[p].t !== v.t && v.t) items.push(item('moet_aml', 'chg|' + p + '|' + v.t, 'Updated on the MoET AML page: ' + v.title, 'changed', 'A document on the MoET AML page was replaced with a newer file.', { url: v.href }));
        }
        for (const [p, v] of Object.entries(prev)) {
          if (!cur[p]) items.push(item('moet_aml', 'rem|' + p, 'Removed from the MoET AML page: ' + v.title, 'removed', 'A document is no longer listed. It may have been superseded.', { url: v.href }));
        }
      }
      return { next: cur, items, note: Object.keys(cur).length + ' documents listed' };
    },
  },
  {
    key: 'moet_rs',
    title: 'MoET responsible sourcing of gold page',
    affects: 'Supply Chain Policy (all sections); AML Manual 7.4 and 7.5',
    async run(prev) {
      const url = MOET + '/en/diligence-regulations-for-responsible-sourcing-of-gold';
      const html = (await fetchBuf(url)).toString('utf8');
      const text = clean(strip(html));
      const a = text.indexOf('Due Diligence Regulations for Responsible Sourcing of Gold in the UAE');
      const b = text.indexOf('Did you find the content');
      if (a < 0 || b < 0) throw new Error('page layout changed');
      const docs = [...html.matchAll(/href="([^"]*\/documents\/[^"?]+)/gi)].map((m) => decodeURIComponent(m[1])).filter((v, i, x) => x.indexOf(v) === i).sort();
      const cur = { hash: sha(text.slice(a, b)), docs };
      const items = [];
      if (prev && (prev.hash !== cur.hash || JSON.stringify(prev.docs) !== JSON.stringify(cur.docs))) {
        items.push(item('moet_rs', cur.hash + sha(JSON.stringify(docs)), 'MoET responsible sourcing page changed', 'changed',
          'The wording or the reference documents on the MoET responsible sourcing of gold page have changed.', { url }));
      }
      return { next: cur, items, note: 'content checked' };
    },
  },
  {
    key: 'eocn_tfs',
    title: 'EOCN guidance on targeted financial sanctions (PDF)',
    affects: 'AML Manual section 6 (targeted financial sanctions) and the report names and deadlines in 8.4',
    async run(prev) {
      const buf = await fetchBuf(EOCN_GUIDANCE);
      if (buf.slice(0, 5).toString() !== '%PDF-') throw new Error('not a PDF (blocked?)');
      const cur = { sha: sha(buf.toString('latin1')), size: buf.length };
      const items = [];
      if (prev && prev.sha !== cur.sha) {
        items.push(item('eocn_tfs', cur.sha, 'EOCN TFS guidance file has changed', 'changed',
          'The EOCN guidance PDF is different from the one last reviewed (previously ' + prev.size + ' bytes, now ' + cur.size + '). Check its version table for what was amended.', { url: EOCN_GUIDANCE }));
      }
      return { next: cur, items, note: Math.round(cur.size / 1024) + ' KB' };
    },
  },
  {
    key: 'national_committee',
    title: 'National Committee high-risk jurisdictions page',
    affects: 'AML Manual Appendix C and section 4.7',
    async run(prev) {
      const dom = browserDom(NC_PAGE);
      const links = [...dom.matchAll(/href="(https:\/\/www\.fatf-gafi\.org[^"]*(?:call-for-action|increased-monitoring)[^"]*)"/gi)].map((m) => m[1]).filter((v, i, x) => x.indexOf(v) === i).sort();
      if (!links.length) throw new Error('FATF links not found on the page');
      const text = clean(strip(dom));
      const a = text.indexOf('High Risk Countries & Counter Measures');
      const cur = { links, hash: a >= 0 ? sha(text.slice(a, a + 6000)) : '' };
      const items = [];
      if (prev && (JSON.stringify(prev.links) !== JSON.stringify(links))) {
        items.push(item('national_committee', sha(JSON.stringify(links)), 'National Committee changed its high-risk list references', 'list_changed',
          'The UAE National Committee page now points to: ' + links.join(', ') + '. Previously: ' + (prev.links || []).join(', '), { url: NC_PAGE }));
      } else if (prev && prev.hash !== cur.hash) {
        items.push(item('national_committee', cur.hash, 'National Committee high-risk page wording changed', 'changed', 'The measures described on the National Committee page have changed.', { url: NC_PAGE }));
      }
      return { next: cur, items, note: links.length + ' FATF links' };
    },
  },
  {
    key: 'fatf_lists',
    title: 'FATF lists (call for action and increased monitoring)',
    affects: 'AML Manual Appendix C register and section 4.7',
    async run(prev) {
      const months = ['january', 'february', 'march', 'april', 'may', 'june', 'july', 'august', 'september', 'october', 'november', 'december'];
      const idx = browserDom(FATF_INDEX);
      const latest = (kind) => {
        const re = new RegExp('High-risk-and-other-monitored-jurisdictions/(' + kind + '-([a-z]+)-(\\d{4}))\\.html', 'gi');
        let best = null;
        for (const m of idx.matchAll(re)) {
          const mi = months.indexOf(m[2].toLowerCase());
          const k = +m[3] * 12 + mi;
          if (mi >= 0 && (!best || k > best.k)) best = { k, slug: m[1] };
        }
        if (!best) throw new Error('no ' + kind + ' publication found');
        return 'https://www.fatf-gafi.org/en/publications/High-risk-and-other-monitored-jurisdictions/' + best.slug + '.html';
      };
      const read = (url) => {
        const dom = browserDom(url);
        const title = clean(decode((dom.match(/<title>([^<]*)<\/title>/i) || [])[1] || ''));
        const lines = strip(dom).split('\n').map((l) => l.trim()).filter(Boolean);
        const i = lines.indexOf('Country');
        const j = lines.indexOf('Topic');
        if (i < 0 || j < i) throw new Error('country list not found on ' + url);
        return { url, title, countries: lines.slice(i + 1, j).join(', ') };
      };
      const cur = { cfa: read(latest('call-for-action')), im: read(latest('increased-monitoring')) };
      const items = [];
      if (prev) {
        for (const [k, label] of [['cfa', 'Call for Action ("black list")'], ['im', 'Increased Monitoring ("grey list")']]) {
          const a = prev[k], b = cur[k];
          if (a.url !== b.url || a.countries !== b.countries) {
            const same = a.countries === b.countries;
            items.push(item('fatf_lists', k + '|' + b.url + '|' + sha(b.countries), 'FATF ' + label + ' updated: ' + b.title, 'list_changed',
              (same ? 'New publication, same countries.' : 'The list of countries changed.') + '\nBefore: ' + a.countries + '\nNow: ' + b.countries,
              { url: b.url, details: { before: a.countries, now: b.countries } }));
          }
        }
      }
      return { next: cur, items, note: cur.cfa.title + '; ' + cur.im.title };
    },
  },
  {
    key: 'cahra_quarterly',
    title: 'EU indicative CAHRA list (quarterly manual check)',
    affects: 'AML Manual Appendix F and section 7.5; Supply Chain Policy 4.4',
    async run() {
      const d = new Date();
      const items = [];
      if ([2, 5, 8, 11].includes(d.getMonth())) {
        const q = d.getFullYear() + '-Q' + (Math.floor(d.getMonth() / 3) + 1);
        items.push(item('cahra_quarterly', q, 'Quarterly check due: EU indicative CAHRA list (' + q + ')', 'reminder',
          'The EU list is reviewed quarterly and sits behind a terms-of-use page, so it is checked by hand. Open cahraslist.net, compare the country list with Appendix F, and update the register if it changed.', { url: 'https://www.cahraslist.net/cahras' }));
      }
      return { next: true, items, note: 'reminder only; checked by hand' };
    },
  },
];

(async () => {
  const results = [], items = [], next = { ...state };
  for (const s of SOURCES) {
    try {
      const r = await s.run(state[s.key]);
      next[s.key] = r.next;
      for (const it of r.items) items.push({ ...it, affected_sections: s.affects });
      results.push({ key: s.key, title: s.title, ok: true, note: (state[s.key] ? '' : 'baseline recorded; ') + r.note });
      log('OK     ' + s.key + ' (' + r.items.length + ' change(s)) ' + r.note);
    } catch (e) {
      results.push({ key: s.key, title: s.title, ok: false, note: String(e.message || e).slice(0, 300) });
      log('FAILED ' + s.key + ': ' + (e.message || e));
    }
  }
  const body = { ran_at: new Date().toISOString(), sources: results, items };
  if (DRY) { console.log(JSON.stringify(body, null, 2)); return; }
  try {
    const r = await fetch(CFG.endpoint, {
      method: 'POST',
      headers: { Authorization: 'Bearer ' + CFG.token, 'Content-Type': 'application/json', Accept: 'application/json' },
      body: JSON.stringify(body),
    });
    if (!r.ok) throw new Error('portal answered HTTP ' + r.status);
    fs.writeFileSync(STATE_FILE, JSON.stringify(next, null, 1));   // only after the portal has the results
    fs.appendFileSync(path.join(HOME, 'run.log'), new Date().toISOString() + ' ok, ' + items.length + ' change(s), ' + results.filter((x) => !x.ok).length + ' failed\n');
  } catch (e) {
    fs.appendFileSync(path.join(HOME, 'run.log'), new Date().toISOString() + ' could not report: ' + e.message + '\n');
    log('Could not report to the portal: ' + e.message);
    process.exitCode = 1;
  }
})();
