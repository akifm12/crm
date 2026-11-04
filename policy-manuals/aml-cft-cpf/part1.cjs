// Front matter and sections 1-3
const { Paragraph, TextRun, AlignmentType, PageBreak, TableOfContents } = require('docx');

module.exports = (L) => {
  const { add, runs, h1, h2, h3, p, bullets, table, note, NAVY } = L;

  // ── Template guide (delete before issue) ───────────────────────────────
  add(new Paragraph({ spacing: { after: 160 }, children: [new TextRun({ text: 'TEMPLATE GUIDE: delete this page before issue', bold: true, size: 28, color: NAVY })] }));
  p('This is the master template of the AML / CFT / CPF and Sanctions Policy Manual for dealers in precious metals (gold / bullion). It is written for a UAE mainland company supervised by the Ministry of Economy and Tourism (MoET). Replace every highlighted placeholder with the company\'s own details. Nothing else in the text is company-specific.');
  table(['Placeholder', 'Replace with', 'Example'], [
    ['{{COMPANY_NAME}}', 'Full legal name, as on the trade license', 'Example Gold Trading LLC'],
    ['{{COMPANY_SHORT}}', 'Short name used throughout the text', 'Example Gold'],
    ['{{TRADE_LICENSE_NO}}', 'Trade license number', '123456'],
    ['{{LICENSING_AUTHORITY}}', 'Authority that issued the trade license', 'Dubai Department of Economy and Tourism'],
    ['{{GOAML_ENTITY_ID}}', 'goAML reporting entity registration number', 'Not applicable'],
    ['{{SENIOR_MANAGEMENT}}', 'Who exercises senior management / board authority', 'the Managing Director'],
    ['{{MLRO_NAME}} / {{MLRO_EMAIL}} / {{MLRO_PHONE}}', 'Money Laundering Reporting Officer', 'Not applicable'],
    ['{{DEPUTY_MLRO_NAME}} / {{DEPUTY_MLRO_EMAIL}} / {{DEPUTY_MLRO_PHONE}}', 'Deputy MLRO (covers absence)', 'Not applicable'],
    ['{{MLRO_APPOINTMENT}}', 'Whether the MLRO is an employee or an external appointee', 'an external appointee under a written service agreement'],
    ['{{SCREENING_TOOL}}', 'Sanctions / PEP / adverse media screening tool in use', 'Not applicable'],
    ['{{RETENTION_YEARS}}', 'Record retention period in years (not less than five)', '5'],
    ['{{VERSION}} / {{ISSUE_DATE}} / {{APPROVER}}', 'Document control', '1.0 / 1 January 2027 / Managing Director'],
  ], [3300, 3900, 2200]);
  p('**Cyan [VERIFY: …] tags** mark statements that depend on the current text of a law, circular or authority guidance (deadlines, thresholds, instrument numbers, report types). They are to be checked against the latest MoET, FIU and EOCN publications and removed once confirmed. The regulatory change log in section 1.6 records each update.');
  p('End of template guide.');

  // ── Cover ──────────────────────────────────────────────────────────────
  add(new Paragraph({ children: [new PageBreak()] }));
  for (let i = 0; i < 5; i++) add(new Paragraph({ children: [] }));
  add(new Paragraph({ alignment: AlignmentType.LEFT, spacing: { after: 120 }, children: [new TextRun({ text: 'ANTI-MONEY LAUNDERING, COUNTER-TERRORIST FINANCING, COUNTER-PROLIFERATION FINANCING AND SANCTIONS', bold: true, size: 40, color: NAVY })] }));
  add(new Paragraph({ spacing: { after: 360 }, children: [new TextRun({ text: 'Policy and Procedures Manual', size: 32, color: NAVY })] }));
  add(new Paragraph({ spacing: { after: 120 }, children: runs('{{COMPANY_NAME}}', { size: 32, bold: true }) }));
  add(new Paragraph({ spacing: { after: 60 }, children: runs('Dealer in Precious Metals and Stones, Trade License No. {{TRADE_LICENSE_NO}}', { size: 22 }) }));
  add(new Paragraph({ spacing: { after: 360 }, children: runs('Licensing authority: {{LICENSING_AUTHORITY}}   |   AML supervisor: Ministry of Economy and Tourism', { size: 22 }) }));
  add(new Paragraph({ spacing: { after: 60 }, children: runs('Version {{VERSION}}', { size: 24 }) }));
  add(new Paragraph({ spacing: { after: 600 }, children: runs('Issued {{ISSUE_DATE}}', { size: 24 }) }));
  add(new Paragraph({ children: runs('CONFIDENTIAL: for internal use by {{COMPANY_SHORT}} staff and its regulators.', { size: 20, italics: true }) }));

  add(new Paragraph({ children: [new PageBreak()] }));
  add(new Paragraph({ spacing: { after: 160 }, children: [new TextRun({ text: 'TABLE OF CONTENTS', bold: true, size: 30, color: NAVY })] }));
  add(new TableOfContents('Contents', { hyperlink: true, headingStyleRange: '1-2' }));

  // ── 1. Introduction ────────────────────────────────────────────────────
  h1('1. INTRODUCTION');
  h2('1.1 Purpose and scope');
  p('This Manual sets out the policies and procedures of {{COMPANY_NAME}} ("{{COMPANY_SHORT}}") to prevent money laundering (ML), the financing of terrorism (TF), the financing of illegal organizations and the financing of the proliferation of weapons of mass destruction (PF), and to comply with targeted financial sanctions (TFS). It applies to all of {{COMPANY_SHORT}}\'s activities as a dealer in precious metals and stones (DPMS), including buying, selling, refining, trading, import and export of gold and other precious metals.');
  p('It applies to the owners, {{SENIOR_MANAGEMENT}}, all employees (permanent, temporary and contracted), and anyone acting on behalf of {{COMPANY_SHORT}}. It was approved by {{SENIOR_MANAGEMENT}} and takes effect on the issue date shown on the cover.');
  h2('1.2 Staff obligations and consequences');
  p('Every member of staff must read this Manual, sign the declaration in Appendix D and follow it. In particular, every member of staff must:');
  bullets([
    'report any knowledge or suspicion of ML, TF or PF to the MLRO immediately, using the Internal SAR form (Appendix E);',
    'never tell a customer or any third party that a report has been made or that an inquiry is under way ("tipping off");',
    'never help anyone to launder money, finance terrorism or evade sanctions, or "aid or abet" such activity;',
    'complete customer due diligence and sanctions screening before any transaction.',
  ]);
  p('Failure to comply may lead to disciplinary action up to dismissal, and may be a criminal offense for the individual and an administrative or criminal matter for {{COMPANY_SHORT}}. {{COMPANY_SHORT}} may report breaches to the Ministry of Economy and Tourism (MoET) or law enforcement.');

  h2('1.3 Legal and regulatory framework');
  p('This Manual is based on the following official instruments. The MLRO keeps the list current (section 12) and records every change in the log in section 1.6.');
  h3('Legislation');
  bullets([
    'Federal Decree-Law No. (10) of 2025 on Anti-Money Laundering, Combating the Financing of Terrorism and Proliferation Financing (issued 30 September 2025), the primary law.',
    'Cabinet Resolution No. (134) of 2025, the Executive Regulations of that law (issued 29 October 2025; in force 30 days after publication; it repealed Cabinet Resolution No. (10) of 2019).',
    'Cabinet Decision No. (74) of 2020 on the Terrorism Lists Regulation and the implementation of United Nations Security Council (UNSC) resolutions (targeted financial sanctions).',
    'Cabinet Decision No. (109) of 2023 on beneficial owner procedures, and Cabinet Decision No. (132) of 2023 on the penalties for breaching it.',
    'Cabinet Resolution No. (71) of 2024 on violations and administrative penalties for entities supervised by the Ministry of Justice and MoET [VERIFY: whether this list is still the operative fine schedule alongside Article (17) of Federal Decree-Law No. (10) of 2025].',
    'Ministerial Decree No. (68) of 2024 on adherence to the due diligence regulations for responsible sourcing of gold, and Federal Law No. (11) of 2015 on Monitoring Trade in Precious Metals and its Stamping.',
    'Ministerial Decision No. (253) of 2025 on the controls and conditions for registration of DNFBPs by licensing authorities (11 December 2025) [VERIFY: content applying to the company; text not yet reviewed].',
    'Federal Decree-Law No. (31) of 2021 (Crimes and Penalties), No. (38) of 2022 (Criminal Procedure) and No. (34) of 2021 (cybercrimes), and the federal law on combating terrorism offenses [VERIFY: current instrument].',
  ]);
  h3('MoET circulars and guidance (DPMS)');
  bullets([
    'Circular No. 08/AML/2021 (2 June 2021): DPMSR reporting through goAML.',
    'Circular No. (2) of 2023: Data Disclosure Notice to be displayed to customers (section 7.8).',
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

  h2('1.4 Authorities and their roles');
  table(['Authority', 'Role for {{COMPANY_SHORT}}'], [
    ['Ministry of Economy and Tourism (MoET)', 'AML/CFT/CPF supervisor of dealers in precious metals and stones. Approves the Compliance Officer/MLRO, inspects, issues circulars and guidance, and can impose administrative sanctions (Article 17 of Federal Decree-Law No. (10) of 2025). Must be informed of the matters in section 3.8.'],
    ['{{LICENSING_AUTHORITY}}', 'Issues and renews the trade license. Registration and license conditions for DNFBPs depend on compliance.'],
    ['UAE Financial Intelligence Unit (FIU)', 'Receives reports through the goAML platform: STR/SAR, DPMSR, HRC/HRCA and follow-up reports (AIF/AIFT/RFI). {{COMPANY_SHORT}} is registered on goAML under entity ID {{GOAML_ENTITY_ID}}.'],
    ['Executive Office for Control and Non-Proliferation (EOCN)', 'Administers targeted financial sanctions: publishes the UN and UAE Local Terrorist Lists, operates the Notification Alert System, and receives CNMR and PNMR reports (copied to MoET) through goAML.'],
    ['National Committee for AML/CFT/CPF', 'Issues the UAE list of high-risk countries and countries under increased monitoring (Appendix C).'],
  ], [2900, 6500]);

  h2('1.5 Definitions');
  table(['Term', 'Meaning'], [
    ['Customer', 'A person with whom {{COMPANY_SHORT}} has or is establishing a business relationship, or for whom it carries out an occasional transaction (including a person who sells gold to {{COMPANY_SHORT}}).'],
    ['Business relationship', 'An ongoing commercial relationship between the customer and {{COMPANY_SHORT}}.'],
    ['Occasional transaction', 'A transaction with a customer with whom there is no established business relationship.'],
    ['Beneficial owner (UBO)', 'The natural person who ultimately owns or controls the customer, or on whose behalf a transaction is carried out. For a company this includes anyone holding, directly or indirectly, 25% or more of the shares or voting rights, or exercising control by other means; where none is found, the senior managing official.'],
    ['Politically exposed person (PEP)', 'A natural person who is or has been entrusted with a prominent public function in the UAE or elsewhere (head of state or government, senior politician, senior government, judicial or military official, ambassador, senior executive of a state-owned company, important political party official, or a senior official of an international organization), and their family members (spouse, children, children\'s spouses, parents) and close associates.'],
    ['Source of funds / source of wealth', 'Source of funds: the origin of the money used in a specific transaction. Source of wealth: how the customer\'s overall wealth was acquired.'],
    ['MLRO / Compliance Officer', 'The Money Laundering Reporting Officer appointed under section 3.3. The Executive Regulations call this role the Compliance Officer, appointed at management level.'],
    ['Senior Management', '{{SENIOR_MANAGEMENT}}.'],
    ['goAML / STR / SAR', 'goAML is the FIU\'s online reporting platform. STR/SAR: Suspicious Transaction Report / Suspicious Activity Report.'],
    ['DPMSR', 'Dealers in Precious Metals and Stones Report: the goAML report filed for the transactions described in section 7.1.'],
    ['CNMR / PNMR', 'Confirmed Name Match Report (formerly the Funds Freeze Report, FFR) and Partial Name Match Report: goAML reports to EOCN under the TFS regime (section 6).'],
    ['HRC / HRCA', 'High-Risk Country Transaction Report / High-Risk Country Activity Report: goAML reports to the FIU for transactions or activity related to high-risk countries (section 8.4).'],
    ['Covered precious metals and stones', 'Precious metals at minimum purity (per 1,000 parts): gold 500, silver 800, platinum 850, palladium 500; objects whose value is at least 50% precious metal or stones; rough diamonds of any weight; loose polished diamonds of 0.3 ct or more (mounted 0.5 ct); loose emeralds, rubies and sapphires of 1 ct or more (mounted 2 ct); loose pearls of 3 mm or more (strung or mounted 10 mm). Anything else is handled on a risk basis (MoET Supplemental Guidance).'],
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
    ['4 Nov 2026', 'Federal Decree-Law 10/2025; Cabinet Resolution 134/2025 (MoET, UAE FIU)', 'New AML/CFT/CPF law and Executive Regulations; PF added; retention triggers widened; Compliance Officer approval; penalty schedule.', '1.3, 2.6, 3.3, 10', ''],
    ['4 Nov 2026', 'EOCN TFS Guidance (amended March 2026); MoET Circulars 3/2025 and 7/2025', 'FFR renamed CNMR; screening scope and triggers; partial-match handling; majority ownership; foreign-sanctions rule.', '6', ''],
    ['4 Nov 2026', 'MoET Supplemental Guidance for DPMS and DNFBP Guidelines (March 2026); Circulars 6/2025, 1/2026, 2/2023', 'DPMSR scope; linked-transaction aggregation; SDD; high-risk country measures and HRC/HRCA; data disclosure notice; third-party Compliance Officer.', '3.3, 4.7, 5, 7, 8.4, App. C', ''],
    ['4 Nov 2026', 'Ministerial Decree 68/2024', 'Dealers apply responsible-sourcing steps 1 to 3; audit and report apply to refiners and recyclers.', '7.4', ''],
    ['4 Nov 2026', 'FATF lists of 30 October 2026; National Committee (high-risk jurisdictions); MoET Responsible Sourcing page and Due Diligence Regulations v1; DNFBP Guidelines section 8', 'Dated register of FATF lists; CAHRA assessment per supply chain; risk assessment method, certification and update triggers.', '4.1 to 4.3, 7.5, App. C, F', ''],
    ['4 Nov 2026', 'EU indicative CAHRA list under Regulation (EU) 2017/821 (cahraslist.net, RAND Europe for the European Commission, September 2026 update)', 'CAHRA register populated with the 31 listed countries and their regional coverage.', '7.5, App. C, F', ''],
    ['', '', '', '', ''],
  ], [1200, 2300, 3000, 1500, 1400]);

  // ── 2. ML / TF / PF ────────────────────────────────────────────────────
  h1('2. MONEY LAUNDERING, TERRORIST FINANCING AND PROLIFERATION FINANCING');
  h2('2.1 Money laundering');
  p('A person commits money laundering if, knowing that funds are the proceeds of a felony or misdemeanor, they intentionally: transfer, move or conduct a transaction with the proceeds to conceal or disguise their illegal source; conceal or disguise the true nature, source, location, movement, ownership or rights over the proceeds; acquire, possess or use the proceeds on receipt; or help the offender of the predicate offense to escape punishment.');
  p('Money laundering is an independent crime: the offender can be punished for it as well as for the predicate offense, and the illegal source need not be proven first. An attempt is also an offense. A company can be liable as well as the individuals involved.');
  h2('2.2 Stages of money laundering');
  table(['Stage', 'What happens', 'Examples in the gold trade'], [
    ['Placement', 'Illegal cash enters the system.', 'Buying gold bars or jewelry with cash; many small cash purchases below reporting thresholds; paying cash for scrap gold.'],
    ['Layering', 'Funds are moved and converted to hide their origin.', 'Rapid buy-and-sell of the same gold; payments through third parties or several accounts; melting and recasting; moving gold across borders; over- or under-invoicing.'],
    ['Integration', 'Funds re-enter the economy as apparently legitimate wealth.', 'Selling the gold to a legitimate buyer and receiving a clean bank payment; using gold sales to justify wealth.'],
  ], [1500, 3000, 4900]);
  h2('2.3 Terrorist financing and financing of illegal organizations');
  p('It is an offense to provide, collect, prepare or obtain funds, or to facilitate others doing so, knowing or intending that they will be used in whole or in part for a terrorist act, a terrorist organization or person, or an illegal organization or its members, and to carry out any ML act with funds known to belong to or be intended for them. Funds used for terrorist financing may be legitimate in origin and small in amount, so the absence of a criminal source does not remove the risk.');
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
  p('Under Federal Decree-Law No. (10) of 2025 the main penalties include:');
  table(['Offense', 'Penalty'], [
    ['Money laundering (Art. 26)', 'Imprisonment of 1 to 10 years and a fine of AED 100,000 to 5,000,000, or the value of the criminal property if greater. Aggravated cases (abuse of position, through a non-profit, organized group, certain predicate offenses, repeat offense): temporary imprisonment and AED 1,000,000 to 10,000,000, or twice the value if greater.'],
    ['Financing of terrorism', 'Life or temporary imprisonment of at least 10 years and AED 1,000,000 to 10,000,000, or twice the value if greater.'],
    ['Proliferation financing', 'Temporary imprisonment and AED 1,000,000 to 10,000,000, or twice the value if greater.'],
    ['Attempt', 'Same penalty as the completed offense.'],
    ['Legal persons (Art. 27)', 'AED 5,000,000 to 100,000,000, or the value of the property if greater, for ML, TF or PF by its representatives, directors or agents; dissolution and closure for TF/PF and possibly for ML. AED 200,000 to 10,000,000 for the other offenses below. The person actually managing the company may also be imprisoned and fined.'],
    ['Failure to report, deliberately or through gross negligence (Art. 28)', 'Imprisonment and AED 100,000 to 1,000,000, or either.'],
    ['Tipping off, or breaching a freezing or seizure order (Art. 29)', 'Imprisonment and a fine of at least AED 50,000, or either.'],
    ['Dealing in funds of evidently illegitimate source or concealed beneficial owner (Art. 30)', 'At least 3 months and at least AED 50,000, or either.'],
    ['Violating EOCN or other competent-authority instructions on targeted financial sanctions (Art. 33)', 'Imprisonment and a fine of at least AED 20,000, or either.'],
    ['False or misleading beneficial-owner information (Art. 35)', 'Imprisonment and a fine of at least AED 20,000, or either.'],
    ['Administrative sanctions by MoET (Art. 17)', 'Warning; fine of AED 10,000 to 5,000,000 per violation; ban from the sector; restriction or suspension of directors and managers; suspension or restriction of the activity; revocation of the license; periodic remediation reports.'],
  ], [3300, 6100]);
  p('Foreign nationals convicted of these crimes may be deported. Confiscation of the criminal property, or its equivalent value, is mandatory on conviction. [VERIFY: the operative MoET fine schedule per violation type (Cabinet Resolution No. (71) of 2024 predates the new law).]');
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
    'complying with the AML/CFT/CPF laws, MoET requirements and this Manual, and adopting a risk-based approach;',
    'approving this Manual and the business risk assessment, and reviewing them at least annually and on any material change to the business;',
    'appointing a suitably qualified MLRO and deputy, and giving them the authority, resources, independence and access to information they need;',
    'receiving and acting on the MLRO\'s reports and the findings of the independent review, and recording the action taken;',
    'approving high-risk customers, all PEPs and other matters reserved to it in this Manual;',
    'ensuring staff are trained and that these obligations are met in practice.',
  ]);
  h2('3.3 Money Laundering Reporting Officer (MLRO)');
  h3('3.3.1 Appointment');
  p('{{COMPANY_SHORT}} has appointed an MLRO (the Compliance Officer, at management level) and a deputy MLRO who acts in the MLRO\'s absence. The MLRO is {{MLRO_APPOINTMENT}}. The MLRO and deputy must be resident in the UAE and must have passed a documented fit-and-proper evaluation (qualifications, AML/CFT/CPF experience, reputation, no criminal or regulatory record). **The appointment of the MLRO requires the prior written approval of MoET**, and MoET must be notified of any change in the role or reporting lines. The MLRO must be registered as a user on goAML for {{COMPANY_SHORT}} and keep the registration active. Records of the fit-and-proper assessment are kept.');
  table(['Function', 'Name', 'Contact'], [
    ['MLRO', '{{MLRO_NAME}}', 'Email: {{MLRO_EMAIL}}\nPhone: {{MLRO_PHONE}}'],
    ['Deputy MLRO', '{{DEPUTY_MLRO_NAME}}', 'Email: {{DEPUTY_MLRO_EMAIL}}\nPhone: {{DEPUTY_MLRO_PHONE}}'],
  ], [2000, 3400, 4000]);
  p('The MLRO and deputy must use an official company or professional email address, not a personal one.');
  p('If the MLRO is an external appointee, MoET permits this where {{COMPANY_SHORT}} cannot appoint a suitably qualified independent officer internally, on these conditions: the person has the qualifications, experience and understanding of {{COMPANY_SHORT}}\'s business and risks; they are independent of operational and revenue-generating roles; they have unrestricted access to all records, systems and staff they need; the appointment is recorded in a written agreement and documented as outsourcing under section 5.11; and the MLRO remains fully accountable for validating any external service or tool used. The appointee must not also carry out the independent review in section 3.5.');
  h3('3.3.2 Independence and access');
  p('The MLRO has direct access to {{SENIOR_MANAGEMENT}}; timely and unrestricted access to all customer, transaction and supplier information; sufficient resources, including trained staff if needed; and the independence to act on their own authority. The decision whether to file an STR/SAR is the MLRO\'s alone and is not subject to anyone\'s consent or approval. If the MLRO resigns or is absent, {{SENIOR_MANAGEMENT}} must immediately appoint a replacement or arrange cover.');
  h3('3.3.3 Responsibilities');
  p('The MLRO is responsible for implementing this Manual and for day-to-day oversight of compliance. The MLRO:');
  bullets([
    'operates the AML/CFT/CPF controls, including customer risk ratings, onboarding approvals and ongoing reviews;',
    'receives and investigates Internal SARs, decides whether to report, and files STRs/SARs and other goAML reports (DPMSR, HRC/HRCA, CNMR, PNMR) on time;',
    'is the point of contact for the FIU, MoET, EOCN and other competent authorities, and answers their requests promptly;',
    'runs sanctions screening and the TFS procedure (section 6);',
    'monitors changes in law, circulars and guidance and updates this Manual (section 12);',
    'runs the training program (section 11) and maintains the records required by section 10;',
    'maintains the register of high-risk jurisdictions (Appendix C).',
  ]);
  p('The full terms of reference are in Appendix A.');
  h3('3.3.4 Annual MLRO report');
  p('Within two months of the end of each financial year the MLRO reports in writing to {{SENIOR_MANAGEMENT}} on: reviews carried out; compliance with the law and with MoET requirements; the adequacy of policies, systems and controls; guidance and findings received and how they were applied; Internal SARs and external reports filed, with decisions and reasons; TFS alerts, screening results and any freezes; training delivered; and any other relevant matter. {{SENIOR_MANAGEMENT}} assesses the report, acts on any deficiency and records its decisions in writing. A full copy, with Senior Management\'s observations and decisions, is provided to MoET on request.');
  h2('3.4 Annual AML return');
  p('{{COMPANY_SHORT}} completes and submits any periodic AML/CFT return or compliance report required by MoET by the deadline set. {{SENIOR_MANAGEMENT}} reviews it before submission and confirms that the information is correct to the best of its knowledge. [VERIFY: current MoET periodic return or self-assessment requirements and deadlines.]');
  h2('3.5 Independent review');
  p('At least annually, an independent function reviews the effectiveness of {{COMPANY_SHORT}}\'s AML/CFT/CPF framework and its compliance with this Manual and the law. The reviewer may be an internal function independent of the MLRO\'s work, or an external auditor or compliance professional. The review covers at least: sample testing of customer due diligence files; the risk assessment methodology and the quality of risk ratings; screening and TFS records; the timeliness and quality of Internal SARs and external reports; cash transactions and DPMSR filings; supplier due diligence; and training records. The report is given to {{SENIOR_MANAGEMENT}}, who records the action taken.');
  h2('3.6 Staff screening');
  p('Before hiring, {{COMPANY_SHORT}} verifies the identity and references of each candidate and checks their background, with a level of checking proportionate to the role. Employees, including the MLRO, are screened against sanctions lists at hiring and periodically.');
  h2('3.7 Training');
  p('Training is described in section 11.');
  h2('3.8 Notification to MoET');
  p('{{COMPANY_SHORT}} informs MoET in writing, promptly and in any case within the period MoET requires, if it:');
  bullets([
    'becomes aware of, or has reasonable grounds to suspect, money laundering, terrorist or proliferation financing or a sanctions breach in or through its business, or by another person it deals with;',
    'receives a request for information from a regulator or authority in another jurisdiction on AML or sanctions matters;',
    'becomes aware of a significant breach of AML/CFT requirements or a matter that could harm its reputation;',
    'changes its MLRO or deputy, its ownership or control, or its business activity materially;',
    'is prevented by the law of another jurisdiction from complying with UAE AML/CFT requirements.',
  ]);
  p('This does not apply to information protected by legal privilege or already in the public domain. [VERIFY: the exact notification triggers and channels required by MoET for DPMS.]');
  h2('3.9 Cooperation with the authorities');
  p('{{COMPANY_SHORT}} deals with MoET, the FIU, EOCN and law enforcement in an open and cooperative way, answers requests promptly and in the language required, and provides records and access to premises, systems and staff when lawfully requested.');
};
