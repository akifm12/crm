// Front matter and sections 1-3
const { Paragraph, TextRun, AlignmentType, PageBreak, TableOfContents } = require('docx');

module.exports = (L) => {
  const { add, runs, h1, h2, h3, p, bullets, table, note, NAVY } = L;

  // ── Template guide (delete before issue) ───────────────────────────────
  add(new Paragraph({ spacing: { after: 160 }, children: [new TextRun({ text: 'TEMPLATE GUIDE — delete this page before issue', bold: true, size: 28, color: NAVY })] }));
  p('This is the master template of the AML / CFT / CPF and Sanctions Policy Manual for dealers in precious metals (gold / bullion). It is written for a UAE mainland company supervised by the Ministry of Economy (MoE). Replace every highlighted placeholder with the company\'s own details. Nothing else in the text is company-specific.');
  table(['Placeholder', 'Replace with', 'Example'], [
    ['{{COMPANY_NAME}}', 'Full legal name, as on the trade licence', 'Example Gold Trading LLC'],
    ['{{COMPANY_SHORT}}', 'Short name used throughout the text', 'Example Gold'],
    ['{{TRADE_LICENCE_NO}}', 'Trade licence number', '123456'],
    ['{{LICENSING_AUTHORITY}}', 'Authority that issued the trade licence', 'Dubai Department of Economy and Tourism'],
    ['{{GOAML_ENTITY_ID}}', 'goAML reporting entity registration number', '—'],
    ['{{SENIOR_MANAGEMENT}}', 'Who exercises senior management / board authority', 'the Managing Director'],
    ['{{MLRO_NAME}} / {{MLRO_EMAIL}} / {{MLRO_PHONE}}', 'Money Laundering Reporting Officer', '—'],
    ['{{DEPUTY_MLRO_NAME}} / {{DEPUTY_MLRO_EMAIL}} / {{DEPUTY_MLRO_PHONE}}', 'Deputy MLRO (covers absence)', '—'],
    ['{{MLRO_APPOINTMENT}}', 'Whether the MLRO is an employee or an external appointee', 'an external appointee under a written service agreement'],
    ['{{SCREENING_TOOL}}', 'Sanctions / PEP / adverse media screening tool in use', '—'],
    ['{{RETENTION_YEARS}}', 'Record retention period in years (not less than five)', '5'],
    ['{{VERSION}} / {{ISSUE_DATE}} / {{APPROVER}}', 'Document control', '1.0 / 1 January 2027 / Managing Director'],
  ], [3300, 3900, 2200]);
  p('**Cyan [VERIFY: …] tags** mark statements that depend on the current text of a law, circular or authority guidance (deadlines, thresholds, instrument numbers, report types). They are to be checked against the latest MoE, FIU and EOCN publications and removed once confirmed. The regulatory change log in section 1.6 records each update.');

  // ── Cover ──────────────────────────────────────────────────────────────
  add(new Paragraph({ children: [new PageBreak()] }));
  for (let i = 0; i < 5; i++) add(new Paragraph({ children: [] }));
  add(new Paragraph({ alignment: AlignmentType.LEFT, spacing: { after: 120 }, children: [new TextRun({ text: 'ANTI-MONEY LAUNDERING, COUNTER-TERRORIST FINANCING, COUNTER-PROLIFERATION FINANCING AND SANCTIONS', bold: true, size: 40, color: NAVY })] }));
  add(new Paragraph({ spacing: { after: 360 }, children: [new TextRun({ text: 'Policy and Procedures Manual', size: 32, color: NAVY })] }));
  add(new Paragraph({ spacing: { after: 120 }, children: runs('{{COMPANY_NAME}}', { size: 32, bold: true }) }));
  add(new Paragraph({ spacing: { after: 60 }, children: runs('Dealer in Precious Metals and Stones — Trade Licence No. {{TRADE_LICENCE_NO}}', { size: 22 }) }));
  add(new Paragraph({ spacing: { after: 360 }, children: runs('Licensing authority: {{LICENSING_AUTHORITY}}   |   AML supervisor: Ministry of Economy', { size: 22 }) }));
  add(new Paragraph({ spacing: { after: 60 }, children: runs('Version {{VERSION}}', { size: 24 }) }));
  add(new Paragraph({ spacing: { after: 600 }, children: runs('Issued {{ISSUE_DATE}}', { size: 24 }) }));
  add(new Paragraph({ children: runs('CONFIDENTIAL — for internal use by {{COMPANY_SHORT}} staff and its regulators.', { size: 20, italics: true }) }));

  add(new Paragraph({ children: [new PageBreak()] }));
  add(new Paragraph({ spacing: { after: 160 }, children: [new TextRun({ text: 'TABLE OF CONTENTS', bold: true, size: 30, color: NAVY })] }));
  add(new TableOfContents('Contents', { hyperlink: true, headingStyleRange: '1-2' }));

  // ── 1. Introduction ────────────────────────────────────────────────────
  h1('1. INTRODUCTION');
  h2('1.1 Purpose and scope');
  p('This Manual sets out the policies and procedures of {{COMPANY_NAME}} ("{{COMPANY_SHORT}}") to prevent money laundering (ML), the financing of terrorism (TF), the financing of illegal organisations and the financing of the proliferation of weapons of mass destruction (PF), and to comply with targeted financial sanctions (TFS). It applies to all of {{COMPANY_SHORT}}\'s activities as a dealer in precious metals and stones (DPMS), including buying, selling, refining, trading, import and export of gold and other precious metals.');
  p('It applies to the owners, {{SENIOR_MANAGEMENT}}, all employees (permanent, temporary and contracted), and anyone acting on behalf of {{COMPANY_SHORT}}. It was approved by {{SENIOR_MANAGEMENT}} and takes effect on the issue date shown on the cover.');
  h2('1.2 Staff obligations and consequences');
  p('Every member of staff must read this Manual, sign the declaration in Appendix D and follow it. In particular, every member of staff must:');
  bullets([
    'report any knowledge or suspicion of ML, TF or PF to the MLRO immediately, using the Internal SAR form (Appendix E);',
    'never tell a customer or any third party that a report has been made or that an enquiry is under way ("tipping off");',
    'never help anyone to launder money, finance terrorism or evade sanctions, or "aid or abet" such activity;',
    'complete customer due diligence and sanctions screening before any transaction.',
  ]);
  p('Failure to comply may lead to disciplinary action up to dismissal, and may be a criminal offence for the individual and an administrative or criminal matter for {{COMPANY_SHORT}}. {{COMPANY_SHORT}} may report breaches to the Ministry of Economy (MoE) or law enforcement.');

  h2('1.3 Legal and regulatory framework');
  p('This Manual is based on the following. The instruments and their latest amendments must be kept up to date by the MLRO (section 12).');
  bullets([
    'Federal Decree-Law No. (10) of 2025 on Anti-Money Laundering, Combating the Financing of Terrorism and Proliferation Financing, and its Executive Regulations [VERIFY: exact title, effective date and the Cabinet resolution issuing the Executive Regulations].',
    'Cabinet Decision No. (74) of 2020 on the Terrorism Lists Regulation and the implementation of United Nations Security Council (UNSC) resolutions, and guidance of the Executive Office for Control and Non-Proliferation (EOCN) [VERIFY: still current].',
    'Cabinet Decision No. (58) of 2020 on the Beneficial Owner procedures [VERIFY: still current].',
    'Federal Decree-Law No. (31) of 2021 (Crimes and Penalties), Federal Decree-Law No. (38) of 2022 (Criminal Procedure) and Federal Decree-Law No. (34) of 2021 (Combating Rumours and Cybercrimes), and the federal law on combating terrorism offences [VERIFY: current instrument].',
    'MoE circulars and guidance for DNFBPs, in particular for dealers in precious metals and stones, including the goAML reporting obligations and the rules on responsible sourcing of gold [VERIFY: list the circulars currently in force].',
    'FATF Recommendations and FATF guidance for dealers in precious metals and stones; the OECD Due Diligence Guidance for Responsible Supply Chains of Minerals from Conflict-Affected and High-Risk Areas.',
  ]);

  h2('1.4 Authorities and their roles');
  table(['Authority', 'Role for {{COMPANY_SHORT}}'], [
    ['Ministry of Economy (MoE)', 'AML/CFT supervisor of dealers in precious metals and stones. Inspects, issues guidance and circulars, and can impose administrative sanctions. Must be informed of the matters in section 3.8.'],
    ['{{LICENSING_AUTHORITY}}', 'Issues and renews the trade licence. Licence conditions and renewals depend on compliance.'],
    ['UAE Financial Intelligence Unit (FIU)', 'Receives reports through the goAML platform (suspicious transaction/activity reports, DPMSR, funds-freeze and partial-name-match reports, and any others it requires). {{COMPANY_SHORT}} is registered on goAML under entity ID {{GOAML_ENTITY_ID}}.'],
    ['Executive Office for Control and Non-Proliferation (EOCN)', 'Administers targeted financial sanctions: publishes the UN and UAE Local Terrorist Lists, issues alerts and notifications, and receives freeze and match information.'],
  ], [2900, 6500]);

  h2('1.5 Definitions');
  table(['Term', 'Meaning'], [
    ['Customer', 'A person with whom {{COMPANY_SHORT}} has or is establishing a business relationship, or for whom it carries out an occasional transaction (including a person who sells gold to {{COMPANY_SHORT}}).'],
    ['Business relationship', 'An ongoing commercial relationship between the customer and {{COMPANY_SHORT}}.'],
    ['Occasional transaction', 'A transaction with a customer with whom there is no established business relationship.'],
    ['Beneficial owner (UBO)', 'The natural person who ultimately owns or controls the customer, or on whose behalf a transaction is carried out. For a company this includes anyone holding, directly or indirectly, 25% or more of the shares or voting rights, or exercising control by other means; where none is found, the senior managing official.'],
    ['Politically exposed person (PEP)', 'A natural person who is or has been entrusted with a prominent public function in the UAE or elsewhere (head of state or government, senior politician, senior government, judicial or military official, ambassador, senior executive of a state-owned company, important political party official, or a senior official of an international organisation), and their family members (spouse, children, children\'s spouses, parents) and close associates.'],
    ['Source of funds / source of wealth', 'Source of funds: the origin of the money used in a specific transaction. Source of wealth: how the customer\'s overall wealth was acquired.'],
    ['MLRO', 'The Money Laundering Reporting Officer appointed under section 3.3.'],
    ['Senior Management', '{{SENIOR_MANAGEMENT}}.'],
    ['goAML / STR / SAR', 'goAML is the FIU\'s online reporting platform. STR/SAR: Suspicious Transaction Report / Suspicious Activity Report.'],
    ['DPMSR', 'Dealers in Precious Metals and Stones Report: the goAML report for cash and other reportable transactions at or above the MoE threshold (section 7.1).'],
    ['FFR / PNMR', 'Funds Freeze Report / Partial Name Match Report: goAML reports under the TFS regime (section 6).'],
    ['TFS', 'Targeted financial sanctions: asset freezing and the prohibition on making funds or services available to listed persons.'],
    ['CAHRA', 'Conflict-affected and high-risk area (as defined in the OECD Due Diligence Guidance).'],
  ], [2500, 6900]);

  h2('1.6 Version control and regulatory change log');
  table(['Version', 'Date', 'Change', 'Approved by'], [
    ['{{VERSION}}', '{{ISSUE_DATE}}', 'Issue of this Manual under Federal Decree-Law No. (10) of 2025.', '{{APPROVER}}'],
    ['', '', '', ''],
  ], [1100, 1800, 4700, 1800]);
  p('Regulatory change log (record each change in law, circular or guidance, the date the MLRO reviewed it and the section of this Manual changed):');
  table(['Date reviewed', 'Source / reference', 'What changed', 'Manual section updated', 'Reviewed by'], [
    ['', '', '', '', ''],
    ['', '', '', '', ''],
  ], [1400, 2200, 2800, 1700, 1300]);

  // ── 2. ML / TF / PF ────────────────────────────────────────────────────
  h1('2. MONEY LAUNDERING, TERRORIST FINANCING AND PROLIFERATION FINANCING');
  h2('2.1 Money laundering');
  p('A person commits money laundering if, knowing that funds are the proceeds of a felony or misdemeanour, they intentionally: transfer, move or conduct a transaction with the proceeds to conceal or disguise their illegal source; conceal or disguise the true nature, source, location, movement, ownership or rights over the proceeds; acquire, possess or use the proceeds on receipt; or help the offender of the predicate offence to escape punishment.');
  p('Money laundering is an independent crime: the offender can be punished for it as well as for the predicate offence, and the illegal source need not be proven first. An attempt is also an offence. A company can be liable as well as the individuals involved.');
  h2('2.2 Stages of money laundering');
  table(['Stage', 'What happens', 'Examples in the gold trade'], [
    ['Placement', 'Illegal cash enters the system.', 'Buying gold bars or jewellery with cash; many small cash purchases below reporting thresholds; paying cash for scrap gold.'],
    ['Layering', 'Funds are moved and converted to hide their origin.', 'Rapid buy-and-sell of the same gold; payments through third parties or several accounts; melting and recasting; moving gold across borders; over- or under-invoicing.'],
    ['Integration', 'Funds re-enter the economy as apparently legitimate wealth.', 'Selling the gold to a legitimate buyer and receiving a clean bank payment; using gold sales to justify wealth.'],
  ], [1500, 3000, 4900]);
  h2('2.3 Terrorist financing and financing of illegal organisations');
  p('It is an offence to provide, collect, prepare or obtain funds, or to facilitate others doing so, knowing or intending that they will be used in whole or in part for a terrorist act, a terrorist organisation or person, or an illegal organisation or its members, and to carry out any ML act with funds known to belong to or be intended for them. Funds used for terrorist financing may be legitimate in origin and small in amount, so the absence of a criminal source does not remove the risk.');
  h2('2.4 Proliferation financing');
  p('Proliferation financing is the provision of funds, assets or financial services used to develop, acquire, transport or export nuclear, chemical or biological weapons and their means of delivery, including dual-use goods, in breach of UNSC resolutions or UAE law. {{COMPANY_SHORT}} manages the risk through TFS screening (section 6), its business risk assessment (section 4), and attention to unusual counterparties, destinations, shipping routes and trade documentation.');
  h2('2.5 Why the gold trade is exposed');
  bullets([
    'gold is highly valuable, portable and globally accepted, and can be bought for cash;',
    'its origin can be hard to trace, especially for scrap, recycled or artisanally mined gold, and it can be melted to change its form;',
    'gold passes through many hands and jurisdictions, including high-risk and conflict-affected areas;',
    'it is used in trade-based money laundering through false invoicing and misdeclared quantity, purity or value.',
  ]);
  h2('2.6 Penalties and consequences');
  p('Money laundering, terrorist financing, financing of illegal organisations, failure to report, tipping off, breach of TFS obligations and breach of AML/CFT requirements are punishable by imprisonment and fines for individuals, and by fines, suspension or cancellation of the licence and closure of the business for legal entities. Foreign nationals convicted of these crimes may be deported. MoE can also impose administrative sanctions on {{COMPANY_SHORT}} and its management for breaching AML/CFT obligations.');
  note('[VERIFY: insert the current penalty schedule (fines and imprisonment, for individuals and entities, and the administrative sanctions available to MoE) from Federal Decree-Law No. (10) of 2025 and the Executive Regulations.]');
  h2('2.7 Protection for good-faith reporting');
  p('Persons who report suspicion in good faith to the FIU, or provide information required by law, are protected from administrative, civil and criminal liability even if a duty of confidentiality is breached, unless the report was made in bad faith.');
  h2('2.8 Freezing, seizure and confiscation');
  p('The competent authorities may freeze or seize funds suspected to be linked to ML, TF or PF, and the court may order confiscation of the proceeds, instruments and equivalent value, without prejudice to the rights of third parties acting in good faith. {{COMPANY_SHORT}} must comply immediately with any freezing or seizure order and must not alert the owner.');

  // ── 3. Governance ──────────────────────────────────────────────────────
  h1('3. GOVERNANCE AND RESPONSIBILITIES');
  h2('3.1 Commitment');
  p('{{COMPANY_SHORT}} maintains effective AML/CFT/CPF policies, procedures, systems and controls proportionate to the nature, size and complexity of its business. {{SENIOR_MANAGEMENT}} is committed to a culture of compliance in which commercial considerations never override these controls.');
  h2('3.2 Responsibilities of Senior Management');
  p('{{SENIOR_MANAGEMENT}} is responsible, with due skill, care and diligence, for:');
  bullets([
    'complying with the AML/CFT/CPF laws, MoE requirements and this Manual, and adopting a risk-based approach;',
    'approving this Manual and the business risk assessment, and reviewing them at least annually and on any material change to the business;',
    'appointing a suitably qualified MLRO and deputy, and giving them the authority, resources, independence and access to information they need;',
    'receiving and acting on the MLRO\'s reports and the findings of the independent review, and recording the action taken;',
    'approving high-risk customers, all PEPs and other matters reserved to it in this Manual;',
    'ensuring staff are trained and that these obligations are met in practice.',
  ]);
  h2('3.3 Money Laundering Reporting Officer (MLRO)');
  h3('3.3.1 Appointment');
  p('{{COMPANY_SHORT}} has appointed an MLRO and a deputy MLRO who acts in the MLRO\'s absence. The MLRO is {{MLRO_APPOINTMENT}}. The MLRO must be resident in the UAE, hold sufficient and up-to-date qualifications and experience for the role, and be approved by MoE where required [VERIFY: MoE approval or notification requirements for MLRO appointment].');
  table(['Function', 'Name', 'Contact'], [
    ['MLRO', '{{MLRO_NAME}}', 'Email: {{MLRO_EMAIL}}\nPhone: {{MLRO_PHONE}}'],
    ['Deputy MLRO', '{{DEPUTY_MLRO_NAME}}', 'Email: {{DEPUTY_MLRO_EMAIL}}\nPhone: {{DEPUTY_MLRO_PHONE}}'],
  ], [2000, 3400, 4000]);
  p('The MLRO and deputy must use an official company or professional email address, not a personal one.');
  p('If the MLRO is an external appointee, the appointment is recorded in a written agreement that gives the MLRO the same authority, independence and access as an employee MLRO; the engagement is documented as outsourcing under section 5.11; and the appointee must not also carry out the independent review in section 3.5.');
  h3('3.3.2 Independence and access');
  p('The MLRO has direct access to {{SENIOR_MANAGEMENT}}; timely and unrestricted access to all customer, transaction and supplier information; sufficient resources, including trained staff if needed; and the independence to act on their own authority. The decision whether to file an STR/SAR is the MLRO\'s alone and is not subject to anyone\'s consent or approval. If the MLRO resigns or is absent, {{SENIOR_MANAGEMENT}} must immediately appoint a replacement or arrange cover.');
  h3('3.3.3 Responsibilities');
  p('The MLRO is responsible for implementing this Manual and for day-to-day oversight of compliance. The MLRO:');
  bullets([
    'operates the AML/CFT/CPF controls, including customer risk ratings, onboarding approvals and ongoing reviews;',
    'receives and investigates Internal SARs, decides whether to report, and files STRs/SARs and other goAML reports (DPMSR, FFR, PNMR) on time;',
    'is the point of contact for the FIU, MoE, EOCN and other competent authorities, and answers their requests promptly;',
    'runs sanctions screening and the TFS procedure (section 6);',
    'monitors changes in law, circulars and guidance and updates this Manual (section 12);',
    'runs the training programme (section 11) and maintains the records required by section 10;',
    'maintains the register of high-risk jurisdictions (Appendix C).',
  ]);
  p('The full terms of reference are in Appendix A.');
  h3('3.3.4 Annual MLRO report');
  p('Within two months of the end of each financial year the MLRO reports in writing to {{SENIOR_MANAGEMENT}} on: reviews carried out; compliance with the law and with MoE requirements; the adequacy of policies, systems and controls; guidance and findings received and how they were applied; Internal SARs and external reports filed, with decisions and reasons; TFS alerts, screening results and any freezes; training delivered; and any other relevant matter. {{SENIOR_MANAGEMENT}} assesses the report, acts on any deficiency and records its decisions in writing. A full copy is provided to MoE on request.');
  h2('3.4 Annual AML return');
  p('{{COMPANY_SHORT}} completes and submits any periodic AML/CFT return or compliance report required by MoE by the deadline set. {{SENIOR_MANAGEMENT}} reviews it before submission and confirms that the information is correct to the best of its knowledge. [VERIFY: current MoE periodic return or self-assessment requirements and deadlines.]');
  h2('3.5 Independent review');
  p('At least annually, an independent function reviews the effectiveness of {{COMPANY_SHORT}}\'s AML/CFT/CPF framework and its compliance with this Manual and the law. The reviewer may be an internal function independent of the MLRO\'s work, or an external auditor or compliance professional. The review covers at least: sample testing of customer due diligence files; the quality of risk ratings; screening and TFS records; the timeliness and quality of Internal SARs and external reports; cash transactions and DPMSR filings; supplier due diligence; and training records. The report is given to {{SENIOR_MANAGEMENT}}, who records the action taken.');
  h2('3.6 Staff screening');
  p('Before hiring, {{COMPANY_SHORT}} verifies the identity and references of each candidate and checks their background, with a level of checking proportionate to the role. Employees, including the MLRO, are screened against sanctions lists at hiring and periodically.');
  h2('3.7 Training');
  p('Training is described in section 11.');
  h2('3.8 Notification to MoE');
  p('{{COMPANY_SHORT}} informs MoE in writing, promptly and in any case within the period MoE requires, if it:');
  bullets([
    'becomes aware of, or has reasonable grounds to suspect, money laundering, terrorist or proliferation financing or a sanctions breach in or through its business, or by another person it deals with;',
    'receives a request for information from a regulator or authority in another jurisdiction on AML or sanctions matters;',
    'becomes aware of a significant breach of AML/CFT requirements or a matter that could harm its reputation;',
    'changes its MLRO or deputy, its ownership or control, or its business activity materially;',
    'is prevented by the law of another jurisdiction from complying with UAE AML/CFT requirements.',
  ]);
  p('This does not apply to information protected by legal privilege or already in the public domain. [VERIFY: the exact notification triggers and channels required by MoE for DPMS.]');
  h2('3.9 Cooperation with the authorities');
  p('{{COMPANY_SHORT}} deals with MoE, the FIU, EOCN and law enforcement in an open and cooperative way, answers requests promptly and in the language required, and provides records and access to premises, systems and staff when lawfully requested.');
};
