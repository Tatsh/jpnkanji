templatechecks: japkanji.php.in settings.mak.in configure \
                konverto.php.in kanjikonverto.php.in konvertfun.php.in \
                kanjisqlfun.php.in

attrlist.txt: edict_doc.txt enamdict_doc.txt
	rm -f tmp;grep -n 'such markers' edict_doc.txt|sed 's/:.*//' >tmp
	rm -f tmp2;expr `cat tmp` + 2 >tmp2
	rm -f tmp;tail +`cat tmp2` edict_doc.txt >tmp
	rm -f tmp2;head -n `grep -nv . tmp|head -n 1|sed 's/:.*//'` tmp|grep .|sed "s/'/\\\\\\\\'/" >tmp2
	rm -f tmp;grep '^[a-z] - ' enamdict_doc.txt >tmp
	for s in 1 2 3 4 5 6 7 8 9 10 11 12 13 14 15 16 17 18 19 20;do echo "$$s -  $$s";done >>tmp
	sed 's/ - /         /' <tmp >>tmp2
	grep '[a-z]-ben$$' edict_doc.txt|sed 's/  */:   /' >>tmp2
	rm -f "$@"; "$(PERL)" -pe "s/^([^ ]*)  ( *)(.*)/'\1'\2=> '\3',/g" <tmp2 >"$@"
	echo "'masc' => 'male term or language'," >> "$@"
	rm -f tmp2

japkanji.php.in: japkanji.php attrlist.txt
	rm -f tmp2 && sed 's!@ATTRLIST@!'"`cat attrlist.txt|tr '\012' @`"'!' <"$<"|tr @ '\012' >tmp2 && mv -f tmp2 "$<"
	touch -r"$@" "$<" && chmod a-wx "$<"
konverto.php.in: konverto.php attrlist.txt
	rm -f tmp2 && sed 's!@ATTRLIST@!'"`cat attrlist.txt|tr '\012' @`"'!' <"$<"|tr @ '\012' >tmp2 && mv -f tmp2 "$<"
	touch -r"$@" "$<" && chmod a-wx "$<"
settings.mak.in: settings.mak
	touch -r"$@" "$<" && chmod a-wx "$<"
kanjikonverto.php.in: kanjikonverto.php
	touch -r"$@" "$<" && chmod a-wx "$<"
konvertfun.php.in: konvertfun.php
	touch -r"$@" "$<" && chmod a-wx "$<"
kanjisqlfun.php.in: kanjisqlfun.php
	touch -r"$@" "$<" && chmod a-wx "$<"
masut.php: masu.php
	$(PHP) -q $< > $@

configure: configure.in
	@echo '*** $< is more recent than $@. Regenerating $@...'
	autoconf2.50
	@sed s/^cache_file=.*/cache_file=config.cache/ <$@ >$@.tmp && mv -f $@.tmp $@ && chmod a+x $@
	@echo '*** Please rerun ./configure'
	@false
