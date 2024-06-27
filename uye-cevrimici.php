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
include_once('phpkf-bilesenler/hangi_sayfada.php');


$sayfa_numara = 5;
$cevrimici_kota = 30;
$sayfa_adi = $l['cevrimici_ziyaretciler'];
$TEMA_SAYFA_BASLIK = $l['cevrimici_ziyaretciler'];
$zaman_asimi = $ayarlar['uye_cevrimici_sure'];
$tarih = time();


if (isset($_GET['s'])) $sayfano = zkTemizleNumara($_GET['s']);
else $sayfano = 1;
$kosul_sayfa = ($sayfano * $cevrimici_kota)-$cevrimici_kota;



// Çevrimiçi kullanıcı sayısı alınıyor
$vtsonuc9 = $vt->query("SELECT id FROM $tablo_kullanicilar WHERE (son_hareket + $zaman_asimi) > $tarih AND gizli='0' AND sayfano!='-1'") or die ($vt->hata_ver());
$kullanici_sayi = $vt->num_rows($vtsonuc9);


// Gizli kullanıcı üye sayısı alınıyor
$vtsonuc9 = $vt->query("SELECT id FROM $tablo_kullanicilar WHERE (son_hareket + $zaman_asimi) > $tarih AND gizli='1' AND sayfano!='-1'") or die ($vt->hata_ver());
$gizli_sayi = $vt->num_rows($vtsonuc9);


// çevrimiçi misafir sayısı alınıyor
$vtsonuc9 = $vt->query("SELECT sid FROM $tablo_oturumlar WHERE (son_hareket + $zaman_asimi) > $tarih") or die ($vt->hata_ver());
$misafir_sayi = $vt->num_rows($vtsonuc9);


if ($kullanici_sayi > $misafir_sayi) $satir_sayi = $kullanici_sayi;
else $satir_sayi = $misafir_sayi;

$toplam_sayfa = ($satir_sayi / $cevrimici_kota);
settype($toplam_sayfa,'integer');

if (($satir_sayi % $cevrimici_kota) != 0) $toplam_sayfa++;



// yönetici ise gizlileri göster
if ($kullanici_kim['yetki'] == '1') $sorgu_ek = '';
else $sorgu_ek = "AND gizli='0'";



// Çevrimiçi kullanıcı bilgileri çekiliyor
$vtsorgu = "SELECT id,kullanici_adi,son_giris,son_hareket,hangi_sayfada,sayfano,kul_ip,gizli,yetki
FROM $tablo_kullanicilar WHERE (son_hareket + $zaman_asimi) > $tarih
$sorgu_ek AND sayfano!='-1'
ORDER BY son_hareket DESC LIMIT $kosul_sayfa,$cevrimici_kota";
$cevirim_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


// Çevrimiçi misafir bilgileri çekiliyor
$vtsorgu = "SELECT giris,son_hareket,hangi_sayfada,kul_ip,sayfano
FROM $tablo_oturumlar WHERE (son_hareket + $zaman_asimi) > $tarih
ORDER BY son_hareket DESC LIMIT $kosul_sayfa,$cevrimici_kota";
$misafir_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());






//  ÇEVRİMİÇİ KULLANICILAR SIRALANIYOR  //

while ($cevirimici_uyeler = $vt->fetch_assoc($cevirim_sonuc))
{

// Yetkiler
if ($cevirimici_uyeler['id'] == 1)
{
	if ($cevirimici_uyeler['gizli'] == 1) $uye_yetki = '<font class="kurucu"><i>'.$cevirimici_uyeler['kullanici_adi'].'</i></font>';
	else $uye_yetki = '<font class="kurucu">'.$cevirimici_uyeler['kullanici_adi'].'</font>';
}
elseif ($cevirimici_uyeler['yetki'] == 1)
{
	if ($cevirimici_uyeler['gizli'] == 1) $uye_yetki = '<font class="yonetici"><i>'.$cevirimici_uyeler['kullanici_adi'].'</i></font>';
	else $uye_yetki = '<font class="yonetici">'.$cevirimici_uyeler['kullanici_adi'].'</font>';
}
elseif ($cevirimici_uyeler['yetki'] == 2)
{
	if ($cevirimici_uyeler['gizli'] == 1) $uye_yetki = '<font class="yardimci"><i>'.$cevirimici_uyeler['kullanici_adi'].'</i></font>';
	else $uye_yetki = '<font class="yardimci">'.$cevirimici_uyeler['kullanici_adi'].'</font>';
}
elseif ($cevirimici_uyeler['yetki'] == 3)
{
	if ($cevirimici_uyeler['gizli'] == 1) $uye_yetki = '<font class="blm_yrd"><i>'.$cevirimici_uyeler['kullanici_adi'].'</i></font>';
	else $uye_yetki = '<font class="blm_yrd">'.$cevirimici_uyeler['kullanici_adi'].'</font>';
}
else
{
	if ($cevirimici_uyeler['gizli'] == 1) $uye_yetki = '<i>'.$cevirimici_uyeler['kullanici_adi'].'</i>';
	else $uye_yetki = $cevirimici_uyeler['kullanici_adi'];
}



// Hangi sayfada ve ip
$uye_sayfa = HangiSayfada($cevirimici_uyeler['sayfano'], $cevirimici_uyeler['hangi_sayfada']);
if ($kullanici_kim['yetki'] == 1) $uye_ip = '<a href="phpkf-yonetim/forum_ip_yonetimi.php?kip=1&amp;ip='.$cevirimici_uyeler['kul_ip'].'">'.$cevirimici_uyeler['kul_ip'].'</a>';
else $uye_ip = '';


// Bilgiler
$uye_baglanti = '<a href="'.linkyap($phpkf_dosyalar['profil'].'?u='.$cevirimici_uyeler['id'].'&kim='.$cevirimici_uyeler['kullanici_adi'], $cevirimici_uyeler['kullanici_adi']).'">'.$uye_yetki.'</a>';
$uye_son_giris = zaman('H:i:s', $ayarlar['saat_dilimi'], false, $cevirimici_uyeler['son_giris'], $ayarlar['tarih'], false);
$uye_son_hareket = zaman('H:i:s', $ayarlar['saat_dilimi'], false, $cevirimici_uyeler['son_hareket'], $ayarlar['tarih'], false);


$uyeler[] = array('baglanti' => $uye_baglanti,
'son_giris' => $uye_son_giris,
'son_hareket' => $uye_son_hareket,
'sayfa' => $uye_sayfa,
'ip' => $uye_ip);

}




//  ÇEVRİMİÇİ MİSAFİRLER SIRALANIYOR  //

while ($cevirimici_misafirler = $vt->fetch_assoc($misafir_sonuc))
{
	$misafir_son_giris = zaman('H:i:s', $ayarlar['saat_dilimi'], false, $cevirimici_misafirler['giris'], $ayarlar['tarih'], false);
	$misafir_son_hareket = zaman('H:i:s', $ayarlar['saat_dilimi'], false, $cevirimici_misafirler['son_hareket'], $ayarlar['tarih'], false);
	$misafir_sayfa = HangiSayfada($cevirimici_misafirler['sayfano'], $cevirimici_misafirler['hangi_sayfada']);

	if ($kullanici_kim['yetki'] == '1') $misafir_ip = '<a href="phpkf-yonetim/forum_ip_yonetimi.php?kip=1&amp;ip='.$cevirimici_misafirler['kul_ip'].'">'.$cevirimici_misafirler['kul_ip'].'</a>';
	else $misafir_ip = '';

	$misafir_bot = $l['misafir'];


	$misafirler[] = array('misafir_bot' => $misafir_bot,
	'son_giris' => $misafir_son_giris,
	'son_hareket' => $misafir_son_hareket,
	'sayfa' => $misafir_sayfa,
	'ip' => $misafir_ip);
}



// Ziyaretçi yoksa boş dizi değişkenler tanımlanıyor
if (!isset($uyeler)) $uyeler = array();
if (!isset($misafirler)) $misafirler = array();


// Sayfalama
if ($satir_sayi > $cevrimici_kota) $TEMA_SAYFALAMA = phpkf_sayfalama($satir_sayi, $cevrimici_kota, $sayfano, 's=');
else $TEMA_SAYFALAMA = '';



// Veriler hazırlanıyor
$cevrimici_sure = ($zaman_asimi/60);
$kullanici_sayi += $gizli_sayi;
$gizli_sayi = '('.$gizli_sayi.' '.$l['gizli'].')';
$cevrimici_bilgi = str_replace('{00}', $cevrimici_sure, $l['cevrimici_bilgi']);


if ($kullanici_kim['yetki'] == '1') $gizli = ' - <b><i>'.$l['gizli'].'</i></b>';
else $gizli = '';





// tema dosyası yükleniyor
eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.'/cevrimici.php'));

?>