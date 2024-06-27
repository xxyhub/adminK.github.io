<?php
/*
 +-=========================================================================-+
 |                                phpKF v3.00                                |
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


if (!defined('DOSYA_AYAR')) include 'ayar.php';
if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';
if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';


// ziyaretçi ip adresi alınıyor
if (isset($_SERVER["HTTP_CF_CONNECTING_IP"])) $ip = $_SERVER["HTTP_CF_CONNECTING_IP"];
else $ip = $_SERVER['REMOTE_ADDR'];
$ip = zkTemizle4($ip);
$ip = zkTemizle($ip);


// oturum bilgisine bakılıyor
if (isset($_GET['o'])) $go = zkTemizle($_GET['o']);
else $go = '';

// oturum kodu kontrol ediliyor
if ($go != $o)
{
	header('Location: '.$phpkf_dosyalar['hata'].'?hata=45');
	exit();
}



$sayfano = '-1';
$sayfa_adi = $l['cikis_yapti'];
$tarih = time();



// Android uygulaması için
if (!@preg_match('/phpKF\ Android\ Uygulamasi/', $_SERVER['HTTP_USER_AGENT']))
	$eksorgu = ",kullanici_kimlik='',yonetim_kimlik=''";
else $eksorgu = '';



// üyenin kimlik bilgileri siliniyor, sayfa ve tarih güncelleniyor
$vtsorgu = "UPDATE $tablo_kullanicilar SET son_hareket='$tarih', hangi_sayfada='$sayfa_adi', sayfano='$sayfano' ,kul_ip='$ip' $eksorgu WHERE id='$kullanici_kim[id]'";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());



// Çerezler siliniyor
setcookie('kullanici_kimlik', '', 0, $cerez_dizin, $cerez_alanadi);
setcookie('yonetim_kimlik', '', 0, $cerez_dizin, $cerez_alanadi);
setcookie('kfk_okundu', '', 0, $cerez_dizin, $cerez_alanadi);



// Gelinen sayfaya yönlendirme
if (isset($_SERVER['HTTP_REFERER']))
{
	if (is_array($_SERVER['HTTP_REFERER'])) $_SERVER['HTTP_REFERER'] = '';
	$gelinen = zkTemizle3($_SERVER['HTTP_REFERER']);
	$gelinen = zkTemizle4($gelinen);
	$gelinen = str_replace($TEMA_SITE_ANADIZIN, '', $gelinen);
	$gelinen = str_replace('&amp;', '&', $gelinen);
}
else $gelinen = '';


if ($gelinen == '') $adres = $phpkf_dosyalar['forum'].'?cikiss=1';
elseif ( (@preg_match('/giris.php/', $gelinen)) OR (@preg_match('/'.$phpkf_dosyalar['giris'].'/', $gelinen)) OR (@preg_match('/hata.php/', $gelinen)) OR (@preg_match('/'.$phpkf_dosyalar['hata'].'/', $gelinen)) OR (@preg_match('/yonetim\//', $gelinen)) ) $adres = $phpkf_dosyalar['forum'].'?cikiss=1';
elseif (@preg_match('/\//i', $gelinen)) $adres = $gelinen;
elseif (@preg_match('/.html/i', $gelinen)) $adres = $gelinen;
elseif (@preg_match('/.php\?/i', $gelinen)) $adres = $gelinen.'&cikiss=1';
else $adres = $gelinen.'?cikiss=1';


header('Location: '.$adres);
exit();
?>