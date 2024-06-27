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


if ((isset($_GET['fno'])) AND (isset($_GET['kip'])) OR  (isset($_POST['fno'])) AND (isset($_POST['kip']))):


$phpkf_ayarlar_kip = "WHERE kip='1' OR kip='3' OR kip='6'";
if (!defined('DOSYA_AYAR')) include 'ayar.php';
if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';
if (!defined('DOSYA_GUVENLIK')) include 'phpkf-bilesenler/guvenlik.php';
if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';


if ( isset($_GET['mesaj_no']) )
{
    if (is_numeric($_GET['mesaj_no']) == false)
    {
        header('Location: hata.php?hata=47');
        exit();
    }

    else $mesaj_no = @zkTemizle($_GET['mesaj_no']);
}

else $mesaj_no = 0;


if ( isset($_POST['mesaj_no']) )
{
    if (is_numeric($_POST['mesaj_no']) == false)
    {
        header('Location: hata.php?hata=47');
        exit();
    }

    else $mesaj_no = @zkTemizle($_POST['mesaj_no']);
}


if ( isset($_GET['fno']) )
{
    if (is_numeric($_GET['fno']) == false)
    {
        header('Location: hata.php?hata=14');
        exit();
    }

    else $fno = @zkTemizle($_GET['fno']);
}


if ( isset($_POST['fno']) )
{
    if (is_numeric($_POST['fno']) == false)
    {
        header('Location: hata.php?hata=14');
        exit();
    }

    else $fno = @zkTemizle($_POST['fno']);
}


if ( isset($_GET['cevap_no']) ) $cevap_no = @zkTemizle($_GET['cevap_no']);
if ( isset($_POST['cevap_no']) ) $cevap_no = @zkTemizle($_POST['cevap_no']);


if ( isset($_GET['kip']) )
{
    $kip = $_GET['kip'];
    $kip = @zkTemizle($kip);
    $kip = @zkTemizle4($kip);
}
if ( isset($_POST['kip']) ) $kip = $_POST['kip'];


if ( isset($_GET['fsayfa']) )
{
    $fsayfa = $_GET['fsayfa'];
    $fsayfa = @zkTemizle($fsayfa);
    $fsayfa = @zkTemizle4($fsayfa);
}
elseif ( isset($_POST['fsayfa']) ) $fsayfa = $_POST['fsayfa'];
else $fsayfa = 0;


if ( isset($_GET['sayfa']) )
{
    $sayfa = $_GET['sayfa'];
    $sayfa = @zkTemizle($sayfa);
    $sayfa = @zkTemizle4($sayfa);
}
elseif ( isset($_POST['sayfa']) ) $sayfa = $_POST['sayfa'];
else $sayfa = 0;


// FORUM BİLGİLERİ ÇEKİLİYOR //

$vtsorgu = "SELECT id,okuma_izni,yazma_izni,konu_acma_izni,forum_baslik,alt_forum FROM $tablo_forumlar WHERE id='$fno' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$forum_satir = $vt->fetch_array($vtsonuc);

if (!$vt->num_rows($vtsonuc))
{
	header('Location: hata.php?hata=14');
	exit();
}



		//	FORUM YETKİLERİ - BAŞI	//
		//	FORUM YETKİLERİ - BAŞI	//



// forum okumaya kapalıysa sadece yöneticiler girebilir
if ($forum_satir['okuma_izni'] == 5)
{
	if ( (!isset($kullanici_kim['yetki']) ) OR ($kullanici_kim['yetki'] != 1) )
	{
		header('Location: hata.php?hata=164');
		exit();
	}
}



	//	KULLANICIYA GÖRE CEVAP YAZMA - BAŞI		//

if ($kip == 'cevapla')
{
	// KONUNUN KİLİT DURUMUNA BAKILIYOR

	$vtsorgu = "SELECT kilitli,mesaj_baslik,hangi_forumdan FROM $tablo_mesajlar WHERE id='$mesaj_no' AND silinmis='0' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$kilit_satir = $vt->fetch_array($vtsonuc);

	// konu yok uyarısı
	if (!$vt->num_rows($vtsonuc))
	{
		header('Location: hata.php?hata=47');
		exit();
	}

	// konu kilitli uyarısı
	elseif ($kilit_satir['kilitli'] == 1)
	{
		header('Location: hata.php?hata=57');
		exit();
	}


	// FORUM BİLGİLERİ ÇEKİLİYOR //

	$vtsorgu = "SELECT id,okuma_izni,yazma_izni,konu_acma_izni,forum_baslik,alt_forum FROM $tablo_forumlar WHERE id='$kilit_satir[hangi_forumdan]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$forum_satir = $vt->fetch_array($vtsonuc);


	//	OKUMA İZNİ SADECE YÖNETİCİLER İÇİNSE	//

	if ($forum_satir['okuma_izni'] == 1)
	{
		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) )
		{
			header('Location: hata.php?hata=15');
			exit();
		}
	}


	//	OKUMA İZNİ YÖNETİCİLER VE YARDIMCILAR İÇİNSE	//

	elseif ($forum_satir['okuma_izni'] == 2)
	{
		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1)
			AND ($kullanici_kim['yetki'] != 2) AND ($kullanici_kim['yetki'] != 3) )
		{
			header('Location: hata.php?hata=16');
			exit();
		}
	}


	//	OKUMA İZNİ SADECE ÖZEL ÜYELER İÇİNSE	//

	elseif ($forum_satir['okuma_izni'] == 3)
	{
		//	YÖNETİCİ DEĞİLSE YARDIMCILIĞINA BAK	//

		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) AND ($kullanici_kim['yetki'] != 2) )
		{
			if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$kilit_satir[hangi_forumdan]' AND okuma='1' OR";
			else $grupek = "grup='0' AND";

			$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$kilit_satir[hangi_forumdan]' AND okuma='1'";
			$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());

			if ( !$vt->num_rows($kul_izin) )
			{
				header('Location: hata.php?hata=17');
				exit();
			}
		}
	}


	//	CEVAP YAZMAYA KAPALIYSA	//

	if ($forum_satir['yazma_izni'] == 5)
	{
		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) )
		{
			header('Location: hata.php?hata=193');
			exit();
		}
	}


	//	CEVAP YAZMA İZNİ SADECE YÖNETİCİLER İÇİNSE	//

	elseif ($forum_satir['yazma_izni'] == 1)
	{
		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) )
		{
			header('Location: hata.php?hata=58');
			exit();
		}
	}


	//	CEVAP YAZMA İZNİ YÖNETİCİLER VE YARDIMCILAR İÇİNSE	//

	elseif ($forum_satir['yazma_izni'] == 2)
	{
		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1)
			AND ($kullanici_kim['yetki'] != 2) AND ($kullanici_kim['yetki'] != 3) )
		{
			header('Location: hata.php?hata=59');
			exit();
		}
	}


	//	CEVAP YAZMA İZNİ SADECE ÖZEL ÜYELER İÇİNSE	//

	elseif ($forum_satir['yazma_izni'] == 3)
	{
		//	YÖNETİCİ DEĞİLSE KOŞULLARA BAK	//

		if ( (isset($kullanici_kim['yetki'])) AND ($kullanici_kim['yetki'] != 1) AND ($kullanici_kim['yetki'] != 2) )
		{
			if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$fno' AND yazma='1' OR";
			else $grupek = "grup='0' AND";

			$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$fno' AND yazma='1'";
			$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());

			if ( !$vt->num_rows($kul_izin) )
			{
				header('Location: hata.php?hata=60');
				exit();
			}
		}
	}
}

	//	KULLANICIYA GÖRE CEVAP YAZMA - SONU			//




	//	KULLANICIYA GÖRE KONU AÇMA - BAŞI		//

else
{
	//	OKUMA İZNİ SADECE YÖNETİCİLER İÇİNSE	//

	if ($forum_satir['okuma_izni'] == 1)
	{
		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) )
		{
			header('Location: hata.php?hata=15');
			exit();
		}
	}


	//	KONU AÇMAYA KAPALIYSA 	//

	elseif ($forum_satir['konu_acma_izni'] == 5)
	{
		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) )
		{
			header('Location: hata.php?hata=192');
			exit();
		}
	}


	//	SADECE YÖNETİCİLER İÇİNSE	//

	elseif ($forum_satir['konu_acma_izni'] == 1)
	{
		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) )
		{
			header('Location: hata.php?hata=165');
			exit();
		}
	}


	//	YÖNETİCİLER VE YARDIMCILAR İÇİNSE	//

	elseif ($forum_satir['konu_acma_izni'] == 2)
	{
		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1)
			AND ($kullanici_kim['yetki'] != 2) AND ($kullanici_kim['yetki'] != 3) )
		{
			header('Location: hata.php?hata=166');
			exit();
		}
	}


	//	SADECE ÖZEL ÜYELER İÇİNSE 	//

	elseif ($forum_satir['konu_acma_izni'] == 3)
	{
		//	YÖNETİCİ DEĞİLSE KOŞULLARA BAK	//

		if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) AND ($kullanici_kim['yetki'] != 2) )
		{
			if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$fno' AND konu_acma='1' OR";
			else $grupek = "grup='0' AND";

			$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$fno' AND konu_acma='1'";
			$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());

			if ( !$vt->num_rows($kul_izin) )
			{
				header('Location: hata.php?hata=167');
				exit();
			}
		}
	}
}

	//	KULLANICIYA GÖRE KONU AÇMA - SONU			//




		//	FORUM YETKİLERİ - SONU	//
		//	FORUM YETKİLERİ - SONU	//






if (isset($_POST['mesaj_onizleme']))
{
	if ($kip == 'cevapla')
	{
		$sayfano = '17,'.$mesaj_no;
		$sayfa_adi = 'Cevap Yazma Önizlemesi: '.$kilit_satir['mesaj_baslik'];
	}
	else
	{
		$sayfano = '18,'.$fno;
		$sayfa_adi = 'Yeni Konu Oluşturma Önizlemesi: '.$forum_satir['forum_baslik'];
	}
}

else
{
	if ($kip == 'cevapla')
	{
		$sayfano = '19,'.$mesaj_no;
		$sayfa_adi = 'Cevap Yazma: '.$kilit_satir['mesaj_baslik'];
	}
	else
	{
		$sayfano = '20,'.$fno;
		$sayfa_adi = 'Yeni Konu Oluşturma: '.$forum_satir['forum_baslik'];
	}
}

include_once('phpkf-bilesenler/sayfa_baslik_forum.php');



//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/mesaj_yaz.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


// link ağacı
$forum_anasayfa = '<span><a href="'.$phpkf_dosyalar['forum'].'">Forum Ana Sayfası</a></span>';

if ($forum_satir['alt_forum'] != '0')
{
	$vtsorgu = "SELECT id,forum_baslik FROM $tablo_forumlar WHERE id='$forum_satir[alt_forum]' LIMIT 1";
	$vtsonuc_ust = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$forum_satir_ust = $vt->fetch_assoc($vtsonuc_ust);

	$ust_forum_baslik = '<span><a href="forum.php?f='.$forum_satir_ust['id'].'">'.$forum_satir_ust['forum_baslik'].'</a></span>';

	$alt_forum_baslik = '<span><a href="forum.php?f='.$forum_satir['id'].'">'.$forum_satir['forum_baslik'].'</a></span>';
}

else
{
	$ust_forum_baslik = '<span><a href="forum.php?f='.$forum_satir['id'].'">'.$forum_satir['forum_baslik'].'</a></span>';
	$alt_forum_baslik = '';
}




if ($kip == 'cevapla')
{
	$sayfa_baslik = '<span><a href="konu.php?k='.$mesaj_no.'&amp;fs='.$fsayfa.'&amp;ks='.$sayfa.'">'.$kilit_satir['mesaj_baslik'].'</a></span>';
}

else $sayfa_baslik = '<span>Yeni Konu Oluştur</span>';





			//		ÖNİZLEME TABLOSU BAŞI		//


if ( isset($_POST['mesaj_onizleme']) ):

	if ( empty($_POST['mesaj_icerik']) ):
		$ornek1->kosul('1', array('' => ''), false);
		$javascript_kapali = '<center><br><b><font size="3" color="red">Önizleme özelliği için taraycınızın java özelliğinin açık olması gereklidir.</b></center><br>';



	else:

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


$javascript_kapali = '';

$onizleme_uye_adi = '<a href="profil.php?kim='.$kullanici_kim['kullanici_adi'].'">'.$kullanici_kim['kullanici_adi'].'</a>';


if (!empty($kullanici_kim['ozel_ad']))
	$onizleme_yetki = '<font class="ozel_ad"><u>'.$kullanici_kim['ozel_ad'].'</u></font>';

elseif ($kullanici_kim['id'] == 1)
	$onizleme_yetki = '<font class="kurucu"><u>'.$ayarlar['kurucu'].'</u></font>';

elseif ( $kullanici_kim['yetki'] == 1 )
	$onizleme_yetki = '<font class="yonetici"><u>'.$ayarlar['yonetici'].'</u></font>';

elseif ( $kullanici_kim['yetki'] == 2 )
	$onizleme_yetki = '<font class="yardimci"><u>'.$ayarlar['yardimci'].'</u></font>';

elseif ( $kullanici_kim['yetki'] == 3 )
	$onizleme_yetki = '<font class="blm_yrd"><u>'.$ayarlar['blm_yrd'].'</u></font>';

else $onizleme_yetki = '';



if ($kullanici_kim['resim'] != '') $onizleme_resim = '<img src="'.$kullanici_kim['resim'].'" alt="Kullanıcı Resmi">';
elseif ($ayarlar['v-uye_resmi'] != '') $onizleme_resim = '<img src="'.$ayarlar['v-uye_resmi'].'" alt="Varsayılan Kullanıcı Resmi">';
else $onizleme_resim = '';


$onizleme_katilim = zonedate('d.m.Y', $ayarlar['saat_dilimi'], false, $kullanici_kim['katilim_tarihi']);


if ($kullanici_kim['sehir_goster'] == 1)
{
	if ($kullanici_kim['sehir'] != '') $onizleme_sehir = $kullanici_kim['sehir'];
	else $onizleme_sehir = 'Yok';
}

else $onizleme_sehir = 'Gizli';



if (empty($kullanici_kim['gercek_ad']))
	$onizleme_durum = '<font color="#FF0000">üye silinmiş</font>';

elseif ($kullanici_kim['engelle'] == 1)
	$onizleme_durum = '<font color="#FF0000">üye uzaklaştırılmış</font>';

elseif ($kullanici_kim['gizli'] == 1)
	$onizleme_durum = '<font color="#FF0000">Gizli</font>';

elseif ( (($kullanici_kim['son_hareket'] + $ayarlar['uye_cevrimici_sure']) > time() ) AND
		($kullanici_kim['sayfano'] != '-1') )
	$onizleme_durum = '<font color="#339900">Forumda</font>';

else $onizleme_durum = '<font color="#FF0000">Forumda Değil</font>';



$onizleme_eposta = '<a title="Forum üzerinden e-posta gönder" href="eposta.php?kim='.$kullanici_kim['kullanici_adi'].'">';


if ($kullanici_kim['web'])
	$onizleme_web = '<br><a href="'.$kullanici_kim['web'].'" target="_blank">Web Adresi</a>';

else $onizleme_web  = '';


$onizleme_io = '<a href="oi_yaz.php?ozel_kime='.$kullanici_kim['kullanici_adi'].'">';

$onizleme_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, time());





	//	BAŞLIK İÇERİĞİ YAZDIRILIYOR	//
	//	VARSA İMZA BİLGİLERİ YAZDIRILIYOR	//


$onizleme_mesaj = $_POST['mesaj_icerik'];

if (isset($_POST['ifade'])) $onizleme_mesaj = ifadeler($onizleme_mesaj);

if ((isset($_POST['bbcode_kullan'])) AND ($ayarlar['bbcode'] == 1)) $onizleme_mesaj = bbcode_acik($onizleme_mesaj,1);
else $onizleme_mesaj = bbcode_kapali($onizleme_mesaj);


if ( (isset($kullanici_kim['imza'])) AND ($kullanici_kim['imza'] != '') )
{
	if ($ayarlar['bbcode'] == 1)
		$onizleme_imza = bbcode_acik(ifadeler($kullanici_kim['imza']),0);

	else $onizleme_imza = bbcode_kapali(ifadeler($kullanici_kim['imza']));
}

else $onizleme_imza = '';


//	veriler tema motoruna yollanıyor	//

$ornek1->kosul('1', array('{ONIZLEME_BASLIK}' => $_POST['mesaj_baslik'],
'{ONIZLEME_UYE_ADI}' => $onizleme_uye_adi,
'{ONIZLEME_GERCEK_AD}' => $kullanici_kim['gercek_ad'],
'{ONIZLEME_YETKISI}' => $onizleme_yetki,
'{ONIZLEME_RESIM}' => $onizleme_resim,
'{ONIZLEME_KATILIM}' => $onizleme_katilim,
'{ONIZLEME_MESAJ_SAYI}' => NumaraBicim($kullanici_kim['mesaj_sayisi']),
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






if ($kip == 'yeni')
	$sayfa_kip = 'Yeni Konu Oluştur';

elseif ($kip == 'cevapla')
	$sayfa_kip = 'Cevap Yaz';

if ( isset($_POST['mesaj_baslik']) ){
	if ($_POST['mesaj_baslik'] == '') $form_baslik = 'Cvp:';
	else $form_baslik = $_POST['mesaj_baslik'];
}

else{
	if ($kip == 'cevapla') $form_baslik = 'Cvp:';
	else $form_baslik = '';
}


//	MESAJ ALINTI TIKLANMIŞSA VERİTABANINDAN ÇEKİLİYOR	//

if ( (isset($_GET['alinti'])) AND ($_GET['alinti'] == 'mesaj') ):

$vtsorgu = "SELECT mesaj_icerik, yazan FROM $tablo_mesajlar
			WHERE id='$mesaj_no' AND silinmis='0' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$mesaj_alinti = $vt->fetch_array($vtsonuc);


$form_icerik = '[quote="'.$mesaj_alinti['yazan'].'"]';
$form_icerik .= "\n".$mesaj_alinti['mesaj_icerik'];
$form_icerik .= "\n[/quote]\n";


//	CEVAP ALINTI TIKLANMIŞSA VERİTABANINDAN ÇEKİLİYOR	//

elseif ( (isset($_GET['alinti'])) AND ($_GET['alinti'] == 'cevap') ):

$vtsorgu = "SELECT cevap_icerik, cevap_yazan FROM $tablo_cevaplar
			WHERE id='$cevap_no' AND silinmis='0' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$cevap_alinti = $vt->fetch_array($vtsonuc);


$form_icerik = '[quote="'.$cevap_alinti['cevap_yazan'].'"]';
$form_icerik .= "\n".$cevap_alinti['cevap_icerik'];
$form_icerik .= "\n[/quote]\n";


//	NORMAL YENİ MESAJ VEYA CEVAP ALANI	//

else:

if ( isset($_POST['mesaj_icerik']) ) 
	$form_icerik = $_POST['mesaj_icerik'];

endif;




//  BBCODE AÇMA - KAPATMA    //

$form_ozellik = '';

if ($ayarlar['bbcode'] == 1)
{
	$form_ozellik .= '<label style="cursor:pointer"><input type="checkbox" name="bbcode_kullan" ';
	if ((isset($_POST['mesaj_onizleme'])) AND (!isset($_POST['bbcode_kullan']))) $form_ozellik .= '';
	else $form_ozellik .= ' checked="checked"';
	$form_ozellik .= '>Bu iletide BBCode kullan</label>';
}

// bbcode kapalı ise
else $form_ozellik .= '<input type="hidden" name="bbcode_kullan">&nbsp;BBCode Kapalı';


//  İFADE AÇMA - KAPATMA    //

$form_ozellik .= '<br><label style="cursor:pointer"><input type="checkbox" name="ifade" ';
if ((isset($_POST['mesaj_onizleme'])) AND (!isset($_POST['ifade']))) $form_ozellik .= '';
else $form_ozellik .= ' checked="checked"';
$form_ozellik .= '>Bu iletide ifade kullan</label>';




//	ÜST KONU SEÇENEĞİ KULLANICIYA GÖRE GÖSTERİLİYOR	- BAŞI //


//	YÖNETİCİ İSE	//

if ( ($kip == 'yeni') AND (($kullanici_kim['yetki'] == 1) OR ($kullanici_kim['yetki'] == 2)) )
{
	$form_ozellik .= '<br><label style="cursor:pointer">
	<input type="checkbox" name="ust_konu" ';

	if ( (isset($_POST['ust_konu'])) AND ($_POST['ust_konu'] == 1) )
	$form_ozellik .= ' checked="checked"';

	$form_ozellik .= '>Mesajı üst konu yap</label>';
}


//	YARDIMCI İSE	//

elseif ( ($kip == 'yeni') AND ($kullanici_kim['yetki'] == 3) )
{
	if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$fno' AND yonetme='1' OR";
	else $grupek = "grup='0' AND";

	$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$fno' AND yonetme='1'";
	$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());


	//	YÖNETME YETKİSİ VARSA	//

	if ($vt->num_rows($kul_izin))
	{
		$form_ozellik .= '<br><label style="cursor:pointer">
		<input type="checkbox" name="ust_konu" ';

		if ( (isset($_POST['ust_konu'])) AND ($_POST['ust_konu'] == 1) )
		$form_ozellik .= ' checked="checked"';

		$form_ozellik .= '>Mesajı üst konu yap</label>';
	}
}


//	ÜST KONU SEÇENEĞİ KULLANICIYA GÖRE GÖSTERİLİYOR	- SONU //



if (isset($_GET['alinti']))
{
	$mesaj_alinti = $_GET['alinti'];
	$mesaj_alinti = @zkTemizle($mesaj_alinti);
	$mesaj_alinti = @zkTemizle4($mesaj_alinti);
}

else $mesaj_alinti = '';










			//		KONUNUN SON CEVAPLARI SIRALANIYOR - BAŞI		//



if ($kip == 'cevapla'):

// MESAJ BİLGİLERİ ÇEKİLİYOR //

$vtsorgu = "SELECT id,yazan,mesaj_baslik,mesaj_icerik,tarih,bbcode_kullan,cevap_sayi,ifade
FROM $tablo_mesajlar WHERE id='$mesaj_no' AND silinmis='0' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$mesaj_satir = $vt->fetch_assoc($vtsonuc);


//	KONUNUN CEVAPLARI VARSA WHILE DÖNGÜSÜNE GİRİLİYOR	//

if ($mesaj_satir['cevap_sayi'] > 0):


// CEVAP BİLGİLERİ ÇEKİLİYOR //

$vtsorgu = "SELECT id,cevap_yazan,cevap_baslik,cevap_icerik,tarih,bbcode_kullan,ifade
FROM $tablo_cevaplar WHERE silinmis='0' AND hangi_basliktan='$mesaj_no' ORDER BY tarih DESC LIMIT 10";
$cevap = $vt->query($vtsorgu) or die ($vt->hata_ver());



// CEVAPLAR TERSTEN YAZDIRILIYOR //

while ($cevap_satir = $vt->fetch_assoc($cevap)):


$cevap_baslik = '<a href="konu.php?k='.$mesaj_no.'&amp;fs='.$fsayfa.'&amp;ks='.$sayfa.'#c'.$cevap_satir['id'].'">'.$cevap_satir['cevap_baslik'].'</a>';

$cevap_yazan = '<a href="profil.php?kim='.$cevap_satir['cevap_yazan'].'">'.$cevap_satir['cevap_yazan'].'</a>';

$cevap_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $cevap_satir['tarih']);


if ($cevap_satir['ifade'] == 1)
    $cevap_satir['cevap_icerik'] = ifadeler($cevap_satir['cevap_icerik']);

if ($cevap_satir['bbcode_kullan'] == 1)
	$cevap_icerik = bbcode_acik($cevap_satir['cevap_icerik'],$cevap_satir['id']);

else $cevap_icerik = bbcode_kapali($cevap_satir['cevap_icerik']);


//	veriler tema motoruna yollanıyor	//

$tekli1[] = array('{YAZI_BASLIK}' => 'Başlık',
'{YAZI_YAZAN}' => 'Yazan',
'{YAZI_TARIH}' => 'Tarih',
'{CEVAP_BASLIK}' => $cevap_baslik,
'{CEVAP_YAZAN}' => $cevap_yazan,
'{CEVAP TARIH}' => $cevap_tarih,
'{CEVAP_ICERIK}' => $cevap_icerik);


endwhile;

endif;




//  10'DAN AZ CEVAP VARSA KONUNUN İÇERİĞİ YAZDIRILIYOR //

if ($mesaj_satir['cevap_sayi'] < 10):


$cevap_baslik = '<a href="konu.php?k='.$mesaj_no.'&amp;fs='.$fsayfa.'&amp;ks='.$sayfa.'">'.$mesaj_satir['mesaj_baslik'].'</a>';

$cevap_yazan = '<a href="profil.php?kim='.$mesaj_satir['yazan'].'">'.$mesaj_satir['yazan'].'</a>';

$cevap_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $mesaj_satir['tarih']);


if ($mesaj_satir['ifade'] == 1)
    $mesaj_satir['mesaj_icerik'] = ifadeler($mesaj_satir['mesaj_icerik']);

if ($mesaj_satir['bbcode_kullan'] == 1)
	$cevap_icerik = bbcode_acik($mesaj_satir['mesaj_icerik'],$mesaj_satir['id']);

else $cevap_icerik = bbcode_kapali($mesaj_satir['mesaj_icerik']);



//	veriler tema motoruna yollanıyor	//

$tekli1[] = array('{YAZI_BASLIK}' => 'Konu Başlığı',
'{YAZI_YAZAN}' => 'Konuyu Açan',
'{YAZI_TARIH}' => 'Konu Tarihi',
'{CEVAP_BASLIK}' => $cevap_baslik,
'{CEVAP_YAZAN}' => $cevap_yazan,
'{CEVAP TARIH}' => $cevap_tarih,
'{CEVAP_ICERIK}' => $cevap_icerik);


endif;
endif;


			//		BAŞLIĞIN SON CEVAPLARI SIRALANIYOR - SONU		//




$form_bilgi1 = '<form action="phpkf-bilesenler/mesaj_yaz_yap.php" method="post" onsubmit="return denetle_yazi()" name="duzenleyici_form" id="duzenleyici_form">
<input type="hidden" name="kayit_yapildi_mi" value="form_dolu">
<input type="hidden" name="sayfa_onizleme" value="mesaj_yaz">
<input type="hidden" name="mesaj_onizleme" value="Önizleme">
<input type="hidden" name="fno" value="'.$fno.'">
<input type="hidden" name="kip" value="'.$kip.'">
<input type="hidden" name="mesaj_no" value="'.$mesaj_no.'">
<input type="hidden" name="fsayfa" value="'.$fsayfa.'">
<input type="hidden" name="sayfa" value="'.$sayfa.'">';



if (!isset($mesaj_satir['mesaj_baslik'])) $mesaj_satir['mesaj_baslik'] = '';
if (!isset($form_icerik)) $form_icerik = '';
if (!isset($javascript_kapali)) $javascript_kapali = '';



//	TEMA UYGULANIYOR	//

$ornek1->kosul('3', array('' => ''), false);
$ornek1->kosul('5', array('' => ''), false);

if (isset($tekli1))
{
	$ornek1->tekli_dongu('1',$tekli1);
	$ornek1->kosul('2', array('{MESAJ_BASLIK}' => $mesaj_satir['mesaj_baslik']), true);
}

else $ornek1->kosul('2', array('' => ''), false);


$dongusuz = array('{FORUM_ANASAYFA}' => $forum_anasayfa,
'{FORUM_BASLIK}' => $ust_forum_baslik,
'{ALT_FORUM_BASLIK}' => $alt_forum_baslik,
'{SAYFA_BASLIK}' => $sayfa_baslik,
'{SAYFA_KIP}' => $sayfa_kip,
'{FORM_BASLIK}' => $form_baslik,
'{FORM_ICERIK}' => $form_icerik,
'{FORM_OZELLIK}' => $form_ozellik,
'{MESAJ_BASLIK}' => $mesaj_satir['mesaj_baslik'],
'{JAVASCRIPT_KAPALI}' => $javascript_kapali,
'{FORM_BILGI1}' => $form_bilgi1,
'{CEVAP_BASLIK}' => '');


$ornek1->dongusuz($dongusuz);

eval(TEMA_UYGULA);
$gec='';


else:
header('Location: hata.php?hata=14');
exit();


endif;
?>