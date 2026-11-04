// Sections 4-7: risk-based approach, CDD, targeted financial sanctions, gold-sector controls
module.exports = (L) => {
  const { h1, h2, h3, p, bullets, table, note } = L;

  // ── 4. Risk-based approach ─────────────────────────────────────────────
  h1('4. RISK-BASED APPROACH');
  h2('4.1 Principle');
  p('{{COMPANY_SHORT}} identifies, assesses, understands, documents and keeps up to date its ML/TF/PF risks, and applies controls in proportion: enhanced measures where risk is higher, and, only where the law and this Manual allow, simplified measures where it is demonstrably lower. Even for low-risk customers, identification, verification and sanctions screening are always carried out.');
  p('The risk methodology, both for the business and for customers, is written down with its rationale and assumptions, approved by {{SENIOR_MANAGEMENT}}, and communicated to everyone who applies it. Its results decide the due diligence applied (simplified, standard or enhanced).');
  h2('4.2 Business risk assessment');
  p('The MLRO prepares a written business (enterprise-wide) risk assessment (BRA) covering ML, TF and PF, taking account of the UAE National Risk Assessment (2024) and the MoET sectoral findings, which rate the DPMS sector as high inherent risk. The BRA is separate from the customer risk assessment: it measures the risk of the business as a whole, while the customer assessment rates one relationship.');
  h3('Method');
  bullets([
    'Identify the inherent risks from these factors: customer types and their activities; countries and geographic areas of customers, suppliers, gold origin, transit and destination; products and services (bars, coins, jewelry, scrap, refined gold; buying and selling); delivery channels and counterparties; transaction size, frequency and payment methods, especially cash and third-party payments; supply chain and responsible-sourcing exposure; proliferation-financing exposure, including dual-use goods and sanctioned jurisdictions; new products, practices, delivery mechanisms and technologies.',
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
  p('Every customer is risk-assessed and rated Low, Medium or High before the relationship starts or any transaction takes place, then reviewed periodically (section 5.9) and whenever risk factors change. The assessment is recorded in the customer risk assessment in the KYC module. Factors:');
  bullets([
    'the customer and each beneficial owner: nature, business, reputation, ownership and control structure;',
    'purpose and intended nature of the relationship and the expected volume and type of transactions;',
    'geography: nationality, residence, incorporation, place of business, and the origin and destination of the gold and payments;',
    'products, payment method and delivery channel;',
    'PEP status, sanctions screening and adverse media results;',
    'the outcome of the business risk assessment.',
  ]);
  p('The customer risk scoring model in the KYC module, including its factors, scores and rating bands, is a documented model: the MLRO explains the rationale and assumptions behind it, and {{SENIOR_MANAGEMENT}} approves it. The MLRO reviews the model at least annually and when the BRA changes. The rating decides the due diligence level. A rating may be overridden only by the MLRO, in writing, with reasons, and never below the minimum rating the model requires for a PEP, a sanctions concern, a call-for-action jurisdiction, adverse media on financial crime, or a beneficial owner who cannot be identified. Besides the scheduled reviews in section 5.9, an event-driven review is carried out on: contradictory or outdated CDD information; a change of ownership, structure or control; new legal proceedings involving the customer or a beneficial owner; adverse media; qualified audit opinions or third-party concerns; and transactions inconsistent with the customer\'s profile. Each review decides whether to update the rating, escalate to Senior Management, file an STR/SAR, or suspend or end the relationship.');
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
    'gold of unclear or undocumented origin, scrap or second-hand gold in bulk, or gold from conflict-affected and high-risk areas (section 7.5);',
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

  // ── 4.7 high-risk countries
  h2('4.7 High-risk countries (MoET Circular No. 1 of 2026)');
  p('The MLRO follows the lists issued by the National Committee for AML/CFT/CPF and the FATF (Appendix C) and applies the following:');
  bullets([
    '**Countries subject to a FATF call for action ("blacklist"):** Enhanced Due Diligence on every business relationship and transaction connected with the country; no branches, representative offices or subsidiaries there; no reliance on third parties located there for CDD; strengthened internal reporting and STRs where appropriate; full compliance with targeted financial sanctions.',
    '**Countries under increased monitoring ("graylist"):** Enhanced Due Diligence proportionate to the risk, and account taken of FATF updates in risk assessments and relationship reviews.',
    '**HRC / HRCA reports:** a transaction or activity related to a high-risk country is reported to the FIU through goAML (section 8.4) and not carried out until three working days after reporting, unless the FIU objects. If it is also suspicious, the STR obligation takes priority.',
  ]);

  // ── 5. CDD
  h1('5. CUSTOMER DUE DILIGENCE');
  h2('5.1 When customer due diligence (CDD) is required');
  p('{{COMPANY_SHORT}} applies CDD:');
  bullets([
    'before establishing a business relationship;',
    'before any occasional transaction of AED 55,000 or more, whether a single transaction or several that appear linked, including installments, deposits, trade-ins and payments in kind, whatever the payment method (the Supplemental Guidance treats AED 55,000 as the minimum legal trigger and not as an exemption below it) [VERIFY: MoET to confirm the reading of "cash" in Article 3(3) of Cabinet Resolution No. (134) of 2025; this Manual applies the stricter reading];',
    'when buying gold from any individual or business, whatever the value (section 7.3);',
    'whenever ML, TF or PF is suspected;',
    'whenever there are doubts about the accuracy or adequacy of previously obtained information.',
  ]);
  p('CDD is also applied to existing customers, on the schedule in section 5.9 and whenever circumstances change. Sanctions and PEP screening applies to every customer without exception, whatever the value or payment method (Circular 3 of 2025).');
  h2('5.2 Timing');
  p('CDD, including sanctions and PEP screening, is completed before the relationship is established and before any transaction is carried out. The Executive Regulations allow identity verification to be deferred in low-risk cases; **this Manual does not use that option**. A walk-in customer who cannot be identified and screened is not served.');
  h2('5.3 Identifying and verifying the customer');
  p('{{COMPANY_SHORT}} identifies the customer and verifies identity from original, valid documents (or certified copies where originals cannot be seen). Copies taken by staff are dated, signed and marked "original sighted". The documents required for each customer type are set out in the KYC module checklist.');
  table(['Customer type', 'Information obtained and verified'], [
    ['Natural person (UAE resident)', 'Full name (and any alias), date of birth, nationality, Emirates ID (original, scanned), residential address, contact details, occupation/employer, purpose of the transaction.'],
    ['Natural person (non-resident / visitor)', 'As above from a valid passport; proof of entry (visa or entry stamp); address abroad; Emirates ID if held.'],
    ['Company / body corporate', 'Full legal name and trading names; legal form; registered address and place of business; date and place of incorporation; trade license; memorandum/articles of association; certificate of incorporation/registration; list of directors, managers and shareholders; ownership structure chart; tax registration (TRN) where applicable; authorization for signatories and ID of each; full names of governing body members and senior managers.'],
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
  p('For High-risk customers the ownership threshold for identification is lowered to 10%. Where a shareholder is itself a legal entity, {{COMPANY_SHORT}} looks through the chain until it reaches natural persons. Beneficial owners are not identified only where the law exempts the customer (for example a listed company or a government-owned entity), and the basis is recorded. {{COMPANY_SHORT}} keeps beneficial-owner information on its customers up to date, and its own beneficial-owner information current, notifying changes within 15 working days as the Executive Regulations and Cabinet Decision No. (109) of 2023 require.');
  h2('5.5 Purpose, nature and source of funds');
  p('{{COMPANY_SHORT}} obtains the purpose and intended nature of the relationship, the expected volume, value, frequency and payment method of transactions, and the source of funds. For Medium and High risk customers, and for cash transactions at or above the reporting threshold, the source of funds is obtained and recorded; for High risk it is evidenced by documents (such as bank statements, sale contracts, invoices, or proof of dividends) and source of wealth is also established.');
  h2('5.6 Politically exposed persons (PEPs)');
  p('{{COMPANY_SHORT}} screens every customer and beneficial owner for PEP status at onboarding and on an ongoing basis ({{SCREENING_TOOL}}). Being a PEP does not by itself make a person unacceptable, but it requires EDD. The law requires Senior Management approval and source-of-wealth checks for all foreign PEPs, and for domestic PEPs and international-organization officials in high-risk relationships; this Manual applies them to every PEP:');
  bullets([
    'approval of Senior Management before the relationship starts or continues, on the MLRO\'s recommendation;',
    'identification and evidence of source of wealth and source of funds;',
    'enhanced and more frequent monitoring and review (at least every 6 months);',
    'the same measures for family members and close associates of a PEP, and for former PEPs for as long as the risk remains (at least 12 months after leaving office, longer if the MLRO assesses the person still carries influence or risk).',
  ]);
  p('The assessment of each PEP and the reasons for the decision are recorded on the Customer Risk Assessment Form.');
  h2('5.7 Simplified due diligence (SDD)');
  p('SDD is available only for customers the MLRO has rated Low risk in writing, only where there is no suspicion, only in coordination with MoET (Circular 6 of 2025), and always with full application of targeted financial sanctions. SDD never removes identification, verification of identity, beneficial owner identification or sanctions and PEP screening. It may only reduce, in proportion to the risk, the frequency of reviews (to every 2 years) and the amount of supporting evidence on purpose and source of funds. SDD is not applied when there is any suspicion, any sanctions or PEP hit, any high-risk jurisdiction link, or a cash transaction at or above the reporting threshold.');
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
  p('Where {{COMPANY_SHORT}} outsources any AML/CFT function (including the MLRO role, screening, record storage or CDD), it first performs due diligence on the provider, documents the provider\'s obligations in a written agreement that includes confidentiality, access for MoET and audit, and record return on termination, and reviews performance at least annually. Responsibility stays with {{COMPANY_SHORT}}.');

  // ── 6. TFS ─────────────────────────────────────────────────────────────
  h1('6. TARGETED FINANCIAL SANCTIONS (TFS)');
  h2('6.1 Obligation');
  p('{{COMPANY_SHORT}} must, without delay and without prior notice: freeze all funds and other assets owned or controlled by, or held for the benefit of, persons and entities on the UN Consolidated List and the UAE Local Terrorist List; not make funds, assets, gold or services available to them, directly or indirectly; and report to the authorities as set out in this section (Cabinet Decision No. (74) of 2020, Article 21, and the EOCN Guidance on TFS, last amended March 2026). "Without delay" means immediately and in any case within 24 hours of the designation. These duties apply at all times and to all customers, beneficial owners, agents, suppliers, counterparties and transaction parties.');
  h2('6.2 Responsibilities');
  p('The MLRO is {{COMPANY_SHORT}}\'s TFS compliance officer, and the deputy MLRO acts in the MLRO\'s absence. {{SENIOR_MANAGEMENT}} makes sure the MLRO has the tools, human and technical resources and authority to freeze and report. Staff must not tell any customer or third party that a freeze is going to be applied. All staff must stop and refer to the MLRO any actual or possible match.');
  h2('6.3 Staying informed');
  p('{{COMPANY_SHORT}} is registered with the EOCN Notification Alert System (NAS) so it receives every list update by email, and uses its "Follow TFS Instructions" feature. The registration is kept current with at least two monitored contacts. The MLRO also checks the UN website for press releases regularly (daily where the business model requires it) and keeps evidence of each notification and the action taken.');
  h2('6.4 Screening');
  p('Sanctions and terrorist-list screening applies to all customers without exception, whether the transaction is cash or non-cash and whatever its value (MoET Circular No. (3) of 2025). Screening is done immediately:');
  bullets([
    'upon any update to the UN Consolidated List or UAE Local Terrorist List (so that any freeze happens within 24 hours);',
    'before onboarding any new customer, and before any transaction with a counterparty;',
    'at each periodic KYC review and on any material change in the nature or ownership of a customer.',
  ]);
  table(['What is screened', 'Details'], [
    ['Customers and prospective customers', 'All current customers and everyone seeking to deal with {{COMPANY_SHORT}}, before any transaction or relationship. Former customers are included for 5 years after the relationship ended.'],
    ['Beneficial owners and related parties', 'Beneficial owners (natural and legal), directors, authorized signatories, agents and anyone with power of attorney, and persons known to be directly or indirectly connected with a designated person.'],
    ['Transaction parties', 'Buyers, sellers, suppliers, refiners, agents, freight forwarders, shipping and other parties named in a transaction, and payers and payees of any third-party payment (section 7.2).'],
    ['Employees', 'At hiring and periodically.'],
  ], [3000, 6400]);
  p('Lists: the UN Consolidated List and the UAE Local Terrorist List are mandatory. Others (for example OFAC, EU, UK) are screened as a risk measure (section 6.8). Screening is done with {{SCREENING_TOOL}}. The MLRO makes sure that every system holding customer or transaction data is covered by the screening, checks that the tool carries the current lists, and records when the lists were last updated. Screening is also effective on weekends and public holidays: {{COMPANY_SHORT}} has a procedure so that no assets can be accessed or used during a closure, and screening resumes from the first minute of business. Routine screening results are kept in the screening tool\'s log, which must be exportable and show the date of each list update. Every alert and its outcome is documented in the KYC module or the screening record.');
  h2('6.5 Matching and handling alerts');
  p('Any alert is passed to the MLRO immediately; the transaction or relationship is put on hold, and nothing is released while the alert is open. The MLRO compares the **key identifiers** first, and only then other identifiers such as aliases:');
  table(['Person', 'Key identifiers'], [
    ['Natural person', 'Full name, date of birth, nationality, ID or passport number, last known address.'],
    ['Entity / company', 'Name, trade license number, address of registration, address of branches.'],
  ], [2200, 7200]);
  table(['Result', 'Meaning', 'Action'], [
    ['Negative match', 'No match on the key identifiers.', 'Proceed. Record the check.'],
    ['False positive', 'A name match that, on the key identifiers, is clearly not the designated person.', 'Document the reasons and evidence in the screening record. No report is needed.'],
    ['Partial name match', 'Some key identifiers match, but the MLRO cannot conclude it is a false positive or a confirmed match.', 'Section 6.6, PNMR.'],
    ['Confirmed name match', 'All key identifiers match a designated person.', 'Section 6.6, freeze and CNMR.'],
  ], [2000, 4000, 3400]);
  h2('6.6 Confirmed and partial matches');
  bullets([
    '**Confirmed name match:** freeze without delay (within 24 hours at the latest) all funds, gold and other assets of the listed person; do not carry out any transaction or provide any service; and file a **Confirmed Name Match Report (CNMR)** through goAML within **5 business days** of the freeze (or of rejecting a transaction from a prospective customer). The report goes to EOCN and MoET. A CNMR is also filed for relationships and transactions in the previous five years, including closed accounts and where no assets are held now, stating that none are held.',
    '**Partial name match, existing customer:** suspend without delay any transaction, offer no funds, assets or services, and file a **Partial Name Match Report (PNMR)** through goAML within 5 business days of the suspension. Send all documents on the match and keep the suspension in place until EOCN instructs through goAML either to cancel it (false positive) or to freeze (confirmed).',
    '**Partial name match, prospective customer or counterparty:** first try to obtain identification documents to decide whether it is a false positive or a confirmed match. If it is a false positive, the relationship or transaction may go ahead, with the reasons documented. If the identification documents cannot be obtained within 10 business days, reject the transaction or service and file a PNMR within 5 business days of the rejection, stating that it was rejected for lack of ID documents.',
    '**Majority ownership:** an entity that a designated person owns by more than 50%, or controls, is also frozen. A designated person holding 50% or less is monitored for any increase in stake or control, and anything owed to the designated person (profits, sale proceeds) is frozen.',
    '**Maintain the freeze** until the authorities instruct otherwise; do not release, return or deal with the assets. Additions to a frozen account are allowed, but they too are frozen.',
    '**Do not alert** the customer or anyone else other than as the authorities require. Requests to use frozen funds, or any humanitarian exemption, are handled only on the written approval of the competent authority.',
  ]);
  h2('6.7 EOCN alerts and notifications');
  p('When EOCN issues a notification asking {{COMPANY_SHORT}} to check its records against newly listed or amended names, or to confirm the outcome of a search (for example through a unique survey link):');
  bullets([
    'the MLRO receives the notification, records the date and time, and checks the deadline;',
    'the MLRO searches the complete customer, former customer, beneficial owner, supplier and counterparty records, using {{SCREENING_TOOL}} and the company\'s own client list, for every name in the notification, including variants and transliterations;',
    'the MLRO keeps evidence of the search (dated screenshot or export showing the names searched and the result);',
    'only after the search is complete does the MLRO answer EOCN. "No match" is given only if nothing was found. If a possible or confirmed match exists, the MLRO answers accordingly, freezes and reports under section 6.6. Nobody else may answer on {{COMPANY_SHORT}}\'s behalf, and the answer must never be given before the search is done;',
    'the MLRO keeps the confirmation of submission (for example the survey completion page) together with the search evidence.',
  ]);
  h2('6.8 Other sanctions regimes and sanctions evasion');
  p('Sanctions imposed by other jurisdictions (for example the US, EU and UK) and criminal watch-lists are screened as a risk measure because they affect banking, shipment and insurance, but **CNMR and PNMR are for the UN and UAE Local Terrorist Lists only**. A match with any other list is escalated to the MLRO and Senior Management, who consult MoET and consider an STR/SAR to the FIU. Suspected sanctions evasion, including a customer named in a UN Panel of Experts report but not listed, is reported to the FIU as an STR/SAR, using the sanctions-evasion reasons for reporting in goAML. The MLRO must be able to tell which reports are CNMR/PNMR and which are STR/SAR. Proliferation-related risk, including the UN sanctions on Iran (re-imposed under UNSCR 1737 (2006), MoET Circular No. (7) of 2025) and on the DPRK, is assessed in the business risk assessment.');
  h2('6.9 Records and training');
  p('All TFS records (screening results of every kind, alerts, decisions, freezes, reports, EOCN notifications and responses) are kept for at least {{RETENTION_YEARS}} years and never less than five. TFS is part of induction and annual training for all staff, and of additional training for the MLRO, Senior Management and anyone who screens or onboards customers. {{COMPANY_SHORT}} cooperates with EOCN and MoET in verifying the accuracy of any report.');

  // ── 7. Gold-sector controls ───────────────────────────────────────────
  h1('7. CONTROLS SPECIFIC TO THE GOLD AND PRECIOUS METALS TRADE');
  h2('7.1 Cash transactions and DPMSR reporting');
  p('The AED 55,000 threshold is a minimum legal trigger, not a safe level. Below it, {{COMPANY_SHORT}} still applies the risk-based controls in this Manual.');
  bullets([
    'Every cash transaction is recorded with the customer\'s identity, the amount, the gold bought or sold, the payment method and the source of funds. For cash, staff record as much detail as possible, including banknote denominations and serial numbers.',
    'A **DPMSR** is filed through goAML for: cash transactions of AED 55,000 or more (or the foreign-currency equivalent) with resident individuals; cash transactions of AED 55,000 or more with non-resident individuals; and transactions of AED 55,000 or more with companies and other entities, **in cash or by wire transfer**. The obligation applies even if CDD is complete and there are no red flags. If the transaction is also suspicious, the DPMSR and the STR/SAR are both filed.',
    'For a resident individual the Emirates ID or passport is obtained; for a non-resident, the passport or ID; for a company, a copy of the trade license and the ID (Emirates ID or passport) of its representative. Records are kept for the period in section 10 [VERIFY: DPMSR filing deadline in the current goAML/FIU guidance].',
    'Linked transactions, installments, deposits, cash equivalents and trade-ins are added together. Splitting one purchase over separate invoices does not avoid the threshold. Staff must not split a transaction or advise a customer to do so. Apparent structuring is escalated to the MLRO as an Internal SAR even if no single transaction reaches the threshold.',
    'Cash is counted, receipted and banked as soon as practicable.',
  ]);
  h2('7.2 Payments');
  bullets([
    'Payments are accepted only from, and made only to, the customer or supplier named in the transaction, from an account in their own name.',
    'The payment method must be consistent with the customer\'s profile. Hard-to-trace instruments (cashier\'s or traveler\'s checks, postal money orders, prepaid cards, third-party endorsed checks, cryptocurrencies, IOUs and promissory notes) are not accepted unless the MLRO approves in advance.',
    'A payment from or to a third party is not accepted unless the MLRO approves it in advance, after establishing the reason, the identity of the third party and its relationship to the customer, and screening the third party. Where fees are collected for services (refining, consignment), funds must come from the known customer, not from third parties or unknown foreign accounts.',
    'Payment to a different account from the one the customer paid from, or to a country unrelated to the transaction, is treated as a red flag.',
    'Virtual assets are not accepted unless Senior Management has approved the channel in writing, with controls in place.',
  ]);
  h2('7.3 Buying gold from the public (scrap, jewelry and second-hand gold)');
  bullets([
    'The seller is identified, verified and screened for every purchase, whatever the value, and a purchase record is kept (seller ID, date, description, weight, purity, price, payment method).',
    'The seller states, and where possible shows evidence of, how they came to own the gold. Unverifiable "scrap" claims, bulk or recurrent sales of scrap, scrap volumes that exceed what a retail or personal source would produce, gold without a plausible origin, damaged or defaced marks, or items that appear new are treated as high risk: Enhanced Due Diligence and escalation to the MLRO.',
    'Items showing signs of theft or tampering are not bought. Payments follow section 7.2.',
  ]);
  h2('7.4 Suppliers, refiners and responsible sourcing');
  p('Under Ministerial Decree No. (68) of 2024, precious metals dealers and other participants in the supply chain must apply the first three steps of the Ministry\'s due diligence regulations for responsible sourcing of gold. Refiners and recyclers must also appoint an independent third-party auditor and submit an annual due diligence report. {{COMPANY_SHORT}} applies steps 1 to 3 to every supplier and upstream counterparty before first dealing and periodically:');
  bullets([
    ['**Step 1: Management systems:** this Manual is the company-specific policy on due diligence for gold supply chains; the MLRO is responsible; records are kept; staff are trained.', null],
    ['**Step 2: Identify and assess risks:** for each supplier and consignment record the supplier\'s identity, ownership and beneficial owners (CDD as for a customer), license and regulatory status, the type of gold (mined, recycled/scrap, refined, jewelry), the country of origin and transit, and whether any conflict-affected or high-risk area (CAHRA) is involved. Obtain and check the supporting documents: invoices, packing lists, assay and refiner certificates, import/export and customs documents, transport and insurance documents. Check the plausibility of origin and reconcile weight, purity, value and parties. For rough diamonds, verify the Kimberley Process certificate.', null],
    ['**Step 3: Manage and mitigate:** accept, suspend or refuse; apply EDD for higher-risk suppliers; reject incomplete or unverifiable documents; do not deal with any supplier linked to sanctions, serious human rights abuses, funding of armed groups, or illicit trade. Document each decision.', null],
    ['If {{COMPANY_SHORT}} also operates as a refiner or recycler, it additionally appoints an independent third-party auditor and files the annual due diligence report with MoET. Otherwise, supplier due diligence is included in the annual independent review (section 3.5).', null],
  ]);
  p('Suppliers are rated Low, Medium or High risk and reviewed on the same schedule as customers (section 5.9). Gold of all kinds must also meet the precious metals stamping and trade-monitoring rules in Federal Law No. (11) of 2015.');
  h2('7.5 Conflict-affected and high-risk areas (CAHRA)');
  p('The UAE does not publish a list of conflict-affected and high-risk areas, and neither does the OECD. MoET\'s Due Diligence Regulations for Responsible Sourcing of Gold (Version 1, August 2022) define CAHRAs as areas of armed conflict, widespread violence (including violence by criminal networks), or other serious and widespread harm to people, and high-risk areas as those with a high risk of conflict or of widespread or serious abuses, as described in Annex II of the OECD Due Diligence Guidance. They are often marked by political instability or repression, institutional weakness, insecurity, collapse of civil infrastructure, and widespread violations of national or international law. Gold or stones that originate in, are routed through, or are linked to a CAHRA need enhanced scrutiny. **{{COMPANY_SHORT}} therefore decides for itself, and records, which areas it treats as CAHRAs (Appendix F).**');
  h3('Assessment of each supply chain');
  bullets([
    'The assessment is made for **each supply chain** (each consignment or batch), not once for the supplier, and is repeated even if earlier supplies from that supplier were clean or showed no high-risk element.',
    'Collect, for each supply: the country and place of origin and the type of source (large-scale mine, artisanal and small-scale mining (ASM), recycled or scrap, mixed); the transport and transit route; where and by whom it was refined; and the upstream suppliers as far as can be established.',
    'Use KYC and the red flags below to decide whether the gold was mined, transported or traded in a CAHRA. Record the conclusion and the evidence in Appendix F.',
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
  p('For ASM gold also check whether the supplier, processing plant, trader or exporter is legitimate: legally registered, cooperative-based or government-recognized, or part of a government or central-bank supported initiative, and whether the mining follows recognized standards and good practice.');
  h3('Outcome and action');
  table(['Finding', 'Action'], [
    ['No CAHRA link and no red flags, on the information collected', 'Record the reasoning. Standard supplier CDD and monitoring apply; no further enhanced due diligence on that supply chain.'],
    ['Red flags, or origin or route information is missing', 'Enhanced due diligence: obtain the missing evidence, corroborate origin and route with independent documents, and escalate to the MLRO. Do not accept the gold until the MLRO has cleared it.'],
    ['CAHRA link confirmed, or red flags cannot be resolved', 'Suspend or refuse the supply chain. Any exception needs Senior Management approval and a written risk-mitigation plan with monitoring. The MLRO considers an STR/SAR, and reviews the supplier relationship.'],
  ], [3300, 6100]);
  p('The MLRO keeps the CAHRA register in Appendix F and reviews it at least quarterly, and whenever circumstances change. Inputs include: UN Security Council and UAE sanctions regimes; the FATF and National Committee lists (Appendix C); MoET circulars and guidance; and reputable government and intergovernmental reporting on conflict, illicit gold and human rights abuse. Records of each assessment are kept under section 10.');
  h2('7.6 Monitoring and record details');
  bullets([
    'For covered transactions, keep the certificate numbers and identifying characteristics of the metals or stones involved (weight, purity or quality, color, shape, cut, markings).',
    'For items held in safekeeping for a customer or partner, keep the same records and watch for unusual changes or substitutions.',
    'Wholesale and trading flows: watch for price deviations from the market, mismatches between weight, purity and documents, and circular trading.',
    'Retail: monitor cumulative totals per customer, and record cash denominations and serial numbers.',
  ]);
  h2('7.7 Trade and shipment red flags');
  p('The MLRO assesses and escalates: mismatch between the quantity, purity, value or origin of the gold and the documents; prices well above or below market; unnecessary intermediaries or routing through jurisdictions unrelated to the transaction; unexplained changes of consignee or payee; sanctioned or high-risk vessels, ports, routes or end users; and dual-use goods in the customer\'s other business. See Appendix B.');
  h2('7.8 Data disclosure notice');
  p('The MoET Data Disclosure Notice (Circular No. (2) of 2023) is displayed visibly on {{COMPANY_SHORT}}\'s premises so that customers are told they must disclose their data when dealing with the company. The MLRO checks each branch, and replaces the notice if it is lost or updated.');
  h2('7.9 Refusing or ending a transaction');
  p('Staff may refuse any transaction they are not comfortable with. A refusal and its reason are recorded and passed to the MLRO, who decides whether it is also reportable. Staff do not explain to the customer that a suspicion exists. {{COMPANY_SHORT}} refuses or exits any relationship where beneficial ownership, source of funds or origin of the gold cannot be reasonably verified, or where sanctions or PF risks cannot be mitigated.');
};
