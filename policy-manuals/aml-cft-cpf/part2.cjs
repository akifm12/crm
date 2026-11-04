// Sections 4-7: risk-based approach, CDD, targeted financial sanctions, gold-sector controls
module.exports = (L) => {
  const { h1, h2, h3, p, bullets, table, note } = L;

  // ── 4. Risk-based approach ─────────────────────────────────────────────
  h1('4. RISK-BASED APPROACH');
  h2('4.1 Principle');
  p('{{COMPANY_SHORT}} identifies, assesses, understands, documents and keeps up to date its ML/TF/PF risks, and applies controls in proportion: enhanced measures where risk is higher, and, only where the law and this Manual allow, simplified measures where it is demonstrably lower. Even for low-risk customers, identification, verification and sanctions screening are always carried out.');
  p('The assessment is objective, based on reasonable grounds, documented, approved by {{SENIOR_MANAGEMENT}}, and updated at least annually and whenever the business changes.');
  h2('4.2 Business risk assessment');
  p('The MLRO prepares a written business (enterprise-wide) risk assessment covering ML, TF and PF, taking account of the UAE National Risk Assessment and MoE sector guidance [VERIFY: current National Risk Assessment and sectoral risk assessment for DPMS]. It considers at least:');
  bullets([
    'customer types and their activities (retail, jewellers, refiners, traders, corporates, walk-in sellers of scrap gold);',
    'countries and areas of the customers, suppliers, gold origin, transit and destination;',
    'products and services (bars, coins, jewellery, scrap, refined gold; buy and sell);',
    'delivery channels and counterparties (walk-in, remote, intermediaries, agents);',
    'transaction size, frequency, payment methods, especially cash and third-party payments;',
    'supply chain and responsible-sourcing exposure;',
    'proliferation-financing exposure, including dual-use goods and sanctioned jurisdictions;',
    'new products, practices, delivery mechanisms and technologies. Before any is launched, the MLRO assesses its ML/TF/PF risk and the mitigation required.',
  ]);
  p('The results are used to design and test controls, to allocate resources, and to inform the customer risk assessment. The report is kept as a controlled document.');
  h2('4.3 Customer risk assessment');
  p('Every customer is risk-assessed and rated Low, Medium or High before the relationship starts or any transaction takes place, then reviewed periodically (section 5.9) and whenever risk factors change. The rating decides the level of due diligence (Simplified, Standard or Enhanced). The assessment is recorded on the Customer Risk Assessment Form (Appendix G). Factors:');
  bullets([
    'the customer and each beneficial owner: nature, business, reputation, ownership and control structure;',
    'purpose and intended nature of the relationship and the expected volume and type of transactions;',
    'geography: nationality, residence, incorporation, place of business, and the origin and destination of the gold and payments;',
    'products, payment method and delivery channel;',
    'PEP status, sanctions screening and adverse media results;',
    'the outcome of the business risk assessment.',
  ]);
  h2('4.4 Lower-risk indicators');
  p('A customer may be rated Low only after the MLRO has assessed its actual exposure, and only if all of the following hold: ownership and activity are transparent; the customer is, for example, a government body or government-owned entity, a company listed on a regulated exchange with equivalent disclosure obligations, or a regulated financial institution supervised for AML/CFT in a jurisdiction with equivalent standards; there is no PEP, sanctions or adverse media concern; no high-risk jurisdiction is involved; and the customer is not cash-intensive. Other DNFBPs (including lawyers, accountants, real estate agents and other precious metals dealers) are not automatically Low risk.');
  h2('4.5 Higher-risk indicators');
  p('The following indicate higher risk and normally lead to a High rating and Enhanced Due Diligence (EDD):');
  bullets([
    'the customer or a beneficial owner is a PEP, or is linked to one;',
    'the customer or any party is linked to a high-risk jurisdiction (Appendix C) or to a jurisdiction subject to sanctions, embargoes, or credible reports of terrorism financing, corruption or illicit gold;',
    'cash-intensive business or cash payment above the reporting threshold (section 7.1);',
    'complex, opaque or frequently changing ownership; nominee shareholders or directors; bearer shares; personal asset-holding vehicles; unexplained use of multiple entities;',
    'non-face-to-face relationships without appropriate safeguards;',
    'payments from or to unknown or unrelated third parties;',
    'gold of unclear or undocumented origin, scrap or second-hand gold in bulk, or gold from conflict-affected and high-risk areas (section 7.4);',
    'adverse media about financial crime, sanctions evasion, smuggling or illicit gold.',
  ]);
  h2('4.6 Relationships {{COMPANY_SHORT}} will not enter into');
  bullets([
    'anonymous accounts or transactions, accounts in fictitious or false names, or nominee arrangements where the real owner is not disclosed;',
    'customers whose beneficial owners cannot be identified, including entities that issue bearer shares or similar instruments that prevent identification;',
    'customers who refuse or fail to provide the information and documents required, or give information that cannot be verified;',
    'any person or entity on the UN Consolidated List or the UAE Local Terrorist List, or owned or controlled by one: these are frozen and reported, not onboarded (section 6);',
    'shell banks or shell companies with no real business or identifiable owner;',
    'any relationship {{COMPANY_SHORT}} cannot manage within its resources and risk appetite.',
  ]);

  // ── 5. CDD ─────────────────────────────────────────────────────────────
  h1('5. CUSTOMER DUE DILIGENCE');
  h2('5.1 When customer due diligence (CDD) is required');
  p('{{COMPANY_SHORT}} applies CDD:');
  bullets([
    'before establishing a business relationship;',
    'before any occasional transaction in cash, or equivalent, of AED 55,000 or more, whether a single transaction or several that appear linked [VERIFY: current MoE threshold and whether it also applies to non-cash payments];',
    'when buying gold from any individual or business, whatever the value (section 7.3);',
    'whenever ML, TF or PF is suspected;',
    'whenever there are doubts about the accuracy or adequacy of previously obtained information.',
  ]);
  p('CDD is also applied to existing customers, on the schedule in section 5.9 and whenever circumstances change.');
  h2('5.2 Timing');
  p('CDD, including sanctions and PEP screening, is completed before the relationship is established and before any transaction is carried out. This Manual does not allow transactions to proceed before verification is complete. A walk-in customer who cannot be identified and screened is not served.');
  h2('5.3 Identifying and verifying the customer');
  p('{{COMPANY_SHORT}} identifies the customer and verifies identity from original, valid documents (or certified copies where originals cannot be seen). Copies taken by staff are dated, signed and marked "original sighted". The standard documents are listed in Appendix F.');
  table(['Customer type', 'Information obtained and verified'], [
    ['Natural person (UAE resident)', 'Full name (and any alias), date of birth, nationality, Emirates ID (original, scanned), residential address, contact details, occupation/employer, purpose of the transaction.'],
    ['Natural person (non-resident / visitor)', 'As above from a valid passport; proof of entry (visa or entry stamp); address abroad; Emirates ID if held.'],
    ['Company / body corporate', 'Full legal name and trading names; legal form; registered address and place of business; date and place of incorporation; trade licence; memorandum/articles of association; certificate of incorporation/registration; list of directors, managers and shareholders; ownership structure chart; tax registration (TRN) where applicable; authorisation for signatories and ID of each; full names of governing body members and senior managers.'],
    ['Partnership, trust, foundation, association, non-profit', 'Constituting documents (deed, charter, bylaws); documentary evidence of the appointment of the trustees/managers/guardian; identity of settlor, trustees, beneficiaries, founders, council members, protectors and any person with control. Always High risk and approved by the MLRO and Senior Management.'],
    ['Person acting for the customer', 'Valid power of attorney or written authority; identification and verification of the agent and of the customer they represent.'],
  ], [2500, 6900]);
  h2('5.4 Beneficial owners');
  p('For every customer that is not a natural person, {{COMPANY_SHORT}} identifies the beneficial owners and takes reasonable measures to verify their identity, so that it is satisfied it knows who they are. The steps, in order:');
  bullets([
    'identify each natural person who ultimately holds, directly or indirectly, 25% or more of the shares, capital or voting rights;',
    'if none (or if there is doubt), identify any natural person who controls the entity by other means, including the right to appoint or remove most of the board;',
    'if still none, identify the senior managing official and record that this was done;',
    'for trusts and similar arrangements, identify the settlor, trustees, protector, each beneficiary or class of beneficiaries and anyone with ultimate effective control;',
    'for foundations, the founder, council members, guardian, beneficiaries and anyone with control.',
  ]);
  p('For High-risk customers the ownership threshold for identification is lowered to 10%. Where a shareholder is itself a legal entity, {{COMPANY_SHORT}} looks through the chain until it reaches natural persons. Beneficial owners are not identified only where the law exempts the customer (for example a listed company or a government-owned entity), and the basis is recorded. {{COMPANY_SHORT}} also checks the customer\'s declared ownership against the UBO register where accessible [VERIFY: current UBO register access and the duty to report discrepancies].');
  h2('5.5 Purpose, nature and source of funds');
  p('{{COMPANY_SHORT}} obtains the purpose and intended nature of the relationship, the expected volume, value, frequency and payment method of transactions, and the source of funds. For Medium and High risk customers, and for cash transactions at or above the reporting threshold, the source of funds is obtained and recorded; for High risk it is evidenced by documents (such as bank statements, sale contracts, invoices, or proof of dividends) and source of wealth is also established.');
  h2('5.6 Politically exposed persons (PEPs)');
  p('{{COMPANY_SHORT}} screens every customer and beneficial owner for PEP status at onboarding and on an ongoing basis ({{SCREENING_TOOL}}). Being a PEP does not by itself make a person unacceptable, but it requires EDD:');
  bullets([
    'approval of Senior Management before the relationship starts or continues, on the MLRO\'s recommendation;',
    'identification and evidence of source of wealth and source of funds;',
    'enhanced and more frequent monitoring and review (at least every 6 months);',
    'the same measures for family members and close associates of a PEP, and for former PEPs for as long as the risk remains (at least 12 months after leaving office, longer if the MLRO assesses the person still carries influence or risk).',
  ]);
  p('The assessment of each PEP and the reasons for the decision are recorded on the Customer Risk Assessment Form.');
  h2('5.7 Simplified due diligence (SDD)');
  p('SDD is available only for customers the MLRO has rated Low risk in writing, and only where the law permits. SDD never removes identification, verification of identity, beneficial owner identification or sanctions and PEP screening. It may only reduce, in proportion to the risk, the frequency of reviews (to every 2 years) and the amount of supporting evidence on purpose and source of funds. SDD is not applied when there is any suspicion, any sanctions or PEP hit, any high-risk jurisdiction link, or a cash transaction at or above the reporting threshold.');
  h2('5.8 Enhanced due diligence (EDD)');
  p('EDD is applied to all High-risk customers, all PEPs, every relationship or transaction involving a high-risk jurisdiction, and wherever the MLRO decides it is needed. In addition to standard CDD, {{COMPANY_SHORT}}:');
  bullets([
    'obtains additional identification information on the customer and all beneficial owners (10% threshold) and independent corroboration of the information given;',
    'obtains more information on the nature of the business, the reasons for the transactions and the commercial rationale;',
    'establishes and evidences source of funds and source of wealth;',
    'obtains the approval of Senior Management before establishing or continuing the relationship;',
    'applies enhanced monitoring: more frequent reviews (every 6 months), lower alert thresholds, and review of each transaction against the profile;',
    'for large or unusual payments, may require payment through an account in the customer\'s own name with a regulated bank.',
  ]);
  p('The MLRO decides the measures case by case and records the decision. Evidence of source of funds includes bank statements, contracts, invoices and sale documents; evidence of source of wealth includes audited accounts, company registers, asset records, tax records or reputable independent sources.');
  h2('5.9 Ongoing due diligence and reviews');
  p('{{COMPANY_SHORT}} monitors transactions throughout the relationship to make sure they are consistent with its knowledge of the customer, its business and its risk rating, with particular attention to complex or unusually large transactions and unusual patterns without an apparent economic or lawful purpose. Customer files are reviewed periodically:');
  table(['Customer risk rating', 'Due diligence level', 'Full review at least every'], [
    ['Low', 'Simplified (SDD)', '2 years'],
    ['Medium', 'Standard (CDD)', '1 year'],
    ['High, and all PEPs', 'Enhanced (EDD)', '6 months'],
  ], [3000, 3200, 3200]);
  p('A review is also triggered immediately when: {{COMPANY_SHORT}}\'s CDD requirements change; an unusual transaction is expected or occurs; there is a material change in the relationship, the customer\'s activity, ownership or control; there is a sanctions, PEP or adverse media alert; or an Internal SAR is raised. Reviews update the information, re-check the risk rating and re-screen all parties. Expired identification is replaced at the next review, and in any case before any further transaction with that customer.');
  h2('5.10 Failure to complete CDD');
  p('If {{COMPANY_SHORT}} cannot complete CDD, it must not establish the relationship, open an account, provide a service or carry out any transaction; it ends or suspends an existing relationship; and the MLRO decides, without alerting the customer, whether to file an STR/SAR and what to do with any funds or gold held (nothing is returned or released without the MLRO\'s approval, and anything linked to a sanctions match is frozen under section 6). {{COMPANY_SHORT}} does not take any step that would amount to tipping off.');
  h2('5.11 Reliance on third parties and outsourcing');
  p('{{COMPANY_SHORT}} may rely on CDD done by another regulated and supervised person (such as a bank, a licensed financial institution or another AML-supervised DNFBP) only if it immediately obtains the CDD information and has satisfied itself that copies of the underlying documents will be provided without delay on request, that the third party is subject to equivalent AML/CFT regulation and supervision, and that the information is up to date. If it is not satisfied, {{COMPANY_SHORT}} carries out CDD itself. {{COMPANY_SHORT}} remains responsible for the outcome. It records how it assessed the third party.');
  p('Where {{COMPANY_SHORT}} outsources any AML/CFT function (including the MLRO role, screening, record storage or CDD), it first performs due diligence on the provider, documents the provider\'s obligations in a written agreement that includes confidentiality, access for MoE and audit, and record return on termination, and reviews performance at least annually. Responsibility stays with {{COMPANY_SHORT}}.');

  // ── 6. TFS ─────────────────────────────────────────────────────────────
  h1('6. TARGETED FINANCIAL SANCTIONS (TFS)');
  h2('6.1 Obligation');
  p('{{COMPANY_SHORT}} must, without delay and without prior notice: freeze all funds and assets owned or controlled by, or held for the benefit of, persons and entities on the UN Consolidated List and the UAE Local Terrorist List; not make funds, assets, gold or services available to them; and report to the authorities as set out in this section. These duties apply at all times and to all customers, beneficial owners, agents, suppliers, counterparties and transaction parties. [VERIFY: current Cabinet Decision No. (74) of 2020 and EOCN guidance on scope, deadlines and report types.]');
  h2('6.2 Responsibilities');
  p('The MLRO is {{COMPANY_SHORT}}\'s TFS compliance officer, and the deputy MLRO acts in the MLRO\'s absence. {{SENIOR_MANAGEMENT}} makes sure the MLRO has the tools and authority to freeze and report. All staff must stop and refer to the MLRO any actual or possible match.');
  h2('6.3 Staying informed');
  p('{{COMPANY_SHORT}} is registered with the EOCN notification system so it receives list updates and alerts, and keeps the registration current, with at least two monitored contacts. The MLRO checks that notifications are being received and keeps evidence of each notification and the action taken.');
  h2('6.4 Screening');
  table(['Who is screened', 'When', 'Against'], [
    ['Customers, beneficial owners, directors, shareholders, authorised signatories and agents', 'Before the relationship starts and before any transaction; again automatically whenever a list is updated; and at each periodic review', 'UN Consolidated List and UAE Local Terrorist List (mandatory); other lists chosen by {{COMPANY_SHORT}} on a risk basis (for example OFAC, EU, UK) and PEP/adverse media data'],
    ['Suppliers, refiners, intermediaries and counterparties, including the parties named on shipping and customs documents', 'Before the first purchase or sale, and on list updates', 'As above'],
    ['Payers and payees of third-party payments (section 7.2) and the beneficiaries of any transaction', 'Before the payment is accepted or made', 'As above'],
    ['Employees', 'At hiring and periodically', 'As above'],
  ], [3300, 3200, 2900]);
  p('Screening is done with {{SCREENING_TOOL}}. The MLRO checks that the tool carries current lists and records the date the lists were last updated. A screening record (name, date, lists, result, who reviewed) is kept for every check (Appendix H).');
  h2('6.5 Handling potential matches');
  bullets([
    'Any alert is passed to the MLRO immediately. The transaction or relationship is put on hold, and nothing is released while the alert is open.',
    'The MLRO compares the name and identifying details (date of birth, nationality, passport/ID number, address, aliases) with the listing and classifies the alert as: false positive (clearly a different person, with documented reasons), partial/possible match (some details match, but identity cannot be excluded), or confirmed match.',
    'The reasoning and evidence for every alert, including false positives, are recorded in the TFS Screening and Match Record (Appendix H).',
  ]);
  h2('6.6 Confirmed or partial matches');
  bullets([
    '**Confirmed match:** freeze, without delay (within 24 hours at the latest) all funds, gold and other assets of the listed person that {{COMPANY_SHORT}} holds or controls; do not carry out any transaction or provide any service; report through goAML with a Funds Freeze Report (FFR) within 5 business days, and notify EOCN as required.',
    '**Partial / possible match that cannot be excluded:** suspend the transaction, do not tip off, and report through goAML with a Partial Name Match Report (PNMR) within 5 business days, then follow the instructions of the authorities.',
    '**Maintain the freeze** until the authority instructs otherwise; do not release, return or deal with the assets.',
    '**Do not alert** the customer or anyone else, other than as required by the authorities.',
    '**Humanitarian or other exemptions** and requests to use frozen funds are handled only on the written approval of the competent authority.',
  ]);
  p('[VERIFY: freezing deadline, reporting deadline, report types and contact channels against current Cabinet Decision No. (74) of 2020, EOCN guidance and goAML requirements.]');
  h2('6.7 EOCN alerts and notifications');
  p('When EOCN issues a notification asking {{COMPANY_SHORT}} to check its records against newly listed or amended names, or to confirm the outcome of a search (for example through a unique survey link):');
  bullets([
    'the MLRO receives the notification, records the date and time, and checks the deadline;',
    'the MLRO searches the complete customer, beneficial owner, supplier and counterparty records, using {{SCREENING_TOOL}} and the company\'s own client list, for every name in the notification, including variants and transliterations;',
    'the MLRO keeps evidence of the search (dated screenshot or export showing the names searched and the result);',
    'only after the search is complete does the MLRO answer EOCN. "No match" is given only if nothing was found. If a possible or confirmed match exists, the MLRO answers accordingly, freezes and reports under section 6.6. Nobody else may answer on {{COMPANY_SHORT}}\'s behalf, and the answer must never be given before the search is done;',
    'the MLRO keeps the confirmation of submission (for example the survey completion page) together with the search evidence.',
  ]);
  h2('6.8 Other sanctions regimes');
  p('{{COMPANY_SHORT}} considers sanctions issued by other jurisdictions (for example the US, EU and UK) as a risk factor and screens against them as set out in 6.4, because they affect banking, shipment and insurance. Dealing with a person who is sanctioned only under a foreign regime is not automatically prohibited by UAE law, but it is escalated to the MLRO and Senior Management for a risk decision. Proliferation-related risk is assessed in the business risk assessment.');
  h2('6.9 Records and training');
  p('All TFS records (screening results, alerts, decisions, freezes, reports, EOCN notifications and responses) are kept for at least {{RETENTION_YEARS}} years. TFS is part of induction and annual training for all staff and of additional training for the MLRO and anyone who screens or onboards customers.');

  // ── 7. Gold-sector controls ───────────────────────────────────────────
  h1('7. CONTROLS SPECIFIC TO THE GOLD AND PRECIOUS METALS TRADE');
  h2('7.1 Cash transactions and DPMSR reporting');
  bullets([
    'Every cash transaction is recorded with the customer\'s identity, the amount, the gold bought or sold, the payment method and the source of funds.',
    'CDD is required for any cash (or equivalent) transaction of AED 55,000 or more, whether a single transaction or linked transactions, and the customer\'s source of funds is recorded [VERIFY: current threshold and the types of payment covered].',
    'The MLRO files a DPMSR through goAML for every transaction that falls in the MoE\'s reporting scope, within the deadline MoE sets [VERIFY: DPMSR scope (cash, wire transfers, others), threshold, linked-transaction rule and filing deadline].',
    'Staff must not split a transaction, advise a customer to split it, or accept several payments that appear designed to stay below the threshold. Apparent structuring is escalated to the MLRO as an Internal SAR even if no single transaction reaches the threshold.',
    'Cash is accepted only in the UAE currency or other currency the Senior Management has approved. Cash received is counted, receipted and banked as soon as practicable.',
  ]);
  h2('7.2 Payments');
  bullets([
    'Payments are accepted only from, and made only to, the customer or supplier named in the transaction, from an account in their own name.',
    'A payment from or to a third party is not accepted unless the MLRO approves it in advance after establishing the reason for the payment, the identity of the third party and its relationship to the customer, and screening the third party.',
    'Payment to a different account from the one the customer paid from, or to a country unrelated to the transaction, is treated as a red flag.',
    'Virtual assets, prepaid instruments and cheques from unrelated parties are not accepted unless Senior Management has approved the channel in writing with controls in place.',
  ]);
  h2('7.3 Buying gold from the public (scrap, jewellery and second-hand gold)');
  bullets([
    'The seller is identified, verified and screened for every purchase, whatever the value, and a purchase record is kept (seller ID, date, description, weight, purity, price, payment method).',
    'The seller states, and where possible shows evidence of, how they came to own the gold. Bulk or recurrent sales of scrap gold, gold without a plausible origin, damaged or defaced marks, or items that appear new are escalated to the MLRO.',
    'Items showing signs of theft or tampering are not bought. Payments follow section 7.2.',
  ]);
  h2('7.4 Suppliers, refiners and responsible sourcing');
  p('{{COMPANY_SHORT}} applies due diligence to every supplier and upstream counterparty before first dealing and periodically, following the five-step framework of the OECD Due Diligence Guidance and MoE requirements on responsible sourcing [VERIFY: current MoE responsible sourcing rules, reporting and audit requirements]:');
  bullets([
    ['**Step 1 — Management systems:** this Manual and the supplier due diligence procedure are the company policy; the MLRO is responsible; records are kept; staff are trained.', null],
    ['**Step 2 — Identify and assess risks:** for each supplier and consignment, record the supplier\'s identity, ownership and beneficial owners (CDD as for a customer), licence and regulatory status, the type of gold (mined, recycled/scrap, refined, jewellery), the country of origin and transit, and whether any CAHRA is involved. Obtain and check supporting documents: invoices, packing lists, assay certificates, refiner certificates, import/export and customs documents, transport and insurance documents. Check for consistency of weight, purity, value and parties.', null],
    ['**Step 3 — Manage and mitigate:** accept, suspend or refuse; apply EDD for higher-risk suppliers; do not deal with any supplier linked to sanctions, serious human rights abuses, funding of armed groups, or illicit trade. Document each decision.', null],
    ['**Step 4 — Independent check:** include supplier due diligence and responsible sourcing in the annual independent review (section 3.5) and any third-party audit MoE requires.', null],
    ['**Step 5 — Report:** prepare the annual responsible-sourcing report where required, make it available to the authorities, and retain it.', null],
  ]);
  p('Suppliers are rated Low, Medium or High risk and reviewed on the same schedule as customers (section 5.9).');
  h2('7.5 Trade and shipment red flags');
  p('The MLRO assesses and escalates: mismatch between the quantity, purity, value or origin of the gold and the documents; prices well above or below market; unnecessary intermediaries or routing through jurisdictions unrelated to the transaction; unexplained changes of consignee or payee; sanctioned or high-risk vessels, ports, routes or end users; and dual-use goods in the customer\'s other business. See Appendix B.');
  h2('7.6 Refusing or ending a transaction');
  p('Staff may refuse any transaction they are not comfortable with. A refusal and its reason are recorded and passed to the MLRO, who decides whether it is also reportable. Staff do not explain to the customer that a suspicion exists.');
};
