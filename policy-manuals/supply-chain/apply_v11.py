# Supply Chain Policy v1 -> v1.1: clear the VERIFY comments and tidy for submission.
import os, re, sys
os.chdir(os.path.dirname(os.path.abspath(__file__)))
sys.stdout.reconfigure(encoding='utf-8')
IO = dict(encoding='utf-8', newline='')
rd = lambda p: open(p, **IO).read()
def wr(p, s): open(p, 'w', **IO).write(s)

s = rd('part1.cjs')
# template guide: no more mention of comment tags
n_before = s.count('Cyan [VERIFY')
s = re.sub(r"\*\*Cyan \[VERIFY: \.\.\.\] tags\*\* mark statements[^.]*\. Check them[^.]*\. ", "", s)
assert n_before == 1 and 'Cyan [VERIFY' not in s
# remove the note about the "V9" file name
s, n = re.subn(r' The "V9" in the title of the file on the MoET site is not a version of the document\.', '', s)
assert n == 1
wr('part1.cjs', s)

s = rd('part2.cjs')
s, n = re.subn(r" \[VERIFY: [^\]]*\]", "", s)
assert n == 1, n
wr('part2.cjs', s)

s = rd('part3.cjs')
s, n = re.subn(r" to MoET every year \[VERIFY: [^\]]*\]; and", " to MoET every year, within the deadline MoET sets for each review cycle; and", s)
assert n == 1, n
s, n = re.subn(r" \[VERIFY: [^\]]*\]", "", s)
assert n == 1, n
wr('part3.cjs', s)

b = rd('build.cjs')
b = b.replace('Supply-Chain-Policy-Template-v1.docx', 'Supply-Chain-Policy-Template-v1.1.docx')
b = b.replace("'Supply Chain Policy for Responsible Sourcing of Gold, Template v1'", "'Supply Chain Policy for Responsible Sourcing of Gold'")
wr('build.cjs', b)

for f in ('part1.cjs', 'part2.cjs', 'part3.cjs'):
    assert 'VERIFY' not in rd(f), f
print('ok')
