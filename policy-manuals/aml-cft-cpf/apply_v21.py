# One-off patch: v2 -> v2.1 (regulatory update review of 4 Nov 2026, official sources only).
import os, re
os.chdir(os.path.dirname(os.path.abspath(__file__)))
IO = dict(encoding='utf-8', newline='')

def rd(p): return open(p, **IO).read()
def wr(p, s): open(p, 'w', **IO).write(s)

def between(s, start, end, new, include_end=False):
    a = s.index(start)
    b = s.index(end, a)
    return s[:a] + new + s[b:]

def sub1(s, old, new):
    assert s.count(old) == 1, (s.count(old), old[:70])
    return s.replace(old, new, 1)

# ═════════════════════════════ part1 ═════════════════════════════
s = rd('part1.cjs')

s = between(s, "  h2('1.3 Legal and regulatory framework');", "  h2('1.4 Authorities", r'''  h2('1.3 Legal and regulatory framework');
  p('This Manual is based on the following official instruments. The MLRO keeps the list current (section 12) and records every change in the log in section 1.6.');
  h3('Legislation');
  bullets([
    'Federal Decree-Law No. (10) of 2025 on Anti-Money Laundering, Combating the Financing of Terrorism and Proliferation Financing (issued 30 September 2025), the primary law.',
    'Cabinet Resolution No. (134) of 2025, the Executive Regulations of that law (issued 29 October 2025; in force 30 days after publication; it repealed Cabinet Resolution No. (10) of 2019).',
    'Cabinet Decision No. (74) of 2020 on the Terrorism Lists Regulation and the implementation of United Nations Security Council (UNSC) resolutions (targeted financial sanctions).',
    'Cabinet Decision No. (109) of 2023 on beneficial owner procedures, and Cabinet Decision No. (132) of 2023 on the penalties for breaching it.',
    'Cabinet Resolution No. (71) of 2024 on violations and administrative penalties for entities supervised by the Ministry of Justice and MoET [VERIFY: whether this list is still the operative fine schedule alongside Article (17) of Federal Decree-Law No. (10) of 2025].',
    'Ministerial Decree No. (68) of 2024 on adherence to the due diligence regulations for responsible sourcing of gold, and Federal Law No. (11) of 2015 on Monitoring Trade in Precious Metals and its Stamping.',
    'Ministerial Decision No. (253) of 2025 on the controls and conditions for registration of DNFBPs by licensing authorities (11 December 2025) [VERIFY: content applying to {{COMPANY_SHORT}}; text not yet reviewed].',
    'Federal Decree-Law No. (31) of 2021 (Crimes and Penalties), No. (38) of 2022 (Criminal Procedure) and No. (34) of 2021 (cybercrimes), and the federal law on combating terrorism offences [VERIFY: current instrument].',
  ]);
  h3('MoET circulars and guidance (DPMS)');
  bullets([
    'Circular No. 08/AML/2021 (2 June 2021): DPMSR reporting through goAML.',
    'Circular No. (2) of 2023: Data Disclosure Notice to be displayed to customers (section 7.7).',
    'Circular No. (3) of 2025 (19 March 2025): sanctions and terrorist-list screening of all customers, regardless of value or payment method.',
    'Circular No. (6) of 2025 (5 August 2025): risk-based CDD, with simplified due diligence for low risk.',
    'Circular No. (7) of 2025 (19 December 2025): re-imposition of UN sanctions on Iran (UNSCR 1737 and later resolutions).',
    'Circular No. (1) of 2026 (11 March 2026): high-risk countries and countries under increased monitoring, under National Committee Resolution No. (15) of 2025.',
    'Guidelines for Designated Non-Financial Businesses and Professions (March 2026) and the Supplemental Guidance for Dealers in Precious Metals and Stones (March 2026).',
  ]);
  h3('Other');
  bullets([
    'EOCN Guidance on Targeted Financial Sanctions (issued January 2021; last amended March 2026).',
    'The UAE National Risk Assessment (2024) and MoET sectoral risk assessment findings.',
    'FATF Recommendations and guidance for dealers in precious metals and stones; the OECD Due Diligence Guidance for Responsible Supply Chains of Minerals from Conflict-Affected and High-Risk Areas.',
  ]);

''')

s = between(s, "  h2('1.4 Authorities and their roles');", "  h2('1.5 Definitions');", r'''  h2('1.4 Authorities and their roles');
  table(['Authority', 'Role for {{COMPANY_SHORT}}'], [
    ['Ministry of Economy and Tourism (MoET)', 'AML/CFT/CPF supervisor of dealers in precious metals and stones. Approves the Compliance Officer/MLRO, inspects, issues circulars and guidance, and can impose administrative sanctions (Article 17 of Federal Decree-Law No. (10) of 2025). Must be informed of the matters in section 3.8.'],
    ['{{LICENSING_AUTHORITY}}', 'Issues and renews the trade licence. Registration and licence conditions for DNFBPs depend on compliance.'],
    ['UAE Financial Intelligence Unit (FIU)', 'Receives reports through the goAML platform: STR/SAR, DPMSR, HRC/HRCA and follow-up reports (AIF/AIFT/RFI). {{COMPANY_SHORT}} is registered on goAML under entity ID {{GOAML_ENTITY_ID}}.'],
    ['Executive Office for Control and Non-Proliferation (EOCN)', 'Administers targeted financial sanctions: publishes the UN and UAE Local Terrorist Lists, operates the Notification Alert System, and receives CNMR and PNMR reports (copied to MoET) through goAML.'],
    ['National Committee for AML/CFT/CPF', 'Issues the UAE list of high-risk countries and countries under increased monitoring (Appendix C).'],
  ], [2900, 6500]);

''')

s = sub1(s, "['MLRO', 'The Money Laundering Reporting Officer appointed under section 3.3.'],",
         "['MLRO / Compliance Officer', 'The Money Laundering Reporting Officer appointed under section 3.3. The Executive Regulations call this role the Compliance Officer, appointed at management level.'],")
s = sub1(s, "['DPMSR', 'Dealers in Precious Metals and Stones Report: the goAML report for cash and other reportable transactions at or above the MoE threshold (section 7.1).'],",
         "['DPMSR', 'Dealers in Precious Metals and Stones Report: the goAML report filed for the transactions described in section 7.1.'],")
s = sub1(s, "['FFR / PNMR', 'Funds Freeze Report / Partial Name Match Report: goAML reports under the TFS regime (section 6).'],",
         "['CNMR / PNMR', 'Confirmed Name Match Report (formerly the Funds Freeze Report, FFR) and Partial Name Match Report: goAML reports to EOCN under the TFS regime (section 6).'],\n    ['HRC / HRCA', 'High-Risk Country Transaction Report / High-Risk Country Activity Report: goAML reports to the FIU for transactions or activity related to high-risk countries (section 8.4).'],\n    ['Covered precious metals and stones', 'Precious metals at minimum purity (per 1,000 parts): gold 500, silver 800, platinum 850, palladium 500; objects whose value is at least 50% precious metal or stones; rough diamonds of any weight; loose polished diamonds of 0.3 ct or more (mounted 0.5 ct); loose emeralds, rubies and sapphires of 1 ct or more (mounted 2 ct); loose pearls of 3 mm or more (strung or mounted 10 mm). Anything else is handled on a risk basis (MoET Supplemental Guidance).'],")
s = sub1(s, "['Senior Management', '{{SENIOR_MANAGEMENT}}.'],", "['Senior Management', '{{SENIOR_MANAGEMENT}}.'],")

s = between(s, "  table(['Date reviewed', 'Source / reference'", "  // ── 2. ML / TF / PF", r'''  table(['Date reviewed', 'Source / reference', 'What changed', 'Manual section updated', 'Reviewed by'], [
    ['4 Nov 2026', 'Federal Decree-Law 10/2025; Cabinet Resolution 134/2025 (MoET, UAE FIU)', 'New AML/CFT/CPF law and Executive Regulations; PF added; retention triggers widened; Compliance Officer approval; penalty schedule.', '1.3, 2.6, 3.3, 10', ''],
    ['4 Nov 2026', 'EOCN TFS Guidance (amended March 2026); MoET Circulars 3/2025 and 7/2025', 'FFR renamed CNMR; screening scope and triggers; partial-match handling; majority ownership; foreign-sanctions rule.', '6', ''],
    ['4 Nov 2026', 'MoET Supplemental Guidance for DPMS and DNFBP Guidelines (March 2026); Circulars 6/2025, 1/2026, 2/2023', 'DPMSR scope; linked-transaction aggregation; SDD; high-risk country measures and HRC/HRCA; data disclosure notice; third-party Compliance Officer.', '3.3, 4.7, 5, 7, 8.4, App. C', ''],
    ['4 Nov 2026', 'Ministerial Decree 68/2024', 'Dealers apply responsible-sourcing steps 1 to 3; audit and report apply to refiners and recyclers.', '7.4', ''],
    ['', '', '', '', ''],
  ], [1200, 2300, 3000, 1500, 1400]);

''')

s = between(s, "  h2('2.6 Penalties and consequences');", "  h2('2.7 Protection", r'''  h2('2.6 Penalties and consequences');
  p('Under Federal Decree-Law No. (10) of 2025 the main penalties include:');
  table(['Offence', 'Penalty'], [
    ['Money laundering (Art. 26)', 'Imprisonment of 1 to 10 years and a fine of AED 100,000 to 5,000,000, or the value of the criminal property if greater. Aggravated cases (abuse of position, through a non-profit, organised group, certain predicate offences, repeat offence): temporary imprisonment and AED 1,000,000 to 10,000,000, or twice the value if greater.'],
    ['Financing of terrorism', 'Life or temporary imprisonment of at least 10 years and AED 1,000,000 to 10,000,000, or twice the value if greater.'],
    ['Proliferation financing', 'Temporary imprisonment and AED 1,000,000 to 10,000,000, or twice the value if greater.'],
    ['Attempt', 'Same penalty as the completed offence.'],
    ['Legal persons (Art. 27)', 'AED 5,000,000 to 100,000,000, or the value of the property if greater, for ML, TF or PF by its representatives, directors or agents; dissolution and closure for TF/PF and possibly for ML. AED 200,000 to 10,000,000 for the other offences below. The person actually managing the company may also be imprisoned and fined.'],
    ['Failure to report, deliberately or through gross negligence (Art. 28)', 'Imprisonment and AED 100,000 to 1,000,000, or either.'],
    ['Tipping off, or breaching a freezing or seizure order (Art. 29)', 'Imprisonment and a fine of at least AED 50,000, or either.'],
    ['Dealing in funds of evidently illegitimate source or concealed beneficial owner (Art. 30)', 'At least 3 months and at least AED 50,000, or either.'],
    ['Violating EOCN or other competent-authority instructions on targeted financial sanctions (Art. 33)', 'Imprisonment and a fine of at least AED 20,000, or either.'],
    ['False or misleading beneficial-owner information (Art. 35)', 'Imprisonment and a fine of at least AED 20,000, or either.'],
    ['Administrative sanctions by MoET (Art. 17)', 'Warning; fine of AED 10,000 to 5,000,000 per violation; ban from the sector; restriction or suspension of directors and managers; suspension or restriction of the activity; revocation of the licence; periodic remediation reports.'],
  ], [3300, 6100]);
  p('Foreign nationals convicted of these crimes may be deported. Confiscation of the criminal property, or its equivalent value, is mandatory on conviction. [VERIFY: the operative MoET fine schedule per violation type (Cabinet Resolution No. (71) of 2024 predates the new law).]');
''')

s = between(s, "  p('{{COMPANY_SHORT}} has appointed an MLRO and a deputy MLRO", "  table(['Function', 'Name', 'Contact']", r'''  p('{{COMPANY_SHORT}} has appointed an MLRO (the Compliance Officer, at management level) and a deputy MLRO who acts in the MLRO\'s absence. The MLRO is {{MLRO_APPOINTMENT}}. The MLRO and deputy must be resident in the UAE and must have passed a documented fit-and-proper evaluation (qualifications, AML/CFT/CPF experience, reputation, no criminal or regulatory record). **The appointment of the MLRO requires the prior written approval of MoET**, and MoET must be notified of any change in the role or reporting lines. The MLRO must be registered as a user on goAML for {{COMPANY_SHORT}} and keep the registration active. Records of the fit-and-proper assessment are kept.');
''')
s = between(s, "  p('If the MLRO is an external appointee,", "  h3('3.3.2 Independence and access');", r'''  p('If the MLRO is an external appointee, MoET permits this where {{COMPANY_SHORT}} cannot appoint a suitably qualified independent officer internally, on these conditions: the person has the qualifications, experience and understanding of {{COMPANY_SHORT}}\'s business and risks; they are independent of operational and revenue-generating roles; they have unrestricted access to all records, systems and staff they need; the appointment is recorded in a written agreement and documented as outsourcing under section 5.11; and the MLRO remains fully accountable for validating any external service or tool used. The appointee must not also carry out the independent review in section 3.5.');
''')
s = sub1(s, "A full copy is provided to MoE on request.", "A full copy, with Senior Management\\'s observations and decisions, is provided to MoE on request.")
wr('part1.cjs', s)

# ═════════════════════════════ part2 ═════════════════════════════
s = rd('part2.cjs')

s = sub1(s, "taking account of the UAE National Risk Assessment and MoE sector guidance [VERIFY: current National Risk Assessment and sectoral risk assessment for DPMS]. It considers at least:",
         "taking account of the UAE National Risk Assessment (2024) and the MoET sectoral findings, which rate the DPMS sector as high inherent risk. It is documented, kept, updated at least annually and provided to the authorities on request. It considers at least:")

s = between(s, "  // ── 5. CDD", "  h2('5.3 Identifying", r'''  // ── 4.7 high-risk countries
  h2('4.7 High-risk countries (MoET Circular No. 1 of 2026)');
  p('The MLRO follows the lists issued by the National Committee for AML/CFT/CPF and the FATF (Appendix C) and applies the following:');
  bullets([
    '**Countries subject to a FATF call for action ("blacklist"):** Enhanced Due Diligence on every business relationship and transaction connected with the country; no branches, representative offices or subsidiaries there; no reliance on third parties located there for CDD; strengthened internal reporting and STRs where appropriate; full compliance with targeted financial sanctions.',
    '**Countries under increased monitoring ("greylist"):** Enhanced Due Diligence proportionate to the risk, and account taken of FATF updates in risk assessments and relationship reviews.',
    '**HRC / HRCA reports:** a transaction or activity related to a high-risk country is reported to the FIU through goAML (section 8.4) and not carried out until three working days after reporting, unless the FIU objects. If it is also suspicious, the STR obligation takes priority.',
  ]);

  // ── 5. CDD
  h1('5. CUSTOMER DUE DILIGENCE');
  h2('5.1 When customer due diligence (CDD) is required');
  p('{{COMPANY_SHORT}} applies CDD:');
  bullets([
    'before establishing a business relationship;',
    'before any occasional transaction of AED 55,000 or more, whether a single transaction or several that appear linked, including instalments, deposits, trade-ins and payments in kind, whatever the payment method (the Supplemental Guidance treats AED 55,000 as the minimum legal trigger and not as an exemption below it) [VERIFY: MoET to confirm the reading of "cash" in Article 3(3) of Cabinet Resolution No. (134) of 2025; this Manual applies the stricter reading];',
    'when buying gold from any individual or business, whatever the value (section 7.3);',
    'whenever ML, TF or PF is suspected;',
    'whenever there are doubts about the accuracy or adequacy of previously obtained information.',
  ]);
  p('CDD is also applied to existing customers, on the schedule in section 5.9 and whenever circumstances change. Sanctions and PEP screening applies to every customer without exception, whatever the value or payment method (Circular 3 of 2025).');
  h2('5.2 Timing');
  p('CDD, including sanctions and PEP screening, is completed before the relationship is established and before any transaction is carried out. The Executive Regulations allow identity verification to be deferred in low-risk cases; **this Manual does not use that option**. A walk-in customer who cannot be identified and screened is not served.');
''')
# the original 5. CDD h1 + 5.1 + 5.2 were removed by 'between' above (up to 5.3 heading) – check below

s = sub1(s, " {{COMPANY_SHORT}} also checks the customer\\'s declared ownership against the UBO register where accessible [VERIFY: current UBO register access and the duty to report discrepancies].",
         " {{COMPANY_SHORT}} keeps beneficial-owner information on its customers up to date, and its own beneficial-owner information current, notifying changes within 15 working days as the Executive Regulations and Cabinet Decision No. (109) of 2023 require.")

s = sub1(s, "Being a PEP does not by itself make a person unacceptable, but it requires EDD:",
         "Being a PEP does not by itself make a person unacceptable, but it requires EDD. The law requires Senior Management approval and source-of-wealth checks for all foreign PEPs, and for domestic PEPs and international-organisation officials in high-risk relationships; this Manual applies them to every PEP:")

s = sub1(s, "SDD is available only for customers the MLRO has rated Low risk in writing, and only where the law permits.",
         "SDD is available only for customers the MLRO has rated Low risk in writing, only where there is no suspicion, only in coordination with MoET (Circular 6 of 2025), and always with full application of targeted financial sanctions.")

# wholesale rewrite of sections 6 and 7
a = s.index("  // ── 6. TFS")
s = s[:a] + open('part2_tail.txt', **IO).read()
wr('part2.cjs', s)

# ═════════════════════════════ part3 ═════════════════════════════
s = rd('part3.cjs')
s = sub1(s, "Failure to report may be a criminal offence and gross misconduct.",
         "The MLRO must start any internal investigation immediately and continue it without interruption until the matter is reported or cleared. Failure to report may be a criminal offence and gross misconduct.")
s = between(s, "  h2('8.4 External reporting through goAML');", "  h2('8.5 Tipping off');", r'''  h2('8.4 External reporting through goAML');
  p('Reports to the FIU and EOCN are filed only through goAML, by the MLRO as the registered user. STRs/SARs are filed immediately and without delay once suspicion is established, whatever the amount. The report types are:');
  table(['Report', 'When'], [
    ['STR / SAR', 'Suspicion on a transaction (STR) or an activity or attempted transaction (SAR). Filed immediately and without delay.'],
    ['AIF / AIFT / RFI', 'Follow-up information requested by the FIU through the goAML message board (with or without transactions). Reference the original STR/SAR number.'],
    ['DPMSR', 'Threshold transactions described in section 7.1.'],
    ['HRC / HRCA', 'Transactions or activity related to high-risk countries (section 4.7). Do not execute until three working days after reporting unless the FIU objects.'],
    ['CNMR / PNMR', 'Confirmed or partial name match with the UN or UAE Local Terrorist List, reported to EOCN and copied to MoET (section 6).'],
  ], [2200, 7200]);
  p('Select the correct report type. Do not use a CNMR or PNMR for foreign sanctions lists or for suspected sanctions evasion without a list match: those go to the FIU as an STR/SAR (section 6.8). The MLRO keeps the report, the confirmation and all supporting analysis (section 10). Email or post is used only if the FIU directs it.');
''')
s = between(s, "  p('**Retention.**", "  p('**Form and access.**", r'''  p('**Retention.** Records are kept for at least {{RETENTION_YEARS}} years (never less than five) from the **latest** of: the termination of the business relationship or closure of the account; the completion of an occasional transaction where there is no business relationship; the issue of a final judgment by a competent court; the dissolution, liquidation or termination of a legal person; the completion of an inspection by MoET; or the completion of an investigation. If the end of a relationship is unclear, it is taken to be the date of the last transaction. This covers CDD records, account files, correspondence, STRs and analysis, CCTV and other recordings, and screening results (confirmed, partial and false positive), which are kept for at least five years. Records under investigation or requested by an authority are kept for as long as required, even beyond this period. Training logs are kept for the same period.');
''')
wr('part3.cjs', s)

# ═════════════════════════════ part4 ═════════════════════════════
s = rd('part4.cjs')
s = between(s, "  h1('APPENDIX C — HIGH-RISK JURISDICTIONS');", "  table(['Jurisdiction', 'Source list'", r'''  h1('APPENDIX C — HIGH-RISK JURISDICTIONS');
  p('This Manual does not reproduce lists that change. The MLRO keeps the register below up to date from the following sources and checks them at least quarterly and after each FATF plenary and each National Committee resolution (the current resolution is No. (15) of 2025, notified by MoET Circular No. (1) of 2026):');
  bullets([
    '**FATF "Call for Action" list** ("blacklist"): measures as in section 4.7 (Enhanced Due Diligence on all relationships and transactions, no branches or subsidiaries, no third-party reliance, strengthened reporting, HRC/HRCA reports).',
    '**FATF "Increased Monitoring" list** ("greylist"): Enhanced Due Diligence proportionate to the risk, and HRC/HRCA reporting where the National Committee list requires it.',
    '**National Committee for AML/CFT/CPF** list of high-risk countries (namlcftc.gov.ae), and any MoET or FIU circular updating it.',
    'UN and UAE sanctions regimes, including the UN sanctions on Iran re-imposed under UNSCR 1737 (2006) (Circular 7 of 2025) and on the DPRK.',
    'Jurisdictions {{COMPANY_SHORT}} itself assesses as high risk (corruption, illicit gold, conflict, weak AML supervision), recorded with the reason.',
  ]);
  p('[VERIFY: the jurisdictions currently on each list, taken from the National Committee website and the FATF pages cited in Circular No. (1) of 2026.]');
''')
s = sub1(s, '☐ FFR filed', '☐ CNMR filed')
s = sub1(s, '☐ False positive     ☐ Partial / possible match     ☐ Confirmed match',
         '☐ Negative match     ☐ False positive     ☐ Partial name match     ☐ Confirmed name match')
s = sub1(s, "    'authority to hold or refuse any transaction, to freeze assets under section 6 and to refuse any relationship;',",
         "    'authority to hold or refuse any transaction, to freeze assets under section 6 and to refuse any relationship;',\n    'prior written approval of MoET for the appointment, and fit-and-proper records (section 3.3.1);',")
s = sub1(s, "'filing STRs/SARs, DPMSRs, FFRs, PNMRs and other goAML reports on time, and notifying MoE where required;'",
         "'filing STRs/SARs, DPMSRs, HRC/HRCA, CNMRs, PNMRs and other goAML reports on time, and notifying MoE where required;'")
wr('part4.cjs', s)

# ═════════════════════════════ global renames ═════════════════════════════
for f in ('part1.cjs', 'part2.cjs', 'part3.cjs', 'part4.cjs'):
    t = rd(f)
    t = t.replace('Ministry of Economy (MoE)', 'Ministry of Economy and Tourism (MoET)')
    t = t.replace('Ministry of Economy', 'Ministry of Economy and Tourism') if 'Ministry of Economy and Tourism' not in t else re.sub(r'Ministry of Economy(?! and Tourism)', 'Ministry of Economy and Tourism', t)
    t = re.sub(r'\bMoE\b', 'MoET', t)
    wr(f, t)

b = rd('build.cjs')
b = b.replace('AML-CFT-CPF-Manual-Template-v2.docx', 'AML-CFT-CPF-Manual-Template-v2.1.docx').replace('Template v2', 'Template v2.1').replace('node build.cjs  ->  AML-CFT-CPF-Manual-Template-v2.docx', 'node build.cjs')
wr('build.cjs', b)
print('patched')
