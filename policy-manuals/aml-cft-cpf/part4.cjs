// Appendices A-H
module.exports = (L) => {
  const { h1, h2, h3, p, bullets, table, note } = L;

  // ── A. MLRO terms of reference ────────────────────────────────────────
  h1('APPENDIX A — TERMS OF REFERENCE: MLRO');
  table(null, [
    ['Name', '{{MLRO_NAME}}'],
    ['Contact', '{{MLRO_EMAIL}}   |   {{MLRO_PHONE}}'],
    ['Appointment', '{{MLRO_APPOINTMENT}}'],
    ['Deputy', '{{DEPUTY_MLRO_NAME}}   |   {{DEPUTY_MLRO_EMAIL}}   |   {{DEPUTY_MLRO_PHONE}}'],
    ['Reports to', '{{SENIOR_MANAGEMENT}}, directly'],
    ['goAML', 'Registered compliance officer on goAML for {{COMPANY_NAME}}, entity ID {{GOAML_ENTITY_ID}}'],
  ], [2200, 7200]);
  h3('Authority and independence');
  bullets([
    'direct access to {{SENIOR_MANAGEMENT}};',
    'enough seniority and independence to act on their own authority;',
    'sufficient resources, including trained staff where needed;',
    'timely and unrestricted access to all customer, transaction, supplier and system information;',
    'authority to hold or refuse any transaction, to freeze assets under section 6 and to refuse any relationship;',
    'prior written approval of MoET for the appointment, and fit-and-proper records (section 3.3.1);',
    'the decision to file an STR/SAR is the MLRO\'s alone, and is not subject to anyone\'s consent.',
  ]);
  h3('Responsibilities');
  bullets([
    'day-to-day operation of the AML/CFT/CPF policies, procedures, systems and controls;',
    'customer risk ratings, onboarding approvals, reviews and the customer register;',
    'receiving and investigating Internal SARs and deciding on external reports;',
    'filing STRs/SARs, DPMSRs, HRC/HRCA, CNMRs, PNMRs and other goAML reports on time, and notifying MoET where required;',
    'TFS: screening, EOCN notifications, freezing and reporting (section 6);',
    'supplier due diligence and responsible sourcing oversight (section 7.4);',
    'liaison with the FIU, MoET, EOCN and other authorities, and prompt response to their requests;',
    'monitoring regulatory change and keeping this Manual and the regulatory change log current;',
    'training programme and records (section 11);',
    'the annual MLRO report (section 3.3.4) and the business risk assessment (section 4.2);',
    'maintaining the high-risk jurisdictions register (Appendix C).',
  ]);
  h3('Reporting');
  p('The MLRO reports in writing to {{SENIOR_MANAGEMENT}} at least annually (section 3.3.4), and immediately on any serious matter, including any freeze, any STR/SAR of significance, any regulatory request or any significant breach.');

  // ── B. Red flags ──────────────────────────────────────────────────────
  h1('APPENDIX B — RED FLAG INDICATORS: GOLD AND PRECIOUS METALS');
  p('The presence of a red flag does not mean that a crime has occurred. Red flags are assessed together and in context. If a concern cannot be resolved, it is escalated to the MLRO on an Internal SAR for investigation, EDD, and a decision whether to file an STR/SAR through goAML.');
  h3('Customer behaviour and profile');
  bullets([
    'unwilling or unable to provide satisfactory KYC, beneficial ownership or business information; refuses to identify or to explain the source of funds or wealth;',
    'stated business or financial profile is inconsistent with the volume or value of gold dealt in;',
    'unusual concern about reporting requirements, KYC or record keeping; tries to persuade staff to bypass controls; cancels or changes the transaction once asked about KYC, source of funds or origin of the gold;',
    'acts for an undisclosed principal or seems to be directed by someone else;',
    'unusually complex, opaque or frequently changed ownership or control; several unrelated entities controlled by the same person without business reason;',
    'newly formed business with no track record dealing in large volumes; sudden unexplained increase in frequency or value.',
  ]);
  h3('Payments and transactions');
  bullets([
    'large or frequent cash transactions out of line with the customer\'s profile; cash just below the reporting threshold; several transactions apparently structured to avoid it;',
    'payments from or to unrelated third parties, multiple unconnected accounts or persons; request to pay a different party or account from the one that paid;',
    'payment in unusual instruments, or overpayment followed by a request for a refund;',
    'rapid buying and selling of the same gold, or transactions with no obvious economic purpose or profit motive;',
    'prices significantly above or below market without reason.',
  ]);
  h3('Gold, origin and documents');
  bullets([
    'gold offered without credible documents of source, origin, ownership or purchase;',
    'large quantities presented as scrap or personal gold with no explanation or proof;',
    'marks defaced, removed or inconsistent with the refiner or the documents; melted shortly after receipt;',
    'invoices, customs documents, assay reports, certificates or shipping papers that are incomplete, altered, inconsistent in quantity, purity, value, parties or destination, or that look unreliable;',
    'unnecessary intermediaries or supply-chain complexity that hides the original source;',
    'gold said to originate from a conflict-affected or high-risk area, or from a country that does not produce it in such quantities.',
  ]);
  h3('Geography, sanctions and proliferation');
  bullets([
    'customer, supplier, beneficial owner or counterparty linked to a high-risk or sanctioned jurisdiction;',
    'a party that is, or may be linked to, a sanctioned person or entity or a PEP;',
    'unusual countries, routes, vessels, ports, freight forwarders or payment arrangements that are not commercially justified;',
    'links to dual-use goods or to entities in countries of proliferation concern; end user or destination unclear;',
    'any other behaviour or transaction inconsistent with the customer\'s known profile or lacking an apparent legitimate economic purpose.',
  ]);

  // ── C. High-risk jurisdictions ───────────────────────────────────────
  h1('APPENDIX C — HIGH-RISK JURISDICTIONS');
  p('This Manual does not reproduce lists that change. The MLRO keeps the register below up to date from the following sources and checks them at least quarterly and after each FATF plenary and each National Committee resolution (the current resolution is No. (15) of 2025, notified by MoET Circular No. (1) of 2026):');
  bullets([
    '**FATF "Call for Action" list** ("blacklist"): measures as in section 4.7 (Enhanced Due Diligence on all relationships and transactions, no branches or subsidiaries, no third-party reliance, strengthened reporting, HRC/HRCA reports).',
    '**FATF "Increased Monitoring" list** ("greylist"): Enhanced Due Diligence proportionate to the risk, and HRC/HRCA reporting where the National Committee list requires it.',
    '**National Committee for AML/CFT/CPF** list of high-risk countries (namlcftc.gov.ae), and any MoET or FIU circular updating it.',
    'UN and UAE sanctions regimes, including the UN sanctions on Iran re-imposed under UNSCR 1737 (2006) (Circular 7 of 2025) and on the DPRK.',
    'Jurisdictions {{COMPANY_SHORT}} itself assesses as high risk (corruption, illicit gold, conflict, weak AML supervision), recorded with the reason.',
  ]);
  p('[VERIFY: the jurisdictions currently on each list, taken from the National Committee website and the FATF pages cited in Circular No. (1) of 2026.]');
  table(['Jurisdiction', 'Source list', 'Date added', 'Reason / measures', 'Last reviewed'], [
    ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''],
  ], [2000, 1900, 1400, 2800, 1300]);

  // ── D. Declaration ───────────────────────────────────────────────────
  h1('APPENDIX D — STAFF DECLARATION');
  p('I confirm that I have received a copy of the Anti-Money Laundering, Counter-Terrorist Financing, Counter-Proliferation Financing and Sanctions Policy and Procedures Manual of {{COMPANY_NAME}}, that I have read and understood it, that I am subject to it and that I agree to comply with it.');
  bullets([
    'I understand it is {{COMPANY_SHORT}}\'s policy to prevent its business being used for money laundering, terrorist financing or the financing of weapons of mass destruction, and that it is my duty to be vigilant.',
    'I understand it is a criminal offence to knowingly help any person with these activities, or to tell anyone that a transaction is being examined or that an authority is investigating.',
    'I understand that if I know or have reasonable grounds to suspect money laundering, terrorist financing or proliferation financing, I must report it immediately to the MLRO on the Internal SAR form, and that failing to do so may be grounds for disciplinary action and an offence under UAE law.',
    'I understand that I must not carry out a transaction before the customer has been identified, verified and screened.',
    'I agree that this declaration also covers any later written supplementary or replacement procedures given to me by the MLRO or {{SENIOR_MANAGEMENT}}.',
    'I agree that this declaration forms part of my contract of employment (or contract for services), and that a breach entitles {{COMPANY_SHORT}} to take disciplinary action, including termination with immediate effect.',
  ]);
  table(['Name', 'Function', 'Signature', 'Date'], [['', '', '', ''], ['', '', '', ''], ['', '', '', '']], [2600, 2400, 2600, 1800]);

  // ── E. Internal SAR ──────────────────────────────────────────────────
  h1('APPENDIX E — INTERNAL SUSPICIOUS ACTIVITY REPORT (INTERNAL SAR)');
  p('To be completed by any employee who has a suspicion, and handed to the MLRO immediately. Do not discuss the report with the customer or with anyone other than the MLRO.');
  table(null, [
    ['Date and time of report', ''],
    ['Reporting employee (name, function)', ''],
    ['Customer / counterparty full name', ''],
    ['Passport / Emirates ID / trade licence no.', ''],
    ['Nationality / country', ''],
    ['Address / contact details', ''],
    ['Transaction date(s), amount(s) and currency', ''],
    ['Gold involved (description, weight, purity, value)', ''],
    ['Payment method and payer/payee details', ''],
    ['Reason for suspicion (what you saw or were told, and why it is unusual)', '\n\n\n'],
    ['Supporting documents attached', ''],
    ['Signature of employee', ''],
  ], [3800, 5600]);
  h3('MLRO use only');
  table(null, [
    ['Date received / acknowledged', ''],
    ['Investigation steps and information reviewed', '\n\n'],
    ['Decision', '☐ Report to FIU (STR/SAR)     ☐ No report     ☐ Further information needed'],
    ['Reasons for the decision', '\n\n'],
    ['goAML reference / date filed; MoET notified (if required)', ''],
    ['Action on the relationship / transaction', ''],
    ['MLRO signature and date', ''],
  ], [3800, 5600]);

  // ── F. CDD checklist ─────────────────────────────────────────────────
  h1('APPENDIX F — CUSTOMER IDENTIFICATION: DOCUMENT CHECKLIST');
  table(['Customer type', 'Documents required', 'Done'], [
    ['Individual — UAE resident', 'Emirates ID (original, scanned both sides)\nPassport copy\nProof of address if not on Emirates ID\nSource-of-funds declaration (and evidence for Medium/High risk or cash ≥ threshold)\nSigned Customer Identification Form', '☐'],
    ['Individual — visitor / non-resident', 'Valid passport (original)\nEntry stamp or visa\nHome address and contact details\nSource-of-funds declaration (and evidence as above)\nSigned Customer Identification Form', '☐'],
    ['Company / business', 'Valid trade licence\nMemorandum and articles of association (and amendments)\nCertificate of incorporation / registration\nShareholder and director register; ownership structure chart to the beneficial owners\nVAT / tax registration where applicable\nBoard resolution or power of attorney for signatories\nID of each shareholder 25% or more (10% for High risk), each director and each authorised signatory\nRegistered address and place of business; lease or Ejari where relevant\nLatest financial statements or bank reference for Medium/High risk\nSigned Customer Identification Form', '☐'],
    ['Partnership, trust, foundation, association, NPO', 'Constituting document (deed, charter, bylaws)\nEvidence of appointment of trustees / managers / guardian\nIdentification of all persons listed in section 5.4\nPurpose and source of funds; MLRO and Senior Management approval', '☐'],
    ['Supplier / refiner / counterparty', 'As for a company, plus:\nGold origin and supply chain declaration\nRefiner / assay certification and responsible-sourcing evidence\nImport / export licences and customs documents where applicable\nSanctions and adverse media screening record', '☐'],
  ], [2300, 6400, 700]);
  h3('Customer profile (completed for every relationship)');
  table(null, [
    ['Nature of customer\'s business and how the gold will be used', ''],
    ['Expected monthly volume and value; usual payment method', ''],
    ['Source of funds (and wealth for High risk)', ''],
    ['Countries involved (customer, buyers/suppliers, destination, origin)', ''],
    ['Prepared by / date', ''],
  ], [4200, 5200]);

  // ── G. Customer risk assessment ──────────────────────────────────────
  h1('APPENDIX G — CUSTOMER RISK ASSESSMENT FORM');
  table(null, [
    ['Customer name', ''],
    ['Type (individual / company / other)', ''],
    ['Beneficial owner(s) identified', ''],
    ['Date of assessment; assessor', ''],
  ], [3200, 6200]);
  p('Score each factor 1 (Low), 2 (Medium) or 3 (High). Add the scores.');
  table(['Factor', 'Low (1)', 'Medium (2)', 'High (3)', 'Score'], [
    ['Customer type and structure', 'Individual, simple company, transparent ownership', 'Company with layered but traceable ownership', 'Complex or opaque structure, trust/foundation, nominees', ''],
    ['Nationality / residence / incorporation', 'UAE or low-risk country', 'Other country, no concerns', 'High-risk jurisdiction (Appendix C)', ''],
    ['Business and occupation', 'Salaried or regulated business', 'Trading or other business', 'Cash-intensive; precious metals trader; unclear business', ''],
    ['Gold origin / products', 'Refined, documented gold', 'Jewellery or mixed', 'Scrap or undocumented; CAHRA link', ''],
    ['Payment method', 'Bank transfer from own account', 'Cheque / card', 'Cash; third-party payments', ''],
    ['Expected volume / value', 'Consistent with profile', 'Higher than average', 'Large, or inconsistent with profile', ''],
    ['Delivery channel / relationship', 'Face to face, long standing', 'Face to face, new', 'Remote, via intermediary or agent', ''],
  ], [2300, 1900, 1900, 2400, 900]);
  table(null, [
    ['Total score', ''],
    ['Score rating', '7–10 Low     11–15 Medium     16–21 High'],
    ['Overrides — High regardless of score', '☐ PEP or linked to a PEP     ☐ sanctions / TFS concern     ☐ high-risk jurisdiction (Call for Action)     ☐ adverse media on financial crime     ☐ beneficial owner not identifiable (refuse)'],
    ['Screening result (Appendix H ref.)', ''],
    ['Final risk rating and DD level', '☐ Low – SDD     ☐ Medium – CDD     ☐ High – EDD'],
    ['Source of funds / wealth assessment', ''],
    ['MLRO decision', '☐ Approve     ☐ Approve with conditions     ☐ Refuse     ☐ More information needed'],
    ['MLRO signature / date', ''],
    ['Senior Management approval (High risk and PEPs)', 'Name / signature / date:'],
    ['Next review due', ''],
  ], [3200, 6200]);

  // ── H. TFS record ────────────────────────────────────────────────────
  h1('APPENDIX H — TFS SCREENING AND MATCH RECORD');
  table(null, [
    ['Date and time', ''],
    ['Trigger', '☐ Onboarding     ☐ Transaction     ☐ List update     ☐ Periodic review     ☐ EOCN notification (reference: ______ )'],
    ['Names / entities screened', ''],
    ['Tool and list versions used; list update date', '{{SCREENING_TOOL}}  /  '],
    ['Result', '☐ No match     ☐ Alert raised'],
    ['If an alert: details compared (DOB, nationality, ID no., address)', '\n\n'],
    ['Classification', '☐ Negative match     ☐ False positive     ☐ Partial name match     ☐ Confirmed name match'],
    ['Reasons and evidence kept (screenshots / exports)', '\n'],
    ['Action', '☐ Cleared     ☐ Frozen (date/time: ______ )     ☐ PNMR filed     ☐ CNMR filed     ☐ EOCN notified'],
    ['goAML report reference(s) and date(s)', ''],
    ['EOCN response given (if any) and confirmation saved', ''],
    ['MLRO signature and date', ''],
  ], [3800, 5600]);
};
