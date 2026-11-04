// Section 7 for trust and company service providers
module.exports = (L) => {
  const { h1, h2, h3, p, bullets } = L;

  h1('7. CONTROLS SPECIFIC TO TRUST AND COMPANY SERVICES');
  h2('7.1 Covered activities and when the relationship starts');
  p('The AML/CFT/CPF obligations apply to {{COMPANY_SHORT}} when it carries out or executes any of these activities for, or on behalf of, a customer:');
  bullets([
    'acting as an agent in the incorporation or establishment of a legal person;',
    'acting as, or arranging for another person to act as, a director or secretary of a company, or a partner or in a similar position in another legal person;',
    'providing a registered office, business address, residence, correspondence address or administrative address for a company, another legal person or a legal arrangement;',
    'acting as, or arranging for another person to act as, trustee of an express trust, or in an equivalent function for another legal arrangement;',
    'acting as, or arranging for another person to act as, a nominee shareholder for another person.',
  ]);
  p('A business relationship starts when {{COMPANY_SHORT}} agrees to provide or arrange any of these services, for example when an engagement letter is signed, when instructions are received, or when fees are paid. Customer due diligence is completed before or at that moment, and before any company is formed or registered, any person is appointed, any address is provided, or {{COMPANY_SHORT}} acts for anyone. No service is provided anonymously. Reports are filed because of suspicion, whatever the value, and include attempted activity and cases where {{COMPANY_SHORT}} declines a customer because of due diligence concerns (section 8).');

  h2('7.2 Payments');
  bullets([
    'Fees, capital contributions and other funds are paid by bank transfer from an account in the customer\'s own name. Cash is not accepted unless the MLRO approves it in writing for that payment.',
    'A payment from or to a person other than the customer is not accepted unless the MLRO approves it in advance, after establishing the reason, the identity of that person and their relationship to the customer, and screening them.',
    '{{COMPANY_SHORT}} does not hold or move customer money except where its license and the law allow it, for a named purpose, and with the MLRO\'s approval.',
    'Payments that do not match the structure or the services, such as large payments for a dormant company or fees paid by unrelated parties, are escalated to the MLRO.',
  ]);

  h2('7.3 Nominee, director and trustee services');
  p('These services carry the highest risk, because another person\'s name stands in for the real owner or controller. {{COMPANY_SHORT}} provides them only on these conditions:');
  bullets([
    'the person for whom it acts (the nominator) and that person\'s beneficial owner are identified and verified, screened, and treated as High risk with Enhanced Due Diligence, and Senior Management approves each arrangement;',
    'the arrangement is set out in a written agreement that names the nominator and the beneficial owner, states the purpose, and allows {{COMPANY_SHORT}} to disclose them to the authorities when the law requires;',
    '{{COMPANY_SHORT}} keeps a register of nominee arrangements and nominators, and never treats a nominee as the beneficial owner;',
    'no nominee, director or trustee service is provided where the beneficial owner cannot be identified, the purpose is unclear, or the structure appears designed to hide ownership;',
    'bearer shares and similar instruments are never used;',
    'staff who act as a director, secretary or nominee exercise real oversight, do not sign blank or pre-signed documents, and refer any instruction that raises a concern to the MLRO;',
    'each arrangement is reviewed at least every six months.',
  ]);

  h2('7.4 Forming and structuring companies');
  bullets([
    '{{COMPANY_SHORT}} records the purpose and economic rationale of each structure, the expected activity and the jurisdictions involved, and asks why when a structure is layered or spans several jurisdictions with no clear commercial reason.',
    'A shelf or dormant company is not sold or transferred without full due diligence on the buyer, and documents are never backdated.',
    'Before any change of owner, director, secretary, trustee, nominee or address is processed, the due diligence and screening are updated.',
    'Each legal person must keep its beneficial owner information up to date and notify changes within 15 working days. {{COMPANY_SHORT}} tells its customers this duty, helps them to meet it, and keeps its own records of beneficial owners current.',
    'Where ownership cannot be established from the usual ownership thresholds, {{COMPANY_SHORT}} identifies the people who control the entity by other means, and then the senior managing official (section 5.4), and does not rely on the customer\'s declaration alone: ownership is corroborated against registries, legal documents and other independent sources.',
    'Structures involving jurisdictions with limited transparency, shares held on behalf of others without clear documents, frequent unexplained changes of ownership, or a customer who limits access to ownership information receive Enhanced Due Diligence.',
  ]);

  h2('7.5 Registered office and address services');
  bullets([
    '{{COMPANY_SHORT}} verifies the real activity of each entity that uses its address, and refuses to provide an address for a mailbox or shell arrangement with no substance, or where the customer cannot show a lawful purpose.',
    'Mail and notices are received and passed on only for identified and screened customers.',
    'The MLRO reviews entities that use the address for unusual correspondence, and ends the service for entities that are inactive, unscreened or no longer have a lawful purpose.',
  ]);

  h2('7.6 Trusts and similar arrangements');
  p('For a trust or similar arrangement, {{COMPANY_SHORT}} identifies and verifies the settlor, every trustee, any protector, each beneficiary or class of beneficiaries, and anyone who exercises ultimate control, and obtains the trust deed and the documents that show how the trustees were appointed. It understands the purpose of the trust and the source of the assets placed in it and of the settlor\'s wealth. Trusts are High risk and need Senior Management approval. Where {{COMPANY_SHORT}} acts as trustee, it keeps the trust records, supervises the trust assets, and reviews the arrangement at least every six months.');

  h2('7.7 Refusing or ending a service');
  p('Staff may refuse any service they are not comfortable with. A refusal and its reason are recorded and passed to the MLRO, who decides whether it is also reportable; declining a customer because of due diligence concerns can itself be reported. Staff do not explain to the customer that a suspicion exists. Delaying a service or asking for more information for compliance reasons is not tipping off, as long as it does not reveal a report or suspicion. {{COMPANY_SHORT}} refuses or exits any relationship where beneficial ownership, source of funds or the purpose of the structure cannot be reasonably verified, or where sanctions or proliferation financing risks cannot be mitigated.');
};
