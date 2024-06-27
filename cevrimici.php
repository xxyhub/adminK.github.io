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
include_once('phpkf-bilesenler/seo.php');


$sayfano = 5;
$sayfa_adi = 'Çevrimiçi Kullanıcılar';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');
include_once('phpkf-bilesenler/hangi_sayfada.php');


if (empty($_GET['sayfa'])) $_GET['sayfa'] = 0;
else $_GET['sayfa'] = @zkTemizle($_GET['sayfa']);


$zaman_asimi = $ayarlar['uye_cevrimici_sure'];
$tarih = time();
$cevrimici_kota = 30;


//  ÇEVRİMİÇİ KULLANICI SAYISI ÇEKİLİYOR    //

$vtsonuc9 = $vt->query("SELECT id FROM $tablo_kullanicilar WHERE (son_hareket + $zaman_asimi) > $tarih AND gizli='0' AND sayfano!='-1'") or die ($vt->hata_ver());
$kullanici_sayi = $vt->num_rows($vtsonuc9);


//  GİZLİ ÇEVRİMİÇİ KULLANICI SAYISI ALINIYOR   //

$vtsonuc9 = $vt->query("SELECT id FROM $tablo_kullanicilar WHERE (son_hareket + $zaman_asimi) > $tarih AND gizli='1' AND sayfano!='-1'") or die ($vt->hata_ver());
$gizli_sayi = $vt->num_rows($vtsonuc9);


//  ÇEVRİMİÇİ MİSAFİRLERİN SAYISI ÇEKİLİYOR //

$vtsonuc9 = $vt->query("SELECT sid FROM $tablo_oturumlar WHERE (son_hareket + $zaman_asimi) > $tarih") or die ($vt->hata_ver());
$misafir_sayi = $vt->num_rows($vtsonuc9);


if ($kullanici_sayi > $misafir_sayi) $satir_sayi = $kullanici_sayi;
else $satir_sayi = $misafir_sayi;

$toplam_sayfa = ($satir_sayi / $cevrimici_kota);
settype($toplam_sayfa,'integer');

if ( ($satir_sayi % $cevrimici_kota) != 0 ) $toplam_sayfa++;



// yönetici ise gizlileri göster
if ($kullanici_kim['yetki'] == '1') $sorgu_ek = '';
else $sorgu_ek = "AND gizli='0'";



//  ÇEVRİMİÇİ KULLANICI BİLGİLERİ ÇEKİLİYOR //

$vtsorgu = "SELECT id,kullanici_adi,son_giris,son_hareket,hangi_sayfada,sayfano,kul_ip,gizli,yetki
        FROM $tablo_kullanicilar WHERE (son_hareket + $zaman_asimi) > $tarih
        $sorgu_ek AND sayfano!='-1'
        ORDER BY son_hareket DESC LIMIT $_GET[sayfa],$cevrimici_kota";

$cevirim_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


//  ÇEVRİMİÇİ MİSAFİRLERİN BİLGİLERİ ÇEKİLİYOR  //

$vtsorgu = "SELECT giris,son_hareket,hangi_sayfada,kul_ip,sayfano
        FROM $tablo_oturumlar WHERE (son_hareket + $zaman_asimi) > $tarih
        ORDER BY son_hareket DESC LIMIT $_GET[sayfa],$cevrimici_kota";

$misafir_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());






//  ÇEVRİMİÇİ KULLANICILAR SIRALANIYOR  //

while ($cevirimici = $vt->fetch_assoc($cevirim_sonuc)):


$uye_baglanti = '<a href="'.linkver('profil.php?u='.$cevirimici['id'].'&kim='.$cevirimici['kullanici_adi'], $cevirimici['kullanici_adi']).'">';



if ($cevirimici['id'] == 1)
{
    if ($cevirimici['gizli'] == 1)
    $uye_baglanti .= '<font class="kurucu"><i>'.$cevirimici['kullanici_adi'].'</i></font></a>';
    else $uye_baglanti .= '<font class="kurucu">'.$cevirimici['kullanici_adi'].'</font></a>';
}

elseif ($cevirimici['yetki'] == 1)
{
    if ($cevirimici['gizli'] == 1)
    $uye_baglanti .= '<font class="yonetici"><i>'.$cevirimici['kullanici_adi'].'</i></font></a>';
    else $uye_baglanti .= '<font class="yonetici">'.$cevirimici['kullanici_adi'].'</font></a>';
}

elseif ($cevirimici['yetki'] == 2)
{
    if ($cevirimici['gizli'] == 1)
    $uye_baglanti .= '<font class="yardimci"><i>'.$cevirimici['kullanici_adi'].'</i></font></a>';
    else $uye_baglanti .= '<font class="yardimci">'.$cevirimici['kullanici_adi'].'</font></a>';
}

elseif ($cevirimici['yetki'] == 3)
{
    if ($cevirimici['gizli'] == 1)
    $uye_baglanti .= '<font class="blm_yrd"><i>'.$cevirimici['kullanici_adi'].'</i></font></a>';
    else $uye_baglanti .= '<font class="blm_yrd">'.$cevirimici['kullanici_adi'].'</font></a>';
}

else
{
    if ($cevirimici['gizli'] == 1)
    $uye_baglanti .= '<i>'.$cevirimici['kullanici_adi'].'</i></a>';
    else $uye_baglanti .= $cevirimici['kullanici_adi'].'</a>';
}



$uye_son_giris = zonedate2('H:i:s', $ayarlar['saat_dilimi'], false, $cevirimici['son_giris']);

$uye_son_hareket = zonedate2('H:i:s', $ayarlar['saat_dilimi'], false, $cevirimici['son_hareket']);



if ($kullanici_kim['yetki'] == 1)
{
    $uye_sayfa = HangiSayfada($cevirimici['sayfano'], $cevirimici['hangi_sayfada']);

    $uye_ip = '<a href="phpkf-yonetim/forum_ip_yonetimi.php?kip=1&amp;ip='.$cevirimici['kul_ip'].'">'.$cevirimici['kul_ip'].'</a>';
}

else
{
    if (@preg_match('/^Yönetim/', $cevirimici['hangi_sayfada']))
        $uye_sayfa = 'Yönetim Sayfaları';

    else $uye_sayfa = HangiSayfada($cevirimici['sayfano'], $cevirimici['hangi_sayfada']);

    $uye_ip = '';
}



//  veriler tema motoruna yollanıyor    //

$tekli1[] = array('{UYE_BAGLANTI}' => $uye_baglanti,
'{UYE_SON_GIRIS}' => $uye_son_giris,
'{UYE_SON_HAREKET}' => $uye_son_hareket,
'{UYE_SAYFA}' => $uye_sayfa,
'{UYE_IP}' => $uye_ip);


endwhile;




//  ÇEVRİMİÇİ MİSAFİRLER SIRALANIYOR    //

while ($misafirler = $vt->fetch_assoc($misafir_sonuc)):


$misafir_son_giris = zonedate2('H:i:s', $ayarlar['saat_dilimi'], false, $misafirler['giris']);

$misafir_son_hareket = zonedate2('H:i:s', $ayarlar['saat_dilimi'], false, $misafirler['son_hareket']);

$misafir_sayfa = HangiSayfada($misafirler['sayfano'], $misafirler['hangi_sayfada']);

if ($kullanici_kim['yetki'] == '1') $misafir_ip = '<a href="phpkf-yonetim/forum_ip_yonetimi.php?kip=1&amp;ip='.$misafirler['kul_ip'].'">'.$misafirler['kul_ip'].'</a>';
else $misafir_ip = '';


//  veriler tema motoruna yollanıyor    //

$tekli2[] = array('{MISAFIR_SON_GIRIS}' => $misafir_son_giris,
'{MISAFIR_SON_HAREKET}' => $misafir_son_hareket,
'{MISAFIR_SAYFA}' => $misafir_sayfa,
'{MISAFIR_IP}' => $misafir_ip);


endwhile;




$sayfalama = '';

if ($satir_sayi > $cevrimici_kota):

$sayfalama .= '<p>
<table cellspacing="1" cellpadding="4" border="0" align="right" class="tablo_border">
    <tr>
    <td class="forum_baslik">
Toplam '.$toplam_sayfa.' Sayfa:&nbsp;
    </td>';

if ($_GET['sayfa'] != 0)
{
    $sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="ilk sayfaya git">';
    $sayfalama .= '&nbsp;<a href="cevrimici.php?sayfa=0">&laquo;ilk</a>&nbsp;</td>';

    $sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="önceki sayfaya git">';
    $sayfalama .= '&nbsp;<a href="cevrimici.php?sayfa='.($_GET['sayfa'] - $cevrimici_kota).'">&lt;</a>&nbsp;</td>';
}

for ($sayi=0,$sayfa_sinir=$_GET['sayfa']; $sayi < $toplam_sayfa; $sayi++)
{
    if ($sayi < (($_GET['sayfa'] / $cevrimici_kota) - 3));
    else
    {
        $sayfa_sinir++;
        if ($sayfa_sinir >= ($_GET['sayfa'] + 8)) {break;}
        if (($sayi == 0) and ($_GET['sayfa'] == 0))
        {
            $sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">';
            $sayfalama .= '&nbsp;<b>[1]</b>&nbsp;</td>';
        }

        elseif (($sayi + 1) == (($_GET['sayfa'] / $cevrimici_kota) + 1))
        {
            $sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">';
            $sayfalama .= '&nbsp;<b>['.($sayi + 1).']</b>&nbsp;</td>';
        }

        else
        {
            $sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="'.($sayi + 1).' numaralı sayfaya git">';

            $sayfalama .= '&nbsp;<a href="cevrimici.php?sayfa='.($sayi * $cevrimici_kota).'">'.($sayi + 1).'</a>&nbsp;</td>';
        }
    }
}
if ($_GET['sayfa'] < ($satir_sayi - $cevrimici_kota))
{
    $sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="sonraki sayfaya git">';
    $sayfalama .= '&nbsp;<a href="cevrimici.php?sayfa='.($_GET['sayfa'] + $cevrimici_kota).'">&gt;</a>&nbsp;</td>';

    $sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="son sayfaya git">';
    $sayfalama .= '&nbsp;<a href="cevrimici.php?sayfa='.(($toplam_sayfa - 1) * $cevrimici_kota).'">son&raquo;</a>&nbsp;</td>';
}

$sayfalama .= '</tr>
</table>';

endif;




$cevrimici_sure = ($zaman_asimi/60);
$kullanici_sayi += $gizli_sayi;
$gizli_sayi = '('.$gizli_sayi.' '.$l['gizli'].')';
$cevrimici_bilgi = str_replace('{00}', $cevrimici_sure, $l['cevrimici_bilgi']);



//  TEMA UYGULANIYOR    //

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/cevrimici.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));



if ($kullanici_kim['yetki'] == '1')
{
    $gizli = ' - <b><i>Gizli</i></b>';
    $hucre_sayisi = 5;
    $ornek1->kosul('3', array('' => ''), true);
}

else
{
    $gizli = '';
    $hucre_sayisi = 4;
    $ornek1->kosul('3', array('' => ''), false);
}


// kullanıcılar
if (isset($tekli1))
{
    if ($kullanici_kim['yetki'] == '1')
    {
        $ornek1->kosul('1', array('' => ''), false);
        $ornek1->kosul('4', array('' => ''), true);
    }
    else
    {
        $ornek1->kosul('1', array('' => ''), true);
        $ornek1->kosul('4', array('' => ''), false);
    }

    $ornek1->tekli_dongu('1',$tekli1);
}

else
{
    $ornek1->kosul('1', array('' => ''), false);
    $ornek1->kosul('4', array('' => ''), false);
}



// misafiler
if (isset($tekli2))
{
    if ($kullanici_kim['yetki'] == '1')
    {
        $ornek1->kosul('2', array('' => ''), false);
        $ornek1->kosul('5', array('' => ''), true);
    }
    else
    {
        $ornek1->kosul('2', array('' => ''), true);
        $ornek1->kosul('5', array('' => ''), false);
    }

    $ornek1->tekli_dongu('2',$tekli2);
}

else
{
    $ornek1->kosul('2', array('' => ''), false);
    $ornek1->kosul('5', array('' => ''), false);
}





//  veriler tema motoruna yollanıyor    //

$dongusuz = array('{HUCRE_SAYISI}' => $hucre_sayisi,
'{KULLANICI_SAYI}' => $kullanici_sayi,
'{GIZLI}' => $gizli,
'{GIZLI_SAYI}' => $gizli_sayi,
'{KURUCU}' => $ayarlar['kurucu'],
'{YONETICI}' => $ayarlar['yonetici'],
'{YARDIMCI}' => $ayarlar['yardimci'],
'{BLM_YRD}' => $ayarlar['blm_yrd'],
'{ZAMAN_ASIMI}' => $cevrimici_sure,
'{MISAFIR SAYI}' => $misafir_sayi,
'{MISAFIR}' => 'Misafir',
'{SAYFALAMA}' => $sayfalama);


$ornek1->dongusuz($dongusuz);

eval(TEMA_UYGULA);

?>