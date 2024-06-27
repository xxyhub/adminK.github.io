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


if (isset($_GET['mesaj_no'])) $_GET['k'] = @zkTemizle($_GET['mesaj_no']);
elseif (isset($_GET['k'])) $_GET['k'] = @zkTemizle($_GET['k']);
else $_GET['k'] = 0;


if (is_numeric($_GET['k']) == false)
{
	header('Location: hata.php?hata=47');
	exit();
}


//	SAYFA DEĞERLERİ YOKSA SIFIR YAPILIYOR

if (isset($_GET['sayfa'])) $_GET['ks'] = $_GET['sayfa'];
if (isset($_GET['fsayfa'])) $_GET['fs'] = $_GET['fsayfa'];

if (empty($_GET['ks'])) {$_GET['ks'] = 0; $baslik_ek = '';}
else
{
    $_GET['ks'] = @zkTemizle($_GET['ks']);
    $_GET['ks'] = @str_replace(array('-','x','.'), '', $_GET['ks']);
    if (is_numeric($_GET['ks']) == false) $_GET['ks'] = 0;
    if ($_GET['ks'] < 0) $_GET['ks'] = 0;
    $baslik_ek = ' : Sayfa '.(($_GET['ks']/$ayarlar['ksyfkota'])+1);
}


if (empty($_GET['fs'])) $_GET['fs'] = 0;
else
{
    $_GET['fs'] = @zkTemizle($_GET['fs']);
    $_GET['fs'] = @str_replace(array('-','x','.'), '', $_GET['fs']);
    if (is_numeric($_GET['fs']) == false) $_GET['fs'] = 0;
    if ($_GET['fs'] < 0) $_GET['fs'] = 0;
}


$zaman_asimi = $ayarlar['uye_cevrimici_sure'];
$tarih = time();


// MESAJ BİLGİLERİ ÇEKİLİYOR //

$vtsorgu = "SELECT * FROM $tablo_mesajlar WHERE id='$_GET[k]' AND silinmis='0' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$mesaj_satir = $vt->fetch_assoc($vtsonuc);


// KONU YOKSA HATA MESAJI, VARSA DEVAM //

if (empty($mesaj_satir))
{
	header('Location: hata.php?hata=47');
	exit();
}


// SEO ADRESİNİN DOĞRULUĞU KONTROL EDİLİYOR YANLIŞSA DOĞRU ADRESE YÖNLENDİRİLİYOR //

$dogru_adres = seoyap($mesaj_satir['mesaj_baslik']);

if ( (isset($_SERVER['REQUEST_URI'])) AND ($_SERVER['REQUEST_URI'] != '') AND (!@preg_match("/-$dogru_adres.html/i", $_SERVER['REQUEST_URI'])) AND (!@preg_match('/konu\.php\?/i', $_SERVER['REQUEST_URI'])) )
{
    $yonlendir = linkver('konu.php?k='.$mesaj_satir['id'], $mesaj_satir['mesaj_baslik']);
    header('Location:'.$yonlendir);
    exit();
}


// FORUM BİLGİLERİ ÇEKİLİYOR //

$vtsorgu = "SELECT forum_baslik,okuma_izni,yazma_izni,alt_forum FROM $tablo_forumlar
			WHERE id='$mesaj_satir[hangi_forumdan]' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$forum_satir = $vt->fetch_assoc($vtsonuc);



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
	if ( empty($kullanici_kim['id']) )
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
		if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$mesaj_satir[hangi_forumdan]' AND okuma='1' OR";
		else $grupek = "grup='0' AND";

		$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$mesaj_satir[hangi_forumdan]' AND okuma='1'";
		$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());

		if ( !$vt->num_rows($kul_izin) )
		{
			header('Location: hata.php?hata=17');
			exit();
		}
	}
}


// bölüm yardımcısı ise yönetme yetkisine bakılıyor - sil, düzenle, vs linkleri için
if ($kullanici_kim['yetki'] == 3)
{
	if ($kullanici_kim['grupid'] != '0') $grupek = "grup='$kullanici_kim[grupid]' AND fno='$mesaj_satir[hangi_forumdan]' AND yonetme='1' OR";
	else $grupek = "grup='0' AND";

	$vtsorgu = "SELECT fno FROM $tablo_ozel_izinler WHERE $grupek kulad='$kullanici_kim[kullanici_adi]' AND fno='$mesaj_satir[hangi_forumdan]' AND yonetme='1'";
	$kul_izin = $vt->query($vtsorgu) or die ($vt->hata_ver());

	if ($vt->num_rows($kul_izin)) $yrd_yetkisi = true;
	else $yrd_yetkisi = false;
}

			//	KULLANICIYA GÖRE FORUM GÖSTERİMİ - SONU			//




// MESAJ SAHİBİNİN PROFİLİ ÇEKİLİYOR //

$vtsorgu = "SELECT
id,kullanici_adi,gercek_ad,resim,katilim_tarihi,mesaj_sayisi,sehir_goster,sehir,web,imza,yetki,son_hareket,gizli,engelle,hangi_sayfada,sayfano,ozel_ad 
FROM $tablo_kullanicilar WHERE kullanici_adi='$mesaj_satir[yazan]' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$mesaj_sahibi = $vt->fetch_assoc($vtsonuc);


// GÖRÜNTÜLEME SAYISINI ARTTIR //

$vtsorgu = "UPDATE $tablo_mesajlar SET goruntuleme=goruntuleme + 1
			WHERE id='$mesaj_satir[id]' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


// CEVAP BİLGİLERİ ÇEKİLİYOR

$vtsorgu = "SELECT
id,cevap_yazan,cevap_baslik,cevap_icerik,tarih,yazan_ip,bbcode_kullan,degistirme_sayisi,degistiren,degistirme_tarihi,degistiren_ip,ifade
FROM $tablo_cevaplar WHERE silinmis='0' AND hangi_basliktan='$_GET[k]' ORDER BY tarih LIMIT $_GET[ks],$ayarlar[ksyfkota]";
$cevap = $vt->query($vtsorgu) or die ($vt->hata_ver());



// OLUŞTURULACAK SAYFA SAYISI BAĞLANTISI //

$satir_sayi = $mesaj_satir['cevap_sayi'];
$toplam_sayfa = ($satir_sayi / $ayarlar['ksyfkota']);
settype($toplam_sayfa,'integer');

if (($satir_sayi % $ayarlar['ksyfkota']) != 0)
$toplam_sayfa++;


//	BAŞLIĞIN İLETİ NUMARASI //

$ileti_no = $_GET['ks'];





//  BAŞLIĞIN OKUNDU BİLGİSİ ÇEREZE YAZDIRILIYOR    //

if ( (isset($kullanici_kim['son_giris'])) AND ($mesaj_satir['son_mesaj_tarihi'] > $kullanici_kim['son_giris']) )
{
    if (isset($_COOKIE['kfk_okundu']))
    {
        $cerez_dizi = explode('_', $_COOKIE['kfk_okundu']);

        foreach ($cerez_dizi as $cerez_parcala)
        {
            $okunan_kno = substr($cerez_parcala, 11);
            $okunan_dizi[$okunan_kno] = substr($cerez_parcala, 0, 10);
        }

        if (empty($okunan_dizi[$mesaj_satir['id']]))
        {
            setcookie('kfk_okundu', $_COOKIE['kfk_okundu'].'_'.$tarih.'-'.$mesaj_satir['id'], $tarih +$ayarlar['k_cerez_zaman'], $cerez_dizin, $cerez_alanadi);
        }

        elseif ($mesaj_satir['son_mesaj_tarihi'] > $okunan_dizi[$mesaj_satir['id']])
        {
            $cereze_yaz = '';

            foreach ($okunan_dizi as $ckno => $ctarih)
            {
                if ($ckno == $mesaj_satir['id']) $cereze_yaz .= '_'.$tarih.'-'.$ckno;

                else $cereze_yaz .= '_'.$ctarih.'-'.$ckno;
            }

            $cereze_yaz = substr($cereze_yaz, 1);
            setcookie('kfk_okundu', $cereze_yaz, $tarih +$ayarlar['k_cerez_zaman'], $cerez_dizin, $cerez_alanadi);
        }
    }

    else 
    {
        setcookie('kfk_okundu', $tarih.'-'.$mesaj_satir['id'], $tarih +$ayarlar['k_cerez_zaman'], $cerez_dizin, $cerez_alanadi);
    }
}



$sayfano = '2,'.$mesaj_satir['id'].',3,'.$mesaj_satir['hangi_forumdan'];
$sayfa_adi = $mesaj_satir['mesaj_baslik'].$baslik_ek;


include_once('phpkf-bilesenler/sayfa_baslik_forum.php');





	//		SAYFA BAĞLANTILARI OLUŞTURULUYOR BAŞI	//

$sayfalama_cikis = '';

if ($satir_sayi > $ayarlar['ksyfkota']):
$sayfalama_cikis = '<table cellspacing="1" cellpadding="4" border="0" align="right" class="tablo_border">
	<tbody>
	<tr>
	<td class="forum_baslik">
<span class="mobil-gizle">&nbsp;Toplam '.$toplam_sayfa.' Sayfa:&nbsp;</span>
<span class="genis-gizle masa-gizle tablet-gizle">&nbsp;Sayfa:&nbsp;</span>
	</td>';


if ($_GET['ks'] != 0)
{
	$sayfalama_cikis .= '<td bgcolor="#ffffff" class="liste-veri" title="ilk sayfaya git">
	&nbsp;<a href="'.linkver('konu.php?k='.$_GET['k'], $mesaj_satir['mesaj_baslik']).'">&laquo;ilk</a>&nbsp;</td>
		
	<td bgcolor="#ffffff" class="liste-veri" title="önceki sayfaya git">
	&nbsp;<a href="'.linkver('konu.php?k='.$_GET['k'].'&fs='.$_GET['fs'].'&ks='.($_GET['ks'] - $ayarlar['ksyfkota']), $mesaj_satir['mesaj_baslik']).'">&lt;</a>&nbsp;</td>';
}

for ($sayi=0,$sayfa_sinir=$_GET['ks']; $sayi < $toplam_sayfa; $sayi++)
{
	if ($sayi < (($_GET['ks'] / $ayarlar['ksyfkota']) - 3));
	else
	{
		$sayfa_sinir++;
		if ($sayfa_sinir >= ($_GET['ks'] + 8)) break;
		if (($sayi == 0) and ($_GET['ks'] == 0))
		{
			$sayfalama_cikis .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">
			&nbsp;<b>[1]</b>&nbsp;</td>';
		}
	
		elseif (($sayi + 1) == (($_GET['ks'] / $ayarlar['ksyfkota']) + 1))
		{
			$sayfalama_cikis .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">
			&nbsp;<b>['.($sayi + 1).']</b>&nbsp;</td>';
		}
	
		else
		{
			$sayfalama_cikis .= '<td bgcolor="#ffffff" class="liste-veri" title="'.($sayi + 1).' numaralı sayfaya git">

			&nbsp;<a href="'.linkver('konu.php?k='.$_GET['k'].'&fs='.$_GET['fs'].'&ks='.($sayi * $ayarlar['ksyfkota']), $mesaj_satir['mesaj_baslik']).'">'.($sayi + 1).'</a>&nbsp;</td>';
		}
	}
}

if ($_GET['ks'] < ($satir_sayi - $ayarlar['ksyfkota']))
{
	$sayfalama_cikis .= '<td bgcolor="#ffffff" class="liste-veri" title="sonraki sayfaya git">
	&nbsp;<a href="'.linkver('konu.php?k='.$_GET['k'].'&fs='.$_GET['fs'].'&ks='.($_GET['ks'] + $ayarlar['ksyfkota']), $mesaj_satir['mesaj_baslik']).'">&gt;</a>&nbsp;</td>

	<td bgcolor="#ffffff" class="liste-veri" title="son sayfaya git">
	&nbsp;<a href="'.linkver('konu.php?k='.$_GET['k'].'&fs='.$_GET['fs'].'&ks='.(($toplam_sayfa - 1) * $ayarlar['ksyfkota']), $mesaj_satir['mesaj_baslik']).'">son&raquo;</a>&nbsp;</td>';
}

$sayfalama_cikis .= '</tr></tbody></table>';
endif;


	//		SAYFA BAĞLANTILARI OLUŞTURULUYOR SONU	//






//	YENİ BAŞLIK YENİ CEVAP			//


$baslik_cevap = '<a href="mesaj_yaz.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=yeni" title="Yeni Konu Açmak için Tıklayın">'.$yenibaslik_rengi.'</a> &nbsp;';



if ($mesaj_satir['kilitli'] == 1)
{
	$baslik_cevap .= '<a href="javascript:void(0)" title="Bu konu kilitlenmiştir, cevap yazılamaz" style="cursor:help">'.$kilitli_rengi.'</a>';
	$form_ksayfa = 0;
}

else
{
	if ($satir_sayi < $ayarlar['ksyfkota'])
	{
		$baslik_cevap .= '<a href="mesaj_yaz.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;mesaj_no='.$_GET['k'].'&amp;kip=cevapla&amp;fsayfa='.$_GET['fs'].'" title="Bu Konuya Cevap Yazmak için Tıklayın">'.$cevapyaz_rengi.'</a>';
		$form_ksayfa = 0;
	}

	elseif ( ($satir_sayi % $ayarlar['ksyfkota']) == 0 )
	{
		$baslik_cevap .= '<a href="mesaj_yaz.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;mesaj_no='.$_GET['k'].'&amp;kip=cevapla&amp;fsayfa='.$_GET['fs'].'&amp;sayfa='.$satir_sayi.'" title="Bu Konuya Cevap Yazmak için Tıklayın">'.$cevapyaz_rengi.'</a>';
		$form_ksayfa = $satir_sayi; 
	}

	else
	{
		$y_sayi = $satir_sayi - ($satir_sayi % $ayarlar['ksyfkota']);
		$baslik_cevap .= '<a href="mesaj_yaz.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;mesaj_no='.$_GET['k'].'&amp;kip=cevapla&amp;fsayfa='.$_GET['fs'].'&amp;sayfa='.$y_sayi.'" title="Bu Konuya Cevap Yazmak için Tıklayın">'.$cevapyaz_rengi.'</a>';
		$form_ksayfa = $y_sayi;
	}
}




				//		BAŞLIK TABLOSU BAŞI		//


if ($_GET['ks'] < 1 ):


if ($mesaj_sahibi['engelle'] != 1)
    $konu_acan = '<a href="'.linkver($phpkf_dosyalar['profil'].'?u='.$mesaj_sahibi['id'].'&kim='.$mesaj_satir['yazan'],$mesaj_satir['yazan']).'">'.$mesaj_satir['yazan'].'</a>';

else $konu_acan = '<a href="'.linkver($phpkf_dosyalar['profil'].'?u='.$mesaj_sahibi['id'].'&kim='.$mesaj_satir['yazan'],$mesaj_satir['yazan']).'"><s>'.$mesaj_satir['yazan'].'</s></a>';


if (!empty($mesaj_sahibi['gercek_ad']))
	$konu_acan_adi = $mesaj_sahibi['gercek_ad'];
else $konu_acan_adi = '';


if (!empty($mesaj_sahibi['ozel_ad']))
	$konu_acan_yetkisi = '<font class="ozel_ad"><u>'.$mesaj_sahibi['ozel_ad'].'</u></font>';

elseif ($mesaj_sahibi['id'] == 1) 
	$konu_acan_yetkisi = '<font class="kurucu"><u>'.$ayarlar['kurucu'].'</u></font>';

elseif ($mesaj_sahibi['yetki'] == 1)
	$konu_acan_yetkisi = '<font class="yonetici"><u>'.$ayarlar['yonetici'].'</u></font>';

elseif ($mesaj_sahibi['yetki'] == 2)
	$konu_acan_yetkisi = '<font class="yardimci"><u>'.$ayarlar['yardimci'].'</u></font>';

elseif ($mesaj_sahibi['yetki'] == 3)
	$konu_acan_yetkisi = '<font class="blm_yrd"><u>'.$ayarlar['blm_yrd'].'</u></font>';

else $konu_acan_yetkisi = '<font class="kullanici">'.$ayarlar['kullanici'].'</font>';


if ($mesaj_sahibi['resim'] != '') $konu_acan_resmi = '<img src="'.$mesaj_sahibi['resim'].'" alt="." title='.$l['uye_resmi'].' style="max-width:98%" />';
elseif ($ayarlar['v-uye_resmi'] != '') $konu_acan_resmi = '<img src="'.$ayarlar['v-uye_resmi'].'" alt="." title='.$l['varsayilan_uye_resmi'].' style="max-width:98%" />';
else $konu_acan_resmi = '';


if (!empty($mesaj_sahibi['katilim_tarihi']))
	$konu_acan_kayit = zonedate('d.m.Y', $ayarlar['saat_dilimi'], false, $mesaj_sahibi['katilim_tarihi']);

else $konu_acan_kayit = '';


if (!empty($mesaj_sahibi['mesaj_sayisi']))
	$konu_acan_mesajsayi = $mesaj_sahibi['mesaj_sayisi'];

	else $konu_acan_mesajsayi = 0;


if ($mesaj_sahibi['sehir_goster'] == 1)
{
	if ($mesaj_sahibi['sehir'] != '') $konu_acan_sehir = $mesaj_sahibi['sehir'];
	else $konu_acan_sehir = 'Yok';
}

else $konu_acan_sehir = 'Gizli';


if (empty($mesaj_sahibi['gercek_ad']))
	$konu_acan_durum = '<font color="#FF0000">üye silinmiş</font>';

elseif ($mesaj_sahibi['engelle'] == 1)
	$konu_acan_durum = '<font color="#FF0000">üye uzaklaştırılmış</font>';

elseif ($mesaj_sahibi['gizli'] == 1)
	$konu_acan_durum = '<font color="#FF0000">Gizli</font>';

elseif ( (($mesaj_sahibi['son_hareket'] + $zaman_asimi) > $tarih ) AND
		($mesaj_sahibi['sayfano'] != '-1') )
	$konu_acan_durum = '<font color="#339900">Forumda</font>';

else $konu_acan_durum = '<font color="#FF0000">Forumda Değil</font>';


$konu_acan_eposta = '<a title="Forum üzerinden e-posta gönder" href="eposta.php?kim='.$mesaj_sahibi['kullanici_adi'].'">'.$l['eposta_gonder'].'</a>';

if ($mesaj_sahibi['web'])
	$konu_acan_web = '<br><a href="'.$mesaj_sahibi['web'].'" target="_blank" rel="nofollow">'.$l['web_sitesi'].'</a>';

else $konu_acan_web = '';

$konu_acan_ozel = '<a href="oi_yaz.php?ozel_kime='.$mesaj_sahibi['kullanici_adi'].'">'.$l['ozel_ileti_gonder'].'</a>';

$konu_tarihi = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $mesaj_satir['tarih']);



		//	ALINTI SİL VE DÜZENLE OLUŞTURULUYOR - BAŞI	//


$konu_alinti_duzenle = '';

if (isset($kullanici_kim['id']))
{
	if ($satir_sayi < $ayarlar['ksyfkota'])
		$konu_alinti_duzenle .= '<a href="mesaj_yaz.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevapla&amp;mesaj_no='.$mesaj_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;alinti=mesaj">';

	elseif (($satir_sayi % $ayarlar['ksyfkota']) == 0 )
		$konu_alinti_duzenle .= '<a href="mesaj_yaz.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevapla&amp;mesaj_no='.$mesaj_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;alinti=mesaj&amp;sayfa='.$satir_sayi.'">';

	else
	{
		$y_sayi = $satir_sayi - ($satir_sayi % $ayarlar['ksyfkota']);
		$konu_alinti_duzenle .= '<a href="mesaj_yaz.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevapla&amp;mesaj_no='.$mesaj_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;alinti=mesaj&amp;sayfa='.$y_sayi.'">';
	}

	$konu_alinti_duzenle .= '<img '.$simge_alinti.' alt="." title="Alıntı yaparak cevapla"></a>&nbsp;&nbsp;';
}


			//	KULLANICIYA GÖRE SİL VE DÜZENLE - BAŞI		//



//	YÖNETİCİ VE YARDIMCI İSE	//
if ( ($kullanici_kim['yetki'] == 1) OR ($kullanici_kim['yetki'] == 2) ):

$konu_alinti_duzenle .= '<a href="phpkf-bilesenler/mesaj_sil.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=mesaj&amp;mesaj_no='.$mesaj_satir['id'].'&amp;fsayfa='.$_GET['fs'].'"><img '.$simge_sil.' alt="." title="Bu konuyu sil"></a>&nbsp;&nbsp;';

$konu_alinti_duzenle .= '<a href="baslik_tasi.php?kip=tasi&amp;mesaj_no='.$mesaj_satir['id'].'"><img '.$simge_tasi.' alt="." title="Bu konuyu taşı"></a>&nbsp;&nbsp;';

$konu_alinti_duzenle .= '<a href="mesaj_degistir.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=mesaj&amp;mesaj_no='.$mesaj_satir['id'].'&amp;fsayfa='.$_GET['fs'].'"><img '.$simge_degistir.' alt="." title="Bu konuyu değiştir"></a>&nbsp;&nbsp;';

$konu_alinti_duzenle .= '<a href="phpkf-bilesenler/mesaj_degistir_yap.php?kip=kilitle&amp;mesaj_no='.$mesaj_satir['id'].'">';

if ($mesaj_satir['kilitli'] == 1)
$konu_alinti_duzenle .= '<img '.$simge_kilitle.' alt="." title="Bu konunun kilitini aç"></a>&nbsp;&nbsp;';

else $konu_alinti_duzenle .= '<img '.$simge_kilitle.' alt="." title="Bu konuyu kilitle"></a>&nbsp;&nbsp;';

$konu_alinti_duzenle .= '<a href="phpkf-bilesenler/mesaj_degistir_yap.php?kip=ustkonu&amp;mesaj_no='.$mesaj_satir['id'].'">';

if ($mesaj_satir['ust_konu'] == 1)
$konu_alinti_duzenle .= '<img '.$simge_ust.' alt="." title="Alt konu yap"></a>&nbsp;&nbsp;';

else $konu_alinti_duzenle .= '<img '.$simge_ust.' alt="." title="Üst konu yap"></a>&nbsp;&nbsp;';

$konu_alinti_duzenle .= '<a href="phpkf-yonetim/forum_ip_yonetimi.php?kip=1&amp;ip='.$mesaj_satir['yazan_ip'].'"><img  '.$simge_ip.' alt="." title="Bu konuyu açanın ip adresi"></a>&nbsp;&nbsp;';


//	BÖLÜM YARDIMCI İSE	//
elseif ($kullanici_kim['yetki'] == 3):

if ( (isset($yrd_yetkisi)) AND ($yrd_yetkisi == true) ):


$konu_alinti_duzenle .= '<a href="phpkf-bilesenler/mesaj_sil.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=mesaj&amp;mesaj_no='.$mesaj_satir['id'].'&amp;fsayfa='.$_GET['fs'].'"><img '.$simge_sil.' alt="." title="Bu konuyu sil"></a>&nbsp;&nbsp;';

$konu_alinti_duzenle .= '<a href="baslik_tasi.php?kip=tasi&amp;mesaj_no='.$mesaj_satir['id'].'"><img '.$simge_tasi.' alt="." title="Bu konuyu taşı"></a>&nbsp;&nbsp;';

$konu_alinti_duzenle .= '<a href="mesaj_degistir.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=mesaj&amp;mesaj_no='.$mesaj_satir['id'].'&amp;fsayfa='.$_GET['fs'].'"><img '.$simge_degistir.' alt="." title="Bu konuyu değiştir"></a>&nbsp;&nbsp;';

$konu_alinti_duzenle .= '<a href="phpkf-bilesenler/mesaj_degistir_yap.php?kip=kilitle&amp;mesaj_no='.$mesaj_satir['id'].'">';

if ($mesaj_satir['kilitli'] == 1)
$konu_alinti_duzenle .= '<img '.$simge_kilitle.' alt="." title="Bu konunun kilitini aç"></a>&nbsp;&nbsp;';

else $konu_alinti_duzenle .= '<img '.$simge_kilitle.' alt="." title="Bu konuyu kilitle"></a>&nbsp;&nbsp;';

$konu_alinti_duzenle .= '<a href="phpkf-bilesenler/mesaj_degistir_yap.php?kip=ustkonu&amp;mesaj_no='.$mesaj_satir['id'].'">';

if ($mesaj_satir['ust_konu'] == 1)
$konu_alinti_duzenle .= '<img '.$simge_ust.' alt="." title="Alt konu yap"></a>&nbsp;&nbsp;';

else $konu_alinti_duzenle .= '<img '.$simge_ust.' alt="." title="Üst konu yap"></a>&nbsp;&nbsp;';



//	BU FORUMUN YARDIMCISI OLMADIĞI HALDE İLETİYİ YAZANSA	//

elseif ($kullanici_kim['kullanici_adi'] == $mesaj_satir['yazan']):
	$konu_alinti_duzenle .= '<a href="mesaj_degistir.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=mesaj&amp;mesaj_no='.$mesaj_satir['id'].'&amp;fsayfa='.$_GET['fs'].'"><img '.$simge_degistir.' alt="." title="Bu konuyu değiştir"></a>&nbsp;&nbsp;';
endif;


//	İLETİYİ YAZAN KİŞİYSE	//

elseif ($kullanici_kim['kullanici_adi'] == $mesaj_satir['yazan']):
	$konu_alinti_duzenle .= '<a href="mesaj_degistir.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=mesaj&amp;mesaj_no='.$mesaj_satir['id'].'&amp;fsayfa='.$_GET['fs'].'"><img '.$simge_degistir.' alt="." title="Bu konuyu değiştir"></a>&nbsp;&nbsp;';
endif;



			//	KULLANICIYA GÖRE SİL VE DÜZENLE - SONU			//




	//	BAŞLIK İÇERİĞİ YAZDIRILIYOR	//
	//	VARSA İMZA VE DEĞİŞTİRME BİLGİLERİ YAZDIRILIYOR	//


if ($mesaj_satir['ifade'] == 1)
    $mesaj_satir['mesaj_icerik'] = ifadeler($mesaj_satir['mesaj_icerik']);

if ( ($mesaj_satir['bbcode_kullan'] == 1) AND ($ayarlar['bbcode'] == 1) )
	$konu_icerik = bbcode_acik($mesaj_satir['mesaj_icerik'],$mesaj_satir['id']);

else $konu_icerik = bbcode_kapali($mesaj_satir['mesaj_icerik']);


$konu_acan_imza = '';

if ( (isset($mesaj_sahibi['imza'])) AND ($mesaj_sahibi['imza'] != '') )
{
	if ($ayarlar['bbcode'] == 1) $konu_acan_imza .= bbcode_acik(ifadeler($mesaj_sahibi['imza']),0);
	else $konu_acan_imza .= bbcode_kapali(ifadeler($mesaj_sahibi['imza']));
}


		//	İLETİ DEĞİŞTİRİLME BİLGİLERİ		//

$konu_degisme = '';

if ($mesaj_satir['degistirme_sayisi'] != 0):
	$konu_degisme .= '<hr class="ileti_degisim_bilgi" /><font size="1"><i> Bu ileti en son <b>'.$mesaj_satir['degistiren'].'</b>
tarafından <b>'.zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $mesaj_satir['degistirme_tarihi']).'</b> tarihinde, toplamda '.$mesaj_satir['degistirme_sayisi'].' kez değiştirilmiştir.</i></font>';

if ($kullanici_kim['yetki'] == 1):
	$konu_degisme .= '&nbsp;<a href="phpkf-yonetim/forum_ip_yonetimi.php?kip=1&amp;ip='.$mesaj_satir['degistiren_ip'].'"><img  '.$simge_ip.' alt="." title="Bu konuyu değiştirenin ip adresi"></a>';

endif;
endif;


$konu_baglanti = '<a href="'.linkver('konu.php?k='.$mesaj_satir['id'], $mesaj_satir['mesaj_baslik']).'" style="color:#ffffff;text-decoration:none" title="Permalink">#</a>';



//	veriler tema motoruna yollanıyor	//

$kosul1 = array('{KONU_ANAME}' => '<a name="c0"></a>',
'{KONU_BAGLANTI}' => $konu_baglanti,
'{KONU_BASLIK2}' => $mesaj_satir['mesaj_baslik'],
'{GOSTERIM}' => NumaraBicim(($mesaj_satir['goruntuleme']+1)),
'{KONU_ACAN}' => $konu_acan,
'{KONU_ACAN_ADI}' => $konu_acan_adi,
'{KONU_ACAN_YETKISI}' => $konu_acan_yetkisi,
'{KONU_ACAN_RESMI}' => $konu_acan_resmi,
'{KONU_ACAN_KAYIT}' => $konu_acan_kayit,
'{KONU_ACAN_MESAJSAYI}' => NumaraBicim($konu_acan_mesajsayi),
'{KONU_ACAN_SEHIR}' => $konu_acan_sehir,
'{KONU_ACAN_DURUM}' => $konu_acan_durum,
'{KONU_ACAN_EPOSTA}' => $konu_acan_eposta,
'{KONU_ACAN_WEB}' => $konu_acan_web,
'{KONU_ACAN_OZEL}' => $konu_acan_ozel,
'{KONU_TARIHI}' => $konu_tarihi,
'{KONU_ALINTI_DUZENLE}' => $konu_alinti_duzenle,
'{KONU_ICERIK}' => $konu_icerik,
'{KONU_ACAN_IMZA}' => $konu_acan_imza,
'{KONU_DEGISTIRME}' => $konu_degisme);


endif;




						//	BAŞLIK TABLOSU SONU	//



						//	CEVAPLAR SIRALANIYOR	//



//	SADECE BAŞLIĞIN CEVAPLARI VARSA WHILE DÖNGÜSÜNE GİRİLİYOR	//
if (isset($satir_sayi)):
while ($cevap_satir = $vt->fetch_assoc($cevap)):

$vtsorgu = "SELECT id,kullanici_adi,gercek_ad,resim,katilim_tarihi,mesaj_sayisi,sehir_goster,sehir,web,imza,yetki,son_hareket,gizli,engelle,hangi_sayfada,sayfano,ozel_ad 
FROM $tablo_kullanicilar WHERE kullanici_adi='$cevap_satir[cevap_yazan]' LIMIT 1";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
$cevap_sahibi = $vt->fetch_assoc($vtsonuc);



		//	CEVAP TABLOLARI	BAŞI	//


$cevap_aname = '<a name="c'.$cevap_satir['id'].'"></a>';

$ileti_no++;


if ($cevap_sahibi['engelle'] != 1)
    $cevap_yazan = '<a href="'.linkver($phpkf_dosyalar['profil'].'?u='.$cevap_sahibi['id'].'&kim='.$cevap_satir['cevap_yazan'],$cevap_satir['cevap_yazan']).'">'.$cevap_satir['cevap_yazan'].'</a>';

else $cevap_yazan = '<a href="'.linkver($phpkf_dosyalar['profil'].'?u='.$cevap_sahibi['id'].'&kim='.$cevap_satir['cevap_yazan'],$cevap_satir['cevap_yazan']).'"><s>'.$cevap_satir['cevap_yazan'].'</s></a>';



if (!empty($cevap_sahibi['gercek_ad']))
	$cevap_yazan_adi = $cevap_sahibi['gercek_ad'];

else $cevap_yazan_adi = '';


if (!empty($cevap_sahibi['ozel_ad']))
	$cevap_yazan_yetkisi = '<font class="ozel_ad"><u>'.$cevap_sahibi['ozel_ad'].'</u></font>';

elseif ($cevap_sahibi['id'] == 1)
	$cevap_yazan_yetkisi = '<font class="kurucu"><u>'.$ayarlar['kurucu'].'</u></font>';

elseif ($cevap_sahibi['yetki'] == 1)
	$cevap_yazan_yetkisi = '<font class="yonetici"><u>'.$ayarlar['yonetici'].'</u></font>';

elseif ($cevap_sahibi['yetki'] == 2)
	$cevap_yazan_yetkisi = '<font class="yardimci"><u>'.$ayarlar['yardimci'].'</u></font>';

elseif ($cevap_sahibi['yetki'] == 3)
	$cevap_yazan_yetkisi = '<font class="blm_yrd"><u>'.$ayarlar['blm_yrd'].'</u></font>';

else $cevap_yazan_yetkisi = '<font class="kullanici">'.$ayarlar['kullanici'].'</font>';


if ($cevap_sahibi['resim'] != '')
	$cevap_yazan_resmi = '<img src="'.$cevap_sahibi['resim'].'" alt="." title='.$l['uye_resmi'].' style="max-width:98%" />';
elseif ($ayarlar['v-uye_resmi'] != '')
	$cevap_yazan_resmi = '<img src="'.$ayarlar['v-uye_resmi'].'" alt="." title='.$l['varsayilan_uye_resmi'].' style="max-width:98%" />';
else $cevap_yazan_resmi = '';


if (!empty($cevap_sahibi['katilim_tarihi']))
	$cevap_yazan_kayit = zonedate('d.m.Y', $ayarlar['saat_dilimi'], false, $cevap_sahibi['katilim_tarihi']);

else $cevap_yazan_kayit ='';


if (!empty($cevap_sahibi['mesaj_sayisi']))
	$cevap_yazan_mesajsayi = $cevap_sahibi['mesaj_sayisi'];

else $cevap_yazan_mesajsayi = 0;


if ($cevap_sahibi['sehir_goster'] == 1)
{
	if ($cevap_sahibi['sehir'] != '') $cevap_yazan_sehir = $cevap_sahibi['sehir'];
	else $cevap_yazan_sehir = 'Yok';
}

else $cevap_yazan_sehir = 'Gizli';


if (empty($cevap_sahibi['gercek_ad']))
	$cevap_yazan_durum = '<font color="#FF0000">üye silinmiş</font>';

elseif ($cevap_sahibi['engelle'] == 1)
	$cevap_yazan_durum = '<font color="#FF0000">üye uzaklaştırılmış</font>';

elseif ($cevap_sahibi['gizli'] == 1)
	$cevap_yazan_durum = '<font color="#FF0000">Gizli</font>';

elseif ( (($cevap_sahibi['son_hareket'] + $zaman_asimi) > $tarih ) AND
		($cevap_sahibi['sayfano'] != '-1') )
	$cevap_yazan_durum = '<font color="#339900">Forumda</font>';

else $cevap_yazan_durum = '<font color="#FF0000">Forumda Değil</font>';


$cevap_yazan_eposta = '<a title="Forum üzerinden e-posta gönder" href="eposta.php?kim='.$cevap_sahibi['kullanici_adi'].'">'.$l['eposta_gonder'].'</a>';


if ($cevap_sahibi['web'])
	$cevap_yazan_web = '<br><a href="'.$cevap_sahibi['web'].'" target="_blank" rel="nofollow">'.$l['web_sitesi'].'</a>';

else $cevap_yazan_web = '';


$cevap_yazan_ozel = '<a href="oi_yaz.php?ozel_kime='.$cevap_sahibi['kullanici_adi'].'">'.$l['ozel_ileti_gonder'].'</a>';

$cevap_tarihi = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $cevap_satir['tarih']);



		//	ALINTI SİL VE DÜZENLE OLUŞTURULUYOR - BAŞI	//


$cevap_alinti_duzenle = '';

if (isset($kullanici_kim['id']))
{
	if ($satir_sayi < $ayarlar['ksyfkota'])
		$cevap_alinti_duzenle .= '<a href="mesaj_yaz.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevapla&amp;mesaj_no='.$mesaj_satir['id'].'&amp;cevap_no='.$cevap_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;alinti=cevap">';

	elseif ( ($satir_sayi % $ayarlar['ksyfkota']) == 0 )
		$cevap_alinti_duzenle .= '<a href="mesaj_yaz.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevapla&amp;mesaj_no='.$mesaj_satir['id'].'&amp;cevap_no='.$cevap_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;alinti=cevap&amp;sayfa='.$satir_sayi.'">';

	else
	{
		$y_sayi = $satir_sayi - ($satir_sayi % $ayarlar['ksyfkota']);
		$cevap_alinti_duzenle .= '<a href="mesaj_yaz.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevapla&amp;mesaj_no='.$mesaj_satir['id'].'&amp;cevap_no='.$cevap_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;alinti=cevap&amp;sayfa='.$y_sayi.'">';
	}

	$cevap_alinti_duzenle .= '<img '.$simge_alinti.' alt="." title="Alıntı yaparak cevapla"></a>&nbsp;&nbsp;';
}


			//	KULLANICIYA GÖRE SİL VE DÜZENLE - BAŞI		//


//	YÖNETİCİ VE YARDIMCI İSE	//

if ( ($kullanici_kim['yetki'] == 1) OR ($kullanici_kim['yetki'] == 2) ):

$cevap_alinti_duzenle .= '<a href="phpkf-bilesenler/mesaj_sil.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevap&amp;mesaj_no='.$mesaj_satir['id'].'&amp;cevap_no='.$cevap_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;sayfa='.$_GET['ks'].'"><img '.$simge_sil.'  alt="." title="Bu cevabı sil"></a>&nbsp;&nbsp;';

$cevap_alinti_duzenle .= '<a href="mesaj_degistir.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevap&amp;mesaj_no='.$mesaj_satir['id'].'&amp;cevap_no='.$cevap_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;sayfa='.$_GET['ks'].'"><img '.$simge_degistir.' alt="." title="Bu cevabı değiştir"></a>&nbsp;&nbsp;';

$cevap_alinti_duzenle .= '<a href="phpkf-yonetim/forum_ip_yonetimi.php?kip=1&amp;ip='.$cevap_satir['yazan_ip'].'"><img  '.$simge_ip.' alt="." title="Bu cevabı yazanın ip adresi"></a>&nbsp;&nbsp;';


//	BÖLÜM YARDIMCI İSE	//

elseif ($kullanici_kim['yetki'] == 3):

if ( (isset($yrd_yetkisi)) AND ($yrd_yetkisi == true) ):

$cevap_alinti_duzenle .= '<a href="phpkf-bilesenler/mesaj_sil.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevap&amp;mesaj_no='.$mesaj_satir['id'].'&amp;cevap_no='.$cevap_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;sayfa='.$_GET['ks'].'"><img '.$simge_sil.' alt="." title="Bu cevabı sil"></a>&nbsp;&nbsp;';

$cevap_alinti_duzenle .= '<a href="mesaj_degistir.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevap&amp;mesaj_no='.$mesaj_satir['id'].'&amp;cevap_no='.$cevap_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;sayfa='.$_GET['ks'].'"><img '.$simge_degistir.' alt="." title="Bu cevabı değiştir"></a>&nbsp;&nbsp;';


//	BU FORUMUN YARDIMCISI OLMADIĞI HALDE İLETİYİ YAZANSA	//

elseif ($kullanici_kim['kullanici_adi'] == $cevap_satir['cevap_yazan']):
	$cevap_alinti_duzenle .= '<a href="mesaj_degistir.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevap&amp;mesaj_no='.$mesaj_satir['id'].'&amp;cevap_no='.$cevap_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;sayfa='.$_GET['ks'].'"><img '.$simge_degistir.' alt="." title="Bu cevabı değiştir"></a>&nbsp;&nbsp;';
endif;


//	İLETİYİ YAZAN KİŞİYSE	//

elseif ($kullanici_kim['kullanici_adi'] == $cevap_satir['cevap_yazan']):
	$cevap_alinti_duzenle .= '<a href="mesaj_degistir.php?fno='.$mesaj_satir['hangi_forumdan'].'&amp;kip=cevap&amp;mesaj_no='.$mesaj_satir['id'].'&amp;cevap_no='.$cevap_satir['id'].'&amp;fsayfa='.$_GET['fs'].'&amp;sayfa='.$_GET['ks'].'"><img '.$simge_degistir.' alt="." title="Bu cevabı değiştir"></a>&nbsp;&nbsp;';

endif;



			//	KULLANICIYA GÖRE SİL VE DÜZENLE - SONU			//




	//	CEVAPLARIN İÇERİĞİ YAZDIRILIYOR	//
	//	VARSA İMZA VE DEĞİŞTİRME BİLGİLERİ YAZDIRILIYOR	//


if ($cevap_satir['ifade'] == 1)
    $cevap_satir['cevap_icerik'] = ifadeler($cevap_satir['cevap_icerik']);

if ( ($cevap_satir['bbcode_kullan'] == 1) AND ($ayarlar['bbcode'] == 1) )
	$cevap_icerik = bbcode_acik($cevap_satir['cevap_icerik'],$cevap_satir['id']);

else $cevap_icerik = bbcode_kapali($cevap_satir['cevap_icerik']);

if ( (isset($cevap_sahibi['imza'])) and ($cevap_sahibi['imza']!='') )
{
	if ($ayarlar['bbcode'] == 1) $cevap_yazan_imza = bbcode_acik(ifadeler($cevap_sahibi['imza']),1);
	else $cevap_yazan_imza = bbcode_kapali(ifadeler($cevap_sahibi['imza']));
}

else $cevap_yazan_imza = '';




		//		İLETİ DEĞİŞTİRİLME BİLGİLERİ	//

$cevap_degisme = '';

if ($cevap_satir['degistirme_sayisi'] != 0):
	$cevap_degisme .= '<hr class="ileti_degisim_bilgi" /><font size="1"><i> Bu ileti en son <b>'.$cevap_satir['degistiren'].'</b>
tarafından <b>'.zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $cevap_satir['degistirme_tarihi']).'</b> tarihinde, toplamda '.$cevap_satir['degistirme_sayisi'].' kez değiştirilmiştir.</i></font>';

if ($kullanici_kim['yetki'] == 1):
	$cevap_degisme .= '&nbsp;<a href="phpkf-yonetim/forum_ip_yonetimi.php?kip=1&amp;ip='.$cevap_satir['degistiren_ip'].'"><img '.$simge_ip.' alt="." title="Bu cevabı değiştirenin ip adresi"></a>';

endif;
endif;



$cevap_bag = '<a href="'.linkver('konu.php?k='.$mesaj_satir['id'].'&ks='.$_GET['ks'], $mesaj_satir['mesaj_baslik'], '#c'.$cevap_satir['id']).'" style="color:#ffffff;text-decoration:none" title="Cevap bağlantısı">'.$l['cevap'].': '.$ileti_no.'</a>';



//	veriler tema motoruna yollanıyor	//

$tekli1[] = array('{CEVAP_ANAME}' => $cevap_aname,
'{CEVAP_BASLIK}' => $cevap_satir['cevap_baslik'],
'{ILETI_NO}' => $cevap_bag,
'{CEVAP_YAZAN}' => $cevap_yazan,
'{CEVAP_YAZAN_ADI}' => $cevap_yazan_adi,
'{CEVAP_YAZAN_YETKISI}' => $cevap_yazan_yetkisi,
'{CEVAP_YAZAN_RESMI}' => $cevap_yazan_resmi,
'{CEVAP_YAZAN_KAYIT}' => $cevap_yazan_kayit,
'{CEVAP_YAZAN_MESAJSAYI}' => NumaraBicim($cevap_yazan_mesajsayi),
'{CEVAP_YAZAN_SEHIR}' => $cevap_yazan_sehir,
'{CEVAP_YAZAN_DURUM}' => $cevap_yazan_durum,
'{CEVAP_YAZAN_EPOSTA}' => $cevap_yazan_eposta,
'{CEVAP_YAZAN_WEB}' => $cevap_yazan_web,
'{CEVAP_YAZAN_OZEL}' => $cevap_yazan_ozel,
'{CEVAP_TARIHI}' => $cevap_tarihi,
'{CEVAP_ALINTI_DUZENLE}' => $cevap_alinti_duzenle,
'{CEVAP_ICERIK}' => $cevap_icerik,
'{CEVAP_YAZAN_IMZA}' => $cevap_yazan_imza,
'{CEVAP_DEGISTIRME}' => $cevap_degisme);


endwhile;
endif;



				//		CEVAP TABLOLARI	SONU		//




if (isset($kullanici_kim['id']))
	$kullanici_cikis = '&nbsp; | &nbsp; <a href="'.$phpkf_dosyalar['cikis'].'?o='.$o.'" onclick="return window.confirm(\'Çıkış yapmak istediğinize emin misiniz?\')">Çıkış [ '.$kullanici_kim['kullanici_adi'].' ]</a>';

else $kullanici_cikis = '';



//  KONUYU GÖRÜNTÜLEYENLER

if ($ayarlar['konu_kisi'] == 1)
{
	$gor_usayi = 0;
	$gor_usayi2 = 0;
	$gor_uyeler = '';

	$vtsonuc = $vt->query("SELECT sid FROM $tablo_oturumlar WHERE (sayfano LIKE '2,$mesaj_satir[id],%') AND (son_hareket + $zaman_asimi) > $tarih") or die ($vt->hata_ver());
	$gor_msayi = $vt->num_rows($vtsonuc);


	$vtsonuc = $vt->query("SELECT id,kullanici_adi,gizli FROM $tablo_kullanicilar WHERE (sayfano LIKE '2,$mesaj_satir[id],%') AND (son_hareket + $zaman_asimi) > $tarih AND sayfano!='-1'") or die ($vt->hata_ver());

	while ($gor_uye = $vt->fetch_assoc($vtsonuc))
	{
		if ($gor_uye['gizli'] == 0)
		{
			$gor_uyeler .= '<a href="'.linkver($phpkf_dosyalar['profil'].'?u='.$gor_uye['id'].'&kim='.$gor_uye['kullanici_adi'],$gor_uye['kullanici_adi']).'">'.$gor_uye['kullanici_adi'].'</a>, ';
			$gor_usayi++;
		}

		else
		{
			if ((isset($kullanici_kim['yetki'])) AND ($kullanici_kim['yetki'] == 1))
				$gor_uyeler .= '<a href="'.linkver($phpkf_dosyalar['profil'].'?u='.$gor_uye['id'].'&kim='.$gor_uye['kullanici_adi'],$gor_uye['kullanici_adi']).'"><i>'.$gor_uye['kullanici_adi'].'</i></a>, ';
			$gor_usayi2++;
		}
	}

	if ($gor_uyeler == '') $gor_uyeler = 'Bu konuyu görüntüleyen üye yok.';

	$gor_kisi = 'Bu konuyu '.($gor_msayi + $gor_usayi + $gor_usayi2).' kişi görüntülüyor:&nbsp; '.$gor_msayi.' Misafir, '.($gor_usayi + $gor_usayi2).' Üye';
	if ($gor_usayi2 != 0) $gor_kisi .= ' ('.$gor_usayi2.' tanesi gizli)';
}

else {$gor_kisi = ''; $gor_uyeler = '';}




// link ağacı
$forum_anasayfa = '<span><a href="'.$phpkf_dosyalar['forum'].'">'.$l['forum'].' '.$l['anasayfa'].'</a></span>';
$konu_baslik = '<span>'.$mesaj_satir['mesaj_baslik'].'</span>';

if ($forum_satir['alt_forum'] != '0')
{
	$alt_forum_baslik = '<span><a href="'.linkver('forum.php?f='.$mesaj_satir['hangi_forumdan'].'&fs='.$_GET['fs'], $forum_satir['forum_baslik']).'">'.$forum_satir['forum_baslik'].'</a></span>';

	$vtsorgu = "SELECT id,forum_baslik FROM $tablo_forumlar WHERE id='$forum_satir[alt_forum]' LIMIT 1";
	$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$forum_satir = $vt->fetch_assoc($vtsonuc2);

	$ust_forum_baslik = '<span><a href="'.linkver('forum.php?f='.$forum_satir['id'], $forum_satir['forum_baslik']).'">'.$forum_satir['forum_baslik'].'</a></span>';
}

else
{
	$ust_forum_baslik = '<span><a href="'.linkver('forum.php?f='.$mesaj_satir['hangi_forumdan'].'&fs='.$_GET['fs'], $forum_satir['forum_baslik']).'">'.$forum_satir['forum_baslik'].'</a></span>';
	$alt_forum_baslik = '';
}


$forumlar_arasi_gecis = '';
$etiketler_cikti = '';
$benzer_cikti = '';


//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/konu.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


$dongusuz = array('{FORUM_ANASAYFA}' => $forum_anasayfa,
'{FORUM_BASLIK}' => $ust_forum_baslik,
'{KONU_BASLIK}' => $konu_baslik,
'{SAYFALAMA}' => $sayfalama_cikis,
'{BASLIK_CEVAP}' => $baslik_cevap,
'{KULLANICI_CIKIS}' => $kullanici_cikis,
'{ALT_FORUM_BASLIK}' => $alt_forum_baslik,
'{GOR_KISI}' => $gor_kisi,
'{GOR_UYELER}' => $gor_uyeler,
'{BENZER_KONULAR}' => $benzer_cikti,
'{ETIKETLER}' => $etiketler_cikti,
'{FORUMLAR_ARASI_GECIS}' => $forumlar_arasi_gecis);

$ornek1->dongusuz($dongusuz);


//	sadece birinci sayfada koşul 1 alanını göster

if (isset($kosul1))
	$ornek1->kosul('1', $kosul1, true);

else	$ornek1->kosul('1', array('' => ''), false);


//	cevap varsa koşul 2 alalını göster

if (isset($tekli1))
{
	$ornek1->kosul('2', array('' => ''), true);
	$ornek1->tekli_dongu('1',$tekli1);
}

else	$ornek1->kosul('2', array('' => ''), false);


// sadece üyelere hızlı cevap yazma formunu göster

if (isset($kullanici_kim['id']))
{
	$form_bilgi1 = '<form action="phpkf-bilesenler/mesaj_yaz_yap.php" method="post" onsubmit="return denetle_yazi()" name="duzenleyici_form" id="duzenleyici_form">
	<input type="hidden" name="kayit_yapildi_mi" value="form_dolu">
	<input type="hidden" name="sayfa_onizleme" value="mesaj_yaz">
	<input type="hidden" name="mesaj_onizleme" value="Önizleme">
	<input type="hidden" name="fno" value="'.$mesaj_satir['hangi_forumdan'].'">
	<input type="hidden" name="kip" value="cevapla">
	<input type="hidden" name="mesaj_no" value="'.$_GET['k'].'">
	<input type="hidden" name="fsayfa" value="'.$_GET['fs'].'">
	<input type="hidden" name="sayfa" value="'.$form_ksayfa.'">';

	$ornek1->kosul('3', array('{FORM_BILGI1}' => $form_bilgi1, '{FORM_ICERIK}' => ''), true);
}

else $ornek1->kosul('3', array('' => ''), false);

if ($ayarlar['konu_kisi'] != 1) $ornek1->kosul('4', array(''=>''), false);

eval(TEMA_UYGULA);

?>