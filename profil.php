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
include_once('phpkf-bilesenler/seo.php');

$zaman_asimi = $ayarlar['uye_cevrimici_sure'];
$tarih = time();



//	$U DEĞİŞKENİ VARSA BU KULLANICIYA AİT VERİLERİ ÇEK 	//

if ( (isset($_GET['u'])) AND ($_GET['u'] != '') )
{
	if (is_numeric($_GET['u']) == false)
	{
		header('Location: hata.php?hata=46');
		exit();
	}

	$_GET['u'] = @zkTemizleNumara($_GET['u']);

	$vtsorgu = "SELECT * FROM $tablo_kullanicilar WHERE id='$_GET[u]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$satir = $vt->fetch_array($vtsonuc);

	if (empty($satir['kullanici_adi']))
	{
		header('Location: hata.php?hata=46');
		exit();
	}

	$sayfano = '4,'.$satir['id'];
	$sayfa_adi = 'Profil Görüntüle: '.$satir['kullanici_adi'];
	$sayfa_baslik2 = 'Üye Profili';
	$bildirim_kapat = '';

	// Bazı veriler siliniyor
	$satir['kullanici_kimlik'] = '';
	$satir['yonetim_kimlik'] = '';
	$satir['kul_etkin_kod'] = '';
	$satir['yeni_sifre'] = '';
	$satir['sifre'] = '';
}




//	$KIM DEĞİŞKENİ VARSA BU KULLANICIYA AİT VERİLERİ ÇEK 	//

elseif ( (isset($_GET['kim'])) AND ($_GET['kim'] != '') )
{
	if ((@strlen($_GET['kim']) > 20))
	{
		header('Location: hata.php?hata=72');
		exit();
	}

	$_GET['kim'] = @zkTemizle($_GET['kim']);

	$vtsorgu = "SELECT * FROM $tablo_kullanicilar WHERE kullanici_adi='$_GET[kim]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$satir = $vt->fetch_array($vtsonuc);

	if (empty($satir['kullanici_adi']))
	{
		header('Location: hata.php?hata=46');
		exit();
	}

	$sayfano = '4,'.$satir['id'];
	$sayfa_adi = 'Profil Görüntüle: '.$satir['kullanici_adi'];
	$sayfa_baslik2 = 'Kullanıcı Profili';
	$bildirim_kapat = '';

	// Bazı veriler siliniyor
	$satir['kullanici_kimlik'] = '';
	$satir['yonetim_kimlik'] = '';
	$satir['kul_etkin_kod'] = '';
	$satir['yeni_sifre'] = '';
	$satir['sifre'] = '';
}



//	$U ve $KİM DEĞİŞKENİ YOKSA KULLANICININ KENDİ PROFİLİNİ ÇEK	//

else
{
	if (!defined('DOSYA_GUVENLIK')) include 'phpkf-bilesenler/guvenlik.php';
	if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';

	$satir = $kullanici_kim;
	$sayfano = '4,'.$satir['id'];
	$sayfa_adi = 'Profil Görüntüle';
	$sayfa_baslik2 = 'Profilim';
	$bildirim_kapat = "Bildirim('bildirimk1',4)";
}




// SEO ADRESİNİN DOĞRULUĞU KONTROL EDİLİYOR YANLIŞSA DOĞRU ADRESE YÖNLENDİRİLİYOR //

$dogru_adres = seoyap($satir['kullanici_adi']);

if ( (isset($_SERVER['REQUEST_URI'])) AND ($_SERVER['REQUEST_URI'] != '') AND (!@preg_match("/-$dogru_adres.html/i", $_SERVER['REQUEST_URI'])) AND (!@preg_match('/profil\.php/i', $_SERVER['REQUEST_URI'])) )
{
	$yonlendir = linkver('profil.php?u='.$satir['id'].'&kim='.$satir['kullanici_adi'],$satir['kullanici_adi']);
	header('Location:'.$yonlendir);
	exit();
}



include_once('phpkf-bilesenler/sayfa_baslik_forum.php');
include 'phpkf-bilesenler/hangi_sayfada.php';


//	TEMA SINIFI ÖRNEĞİ OLUŞTURULUYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/profil.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));



$blmyrd_yetki = '';

if ($satir['id'] == 1) $uye_yetki = '<font class="kurucu">'.$ayarlar['kurucu'].'</font>';

elseif ($satir['yetki'] == 1) $uye_yetki = '<font class="yonetici">'.$ayarlar['yonetici'].'</font>';

elseif ($satir['yetki'] == 2) $uye_yetki = '<font class="yardimci">'.$ayarlar['yardimci'].'</font>';

// bölüm yardımcısı
elseif ($satir['yetki'] == 3)
{
	$uye_yetki = '<font class="blm_yrd">'.$ayarlar['blm_yrd'].'</font>';

	if ($satir['grupid'] != '0') $grupek = "grup='$satir[grupid]' AND yonetme='1' OR";
	else $grupek = "grup='0' AND";

	$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$satir[kullanici_adi]' AND yonetme='1' ORDER BY fno DESC";
	$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());

	while ($ozelizinler_satir = $vt->fetch_array($vtsonuc2))
	{
		$vtsorgu3 = "SELECT id,forum_baslik FROM $tablo_forumlar WHERE id='$ozelizinler_satir[fno]' LIMIT 1";
		$vtsonuc3 = $vt->query($vtsorgu3) or die ($vt->hata_ver());
		$forum_satir = $vt->fetch_array($vtsonuc3);

		$blmyrd_yetki .= '<a href="'.linkver('forum.php?f='.$forum_satir['id'], $forum_satir['forum_baslik']).'">'.$forum_satir['forum_baslik'].'</a><br>';
	}
}

else $uye_yetki = '<font class="kullanici">'.$ayarlar['kullanici'].'</font>';



//	grup üyeliği varsa grubun bilgileri çekiliyor
if ($satir['grupid'] != 0)
{
	$vtsorgu = "SELECT grup_adi,gizle FROM $tablo_gruplar WHERE id='$satir[grupid]' LIMIT 1";

	$vtsonuc4 = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$grup_satir = $vt->fetch_assoc($vtsonuc4);

	// grup gizli değilse
	if ($grup_satir['gizle'] != '1') $ornek1->kosul('4', array('{UYE_GRUBU}' => '<a href="uyeler.php?kip=grup">'.$grup_satir['grup_adi'].'</a>'), true);

	else $ornek1->kosul('4', array('' => ''), false);
}

else $ornek1->kosul('4', array('' => ''), false);



if ($satir['dogum_tarihi_goster'] == 1)
{
	$dogum_yas = 'Doğum Tarihi';
	if ($satir['dogum_tarihi'] == '00-00-0000') $uye_dogum = 'Girilmemiş';
	else $uye_dogum = $uye_dogum = $satir['dogum_tarihi'];
}
elseif ($satir['dogum_tarihi_goster'] == 2)
{
	$dogum_yas = 'Yaşı';
	$uye_dogum = explode('-', $satir['dogum_tarihi']);
	$uye_dogum = @date('Y') - $uye_dogum[2];
}
else {$uye_dogum = 'Gizli'; $dogum_yas = 'Doğum Tarihi';}


if ($satir['sehir_goster'] == 1)
{
	if ($satir['sehir'] != '') $uye_sehir = $satir['sehir'];
	else $uye_sehir = 'Yok';
}
else $uye_sehir = 'Gizli';



if ($satir['posta_goster'] == 1) $uye_eposta = '<a title="Forum üzerinden e-posta gönder" href="eposta.php?kim='.$satir['kullanici_adi'].'">'.$satir['posta'].'</a>';
else $uye_eposta = '<a title="Forum üzerinden e-posta gönder" href="eposta.php?kim='.$satir['kullanici_adi'].'">E-Posta Gönder</a>';



$uye_oi = '<a href="oi_yaz.php?ozel_kime='.$satir['kullanici_adi'].'">Özel ileti Gönder</a>';



if ($satir['web']) $uye_web = '<a href="'.$satir['web'].'" target="_blank" rel="nofollow">Tıklayın</a>';
else $uye_web = '';



$uye_katilim = zonedate('d-m-Y', $ayarlar['saat_dilimi'], false, $satir['katilim_tarihi']);



if ( (isset($_GET['kim'])) OR (isset($_GET['u'])) )
{
	$konu_goster = '<a href="km_ara.php?kip=mesaj&amp;kim='.$satir['kullanici_adi'].'">Açtığı Konuları Göster</a>';
	$cevap_goster = '<a href="km_ara.php?kip=cevap&amp;kim='.$satir['kullanici_adi'].'">Yazdığı Cevapları Göster</a>';
	$mesaj_ara = '<a href="arama.php?a=1&amp;b=1&amp;forum=tum&amp;yazar_ara='.$satir['kullanici_adi'].'">Tüm yazdıklarında Arama Yap</a>';

	$ornek1->kosul('2', array('' => ''), false);
	$tablo_genislik = '700';
}


else
{
	$konu_goster = '<a href="km_ara.php?kip=mesaj&amp;kim='.$satir['kullanici_adi'].'">Açtığım Konuları Göster</a>';
	$cevap_goster = '<a href="km_ara.php?kip=cevap&amp;kim='.$satir['kullanici_adi'].'">Yazdığım Cevapları Göster</a>';
	$mesaj_ara = '<a href="arama.php?a=1&amp;b=1&amp;forum=tum&amp;yazar_ara='.$satir['kullanici_adi'].'">Tüm yazdıklarımda Arama Yap</a>';

	// okunmamış özel iletisi varsa
	if ($ayarlar['o_ileti'] == 1)
	{
		if ($kullanici_kim['okunmamis_oi'])
			$okunmamis_oi = ' ('.$kullanici_kim['okunmamis_oi'].')';
		else $okunmamis_oi = '';
	}

	else $okunmamis_oi = '';

	$ornek1->kosul('2', array('{OKUNMAMIS_OI}' => $okunmamis_oi), true);
	$tablo_genislik = '852';
}



if ($satir['kul_etkin'] != 1)
$uye_durum = '<font color="#FF0000">Etkinleştirilmemiş</font>';


elseif ($satir['engelle'] == 1)
$uye_durum = '<font color="#FF0000">Uzaklaştırılmış</font>';


elseif ($satir['gizli'] == 1)
{
	$uye_durum = '<font color="#FF0000">Gizli</font>';

	if  ( (isset($kullanici_kim['yetki'])) AND ($kullanici_kim['yetki'] == 1)
	AND ( ($satir['son_hareket'] + $zaman_asimi) > $tarih )
	AND ($satir['sayfano'] != '-1') )
	$uye_durum .= ' (Forumda)';

	elseif ( (isset($kullanici_kim['yetki'])) AND ($kullanici_kim['yetki'] == 1) )
	$uye_durum .= ' (Forumda Değil)';
}


elseif ( (($satir['son_hareket'] + $zaman_asimi) > $tarih) AND ($satir['sayfano'] != '-1') )
$uye_durum = '<font color="#339900">Forumda</font>';


else $uye_durum = '<font color="#FF0000">Forumda Değil</font>';




if ( (isset($kullanici_kim['yetki'])) AND ($kullanici_kim['yetki'] == 1)
	AND ($satir['gizli'] == 1) AND ($satir['son_hareket'] != 0) )
$uye_giris = zonedate('d-m-Y- H:i', $ayarlar['saat_dilimi'], false, $satir['son_hareket']);


elseif ($satir['gizli'] == 1) $uye_giris = 'Gizli';


elseif ($satir['son_hareket'] != 0)
$uye_giris = zonedate('d-m-Y- H:i', $ayarlar['saat_dilimi'], false, $satir['son_hareket']);

else $uye_giris = '';




if ( (isset($kullanici_kim['yetki'])) AND ($kullanici_kim['yetki'] == 1) )
{
	$uye_sayfa = HangiSayfada($satir['sayfano'], $satir['hangi_sayfada']);
}

elseif ($satir['gizli'] == 1) $uye_sayfa = '<b>Gizli</b>';

else
{
	if (@preg_match('/^Yönetim/', $satir['hangi_sayfada'])) $uye_sayfa = 'Yönetim Sayfaları';
	elseif (@preg_match('/^Hata iletisi /', $satir['hangi_sayfada'])) $uye_sayfa = 'Hata iletisi';
	elseif (@preg_match('/^Bilgi iletisi /', $satir['hangi_sayfada'])) $uye_sayfa = 'Bilgi iletisi';
	elseif (@preg_match('/^Uyarı iletisi /', $satir['hangi_sayfada'])) $uye_sayfa = 'Uyarı iletisi';
	else $uye_sayfa = HangiSayfada($satir['sayfano'], $satir['hangi_sayfada']);
}


// FACEBOOK
if ($satir['aim'] != '') $uye_aim = '<a href="'.$satir['aim'].'" target="_blank" rel="nofollow"">Tıklayın</a>';
else $uye_aim = '';


// TWITTER
if ($satir['skype'] != '') $uye_skype = '<a href="'.$satir['skype'].'" target="_blank" rel="nofollow">Tıklayın</a>';
else $uye_skype = '';


// Skype
if ($satir['msn'] != '')
$uye_msn = '<a href="skype:'.$satir['msn'].'?userinfo" target="_blank" rel="nofollow">Tıklayın</a>';
else $uye_msn = '';


// YAHOO
if ($satir['yahoo'] != '') $uye_yahoo = '<a href="http://members.yahoo.com/interests?.oc=t&amp;.kw='.$satir['yahoo'].'&amp;.sb=1" target="_blank" rel="nofollow">Tıklayın</a>';
else $uye_yahoo = '';


// ICQ
if ($satir['icq'] != '') $uye_icq = '<a href="http://wwp.icq.com/scripts/search.dll?to='.$satir['icq'].'" target="_blank" rel="nofollow">Tıklayın</a>';
else $uye_icq = '';


// RESIM - AVATAR
if ($satir['resim'] != '') $uye_resim = '<img src="'.$satir['resim'].'" alt="Kullanıcı Resmi" title="Kullanıcı Resmi" style="max-width:98%" />';
elseif ($ayarlar['v-uye_resmi'] != '') $uye_resim = '<img src="'.$ayarlar['v-uye_resmi'].'" alt="Varsayılan Kullanıcı Resmi" style="max-width:98%" />';
else $uye_resim = '';



// cinsiyet
if ($satir['cinsiyet'] == '1') $uye_cinsiyet = 'Erkek';
elseif ($satir['cinsiyet'] == '2') $uye_cinsiyet = 'Kadın';
else $uye_cinsiyet = 'Belirtilmemiş';



// imza
if ( (isset($satir['imza'])) AND ($satir['imza'] != '') )
{
	$uye_imza = '<br>';
	if ($ayarlar['bbcode'] == 1) $uye_imza .= bbcode_acik(ifadeler($satir['imza']),0);
	else $uye_imza .= bbcode_kapali(ifadeler($satir['imza']));
}

else $uye_imza = '<br>Üyenin imzası bulunmamaktadır.<br><br>';



// hakkında
if ( (isset($satir['hakkinda'])) AND ($satir['hakkinda'] != '') )
{
	$uye_hakkinda = '<br>';
	if ($ayarlar['bbcode'] == 1) $uye_hakkinda .= bbcode_acik(ifadeler($satir['hakkinda']),0);
	else $uye_hakkinda .= bbcode_kapali(ifadeler($satir['hakkinda']));
}

else $uye_hakkinda = '<br>Üyenin hakkında yazısı bulunmamaktadır.<br><br>';



if (isset($kullanici_kim['id'])) $jsgiris = 1;
else $jsgiris = 0;



// JavaScript Kodları

$javascript_kodu = '<script type="text/javascript"><!-- //
var jsgiris = '.$jsgiris.';
var uyeid = '.$satir['id'].';
var mesaj_sayisi = '.$satir['mesaj_sayisi'].';
var yrm_sayi = '.$satir['yrm_sayi'].';
// -->
</script>
<script type="text/javascript" src="phpkf-bilesenler/js/betik_profil.js"></script>';



//	TEMA UYGULANIYOR	//

$ornek1->dongusuz(array('{SAYFA_BASLIK}' => $sayfa_baslik2,
'{UYE_ADI}' => $satir['kullanici_adi'],
'{UYE_YETKI}' => $uye_yetki,
'{UYE_GERCEK_AD}' => $satir['gercek_ad'],
'{DOGUM_YAS}' => $dogum_yas,
'{UYE_DOGUM}' => $uye_dogum,
'{UYE_SEHIR}' => $uye_sehir,
'{UYE_EPOSTA}' => $uye_eposta,
'{UYE_OI}' => $uye_oi,
'{UYE_WEB}' => $uye_web,
'{UYE_KATILIM}' => $uye_katilim,
'{UYE_MESAJ_SAYISI}' => NumaraBicim($satir['mesaj_sayisi']),
'{KONU_GOSTER}' => $konu_goster,
'{CEVAP_GOSTER}' => $cevap_goster,
'{MESAJ_ARA}' => $mesaj_ara,
'{UYE_DURUM}' => $uye_durum,
'{UYE_GIRIS}' => $uye_giris,
'{SON_SAYFA}' => $uye_sayfa,
'{UYE_ICQ}' => $uye_icq,
'{UYE_AIM}' => $uye_aim,
'{UYE_MSN}' => $uye_msn,
'{UYE_YAHOO}' => $uye_yahoo,
'{UYE_SKYPE}' => $uye_skype,
'{UYE_RESIM}' => $uye_resim,
'{UYE_CINSIYET}' => $uye_cinsiyet,
'{UYE_IMZA}' => $uye_imza,
'{UYE_HAKKINDA}' => $uye_hakkinda,
'{JAVASCRIPT_KODU}' => $javascript_kodu,
'{YRM_SAYI}' => $satir['yrm_sayi'],
'{YRM_YAPILAN}' => $satir['yrm_yapilan'],
'{BILDIRIM_KAPAT}' => $bildirim_kapat,
'{TG}' => $tablo_genislik));


if ( (isset($satir['ozel_ad']))  AND ($satir['ozel_ad'] != '') )
	$ornek1->kosul('1', array('{OZEL_AD}' => $satir['ozel_ad']), true);

else $ornek1->kosul('1', array('' => ''), false);


if ($blmyrd_yetki != '')
	$ornek1->kosul('3', array('{BLMYRD_YETKI}' => $blmyrd_yetki), true);

else $ornek1->kosul('3', array('' => ''), false);


eval(TEMA_UYGULA);

?>