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


$phpkf_ayarlar_kip = "WHERE kip='1' OR kip='3'";
if (!defined('DOSYA_AYAR')) include 'ayar.php';
if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';


// ziyaretçi ip adresi alınıyor
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
else $ip = $_SERVER['REMOTE_ADDR'];
$ip = zkTemizle4($ip);
$ip = zkTemizle($ip);



//  GİRİŞ YAPILIYOR - BAŞI  //
//  GİRİŞ YAPILIYOR - BAŞI  //
//  GİRİŞ YAPILIYOR - BAŞI  //

if ((isset($_POST['kayit_yapildi_mi'])) AND ($_POST['kayit_yapildi_mi'] == 'form_dolu')):



// Geçersiz bir çerez varsa çıkış dosyasına yönlendir
if (isset($_COOKIE['kullanici_kimlik']))
{
	if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';

	if (empty($kullanici_kim['id']))
	{
		setcookie('kullanici_kimlik', '', 0, $cerez_dizin, $cerez_alanadi);
		setcookie('yonetim_kimlik', '', 0, $cerez_dizin, $cerez_alanadi);
		setcookie('kfk_okundu', '', 0, $cerez_dizin, $cerez_alanadi);

		header('Location: '.$phpkf_dosyalar['giris']);
		exit();
	}


	// giriş yapılmışsa ana sayfaya yönlendir
	else
	{
		header('Location: '.$phpkf_dosyalar['forum']);
		exit();
	}
}



// Form boş ise hata ver //

if ((empty($_POST['kullanici_adi'])) OR (empty($_POST['sifre'])))
{
	header('Location: hata.php?hata=18');
	exit();
}

if ((@strlen($_POST['kullanici_adi']) > 20) OR (@strlen($_POST['kullanici_adi']) < 4))
{
	header('Location: hata.php?hata=19');
	exit();
}

if ((@strlen($_POST['sifre']) > 20) OR (@strlen($_POST['sifre']) < 5))
{
	header('Location: hata.php?hata=20');
	exit();
}



// zararlı kodlar temizleniyor

if (isset($_COOKIE['misafir_kimlik'])) $_COOKIE['misafir_kimlik'] = zkTemizle($_COOKIE['misafir_kimlik']);
else $_COOKIE['misafir_kimlik'] = '';
$_POST['kullanici_adi'] = @zkTemizle($_POST['kullanici_adi']);
$_POST['sifre'] = @zkTemizle($_POST['sifre']);

$tarih = time();
$sayfa_adi = $l['giris_yapti'];



// Şifre anahtar ile karıştırılarak veritabanındaki ile karşılaştırılıyor

$karma = sha1(($anahtar.$_POST['sifre']));

$vtsorgu = "SELECT id,sifre,kul_etkin,kul_etkin_kod,engelle,giris_denemesi,kilit_tarihi,son_giris,kullanici_kimlik
		FROM $tablo_kullanicilar WHERE kullanici_adi='$_POST[kullanici_adi]' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());

$kullanici_denetim = $vt->fetch_assoc($vtsonuc);



// Hesap kilit tarihi kontrol ediliyor

if ( (isset($kullanici_denetim['kilit_tarihi'])) AND
(($kullanici_denetim['kilit_tarihi'] + $ayarlar['uye_kilit_sure']) > $tarih) AND
($kullanici_denetim['giris_denemesi'] > 4) )
{
	header('Location: hata.php?hata=21');
	exit();
}




//  KULLANICI ADI VE ŞİFRE UYUŞMUYORSA  //

elseif ((!$vt->num_rows($vtsonuc)) OR ($kullanici_denetim['sifre'] != $karma))
{
	// Başarısız girişler 5 olduğunda hesap kilitleniyor

	$vtsorgu = "UPDATE $tablo_kullanicilar SET kilit_tarihi='$tarih', giris_denemesi=giris_denemesi + 1
				WHERE kullanici_adi='$_POST[kullanici_adi]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


	if ($kullanici_denetim['giris_denemesi'] > 3)
	{
		header('Location: hata.php?hata=21');
		exit();
	}

	else
	{
		if (isset($kullanici_denetim['id'])) header('Location: hata.php?hata=22');
		else
		{
			if (preg_match('/@/i', $_POST['kullanici_adi'])) header('Location: hata.php?hata=208');
			else header('Location: hata.php?hata=207');
		}
		exit();
	}
}


// hesap etkin değilse
elseif ($kullanici_denetim['kul_etkin'] == 0)
{
	if ($kullanici_denetim['kul_etkin_kod'] == '0')
	{
		header('Location: hata.php?hata=221');
		exit();
	}

	else
	{
		header('Location: hata.php?hata=23');
		exit();
	}
}


// Hesap engellenmişse
elseif ($kullanici_denetim['engelle'] == 1)
{
	header('Location: hata.php?hata=24');
	exit();
}




// SORUN YOK GİRİŞ YAPILIYOR
// Zaman degeri sha1() ile kodlanarak çereze giriliyor
// Beni hatırla işaretli ise çerez geçerlilik süresi ekleniyor

elseif ($kullanici_denetim['sifre'] == $karma)
{
	$kullanici_kimlik = sha1(microtime());

	// Android uygulaması için
	if ((@preg_match('/phpKF\ Android\ Uygulamasi/', $_SERVER['HTTP_USER_AGENT'])) AND ($kullanici_denetim['kullanici_kimlik'] != ''))
	{
		$kullanici_kimlik = $kullanici_denetim['kullanici_kimlik'];
	}


	if (isset($_POST['hatirla'])) $cerez_tarih = $tarih +$ayarlar['k_cerez_zaman'];
	else $cerez_tarih = 0;

	// çerez yazılıyor
	setcookie('kullanici_kimlik', $kullanici_kimlik, $cerez_tarih, $cerez_dizin, $cerez_alanadi);
	setcookie('kfk_okundu', '', 0, $cerez_dizin, $cerez_alanadi);


	// Kullanıcı giriş yapınca açılan misafir oturumu ve çerezi siliniyor
	if ((isset($_COOKIE['misafir_kimlik'])) OR ($_COOKIE['misafir_kimlik'] != ''))
	{
		$vtsorgu = "DELETE FROM $tablo_oturumlar WHERE sid='$_COOKIE[misafir_kimlik]'";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
		setcookie('misafir_kimlik', '', 0, $cerez_dizin, $cerez_alanadi);
	}


	// kullanıcı kimlik veritabanına giriliyor
	// son_hareket tarihi son_girise yazdırılıyor
	$vtsorgu = "UPDATE $tablo_kullanicilar SET
				kullanici_kimlik='$kullanici_kimlik', giris_denemesi=0, kilit_tarihi=0, yeni_sifre=0,
				son_giris=son_hareket, son_hareket='$tarih',
				hangi_sayfada='$sayfa_adi', kul_ip='$ip'
				WHERE id='$kullanici_denetim[id]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());



	// Kullanıcı giriş sayfasına yönlendirilmişse aynı adrese geri gönderiliyor
	if (isset($_POST['git']))
	{
		if (@is_array($_POST['git'])) $git = $phpkf_dosyalar['forum'];
		else $git = $_POST['git'];

		if ($git == '') $git = $phpkf_dosyalar['forum'];
		elseif (@preg_match('/'.$phpkf_dosyalar['hata'].'/i', $git)) $git = $phpkf_dosyalar['forum'];
		elseif (@preg_match('/'.$phpkf_dosyalar['giris'].'/i', $git)) $git = $phpkf_dosyalar['forum'];
		elseif (@preg_match('/giris.php/i', $git)) $git = $phpkf_dosyalar['forum'];
		elseif ((@preg_match('/^http(s):\/\//i', $git)) AND (!@preg_match('/^http(s):\/\/'.$ayarlar['alanadi'].'/i', $git))) $git = $phpkf_dosyalar['forum'];
	}
	else $git = $phpkf_dosyalar['forum'];

	$git = @str_replace('veisareti', '&', $git);
	$git = zkTemizle($git);

	header('Location: '.$git);
	exit();
}
$gec = '';

//  GİRİŞ YAPILIYOR - SONU  //
//  GİRİŞ YAPILIYOR - SONU  //
//  GİRİŞ YAPILIYOR - SONU  //





elseif ((isset($_COOKIE['kullanici_kimlik'])) AND ($_COOKIE['kullanici_kimlik'] != '')):

if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';


// Geçersiz çerez varsa siliniyor
if (empty($kullanici_kim['id']))
{
	setcookie('kullanici_kimlik', '', 0, $cerez_dizin, $cerez_alanadi);
	setcookie('yonetim_kimlik', '', 0, $cerez_dizin, $cerez_alanadi);
	setcookie('kfk_okundu', '', 0, $cerez_dizin, $cerez_alanadi);

	header('Location: '.$phpkf_dosyalar['giris']);
	exit();
}


// Giriş yapılmışsa ana sayfaya yönlendiriliyor
elseif (isset($kullanici_kim['id']))
{
	header('Location: '.$phpkf_dosyalar['forum']);
	exit();
}
$gec = '';





// GİRİŞ YAPILMAMIŞSA GİRİŞ EKRANINI VER    //

else:
$sayfano = 8;
$sayfa_adi = $l['kullanici_giris'];
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');




if (isset($_GET['git']))
{
	if (is_array($_GET['git'])) $_GET['git'] = '';
	$gelinen_adres = @zkTemizle3($_GET['git']);
	$gelinen_adres = @zkTemizle4($gelinen_adres);
}

elseif (isset($_SERVER['HTTP_REFERER']))
{
	if (is_array($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = '';
	$gelinen_adres = @zkTemizle3($_SERVER['HTTP_REFERER']);
	$gelinen_adres = @zkTemizle4($gelinen_adres);
}

else $gelinen_adres = '';

if ((@preg_match('/giris.php/', $gelinen_adres)) OR (@preg_match('/'.$phpkf_dosyalar['giris'].'/', $gelinen_adres))) $gelinen_adres = $phpkf_dosyalar['forum'];



$javascript_kodu = '';

$ek_girisler = '';


$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/giris.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));

$dongusuz = array('{GELINEN_ADRES}' => $gelinen_adres,
'{EK_GIRISLER}' => $ek_girisler,
'{JAVASCRIPT_KODU}' => $javascript_kodu);

$ornek1->dongusuz($dongusuz);

eval(TEMA_UYGULA);
endif;

?>