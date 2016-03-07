# Get system-specific macroes from settings.mak
include settings.mak

SEARCHFILES= \
          searchmodules/bushu.php \
          searchmodules/criter.php \
          searchmodules/fourcorner.php \
          searchmodules/jouyou.php \
          searchmodules/kanjiinput.php \
          searchmodules/skip.php \
          searchmodules/strokecount.php \
          searchmodules/customize.php \
          searchmodules/component.php

UTILITYINCLUDES= \
          headers.php cache.php gzcompress.php \
          slowloading.php headerfun.php japcharset.php \
          utf8.php smallcache.php shodouka.php

ARCHDIR=archives/
ARCHNAME=japkanji-$(VERSION)
ARCHFILES=$(DICTFILES) kanjidic radkfile simgen.php \
	  \
	  $(UTILITYINCLUDES) \
          \
          deconjfun.php attrlist.php \
          makediff.php progdesc.php \
          tables.sql COPYING edict_license.html \
          configure configure.in updatescript \
          japkanji.php.in japkanjilist.php kanjisearch.php \
          konverto.php.in kanjikonverto.php.in konvertfun.php.in \
          kanjisqlfun.php.in \
          unique2.php japverb.php japverb3.php .htaccess \
          settings.mak.in templates.mak \
          japkanji.css jislist.php \
          session.php masu.php unicodemrk.php \
          $(SEARCHFILES)

INSTALL=install
DEPFUN_INSTALL=not
BISQINSTALLDIR=/WWW/japtools
NOGZIPARCHIVES=1

default all: templatechecks
	@echo 'Error: Default make method does not exist.'
	@echo 'The following choices do exist:'
	@echo '    make sql       - Builds the MySQL tables required by the dictionary.'
	@echo '    make data      - Loads the MySQL tables with EDICT data.'
	@echo '    make kanjidata - Loads the MySQL tables with kanjidic data.'
	@echo '    make install   - Installation instructions.'
	@echo '    make update    - Download new dictionary files from the EDICT site.'

sql: templatechecks tables.sql $(MYSQL)
	-echo create database "$(SQLBASE)" | $(RUNSQL)
	(echo use "$(SQLBASE);";egrep -v "KEY |FOREIGN KEY" tables.sql) | $(RUNSQL)
	#echo use "$(SQLBASE);" `sed 's/#.*//'<tables.sql|grep .`|tr ';' '\012'|"$(PERL)" -pe 's/CREATE TABLE/ALTER TABLE/;s/PRIMARY KEY/foo/;s/\(.*?KEY/KEY/;s/KEY/ADD INDEX/g;s/\)$$/;/' | $(RUNSQL)
	#-(echo use "$(SQLBASE);";cat tables.sql) | $(RUNSQL)

data: templatechecks konverto.php konvertfun.php $(PHP) FORCE
	@echo Hmm.
	$(PHP) -q konverto.php

kanjidata: templatechecks kanjikonverto.php konvertfun.php $(PHP) FORCE
	@echo Hmm.
	$(PHP) -q kanjikonverto.php

Unihan.txt: FORCE
	wget "ftp://ftp.unicode.org/Public/UNIDATA/Unihan.txt"

install: templatechecks
	@echo '*** There is no installation method provided.'
	@echo '***   Try "$(MAKE)" to see what you should do / have done first.'
	@echo '***   When it is done, you can copy these files to a directory'
	@echo '***   you want, and edit headers.php:'
	@echo '***      japkanji.php'
	@echo '***      headers.php'
	@echo '***      cache.php'
	@echo '***      japkanji.css'
	@echo '*** Disclaimer: I have never attempted an installation'
	@echo '*** of this software in a system other than my own.'
	@echo '*** If it fails, send me a detailed friendly'
	@echo '*** error report and I can assist.'

warning: FORCE
	@echo '*** '
	@echo '*** WARNING '
	@echo '*** '

update: templatechecks updatenote updatereally

updatenote: warning
	@echo '*** Downloading new dictionaries from the EDICT ftpsite.'
	@echo '*** This means downloading over 10 MB of stuff and uncompressing it.'

updatereally: cancelwait
	@echo 'Please wait.'
	./updatescript

deinstall uninstall: tables.sql $(MYSQL) uninstnote uninstallreally FORCE

uninstnote: warning
	@echo '*** Dropping everything that has been created in tables.sql.'

uninstallreally: cancelwait
	(echo use "$(SQLBASE);";grep CREATE tables.sql|sed 's/CREATE/DROP/;s/$$/;/') | $(RUNSQL)

cancelwait: FORCE
	@echo '*** Interrupt in 7 seconds to cancel.'
	@echo '*** '
	@echo 7
	@sleep 1
	@echo 6
	@sleep 1
	@echo 5
	@sleep 1
	@echo 3
	@sleep 1
	@echo 2
	@sleep 1
	@echo 1
	@sleep 1
	@echo 0

BISQINSTUTILS=unique2.php deconjfun.php
bisqinstallchecks: templatechecks $(SEARCHFILES) $(BISQINSTUTILS)

# Tiedostot jotka asennetaan japtoolsiin
BISQINSTFILES=japkanji.php japkanjilist.php kanjisearch.php \
              unicodemap.php kanjisqlfun.php jislist.php \
              japverb.php japverb3.php .htaccess \
              japkanji.css masut.php session.php unicodemrk.php \
              attrlist.php

bisqinstall: bisqinstallchecks $(BISQINSTFILES)
	for s in $(BISQINSTFILES);do rm -f $(BISQINSTALLDIR)/$$s && cp -vp "$$s" $(BISQINSTALLDIR)/;done
	for s in $(SEARCHFILES); do cp --parents -vp "$$s" $(BISQINSTALLDIR)/;done
	cp -vp $(BISQINSTUTILS) /WWW/
	for s in $(BISQINSTUTILS);do ln -sf /WWW/"$$s" $(BISQINSTALLDIR)/;done
	rm -f /protemp/phpcache/kanjidict/*

include templates.mak
include depfun.mak

.PHONY: default all sql data kanjidata install \
        warning cancelwait templatechecks bisqinstall \
        deinstall uninstall uninstnote uninstallreally \
        update updatenote updatereally bisqinstallchecks FORCE
