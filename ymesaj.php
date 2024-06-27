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


$arama_kota = 30;
$tarih = time();

if (empty($_GET['sayfa'])) $_GET['sayfa'] = 0;
else $_GET['sayfa'] = @zkTemizle($_GET['sayfa']);



//	SAYFA SEÇİMİNE GÖRE SORGU HAZIRLANIYOR	//

if ((isset($_GET['kip'])) AND ($_GET['kip'] == 'bugun'))
{
	// bugünün ilk saati
	$bugun = mktime(0,0,0,date('m'),date('d'),date('Y'));

	$vtsonuc9 = $vt->query("SELECT id FROM $tablo_mesajlar WHERE silinmis='0' AND son_mesaj_tarihi > '$bugun'") or die ('<h2>ARAMA SONUÇLANAMADI</h2>') or die ($vt->hata_ver());
	$satir_sayi = $vt->num_rows($vtsonuc9);

	$vtsorgu = "SELECT id,yazan,hangi_forumdan,son_mesaj_tarihi,cevap_sayi,goruntuleme,mesaj_baslik,yazan
	FROM $tablo_mesajlar WHERE silinmis='0' AND son_mesaj_tarihi > '$bugun'
	ORDER BY son_mesaj_tarihi DESC LIMIT $_GET[sayfa],$arama_kota";

	$m_arama_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


	// Kipe göre değişen veriler
	$sayfa_adi = 'Bugün Yazılan iletiler';
	$kip_ek = 'kip=bugun&amp;';
	$vtsonuc_bilgi = 'Forumda bugün bırakılan <b>'.$satir_sayi.'</b> ileti bulunmaktadır.
<br>Henüz okunmayanlar <b>kalın</b> yazılmıştır.';
}




elseif ((isset($_GET['kip'])) AND ($_GET['kip'] == 'cevapsiz'))
{
	$vtsonuc9 = $vt->query("SELECT id FROM $tablo_mesajlar WHERE cevap_sayi='0' AND silinmis='0' AND kilitli='0'") or die ('<h2>ARAMA SONUÇLANAMADI</h2>') or die ($vt->hata_ver());
	$satir_sayi = $vt->num_rows($vtsonuc9);

	$vtsorgu = "SELECT id,yazan,hangi_forumdan,son_mesaj_tarihi,cevap_sayi,goruntuleme,mesaj_baslik,yazan
	FROM $tablo_mesajlar WHERE cevap_sayi='0' AND silinmis='0' AND kilitli='0'
	ORDER BY son_mesaj_tarihi DESC LIMIT $_GET[sayfa],$arama_kota";

	$m_arama_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


	// Kipe göre değişen veriler
	$sayfa_adi = 'Cevapsız Konular';
	$kip_ek = 'kip=cevapsiz&amp;';
	$vtsonuc_bilgi = 'Forumda cevapsız <b>'.$satir_sayi.'</b> konu bulunmaktadır.
<br>Henüz okunmayanlar <b>kalın</b> yazılmıştır.';
}




elseif ((isset($_GET['kip'])) AND ($_GET['kip'] == 'takip'))
{
	if ($kullanici_kim['takip'] != '')
	{
		$takip_dizi = explode(";", $kullanici_kim['takip']);
		$takip_eksorgu = '';

		foreach ($takip_dizi as $takip_tek)
		{
			if (preg_match('/^f-/i', $takip_tek))
				$takip_eksorgu .= " silinmis='0' AND hangi_forumdan='".substr($takip_tek,2)."' AND son_mesaj_tarihi > '$kullanici_kim[son_giris]' OR";
		}
		$takip_eksorgu = substr($takip_eksorgu, 0, -2);


		$vtsonuc9 = $vt->query("SELECT id FROM $tablo_mesajlar WHERE $takip_eksorgu") or die ($vt->hata_ver());
		$satir_sayi = $vt->num_rows($vtsonuc9);

		$vtsorgu = "SELECT id,yazan,hangi_forumdan,son_mesaj_tarihi,cevap_sayi,goruntuleme,mesaj_baslik,yazan
		FROM $tablo_mesajlar WHERE $takip_eksorgu
		ORDER BY son_mesaj_tarihi DESC LIMIT $_GET[sayfa],$arama_kota";

		$m_arama_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	}

	else
	{
		$satir_sayi = 0;
		$takip_bilgi = 'Takip ettiğiniz herhangi bir bölüm bulunmaktadır.
		<br>Ayarlamak için <a href="profil_degistir.php?kosul=takip">şu sayfaya</a> bakın.';
	}

	// Kipe göre değişen veriler
	$sayfa_adi = 'Takip Edilen iletiler';
	$kip_ek = 'kip=takip&amp;';
	$vtsonuc_bilgi = 'Takip ettiğiniz bölüm(ler)de <b>'.$satir_sayi.'</b> yeni ileti bulunmaktadır.
	<br>Henüz okunmayanlar <b>kalın</b> yazılmıştır.';
}




else
{
	$vtsonuc9 = $vt->query("SELECT id FROM $tablo_mesajlar WHERE silinmis='0' AND son_mesaj_tarihi > '$kullanici_kim[son_giris]'") or die ($vt->hata_ver());
	$satir_sayi = $vt->num_rows($vtsonuc9);

	$vtsorgu = "SELECT id,yazan,hangi_forumdan,son_mesaj_tarihi,cevap_sayi,goruntuleme,mesaj_baslik,yazan
	FROM $tablo_mesajlar WHERE silinmis='0' AND son_mesaj_tarihi > '$kullanici_kim[son_giris]'
	ORDER BY son_mesaj_tarihi DESC LIMIT $_GET[sayfa],$arama_kota";

	$m_arama_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


	// Kipe göre değişen veriler
	$sayfa_adi = 'Yeni iletiler';
	$kip_ek = '';
	$vtsonuc_bilgi = 'Son gelişinizden sonra bırakılan <b>'.$satir_sayi.'</b> ileti bulunmaktadır.
<br>Henüz okunmayanlar <b>kalın</b> yazılmıştır.';
}



$toplam_sayfa = ($satir_sayi / $arama_kota);
settype($toplam_sayfa,'integer');
if (($satir_sayi % $arama_kota) != 0) $toplam_sayfa++;
if (empty($satir_sayi)) $satir_sayi = 0;



//	BAŞLIK DAHİL EDİLİYOR	//

$sayfano = 34;
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');




//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/ymesaj.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));




        //      ARAMA SONUÇLARI SIRALANIYOR BAŞLANGIÇ       //



if ($satir_sayi <= 0):


//	veriler tema motoruna yollanıyor	//

if ((isset($_GET['kip'])) AND ($_GET['kip'] == 'bugun'))
	$yeni_mesaj_yok = 'Bugün bırakılan ileti yok.';
elseif ((isset($_GET['kip'])) AND ($_GET['kip'] == 'cevapsiz'))
	$yeni_mesaj_yok = 'Cevapsız konu yok.';
elseif ((isset($_GET['kip'])) AND ($_GET['kip'] == 'takip'))
{
	if (isset($takip_bilgi)) $yeni_mesaj_yok = $takip_bilgi;
	else $yeni_mesaj_yok = 'Takip ettiğiniz bölüm(ler)de yeni ileti yok.';
}
else $yeni_mesaj_yok = 'Son gelişinizden sonra bırakılan ileti yok.';

$vtsonuc_bilgi = '';

$ornek1->kosul('2', array('' => ''), false);
$ornek1->kosul('1', array('{YENI_MESAJ_YOK}' => $yeni_mesaj_yok), true);



elseif ($satir_sayi > 0):

// ALT FORUM BİLGİLERİ ÇEKİLİYOR	//

$vtsorgu = "SELECT id,forum_baslik FROM $tablo_forumlar ORDER BY dal_no, sira";
$vtsonuc3 = $vt->query($vtsorgu) or die ($vt->hata_ver());

while ($forum_satir = $vt->fetch_array($vtsonuc3))
	$tumforum_satir[$forum_satir['id']] = $forum_satir['forum_baslik'];

$yeni_mesaj_yok = '';



while ($m_arama_satir = $vt->fetch_assoc($m_arama_sonuc)):

$konu_baslik = '<a href="konu.php?k='.$m_arama_satir['id'].'">';



//  OKUNMAMIŞ MESAJLARI KALIN YAZDIR  //

if ($m_arama_satir['son_mesaj_tarihi'] < $kullanici_kim['son_giris'])
	$konu_baslik .= $m_arama_satir['mesaj_baslik'].'</a>';

elseif (isset($_COOKIE['kfk_okundu']))
{
	$cerez_dizi = explode('_', $_COOKIE['kfk_okundu']);

	foreach ($cerez_dizi as $cerez_parcala)
	{
		$okunan_kno = substr($cerez_parcala, 11);
		$okunan_dizi[$okunan_kno] = substr($cerez_parcala, 0, 10);
	}

	if ( (empty($okunan_dizi[$m_arama_satir['id']])) OR ($m_arama_satir['son_mesaj_tarihi'] > $okunan_dizi[$m_arama_satir['id']]) )
		$konu_baslik .= '<b>'.$m_arama_satir['mesaj_baslik'].'</b></a>';

	else $konu_baslik .= $m_arama_satir['mesaj_baslik'].'</a>';
}

else $konu_baslik .= '<b>'.$m_arama_satir['mesaj_baslik'].'</b></a>';



$forum_baslik = '<a href="forum.php?f='.$m_arama_satir['hangi_forumdan'].'">'.$tumforum_satir[$m_arama_satir['hangi_forumdan']].'</a>';

$yazan = '<a href="profil.php?kim='.$m_arama_satir['yazan'].'">'.$m_arama_satir['yazan'].'</a>';

$sonmesaj_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $m_arama_satir['son_mesaj_tarihi']);

$sonmesaj_baglanti = '';



//	CEVAP YOKSA MESAJ TARİHİNİ YAZ	//

if ($m_arama_satir['cevap_sayi'] == 0)
$sonmesaj_baglanti .= '<a href="profil.php?kim='.$m_arama_satir['yazan'].'">'.$m_arama_satir['yazan'].'</a>&nbsp;<a href="konu.php?k='.$m_arama_satir['id'].'" style="text-decoration: none">&nbsp;<img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="Son iletiye git" title="Son iletiye git">&nbsp;</a>';


//	CEVAP VARSA SON MESAJ BİLGİLERİ ÇEKİLİYOR	//

else
{
	$vtsorgu = "SELECT id,cevap_yazan FROM $tablo_cevaplar WHERE silinmis='0' AND hangi_basliktan='$m_arama_satir[id]' ORDER BY tarih DESC LIMIT 1";
	$vtsonuc2 = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$son_mesaj = $vt->fetch_assoc($vtsonuc2);
	$sonmesaj_baglanti .= '<a href="profil.php?kim='.$son_mesaj['cevap_yazan'].'">'.$son_mesaj['cevap_yazan'].'</a>';


	//  BAŞLIK ÇOK SAYFALI İSE SON SAYFAYA GİT  //

	if ($m_arama_satir['cevap_sayi'] > $ayarlar['ksyfkota'])
	{
		$sayfaya_git = (($m_arama_satir['cevap_sayi']-1) / $ayarlar['ksyfkota']);
		settype($sayfaya_git,'integer');
		$sayfaya_git = ($sayfaya_git * $ayarlar['ksyfkota']);

		$sonmesaj_baglanti .= '&nbsp;<a href="konu.php?k='.$m_arama_satir['id'].'&amp;ks='.$sayfaya_git.'#c'.$son_mesaj['id'].'" style="text-decoration: none">';
	}

	else $sonmesaj_baglanti .= '&nbsp;<a href="konu.php?k='.$m_arama_satir['id'].'#c'.$son_mesaj['id'].'" style="text-decoration: none">';

	$sonmesaj_baglanti .= '&nbsp;<img src="'.$sonileti_rengi.'" border="0" width="13" height="9" alt="Son iletiye git" title="Son iletiye git">&nbsp;</a>';
}




//	veriler tema motoruna yollanıyor	//

$tekli1[] = array('{SONUC_SAYISI}' => $satir_sayi,
'{KONU_BASLIK}' => $konu_baslik,
'{FORUM_BASLIK}' => $forum_baslik,
'{YAZAN}' => $yazan,
'{CEVAP}' => NumaraBicim($m_arama_satir['cevap_sayi']),
'{GOSTERIM}' => NumaraBicim($m_arama_satir['goruntuleme']),
'{SONMESAJ_TARIH}' => $sonmesaj_tarih,
'{SONMESAJ_BAGLANTI}' => $sonmesaj_baglanti);



endwhile;






				//	SAYFALAMA BAŞLANGIÇ	//


$sayfalama = '';

if ($satir_sayi > $arama_kota):
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
	$sayfalama .= '&nbsp;<a href="ymesaj.php?'.$kip_ek.'sayfa=0">&laquo;ilk</a>&nbsp;</td>';

	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="önceki sayfaya git">';
	$sayfalama .= '&nbsp;<a href="ymesaj.php?'.$kip_ek.'sayfa='.($_GET['sayfa'] - $arama_kota).'">&lt;</a>&nbsp;</td>';
}



for ($sayi=0,$sayfa_sinir=$_GET['sayfa']; $sayi < $toplam_sayfa; $sayi++)
{
	if ($sayi < (($_GET['sayfa'] / $arama_kota) - 3));
	else
	{
		$sayfa_sinir++;
		if ($sayfa_sinir >= ($_GET['sayfa'] + 8)) break;
		if (($sayi == 0) and ($_GET['sayfa'] == 0))
		{
			$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">';
			$sayfalama .= '&nbsp;<b>[1]</b>&nbsp;</td>';
		}

		elseif (($sayi + 1) == (($_GET['sayfa'] / $arama_kota) + 1))
		{
			$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">';
			$sayfalama .= '&nbsp;<b>['.($sayi + 1).']</b>&nbsp;</td>';
		}

		else
		{
			$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="'.($sayi + 1).' numaralı sayfaya git">';
			$sayfalama .= '&nbsp;<a href="ymesaj.php?'.$kip_ek.'sayfa='.($sayi * $arama_kota).'">'.($sayi + 1).'</a>&nbsp;</td>';
		}
	}
}


if ($_GET['sayfa'] < ($satir_sayi - $arama_kota))
{
	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="sonraki sayfaya git">';
	$sayfalama .= '&nbsp;<a href="ymesaj.php?'.$kip_ek.'sayfa='.($_GET['sayfa'] + $arama_kota).'">&gt;</a>&nbsp;</td>';

	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="son sayfaya git">';
	$sayfalama .= '&nbsp;<a href="ymesaj.php?'.$kip_ek.'sayfa='.(($toplam_sayfa - 1) * $arama_kota).'">son&raquo;</a>&nbsp;</td>';
}

$sayfalama .= '</tr>
	</tbody>
</table>
';

endif;




				//	SAYFALAMA BİTİŞ		//



//	veriler tema motoruna yollanıyor	//

$ornek1->kosul('1', array('' => ''), false);
$ornek1->kosul('2', array('{SAYFALAMA}' => $sayfalama), true);

$ornek1->tekli_dongu('1',$tekli1);


endif;



//  Sayfa kip bağlantıları oluşturuluyor

$bag_cevapsiz = '<a href="ymesaj.php?kip=cevapsiz">Cevapsız Konular</a>';
$bag_bugun = '<a href="ymesaj.php?kip=bugun">Bugün Yazılanlar</a>';
$bag_yeniler = '<a href="ymesaj.php">Yeniler</a>';
$bag_takip = '<a href="ymesaj.php?kip=takip">Takip Edilenler</a>';

if (isset($_GET['kip']))
{
	if ($_GET['kip'] == 'cevapsiz') $bag_cevapsiz = '<b>Cevapsız Konular</b>';
	elseif ($_GET['kip'] == 'bugun') $bag_bugun = '<b>Bugün Yazılanlar</b>';
	elseif ($_GET['kip'] == 'takip') $bag_takip = '<b>Takip Edilenler</b>';
	else $bag_yeniler = '<b>Yeniler</b>';
}

else $bag_yeniler = '<b>Yeniler</b>';





//	TEMA UYGULANIYOR	//

$ornek1->dongusuz(array('{SAYFA_BASLIK}' => $sayfa_adi,
'{BAG_YENILER}' => $bag_yeniler,
'{BAG_BUGUN}' => $bag_bugun,
'{BAG_CEVAPSIZ}' => $bag_cevapsiz,
'{BAG_TAKIP}' => $bag_takip,
'{SONUC_BILGI}' => $vtsonuc_bilgi));

eval(TEMA_UYGULA);

?>