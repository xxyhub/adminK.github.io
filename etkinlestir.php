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


$phpkf_ayarlar_kip = "WHERE kip='1' OR kip='2' OR kip='4'";
if (!defined('DOSYA_AYAR')) include 'ayar.php';


//	GİRİŞ YAPILMIŞSA ANA SAYFAYA YÖNLENDİR	//

if (isset($_COOKIE['kullanici_kimlik']))
{
	header('Location: index.php');
	exit();
}


//	ETKİNLEŞTİRME KODU TALEBİ YAPILIYORSA	//

if (isset($_POST['kayit_yapildi_mi']) and ($_POST['kayit_yapildi_mi'] == 'etkinlestirme_talebi')):

if ( (!isset($_POST['posta'])) OR ($_POST['posta'] == '') ):
	header('Location: hata.php?hata=8');
	exit();
endif;

if (@strlen($_POST['posta']) > 100):
	header('Location: hata.php?hata=40');
	exit();
endif;

if (!@preg_match('/^([~&+.0-9a-z_-]+)@(([~&+0-9a-z-]+\.)+[0-9a-z]{2,4})$/i', $_POST['posta'])):
	header('Location: hata.php?hata=10');
	exit();
endif;





//	FORM DOĞRU DOLDURULDUYSA İŞLEMLERE DEVAM	//

if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';

$_POST['posta'] = @zkTemizle($_POST['posta']);


//	E-POSTA ADRESİNİN DOĞRULUĞU KONTROL EDİLİYOR	//

$vtsorgu = "SELECT id,kullanici_adi,posta,gercek_ad,dogum_tarihi,sehir,kul_etkin_kod,kul_etkin
            FROM $tablo_kullanicilar WHERE posta='$_POST[posta]' LIMIT 1";
$etkin_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());

if ($vt->num_rows($etkin_sonuc)):
$etkin_satir = $vt->fetch_array($etkin_sonuc);


if ($etkin_satir['kul_etkin'] == 1)
{
	header('Location: hata.php?hata=12');
	exit();
}

if ($ayarlar['kayit_hesap_etkin'] == 2)
{
	header('Location: hata.php?hata=211');
	exit();
}




//		postalar/etkinlestirme.txt DOSYASINDAKİ YAZILAR ALINIYOR...		//
//		... BELİRTİLEN YERLERE YENİ BİLGİLER GİRİLİYOR		// 


$site_link = $TEMA_SITE_ANADIZIN.'phpkf-bilesenler/kul_etkin.php?kulid='.$etkin_satir['id'].'&kulkod='.$etkin_satir['kul_etkin_kod'];

if (!($dosya_ac = fopen('./phpkf-bilesenler/postalar/etkinlestirme.txt','r'))) die ('Dosya Açılamıyor');
$posta_metni = fread($dosya_ac,1024);
fclose($dosya_ac);

$bul = array('{siteadi}',
'{site_link}',
);

$cevir = array($ayarlar['site_adi'],
$site_link,
);

$posta_metni = str_replace($bul,$cevir,$posta_metni);




//	ETKİNLEŞTİRME BİLGİLERİ POSTALANIYOR		//

require('phpkf-bilesenler/sinif_eposta.php');
$mail = new eposta_yolla();


if ($ayarlar['eposta_yontem'] == 'mail') $mail->MailKullan();
elseif ($ayarlar['eposta_yontem'] == 'smtp') $mail->SMTPKullan();


$mail->sunucu = $ayarlar['smtp_sunucu'];
if ($ayarlar['smtp_kd'] == '1') $mail->smtp_dogrulama = true;
else $mail->smtp_dogrulama = false;
$mail->kullanici_adi = $ayarlar['smtp_kullanici'];
$mail->sifre = $ayarlar['smtp_sifre'];

$mail->gonderen = $ayarlar['site_posta'];
$mail->gonderen_adi = $ayarlar['site_adi'];
$mail->GonderilenAdres($etkin_satir['posta']);
$mail->YanitlamaAdres($ayarlar['site_posta']);
$mail->konu = $ayarlar['site_adi'].' - Etkinleştirme Kodu';
$mail->icerik = $posta_metni;


// E-POSTA YOLLANDI, EKRAN ÇIKTISI VERİLİYOR //
if ($mail->Yolla())
{
	header('Location: hata.php?bilgi=14');
	exit();
}

else
{
	echo '<br><br><center><h3><font color="red">E-posta gönderilemedi !<p>Hata iletisi: ';
	echo $mail->hata_bilgi;
	echo '</p></font></h3></center>';
	exit();
}

//	GİRİLEN E-POSTA VERİTABANINDA YOKSA 	//

else:
	header('Location: hata.php?hata=13');
	exit();
endif;





//	SAYFAYA İLK DEFA GİRİLİYORSA BURADAN SONRASI GÖSTERİLİYOR	//

else :

$sayfano = 35;
$sayfa_adi = 'Etkinleştirme Kodu Başvurusu';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');



$javascript_kodu = '<script type="text/javascript">
<!--
function denetle(){
var dogruMu = true;
if (document.giris.posta.value.length < 4){
	dogruMu = false; 
	alert("Lütfen E-Posta adresinizi giriniz !");
}
else;
return dogruMu;
}
//  -->
</script>';



//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/etkinlestir.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));

$ornek1->dongusuz(array('{JAVASCRIPT_KODU}' => $javascript_kodu));

eval(TEMA_UYGULA);
endif;

?>