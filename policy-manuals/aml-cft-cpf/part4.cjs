// Appendices A-H
module.exports = (L) => {
  const { h1, h2, h3, p, bullets, table, note } = L;

  // ── A. MLRO terms of reference ────────────────────────────────────────
  h1('APPENDIX A: TERMS OF REFERENCE: MLRO');
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
    'training program and records (section 11);',
    'the annual MLRO report (section 3.3.4) and the business risk assessment (section 4.2);',
    'maintaining the high-risk jurisdictions register (Appendix C).',
  ]);
  h3('Reporting');
  p('The MLRO reports in writing to {{SENIOR_MANAGEMENT}} at least annually (section 3.3.4), and immediately on any serious matter, including any freeze, any STR/SAR of significance, any regulatory request or any significant breach.');

  // ── B. Red flags ──────────────────────────────────────────────────────
  h1('APPENDIX B: RED FLAG INDICATORS: GOLD AND PRECIOUS METALS');
  p('The presence of a red flag does not mean that a crime has occurred. Red flags are assessed together and in context. If a concern cannot be resolved, it is escalated to the MLRO on an Internal SAR for investigation, EDD, and a decision whether to file an STR/SAR through goAML.');
  h3('Customer behavior and profile');
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
    'any other behavior or transaction inconsistent with the customer\'s known profile or lacking an apparent legitimate economic purpose.',
  ]);

  // ── C. High-risk jurisdictions ───────────────────────────────────────
  h1('APPENDIX C: HIGH-RISK JURISDICTIONS');
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
    ['Democratic Republic of the Congo', 'FATF Increased Monitoring', 'As above. Also treat as a CAHRA candidate (Appendix F).'],
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
  p('Countries {{COMPANY_SHORT}} assesses as high risk for corruption, illicit gold, conflict or weak AML supervision, with the reason, are recorded here. UN and UAE sanctions regimes (including Iran under UNSCR 1737 (2006), and the DPRK) apply in addition. Conflict-affected and high-risk areas for the gold supply chain are recorded in Appendix F.');
  table(['Jurisdiction', 'Reason', 'Date added', 'Measures'], [
    ['', '', '', ''], ['', '', '', ''], ['', '', '', ''],
  ], [2400, 3600, 1400, 2000]);
  h3('Review log');
  table(['Date checked', 'Sources checked', 'Change found', 'Register updated', 'MLRO'], [
    ['', '', '', '', ''], ['', '', '', '', ''], ['', '', '', '', ''],
  ], [1300, 2900, 2500, 1500, 1200]);

  // ── D. Declaration ───────────────────────────────────────────────────
  h1('APPENDIX D: STAFF DECLARATION');
  p('I confirm that I have received a copy of the Anti-Money Laundering, Counter-Terrorist Financing, Counter-Proliferation Financing and Sanctions Policy and Procedures Manual of {{COMPANY_NAME}}, that I have read and understood it, that I am subject to it and that I agree to comply with it.');
  bullets([
    'I understand it is {{COMPANY_SHORT}}\'s policy to prevent its business being used for money laundering, terrorist financing or the financing of weapons of mass destruction, and that it is my duty to be vigilant.',
    'I understand it is a criminal offense to knowingly help any person with these activities, or to tell anyone that a transaction is being examined or that an authority is investigating.',
    'I understand that if I know or have reasonable grounds to suspect money laundering, terrorist financing or proliferation financing, I must report it immediately to the MLRO on the Internal SAR form, and that failing to do so may be grounds for disciplinary action and an offense under UAE law.',
    'I understand that I must not carry out a transaction before the customer has been identified, verified and screened.',
    'I agree that this declaration also covers any later written supplementary or replacement procedures given to me by the MLRO or {{SENIOR_MANAGEMENT}}.',
    'I agree that this declaration forms part of my contract of employment (or contract for services), and that a breach entitles {{COMPANY_SHORT}} to take disciplinary action, including termination with immediate effect.',
  ]);
  table(['Name', 'Function', 'Signature', 'Date'], [['', '', '', ''], ['', '', '', ''], ['', '', '', '']], [2600, 2400, 2600, 1800]);

  // ── E. Internal SAR ──────────────────────────────────────────────────
  h1('APPENDIX E: INTERNAL SUSPICIOUS ACTIVITY REPORT (INTERNAL SAR)');
  p('To be completed by any employee who has a suspicion, and handed to the MLRO immediately. Do not discuss the report with the customer or with anyone other than the MLRO.');
  table(null, [
    ['Date and time of report', ''],
    ['Reporting employee (name, function)', ''],
    ['Customer / counterparty full name', ''],
    ['Passport / Emirates ID / trade license no.', ''],
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

  // ── I. CAHRA assessment ──────────────────────────────────────────────
  h1('APPENDIX F: CAHRA ASSESSMENT RECORD');
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
    ['ASM checks (if applicable)', '☐ Legally registered / cooperative / government-recognized     ☐ Recognized standards followed     ☐ Not applicable'],
    ['Conclusion', '☐ No CAHRA link, no red flags: accept     ☐ Red flags or gaps: EDD     ☐ CAHRA link: suspend / refuse / exception'],
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
