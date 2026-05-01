@extends('layouts.admin')
@section('title', 'Marketing — MailCommand')
@section('page-title', 'Marketing')

@section('content')
{{-- React + Babel via CDN --}}
<script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<style>
/* ── Mailer-specific styles that Tailwind CDN doesn't cover ── */
.mc-textarea { font-family: 'DM Mono', 'Courier New', monospace; font-size: 12px; line-height: 1.65; resize: vertical; min-height: 200px; }
.mc-code { font-family: 'DM Mono', 'Courier New', monospace; font-size: 11px; white-space: pre; }
.mc-preview { width: 100%; height: 420px; border: 1px solid #e2e8f0; border-radius: 10px; background: #fff; }
.mc-search-icon { position: absolute; left: 10px; top: 50%; transform: translateY(-50%); color: #94a3b8; pointer-events: none; }
.mc-spinner { display: inline-block; width: 13px; height: 13px; border: 2px solid rgba(0,0,0,.15); border-top-color: transparent; border-radius: 50%; animation: mc-spin .6s linear infinite; vertical-align: middle; }
@keyframes mc-spin { to { transform: rotate(360deg); } }
.mc-toast-wrap { position: fixed; top: 20px; right: 20px; display: flex; flex-direction: column; gap: 8px; z-index: 9999; pointer-events: none; }
.mc-toast { padding: 10px 16px; border-radius: 8px; font-size: 13px; font-weight: 500; max-width: 320px; animation: mc-toastIn .2s ease; pointer-events: auto; }
.mc-toast-success { background: #059669; color: #fff; }
.mc-toast-error   { background: #dc2626; color: #fff; }
.mc-toast-info    { background: #2563eb; color: #fff; }
@keyframes mc-toastIn { from { transform: translateX(110%); opacity: 0; } to { transform: translateX(0); opacity: 1; } }
.mc-progress-bar  { height: 4px; background: #e2e8f0; border-radius: 2px; overflow: hidden; }
.mc-progress-fill { height: 100%; background: #1e40af; transition: width .3s; }
.mc-table th { background: #f8fafc; color: #6b7280; font-weight: 600; text-align: left; padding: 8px 14px; border-bottom: 1px solid #e5e7eb; font-size: 11px; text-transform: uppercase; letter-spacing: .5px; }
.mc-table td { padding: 7px 14px; border-bottom: 1px solid #f3f4f6; color: #4b5563; font-size: 12px; vertical-align: middle; }
.mc-table tr:last-child td { border-bottom: none; }
.mc-table tr:hover td { background: #f9fafb; }
.mc-group-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(180px,1fr)); gap: 10px; }
</style>

<div id="mc-root"></div>

@verbatim
<script type="text/babel">
const {useState, useEffect, useRef, useMemo} = React;

/* ── Constants ── */
const API = '/marketing/api';
const ADMIN_CONTACT = { id: 'admin', name: 'Blue Arrow Admin', email: 'contact@bluearrow.ae', company: 'Blue Arrow' };

/* ── Utils ── */
const ls   = (k, d) => { try { const v = localStorage.getItem('mc_' + k); return v ? JSON.parse(v) : d; } catch { return d; } };
const save = (k, v) => { try { localStorage.setItem('mc_' + k, JSON.stringify(v)); } catch {} };
const today    = () => new Date().toISOString().split('T')[0];
const fmtDate  = d  => new Date(d).toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
const fmtTime  = ts => new Date(ts).toLocaleTimeString('en-GB', { hour: '2-digit', minute: '2-digit' });
const makeGroups = contacts => {
    const g = [];
    for (let i = 0; i < contacts.length; i += 100) {
        const sl = contacts.slice(i, i + 100), n = Math.floor(i / 100) + 1;
        g.push({ id: `auto-${n}`, name: `Group ${n}`, contactIds: sl.map(c => c.id), count: sl.length, firstId: sl[0]?.id, lastId: sl[sl.length - 1]?.id });
    }
    return g;
};

/* ── Toasts ── */
function Toasts({ toasts }) {
    return <div className="mc-toast-wrap">{toasts.map(t => <div key={t.id} className={`mc-toast mc-toast-${t.type}`}>{t.msg}</div>)}</div>;
}

/* ── Badge helper ── */
function Badge({ children, color = 'gray' }) {
    const colors = {
        gray:   'bg-gray-100 text-gray-600',
        blue:   'bg-blue-100 text-blue-700',
        green:  'bg-green-100 text-green-700',
        red:    'bg-red-100 text-red-700',
        amber:  'bg-amber-100 text-amber-700',
        purple: 'bg-purple-100 text-purple-700',
    };
    return <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${colors[color] ?? colors.gray}`}>{children}</span>;
}

/* ── Subscriber Modal ── */
function SubModal({ mode, contact, apiEndpoint, onDone, onClose, addToast, totalContacts }) {
    const [form, setForm] = useState(mode === 'edit' ? { ...contact } : { list_id: 1, company: '', name: '', phone: '', email: '' });
    const [saving, setSaving] = useState(false);
    const upd = (k, v) => setForm(p => ({ ...p, [k]: v }));
    const newGroupNum = Math.floor(totalContacts / 100) + 1;

    const submit = async () => {
        if (!form.name.trim()) { addToast('Name is required', 'error'); return; }
        if (!form.email.trim()) { addToast('Email is required', 'error'); return; }
        setSaving(true);
        try {
            const url = mode === 'edit' ? `${apiEndpoint}?id=${contact.id}` : apiEndpoint;
            const r = await fetch(url, { method: mode === 'edit' ? 'PUT' : 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(form) });
            const d = await r.json();
            if (d.error) throw new Error(d.error);
            addToast(mode === 'edit' ? 'Subscriber updated' : 'Subscriber added', 'success');
            onDone();
        } catch (e) { addToast('Error: ' + e.message, 'error'); }
        setSaving(false);
    };

    return (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000 }} onClick={onClose}>
            <div className="bg-white rounded-2xl shadow-xl p-6 w-full max-w-md mx-4" onClick={e => e.stopPropagation()}>
                <div className="flex items-center justify-between mb-5">
                    <h3 className="text-base font-semibold text-gray-800">{mode === 'edit' ? 'Edit subscriber' : 'Add subscriber'}</h3>
                    <button onClick={onClose} className="text-gray-400 hover:text-gray-600 text-xl leading-none">×</button>
                </div>
                {mode === 'add' && <div className="mb-4 p-3 bg-blue-50 border border-blue-200 rounded-lg text-xs text-blue-700">Will be added to <strong>Group {newGroupNum}</strong></div>}
                <div className="space-y-3">
                    {[['Full name *', 'name', 'text'], ['Email *', 'email', 'email'], ['Company', 'company', 'text'], ['Phone', 'phone', 'tel']].map(([label, key, type]) => (
                        <div key={key}>
                            <label className="block text-xs font-medium text-gray-600 mb-1">{label}</label>
                            <input type={type} value={form[key] || ''} onChange={e => upd(key, e.target.value)}
                                className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                        </div>
                    ))}
                </div>
                <div className="flex gap-3 justify-end mt-5">
                    <button onClick={onClose} className="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button onClick={submit} disabled={saving} className="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-40">
                        {saving ? <><span className="mc-spinner" /> Saving…</> : mode === 'edit' ? 'Save changes' : 'Add subscriber'}
                    </button>
                </div>
            </div>
        </div>
    );
}

/* ── Delete Confirm ── */
function DeleteConfirm({ contacts, apiEndpoint, onDone, onClose, addToast, bulkIds }) {
    const [deleting, setDeleting] = useState(false);
    const ids = bulkIds || [];
    const label = ids.length === 1 ? contacts.find(c => c.id === ids[0])?.name : `${ids.length} subscribers`;
    const doDelete = async () => {
        setDeleting(true);
        try {
            await Promise.all(ids.map(id => fetch(`${apiEndpoint}?id=${id}`, { method: 'DELETE' })));
            addToast(`Deleted ${label}`, 'success');
            onDone();
        } catch (e) { addToast('Delete failed: ' + e.message, 'error'); }
        setDeleting(false);
    };
    return (
        <div style={{ position: 'fixed', inset: 0, background: 'rgba(0,0,0,.5)', display: 'flex', alignItems: 'center', justifyContent: 'center', zIndex: 1000 }} onClick={onClose}>
            <div className="bg-white rounded-2xl shadow-xl p-6 w-full max-w-sm mx-4" onClick={e => e.stopPropagation()}>
                <h3 className="text-base font-semibold text-gray-800 mb-2">Delete {label}?</h3>
                <p className="text-sm text-gray-500 mb-5">This cannot be undone.</p>
                <div className="flex gap-3 justify-end">
                    <button onClick={onClose} className="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Cancel</button>
                    <button onClick={doDelete} disabled={deleting} className="px-4 py-2 text-sm font-semibold text-white bg-red-600 rounded-lg hover:bg-red-700 disabled:opacity-40">
                        {deleting ? <><span className="mc-spinner" /> Deleting…</> : 'Delete'}
                    </button>
                </div>
            </div>
        </div>
    );
}

/* ── Contacts Page ── */
function ContactsPage({ contacts, loading, onRefresh, apiEndpoint, addToast }) {
    const [q, setQ] = useState('');
    const [pg, setPg] = useState(1);
    const [sel, setSel] = useState(new Set());
    const [modal, setModal] = useState(null);
    const PER = 25;
    const filtered = contacts.filter(c => !q || [c.name, c.company, c.email, c.phone].some(f => f?.toLowerCase().includes(q.toLowerCase())));
    const pages = Math.ceil(filtered.length / PER);
    const visible = filtered.slice((pg - 1) * PER, pg * PER);
    const allChecked = sel.size === visible.length && visible.length > 0;
    const toggleAll  = () => setSel(allChecked ? new Set() : new Set(visible.map(c => c.id)));
    const toggleOne  = id => { const s = new Set(sel); s.has(id) ? s.delete(id) : s.add(id); setSel(s); };
    const closeAndRefresh = () => { setModal(null); setSel(new Set()); onRefresh(); };

    return (<>
        {modal?.mode === 'add'    && <SubModal mode="add" apiEndpoint={apiEndpoint} onDone={closeAndRefresh} onClose={() => setModal(null)} addToast={addToast} totalContacts={contacts.length} />}
        {modal?.mode === 'edit'   && <SubModal mode="edit" contact={modal.contact} apiEndpoint={apiEndpoint} onDone={closeAndRefresh} onClose={() => setModal(null)} addToast={addToast} />}
        {modal?.mode === 'delete' && <DeleteConfirm contacts={contacts} apiEndpoint={apiEndpoint} bulkIds={modal.ids} onDone={closeAndRefresh} onClose={() => setModal(null)} addToast={addToast} />}

        <div className="flex flex-wrap items-center justify-between gap-3 mb-5">
            <div className="flex items-center gap-3">
                <div className="relative">
                    <span className="mc-search-icon text-sm">⌕</span>
                    <input className="pl-8 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-64 bg-white"
                        placeholder="Search contacts…" value={q} onChange={e => { setQ(e.target.value); setPg(1); }} />
                </div>
                <Badge color="gray">{filtered.length} contacts</Badge>
                {sel.size > 0 && <Badge color="amber">{sel.size} selected</Badge>}
            </div>
            <div className="flex items-center gap-2">
                {sel.size > 0 && <button onClick={() => setModal({ mode: 'delete', ids: [...sel] })} className="px-3 py-1.5 text-xs font-semibold text-red-600 bg-red-50 border border-red-200 rounded-lg hover:bg-red-100">✕ Delete {sel.size}</button>}
                <button onClick={onRefresh} disabled={loading} className="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 bg-white">
                    {loading ? <><span className="mc-spinner" /> Loading</> : '↻ Refresh'}
                </button>
                <button onClick={() => setModal({ mode: 'add' })} className="px-3 py-1.5 text-xs font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">+ Add subscriber</button>
            </div>
        </div>

        <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            <table className="w-full mc-table">
                <thead><tr>
                    <th style={{width:40}}><input type="checkbox" onChange={toggleAll} checked={allChecked} /></th>
                    <th style={{width:50}}>ID</th><th>Company</th><th>Name</th><th>Phone</th><th>Email</th><th style={{width:110}}>Subscribed</th><th style={{width:90}}></th>
                </tr></thead>
                <tbody>
                    {visible.map(c => <tr key={c.id}>
                        <td><input type="checkbox" checked={sel.has(c.id)} onChange={() => toggleOne(c.id)} /></td>
                        <td className="text-gray-400 font-mono text-xs">{c.id}</td>
                        <td className="font-medium text-gray-800">{c.company || <span className="text-gray-300">—</span>}</td>
                        <td className="text-gray-700">{c.name}</td>
                        <td className="font-mono text-xs text-gray-500">{c.phone || <span className="text-gray-300">—</span>}</td>
                        <td className="font-mono text-xs text-blue-600">{c.email}</td>
                        <td className="text-xs text-gray-400">{c.subscribed_at?.split('T')[0] || c.subscribed_at}</td>
                        <td>
                            <div className="flex gap-1.5">
                                <button onClick={() => setModal({ mode: 'edit', contact: c })} className="px-2 py-1 text-xs text-gray-600 border border-gray-200 rounded hover:bg-gray-50">Edit</button>
                                <button onClick={() => setModal({ mode: 'delete', ids: [c.id] })} className="px-2 py-1 text-xs text-red-600 border border-red-200 rounded hover:bg-red-50">✕</button>
                            </div>
                        </td>
                    </tr>)}
                    {visible.length === 0 && <tr><td colSpan={8} className="text-center py-12 text-gray-400 text-sm">No contacts found</td></tr>}
                </tbody>
            </table>
        </div>

        {pages > 1 && <div className="flex items-center gap-1.5 mt-4">
            <button onClick={() => setPg(Math.max(1, pg - 1))} className="w-8 h-8 flex items-center justify-center border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 bg-white text-sm">‹</button>
            {Array.from({ length: Math.min(pages, 9) }, (_, i) => i + 1).map(p =>
                <button key={p} onClick={() => setPg(p)} className={`w-8 h-8 flex items-center justify-center border rounded-lg text-sm transition ${p === pg ? 'bg-blue-600 text-white border-blue-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50 bg-white'}`}>{p}</button>
            )}
            {pages > 9 && <span className="text-xs text-gray-400">…{pages}</span>}
            <button onClick={() => setPg(Math.min(pages, pg + 1))} className="w-8 h-8 flex items-center justify-center border border-gray-200 rounded-lg text-gray-500 hover:bg-gray-50 bg-white text-sm">›</button>
            <span className="text-xs text-gray-400 ml-2">{(pg - 1) * PER + 1}–{Math.min(pg * PER, filtered.length)} of {filtered.length}</span>
        </div>}
    </>);
}

/* ── Groups Page ── */
function GroupsPage({ contacts, logs }) {
    const groups = makeGroups(contacts);
    const sentIds = new Set(logs.flatMap(l => l.groupIds || []));
    const [expanded, setExpanded] = useState(new Set());
    const [search, setSearch] = useState('');
    const toggle = id => { const s = new Set(expanded); s.has(id) ? s.delete(id) : s.add(id); setExpanded(s); };

    return (<>
        <div className="flex items-center justify-between mb-5">
            <div className="flex items-center gap-3">
                <Badge color="blue">{groups.length} groups</Badge>
                <Badge color="gray">{contacts.length} subscribers · 100 per group</Badge>
            </div>
            <div className="relative">
                <span className="mc-search-icon text-sm">⌕</span>
                <input className="pl-8 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-48 bg-white"
                    placeholder="Search within groups…" value={search} onChange={e => setSearch(e.target.value)} />
            </div>
        </div>

        {groups.length === 0 ? <div className="text-center py-16 text-gray-400 text-sm">No groups yet — add subscribers first.</div> :
        <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            {/* Admin group */}
            <div className="bg-green-50 border-b border-green-100">
                <div className="flex items-center px-4 py-3 gap-3">
                    <span className="text-green-500 text-xs font-bold w-8">★</span>
                    <span className="text-xs font-mono text-green-600 w-20">Admin</span>
                    <span className="text-sm font-medium text-green-700 flex-1">Admin Group</span>
                    <span className="text-xs text-gray-400 mr-4">Always included</span>
                    <Badge color="green">auto BCC every send</Badge>
                </div>
            </div>

            {groups.map((g, gi) => {
                const sent = sentIds.has(g.id);
                const last = logs.filter(l => (l.groupIds || []).includes(g.id)).pop();
                const isOpen = expanded.has(g.id);
                const groupContacts = contacts.filter(c => g.contactIds.includes(c.id));
                const filteredContacts = search ? groupContacts.filter(c => [c.name, c.company, c.email].some(f => f?.toLowerCase().includes(search.toLowerCase()))) : groupContacts;
                if (search && filteredContacts.length === 0) return null;
                return (
                    <div key={g.id} className={gi < groups.length - 1 ? 'border-b border-gray-100' : ''}>
                        <div className={`flex items-center px-4 py-3 cursor-pointer hover:bg-gray-50 transition gap-3 ${isOpen ? 'bg-blue-50' : ''}`} onClick={() => toggle(g.id)}>
                            <span className="text-gray-400 text-xs w-4">{isOpen ? '▾' : '▸'}</span>
                            <span className="font-mono text-xs text-gray-400 w-20">Group {String(gi + 1).padStart(2, '0')}</span>
                            <span className="text-sm font-medium text-gray-800 flex-1">{g.name}</span>
                            <span className="text-xs font-mono text-gray-400 mr-4">IDs {g.firstId}–{g.lastId}</span>
                            <span className="text-xs font-mono text-gray-500 w-20 text-right mr-4">{g.count} contacts</span>
                            {sent
                                ? <Badge color="green">✓ {fmtDate(last?.date)}</Badge>
                                : <Badge color="gray">not sent yet</Badge>
                            }
                        </div>
                        {isOpen && <div className="bg-gray-50 border-t border-gray-100">
                            {filteredContacts.map((c, ci) =>
                                <div key={c.id} className={`flex items-center px-4 py-2 pl-14 gap-4 ${ci < filteredContacts.length - 1 ? 'border-b border-gray-100' : ''}`}>
                                    <span className="font-mono text-xs text-gray-400 w-10">#{c.id}</span>
                                    <span className="text-xs text-gray-700 w-44 truncate">{c.name}</span>
                                    <span className="text-xs text-gray-500 w-48 truncate">{c.company || '—'}</span>
                                    <span className="text-xs font-mono text-blue-600 truncate">{c.email}</span>
                                </div>
                            )}
                        </div>}
                    </div>
                );
            })}
        </div>}
    </>);
}

/* ── Templates ── */
const TPLS = {
  'AML Compliance': `<!DOCTYPE html><html><head><meta charset="utf-8"><title>AML Compliance</title></head><body style="background:#eef5f8;margin:0;padding:20px;font-family:Arial,sans-serif;"><table width="560" style="margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;"><tr><td><img src="https://mailer.bluearrow.ae/flyers/A1.jpeg" width="560" style="width:100%;display:block;"></td></tr><tr><td style="background:#0e8fb1;padding:14px 24px;"><p style="margin:0;font-size:11px;color:#eaf7fb;letter-spacing:.8px;text-transform:uppercase;font-weight:700;">AML Compliance for Real Estate</p></td></tr><tr><td style="padding:22px 24px;"><h1 style="margin:0 0 10px;font-size:20px;color:#0f172a;">Don't postpone compliance. Stay protected and ahead.</h1><p style="margin:0;font-size:13px;line-height:1.75;color:#475569;">Blue Arrow helps real estate firms implement practical AML controls without slowing deals.</p></td></tr><tr><td style="background:#0e8fb1;padding:12px 24px;"><p style="margin:0;font-size:11px;color:#eaf7fb;">Blue Arrow Management Consultants FZC — <a href="https://www.bluearrow.ae" style="color:#eaf7fb;">www.bluearrow.ae</a></p></td></tr></table></body></html>`,
  'Sentinel': `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Sentinel AML Screening</title></head><body style="background:#eef5f8;margin:0;padding:20px;font-family:Arial,sans-serif;"><table width="560" style="margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;"><tr><td><img src="https://mailer.bluearrow.ae/flyers/A2.jpeg" width="560" style="width:100%;display:block;"></td></tr><tr><td style="background:#0e8fb1;padding:14px 24px;"><p style="margin:0;font-size:11px;color:#eaf7fb;letter-spacing:.8px;text-transform:uppercase;font-weight:700;">Sentinel — AML Screening for Real Estate</p></td></tr><tr><td style="padding:22px 24px;"><h1 style="margin:0 0 10px;font-size:20px;color:#0f172a;">Screen smarter. Stay compliant. Protect every transaction.</h1><p style="margin:0;font-size:13px;line-height:1.75;color:#475569;">Sentinel is Blue Arrow's purpose-built AML screening tool for UAE real estate businesses.</p></td></tr><tr><td style="background:#0e8fb1;padding:12px 24px;"><p style="margin:0;font-size:11px;color:#eaf7fb;">Blue Arrow Management Consultants FZC — <a href="https://www.bluearrow.ae" style="color:#eaf7fb;">www.bluearrow.ae</a></p></td></tr></table></body></html>`,
  'General': `<!DOCTYPE html><html><head><meta charset="utf-8"><title>Blue Arrow</title></head><body style="background:#eef5f8;margin:0;padding:20px;font-family:Arial,sans-serif;"><table width="560" style="margin:0 auto;background:#fff;border-radius:10px;overflow:hidden;"><tr><td><img src="https://mailer.bluearrow.ae/flyers/A3.jpeg" width="560" style="width:100%;display:block;"></td></tr><tr><td style="background:#0e8fb1;padding:14px 24px;"><p style="margin:0;font-size:11px;color:#eaf7fb;letter-spacing:.8px;text-transform:uppercase;font-weight:700;">Blue Arrow Management Consultants FZC</p></td></tr><tr><td style="padding:22px 24px;"><h1 style="margin:0 0 10px;font-size:20px;color:#0f172a;">Your trusted compliance partner in the UAE.</h1><p style="margin:0;font-size:13px;line-height:1.75;color:#475569;">Blue Arrow provides AML compliance, risk management, and regulatory advisory services.</p></td></tr><tr><td style="background:#0e8fb1;padding:12px 24px;"><p style="margin:0;font-size:11px;color:#eaf7fb;">Blue Arrow Management Consultants FZC — <a href="https://www.bluearrow.ae" style="color:#eaf7fb;">www.bluearrow.ae</a></p></td></tr></table></body></html>`,
};

/* ── Compose Page ── */
function ComposePage({ contacts, settings, addLog, addToast }) {
    const [step, setStep]         = useState(1);
    const [subject, setSubject]   = useState('');
    const [html, setHtml]         = useState('');
    const [selGroups, setSelGroups] = useState(new Set());
    const [sending, setSending]   = useState(false);
    const [progress, setProgress] = useState(0);
    const [results, setResults]   = useState(null);
    const ifrRef = useRef(null);
    const groups = makeGroups(contacts);

    useEffect(() => {
        if (!ifrRef.current) return;
        const doc = ifrRef.current.contentDocument;
        doc.open(); doc.write(html || '<div style="color:#aaa;text-align:center;padding:60px;font-family:sans-serif;font-size:14px">Preview updates as you type…</div>'); doc.close();
    }, [html]);

    const recipients = useMemo(() => {
        const ids = new Set();
        selGroups.forEach(gid => { const g = groups.find(ag => ag.id === gid); if (g) g.contactIds.forEach(id => ids.add(id)); });
        return contacts.filter(c => ids.has(c.id));
    }, [selGroups, contacts, groups]);

    const toggleGroup = id => { const s = new Set(selGroups); s.has(id) ? s.delete(id) : s.add(id); setSelGroups(s); };

    const doSend = async () => {
        if (!subject) { addToast('Subject line is required', 'error'); return; }
        if (!html) { addToast('Email content is required', 'error'); return; }
        const allRecipients = [...recipients, ADMIN_CONTACT];
        setSending(true); setProgress(0); setResults(null);
        let sent = 0, failed = 0;
        const BATCH = 10, DELAY = 1000;
        for (let i = 0; i < allRecipients.length; i += BATCH) {
            const batch = allRecipients.slice(i, i + BATCH);
            const batchStart = Date.now();
            await Promise.all(batch.map(async c => {
                try {
                    const r = await fetch(`${API}?action=send`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ to: { email: c.email, name: c.name }, subject, htmlContent: html }) });
                    const d = await r.json();
                    d.sent ? sent++ : failed++;
                } catch { failed++; }
            }));
            setProgress(Math.round(((i + batch.length) / allRecipients.length) * 100));
            const elapsed = Date.now() - batchStart;
            if (i + BATCH < allRecipients.length && elapsed < DELAY) await new Promise(res => setTimeout(res, DELAY - elapsed));
        }
        const entry = { id: Date.now(), date: today(), subject, recipients: recipients.length, sent, failed, groupIds: [...selGroups], timestamp: new Date().toISOString() };
        addLog(entry); setResults({ sent, failed }); setSending(false);
        addToast(sent > 0 ? `✓ Sent to ${sent} contacts` : `All ${failed} sends failed`, sent > 0 ? 'success' : 'error');
    };

    return (<div>
        {/* Step indicator */}
        <div className="flex items-center gap-3 mb-6">
            {['Recipients', 'Compose', 'Preview & Send'].map((label, i) => (<React.Fragment key={i}>
                <button onClick={() => setStep(i + 1)} className={`flex items-center gap-2 text-sm font-medium transition ${step === i + 1 ? 'text-blue-600' : step > i + 1 ? 'text-green-600' : 'text-gray-400'}`}>
                    <span className={`w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold ${step === i + 1 ? 'bg-blue-600 text-white' : step > i + 1 ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-500'}`}>
                        {step > i + 1 ? '✓' : i + 1}
                    </span>
                    {label}
                </button>
                {i < 2 && <span className="text-gray-300 text-lg">›</span>}
            </React.Fragment>))}
        </div>

        {step === 1 && <div>
            <div className="flex items-center justify-between mb-4">
                <p className="text-sm text-gray-500">
                    {recipients.length > 0
                        ? <><span className="text-blue-600 font-semibold">{recipients.length}</span> recipients across <span className="text-blue-600 font-semibold">{selGroups.size}</span> groups selected</>
                        : 'No groups selected — will send to admin only'}
                </p>
                <button onClick={() => setStep(2)} className="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Next: Compose →</button>
            </div>
            <div className="mc-group-grid mb-6">
                {groups.map(g => <div key={g.id}
                    onClick={() => toggleGroup(g.id)}
                    className={`bg-white border rounded-xl p-4 cursor-pointer transition hover:shadow-sm ${selGroups.has(g.id) ? 'border-blue-500 bg-blue-50' : 'border-gray-200 hover:border-gray-300'}`}>
                    <p className="text-xs font-mono text-gray-400 mb-1">Auto Group</p>
                    <p className={`text-sm font-semibold mb-1 ${selGroups.has(g.id) ? 'text-blue-700' : 'text-gray-800'}`}>{g.name}</p>
                    <p className="text-xs text-gray-500">{g.count} contacts</p>
                    <p className="text-xs text-gray-400 mt-0.5">IDs {g.firstId}–{g.lastId}</p>
                </div>)}
                {groups.length === 0 && <p className="text-sm text-gray-400">No groups yet — load contacts first.</p>}
            </div>
            <div className="flex justify-end">
                <button onClick={() => setStep(2)} className="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Next: Compose →</button>
            </div>
        </div>}

        {step === 2 && <div>
            <div className="flex justify-between mb-4">
                <button onClick={() => setStep(1)} className="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">← Back</button>
                <button onClick={() => setStep(3)} className="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Next: Preview & Send →</button>
            </div>
            <div className="mb-4">
                <label className="block text-xs font-medium text-gray-600 mb-1">Subject line</label>
                <input className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" placeholder="Your email subject…" value={subject} onChange={e => setSubject(e.target.value)} />
            </div>
            <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
                <div>
                    <div className="flex items-center justify-between mb-2">
                        <span className="text-xs font-medium text-gray-600">HTML editor</span>
                        <div className="flex gap-1.5">
                            {Object.keys(TPLS).map(k => <button key={k} onClick={() => setHtml(TPLS[k])} className="px-2 py-1 text-xs bg-gray-100 text-gray-600 border border-gray-200 rounded hover:bg-gray-200">{k}</button>)}
                        </div>
                    </div>
                    <textarea className="mc-textarea w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                        style={{ height: 'calc(100vh - 380px)', minHeight: 400 }}
                        placeholder="Paste HTML here or pick a template above…"
                        value={html} onChange={e => setHtml(e.target.value)} />
                </div>
                <div>
                    <p className="text-xs font-medium text-gray-600 mb-2">Live preview</p>
                    <iframe ref={ifrRef} title="preview" style={{ width: '100%', height: 'calc(100vh - 380px)', minHeight: 400, border: '1px solid #e5e7eb', borderRadius: 10, background: '#fff' }} />
                </div>
            </div>
            <div className="flex justify-between mt-4">
                <button onClick={() => setStep(1)} className="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">← Back</button>
                <button onClick={() => setStep(3)} className="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700">Next: Preview & Send →</button>
            </div>
        </div>}

        {step === 3 && <div style={{ display: 'grid', gridTemplateColumns: '360px 1fr', gap: 20 }}>
            <div>
                <p className="text-xs font-medium text-gray-600 mb-3">Summary</p>
                <div className="bg-white rounded-xl border border-gray-200 p-4 mb-4 space-y-3">
                    {[['Recipients', recipients.length], ['Groups', selGroups.size], ['Total incl. admin', recipients.length + 1]].map(([k, v]) =>
                        <div key={k} className="flex items-center justify-between">
                            <span className="text-sm text-gray-500">{k}</span>
                            <span className="font-mono text-sm font-semibold text-gray-800">{v}</span>
                        </div>
                    )}
                    <div className="flex items-start justify-between pt-2 border-t border-gray-100">
                        <span className="text-sm text-gray-500">Subject</span>
                        <span className="text-sm text-gray-700 text-right max-w-48">{subject || '—'}</span>
                    </div>
                </div>

                {/* Admin notice */}
                <div className="bg-green-50 border border-green-200 rounded-xl p-3 mb-4">
                    <p className="text-xs font-semibold text-green-700 mb-1">★ Admin always included</p>
                    <p className="text-xs text-gray-600">{ADMIN_CONTACT.name}</p>
                    <p className="text-xs font-mono text-green-600">{ADMIN_CONTACT.email}</p>
                </div>

                {sending && <div className="bg-white rounded-xl border border-gray-200 p-4 mb-4">
                    <div className="flex justify-between text-sm mb-2">
                        <span className="text-gray-500">Sending…</span>
                        <span className="font-mono text-blue-600">{progress}%</span>
                    </div>
                    <div className="mc-progress-bar"><div className="mc-progress-fill" style={{ width: `${progress}%` }} /></div>
                </div>}

                {results && <div className="bg-white rounded-xl border border-gray-200 p-4 mb-4 space-y-2">
                    <div className="flex items-center gap-2">
                        <span className="w-2 h-2 rounded-full bg-green-500 inline-block" />
                        <span className="text-sm text-green-600 font-medium">{results.sent} sent successfully</span>
                    </div>
                    {results.failed > 0 && <div className="flex items-center gap-2">
                        <span className="w-2 h-2 rounded-full bg-red-500 inline-block" />
                        <span className="text-sm text-red-600">{results.failed} failed</span>
                    </div>}
                </div>}

                <div className="flex gap-3">
                    <button onClick={() => setStep(2)} disabled={sending} className="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 disabled:opacity-40">← Back</button>
                    <button onClick={doSend} disabled={sending || !!results} className="flex-1 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-40">
                        {sending ? <><span className="mc-spinner" /> Sending…</> : results ? '✓ Done' : `▶ Send to ${recipients.length + 1} contacts`}
                    </button>
                </div>
            </div>
            <div>
                <p className="text-xs font-medium text-gray-600 mb-2">Preview</p>
                <iframe ref={ifrRef} title="preview-final" className="mc-preview" style={{ height: 'calc(100vh - 280px)' }} />
            </div>
        </div>}
    </div>);
}

/* ── Logs Page ── */
function LogsPage({ logs }) {
    const [view, setView] = useState('all');
    const [dt, setDt]     = useState('');
    const yd = new Date(Date.now() - 86400000).toISOString().split('T')[0];
    const filtered = logs.filter(l => {
        if (view === 'today') return l.date === today();
        if (view === 'yesterday') return l.date === yd;
        if (dt) return l.date === dt;
        return true;
    }).sort((a, b) => b.id - a.id);
    const totals = logs.reduce((acc, l) => ({ s: acc.s + (l.sent || 0), f: acc.f + (l.failed || 0) }), { s: 0, f: 0 });

    return (<>
        <div className="grid grid-cols-4 gap-4 mb-6">
            {[['Campaigns', logs.length, 'text-gray-900'], ['Total sent', totals.s, 'text-green-600'], ['Failed', totals.f, 'text-red-600'], ['Success rate', (totals.s + totals.f ? Math.round(totals.s / (totals.s + totals.f) * 100) : 0) + '%', 'text-blue-600']].map(([label, val, cls]) =>
                <div key={label} className="bg-white rounded-xl border border-gray-200 p-4">
                    <p className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{label}</p>
                    <p className={`text-2xl font-bold font-mono ${cls}`}>{val}</p>
                </div>
            )}
        </div>
        <div className="flex items-center gap-2 mb-4 flex-wrap">
            {['all', 'today', 'yesterday'].map(v =>
                <button key={v} onClick={() => { setView(v); setDt(''); }}
                    className={`px-3 py-1.5 text-sm font-medium rounded-lg transition ${view === v ? 'bg-blue-600 text-white' : 'bg-white text-gray-600 border border-gray-200 hover:bg-gray-50'}`}>
                    {v === 'all' ? 'All time' : v === 'today' ? 'Today' : 'Yesterday'}
                </button>
            )}
            <input type="date" value={dt} onChange={e => { setDt(e.target.value); setView('all'); }}
                className="px-3 py-1.5 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" />
            {dt && <button onClick={() => setDt('')} className="px-3 py-1.5 text-sm text-gray-500 border border-gray-200 rounded-lg hover:bg-gray-50 bg-white">✕ Clear</button>}
            <Badge color="gray">{filtered.length} campaigns</Badge>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            {filtered.length === 0 ? <div className="text-center py-16 text-gray-400 text-sm">No campaigns found for this period.</div> :
            <table className="w-full mc-table">
                <thead><tr><th>Date / Time</th><th>Subject</th><th>Groups</th><th>Recipients</th><th>Sent</th><th>Failed</th><th></th></tr></thead>
                <tbody>{filtered.map(l => <tr key={l.id}>
                    <td><span className="font-mono text-xs text-gray-500">{l.date}</span><br /><span className="font-mono text-xs text-gray-400">{fmtTime(l.timestamp)}</span></td>
                    <td className="font-medium text-gray-800 max-w-xs truncate">{l.subject}</td>
                    <td className="font-mono text-xs text-gray-500">{(l.groupIds || []).map(g => g.replace('auto-', 'G')).join(', ') || '—'}</td>
                    <td className="font-mono text-sm">{l.recipients}</td>
                    <td><Badge color="green">{l.sent || 0}</Badge></td>
                    <td>{(l.failed || 0) > 0 ? <Badge color="red">{l.failed}</Badge> : <span className="text-gray-300">—</span>}</td>
                    <td><span className={`w-2 h-2 rounded-full inline-block ${(l.failed || 0) === 0 ? 'bg-green-500' : 'bg-amber-400'}`} /></td>
                </tr>)}</tbody>
            </table>}
        </div>
    </>);
}

/* ── Bounce Page ── */
function BouncePage() {
    const [bounces, setBounces] = useState([]);
    const [loading, setLoading] = useState(true);
    const [q, setQ] = useState('');

    useEffect(() => {
        fetch(`${API}?action=bounces`).then(r => r.json()).then(d => { setBounces(Array.isArray(d) ? d : []); setLoading(false); }).catch(() => setLoading(false));
    }, []);

    const filtered = bounces.filter(b => !q || b.email.toLowerCase().includes(q.toLowerCase()));
    const totalRemoved = bounces.filter(b => b.removed > 0).length;

    return (<>
        <div className="grid grid-cols-3 gap-4 mb-6">
            {[['Total bounces', bounces.length, 'text-red-600'], ['Auto-removed', totalRemoved, 'text-green-600'], ['Not in list', bounces.length - totalRemoved, 'text-gray-400']].map(([label, val, cls]) =>
                <div key={label} className="bg-white rounded-xl border border-gray-200 p-4">
                    <p className="text-xs font-semibold text-gray-400 uppercase tracking-wide mb-1">{label}</p>
                    <p className={`text-2xl font-bold font-mono ${cls}`}>{val}</p>
                </div>
            )}
        </div>
        <div className="flex items-center justify-between mb-4">
            <div className="relative">
                <span className="mc-search-icon text-sm">⌕</span>
                <input className="pl-8 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-64 bg-white"
                    placeholder="Search by email…" value={q} onChange={e => setQ(e.target.value)} />
            </div>
            <div className="flex items-center gap-2">
                <Badge color="red">{filtered.length} hard bounces</Badge>
                <button onClick={() => { setLoading(true); fetch(`${API}?action=bounces`).then(r => r.json()).then(d => { setBounces(Array.isArray(d) ? d : []); setLoading(false); }); }}
                    className="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 bg-white">
                    {loading ? <><span className="mc-spinner" /> Loading</> : '↻ Refresh'}
                </button>
            </div>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            {filtered.length === 0 ? <div className="text-center py-16 text-gray-400 text-sm">{loading ? 'Loading bounces…' : 'No bounces recorded yet.'}</div> :
            <table className="w-full mc-table">
                <thead><tr><th>Email</th><th>Bounce type</th><th>Sub-type</th><th>Status</th><th>Date</th></tr></thead>
                <tbody>{filtered.map(b => <tr key={b.id}>
                    <td className="font-mono text-xs text-red-600">{b.email}</td>
                    <td><Badge color="red">{b.bounce_type}</Badge></td>
                    <td className="text-xs text-gray-400">{b.bounce_subtype || '—'}</td>
                    <td>{b.removed > 0 ? <Badge color="green">✓ Removed</Badge> : <span className="text-xs text-gray-400">Not in list</span>}</td>
                    <td className="font-mono text-xs text-gray-400">{b.bounced_at?.replace('T', ' ').slice(0, 16) || '—'}</td>
                </tr>)}</tbody>
            </table>}
        </div>
        <p className="mt-4 text-xs text-gray-400">Only hard bounces (Permanent) are logged and auto-removed. Bounce detection is via Amazon SES → SNS → webhook.</p>
    </>);
}

/* ── AI Flyer ── */
function AIFlyerPage({ addToast, onUseHtml }) {
    const [ak, setAk]       = useState(() => ls('anthropic_key', ''));
    const [prompt, setPrompt] = useState('');
    const [gen, setGen]     = useState(false);
    const [out, setOut]     = useState('');
    const ifrRef = useRef(null);
    const saveKey = () => { save('anthropic_key', ak); addToast('API key saved', 'success'); };

    useEffect(() => {
        if (!ifrRef.current || !out) return;
        const doc = ifrRef.current.contentDocument;
        doc.open(); doc.write(out); doc.close();
    }, [out]);

    const generate = async () => {
        if (!ak) { addToast('Enter your Anthropic API key first', 'error'); return; }
        if (!prompt.trim()) { addToast('Describe the email you want', 'error'); return; }
        setGen(true); setOut('');
        try {
            const r = await fetch('https://api.anthropic.com/v1/messages', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'anthropic-version': '2023-06-01', 'x-api-key': ak },
                body: JSON.stringify({
                    model: 'claude-sonnet-4-20250514', max_tokens: 3000,
                    messages: [{ role: 'user', content: `Create a beautiful, professional HTML email flyer:\n\n${prompt}\n\nRequirements:\n- Complete standalone HTML with DOCTYPE\n- All styles inline\n- Max width 600px centered\n- Mobile-friendly\n- Clear CTA\n- Footer with unsubscribe placeholder\n\nReturn ONLY raw HTML. No markdown. No code fences.` }]
                })
            });
            const d = await r.json();
            if (d.error) throw new Error(d.error.message);
            const html = (d.content || []).find(b => b.type === 'text')?.text || '';
            setOut(html.trim());
            addToast('Flyer generated!', 'success');
        } catch (e) { addToast('Generation failed: ' + e.message, 'error'); }
        setGen(false);
    };

    return (<div>
        <div className="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <div className="flex items-center gap-3 mb-4">
                <div className="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center text-purple-600 text-lg">✦</div>
                <div>
                    <p className="text-sm font-semibold text-gray-800">AI Email Flyer Generator</p>
                    <p className="text-xs text-gray-400">Powered by Claude Sonnet</p>
                </div>
            </div>
            <div className="mb-4">
                <label className="block text-xs font-medium text-gray-600 mb-1">Anthropic API key</label>
                <div className="flex gap-2">
                    <input type="password" placeholder="sk-ant-…" value={ak} onChange={e => setAk(e.target.value)}
                        className="flex-1 px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white" />
                    <button onClick={saveKey} className="px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Save</button>
                </div>
                <p className="text-xs text-gray-400 mt-1">Get your key at console.anthropic.com · Stored locally only</p>
            </div>
            <div class="border-t border-gray-100 pt-4">
                <label className="block text-xs font-medium text-gray-600 mb-1">Describe your email campaign</label>
                <textarea className="mc-textarea w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    style={{ minHeight: 100 }}
                    placeholder="E.g. A promotional email for our AML compliance services targeting real estate brokers in Dubai. Professional tone, blue and teal colour scheme, include our contact details."
                    value={prompt} onChange={e => setPrompt(e.target.value)} />
            </div>
            <button onClick={generate} disabled={gen || !ak || !prompt.trim()}
                className="mt-3 px-5 py-2.5 text-sm font-semibold text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-40 flex items-center gap-2">
                {gen ? <><span className="mc-spinner" /> Generating…</> : '✦ Generate flyer'}
            </button>
        </div>

        {(out || gen) && <div style={{ display: 'grid', gridTemplateColumns: '1fr 1fr', gap: 16 }}>
            <div>
                <div className="flex items-center justify-between mb-2">
                    <p className="text-xs font-medium text-gray-600">Generated HTML</p>
                    {out && <button onClick={() => { onUseHtml(out); addToast('Inserted into Compose!', 'success'); }}
                        className="px-3 py-1.5 text-xs font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">→ Use in Compose</button>}
                </div>
                <textarea className="mc-textarea w-full px-3 py-2 border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white"
                    style={{ height: 420 }} value={out} onChange={e => setOut(e.target.value)} placeholder={gen ? 'Generating…' : ''} />
            </div>
            <div>
                <p className="text-xs font-medium text-gray-600 mb-2">Preview</p>
                {out ? <iframe ref={ifrRef} title="ai-preview" className="mc-preview" />
                    : <div className="mc-preview flex items-center justify-center text-gray-400 text-sm">Preview appears here</div>}
            </div>
        </div>}
    </div>);
}

/* ── Settings Page ── */
function SettingsPage({ settings }) {
    return (<div style={{ maxWidth: 560 }}>
        <div className="bg-green-50 border border-green-200 rounded-xl p-4 mb-5">
            <p className="text-sm font-semibold text-green-700 mb-1">✓ Server-side configuration active</p>
            <p className="text-xs text-gray-600 leading-relaxed">AWS SES credentials and database credentials are stored securely in <span className="font-mono text-xs bg-white border border-gray-200 px-1 rounded">/var/www/marketing/config.php</span> on the server. Nothing sensitive is stored in the browser.</p>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <p className="text-sm font-semibold text-gray-800 mb-4 pb-3 border-b border-gray-100">Current configuration</p>
            <div className="space-y-3">
                {[['API endpoint', settings.apiEndpoint], ['Sender name', settings.senderName], ['Sender email', settings.senderEmail], ['AWS SES credentials', 'Stored securely on server'], ['DB credentials', 'Stored securely on server']].map(([k, v]) =>
                    <div key={k} className="flex items-center justify-between">
                        <span className="text-sm text-gray-500">{k}</span>
                        <span className="text-sm font-mono text-gray-700">{v || '—'}</span>
                    </div>
                )}
            </div>
        </div>
        <div className="bg-gray-50 rounded-xl border border-gray-200 p-5 mb-5">
            <p className="text-xs font-semibold text-amber-600 mb-3">To update settings, edit on your server:</p>
            <code className="mc-code block bg-white border border-gray-200 rounded-lg p-3 text-gray-700">sudo nano /var/www/marketing/config.php</code>
        </div>
        <div className="bg-white rounded-xl border border-gray-200 p-5">
            <p className="text-xs font-semibold text-gray-600 mb-3">★ Admin contact (always BCC'd on every send)</p>
            <p className="text-sm text-gray-700">Name: <strong>{ADMIN_CONTACT.name}</strong></p>
            <p className="text-sm font-mono text-green-600 mt-1">{ADMIN_CONTACT.email}</p>
        </div>
    </div>);
}

/* ── Main App ── */
function MailApp() {
    const TABS = [
        { id: 'contacts', label: 'Contacts' },
        { id: 'groups',   label: 'Groups' },
        { id: 'compose',  label: 'Compose' },
        { id: 'logs',     label: 'Send Logs' },
        { id: 'bounces',  label: 'Bounce Log' },
        { id: 'ai',       label: 'AI Flyer' },
        { id: 'settings', label: 'Settings' },
    ];

    const [tab, setTab]           = useState('contacts');
    const [contacts, setContacts] = useState([]);
    const [loading, setLoading]   = useState(false);
    const [logs, setLogs]         = useState(() => ls('logs', []));
    const [toasts, setToasts]     = useState([]);
    const [settings, setSettings] = useState({ apiEndpoint: API, senderName: '', senderEmail: '' });
    const [composeHtml, setComposeHtml] = useState('');

    const addToast = (msg, type = 'info') => {
        const id = Date.now() + Math.random();
        setToasts(t => [...t, { id, msg, type }]);
        setTimeout(() => setToasts(t => t.filter(x => x.id !== id)), 3500);
    };
    const addLog = entry => setLogs(prev => { const n = [...prev, entry]; save('logs', n); return n; });

    const fetchContacts = async () => {
        setLoading(true);
        try {
            const r = await fetch(API);
            const d = await r.json();
            if (Array.isArray(d)) { setContacts(d); addToast(`Loaded ${d.length} subscribers`, 'success'); }
            else throw new Error('Expected array');
        } catch (e) { addToast('Failed to fetch contacts: ' + e.message, 'error'); }
        setLoading(false);
    };

    useEffect(() => {
        fetch(`${API}?action=config`).then(r => r.json()).then(cfg => {
            setSettings(s => ({ ...s, senderName: cfg.sender_name, senderEmail: cfg.sender_email, apiEndpoint: cfg.api_endpoint }));
        }).catch(() => {});
        fetchContacts();
    }, []);

    const groups = makeGroups(contacts);
    const COUNTS = { contacts: contacts.length, groups: groups.length, logs: logs.length };

    return (<>
        <Toasts toasts={toasts} />

        {/* Tab bar */}
        <div className="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 mb-5 overflow-x-auto">
            {TABS.map(t => <button key={t.id} onClick={() => setTab(t.id)}
                className={`px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap flex items-center gap-1.5 ${tab === t.id ? 'bg-blue-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'}`}>
                {t.label}
                {COUNTS[t.id] !== undefined && <span className={`text-xs px-1.5 py-0.5 rounded-full font-mono ${tab === t.id ? 'bg-blue-500 text-white' : 'bg-gray-100 text-gray-500'}`}>{COUNTS[t.id]}</span>}
            </button>)}

            {/* Live data indicator */}
            <div className="ml-auto flex items-center gap-1.5 px-3">
                <span className={`w-2 h-2 rounded-full ${contacts.length > 0 ? 'bg-green-500' : 'bg-amber-400'}`} />
                <span className="text-xs text-gray-400">{contacts.length > 0 ? 'Live DB' : 'No data'}</span>
            </div>
        </div>

        {/* Tab content */}
        {tab === 'contacts' && <ContactsPage contacts={contacts} loading={loading} onRefresh={fetchContacts} apiEndpoint={settings.apiEndpoint} addToast={addToast} />}
        {tab === 'groups'   && <GroupsPage contacts={contacts} logs={logs} />}
        {tab === 'compose'  && <ComposePage contacts={contacts} settings={settings} addLog={addLog} addToast={addToast} />}
        {tab === 'logs'     && <LogsPage logs={logs} />}
        {tab === 'bounces'  && <BouncePage />}
        {tab === 'ai'       && <AIFlyerPage addToast={addToast} onUseHtml={html => { setComposeHtml(html); setTab('compose'); addToast('HTML ready in Compose!', 'success'); }} />}
        {tab === 'settings' && <SettingsPage settings={settings} />}
    </>);
}

ReactDOM.createRoot(document.getElementById('mc-root')).render(<MailApp />);
</script>
@endverbatim
@endsection
