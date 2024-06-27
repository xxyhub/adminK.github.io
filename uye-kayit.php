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


$phpkf_ayarlar_kip = "WHERE kip='1' OR kip='2'";
if (!defined('DOSYA_AYAR')) include 'phpkf-ayar.php';
if (!defined('DOSYA_GERECLER')) include_once('phpkf-bilesenler/gerecler.php');
if (!defined('DOSYA_TEMA_SINIF')) include_once('phpkf-bilesenler/sinif_tema.php');
if (!defined('DOSYA_BLOKLAR')) include_once('phpkf-bilesenler/bloklar.php');


//  KULLANICI ADI KONTROLÜ - BAŞI  //

if ((isset($_GET['kosul'])) AND ($_GET['kosul'] == 'kadi')):

header("Content-type: text/html; charset=utf-8");
@session_start();


if ((!isset($_GET['kadi'])) OR ($_GET['kadi'] == ''))
{
	echo $l['ad_girilmedi'];
	exit();
}

if (!@preg_match('/^[A-Za-z0-9-_ğĞüÜŞşİıÖöÇç.]+$/', $_GET['kadi']))
{
	echo $l['gecersiz_karakter'];
	exit();
}

if ((@strlen($_GET['kadi']) > 20) or (@strlen($_GET['kadi']) < 4))
{
	echo $l['4_20_karakter'];
	exit();
}



//  YASAK KULLANICI ADLARI ALINIYOR //

$vtsorgu = "SELECT deger FROM $tablo_yasaklar WHERE etiket='kulad' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die($vt->hata_ver());
$yasak_kulad = $vt->fetch_row($vtsonuc);
$ysk_kuladd = explode("\r\n", $yasak_kulad[0]);


//  KULLANICI ADI YASAKLARLARI    //

if ($ysk_kuladd[0] != '')
{
	$dongu_sayi = count($ysk_kuladd);
	for ($d=0; $d < $dongu_sayi; $d++)
	{
		if ( (!preg_match('/^\*/', $ysk_kuladd[$d])) AND (!preg_match('/\*$/', $ysk_kuladd[$d])) )
			$ysk_kuladd[$d] = '^'.$ysk_kuladd[$d].'$';

		elseif (!preg_match('/^\*/', $ysk_kuladd[$d])) $ysk_kuladd[$d] = '^'.$ysk_kuladd[$d];

		elseif (!preg_match('/\*$/', $ysk_kuladd[$d])) $ysk_kuladd[$d] .= '$';

		$ysk_kuladd[$d] = str_replace('*', '', $ysk_kuladd[$d]);


		if (preg_match("/$ysk_kuladd[$d]/i", $_GET['kadi']))
		{
			echo $l['ad_yasak'];
			exit();
		}
	}
}


// KULLANICI ADININ DAHA ÖNCE ALINIP ALINMADIĞI DENETLENİYOR //

$vtsorgu = "SELECT kullanici_adi FROM $tablo_kullanicilar WHERE kullanici_adi='$_GET[kadi]'";
$vtsonuc = $vt->query($vtsorgu) or die($vt->hata_ver());

if ($vt->num_rows($vtsonuc))
{
	echo $l['ad_kullaniliyor'];
	exit();
}


// Sorun yok ise session`a kaydediliyor
$_SESSION['fbkullanici_adi'] = $_GET['kadi'];


echo '<font color="green"><b>'.$l['uygun'].'</b></font>';

//  KULLANICI ADI KONTROLÜ - SONU  //




//     NORMAL GÖSTERİM     //
//     NORMAL GÖSTERİM     //


else:

// üye alımı kapalıysa
if ($ayarlar['kayit_uyelik'] != 1)
{
	header('Location: '.$phpkf_dosyalar['hata'].'?uyari=9');
	exit();
}


//  GEÇERSİZ BİR ÇEREZ VARSA SİL  //

if (isset($_COOKIE['kullanici_kimlik'])):
if (!defined('DOSYA_KULLANICI_KIMLIK')) include_once('phpkf-bilesenler/kullanici_kimlik.php');

if (empty($kullanici_kim['id'])):
setcookie('kullanici_kimlik', '', 0, $cerez_dizin, $cerez_alanadi);
header('Location: '.$phpkf_dosyalar['cms']);
exit();


//	GİRİŞ YAPILMIŞSA ANA SAYFAYA YÖNLENDİR	//

elseif (isset($kullanici_kim['id'])):
header('Location: '.$phpkf_dosyalar['cms']);
exit();
endif;


else:
//	oturum açlıyor	//
@session_start();




//  KAYIT KOŞULLARI - BAŞI  //

if ( (isset($_GET['kosul'])) AND ($_GET['kosul'] == 'kabul') ):

$sayfa_adi = 'Kullanıcı Kayıt - Kayıt Koşulları';
eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.'/kayit-kosul.php'));

exit();

//  KAYIT KOŞULLARI - SONU  //







else:

$sayfa_adi = 'Kullanıcı Kayıt';


if (isset($_SESSION['kullanici_adi']))
	$kullanici_adi = zkTemizle4($_SESSION['kullanici_adi']);

else $kullanici_adi = '';


if (isset($_SESSION['posta']))
	$eposta = zkTemizle4($_SESSION['posta']);

else $eposta = '';


$onay_id = session_id().'&amp;sayi='.sha1(microtime());







// kayıt sorusu özelliği açıksa

if ($ayarlar['kayit_soru'] == 1)
{
	if (isset($_SESSION['kayit_cevabi']))
		$kayit_cevabi = zkTemizle4($_SESSION['kayit_cevabi']);

	else $kayit_cevabi = '';

	$form_alan_sayi = 7;
}

else
{
	$form_alan_sayi = 12;
}


// onay kodu açık ise

if ($ayarlar['kayit_onay_kodu'] == '1')
{
	$form_alan_sayi++;
}




$session_id = @session_id();
if (isset($_COOKIE['PHPSESSID']))
{
	$php_session = zkTemizle4($_COOKIE['PHPSESSID']);
	$php_session = zkTemizle($php_session);
}
else $php_session = '';


$javascript_kodu = '
<script type="text/javascript"><!-- //
var dosya_kayit="'.$phpkf_dosyalar['kayit'].'";
function SayiArttir(){
var now=new Date();
var sayac=Math.random();
sayac++;
document.images.onaykodu.src="phpkf-bilesenler/onay_kodu.php?a=1&sayi="+sayac+"&oturum='.$session_id.'";
document.getElementById("onay_kodu").value="";
}
// -->
</script>';



// tema dosyası yükleniyor
$sayfa_numara = 9;
eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.'/kayit.php'));


endif;
endif;
endif;

?>