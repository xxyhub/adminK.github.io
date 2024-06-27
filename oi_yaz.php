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


$phpkf_ayarlar_kip = "WHERE kip='1' OR kip='6'";
if (!defined('DOSYA_AYAR')) include 'ayar.php';


// özel ileti özelliği kapalı ise uyarı veriliyor
if ($ayarlar['o_ileti'] == 0)
{
	header('Location: hata.php?uyari=2');
	exit();
}


if (isset($_POST['mesaj_onizleme']))
{
	$sayfano = 22;
	$sayfa_adi = 'Özel ileti Önizlemesi';
}
else
{
	$sayfano = 23;
	$sayfa_adi = 'Özel ileti Yazma';
}


if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';
if (!defined('DOSYA_GUVENLIK')) include 'phpkf-bilesenler/guvenlik.php';




//  ÜYE ARAMA - BAŞI  //

if (isset($_GET['uye_ara']))
{
	// veri temizleniyor
	$_GET['uye_ara'] = @zkTemizle($_GET['uye_ara']);
	$_GET['uye_ara'] = @zkTemizle4($_GET['uye_ara']);


	if (isset($_GET['kip']))
	{
		if ($_GET['kip'] == '1')
		{
			$formadi = 'duzenleyici_form';
			$formkime = 'ozel_kime';
			$sayfano = 0;
			$sayfa_adi = 'Özel ileti Üye Arama';
		}
		elseif ($_GET['kip'] == '2')
		{
			$formadi = 'kul_izinleri';
			$formkime = 'kim';
			$sayfano = 0;
			$sayfa_adi = 'Yönetim Üye Arama';
		}
	}

	else
	{
		$formadi = 'duzenleyici_form';
		$formkime = 'ozel_kime';
		$_GET['kip'] = '1';
		$sayfano = 0;
		$sayfa_adi = 'Özel ileti Üye Arama';
	}


	if (!defined('DOSYA_OTURUM')) include 'phpkf-bilesenler/oturum.php';


	echo '<center><font style="font-family:verdana;font-weight:bold;font-size:18px;">- ÜYE ARAMA -</font><br><br>
	<form action="oi_yaz.php" method="get" name="ozel_uye">
	<input type="hidden" name="kip" value="'.$_GET['kip'].'">
	<b>Üye:&nbsp;</b> <input type="text" name="uye_ara" size="25" maxlength="20" value="'.$_GET['uye_ara'].'"> &nbsp; <input name="ara" type="submit" value="Ara"></center>';


	// boş ise
	if ($_GET['uye_ara'] == '') echo '<center><br>Başta joker olarak * kullanabilirsiniz. <br>Sona joker girmeye gerek yoktur, var kabul edilir.<br>Joker hariç en az 2, en çok 20 karakter girebilirsiniz.<br><br><a href="javascript:window.close()">Kapat</a></center>';


	// 20 karakterden uzunsa
	elseif (strlen($_GET['uye_ara']) > 20)
	{
		echo '<center><br><font color="#ff0000"><b>20 karakterden fazla giremezsiniz !</b></font><br><br><a href="oi_yaz.php?uye_ara=&amp;kip=1">Geri</a></center>';
		exit();
	}


	// geçersiz karakterler varsa
	elseif (!preg_match('/^[A-Za-z0-9-_ğĞüÜŞşİıÖöÇç.*]+$/', $_GET['uye_ara']))
	{
		echo '<center><br><font color="#ff0000"><b>Geçersiz karakter !</b></font><br><br><a href="oi_yaz.php?uye_ara=&amp;kip=1">Geri</a></center>';
		exit();
	}


	// sorun yok ise aramaya başlanıyor
	else
	{
		if (strlen(@str_replace('*','',trim($_GET['uye_ara']))) < 2)
		{
			echo '<center><br><font color="#ff0000"><b>Üye arama için en az iki harf girmelisiniz !</b></font><br><br><a href="javascript:window.close()">Kapat</a></center>';
			exit();
		}
		$_GET['uye_ara'] = @str_replace('*','%',trim($_GET['uye_ara']));


		// üyeler aranıyor
		$vtsorgu = "SELECT id,kullanici_adi FROM $tablo_kullanicilar WHERE engelle='0' AND kul_etkin='1' AND kullanici_adi LIKE '$_GET[uye_ara]%' LIMIT 0,20";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


		if (!$vt->num_rows($vtsonuc))
		{
			echo '<center><br><b>Aradığınız koşula uyan herhangi bir üye bulunamadı !</b><br><br>
			<a href="javascript:window.close()">Kapat</a></center>';
		}


		else
		{
			echo '<p align="center"><b>İstediğiniz üye adının üzerine tıklayın.</b><p>';

			$sayi = 0;
			while($uyeler = $vt->fetch_assoc($vtsonuc))
			{
				$sayi++;
				echo $sayi.')&nbsp; <a href="javascript:void(0);" onclick="opener.document.forms[\''.$formadi.'\'].'.$formkime.'.value=\''.$uyeler['kullanici_adi'].'\'; window.close()">'.$uyeler['kullanici_adi'].'</a><br>';
			}

			if ($sayi>19) echo '<br>Çok fazla sonuç bulundu, sadece 20 tanesi gösteriliyor. Arama sözcüğünü değiştirin veya aradığınız üyeyi <a href="uyeler.php" target="_blank"><b>üyeler</b></a> sayfasından bulun.';
		}
	}

	echo '<script type="text/javascript"><!-- //
	document.ozel_uye.uye_ara.focus();
	// --></script>';

	exit();
}

//  ÜYE ARAMA - SONU  //



$renkver = 1;
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');


if (isset($_POST['ozel_kime'])) $ozel_kime = @zkTemizle($_POST['ozel_kime']);
elseif (isset($_GET['ozel_kime'])) $ozel_kime = @zkTemizle($_GET['ozel_kime']);
elseif (isset($_POST['ozel_yanitla'])) 
{
	$_POST['oino'] = @zkTemizle($_POST['oino']);
	$ozel_kime = @zkTemizle($_POST['kime']);
}
else $ozel_kime = '';
$ozel_kime = @zkTemizle4($ozel_kime);





//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/mesaj_yaz.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));





			//   ÖNİZLEME TABLOSU BAŞI   //


if (isset($_POST['mesaj_onizleme'])):

if (!isset($_POST['mesaj_icerik'])):
	$javascript_kapali = '<center><br><b><font size="3" color="red">Önizleme özelliği için taraycınızın java özelliğinin açık olması gereklidir.</b></center><br>';

else:

$javascript_kapali = '';


//	ZARARLI KODLAR TEMİZLENİYOR	//
//	magic_quotes_gpc açıksa	//

if (get_magic_quotes_gpc())
{
	$_POST['mesaj_baslik'] = @ileti_yolla(stripslashes($_POST['mesaj_baslik']),3);
	$_POST['mesaj_icerik'] = @ileti_yolla(stripslashes($_POST['mesaj_icerik']),5);
}


//	magic_quotes_gpc kapalıysa	//
else
{
	$_POST['mesaj_baslik'] = @ileti_yolla($_POST['mesaj_baslik'],3);
	$_POST['mesaj_icerik'] = @ileti_yolla($_POST['mesaj_icerik'],5);
}



if (isset($ozel_kime)) $onizleme_kime = $ozel_kime;
else $onizleme_kime = '';

$onizleme_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, time());
$onizleme_mesaj = $_POST['mesaj_icerik'];

if (isset($_POST['ifade'])) $onizleme_mesaj = ifadeler($onizleme_mesaj);
if ((isset($_POST['bbcode_kullan'])) AND ($ayarlar['bbcode'] == 1)) $onizleme_icerik = bbcode_acik($onizleme_mesaj,1);
else $onizleme_icerik = bbcode_kapali($onizleme_mesaj);



//	veriler tema motoruna yollanıyor	//

$ornek1->kosul('3', array('{ONIZLEME_KIMDEN}' => $kullanici_kim['kullanici_adi'],
'{ONIZLEME_KIME}' => $onizleme_kime,
'{ONIZLEME_BASLIK}' => $_POST['mesaj_baslik'],
'{ONIZLEME_TARIH}' => $onizleme_tarih,
'{ONIZLEME_ICERIK}' => $onizleme_icerik), true);


endif;

else: $ornek1->kosul('3', array('' => ''), false);

endif;



			//   ÖNİZLEME TABLOSU SONU   //







if (isset($_POST['mesaj_baslik']) ) $form_baslik = $_POST['mesaj_baslik'];
elseif (isset($_POST['ozel_yanitla'])) $form_baslik = 'Cvp: ';
else $form_baslik = '';


if (isset($_POST['mesaj_icerik'])) $form_icerik = $_POST['mesaj_icerik'];
else $form_icerik = '';


//  BBCODE AÇMA - KAPATMA    //

$form_ozellik = '';

if ($ayarlar['bbcode'] == 1)
{
	$form_ozellik .= '<label style="cursor:pointer"><input type="checkbox" name="bbcode_kullan"';
	if ((isset($_POST['mesaj_onizleme'])) AND (!isset($_POST['bbcode_kullan']))) $form_ozellik .= '';
	else $form_ozellik .= ' checked="checked"';
	$form_ozellik .= '>Bu iletide BBCode kullan</label>';
}

// bbcode kapalı ise
else $form_ozellik .= '<input type="hidden" name="bbcode_kullan">&nbsp;BBCode Kapalı';



//  İFADE AÇMA - KAPATMA    //

$form_ozellik .= '<br><label style="cursor:pointer"><input type="checkbox" name="ifade" ';
if ((isset($_POST['mesaj_onizleme'])) AND (!isset($_POST['ifade']))) $form_ozellik .= '';
else $form_ozellik .= ' checked="checked"';
$form_ozellik .= '>Bu iletide ifade kullan</label>';



if (isset($_POST['oino'])) $oi_no = $_POST['oino'];
else $oi_no = '';


if (isset($_POST['ozel_yanitla']))
	$form_yanitla = '<input type="hidden" name="ozel_yanitla" value="1">';
else $form_yanitla = '';




$form_bilgi1 = '<form action="phpkf-bilesenler/oi_yaz_yap.php" method="post" onsubmit="return denetle_yazi()" name="duzenleyici_form" id="duzenleyici_form">
<input type="hidden" name="kayit_yapildi_mi" value="form_dolu">
<input type="hidden" name="sayfa_onizleme" value="oi_yaz">
<input type="hidden" name="mesaj_onizleme" value="Önizleme">
<input type="hidden" name="oino" value="'.$oi_no.'">'.$form_yanitla;



if (!isset($form_icerik)) $form_icerik = '';
if (!isset($javascript_kapali)) $javascript_kapali = '';



//	TEMA UYGULANIYOR	//

$ornek1->kosul('1', array('' => ''), false);
$ornek1->kosul('2', array('' => ''), false);
$ornek1->kosul('4', array('' => ''), false);
$ornek1->kosul('5', array('' => ''), true);


$dongusuz = array('{JAVASCRIPT_KAPALI}' => $javascript_kapali,
'{SAYFA_KIP}' => 'Özel İleti Yaz',
'{OI_KIME}' => $ozel_kime,
'{FORM_BASLIK}' => $form_baslik,
'{FORM_ICERIK}' => $form_icerik,
'{FORM_OZELLIK}' => $form_ozellik,
'{FORM_BILGI1}' => $form_bilgi1);


$ornek1->dongusuz($dongusuz);

eval(TEMA_UYGULA);

?>