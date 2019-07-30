#!/usr/bin/env python
import re
import sys

try: lang = sys.argv[1]
except: lang = 'de_DE'

id = re.compile('msgid "(.*)"')
st = re.compile('msgstr ""')

prev = None
lc = 0
filename = lang+'/LC_MESSAGES/stu.po'

for line in open(filename).xreadlines():
        r = id.match(line)
        noprint = False
        if r:
                prev = r.group(1)
        else:
                r = st.match(line)
                if r:
                        noprint = True
                        sys.stdout.write( 'msgstr "%s"\n' % prev )
        if not noprint:
                sys.stdout.write( line )

