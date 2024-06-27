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


$javascript_kodu2 = '<script type="text/javascript">
<!-- 
function secim(ne){
var neresi;for (i=0, tablono=1; i < document.secim_formu.length; i++,tablono++){
document.secim_formu.elements[i].checked = ne;
neresi = document.getElementById("secili"+tablono);
if ( (ne == false) && (neresi != null) )
neresi.style.backgroundColor = "#ffffff";
if ( (ne == true) && (neresi != null) )
neresi.style.backgroundColor = "#e9e9e9";}}

function secili_yap(tablono){
var neresi = document.getElementById("secili"+tablono);
if (document.secim_formu.elements[tablono].checked == false)
neresi.style.backgroundColor = "#ffffff";
else
neresi.style.backgroundColor = "#e9e9e9";}
//  -->
</script>';


// DUYURU BİLGİLERİ ÇEKİLİYOR //

$vtsorgu = "SELECT * FROM $tablo_duyurular WHERE fno='ozel' ORDER BY id";
$duyuru_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


// DUYURU VARSA DÖNGÜYE GİRİLİYOR //

if ($vt->num_rows($duyuru_sonuc)) 
{
	while ($duyurular = $vt->fetch_assoc($duyuru_sonuc))
	{
		$tekli2[] = array('{OZEL_DUYURU_BASLIK}' => $duyurular['duyuru_baslik'], '{OZEL_DUYURU_ICERIK}' => $duyurular['duyuru_icerik']);
	}
}





//  ENGELLEME İŞLEMLERİ - BAŞI  //

if ( (isset($_POST['kip'])) AND ($_POST['kip'] == 'engel') ):

$_POST['engel_tipi'] = @zkTemizle($_POST['engel_tipi']);
if (!preg_match('/^[0-2]+$/', $_POST['engel_tipi'])) $_POST['engel_tipi'] = 0;
$dogru_kuladlar = '';


// Tip sıfır değilse üye adlarını denetle
if ($_POST['engel_tipi'] != '0')
{
	// değişkendeki veriler satır satır ayrılıp dizi değişkene aktarılıyor //
	$yasak_kulad_bosluk = explode("\r\n", $_POST['engellenenler']);

	// satır sayısı alınıyor //
	$yasak_kulad_sayi = count($yasak_kulad_bosluk);

	// dizideki satırlar döngüye sokuluyor //
	for ($d=0,$a=0; $d < $yasak_kulad_sayi; $d++)
	{
		$yasak_kulad_bosluk[$d] = @zkTemizle(trim($yasak_kulad_bosluk[$d]));

		// 3 karakterden kısa ve aynı olan isimler diziden atılıyor	//
		if ( (strlen($yasak_kulad_bosluk[$d]) > 3) AND (!preg_match("/$yasak_kulad_bosluk[$d],/i", $dogru_kuladlar)) )
		{
			// kullanıcı adı denetleniyor
			$vtsorgu = "SELECT id,kullanici_adi FROM $tablo_kullanicilar WHERE kullanici_adi='$yasak_kulad_bosluk[$d]' AND yetki='0' AND id!=$kullanici_kim[id] LIMIT 1";
			$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
			$satir = $vt->fetch_array($vtsonuc);

			if (isset($satir['kullanici_adi'])) $dogru_kuladlar .= $satir['kullanici_adi'].',';
			$a++;
		}
		}
}


// kullanıcının engelleme girdileri çekiliyor
$vtsorgu = "SELECT deger FROM $tablo_yasaklar WHERE etiket='$kullanici_kim[id]' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


// kullanıcıya ait girdi varsa
if ($vt->num_rows($vtsonuc))
{
	// tip sıfır değilse yasaklar tablosuna girdi yapılıyor
	if ($_POST['engel_tipi'] != '0')
		$vtsorgu = "UPDATE $tablo_yasaklar SET deger='$dogru_kuladlar', tip='$_POST[engel_tipi]' where etiket='$kullanici_kim[id]' LIMIT 1";

	// tip sıfır ise yasaklar tablosundaki girdi siliniyor
	else $vtsorgu = "DELETE FROM $tablo_yasaklar WHERE etiket='$kullanici_kim[id]' LIMIT 1";

	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
}


// kullanıcıya ait girdi yoksa
else
{
	// tip sıfır değilse yasaklar tablosuna girdi yapılıyor
	if ($_POST['engel_tipi'] != '0')
	{
		$vtsorgu = "INSERT INTO $tablo_yasaklar (etiket, deger, tip)";
		$vtsorgu .= "VALUES ('$kullanici_kim[id]', '$dogru_kuladlar', '$_POST[engel_tipi]')";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	}
}

header('Location: hata.php?bilgi=46');
exit();


//  ENGELLEME İŞLEMLERİ - SONU  //




//  İLETİ SİLME İŞLEMLERİ - BAŞI  //

elseif ( (isset($_POST['secili_sil'])) AND ($_POST['secili_sil'] != '') ):

// seçim yapılmamışsa
if (!isset($_POST['sec_ileti']))
{
	header('Location: hata.php?hata=68');
	exit();
}


foreach ($_POST['sec_ileti'] as $sec_ileti_sil)
{
	$sec_ileti_sil = zkTemizle($sec_ileti_sil);

	$vtsorgu = "SELECT okunma_tarihi,kimden,kime,alan_kutu,gonderen_kutu,cevap_sayi,cevap FROM $tablo_ozel_ileti WHERE id='$sec_ileti_sil' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$ozel_ileti = $vt->fetch_array($vtsonuc);


	//   YOLLADIĞI ÖZEL İLETİ İSE   //

	if (($ozel_ileti['kimden'] == $kullanici_kim['kullanici_adi']))
	{
		// kendine yolladığı özel ileti ise gerçekten sil
		if ($ozel_ileti['kimden'] == $ozel_ileti['kime']){
			$vtsorgu = "DELETE FROM $tablo_ozel_ileti WHERE id='$sec_ileti_sil' LIMIT 1";
			$csil = true;}

		// iletiyi alan kişi de silmişse gerçekten sil
		elseif ($ozel_ileti['alan_kutu'] == '0'){
			$vtsorgu = "DELETE FROM $tablo_ozel_ileti WHERE id='$sec_ileti_sil' LIMIT 1";
			$csil = true;}

		// sadece gonderen kutusunu sıfırla
		else {$vtsorgu = "UPDATE $tablo_ozel_ileti SET gonderen_kutu='0' WHERE id='$sec_ileti_sil' LIMIT 1";
		$csil = false;}

		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


		// cevapları varsa siliniyor
		if ($ozel_ileti['cevap_sayi'] != '0')
		{
			if ($csil)
			{
				$vtsorgu = "DELETE FROM $tablo_ozel_ileti WHERE cevap='$sec_ileti_sil' AND okunma_tarihi is null";
				$vtsorgu2 = "DELETE FROM $tablo_ozel_ileti WHERE cevap='$sec_ileti_sil'";
			}

			else
			{
				$vtsorgu = "UPDATE $tablo_ozel_ileti SET gonderen_kutu='0',alan_kutu='0' WHERE cevap='$sec_ileti_sil' AND kimden='$kullanici_kim[kullanici_adi]' AND okunma_tarihi is null";
				$vtsorgu2 = "UPDATE $tablo_ozel_ileti SET gonderen_kutu='0',alan_kutu='0' WHERE cevap='$sec_ileti_sil' AND kimden='$kullanici_kim[kullanici_adi]'";
			}


			// okunmamış olanlar siliniyor ve sayısı alınıyor
			$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
			$silinen_oi = $vt->affected_rows();

			// okunmuş olanlar siliniyor
			$vtsonuc = $vt->query($vtsorgu2) or die ($vt->hata_ver());

			// üyenin okunmamış özel ileti sayısı düşürülüyor
			if ($kullanici_kim['okunmamis_oi'] != '0')
			{
				if (!isset($ozel_ileti['okunma_tarihi'])) $silinen_oi++;
				if ($kullanici_kim['okunmamis_oi'] < $silinen_oi) $silinen_oi = $kullanici_kim['okunmamis_oi'];

				$vtsorgu = "UPDATE $tablo_kullanicilar SET okunmamis_oi=okunmamis_oi-$silinen_oi WHERE id='$kullanici_kim[id]' LIMIT 1";
				$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
			}
		}

		// cevabı yoksa
		else
		{
			// ileti okunmadan siliniyorsa
			if ((!isset($ozel_ileti['okunma_tarihi'])) AND ($kullanici_kim['okunmamis_oi'] != '0'))
			{
				$vtsorgu = "UPDATE $tablo_kullanicilar SET okunmamis_oi=okunmamis_oi-1 WHERE id='$kullanici_kim[id]' LIMIT 1";
				$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
			}
		}
	}



	//  ALDIĞI ÖZEL İLETİ İSE   //

	elseif (($ozel_ileti['kime'] == $kullanici_kim['kullanici_adi']))
	{
		// iletiyi gönderen kişi de silmişse gerçekten sil
		if ($ozel_ileti['gonderen_kutu'] == '0'){
			$vtsorgu = "DELETE FROM $tablo_ozel_ileti WHERE id='$sec_ileti_sil' LIMIT 1";
			$csil = true;}

		// sadece alan kutusunu sıfırla
		else {$vtsorgu = "UPDATE $tablo_ozel_ileti SET alan_kutu='0' WHERE id='$sec_ileti_sil' LIMIT 1";
		$csil = false;}

		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


		// cevapları varsa siliniyor
		if ($ozel_ileti['cevap_sayi'] != '0')
		{
			if ($csil)
			{
				$vtsorgu = "DELETE FROM $tablo_ozel_ileti WHERE cevap='$sec_ileti_sil' AND okunma_tarihi is null";
				$vtsorgu2 = "DELETE FROM $tablo_ozel_ileti WHERE cevap='$sec_ileti_sil'";
			}
			else
			{
				$vtsorgu = "UPDATE $tablo_ozel_ileti SET gonderen_kutu=0,alan_kutu='0' WHERE cevap='$sec_ileti_sil' AND kimden='$kullanici_kim[kullanici_adi]' AND okunma_tarihi is null";
				$vtsorgu2 = "UPDATE $tablo_ozel_ileti SET gonderen_kutu=0,alan_kutu='0' WHERE cevap='$sec_ileti_sil'AND kimden='$kullanici_kim[kullanici_adi]'";
			}

			// okunmamış olanlar siliniyor ve sayısı alınıyor
			$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
			$silinen_oi = $vt->affected_rows();

			// okunmuş olanlar siliniyor
			$vtsonuc = $vt->query($vtsorgu2) or die ($vt->hata_ver());

			// üyenin okunmamış özel ileti sayısı düşürülüyor
			if ($kullanici_kim['okunmamis_oi'] != '0')
			{
				if (!isset($ozel_ileti['okunma_tarihi'])) $silinen_oi++;
				if ($kullanici_kim['okunmamis_oi'] < $silinen_oi) $silinen_oi = $kullanici_kim['okunmamis_oi'];

				$vtsorgu = "UPDATE $tablo_kullanicilar SET okunmamis_oi=okunmamis_oi-$silinen_oi WHERE id='$kullanici_kim[id]' LIMIT 1";
				$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
			}
		}

		// cevabı yoksa
		else
		{
			// ileti okunmadan siliniyorsa
			if ((!isset($ozel_ileti['okunma_tarihi'])) AND ($kullanici_kim['okunmamis_oi'] != '0'))
			{
				$vtsorgu = "UPDATE $tablo_kullanicilar SET okunmamis_oi=okunmamis_oi-1 WHERE id='$kullanici_kim[id]' LIMIT 1";
				$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
			}
		}
	}

	// silme yetkisi yoksa
	else
	{
		header('Location: hata.php?hata=69');
		exit();
	}
}


// gelinen sayfaya geri dönülüyor

if ($_POST['git'] == 'ozel_ileti') $git = 'ozel_ileti.php';
elseif ($_POST['git'] == 'ulasan') $git = 'ozel_ileti.php?kip=ulasan';
elseif ($_POST['git'] == 'gonderilen') $git = 'ozel_ileti.php?kip=gonderilen';
elseif ($_POST['git'] == 'kaydedilen') $git = 'ozel_ileti.php?kip=kaydedilen';
else $git = 'ozel_ileti.php';

header('Location: '.$git);
exit();


//  İLETİ SİLME İŞLEMLERİ - SONU  //




//  İLETİ KAYDETME İŞLEMLERİ - BAŞI //

elseif (isset($_POST['secili_kaydet'])):

// seçim yapılmamışsa
if (!isset($_POST['sec_ileti']))
{
	header('Location: hata.php?hata=68');
	exit();
}

$vtsonuc9 = $vt->query("SELECT id FROM $tablo_ozel_ileti WHERE kimden='$kullanici_kim[kullanici_adi]' AND gonderen_kutu='4' OR kime='$kullanici_kim[kullanici_adi]' AND alan_kutu='4'") or die ($vt->hata_ver());
$num_rows = $vt->num_rows($vtsonuc9);


// seçilen iletiler kaydedilen kutusundaki boşluktan fazla ise
if (($num_rows + count($_POST['sec_ileti'])) > $ayarlar['kaydedilen_kutu_kota'])
{
	header('Location: hata.php?hata=70');
	exit();
}


foreach ($_POST['sec_ileti'] as $sec_ileti_kaydet)
{
	$sec_ileti_kaydet = zkTemizle($sec_ileti_kaydet);

	$vtsorgu = "SELECT kime,kimden,alan_kutu,gonderen_kutu FROM $tablo_ozel_ileti WHERE id='$sec_ileti_kaydet' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$ozel_ileti = $vt->fetch_array($vtsonuc);


	// yolladığı bir özel ilet ise
	if (($ozel_ileti['kimden'] == $kullanici_kim['kullanici_adi']))
	{
		// kendine yolladığı özel ileti ise
		if ($ozel_ileti['kimden'] == $ozel_ileti['kime']){
			$vtsorgu = "UPDATE $tablo_ozel_ileti SET gonderen_kutu='4',alan_kutu='4' WHERE id='$sec_ileti_kaydet' LIMIT 1";
			$sorguek = "gonderen_kutu='4',alan_kutu='4'";}
		else{
			$vtsorgu = "UPDATE $tablo_ozel_ileti SET gonderen_kutu='4' WHERE id='$sec_ileti_kaydet' LIMIT 1";
			$sorguek = "gonderen_kutu='4',alan_kutu='4'";}
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


		// cevapları varsa kaydediliyor
		if ($ozel_ileti['cevap_sayi'] != '0')
		{
			$vtsorgu = "UPDATE $tablo_ozel_ileti SET $sorguek WHERE cevap='$sec_ileti_kaydet' AND kimden='$kullanici_kim[kullanici_adi]'";
			$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
		}
	}


	// aldığı özel ilet ise
	elseif (($ozel_ileti['kime'] == $kullanici_kim['kullanici_adi']))
	{
		$vtsorgu = "UPDATE $tablo_ozel_ileti SET alan_kutu='4' WHERE id='$sec_ileti_kaydet' LIMIT 1";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());

		// cevapları varsa kaydediliyor
		if ($ozel_ileti['cevap_sayi'] != '0')
		{
			$vtsorgu = "UPDATE $tablo_ozel_ileti SET gonderen_kutu='4',alan_kutu='4' WHERE cevap='$sec_ileti_kaydet' AND kimden='$kullanici_kim[kullanici_adi]'";
			$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
		}
	}


	// kaydetme yetkisi yoksa
	else
	{
		header('Location: hata.php?hata=71');
		exit();
	}
}


header('Location: ozel_ileti.php?kip=kaydedilen');
exit();

//  İLETİ KAYDETME İŞLEMLERİ - SONU //









// ÖZEL İLETİ KUTULARI GÖRÜTÜLENİYOR - BAŞI //
// ÖZEL İLETİ KUTULARI GÖRÜTÜLENİYOR - BAŞI //

elseif (isset($_GET['kip'])):



//  AYARLAR SAYFASI GÖRÜNTÜLENİYOR - BAŞI  //

if ($_GET['kip'] == 'ayarlar')
{
$sayfano = 24;
$sayfa_adi = 'Özel ileti Ayarları';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');


if ($kullanici_kim['okunmamis_oi']) $okunmamis_oi = ' ('.$kullanici_kim['okunmamis_oi'].')';
else $okunmamis_oi = '';


// kullanıcının engelleme girdileri çekiliyor
$vtsorgu = "SELECT * FROM $tablo_yasaklar WHERE etiket='$kullanici_kim[id]' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$satir = $vt->fetch_array($vtsonuc);


// engelleme tipi belirleniyor
if (isset($satir['tip']))
{
	$tip_hickimse = '';

	if ($satir['tip'] == '1') $tip_herkes = 'checked="checked"';
	else $tip_herkes = '';

	if ($satir['tip'] == '2') $tip_sadece = 'checked="checked"';
	else $tip_sadece = '';
}

else
{
	$tip_hickimse = 'checked="checked"';
	$tip_herkes = '';
	$tip_sadece = '';
}


$satir['deger'] = @str_replace(',', "\r\n", $satir['deger']);
$satir['deger'] = @preg_replace('|\r\n$|si','',$satir['deger']);


if ( (isset($_GET['kim'])) AND ($_GET['kim'] != '') )
{
	$_GET['kim'] = @zkTemizle($_GET['kim']);
	$_GET['kim'] = @zkTemizle4($_GET['kim']);

	if ( (isset($satir['deger'])) AND ($satir['deger'] != '') ) $engellenenler = $satir['deger']."\r\n".$_GET['kim'];
	else $engellenenler = $_GET['kim'];

	$euyari= '<br><br><br><p align="center"><font style="color: #FF6600; font-weight: bolder;">Önceki sayfadan tıkladığınız " <u>'.$_GET['kim'].'</u> " üye adı<br>aşağıdaki alana eklenmiştir.<br><br>Uygulamak için " *Sadece alttakileri engelle "<br>seçeneğini seçip "Değiştir" düğmesini tıklayın.</font></p>';
}

else
{
	if ( (isset($satir['deger'])) AND ($satir['deger'] != '') ) $engellenenler = $satir['deger'];
	else $engellenenler = '';
	$euyari= '';
}


$form_bilgi2 = '<form name="engelle" action="ozel_ileti.php" method="post">
<input type="hidden" name="kip" value="engel">';


// tema sınıfı örneği oluşturuluyor
$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/ozel_ileti.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


// duyuru varsa koşul 8 alanı tekli döngüye sokuluyor
if (isset($tekli2))
{
	$ornek1->kosul('8', array('' => ''), true);
	$ornek1->tekli_dongu('2',$tekli2);
	unset($tekli2);
}
else $ornek1->kosul('8', array('' => ''), false);


// tema uygulanıyor
$ornek1->kosul('5', array('' => ''), false);
$ornek1->kosul('6', array('' => ''), true);

$dongusuz = array('{FORM_BILGI2}' => $form_bilgi2,
'{OKUNMAMIS_OI}' => $okunmamis_oi,
'{EUYARI}' => $euyari,
'{TIP_HICKIMSE}' => $tip_hickimse,
'{TIP_HERKES}' => $tip_herkes,
'{TIP_SADECE}' => $tip_sadece,
'{ENGELLENENLER}' => $engellenenler);

$ornek1->dongusuz($dongusuz);
eval(TEMA_UYGULA);
exit();
}

//  AYARLAR SAYFASI GÖRÜNTÜLENİYOR - SONU  //





//  ULAŞAN KUTUSU GÖRÜNTÜLENİYOR - BAŞI  //

elseif ($_GET['kip'] == 'ulasan')
{
$sayfano = 25;
$sayfa_adi = 'Özel iletiler Ulaşan Kutusu';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');


//	 ULAŞAN İLETİLER OKUNMA TARİH SIRASINA GÖRE ÇEKİLİYOR	//
$vtsorgu = "SELECT id,kimden,kime,ozel_baslik,gonderme_tarihi,okunma_tarihi,cevap_sayi,cevap FROM $tablo_ozel_ileti WHERE
kimden='$kullanici_kim[kullanici_adi]' AND gonderen_kutu='2' AND cevap=0 OR
kime='$kullanici_kim[kullanici_adi]' AND alan_kutu='2' AND cevap_sayi!=0 ORDER BY okunma_tarihi DESC";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


//	ULAŞAN İLETİLERİN SAYISI ALINIYOR		//

$vtsonuc9 = $vt->query("SELECT id FROM $tablo_ozel_ileti WHERE kimden='$kullanici_kim[kullanici_adi]' AND gonderen_kutu='2' AND cevap=0 OR kime='$kullanici_kim[kullanici_adi]' AND alan_kutu='2' AND cevap_sayi!=0") or die ($vt->hata_ver());
$num_rows = $vt->num_rows($vtsonuc9);


// tema sınıfı örneği oluşturuluyor
$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/ozel_ileti.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


// duyuru varsa koşul 8 alanı tekli döngüye sokuluyor
if (isset($tekli2))
{
	$ornek1->kosul('8', array('' => ''), true);
	$ornek1->tekli_dongu('2',$tekli2);
	unset($tekli2);
}
else $ornek1->kosul('8', array('' => ''), false);


//	OZEL İLETİ YOKSA	//

if (!$vt->num_rows($vtsonuc9))
{
	$ornek1->kosul('1', array('{KUTU_BOS}' => 'Ulaşan Kutusunda hiç iletiniz yok.'), true);
	$ornek1->kosul('2', array('' => ''), false);
}


//	OZEL İLETİ VARSA	//

else
{
	$tablono = 1;

	$ornek1->kosul('2', array('' => ''), true);
	$ornek1->kosul('1', array('' => ''), false);


	while ($satir = $vt->fetch_array($vtsonuc))
	{
		// son cevap çekiliyor
		if ($satir['cevap_sayi'] != 0)
		{
			$vtsorgu = "SELECT id,kimden,kime,gonderme_tarihi,okunma_tarihi FROM $tablo_ozel_ileti WHERE cevap='$satir[id]' ORDER BY gonderme_tarihi DESC LIMIT 1";
			$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
			$satir2 = $vt->fetch_assoc($vtsonuc2);

			// son cevap kendinin değilse
			if ($satir2['kimden'] != $kullanici_kim['kullanici_adi'])
			{
				$oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_giden.png" alt="" title="Gönderilen Yanıtlanmış" width="26" height="26">';
				if ($satir2['okunma_tarihi']) $oi_okunma_tarihi = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir2['okunma_tarihi']);
				else $oi_okunma_tarihi = '<font size="3">-</font>';
				$oi_gonderme_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir2['gonderme_tarihi']);
				$oi_soncevap = '<a href="profil.php?kim='.$satir2['kimden'].'">'.$satir2['kimden'].'</a>';
				$oi_kime = '<a href="profil.php?kim='.$satir2['kime'].'">'.$satir2['kime'].'</a>';
			}

			else
			{
				$vtsorgu = "SELECT id,kimden,kime,gonderme_tarihi,okunma_tarihi FROM $tablo_ozel_ileti WHERE cevap='$satir[id]' AND kimden='$kullanici_kim[kullanici_adi]' ORDER BY gonderme_tarihi DESC LIMIT 1";
				$vtsonuc3 = $vt->query($vtsorgu) or die ($vt->hata_ver());
				$satir3 = $vt->fetch_assoc($vtsonuc3);

				if ($satir3['kimden'] == $kullanici_kim['kullanici_adi'])
					$oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_giden.png" alt="" title="Gönderilen Yanıtlanmış" width="26" height="26">';
				else $oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_gelen.png" alt="" title="Alınan Yanıtlanmış" width="26" height="26">';

				if ($satir2['okunma_tarihi']) $oi_okunma_tarihi = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir2['okunma_tarihi']);
				else $oi_okunma_tarihi = '<font size="3">-</font>';

				$oi_gonderme_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir3['gonderme_tarihi']);
				$oi_soncevap = '<a href="profil.php?kim='.$satir3['kimden'].'">'.$satir3['kimden'].'</a>';
				$oi_kime = '<a href="profil.php?kim='.$satir3['kime'].'">'.$satir3['kime'].'</a>';
			}
		}

		else
		{
			$oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_giden.png" alt="" title="Gönderilen" width="26" height="26">';
			$oi_gonderme_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['gonderme_tarihi']);
			$oi_okunma_tarihi = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['okunma_tarihi']);
			$oi_soncevap = '<font size="3">-</font>';
			$oi_kime = '<a href="profil.php?kim='.$satir['kime'].'">'.$satir['kime'].'</a>';
		}

		$oi_baslik = '<a href="oi_oku.php?oino='.$satir['id'].'">'.$satir['ozel_baslik'].'</a>';



		//	veriler tema motoruna yollanıyor	//
		$tekli1[] = array('{TABLO_NO}' => $tablono,
		'{OI_NO}' => $satir['id'],
		'{OI_SIMGE}' => $oi_simge,
		'{OZEL_ILET_BASLIK}' => $oi_baslik,
		'{OI_KIMDEN}' => $oi_kime,
		'{OI_TARIH1}' => $oi_gonderme_tarih,
		'{OI_CEVAP}' => $satir['cevap_sayi'],
		'{OI_TARIH2}' => $oi_soncevap,
		'{OI_SONCEVAP}' => $oi_okunma_tarihi);

		$tablono++;
	}
}


//	DOLULUK ORANI YÜZDESİ HESAPLANIYOR	//

if ($num_rows != 0)
{
	$doluluk_orani = 100 / ($ayarlar['ulasan_kutu_kota'] / $num_rows);
	settype($doluluk_orani,'integer');
	if ($doluluk_orani > 100) $doluluk_orani = 100;
}

else $doluluk_orani = 1;


$form_bilgi = '<form name="secim_formu" action="ozel_ileti.php" method="post">
<input type="hidden" name="git" value="ulasan">';

$kutu_aciklama = 'Yolladığınız iletiler gönderilen tarafından okunduğunda buraya taşınır.
<br>İletinin okunma tarihini yukarıda görebilirsiniz.';



//	TEMA UYGULANIYOR	//

$ornek1->kosul('6', array('' => ''), false);
$ornek1->kosul('5', array('' => ''), true);
$ornek1->kosul('7', array('' => ''), true);

if (isset($tekli1)) $ornek1->tekli_dongu('1',$tekli1);

$ornek1->dongusuz(array('{JAVASCRIPT_KODU}' => $javascript_kodu2,
'{KUTU_KOTA}' => $ayarlar['ulasan_kutu_kota'],
'{DOLULUK}' => $num_rows,
'{OZEL_ILETI_GONDER}' => $oi_rengi,
'{DOLULUK_ORANI}' => $doluluk_orani,
'{FORM_BILGI}' => $form_bilgi,
'{KIMDEN_KIME}' => 'Gönderilen',
'{GELEN_KUTUSU}' => 'Gelen Kutusu',
'{ULASAN_KUTUSU}' => 'Ulaşan Kutusu',
'{GONDERILEN_KUTUSU}' => 'Gönderilen Kutusu',
'{KAYDEDILEN_KUTUSU}' => 'Kaydedilen Kutusu',
'{GELEN_KUTUSU_BAG}' => '<a href="ozel_ileti.php">',
'{GELEN_KUTUSU_BAG2}' => '</a>',
'{ULASAN_KUTUSU_BAG}' => '',
'{ULASAN_KUTUSU_BAG2}' => '',
'{GONDERILEN_KUTUSU_BAG}' => '<a href="ozel_ileti.php?kip=gonderilen">',
'{GONDERILEN_KUTUSU_BAG2}' => '</a>',
'{KAYDEDILEN_KUTUSU_BAG}' => '<a href="ozel_ileti.php?kip=kaydedilen">',
'{KAYDEDILEN_KUTUSU_BAG2}' => '</a>',
'{TARIH_ALAN1}' => 'Gönderme Tarihi',
'{TARIH_ALAN2}' => 'Son Cevap',
'{SON_CEVAP}' => 'Okunma Tarihi',
'{KUTU_ACIKLAMA}' => $kutu_aciklama));

eval(TEMA_UYGULA);
exit();
}

//  ULAŞAN KUTUSU GÖRÜNTÜLENİYOR - SONU  //





//  GÖNDERİLEN KUTUSU GÖRÜNTÜLENİYOR - BAŞI  //

elseif ($_GET['kip'] == 'gonderilen')
{
$sayfano = 26;
$sayfa_adi = 'Özel iletiler Gönderilen Kutusu';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');


//	GÖNDERİLEN İLETİLER TARİH SIRASINA GÖRE ÇEKİLİYOR	//

$vtsorgu = "SELECT id,ozel_baslik,kimden,kime,gonderme_tarihi,cevap_sayi,cevap FROM $tablo_ozel_ileti WHERE
kimden='$kullanici_kim[kullanici_adi]' AND gonderen_kutu='3' ORDER BY gonderme_tarihi DESC";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


//	GÖNDERİLEN İLETİLERİN SAYISI ALINIYOR		//

$vtsonuc9 = $vt->query("SELECT id FROM $tablo_ozel_ileti WHERE kimden='$kullanici_kim[kullanici_adi]' AND gonderen_kutu='3'") or die ($vt->hata_ver());
$num_rows = $vt->num_rows($vtsonuc9);


// tema sınıfı örneği oluşturuluyor
$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/ozel_ileti.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


// duyuru varsa koşul 8 alanı tekli döngüye sokuluyor
if (isset($tekli2))
{
	$ornek1->kosul('8', array('' => ''), true);
	$ornek1->tekli_dongu('2',$tekli2);
	unset($tekli2);
}
else $ornek1->kosul('8', array('' => ''), false);


//	OZEL İLETİ YOKSA	//

if (!$vt->num_rows($vtsonuc9))
{
	$ornek1->kosul('1', array('{KUTU_BOS}' => 'Gönderilen Kutusunda hiç iletiniz yok.'), true);
	$ornek1->kosul('2', array('' => ''), false);
}


//	OZEL İLETİ VARSA	//

else
{
	$tablono = 1;

	$ornek1->kosul('2', array('' => ''), true);
	$ornek1->kosul('1', array('' => ''), false);


	while ($satir = $vt->fetch_array($vtsonuc))
	{
		// cevapsa konusu çekiliyor
		if ($satir['cevap'] != 0)
		{
			$oi_soncevap = '<a href="profil.php?kim='.$satir['kimden'].'">'.$satir['kimden'].'</a>';

			$vtsorgu = "SELECT id,ozel_baslik,kime,kimden,gonderme_tarihi,cevap_sayi,cevap FROM $tablo_ozel_ileti WHERE id='$satir[cevap]' LIMIT 1";
			$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
			$satir2 = $vt->fetch_assoc($vtsonuc2);

			$oi_no = $satir2['id'];
			$cevap_sayi = $satir2['cevap_sayi'];
			$oi_baslik = '<a href="oi_oku.php?oino='.$satir2['id'].'">'.$satir2['ozel_baslik'].'</a>';
			$oi_kime = '<a href="profil.php?kim='.$satir['kime'].'">'.$satir['kime'].'</a>';
			$oi_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir2['gonderme_tarihi']);
			$oi_tarih2 = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['gonderme_tarihi']);


			if ($satir2['kimden'] == $kullanici_kim['kullanici_adi'])
				$oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_giden.png" alt="" title="Gönderilen Yanıtlanmış" width="26" height="26">';
			else $oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_gelen.png" alt="" title="Alınan Yanıtlanmış" width="26" height="26">';
		}

		else
		{
			$oi_no = $satir['id'];
			$cevap_sayi = $satir['cevap_sayi'];
			$oi_baslik = '<a href="oi_oku.php?oino='.$satir['id'].'">'.$satir['ozel_baslik'].'</a>';
			$oi_kime = '<a href="profil.php?kim='.$satir['kime'].'">'.$satir['kime'].'</a>';
			$oi_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['gonderme_tarihi']);
			$oi_tarih2 = '<font size="3">-</font>';
			$oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_giden.png" alt="" title="Gönderilen" width="26" height="26">';
			$oi_soncevap = '<font size="3">-</font>';
		}


		//	veriler tema motoruna yollanıyor	//
		$tekli1[] = array('{TABLO_NO}' => $tablono,
		'{OI_NO}' => $oi_no,
		'{OI_SIMGE}' => $oi_simge,
		'{OZEL_ILET_BASLIK}' => $oi_baslik,
		'{OI_KIMDEN}' => $oi_kime,
		'{OI_CEVAP}' => $cevap_sayi,
		'{OI_TARIH1}' => $oi_tarih,
		'{OI_TARIH2}' => $oi_tarih2,
		'{OI_SONCEVAP}' => $oi_soncevap);

		$tablono++;
	}
}


$form_bilgi = '<form name="secim_formu" action="ozel_ileti.php" method="post">
<input type="hidden" name="git" value="gonderilen">';

$kutu_aciklama = 'Gönderdiğiniz kişi tarafından henüz okunmayan iletiler burada bulunur,
<br>gönderilen tarafından okunduklarında Ulaşan Kutusuna taşınır.';



//	TEMA UYGULANIYOR	//

$ornek1->kosul('6', array('' => ''), false);
$ornek1->kosul('5', array('' => ''), true);
$ornek1->kosul('7', array('' => ''), true);

if (isset($tekli1)) $ornek1->tekli_dongu('1',$tekli1);

$ornek1->dongusuz(array('{JAVASCRIPT_KODU}' => $javascript_kodu2,
'{KUTU_KOTA}' => '&#8734;',
'{DOLULUK}' => $num_rows,
'{OZEL_ILETI_GONDER}' => $oi_rengi,
'{DOLULUK_ORANI}' => '0',
'{FORM_BILGI}' => $form_bilgi,
'{KIMDEN_KIME}' => 'Gönderilen',
'{GELEN_KUTUSU}' => 'Gelen Kutusu',
'{ULASAN_KUTUSU}' => 'Ulaşan Kutusu',
'{GONDERILEN_KUTUSU}' => 'Gönderilen Kutusu',
'{KAYDEDILEN_KUTUSU}' => 'Kaydedilen Kutusu',
'{GELEN_KUTUSU_BAG}' => '<a href="ozel_ileti.php">',
'{GELEN_KUTUSU_BAG2}' => '</a>',
'{ULASAN_KUTUSU_BAG}' => '<a href="ozel_ileti.php?kip=ulasan">',
'{ULASAN_KUTUSU_BAG2}' => '</a>',
'{GONDERILEN_KUTUSU_BAG}' => '',
'{GONDERILEN_KUTUSU_BAG2}' => '',
'{KAYDEDILEN_KUTUSU_BAG}' => '<a href="ozel_ileti.php?kip=kaydedilen">',
'{KAYDEDILEN_KUTUSU_BAG2}' => '</a>',
'{TARIH_ALAN1}' => 'Gönderme Tarihi',
'{TARIH_ALAN2}' => 'Cevap Tarihi',
'{SON_CEVAP}' => 'Son Cevap',
'{KUTU_ACIKLAMA}' => $kutu_aciklama));

eval(TEMA_UYGULA);
exit();
}

//  GÖNDERİLEN KUTUSU GÖRÜNTÜLENİYOR - SONU  //





//  KAYDEDİLEN KUTUSU GÖRÜNTÜLENİYOR - BAŞI  //

elseif ($_GET['kip'] == 'kaydedilen')
{
$sayfano = 27;
$sayfa_adi = 'Özel iletiler Kaydedilen Kutusu';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');


//	KAYDEDİLEN İLETİLER TARİH SIRASINA GÖRE ÇEKİLİYOR	//

$vtsorgu = "SELECT * FROM $tablo_ozel_ileti WHERE
kimden='$kullanici_kim[kullanici_adi]' AND gonderen_kutu='4' AND cevap='0' OR
kime='$kullanici_kim[kullanici_adi]' AND alan_kutu='4' AND cevap='0' ORDER BY gonderme_tarihi DESC";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


//	KAYDEDİLEN İLETİLERİN SAYISI ALINIYOR		//

$vtsonuc9 = $vt->query("SELECT id FROM $tablo_ozel_ileti WHERE kimden='$kullanici_kim[kullanici_adi]' AND gonderen_kutu='4' OR kime='$kullanici_kim[kullanici_adi]' AND alan_kutu='4' AND cevap='0'") or die ($vt->hata_ver());
$num_rows = $vt->num_rows($vtsonuc9);


// tema sınıfı örneği oluşturuluyor
$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/ozel_ileti.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


// duyuru varsa koşul 8 alanı tekli döngüye sokuluyor
if (isset($tekli2))
{
	$ornek1->kosul('8', array('' => ''), true);
	$ornek1->tekli_dongu('2',$tekli2);
	unset($tekli2);
}
else $ornek1->kosul('8', array('' => ''), false);


//	OZEL İLETİ YOKSA	//

if (!$vt->num_rows($vtsonuc9))
{
	$ornek1->kosul('1', array('{KUTU_BOS}' => 'Kaydedilen Kutusunda hiç iletiniz yok.'), true);
	$ornek1->kosul('2', array('' => ''), false);
}


//	OZEL İLETİ VARSA	//

else
{
	$tablono = 1;

	$ornek1->kosul('2', array('' => ''), true);
	$ornek1->kosul('1', array('' => ''), false);


	while ($satir = $vt->fetch_array($vtsonuc))
	{
		$oi_baslik = '<a href="oi_oku.php?oino='.$satir['id'].'">'.$satir['ozel_baslik'].'</a>';
		$oi_kimden = '<a href="profil.php?kim='.$satir['kimden'].'">'.$satir['kimden'].'</a>';
		$oi_kime = '<a href="profil.php?kim='.$satir['kime'].'">'.$satir['kime'].'</a>';
		$oi_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['gonderme_tarihi']);

		// yanıtlanmışsa
		if ($satir['cevap_sayi'] != '0')
		{
			if ($satir['kimden'] == $kullanici_kim['kullanici_adi'])
				$oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_giden.png" alt="" title="Gönderilen Yanıtlanmış" width="26" height="26">';
			else $oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_gelen.png" alt="" title="Alınan Yanıtlanmış" width="26" height="26">';
			$oi_soncevap = '<a href="profil.php?kim='.$satir['kimden'].'">'.$satir['kimden'].'</a>';
		}

		else
		{
			if ($satir['kimden'] == $kullanici_kim['kullanici_adi'])
				$oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_giden.png" alt="" title="Gönderilen" width="26" height="26">';
			else $oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_gelen.png" alt="" title="Alınan" width="26" height="26">';
			$oi_soncevap = '';
		}


		//	veriler tema motoruna yollanıyor	//
		$tekli1[] = array('{TABLO_NO}' => $tablono,
		'{OI_NO}' => $satir['id'],
		'{OI_SIMGE}' => $oi_simge,
		'{OZEL_ILET_BASLIK}' => $oi_baslik,
		'{OI_KIMDEN}' => $oi_kimden,
		'{OI_TARIH1}' => $oi_kime,
		'{OI_CEVAP}' => $satir['cevap_sayi'],
		'{OI_TARIH2}' => $oi_tarih,
		'{OI_SONCEVAP}'=> $oi_soncevap);
		$tablono++;
	}
}


//	DOLULUK ORANI YÜZDESİ HESAPLANIYOR	//

if ($num_rows != 0)
{
	$doluluk_orani = 100 / ($ayarlar['kaydedilen_kutu_kota'] / $num_rows);
	settype($doluluk_orani,'integer');
	if ($doluluk_orani > 100) $doluluk_orani = 100;
}

else $doluluk_orani = 1;


$form_bilgi = '<form name="secim_formu" action="ozel_ileti.php" method="post">
<input type="hidden" name="git" value="kaydedilen">';



//	TEMA UYGULANIYOR	//

$ornek1->kosul('6', array('' => ''), false);
$ornek1->kosul('5', array('' => ''), true);
$ornek1->kosul('7', array('' => ''), false);

if (isset($tekli1)) $ornek1->tekli_dongu('1',$tekli1);

$ornek1->dongusuz(array('{JAVASCRIPT_KODU}' => $javascript_kodu2,
'{KUTU_KOTA}' => $ayarlar['kaydedilen_kutu_kota'],
'{DOLULUK}' => $num_rows,
'{OZEL_ILETI_GONDER}' => $oi_rengi,
'{DOLULUK_ORANI}' => $doluluk_orani,
'{FORM_BILGI}' => $form_bilgi,
'{KIMDEN_KIME}' => 'Gönderen',
'{GELEN_KUTUSU}' => 'Gelen Kutusu',
'{ULASAN_KUTUSU}' => 'Ulaşan Kutusu',
'{GONDERILEN_KUTUSU}' => 'Gönderilen Kutusu',
'{KAYDEDILEN_KUTUSU}' => 'Kaydedilen Kutusu',
'{GELEN_KUTUSU_BAG}' => '<a href="ozel_ileti.php">',
'{GELEN_KUTUSU_BAG2}' => '</a>',
'{ULASAN_KUTUSU_BAG}' => '<a href="ozel_ileti.php?kip=ulasan">',
'{ULASAN_KUTUSU_BAG2}' => '</a>',
'{GONDERILEN_KUTUSU_BAG}' => '<a href="ozel_ileti.php?kip=gonderilen">',
'{GONDERILEN_KUTUSU_BAG2}' => '</a>',
'{KAYDEDILEN_KUTUSU_BAG}' => '',
'{KAYDEDILEN_KUTUSU_BAG2}' => '',
'{TARIH_ALAN1}' => 'Alan',
'{TARIH_ALAN2}' => 'Gönderme Tarihi',
'{SON_CEVAP}' => 'Son Cevap',
'{KUTU_ACIKLAMA}' => ''));

eval(TEMA_UYGULA);
exit();
}
$gec = '';

//  KAYDEDİLEN KUTUSU GÖRÜNTÜLENİYOR - SONU  //





//  GELEN KUTUSU GÖRÜNTÜLENİYOR - BAŞI  //

else:

$sayfano = 28;
$sayfa_adi = 'Özel iletiler Gelen Kutusu';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');


// tema sınıfı örneği oluşturuluyor
$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/ozel_ileti.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


// duyuru varsa koşul 8 alanı tekli döngüye sokuluyor
if (isset($tekli2))
{
	$ornek1->kosul('8', array('' => ''), true);
	$ornek1->tekli_dongu('2',$tekli2);
	unset($tekli2);
}
else $ornek1->kosul('8', array('' => ''), false);




//  ÖZEL İLETİLERİN SAYISI ALINIYOR //
$vtsonuc9 = $vt->query("SELECT id FROM $tablo_ozel_ileti WHERE kime='$kullanici_kim[kullanici_adi]' AND alan_kutu='1' AND cevap=0 OR kimden='$kullanici_kim[kullanici_adi]' AND gonderen_kutu!='0' AND gonderen_kutu!='4' AND cevap_sayi!=0") or die ($vt->hata_ver());
$num_rows = $vt->num_rows($vtsonuc9);

$vtsonuc92 = $vt->query("SELECT id FROM $tablo_ozel_ileti WHERE kimden='$kullanici_kim[kullanici_adi]' AND gonderen_kutu='2' AND cevap!=0") or die ($vt->hata_ver());
$num_rows2 = $vt->num_rows($vtsonuc92);


//  OZEL İLETİ YOKSA    //

if (!$vt->num_rows($vtsonuc9))
{
	$ornek1->kosul('1', array('{KUTU_BOS}' => 'Gelen Kutusunda hiç iletiniz yok.'), true);
	$ornek1->kosul('2', array('' => ''), false);
}


//  OZEL İLETİ VARSA    //

else
{
	// ÖZEL İLETİLER TARİH SIRASINA GÖRE ÇEKİLİYOR //
	$vtsorgu = "SELECT id,ozel_baslik,kimden,gonderme_tarihi,okunma_tarihi,cevap_sayi,cevap FROM $tablo_ozel_ileti WHERE
	kime='$kullanici_kim[kullanici_adi]' AND alan_kutu='1' AND cevap=0 OR
	kimden='$kullanici_kim[kullanici_adi]' AND gonderen_kutu!='0' AND gonderen_kutu!='4' AND cevap_sayi!=0
	ORDER BY gonderme_tarihi DESC";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$tablono = 1;


	while ($satir = $vt->fetch_assoc($vtsonuc))
	{
		// gelen özel ileti cevapsa konusu çekiliyor
		if ($satir['cevap'] != 0)
		{
			// konunun bilgileri depolanıyor
			$gonderme_tarihi = $satir['gonderme_tarihi'];
			$kimden = $satir['kimden'];
			$oi_tarih2 = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['gonderme_tarihi']);

			$vtsorgu = "SELECT id,ozel_baslik,okunma_tarihi,cevap_sayi,cevap FROM $tablo_ozel_ileti WHERE id='$satir[cevap]' LIMIT 1";
			$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
			$satir = $vt->fetch_assoc($vtsonuc2);

			// konunun bilgileri geri yükleniyor
			$oi_soncevap = '<a href="profil.php?kim='.$satir['kimden'].'">'.$satir['kimden'].'</a>';
			$satir['kimden'] = $kimden;
			$satir['gonderme_tarihi'] = $gonderme_tarihi;
			$oi_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['gonderme_tarihi']);
			$oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_gelen.png" alt="" title="Okunmuş" width="26" height="26">';
		}


		// kendi yolladığı cevaplanmışsa son cevabı çekiliyor
		elseif ($satir['cevap_sayi'] != 0)
		{
			// konunun bilgileri depolanıyor
			$ozel_id = $satir['id'];
			$kimden = $satir['kimden'];
			$ozel_baslik = $satir['ozel_baslik'];
			$cevap_sayi = $satir['cevap_sayi'];
			$oi_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['gonderme_tarihi']);

			$vtsorgu = "SELECT id,kimden,gonderme_tarihi,okunma_tarihi,cevap_sayi,cevap FROM $tablo_ozel_ileti WHERE cevap='$satir[id]' ORDER BY gonderme_tarihi DESC LIMIT 1";
			$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
			$satir = $vt->fetch_assoc($vtsonuc2);

			if ($satir['kimden'] == $kullanici_kim['kullanici_adi']) $satir['okunma_tarihi'] = 1;
			$oi_soncevap = '<a href="profil.php?kim='.$satir['kimden'].'">'.$satir['kimden'].'</a>';
			$oi_tarih2 = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['gonderme_tarihi']);


			// konunun bilgileri geri yükleniyor
			$satir['id'] = $ozel_id;
			$satir['kimden'] = $kimden;
			$satir['ozel_baslik'] = $ozel_baslik;
			$satir['cevap_sayi'] = $cevap_sayi;

			if ($satir['kimden'] == $kullanici_kim['kullanici_adi'])
				$oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_giden.png" alt="" title="Gönderilen Yanıtlanmış" width="26" height="26">';
			else $oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_gelen.png" alt="" title="Alınan Yanıtlanmış" width="26" height="26">';
		}

		else
		{
			if (!isset($satir['okunma_tarihi']))
				$oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_kapali.png" alt="" title="Okunmamış" width="26" height="26">';
			else $oi_simge = '<img src="phpkf-dosyalar/resimler/oi_simge/oi_acik.png" alt="" title="Okunmuş" width="26" height="26">';

			$oi_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $satir['gonderme_tarihi']);
			$oi_tarih2 = '<font size="3">-</font>';
			$oi_soncevap = '<font size="3">-</font>';
		}


		// okunmamış iletiler kalın yazdırılıyor
		$oi_baslik = '<a href="oi_oku.php?oino='.$satir['id'].'">';
		if (!isset($satir['okunma_tarihi'])) $oi_baslik .= '<b>'.$satir['ozel_baslik'].'</b></a>';
		else $oi_baslik .= $satir['ozel_baslik'].'</a>';

		$oi_kimden = '<a href="profil.php?kim='.$satir['kimden'].'">'.$satir['kimden'].'</a>';


		// veriler tema motoruna yollanıyor
		$tekli1[] = array('{TABLO_NO}' => $tablono,
		'{OI_NO}' => $satir['id'],
		'{OI_SIMGE}' => $oi_simge,
		'{OZEL_ILET_BASLIK}' => $oi_baslik,
		'{OI_KIMDEN}' => $oi_kimden,
		'{OI_CEVAP}' => $satir['cevap_sayi'],
		'{OI_TARIH1}' => $oi_tarih,
		'{OI_TARIH2}' => $oi_tarih2,
		'{OI_SONCEVAP}'=> $oi_soncevap);

		$tablono++;
	}

	$ornek1->kosul('2', array('' => ''), true);
	$ornek1->kosul('1', array('' => ''), false);
}




//  DOLULUK ORANI YÜZDESİ HESAPLANIYOR  //

if ($num_rows != 0)
{
	$num_rows += $num_rows2;
	$doluluk_orani = 100 / ($ayarlar['gelen_kutu_kota'] / $num_rows);
	settype($doluluk_orani,'integer');
	if ($doluluk_orani > 100) $doluluk_orani = 100;
}

else $doluluk_orani = 1;


$form_bilgi = '<form name="secim_formu" action="ozel_ileti.php" method="post">
<input type="hidden" name="git" value="ozel_ileti">';



//  TEMA UYGULANIYOR    //

$ornek1->kosul('6', array('' => ''), false);
$ornek1->kosul('5', array('' => ''), true);
$ornek1->kosul('7', array('' => ''), true);

if (isset($tekli1)) $ornek1->tekli_dongu('1',$tekli1);

$ornek1->dongusuz(array('{JAVASCRIPT_KODU}' => $javascript_kodu2,
'{KUTU_KOTA}' => $ayarlar['gelen_kutu_kota'],
'{DOLULUK}' => $num_rows,
'{OZEL_ILETI_GONDER}' => $oi_rengi,
'{DOLULUK_ORANI}' => $doluluk_orani,
'{FORM_BILGI}' => $form_bilgi,
'{KIMDEN_KIME}' => 'Gönderen',
'{GELEN_KUTUSU}' => 'Gelen Kutusu',
'{ULASAN_KUTUSU}' => 'Ulaşan Kutusu',
'{GONDERILEN_KUTUSU}' => 'Gönderilen Kutusu',
'{KAYDEDILEN_KUTUSU}' => 'Kaydedilen Kutusu',
'{GELEN_KUTUSU_BAG}' => '',
'{GELEN_KUTUSU_BAG2}' => '',
'{ULASAN_KUTUSU_BAG}' => '<a href="ozel_ileti.php?kip=ulasan">',
'{ULASAN_KUTUSU_BAG2}' => '</a>',
'{GONDERILEN_KUTUSU_BAG}' => '<a href="ozel_ileti.php?kip=gonderilen">',
'{GONDERILEN_KUTUSU_BAG2}' => '</a>',
'{KAYDEDILEN_KUTUSU_BAG}' => '<a href="ozel_ileti.php?kip=kaydedilen">',
'{KAYDEDILEN_KUTUSU_BAG2}' => '</a>',
'{TARIH_ALAN1}' => 'Gönderme Tarihi',
'{TARIH_ALAN2}' => 'Cevap Tarihi',
'{SON_CEVAP}' => 'Son Cevap',
'{KUTU_ACIKLAMA}' => ''));

eval(TEMA_UYGULA);


// Gelen kutusu dolu uyarısı

if ($ayarlar['gelen_kutu_kota'] <= $num_rows)
{
echo '<script type="text/javascript">
<!-- 
alert(\'Gelen Kutusu Tam Dolu !\\nTekrar özel ileti alabilmek için gelen kutusunu boşaltın.\')
//  -->
</script>';
}


//  NORMAL SAYFA GÖRÜNTÜLENİYOR - SONU  //



// ÖZEL İLETİ KUTULARI GÖRÜTÜLENİYOR - SONU //
// ÖZEL İLETİ KUTULARI GÖRÜTÜLENİYOR - SONU //

endif;
?>