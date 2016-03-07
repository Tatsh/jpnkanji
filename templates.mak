templatechecks: attrlist.txt attrlist.php masut.php

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

attrlist.php: attrlist.php.in attrlist.txt
	rm -f tmp2 && sed 's!@ATTRLIST@!'"`cat attrlist.txt|tr '\012' @`"'!' <"$<"|tr @ '\012' >tmp2 && mv -f tmp2 "$@"

masut.php: masu.php
	$(PHP) -q $< > $@
