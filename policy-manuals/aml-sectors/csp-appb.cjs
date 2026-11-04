// Appendix B for trust and company service providers
// (themes from the MoET Supplemental Guidance for Trust and Company Service Providers, 2026)
module.exports = (L) => {
  const { h1, h2, h3, p, bullets } = L;

  h1('APPENDIX B: RED FLAG INDICATORS: COMPANY AND TRUST SERVICES');
  p('The presence of a red flag does not mean that a crime has occurred. Red flags are assessed together and in context. If a concern cannot be resolved, it is escalated to the MLRO on an Internal SAR for investigation, EDD, and a decision whether to file an STR/SAR through goAML.');
  h3('Individual customers');
  bullets([
    'reluctant to give, or cannot explain, the identity of the beneficial owner, the source of wealth or funds, or the purpose of the structure;',
    'unwilling to meet, or instructs only through intermediaries, with no good reason;',
    'the stated business does not fit the customer\'s background, or the customer cannot describe how the company will operate;',
    'acts for an undisclosed principal, or is clearly taking instructions from someone else;',
    'asks for unusual secrecy, or tries to limit what {{COMPANY_SHORT}} may know or disclose;',
    'unconcerned by fees or costs, or in an unusual hurry to complete formation;',
    'tries to persuade staff to bypass or shorten due diligence, or withdraws once questions are asked.',
  ]);
  h3('Legal entities and arrangements');
  bullets([
    'ownership held through layers of companies or trusts in several jurisdictions with no commercial reason, or through jurisdictions with limited transparency;',
    'shares held for others without a clear document of the underlying relationship, or bearer shares;',
    'frequent or unexplained changes of owner, director, secretary, trustee or address;',
    'nominee directors or shareholders who have no apparent link to the business;',
    'a newly formed or shelf company with no track record that quickly shows large flows, or a dormant company that suddenly becomes active;',
    'a registered office or address with no sign of real activity, or many unrelated entities at the same address;',
    'the same person or group controls several apparently unrelated entities;',
    'the entity cannot show a clear business purpose, or the purpose changes during the engagement;',
    'a trust or foundation whose settlor, beneficiaries or controllers are unclear, or whose purpose is not explained.',
  ]);
  h3('Transactions and services');
  bullets([
    'payments from or to unrelated third parties, or from jurisdictions unconnected to the customer or the business;',
    'cash payments, or payments split into smaller amounts;',
    'large inflows or loans with no documents, or intercompany invoices and loans with no substance;',
    'requests for services that appear designed to avoid regulatory scrutiny, such as backdated documents, transfers of ownership with no consideration, or signing authority given to an unknown person;',
    'unusually quick or repeated formation, restructuring or transfer of companies for the same customer;',
    'funds flow that does not match the declared activity of the entity.',
  ]);
  h3('Geography, sanctions and proliferation');
  bullets([
    'a customer, beneficial owner, director or counterparty linked to a high-risk or sanctioned jurisdiction, or listed under targeted financial sanctions;',
    'a PEP, or a person linked to a PEP, whose role in the structure is unclear;',
    'links to dual-use goods, sensitive technology or sanctioned trade, or opaque entities linked to jurisdictions under UN proliferation-related sanctions;',
    'adverse media about corruption, fraud, tax crime or other financial crime;',
    'any other behavior or structure that is inconsistent with the customer\'s known profile or lacks an apparent lawful purpose.',
  ]);
};
