@extends('layouts.admin')
@section('title', 'WhatsApp — BlueArrow Portal')
@section('page-title', 'WhatsApp')

@section('content')
<script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
<script src="https://unpkg.com/react@18/umd/react.production.min.js"></script>
<script src="https://unpkg.com/react-dom@18/umd/react-dom.production.min.js"></script>
<script src="https://unpkg.com/@babel/standalone/babel.min.js"></script>

<style>
.wa-spinner { display:inline-block;width:13px;height:13px;border:2px solid rgba(0,0,0,.15);border-top-color:transparent;border-radius:50%;animation:wa-spin .6s linear infinite;vertical-align:middle; }
@keyframes wa-spin { to { transform:rotate(360deg); } }
.wa-toast-wrap { position:fixed;top:20px;right:20px;display:flex;flex-direction:column;gap:8px;z-index:9999;pointer-events:none; }
.wa-toast { padding:10px 16px;border-radius:8px;font-size:13px;font-weight:500;max-width:320px;animation:wa-toastIn .2s ease;pointer-events:auto; }
.wa-toast-success { background:#059669;color:#fff; }
.wa-toast-error   { background:#dc2626;color:#fff; }
.wa-toast-info    { background:#2563eb;color:#fff; }
@keyframes wa-toastIn { from{transform:translateX(110%);opacity:0}to{transform:translateX(0);opacity:1} }
.wa-log { font-family:'Courier New',monospace;font-size:11px;line-height:1.8;background:#0f172a;color:#94a3b8;border-radius:10px;padding:16px;max-height:400px;overflow-y:auto; }
.wa-log .log-ok  { color:#4ade80; }
.wa-log .log-err { color:#f87171; }
.wa-log .log-inf { color:#60a5fa; }
.wa-log .log-warn{ color:#fbbf24; }
</style>

<div id="wa-root"></div>

@verbatim
<script type="text/babel">
const {useState, useEffect, useRef, useCallback} = React;

const API = '/whatsapp/api';

/* ── Toasts ── */
function Toasts({toasts}){
    return <div className="wa-toast-wrap">{toasts.map(t=><div key={t.id} className={`wa-toast wa-toast-${t.type}`}>{t.msg}</div>)}</div>;
}

/* ── Badge ── */
function Badge({children, color='gray'}){
    const colors={gray:'bg-gray-100 text-gray-600',green:'bg-green-100 text-green-700',red:'bg-red-100 text-red-700',amber:'bg-amber-100 text-amber-700',blue:'bg-blue-100 text-blue-700',purple:'bg-purple-100 text-purple-700'};
    return <span className={`inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold ${colors[color]}`}>{children}</span>;
}

/* ── Connection Tab ── */
function ConnectionTab({addToast}){
    const [status, setStatus]   = useState(null);
    const [loading, setLoading] = useState(true);
    const qrRef = useRef(null);
    const pollRef = useRef(null);

    const fetchStatus = useCallback(async () => {
        try {
            const r = await fetch(`${API}/status`);
            const d = await r.json();
            setStatus(d);

            // Render QR code if available
            if (d.qrData && qrRef.current) {
                qrRef.current.innerHTML = '';
                QRCode.toCanvas ? 
                    QRCode.toCanvas(qrRef.current, d.qrData, {width:220,margin:2}) :
                    new QRCode(qrRef.current, {text:d.qrData,width:220,height:220,colorDark:'#0f172a',colorLight:'#fff'});
            }
        } catch(e) {
            setStatus({error: e.message});
        }
        setLoading(false);
    }, []);

    useEffect(() => {
        fetchStatus();
        pollRef.current = setInterval(fetchStatus, 5000);
        return () => clearInterval(pollRef.current);
    }, [fetchStatus]);

    const reconnect = async () => {
        setLoading(true);
        await fetch(`${API}/reconnect`, {method:'POST'});
        addToast('Reconnecting…', 'info');
        setTimeout(fetchStatus, 3000);
    };

    const disconnect = async () => {
        if (!confirm('Disconnect WhatsApp? You will need to scan the QR code again.')) return;
        await fetch(`${API}/disconnect`, {method:'POST'});
        addToast('Disconnected', 'info');
        setTimeout(fetchStatus, 2000);
    };

    const isConnected = status?.isReady;
    const hasQr       = status?.qrData && !isConnected;

    return (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-5">
            {/* Status card */}
            <div className="bg-white rounded-xl border border-gray-200 p-5">
                <h3 className="text-sm font-semibold text-gray-700 mb-4">Connection status</h3>

                <div className={`flex items-center gap-3 p-4 rounded-xl mb-4 ${isConnected ? 'bg-green-50 border border-green-200' : 'bg-gray-50 border border-gray-200'}`}>
                    <div className={`w-3 h-3 rounded-full flex-shrink-0 ${isConnected ? 'bg-green-500' : 'bg-gray-400'}`} style={isConnected ? {boxShadow:'0 0 0 4px rgba(74,222,128,0.2)'} : {}}/>
                    <div>
                        <p className={`text-sm font-semibold ${isConnected ? 'text-green-700' : 'text-gray-600'}`}>
                            {loading ? 'Checking…' : isConnected ? 'Connected' : hasQr ? 'Waiting for QR scan' : 'Disconnected'}
                        </p>
                        {status?.groupCount !== undefined && <p className="text-xs text-gray-400 mt-0.5">{status.groupCount} groups loaded</p>}
                    </div>
                </div>

                {/* QR code */}
                {hasQr && <div className="mb-4 text-center">
                    <p className="text-xs text-gray-500 mb-3">Open WhatsApp on your phone → Linked Devices → Link a device → scan this code</p>
                    <div className="inline-block p-3 bg-white border-2 border-gray-200 rounded-xl">
                        <canvas ref={qrRef} />
                    </div>
                    <p className="text-xs text-amber-600 mt-2">QR code refreshes every 20 seconds</p>
                </div>}

                {/* Actions */}
                <div className="flex gap-2 flex-wrap">
                    <button onClick={fetchStatus} className="px-3 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 flex items-center gap-1.5">
                        ↻ Refresh
                    </button>
                    {isConnected
                        ? <button onClick={disconnect} className="px-3 py-2 text-sm text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Disconnect</button>
                        : <button onClick={reconnect} className="px-3 py-2 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700">Reconnect</button>
                    }
                </div>
            </div>

            {/* Info card */}
            <div className="bg-white rounded-xl border border-gray-200 p-5">
                <h3 className="text-sm font-semibold text-gray-700 mb-4">WhatsApp manager info</h3>
                <div className="space-y-3">
                    {[
                        ['Manager URL', 'http://127.0.0.1:3001'],
                        ['Groups loaded', status?.groupCount ?? '—'],
                        ['Status', isConnected ? 'Ready' : 'Not connected'],
                    ].map(([k,v]) => <div key={k} className="flex items-center justify-between">
                        <span className="text-sm text-gray-500">{k}</span>
                        <span className="text-sm font-medium text-gray-700">{String(v)}</span>
                    </div>)}
                </div>
                <div className="mt-4 pt-4 border-t border-gray-100">
                    <p className="text-xs text-gray-400 leading-relaxed">
                        Do not open WhatsApp Web on the same number linked here — it will break the session. Use this dashboard to manage scheduled messages instead.
                    </p>
                </div>
                <a href="https://wa.bluearrow.ae" target="_blank"
                   className="mt-4 flex items-center justify-between text-sm text-blue-600 hover:underline">
                    <span>Open standalone WA dashboard</span>
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                </a>
            </div>
        </div>
    );
}

/* ── Groups Tab ── */
function GroupsTab({addToast}){
    const [groups, setGroups]     = useState([]);
    const [loading, setLoading]   = useState(true);
    const [refreshing, setRefreshing] = useState(false);
    const [q, setQ]               = useState('');

    const fetchGroups = async () => {
        try {
            const r = await fetch(`${API}/groups`);
            const d = await r.json();
            setGroups(Array.isArray(d) ? d : []);
        } catch(e) { addToast('Failed to load groups', 'error'); }
        setLoading(false);
    };

    const refreshGroups = async () => {
        setRefreshing(true);
        try {
            await fetch(`${API}/groups/refresh`, {method:'POST'});
            addToast('Refreshing groups from WhatsApp…', 'info');
            setTimeout(fetchGroups, 3000);
        } catch(e) { addToast('Refresh failed', 'error'); }
        setRefreshing(false);
    };

    useEffect(() => { fetchGroups(); }, []);

    const filtered = groups.filter(g => !q || g.name?.toLowerCase().includes(q.toLowerCase()));

    return (<>
        <div className="flex items-center justify-between mb-5">
            <div className="flex items-center gap-3">
                <div className="relative">
                    <input className="pl-8 pr-4 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 w-64 bg-white"
                        placeholder="Search groups…" value={q} onChange={e=>setQ(e.target.value)} />
                    <span className="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400 text-sm pointer-events-none">⌕</span>
                </div>
                <Badge color="green">{filtered.length} groups</Badge>
            </div>
            <button onClick={refreshGroups} disabled={refreshing}
                className="px-3 py-2 text-sm font-medium text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-40 flex items-center gap-2">
                {refreshing ? <><span className="wa-spinner"/>Refreshing…</> : '↻ Refresh from WhatsApp'}
            </button>
        </div>

        <div className="bg-white rounded-xl border border-gray-200 overflow-hidden">
            {loading ? <div className="text-center py-12 text-gray-400 text-sm">Loading groups…</div> :
            filtered.length === 0 ? <div className="text-center py-12 text-gray-400 text-sm">No groups found. Connect WhatsApp and refresh.</div> :
            <table className="w-full text-sm">
                <thead className="bg-gray-50">
                    <tr>
                        <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Group name</th>
                        <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden md:table-cell">Members</th>
                        <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase hidden lg:table-cell">Last active</th>
                        <th className="px-4 py-3 text-left text-xs font-semibold text-gray-500 uppercase">Status</th>
                    </tr>
                </thead>
                <tbody className="divide-y divide-gray-100">
                    {filtered.map(g => <tr key={g.id} className="hover:bg-gray-50">
                        <td className="px-4 py-3 font-medium text-gray-800">{g.name}</td>
                        <td className="px-4 py-3 text-gray-500 hidden md:table-cell">{g.members}</td>
                        <td className="px-4 py-3 text-gray-400 text-xs hidden lg:table-cell">{g.lastActive}</td>
                        <td className="px-4 py-3">
                            <Badge color={g.active ? 'green' : 'gray'}>{g.active ? 'Active' : 'Inactive'}</Badge>
                        </td>
                    </tr>)}
                </tbody>
            </table>}
        </div>
    </>);
}

/* ── Send Tab ── */
function SendTab({addToast}){
    const [groups, setGroups]         = useState([]);
    const [selectedGroups, setSelected] = useState(new Set());
    const [message, setMessage]       = useState('');
    const [sending, setSending]       = useState(false);
    const [result, setResult]         = useState(null);

    useEffect(() => {
        fetch(`${API}/groups`).then(r=>r.json()).then(d=>{
            if(Array.isArray(d)) setGroups(d);
        }).catch(()=>{});
    }, []);

    const toggle = id => {
        const s = new Set(selectedGroups);
        s.has(id) ? s.delete(id) : s.add(id);
        setSelected(s);
    };

    const sendNow = async () => {
        if (!message.trim()) { addToast('Message is required', 'error'); return; }
        if (selectedGroups.size === 0) { addToast('Select at least one group', 'error'); return; }
        if (!confirm(`Send to ${selectedGroups.size} group(s)?`)) return;

        setSending(true); setResult(null);
        try {
            const r = await fetch(`${API}/send/immediate`, {
                method: 'POST',
                headers: {'Content-Type':'application/json'},
                body: JSON.stringify({groupIds: [...selectedGroups], message})
            });
            const d = await r.json();
            setResult(d);
            addToast(`Sent to ${selectedGroups.size} groups`, 'success');
            setSelected(new Set());
            setMessage('');
        } catch(e) { addToast('Send failed: ' + e.message, 'error'); }
        setSending(false);
    };

    return (
        <div className="grid grid-cols-1 lg:grid-cols-2 gap-5">
            {/* Group picker */}
            <div className="bg-white rounded-xl border border-gray-200 p-5">
                <div className="flex items-center justify-between mb-4">
                    <h3 className="text-sm font-semibold text-gray-700">Select groups</h3>
                    {selectedGroups.size > 0 && <Badge color="blue">{selectedGroups.size} selected</Badge>}
                </div>
                {groups.length === 0 ? <p className="text-sm text-gray-400">No groups loaded. Go to Groups tab and refresh.</p> :
                <div className="space-y-1.5 max-h-96 overflow-y-auto">
                    {groups.map(g => <label key={g.id}
                        className={`flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition ${selectedGroups.has(g.id) ? 'bg-green-50 border border-green-200' : 'hover:bg-gray-50 border border-transparent'}`}>
                        <input type="checkbox" checked={selectedGroups.has(g.id)} onChange={() => toggle(g.id)}
                            className="rounded border-gray-300 text-green-600" />
                        <div className="flex-1 min-w-0">
                            <p className="text-sm font-medium text-gray-800 truncate">{g.name}</p>
                            <p className="text-xs text-gray-400">{g.members} members</p>
                        </div>
                        <Badge color={g.active ? 'green' : 'gray'}>{g.active ? 'Active' : 'Inactive'}</Badge>
                    </label>)}
                </div>}
            </div>

            {/* Message + send */}
            <div className="space-y-4">
                <div className="bg-white rounded-xl border border-gray-200 p-5">
                    <h3 className="text-sm font-semibold text-gray-700 mb-3">Message</h3>
                    <textarea
                        className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 bg-white resize-none"
                        rows={8}
                        placeholder="Type your WhatsApp message here…&#10;&#10;You can use *bold*, _italic_, ~strikethrough~ formatting."
                        value={message}
                        onChange={e => setMessage(e.target.value)}
                    />
                    <div className="flex items-center justify-between mt-3">
                        <span className="text-xs text-gray-400">{message.length} characters</span>
                        <button onClick={sendNow} disabled={sending || !message.trim() || selectedGroups.size === 0}
                            className="px-5 py-2.5 text-sm font-semibold text-white bg-green-600 rounded-lg hover:bg-green-700 disabled:opacity-40 flex items-center gap-2">
                            {sending ? <><span className="wa-spinner"/>Sending…</> : `▶ Send to ${selectedGroups.size} group(s)`}
                        </button>
                    </div>
                </div>

                {result && <div className={`rounded-xl border p-4 ${result.error ? 'bg-red-50 border-red-200' : 'bg-green-50 border-green-200'}`}>
                    <p className={`text-sm font-medium ${result.error ? 'text-red-700' : 'text-green-700'}`}>
                        {result.error ? '✕ ' + result.error : '✓ ' + (result.message || 'Sent successfully')}
                    </p>
                </div>}

                <div className="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <p className="text-xs text-amber-700 font-semibold mb-1">⚠ Important</p>
                    <p className="text-xs text-amber-600 leading-relaxed">Messages are sent with an 8-second delay between groups to avoid WhatsApp rate limiting. Sending to many groups may take several minutes.</p>
                </div>
            </div>
        </div>
    );
}

/* ── Schedules Tab ── */
function SchedulesTab({addToast}){
    const [schedules, setSchedules] = useState([]);
    const [groups, setGroups]       = useState([]);
    const [loading, setLoading]     = useState(true);
    const [showForm, setShowForm]   = useState(false);
    const [form, setForm]           = useState({label:'',message:'',hour:9,minute:0,dayOfWeek:1,groupIds:[]});
    const [saving, setSaving]       = useState(false);

    const DAYS = ['Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    const fetchAll = async () => {
        const [sr, gr] = await Promise.all([
            fetch(`${API}/schedules`).then(r=>r.json()).catch(()=>[]),
            fetch(`${API}/groups`).then(r=>r.json()).catch(()=>[]),
        ]);
        setSchedules(Array.isArray(sr) ? sr : []);
        setGroups(Array.isArray(gr) ? gr : []);
        setLoading(false);
    };

    useEffect(() => { fetchAll(); }, []);

    const upd = (k,v) => setForm(p => ({...p,[k]:v}));

    const toggleGroupInForm = id => {
        const ids = form.groupIds.includes(id) ? form.groupIds.filter(x=>x!==id) : [...form.groupIds, id];
        upd('groupIds', ids);
    };

    const addSchedule = async () => {
        if (!form.label.trim()) { addToast('Label is required', 'error'); return; }
        if (!form.message.trim()) { addToast('Message is required', 'error'); return; }
        if (form.groupIds.length === 0) { addToast('Select at least one group', 'error'); return; }
        setSaving(true);
        try {
            await fetch(`${API}/schedules`, {method:'POST', headers:{'Content-Type':'application/json'}, body:JSON.stringify(form)});
            addToast('Schedule added', 'success');
            setShowForm(false);
            setForm({label:'',message:'',hour:9,minute:0,dayOfWeek:1,groupIds:[]});
            fetchAll();
        } catch(e) { addToast('Failed: ' + e.message, 'error'); }
        setSaving(false);
    };

    const deleteSchedule = async id => {
        if (!confirm('Delete this schedule?')) return;
        await fetch(`${API}/schedules/${id}`, {method:'DELETE'});
        addToast('Schedule deleted', 'success');
        fetchAll();
    };

    const toggleEnabled = async (sched) => {
        await fetch(`${API}/schedules/${sched.id}`, {
            method:'PUT', headers:{'Content-Type':'application/json'},
            body: JSON.stringify({...sched, enabled: !sched.enabled})
        });
        fetchAll();
    };

    return (<>
        <div className="flex items-center justify-between mb-5">
            <Badge color="blue">{schedules.length} schedules</Badge>
            <button onClick={() => setShowForm(!showForm)}
                className="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 flex items-center gap-2">
                {showForm ? '✕ Cancel' : '+ New schedule'}
            </button>
        </div>

        {/* Add schedule form */}
        {showForm && <div className="bg-white rounded-xl border border-gray-200 p-5 mb-5">
            <h3 className="text-sm font-semibold text-gray-700 mb-4">New scheduled message</h3>
            <div className="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                <div>
                    <label className="block text-xs font-medium text-gray-600 mb-1">Label *</label>
                    <input type="text" value={form.label} onChange={e=>upd('label',e.target.value)} placeholder="e.g. Monday Compliance Reminder"
                        className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label className="block text-xs font-medium text-gray-600 mb-1">Day of week</label>
                    <select value={form.dayOfWeek} onChange={e=>upd('dayOfWeek',parseInt(e.target.value))}
                        className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 bg-white">
                        {DAYS.map((d,i)=><option key={i} value={i}>{d}</option>)}
                    </select>
                </div>
                <div>
                    <label className="block text-xs font-medium text-gray-600 mb-1">Hour (0–23)</label>
                    <input type="number" min={0} max={23} value={form.hour} onChange={e=>upd('hour',parseInt(e.target.value))}
                        className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
                <div>
                    <label className="block text-xs font-medium text-gray-600 mb-1">Minute (0–59)</label>
                    <input type="number" min={0} max={59} value={form.minute} onChange={e=>upd('minute',parseInt(e.target.value))}
                        className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" />
                </div>
            </div>
            <div className="mb-4">
                <label className="block text-xs font-medium text-gray-600 mb-1">Message *</label>
                <textarea rows={4} value={form.message} onChange={e=>upd('message',e.target.value)} placeholder="WhatsApp message content…"
                    className="w-full px-3 py-2 text-sm border border-gray-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 resize-none" />
            </div>
            <div className="mb-4">
                <label className="block text-xs font-medium text-gray-600 mb-2">Target groups * ({form.groupIds.length} selected)</label>
                <div className="grid grid-cols-2 md:grid-cols-3 gap-2 max-h-48 overflow-y-auto border border-gray-100 rounded-lg p-2">
                    {groups.map(g => <label key={g.id} className={`flex items-center gap-2 p-2 rounded-lg cursor-pointer text-xs transition ${form.groupIds.includes(g.id) ? 'bg-blue-50 border border-blue-200' : 'hover:bg-gray-50 border border-transparent'}`}>
                        <input type="checkbox" checked={form.groupIds.includes(g.id)} onChange={()=>toggleGroupInForm(g.id)} className="rounded" />
                        <span className="truncate font-medium text-gray-700">{g.name}</span>
                    </label>)}
                    {groups.length === 0 && <p className="text-xs text-gray-400 col-span-3 p-2">No groups — go to Groups tab and refresh first.</p>}
                </div>
            </div>
            <div className="flex gap-2 justify-end">
                <button onClick={() => setShowForm(false)} className="px-4 py-2 text-sm text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50">Cancel</button>
                <button onClick={addSchedule} disabled={saving} className="px-4 py-2 text-sm font-semibold text-white bg-blue-600 rounded-lg hover:bg-blue-700 disabled:opacity-40">
                    {saving ? <><span className="wa-spinner"/> Saving…</> : 'Add schedule'}
                </button>
            </div>
        </div>}

        {/* Schedule list */}
        {loading ? <div className="text-center py-12 text-gray-400 text-sm">Loading…</div> :
        schedules.length === 0 ? <div className="bg-white rounded-xl border border-dashed border-gray-300 p-12 text-center text-gray-400 text-sm">No schedules yet. Add one above.</div> :
        <div className="space-y-3">
            {schedules.map(s => {
                const groupNames = s.groupIds?.map(id => groups.find(g=>g.id===id)?.name).filter(Boolean) || [];
                return <div key={s.id} className={`bg-white rounded-xl border p-4 ${s.enabled===false ? 'border-gray-100 opacity-60' : 'border-gray-200'}`}>
                    <div className="flex items-start justify-between gap-4">
                        <div className="flex-1 min-w-0">
                            <div className="flex items-center gap-2 mb-1">
                                <p className="font-semibold text-gray-800">{s.label}</p>
                                <Badge color={s.enabled===false ? 'gray' : 'green'}>{s.enabled===false ? 'Paused' : 'Active'}</Badge>
                            </div>
                            <p className="text-xs text-gray-500 mb-2">
                                Every <strong>{DAYS[s.dayOfWeek]}</strong> at <strong>{String(s.hour).padStart(2,'0')}:{String(s.minute).padStart(2,'0')}</strong>
                            </p>
                            <p className="text-sm text-gray-600 line-clamp-2 mb-2">{s.message}</p>
                            <div className="flex flex-wrap gap-1">
                                {groupNames.map(n => <span key={n} className="text-xs bg-green-50 text-green-700 border border-green-100 px-2 py-0.5 rounded-full">{n}</span>)}
                            </div>
                        </div>
                        <div className="flex items-center gap-2 flex-shrink-0">
                            <button onClick={() => toggleEnabled(s)} className={`px-3 py-1.5 text-xs font-medium rounded-lg border transition ${s.enabled===false ? 'text-green-600 border-green-200 hover:bg-green-50' : 'text-amber-600 border-amber-200 hover:bg-amber-50'}`}>
                                {s.enabled===false ? 'Enable' : 'Pause'}
                            </button>
                            <button onClick={() => deleteSchedule(s.id)} className="px-3 py-1.5 text-xs font-medium text-red-600 border border-red-200 rounded-lg hover:bg-red-50">Delete</button>
                        </div>
                    </div>
                </div>;
            })}
        </div>}
    </>);
}

/* ── Logs Tab ── */
function LogsTab(){
    const [logs, setLogs]     = useState([]);
    const [loading, setLoading] = useState(true);
    const logRef = useRef(null);

    const fetchLogs = async () => {
        try {
            const r = await fetch(`${API}/logs`);
            const d = await r.json();
            setLogs(Array.isArray(d) ? d : []);
        } catch {}
        setLoading(false);
    };

    useEffect(() => {
        fetchLogs();
        const interval = setInterval(fetchLogs, 10000);
        return () => clearInterval(interval);
    }, []);

    const colorClass = line => {
        if (line.includes('✅') || line.includes('Connected') || line.includes('ready')) return 'log-ok';
        if (line.includes('❌') || line.includes('Error') || line.includes('Failed')) return 'log-err';
        if (line.includes('⚠') || line.includes('Warning') || line.includes('Disconnect')) return 'log-warn';
        if (line.includes('⏰') || line.includes('📋') || line.includes('📱')) return 'log-inf';
        return '';
    };

    return (<>
        <div className="flex items-center justify-between mb-5">
            <h3 className="text-sm font-semibold text-gray-700">Activity log</h3>
            <button onClick={fetchLogs} className="px-3 py-1.5 text-xs font-medium text-gray-600 border border-gray-200 rounded-lg hover:bg-gray-50 bg-white flex items-center gap-1.5">
                ↻ Refresh
            </button>
        </div>
        <div className="wa-log" ref={logRef}>
            {loading ? <span className="log-inf">Loading logs…</span> :
             logs.length === 0 ? <span className="log-inf">No activity yet.</span> :
             logs.map((line, i) => <div key={i} className={colorClass(line)}>{line}</div>)}
        </div>
        <p className="text-xs text-gray-400 mt-2">Auto-refreshes every 10 seconds. Showing last 100 entries.</p>
    </>);
}

/* ── Main App ── */
function WAApp(){
    const [tab, setTab]     = useState('connection');
    const [toasts, setToasts] = useState([]);

    const addToast = (msg, type='info') => {
        const id = Date.now() + Math.random();
        setToasts(t => [...t, {id, msg, type}]);
        setTimeout(() => setToasts(t => t.filter(x => x.id !== id)), 3500);
    };

    const TABS = [
        {id:'connection', label:'Connection'},
        {id:'groups',     label:'Groups'},
        {id:'send',       label:'Send Message'},
        {id:'schedules',  label:'Schedules'},
        {id:'logs',       label:'Activity Log'},
    ];

    return (<>
        <Toasts toasts={toasts} />

        {/* Tab bar */}
        <div className="flex gap-1 bg-white rounded-xl border border-gray-200 p-1 mb-5 overflow-x-auto">
            {TABS.map(t => <button key={t.id} onClick={() => setTab(t.id)}
                className={`px-4 py-2 rounded-lg text-sm font-medium transition-all whitespace-nowrap ${tab===t.id ? 'bg-green-600 text-white shadow-sm' : 'text-gray-500 hover:text-gray-700 hover:bg-gray-100'}`}>
                {t.label}
            </button>)}
        </div>

        {tab === 'connection' && <ConnectionTab addToast={addToast} />}
        {tab === 'groups'     && <GroupsTab addToast={addToast} />}
        {tab === 'send'       && <SendTab addToast={addToast} />}
        {tab === 'schedules'  && <SchedulesTab addToast={addToast} />}
        {tab === 'logs'       && <LogsTab />}
    </>);
}

ReactDOM.createRoot(document.getElementById('wa-root')).render(<WAApp />);
</script>
@endverbatim

@endsection
