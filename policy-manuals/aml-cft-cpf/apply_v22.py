# One-off patch: v2.1 -> v2.2 (dated FATF register, CAHRA section, tighter risk-assessment wording).
import os
os.chdir(os.path.dirname(os.path.abspath(__file__)))
IO = dict(encoding='utf-8', newline='')
rd = lambda p: open(p, **IO).read()
def wr(p, s): open(p, 'w', **IO).write(s)
def sub1(s, old, new):
    assert s.count(old) == 1, (s.count(old), old[:80])
    return s.replace(old, new, 1)
def between(s, start, end, new):
    a = s.index(start); b = s.index(end, a)
    return s[:a] + new + s[b:]

# ═════════ part2: section 4 wording, section 7 CAHRA ═════════
s = rd('part2.cjs')

s = between(s, "  h2('4.1 Principle');", "  h2('4.4 Lower-risk indicators');", r'''  h2('4.1 Principle');
  p('{{COMPANY_SHORT}} identifies, assesses, understands, documents and keeps up to date its ML/TF/PF risks, and applies controls in proportion: enhanced measures where risk is higher, and, only where the law and this Manual allow, simplified measures where it is demonstrably lower. Even for low-risk customers, identification, verification and sanctions screening are always carried out.');
  p('The risk methodology, both for the business and for customers, is written down with its rationale and assumptions, approved by {{SENIOR_MANAGEMENT}}, and communicated to everyone who applies it. Its results decide the due diligence applied (simplified, standard or enhanced).');
  h2('4.2 Business risk assessment');
  p('The MLRO prepares a written business (enterprise-wide) risk assessment (BRA) covering ML, TF and PF, taking account of the UAE National Risk Assessment (2024) and the MoET sectoral findings, which rate the DPMS sector as high inherent risk. The BRA is separate from the customer risk assessment: it measures the risk of the business as a whole, while the customer assessment rates one relationship.');
  h3('Method');
  bullets([
    'Identify the inherent risks from these factors: customer types and their activities; countries and geographic areas of customers, suppliers, gold origin, transit and destination; products and services (bars, coins, jewellery, scrap, refined gold; buying and selling); delivery channels and counterparties; transaction size, frequency and payment methods, especially cash and third-party payments; supply chain and responsible-sourcing exposure; proliferation-financing exposure, including dual-use goods and sanctioned jurisdictions; new products, practices, delivery mechanisms and technologies.',
    'Rate each risk for likelihood and impact, using both quantitative data (transaction volumes, cash share, customer mix, report counts) and qualitative input (interviews with staff, the MLRO and management; internal questionnaires; audit and review findings).',
    'Weight the factors, rank the risks and record the rationale for the weights.',
    'Assess how well the existing controls work, using the independent review, MLRO reports, testing and management information.',
    'Work out the residual risk (inherent risk less control effectiveness) and compare it with the risk appetite approved by {{SENIOR_MANAGEMENT}}. Where residual risk is above appetite, decide on added controls, with an owner and a deadline.',
    'Record any manual override of a model-based weighting or rating, with the reason and who approved it.',
  ]);
  h3('Documentation, certification and updating');
  bullets([
    'The BRA, its methodology, analysis, data and sources are documented and kept (section 10), and given to MoET on request.',
    '{{SENIOR_MANAGEMENT}} formally certifies in writing that the BRA is an accurate reflection of {{COMPANY_SHORT}}\'s risk exposure, is supported by appropriate mitigation measures, and is available for MoET review. The certification is dated and signed.',
    'The BRA is reviewed at least annually, and updated sooner whenever there is a change in business strategy, products, services or customer base; a new technology or delivery channel; a change in law, a circular or guidance; a new National or sectoral risk assessment; a FATF or National Committee list change affecting {{COMPANY_SHORT}}\'s markets; or the identification of a material new threat.',
    'Before any new product, service, practice or technology is launched, the MLRO assesses its ML/TF/PF risk and the mitigation needed.',
    'The BRA methodology and its outputs are tested in the annual independent review (section 3.5).',
  ]);
  p('The results feed the design and testing of controls, the allocation of resources, and the customer risk assessment.');
  h2('4.3 Customer risk assessment');
  p('Every customer is risk-assessed and rated Low, Medium or High before the relationship starts or any transaction takes place, then reviewed periodically (section 5.9) and whenever risk factors change. The assessment is recorded on the Customer Risk Assessment Form (Appendix G). Factors:');
  bullets([
    'the customer and each beneficial owner: nature, business, reputation, ownership and control structure;',
    'purpose and intended nature of the relationship and the expected volume and type of transactions;',
    'geography: nationality, residence, incorporation, place of business, and the origin and destination of the gold and payments;',
    'products, payment method and delivery channel;',
    'PEP status, sanctions screening and adverse media results;',
    'the outcome of the business risk assessment.',
  ]);
  p('The scoring model in Appendix G, including its factors, scores and rating bands, is a documented model: the MLRO explains the rationale and assumptions behind it, and {{SENIOR_MANAGEMENT}} approves it. The MLRO reviews the model at least annually and when the BRA changes. The rating decides the due diligence level. A rating may be overridden only by the MLRO, in writing, with reasons, and never to a lower rating than the Appendix G overrides require. Besides the scheduled reviews in section 5.9, an event-driven review is carried out on: contradictory or outdated CDD information; a change of ownership, structure or control; new legal proceedings involving the customer or a beneficial owner; adverse media; qualified audit opinions or third-party concerns; and transactions inconsistent with the customer\'s profile. Each review decides whether to update the rating, escalate to Senior Management, file an STR/SAR, or suspend or end the relationship.');
''')

s = sub1(s, "or gold from conflict-affected and high-risk areas (section 7.4);", "or gold from conflict-affected and high-risk areas (section 7.5);")
s = sub1(s, "  h2('7.5 Monitoring and record details');", r'''  h2('7.5 Conflict-affected and high-risk areas (CAHRA)');
  p('The UAE does not publish a list of conflict-affected and high-risk areas, and neither does the OECD. MoET\'s Due Diligence Regulations for Responsible Sourcing of Gold (Version 1, August 2022) define CAHRAs as areas of armed conflict, widespread violence (including violence by criminal networks), or other serious and widespread harm to people, and high-risk areas as those with a high risk of conflict or of widespread or serious abuses, as described in Annex II of the OECD Due Diligence Guidance. They are often marked by political instability or repression, institutional weakness, insecurity, collapse of civil infrastructure, and widespread violations of national or international law. Gold or stones that originate in, are routed through, or are linked to a CAHRA need enhanced scrutiny. **{{COMPANY_SHORT}} therefore decides for itself, and records, which areas it treats as CAHRAs (Appendix I).**');
  h3('Assessment of each supply chain');
  bullets([
    'The assessment is made for **each supply chain** (each consignment or batch), not once for the supplier, and is repeated even if earlier supplies from that supplier were clean or showed no high-risk element.',
    'Collect, for each supply: the country and place of origin and the type of source (large-scale mine, artisanal and small-scale mining (ASM), recycled or scrap, mixed); the transport and transit route; where and by whom it was refined; and the upstream suppliers as far as can be established.',
    'Use KYC and the red flags below to decide whether the gold was mined, transported or traded in a CAHRA. Record the conclusion and the evidence in Appendix I.',
  ]);
  h3('Location red flags');
  bullets([
    'the gold comes from, or has passed through, a CAHRA or a country subject to international sanctions;',
    'the gold comes from a country with limited known reserves or production that does not match the volume offered;',
    'the gold comes from a country through which gold from CAHRAs is known or reasonably suspected to transit;',
    'the gold is described as recycled, scrap or mixed and was refined in such a transit country;',
    'the gold comes from or passed through countries with weak oversight of money laundering, corruption, bribery, informal banking and cash-intensive economies.',
  ]);
  h3('Supplier red flags');
  bullets([
    'the supplier or its upstream entities operate in a location above, or hold interests in suppliers from one;',
    'the supplier is known to have sourced gold from one of those locations in the last 12 months;',
    'the KYC information is inconsistent, or the supplier refuses to provide documents;',
    'the supplier or a beneficial owner appears on a government list for money laundering, fraud or terrorism, or under international sanctions;',
    'the supplier has no ethics, integrity, anti-money-laundering, bribery and corruption policies;',
    'the supplier cannot show an effective assessment of its own counterparties and upstream supply chain.',
  ]);
  p('For ASM gold also check whether the supplier, processing plant, trader or exporter is legitimate: legally registered, cooperative-based or government-recognised, or part of a government or central-bank supported initiative, and whether the mining follows recognised standards and good practice.');
  h3('Outcome and action');
  table(['Finding', 'Action'], [
    ['No CAHRA link and no red flags, on the information collected', 'Record the reasoning. Standard supplier CDD and monitoring apply; no further enhanced due diligence on that supply chain.'],
    ['Red flags, or origin or route information is missing', 'Enhanced due diligence: obtain the missing evidence, corroborate origin and route with independent documents, and escalate to the MLRO. Do not accept the gold until the MLRO has cleared it.'],
    ['CAHRA link confirmed, or red flags cannot be resolved', 'Suspend or refuse the supply chain. Any exception needs Senior Management approval and a written risk-mitigation plan with monitoring. The MLRO considers an STR/SAR, and reviews the supplier relationship.'],
  ], [3300, 6100]);
  p('The MLRO keeps the CAHRA register in Appendix I and reviews it at least quarterly, and whenever circumstances change. Inputs include: UN Security Council and UAE sanctions regimes; the FATF and National Committee lists (Appendix C); MoET circulars and guidance; and reputable government and intergovernmental reporting on conflict, illicit gold and human rights abuse. Records of each assessment are kept under section 10.');
  h2('7.6 Monitoring and record details');''')
s = sub1(s, "  h2('7.6 Trade and shipment red flags');", "  h2('7.7 Trade and shipment red flags');")
s = sub1(s, "  h2('7.7 Data disclosure notice');", "  h2('7.8 Data disclosure notice');")
s = sub1(s, "  h2('7.8 Refusing or ending a transaction');", "  h2('7.9 Refusing or ending a transaction');")
wr('part2.cjs', s)

# ═════════ part1: cross-reference, change log, review scope ═════════
s = rd('part1.cjs')
s = sub1(s, "Data Disclosure Notice to be displayed to customers (section 7.7).", "Data Disclosure Notice to be displayed to customers (section 7.8).")
s = sub1(s, "the quality of risk ratings;", "the risk assessment methodology and the quality of risk ratings;")
s = sub1(s, "    ['', '', '', '', ''],\n  ], [1200, 2300, 3000, 1500, 1400]);",
         "    ['4 Nov 2026', 'FATF lists of 30 October 2026; National Committee (high-risk jurisdictions); MoET Responsible Sourcing page and Due Diligence Regulations v1; DNFBP Guidelines section 8', 'Dated register of FATF lists; CAHRA assessment per supply chain; risk assessment method, certification and update triggers.', '4.1 to 4.3, 7.5, App. C, G, I', ''],\n    ['', '', '', '', ''],\n  ], [1200, 2300, 3000, 1500, 1400]);")
wr('part1.cjs', s)

# ═════════ part4: Appendix C register, G wording, Appendix I ═════════
s = rd('part4.cjs')
s = between(s, "  h1('APPENDIX C — HIGH-RISK JURISDICTIONS');", "  // ── D. Declaration", r'''  h1('APPENDIX C — HIGH-RISK JURISDICTIONS');
  p('The UAE list of high-risk countries and countries under increased monitoring is issued by the National Committee for AML/CFT/CPF and notified by MoET circulars (currently Circular No. (1) of 2026, under National Committee Resolution No. (15) of 2025). It follows the FATF lists, which the National Committee publishes on its website (namlcftc.gov.ae). The MLRO checks the sources below at least quarterly and after every FATF plenary and National Committee resolution, updates the register, and records each check in the review log. If the National Committee\'s list and the FATF list ever differ, the stricter applies until MoET clarifies.');
  h3('Register as at 30 October 2026');
  p('Source: FATF publications of 30 October 2026 ("Call for Action" and "Increased Monitoring"), the editions linked from the National Committee website. Last checked by the MLRO: ______ (date).');
  table(['Jurisdiction', 'List', 'Measures'], [
    ['Democratic People\'s Republic of Korea', 'FATF Call for Action', 'Section 4.7: Enhanced Due Diligence on all relationships and transactions, countermeasures, HRC/HRCA reports, no reliance on third parties there, no branches or subsidiaries.'],
    ['Iran', 'FATF Call for Action', 'As above. Also UN sanctions re-imposed (Circular No. (7) of 2025): screen and freeze under section 6.'],
    ['Myanmar', 'FATF Call for Action', 'As above.'],
  ], [3000, 2000, 4400]);
  table(['Jurisdiction', 'List', 'Measures'], [
    ['Angola', 'FATF Increased Monitoring', 'Enhanced Due Diligence proportionate to the risk; account taken in risk assessments and reviews; HRC/HRCA where the National Committee list requires.'],
    ['Bolivia', 'FATF Increased Monitoring', 'As above.'],
    ['Bosnia and Herzegovina', 'FATF Increased Monitoring (added June 2026)', 'As above.'],
    ['Bulgaria', 'FATF Increased Monitoring', 'As above. FATF made an initial determination in June 2026 that its action plan is substantially complete; watch for removal.'],
    ['Cameroon', 'FATF Increased Monitoring', 'As above.'],
    ['Côte d\'Ivoire', 'FATF Increased Monitoring', 'As above.'],
    ['Democratic Republic of the Congo', 'FATF Increased Monitoring', 'As above. Also treat as a CAHRA candidate (Appendix I).'],
    ['Haiti', 'FATF Increased Monitoring', 'As above.'],
    ['Iraq', 'FATF Increased Monitoring (added June 2026)', 'As above.'],
    ['Kenya', 'FATF Increased Monitoring', 'As above.'],
    ['Kuwait', 'FATF Increased Monitoring', 'As above.'],
    ['Lao PDR', 'FATF Increased Monitoring', 'As above.'],
    ['Lebanon', 'FATF Increased Monitoring', 'As above.'],
    ['Monaco', 'FATF Increased Monitoring', 'As above.'],
    ['Nepal', 'FATF Increased Monitoring', 'As above.'],
    ['Papua New Guinea', 'FATF Increased Monitoring', 'As above.'],
    ['South Sudan', 'FATF Increased Monitoring', 'As above.'],
    ['Syria', 'FATF Increased Monitoring', 'As above.'],
    ['Venezuela', 'FATF Increased Monitoring', 'As above.'],
    ['Vietnam', 'FATF Increased Monitoring', 'As above.'],
    ['Virgin Islands (UK)', 'FATF Increased Monitoring', 'As above.'],
    ['Yemen', 'FATF Increased Monitoring', 'As above.'],
  ], [3000, 2400, 4000]);
  h3('Other jurisdictions treated as high risk by {{COMPANY_SHORT}}');
  p('Countries {{COMPANY_SHORT}} assesses as high risk for corruption, illicit gold, conflict or weak AML supervision, with the reason, are recorded here. UN and UAE sanctions regimes (including Iran under UNSCR 1737 (2006), and the DPRK) apply in addition. Conflict-affected and high-risk areas for the gold supply chain are recorded in Appendix I.');
  table(['Jurisdiction', 'Reason', 'Date added', 'Measures'], [
    ['', '', '', ''], ['', '', '', ''], ['', '', '', ''],
  ], [2400, 3600, 1400, 2000]);
  h3('Review log');
  table(['Date checked', 'Sources checked', 'Change found', 'Register updated', 'MLRO'], [
    ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''],
  ], [1300, 2900, 2500, 1500, 1200]);

''')

s = sub1(s, "    ['Date of assessment; assessor', ''],\n  ], [3200, 6200]);",
         "    ['Date of assessment; assessor', ''],\n    ['Scoring model version; approved by Senior Management on', ''],\n  ], [3200, 6200]);")
s = sub1(s, "p('Score each factor 1 (Low), 2 (Medium) or 3 (High). Add the scores.');",
         "p('Score each factor 1 (Low), 2 (Medium) or 3 (High). Add the scores. The factors, scores and rating bands are the documented model approved by Senior Management (section 4.3); only the MLRO may override a rating, in writing and with reasons.');")

s = s.rstrip()
assert s.endswith('};')
s = s[:-2] + r'''
  // ── I. CAHRA assessment ──────────────────────────────────────────────
  h1('APPENDIX I — CAHRA ASSESSMENT RECORD');
  p('Complete one record for each supply chain (consignment or batch) before the gold is accepted (section 7.5). Keep it with the supplier file.');
  table(null, [
    ['Date; completed by', ''],
    ['Supplier and ultimate beneficial owners', ''],
    ['Supply / consignment reference', ''],
    ['Type of source', '☐ Large-scale mine     ☐ ASM     ☐ Recycled / scrap     ☐ Mixed     ☐ Refined bars'],
    ['Country and place of origin', ''],
    ['Transport and transit route; refiner and refining location', ''],
    ['Upstream suppliers identified', ''],
    ['Documents obtained (origin certificate, assay, invoices, customs, transport)', ''],
    ['Location red flags found (section 7.5)', '☐ None     ☐ CAHRA or sanctioned origin or transit     ☐ Output beyond known reserves     ☐ CAHRA transit country     ☐ Scrap refined in a transit country     ☐ Weak AML oversight / cash economy'],
    ['Supplier red flags found (section 7.5)', '☐ None     ☐ Operates in or sources from a red-flag location (last 12 months)     ☐ Inconsistent KYC / refusal     ☐ Listed person / sanctions     ☐ No integrity policies     ☐ Weak upstream assessment'],
    ['ASM checks (if applicable)', '☐ Legally registered / cooperative / government-recognised     ☐ Recognised standards followed     ☐ Not applicable'],
    ['Conclusion', '☐ No CAHRA link, no red flags – accept     ☐ Red flags or gaps – EDD     ☐ CAHRA link – suspend / refuse / exception'],
    ['Reasons and evidence', '\n\n'],
    ['MLRO decision, signature, date', ''],
    ['Senior Management approval (exceptions only) and mitigation plan', ''],
  ], [3800, 5600]);
  h3('CAHRA register');
  p('Areas {{COMPANY_SHORT}} treats as conflict-affected or high-risk for gold supply chains, kept by the MLRO and reviewed at least quarterly (section 7.5). No official list exists; record the basis for each entry.');
  table(['Area / country', 'Why treated as a CAHRA (sources)', 'Date added', 'Last reviewed', 'Reviewed by'], [
    ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''],
  ], [2000, 3600, 1200, 1400, 1200]);
};
'''
wr('part4.cjs', s)
print('patched')
