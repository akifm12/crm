// Appendix B for real estate brokers and agents (based on the MoET Supplemental Guidance, 2026)
module.exports = (L) => {
  const { h1, h2, h3, p, bullets } = L;

  h1('APPENDIX B: RED FLAG INDICATORS: REAL ESTATE');
  p('The presence of a red flag does not mean that a crime has occurred. Red flags are assessed together and in context. If a concern cannot be resolved, it is escalated to the MLRO on an Internal SAR for investigation, EDD, and a decision whether to file an STR/SAR through goAML.');
  h3('Customer behavior and profile');
  bullets([
    'refuses to cooperate or to explain the source of funds, or avoids meetings and direct contact without a good reason;',
    'a foreign national with no ties to the UAE and no clear purpose or economic reason for buying property;',
    'the transaction does not fit the customer\'s background, profession or means, or the customer shows little understanding of, or interest in, the property or the price;',
    'sudden changes in behavior, or unknown third parties appearing during the deal, such as lawyers, notaries or financial institutions where that is not usual;',
    'repeated changes of the declared beneficial owner during the transaction without a credible reason;',
    'instructions given entirely through a representative, with no valid confidentiality or legal reason;',
    'insists on completing and paying for a high-value property in cash with no clear source of funds;',
    'withdraws once enhanced due diligence is requested, threatens legal action or reputational harm to discourage questions, or tries to persuade staff to bypass controls.',
  ]);
  h3('Ownership and structures');
  bullets([
    'a company, foreign or domestic, used to buy property, especially with an unusual or overly complex structure;',
    'shell companies, trusts or other vehicles with no clear purpose that hide the real parties;',
    'nominee shareholders or directors with no apparent link to the deal;',
    'ownership split between relatives or entities to avoid screening or thresholds, or family members used with no financial reason;',
    'beneficial owner resident in a different jurisdiction from the source of funds or the place of incorporation, with no clear reason.',
  ]);
  h3('Source of funds and payments');
  bullets([
    'the customer cannot or will not explain the source of funds, or the explanation cannot be verified;',
    'significant cash or virtual assets with no clear explanation, or part of the price settled in cash or foreign currency without a business reason;',
    'bank drafts, cashier\'s checks, bearer instruments or third-party checks without identification of the true payer;',
    'payments split into smaller amounts with short gaps, or payments from several unrelated third parties;',
    'cryptocurrency, wallet-to-wallet transfers, online platforms or virtual agents with unclear oversight, or peer-to-peer and offshore transfers that are hard to trace.',
  ]);
  h3('Financing and valuation');
  bullets([
    'a mortgage or investment scheme that appears fraudulent or unusual, or financing by third parties with no commercial justification;',
    'valuations from unfamiliar or unlicensed valuers, or large gaps between independent valuations and the price;',
    'pressure on valuers or agents to adjust a valuation;',
    'family offices or private investment vehicles with unclear governance or funding.',
  ]);
  h3('Timing and patterns');
  bullets([
    'several off-plan units bought and then resold or assigned soon after booking;',
    'properties bought, sold or exchanged one after another in a short time, with changes in price that make no sense, or successive sales within a group of people at prices above or below market;',
    'property bought mainly to store value, with no intention to occupy, lease, develop or resell;',
    'urgency with no good reason, or a customer who comes back on following days to buy more properties with no clear source of funds;',
    'a sale of a building plot soon followed by the declaration of a new finished building on it.',
  ]);
  h3('Sanctions and proliferation');
  bullets([
    'a customer, beneficial owner or associate on a targeted financial sanctions list, or linked to sanctioned parties or countries;',
    'attempts to avoid screening through layered structures or frequent ownership changes;',
    'links to dual-use goods, sensitive technology or sanctioned trade, or opaque entities linked to jurisdictions under UN proliferation-related sanctions;',
    'a property used as collateral in arrangements involving sanctioned or high-risk counterparties;',
    'a PEP or a person linked to one, or someone on trial for or publicly linked to crimes, including corruption.',
  ]);
};
