// Supply Chain Policy for Responsible Sourcing of Gold (bullion traders): front matter, sections 1 and 2
const { Paragraph, TextRun, AlignmentType, PageBreak, TableOfContents } = require('docx');

module.exports = (L) => {
  const { add, runs, h1, h2, h3, p, bullets, table, note, NAVY } = L;

  // ── Template guide (removed from each client's copy) ──────────────────
  add(new Paragraph({ spacing: { after: 160 }, children: [new TextRun({ text: 'TEMPLATE GUIDE: delete this page before issue', bold: true, size: 28, color: NAVY })] }));
  p('This is the master template of the Supply Chain Policy for Responsible Sourcing of Gold, written for a UAE gold and bullion trader supervised by the Ministry of Economy and Tourism (MoET). It follows the MoET Due Diligence Regulations for Responsible Sourcing of Gold and the OECD model supply chain policy. Replace every highlighted placeholder with the company\'s own details. Nothing else in the text is company specific.');
  table(['Placeholder', 'Replace with', 'Example'], [
    ['{{COMPANY_NAME}}', 'Full legal name, as on the trade license', 'Example Gold Trading LLC'],
    ['{{COMPANY_SHORT}}', 'Short name used throughout the text', 'Example Gold'],
    ['{{TRADE_LICENSE_NO}}', 'Trade license number', '123456'],
    ['{{SENIOR_MANAGEMENT}}', 'Who holds senior management and board authority', 'the Managing Director'],
    ['{{MLRO_NAME}} / {{MLRO_EMAIL}} / {{MLRO_PHONE}}', 'The Compliance Officer (MLRO), who also runs supply chain due diligence', 'Name, email, phone'],
    ['{{SCREENING_TOOL}}', 'Sanctions, PEP and adverse media screening tool in use', 'Name of tool'],
    ['{{RETENTION_YEARS}}', 'Record retention period in years (not less than five)', '5'],
    ['{{WEBSITE}}', 'Website where this policy and the grievance mechanism are published', 'www.example.ae'],
    ['{{GRIEVANCE_CONTACT}}', 'Confidential channel for concerns (email, hotline or form)', 'concerns@example.ae'],
    ['{{VERSION}} / {{ISSUE_DATE}} / {{APPROVER}}', 'Document control', '1.0 / 1 January 2027 / Managing Director'],
  ], [3300, 3900, 2200]);
  p('Record each update in the log in section 1.6. This policy works together with the company\'s AML/CFT/CPF and Sanctions Manual, which it refers to by section number.');
  p('End of template guide.');

  // ── Cover ──────────────────────────────────────────────────────────────
  add(new Paragraph({ children: [new PageBreak()] }));
  for (let i = 0; i < 5; i++) add(new Paragraph({ children: [] }));
  add(new Paragraph({ spacing: { after: 120 }, children: [new TextRun({ text: 'SUPPLY CHAIN POLICY FOR RESPONSIBLE SOURCING OF GOLD', bold: true, size: 40, color: NAVY })] }));
  add(new Paragraph({ spacing: { after: 360 }, children: [new TextRun({ text: 'Policy and Procedures for Gold and Bullion Traders', size: 30, color: NAVY })] }));
  add(new Paragraph({ spacing: { after: 120 }, children: runs('{{COMPANY_NAME}}', { size: 32, bold: true }) }));
  add(new Paragraph({ spacing: { after: 360 }, children: runs('Dealer in Precious Metals and Stones, Trade License No. {{TRADE_LICENSE_NO}}   |   Supervisor: Ministry of Economy and Tourism', { size: 22 }) }));
  add(new Paragraph({ spacing: { after: 60 }, children: runs('Version {{VERSION}}', { size: 24 }) }));
  add(new Paragraph({ spacing: { after: 600 }, children: runs('Issued {{ISSUE_DATE}}', { size: 24 }) }));
  add(new Paragraph({ children: runs('CONFIDENTIAL: for internal use by {{COMPANY_SHORT}} staff and its regulators. Section 8.3 describes the parts that are published.', { size: 20, italics: true }) }));

  add(new Paragraph({ children: [new PageBreak()] }));
  add(new Paragraph({ spacing: { after: 160 }, children: [new TextRun({ text: 'TABLE OF CONTENTS', bold: true, size: 30, color: NAVY })] }));
  add(new TableOfContents('Contents', { hyperlink: true, headingStyleRange: '1-2' }));

  // ── 1. Introduction ────────────────────────────────────────────────────
  h1('1. INTRODUCTION');
  h2('1.1 Purpose and scope');
  p('This policy sets out how {{COMPANY_NAME}} ("{{COMPANY_SHORT}}") buys, sells, transports and handles gold and other precious metals responsibly. It identifies, assesses and manages the risks that gold can carry: financing of conflict, money laundering, terrorist and proliferation financing, corruption, serious human rights abuses, child and forced labor, and environmental harm.');
  p('{{COMPANY_SHORT}} is a trader in gold and bullion. Under Ministerial Decree No. (68) of 2024, precious metals dealers must apply the first three steps of the MoET Due Diligence Regulations for Responsible Sourcing of Gold: (1) establish strong company management systems and a company-specific policy, (2) identify and assess risks in the gold supply chain, and (3) design and implement a strategy to respond to the risks identified. Section 3 covers step 1, section 4 step 2 and section 5 step 3. The independent audit and annual report (steps 4 and 5) are required of refiners and recyclers, and section 6 explains how they apply to {{COMPANY_SHORT}}.');
  p('The policy covers every gold supply chain {{COMPANY_SHORT}} deals in, whether it buys mined gold, recycled or scrap gold, jewelry, or refined bars and coins, and whether the counterparty is a supplier, a refiner, a customer selling gold to {{COMPANY_SHORT}}, or an intermediary.');
  p('By adopting it, {{COMPANY_SHORT}} recognizes the risk of significant adverse impacts connected with extracting, trading, handling and exporting minerals from conflict-affected and high-risk areas, and its responsibility to respect human rights and not contribute to conflict. {{SENIOR_MANAGEMENT}} approved this policy and commits {{COMPANY_SHORT}} to adopt it, make it widely known, and write it into its agreements with suppliers.');
  h2('1.2 Who this policy applies to');
  p('It applies to the owners, {{SENIOR_MANAGEMENT}} and all employees of {{COMPANY_SHORT}}, and to its suppliers, agents, contractors, transporters and other counterparties who take part in procuring, refining, processing, transporting, storing or selling the gold it deals in. Suppliers are bound through the commitment and contract clause in Appendix A. A breach can lead to corrective action, suspension or termination of the relationship, and, for staff, disciplinary action.');
  h2('1.3 Legal and regulatory framework');
  p('The policy is based on the following. The Compliance Officer keeps the list current (section 8) and records each change in section 1.6.');
  bullets([
    'Federal Decree-Law No. (10) of 2025 on Anti-Money Laundering, Combating the Financing of Terrorism and Proliferation Financing, and Cabinet Resolution No. (134) of 2025 (its Executive Regulations).',
    'Cabinet Decision No. (74) of 2020 on the Terrorism Lists Regulation and implementation of UN Security Council resolutions (targeted financial sanctions).',
    'Ministerial Decree No. (68) of 2024 on adherence to the Due Diligence Regulations for Responsible Sourcing of Gold.',
    'The MoET Due Diligence Regulations for Responsible Sourcing of Gold, Version 1 (August 2022), based on the OECD Due Diligence Guidance for Responsible Supply Chains of Minerals from Conflict-Affected and High-Risk Areas and its Supplement on Gold.',
    'MoET Guidelines for Designated Non-Financial Businesses and Professions and the Supplemental Guidance for Dealers in Precious Metals and Stones (March 2026), and MoET Circular No. 08/AML/2021 on DPMSR reporting.',
    'Federal Law No. (11) of 2015 on Monitoring Trade in Precious Metals and its Stamping, and the customs and export laws that apply to the import, export and transit of gold.',
  ]);
  h2('1.4 How this policy fits with the AML manual');
  p('This policy is part of {{COMPANY_SHORT}}\'s AML/CFT/CPF framework and does not replace it. The AML/CFT/CPF and Sanctions Manual ("the AML Manual") governs customer and supplier due diligence, sanctions screening and freezing, suspicious activity reporting, cash and DPMSR reporting, record keeping and training. Where this policy refers to the AML Manual by section number, the AML Manual applies in full. If the two ever differ, the stricter requirement applies.');
  h2('1.5 Definitions');
  table(['Term', 'Meaning'], [
    ['Supply chain', 'All the activities, parties, information and services involved in moving gold from its source to the end customer.'],
    ['Upstream supply chain', 'The supply chain from the mine to the refiner.'],
    ['Supplier', 'Any individual or organization that supplies gold or gold-bearing material to {{COMPANY_SHORT}}, directly or as part of the chain, including customers who sell gold to it.'],
    ['CAHRA', 'Conflict-affected and high-risk areas: areas of armed conflict, widespread violence or other serious and widespread harm to people, and areas with a high risk of conflict or of widespread or serious abuses, as described in Annex II of the OECD Guidance (AML Manual section 7.5).'],
    ['LSM / ASM', 'Large-scale mining / artisanal and small-scale mining.'],
    ['Mined gold', 'Gold whose origin is the mine itself, except where it is a by-product of mining another metal.'],
    ['Recycled gold', 'Gold that has been used (scrap, jewelry, electronics). Its origin is the point at which it first becomes recyclable, for example when it is first sold back to a recycler or refiner.'],
    ['Grandfathered stock', 'Gold that can be proven by a verifiable date to have existed before 1 January 2012.'],
    ['Red flag', 'A warning sign in the location, supplier, transaction or documents that calls for enhanced due diligence (section 4.5).'],
    ['Chain of custody', 'The record of the sequence of parties who held the gold as it moved through the supply chain.'],
    ['Compliance Officer', 'The MLRO appointed under the AML Manual, who is also responsible for supply chain due diligence.'],
    ['Senior Management', '{{SENIOR_MANAGEMENT}}.'],
  ], [2400, 7000]);
  h2('1.6 Version control and regulatory change log');
  table(['Version', 'Date', 'Change', 'Approved by'], [
    ['{{VERSION}}', '{{ISSUE_DATE}}', 'Issue of this policy for gold and bullion traders under Federal Decree-Law No. (10) of 2025 and Ministerial Decree No. (68) of 2024.', '{{APPROVER}}'],
    ['', '', '', ''],
  ], [1100, 1800, 4700, 1800]);
  table(['Date reviewed', 'Source', 'What changed', 'Section updated', 'Reviewed by'], [
    ['', '', '', '', ''],
    ['', '', '', '', ''],
  ], [1400, 2200, 2800, 1700, 1300]);

  // ── 2. Policy commitments ──────────────────────────────────────────────
  h1('2. POLICY COMMITMENTS');
  p('These commitments follow the model supply chain policy in Annex II of the OECD Guidance and apply whenever {{COMPANY_SHORT}} sources from, trades with, or operates in conflict-affected and high-risk areas.');
  h2('2.1 Serious abuses');
  p('{{COMPANY_SHORT}} will not tolerate, profit from, contribute to, assist with or facilitate any of the following by any party:');
  bullets([
    'any form of torture, or cruel, inhuman and degrading treatment;',
    'any form of forced or compulsory labor, meaning work or service exacted from a person under the menace of penalty and for which the person has not offered themselves voluntarily;',
    'the worst forms of child labor;',
    'other gross human rights violations and abuses, such as widespread sexual violence;',
    'war crimes, other serious violations of international humanitarian law, crimes against humanity or genocide.',
  ]);
  p('{{COMPANY_SHORT}} will immediately suspend or discontinue engagement with an upstream supplier where it identifies a reasonable risk that the supplier sources from, or is linked to, any party committing the abuses in this section.');
  h2('2.2 Direct or indirect support to non-state armed groups');
  p('{{COMPANY_SHORT}} will not tolerate any direct or indirect support to non-state armed groups through the extraction, transport, trade, handling or export of minerals. Direct or indirect support includes, but is not limited to, procuring minerals from, making payments to, or providing logistical assistance or equipment to, non-state armed groups or their affiliates who:');
  bullets([
    'illegally control mine sites, transportation routes, points where minerals are traded, or upstream actors in the supply chain; or',
    'illegally tax or extort money or minerals at points of access to mine sites, along transportation routes or at points where minerals are traded; or',
    'illegally tax or extort intermediaries, export companies or international traders.',
  ]);
  p('{{COMPANY_SHORT}} will immediately suspend or discontinue engagement with an upstream supplier where it identifies a reasonable risk that the supplier sources from, or is linked to, any party providing such support.');
  h2('2.3 Public or private security forces');
  p('Where {{COMPANY_SHORT}} identifies a reasonable risk of direct or indirect support to public or private security forces, it will, in line with its position in the supply chain, immediately devise, adopt and implement a risk management plan with upstream suppliers and other stakeholders to prevent or mitigate that risk. The plan aims for security forces to act in line with the Voluntary Principles on Security and Human Rights and not to illegally control or tax mine sites, routes or trading points. If the mitigation fails within six months of adopting the plan, {{COMPANY_SHORT}} will suspend or discontinue engagement with the supplier.');
  h2('2.4 Bribery and fraudulent misrepresentation of origin');
  p('{{COMPANY_SHORT}} will not offer, promise, give or demand any bribe, and will resist solicitation of bribes, to conceal or disguise the origin of minerals, or to misrepresent taxes, fees and royalties paid to governments for extraction, trade, handling, transport and export. Where it identifies a reasonable risk of such conduct, it will engage with suppliers, authorities, international organizations, civil society and affected third parties, as appropriate, to improve performance through measurable steps within reasonable timescales, and will suspend or discontinue engagement with upstream suppliers after failed attempts at mitigation.');
  h2('2.5 Money laundering and financing of terrorism and proliferation');
  p('{{COMPANY_SHORT}} will support efforts to eliminate money laundering, and will take steps to contribute to its effective elimination, where it identifies a reasonable risk of money laundering connected to the extraction, trade, handling, transport or export of minerals, including from the illegal taxation or extortion of minerals at points of access to mine sites, along routes or at trading points. It complies with the AML Manual, including customer and supplier due diligence, suspicious transaction reporting and cash reporting, and with targeted financial sanctions (AML Manual section 6).');
  h2('2.6 Taxes, fees and royalties');
  p('{{COMPANY_SHORT}} will ensure, in line with its position in the supply chain, that all taxes, fees and royalties related to extraction, trade and export of minerals from conflict-affected and high-risk areas are paid to governments, and will disclose such payments consistently with the principles of the Extractive Industries Transparency Initiative (EITI), where it can obtain the information.');
  h2('2.7 Sanctions');
  p('{{COMPANY_SHORT}} will comply with UN Security Council sanctions, the UAE Local Terrorist List and the UAE laws implementing them. It will not deal with, or make funds or gold available to, any listed person or any entity they own or control.');
};
