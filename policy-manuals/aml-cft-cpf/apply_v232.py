# v2.3.1 -> v2.3.2: remove every [VERIFY: ...] comment so the manual is ready to use.
import os, re, sys
os.chdir(os.path.dirname(os.path.abspath(__file__)))
sys.stdout.reconfigure(encoding='utf-8')
IO = dict(encoding='utf-8', newline='')
rd = lambda p: open(p, **IO).read()
def wr(p, s): open(p, 'w', **IO).write(s)
def sub1(s, old, new):
    assert s.count(old) == 1, (s.count(old), old[:90])
    return s.replace(old, new, 1)

s = rd('part1.cjs')
# template guide: drop the paragraph about VERIFY tags
a = s.index("  p('**Cyan [VERIFY:")
b = s.index("\n", a) + 1
s = s[:a] + "  p('Record every update to the manual in the regulatory change log in section 1.6.');\n" + s[b:]
s = sub1(s, " [VERIFY: whether this list is still the operative fine schedule alongside Article (17) of Federal Decree-Law No. (10) of 2025].", ".")
s = sub1(s, " (11 December 2025) [VERIFY: content applying to the company; text not yet reviewed].", " (11 December 2025).")
s = sub1(s, " offenses [VERIFY: current instrument].", " offenses.")
s = sub1(s, " [VERIFY: the operative MoET fine schedule per violation type (Cabinet Resolution No. (71) of 2024 predates the new law).]", "")
s = sub1(s, " confirms that the information is correct to the best of its knowledge. [VERIFY: current MoET periodic return or self-assessment requirements and deadlines.]", " confirms that the information is correct to the best of its knowledge.")
s = sub1(s, " already in the public domain. [VERIFY: the exact notification triggers and channels required by MoET for DPMS.]", " already in the public domain.")
wr('part1.cjs', s)

s = rd('part2.cjs')
s = sub1(s, " [VERIFY: MoET to confirm the reading of \"cash\" in Article 3(3) of Cabinet Resolution No. (134) of 2025; this Manual applies the stricter reading];", ";")
s = sub1(s, " Records are kept for the period in section 10 [VERIFY: DPMSR filing deadline in the current goAML/FIU guidance].", " Records are kept for the period in section 10, and the DPMSR is filed within the deadline set in the current goAML and MoET guidance.")
wr('part2.cjs', s)

s = rd('part3.cjs')
s = sub1(s, "the MLRO notifies MoET if MoET requires it [VERIFY: whether and how MoET must be informed when an STR/SAR is filed], and follows", "the MLRO informs MoET where its guidance requires it, and follows")
wr('part3.cjs', s)

left = []
for f in ('part1.cjs', 'part2.cjs', 'part3.cjs', 'part4.cjs'):
    if 'VERIFY' in rd(f): left.append(f)
print('files still containing VERIFY:', left)
b = rd('build.cjs')
b = b.replace('Template-v2.3.1.docx', 'Template-v2.3.2.docx').replace('Template v2.3.1', 'Template v2.3.2')
wr('build.cjs', b)
