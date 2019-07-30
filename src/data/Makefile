# generated using GenMakefile.mk and Base.mk
CLASSES= \
buildings_upgrades_cost.class.php \
colonyshiprepair.class.php \
map_regions_settlement.class.php \
# eol
PHP=php
GENPERSISTENTOBJECT=./gendbobject.php
TABLECLASSES=$(CLASSES:.class.php=.table.php)

#all:$(CLASSES)
all:$(CLASSES) $(TABLECLASSES) Makefile

.SUFFIXES: .pobj .class.php .table.php .sql

.pobj.class.php:
	@if [ -e $@ ]; then \
		echo "SKIPPING GENERATION OF $@"; \
		fi
	@if [ ! -e $@ ]; then \
		$(PHP) -f $(GENPERSISTENTOBJECT) php $< > $@ ;\
		fi
	@touch $@ 

.pobj.table.php:
	$(PHP) -f $(GENPERSISTENTOBJECT) table $< > $@


.pobj.sql:
	@echo "generating $@ from $<"
	@$(PHP) $(GENPERSISTENTOBJECT) sql $< > $@

Makefile: genmakefile.mk genbase.mk
	$(MAKE) -f genmakefile.mk

clean: force
	rm -f $(CLASSES)

force:

buildings_upgrades_cost.table.php:$(GENPERSISTENTOBJECT)
colonyshiprepair.table.php:$(GENPERSISTENTOBJECT)
map_regions_settlement.table.php:$(GENPERSISTENTOBJECT)
