// Sections 3 to 5: management systems (step 1), risk assessment (step 2), risk management (step 3)
module.exports = (L) => {
  const { h1, h2, h3, p, bullets, table, note } = L;

  // ── 3. Step 1 ──────────────────────────────────────────────────────────
  h1('3. STEP 1: GOVERNANCE AND MANAGEMENT SYSTEMS');
  h2('3.1 Roles and responsibilities');
  table(['Role', 'Responsibilities'], [
    ['Senior Management', 'Provides oversight of supply chain due diligence and its results; makes sure the Compliance Officer\'s duties cover gold supply chain due diligence; provides people, tools and budget; delegates authority to competent staff; approves this policy, the risk control plans for suppliers and any exception; receives the quarterly risk report (section 5.5); and decides, on the Compliance Officer\'s recommendation, whether to restore or end a suspended relationship. Senior Management gains the knowledge it needs, using external advisers where necessary.'],
    ['Compliance Officer (MLRO)', 'A senior person who reports directly to Senior Management and has direct access to it. Manages the supply chain due diligence process; monitors it; keeps this policy and its procedures current; runs the training and awareness program; screens suppliers; investigates red flags; maintains the supplier files and the CAHRA register; reports to Senior Management; files reports with the FIU; and cooperates with MoET and the FIU, giving them the records and access they need. Contact: {{MLRO_NAME}}, {{MLRO_EMAIL}}, {{MLRO_PHONE}}.'],
    ['Procurement, operations and finance staff', 'Collect the supplier documents; apply the procedures before any purchase; refer red flags and unusual requests to the Compliance Officer at once; never accept gold before approval; and complete the training in section 7.'],
    ['All employees', 'Follow this policy and the AML Manual, report concerns, and never conceal or alter origin information.'],
  ], [2600, 6800]);
  h2('3.2 Fit and proper checks');
  p('{{COMPANY_SHORT}} carries out a fit-and-proper assessment and KYC checks on the Compliance Officer, and on the other employees involved in procurement, when they are recruited and every year after that. Records are kept.');
  h2('3.3 Know your supplier');
  p('Before buying from or selling to any supplier, {{COMPANY_SHORT}} identifies and verifies it under section 5 of the AML Manual and screens it under section 6 of the AML Manual ({{SCREENING_TOOL}}). It carries out due diligence on the immediate counterparty in every case, and, when any risk is identified, extends it back toward the origin of the gold. The information collected is:');
  table(['Supplier type', 'Information and documents'], [
    ['Natural person', 'Name as on the ID or travel document, nationality, address and a copy of the valid ID or travel document. Senior Management approval if the supplier or any beneficial owner is a PEP.'],
    ['Company or legal arrangement', 'Name; legal form; memorandum of association or equivalent; commercial license; country of incorporation; countries of operation; head office or principal place of business; names of senior managers and authorized representatives, with authorization letters; and the beneficial owners holding 25% or more, directly or indirectly. Where a listed company or a subsidiary of a listed holding company is involved, beneficial owners need not be identified if the information is available from reliable sources.'],
    ['A person acting for the supplier', 'Official authorization (for example a power of attorney) and identification of that person.'],
    ['Artisanal and small-scale mining (ASM) gold', 'In addition: identification and verification of the local exporter (export licenses); mine location; the mine\'s legal existence and conformity to the legal framework, or its willingness to formalize.'],
  ], [2700, 6700]);
  p('If {{COMPANY_SHORT}} cannot complete the due diligence on a supplier, it does not start or continue the relationship or carry out the transaction, and the Compliance Officer considers an STR/SAR (AML Manual section 5.10).');
  h2('3.4 Chain of custody and records inventory');
  p('{{COMPANY_SHORT}} documents its due diligence systematically, so that the whole chain of custody of every gold transaction can be seen. For each input and output of gold the record inventory includes at least:');
  bullets([
    'the form, type and physical description of the gold or gold-bearing material, and the weight and assay provided by the supplier and confirmed on receipt;',
    'proof of origin: for mined gold, an official certificate of origin or equivalent, with the invoice and packing list;',
    'the supplier\'s KYC information, including beneficial owners of 25% or more;',
    'a unique reference number for each input and output, and the dates of input, output, purchase and sale;',
    'the shipping and transport documents (such as waybill or airway bill, pro forma invoice, bill of lading) that establish the chain of custody, and the date of arrival and of final assay;',
    'the financial details: amount, payment method, currency and banking information.',
  ]);
  p('Records are kept, preferably in a computerized database, organized so that data analysis and tracking of financial transactions are possible. They are kept for {{RETENTION_YEARS}} years and never less than five, from completion of the transaction or end of the relationship, or longer where the AML Manual (section 10) requires. They are available to MoET, the FIU and law enforcement immediately on request.');
  h2('3.5 Payments');
  p('{{COMPANY_SHORT}} makes and receives payments for gold through official banking channels wherever possible. Unavoidable cash transactions need proper verification of the origin of the cash, and are reported through goAML where required (AML Manual section 7.1). Third-party payments follow AML Manual section 7.2.');
  h2('3.6 Security of shipments');
  p('For material from large-scale or artisanal and small-scale mining, and for any shipment where a red flag exists:');
  bullets([
    'each shipment arrives in an identifiable sealed security box, to prevent tampering or removal of the contents;',
    'different shipments are physically segregated until verification is complete and confirmed against the records in section 3.4;',
    'any inconsistency is reported to the Compliance Officer or Senior Management at once;',
    'whoever assesses the shipment (weighing, assay, inspection) has no conflict of interest; and',
    'where applicable, the supplier\'s participation in the EITI is checked.',
  ]);
  h2('3.7 Supplier engagement');
  bullets([
    '{{COMPANY_SHORT}} aims for long-term relationships with suppliers and makes it clear what it expects of them on responsible sourcing from conflict-affected and high-risk areas.',
    'Every supplier commits to this policy, or to its own policy that is consistent with it and with Annex II of the OECD Guidance, in the commitment and contract clause in Appendix A. The clause is written into the commercial contract or written agreement so that it is legally binding.',
    'Suppliers receive the AML/CFT legislation, the MoET Regulations and the OECD Guidance with its Supplement on Gold.',
    '{{COMPANY_SHORT}} supports suppliers with information and capacity building to improve their supply chain practices (section 7.2).',
  ]);
  h2('3.8 Grievance mechanism');
  p('{{COMPANY_SHORT}} maintains a confidential grievance mechanism through which employees and other stakeholders in the supply chain can raise concerns about sourcing or trading gold from a CAHRA, or any misconduct. Contact: {{GRIEVANCE_CONTACT}}. The mechanism:');
  bullets([
    'allows reports to be made securely, protecting the reporter\'s identity and protecting them from criminal and administrative liability, and without fear of reprisal;',
    'works as a warning system in addition to {{COMPANY_SHORT}}\'s own risk assessments;',
    'is made known to all relevant parties through this policy, the website ({{WEBSITE}}) and other communication channels;',
    'has each concern evaluated independently of anyone with an interest, and treated fairly, with documented procedures for telling stakeholders the status;',
    'feeds into the risk assessment and the improvement of supply chain controls. A concern about the Compliance Officer goes to Senior Management, and a concern about Senior Management goes to the Compliance Officer, who may report it to MoET or the FIU.',
  ]);
  h2('3.9 Cooperation with authorities');
  p('{{COMPANY_SHORT}} cooperates fully with MoET, the FIU and law enforcement agencies on gold transactions, and gives them complete information on every shipment and transaction.');

  // ── 4. Step 2 ──────────────────────────────────────────────────────────
  h1('4. STEP 2: IDENTIFYING AND ASSESSING SUPPLY CHAIN RISK');
  h2('4.1 Due diligence on each supply chain');
  p('Due diligence is carried out before a new supplier relationship starts, and on an ongoing basis. It is carried out on **each supply chain, meaning each consignment or batch, and not only on the supplier**, and is repeated even if earlier supplies were clean. Information is gathered for each supply and recorded in the CAHRA assessment record in the AML Manual (Appendix F). If, on the information collected, {{COMPANY_SHORT}} can reasonably conclude that the gold was not mined, transported or traded in a CAHRA and no red flag exists, no further due diligence is needed on that supply chain beyond the AML Manual measures, and the reasoning is recorded.');
  h2('4.2 Determining the origin of the gold');
  bullets([
    '**Mined gold:** the origin is the mine, except for by-product gold (for example from copper mining). The Compliance Officer checks for by-product gold falsely presented as mined gold.',
    '**Recycled gold:** the origin is the point at which it became recyclable, such as when first sold back to a recycler or refiner. The Compliance Officer checks for newly mined gold falsely presented as recycled.',
    '**Grandfathered stock:** if a verifiable date before 1 January 2012 is shown, no origin determination is needed. If red flags for money laundering or sanctions appear, the supply chain is examined further.',
    '**ASM gold:** additionally check whether the mine is a legitimate ASM operation (legally registered, cooperative based or government recognized, or part of a government or central bank supported initiative), who the beneficial owners and controllers are, and whether ethical and environmental standards are followed.',
    'Origin is established from evidence, collected through the supplier and through {{COMPANY_SHORT}}\'s own research, and not on the supplier\'s word alone.',
  ]);
  h2('4.3 Risk factors');
  table(['Category', 'Factors considered'], [
    ['Counterparty', 'The supplier\'s KYC information, including origin and transport of the gold; red flags found; the number of parties in the chain; the extent and effectiveness of the counterparty\'s own due diligence, and whether it has been independently audited; its conformity with the OECD Guidance; how long it has been established; complex ownership layers or trusts that appear to give anonymity; size of mining operations (ASM or LSM); involvement of PEPs; and sanctions and adverse media findings.'],
    ['Geographic', 'The AML/CFT and regulatory environment where the chain operates; levels of conflict, human rights abuse, bribery and corruption, and organized crime; access to nearby CAHRA markets or processing; enforcement of laws against significant crime; payment mechanisms (formal banking or not); UN and UAE sanctions and embargoes; and involvement of CAHRA countries (AML Manual section 7.5 and Appendix F; high-risk jurisdictions in AML Manual Appendix C).'],
    ['Transaction', 'Transactions inconsistent with local or market practice (amount, quality, profit); volumes or types that differ from earlier shipments; excessive cash; structuring to stay under thresholds; gold that cannot reasonably be reconciled with the declared origin; unexplained geographic distance in the chain.'],
    ['Product', 'The nature of the gold: ASM or LSM, by-product, melted or unprocessed recycled gold; the concentration of gold in the material.'],
    ['Delivery channel', 'Physical delivery to unrelated third parties that is not normal business practice; courier and transport risks, including physical security and tamper evidence; the reliability and KYC of transport companies.'],
  ], [2200, 7200]);
  h2('4.4 Conflict-affected and high-risk areas');
  p('The UAE does not publish a list of CAHRAs. {{COMPANY_SHORT}} keeps its own CAHRA register, starting from the European Union\'s indicative list and adding its own assessments, and follows the assessment steps and outcomes in section 7.5 of the AML Manual. A supply chain with a CAHRA link, or with unresolved red flags, is treated as high risk.');
  h2('4.5 Red flags');
  p('Any one of the following calls for enhanced due diligence before {{COMPANY_SHORT}} engages with the supplier or accepts the gold:');
  h3('Location');
  bullets([
    'the gold comes from, or has been transported through, a CAHRA or a country subject to international sanctions;',
    'the gold comes from a country with limited known reserves or production that does not match the volume offered;',
    'the gold comes from a country through which CAHRA gold is known or reasonably suspected to transit;',
    'gold described as recycled, scrap or mixed has been refined in such a transit country;',
    'the gold has come from or through countries with weak oversight of money laundering, corruption, bribery, informal banking and cash-based economies.',
  ]);
  h3('Supplier');
  bullets([
    'the supplier or its upstream entities operate in a red-flag location, or hold interests in suppliers from one;',
    'the supplier is known to have sourced gold from such a location in the last 12 months;',
    'inconsistent KYC information, or refusal to provide the documents requested;',
    'the supplier or a beneficial owner is listed by a government for money laundering, fraud or terrorism, or under international sanctions;',
    'the supplier has no ethics, integrity, anti-money-laundering, anti-bribery and anti-corruption policies;',
    'the supplier cannot show an effective assessment of its own counterparties and upstream supply chain.',
  ]);
  h3('Transaction and documents');
  bullets([
    'unusual, unconventional or suspicious payment methods: excessive cash, structured payments, payments from unrelated third parties, unclear financial arrangements;',
    'missing documents or origin records that cannot be verified; inconsistent paperwork; alterations or forgery; discrepancies in assay certificates, shipping documents or financial records;',
    'weight, purity or value that do not match the documents, or prices well above or below market;',
    'unnecessary intermediaries or routing through countries unrelated to the transaction; unexplained changes of consignee or payee.',
  ]);
  p('When a red flag appears, the Compliance Officer starts a documented response to clarify and mitigate the risk before the transaction goes ahead. The AML Manual (Appendix B) lists further red flags for money laundering.');
  h2('4.6 Supplier risk rating');
  p('The Compliance Officer rates each supplier and each supply chain Low, Medium or High, using sections 4.3 to 4.5, and records the reasons. Ratings follow the AML Manual: review at least every two years for Low risk, every year for Medium, and every six months for High, and immediately when circumstances change (AML Manual section 5.9).');
  h2('4.7 Enhanced due diligence for high-risk supply chains');
  p('Enhanced due diligence (EDD) is carried out before engaging with a supplier where a high-risk element, a red flag, or unknown information exists, and continues for as long as the relationship lasts, **at least every six months** [VERIFY: the MoET Regulations say "bi-annual"; this policy applies the stricter reading of twice a year]. It consists of:');
  bullets([
    '**Site visits:** visits to the supplier, individually or jointly with other parties or through an industry mechanism, using competent, qualified and independent assessors, to confirm the documented KYC information. {{COMPANY_SHORT}} remains responsible for the result whoever carries out the visit.',
    '**Desk-based review:** independent verification of each party in the chain from operating licenses and registers, of owners and controllers (25% and above), of mines of origin, routes and trading points, and of processing and transport methods; screening of names and owners through sanctions and adverse media lists (at a minimum the UN lists and the UAE Local Terrorist List); and a check for links to governments, political parties, the military, criminal networks, non-state armed groups, or public or private security forces.',
    '**Sample transaction review:** verification of sample documents for transactions the supplier carried out, including payments of taxes, fees and royalties, and comparison of declared production and processing capacity with output.',
    'For ASM and LSM gold, the additional information in section 4.2, and the KYC of the exporter, international traders, and third-party providers of logistics, processing, transport or security.',
  ]);
  p('The findings are documented. Senior Management approval is needed to start or continue a high-risk relationship (AML Manual section 5.8).');

  // ── 5. Step 3 ──────────────────────────────────────────────────────────
  h1('5. STEP 3: MANAGING SUPPLY CHAIN RISK');
  h2('5.1 Risk strategy');
  p('{{COMPANY_SHORT}} decides, on the evidence from section 4, and records the decision:');
  table(['Decision', 'When', 'What happens'], [
    ['Establish or continue', 'The supplier is managing the risks to a reasonable extent, or no risk was found.', 'Low risk: normal engagement with regular oversight. Medium or manageable risk: enhanced monitoring and a risk control plan with remedial actions, agreed with the supplier, measured by quantitative or qualitative indicators, and approved by Senior Management and the Compliance Officer. Significant improvement is expected within six months of adopting the plan.'],
    ['Suspend', 'EDD finds a founded suspicion of money laundering, terrorist financing, human rights abuse, environmental degradation, support to illegitimate non-state armed groups, or fraudulent misrepresentation of origin; or the supplier does not meet the plan.', 'No purchases from that supply chain until risk mitigation is adequately completed. Where mitigation fails within six months, {{COMPANY_SHORT}} disengages for at least three months, or entirely if the controls are not feasible. Suspended relationships go to Senior Management, which decides whether to restore or end them on the Compliance Officer\'s recommendation.'],
    ['Terminate', 'Money laundering, terrorist financing, human rights abuse or support to armed conflict is identified.', 'The relationship ends immediately, and the Compliance Officer files the appropriate report with the FIU (section 5.6).'],
  ], [1800, 3300, 4300]);
  p('{{COMPANY_SHORT}} takes the social and economic impact of withdrawing from a supplier into account where it can, but this never overrides the commitments in section 2. {{COMPANY_SHORT}} has zero tolerance for suppliers involved in severe ethical breaches, human rights violations or significant legal non-compliance.');
  h2('5.2 Risk control plan');
  p('Where the decision is to establish or continue, or to suspend, a risk control plan is adopted. At a minimum it contains:');
  bullets([
    'reporting of the identified risks to Senior Management;',
    'closer engagement with the supplier, including a chain of custody or traceability system, where a red flag has been identified;',
    'stronger physical security, and physical segregation and security of shipments where a red flag exists (section 3.6);',
    'an agreement with the supplier that lets {{COMPANY_SHORT}} get timely and accurate information on the risky parts of the chain;',
    'the disengagement rule in section 5.1 where the supplier fails to comply within six months;',
    'regular review of the results of the mitigation measures, and further fact finding and risk assessment after any change of circumstances.',
  ]);
  p('Suppliers are told what is required and, where useful, trained (section 7.2).');
  h2('5.3 Continuous monitoring');
  p('Supply chain due diligence is a continuing process. After each risk control plan the Compliance Officer decides whether step 2 has to be repeated or more measures are needed. A change in the chain (new origin, new intermediary, new transport route, new volumes) means repeating the relevant checks. Transactions are monitored against the supplier profile as the AML Manual requires, with the Compliance Officer reviewing transaction records at least monthly. Internal reviews and the independent review in section 6 test that the system works.');
  h2('5.4 Documents the Compliance Officer prepares');
  p('A risk assessment and a record of each decision (continue, suspend, terminate) are kept for every supplier and every high-risk supply chain, so that a regulator or an independent reviewer can follow how each decision was reached.');
  h2('5.5 Reporting to Senior Management');
  p('The Compliance Officer reports to Senior Management **at least every three months** on the risks identified in the supply chains, the suppliers rated high risk and their risk control plans, decisions to suspend or terminate, grievances received, and training delivered. Senior Management records its observations and decisions.');
  h2('5.6 Reports to the authorities');
  bullets([
    'A suspicion of money laundering, terrorist financing or related crime, or the termination of a supplier for such reasons, is reported to the FIU through goAML as an STR/SAR (AML Manual section 8).',
    'Cash and other transactions at or above the reporting threshold are reported through a DPMSR (AML Manual section 7.1).',
    'A confirmed or partial name match with the UN or UAE Local Terrorist List is frozen or suspended and reported under AML Manual section 6.',
    '{{COMPANY_SHORT}} does not tip off the supplier or anyone else that a report has been made.',
  ]);
};
