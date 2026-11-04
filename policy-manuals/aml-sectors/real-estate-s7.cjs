// Section 7 for real estate brokers and agents
module.exports = (L) => {
  const { h1, h2, h3, p, bullets, table } = L;

  h1('7. CONTROLS SPECIFIC TO REAL ESTATE BROKERAGE');
  h2('7.1 Cash, virtual assets and REAR reporting');
  p('{{COMPANY_SHORT}} files a Real Estate Activity Report (REAR) through goAML for every purchase or sale of freehold property in which:');
  bullets([
    'any part of the payment is in cash and the amount is AED 55,000 or more, in a single payment or in several payments;',
    'any part or all of the price is paid in a virtual asset; or',
    'the funds used were converted from or to a virtual asset, for any part or all of the price.',
  ]);
  p('The obligation does not depend on risk: a REAR is filed even when customer due diligence is complete and there are no red flags. If the transaction is also suspicious, both the REAR and an STR/SAR are filed. {{COMPANY_SHORT}} records the value, the payment methods, the parties and the dates of every transaction as the deal progresses, so that reports are accurate and filed within the deadline in the current goAML and MoET guidance. Staff must not split a payment, or advise a customer to split it, to stay below the threshold. Payments are handled under section 7.2.');

  h2('7.2 Payments');
  bullets([
    'The price, deposits and installments are paid through banks and other regulated channels, from an account in the buyer\'s own name to the seller, the developer or the escrow account. {{COMPANY_SHORT}} does not receive or hold the purchase price, deposits or installments itself, unless the law allows it and the MLRO approves the arrangement in writing.',
    'Cash is not accepted for any part of a price or deposit, unless Senior Management and the MLRO approve it in writing for that transaction. Cash that is accepted is reported under section 7.1 and is subject to Enhanced Due Diligence.',
    'A payment from or to a person other than the buyer or the seller is not accepted unless the MLRO approves it in advance, after establishing the reason for the payment, the identity of that person and their relationship to the customer, and screening them.',
    'Bank drafts, cashier\'s or manager\'s checks, bearer instruments and third-party checks are accepted only when the true payer is identified and the source of the money is evidenced. Otherwise they are treated as a red flag.',
    'Virtual assets, and payments through wallets or platforms that bypass banks and recognized intermediaries, are not accepted unless Senior Management has approved the channel in writing with controls in place. Any such payment is reported under section 7.1.',
    'Settlement in foreign currency or in cash without a valid business reason is escalated to the MLRO.',
    'Commissions and fees are paid by the customer or the developer named in the engagement, and not by unrelated third parties.',
  ]);

  h2('7.3 Both sides of the transaction');
  p('A business relationship starts when {{COMPANY_SHORT}} agrees to act for a customer, whether for one transaction or many. That happens when a listing agreement, brokerage contract, engagement letter or power of attorney is signed, when instructions are received, when fees or commission are received, or when the company starts to arrange a purchase, sale, lease or transfer. Customer due diligence is completed before or at that moment, and always before any transaction.');
  bullets([
    '**Buyers and sellers:** both are identified, verified and screened, with any person acting for them, whose authority is checked from a power of attorney or equivalent document. Where a party is a legal person, the beneficial owners are identified and the ownership and control structure is understood (section 5.4).',
    '**Ownership and authority to sell:** {{COMPANY_SHORT}} checks the seller\'s title from the title deed or equivalent registry evidence (for an off-plan unit, the registered contract with the developer) and checks the registry for recent changes of ownership.',
    '**Intermediaries and third parties:** where a relative, lawyer, business partner or other intermediary is involved, {{COMPANY_SHORT}} establishes why, and whether it suggests that the real owner is being kept out of sight. Instructions given entirely through a representative without a good reason are a red flag.',
    '**Profile fit:** the customer\'s financial profile must make sense against the value, nature and location of the property. Foreign customers with no apparent link to the UAE, complex or offshore structures, PEPs and customers from high-risk jurisdictions are rated accordingly (section 4).',
    '**Screening:** buyers, sellers, beneficial owners, representatives and the other connected parties are screened before any transaction (section 6.4).',
  ]);

  h2('7.4 Source of funds and source of wealth');
  p('For every property transaction {{COMPANY_SHORT}} establishes how the buyer will pay and where the money comes from, and checks that it matches the buyer\'s profile and financial capacity. For Medium and High risk customers, the explanation is backed by documents, such as bank statements, the sale contract of an earlier property, salary or dividend records, or a mortgage approval. For High risk customers, and for high-value, complex or cross-border transactions, source of wealth is also established and Enhanced Due Diligence is applied (section 5.8). Where a loan or mortgage finances the purchase, the lender is identified, and financing by third parties that are not financial institutions needs a commercial explanation. Proceeds of a sale are paid to the seller\'s own account.');

  h2('7.5 Monitoring through the life of the transaction');
  bullets([
    'The MLRO watches for changes during the transaction in the parties, the declared beneficial owner, the payment method and the price, and updates the customer file when a material change occurs.',
    'The company checks property or land registries for changes of ownership and for successive transactions on the same property within a short time.',
    'Valuations are checked against the price. Significant unexplained gaps between an independent valuation and the price, valuations from unfamiliar or unlicensed valuers, and pressure on a valuer or agent to adjust a valuation are escalated.',
    'Off-plan units resold or assigned soon after booking, several properties bought or sold in a short time, and property bought mainly to store value are monitored as higher risk.',
  ]);

  h2('7.6 Developers, other brokers and introducers');
  p('Before working with a developer, another brokerage or an introducer, {{COMPANY_SHORT}} identifies it, checks its license, ownership and reputation, and screens it. Co-brokerage and referral arrangements are in writing, and fees are paid only to the named party, never in cash and never to an unrelated third party.');

  h2('7.7 Refusing or ending a transaction');
  p('Staff may refuse any transaction they are not comfortable with. A refusal and its reason are recorded and passed to the MLRO, who decides whether it is also reportable. Staff do not explain to the customer that a suspicion exists. {{COMPANY_SHORT}} refuses or exits any engagement where beneficial ownership, source of funds or authority to sell cannot be reasonably verified, or where sanctions or proliferation financing risks cannot be mitigated.');
};
