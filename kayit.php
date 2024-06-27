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


$phpkf_ayarlar_kip = "WHERE kip='1' OR kip='2'";
if (!defined('PHPKF_ICINDEN')) define('PHPKF_ICINDEN', true);
if (!defined('DOSYA_AYAR')) include 'ayar.php';
$sayfano = 9;


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

if ((@strlen($_GET['kadi']) > 20) OR (@strlen($_GET['kadi']) < 4))
{
	echo $l['4_20_karakter'];
	exit();
}



//  YASAK KULLANICI ADLARI ALINIYOR //

$vtsorgu = "SELECT deger FROM $tablo_yasaklar WHERE etiket='kulad' LIMIT 1";
$yasak_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$yasak_kulad = $vt->fetch_row($yasak_sonuc);
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
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());

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
	header('Location: hata.php?uyari=9');
	exit();
}


//	GEÇERSİZ BİR ÇEREZ VARSA ÇIKIS SAYFASINA YÖNLENDİRİLİYOR	//

if (isset($_COOKIE['kullanici_kimlik'])):
if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';

if (empty($kullanici_kim['id'])):
setcookie('kullanici_kimlik', '', 0, $cerez_dizin, $cerez_alanadi);
header('Location: '.$phpkf_dosyalar['forum']);
exit();


//	GİRİŞ YAPILMIŞSA PROFİLE YÖNLENDİR	//

elseif (isset($kullanici_kim['id'])):
header('Location: profil.php');
exit();
endif;


else:
//	oturum açlıyor	// - burası -
@session_start();




//  KAYIT KOŞULLARI - BAŞI  //

if ( (isset($_GET['kosul'])) AND ($_GET['kosul'] == 'kabul') ):
$sayfa_adi = 'Forum Üyelik Koşulları';
if (!defined('DOSYA_TEMA_SINIF')) include 'phpkf-bilesenler/sinif_tema_forum.php';

//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/kayit.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));
$ornek1->kosul('1', array('' => ''), true);
$ornek1->kosul('2', array('' => ''), false);
$ornek1->tema_uygula();
exit();

//  KAYIT KOŞULLARI - SONU  //





else:
$sayfa_adi = 'Kullanıcı Kayıt';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');




if (isset($_SESSION['kullanici_adi']))
	$kullanici_adi = zkTemizle4($_SESSION['kullanici_adi']);

else $kullanici_adi = '';


if (isset($_SESSION['posta']))
	$eposta = zkTemizle4($_SESSION['posta']);

else $eposta = '';


$onay_id = session_id().'&amp;sayi='.sha1(microtime());




//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/kayit.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


// kayıt sorusu özelliği açıksa

if ($ayarlar['kayit_soru'] == 1)
{
	if (isset($_SESSION['kayit_cevabi']))
		$kayit_cevabi = zkTemizle4($_SESSION['kayit_cevabi']);

	else $kayit_cevabi = '';

	$ornek1->kosul('3', array('{KAYIT_SORUSU}' => $ayarlar['kayit_sorusu'],
	'{KAYIT_CEVABI}' => $kayit_cevabi), true);

	$form_alan_sayi = 7;
}

else 
{
	$ornek1->kosul('3', array('' => ''), false);
	$form_alan_sayi = 12;
}


// onay kodu açık ise

if ($ayarlar['kayit_onay_kodu'] == '1')
{
	$ornek1->kosul('4', array('{ONAY_ID}' => $onay_id), true);
	$form_alan_sayi++;
}

else $ornek1->kosul('4', array('' => ''), false);



//  session dizisi siliniyor  - burası -
$_SESSION = 0;
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



$ornek1->kosul('1', array('' => ''), false);
$ornek1->kosul('2', array('{JAVASCRIPT_KODU}' => $javascript_kodu), true);


$ek_girisler = '';


$ornek1->dongusuz(array('{SESSION_ID}' => $session_id,
'{KULLANICI_ADI}' => $kullanici_adi,
'{EPOSTA}' => $eposta,
'{EK_GIRISLER}' => $ek_girisler,
'{PHP_SESSID}' => $php_session));


eval(TEMA_UYGULA);
endif;
endif;
endif;

?>