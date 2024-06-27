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
if (!defined('DOSYA_GUVENLIK')) include 'phpkf-bilesenler/guvenlik.php';


$sayfano = 36;
$sayfa_adi = 'Kullanıcı Resim Galerisi';

include_once('phpkf-bilesenler/sayfa_baslik_forum.php');


if ( (isset($_GET['kim'])) AND ($_GET['kim'] != '') )
{
	$kim = '&amp;kim='.$_GET['kim'];
	$hedef = 'yonetim/kullanici_degistir.php?u='.$_GET['kim'];
}

else
{
	$hedef = 'profil_degistir.php';
	$kim = '';
}


//	ZARARLI KODLAR TEMİZLENİYOR	//

if ( (isset($_GET['galeri'])) AND ($_GET['galeri'] != '') )
{
	$galeri = str_replace('/','',$_GET['galeri']);
	$galeri = str_replace('.','',$_GET['galeri']);
	$dizin_adi = 'phpkf-dosyalar/resimler/galeri/'.$galeri.'/';
	$dizinler = '<a href="galeri.php?galeri='.$kim.'">Ana Galeri</a>';
}

else
{
	$galeri = '';
	$dizin_adi = 'phpkf-dosyalar/resimler/galeri/';
	$dizinler = '<b>Ana Galeri</b>';
}



//  DİĞER GALERİLER //

$galeri_tablo = '';
$secili = 'checked="checked"';	// sadece ilkini seçili yap
$diger_galeriler = 'phpkf-dosyalar/resimler/galeri/'; // galeri dizini
$dizin = @opendir($diger_galeriler);	// dizini açıyoruz

while ( @gettype($bilgi = @readdir($dizin)) != 'boolean' )
{
	if ( (@is_dir($diger_galeriler.$bilgi)) AND ($bilgi != '.') AND ($bilgi != '..') )
	{
		if ($bilgi == $galeri)
			$dizinler .= '&nbsp; | &nbsp;<b>'.$bilgi.'</b>';
		else $dizinler .= '&nbsp; | &nbsp;<a href="galeri.php?galeri='.$bilgi.$kim.'">'.$bilgi.'</a>';
	}
}

@closedir($dizin);	// dizini kapatıyoruz




//	DİZİNDEKİ DOSYALAR DÖNGÜYE SOKULARAK GÖRÜNTÜLENİYOR	//


$dizin = @opendir($dizin_adi);	// dizini açıyoruz

while ( @gettype($bilgi = @readdir($dizin)) != 'boolean' )
{
	if ( (!@is_dir($dizin_adi.$bilgi)) AND (preg_match('/.jpg$/i', $bilgi)) OR
		(!@is_dir($dizin_adi.$bilgi)) AND (preg_match('/.jpeg$/i', $bilgi)) OR
		(!@is_dir($dizin_adi.$bilgi)) AND (preg_match('/.gif$/i', $bilgi)) OR
		(!@is_dir($dizin_adi.$bilgi)) AND (preg_match('/.png$/i', $bilgi)) )
	{

		$galeri_tablo .= '
<table cellspacing="1" cellpadding="0" border="0" align="left" class="tablo_border4" style="margin: 6px;float:left">
	<tr>
	<td height="135" width="135" align="center" valign="middle" class="tablo_ici">
<label style="cursor: pointer;"><img src="'.$dizin_adi.$bilgi.'" alt="'.$bilgi.'"><br>
<input type="radio" name="galeri_resimi" size="20" value="'.$dizin_adi.$bilgi.'" '.$secili.'></label>
	</td>
	</tr>
</table>

';
		$secili = '';
	}
}

@closedir($dizin);	// dizini kapatıyoruz





//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/galeri.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


$ornek1->dongusuz(array('{HEDEF}' => $hedef,
						'{GALERI_TABLO}' => $galeri_tablo,
						'{DIGER_DIZINLER}' => $dizinler));

eval(TEMA_UYGULA);

?>