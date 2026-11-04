# v2.2 -> v2.3: remove Appendices F, G, H (now in the KYC module); renumber CAHRA appendix to F;
# US spelling; no em/en dashes.
import os, re, sys
os.chdir(os.path.dirname(os.path.abspath(__file__)))
sys.stdout.reconfigure(encoding='utf-8')
IO = dict(encoding='utf-8', newline='')
rd = lambda p: open(p, **IO).read()
def wr(p, s): open(p, 'w', **IO).write(s)
def sub1(s, old, new):
    assert s.count(old) == 1, (s.count(old), old[:90])
    return s.replace(old, new, 1)

PARTS = ['part1.cjs', 'part2.cjs', 'part3.cjs', 'part4.cjs']

# ── 1. remove Appendices F, G, H ──────────────────────────────────────────
s = rd('part4.cjs')
a = s.index("  // ── F. CDD checklist")
b = s.index("  // ── I. CAHRA assessment")
s = s[:a] + s[b:]
wr('part4.cjs', s)

# ── 2. references to the removed appendices ───────────────────────────────
s = rd('part2.cjs')
s = sub1(s, "The standard documents are listed in Appendix F.", "The documents required for each customer type are set out in the KYC module checklist.")
s = sub1(s, "The assessment is recorded on the Customer Risk Assessment Form (Appendix G). Factors:", "The assessment is recorded in the customer risk assessment in the KYC module. Factors:")
s = sub1(s, "The scoring model in Appendix G, including its factors, scores and rating bands, is a documented model:", "The customer risk scoring model in the KYC module, including its factors, scores and rating bands, is a documented model:")
s = sub1(s, "and never to a lower rating than the Appendix G overrides require.", "and never below the minimum rating the model requires for a PEP, a sanctions concern, a call-for-action jurisdiction, adverse media on financial crime, or a beneficial owner who cannot be identified.")
s = sub1(s, "A record is made of every check (Appendix H).", "Routine screening results are kept in the screening tool\\'s log, which must be exportable and show the date of each list update. Every alert and its outcome is documented in the KYC module or the screening record.")
s = sub1(s, "Document the reasons and evidence (Appendix H). No report is needed.", "Document the reasons and evidence in the screening record. No report is needed.")
wr('part2.cjs', s)

s = rd('part3.cjs')
s = sub1(s, "Collect all documents in the checklist (Appendix F).", "Collect all documents in the KYC module checklist.")
s = sub1(s, "Record the results (Appendix H).", "Record the results in the KYC module or screening record.")
s = sub1(s, "Complete the Customer Risk Assessment Form (Appendix G).", "Complete the customer risk assessment in the KYC module.")
wr('part3.cjs', s)

s = rd('part1.cjs')
s = sub1(s, "'4.1 to 4.3, 7.5, App. C, G, I'", "'4.1 to 4.3, 7.5, App. C, F'")
wr('part1.cjs', s)

# CAHRA appendix I -> F
for f in PARTS:
    t = rd(f)
    t = t.replace('APPENDIX I ', 'APPENDIX F ').replace('Appendix I', 'Appendix F')
    wr(f, t)

# ── 3. US spelling ────────────────────────────────────────────────────────
PAIRS = [
    (r'licen[cs]e', None),  # handled below
]
MAP = {
    'licence': 'license', 'licences': 'licenses',
    'organisation': 'organization', 'organisations': 'organizations', 'organised': 'organized', 'organise': 'organize',
    'authorised': 'authorized', 'authorisation': 'authorization', 'authorise': 'authorize', 'unauthorised': 'unauthorized',
    'recognised': 'recognized', 'recognise': 'recognize', 'recognising': 'recognizing',
    'specialised': 'specialized', 'summarise': 'summarize', 'prioritise': 'prioritize', 'prioritised': 'prioritized', 'prioritisation': 'prioritization',
    'minimise': 'minimize', 'utilise': 'utilize', 'emphasise': 'emphasize', 'emphasised': 'emphasized', 'categorise': 'categorize',
    'finalise': 'finalize', 'formalise': 'formalize', 'centralised': 'centralized', 'standardised': 'standardized',
    'analyse': 'analyze', 'analysed': 'analyzed', 'scrutinise': 'scrutinize', 'familiarise': 'familiarize', 'realise': 'realize',
    'behaviour': 'behavior', 'behaviours': 'behaviors', 'programme': 'program', 'programmes': 'programs',
    'defence': 'defense', 'offence': 'offense', 'offences': 'offenses',
    'cheque': 'check', 'cheques': 'checks', 'grey': 'gray', 'greylist': 'graylist',
    'judgement': 'judgment', 'enquiry': 'inquiry', 'enquiries': 'inquiries', 'instalment': 'installment', 'instalments': 'installments',
    'fulfil': 'fulfill', 'fulfils': 'fulfills', 'fulfilment': 'fulfillment', 'labelled': 'labeled', 'cancelled': 'canceled', 'cancelling': 'canceling',
    'modelling': 'modeling', 'travelling': 'traveling', 'traveller': 'traveler', 'travellers': 'travelers',
    'centre': 'center', 'catalogue': 'catalog', 'cataloguing': 'cataloging', 'artefact': 'artifact',
    'whilst': 'while', 'amongst': 'among', 'towards': 'toward', 'acknowledgement': 'acknowledgment', 'practise': 'practice',
    'jewellery': 'jewelry', 'misdemeanour': 'misdemeanor', 'harbour': 'harbor', 'labour': 'labor', 'neighbouring': 'neighboring', 'favour': 'favor', 'colour': 'color',
}
def case_like(src, dst):
    if src.isupper(): return dst.upper()
    if src[0].isupper(): return dst[0].upper() + dst[1:]
    return dst
pat = re.compile(r'\b(' + '|'.join(sorted(MAP, key=len, reverse=True)) + r')\b', re.I)
def us(text):
    return pat.sub(lambda m: case_like(m.group(0), MAP[m.group(0).lower()]), text)

# placeholder rename (licence -> license)
for f in PARTS:
    t = rd(f)
    t = t.replace('TRADE_LICENCE_NO', 'TRADE_LICENSE_NO')
    wr(f, us(t))

# ── 4. dashes ─────────────────────────────────────────────────────────────
EM, EN = '—', '–'
for f in PARTS + ['build.cjs']:
    t = rd(f)
    t = t.replace('TEMPLATE GUIDE ' + EM + ' delete', 'TEMPLATE GUIDE: delete')
    t = t.replace('CONFIDENTIAL ' + EM + ' for', 'CONFIDENTIAL: for')
    t = t.replace('Stones ' + EM + ' Trade License', 'Stones, Trade License')
    t = re.sub(r"(\*\*Step \d) " + EM + " ", r"\1: ", t)
    t = re.sub(r"(APPENDIX [A-F]) " + EM + " ", r"\1: ", t)
    t = re.sub(r"(Low|Medium|High) risk " + EM + " ", r"\1 risk, ", t)
    t = t.replace("', '" + EM + "']", "', 'Not applicable']")
    t = t.replace('Template v2.2', 'Template v2.3')
    wr(f, t)

# report anything left
left = []
for f in PARTS + ['build.cjs', 'lib.cjs']:
    for i, l in enumerate(rd(f).split('\n'), 1):
        for m in re.finditer(r'.{0,50}[' + EM + EN + r'].{0,50}', l):
            left.append((f, i, m.group(0)))
print('dashes left:', len(left))
for x in left: print('  ', x)
print('done')
