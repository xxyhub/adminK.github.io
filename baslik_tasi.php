<?php



if (!defined('DOSYA_AYAR')) include 'ayar.php';
if (!defined('DOSYA_GUVENLIK')) include 'phpkf-bilesenler/guvenlik.php';
if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';


//	BAŞLIĞI TAŞI TIKLANMIŞSA	//

if ( ( isset($_POST['kayit_yapildi_mi']) ) AND ($_POST['kayit_yapildi_mi'] == 'form_dolu') ):

if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';


if ( (!isset($_POST['mesaj_no'])) OR (is_numeric($_POST['mesaj_no']) == false) )
{
	header('Location: hata.php?hata=47');
	exit();
}

else $_POST['mesaj_no'] = zkTemizle($_POST['mesaj_no']);


if ( (!isset($_POST['tasinan_forum'])) OR (is_numeric($_POST['tasinan_forum']) == false) )
{
	header('Location: hata.php?hata=14');
	exit();
}

else $_POST['tasinan_forum'] = zkTemizle($_POST['tasinan_forum']);



//	BAŞLIĞIN OLDUĞU FORUM ÖĞRENİLİYOR	//

$vtsorgu = "SELECT id,mesaj_baslik,hangi_forumdan FROM $tablo_mesajlar WHERE id='$_POST[mesaj_no]' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$forum_no = $vt->fetch_array($vtsonuc);


//	YÖNETİCİ VEYA FORUMUN YARDIMCISI İSE DEVAM	//

if (($kullanici_kim['yetki'] == 1) OR ($kullanici_kim['yetki'] == 2))
{
	if ($kullanici_kim['yetki'] == 2)
	{
		// Konunun bulunduğu forum
		$vtsorgu = "SELECT okuma_izni,konu_acma_izni,yazma_izni FROM $tablo_forumlar
					WHERE id='$forum_no[hangi_forumdan]'";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
		$kul_izin = $vt->fetch_assoc($vtsonuc);


		// Konunun bulunduğu forum yetkilerinden biri sadece yöneticilerse veya kapalıysa
		if ( ($kul_izin['okuma_izni'] == 1) OR ($kul_izin['konu_acma_izni'] == 1) OR ($kul_izin['yazma_izni'] == 1) OR ($kul_izin['okuma_izni'] == 5) OR ($kul_izin['konu_acma_izni'] == 5) OR ($kul_izin['yazma_izni'] == 5) )
		{
			header('Location: hata.php?hata=3');
			exit();
		}


		// Konunun taşındığı forum
		$vtsorgu = "SELECT okuma_izni,konu_acma_izni,yazma_izni FROM $tablo_forumlar
					WHERE id='$_POST[tasinan_forum]'";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
		$kul_izin2 = $vt->fetch_assoc($vtsonuc);


		// Konunun taşındığı forum yetkilerinden biri sadece yöneticilerse veya kapalıysa
		if ( ($kul_izin2['okuma_izni'] == 1) OR ($kul_izin2['konu_acma_izni'] == 1) OR ($kul_izin2['yazma_izni'] == 1) OR ($kul_izin2['okuma_izni'] == 5) OR ($kul_izin2['konu_acma_izni'] == 5) OR ($kul_izin2['yazma_izni'] == 5) )
		{
			header('Location: hata.php?hata=195');
			exit();
		}
	}



	//  konu taşınıyor
	$vtsorgu = "UPDATE $tablo_mesajlar SET hangi_forumdan='$_POST[tasinan_forum]'
				WHERE id='$_POST[mesaj_no]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


	// cevapları taşınıyor
	$vtsorgu = "UPDATE $tablo_cevaplar SET hangi_forumdan='$_POST[tasinan_forum]'
				WHERE hangi_basliktan='$_POST[mesaj_no]'";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());



	// gönderilen forumun cevap sayısı hesaplanıyor
	$vtsonuc9 = $vt->query("SELECT id FROM $tablo_cevaplar WHERE hangi_forumdan='$_POST[tasinan_forum]'") or die ($vt->hata_ver());
	$cevap_sayi = $vt->num_rows($vtsonuc9);


	// gönderilen forumun konu ve cevap sayısı arttırılıyor
	$vtsorgu = "UPDATE $tablo_forumlar SET konu_sayisi=konu_sayisi + 1,cevap_sayisi='$cevap_sayi'
				WHERE id='$_POST[tasinan_forum]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());



	// alınan forumun cevap sayısı hesaplanıyor
	$vtsonuc9 = $vt->query("SELECT id FROM $tablo_cevaplar WHERE hangi_forumdan='$forum_no[hangi_forumdan]'") or die ($vt->hata_ver());
	$cevap_sayi = $vt->num_rows($vtsonuc9);


	// alınan forumun konu ve cevap sayısı eksiltiliyor
	$vtsorgu = "UPDATE $tablo_forumlar SET konu_sayisi=konu_sayisi - 1,cevap_sayisi='$cevap_sayi'
				WHERE id='$forum_no[hangi_forumdan]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


	header('Location: hata.php?bilgi=9&fno1='.$forum_no['hangi_forumdan'].'&fno2='.$_POST['tasinan_forum']);
	exit();
}


elseif ($kullanici_kim['yetki'] == 3)
{
	// Konunun bulunduğu forum
	$vtsorgu = "SELECT okuma_izni,konu_acma_izni,yazma_izni FROM $tablo_forumlar
				WHERE id='$forum_no[hangi_forumdan]'";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$kul_izin = $vt->fetch_assoc($vtsonuc);


	// Konunun bulunduğu forum yetkilerinden biri sadece yöneticilerse veya kapalıysa
	if ( ($kul_izin['okuma_izni'] == 1) OR ($kul_izin['konu_acma_izni'] == 1) OR ($kul_izin['yazma_izni'] == 1) OR ($kul_izin['okuma_izni'] == 5) OR ($kul_izin['konu_acma_izni'] == 5) OR ($kul_izin['yazma_izni'] == 5) )
	{
		header('Location: hata.php?hata=3');
		exit();
	}


	// Konunun taşındığı forum
	$vtsorgu = "SELECT okuma_izni,konu_acma_izni,yazma_izni FROM $tablo_forumlar
				WHERE id='$_POST[tasinan_forum]'";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$kul_izin2 = $vt->fetch_assoc($vtsonuc);


	// Konunun taşındığı forum yetkilerinden biri sadece yöneticilerse veya kapalıysa
	if ( ($kul_izin2['okuma_izni'] == 1) OR ($kul_izin2['konu_acma_izni'] == 1) OR ($kul_izin2['yazma_izni'] == 1) OR ($kul_izin2['okuma_izni'] == 5) OR ($kul_izin2['konu_acma_izni'] == 5) OR ($kul_izin2['yazma_izni'] == 5) )
	{
		header('Location: hata.php?hata=195');
		exit();
	}


	if ($kullanici_kim['grupid'] != '0')
	{
		$grupek1 = "grup='$kullanici_kim[grupid]' AND fno='$_POST[tasinan_forum]' AND yonetme='1' OR";
		$grupek2 = "grup='$kullanici_kim[grupid]' AND fno='$forum_no[hangi_forumdan]' AND yonetme='1' OR";
	}

	else
	{
		$grupek1 = "grup='0' AND";
		$grupek2 = "grup='0' AND";
	}


	// Konunun taşındığı forum özel yetki
	$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler
				WHERE $grupek1 kulad='$kullanici_kim[kullanici_adi]'
				AND fno='$_POST[tasinan_forum]' AND yonetme='1'";
	$kul_izin2 = $vt->query($vtsorgu) or die ($vt->hata_ver());


	// Taşınan forumda yetkisi yoksa
	if (!$vt->num_rows($kul_izin2))
	{
		header('Location: hata.php?hata=195');
		exit();
	}



	// Konunun bulunduğu forum özel yetki
	$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler
				WHERE $grupek2 kulad='$kullanici_kim[kullanici_adi]'
				AND fno='$forum_no[hangi_forumdan]' AND yonetme='1'";
	$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());


	//	YÖNETME YETKİSİ VARSA	//
	if ($vt->num_rows($kul_izin))
	{
		// konu taşınıyor
		$vtsorgu = "UPDATE $tablo_mesajlar SET hangi_forumdan='$_POST[tasinan_forum]'
					WHERE id='$_POST[mesaj_no]' LIMIT 1";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


		// cevapları taşınıyor
		$vtsorgu = "UPDATE $tablo_cevaplar SET hangi_forumdan='$_POST[tasinan_forum]'
					WHERE hangi_basliktan='$_POST[mesaj_no]'";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());



		// gönderilen forumun cevap sayısı hesaplanıyor
		$vtsonuc9 = $vt->query("SELECT id FROM $tablo_cevaplar WHERE hangi_forumdan='$_POST[tasinan_forum]'") or die ($vt->hata_ver());
		$cevap_sayi = $vt->num_rows($vtsonuc9);


		// gönderilen forumun konu ve cevap sayısı arttırılıyor
		$vtsorgu = "UPDATE $tablo_forumlar SET konu_sayisi=konu_sayisi + 1,cevap_sayisi='$cevap_sayi'
					WHERE id='$_POST[tasinan_forum]' LIMIT 1";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver()) or die ($vt->hata_ver());



		// alınan forumun cevap sayısı hesaplanıyor
		$vtsonuc9 = $vt->query("SELECT id FROM $tablo_cevaplar WHERE hangi_forumdan='$forum_no[hangi_forumdan]'") or die ($vt->hata_ver());
		$cevap_sayi = $vt->num_rows($vtsonuc9);


		// alınan forumun konu ve cevap sayısı eksiltiliyor
		$vtsorgu = "UPDATE $tablo_forumlar SET konu_sayisi=konu_sayisi - 1,cevap_sayisi='$cevap_sayi'
					WHERE id='$forum_no[hangi_forumdan]' LIMIT 1";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


		header('Location: hata.php?bilgi=9&fno1='.$forum_no['hangi_forumdan'].'&fno2='.$_POST['tasinan_forum']);
		exit();
	}

	//	YETKİSİZ İSE UYARILIYOR	//
	else
	{
		header('Location: hata.php?hata=3');
		exit();
	}
}
//		YETKİSİZ İSE UYARILIYOR		//

else
{
	header('Location: hata.php?hata=3');
	exit();
}






			//	SAYFAYA İLK GİRİŞ KISMI	//


elseif ( ( isset($_GET['kip']) ) AND ($_GET['kip'] == 'tasi') ):


if (!defined('DOSYA_KULLANICI_KIMLIK')) include 'phpkf-bilesenler/kullanici_kimlik.php';


if ( (!isset($_GET['mesaj_no'])) OR (is_numeric($_GET['mesaj_no']) == false) )
{
	header('Location: hata.php?hata=47');
	exit();
}

else $_GET['mesaj_no'] = zkTemizle($_GET['mesaj_no']);



//	BAŞLIĞIN OLDUĞU FORUM ÖĞRENİLİYOR	//

$vtsorgu = "SELECT id,mesaj_baslik,hangi_forumdan FROM $tablo_mesajlar WHERE id='$_GET[mesaj_no]' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$forum_no = $vt->fetch_array($vtsonuc);


//	YÖNETİCİ, FORUM YARDIMCI VEYA BÖLÜMÜN YARDIMCISI İSE DEVAM	//

if ($kullanici_kim['yetki'] == 1);


// forum yardımcısı
elseif ($kullanici_kim['yetki'] == 2)
{
	// Konunun bulunduğu forum
	$vtsorgu = "SELECT okuma_izni,konu_acma_izni,yazma_izni FROM $tablo_forumlar
				WHERE id='$forum_no[hangi_forumdan]'";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$kul_izin = $vt->fetch_assoc($vtsonuc);


	// Konunun bulunduğu forum yetkilerinden biri sadece yöneticilerse veya kapalıysa
	if ( ($kul_izin['okuma_izni'] == 1) OR ($kul_izin['konu_acma_izni'] == 1) OR ($kul_izin['yazma_izni'] == 1) OR ($kul_izin['okuma_izni'] == 5) OR ($kul_izin['konu_acma_izni'] == 5) OR ($kul_izin['yazma_izni'] == 5) )
	{
		header('Location: hata.php?hata=3');
		exit();
	}
}


elseif ($kullanici_kim['yetki'] == 3)
{
	// Konunun bulunduğu forum
	$vtsorgu = "SELECT okuma_izni,konu_acma_izni,yazma_izni FROM $tablo_forumlar
				WHERE id='$forum_no[hangi_forumdan]'";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$kul_izin = $vt->fetch_assoc($vtsonuc);


	// Konunun bulunduğu forum yetkilerinden biri sadece yöneticilerse veya kapalıysa
	if ( ($kul_izin['okuma_izni'] == 1) OR ($kul_izin['konu_acma_izni'] == 1) OR ($kul_izin['yazma_izni'] == 1) OR ($kul_izin['okuma_izni'] == 5) OR ($kul_izin['konu_acma_izni'] == 5) OR ($kul_izin['yazma_izni'] == 5) )
	{
		header('Location: hata.php?hata=3');
		exit();
	}


	// Konunun bulunduğu forum özel yetki
	if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$forum_no[hangi_forumdan]' AND yonetme='1' OR";
	else $grupek = "grup='0' AND";

	$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$forum_no[hangi_forumdan]' AND yonetme='1'";
	$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());


	if ($vt->num_rows($kul_izin));

	//	YETKİSİZ İSE UYARILIYOR	//
	else
	{
		header('Location: hata.php?hata=3');
		exit();
	}
}

//		YETKİSİZ İSE UYARILIYOR		//
else
{
	header('Location: hata.php?hata=3');
	exit();
}


$sayfano = '11,'.$forum_no['id'];
$sayfa_adi = $forum_no['mesaj_baslik'];
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');




$options_forum = '';


// forum dalı adları çekiliyor

$vtsorgu = "SELECT id,ana_forum_baslik FROM $tablo_dallar ORDER BY sira";
$dallar_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


while ($dallar_satir = $vt->fetch_array($dallar_sonuc))
{
	$options_forum .= '<option value="">[ '.$dallar_satir['ana_forum_baslik'].' ]';


	// forum adları çekiliyor

	$vtsorgu = "SELECT id,forum_baslik,alt_forum FROM $tablo_forumlar
				WHERE alt_forum='0' AND dal_no='$dallar_satir[id]' ORDER BY sira";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


	while ($forum_satir = $vt->fetch_array($vtsonuc))
	{
		// alt forumuna bakılıyor
		$vtsorgu = "SELECT id,forum_baslik FROM $tablo_forumlar
					WHERE alt_forum='$forum_satir[id]' ORDER BY sira";
		$vtsonuca = $vt->query($vtsorgu) or die ($vt->hata_ver());


		if (!$vt->num_rows($vtsonuca))
		{
			if ($forum_no['hangi_forumdan'] != $forum_satir['id']) $options_forum .= '
			<option value="'.$forum_satir['id'].'"> &nbsp; - '.$forum_satir['forum_baslik'];

			else $options_forum .= '
			<option value="'.$forum_satir['id'].'" selected="selected"> &nbsp; - '.$forum_satir['forum_baslik'];
		}


		else
		{
			if ($forum_no['hangi_forumdan'] != $forum_satir['id']) $options_forum .= '
			<option value="'.$forum_satir['id'].'"> &nbsp; - '.$forum_satir['forum_baslik'];

			else $options_forum .= '
			<option value="'.$forum_satir['id'].'" selected="selected"> &nbsp; - '.$forum_satir['forum_baslik'];

			while ($alt_forum_satir = $vt->fetch_array($vtsonuca))
			{
				if ($forum_no['hangi_forumdan'] != $alt_forum_satir['id']) $options_forum .= '
				<option value="'.$alt_forum_satir['id'].'"> &nbsp; &nbsp; &nbsp; -- '.$alt_forum_satir['forum_baslik'];

				else $options_forum .= '
				<option value="'.$alt_forum_satir['id'].'" selected="selected"> &nbsp; &nbsp; &nbsp; -- '.$alt_forum_satir['forum_baslik'];
			}
		}
	}
}



//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/baslik_tasi.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));

$ornek1->dongusuz(array('{MESAJ_NO}' => $_GET['mesaj_no'],
						'{OPTION_FORUM}' => $options_forum));

eval(TEMA_UYGULA);
endif;

?>