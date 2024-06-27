<?php
/*
 +-=========================================================================-+
 |                              phpKF-CMS v3.00                              |
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


$TEMA_KUTU_ALTI = "";

// ayar.php yok, kurulum yapılmamış, güncelleme yapılmamış, kurulum sayfasına yönlendir
if ( (!@include_once('phpkf-ayar.php')) OR ($ayarlar['surum'] != '3.00') )
{
	header('Location: phpkf-kurulum/index.php');
	exit();
}


if (!defined('DOSYA_GERECLER')) include_once('phpkf-bilesenler/gerecler.php');
if (!defined('DOSYA_SEF')) include_once('phpkf-bilesenler/sef.php');
if (!defined('DOSYA_TEMA_SINIF')) include_once('phpkf-bilesenler/sinif_tema.php');
if (!defined('DOSYA_BLOKLAR')) include_once('phpkf-bilesenler/bloklar.php');



// Etiket ve Arama işlemleri
if (isset($_GET['etiket']))
{
	$sayfa_numara = 112;
	include_once('phpkf-bilesenler/etiket.php');
	exit();
}

elseif (isset($_GET['arama']))
{
	$sayfa_numara = 113;
	include_once('phpkf-bilesenler/arama.php');
	exit();
}




//   YAZI GÖSTERİMİ - BAŞI   //


// yazı numarasına göre

if ( (isset($_GET['y'])) AND ($_GET['y'] != '') )
{
	$yazi_id = zkTemizle($_GET['y']);

	if ( (!is_numeric($yazi_id)) OR ($yazi_id == 0) )
	{
		header('Location: '.$anadizin.$phpkf_dosyalar['hata'].'?hata=501');
		exit();
	}

	$baslikek = '';
	if ($ayarlar['dil_varsayilan'] != $site_dili)
	{
		if (preg_match("/,$site_dili,/", $ayarlar['dil_eklenen_alanlar'])) $baslikek = ',baslik_'.$site_dili;
	}

	$vtsorgu = "SELECT id,tip,kategori,alt_yazi,sayfa_no,yorum_sayi,adres,baslik $baslikek FROM $tablo_yazilar WHERE id='$yazi_id' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$yazi = $vt->fetch_assoc($vtsonuc);

	if (!isset($yazi['id']))
	{
		header('Location: '.$anadizin.$phpkf_dosyalar['hata'].'?hata=501');
		exit();
	}
}


// Yazı adına göre

elseif ( (isset($_GET['ya'])) AND ($_GET['ya'] != '') )
{
	$yazi_adi = zkTemizle($_GET['ya']);

	$baslikek = '';
	if ($ayarlar['dil_varsayilan'] != $site_dili)
	{
		if (preg_match("/,$site_dili,/", $ayarlar['dil_eklenen_alanlar'])) $baslikek = ',baslik_'.$site_dili;
	}

	$vtsorgu = "SELECT id,tip,kategori,alt_yazi,sayfa_no,yorum_sayi,adres,baslik $baslikek FROM $tablo_yazilar WHERE adres='$yazi_adi' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$yazi = $vt->fetch_assoc($vtsonuc);

	if (!isset($yazi['id']))
	{
		header('Location: '.$anadizin.$phpkf_dosyalar['hata'].'?hata=501');
		exit();
	}
	$yazi_id = $yazi['id'];
}




//  YAZI VARSA  //

if (isset($yazi_id)):


// yazının görüntüleme sayısı arttırılıyor
$vtsorgu = "UPDATE $tablo_yazilar SET goruntuleme=goruntuleme+1 WHERE id='$yazi_id' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die($vt->hata_ver());


// yorum sayfalama için, yazının yanıt olmayan yorumlarının toplamı alınıyor
$vtsorgu = "SELECT id FROM $tablo_yorumlar WHERE yazi_id='$yazi_id' AND yanit<2";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$yrmsayi = $vt->num_rows($vtsonuc);


// Dil seçimine göre içerik alınıyor
if ($ayarlar['dil_varsayilan'] != $site_dili)
{
	if (isset($yazi['baslik_'.$site_dili])) $yazi['baslik'] = $yazi['baslik_'.$site_dili];
}

$yazi_tip = $yazi['tip'];
$yazi_id_yorum = $yazi['id'];
$yorum_sayi = $yazi['yorum_sayi'];
$sayfa_adi = $yazi['baslik'];


// yazı sayfa değişkeni temizleniyor
if (isset($_GET['ys']))
{
	$sayfano = zkTemizleNumara($_GET['ys']);
	if ($sayfano == 0) $sayfano = 1;
}
else $sayfano = 1;


// Yazının alt yazıları varsa

if ($yazi['sayfa_no'] != 0)
{
	// yazısın alt yazı toplamı alınıyor
	$toplam_alt = phpkf_toplam_yazi(array('kosul' => "WHERE alt_yazi='$yazi_id'"));
	$toplam_alt++;

	// alt yazıları var
	if ($toplam_alt > 0)
	{
		// sayfama alanı oluşturuluyor
		$TEMA_SAYFALAMA = phpkf_sayfalama($toplam_alt, 1, $sayfano, 'ys=');

		if ($sayfano > 1)
		{
			$alt_id = $yazi_id;
			$yazi_id = '';
		}
		else $alt_id = '';
	}

	// alt yazıları yoksa
	else
	{
		$alt_id = '';
		$sayfano = '';
		$TEMA_SAYFALAMA = '';
	}
}

// Yazının alt yazıları yoksa
else
{
	$alt_id = '';
	$sayfano = '';
	$TEMA_SAYFALAMA = '';
}



// Ziyaretçinin çerezdeki bilgileri alınıyor
if ((isset($_COOKIE['adsoyad'])) AND ($_COOKIE['adsoyad'] != ''))
	$TEMA_ZIYARETCI_BILGI['adsoyad'] = $_COOKIE['adsoyad'];
else $TEMA_ZIYARETCI_BILGI['adsoyad'] = '';

if ((isset($_COOKIE['posta'])) AND ($_COOKIE['posta'] != ''))
	$TEMA_ZIYARETCI_BILGI['posta'] = $_COOKIE['posta'];
else $TEMA_ZIYARETCI_BILGI['posta'] = '';



// yorum sayfa değişkeni temizleniyor
if (isset($_GET['yms']))
{
	$ysayfano = zkTemizleNumara($_GET['yms']);
	if ($ysayfano == 0) $ysayfano = 1;
}
else $ysayfano = 1;

$TEMA_YORUM_SAYFALAMA = '';


// temadaki fonksiyon için yazı koşulları belirleniyor
$yazi_kosul = array(
'yazi_id' => $yazi_id,
'alt_yazi' => $alt_id,
'sayfa_no' => $sayfano,
'alan' => '*',
);


// temadaki fonksiyon için yorum koşulları belirleniyor
$yorum_kosul = array(
'yazi_id' => $yazi_id_yorum,
'sayfa' => ($ysayfano * $ayarlar['syfkota_yorum'])-$ayarlar['syfkota_yorum'],
'kota' => $ayarlar['syfkota_yorum'],
);


// yazının seçilen ilk kategorisi bulunuyor
$yazi_katid = explode(',', $yazi['kategori']);
$yazi_katid = $yazi_katid[1];


//  Yorum işlemleri  //

$TEMA_YORUM['FORM'] = '<form method="post" action="phpkf-bilesenler/yorum_yap.php" name="duzenleyici_form" id="duzenleyici_form" class="phpkf-kayit-form" onsubmit="return denetle_yazi()">
<input type="hidden" name="dolu" value="dolu" />
<input type="hidden" name="yanitla" value="0" />
<input type="hidden" name="kat_id" value="'.$yazi_katid.'" />'."\r\n";

// Ziyaretçiler için yorum onay kodu işlemleri
if ( ($ayarlar['yorum_onay_kodu'] == '1') AND (!$TEMA_UYE_BILGI) )
{
	@session_start();
	$session_id = @session_id();
	if (isset($_COOKIE['PHPSESSID']))
	{
		$php_session = zkTemizle4($_COOKIE['PHPSESSID']);
		$php_session = zkTemizle($php_session);
	}
	else $php_session = '';

	$onay_id = $session_id.'&amp;sayi='.sha1(microtime());
	$TEMA_YORUM['FORM'] .= '<input type="hidden" name="oturum" value="'.$php_session.'" />'."\r\n";
}
else
{
	$php_session = '';
	$session_id = '';
}


// Yorum Sıralama
$siralama_1 = '';
$siralama_2 = '';

if (isset($_COOKIE['yorum_siralama'])) {
	if ($_COOKIE['yorum_siralama'] == '1') $siralama_1 = 'selected="selected"';
	else $siralama_2 = 'selected="selected"';
}
elseif ($ayarlar['yorum_siralama'] == 1) $siralama_1 = 'selected="selected"';
else $siralama_2 = 'selected="selected"';


$form_yorum_siralama = '<select class="input-select" style="height:27px;margin:0" onchange="CerezYaz(this.value, \''.$ayarlar['dizin'].'\')">
<option name="siralama" value="1" '.$siralama_1.'>'.$l['eskiden_yeniye'].'</option>
<option name="siralama" value="0" '.$siralama_2.'>'.$l['yeniden_eskiye'].'</option>
</select>';



// tema dosyası yükleniyor

if ($yazi_tip == '4')
{
	$sayfa_numara = 102;
	$temadosya_yazi = '/galeri.php';
}
elseif ($yazi_tip == '5')
{
	$sayfa_numara = 103;
	$temadosya_yazi = '/video.php';
}
else
{
	$sayfa_numara = 101;
	$temadosya_yazi = '/yazi.php';
}
$sayfa_numara .= ','.$yazi_id.','.$yazi_katid;

eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.$temadosya_yazi));


//   YAZI GÖSTERİMİ - SONU   //










//   KATEGORİ GÖSTERİMİ - BAŞI   //

elseif ( (isset($_GET['kip'])) AND (($_GET['kip'] == 'kat') OR ($_GET['kip'] == 'sayfa') OR ($_GET['kip'] == 'galeri') OR ($_GET['kip'] == 'video')) ):


// Kipe göre başlık ve linkler
if ($_GET['kip'] == 'sayfa')
{
	$sayfa_numara = 104;
	$sayfa_adi = $l['sayfalar'];
	$TEMA_SAYFA_BASLIK = $l['sayfalar'];
	$yazi_tip = 0;
	$kat_tip = 0;
	$temadosyasi = '/sayfalar.php';
}
elseif ($_GET['kip'] == 'kat')
{
	$sayfa_numara = 105;
	$sayfa_adi = $l['kategoriler'];
	$TEMA_SAYFA_BASLIK = $l['kategoriler'];
	$yazi_tip = '';
	$kat_tip = 0;
	$temadosyasi = '/kategoriler.php';
	$TEMA_SAYFALAMA = '';
}
elseif ($_GET['kip'] == 'galeri')
{
	$sayfa_numara = 106;
	$sayfa_adi = $l['galeriler'];
	$TEMA_SAYFA_BASLIK = $l['galeriler'];
	$yazi_tip = 4;
	$kat_tip = 1;
	$temadosyasi = '/galeriler.php';
	$TEMA_SAYFALAMA = '';
}
elseif ($_GET['kip'] == 'video')
{
	$sayfa_numara = 107;
	$sayfa_adi = $l['videolar'];
	$TEMA_SAYFA_BASLIK = $l['videolar'];
	$yazi_tip = 5;
	$kat_tip = 2;
	$temadosyasi = '/videolar.php';
	$TEMA_SAYFALAMA = '';
}



// Kategori bağlantılarından biri tıklanmışsa

if ( (isset($_GET['k'])) AND ($_GET['k'] !='') )
{
	$kat_id = @zkTemizleNumara($_GET['k']);
	if ($kat_id != 0)
	{
		$vtsorgu = "SELECT * FROM $tablo_kategoriler WHERE id='$kat_id' LIMIT 1";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
		$kat = $vt->fetch_assoc($vtsonuc);
		if (!isset($kat['id'])) $kat_id = '0';
	}
	else $kat_id = '0';
}
elseif ( (isset($_GET['ka'])) AND ($_GET['ka'] !='') )
{
	$kat_adi = @zkTemizle($_GET['ka']);
	$vtsorgu = "SELECT * FROM $tablo_kategoriler WHERE adres='$kat_adi' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$kat = $vt->fetch_assoc($vtsonuc);
	if (isset($kat['id'])) $kat_id = $kat['id'];
	else $kat_id = '0';
}
else $kat_id = '';



// sayfa değişkeni temizleniyor
if (isset($_GET['ks']))
{
	$sayfano = zkTemizleNumara($_GET['ks']);
	if ($sayfano == 0) $sayfano = 1;
}
else $sayfano = 1;



// Seçili kategori için sayfalama yapılıyor

if ($kat_id != '')
{
	if (!isset($kat['id']))
	{
		header('Location: '.$anadizin.$phpkf_dosyalar['hata'].'?hata=501');
		exit();
	}

	if ($kat['tip'] == 1)
	{
		$sayfa_numara = 109;
		$yazi_tip = 4;
		$kat_tip = 1;
		$temadosyasi = '/galeriler.php';
		$TEMA_SAYFALAMA = '';
	}
	elseif ($kat['tip'] == 2)
	{
		$sayfa_numara = 110;
		$yazi_tip = 5;
		$kat_tip = 2;
		$temadosyasi = '/videolar.php';
		$TEMA_SAYFALAMA = '';
	}
	else
	{
		$sayfa_numara = 108;
		$temadosyasi = '/kategoriler.php';
	}

	// Dil seçimine göre içerik alınıyor
	if ($ayarlar['dil_varsayilan'] != $site_dili)
	{
		if (isset($kat['baslik_'.$site_dili])) $kat['baslik'] = $kat['baslik_'.$site_dili];
	}

	$kat_id = $kat['id'];
	$sayfa_numara .= ','.$kat_id;
	$sayfa_adi = $kat['baslik'];
	$TEMA_SAYFA_BASLIK = $kat['baslik'];




	// kategorideki yazıların toplamı alınıyor
	$toplam_yazi = phpkf_tema_toplam_yazi(array('kat_id' => $kat_id));

	$toplam = ($toplam_yazi / $ayarlar['syfkota_kat']);
	settype($toplam,'integer');
	if (($toplam_yazi % $ayarlar['syfkota_kat']) != 0) $toplam++;

	if ( ($sayfano != 1) AND ($sayfano > $toplam) )
	{
		header('Location: '.$anadizin.$phpkf_dosyalar['hata'].'?hata=501');
		exit();
	}

	// sayfalama alanı oluşturuluyor
	$TEMA_SAYFALAMA = phpkf_sayfalama($toplam_yazi, $ayarlar['syfkota_kat'], $sayfano, 'ks=');

	// sayfalama koşulu
	$kosul_sayfa = ($sayfano * $ayarlar['syfkota_kat'])-$ayarlar['syfkota_kat'];
}


// temadaki fonksiyon için kategoriler koşulları belirleniyor
$kat_kosul = array(
'kat_id' => $kat_id,
'tip' => $kat_tip,
);


// tema dosyası yükleniyor
eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.$temadosyasi));


//   KATEGORİ GÖSTERİMİ - SONU   //








//   ARAMA VE ETİKET SAYFALARI - BAŞI   //

elseif ( (isset($_GET['kip'])) AND ($_GET['kip'] == 'etiket') ):
	$sayfa_numara = 114;
	include_once('phpkf-bilesenler/etiket.php');
	exit();


elseif ( (isset($_GET['kip'])) AND ($_GET['kip'] == 'arama') ):
	$sayfa_numara = 115;
	include_once('phpkf-bilesenler/arama.php');
	exit();

//   ARAMA VE ETİKET SAYFALARI - SONU   //








//   İLETİŞİM SAYFASI - BAŞI   //

elseif ( (isset($_GET['kip'])) AND ($_GET['kip'] == 'iletisim') ):


@session_start();
if (isset($_SESSION['ad_soyad'])) $ad_soyad = zkTemizle4($_SESSION['ad_soyad']);
else $ad_soyad = '';

if (isset($_SESSION['posta'])) $eposta = zkTemizle4($_SESSION['posta']);
else $eposta = '';

if (isset($_SESSION['baslik'])) $baslik = zkTemizle4($_SESSION['baslik']);
else $baslik = '';

if (isset($_SESSION['baslik2'])) $baslik2 = zkTemizle4($_SESSION['baslik2']);
else $baslik2 = '';


$session_id = @session_id();
$onay_id = $session_id.'&amp;sayi='.sha1(microtime());


if (isset($_COOKIE['PHPSESSID']))
{
	$php_session = zkTemizle4($_COOKIE['PHPSESSID']);
	$php_session = zkTemizle($php_session);
}
else $php_session = '';


// temadaki fonksiyon için iletişim sayfası koşulları belirleniyor
$iletisim_kosul = array('alan' => '*',
'tum_icerik' => 1,
'tip' => 3,
);
$iletisim_bilgi = 'Buraya iletişim bilgileri eklemek için <a href="phpkf-yonetim/yazi_ekle.php?kip=iletisim">Tıklayın.</a>';


// tema dosyası yükleniyor
$sayfa_numara = 111;
$sayfa_adi = $l['iletisim'];
eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.'/iletisim.php'));


//   İLETİŞİM SAYFASI - SONU   //








//   ANA SAYFA GÖSTERİMİ - BAŞI   //

else:

$sayfa_numara = 100;
$sayfa_adi = $l['anasayfa'];


// Özel ana sayfa varsa yükleniyor

if ($ayarlar['durum_anasayfa'] == '1')
{
	if (!@is_file($ayarlar['anasyfdosya']))
	{
		header('Location: '.$phpkf_dosyalar['hata'].'?hata=500');
		exit();
	}

	// tema dosyası yükleniyor
	eval(phpkf_tema_yukle($ayarlar['anasyfdosya']));
}



// Ana Sayfa yazısı varsa veritabanından çekiliyor

elseif ($ayarlar['durum_anasayfa'] == '2')
{
	$vtsorgu = "SELECT id,alt_yazi,sayfa_no,adres,baslik FROM $tablo_yazilar WHERE id='$ayarlar[anasyfyazi]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$yazi = $vt->fetch_assoc($vtsonuc);

	if (!isset($yazi['id']))
	{
		header('Location: '.$phpkf_dosyalar['hata'].'?hata=506');
		exit();
	}
	else $yazi_id = $yazi['id'];


	// Yazının alt yazıları varsa
	if ($yazi['sayfa_no'] != 0)
	{
		// sayfa değişkeni temizleniyor
		if (isset($_GET['s']))
		{
			$sayfano = zkTemizleNumara($_GET['s']);
			if ($sayfano == 0) $sayfano = 1;
		}
		else $sayfano = 1;


		// yazısın alt yazı toplamı alınıyor
		$toplam_alt = phpkf_toplam_yazi(array('kosul' => "WHERE alt_yazi='$yazi_id'"));
		$toplam_alt++;

		// alt yazıları var
		if ($toplam_alt > 0)
		{
			// sayfama alanı oluşturuluyor
			$TEMA_SAYFALAMA = phpkf_sayfalama($toplam_alt, 1, $sayfano, 's=');

			if ($sayfano > 1)
			{
				$alt_id = $yazi_id;
				$yazi_id = '';
			}
			else $alt_id = '';
		}

		// alt yazıları yoksa
		else
		{
			$alt_id = '';
			$sayfano = '';
			$TEMA_SAYFALAMA = '';
		}
	}

	// Yazının alt yazıları yoksa
	else
	{
		$alt_id = '';
		$sayfano = '';
		$TEMA_SAYFALAMA = '';
	}


	// temadaki fonksiyon için ana sayfa koşulları belirleniyor
	$yazi_kosul = array(
	'yazi_id' => $yazi_id,
	'alt_yazi' => $alt_id,
	'sayfa_no' => $sayfano,
	'alan' => '*',
	);


	// tema dosyası yükleniyor
	eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.'/index.php'));
}





//  Güncel konular gösterimi

else
{
	// sayfa değişkeni temizleniyor
	if (isset($_GET['s']))
	{
		$sayfano = zkTemizleNumara($_GET['s']);
		if ($sayfano == 0) $sayfano = 1;
	}
	else $sayfano = 1;


	// Güncel yazı kategorisi belirleniyor
	if ($ayarlar['guncel_kat'] == 0) $kosul_kat_id = '';
	else $kosul_kat_id = $ayarlar['guncel_kat'];

	// Güncel yazı tipi belirleniyor
	if ($ayarlar['guncel_yazi'] == 0) $kosul_tip = 0;
	elseif ($ayarlar['guncel_yazi'] == 2) $kosul_tip = 2;
	elseif ($ayarlar['guncel_yazi'] == 4) $kosul_tip = 4;
	elseif ($ayarlar['guncel_yazi'] == 5) $kosul_tip = 5;
	else $kosul_tip = '';


	// güncel yazıların toplamı alınıyor
	$toplam_yazi = phpkf_tema_toplam_yazi(array('kat_id' => $kosul_kat_id, 'tip' => $kosul_tip));

	// sayfama alanı oluşturuluyor
	$TEMA_SAYFALAMA = phpkf_sayfalama($toplam_yazi, $ayarlar['syfkota_guncel'], $sayfano, 's=');

	// sayfalama koşulu
	$kosul_sayfa = ($sayfano * $ayarlar['syfkota_guncel'])-$ayarlar['syfkota_guncel'];


	// temadaki fonksiyon için güncel yazılar koşulları belirleniyor
	$yazi_kosul = array(
	'kat_id' => $kosul_kat_id,
	'alan' => '*',
	'tum_icerik' => 0,
	'tip' => $kosul_tip,
	'sayfa' => $kosul_sayfa,
	'kota' => $ayarlar['syfkota_guncel'],
	);


	// tema dosyası yükleniyor
	eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.'/guncel.php'));
}

endif;

//   ANA SAYFA GÖSTERİMİ - SONU   //


?>