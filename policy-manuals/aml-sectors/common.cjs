// The operations that turn the base (gold and bullion) manual into a sector manual.
// A sector file supplies the sector's own wording (see real-estate.cjs and csp.cjs).
const { Q } = require('./engine.cjs');

const E = (t) => String(t).replace(/\\/g, '\\\\').replace(/'/g, "\\'");   // escape for use inside a '...' literal
const opt = (S, k) => (S[k] === undefined || S[k] === null ? null : S[k]);

module.exports = function ops(S, section7Path, appendixBPath) {
  const o = [];
  const sub = (file, a, b) => o.push(['sub', file, a, b]);
  const subAll = (file, a, b) => o.push(['subAll', file, a, b]);
  const line = (file, prefix, to) => o.push(['line', file, prefix, to]);
  const block = (file, from, to, text) => o.push(['block', file, from, to, text]);

  // ── part1: template guide, cover, scope, legal framework, authorities, definitions ──
  line('part1.cjs', "p('This is the master template", `p(${Q(S.intro)});`);
  line('part1.cjs', "['{{COMPANY_NAME}}', 'Full legal name", `['{{COMPANY_NAME}}', 'Full legal name, as on the trade license', ${Q(S.example1)}],`);
  line('part1.cjs', "['{{COMPANY_SHORT}}', 'Short name used", `['{{COMPANY_SHORT}}', 'Short name used throughout the text', ${Q(S.example2)}],`);
  line('part1.cjs', "add(new Paragraph({ spacing: { after: 60 }, children: runs('Dealer in Precious Metals", `add(new Paragraph({ spacing: { after: 60 }, children: runs(${Q(S.cover)}, { size: 22 }) }));`);
  sub('part1.cjs', "as a dealer in precious metals and stones (DPMS), including buying, selling, refining, trading, import and export of gold and other precious metals.", E(S.scope));

  line('part1.cjs', "'Ministerial Decree No. (68) of 2024 on adherence", null);
  line('part1.cjs', "h3('MoET circulars and guidance (DPMS)')", "h3('MoET circulars and guidance');");
  line('part1.cjs', "'Circular No. 08/AML/2021", S.circular ? `${Q(S.circular)},` : null);
  line('part1.cjs', "'Circular No. (2) of 2023", null);
  line('part1.cjs', "'Guidelines for Designated Non-Financial Businesses and Professions (March 2026) and the Supplemental", `${Q(S.guidance)},`);
  line('part1.cjs', "'FATF Recommendations and guidance for dealers", `${Q(S.fatf)},`);

  sub('part1.cjs', 'AML/CFT/CPF supervisor of dealers in precious metals and stones.', E(S.supervisor));
  if (S.licensing) sub('part1.cjs', S.licensing[0], E(S.licensing[1]));
  sub('part1.cjs', 'STR/SAR, DPMSR, HRC/HRCA and follow-up reports', E(S.fiuReports));
  sub('part1.cjs', '(including a person who sells gold to {{COMPANY_SHORT}})', E(S.customerExtra));
  line('part1.cjs', "['DPMSR',", opt(S, 'defReport') ? `['${E(S.defReport[0])}', '${E(S.defReport[1])}'],` : null);
  line('part1.cjs', "['Covered precious metals and stones',", `['${E(S.defA[0])}', '${E(S.defA[1])}'],`);
  line('part1.cjs', "['CAHRA',", `['${E(S.defB[0])}', '${E(S.defB[1])}'],`);

  block('part1.cjs', "table(['Date reviewed', 'Source / reference'", "], [1200, 2300, 3000, 1500, 1400]);",
    "  table(['Date reviewed', 'Source / reference', 'What changed', 'Manual section updated', 'Reviewed by'], [\n" +
    S.changeLog.map((r) => '    [' + r.map(Q).join(', ') + '],').join('\n') + "\n    ['', '', '', '', ''],");

  block('part1.cjs', "table(['Stage', 'What happens', 'Examples in the gold trade']", "], [1500, 3000, 4900]);",
    `  table(['Stage', 'What happens', ${Q(S.stagesHeader)}], [\n` + S.stages.map((r) => '    [' + r.map(Q).join(', ') + '],').join('\n'));
  block('part1.cjs', "h2('2.5 Why the gold trade is exposed');", "h2('2.6 Penalties",
    `  h2(${Q(S.exposureTitle)});\n  bullets([\n` + S.exposure.map((t) => `    ${Q(t)},`).join('\n') + '\n  ]);');

  sub('part1.cjs', '(DPMSR, HRC/HRCA, CNMR, PNMR) on time', E(S.mlroReports));
  sub('part1.cjs', 'cash transactions and DPMSR filings; supplier due diligence;', E(S.reviewScope));

  // ── part2: risk, CDD, TFS wording, and the sector controls section ──
  sub('part2.cjs', 'which rate the DPMS sector as high inherent risk', E(S.sectorRisk));
  line('part2.cjs', "'Identify the inherent risks from these factors:", `${Q(S.braFactors)},`);
  line('part2.cjs', "'geography: nationality, residence, incorporation", `${Q(S.geography)},`);
  sub('part2.cjs', '(including lawyers, accountants, real estate agents and other precious metals dealers)', '(including lawyers, accountants, real estate agents and company service providers)');
  sub('part2.cjs', 'credible reports of terrorism financing, corruption or illicit gold;', 'credible reports of terrorism financing or corruption;');
  line('part2.cjs', "'cash-intensive business or cash payment above the reporting threshold", `${Q(S.cashFlag)},`);
  line('part2.cjs', "'gold of unclear or undocumented origin", `${Q(S.extraFlag)},`);
  line('part2.cjs', "'adverse media about financial crime, sanctions evasion", "'adverse media about financial crime, corruption or sanctions evasion.',");
  line('part2.cjs', "'before any occasional transaction of AED 55,000", null);
  line('part2.cjs', "'when buying gold from any individual", `${Q(S.fiveOne)},`);
  sub('part2.cjs', 'and for cash transactions at or above the reporting threshold, the source of funds is obtained and recorded;', E(S.sourceFunds));
  sub('part2.cjs', 'or a cash transaction at or above the reporting threshold', E(S.sddCash));
  sub('part2.cjs', 'what to do with any funds or gold held', 'what to do with any funds or documents held');
  sub('part2.cjs', 'not make funds, assets, gold or services available', 'not make funds, assets or services available');
  line('part2.cjs', "['Transaction parties',", `['Transaction parties', ${Q(S.transParties)}],`);
  sub('part2.cjs', 'all funds, gold and other assets of the listed person', E(S.freezeAssets));
  block('part2.cjs', '// ── 7. Gold-sector controls', '};', `  // ── 7. Sector controls ──\n  require(${JSON.stringify(section7Path)})(L);`);

  // ── part3 ──
  sub('part3.cjs', 'repeated purchases and sales of the same gold, changes in volume or value, and payments through third parties', E(S.monitor));
  line('part3.cjs', "['DPMSR', 'Threshold transactions", S.reportRow ? `['${E(S.reportRow[0])}', '${E(S.reportRow[1])}'],` : null);
  line('part3.cjs', "['1. Inquiry',", `['1. Inquiry', ${Q(S.step1[0])}, ${Q(S.step1[1])}],`);
  line('part3.cjs', "'customer correspondence and the supporting records of each transaction", `${Q(S.records)},`);
  line('part3.cjs', "'supplier due diligence and responsible-sourcing records and reports;'", null);
  sub('part3.cjs', 'external reports (STR/SAR, DPMSR, HRC/HRCA, CNMR, PNMR)', E(S.reports66));
  sub('part3.cjs', 'recognizing red flags in the gold trade; cash and DPMSR rules;', E(S.training));
  sub('part3.cjs', 'responsible sourcing; how to make an Internal SAR', 'how to make an Internal SAR');

  // ── part4 ──
  line('part4.cjs', "'filing STRs/SARs, DPMSRs, HRC/HRCA, CNMRs, PNMRs", `${Q(S.mlroFiling)},`);
  line('part4.cjs', "'supplier due diligence and responsible sourcing oversight", null);
  block('part4.cjs', '// ── B. Red flags', '// ── C. High-risk jurisdictions', `  // ── B. Red flags ──\n  require(${JSON.stringify(appendixBPath)})(L);\n`);
  subAll('part4.cjs', ' Also on the EU CAHRA list (Appendix F).', '');
  sub('part4.cjs', 'corruption, illicit gold, conflict or weak AML supervision', 'corruption, conflict or weak AML supervision');
  sub('part4.cjs', ' Conflict-affected and high-risk areas for the gold supply chain are recorded in Appendix F.', '');
  line('part4.cjs', "['Gold involved (description, weight, purity, value)',", `[${Q(S.appxERow)}, ''],`);
  block('part4.cjs', '// ── I. CAHRA assessment', '};', null);
  return o;
};
