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


// ayar.php yok, kurulum yapılmamış, güncelleme yapılmamış, kurulum sayfasına yönlendir
$phpkf_ayarlar_kip = "WHERE kip='1'";
if ( (!@include_once('phpkf-ayar.php')) OR ($ayarlar['surum'] != '3.00') )
{
	header('Location: phpkf-kurulum/index.php');
	exit();
}
else include_once('ayar.php');



if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';
$zaman_asimi = $ayarlar['uye_cevrimici_sure'];
$tarih = time();



//  FORUM TEMASINI DEĞİŞTİR //

if ((isset($_GET['renk'])) AND ($_GET['renk'] != ''))
{
	switch($_GET['renk'])
	{
		case 'yesil';
		setcookie('forum_rengi', 'yesil', $tarih+$ayarlar['k_cerez_zaman'], $cerez_dizin, $cerez_alanadi);
		header('Location: '.$phpkf_dosyalar['forum']);
		exit();
		break;

		case 'kirmizi';
		setcookie('forum_rengi', 'kirmizi', $tarih+$ayarlar['k_cerez_zaman'], $cerez_dizin, $cerez_alanadi);
		header('Location: '.$phpkf_dosyalar['forum']);
		exit();
		break;

		case 'turuncu';
		setcookie('forum_rengi', 'turuncu', $tarih+$ayarlar['k_cerez_zaman'], $cerez_dizin, $cerez_alanadi);
		header('Location: '.$phpkf_dosyalar['forum']);
		exit();
		break;

		case 'mavi';
		setcookie('forum_rengi', 'mavi', $tarih+$ayarlar['k_cerez_zaman'], $cerez_dizin, $cerez_alanadi);
		header('Location: '.$phpkf_dosyalar['forum']);
		exit();
		break;

		default:
		setcookie('forum_rengi', 'siyah', $tarih+$ayarlar['k_cerez_zaman'], $cerez_dizin, $cerez_alanadi);
		header('Location: '.$phpkf_dosyalar['forum']);
		exit();
	}
}




// FORUMLAR SIRALANIYOR - BAŞI  //


// Forum kurulu değilse kurulum sayfasına yönlendir
$vtsorgu = "SHOW TABLES LIKE '$tablo_dallar'";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
if (!$vt->num_rows($vtsonuc))
{
	header('Location: phpkf-kurulum/index.php');
	exit();
}


// Forum dallları veritabanından çekiliyor
$vtsorgu = "SELECT * FROM $tablo_dallar ORDER BY sira";
$vtsonuc3 = $vt->query($vtsorgu) or die ($vt->hata_ver());


$sayfano = 1;
$sayfa_adi = $l['anasayfa'];
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');

include_once('phpkf-bilesenler/seo.php');



$guncel_saat = zonedate2($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $tarih);

if ($ayarlar['saat_dilimi'] >= 0) $guncel_saat .= '&nbsp; (UTC +'.$ayarlar['saat_dilimi'].')';
else $guncel_saat .= '&nbsp; (UTC '.$ayarlar['saat_dilimi'].')';



//  SON GELİŞ TARİHİ ÇEREZDEN ALINIYOR  //

if (isset($kullanici_kim['son_giris']))
$guncel_saat .= '<br><b>'.$l['son_ziyaretiniz'].':</b>&nbsp; '
.zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $kullanici_kim['son_giris']).'
<br><div style="width:100%;height:6px"></div>
<a href="ymesaj.php">'.$l['yeni_iletiler'].'</a> &nbsp;-&nbsp;
<a href="ymesaj.php?kip=bugun">'.$l['bugun_yazilanlar'].'</a> &nbsp;-&nbsp;
<a href="ymesaj.php?kip=takip">'.$l['takip_edilenler'].'</a>
<br><div style="width:100%;height:3px"></div>';



$guncel_ek = '';
$toplam_baslik = 0;
$toplam_mesaj = 0;
$dongu1 = 0;


//  FORUM DALLARI SIRALANIYOR   //

while ($dallar_satir = $vt->fetch_assoc($vtsonuc3)):

//	veriler tema motoruna yollanıyor	//
$tema_dis[] = array('{FORUM_DALI_BASLIK}' => $dallar_satir['ana_forum_baslik']);

// üst forumların bilgileri çekiliyor
$vtsorgu = "SELECT id,forum_baslik,forum_bilgi,okuma_izni,resim,konu_sayisi,cevap_sayisi,gizle
		FROM $tablo_forumlar WHERE alt_forum='0' AND dal_no='$dallar_satir[id]' ORDER BY sira";
$vtsonuc4 = $vt->query($vtsorgu) or die ($vt->hata_ver());


// FORUM DALINA AİT FORUM YOKSA //

if (!$vt->num_rows($vtsonuc4))
{
	$tema_ic[$dongu1][] = array('{FORUM_KLASOR}' => $acik_forum.' alt="." title="'.$l['herkese_acik'].'"',
'{FORUM_OZEL_KLASOR}' =>  'src="temalar/'.$temadizini.'/resimler/forum01.gif" width="0" height="0" alt="."',
'{FORUM_BAGLANTI}' => '#',
'{FORUM_BASLIK}' => '</b></a><br><div align="center"><b>'.$l['forum_yok'].'</b></div><a href="#"><b>',
'{FORUM_BILGI}' => '',
'{FORUM_YARDIMCILARI}' => '',
'{FORUM_GOR}' => '',
'{SONMESAJ_BASLIK}' => '',
'{SONMESAJ_YAZAN}' => '',
'{SONMESAJ_TARIH}' => '',
'{SONMESAJ_GIT}' => '',
'{FORUM_BASLIK_SAYISI}' => '',
'{FORUM_MESAJ_SAYISI}' => '',
'{ALT_FORUMLAR}' => '');
}

$forum_yardimcilari = '';




//	ÜST FORUMLAR SIRALANIYOR    //

while ($forum_satir = $vt->fetch_assoc($vtsonuc4)):

// alt forumların bilgileri çekiliyor
$vtsorgu = "SELECT id,forum_baslik,konu_sayisi,cevap_sayisi,okuma_izni,gizle FROM $tablo_forumlar WHERE alt_forum='$forum_satir[id]' ORDER BY sira";
$vtsonuc5 = $vt->query($vtsorgu) or die ($vt->hata_ver());

// forum başlıkları diziye aktarılıyor
$tumforum_satir[$forum_satir['id']] = $forum_satir['forum_baslik'];


$iceride_ek = '';
$alt_forumlar = '';
$alt_forum_sorgu = '';
$fkonu_sayisi = 0;
$fmesaj_sayisi = 0;


// alt forum varsa
if ($vt->num_rows($vtsonuc5))
{
	$alt_forumlar = '<br><br><font class="alt_forum">'.$l['alt_forumlar'].':</font>&nbsp; <br>
	<table cellspacing="0" cellpadding="4" border="0" align="left">
	<tr>';

	$alt_forum_sayi = 0;


	//	ALT FORUMLAR SIRALANIYOR    //

	while ($alt_forum_satir = $vt->fetch_assoc($vtsonuc5))
	{
		$iceride_ek .= "OR sayfano LIKE '%3,$alt_forum_satir[id]'";

		// Yetkiye göre alt forum (ve konu) başlığı gizleme

		if (($alt_forum_satir['gizle'] == 1) AND ($alt_forum_satir['okuma_izni'] != 0))
		{
			if (isset($kullanici_kim['id']))
			{
				if (($alt_forum_satir['okuma_izni'] == 5) AND ($kullanici_kim['yetki'] != 1))
				{
					$guncel_ek .= " AND hangi_forumdan!='$alt_forum_satir[id]' ";
					continue;
				}

				elseif (($alt_forum_satir['okuma_izni'] == 1) AND ($kullanici_kim['yetki'] != 1))
				{
					$guncel_ek .= " AND hangi_forumdan!='$alt_forum_satir[id]' ";
					continue;
				}

				elseif (($alt_forum_satir['okuma_izni'] == 2) AND ($kullanici_kim['yetki'] == 0))
				{
					$guncel_ek .= " AND hangi_forumdan!='$alt_forum_satir[id]' ";
					continue;
				}

				elseif (($alt_forum_satir['okuma_izni'] == 3) AND ($kullanici_kim['yetki'] != 1) AND ($kullanici_kim['yetki'] != 2))
				{
					if ($kullanici_kim['yetki'] >= 0)
					{
						if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$alt_forum_satir[id]' AND okuma='1' OR";
						else $grupek = "grup='0' AND";

						$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$alt_forum_satir[id]' AND okuma='1'";
						$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());
						if (!$vt->num_rows($kul_izin))
						{
							$guncel_ek .= " AND hangi_forumdan!='$alt_forum_satir[id]' ";
							continue;
						}
					}
					else
					{
						$guncel_ek .= " AND hangi_forumdan!='$alt_forum_satir[id]' ";
						continue;
					}
				}
			}

			else
			{
				$guncel_ek .= " AND hangi_forumdan!='$alt_forum_satir[id]' ";
				continue;
			}
		}



		// alt forumların diziliş biçimi, 2 satır dizilmesi için % 2 girin
		if ( ($alt_forum_sayi != 0) AND ($alt_forum_sayi % $ayarlar['altforum_sira']) == 0)
			$alt_forumlar .= '</tr><tr>';

		$alt_forumlar .= '<td align="left" class="liste-veri"><img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="'.$l['alt_forum'].'"> <a href="'.linkver('forum.php?f='.$alt_forum_satir['id'], $alt_forum_satir['forum_baslik']).'">'.$alt_forum_satir['forum_baslik'].'</a></td>';

		$alt_forum_sayi++;

		$toplam_baslik += $alt_forum_satir['konu_sayisi'];
		$toplam_mesaj += ($alt_forum_satir['cevap_sayisi'] + $alt_forum_satir['konu_sayisi']);
		$fkonu_sayisi += $alt_forum_satir['konu_sayisi'];
		$fmesaj_sayisi += ($alt_forum_satir['cevap_sayisi'] + $alt_forum_satir['konu_sayisi']);

		$alt_forum_sorgu .= "OR silinmis='0' AND hangi_forumdan='$alt_forum_satir[id]' ";

		$tumforum_satir[$alt_forum_satir['id']] = $alt_forum_satir['forum_baslik'];
	}

	$alt_forumlar .= '
	</tr>
	</table>';
}


// Yetkiye göre üst forum (ve konu) başlığı gizleme

if (($forum_satir['gizle'] == 1) AND ($forum_satir['okuma_izni'] != 0))
{
	if (isset($kullanici_kim['id']))
	{
		if (($forum_satir['okuma_izni'] == 5) AND ($kullanici_kim['yetki'] != 1))
		{
			$guncel_ek .= " AND hangi_forumdan!='$forum_satir[id]' ";
			continue;
		}

		elseif (($forum_satir['okuma_izni'] == 1) AND ($kullanici_kim['yetki'] != 1))
		{
			$guncel_ek .= " AND hangi_forumdan!='$forum_satir[id]' ";
			continue;
		}

		elseif (($forum_satir['okuma_izni'] == 2) AND ($kullanici_kim['yetki'] == 0))
		{
			$guncel_ek .= " AND hangi_forumdan!='$forum_satir[id]' ";
			continue;
		}

		elseif (($forum_satir['okuma_izni'] == 3) AND ($kullanici_kim['yetki'] != 1) AND ($kullanici_kim['yetki'] != 2))
		{
			if ($kullanici_kim['yetki'] >= 0)
			{
				if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$forum_satir[id]' AND okuma='1' OR";
				else $grupek = "grup='0' AND";

				$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$forum_satir[id]' AND okuma='1'";
				$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());
				if (!$vt->num_rows($kul_izin))
				{
					$guncel_ek .= " AND hangi_forumdan!='$forum_satir[id]' ";
					continue;
				}
			}
			else
			{
				$guncel_ek .= " AND hangi_forumdan!='$forum_satir[id]' ";
				continue;
			}
		}
	}

	else
	{
		$guncel_ek .= " AND hangi_forumdan!='$forum_satir[id]' ";
		continue;
	}
}


unset($yardimcilar);

// forum yardımlarının bilgileri çekiliyor
$vtsorgu = "SELECT kulid,kulad,grup FROM $tablo_ozel_izinler WHERE fno='$forum_satir[id]' AND yonetme='1' ORDER BY kulad";
$ysonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());

while ($yardimci = $vt->fetch_assoc($ysonuc))
{
	if ($yardimci['grup'] == '0')
	{
		if (empty($yardimcilar)) $yardimcilar = '<a href="'.linkver($phpkf_dosyalar['profil'].'?u='.$yardimci['kulid'].'&kim='.$yardimci['kulad'],$yardimci['kulad']).'">'.$yardimci['kulad'].'</a>';
		else $yardimcilar .= ', <a href="'.linkver($phpkf_dosyalar['profil'].'?u='.$yardimci['kulid'].'&kim='.$yardimci['kulad'],$yardimci['kulad']).'">'.$yardimci['kulad'].'</a>';
	}

	else
	{
		if (empty($yardimcilar)) $yardimcilar = '<a href="uyeler.php?kip=grup">'.$yardimci['kulad'].'</a>';
		else $yardimcilar .= ', <a href="<a href="uyeler.php?kip=grup">'.$yardimci['kulad'].'</a>';
	}
}



// forum klasörleri
$forum_klasor = '';

if ($forum_satir['okuma_izni'] == 0) $forum_klasor .= $acik_forum.' alt="." title="'.$l['herkese_acik'].'"';
elseif ($forum_satir['okuma_izni'] == 1) $forum_klasor .= $yonetici_forum.' alt="." title="'.$l['yoneticilere_acik'].'"';
elseif ($forum_satir['okuma_izni'] == 2) $forum_klasor .= $yardimci_forum.' alt="." title="'.$l['yardimcilara_acik'].'"';
elseif ($forum_satir['okuma_izni'] == 3) $forum_klasor .= $ozel_forum.' alt="." title="'.$l['ozel_forum'].'"';
elseif ($forum_satir['okuma_izni'] == 4) $forum_klasor .= $uyeler_forum.' alt="." title="'.$l['uyelere_acik'].'"';
elseif ($forum_satir['okuma_izni'] == 5) $forum_klasor .= $kapali_forum.' alt="." title="'.$l['kapali_forum'].'"';


if (empty($forum_satir['resim']))
$forum_ozel_klasor = 'src="temalar/'.$temadizini.'/resimler/forum01.gif" alt="."';
else $forum_ozel_klasor = 'src="'.$forum_satir['resim'].'" alt="."';


$forum_baglanti = linkver('forum.php?f='.$forum_satir['id'], $forum_satir['forum_baslik']);


//	BÖLÜM YARDIMCISI(LARI) VARSA SIRALANIYOR	//

if (isset($yardimcilar))
{
	if (preg_match('/,/', $yardimcilar)) $forum_yardimcilari = '<br><b><i>'.$l['bolum_yardimcilari'].':</i></b> '.$yardimcilar;
	else $forum_yardimcilari = '<br><b><i>'.$l['bolum_yardimcisi'].':</i></b> '.$yardimcilar;
}




//  EN YENİ KONULARIN BİLGİLERİ ÇEKİLİYOR  //

$vtsorgu = "SELECT id,son_mesaj_tarihi,mesaj_baslik,yazan,cevap_sayi,son_cevap,son_cevap_yazan FROM $tablo_mesajlar WHERE silinmis='0' AND hangi_forumdan='$forum_satir[id]' $alt_forum_sorgu ORDER BY son_mesaj_tarihi DESC LIMIT 1";
$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
$son_mesaj = $vt->fetch_assoc($vtsonuc2);


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
	$sonmesaj_yazan = '<b>'.$l['yazan'].': </b><a href="'.linkver($phpkf_dosyalar['profil'].'?kim='.$son_mesaj['yazan'],$son_mesaj['yazan']).'" title="Kullanıcı Profilini Görüntüle">'.$son_mesaj['yazan'].'</a>';
	$sonmesaj_tarih = '<b>'.$l['tarih'].': </b>'.zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $son_mesaj['son_mesaj_tarihi']);
	$sonmesaj_git = '<a href="'.linkver('konu.php?k='.$son_mesaj['id'], $son_mesaj['mesaj_baslik']).'" style="text-decoration:none">&nbsp;<img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="." title="'.$l['son_ileti'].'">&nbsp;</a>';
}


// cevap varsa
else
{
	$sonmesaj_yazan = '<b>'.$l['yazan'].': </b><a href="'.linkver($phpkf_dosyalar['profil'].'?kim='.$son_mesaj['son_cevap_yazan'],$son_mesaj['son_cevap_yazan']).'" title="Kullanıcı Profilini Görüntüle">'.$son_mesaj['son_cevap_yazan'].'</a>';
	$sonmesaj_tarih = '<b>'.$l['tarih'].': </b>'.zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $son_mesaj['son_mesaj_tarihi']);

	// başlık çok sayfalı ise son sayfaya git
	if ($son_mesaj['cevap_sayi'] > $ayarlar['ksyfkota'])
	{
		$sayfaya_git = (($son_mesaj['cevap_sayi']-1) / $ayarlar['ksyfkota']);
		settype($sayfaya_git,'integer');
		$sayfaya_git = ($sayfaya_git * $ayarlar['ksyfkota']);

		$sonmesaj_git = '<a href="'.linkver('konu.php?k='.$son_mesaj['id'].'&ks='.$sayfaya_git, $son_mesaj['mesaj_baslik'], '#c'.$son_mesaj['son_cevap']).'" style="text-decoration:none">&nbsp;';
	}
	else $sonmesaj_git = '<a href="'.linkver('konu.php?k='.$son_mesaj['id'], $son_mesaj['mesaj_baslik'], '#c'.$son_mesaj['son_cevap']).'" style="text-decoration:none">&nbsp;';

	$sonmesaj_git .= '<img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="." title="'.$l['son_ileti'].'">&nbsp;</a>';
}

endif;



//  BU FORUMUN VE TÜM FORUMLARIN KONU VE MESAJ SAYILARI HESAPLANIYOR    //

$toplam_baslik += $forum_satir['konu_sayisi'];
$toplam_mesaj += ($forum_satir['cevap_sayisi'] + $forum_satir['konu_sayisi']);
$fkonu_sayisi += $forum_satir['konu_sayisi'];
$fmesaj_sayisi += ($forum_satir['cevap_sayisi'] + $forum_satir['konu_sayisi']);



// FORUMU GÖRÜNTÜLEYENLERİN SAYILARI ALINIYOR  //

if ($ayarlar['bolum_kisi'] == 1)
{
	$vtsonuc = $vt->query("SELECT id FROM $tablo_kullanicilar WHERE (sayfano LIKE '%3,$forum_satir[id]' $iceride_ek ) AND (son_hareket + $zaman_asimi) > $tarih AND sayfano!='-1'") or die ($vt->hata_ver());
	$gor_usayi = $vt->num_rows($vtsonuc);

	$vtsonuc = $vt->query("SELECT sid FROM $tablo_oturumlar WHERE (sayfano LIKE '%3,$forum_satir[id]' $iceride_ek ) AND (son_hareket + $zaman_asimi) > $tarih") or die ($vt->hata_ver());
	$gor_msayi = $vt->num_rows($vtsonuc);

	$gor_sayi = $gor_usayi + $gor_msayi;

	if ($gor_sayi > 0) $forum_gor = '('.$gor_sayi.' kişi içeride)';
	else $forum_gor = '';
}

else $forum_gor = '';




//	veriler tema motoruna yollanıyor	//

$tema_ic[$dongu1][] = array('{FORUM_KLASOR}' => $forum_klasor,
'{FORUM_OZEL_KLASOR}' => $forum_ozel_klasor,
'{FORUM_BAGLANTI}' => $forum_baglanti,
'{FORUM_BASLIK}' => $forum_satir['forum_baslik'],
'{FORUM_GOR}' => $forum_gor,
'{FORUM_BILGI}' => $forum_satir['forum_bilgi'],
'{FORUM_YARDIMCILARI}' => $forum_yardimcilari,
'{SONMESAJ_BASLIK}' => $sonmesaj_baslik,
'{SONMESAJ_YAZAN}' => $sonmesaj_yazan,
'{SONMESAJ_TARIH}' => $sonmesaj_tarih,
'{SONMESAJ_GIT}' => $sonmesaj_git,
'{FORUM_BASLIK_SAYISI}' => NumaraBicim($fkonu_sayisi),
'{FORUM_MESAJ_SAYISI}' => NumaraBicim($fmesaj_sayisi),
'{ALT_FORUMLAR}' => $alt_forumlar);

$forum_yardimcilari = '';


endwhile;

// forum dalındaki tüm forumlar gizliyse, forum dalını da gizle
if (!@is_array($tema_ic[$dongu1]))
{
	unset($tema_dis[$dongu1]);
	continue;
}
$dongu1++;

endwhile;


		//      FORUMLAR SIRALANIYOR - SONU      //






        //      GÜNCEL KONULAR SIRALANIYOR - BAŞI      //


if ($ayarlar['sonkonular'] == 1):


//  GÜNCEL KONULARIN BİLGİLERİ ÇEKİLİYOR  //

$vtsorgu = "SELECT id,son_mesaj_tarihi,yazan,hangi_forumdan,cevap_sayi,goruntuleme,mesaj_baslik,yazan,son_cevap,son_cevap_yazan FROM $tablo_mesajlar WHERE silinmis='0' $guncel_ek ORDER BY son_mesaj_tarihi DESC LIMIT $ayarlar[kacsonkonu]";
$vtsonuc10 = $vt->query($vtsorgu) or die ($vt->hata_ver());


$satir_renklendir = 1;

while ($son10 = $vt->fetch_assoc($vtsonuc10))
{
	if (($satir_renklendir % 2)) $satir_renk = 'satir_renk1';
	else $satir_renk = 'satir_renk2';
	$satir_renklendir++;


	$son10konu_baslik = '<a href="'.linkver('konu.php?k='.$son10['id'],$son10['mesaj_baslik']).'">'.$son10['mesaj_baslik'].'</a>';

	$son10konu_forum_baslik = '<a href="'.linkver('forum.php?f='.$son10['hangi_forumdan'],$tumforum_satir[$son10['hangi_forumdan']]).'">'.$tumforum_satir[$son10['hangi_forumdan']].'</a>';

	$son10konu_acan = '<a href="'.linkver($phpkf_dosyalar['profil'].'?kim='.$son10['yazan'],$son10['yazan']).'">'.$son10['yazan'].'</a>';


	//      CEVAP YOKSA     //

	if ($son10['cevap_sayi'] == 0)
		$son10konu_sonyazan = '<a href="'.linkver($phpkf_dosyalar['profil'].'?kim='.$son10['yazan'],$son10['yazan']).'">'.$son10['yazan'].'</a>&nbsp;<a href="'.linkver('konu.php?k='.$son10['id'], $son10['mesaj_baslik']).'" style="text-decoration: none"><img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="" title="'.$l['son_ileti'].'">&nbsp;</a>';


	//      CEVAP VARSA     //

	else
	{
		$son10konu_sonyazan = '<a href="'.linkver($phpkf_dosyalar['profil'].'?kim='.$son10['son_cevap_yazan'],$son10['son_cevap_yazan']).'">'.$son10['son_cevap_yazan'].'</a>';


		//  BAŞLIK ÇOK SAYFALI İSE SON SAYFAYA GİT  //

		if ($son10['cevap_sayi'] > $ayarlar['ksyfkota'])
		{
			$sayfaya_git = (($son10['cevap_sayi']-1) / $ayarlar['ksyfkota']);
			settype($sayfaya_git,'integer');
			$sayfaya_git = ($sayfaya_git * $ayarlar['ksyfkota']);

			$son10konu_sonyazan .= '&nbsp;<a href="'.linkver('konu.php?k='.$son10['id'].'&ks='.$sayfaya_git, $son10['mesaj_baslik'], '#c'.$son10['son_cevap']).'" style="text-decoration: none">';
		}

		else $son10konu_sonyazan .= '&nbsp;<a href="'.linkver('konu.php?k='.$son10['id'], $son10['mesaj_baslik'], '#c'.$son10['son_cevap']).'" style="text-decoration: none">';


		$son10konu_sonyazan .= '<img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="" title="'.$l['son_ileti'].'">&nbsp;</a>';
	}

	$son10konu_sontarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $son10['son_mesaj_tarihi']);


	//	veriler tema motoruna yollanıyor	//

	$tekli[] = array('{SATIR_RENK}' => $satir_renk,
'{SON10KONU_BASLIK}' => $son10konu_baslik,
'{SON10KONU_FORUMBASLIK}' => $son10konu_forum_baslik,
'{SON10KONU_ACAN}' => $son10konu_acan,
'{SON10KONU_CEVAPSAYI}' => NumaraBicim($son10['cevap_sayi']),
'{SON10KONU_GORSAYISI}' => NumaraBicim($son10['goruntuleme']),
'{SON10KONU_SONYAZAN}' => $son10konu_sonyazan,
'{SON10KONU_SONTARIH}' => $son10konu_sontarih);
}

endif;

		//      GÜNCEL KONULAR SIRALANIYOR - SONU      //





		//	FORUM BİLGİLERİ - BAŞI	//


//	TOPLAM ÜYE SAYISI ALINIYOR	//

$uyeler = $vt->query("SELECT id FROM $tablo_kullanicilar");
$uye_sayisi = $vt->num_rows($uyeler);


//	SON KAYDOLAN ÜYENİN ADI ALINIYOR	//

$son_uye = $vt->query("SELECT id,kullanici_adi FROM $tablo_kullanicilar ORDER BY id DESC LIMIT 1");
$sonuye_adi = $vt->fetch_assoc($son_uye);


//	ÇEVRİMİÇİ KULLANICI SAYISI ALINIYOR	//

$vtsonuc9 = $vt->query("SELECT kullanici_adi,yetki FROM $tablo_kullanicilar WHERE (son_hareket + $zaman_asimi) > $tarih AND gizli='0' AND sayfano!='-1' ORDER BY son_hareket DESC") or die ($vt->hata_ver());
$kullanici_sayi = $vt->num_rows($vtsonuc9);


//	ÇEVRİMİÇİ KULLANICI BİLGİLERİ ÇEKİLİYOR	//


$vtsorgu = "SELECT id,kullanici_adi,yetki FROM $tablo_kullanicilar
		WHERE (son_hareket + $zaman_asimi) > $tarih
		AND gizli='0' AND sayfano!='-1' ORDER BY son_hareket DESC LIMIT 0,20";

$cevirim_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


//	GİZLİ ÇEVRİMİÇİ KULLANICI SAYISI ALINIYOR	//

$vtsonuc9 = $vt->query("SELECT id FROM $tablo_kullanicilar WHERE (son_hareket + $zaman_asimi) > $tarih AND gizli='1' AND sayfano!='-1'") or die ($vt->hata_ver());
$gizli_sayi = $vt->num_rows($vtsonuc9);


//	ÇEVRİMİÇİ MİSAFİRLERİN BİLGİLERİ ÇEKİLİYOR	//

$vtsonuc9 = $vt->query("SELECT giris FROM $tablo_oturumlar WHERE (son_hareket + $zaman_asimi) > $tarih") or die ($vt->hata_ver());
$misafir_sayi = $vt->num_rows($vtsonuc9);


$toplam_sayi = ($kullanici_sayi + $gizli_sayi + $misafir_sayi);




//	FORUM BİLGİLERİ YAZDIRILIYOR	//

$yeni_uye = '<a href="'.linkver($phpkf_dosyalar['profil'].'?u='.$sonuye_adi['id'].'&kim='.$sonuye_adi['kullanici_adi'],$sonuye_adi['kullanici_adi']).'">'.$sonuye_adi['kullanici_adi'].'</a>';


$cevrimici_isimler = '';

//	ÇEVRİMİÇİ KULLANICILAR SIRALANIYOR	//

while ($cevirimici = $vt->fetch_assoc($cevirim_sonuc))
{
	$cevrimici_isimler .= '<a href="'.linkver($phpkf_dosyalar['profil'].'?u='.$cevirimici['id'].'&kim='.$cevirimici['kullanici_adi'],$cevirimici['kullanici_adi']).'">';

	if ($cevirimici['id'] == 1)
	$cevrimici_isimler .= '<font class="kurucu">'.$cevirimici['kullanici_adi'].'</font></a>, ';

	elseif ($cevirimici['yetki'] == 1)
	$cevrimici_isimler .= '<font class="yonetici">'.$cevirimici['kullanici_adi'].'</font></a>, ';

	elseif ($cevirimici['yetki'] == 2)
	$cevrimici_isimler .= '<font class="yardimci">'.$cevirimici['kullanici_adi'].'</font></a>, ';

	elseif ($cevirimici['yetki'] == 3)
	$cevrimici_isimler .= '<font class="blm_yrd">'.$cevirimici['kullanici_adi'].'</font></a>, ';

	else $cevrimici_isimler .= $cevirimici['kullanici_adi'].'</a>, ';
}


if ($kullanici_sayi == 0) $cevrimici_isimler .= ' '.$l['yok'];
elseif ($kullanici_sayi > 20) $cevrimici_isimler .= ' <a href="'.$phpkf_dosyalar['cevrimici'].'">...... '.$l['devami'].'</a>';

$cevrimici_sure = ($zaman_asimi / 60);
$cevrimici_bilgi = str_replace('{00}', $cevrimici_sure, $l['cevrimici_bilgi']);

$son24saat = '';


$javascript_kodu = '<script type="text/javascript"><!-- //
function denetle(){
	var dogruMu = true;
	if ((document.giris.kullanici_adi.value.length < 4) || (document.giris.sifre.value.length < 5))
	{
		dogruMu = false; 
		alert("Lütfen kullanıcı adı ve şifrenizi giriniz !");
	}
	return dogruMu;
}
// -->
</script>';


//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/index.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


// giriş yapılmamışsa giriş formu göster

if (isset($kullanici_kim['id']))
{
	$ornek1->kosul('1', array('' => ''), false);
	$kullanici_adi = $kullanici_kim['kullanici_adi'];
}
else $kullanici_adi = '';


//	son kunular açık - kapalı

if (isset($tekli)) $ornek1->tekli_dongu('1',$tekli);

else $ornek1->kosul('2', array('' => ''), false);


// forum dalı var - yok

if ((isset($tema_dis)) AND (isset($tema_ic)))
{
	$ornek1->kosul('5', array('' => ''), false);
	$ornek1->icice_dongu('1', $tema_dis, $tema_ic);
}

else
{
	$ornek1->kosul('3', array('' => ''), false);
	$ornek1->kosul('4', array('' => ''), false);
	$ornek1->kosul('5', array('{FORUM_DALI_YOK}' => $l['forum_dali_yok']), true);
}


$bugun_doganlar = '';


//	veriler tema motoruna yollanıyor	//

$dongusuz = array('{SONKONU_SAYISI}' => $ayarlar['kacsonkonu'],
'{TOPLAM_BASLIK_SAYI}' => NumaraBicim($toplam_baslik),
'{TOPLAM_MESAJ_SAYI}' => NumaraBicim($toplam_mesaj),
'{TOPLAM_UYE_SAYI}' => NumaraBicim($uye_sayisi),
'{YENI_UYE}' => $yeni_uye,
'{KULLANICI_ADI}' => $kullanici_adi,
'{CEVRIMICI_TOPLAM}' => $toplam_sayi,
'{CEVRIMCI_UYE}' => $kullanici_sayi,
'{CEVRIMCI_GIZLI}' => $gizli_sayi,
'{CEVRIMCI_MISAFIR}' => $misafir_sayi,
'{AYARLAR_KURUCU}' => $ayarlar['kurucu'],
'{AYARLAR_YONETICI}' => $ayarlar['yonetici'],
'{AYARLAR_YARDIMCI}' => $ayarlar['yardimci'],
'{AYARLAR_BLM_YRD}' => $ayarlar['blm_yrd'],
'{CEVRIMCI_ISIMLER}' => $cevrimici_isimler,
'{CEVRIMICI_ZAMAN}' => $cevrimici_sure,
'{SON_24_SAAT_CEVRIMCI}' => $son24saat,
'{BUGUN_DOGANLAR}' => $bugun_doganlar,
'{ANASAYFA_BASLIK}' => $ayarlar['site_adi'],
'{GUNCEL_ZAMAN}' => $guncel_saat,
'{ACIK_FORUM}' => $acik_forum,
'{UYELER_FORUM}' => $uyeler_forum,
'{OZEL_FORUM}' => $ozel_forum,
'{YARDIMCI_FORUM}' => $yardimci_forum,
'{YONETICI_FORUM}' => $yonetici_forum,
'{KAPALI_FORUM}' => $kapali_forum,
'{ACIK_KONU}' => $acik_konu,
'{FORUMBILGI_RESIM}' => $forumbilgileri_resim,
'{CEVRIMICI_RESIM}' => $cevrimici_resim,
'{FORUM_INDEX}' => $phpkf_dosyalar['forum'],
'{JAVASCRIPT_KODU}' => $javascript_kodu);

$ornek1->dongusuz($dongusuz);

eval(TEMA_UYGULA);

?>