<?php
//TITLE=Japanese/English web dictionary application

$title = 'Japanese/English web dictionary application';
$progname = 'japkanji';

$text = array(
   '1. Purpose' => "

", '1.1. Tools' => "

Several japanese related tools.

", '1.1.1. Dictionary' => "

See an example of the application installation working at
<a href=\"http://kanjidict.stc.cx/dict\">http://kanjidict.stc.cx/dict</a>.

", '1.1.1. Kanji information' => "

Provides varius information about kanji, like different book
indices etc, and search methods. 
<p>
See an example of the application installation working at
<a href=\"http://kanjidict.stc.cx/search\">http://kanjidict.stc.cx/search</a>.

", '1.1.1. Unicode map' => "

Allows you to pick up regions of UNICODE to display in your web browser.
<p>
See an example of the application installation working at
<a href=\"http://kanjidict.stc.cx/unicodemap\">http://kanjidict.stc.cx/unicodemap</a>.

", '1.1. Functions' => "

These small features written in php are included:

<ul>
 <li>gzip compression filter</li>
 <li>a small caching class</li>
 <li>Shoudoka-dispatcher</li>
 <li>Unicode to UTF8 converter</li>
</ul>

", '1. Copying' => "

These applications
have been written by Joel Yliluoma, a.k.a.
<a href=\"http://iki.fi/bisqwit/\">Bisqwit</a>,<br>
and are being distributed under the terms of the
<a href=\"http://www.gnu.org/licenses/licenses.html#GPL\">General Public License</a> (GPL).<br>
However, the dictionaries come with different licenses.
They all have been downloaded from the
<a href=\"http://www.csse.monash.edu.au/~jwb/edict.html\">home page of the EDICT project</a>.<br>
Here is a list of the dictionaries and their licenses:<ul>
<li><em>Aviation</em>: Feel free to do anything, just don't charge anybody else for it.</li>
<li><em>Classical</em>: Can be used, with acknowledgement, for any free software or server.</li>
<li><em>Concrete</em>: Please include the readme and happy to receive email.</li>
<li><em>Ediclsd3</em>: Free for personal, but public or commercial util need a written permission.</li>
<li><em>Lawgledt</em>: Distributable, but not to be sold.</li>
<li><em>Lingdic</em>: Distributable, but not to be sold. Publishing on paper needs a written permission.</li>
<li><em>Findic, Geodic, Mktdic, Pandpgls, Stardict, 4jwords, Engscidich, j_places</em>: Unknown</li>
<li><em>Compverb</em>: Subset of EDICT, see below</li>
<li><em>Compdic, edict, enamdict, kanjidic</em>: <a href=\"http://www.csse.monash.edu.au/groups/edrdg/newlic.html\">EDICT license</a></li>
</ul>

", '1. HTML code' => "

If what you want is not a local copy of the dictionary application
but just a piece of html code to put on your web page, here's some.
Replace \"unihtml\" with the character set of your web page or leave
it like it is.
<p>
<code>&lt;form method=GET action=\"http://kanjidict.stc.cx/\"&gt;<br>
&nbsp;&lt;input type=hidden name=\"inset\" value=\"unihtml\"&gt;<br>
&nbsp;&lt;table cellspacing=0 cellpadding=0&gt;&lt;tr&gt; <br>
&nbsp; &lt;td width=125&gt;&amp;#26085;&amp;#26412;&amp;#35486;&amp;#25506;&amp;#12377;&lt;br&gt;&lt;small&gt;(nihongo sagasu)&lt;/small&gt;:<br>
&nbsp; &lt;td valign=top&gt;&lt;input type=text name=s size=20&gt;<br>
&nbsp; &lt;td valign=top&gt;&lt;input type=submit value=\"Go\"&gt;<br>
&nbsp;&lt;/tr&gt;&lt;/table&gt;<br>
&lt;/form&gt;<br></code>

", '1. Manual' => "

To be written.

", '1. Requirements' => "

Japkanji requires <a href=\"http://www.mysql.com/\">MySQL</a>
and <a href=\"http://www.php.net/\">PHP</a> to run;
<a href=\"http://kakasi.namazu.org/\">Kakasi</a> and
<a href=\"http://www.gnu.org/software/recode/\">GNU Recode</a>
for database generation,
<a href=\"http://www.perl.org\">Perl</a> for configuration,
<a href=\"http://oktober.stc.cx/source/charconv.html\">charconv</a> for optional &amp;inset -parameter handling,
and
<a href=\"http://www.gnu.org/software/wget/\">wget</a> for updating
the dictionary files from the EDICT master site.<br>
<a href=\"http://www.gnu.org/software/make/\">GNU Make</a> is also required.

", '1. How to install' => "

From what feedback I have got, this package is not easy to install.<br>
Which is not really a surprise to me....<br>
But some have succeeded. Ganbatte kudasai :)

");
include '/WWW/progdesc.php';
