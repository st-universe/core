DOMAIN=stu
PO=LC_MESSAGES/$(DOMAIN).po
MO=LC_MESSAGES/$(DOMAIN).mo
LANGS=de_DE en_US en_PIR

all: de_DE/$(MO) en_US/$(MO) en_PIR/${MO}

.SUFFIXES: .po .mo
.po.mo:
	msgfmt $< -o $@

merge:force
	for a in $(LANGS); do msgmerge --no-wrap -N -U $$a/$(PO) $(DOMAIN).pot; done
	python langfill.py > defill.po
	mv defill.po de_DE/LC_MESSAGES/stu.po


force:

