// Sections 8-12
module.exports = (L) => {
  const { h1, h2, h3, p, bullets, table, note } = L;

  // ── 8. Monitoring and suspicious activity reporting ───────────────────
  h1('8. MONITORING AND SUSPICIOUS ACTIVITY REPORTING');
  h2('8.1 Monitoring');
  p('{{COMPANY_SHORT}} monitors transactions and customer behaviour to detect activity that is inconsistent with the customer\'s profile, or that has no apparent economic or lawful purpose. Front-line staff watch for the red flags in Appendix B. The MLRO reviews transaction records at least monthly for patterns such as structuring, repeated purchases and sales of the same gold, changes in volume or value, and payments through third parties, and documents the review.');
  h2('8.2 Internal reporting');
  p('Any member of staff who knows, suspects or has reasonable grounds to suspect that a person is engaged in, or attempting, money laundering, terrorist or proliferation financing, or any related crime, must report it to the MLRO immediately on the Internal SAR form (Appendix E), and in any case on the same working day. Staff are not expected to know the exact offence. The MLRO must start any internal investigation immediately and continue it without interruption until the matter is reported or cleared. Failure to report may be a criminal offence and gross misconduct. Making an Internal SAR in good faith does not breach customer confidentiality.');
  h2('8.3 The MLRO\'s review');
  bullets([
    'The MLRO acknowledges the Internal SAR, investigates it without delay and records the steps taken, the information reviewed and the conclusion.',
    'The MLRO decides independently whether to file an STR/SAR. If not filing, the reasons are recorded on the Internal SAR.',
    'Where an STR/SAR is filed, the MLRO notifies MoET if MoET requires it [VERIFY: whether and how MoET must be informed when an STR/SAR is filed], and follows any instruction from the FIU about the continuing relationship or transaction.',
    'Until the MLRO decides, no transaction with the customer is completed if completing it could assist the suspected crime, unless the MLRO instructs otherwise.',
  ]);
  h2('8.4 External reporting through goAML');
  p('Reports to the FIU and EOCN are filed only through goAML, by the MLRO as the registered user. STRs/SARs are filed immediately and without delay once suspicion is established, whatever the amount. The report types are:');
  table(['Report', 'When'], [
    ['STR / SAR', 'Suspicion on a transaction (STR) or an activity or attempted transaction (SAR). Filed immediately and without delay.'],
    ['AIF / AIFT / RFI', 'Follow-up information requested by the FIU through the goAML message board (with or without transactions). Reference the original STR/SAR number.'],
    ['DPMSR', 'Threshold transactions described in section 7.1.'],
    ['HRC / HRCA', 'Transactions or activity related to high-risk countries (section 4.7). Do not execute until three working days after reporting unless the FIU objects.'],
    ['CNMR / PNMR', 'Confirmed or partial name match with the UN or UAE Local Terrorist List, reported to EOCN and copied to MoET (section 6).'],
  ], [2200, 7200]);
  p('Select the correct report type. Do not use a CNMR or PNMR for foreign sanctions lists or for suspected sanctions evasion without a list match: those go to the FIU as an STR/SAR (section 6.8). The MLRO keeps the report, the confirmation and all supporting analysis (section 10). Email or post is used only if the FIU directs it.');
  h2('8.5 Tipping off');
  p('After an Internal SAR has been made, and in particular after any STR/SAR, staff must not tell the customer or anyone else, directly or indirectly, that a report has been made or that an investigation is likely, or do anything that could prejudice an investigation. If staff reasonably believe that carrying out CDD would alert the customer, they stop and consult the MLRO, who may decide to file without completing it. Tipping off is a criminal offence.');
  h2('8.6 Protection of staff');
  p('{{COMPANY_SHORT}} will not disadvantage any employee who reports a suspicion in good faith, internally or to the authorities. Reports may be made directly to the MLRO or, if the concern involves the MLRO, to {{SENIOR_MANAGEMENT}} or directly to the FIU.');

  // ── 9. Onboarding ─────────────────────────────────────────────────────
  h1('9. CUSTOMER ONBOARDING PROCESS');
  h2('9.1 Steps');
  table(['Step', 'Who', 'Action'], [
    ['1. Enquiry', 'Sales / front desk', 'Confirm there is a genuine intention to trade. Explain the documents needed. Never accept cash or gold before CDD is complete.'],
    ['2. Collect information', 'Sales / front desk', 'Customer completes and signs the Customer Identification Form. Collect all documents in the checklist (Appendix F). Verify originals, mark copies "original sighted". Record expected activity and source of funds in the Customer Profile.'],
    ['3. Screen', 'MLRO (or trained delegate)', 'Screen the customer, each beneficial owner, director, signatory and agent for sanctions, PEP and adverse media using {{SCREENING_TOOL}}. Record the results (Appendix H). Any hit stops the process and goes to the MLRO.'],
    ['4. Risk-assess', 'MLRO', 'Complete the Customer Risk Assessment Form (Appendix G). Decide the rating and level of due diligence. Request more information or documents where required.'],
    ['5. Approve', 'MLRO; {{SENIOR_MANAGEMENT}} for High risk and PEPs', 'Approve, refuse, or ask for more information. Record the decision, date and reasons. High-risk customers and PEPs also need Senior Management approval.'],
    ['6. Register', 'MLRO', 'Enter the customer in the customer register with the rating, review date and approvals. Confirm the start of the relationship in writing. No transaction before this step.'],
    ['7. Keep records', 'MLRO', 'File the documents in the customer file (electronic and paper) and keep them for the period in section 10.'],
  ], [1700, 2200, 5500]);
  h2('9.2 Due diligence by risk rating');
  table(['', 'Low risk — SDD', 'Medium risk — CDD', 'High risk — EDD'], [
    ['Customer Identification Form', 'Signed by customer and staff', 'Signed by customer and staff', 'Signed by customer and staff'],
    ['Identity verification', 'Original ID/trade licence', 'Original ID/trade licence', 'Original ID/trade licence, plus independent corroboration'],
    ['Beneficial owners', 'Identify and verify at 25%', 'Identify and verify at 25%', 'Identify and verify at 10%'],
    ['Source of funds / wealth', 'Declared source of funds', 'Declared and recorded; documents on request', 'Documented source of funds and source of wealth'],
    ['Document certification', 'Copy acceptable if original seen', 'Original seen and copy stamped', 'Original seen; external certification for any copies'],
    ['Screening', 'On entry and continuous: customer and beneficial owners', 'On entry and continuous: customer, shareholders, beneficial owners', 'On entry and continuous: customer, directors, shareholders, beneficial owners and all related names'],
    ['Senior Management approval', 'Not required', 'Not required (PEPs excepted)', 'Required'],
    ['Review', 'Every 2 years', 'Every 1 year', 'Every 6 months'],
  ], [2200, 2300, 2300, 2600]);
  p('Wherever the table and section 5 appear to differ, the stricter requirement applies.');

  // ── 10. Record keeping ────────────────────────────────────────────────
  h1('10. RECORD KEEPING');
  p('{{COMPANY_SHORT}} keeps the following records:');
  bullets([
    'all CDD and EDD documents and information on customers, beneficial owners and suppliers, and records of verification;',
    'customer correspondence and the supporting records of each transaction, enough for every transaction to be reconstructed (date, parties, gold description, weight, purity, price, payment method, source of funds, invoices, receipts, assay and shipping documents);',
    'risk assessments (business and customer) and the reasons for ratings and decisions;',
    'screening results, alerts, false-positive reasoning, freezes, TFS reports and EOCN notifications and responses, with search evidence (section 6);',
    'Internal SARs, the MLRO\'s investigation and decision, external reports (STR/SAR, DPMSR, HRC/HRCA, CNMR, PNMR) and all communications with the FIU, MoET and EOCN;',
    'supplier due diligence and responsible-sourcing records and reports;',
    'training records, MLRO reports, independent review reports and Senior Management minutes on AML matters;',
    'any other record required by law or MoET.',
  ]);
  p('**Retention.** Records are kept for at least {{RETENTION_YEARS}} years (never less than five) from the **latest** of: the termination of the business relationship or closure of the account; the completion of an occasional transaction where there is no business relationship; the issue of a final judgment by a competent court; the dissolution, liquidation or termination of a legal person; the completion of an inspection by MoET; or the completion of an investigation. If the end of a relationship is unclear, it is taken to be the date of the last transaction. This covers CDD records, account files, correspondence, STRs and analysis, CCTV and other recordings, and screening results (confirmed, partial and false positive), which are kept for at least five years. Records under investigation or requested by an authority are kept for as long as required, even beyond this period. Training logs are kept for the same period.');
  p('**Form and access.** Records may be electronic if readily available. They must be organised so that {{COMPANY_SHORT}} can promptly give MoET, the FIU, other authorities or a court any information requested, identify each customer, transaction and report, and show compliance. Records are kept securely, backed up, with access limited to authorised staff, and, if kept outside the UAE, in a way that keeps them immediately available for inspection. Where another country\'s secrecy or data-protection law could restrict access, certified copies are kept where authorities can obtain them. Records are destroyed only after the retention period, by the MLRO\'s decision.');

  // ── 11. Training ──────────────────────────────────────────────────────
  h1('11. TRAINING AND AWARENESS');
  bullets([
    'Every new employee receives AML/CFT/CPF induction training before dealing with customers or transactions, and in any case within the first month.',
    'All staff receive refresher training at least once a year, and additional training when the law or this Manual changes materially.',
    'The MLRO, deputy MLRO, Senior Management and onboarding staff receive additional role-based training; the MLRO keeps professional knowledge up to date, including attending MoET and FIU events where available.',
    'Training is tailored to {{COMPANY_SHORT}}\'s business and covers: the law and penalties; this Manual and the staff duties; recognising red flags in the gold trade; cash and DPMSR rules; CDD and screening; TFS and the EOCN procedure; responsible sourcing; how to make an Internal SAR; tipping off; and the identity of the MLRO and deputy.',
    'Training is tested (for example a short assessment). Records show the date, content, trainer and attendees and are kept (section 10). Staff who fail the assessment repeat the training.',
  ]);

  // ── 12. Independent review and maintenance ────────────────────────────
  h1('12. REVIEW AND MAINTENANCE OF THIS MANUAL');
  bullets([
    'The MLRO reviews this Manual at least once a year and whenever the law, an MoET or FIU circular, EOCN guidance, the National Risk Assessment, a FATF finding or {{COMPANY_SHORT}}\'s business changes materially.',
    'Each review and each change is recorded in the version table and the regulatory change log (section 1.6). {{SENIOR_MANAGEMENT}} approves each new version.',
    'Staff are told of every change, receive the new version, and re-sign Appendix D when the changes are significant.',
    'The independent review (section 3.5) tests whether this Manual is followed and whether it remains adequate. Weaknesses are recorded with an owner and a deadline, and the MLRO tracks them to closure.',
    'The MLRO checks at least quarterly for updates to the lists and guidance referred to in Appendix C and the regulatory sources in section 1.3.',
  ]);
};
