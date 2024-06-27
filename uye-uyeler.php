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


$uye_kota = 15;
$sayfa_numara = 7;
$sayfa_adi = $l['uyeler'];


// sayfa değişkeni temizleniyor
if (isset($_GET['us'])) $sayfano = zkTemizleNumara($_GET['us']);
else $sayfano = 1;
if ((!is_numeric($sayfano)) OR ($sayfano == 0)) $sayfano = 1;


if (!isset($_GET['sirala'])) $_GET['sirala'] = 1;
else $_GET['sirala'] = @zkTemizle4(@zkTemizle($_GET['sirala']));


if ( (!isset($_GET['kul_ara'])) OR ($_GET['kul_ara'] == '') )
{
	$kul_ara = '';
	$TEMA_UYE_ARAMA = '';
}
else
{
	$kul_ara = @zkTemizle4(@zkTemizle($_GET['kul_ara']));
	$TEMA_UYE_ARAMA = $kul_ara;
	$kul_ara = @str_replace('*','%',trim($kul_ara));
}


if ((@strlen($kul_ara) > 20))
{
	header('Location: hata.php?hata=19');
	exit();
}




//  ÜYE SIRALAMA KODLARI - BAŞI  //

$katilim_0dan9a = '';
$katilim_9dan0a = '';
$ad_AdanZye = '';
$ad_ZdenAya = '';
$mesaj_9dan0a = '';
$mesaj_0dan9a = '';
$yetkiyeGore = '';


// Üye sıralama biçimi
if ($_GET['sirala'] == 'mesaj_0dan9a')
{
	$uye_kosul_sirala = "mesaj_sayisi";
	$mesaj_0dan9a = ' selected="selected"';
}
elseif ($_GET['sirala'] == 'mesaj_9dan0a')
{
	$uye_kosul_sirala = "mesaj_sayisi DESC";
	$mesaj_9dan0a = ' selected="selected"';
}
elseif ($_GET['sirala'] == 'katilim_9dan0a')
{
	$uye_kosul_sirala = "id DESC";
	$katilim_9dan0a = ' selected="selected"';
}
elseif ($_GET['sirala'] == 'ad_AdanZye')
{
	$uye_kosul_sirala = "kullanici_adi";
	$ad_AdanZye = ' selected="selected"';
}
elseif ($_GET['sirala'] == 'ad_ZdenAya')
{
	$uye_kosul_sirala = "kullanici_adi DESC";
	$ad_ZdenAya = ' selected="selected"';
}
elseif ($_GET['sirala'] == 'yetki')
{
	$uye_kosul_sirala = "yetki=0, yetki=3, yetki=2, yetki=1, id";
	$yetkiyeGore = ' selected="selected"';
}
else
{
	$uye_kosul_sirala = "id";
	$katilim_0dan9a = ' selected="selected"';
}



// Sıralama seçenekleri
$TEMA_FORM_SIRALAMA = '<option value="1"'.$katilim_0dan9a.'>'.$l['sirala_kayit'].'</option>
<option value="katilim_9dan0a"'.$katilim_9dan0a.'>'.$l['sirala_kayit_ters'].'</option>
<option value="ad_AdanZye"'.$ad_AdanZye.'>'.$l['sirala_ad'].'</option>
<option value="ad_ZdenAya"'.$ad_ZdenAya.'>'.$l['sirala_ad_ters'].'</option>
<option value="mesaj_9dan0a"'.$mesaj_9dan0a.'>'.$l['sirala_ileti'].'</option>
<option value="mesaj_0dan9a"'.$mesaj_0dan9a.'>'.$l['sirala_ileti_ters'].'</option>
<option value="yetki"'.$yetkiyeGore.'>'.$l['sirala_yetki'].'</option>';

//  ÜYE SIRALAMA KODLARI - SONU  //




// Değerler
$uye_kosul_etkin = 1;
$uye_kosul_engel = 0;


// sayfalama koşulu
$kosul_sayfa = ($sayfano * $uye_kota)-$uye_kota;

// Toplam üye alma
$uye_kosul = array(
'etkin' => $uye_kosul_etkin,
'engel' => $uye_kosul_engel,
'arama' => $kul_ara,
'alan' => 'id');

$toplam_uye = phpkf_tema_toplam_uye($uye_kosul);

$TEMA_SAYFALAMA = phpkf_sayfalama($toplam_uye, $uye_kota, $sayfano, 'us=');


// temadaki fonksiyon için yazı koşulları belirleniyor
$uye_kosul = array(
'etkin' => $uye_kosul_etkin,
'engel' => $uye_kosul_engel,
'arama' => $kul_ara,
'alan' => 'id, resim, kullanici_adi, mesaj_sayisi, katilim_tarihi, sehir, sehir_goster, yetki',
'sirala' => $uye_kosul_sirala,
'kota' => $uye_kota,
'sayfa' => $kosul_sayfa,
);


// tema dosyası yükleniyor
eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.'/uyeler.php'));

?>