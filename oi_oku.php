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


if (!defined('DOSYA_AYAR')) include 'ayar.php';
if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';
if (!defined('DOSYA_GUVENLIK')) include 'phpkf-bilesenler/guvenlik.php';
if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';


// özel ileti özelliği kapalıysa
if ($ayarlar['o_ileti'] == 0)
{
	header('Location: hata.php?uyari=2');
	exit();
}


$_GET['oino'] = zkTemizle($_GET['oino']);
$tarih = time();


// özel ileti çekiliyor
$vtsorgu = "SELECT * FROM $tablo_ozel_ileti WHERE id='$_GET[oino]' LIMIT 1";
$vtsonucoi1 = $vt->query($vtsorgu) or die ($vt->hata_ver());
$ozel_ileti = $vt->fetch_array($vtsonucoi1);


// özel ileti yoksa veya okuma yetkisi yoksa
if (($ozel_ileti['kime'] != $kullanici_kim['kullanici_adi']) AND ($ozel_ileti['kimden'] != $kullanici_kim['kullanici_adi']))
{
	header('Location: hata.php?hata=62');
	exit();
}


// özel ileti cevap ise konusuna yönlendiriliyor
if ($ozel_ileti['cevap'] != 0)
{
	header('Location: oi_oku.php?oino='.$ozel_ileti['cevap']);
	exit();
}


// sadece gelen iletinin okunma tarihi yoksa okundu bilgisi giriliyor
if ((!$ozel_ileti['okunma_tarihi']) AND ($ozel_ileti['kime'] == $kullanici_kim['kullanici_adi']))
{
	$vtsorgu = "UPDATE $tablo_ozel_ileti SET okunma_tarihi='$tarih' WHERE id='$_GET[oino]' LIMIT 1";
	$vtsonucoi1 = $vt->query($vtsorgu) or die ($vt->hata_ver());

	// özel iletinin bildirimi varsa okundu olarak işaretleniyor
	$vtsorgu = "UPDATE $tablo_bildirimler SET okundu='1' WHERE uye_id='$kullanici_kim[id]' AND tip='1' AND okundu='0' AND bildirim='$ozel_ileti[kimden]' ORDER BY id LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


	// gönderen iletiyi silmemişse ulaşan kutusuna taşı
	if ($ozel_ileti['gonderen_kutu'] != '0')
	{
		// gönderenin ulaşan kutusunun doluluk oranına bakılıyor
		$vtsonuc9 = $vt->query("SELECT id FROM $tablo_ozel_ileti WHERE kimden='$ozel_ileti[kimden]' AND gonderen_kutu='2'") or die ($vt->hata_ver());
		$num_rows = $vt->num_rows($vtsonuc9);

		if (($num_rows + 1) > $ayarlar['ulasan_kutu_kota'])
		{
			$vtsorgu = "SELECT id FROM $tablo_ozel_ileti WHERE kimden='$ozel_ileti[kimden]' AND gonderen_kutu='2' ORDER BY okunma_tarihi LIMIT 1";
			$vtsonucoi1 = $vt->query($vtsorgu) or die ($vt->hata_ver());
			$satir = $vt->fetch_array($vtsonucoi1);

			// doluysa kutudaki en eski ileti silinior
			$vtsorgu = "DELETE FROM $tablo_ozel_ileti WHERE id='$satir[id]' LIMIT 1";
			$vtsonucoi1 = $vt->query($vtsorgu) or die ($vt->hata_ver());
		}

		$vtsorgu = "UPDATE $tablo_ozel_ileti SET gonderen_kutu='2',okunma_tarihi='$tarih' WHERE id='$ozel_ileti[id]' LIMIT 1";
	}

	else $vtsorgu = "UPDATE $tablo_ozel_ileti SET okunma_tarihi='$tarih' WHERE id='$ozel_ileti[id]' LIMIT 1";

	$vtsonucoi1 = $vt->query($vtsorgu) or die ($vt->hata_ver());


	// okunmamış özel ileti sayısı sıfır değilse sayıyı eksilt
	if ($kullanici_kim['okunmamis_oi'] != 0)
	{
		$vtsorgu = "UPDATE $tablo_kullanicilar SET okunmamis_oi=okunmamis_oi-1 WHERE id='$kullanici_kim[id]' LIMIT 1";
		$vtsonucoi1 = $vt->query($vtsorgu) or die ($vt->hata_ver());
		$kullanici_kim['okunmamis_oi']--;
	}
}



// gönderenin yetkisi ve resmi çekiliyor
$vtsorgu = "SELECT id,yetki,resim FROM $tablo_kullanicilar WHERE kullanici_adi='$ozel_ileti[kimden]' LIMIT 1";
$vtsonucoi1 = $vt->query($vtsorgu) or die ($vt->hata_ver());
$kimden_yetki = $vt->fetch_assoc($vtsonucoi1);


// gönderen kullanıcı resmi
if ($kimden_yetki['resim'] != '') $gonderen_resim = $kimden_yetki['resim'];
elseif ($ayarlar['v-uye_resmi'] != '') $gonderen_resim = $ayarlar['v-uye_resmi'];
else $gonderen_resim = '';


$oi_kimden = '<a href="profil.php?kim='.$ozel_ileti['kimden'].'" title="Kullanıcı profilini görüntüle">'.$ozel_ileti['kimden'].'</a>';
$oi_kime = '<a href="profil.php?kim='.$ozel_ileti['kime'].'" title="Kullanıcı profilini görüntüle">'.$ozel_ileti['kime'].'</a>';
$oi_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $ozel_ileti['gonderme_tarihi']);


// gönderen yönetici, yardımcı veya kendisi değilse 
if ( ($kimden_yetki['yetki'] == 0) AND ($ozel_ileti['kimden'] != $kullanici_kim['kullanici_adi']) )
	$oi_kimden .= '&nbsp; &nbsp; &nbsp; <a href="ozel_ileti.php?kip=ayarlar&amp;kim='.$ozel_ileti['kimden'].'">[ Bu kişiyi engelle ]</a>';


if ($ozel_ileti['ifade'] == 1)
	$ozel_ileti['ozel_icerik'] = ifadeler($ozel_ileti['ozel_icerik']);

if (($ozel_ileti['bbcode_kullan'] == 1) AND ($ayarlar['bbcode'] == 1))
	$oi_icerik = bbcode_acik($ozel_ileti['ozel_icerik'], $ozel_ileti['id']);

else $oi_icerik = bbcode_kapali($ozel_ileti['ozel_icerik']);



// özel iletinin cevapları varsa çekiliyor
if($ozel_ileti['cevap_sayi'] != 0)
{
	$vtsorgu = "SELECT * FROM $tablo_ozel_ileti WHERE cevap='$ozel_ileti[id]' ORDER BY id";
	$vtsonucoi2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$csira = 1;

	// alan üyenin resmi çekiliyor
	$vtsorgu = "SELECT resim FROM $tablo_kullanicilar WHERE kullanici_adi='$ozel_ileti[kime]' LIMIT 1";
	$vtsonucoi3 = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$kime_resim = $vt->fetch_assoc($vtsonucoi3);


	while ($oi_cevaplar = $vt->fetch_array($vtsonucoi2))
	{
		// sadece gelen iletinin okunma tarihi yoksa okundu bilgisi giriliyor
		if ((!$oi_cevaplar['okunma_tarihi']) AND ($oi_cevaplar['kime'] == $kullanici_kim['kullanici_adi']))
		{
			$vtsorgu = "UPDATE $tablo_ozel_ileti SET gonderen_kutu='2',okunma_tarihi='$tarih' WHERE id='$oi_cevaplar[id]' LIMIT 1";
			$vtsonucoi1 = $vt->query($vtsorgu) or die ($vt->hata_ver());

			// özel iletinin bildirimi varsa okundu olarak işaretleniyor
			$vtsorgu = "UPDATE $tablo_bildirimler SET okundu='1' WHERE uye_id='$kullanici_kim[id]' AND tip='1' AND okundu='0' AND bildirim='$oi_cevaplar[kimden]' ORDER BY id LIMIT 1";
			$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());

			// gönderen konuyu silmemişse ulaşan kutusuna taşı
			if ($ozel_ileti['gonderen_kutu'] != '0')
			{
				$vtsorgu = "UPDATE $tablo_ozel_ileti SET gonderen_kutu='2' WHERE id='$ozel_ileti[id]' LIMIT 1";
				$vtsonucoi1 = $vt->query($vtsorgu) or die ($vt->hata_ver());
			}

			// okunmamış özel ileti sayısı sıfır değilse sayıyı eksilt
			if ($kullanici_kim['okunmamis_oi'] != 0)
			{
				$vtsorgu = "UPDATE $tablo_kullanicilar SET okunmamis_oi=okunmamis_oi-1 WHERE id='$kullanici_kim[id]' LIMIT 1";
				$vtsonucoi1 = $vt->query($vtsorgu) or die ($vt->hata_ver());
				$kullanici_kim['okunmamis_oi']--;
			}
		}


		// cevap yazan kullanıcı resmi
		if ($oi_cevaplar['kimden'] == $ozel_ileti['kimden']) 
		{
			if ($kimden_yetki['resim'] != '') $cgonderen_resim = $kimden_yetki['resim'];
			elseif ($ayarlar['v-uye_resmi'] != '') $cgonderen_resim = $ayarlar['v-uye_resmi'];
			else $cgonderen_resim = '';
		}

		else
		{
			if ($kime_resim['resim'] != '') $cgonderen_resim = $kime_resim['resim'];
			elseif ($ayarlar['v-uye_resmi'] != '') $cgonderen_resim = $ayarlar['v-uye_resmi'];
			else $cgonderen_resim = '';
		}


		if ($oi_cevaplar['kimden'] != $kullanici_kim['kullanici_adi'])
			$ynt_kime = $oi_cevaplar['kimden'];
		else $ynt_kime = $oi_cevaplar['kime'];

		$oicevap_kimden = '<a href="profil.php?kim='.$oi_cevaplar['kimden'].'" title="Kullanıcı profilini görüntüle">'.$oi_cevaplar['kimden'].'</a>';

		if ($oi_cevaplar['ifade'] == 1)
			$oi_cevaplar['ozel_icerik'] = ifadeler($oi_cevaplar['ozel_icerik']);

		if (($oi_cevaplar['bbcode_kullan'] == 1) AND ($ayarlar['bbcode'] == 1))
			$oicevap_icerik = bbcode_acik($oi_cevaplar['ozel_icerik'], $oi_cevaplar['id']);

		else $oicevap_icerik = bbcode_kapali($oi_cevaplar['ozel_icerik']);

		$oicevap_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $oi_cevaplar['gonderme_tarihi']);

		// veriler tema motoruna yollanıyor
		$teklioi[] = array('{OICEVAP_YAZAN}' => $oicevap_kimden,
		'{CGONDEREN_RESIM}' => $cgonderen_resim,
		'{OICEVAP_TARIH}' => $oicevap_tarih,
		'{OICEVAP_SIRA}' => $csira,
		'{OICEVAP_ICERIK}' => $oicevap_icerik);
		$csira++;
	}
}

else
{
	if ($ozel_ileti['kimden'] != $kullanici_kim['kullanici_adi']) $ynt_kime = $ozel_ileti['kimden'];
	else $ynt_kime = $ozel_ileti['kime'];
}


if (!isset($ynt_kime)) $ynt_kime = $ozel_ileti['kimden'];



$sayfano = 21;
$sayfa_adi = 'Özel ileti Okuma';
include 'phpkf-bilesenler/kullanici_kimlik.php';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');



$form_bilgi1 = '<form action="phpkf-bilesenler/oi_yaz_yap.php" method="post" onsubmit="return denetle_yazi()" name="duzenleyici_form" id="duzenleyici_form">
<input type="hidden" name="kayit_yapildi_mi" value="form_dolu">
<input type="hidden" name="sayfa_onizleme" value="oi_yaz">
<input type="hidden" name="mesaj_onizleme" value="Önizleme">
<input type="hidden" name="ozel_yanitla" value="1">
<input type="hidden" name="oino" value="'.$ozel_ileti['id'].'">
<input type="hidden" name="ozel_kime" value="'.$ynt_kime.'">';



//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/oi_oku.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


if (isset($teklioi)) $ornek1->tekli_dongu('1',$teklioi);
else $ornek1->kosul('1', array(''=>''), false);

$ornek1->dongusuz(array('{FORM_BILGI1}' => $form_bilgi1,
'{FORM_ICERIK}' => '',
'{OZEL_ILETI_GONDER}' => $oi_rengi,
'{GONDEREN_RESIM}' => $gonderen_resim,
'{OI_KIMDEN}' => $oi_kimden,
'{OI_KIME}' => $oi_kime,
'{OZEL_ILET_BASLIK}' => $ozel_ileti['ozel_baslik'],
'{OI_TARIH}' => $oi_tarih,
'{OI_ICERIK}' => $oi_icerik));


eval(TEMA_UYGULA);

?>