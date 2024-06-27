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
include_once('phpkf-bilesenler/seo.php');


//		GRUPLAR SIRALANIYOR		//
//		GRUPLAR SIRALANIYOR		//
//		GRUPLAR SIRALANIYOR		//

if ( (isset($_GET['kip'])) AND ($_GET['kip'] == 'grup') ):

$sbaslik = 'FORUM YETKİLİLERİ';
$gbaslik = 'ÜYE GRUPLARI';
$sayfano = 42;
$sayfa_adi = 'Yetkililer ve Gruplar';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');



//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/uyeler.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));

// site kurucusu bilgileri çekiliyor
$vtsorgu = "SELECT id,kullanici_adi,gercek_ad,resim,mesaj_sayisi,katilim_tarihi,sehir_goster,sehir,mesaj_sayisi FROM $tablo_kullanicilar WHERE id='1' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$kurucu = $vt->fetch_assoc($vtsonuc);

// forum yöneticileri bilgileri çekiliyor
$vtsorgu = "SELECT id,kullanici_adi,resim,mesaj_sayisi,katilim_tarihi,sehir_goster,sehir,mesaj_sayisi FROM $tablo_kullanicilar WHERE yetki='1' AND id!='1' ORDER BY id";
$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());

// forum yardımcıları bilgileri çekiliyor
$vtsorgu = "SELECT id,kullanici_adi,resim,mesaj_sayisi,katilim_tarihi,sehir_goster,sehir,mesaj_sayisi FROM $tablo_kullanicilar WHERE yetki='2' ORDER BY id";
$vtsonuc3 = $vt->query($vtsorgu) or die ($vt->hata_ver());

// bölüm yardımcıları bilgileri çekiliyor
$vtsorgu = "SELECT id,kullanici_adi,resim,mesaj_sayisi,katilim_tarihi,sehir_goster,sehir,mesaj_sayisi FROM $tablo_kullanicilar WHERE yetki='3' ORDER BY id";
$vtsonuc4 = $vt->query($vtsorgu) or die ($vt->hata_ver());

// Grupların bilgileri çekiliyor
$vtsorgu = "SELECT * FROM $tablo_gruplar where gizle='0' ORDER BY sira";
$vtsonuc5 = $vt->query($vtsorgu) or die ($vt->hata_ver());




//	SİTE KURUCUSU	//

$kurucubag = linkver('profil.php?u='.$kurucu['id'].'&kim='.$kurucu['kullanici_adi'],$kurucu['kullanici_adi']);

if ($kurucu['resim'] != '') $kurucu_resim = '<a href="'.$kurucubag.'"><img src="'.$kurucu['resim'].'" alt="Kullanıcı Resmi" border="0" width="65"></a>';
elseif ($ayarlar['v-uye_resmi'] != '') $kurucu_resim = '<a href="'.$kurucubag.'"><img src="'.$ayarlar['v-uye_resmi'].'" alt="Varsayılan Kullanıcı Resmi" border="0" width="65"></a>';
else $kurucu_resim = '';

$kurucu_bilgi = '<span style="float:left;width:100%;margin:1px;">&nbsp;
Üye Adı: <a href="'.$kurucubag.'" style="text-decoration:none">'.$kurucu['kullanici_adi'].'</a>&nbsp; ('.$kurucu['gercek_ad'].')</span>
<br><span style="float:left;width:100%;margin:1px;">&nbsp;
Kayıt Tarihi: '.zonedate2('d-m-Y', $ayarlar['saat_dilimi'], false, $kurucu['katilim_tarihi']).'</span>
<br><span style="float:left;width:100%;margin:1px;">&nbsp;
İleti Sayısı: '.$kurucu['mesaj_sayisi'].'</span>
<br><span style="float:left;width:100%;margin:1px;">&nbsp;
Konum: '.$kurucu['sehir'].'</span>';



//	FORUM YÖNETİCİLERİ	//

if ($vt->num_rows($vtsonuc2))
{
	while ($yonetici = $vt->fetch_assoc($vtsonuc2))
	{
		$yonetbag = linkver('profil.php?u='.$yonetici['id'].'&kim='.$yonetici['kullanici_adi'],$yonetici['kullanici_adi']);

		if ($yonetici['resim'] != '') $yonetici_resim = '<a href="'.$yonetbag.'"><img src="'.$yonetici['resim'].'" alt="Kullanıcı Resmi" border="0" width="45" /></a>';
		elseif ($ayarlar['v-uye_resmi'] != '') $yonetici_resim = '<a href="'.$yonetbag.'"><img src="'.$ayarlar['v-uye_resmi'].'" alt="Varsayılan Kullanıcı Resmi" border="0" width="45" /></a>';
		else $yonetici_resim = '';

		$yonetici_bilgi = '<span style="float:left;width:100%;margin:1px;">
Üye Adı: <a href="'.$yonetbag.'" style="text-decoration:none">'.$yonetici['kullanici_adi'].'</a></span>
<br><span style="float:left;width:100%;margin:1px;">
Kayıt Tarihi: '.zonedate2('d-m-Y', $ayarlar['saat_dilimi'], false, $yonetici['katilim_tarihi']).'</span>
<br><span style="float:left;width:100%;margin:1px;">
İleti Sayısı: '.$yonetici['mesaj_sayisi'].'</span>
<br><span style="float:left;width:100%;margin:1px;">
Konum: '.$yonetici['sehir'].'</span>';


		$tekli2[] = array('{YONETICI_RESIM}' => $yonetici_resim,
		'{YONETICI_BILGI}' => $yonetici_bilgi);
	}
}

else
{
	$tekli2[] = array('{YONETICI_RESIM}' => '',
	'{YONETICI_BILGI}' => '&nbsp;'.$ayarlar['yonetici'].' Yok');
}



//	FORUM YARDIMCILARI	//

if ($vt->num_rows($vtsonuc3))
{
	while ($yardimci = $vt->fetch_assoc($vtsonuc3))
	{
		$yardimbag = linkver('profil.php?u='.$yardimci['id'].'&kim='.$yardimci['kullanici_adi'],$yardimci['kullanici_adi']);

		if ($yardimci['resim'] != '') $yardimci_resim = '<a href="'.$yardimbag.'"><img src="'.$yardimci['resim'].'" alt="Kullanıcı Resmi" border="0" width="45" /></a>';
		elseif ($ayarlar['v-uye_resmi'] != '') $yardimci_resim = '<a href="'.$yardimbag.'"><img src="'.$ayarlar['v-uye_resmi'].'" alt="Varsayılan Kullanıcı Resmi" border="0" width="45" /></a>';
		else $yardimci_resim = '';

		$yardimci_bilgi = '<span style="float:left;width:100%;margin:1px;">
Üye Adı: <a href="'.$yardimbag.'" style="text-decoration:none">'.$yardimci['kullanici_adi'].'</a></span>
<br><span style="float:left;width:100%;margin:1px;">
Kayıt Tarihi: '.zonedate2('d-m-Y', $ayarlar['saat_dilimi'], false, $yardimci['katilim_tarihi']).'</span>
<br><span style="float:left;width:100%;margin:1px;">
İleti Sayısı: '.$yardimci['mesaj_sayisi'].'</span>
<br><span style="float:left;width:100%;margin:1px;">
Konum: '.$yardimci['sehir'].'</span>';


		$tekli3[] = array('{YARDIMCI_RESIM}' => $yardimci_resim,
		'{YARDIMCI_BILGI}' => $yardimci_bilgi);
	}
}

else
{
	$tekli3[] = array('{YARDIMCI_RESIM}' => '',
	'{YARDIMCI_BILGI}' =>  '&nbsp;'.$ayarlar['yardimci'].' Yok');
}



//	BÖLÜM YARDIMCILARI	//

if ($vt->num_rows($vtsonuc4))
{
	while ($byardimci = $vt->fetch_assoc($vtsonuc4))
	{
		$byardimbag = linkver('profil.php?u='.$byardimci['id'].'&kim='.$byardimci['kullanici_adi'],$byardimci['kullanici_adi']);

		if ($byardimci['resim'] != '') $blm_yrd_resim = '<a href="'.$byardimbag.'"><img src="'.$byardimci['resim'].'" alt="Kullanıcı Resmi" border="0" width="45" /></a>';
		elseif ($ayarlar['v-uye_resmi'] != '') $blm_yrd_resim = '<a href="'.$byardimbag.'"><img src="'.$ayarlar['v-uye_resmi'].'" alt="Varsayılan Kullanıcı Resmi" border="0" width="45" /></a>';
		else $blm_yrd_resim = '';

		$blm_yrd_bilgi = '<span style="float:left;width:100%;margin:1px;">
Üye Adı: <a href="'.$byardimbag.'" style="text-decoration:none">'.$byardimci['kullanici_adi'].'</a></span>
<br><span style="float:left;width:100%;margin:1px;">
Kayıt Tarihi: '.zonedate2('d-m-Y', $ayarlar['saat_dilimi'], false, $byardimci['katilim_tarihi']).'</span>
<br><span style="float:left;width:100%;margin:1px;">
İleti Sayısı: '.$byardimci['mesaj_sayisi'].'</span>
<br><span style="float:left;width:100%;margin:1px;">
Konum: '.$byardimci['sehir'].'</span>';


		$tekli4[] = array('{BLM_YRD_RESIM}' => $blm_yrd_resim,
		'{BLM_YRD_BILGI}' => $blm_yrd_bilgi);
	}
}

else
{
	$tekli4[] = array('{BLM_YRD_RESIM}' => '',
	'{BLM_YRD_BILGI}' => '&nbsp;'.$ayarlar['blm_yrd'].' Yok');
}





//	GRUP ÜYELERİ	//

$tablosayi = 0;

// GRUPLAR SIRALANIYOR

if ($vt->num_rows($vtsonuc5)):


// tüm forumların bilgileri çekiliyor
$vtsorgu = "SELECT id,forum_baslik FROM $tablo_forumlar ORDER BY id";
$vtsonuc7 = $vt->query($vtsorgu) or die ($vt->hata_ver());

// forumların başlıkları diziye aktarılıyor
while ($forumlar = $vt->fetch_assoc($vtsonuc7))
$tumforumlar[$forumlar['id']] = $forumlar['forum_baslik'];


while ($gruplar = $vt->fetch_assoc($vtsonuc5))
{
	if ($gruplar['ozel_ad'] != '') $gozel_ad = $gruplar['ozel_ad'];
	else $gozel_ad = 'Yok';

	if ($gruplar['yetki'] == '-1') $gyetki = 'Yok';
	elseif ($gruplar['yetki'] == 0) $gyetki = $ayarlar['kullanici'];
	elseif ($gruplar['yetki'] == 2) $gyetki = $ayarlar['yardimci'];
	elseif ($gruplar['yetki'] == 1) $gyetki = $ayarlar['yonetici'];
	elseif ($gruplar['yetki'] == 3)
	{
		$gyetki = $ayarlar['blm_yrd'].'<br><span style="float:left;width:100%;margin:1px;"><b>Yekili Olduğu Bölümler:</b></span>';

		// grubun yetkisine çekiliyor
		$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE grup='$gruplar[id]' AND yonetme='1' ORDER BY fno";
		$vtsonuc8 = $vt->query($vtsorgu) or die ($vt->hata_ver());

		// yetkili olduğu forumlar sıralanıyor
		while ($oforumlar = $vt->fetch_assoc($vtsonuc8))
			$gyetki .= '<br><span style="float:left;width:100%;margin:1px;">
			<a href="'.linkver('forum.php?f='.$oforumlar['fno'], $tumforumlar[$oforumlar['fno']]).'">'.$tumforumlar[$oforumlar['fno']].'</a></span>';
	}


	// grup üyeleri sıralanıyor
	$guyed = explode(',', $gruplar['uyeler']);


	if (count($guyed) > 1)
	{
		foreach ($guyed as $guye)
		{
			if ($guye == '') continue;
			$vtsorgu = "SELECT kulid FROM $tablo_ozel_izinler WHERE kulid='$guye' AND yonetme='1'";
			$yardimcilik = $vt->query($vtsorgu) or die ($vt->hata_ver());

			// grup üyelerinin bilgileri çekiliyor
			$vtsorgu = "SELECT id,kullanici_adi,resim,mesaj_sayisi,katilim_tarihi,sehir_goster,sehir,mesaj_sayisi FROM $tablo_kullanicilar WHERE id='$guye' LIMIT 1";
			$vtsonuc6 = $vt->query($vtsorgu) or die ($vt->hata_ver());
			$guye = $vt->fetch_assoc($vtsonuc6);


			$gbag = linkver('profil.php?u='.$guye['id'].'&kim='.$guye['kullanici_adi'],$guye['kullanici_adi']);

			if ($guye['resim'] != '') $grup_resim = '<a href="'.$gbag.'"><img src="'.$guye['resim'].'" alt="Kullanıcı Resmi" border="0" width="45" /></a>';
			elseif ($ayarlar['v-uye_resmi'] != '') $grup_resim = '<a href="'.$gbag.'"><img src="'.$ayarlar['v-uye_resmi'].'" alt="Varsayılan Kullanıcı Resmi" border="0" width="45" /></a>';
			else $grup_resim = '';

			$grup_uye = '<span style="float:left;width:100%;margin:1px;"></span><span style="float:left;width:100%;margin:1px;">
			Üye Adı: <a href="'.$gbag.'" style="text-decoration:none">'.$guye['kullanici_adi'].'</a></span>
			<br><span style="float:left;width:100%;margin:1px;">
			Kayıt Tarihi: '.zonedate2('d-m-Y', $ayarlar['saat_dilimi'], false, $guye['katilim_tarihi']).'</span>
			<br><span style="float:left;width:100%;margin:1px;">
			İleti Sayısı: '.$guye['mesaj_sayisi'].'</span>
			<br><span style="float:left;width:100%;margin:1px;">
			Konum: '.$guye['sehir'].'</span>';

			$tema_ic[$tablosayi][] = array(	'{GRUP_RESIM}' => $grup_resim,
			'{GRUP_UYE}' => $grup_uye);
		}
	}

	else
	{
		$tema_ic[$tablosayi][] = array('{GRUP_RESIM}' => '',
		'{GRUP_UYE}' => '&nbsp;Grupta Hiçbir Üye Yok');
	}


	$grup_bilgi = '<span style="float:left;width:100%;margin:1px;"></span>
	<span style="float:left;width:100%;margin:1px;">
	Üye Sayısı:'.(count($guyed)-1).'</span>
	<br><span style="float:left;width:100%;margin:1px;">
	Açıklama: '.$gruplar['grup_bilgi'].'</span>
	<br><span style="float:left;width:100%;margin:1px;">
	Özel Ad: '.$gozel_ad.'</span>
	<br><span style="float:left;width:100%;margin:1px;">
	Yetki: '.$gyetki.'</span>
	<span style="float:left;width:100%;margin:1px;"></span>';



	if ( ($tablosayi != 0) AND ($tablosayi % 3) == 0)
	$asagiat = '<div style="float:left; width:100%; height:1px;"></div>';
	else $asagiat = '';

	$tema_dis[] = array('{GRUP_ADI}' => $gruplar['grup_adi'],
	'{GRUP_BILGI}' => $grup_bilgi,
	'{ASAGI_AT}' => $asagiat);
	$tablosayi++;
}

$grup_yok = '';



else:
	$ornek1->kosul('5', array(''=>''), false);

	$grup_yok = '<div style="float:left; width:100%; height:30px;"></div><div align="center" style="float:left; width:100%;">Forumda Hiçbir Grup Yok</div>';

	$tema_dis[] = array('{GRUP_ADI}' => '',
	'{GRUP_BILGI}' => '',
	'{ASAGI_AT}' => '');

	$tema_ic[0][] = array('{GRUP_RESIM}' => '',
	'{GRUP_UYE}' => '');

endif;



		//	veriler tema motoruna yollanıyor	//

$kosul4 = array('{KURUCU_BASLIK}' => $ayarlar['kurucu'],
'{YONETICI_BASLIK}' => $ayarlar['yonetici'],
'{YARDIMCI_BASLIK}' => $ayarlar['yardimci'],
'{BLM_YRD_BASLIK}' => $ayarlar['blm_yrd'],
'{KURUCU_RESIM}' => $kurucu_resim,
'{KURUCU_BILGI}' => $kurucu_bilgi,
'{GRUP_BASLIK}' => $gbaslik,
'{GRUP_YOK}' => $grup_yok);

$siralama_secenek ='';
$sayfalama = '';
$satir_sayi = 0;

$ornek1->kosul('1', array(''=>''), false);
$ornek1->kosul('2', array(''=>''), false);
$ornek1->kosul('3', array(''=>''), false);
$ornek1->kosul('4', $kosul4, true);

if ( (isset($tema_dis)) AND (isset($tema_ic)) )
	$ornek1->icice_dongu('1', $tema_dis, $tema_ic);

$ornek1->tekli_dongu('2',$tekli2);
$ornek1->tekli_dongu('3',$tekli3);
$ornek1->tekli_dongu('4',$tekli4);






//		ÜYELER SIRALANIYOR		//
//		ÜYELER SIRALANIYOR		//
//		ÜYELER SIRALANIYOR		//


else:

//	DEĞERLER YOKSA SIFIRLANIYOR

$uyeler_kota = 30;

if (empty($_GET['sayfa'])) {$_GET['sayfa'] = 0; $baslik_ek = '';}
else
{
	$_GET['sayfa'] = @zkTemizleNumara($_GET['sayfa']);
	$baslik_ek = ' - Sayfa '.(($_GET['sayfa']/$uyeler_kota)+1);
}


$sbaslik = 'ÜYELER';
$sayfano = 7;
$sayfa_adi = 'Üyeler'.$baslik_ek;


if (empty($_GET['sirala'])) $_GET['sirala'] = 1;
else $_GET['sirala'] = @zkTemizle4(@zkTemizle($_GET['sirala']));


if (empty($_GET['kul_ara'])) $_GET['kul_ara'] = '%';
else
{
	$_GET['kul_ara'] = @zkTemizle4(@zkTemizle($_GET['kul_ara']));
	$_GET['kul_ara'] = @str_replace('*','%',trim($_GET['kul_ara']));
}


if (( strlen($_GET['kul_ara']) >  20))
{
	header('Location: hata.php?hata=19');
	exit();
}


include_once('phpkf-bilesenler/sayfa_baslik_forum.php');


//	SORGU SONUCUNDAKİ TOPLAM SONUÇ SAYISI ALINIYOR	//


$vtsonuc9 = $vt->query("SELECT id FROM $tablo_kullanicilar WHERE kullanici_adi LIKE '$_GET[kul_ara]%'") or die ($vt->hata_ver());
$satir_sayi = $vt->num_rows($vtsonuc9);

$toplam_sayfa = ($satir_sayi / $uyeler_kota);
settype($toplam_sayfa,'integer');

if ( ($satir_sayi % $uyeler_kota) != 0 ) $toplam_sayfa++;



//	ÜYELERİN BİLGİLERİ ÇEKİLİYOR	//

$vtsorgu = "SELECT id,kullanici_adi,mesaj_sayisi,katilim_tarihi,yetki,sehir_goster,sehir,engelle,resim FROM $tablo_kullanicilar WHERE kullanici_adi LIKE '$_GET[kul_ara]%' ORDER BY ";

if ($_GET['sirala'] == 'mesaj_0dan9a') $vtsorgu .= "mesaj_sayisi LIMIT $_GET[sayfa],$uyeler_kota";
elseif ($_GET['sirala'] == 'mesaj_9dan0a') $vtsorgu .= "mesaj_sayisi DESC LIMIT $_GET[sayfa],$uyeler_kota";
elseif ($_GET['sirala'] == 'katilim_9dan0a') $vtsorgu .= "id DESC LIMIT $_GET[sayfa],$uyeler_kota";
elseif ($_GET['sirala'] == 'ad_AdanZye') $vtsorgu .= "kullanici_adi LIMIT $_GET[sayfa],$uyeler_kota";
elseif ($_GET['sirala'] == 'ad_ZdenAya') $vtsorgu .= "kullanici_adi DESC LIMIT $_GET[sayfa],$uyeler_kota";
elseif ($_GET['sirala'] == 'yetki') $vtsorgu .= "yetki=0, yetki=3, yetki=2, yetki=1, id LIMIT $_GET[sayfa],$uyeler_kota";
else $vtsorgu .= "id LIMIT $_GET[sayfa],$uyeler_kota";

$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());



// SIRALAMA SEÇENEKLERİ //

$siralama_secenek = '<option value="1">Katılım tarihine göre
<option value="katilim_9dan0a" ';

if ($_GET['sirala'] == 'katilim_9dan0a') $siralama_secenek .= 'selected="selected"';
$siralama_secenek .= '>Katılım tarihine göre tersten

<option value="ad_AdanZye" ';
if ($_GET['sirala'] == 'ad_AdanZye') $siralama_secenek .= 'selected="selected"';
$siralama_secenek .= '>Kullanıcı adına göre A\'dan Z\'ye

<option value="ad_ZdenAya" ';
if ($_GET['sirala'] == 'ad_ZdenAya') $siralama_secenek .= 'selected="selected"';
$siralama_secenek .= '>Kullanıcı adına göre Z\'den A\'ya

<option value="mesaj_9dan0a" ';
if ($_GET['sirala'] == 'mesaj_9dan0a') $siralama_secenek .= 'selected="selected"';
$siralama_secenek .= '>İleti sayısına göre

<option value="mesaj_0dan9a" ';
if ($_GET['sirala'] == 'mesaj_0dan9a') $siralama_secenek .= 'selected="selected"';
$siralama_secenek .= '>İleti sayısına göre tersten

<option value="yetki" ';
if ($_GET['sirala'] == 'yetki') $siralama_secenek .= 'selected="selected"';
$siralama_secenek .= '>Yetkisine göre(Yöneticiler önde)';




while ($uyeler_satir = $vt->fetch_assoc($vtsonuc)):



if ($uyeler_satir['id'] == 1)
	$uye_yetki = '<font class="kurucu">'.$ayarlar['kurucu'].'</font>';

elseif ($uyeler_satir['yetki'] == 1)
	$uye_yetki = '<font class="yonetici">'.$ayarlar['yonetici'].'</font>';

elseif ($uyeler_satir['yetki'] == 2)
	$uye_yetki = '<font class="yardimci">'.$ayarlar['yardimci'].'</font>';

elseif ($uyeler_satir['yetki'] == 3)
	$uye_yetki = '<font class="blm_yrd">'.$ayarlar['blm_yrd'].'</font>';

else $uye_yetki = '';



if($uyeler_satir['sehir_goster'] == 1)
	$uye_sehir = $uyeler_satir['sehir'];

else $uye_sehir = 'GİZLİ';


$uye_bag = linkver('profil.php?u='.$uyeler_satir['id'].'&kim='.$uyeler_satir['kullanici_adi'],$uyeler_satir['kullanici_adi']);


if ($uyeler_satir['engelle'] != 1)
	$uye_adi = '&nbsp;<a href="'.$uye_bag.'">'.$uyeler_satir['kullanici_adi'].'</a>';

else $uye_adi = '&nbsp;<a href="'.$uye_bag.'"><s>'.$uyeler_satir['kullanici_adi'].'</s></a>';


if ($uyeler_satir['resim'] != '') $uye_resim = '<a href="'.$uye_bag.'"><img src="'.$uyeler_satir['resim'].'" alt="Kullanıcı Resmi" border="0" style="max-width:98%" /></a>';
elseif ($ayarlar['v-uye_resmi'] != '') $uye_resim = '<a href="'.$uye_bag.'"><img src="'.$ayarlar['v-uye_resmi'].'" alt="Varsayılan Kullanıcı Resmi" border="0" style="max-width:98%" /></a>';
else $uye_resim = '';


$uye_katilim = zonedate('d-m-Y', $ayarlar['saat_dilimi'], false, $uyeler_satir['katilim_tarihi']);

$uye_eposta = '<a href="eposta.php?kim='.$uyeler_satir['kullanici_adi'].'">E-Posta</a>';

$uye_ileti = '<a href="oi_yaz.php?ozel_kime='.$uyeler_satir['kullanici_adi'].'">ileti</a>';



//	veriler tema motoruna yollanıyor	//

$tekli1[] = array('{UYE_ADI}' => $uye_adi,
'{UYE_YETKISI}' => $uye_yetki,
'{UYE_MESAJ}' => NumaraBicim($uyeler_satir['mesaj_sayisi']),
'{UYE_KATILIM}' => $uye_katilim,
'{UYE_RESIM}' => $uye_resim,
'{UYE_SEHIR}' => $uye_sehir,
'{UYE_EPOSTA}' => $uye_eposta,
'{UYE_OZEL}' => $uye_ileti);


endwhile;



//  SAYFALAMA   //

$sayfalama = '';

if ($satir_sayi > $uyeler_kota):

$sayfalama .= '<p>
<table cellspacing="1" cellpadding="4" border="0" align="right" class="tablo_border">
	<tbody>
	<tr>
	<td class="forum_baslik">
Toplam '.$toplam_sayfa.' Sayfa:&nbsp;
    </td>
';


if ($_GET['sayfa'] != 0)
{
	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="ilk sayfaya git">';
	$sayfalama .= '&nbsp;<a href="uyeler.php?sayfa=0&amp;kul_ara='.$_GET['kul_ara'].'&amp;sirala='.$_GET['sirala'].'">&laquo;ilk</a>&nbsp;</td>';

	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="önceki sayfaya git">';
	$sayfalama .= '&nbsp;<a href="uyeler.php?sayfa='.($_GET['sayfa'] - $uyeler_kota).'&amp;kul_ara='.$_GET['kul_ara'].'&amp;sirala='.$_GET['sirala'].'">&lt;</a>&nbsp;</td>';
}

for ($sayi=0,$sayfa_sinir=$_GET['sayfa']; $sayi < $toplam_sayfa; $sayi++)
{
	if ($sayi < (($_GET['sayfa'] / $uyeler_kota) - 3));
	else
	{
		$sayfa_sinir++;
		if ($sayfa_sinir >= ($_GET['sayfa'] + 8)) break;
		if (($sayi == 0) and ($_GET['sayfa'] == 0))
		{
			$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">';
			$sayfalama .= '&nbsp;<b>[1]</b>&nbsp;</td>';
		}

		elseif (($sayi + 1) == (($_GET['sayfa'] / $uyeler_kota) + 1))
		{
			$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">';
			$sayfalama .= '&nbsp;<b>['.($sayi + 1).']</b>&nbsp;</td>';
		}

		else
		{
			$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="'.($sayi + 1).' numaralı sayfaya git">';

			$sayfalama .= '&nbsp;<a href="uyeler.php?sayfa='.($sayi * $uyeler_kota).'&amp;kul_ara='.$_GET['kul_ara'].'&amp;sirala='.$_GET['sirala'].'">'.($sayi + 1).'</a>&nbsp;</td>';
		}
	}
}
if ($_GET['sayfa'] < ($satir_sayi - $uyeler_kota))
{
	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="sonraki sayfaya git">';
	$sayfalama .= '&nbsp;<a href="uyeler.php?sayfa='.($_GET['sayfa'] + $uyeler_kota).'&amp;kul_ara='.$_GET['kul_ara'].'&amp;sirala='.$_GET['sirala'].'">&gt;</a>&nbsp;</td>';

	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="son sayfaya git">';
	$sayfalama .= '&nbsp;<a href="uyeler.php?sayfa='.(($toplam_sayfa - 1) * $uyeler_kota).'&amp;kul_ara='.$_GET['kul_ara'].'&amp;sirala='.$_GET['sirala'].'">son&raquo;</a>&nbsp;</td>';
}

$sayfalama .= '</tr>
	</tbody>
</table>';

endif;


//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/uyeler.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


if (isset($tekli1))
{
	$ornek1->kosul('2', array(''=>''), false);
	$ornek1->kosul('1', array(''=>''), true);

	$ornek1->tekli_dongu('1',$tekli1);
}

else
{
	$tekli1[] = array('{UYE_ADI}' => '',
	'{UYE_YETKISI}' => '',
	'{UYE_MESAJ}' => '',
	'{UYE_KATILIM}' => '',
	'{UYE_RESIM}' => '',
	'{UYE_SEHIR}' => '',
	'{UYE_EPOSTA}' => '',
	'{UYE_OZEL}' => '');

	$ornek1->tekli_dongu('1',$tekli1);

	$ornek1->kosul('2', array('{SONUC_YOK}'=>'Aradığınız koşula uyan üye yok !'), true);
	$ornek1->kosul('1', array(''=>''), false);
}

$ornek1->kosul('4', array(''=>''), false);


endif;






if (empty($_GET['kul_ara'])) $_GET['kul_ara'] = '%';

$ornek1->dongusuz(array('{KULLANICI_ARA}' => @str_replace('%','*',$_GET['kul_ara']),
'{SAYFA_BASLIK}' => $sbaslik,
'{SIRALAMA_SECENEK}' => $siralama_secenek,
'{SAYFALAMA}' => $sayfalama,
'{UYE_SAYISI}' => NumaraBicim($satir_sayi)));

eval(TEMA_UYGULA);

?>