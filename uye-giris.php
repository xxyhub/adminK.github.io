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


$phpkf_ayarlar_kip = "WHERE kip='1' OR kip='3'";
if (!defined('DOSYA_AYAR')) include_once('phpkf-ayar.php');
if (!defined('DOSYA_GERECLER')) include_once('phpkf-bilesenler/gerecler.php');
if (!defined('DOSYA_TEMA_SINIF')) include_once('phpkf-bilesenler/sinif_tema.php');
if (!defined('DOSYA_BLOKLAR')) include_once('phpkf-bilesenler/bloklar.php');


// ziyaretçi ip adresi alınıyor
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
else $ip = $_SERVER['REMOTE_ADDR'];
$ip = zkTemizle4($ip);
$ip = zkTemizle($ip);


// Dosya Adı
if (@preg_match('/phpKF\ Android\ Uygulamasi/', $_SERVER['HTTP_USER_AGENT'])) $phpkf_dosyalar['hata'] = 'hata.php';



//  GİRİŞ YAPILIYOR - BAŞI  //
//  GİRİŞ YAPILIYOR - BAŞI  //
//  GİRİŞ YAPILIYOR - BAŞI  //

if ((isset($_POST['kayit_yapildi_mi'])) AND ($_POST['kayit_yapildi_mi'] == 'form_dolu')):



// Geçersiz bir çerez varsa çıkış dosyasına yönlendir
if (isset($_COOKIE['kullanici_kimlik']))
{
	if (!defined('DOSYA_KULLANICI_KIMLIK')) include_once('phpkf-bilesenler/kullanici_kimlik.php');

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
		header('Location: '.$phpkf_dosyalar['cms']);
		exit();
	}
}



// Form boş ise hata ver //

if ((empty($_POST['kullanici_adi'])) OR (empty($_POST['sifre'])))
{
	header('Location: '.$phpkf_dosyalar['hata'].'?hata=18');
	exit();
}

if ((@strlen($_POST['kullanici_adi']) > 20) OR (@strlen($_POST['kullanici_adi']) < 4))
{
	header('Location: '.$phpkf_dosyalar['hata'].'?hata=19');
	exit();
}

if ((@strlen($_POST['sifre']) > 20) OR (@strlen($_POST['sifre']) < 5))
{
	header('Location: '.$phpkf_dosyalar['hata'].'?hata=20');
	exit();
}



// zararlı kodlar temizleniyor

if (isset($_COOKIE['misafir_kimlik'])) $_COOKIE['misafir_kimlik'] = zkTemizle($_COOKIE['misafir_kimlik']);
else $_COOKIE['misafir_kimlik'] = '';
$_POST['kullanici_adi'] = zkTemizle($_POST['kullanici_adi']);
$_POST['sifre'] = zkTemizle($_POST['sifre']);

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
	header('Location: '.$phpkf_dosyalar['hata'].'?hata=21');
	exit();
}




//  KULLANICI ADI VE ŞİFRE UYUŞMUYORSA  //

elseif ((!$vt->num_rows($vtsonuc)) OR ($kullanici_denetim['sifre'] != $karma))
{
	// Başarısız girişler 5 olduğunda hesap kilitleniyor
	$vtsorgu = "UPDATE $tablo_kullanicilar SET kilit_tarihi='$tarih', giris_denemesi=giris_denemesi+1
				WHERE kullanici_adi='$_POST[kullanici_adi]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


	if ($kullanici_denetim['giris_denemesi'] > 3)
	{
		header('Location: '.$phpkf_dosyalar['hata'].'?hata=21');
		exit();
	}

	else
	{
		if (isset($kullanici_denetim['id'])) header('Location: '.$phpkf_dosyalar['hata'].'?hata=22');
		else
		{
			if (@preg_match('/@/i', $_POST['kullanici_adi'])) header('Location: '.$phpkf_dosyalar['hata'].'?hata=208');
			else header('Location: '.$phpkf_dosyalar['hata'].'?hata=207');
		}
		exit();
	}
}


// hesap etkin değilse
elseif ($kullanici_denetim['kul_etkin'] == 0)
{
	if ($kullanici_denetim['kul_etkin_kod'] == '0')
	{
		header('Location: '.$phpkf_dosyalar['hata'].'?hatalar=221');
		exit();
	}

	else
	{
		header('Location: '.$phpkf_dosyalar['hata'].'?hata=23');
		exit();
	}
}


// Hesap engellenmişse
elseif ($kullanici_denetim['engelle'] == 1)
{
	header('Location: '.$phpkf_dosyalar['hata'].'?hata=24');
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
		if (@is_array($_POST['git'])) $git = $phpkf_dosyalar['cms'];
		else $git = $_POST['git'];

		if ($git == '') $git = $phpkf_dosyalar['cms'];
		elseif (@preg_match('/'.$phpkf_dosyalar['hata'].'/i', $git)) $git = $phpkf_dosyalar['cms'];
		elseif (@preg_match('/'.$phpkf_dosyalar['giris'].'/i', $git)) $git = $phpkf_dosyalar['cms'];
		elseif (@preg_match('/giris.php/i', $git)) $git = $phpkf_dosyalar['cms'];
		elseif ((@preg_match('/^http(s):\/\//i', $git)) AND (!@preg_match('/^http(s):\/\/'.$ayarlar['alanadi'].'/i', $git))) $git = $phpkf_dosyalar['cms'];
	}
	else $git = $phpkf_dosyalar['cms'];

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

if (!defined('DOSYA_KULLANICI_KIMLIK')) include_once('phpkf-bilesenler/kullanici_kimlik.php');


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
	header('Location: '.$phpkf_dosyalar['cms']);
	exit();
}
$gec = '';






//  ETKİNLEŞTİRME KODU SAYFASI  //

elseif ((isset($_GET['kip'])) AND ($_GET['kip'] == 'etkinlestir')):

$TEMA_FORM_BUTON = $l['tekrar_yolla'];
$TEMA_SAYFA_TIPI = 'etkinlestir';
$TEMA_SAYFA_BASLIK = $l['etkinlestirme_kodu'];
$TEMA_FORM_BILGI = '<form name="giris" action="phpkf-bilesenler/etkinlestir.php" method="post" onsubmit="return denetle_posta()">
<input type="hidden" name="kayit_yapildi_mi" value="etkinlestir">';
$TEMA_SAYFA_BILGI = ' &nbsp; &nbsp; '.$l['etkinlestirme_bilgi'].'<br><br><br>';


// tema dosyası yükleniyor
$sayfa_numara = 35;
$sayfa_adi = $l['etkinlestirme_kodu'];
eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.'/giris.php'));






//  YENİ ŞİFRE SAYFASI  //

elseif ((isset($_GET['kip'])) AND ($_GET['kip'] == 'yeni_sifre')):


if ( (isset($_GET['kulid'])) AND ($_GET['kulid'] != '') AND (isset($_GET['ys'])) AND ($_GET['ys'] != '') )
{
	$kulid = zkTemizle(BoslukSil($_GET['kulid']));
	$ys = zkTemizle(BoslukSil($_GET['ys']));
	if (!is_numeric($kulid)) $kulid = 0;
	if (!is_numeric($ys)) $ys = 0;


	$TEMA_FORM_BUTON = $l['yeni_sifre_olustur'];
	$TEMA_SAYFA_TIPI = 'sifre_sifirlama';
	$TEMA_SAYFA_BASLIK = $l['yeni_sifre'];
	$form_ek = '<input type="hidden" name="kayit_yapildi_mi" value="sifre_olustur">
<input type="hidden" name="kulid" value="'.$kulid.'">
<input type="hidden" name="ys" value="'.$ys.'">';
	$form_ek2 = 'denetle_yeni_sifre()';
	$TEMA_SAYFA_BILGI = ' &nbsp; &nbsp; '.$l['yeni_sifre_bilgi'].'<br><br>';
}

else
{
	$TEMA_FORM_BUTON = $l['gonder'];
	$TEMA_SAYFA_TIPI = 'sifre_basvuru';
	$TEMA_SAYFA_BASLIK = $l['yeni_sifre'];
	$form_ek = '<input type="hidden" name="kayit_yapildi_mi" value="yeni_sifre">';
	$form_ek2 = 'denetle_posta()';
	$TEMA_SAYFA_BILGI = ' &nbsp; &nbsp; '.$l['yeni_sifre_bilgi2'].'<br><br>';
}


$TEMA_FORM_BILGI = '<form name="giris" action="phpkf-bilesenler/yeni_sifre.php" method="post" onsubmit="return '.$form_ek2.'">'.$form_ek;


// tema dosyası yükleniyor
$sayfa_numara = 33;
$sayfa_adi = $l['yeni_sifre'];
eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.'/giris.php'));








//  GİRİŞ YAPILMAMIŞSA GİRİŞ FORMUNU GÖSTER  //

else:


if (isset($_GET['git']))
{
	if (@is_array($_GET['git'])) $_GET['git'] = '';
	$gelinen_adres = zkTemizle3($_GET['git']);
	$gelinen_adres = zkTemizle4($gelinen_adres);
}

elseif (isset($_SERVER['HTTP_REFERER']))
{
	if (@is_array($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = '';
	$gelinen_adres = zkTemizle3($_SERVER['HTTP_REFERER']);
	$gelinen_adres = zkTemizle4($gelinen_adres);
}

else $gelinen_adres = '';

if ((@preg_match('/giris.php/', $gelinen_adres)) OR (@preg_match('/'.$phpkf_dosyalar['giris'].'/', $gelinen_adres))) $gelinen_adres = $phpkf_dosyalar['cms'];



$TEMA_FORM_BUTON = $l['giris_yap'];
$TEMA_SAYFA_TIPI = '';
$TEMA_SAYFA_BASLIK = $l['kullanici_giris'];
$TEMA_SAYFA_BILGI = '';

$ek_girisler = '';



$TEMA_FORM_BILGI = '<form name="giris" action="'.$phpkf_dosyalar['giris'].'" method="post" onsubmit="return denetle_giris()">
<input type="hidden" name="kayit_yapildi_mi" value="form_dolu">
<input type="hidden" name="git" value="'.$gelinen_adres.'">';



// tema dosyası yükleniyor
$sayfa_numara = 8;
$sayfa_adi = $l['kullanici_giris'];
eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.'/giris.php'));


endif;

?>