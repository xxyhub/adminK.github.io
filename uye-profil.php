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



//	PROFİL DEĞİŞTİR	//

if ( (isset($_GET['kip'])) AND ($_GET['kip'] == 'degistir') )
{
	include_once('phpkf-bilesenler/profil_degistir.php');
	exit();
}



//	GALERİ	//

elseif ( (isset($_GET['kip'])) AND ($_GET['kip'] == 'pgaleri') )
{
	include_once('phpkf-bilesenler/profil_galeri.php');
	exit();
}



//	$U DEĞİŞKENİ VARSA BU KULLANICIYA AİT VERİLERİ ÇEK 	//

elseif ( (isset($_GET['u'])) AND ($_GET['u'] != '') )
{
	if (is_numeric($_GET['u']) == false)
	{
		header('Location: '.$phpkf_dosyalar['hata'].'?hata=46');
		exit();
	}

	$_GET['u'] = @zkTemizle($_GET['u']);

	$vtsorgu = "SELECT * FROM $tablo_kullanicilar WHERE id='$_GET[u]' LIMIT 1";

	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$profil = $vt->fetch_array($vtsonuc);

	if (empty($profil['kullanici_adi']))
	{
		header('Location: '.$phpkf_dosyalar['hata'].'?hata=46');
		exit();
	}

	$sayfa_numara = '4,'.$profil['id'];
	$sayfa_adi = $l['uye_profili'].': '.$profil['kullanici_adi'];
	$TEMA_SAYFA_BASLIK = $l['uye_profili'];
}




//	$KIM DEĞİŞKENİ VARSA BU KULLANICIYA AİT VERİLERİ ÇEK 	//

elseif ( (isset($_GET['kim'])) AND ($_GET['kim'] != '') )
{
	if (( strlen($_GET['kim']) > 20))
	{
		header('Location: '.$phpkf_dosyalar['hata'].'?hata=72');
		exit();
	}

	$_GET['kim'] = @zkTemizle($_GET['kim']);

	$vtsorgu = "SELECT * FROM $tablo_kullanicilar WHERE kullanici_adi='$_GET[kim]' LIMIT 1";

	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$profil = $vt->fetch_array($vtsonuc);

	if (empty($profil['kullanici_adi']))
	{
		header('Location: '.$phpkf_dosyalar['hata'].'?hata=46');
		exit();
	}

	$sayfa_numara = '4,'.$profil['id'];
	$sayfa_adi = $l['uye_profili'].': '.$profil['kullanici_adi'];
	$TEMA_SAYFA_BASLIK = $l['uye_profili'];
}




//	$U ve $KİM DEĞİŞKENİ YOKSA KULLANICININ KENDİ PROFİLİNİ ÇEK	//

else
{
	if (!defined('DOSYA_GUVENLIK')) include_once('phpkf-bilesenler/guvenlik.php');

	$_COOKIE['kullanici_kimlik'] = @zkTemizle($_COOKIE['kullanici_kimlik']);

	$vtsorgu = "SELECT * FROM $tablo_kullanicilar WHERE kullanici_kimlik='$_COOKIE[kullanici_kimlik]' LIMIT 1";

	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$profil = $vt->fetch_array($vtsonuc);

	$sayfa_numara = '4,'.$profil['id'];
	$sayfa_adi = $l['profilim'];
	$TEMA_SAYFA_BASLIK = $l['profilim'];
}




// SEF ADRESİNİN DOĞRULUĞU KONTROL EDİLİYOR, YANLIŞSA DOĞRU ADRESE YÖNLENDİRİLİYOR //

$dogru_adres = sefyap($profil['kullanici_adi']);
$dosyaprofil = str_replace('.', '\.', $phpkf_dosyalar['profil']);

if ( ($ayarlar['seo'] == 1) AND (isset($_SERVER['REQUEST_URI'])) AND ($_SERVER['REQUEST_URI'] != '') AND (!@preg_match("/-$dogru_adres.html/i", $_SERVER['REQUEST_URI'])) AND (!@preg_match("/$dosyaprofil/i", $_SERVER['REQUEST_URI'])) )
{
	$yonlendir = linkyap($phpkf_dosyalar['profil'].'?u='.$profil['id'].'&kim='.$profil['kullanici_adi'],$profil['kullanici_adi']);
	header('Location:'.$yonlendir);
	exit();
}




// tarih ve çevrimiçi durumu için
if (!isset($ayarlar['uye_cevrimici_sure']))
{
	$vtsorgu = "SELECT etiket,deger FROM $tablo_ayarlar WHERE kip='3'";
	$vtsonuc = $vt->query($vtsorgu) or die($vt->hata_ver());

	while ($ayar = $vt->fetch_assoc($vtsonuc)) $ayarlar[$ayar['etiket']] = $ayar['deger'];
}

$zaman_asimi = $ayarlar['uye_cevrimici_sure'];
$tarih = time();




// Bazı veriler siliniyor
$profil['kullanici_kimlik'] = '';
$profil['yonetim_kimlik'] = '';
$profil['kul_etkin_kod'] = '';
$profil['yeni_sifre'] = '';
$profil['sifre'] = '';


// Yetkiler
$yetki = phpkf_tema_yetkiler($profil['id'], $profil['yetki'], 1);
$TEMA_PROFIL['uye_yetki'] = '<span class="'.$yetki['renk'].'">'.$yetki['isim'].'</span>';


// doğum tarihi
if ($profil['dogum_tarihi_goster'] == 1)
{
	$TEMA_PROFIL['dogum_yas'] = $l['dogum_tarihi'];
	if ($profil['dogum_tarihi'] == '00-00-0000') $TEMA_PROFIL['uye_dogum'] = $l['yok'];
	else $TEMA_PROFIL['uye_dogum'] = $profil['dogum_tarihi'];
}
elseif ($profil['dogum_tarihi_goster'] == 2)
{
	$TEMA_PROFIL['dogum_yas'] = $l['yas'];
	$uye_dogum = explode('-', $profil['dogum_tarihi']);
	$TEMA_PROFIL['uye_dogum'] = @date('Y') - $uye_dogum[2];
}
else
{
	$TEMA_PROFIL['dogum_yas'] = $l['dogum_tarihi'];
	$TEMA_PROFIL['uye_dogum'] = $l['gizli'];
}


// şehir göster
if ($profil['sehir_goster'] == 1)
{
	if ($profil['sehir'] == '') $TEMA_PROFIL['uye_sehir'] = $l['yok'];
	else $TEMA_PROFIL['uye_sehir'] = $profil['sehir'];
}
else $TEMA_PROFIL['uye_sehir'] = $l['gizli'];


// posta göster
if ($profil['posta_goster'] == 1)
	$TEMA_PROFIL['uye_eposta'] = '<a title="'.$l['eposta_gonder'].'" href="mailto:'.$profil['posta'].'">'.$profil['posta'].'</a>';

else $TEMA_PROFIL['uye_eposta'] = $l['gizli'];


// özel ileti gönder
if ($forum_kullan == 1) $TEMA_PROFIL['uye_oi'] = '<a href="'.$phpkf_dosyalar['oi_yaz'].'?ozel_kime='.$profil['kullanici_adi'].'">'.$l['ozel_ileti_gonder'].'</a>';
else $TEMA_PROFIL['uye_oi'] = '';


// web adresi
if ($profil['web']) $TEMA_PROFIL['uye_web'] = '<a href="'.$profil['web'].'" target="_blank" rel="nofollow">'.str_replace(array('http://', 'https://'), '', $profil['web']).'</a>';
else $TEMA_PROFIL['uye_web'] = '';


// katılım tarihi
$TEMA_PROFIL['uye_katilim'] = zaman('d-m-Y', $ayarlar['saat_dilimi'], false, $profil['katilim_tarihi'], 0, false);



// üye durumu
if ($profil['kul_etkin'] != 1)
	$TEMA_PROFIL['uye_durum'] = '<font color="#FF0000">'.$l['etkisiz'].'</font>';

elseif ($profil['engelle'] == 1)
	$TEMA_PROFIL['uye_durum'] = '<font color="#FF0000">'.$l['engelli'].'</font>';

elseif ($profil['gizli'] == 1)
{
	$TEMA_PROFIL['uye_durum'] = '<font color="#FF0000">'.$l['gizli'].'</font>';

	if  ( (isset($kullanici_kim['yetki'])) AND ($kullanici_kim['yetki'] == 1)
	AND ( ($profil['son_hareket'] + $zaman_asimi) > $tarih )
	AND ($profil['sayfano'] != '-1') )
		$TEMA_PROFIL['uye_durum'] .= ' ('.$l['sitede'].')';

	elseif ( (isset($kullanici_kim['yetki'])) AND ($kullanici_kim['yetki'] == 1) )
		$TEMA_PROFIL['uye_durum'] .= ' ('.$l['sitede_degil'].')';
}

elseif ( (($profil['son_hareket'] + $zaman_asimi) > $tarih) AND ($profil['sayfano'] != '-1') )
	$TEMA_PROFIL['uye_durum'] = '<font color="#339900">'.$l['sitede'].'</font>';

else $TEMA_PROFIL['uye_durum'] = '<font color="#FF0000">'.$l['sitede_degil'].'</font>';




// gizli ise yönetici için üye durumu
if ( (isset($kullanici_kim['yetki'])) AND ($kullanici_kim['yetki'] == 1)
	AND ($profil['gizli'] == 1) AND ($profil['son_hareket'] != 0) )
$TEMA_PROFIL['uye_giris'] = zaman($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $profil['son_hareket'], $ayarlar['tarih'], true);


elseif ($profil['gizli'] == 1) $TEMA_PROFIL['uye_giris'] = $l['gizli'];


elseif ($profil['son_hareket'] != 0)
	$TEMA_PROFIL['uye_giris'] = zaman($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $profil['son_hareket'], $ayarlar['tarih'], true);

else $TEMA_PROFIL['uye_giris'] = '';



// Hangi sayfada
if ($profil['gizli'] == 1) $TEMA_PROFIL['uye_sayfa'] = $l['gizli'];
else $TEMA_PROFIL['uye_sayfa'] = HangiSayfada($profil['sayfano'], $profil['hangi_sayfada']);


// FACEBOOK
if ($profil['aim'] != '') $TEMA_PROFIL['uye_facebook'] = '<a href="'.$profil['aim'].'" target="_blank" rel="nofollow">'.$l['tiklayin'].'</a>';
else $TEMA_PROFIL['uye_facebook'] = '';


// TWITTER
if ($profil['skype'] != '') $TEMA_PROFIL['uye_twitter'] = '<a href="'.$profil['skype'].'" target="_blank" rel="nofollow">'.$l['tiklayin'].'</a>';
else $TEMA_PROFIL['uye_twitter'] = '';


// Skype - MSN
if ($profil['msn'] != '')
$TEMA_PROFIL['uye_msn'] = '<a href="skype:'.$profil['msn'].'" target="_blank" rel="nofollow">'.$l['tiklayin'].'</a>';
else $TEMA_PROFIL['uye_msn'] = '';


// YAHOO
if ($profil['yahoo'] != '') $TEMA_PROFIL['uye_yahoo'] = '<a href="http://members.yahoo.com/interests?.oc=t&amp;.kw='.$profil['yahoo'].'&amp;.sb=1" target="_blank" rel="nofollow">'.$l['tiklayin'].'</a>';
else $TEMA_PROFIL['uye_yahoo'] = '';


// ICQ
if ($profil['icq'] != '') $TEMA_PROFIL['uye_icq'] = '<a href="http://wwp.icq.com/scripts/search.dll?to='.$profil['icq'].'" target="_blank" rel="nofollow">'.$l['tiklayin'].'</a>';
else $TEMA_PROFIL['uye_icq'] = '';


// RESIM - AVATAR
if ($profil['resim'] != '') $TEMA_PROFIL['uye_resim'] = '<img src="'.$profil['resim'].'" alt="'.$l['uye_resmi'].'">';
elseif ($ayarlar['v-uye_resmi'] != '') $TEMA_PROFIL['uye_resim'] = '<img src="'.$ayarlar['v-uye_resmi'].'" alt="'.$l['varsayilan_uye_resmi'].'">';
else $TEMA_PROFIL['uye_resim'] = '';



// cinsiyet
if ($profil['cinsiyet'] == '1') $TEMA_PROFIL['uye_cinsiyet'] = $l['erkek'];
elseif ($profil['cinsiyet'] == '2') $TEMA_PROFIL['uye_cinsiyet'] = $l['kadin'];
else $TEMA_PROFIL['uye_cinsiyet'] = $l['belirtilmemis'];



// imza
if ( (isset($profil['imza'])) AND ($profil['imza'] != '') )
	$TEMA_PROFIL['uye_imza'] = bbcode_acik(ifadeler($profil['imza']),0);
else $TEMA_PROFIL['uye_imza'] = $l['imza_yok'];



// hakkında
if ( (isset($profil['hakkinda'])) AND ($profil['hakkinda'] != '') )
{
	if ($ayarlar['bbcode'] == 1) $TEMA_PROFIL['uye_hakkinda'] = bbcode_acik(ifadeler($profil['hakkinda']),0);
	else $TEMA_PROFIL['uye_hakkinda'] = bbcode_kapali(ifadeler($profil['hakkinda']));
}
else $TEMA_PROFIL['uye_hakkinda'] = $l['hakinda_yok'];




// diğer
$TEMA_PROFIL['uye_adi'] = $profil['kullanici_adi'];
$TEMA_PROFIL['tam_adi'] = $profil['gercek_ad'];
$TEMA_PROFIL['yorum_sayisi'] = NumaraBicim($profil['mesaj_sayisi']);




// tema dosyası yükleniyor
eval(phpkf_tema_yukle('phpkf-bilesenler/temalar/'.$temadizini_cms.'/profil.php'));


?>