# v2.3 -> v2.3.1: populate the CAHRA register from the EU indicative CAHRA list (September 2026 update).
import os, re, sys
os.chdir(os.path.dirname(os.path.abspath(__file__)))
IO = dict(encoding='utf-8', newline='')
rd = lambda p: open(p, **IO).read()
def wr(p, s): open(p, 'w', **IO).write(s)
def sub1(s, old, new):
    assert s.count(old) == 1, (s.count(old), old[:90])
    return s.replace(old, new, 1)

# Country, regions on the list, regions in the country (from cahraslist.net, list updated September 2026, data June 2026)
EU = [
    ('Afghanistan', 34, 34), ('Burkina Faso', 6, 13), ('Burundi', 17, 17), ('Cameroon', 3, 10),
    ('Central African Republic', 17, 17), ('Chad', 2, 23), ('Colombia', 10, 32), ('Democratic Republic of the Congo', 26, 26),
    ('Eritrea', 6, 6), ('Ethiopia', 5, 11), ('Haiti', 3, 10), ('India', 4, 35), ('Iran', 10, 31), ('Lebanon', 6, 8),
    ('Libya', 22, 22), ('Mali', 8, 9), ('Mexico', 10, 32), ('Mozambique', 2, 11), ('Myanmar', 15, 15), ('Niger', 4, 8),
    ('Nigeria', 9, 37), ('Pakistan', 3, 7), ('Russia', 2, 81), ('Somalia', 18, 18), ('South Sudan', 10, 10), ('Sudan', 18, 18),
    ('Togo', 1, 5), ('Ukraine', 10, 27), ('Venezuela', 25, 25), ('Yemen', 21, 21), ('Zimbabwe', 10, 10),
]
assert len(EU) == 31

def cov(n, t):
    return 'Whole country (%d of %d regions)' % (n, t) if n == t else 'Part of the country: %d of %d regions' % (n, t)

rows = ''.join("    ['%s', '%s', '', ''],\n" % (c.replace("'", "\\'"), cov(n, t)) for c, n, t in EU)

s = rd('part4.cjs')
a = s.index("  h3('CAHRA register');")
b = s.index("};", a)
s = s[:a] + r'''  h3('CAHRA register');
  p('Areas {{COMPANY_SHORT}} treats as conflict-affected or high-risk for gold supply chains, kept by the MLRO and reviewed at least quarterly (section 7.5). The register starts from the European Union\'s indicative, non-exhaustive list of conflict-affected and high-risk areas under Regulation (EU) 2017/821, maintained by RAND Europe for the European Commission (list updated September 2026, based on June 2026 data; source: cahraslist.net). That list is not an official EU opinion and is not UAE or MoET guidance. It does not replace {{COMPANY_SHORT}}\'s own assessment: an area missing from it can still be a CAHRA, and where only some regions of a country are listed the assessment looks at the specific region of origin and transit. Being listed does not prohibit trade. It requires enhanced scrutiny (section 7.5).');
  table(['Area / country', 'Coverage on the EU list', 'Last reviewed', 'Reviewed by'], [
''' + rows + r'''  ], [3300, 3700, 1300, 1100]);
  h3('Additional areas assessed by {{COMPANY_SHORT}}');
  p('Areas {{COMPANY_SHORT}} adds to the register, with the basis for each (sanctions regimes, FATF and National Committee lists, MoET guidance, credible government or intergovernmental reporting).');
  table(['Area / country', 'Why treated as a CAHRA (sources)', 'Date added', 'Reviewed by'], [
    ['', '', '', ''], ['', '', '', ''], ['', '', '', ''],
  ], [2400, 4400, 1300, 1300]);
''' + s[b:]

# cross-reference in the jurisdiction register: countries that are on both lists
both = ['Cameroon', 'Haiti', 'Lebanon', 'South Sudan', 'Venezuela', 'Yemen', 'Myanmar']
for c in both:
    pat = "    ['%s', 'FATF" % c
    i = s.index(pat)
    j = s.index("'],", i)
    row = s[i:j]
    assert row.endswith("As above.")
    s = s[:i] + row + " Also on the EU CAHRA list (Appendix F)." + s[j:]
s = sub1(s, "'As above. Also treat as a CAHRA candidate (Appendix F).'", "'As above. Also on the EU CAHRA list (Appendix F).'")
s = sub1(s, "screen and freeze under section 6.'],", "screen and freeze under section 6. Also on the EU CAHRA list (Appendix F).'],")
wr('part4.cjs', s)

s = rd('part2.cjs')
old = "**{{COMPANY_SHORT}} therefore decides for itself, and records, which areas it treats as CAHRAs (Appendix F).**"
new = "**{{COMPANY_SHORT}} therefore decides for itself, and records, which areas it treats as CAHRAs (Appendix F).** The register starts from the European Union\\'s indicative, non-exhaustive CAHRA list under Regulation (EU) 2017/821 (maintained by RAND Europe for the European Commission and updated quarterly), to which {{COMPANY_SHORT}} adds its own assessments. The EU list is a starting point only: it is not UAE or MoET guidance, an area missing from it can still be a CAHRA, and a listed area is not automatically off limits but needs enhanced scrutiny."
s = sub1(s, old, new)
wr('part2.cjs', s)

# change log
s = rd('part1.cjs')
s = sub1(s, "    ['', '', '', '', ''],\n  ], [1200, 2300, 3000, 1500, 1400]);",
         "    ['4 Nov 2026', 'EU indicative CAHRA list under Regulation (EU) 2017/821 (cahraslist.net, RAND Europe for the European Commission, September 2026 update)', 'CAHRA register populated with the 31 listed countries and their regional coverage.', '7.5, App. C, F', ''],\n    ['', '', '', '', ''],\n  ], [1200, 2300, 3000, 1500, 1400]);")
wr('part1.cjs', s)
print('ok')
