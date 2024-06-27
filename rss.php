<?php
/*
 +-=========================================================================-+
 |                              phpKF Forum v3.00                            |
 +---------------------------------------------------------------------------+
 |                  Telif - Copyright (c) 2007 - 2019 phpKF                  |
 |                    www.phpKF.com   -   phpKF@phpKF.com                    |
 |                 Tüm hakları saklıdır - All Rights Reserved                |
 +---------------------------------------------------------------------------+
 |  Bu yazılım ücretsiz olarak kullanıma sunulmuştur.                        |
 |  Dağıtımı yapılamaz ve ücretli olarak satılamaz.                          |
 |  Yazılımı dağıtma, sürüm çıkarma ve satma hakları sadece phpKF`ye aittir. |
 |  Yazılımdaki kodlar hiçbir şekilde başka bir yazılımda kullanılamaz.      |
 |  Kodlardaki ve sayfa altındaki telif yazıları silinemez, değiştirilemez,  |
 |  veya bu telif ile çelişen başka bir telif eklenemez.                     |
 |  Yazılımı kullanmaya başladığınızda bu maddeleri kabul etmiş olursunuz.   |
 |  Telif maddelerinin değiştirilme hakkı saklıdır.                          |
 |  Güncel telif maddeleri için  phpKF.com/telif.php  adresini ziyaret edin. |
 +-=========================================================================-+*/


$phpkf_ayarlar_kip = "WHERE kip='1' OR etiket='site_posta'";
if (!defined('DOSYA_AYAR')) include 'ayar.php';
if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';
if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';
include_once('phpkf-bilesenler/seo.php');


$tarih = time();
$forum = '';
$anasayfa_cikis = '';
$forumlar_cikis = '';
$sayfano = 31;
$sayfa_adi = 'RSS Beslemesi';


function satir_atlama($metin)
{
	$metin = preg_replace('|\[quote=(.*?)\](.*?)\[/quote\]|si','<b><u>Alıntı Çizelgesi:</u>&nbsp; \\1</b><font face="Lucida Console" size="1"><br />\\2<br />-------------------</font><br />',$metin);

	$metin = preg_replace('|\[code=(.*?)\](.*?)\[/code\]|si','<b><u>Kod Çizelgesi:</u>&nbsp; \\1</b><font face="Lucida Console" size="1"><br />\\2<br />-------------------</font><br />',$metin);

	$metin = preg_replace('|\[img\]([a-z0-9?&\\/\-_+.:,=#@;]+?)\[/img\]|si','<img src="\\1" alt="Resim Ekleme" />',$metin);

	$metin = bbcode_acik($metin, 0);
	$metin = str_replace('&#38', '&', $metin);
	$metin = str_replace('<br>', '<br />', $metin);
	return $metin;
}


function yazi_kisalt($metin)
{
	//$metin = (mb_substr($metin, 0, 500, 'utf-8').'....');
	$metin = satir_atlama($metin).'<br /><br /><hr />';
	return $metin;
}


$yaz_saati = date('I');

if ($ayarlar['saat_dilimi'] >= 0)
{
	if ($ayarlar['saat_dilimi'] > 9)
	{
		if ($yaz_saati == 1) $gmt_ekle = ' +'.($ayarlar['saat_dilimi']+1).'00';
		else $gmt_ekle = ' +'.$ayarlar['saat_dilimi'].'00';
	}

	else
	{
		if ($yaz_saati == 1)
		{
			if ($ayarlar['saat_dilimi'] == '9') $gmt_ekle = ' +'.($ayarlar['saat_dilimi']+1).'00';
			else $gmt_ekle = ' +0'.($ayarlar['saat_dilimi']+1).'00';
		}

		else $gmt_ekle = ' +0'.$ayarlar['saat_dilimi'].'00';
	}
}



else 
{
	if ($ayarlar['saat_dilimi'] < -9)
	{
		$saat_dilimi = substr($ayarlar['saat_dilimi'], 1, 2);
		if ($yaz_saati == 1) $gmt_ekle = ' -'.($saat_dilimi-1).'00';
		else $gmt_ekle = ' -'.$saat_dilimi.'00';
	}

	else
	{
		if ($yaz_saati == 1) $gmt_ekle = ' -0'.($ayarlar['saat_dilimi'][1]-1).'00';
		else $gmt_ekle = ' -0'.$ayarlar['saat_dilimi'][1].'00';
	}
}




if ($ayarlar['f_dizin'] == '/') $fdizin = '';
else $fdizin = $ayarlar['f_dizin'];


if (empty($_GET['f'])) $_GET['f'] = 0;
else $_GET['f'] = zkTemizle($_GET['f']);


if (is_numeric($_GET['f']) == false)
{
	header('Location: hata.php?hata=14');
	exit();
}

if ($_GET['f'] == 0) unset($_GET['f']);





//  SADECE TEK FORUM ALTINDAKİ KONULAR  //
//  SADECE TEK FORUM ALTINDAKİ KONULAR  //
//  SADECE TEK FORUM ALTINDAKİ KONULAR  //

if ( (isset($_GET['f'])) AND ($_GET['f']) != '' ):
$atom = '?f='.$_GET['f'];


//	FORUM BAŞLIĞI ALINIYOR	//

$vtsorgu = "SELECT id,forum_baslik,okuma_izni,alt_forum FROM $tablo_forumlar WHERE id='$_GET[f]' LIMIT 1";
$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
$forum_satir = $vt->fetch_assoc($vtsonuc2);
$sayfano = '32,'.$forum_satir['id'];
$sayfa_adi = 'RSS Beslemesi: '.$forum_satir['forum_baslik'];



if (empty($forum_satir))
{
	header('Location: hata.php?hata=14');
	exit();
}

elseif ($forum_satir['okuma_izni'] != 0)
{
	header('Location: hata.php?hata=95');
	exit();
}



// ALT FORUM DEĞİLSE ALT FORUMLARINA BAK	//

if ($forum_satir['alt_forum'] == '0')
{
	// ALT FORUMLARIN BİLGİLERİ ÇEKİLİYOR	//

	$vtsorgu = "SELECT id,forum_baslik FROM $tablo_forumlar WHERE alt_forum='$forum_satir[id]' AND gizle='0' ORDER BY sira";
	$vtsonuc6 = $vt->query($vtsorgu) or die ($vt->hata_ver());


	if ($vt->num_rows($vtsonuc6))
$forum .= '<item>
<pubDate>'.zonedate2('D, d M Y H:i:s', $ayarlar['saat_dilimi'], false, $tarih).$gmt_ekle.'</pubDate>
<category><![CDATA[ALT FORUMLAR]]></category>
<title><![CDATA[ALT FORUMLAR]]></title>
<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/rss.php?f='.$_GET['f'].'</link>
<guid isPermaLink="false">'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/rss.php?f='.$_GET['f'].'</guid>
<description><![CDATA[ <b><font color="#ff0000">';


	while ($alt_forum_satir = $vt->fetch_assoc($vtsonuc6))
		$forum .= '<a href="'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/rss.php?f='.$alt_forum_satir['id'].'">'.$alt_forum_satir['forum_baslik'].'</a><br />';


	if ($vt->num_rows($vtsonuc6)) $forum .= '</b></font><br /><br /><hr /><br /> ]]></description>
</item>

';
}





//	BAŞLIK BİLGİLERİ ÇEKİLİYOR	//

$vtsorgu = "SELECT id,mesaj_baslik,mesaj_icerik,cevap_sayi,yazan,goruntuleme,tarih,kilitli,son_mesaj_tarihi
FROM $tablo_mesajlar WHERE silinmis='0' AND hangi_forumdan='$_GET[f]' ORDER BY son_mesaj_tarihi DESC LIMIT 0,$ayarlar[fsyfkota]";
$satir = $vt->query($vtsorgu) or die ($vt->hata_ver());
$baslik_sayi = $vt->num_rows($satir);


//  FORUMDA HİÇ BAŞLIK YOKSA  //

if ($baslik_sayi == 0)
$forum .= '<item>
<category><![CDATA['.$forum_satir['forum_baslik'].']]></category>
<title><![CDATA[Henüz yazı bulunmamaktadır]]></title>
<description><![CDATA[Bu forumda henüz hiçbir yazı bulunmamaktadır.]]></description>
<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('forum.php?f='.$forum_satir['id'], $forum_satir['forum_baslik']).'</link>
<guid isPermaLink="false">'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('forum.php?f='.$forum_satir['id'], $forum_satir['forum_baslik']).'</guid>
</item>';



//		BAŞLIKLAR SIRALANIYOR BAŞLANGIÇ		//

while ($baslik_sirala = $vt->fetch_assoc($satir)):


//	CEVAP YOKSA MESAJ TARİHİNİ YAZ	//

if ($baslik_sirala['cevap_sayi'] == 0):

//  mesaj başlığı ve içeriği yazdırılıyor  //

$forum .= '<item>
<pubDate>'.zonedate2('D, d M Y H:i:s', $ayarlar['saat_dilimi'], false, $baslik_sirala['tarih']).$gmt_ekle.'</pubDate>
<category><![CDATA['.$forum_satir['forum_baslik'].']]></category>
<title><![CDATA['.satir_atlama($baslik_sirala['mesaj_baslik']).']]></title>
<description><![CDATA[<b><u>Yazan:</u></b>&nbsp; <a href="'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('profil.php?kim='.$baslik_sirala['yazan'],$baslik_sirala['yazan']).'">'.$baslik_sirala['yazan'].'</a><br /><br />'.yazi_kisalt($baslik_sirala['mesaj_icerik']).']]></description>
<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$baslik_sirala['id'], $baslik_sirala['mesaj_baslik']).'</link>
<guid isPermaLink="false">'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$baslik_sirala['id'], $baslik_sirala['mesaj_baslik']).'</guid>
</item>

';


//	CEVAP VARSA SON CEVAP TARİHİNİ YAZ	//

else:

$vtsorgu = "SELECT id,hangi_basliktan,cevap_icerik,tarih,cevap_yazan FROM $tablo_cevaplar
			WHERE silinmis='0' AND hangi_basliktan='$baslik_sirala[id]' AND
			hangi_forumdan='$forum_satir[id]' ORDER BY tarih DESC LIMIT 0,1";
$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
$son_cevap = $vt->fetch_assoc($vtsonuc2);


//  BAŞLIK ÇOK SAYFALI İSE SON SAYFAYA GİT  //

if ($baslik_sirala['cevap_sayi'] > $ayarlar['ksyfkota'])
{
	$sayfaya_git = (($baslik_sirala['cevap_sayi']-1) / $ayarlar['ksyfkota']);
	settype($sayfaya_git,'integer');
	$sayfaya_git = ($sayfaya_git * $ayarlar['ksyfkota']);


	$forum .= '<item>
<pubDate>'.zonedate2('D, d M Y H:i:s', $ayarlar['saat_dilimi'], false, $son_cevap['tarih']).$gmt_ekle.'</pubDate>
<category><![CDATA['.$forum_satir['forum_baslik'].']]></category>
<title><![CDATA['.satir_atlama($baslik_sirala['mesaj_baslik']).']]></title>
<description><![CDATA[<b><u>Yazan:</u></b>&nbsp; <a href="'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('profil.php?kim='.$son_cevap['cevap_yazan'],$son_cevap['cevap_yazan']).'">'.$son_cevap['cevap_yazan'].'</a><br /><br />'.yazi_kisalt($son_cevap['cevap_icerik']).']]></description>
<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$son_cevap['hangi_basliktan'].'&ks='.$sayfaya_git, $baslik_sirala['mesaj_baslik'], '#c'.$son_cevap['id']).'</link>
<guid isPermaLink="false">'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$son_cevap['hangi_basliktan'].'&ks='.$sayfaya_git, $baslik_sirala['mesaj_baslik'], '#c'.$son_cevap['id']).'</guid>
</item>

';
}


else
$forum .= '<item>
<pubDate>'.zonedate2('D, d M Y H:i:s', $ayarlar['saat_dilimi'], false, $son_cevap['tarih']).$gmt_ekle.'</pubDate>
<category><![CDATA['.$forum_satir['forum_baslik'].']]></category>
<title><![CDATA['.satir_atlama($baslik_sirala['mesaj_baslik']).']]></title>
<description><![CDATA[<b><u>Yazan:</u></b>&nbsp; <a href="'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('profil.php?kim='.$son_cevap['cevap_yazan'],$son_cevap['cevap_yazan']).'">'.$son_cevap['cevap_yazan'].'</a><br /><br />'.yazi_kisalt($son_cevap['cevap_icerik']).']]></description>
<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$son_cevap['hangi_basliktan'], $baslik_sirala['mesaj_baslik'], '#c'.$son_cevap['id']).'</link>
<guid isPermaLink="false">'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$son_cevap['hangi_basliktan'], $baslik_sirala['mesaj_baslik'], '#c'.$son_cevap['id']).'</guid>
</item>

';

endif; // cevap varsa yoksa
endwhile; // sadece tek forum altındaki konular kapat









//  ANA SAYFADA GÖRÜNEN KONULAR //
//  ANA SAYFADA GÖRÜNEN KONULAR //
//  ANA SAYFADA GÖRÜNEN KONULAR //

else:

$guncel_ek = '';
$atom = '';

$forumlar_cikis .= '<item>
<category><![CDATA[TÜM FORUMLAR]]></category>
<title><![CDATA[TÜM FORUMLAR]]></title>
<pubDate>'.zonedate2('D, d M Y H:i:s', $ayarlar['saat_dilimi'], false, $tarih).$gmt_ekle.'</pubDate>
<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/rss.php</link>
<guid isPermaLink="false">'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/rss.php</guid>
<description><![CDATA[ <b><font color="#ff0000">';



//	FORUM DALI BİLGİLERİ ÇEKİLİYOR	//

$vtsorgu = "SELECT * FROM $tablo_dallar ORDER BY sira";
$vtsonuc3 = $vt->query($vtsorgu) or die ($vt->hata_ver());

while ($dallar_satir = $vt->fetch_assoc($vtsonuc3)):

$vtsorgu = "SELECT id FROM $tablo_forumlar WHERE dal_no='$dallar_satir[id]' AND alt_forum='0' AND gizle='0'";
$vtsonuc7 = $vt->query($vtsorgu) or die ($vt->hata_ver());

if ($vt->num_rows($vtsonuc7)) $forumlar_cikis .= '<br />'.$dallar_satir['ana_forum_baslik'].'<br />';



// ÜST FORUM BİLGİLERİ ÇEKİLİYOR	//

$vtsorgu = "SELECT id,forum_baslik,okuma_izni,alt_forum,gizle
		FROM $tablo_forumlar WHERE dal_no='$dallar_satir[id]' AND alt_forum='0' ORDER BY sira";
$vtsonuc4 = $vt->query($vtsorgu) or die ($vt->hata_ver());


while ($forum_satir = $vt->fetch_assoc($vtsonuc4)):

// üst forum yetkilendirilmişse konularını gösterme
if ($forum_satir['okuma_izni'] != 0) $guncel_ek .= " AND hangi_forumdan!='$forum_satir[id]' ";

if ($forum_satir['gizle'] != 1)
{
	$tumforum_satir[$forum_satir['id']] = $forum_satir['forum_baslik'];
	$forumlar_cikis .= '<a href="'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/rss.php?f='.$forum_satir['id'].'">'.$forum_satir['forum_baslik'].'</a>';
}



// ALT FORUM BİLGİLERİ ÇEKİLİYOR	//

$vtsorgu = "SELECT id,forum_baslik,okuma_izni,gizle
		FROM $tablo_forumlar WHERE alt_forum='$forum_satir[id]' ORDER BY sira";
$vtsonuc5 = $vt->query($vtsorgu) or die ($vt->hata_ver());


if (($vt->num_rows($vtsonuc5)) AND ($forum_satir['gizle'] != 1)) $forumlar_cikis .= ' (</b>';


while ($alt_forum_satir = $vt->fetch_assoc($vtsonuc5)):

// alt forum yetkilendirilmişse gösterme
if ($alt_forum_satir['okuma_izni'] != 0) $guncel_ek .= " AND hangi_forumdan!='$alt_forum_satir[id]' ";

if (($forum_satir['gizle'] != 1) AND ($alt_forum_satir['gizle'] != 1))
{
	$tumforum_satir[$alt_forum_satir['id']] = $alt_forum_satir['forum_baslik'];
	$forumlar_cikis .= '<a href="'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/rss.php?f='.$alt_forum_satir['id'].'">'.$alt_forum_satir['forum_baslik'].'</a> - ';
}

endwhile;


if (($vt->num_rows($vtsonuc5)) AND ($forum_satir['gizle'] != 1))
{
	$forumlar_cikis = substr($forumlar_cikis, 0, -2);
	$forumlar_cikis .= '<b>) ';
}

if ($forum_satir['gizle'] != 1) $forumlar_cikis .= '<br />';


endwhile; // üst forumlar
$forumlar_cikis .= '<br />';
endwhile; // forum dalı





//  EN YENİ BAŞLIĞIN BİLGİLERİ ÇEKİLİYOR  //

$vtsorgu = "SELECT id,tarih,mesaj_baslik,mesaj_icerik,yazan,cevap_sayi,hangi_forumdan FROM $tablo_mesajlar
		WHERE silinmis='0' $guncel_ek ORDER BY son_mesaj_tarihi DESC LIMIT 20";
$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
$konu_sayi = $vt->num_rows($vtsonuc2);


//  FORUMDA HİÇ BAŞLIK YOKSA  //

if ($konu_sayi == 0):
$anasayfa_cikis .= '<item>
<category><![CDATA['.$ayarlar['site_adi'].']]></category>
<title><![CDATA[Henüz yazı bulunmamaktadır]]></title>
<description><![CDATA[Forumda henüz hiçbir yazı bulunmamaktadır.]]></description>
<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.$phpkf_dosyalar['forum'].'</link>
<guid isPermaLink="false">'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.$phpkf_dosyalar['forum'].'</guid>
</item>';



else:

while ($son_konular = $vt->fetch_assoc($vtsonuc2)):







//	CEVAP YOKSA MESAJ TARİHİNİ YAZ	//

if ($son_konular['cevap_sayi'] == 0):





//  son mesaj başlığı ve içeriği yazdırılıyor  //

$anasayfa_cikis .= '
<item>
<pubDate>'.zonedate2('D, d M Y H:i:s', $ayarlar['saat_dilimi'], false, $son_konular['tarih']).$gmt_ekle.'</pubDate>
<category><![CDATA['.$tumforum_satir[$son_konular['hangi_forumdan']].']]></category>
<title><![CDATA['.satir_atlama($son_konular['mesaj_baslik']).']]></title>
';

if ($forum_satir['okuma_izni'] != 0)
$anasayfa_cikis .= '<description><![CDATA[Buradaki yazıları ancak forum üzerinden okuyabilirsiniz.]]></description>
';

else $anasayfa_cikis .= '<description><![CDATA[<b><u>Yazan:</u></b>&nbsp; <a href="'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('profil.php?kim='.$son_konular['yazan'],$son_konular['yazan']).'">'.$son_konular['yazan'].'</a><br /><br />'.yazi_kisalt($son_konular['mesaj_icerik']).']]></description>
';

$anasayfa_cikis .= '<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$son_konular['id'], $son_konular['mesaj_baslik']).'</link>
<guid isPermaLink="false">'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$son_konular['id'], $son_konular['mesaj_baslik']).'</guid>
</item>

';


//	CEVAP VARSA SON CEVAP TARİHİNİ YAZ	//

else:

$vtsorgu = "SELECT id,hangi_basliktan,cevap_icerik,tarih,cevap_yazan FROM $tablo_cevaplar
		WHERE silinmis='0' AND hangi_basliktan='$son_konular[id]' ORDER BY tarih DESC LIMIT 0,1";
$vtsonucs = $vt->query($vtsorgu) or die ($vt->hata_ver());
$son_cevap = $vt->fetch_assoc($vtsonucs);


//  BAŞLIK ÇOK SAYFALI İSE SON SAYFAYA GİT  //

if ($son_konular['cevap_sayi'] > $ayarlar['ksyfkota'])
{
	$sayfaya_git = (($son_konular['cevap_sayi']-1) / $ayarlar['ksyfkota']);
	settype($sayfaya_git,'integer');
	$sayfaya_git = ($sayfaya_git * $ayarlar['ksyfkota']);


	$anasayfa_cikis .= '<item>
<pubDate>'.zonedate2('D, d M Y H:i:s', $ayarlar['saat_dilimi'], false, $son_cevap['tarih']).$gmt_ekle.'</pubDate>
<category><![CDATA['.$tumforum_satir[$son_konular['hangi_forumdan']].']]></category>
<title><![CDATA['.satir_atlama($son_konular['mesaj_baslik']).']]></title>
';

if ($forum_satir['okuma_izni'] != 0)
$anasayfa_cikis .= '<description><![CDATA[Buradaki yazıları ancak forum üzerinden okuyabilirsiniz.]]></description>
';

else $anasayfa_cikis .= '<description><![CDATA[<b><u>Yazan:</u></b>&nbsp; <a href="'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('profil.php?kim='.$son_cevap['cevap_yazan'],$son_cevap['cevap_yazan']).'">'.$son_cevap['cevap_yazan'].'</a><br /><br />'.yazi_kisalt($son_cevap['cevap_icerik']).']]></description>
';

$anasayfa_cikis .= '<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$son_cevap['hangi_basliktan'].'&ks='.$sayfaya_git, $son_konular['mesaj_baslik'], '#c'.$son_cevap['id']).'</link>
<guid isPermaLink="false">'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$son_cevap['hangi_basliktan'].'&ks='.$sayfaya_git, $son_konular['mesaj_baslik'], '#c'.$son_cevap['id']).'</guid>
</item>

';
}


else
{
	$anasayfa_cikis .= '
<item>
<pubDate>'.zonedate2('D, d M Y H:i:s', $ayarlar['saat_dilimi'], false, $son_cevap['tarih']).$gmt_ekle.'</pubDate>
<category><![CDATA['.$tumforum_satir[$son_konular['hangi_forumdan']].']]></category>
<title><![CDATA['.satir_atlama($son_konular['mesaj_baslik']).']]></title>
';

	if ($forum_satir['okuma_izni'] != 0)
$anasayfa_cikis .= '<description><![CDATA[Buradaki yazıları ancak forum üzerinden okuyabilirsiniz.]]></description>
';

	else $anasayfa_cikis .= '<description><![CDATA[<b><u>Yazan:</u></b>&nbsp; <a href="'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('profil.php?kim='.$son_cevap['cevap_yazan'],$son_cevap['cevap_yazan']).'">'.$son_cevap['cevap_yazan'].'</a><br /><br />'.yazi_kisalt($son_cevap['cevap_icerik']).']]></description>
';

	$anasayfa_cikis .= '<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$son_cevap['hangi_basliktan'], $son_konular['mesaj_baslik'], '#c'.$son_cevap['id']).'</link>
<guid isPermaLink="false">'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/'.linkver('konu.php?k='.$son_cevap['hangi_basliktan'], $son_konular['mesaj_baslik'], '#c'.$son_cevap['id']).'</guid>
</item>

';
}

endif; // cevap varsa yoksa
endwhile; // son 20 konu
endif; // başlık varsa yoksa

endif; // ana sayfa yada forum



//  BASLIK_KOD DAHİL EDİLİYOR   ///

if (!defined('DOSYA_OTURUM')) include 'phpkf-bilesenler/oturum.php';



header('Content-type: text/xml');
header("Content-type: text/xml; charset=utf-8");

//  XML ÇIKTISI YAZDIRILIYOR    //

$rss_cikis = '<?xml version="1.0" encoding="utf-8"?>
<rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom">
<channel>
<title><![CDATA['.satir_atlama($ayarlar['site_adi']).']]></title>
<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'</link>
<atom:link href="'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/rss.php'.$atom .'" rel="self" type="application/rss+xml" />
<description><![CDATA['.$ayarlar['site_adi'].']]></description> 
<language>tr-TR</language>
<copyright>phpKF 2007-'.date('Y').'</copyright>
<managingEditor>phpkf@phpkf.com (phpKF)</managingEditor>
<webMaster>'.$ayarlar['site_posta'].' ('.$ayarlar['site_adi'].')</webMaster>
<category><![CDATA['.$ayarlar['site_adi'].']]></category>
<lastBuildDate>'.zonedate2('D, d M Y H:i:s', $ayarlar['saat_dilimi'], false, $tarih).$gmt_ekle.'</lastBuildDate>
<generator>phpKF</generator>
<ttl>60</ttl>

<image>
<title><![CDATA['.satir_atlama($ayarlar['site_adi']).']]></title>
<link>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'</link>
<url>'.$protocol.'://'.$ayarlar['alanadi'].$fdizin.'/temalar/varsayilan/resimler/phpkf-b.png</url>
<width>100</width>
<height>32</height>
</image>

';


if ($anasayfa_cikis != '')
$rss_cikis .= $forumlar_cikis.'</b></font><br /><br /><hr /><br /> ]]></description>
</item>
'.$anasayfa_cikis;


else $rss_cikis .= $forum;

$rss_cikis .= '
</channel>
</rss>';

echo $rss_cikis;

?>