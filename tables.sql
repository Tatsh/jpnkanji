USE japdict;

# DICTIONARY PART

# Dictionary name. For example "name=ediclsd3", description="Life Sciences"
CREATE TABLE japdict
(
  id TINYINT(3) NOT NULL AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(16) NOT NULL,
  description VARCHAR(64) NOT NULL
  
  , KEY name(name(8))
);

# Dictionary line.
CREATE TABLE japdata
(
  id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  # key to japdict
  dict_id TINYINT(3) NOT NULL DEFAULT 0,
  # kanji
  kanji VARCHAR(255) BINARY NOT NULL,
  # kanji as romaji
  kanjir VARCHAR(255) NOT NULL,
  # kana
  kana VARCHAR(255) BINARY NOT NULL,
  # kana as romaji
  kanar VARCHAR(255) NOT NULL
  
  , KEY dict_id(dict_id)
  , KEY kanji(kanji(8)), KEY kanjir(kanjir(10))
  , KEY kana(kana(16)), KEY kanar(kanar(9))
);

# Translations for each dictionary line
CREATE TABLE japtrans
(
  id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  
  # Key to japdata
  trans_id MEDIUMINT NOT NULL DEFAULT 0,
  
  # Translation
  name VARCHAR(255) NOT NULL
  
  , KEY trans_id(trans_id)
  , KEY name(name(12))
);

# Attributes of translation
CREATE TABLE japattr
(
  id MEDIUMINT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  
  # Key to japdata
  trans_id MEDIUMINT NOT NULL DEFAULT 0,
  
  # Attribute (abbr, adj, adv, na, a-no, arch, ...)
  name CHAR(6) BINARY NOT NULL
  
  , KEY trans_id(trans_id)
  , KEY name(name)
);

CREATE TABLE dictlog
(
  id       INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  search   VARCHAR(255) NOT NULL,
  # Search type
  stype    VARCHAR(16) NOT NULL,
  
  date     INT(11) NOT NULL, KEY date(date),
  hostname VARCHAR(255) NOT NULL
);

# KANJI DATABASE PART

CREATE TABLE japkanji
(
  jiscode        SMALLINT(5) UNSIGNED NOT NULL PRIMARY KEY,
  
  # Origin of this kanji for this database
  #   K=kanjidic (from EDICT site)
  #   U=Unihan.txt (from Unicode site)
  origin         CHAR NOT NULL,
  
  utf8kanji      CHAR(3) BINARY NOT NULL,
  unicode        SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, #unicode index
  
  radb           SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, #Bushu radical
  radc           SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, #Classical radical
  indexh         SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, #Halpern
  indexn         SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, #Nelson
  indexv         SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, #Haig
  indexe         SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, #Henshall
  indexk         SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, #Gakken
  indexl         SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0, #Heisig
  indexo1        CHAR(5)      NOT NULL DEFAULT 0, #O'Neill
  indexo2        CHAR(5)      NOT NULL DEFAULT 0, #O'Neill
  
  indexm1        CHAR(6)      NOT NULL DEFAULT 0, #Morohashi
  indexm2        CHAR(6)      NOT NULL DEFAULT 0, #Morohashi
  
  indexk1        CHAR(8)      NOT NULL DEFAULT 0, #KangXi
  indexk2        CHAR(8)      NOT NULL DEFAULT 0, #KangXi
  
  # Skip code is <num>-<num>-<num>
  skip1          TINYINT(1)   NOT NULL DEFAULT 0,  #1-4
  skip2          TINYINT(3)   NOT NULL DEFAULT 0,  #
  skip3          TINYINT(3)   NOT NULL DEFAULT 0,
  
  # Stroke count (first is correct, rest are common errors)
  strokecount1   TINYINT(2)   NOT NULL DEFAULT 0,  #max 30
  strokecount2   TINYINT(2)   NOT NULL DEFAULT 0,  #max 25
  strokecount3   TINYINT(2)   NOT NULL DEFAULT 0,  #max 24
  
  frequency      SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
  freqorder      SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,  #Suitable for sorting
  jouyougrade    TINYINT(1)   NOT NULL DEFAULT 0,
  
  fourcorner1    CHAR(6)      NOT NULL DEFAULT '',
  fourcorner2    CHAR(6)      NOT NULL DEFAULT '',
  
  # Henshall mnemonic - you need permission from the
  # Tuttle Publishing in order to have this data.
  mnemonic       VARCHAR(60)  NOT NULL DEFAULT '',
  
  # From Unihan.txt: The value of the character
  # when used in the writing of accounting numerals.
  numericvalue   INT(6)       NOT NULL DEFAULT 0

  
  ,UNIQUE utf8kanji(utf8kanji)
  ,UNIQUE indexv(indexv)
  ,UNIQUE jiscode(jiscode)
  ,KEY radb(radb), KEY radc(radc)
  ,KEY indexn(indexn)
  ,KEY indexh(indexh),    KEY indexe(indexe)
  ,KEY indexk(indexk),    KEY indexl(indexl)
  ,KEY indexo1(indexo1), KEY indexo2(indexo2)
  ,KEY skip1(skip1), KEY skip2(skip2), KEY skip3(skip3)
  ,KEY frequency(frequency), KEY freqorder(freqorder)
  ,KEY fourcorner1(fourcorner1), KEY fourcorner2(fourcorner2)
);

CREATE TABLE kanjiparts
(
  id       INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
  jiscode  SMALLINT(5) UNSIGNED NOT NULL,
  partcode SMALLINT(5) UNSIGNED NOT NULL
  
  ,KEY jiscode(jiscode)
  ,KEY partcode(partcode)
);

CREATE TABLE kanjiradstrokes
(
  partcode SMALLINT(5) UNSIGNED NOT NULL PRIMARY KEY,
  strokes  TINYINT(2) UNSIGNED NOT NULL
  ,KEY strokes(strokes)
);


CREATE TABLE kanjisimilarity
(
  jiscode1 SMALLINT(5) UNSIGNED NOT NULL,
  jiscode2 SMALLINT(5) UNSIGNED NOT NULL,
  unsimilarity TINYINT(3) UNSIGNED NOT NULL
  ,PRIMARY KEY(jiscode1,jiscode2)
  ,KEY jiscode2(jiscode2)
  ,KEY unsimilarity(unsimilarity)
);

# Note: Max 3 per japkanji (appears so)
CREATE TABLE kanjikorea
(
  id       SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

  # Key to japkanji
  jiscode  SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
  
  romaji  VARCHAR(6) NOT NULL

  #, KEY jiscode(jiscode)
  #, KEY romaji(romaji(5))
);

# Note: Max 7 per japkanji (appears so)
CREATE TABLE kanjichina
(
  id       SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

  # Key to japkanji
  jiscode SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
  
  romaji  VARCHAR(7) NOT NULL

  , KEY jiscode(jiscode)
  , KEY romaji(romaji(5))
);

# Note: Max 19 per japkaji (appears so)
CREATE TABLE kanjijapan
(
  id      SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,

  # Key to japkanji
  jiscode SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
  
  # kana
  kana VARCHAR(32) BINARY NOT NULL,
  # kana as romaji
  kanar VARCHAR(16) NOT NULL,
  
  type TINYINT(1) NOT NULL DEFAULT 0

  , KEY jiscode(jiscode)
  , KEY kana(kana(16)), KEY kanar(kanar(8))
  , KEY type(type)
);

# Translations for each kanji
CREATE TABLE kanjitrans
(
  id SMALLINT(5) UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
  
  # Key to japkanji
  jiscode SMALLINT(5) UNSIGNED NOT NULL DEFAULT 0,
  
  # Translation
  name VARCHAR(128) NOT NULL
  
  , KEY jiscode(jiscode)
  , KEY name(name(12))
);
