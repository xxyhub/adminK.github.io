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


if ( (isset($_GET['fno'])) AND (isset($_GET['kip'])) OR (isset($_POST['fno'])) AND (isset($_POST['kip'])) ):


$phpkf_ayarlar_kip = "WHERE kip='1' OR kip='3' OR kip='6'";
if (!defined('DOSYA_AYAR')) include 'ayar.php';
if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';
if (!defined('DOSYA_GUVENLIK')) include 'phpkf-bilesenler/guvenlik.php';
if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';


if ( isset($_GET['mesaj_no']) ) $mesaj_no = zkTemizle($_GET['mesaj_no']);
if ( isset($_POST['mesaj_no']) ) $mesaj_no = zkTemizle($_POST['mesaj_no']);

if ( isset($_GET['cevap_no']) ) $cevap_no = zkTemizle($_GET['cevap_no']);
else $cevap_no = 0;

if ( isset($_POST['cevap_no']) ) $cevap_no = zkTemizle($_POST['cevap_no']);

if ( isset($_GET['kip']) ) $kip = $_GET['kip'];
if ( isset($_POST['kip']) ) $kip = $_POST['kip'];

if ( isset($_GET['fsayfa']) ) $fsayfa = $_GET['fsayfa'];
elseif ( isset($_POST['fsayfa']) ) $fsayfa = $_POST['fsayfa'];
else $fsayfa = 0;

if ( isset($_GET['sayfa']) ) $sayfa = $_GET['sayfa'];
elseif ( isset($_POST['sayfa']) ) $sayfa = $_POST['sayfa'];
else $sayfa = 0;



//	DEĞİŞTİRİLEN BAŞLIKSA	//

if ($kip == 'mesaj')
{
	$vtsorgu = "SELECT id,yazan,mesaj_baslik,mesaj_icerik,bbcode_kullan,hangi_forumdan,ust_konu,kilitli,ifade FROM $tablo_mesajlar WHERE id='$mesaj_no' AND silinmis='0' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


	// konu yoksa uyarı ver //
	if (!$vt->num_rows($vtsonuc))
	{
		header('Location: hata.php?hata=47');
		exit();
	}


	$mesaj_degistir_satir = $vt->fetch_assoc($vtsonuc);
	$fno = $mesaj_degistir_satir['hangi_forumdan'];
	$yazan = $mesaj_degistir_satir['yazan'];
	$baslik = $mesaj_degistir_satir['mesaj_baslik'];
	$cevap_baslik = '';


	$vtsorgu = "SELECT id,okuma_izni,yazma_izni,konu_acma_izni,forum_baslik,alt_forum FROM $tablo_forumlar WHERE id='$fno' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$forum_satir = $vt->fetch_assoc($vtsonuc);


	//	İZİNLERDEN BİRİ SADECE YÖNETİCİLER İÇİNSE VEYA KAPALIYSA	//

	if ( ($forum_satir['okuma_izni'] == 1) OR ($forum_satir['konu_acma_izni'] == 1) OR ($forum_satir['yazma_izni'] == 1) OR ($forum_satir['okuma_izni'] == 5) OR ($forum_satir['konu_acma_izni'] == 5) OR ($forum_satir['yazma_izni'] == 5) )
	{
		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) )
		{
			header('Location: hata.php?hata=52');
			exit();
		}
	}


	// konu kilitli ise değiştirilemez uyarısı veriliyor //
	if ( ($mesaj_degistir_satir['kilitli'] == 1) AND (($kullanici_kim['yetki'] != 1) AND ($kullanici_kim['yetki'] != 2)) )
	{
		header('Location: hata.php?hata=50');
		exit();
	}
}


//	DEĞİŞTİRİLEN CEVAPSA	//

if ($kip == 'cevap')
{
	$vtsorgu = "SELECT id,cevap_yazan,cevap_baslik,cevap_icerik,bbcode_kullan,hangi_forumdan,hangi_basliktan,ifade FROM $tablo_cevaplar WHERE id='$cevap_no' AND silinmis='0' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


	// cevap yoksa uyarı ver //
	if (!$vt->num_rows($vtsonuc))
	{
		header('Location: hata.php?hata=55');
		exit();
	}


	$cevap_degistir_satir = $vt->fetch_assoc($vtsonuc);
	$fno = $cevap_degistir_satir['hangi_forumdan'];
	$yazan = $cevap_degistir_satir['cevap_yazan'];


	// konu kilitli ise değiştirilemez uyarısı veriliyor //

	$vtsorgu = "SELECT kilitli,mesaj_baslik FROM $tablo_mesajlar WHERE id='$cevap_degistir_satir[hangi_basliktan]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$konu_kilitlimi = $vt->fetch_assoc($vtsonuc);


	if ( ($konu_kilitlimi['kilitli'] == 1) AND (($kullanici_kim['yetki'] != 1) AND ($kullanici_kim['yetki'] != 2)) )
	{
		header('Location: hata.php?hata=51');
		exit();
	}


	$vtsorgu = "SELECT id,okuma_izni,yazma_izni,konu_acma_izni,forum_baslik,alt_forum FROM $tablo_forumlar WHERE id='$fno' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$forum_satir = $vt->fetch_assoc($vtsonuc);


	//	İZİNLERDEN BİRİ SADECE YÖNETİCİLER İÇİNSE VEYA KAPALIYSA	//

	if ( ($forum_satir['okuma_izni'] == 1) OR ($forum_satir['konu_acma_izni'] == 1) OR ($forum_satir['yazma_izni'] == 1) OR ($forum_satir['okuma_izni'] == 5) OR ($forum_satir['konu_acma_izni'] == 5) OR ($forum_satir['yazma_izni'] == 5) )
	{
		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) )
		{
			header('Location: hata.php?hata=52');
			exit();
		}
	}


	$baslik = $konu_kilitlimi['mesaj_baslik'];
	$cevap_baslik = '&nbsp;&raquo;&nbsp; '.$cevap_degistir_satir['cevap_baslik'];
}


//	DEĞİŞTİRMEYE YETKİLİ OLUP OLMADIĞINA BAKILIYOR	- BAŞI	//

//	YARDIMCI İSE	//
if ($kullanici_kim['yetki'] == 3)
{
	//	KENDİ YAZISI DEĞİLSE	//
	if ( ($yazan != $kullanici_kim['kullanici_adi']) )
	{
		if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$fno' AND yonetme='1' OR";
		else $grupek = "grup='0' AND";

		$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$fno' AND yonetme='1'";
		$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());

		if ( !$vt->num_rows($kul_izin) )
		{
			header('Location: hata.php?hata=52');
			exit();
		}
	}
}

//	YAZAN VEYA YÖNETİCİ İSE	//
elseif ( ($yazan == $kullanici_kim['kullanici_adi']) OR ($kullanici_kim['yetki'] == 1) OR ($kullanici_kim['yetki'] == 2) );

//	HİÇBİRİ DEĞİLSE	//
else
{
	header('Location: hata.php?hata=52');
	exit();
}

//	DEĞİŞTİRMEYE YETKİLİ OLUP OLMADIĞINA BAKILIYOR	- SONU	//





if (isset($_POST['mesaj_onizleme']))
{
	if ($kip == 'mesaj')
	{
		$sayfano = '13,'.$mesaj_no;
		$sayfa_adi = 'Konu Değiştirme Önizlemesi: '.$baslik;
	}
	else
	{
		$sayfano = '14,'.$mesaj_no.','.$cevap_degistir_satir['id'];
		$sayfa_adi = 'Cevap Değiştirme Önizlemesi: '.$baslik;
	}
}

else
{
	if ($kip == 'mesaj')
	{
		$sayfano = '15,'.$mesaj_no;
		$sayfa_adi = 'Konu Değiştirme: '.$baslik;
	}
	else
	{
		$sayfano = '16,'.$mesaj_no.','.$cevap_degistir_satir['id'];
		$sayfa_adi = 'Cevap Değiştirme: '.$baslik;
	}
}

include_once('phpkf-bilesenler/sayfa_baslik_forum.php');



//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/mesaj_yaz.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));




// üst forum - alt forum başlığı
if ($forum_satir['alt_forum'] != '0')
{
	$vtsorgu = "SELECT id,forum_baslik FROM $tablo_forumlar WHERE id='$forum_satir[alt_forum]' LIMIT 1";
	$vtsonuc_ust = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$forum_satir_ust = $vt->fetch_assoc($vtsonuc_ust);

	$ust_forum_baslik = '<span><a href="forum.php?f='.$forum_satir_ust['id'].'">'.$forum_satir_ust['forum_baslik'].'</a></span>';

	$alt_forum_baslik = '<span><a href="forum.php?f='.$fno.'&amp;fs='.$fsayfa.'">'.$forum_satir['forum_baslik'].'</a></span>';
}

else
{
	$ust_forum_baslik = '<span><a href="forum.php?f='.$fno.'&amp;fs='.$fsayfa.'">'.$forum_satir['forum_baslik'].'</a></span>';
	$alt_forum_baslik = '';
}

$sayfa_baslik = '<span><a href="konu.php?k='.$mesaj_no.'&amp;fs='.$fsayfa.'&amp;ks='.$sayfa.'">'.$baslik.'</a></span>';






			//		ÖNİZLEME TABLOSU BAŞI		//


if ( isset($_POST['mesaj_onizleme']) ):

	if ( empty($_POST['mesaj_icerik']) ):
		$javascript_kapali = '<center><br><b><font size="3" color="red">Önizleme özelliği için taraycınızın java özelliğinin açık olması gereklidir.</b></center><br>';

	else: $javascript_kapali = '';


// MESAJ SAHİBİNİN PROFİLİ ÇEKİLİYOR //

$vtsorgu = "SELECT id,kullanici_adi,gercek_ad,resim,katilim_tarihi,mesaj_sayisi,sehir_goster,sehir,web,imza,yetki,ozel_ad,engelle,gizli,son_hareket,sayfano
FROM $tablo_kullanicilar WHERE kullanici_adi='$yazan' LIMIT 1";

$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$mesaj_sahibi = $vt->fetch_assoc($vtsonuc);


//	ZARARLI KODLAR TEMİZLENİYOR	//

//	magic_quotes_gpc açıksa	//
if (get_magic_quotes_gpc())
{
	$_POST['mesaj_baslik'] = @ileti_yolla(stripslashes($_POST['mesaj_baslik']),3);
	$_POST['mesaj_icerik'] = @ileti_yolla(stripslashes($_POST['mesaj_icerik']),5);
}

//	magic_quotes_gpc kapalıysa	//
else
{
	$_POST['mesaj_baslik'] = @ileti_yolla($_POST['mesaj_baslik'],3);
	$_POST['mesaj_icerik'] = @ileti_yolla($_POST['mesaj_icerik'],5);
}



$onizleme_uye_adi = '<a href="profil.php?kim='.$mesaj_sahibi['kullanici_adi'].'">'.$mesaj_sahibi['kullanici_adi'].'</a>';


if (!empty($mesaj_sahibi['ozel_ad']))
	$onizleme_yetki = '<font class="ozel_ad"><u>'.$mesaj_sahibi['ozel_ad'].'</u></font>';

elseif ($mesaj_sahibi['id'] == 1)
	$onizleme_yetki = '<font class="kurucu"><u>'.$ayarlar['kurucu'].'</u></font>';

elseif ( $mesaj_sahibi['yetki'] == 1 )
	$onizleme_yetki = '<font class="yonetici"><u>'.$ayarlar['yonetici'].'</u></font>';

elseif ( $mesaj_sahibi['yetki'] == 2 )
	$onizleme_yetki = '<font class="yardimci"><u>'.$ayarlar['yardimci'].'</u></font>';

elseif ( $mesaj_sahibi['yetki'] == 3 )
	$onizleme_yetki = '<font class="blm_yrd"><u>'.$ayarlar['blm_yrd'].'</u></font>';

else $onizleme_yetki = '<font class="kullanici">'.$ayarlar['kullanici'].'</font>';


if ($mesaj_sahibi['resim']) $onizleme_resim = '<img src="'.$mesaj_sahibi['resim'].'" alt="Kulanıcı Resmi">';
elseif ($ayarlar['v-uye_resmi'] != '') $onizleme_resim = '<img src="'.$ayarlar['v-uye_resmi'].'" alt="Varsayılan Kullanıcı Resmi">';
else $onizleme_resim = '';



$onizleme_katilim = zonedate('d.m.Y', $ayarlar['saat_dilimi'], false, $mesaj_sahibi['katilim_tarihi']);


if ($mesaj_sahibi['sehir_goster'] == 1)
{
	if ($mesaj_sahibi['sehir'] != '') $onizleme_sehir = $mesaj_sahibi['sehir'];
	else $onizleme_sehir = 'Yok';
}

else $onizleme_sehir = 'Gizli';



if (empty($mesaj_sahibi['gercek_ad']))
	$onizleme_durum = '<font color="#FF0000">üye silinmiş</font>';

elseif ($mesaj_sahibi['engelle'] == 1)
	$onizleme_durum = '<font color="#FF0000">üye uzaklaştırılmış</font>';

elseif ($mesaj_sahibi['gizli'] == 1)
	$onizleme_durum = '<font color="#FF0000">Gizli</font>';

elseif ( (($mesaj_sahibi['son_hareket'] + $ayarlar['uye_cevrimici_sure']) > time() ) AND
		($mesaj_sahibi['sayfano'] != '-1') )
	$onizleme_durum = '<font color="#339900">Forumda</font>';

else $onizleme_durum = '<font color="#FF0000">Forumda Değil</font>';



$onizleme_eposta = '<a title="Forum üzerinden e-posta gönder" href="eposta.php?kim='.$mesaj_sahibi['kullanici_adi'].'">';


if ($mesaj_sahibi['web'])
$onizleme_web = '<br><a href="'.$mesaj_sahibi['web'].'" target="_blank">Web Adresi</a>';

else $onizleme_web = '';


$onizleme_io = '<a href="oi_yaz.php?ozel_kime='.$mesaj_sahibi['kullanici_adi'].'">';

$onizleme_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, time());




	//	BAŞLIK İÇERİĞİ YAZDIRILIYOR	//
	//	VARSA İMZA BİLGİLERİ YAZDIRILIYOR	//


$onizleme_mesaj = $_POST['mesaj_icerik'];

if (isset($_POST['ifade'])) $onizleme_mesaj = ifadeler($onizleme_mesaj);

if ((isset($_POST['bbcode_kullan'])) AND ($ayarlar['bbcode'] == 1)) $onizleme_mesaj = bbcode_acik($onizleme_mesaj,1);
else $onizleme_mesaj = bbcode_kapali($onizleme_mesaj);

if ( (isset($mesaj_sahibi['imza'])) AND ($mesaj_sahibi['imza'] != '') )
{
	if ($ayarlar['bbcode'] == 1)
	$onizleme_imza = bbcode_acik(ifadeler($mesaj_sahibi['imza']),0);

	else $onizleme_imza = bbcode_kapali(ifadeler($mesaj_sahibi['imza']));
}

else $onizleme_imza = '';



//	veriler tema motoruna yollanıyor	//

$ornek1->kosul('1', array('{ONIZLEME_BASLIK}' => $_POST['mesaj_baslik'],
'{ONIZLEME_UYE_ADI}' => $onizleme_uye_adi,
'{ONIZLEME_GERCEK_AD}' => $mesaj_sahibi['gercek_ad'],
'{ONIZLEME_YETKISI}' => $onizleme_yetki,
'{ONIZLEME_RESIM}' => $onizleme_resim,
'{ONIZLEME_KATILIM}' => $onizleme_katilim,
'{ONIZLEME_MESAJ_SAYI}' => NumaraBicim($mesaj_sahibi['mesaj_sayisi']),
'{ONIZLEME_SEHIR}' => $onizleme_sehir,
'{ONIZLEME_DURUM}' => $onizleme_durum,
'{ONIZLEME_EPOSTA}' => $onizleme_eposta,
'{ONIZLEME_WEB}' => $onizleme_web,
'{ONIZLEME_OI}' => $onizleme_io,
'{ONIZLEME_TARIH}' => $onizleme_tarih,
'{ONIZLEME_MESAJ}' => $onizleme_mesaj,
'{ONIZLEME_IMZA}' => $onizleme_imza), true);


endif;

else: $ornek1->kosul('1', array('' => ''), false);

endif;



						//	ÖNİZLEME TABLOSU SONU	//






if (isset($_POST['mesaj_baslik']))
	$form_baslik = $_POST['mesaj_baslik'];

elseif (isset($mesaj_degistir_satir['mesaj_baslik']))
	$form_baslik = $mesaj_degistir_satir['mesaj_baslik'];

elseif (isset($cevap_degistir_satir['cevap_baslik']))
	$form_baslik = $cevap_degistir_satir['cevap_baslik'];



if (isset($_POST['mesaj_icerik']))
	$form_icerik = $_POST['mesaj_icerik'];

elseif (isset($mesaj_degistir_satir['mesaj_icerik']))
	$form_icerik = $mesaj_degistir_satir['mesaj_icerik'];

elseif (isset($cevap_degistir_satir['cevap_icerik']))
	$form_icerik = $cevap_degistir_satir['cevap_icerik'];



//  BBCODE AÇMA - KAPATMA    //
$form_ozellik = '';

if ($ayarlar['bbcode'] == 1)
{
	$form_ozellik .= '<label style="cursor:pointer"><input type="checkbox" name="bbcode_kullan" ';
	if (isset($_POST['mesaj_onizleme']))
	{
		if (isset($_POST['bbcode_kullan'])) $form_ozellik .= ' checked="checked"';
	}
	else
	{
		if ((isset($mesaj_degistir_satir['bbcode_kullan'])) AND ($mesaj_degistir_satir['bbcode_kullan'] != 1));
		elseif ((isset($cevap_degistir_satir['bbcode_kullan'])) AND ($cevap_degistir_satir['bbcode_kullan'] != 1));
		else $form_ozellik .= ' checked="checked"';
	}
	$form_ozellik .= '>Bu iletide BBCode kullan</label>';
}

// bbcode kapalı ise
else $form_ozellik .= '<input type="hidden" name="bbcode_kullan">&nbsp;BBCode Kapalı';



//  İFADE AÇMA - KAPATMA    //
$form_ozellik .= '<br><label style="cursor:pointer"><input type="checkbox" name="ifade" ';
if (isset($_POST['mesaj_onizleme']))
{
	if (isset($_POST['ifade'])) $form_ozellik .= ' checked="checked"';
}
else
{
	if ((isset($mesaj_degistir_satir['ifade'])) AND ($mesaj_degistir_satir['ifade'] != 1));
	elseif ((isset($cevap_degistir_satir['ifade'])) AND ($cevap_degistir_satir['ifade'] != 1));
	else $form_ozellik .= ' checked="checked"';
}
$form_ozellik .= '>Bu iletide ifade kullan</label>';




//	ÜST KONU SEÇENEĞİ KULLANICIYA GÖRE GÖSTERİLİYOR	- BAŞI //

//	YÖNETİCİ İSE	//
if ( ($kip == 'mesaj') AND (($kullanici_kim['yetki'] == 1) OR ($kullanici_kim['yetki'] == 2)) )
{
	$form_ozellik .= '<br><label style="cursor:pointer"><input type="checkbox" name="ust_konu" ';
	if (isset($_POST['mesaj_onizleme']))
	{
		if (isset($_POST['ust_konu'])) $form_ozellik .= ' checked="checked"';
	}
	else
	{
		if ((isset($mesaj_degistir_satir['ust_konu'])) AND ($mesaj_degistir_satir['ust_konu'] != 1));
		else $form_ozellik .= ' checked="checked"';
	}
	$form_ozellik .= '>Mesajı üst konu yap</label>';
}


//	YARDIMCI İSE	//
elseif ( ($kip == 'mesaj') AND ($kullanici_kim['yetki'] == 3) )
{
	if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$fno' AND yonetme='1' OR";
	else $grupek = "grup='0' AND";

	$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$fno' AND yonetme='1'";
	$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());

	//	YÖNETME YETKİSİ VARSA	//
	if ($vt->num_rows($kul_izin))
	{
		$form_ozellik .= '<br><label style="cursor:pointer"><input type="checkbox" name="ust_konu" ';
		if (isset($_POST['mesaj_onizleme']))
		{
			if (isset($_POST['ust_konu'])) $form_ozellik .= ' checked="checked"';
		}
		else
		{
			if ((isset($mesaj_degistir_satir['ust_konu'])) AND ($mesaj_degistir_satir['ust_konu'] != 1));
			else $form_ozellik .= ' checked="checked"';
		}
		$form_ozellik .= '>Mesajı üst konu yap</label>';
	}
}

//	ÜST KONU SEÇENEĞİ KULLANICIYA GÖRE GÖSTERİLİYOR	- SONU //




// link ağacı
$forum_anasayfa = '<span><a href="'.$phpkf_dosyalar['forum'].'">Forum Ana Sayfası</a></span>';


if (isset($_GET['alinti'])) $mesaj_alinti = $_GET['alinti'];
else $mesaj_alinti = '';


$form_bilgi1 = '<form action="phpkf-bilesenler/mesaj_degistir_yap.php" method="post" onsubmit="return denetle_yazi()" name="duzenleyici_form" id="duzenleyici_form">
<input type="hidden" name="mesaj_degisti_mi" value="form_dolu">
<input type="hidden" name="sayfa_onizleme" value="mesaj_degistir">
<input type="hidden" name="mesaj_onizleme" value="Önizleme">
<input type="hidden" name="fno" value="'.$fno.'">
<input type="hidden" name="kip" value="'.$kip.'">
<input type="hidden" name="mesaj_no" value="'.$mesaj_no.'">
<input type="hidden" name="cevap_no" value="'.$cevap_no.'">
<input type="hidden" name="fsayfa" value="'.$fsayfa.'">
<input type="hidden" name="sayfa" value="'.$sayfa.'">';


if (!isset($javascript_kapali)) $javascript_kapali = '';


//	TEMA UYGULANIYOR	//

$ornek1->kosul('2', array('' => ''), false);
$ornek1->kosul('3', array('' => ''), false);
$ornek1->kosul('5', array('' => ''), false);


$dongusuz = array('{FORUM_ANASAYFA}' => $forum_anasayfa,
'{FORUM_BASLIK}' => $ust_forum_baslik,
'{ALT_FORUM_BASLIK}' => $alt_forum_baslik,
'{SAYFA_BASLIK}' => $sayfa_baslik,
'{CEVAP_BASLIK}' => $cevap_baslik,
'{SAYFA_KIP}' => 'İleti Değiştir',
'{FORM_BASLIK}' => $form_baslik,
'{FORM_ICERIK}' => $form_icerik,
'{FORM_OZELLIK}' => $form_ozellik,
'{JAVASCRIPT_KAPALI}' => $javascript_kapali,
'{FORM_BILGI1}' => $form_bilgi1);


$ornek1->dongusuz($dongusuz);

eval(TEMA_UYGULA);
$gec='';



else:
header('Location: hata.php?hata=14');
exit();


endif;
?>