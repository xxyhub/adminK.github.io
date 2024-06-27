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
if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';
include_once('phpkf-bilesenler/seo.php');
$zaman_asimi = $ayarlar['uye_cevrimici_sure'];


//	SAYFA DEĞERLERİ YOKSA SIFIR YAPILIYOR

if (isset($_GET['fsayfa'])) $_GET['fs'] = $_GET['fsayfa'];
if (isset($_GET['fno'])) $_GET['f'] = $_GET['fno'];

if (empty($_GET['fs'])) {$_GET['fs'] = 0; $baslik_ek = '';}
else
{
    $_GET['fs'] = @zkTemizle($_GET['fs']);
    $_GET['fs'] = @str_replace(array('-','x','.'), '', $_GET['fs']);
    if (is_numeric($_GET['fs']) == false) $_GET['fs'] = 0;
    if ($_GET['fs'] < 0) $_GET['fs'] = 0;
    $baslik_ek = ' : Sayfa '.(($_GET['fs']/$ayarlar['fsyfkota'])+1);
}


if ($_GET['fs'] == 0) $fs = '';
else $fs = '&fs='.$_GET['fs'];


if (empty($_GET['f'])) $_GET['f'] = 0;
else $_GET['f'] = @zkTemizle($_GET['f']);


if (is_numeric($_GET['f']) == false)
{
	header('Location: hata.php?hata=14');
	exit();
}



//	ÜST FORUM BİLGİLERİ ÇEKİLİYOR	//

$vtsorgu = "SELECT id,forum_baslik,okuma_izni,konu_acma_izni,alt_forum FROM $tablo_forumlar WHERE id='$_GET[f]' LIMIT 1";
$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
$forum_satir = $vt->fetch_assoc($vtsonuc2);

if (empty($forum_satir))
{
	header('Location: hata.php?hata=14');
	exit();
}


// SEO ADRESİNİN DOĞRULUĞU KONTROL EDİLİYOR YANLIŞSA DOĞRU ADRESE YÖNLENDİRİLİYOR //

$dogru_adres = seoyap($forum_satir['forum_baslik']);

if ( (isset($_SERVER['REQUEST_URI'])) AND ($_SERVER['REQUEST_URI'] != '') AND (!@preg_match("/-$dogru_adres.html/i", $_SERVER['REQUEST_URI'])) AND (!@preg_match('/forum\.php\?/i', $_SERVER['REQUEST_URI'])) )
{
    $yonlendir = linkver('forum.php?f='.$forum_satir['id'], $forum_satir['forum_baslik']);
    header('Location:'.$yonlendir);
    exit();
}




			//	KULLANICIYA GÖRE FORUM GÖSTERİMİ - BAŞI		//



//	FORUM HERKESE KAPALIYSA	//

if ($forum_satir['okuma_izni'] == 5)
{
	// sadece yöneticiyse girebilir
	if ( (!isset($kullanici_kim['yetki']) ) OR ($kullanici_kim['yetki'] != 1) )
	{
		header('Location: hata.php?hata=164');
		exit();
	}
}


//	FORUM MİSAFİRLERE KAPALIYSA		//

if ($forum_satir['okuma_izni'] > 0)
{
	// üye değilse - ziyaretçiyse
	if (empty($kullanici_kim['id']))
	{
		if (@preg_match('/cikiss=1/', $_SERVER['REQUEST_URI']))
		{
			header('Location: index.php');
			exit();
		}

		else
		{
			header('Location: hata.php?uyari=6&git='.$_SERVER['REQUEST_URI']);
			exit();
		}
	}
}


//	SADECE YÖNETİCİLER İÇİNSE	//

if ($forum_satir['okuma_izni'] == 1)
{
	if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) )
	{
		header('Location: hata.php?hata=15');
		exit();
	}
}


//	SADECE YÖNETİCİLER VE YARDIMCILAR İÇİNSE	//

elseif ($forum_satir['okuma_izni'] == 2)
{
	if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1)
		AND ($kullanici_kim['yetki'] != 2) AND ($kullanici_kim['yetki'] != 3) )
	{
		header('Location: hata.php?hata=16');
		exit();
	}
}


//	SADECE ÖZEL ÜYELER İÇİNSE 	//

elseif ($forum_satir['okuma_izni'] == 3)
{
	//	YÖNETİCİ DEĞİLSE YARDIMCILIĞINA BAK	//

	if ( ( isset($kullanici_kim['yetki']) ) AND ($kullanici_kim['yetki'] != 1) AND ($kullanici_kim['yetki'] != 2) )
	{
		if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$_GET[f]' AND okuma='1' OR";
		else $grupek = "grup='0' AND";

		$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$_GET[f]' AND okuma='1'";
		$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());

		if (!$vt->num_rows($kul_izin))
		{
			header('Location: hata.php?hata=17');
			exit();
		}
	}
}

			//	KULLANICIYA GÖRE FORUM GÖSTERİMİ - SONU			//




//	SAYFA ADI VE BAŞLIK DOSYASI	//

$sayfano = '3,'.$forum_satir['id'];
$sayfa_adi = $forum_satir['forum_baslik'].$baslik_ek;

include_once('phpkf-bilesenler/sayfa_baslik_forum.php');





	//	ALT FORUM KODLARI - BAŞI	//


//	ALT FORUM BİLGİLERİ ÇEKİLİYOR	//

$vtsorgu = "SELECT id,forum_baslik,forum_bilgi,okuma_izni,resim,konu_sayisi,cevap_sayisi,gizle
		FROM $tablo_forumlar WHERE alt_forum='$forum_satir[id]' ORDER BY sira";
$vtsonuc4 = $vt->query($vtsorgu) or die ($vt->hata_ver());

$toplam_baslik = 0;
$toplam_mesaj = 0;
$forum_yardimcilari = '';



//	ALT FORUM DÖNGÜSÜ	//

while ($alt_forum_satir = $vt->fetch_assoc($vtsonuc4)):


// Yetkiye göre üst forum (ve konu) başlığı gizleme

if (($alt_forum_satir['gizle'] == 1) AND ($alt_forum_satir['okuma_izni'] != 0))
{
	if (isset($kullanici_kim['id']))
	{
		if (($alt_forum_satir['okuma_izni'] == 5) AND ($kullanici_kim['yetki'] != 1)) continue;
		elseif (($alt_forum_satir['okuma_izni'] == 1) AND ($kullanici_kim['yetki'] != 1)) continue;
		elseif (($alt_forum_satir['okuma_izni'] == 2) AND ($kullanici_kim['yetki'] == 0)) continue;
		elseif (($alt_forum_satir['okuma_izni'] == 3) AND ($kullanici_kim['yetki'] != 1) AND ($kullanici_kim['yetki'] != 2))
		{
			if ($kullanici_kim['yetki'] >= 0)
			{
				if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$alt_forum_satir[id]' AND okuma='1' OR";
				else $grupek = "grup='0' AND";

				$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$alt_forum_satir[id]' AND okuma='1'";
				$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());
				if (!$vt->num_rows($kul_izin)) continue;
			}
			else continue;
		}
	}
	else continue;
}


unset($yardimcilar);

$vtsorgu = "SELECT kulid,kulad,grup FROM $tablo_ozel_izinler WHERE fno='$alt_forum_satir[id]' AND yonetme='1' ORDER BY kulad";
$ysonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());

while ($yardimci = $vt->fetch_assoc($ysonuc))
{
	if ($yardimci['grup'] == '0')
	{
		if (empty($yardimcilar)) $yardimcilar = '<a href="'.linkver('profil.php?u='.$yardimci['kulid'].'&kim='.$yardimci['kulad'],$yardimci['kulad']).'">'.$yardimci['kulad'].'</a>';
		else $yardimcilar .= ', <a href="'.linkver('profil.php?u='.$yardimci['kulid'].'&kim='.$yardimci['kulad'],$yardimci['kulad']).'">'.$yardimci['kulad'].'</a>';
	}

	else
	{
		if (empty($yardimcilar)) $yardimcilar = '<a href="uyeler.php?kip=grup">'.$yardimci['kulad'].'</a>';
		else $yardimcilar .= ', <a href="uyeler.php?kip=grup">'.$yardimci['kulad'].'</a>';
	}
}



$forum_klasor = '';

if ($alt_forum_satir['okuma_izni'] == 0) $forum_klasor .= $acik_forum.' alt="." title="'.$l['herkese_acik'].'"';
elseif ($alt_forum_satir['okuma_izni'] == 1) $forum_klasor .= $yonetici_forum.' alt="." title="'.$l['yoneticilere_acik'].'"';
elseif ($alt_forum_satir['okuma_izni'] == 2) $forum_klasor .= $yardimci_forum.' alt="." title="'.$l['yardimcilara_acik'].'"';
elseif ($alt_forum_satir['okuma_izni'] == 3) $forum_klasor .= $ozel_forum.' alt="." title="'.$l['ozel_forum'].'"';
elseif ($alt_forum_satir['okuma_izni'] == 4) $forum_klasor .= $uyeler_forum.' alt="." title="'.$l['uyelere_acik'].'"';
elseif ($alt_forum_satir['okuma_izni'] == 5) $forum_klasor .= $kapali_forum.' alt="." title="'.$l['kapali_forum'].'"';


if (empty($alt_forum_satir['resim']))
$forum_ozel_klasor = 'src="temalar/'.$temadizini.'/resimler/forum01.gif" alt="."';
else $forum_ozel_klasor = 'src="'.$alt_forum_satir['resim'].'" alt="."';




$forum_baglanti = linkver('forum.php?f='.$alt_forum_satir['id'], $alt_forum_satir['forum_baslik']);


//	BÖLÜM YARDIMCISI(LARI) VARSA SIRALANIYOR	//

if (isset($yardimcilar))
{
	if (preg_match('/,/', $yardimcilar)) $forum_yardimcilari = '<br><b><i>'.$l['bolum_yardimcilari'].':</i></b> '.$yardimcilar;
	else $forum_yardimcilari = '<br><b><i>'.$l['bolum_yardimcisi'].':</i></b> '.$yardimcilar;
}




//  EN YENİ BAŞLIĞIN BİLGİLERİ ÇEKİLİYOR  //

$vtsorgu = "SELECT id,son_mesaj_tarihi,mesaj_baslik,yazan,cevap_sayi,son_cevap,son_cevap_yazan FROM $tablo_mesajlar WHERE silinmis='0' AND hangi_forumdan='$alt_forum_satir[id]' ORDER BY son_mesaj_tarihi DESC LIMIT 1";
$vtsonuc3 = $vt->query($vtsorgu) or die ($vt->hata_ver());
$son_mesaj = $vt->fetch_assoc($vtsonuc3);


// forumda hiç konu yoksa
if (!isset($son_mesaj['id'])):
$sonmesaj_baslik = '<span class="baslik_kisalt">'.$l['forum_yazi_yok'].'</span>';
$sonmesaj_yazan = '';
$sonmesaj_tarih = '';
$sonmesaj_git = '';


// konu varsa
else:
// son konunun başlığı yazdırılıyor
$sonmesaj_baslik = '<a class="baslik_kisalt" title="'.$son_mesaj['mesaj_baslik'].'" href="'.linkver('konu.php?k='.$son_mesaj['id'], $son_mesaj['mesaj_baslik']).'"><b>'.$son_mesaj['mesaj_baslik'].'</b></a>';

// cevap yoksa
if ($son_mesaj['cevap_sayi'] == 0)
{
	$sonmesaj_yazan = '<b>'.$l['yazan'].': </b><a href="'.linkver('profil.php?kim='.$son_mesaj['yazan'],$son_mesaj['yazan']).'" title="Kullanıcı Profilini Görüntüle">'.$son_mesaj['yazan'].'</a>';
	$sonmesaj_tarih = '<b>'.$l['tarih'].': </b>'.zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $son_mesaj['son_mesaj_tarihi']);
	$sonmesaj_git = '<a href="'.linkver('konu.php?k='.$son_mesaj['id'], $son_mesaj['mesaj_baslik']).'" style="text-decoration: none">&nbsp;<img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="." title="'.$l['son_ileti'].'">&nbsp;</a>';
}


// cevap varsa
else
{
	$sonmesaj_yazan = '<b>'.$l['yazan'].': </b><a href="'.linkver('profil.php?kim='.$son_mesaj['son_cevap_yazan'],$son_mesaj['son_cevap_yazan']).'" title="Kullanıcı Profilini Görüntüle">'.$son_mesaj['son_cevap_yazan'].'</a>';
	$sonmesaj_tarih = '<b>'.$l['tarih'].': </b>'.zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $son_mesaj['son_mesaj_tarihi']);

	// başlık çok sayfalı ise son sayfaya git
	if ($son_mesaj['cevap_sayi'] > $ayarlar['ksyfkota'])
	{
		$sayfaya_git = (($son_mesaj['cevap_sayi']-1) / $ayarlar['ksyfkota']);
		settype($sayfaya_git,'integer');
		$sayfaya_git = ($sayfaya_git * $ayarlar['ksyfkota']);

		$sonmesaj_git = '<a href="'.linkver('konu.php?k='.$son_mesaj['id'].'&ks='.$sayfaya_git, $son_mesaj['mesaj_baslik'], '#c'.$son_mesaj['son_cevap']).'" style="text-decoration: none">&nbsp;';
	}

	else $sonmesaj_git = '<a href="'.linkver('konu.php?k='.$son_mesaj['id'], $son_mesaj['mesaj_baslik'], '#c'.$son_mesaj['son_cevap']).'" style="text-decoration: none">&nbsp;';

	$sonmesaj_git .= '<img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="." title="'.$l['son_ileti'].'">&nbsp;</a>';
}

endif;



//  BU FORUMUN VE TÜM FORUMLARIN KONU VE MESAJ SAYILARI HESAPLANIYOR    //

$toplam_baslik += $alt_forum_satir['konu_sayisi'];
$toplam_mesaj += ($alt_forum_satir['cevap_sayisi'] + $alt_forum_satir['konu_sayisi']);
$fmesaj_sayisi = ($alt_forum_satir['cevap_sayisi'] + $alt_forum_satir['konu_sayisi']);



// ALT FORUMU GÖRÜNTÜLEYENLERİN SAYILARI ALINIYOR  //

if ($ayarlar['bolum_kisi'] == 1)
{
	$vtsonuc = $vt->query("SELECT id FROM $tablo_kullanicilar WHERE sayfano LIKE '%3,$alt_forum_satir[id]' AND (son_hareket + $zaman_asimi) > $tarih  AND sayfano!='-1'") or die ($vt->hata_ver());
	$gor_usayi = $vt->num_rows($vtsonuc);

	$vtsonuc = $vt->query("SELECT sid FROM $tablo_oturumlar WHERE sayfano LIKE '%3,$alt_forum_satir[id]' AND (son_hareket + $zaman_asimi) > $tarih") or die ($vt->hata_ver());
	$gor_msayi = $vt->num_rows($vtsonuc);

	$gor_sayi = $gor_usayi + $gor_msayi;

	if ($gor_sayi > 0) $alt_forum_gor = '('.$gor_sayi.' kişi içeride)';
	else $alt_forum_gor = '';
}

else $alt_forum_gor = '';




//	veriler tema motoruna yollanıyor	//

$tekli3[] = array('{ALT_FORUM_KLASOR}' => $forum_klasor,
'{ALT_FORUM_OZEL_KLASOR}' => $forum_ozel_klasor,
'{ALT_FORUM_BAGLANTI}' => $forum_baglanti,
'{ALT_FORUM_BASLIK}' => $alt_forum_satir['forum_baslik'],
'{ALT_FORUM_GOR}' => $alt_forum_gor,
'{ALT_FORUM_BILGI}' => $alt_forum_satir['forum_bilgi'],
'{ALT_FORUM_YARDIMCILARI}' => $forum_yardimcilari,
'{ALT_SONMESAJ_BASLIK}' => $sonmesaj_baslik,
'{ALT_SONMESAJ_YAZAN}' => $sonmesaj_yazan,
'{ALT_SONMESAJ_TARIH}' => $sonmesaj_tarih,
'{ALT_SONMESAJ_GIT}' => $sonmesaj_git,
'{ALT_FORUM_BASLIK_SAYISI}' => NumaraBicim($alt_forum_satir['konu_sayisi']),
'{ALT_FORUM_MESAJ_SAYISI}' => NumaraBicim($fmesaj_sayisi));

$forum_yardimcilari = '';


endwhile;


	//	ALT FORUM KODLARI - SONU	//





//	SADECE İLK SAYFADA ÜST KONU GÖSTER	//

if ($_GET['fs'] == 0):


//	ÜST KONU BİLGİLERİ ÇEKİLİYOR	//

$vtsorgu = "SELECT id,mesaj_baslik,cevap_sayi,yazan,goruntuleme,kilitli,son_mesaj_tarihi,son_cevap,son_cevap_yazan FROM $tablo_mesajlar WHERE silinmis='0' AND hangi_forumdan='$_GET[f]' AND ust_konu='1' ORDER BY son_mesaj_tarihi DESC";
$ustkonu = $vt->query($vtsorgu) or die ($vt->hata_ver());


//	ÜST KONU SAYISI ALINIYOR		//

$vtsonuc9 = $vt->query("SELECT id FROM $tablo_mesajlar WHERE silinmis='0' AND hangi_forumdan='$_GET[f]' AND ust_konu='1'") or die ($vt->hata_ver());
$ustkonu_sayi = $vt->num_rows($vtsonuc9);

else:
	$ustkonu_sayi = 0;
	$ustkonu = 0;

endif;



//	BAŞLIK BİLGİLERİ ÇEKİLİYOR	//

$vtsorgu = "SELECT id,mesaj_baslik,cevap_sayi,yazan,goruntuleme,kilitli,son_mesaj_tarihi,son_cevap,son_cevap_yazan
FROM $tablo_mesajlar WHERE silinmis='0' AND hangi_forumdan='$_GET[f]' AND ust_konu='0'
ORDER BY son_mesaj_tarihi DESC LIMIT $_GET[fs],$ayarlar[fsyfkota]";
$baslik_sirala = $vt->query($vtsorgu) or die ($vt->hata_ver());


//	FORUM BAŞLIKLARININ SAYISI ALINIYOR		//

$vtsonuc9 = $vt->query("SELECT id FROM $tablo_mesajlar WHERE silinmis='0' AND hangi_forumdan='$_GET[f]' AND ust_konu='0'") or die ($vt->hata_ver());
$satir_sayi = $vt->num_rows($vtsonuc9);


// OLUŞTURULACAK SAYFA SAYISI BAĞLANTISI //

$toplam_sayfa = ($satir_sayi / $ayarlar['fsyfkota']);
settype($toplam_sayfa,'integer');

if ( ($satir_sayi % $ayarlar['fsyfkota']) != 0 )
$toplam_sayfa++;


if (isset($baslik_sirala)):






        //      SAYFA BAĞLANTILARI OLUŞTURULUYOR BAŞI       //


$sayfalama_cikis ='';

if ($satir_sayi > $ayarlar['fsyfkota']):
$sayfalama_cikis = '<table cellspacing="1" cellpadding="4" border="0" align="right" class="tablo_border">
	<tbody>
	<tr>
	<td class="forum_baslik">
<span class="mobil-gizle">&nbsp;Toplam '.$toplam_sayfa.' Sayfa:&nbsp;</span>
<span class="genis-gizle masa-gizle tablet-gizle">&nbsp;Sayfa:&nbsp;</span>
	</td>';


if ($_GET['fs'] != 0)
{
	$sayfalama_cikis .= '<td bgcolor="#ffffff" class="liste-veri" title="ilk sayfaya git">
	&nbsp;<a href="'.linkver('forum.php?f='.$_GET['f'], $forum_satir['forum_baslik']).'">&laquo;ilk</a>&nbsp;</td>
		
	<td bgcolor="#ffffff" class="liste-veri" title="önceki sayfaya git">
	&nbsp;<a href="'.linkver('forum.php?f='.$_GET['f'].'&fs='.($_GET['fs'] - $ayarlar['fsyfkota']), $forum_satir['forum_baslik']).'">&lt;</a>&nbsp;</td>';
}

for ($sayi=0,$sayfa_sinir=$_GET['fs']; $sayi < $toplam_sayfa; $sayi++)
{
	if ($sayi < (($_GET['fs'] / $ayarlar['fsyfkota']) - 3));
	else
	{
		$sayfa_sinir++;
		if ($sayfa_sinir >= ($_GET['fs'] + 8))  break;
		if (($sayi == 0) AND ($_GET['fs'] == 0))
		{
			$sayfalama_cikis .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">
			&nbsp;<b>[1]</b>&nbsp;</td>';
		}

		elseif (($sayi + 1) == (($_GET['fs'] / $ayarlar['fsyfkota']) + 1))
		{
			$sayfalama_cikis .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">
			&nbsp;<b>['.($sayi + 1).']</b>&nbsp;</td>';
		}

		else
		{
			$sayfalama_cikis .= '<td bgcolor="#ffffff" class="liste-veri" title="'.($sayi + 1).' numaralı sayfaya git">

			&nbsp;<a href="'.linkver('forum.php?f='.$_GET['f'].'&fs='.($sayi * $ayarlar['fsyfkota']), $forum_satir['forum_baslik']).'">'.($sayi + 1).'</a>&nbsp;</td>';
		}
	}
}
if ($_GET['fs'] < ($satir_sayi - $ayarlar['fsyfkota']))
{
	$sayfalama_cikis .= '<td bgcolor="#ffffff" class="liste-veri" title="sonraki sayfaya git">
	&nbsp;<a href="'.linkver('forum.php?f='.$_GET['f'].'&fs='.($_GET['fs'] + $ayarlar['fsyfkota']), $forum_satir['forum_baslik']).'">&gt;</a>&nbsp;</td>

	<td bgcolor="#ffffff" class="liste-veri" title="son sayfaya git">
	&nbsp;<a href="'.linkver('forum.php?f='.$_GET['f'].'&fs='.(($toplam_sayfa - 1) * $ayarlar['fsyfkota']), $forum_satir['forum_baslik']).'">son&raquo;</a>&nbsp;</td>';
}
$sayfalama_cikis .= '</tr></tbody></table>';
endif;




        //      SAYFA BAĞLANTILARI OLUŞTURULUYOR SONU       //





//	YENİ BAŞLIK	//

// forum konu açmaya açıksa
if (($forum_satir['konu_acma_izni'] != 1) AND ($forum_satir['konu_acma_izni'] != 5))
	$yeni_baslik = '<a href="mesaj_yaz.php?fno='.$_GET['f'].'&amp;kip=yeni" title="Yeni Konu Açmak için Tıklayın">'.$yenibaslik_rengi.'</a> &nbsp;&nbsp; ';

// forum sadece yöneticilerin konu açmasına açıksa
else
{
	if ( (!isset($kullanici_kim['yetki'])) OR ($kullanici_kim['yetki'] != 1) )
		$yeni_baslik = '';

	else $yeni_baslik = '<a href="mesaj_yaz.php?fno='.$_GET['f'].'&amp;kip=yeni" title="Yeni Konu Açmak için Tıklayın">'.$yenibaslik_rengi.'</a> &nbsp;&nbsp; ';
}




//  FORUMDA BAŞLIK YOKSA AŞAĞIDAKİNİ YAZ, VARSA WHILE DÖNGÜSÜNE GİR //


if ( ($ustkonu_sayi == 0) AND ($satir_sayi == 0) ):

	$kosul1_varmi = true;
	$temakosul1 = array('{KONU_YOK_UYARI}' => $l['forum_yazi_yok']);
	$forum_konulari = '';

else:
	$kosul1_varmi = false;
	$temakosul1 = '';
	$forum_konulari = '';
endif;



//  ÜST KONU VARSA WHILE DÖNGÜSÜNE GİR //


if ($ustkonu_sayi > 0):

$satir_renklendir = 1;

while ($ustkonu_satir = $vt->fetch_assoc($ustkonu)):


if (($satir_renklendir % 2)) $satir_renk = 'satir_renk1';
else $satir_renk = 'satir_renk2';


if ($ustkonu_satir['kilitli'] == 1)
	$konu_klasor = '<img '.$kilitli_konu.' alt="." title="'.$l['kilitli_ust_konu'].'">';

else
	$konu_klasor =  '<img '.$ust_konu.' alt="." title="'.$l['ust_konu'].'">';

$konu_baglanti = '<a href="'.linkver('konu.php?k='.$ustkonu_satir['id'], $ustkonu_satir['mesaj_baslik']).'">';



$forum_konulari = '';

//  OKUNMAMIŞ MESAJLARI KALIN YAZDIR  //

if ( (isset($kullanici_kim['son_giris'])) AND ($ustkonu_satir['son_mesaj_tarihi'] > $kullanici_kim['son_giris']) )
{
    if (isset($_COOKIE['kfk_okundu']))
    {
        $cerez_dizi = explode('_', $_COOKIE['kfk_okundu']);

        foreach ($cerez_dizi as $cerez_parcala)
        {
            $okunan_kno = substr($cerez_parcala, 11);
            $okunan_dizi[$okunan_kno] = substr($cerez_parcala, 0, 10);
        }

        if ( (empty($okunan_dizi[$ustkonu_satir['id']])) OR ($ustkonu_satir['son_mesaj_tarihi'] > $okunan_dizi[$ustkonu_satir['id']]) )
            $forum_konulari .= '<b>'.$ustkonu_satir['mesaj_baslik'].'</b></a>';

        else $forum_konulari .= $ustkonu_satir['mesaj_baslik'].'</a>';
    }

    else $forum_konulari .= '<b>'.$ustkonu_satir['mesaj_baslik'].'</b></a>';
}

else $forum_konulari .= $ustkonu_satir['mesaj_baslik'].'</a>';





//  ÇOK SAYFALI BAŞLIK İSE, SAYFA BAĞLANTILARI OLUŞTURULUYOR  //

if ($ustkonu_satir['cevap_sayi'] > $ayarlar['ksyfkota'])
{
    $konu_sayfa = (($ustkonu_satir['cevap_sayi']-1) / $ayarlar['ksyfkota']);
    settype($konu_sayfa,'integer');

    $forum_konulari .= '<br>(Sayfa: ';

    for ($i=0; $i<($konu_sayfa+1); $i++)
    {
        if ($i > 8)
        {
            $forum_konulari .= ' ... <a href="'.linkver('konu.php?k='.$ustkonu_satir['id'].'&ks='.($ayarlar['ksyfkota']*$konu_sayfa), $ustkonu_satir['mesaj_baslik']).'">Son&raquo;</a>';
            break;
        }
        else $forum_konulari .= ' <a href="'.linkver('konu.php?k='.$ustkonu_satir['id'].'&ks='.($ayarlar['ksyfkota']*$i), $ustkonu_satir['mesaj_baslik']).'">'.($i+1).'</a>';
    }

    $forum_konulari .= ')';
}

$yazan_baglanti = '<a href="'.linkver('profil.php?kim='.$ustkonu_satir['yazan'],$ustkonu_satir['yazan']).'">';



//      CEVAP YOKSA     //

if ($ustkonu_satir['cevap_sayi'] == 0):

$sonmesaj_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $ustkonu_satir['son_mesaj_tarihi']);

$cevap_yazan_baglanti = '<a href="'.linkver('profil.php?kim='.$ustkonu_satir['yazan'],$ustkonu_satir['yazan']).'">';

$sonmesaj_baglanti = '<a href="'.linkver('konu.php?k='.$ustkonu_satir['id'], $ustkonu_satir['mesaj_baslik']).'" style="text-decoration: none">&nbsp;<img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="." title="'.$l['son_ileti'].'"></a>';

$cevap_yazan = $ustkonu_satir['yazan'];


//      CEVAP VARSA     //

else:

$sonmesaj_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $ustkonu_satir['son_mesaj_tarihi']);

$cevap_yazan_baglanti = '<a href="'.linkver('profil.php?kim='.$ustkonu_satir['son_cevap_yazan'],$ustkonu_satir['son_cevap_yazan']).'">';

$cevap_yazan = $ustkonu_satir['son_cevap_yazan'];


//  BAŞLIK ÇOK SAYFALI İSE SON SAYFAYA GİT  //

if ($ustkonu_satir['cevap_sayi'] > $ayarlar['ksyfkota'])
{
    $sayfaya_git = (($ustkonu_satir['cevap_sayi']-1) / $ayarlar['ksyfkota']);
    settype($sayfaya_git,'integer');
    $sayfaya_git = ($sayfaya_git * $ayarlar['ksyfkota']);

    $sonmesaj_baglanti = '<a href="'.linkver('konu.php?k='.$ustkonu_satir['id'].'&ks='.$sayfaya_git, $ustkonu_satir['mesaj_baslik'], '#c'.$ustkonu_satir['son_cevap']).'" style="text-decoration: none">';
}

else $sonmesaj_baglanti = '<a href="'.linkver('konu.php?k='.$ustkonu_satir['id'], $ustkonu_satir['mesaj_baslik'], '#c'.$ustkonu_satir['son_cevap']).'" style="text-decoration: none">';

$sonmesaj_baglanti .= '&nbsp;<img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="." title="'.$l['son_ileti'].'"></a>';


endif;


//	veriler tema motoruna yollanıyor	//

$tekli1[] = array('{SATIR_RENK}' => $satir_renk,
'{KONU_KLASOR}' => $konu_klasor,
'{KONU_BAGLANTI}' => $konu_baglanti,
'{KONU_SAYFALARI}' => $forum_konulari,
'{CEVAP_SAYISI}' => NumaraBicim($ustkonu_satir['cevap_sayi']),
'{YAZAN_BAGLANTI}' => $yazan_baglanti,
'{KONUYU_ACAN}' => $ustkonu_satir['yazan'],
'{GOSTERIM}' => NumaraBicim($ustkonu_satir['goruntuleme']),
'{SONMESAJ_TARIH}' => $sonmesaj_tarih,
'{CEVAP_YAZAN_BAGLANTI}' => $cevap_yazan_baglanti,
'{CEVAP_YAZAN}' => $cevap_yazan,
'{SONMESAJ_BAGLANTI}' => $sonmesaj_baglanti);


$satir_renklendir++;
endwhile;
endif;


		//		ÜST KONULAR SIRALANIYOR BİTİŞ	//



		//		BAŞLIKLAR SIRALANIYOR BAŞLANGIÇ		//


if ($satir_sayi > 0):
$satir_renklendir = 1;


while ($satir = $vt->fetch_assoc($baslik_sirala)):

if (($satir_renklendir % 2)) $satir_renk = 'satir_renk1';
else $satir_renk = 'satir_renk2';


if ($satir['kilitli'] == 1) $konu_klasor = '<img '.$kilitli_konu.' alt="." title="'.$l['kilitli_konu'].'">';

else $konu_klasor = '<img '.$acik_konu.' alt="." title="'.$l['uyelere_acik'].'">';


$konu_baglanti = '<a href="'.linkver('konu.php?k='.$satir['id'].$fs, $satir['mesaj_baslik']).'">';



$forum_konulari = '';


//  OKUNMAMIŞ MESAJLARI KALIN YAZDIR  //

if ( (isset($kullanici_kim['son_giris'])) AND ($satir['son_mesaj_tarihi'] > $kullanici_kim['son_giris']) )
{
    if (isset($_COOKIE['kfk_okundu']))
    {
        $cerez_dizi = explode('_', $_COOKIE['kfk_okundu']);

        foreach ($cerez_dizi as $cerez_parcala)
        {
            $okunan_kno = substr($cerez_parcala, 11);
            $okunan_dizi[$okunan_kno] = substr($cerez_parcala, 0, 10);
        }

        if ( (empty($okunan_dizi[$satir['id']])) OR ($satir['son_mesaj_tarihi'] > $okunan_dizi[$satir['id']]) )
            $forum_konulari .= '<b>'.$satir['mesaj_baslik'].'</b></a>';

        else $forum_konulari .= $satir['mesaj_baslik'].'</a>';
    }

    else $forum_konulari .= '<b>'.$satir['mesaj_baslik'].'</b></a>';
}

else $forum_konulari .= $satir['mesaj_baslik'].'</a>';





//  ÇOK SAYFALI BAŞLIK İSE, SAYFA BAĞLANTILARI OLUŞTURULUYOR  //

if ($satir['cevap_sayi'] > $ayarlar['ksyfkota'])
{
    $konu_sayfa = (($satir['cevap_sayi']-1) / $ayarlar['ksyfkota']);
    settype($konu_sayfa,'integer');

    $forum_konulari .= '<br>(Sayfa: ';

    for ($i=0; $i<($konu_sayfa+1); $i++)
    {
        if ($i > 8)
        {
            $forum_konulari .= ' ... <a href="'.linkver('konu.php?k='.$satir['id'].$fs.'&ks='.($ayarlar['ksyfkota']*$konu_sayfa),$satir['mesaj_baslik']).'">Son&raquo;</a>';
            break;
        }
        else $forum_konulari .= ' <a href="'.linkver('konu.php?k='.$satir['id'].$fs.'&ks='.($ayarlar['ksyfkota']*$i),$satir['mesaj_baslik']).'">'.($i+1).'</a>';
    }

    $forum_konulari .= ')';
}

$yazan_baglanti = '<a href="'.linkver('profil.php?kim='.$satir['yazan'],$satir['yazan']).'">';


//      CEVAP YOKSA     //

if ($satir['cevap_sayi'] == 0):

$sonmesaj_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['son_mesaj_tarihi']);

$cevap_yazan_baglanti = '<a href="'.linkver('profil.php?kim='.$satir['yazan'],$satir['yazan']).'">';

$cevap_yazan = $satir['yazan'];

$sonmesaj_baglanti = '<a href="'.linkver('konu.php?k='.$satir['id'].$fs, $satir['mesaj_baslik']).'" style="text-decoration: none">&nbsp;<img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="." title="'.$l['son_ileti'].'"></a>';



//      CEVAP VARSA     //

else:

$sonmesaj_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['son_mesaj_tarihi']);
$cevap_yazan_baglanti = '<a href="'.linkver('profil.php?kim='.$satir['son_cevap_yazan'],$satir['son_cevap_yazan']).'">';

$cevap_yazan = $satir['son_cevap_yazan'];


//  BAŞLIK ÇOK SAYFALI İSE SON SAYFAYA GİT  //

if ($satir['cevap_sayi'] > $ayarlar['ksyfkota'])
{
    $sayfaya_git = (($satir['cevap_sayi']-1) / $ayarlar['ksyfkota']);
    settype($sayfaya_git,'integer');
    $sayfaya_git = ($sayfaya_git * $ayarlar['ksyfkota']);

    $sonmesaj_baglanti = '<a href="'.linkver('konu.php?k='.$satir['id'].$fs.'&ks='.$sayfaya_git, $satir['mesaj_baslik'], '#c'.$satir['son_cevap']).'" style="text-decoration: none">';
}

else $sonmesaj_baglanti = '<a href="'.linkver('konu.php?k='.$satir['id'].$fs, $satir['mesaj_baslik'], '#c'.$satir['son_cevap']).'" style="text-decoration: none">';

$sonmesaj_baglanti .= '&nbsp;<img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="." title="'.$l['son_ileti'].'"></a>';


endif;


//	veriler tema motoruna yollanıyor	//

$tekli2[] = array('{SATIR_RENK}' => $satir_renk,
'{KONU_KLASOR}' => $konu_klasor,
'{KONU_BAGLANTI}' => $konu_baglanti,
'{KONU_SAYFALARI}' => $forum_konulari,
'{CEVAP_SAYISI}' => NumaraBicim($satir['cevap_sayi']),
'{YAZAN_BAGLANTI}' => $yazan_baglanti,
'{KONUYU_ACAN}' => $satir['yazan'],
'{GOSTERIM}' => NumaraBicim($satir['goruntuleme']),
'{SONMESAJ_TARIH}' => $sonmesaj_tarih,
'{CEVAP_YAZAN_BAGLANTI}' => $cevap_yazan_baglanti,
'{CEVAP_YAZAN}' => $cevap_yazan,
'{SONMESAJ_BAGLANTI}' => $sonmesaj_baglanti);


$satir_renklendir++;
endwhile;
endif;


		//      BAŞLIKLAR SIRALANIYOR BİTİŞ      //




//  BÖLÜMÜ GÖRÜNTÜLEYENLER  //

if ($ayarlar['konu_kisi'] == 1)
{
	$gor_usayi = 0;
	$gor_usayi2 = 0;
	$gor_uyeler = '';

	$vtsonuc = $vt->query("SELECT sid FROM $tablo_oturumlar WHERE (sayfano LIKE '%3,$forum_satir[id]') AND (son_hareket + $zaman_asimi) > $tarih") or die ($vt->hata_ver());
	$gor_msayi = $vt->num_rows($vtsonuc);


	$vtsonuc = $vt->query("SELECT id,kullanici_adi,gizli FROM $tablo_kullanicilar WHERE (sayfano LIKE '%3,$forum_satir[id]') AND (son_hareket + $zaman_asimi) > $tarih AND sayfano!='-1'") or die ($vt->hata_ver());

	while ($gor_uye = $vt->fetch_assoc($vtsonuc))
	{
		if ($gor_uye['gizli'] == 0)
		{
			$gor_uyeler .= '<a href="'.linkver('profil.php?u='.$gor_uye['id'].'&kim='.$gor_uye['kullanici_adi'],$gor_uye['kullanici_adi']).'">'.$gor_uye['kullanici_adi'].'</a>, ';
			$gor_usayi++;
		}

		else
		{
			if ((isset($kullanici_kim['yetki'])) AND ($kullanici_kim['yetki'] == 1))
				$gor_uyeler .= '<a href="'.linkver('profil.php?u='.$gor_uye['id'].'&kim='.$gor_uye['kullanici_adi'],$gor_uye['kullanici_adi']).'"><i>'.$gor_uye['kullanici_adi'].'</i></a>, ';
			$gor_usayi2++;
		}
	}

	if ($gor_uyeler == '') $gor_uyeler = 'Bu konuyu görüntüleyen üye yok.';

	$gor_kisi = 'Bu bölümü '.($gor_msayi + $gor_usayi + $gor_usayi2).' kişi görüntülüyor:&nbsp; '.$gor_msayi.' Misafir, '.($gor_usayi + $gor_usayi2).' Üye';
	if ($gor_usayi2 != 0) $gor_kisi .= ' ('.$gor_usayi2.' tanesi gizli)';
}

else {$gor_kisi = ''; $gor_uyeler = '';}




// link ağacı
$forum_anasayfa = '<span><a href="'.$phpkf_dosyalar['forum'].'">'.$l['forum'].' '.$l['anasayfa'].'</a></span>';

if ($forum_satir['alt_forum'] != '0')
{
	$alt_forum_baslik = '<span>'.$forum_satir['forum_baslik'].'</span>';

	$vtsorgu = "SELECT id,forum_baslik FROM $tablo_forumlar WHERE id='$forum_satir[alt_forum]' LIMIT 1";
	$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$forum_satir = $vt->fetch_assoc($vtsonuc2);

	$ust_forum_baslik = '<span><a href="'.linkver('forum.php?f='.$forum_satir['id'], $forum_satir['forum_baslik']).'">'.$forum_satir['forum_baslik'].'</a></span>';
}

else
{
	$ust_forum_baslik = '<span>'.$forum_satir['forum_baslik'].'</span>';
	$alt_forum_baslik = '';
}

$forumlar_arasi_gecis = '';



//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/forum.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


$dongusuz = array('{FORUM_ANASAYFA}' => $forum_anasayfa,
'{FORUM_BASLIK}' => $ust_forum_baslik,
'{ALT_FORUM_BASLIK}' => $alt_forum_baslik,
'{SAYFALAMA}' => $sayfalama_cikis,
'{YENI_BASLIK}' => $yeni_baslik,
'{FORUM_KONULARI}' => $forum_konulari,
'{GOR_KISI}' => $gor_kisi,
'{GOR_UYELER}' => $gor_uyeler,
'{ACIK_FORUM}' => $acik_forum,
'{OZEL_FORUM}' => $ozel_forum,
'{YONETICI_FORUM}' => $yonetici_forum,
'{FORUMLAR_ARASI_GECIS}' => $forumlar_arasi_gecis);



if (isset($tekli3))
{
	$ornek1->kosul('5', array(''=>''), true);
	$ornek1->tekli_dongu('3',$tekli3);
}

else $ornek1->kosul('5', array(''=>''), false);



// forumda konu yoksa uyarı kısmını yazdır

if ($kosul1_varmi == false)
{
	if (isset($tekli1))
	{
		$ornek1->kosul('1', array(''=>''), false);
		$ornek1->kosul('2', array(''=>''), true);
		$ornek1->tekli_dongu('1',$tekli1);
	}

	else
	{
		$ornek1->kosul('1', array(''=>''), false);
		$ornek1->kosul('2', array(''=>''), false);
	}


	if (isset($tekli2))
	{
		$ornek1->kosul('1', array(''=>''), false);
		$ornek1->kosul('3', array(''=>''), true);
		$ornek1->tekli_dongu('2',$tekli2);
	}

	else
	{
		$ornek1->kosul('1', array(''=>''), false);
		$ornek1->kosul('3', array(''=>''), false);
	}


	if ( (isset($tekli1)) AND (isset($tekli2)) )
		$ornek1->kosul('4', array(''=>''), true);

	else $ornek1->kosul('4', array(''=>''), false);
}


else
{
	$ornek1->kosul('1', $temakosul1, true);
	$ornek1->kosul('2', array(''=>''), false);
	$ornek1->kosul('3', array(''=>''), false);
	$ornek1->kosul('4', array(''=>''), false);
}

if ($ayarlar['konu_kisi'] != 1) $ornek1->kosul('6', array(''=>''), false);

$ornek1->dongusuz($dongusuz);

endif;

eval(TEMA_UYGULA);

?>