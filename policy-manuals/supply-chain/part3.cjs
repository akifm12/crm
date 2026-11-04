// Sections 6 to 8 and the appendix
module.exports = (L) => {
  const { h1, h2, h3, p, bullets, table, note } = L;

  // ── 6. Steps 4 and 5 ───────────────────────────────────────────────────
  h1('6. INDEPENDENT REVIEW AND REPORTING (STEPS 4 AND 5)');
  h2('6.1 What applies to {{COMPANY_SHORT}}');
  p('Under Ministerial Decree No. (68) of 2024, the independent third-party audit and the annual due diligence report are required of entities that refine gold or recycle gold products. As a trader, {{COMPANY_SHORT}} is required to apply steps 1 to 3 (sections 3 to 5). It nevertheless has an independent check on its own system (section 6.3), uses the independent audits of its suppliers as evidence (section 6.2), and follows section 6.4 if it ever takes up refining or recycling. If MoET changes the scope of the requirement, this section is updated.');
  h2('6.2 Evidence from suppliers\' audits');
  p('Where a supplier is a refiner or recycler, {{COMPANY_SHORT}} asks for its latest independent third-party audit report on responsible sourcing, and for its accreditation reports where it is an accredited member of the Emirates Good Delivery, and uses them as supporting evidence in its risk assessment. These reports, like any other certification of conformance with a recognized responsible sourcing standard, support {{COMPANY_SHORT}}\'s own due diligence but never replace it. A supplier that is required to be audited and cannot show a current report is treated as high risk.');
  h2('6.3 Independent review of {{COMPANY_SHORT}}\'s own system');
  p('The annual independent review of the AML/CFT/CPF framework (AML Manual section 3.5) also covers this policy. The reviewer is independent of the Compliance Officer\'s work and tests, on a sample selected by risk: this policy and its procedures; the management structure and training; supplier due diligence files; chain of custody and records; the risk assessments and CAHRA assessments; decisions to continue, suspend or terminate; and the grievance mechanism. The report goes to Senior Management, who record the action taken, and the Compliance Officer tracks every finding to closure.');
  h2('6.4 If {{COMPANY_SHORT}} also refines or recycles gold');
  p('Entities that refine gold or recycle gold products must, in addition to steps 1 to 3:');
  bullets([
    'appoint an independent third-party reviewer approved by MoET, and arrange an independent audit of the supply chain due diligence framework every year, at the company\'s own cost;',
    'make sure the reviewer is independent of the company, its subsidiaries, licensees, contractors and suppliers, has no conflict of interest, and has not provided compliance consultancy (such as setting up a compliance framework or drafting policies) in the past 12 months;',
    'give the reviewer access to all sites, interviews with management, and a risk-based sample of suppliers and documents;',
    'submit the audit reports and the Comprehensive Management Report (management systems, risk assessment and risk management, with the numbers of relationships continued, suspended and terminated, without naming the parties) to MoET every year [VERIFY: current submission deadline; the MoET announcement of September 2022 gave 90 days after the end of the review cycle]; and',
    'if an accredited member of the Emirates Good Delivery, submit its accreditation reports to MoET each year.',
  ]);

  // ── 7. Training ────────────────────────────────────────────────────────
  h1('7. TRAINING AND AWARENESS');
  h2('7.1 Staff training');
  bullets([
    'Everyone involved in supply chain due diligence is trained at recruitment, before they handle suppliers or gold, and on an ongoing basis, **at least twice a year**, in person or digitally [VERIFY: the MoET Regulations say "bi-annual"; this policy applies the stricter reading].',
    'Training mixes general supply chain due diligence topics (this policy, the OECD Guidance, CAHRAs, red flags, the grievance mechanism) with role-specific content for procurement, compliance, logistics and finance staff.',
    'The AML/CFT/CPF training in the AML Manual (section 11) also covers cash and DPMSR reporting, sanctions, and suspicious activity reporting.',
    'Effectiveness is assessed through questionnaires and feedback forms, and staff who do not pass repeat the training.',
    'Records of the training and the assessments are kept for the period in section 3.4 and given to MoET on request.',
  ]);
  h2('7.2 Supplier awareness and capacity building');
  bullets([
    'Suppliers receive this policy\'s commitments (Appendix A), the AML/CFT legislation, the MoET Regulations and the OECD Guidance.',
    '{{COMPANY_SHORT}} offers suppliers information sessions or materials on AML/CFT obligations, responsible gold sourcing, red flags and record keeping, and helps them to build their own due diligence systems, particularly where a risk control plan requires improvement.',
    'The training and the supplier\'s attendance or acknowledgment are recorded in the supplier file.',
  ]);

  // ── 8. Review ──────────────────────────────────────────────────────────
  h1('8. REVIEW, COMMUNICATION AND PUBLICATION');
  h2('8.1 Review and update');
  bullets([
    'The Compliance Officer reviews this policy at least every year, using compliance and audit findings, grievances, stakeholder feedback and regulatory changes, and whenever the law, a MoET circular or guidance, the OECD Guidance, or {{COMPANY_SHORT}}\'s business or sourcing changes materially.',
    'Each review and change is recorded in the version table and change log (section 1.6).',
    'The Compliance Officer checks MoET\'s responsible sourcing and AML pages at least quarterly for new requirements.',
  ]);
  h2('8.2 Approval and communication');
  p('{{SENIOR_MANAGEMENT}} approves every new version. The Compliance Officer then tells employees, suppliers, contractors and other stakeholders through official channels, trains them on the changes, and makes sure the updates are applied consistently across the business. Suppliers are given the updated commitment to sign when the changes are significant.');
  h2('8.3 Publication');
  p('The policy statement (sections 1.1 and 2) and the grievance mechanism (section 3.8) are published on {{WEBSITE}} so that suppliers, employees and other stakeholders know of them. Supplier files, risk assessments, red flag findings and internal reports remain confidential, except to MoET, the FIU and other authorities entitled to them.');
  h2('8.4 Approval');
  table(['Approved by', 'Title', 'Date', 'Signature'], [
    ['{{APPROVER}}', '{{SENIOR_MANAGEMENT}}', '{{ISSUE_DATE}}', ''],
  ], [2600, 2800, 2000, 2000]);

  // ── Appendix A ─────────────────────────────────────────────────────────
  h1('APPENDIX A: SUPPLIER COMMITMENT AND CONTRACT CLAUSE');
  p('Include this clause in every supply agreement, and have the supplier sign the commitment. The supplier may instead provide its own responsible sourcing policy if the Compliance Officer confirms in writing that it is consistent with this policy and with Annex II of the OECD Guidance.');
  h3('Responsible sourcing clause');
  table(null, [
    ['1. Policy', 'The Supplier has received and read the Supply Chain Policy of {{COMPANY_NAME}} and agrees to comply with it, as a condition of the supply of gold to or from {{COMPANY_SHORT}}.'],
    ['2. Commitments', 'The Supplier will not tolerate, profit from, contribute to, assist with or facilitate: torture or cruel, inhuman or degrading treatment; forced or compulsory labor; the worst forms of child labor; other gross human rights abuses; war crimes, crimes against humanity or genocide; direct or indirect support to non-state armed groups; unlawful support to public or private security forces; bribery or misrepresentation of the origin of gold; or money laundering, terrorist financing or proliferation financing. It will pay all taxes, fees and royalties due to governments, and comply with UN, UAE and other applicable sanctions.'],
    ['3. Information', 'The Supplier will provide, accurately and on request, the information and documents {{COMPANY_SHORT}} needs for its due diligence: its identity and beneficial owners; the origin of the gold (mine or, for recycled gold, the point it became recyclable); the transport route and the parties involved; and assay, shipping and payment records. It will tell {{COMPANY_SHORT}} at once of any change that could affect these.'],
    ['4. Access and audit', 'The Supplier will allow {{COMPANY_SHORT}}, or an assessor it appoints, to visit its sites, review its records and a sample of its transactions, and speak to its staff. Where the Supplier is required to be audited, it will give {{COMPANY_SHORT}} its latest independent audit report.'],
    ['5. Risk and remediation', 'Where {{COMPANY_SHORT}} identifies a risk, the Supplier will agree and carry out a risk control plan with timelines. {{COMPANY_SHORT}} may suspend purchases while risks are mitigated, and may suspend or end the relationship if the Supplier does not achieve significant improvement within six months, or immediately if it finds money laundering, terrorist financing, human rights abuse or support to armed conflict.'],
    ['6. Reporting', 'The Supplier acknowledges that {{COMPANY_SHORT}} is required by law to report suspicions to the authorities and may do so without notice to the Supplier.'],
    ['7. Concerns', 'The Supplier and its staff may raise concerns confidentially through {{GRIEVANCE_CONTACT}}.'],
    ['8. Records', 'The Supplier will keep the records of each transaction for at least five years.'],
  ], [2000, 7400]);
  h3('Signature');
  table(['Supplier name', 'Authorized signatory (name and title)', 'Signature', 'Date'], [
    ['', '', '', ''],
  ], [2600, 3000, 2000, 1800]);
};
