Makefile:force
	@echo "Generating Makefile"
	@echo "# generated using GenMakefile.mk and Base.mk" > Makefile
	@echo "CLASSES= \\" >> Makefile
	@ls *.pobj | sed -e 's,pobj,class.php \\,' >> Makefile
	@echo "# eol" >> Makefile
	@cat genbase.mk >> Makefile
	@ls *.pobj | sed -e 's,pobj,table.php:$$(GENPERSISTENTOBJECT),' >> Makefile

force:
