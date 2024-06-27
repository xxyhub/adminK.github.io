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
if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';


//	KİM DEĞİŞKENİ YOKSA UYARILIYOR	//

if ( (empty($_GET['kim'])) OR ($_GET['kim'] == '') )
{
	header('Location: hata.php?hata=45');
	exit();
}

if ( (empty($_GET['kip'])) OR ($_GET['kip'] == '') )
{
	header('Location: hata.php?hata=45');
	exit();
}



$arama_kota = 30;
$tarih = time();
@session_start();
$_GET['kim'] = zkTemizle(trim($_GET['kim']));

if (empty($_GET['sayfa'])) $_GET['sayfa'] = 0;
else $_GET['sayfa'] = @zkTemizle($_GET['sayfa']);




//	KULLANICI ADI DENETLENİYOR  //

$vtsonuc9 = $vt->query("SELECT id FROM $tablo_kullanicilar WHERE kullanici_adi='$_GET[kim]' LIMIT 1") or die ($vt->hata_ver());

$satir_sayi = $vt->num_rows($vtsonuc9);

if (($satir_sayi <= 0))
{
	header('Location: hata.php?hata=46');
	exit();
}



//	İKİ İLETİ ARASI SÜRESİ DOLMAMIŞSA UYARILIYOR	//	
//	oturum açlıyor, arama zamanına bakılıyor  //

if ($_GET['sayfa'] <= 0)
{
	if ( isset($_SESSION['arama_tarih']) AND (($_SESSION['arama_tarih']) > ($tarih - 20)) )
	{
		header('Location: hata.php?hata=1');
		exit();
	}
}




//  KULLANICININ AÇTIĞI KONULAR ARANIYOR    //

if ($_GET['kip'] == 'mesaj')
{
	//	SORGU SONUCUNDAKİ TOPLAM SONUÇ SAYISI ALINIYOR	//

	$vtsonuc9 = $vt->query("SELECT id FROM $tablo_mesajlar WHERE silinmis='0' AND yazan='$_GET[kim]'") or die ($vt->hata_ver());

	$satir_sayi = $vt->num_rows($vtsonuc9);

	if ($satir_sayi > 0)
	{
		//  FORUM BİLGİLERİ ÇEKİLİYOR	//

		$vtsorgu = "SELECT id,forum_baslik FROM $tablo_forumlar ORDER BY dal_no, sira";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());

		while ($forum_satir = $vt->fetch_array($vtsonuc))
			$tumforum_satir[$forum_satir['id']] = $forum_satir['forum_baslik'];


		//  MESAJLAR TABLOSUNDA ARAMA YAPILIYOR //

		$vtsorgu = "SELECT id,hangi_forumdan,son_mesaj_tarihi,cevap_sayi,goruntuleme,mesaj_baslik
		FROM $tablo_mesajlar WHERE silinmis='0' AND yazan='$_GET[kim]'
		ORDER BY son_mesaj_tarihi DESC LIMIT $_GET[sayfa],$arama_kota";

		$km_ara_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


		//	ARAMA SONUÇ VERİRSE SON ARAMA ZAMANI OTURUMA GİRİLİYOR	//

		$_SESSION['arama_tarih'] = $tarih;
	}

	$sayfano = 37;
	$sayfa_adi = 'Üye Konu Arama: '.$_GET['kim'];
}





//  KULLANICININ YAZDIĞI CEVAPLAR ARANIYOR    //

elseif ($_GET['kip'] == 'cevap')
{
	//	SORGU SONUCUNDAKİ TOPLAM SONUÇ SAYISI ALINIYOR	//

	$vtsonuc9 = $vt->query("SELECT id FROM $tablo_cevaplar WHERE silinmis='0' AND cevap_yazan='$_GET[kim]'") or die ($vt->hata_ver());

	$satir_sayi = $vt->num_rows($vtsonuc9);

	if ($satir_sayi > 0)
	{
		//  FORUM BİLGİLERİ ÇEKİLİYOR	//

		$vtsorgu = "SELECT id,forum_baslik FROM $tablo_forumlar ORDER BY dal_no, sira";
		$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());

		while ($forum_satir = $vt->fetch_array($vtsonuc))
			$tumforum_satir[$forum_satir['id']] = $forum_satir['forum_baslik'];


		//  CEVAPLAR TABLOSUNDA ARAMA YAPILIYOR //

		$vtsorgu = "SELECT id,hangi_forumdan,hangi_basliktan,tarih,cevap_baslik
		FROM $tablo_cevaplar WHERE silinmis='0' AND cevap_yazan='$_GET[kim]'
		ORDER BY tarih DESC LIMIT $_GET[sayfa],$arama_kota";

		$km_ara_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());


		//	ARAMA SONUÇ VERİRSE SON ARAMA ZAMANI OTURUMA GİRİLİYOR	//

		$_SESSION['arama_tarih'] = $tarih;
	}

	$sayfano = 38;
	$sayfa_adi = 'Üye Cevap Arama: '.$_GET['kim'];
}



else
{
	header('Location: hata.php?hata=45');
	exit();
}



$toplam_sayfa = ($satir_sayi / $arama_kota);
settype($toplam_sayfa,'integer');
if (($satir_sayi % $arama_kota) != 0) $toplam_sayfa++;
if (empty($satir_sayi)) $satir_sayi = 0;


include_once('phpkf-bilesenler/sayfa_baslik_forum.php');


if ($satir_sayi <= 0):


if ($_GET['kip'] == 'cevap')
$vtsonuc_yok = '<b>'.$_GET['kim'].'</b> adlı kullanıcının yazdığı cevap bulunmamaktadır !';

else $vtsonuc_yok = '<b>'.$_GET['kim'].'</b> adlı kullanıcının açtığı konu bulunmamaktadır !';
$bulunan_sonuc = '';




		//      ARAMA SONUÇLARI SIRALANIYOR BAŞLANGIÇ       //




elseif ($satir_sayi > 0):

if ($_GET['kip'] == 'cevap')
$bulunan_sonuc = '<b>'.$_GET['kim'].'</b> adlı kullanıcının yazdığı &nbsp;<b>'.$satir_sayi.'</b>&nbsp; adet cevap bulundu.';

else $bulunan_sonuc = '<b>'.$_GET['kim'].'</b> adlı kullanıcının açtığı &nbsp;<b>'.$satir_sayi.'</b>&nbsp; adet konu bulundu.';



//	ARAMA SONUÇLARI SIRALANIYOR	//

while ($km_ara_satir = $vt->fetch_assoc($km_ara_sonuc)):

if ($_GET['kip'] == 'cevap')
{
	// cevabın bağlı olduğu konunun bilgileri çekiliyor
	$vtsorgu = "SELECT mesaj_baslik,cevap_sayi,goruntuleme,son_mesaj_tarihi FROM $tablo_mesajlar WHERE id='$km_ara_satir[hangi_basliktan]' LIMIT 1";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	$konu_satir = $vt->fetch_assoc($vtsonuc);


	// cevabın kaçıncı sırada olduğu hesaplanıyor
	$vtsonuc9 = $vt->query("SELECT id FROM $tablo_cevaplar WHERE silinmis='0' AND hangi_basliktan='$km_ara_satir[hangi_basliktan]' AND id < $km_ara_satir[id]") or die ($vt->hata_ver());
	$cavabin_sirasi = $vt->num_rows($vtsonuc9);

	$sayfaya_git = ($cavabin_sirasi / $ayarlar['ksyfkota']);
	settype($sayfaya_git,'integer');
	$sayfaya_git = ($sayfaya_git * $ayarlar['ksyfkota']);

	if ($sayfaya_git != 0) $sayfaya_git = '&amp;ks='.$sayfaya_git;
	else $sayfaya_git = '';


	$km_ara_satir['cevap_sayi'] = $konu_satir['cevap_sayi'];
	$km_ara_satir['goruntuleme'] = $konu_satir['goruntuleme'];

	$baslik_baglanti = '<a href="konu.php?k='.$km_ara_satir['hangi_basliktan'].$sayfaya_git.'#c'.$km_ara_satir['id'].'">'.$konu_satir['mesaj_baslik'].' &raquo; '.$km_ara_satir['cevap_baslik'].'</a>';

	$sonu_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $km_ara_satir['tarih']);
}


else
{
	$baslik_baglanti = '<a href="konu.php?k='.$km_ara_satir['id'].'">'.$km_ara_satir['mesaj_baslik'].'</a>';

	$sonu_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $km_ara_satir['son_mesaj_tarihi']);
}

$forum_baglanti = '<a href="forum.php?f='.$km_ara_satir['hangi_forumdan'].'">'.$tumforum_satir[$km_ara_satir['hangi_forumdan']].'</a>';



//	veriler tema motoruna yollanıyor	//

$tekli1[] = array('{BASLIK_BAGLANTI}' => $baslik_baglanti,
'{FORUM_BAGLANTI}' => $forum_baglanti,
'{YAZAN}' => $_GET['kim'],
'{CEVAP_SAYI}' => $km_ara_satir['cevap_sayi'],
'{GORUNTULEME}' => $km_ara_satir['goruntuleme'],
'{TARIH}' => $sonu_tarih);



endwhile;




// 	SAYFALAR BAŞLANGIÇ 	//

$sayfalama = '';

if ($satir_sayi > $arama_kota): 

$sayfalama = '<p>
<table cellspacing="1" cellpadding="4" border="0" align="right" class="tablo_border">
	<tbody>
	<tr>
	<td class="forum_baslik">
Toplam '.$toplam_sayfa.' Sayfa:&nbsp;
	</td>';


if ($_GET['sayfa'] != 0)
{
	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="ilk sayfaya git">';
	$sayfalama .= '&nbsp;<a href="km_ara.php?kip='.$_GET['kip'].'&amp;kim='.$_GET['kim'].'&amp;sayfa=0">&laquo;ilk</a>&nbsp;</td>';

	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="önceki sayfaya git">';
	$sayfalama .= '&nbsp;<a href="km_ara.php?kip='.$_GET['kip'].'&amp;kim='.$_GET['kim'].'&amp;sayfa='.($_GET['sayfa'] - $arama_kota).'">&lt;</a>&nbsp;</td>';
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
			$sayfalama .= '&nbsp;<a href="km_ara.php?kip='.$_GET['kip'].'&amp;kim='.$_GET['kim'].'&amp;sayfa='.($sayi * $arama_kota).'">'.($sayi + 1).'</a>&nbsp;</td>';
		}
	}
}


if ($_GET['sayfa'] < ($satir_sayi - $arama_kota))
{	
	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="sonraki sayfaya git">';
	$sayfalama .= '&nbsp;<a href="km_ara.php?kip='.$_GET['kip'].'&amp;kim='.$_GET['kim'].'&amp;sayfa='.($_GET['sayfa'] + $arama_kota).'">&gt;</a>&nbsp;</td>';

	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="son sayfaya git">';
	$sayfalama .= '&nbsp;<a href="km_ara.php?kip='.$_GET['kip'].'&amp;kim='.$_GET['kim'].'&amp;sayfa='.(($toplam_sayfa - 1) * $arama_kota).'">son&raquo;</a>&nbsp;</td>';

}
$sayfalama .= '</tr>
	</tbody>
</table>';


// 	SAYFALAR BİTİŞ 	//


endif;
endif;




//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/km_ara.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


if (isset($tekli1))
{
	$ornek1->kosul('1', array('' => ''), false);
	$ornek1->kosul('2', array('{BULUNAN_SONUC}' => $bulunan_sonuc,
		'{SAYFALAMA}' => $sayfalama), true);
	$ornek1->tekli_dongu('1',$tekli1);
}

else
{
	$ornek1->kosul('2', array('' => ''), false);
	$ornek1->kosul('1', array('{SONUC_YOK}' => $vtsonuc_yok), true);
}


$ornek1->dongusuz(array('{BULUNAN_SONUC}' => $bulunan_sonuc));
eval(TEMA_UYGULA);

?>