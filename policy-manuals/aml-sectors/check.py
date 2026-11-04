import zipfile,re,html,sys,collections
f=sys.argv[1]
z=zipfile.ZipFile(f)
from xml.dom import minidom
for n in z.namelist():
    if n.endswith('.xml') or n.endswith('.rels'): minidom.parseString(z.read(n))
allx=''.join(z.read(n).decode('utf8') for n in z.namelist() if n.startswith('word/') and n.endswith('.xml'))
d=z.read('word/document.xml').decode('utf8')
paras=[html.unescape(''.join(re.findall(r'<w:t[^>]*>([^<]*)</w:t>',p))).strip() for p in re.findall(r'<w:p[ >].*?</w:p>',d,flags=re.S)]
full='\n'.join(p for p in paras if p)
print('words',len(full.split()),'| VERIFY',allx.count('VERIFY'),'| dashes',allx.count('—'),allx.count('–'),'| Arial','Arial' in z.read('word/styles.xml').decode())
for term in ['gold','bullion','DPMS','precious','CAHRA','scrap','refin','assay','jewel','metal','stones','Appendix F','Appendix G','Appendix H','section 7.8','section 7.9','responsible sourcing','Decree No. (68)']:
    for m in re.finditer(term, full, flags=re.I):
        s=max(0,m.start()-70); print('  LEFTOVER', term, '|', full[s:m.end()+60].replace('\n',' '))
heads=set(re.findall(r'^(\d+(?:\.\d+)*)[ .]',full,flags=re.M)); refs=set(re.findall(r'section (\d+(?:\.\d+)*)',full))
print('refs without heading:',sorted(r for r in refs if r not in heads))
print('appendices referenced:',sorted(set(re.findall(r'Appendix ([A-Z])',full))),'| headings:',re.findall(r'APPENDIX ([A-Z])',full))
words=collections.Counter(re.findall(r"[A-Za-z']+",full))
print([w for w in words if re.search(r'(isation|ised|ises|ising|our|ours|yse|ysed|lled|lling|ogue|tre|mme)$',w.lower()) and len(w)>4 and w.lower() not in ('supervised','selling','telling')])
