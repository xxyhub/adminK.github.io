<?php



if (!defined('DOSYA_AYAR')) include 'ayar.php';
if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';


// arama sonuç renklendirme
function SonucRenklendir($metin, $sozcuk)
{
	$donen = @str_ireplace($sozcuk,'<b style="background: #ffff00; color: #000000;">'.$sozcuk.'</b>',$metin);
	return $donen;
}


// arama sonuç satır atlama ve ifade ekleme
function SonucDuzenle($metin)
{
	$metin = @str_replace("\n", '<br>', mb_substr($metin, 0, 500, 'utf-8')).'....';
	return $metin;
}




// Alanlar değişkenlere aktarılıyor

$arama_kota = 20;

if (isset($_GET['sayfa'])) $sayfa = @zkTemizleNumara($_GET['sayfa']);
else $sayfa = 0;

if (isset($_GET['tarih'])) $arama_tarih = @zkTemizle($_GET['tarih']);
else $arama_tarih = 'tum_zamanlar';

if (isset($_GET['sozcuk_aynen'])) $sozcuk_aynen = trim(zkTemizle($_GET['sozcuk_aynen']));
else $sozcuk_aynen = '';

if (isset($_GET['sozcuk_hepsi'])) $sozcuk_hepsi = trim(zkTemizle($_GET['sozcuk_hepsi']));
else $sozcuk_hepsi = '';

if (isset($_GET['sozcuk_herhangi'])) $sozcuk_herhangi = trim(zkTemizle($_GET['sozcuk_herhangi']));
else $sozcuk_herhangi = '';

if (isset($_GET['sozcuk_haric'])) $sozcuk_haric = trim(zkTemizle($_GET['sozcuk_haric']));
else $sozcuk_haric = '';

if (isset($_GET['yazar_ara'])) $yazar_ara = trim(zkTemizle($_GET['yazar_ara']));
else $yazar_ara = '';

if (isset($_GET['forum'])) $sozcuk_forum = trim(zkTemizle($_GET['forum']));
else $sozcuk_forum = 1;




// Alanlar kontrol ediliyor
$sozcuk_aynen = str_replace('%','',$sozcuk_aynen);
$sozcuk_hepsi = str_replace('%','',$sozcuk_hepsi);
$sozcuk_herhangi = str_replace('%','',$sozcuk_herhangi);


if (($sozcuk_aynen=='')AND($sozcuk_hepsi=='')AND($sozcuk_herhangi=='')AND(strlen($yazar_ara)>=4)) $sozcuk_aynen = '%%%';


if ($sozcuk_aynen == '') $sozcuk_aynen = '%';
else $sozcuk_aynen = @str_replace('*','%',$sozcuk_aynen);


if ($sozcuk_hepsi == '') $sozcuk_hepsi = '%';
else $sozcuk_hepsi = @str_replace('*','%',$sozcuk_hepsi);


if ($sozcuk_herhangi == '') $sozcuk_herhangi = '%';
else $sozcuk_herhangi = @str_replace('*','%',$sozcuk_herhangi);


if ($sozcuk_haric == '') $sozcuk_haric = '%';
else $sozcuk_haric = @str_replace('*','%',$sozcuk_haric);


if ($yazar_ara == '') $yazar_ara = '%';
if (strlen($yazar_ara) >= 4)
{
	$yazar_ara = @str_replace('*','%',$yazar_ara);
	$myazan_ara = " $tablo_mesajlar.yazan LIKE '$yazar_ara' AND ";
	$cyazan_ara = " $tablo_cevaplar.cevap_yazan LIKE '$yazar_ara' AND ";
}
else
{
	$myazan_ara = '';
	$cyazan_ara = '';
}






//	ARAMA YAPILMIŞSA ÇALIŞTIRILACAK KODLAR - BAŞI	//

if ( !empty($_GET['b']) ):




//	İKİ İLETİ ARASI SÜRESİ DOLMAMIŞSA UYARILIYOR	//
//	oturum açlıyor, arama zamanına bakılıyor  //

@session_start();
$tarih = time();

if ( ($sayfa <= 0) AND (isset($_GET['a'])) )
{
	if ( isset($_SESSION['arama_tarih']) AND ($_SESSION['arama_tarih'] > ($tarih - 5)) )
	{
		header('Location: hata.php?hata=1');
		exit();
	}
}




//  SEÇİLİ TARİH HESAPLANIYOR   //

if ( (isset($arama_tarih)) AND ($arama_tarih != '') )
{
	switch($arama_tarih)
	{
		case 'tum_zamanlar';
		$msecili_tarih = '';
		$csecili_tarih = '';
		break;

		case '1gun';
		$msecili_tarih = "AND $tablo_mesajlar.tarih > ".($tarih - 86400);
		$csecili_tarih = "AND $tablo_cevaplar.tarih > ".($tarih - 86400);
		break;

		case '3gun';
		$msecili_tarih = "AND $tablo_mesajlar.tarih > ".($tarih - 259200);
		$csecili_tarih = "AND $tablo_cevaplar.tarih > ".($tarih - 259200);
		break;

		case '1hafta';
		$msecili_tarih = "AND $tablo_mesajlar.tarih > ".($tarih - 604800);
		$csecili_tarih = "AND $tablo_cevaplar.tarih > ".($tarih - 604800);
		break;

		case '2hafta';
		$msecili_tarih = "AND $tablo_mesajlar.tarih > ".($tarih - 1296000);
		$csecili_tarih = "AND $tablo_cevaplar.tarih > ".($tarih - 1296000);
		break;

		case '1ay';
		$msecili_tarih = "AND $tablo_mesajlar.tarih > ".($tarih - 2592000);
		$csecili_tarih = "AND $tablo_cevaplar.tarih > ".($tarih - 2592000);
		break;

		case '3ay';
		$msecili_tarih = "AND $tablo_mesajlar.tarih > ".($tarih - 7776000);
		$csecili_tarih = "AND $tablo_cevaplar.tarih > ".($tarih - 7776000);
		break;

		case '6ay';
		$msecili_tarih = "AND $tablo_mesajlar.tarih > ".($tarih - 15552000);
		$csecili_tarih = "AND $tablo_cevaplar.tarih > ".($tarih - 15552000);
		break;

		case '1sene';
		$msecili_tarih = "AND $tablo_mesajlar.tarih > ".($tarih - 31536000);
		$csecili_tarih = "AND $tablo_cevaplar.tarih > ".($tarih - 31536000);
		break;

		default:
		$msecili_tarih = '';
		$csecili_tarih = '';
	}
}


else
{
	$msecili_tarih = '';
	$csecili_tarih = '';
}










//		HANGİ ALANDA KAÇ KELİME OLDUĞUNA BAKILARAK...	//
//		...	SORGUSUNUN WHERE KISMI HAZIRLANIYOR					//



if ($sozcuk_forum == 'tum')
{
	$mhangi = " $tablo_mesajlar.silinmis='0' $msecili_tarih AND ";
	$changi = " $tablo_cevaplar.silinmis='0' $csecili_tarih AND ";
}

elseif ($sozcuk_forum[0] == 'f')
{
	$fno = substr($sozcuk_forum, 1);

	$mhangi = "$tablo_mesajlar.silinmis='0' AND $tablo_mesajlar.hangi_forumdan='$fno' $msecili_tarih AND ";
	$changi = "$tablo_cevaplar.silinmis='0' AND $tablo_cevaplar.hangi_forumdan='$fno' $csecili_tarih AND ";
}

else
{
	$mhangi = "$tablo_mesajlar.silinmis='0' AND $tablo_mesajlar.hangi_forumdan=satir[id] $msecili_tarih AND ";
	$changi = "$tablo_cevaplar.silinmis='0' AND $tablo_cevaplar.hangi_forumdan=satir[id] $csecili_tarih AND ";
}


if ((isset($sozcuk_haric)) AND (strlen($sozcuk_haric) >= 3))
{
	$harama_dizisi = explode(' ', $sozcuk_haric);
	$ad_boyut = count($harama_dizisi);

	if ($ad_boyut == 1)
	{
		$haric_mesaj_baslik = "AND $tablo_mesajlar.mesaj_baslik NOT LIKE '%$sozcuk_haric%' ";
		$haric_mesaj_icerik = "AND $tablo_mesajlar.mesaj_icerik NOT LIKE '%$sozcuk_haric%' ";

		$haric_cevap_baslik = "AND $tablo_cevaplar.cevap_baslik NOT LIKE '%$sozcuk_haric%' ";
		$haric_cevap_icerik = "AND $tablo_cevaplar.cevap_icerik NOT LIKE '%$sozcuk_haric%' ";
	}

	else
	{
		$haric_mesaj_baslik = '';
		$haric_mesaj_icerik = '';

		$haric_cevap_baslik = '';
		$haric_cevap_icerik = '';

		for ($i=1,$d=0; $d < $ad_boyut; $i++,$d++)
		{
			if ($harama_dizisi[$d] != '')
			{
				$haric_mesaj_baslik .= "AND $tablo_mesajlar.mesaj_baslik NOT LIKE '%$harama_dizisi[$d]%' ";
				$haric_mesaj_icerik .= "AND $tablo_mesajlar.mesaj_icerik NOT LIKE '%$harama_dizisi[$d]%' ";

				$haric_cevap_baslik .= "AND $tablo_cevaplar.cevap_baslik NOT LIKE '%$harama_dizisi[$d]%' ";
				$haric_cevap_icerik .= "AND $tablo_cevaplar.cevap_icerik NOT LIKE '%$harama_dizisi[$d]%' ";
			}
		}
	}
}

else
{
	$haric_mesaj_baslik = '';
	$haric_mesaj_icerik = '';
	$haric_cevap_baslik = '';
	$haric_cevap_icerik = '';
}


if ((isset($sozcuk_hepsi)) AND (strlen($sozcuk_hepsi) >= 3))
{
	$arama_dizisi_bosluk = explode(' ', $sozcuk_hepsi);
	$ad_boyut_bosluk = count($arama_dizisi_bosluk);


	//	BOŞ DİZİLER ATILIYOR	//

	for ($d=0,$a=0; $d < $ad_boyut_bosluk; $d++)
	{
		if ($arama_dizisi_bosluk[$d] != '')
		{
			$arama_dizisi[$a] = $arama_dizisi_bosluk[$d];
			$a++;
		}
	}

	$ad_boyut = count($arama_dizisi);

	if ($ad_boyut == 1)
	{
		$hepsi_mesaj = "$mhangi $myazan_ara $tablo_mesajlar.mesaj_baslik LIKE '%$sozcuk_hepsi%' $haric_mesaj_baslik OR ";
		$hepsi_mesaj .= "$mhangi $myazan_ara $tablo_mesajlar.mesaj_icerik LIKE '%$sozcuk_hepsi%' $haric_mesaj_icerik ";

		$hepsi_cevap = "$changi $cyazan_ara $tablo_cevaplar.cevap_baslik LIKE '%$sozcuk_hepsi%' $haric_cevap_baslik OR ";
		$hepsi_cevap .= "$changi $cyazan_ara $tablo_cevaplar.cevap_icerik LIKE '%$sozcuk_hepsi%' $haric_cevap_icerik ";
	}

	else
	{
		for ($i=1,$d=0; $d < $ad_boyut; $i++,$d++)
		{
			if (empty($hepsi_mesaj))
			{
				$hepsi_mesaj = $mhangi.$myazan_ara;
				$hepsi_cevap = $changi.$cyazan_ara;
			}

			if (($d + 1) == $ad_boyut)
			{
				$hepsi_mesaj .= "$tablo_mesajlar.mesaj_icerik LIKE '%$arama_dizisi[$d]%' $haric_mesaj_icerik ";
				$hepsi_cevap .= "$tablo_cevaplar.cevap_icerik LIKE '%$arama_dizisi[$d]%' $haric_cevap_icerik ";
				break;
			}

			else
			{
				$hepsi_mesaj .= "$tablo_mesajlar.mesaj_icerik LIKE '%$arama_dizisi[$d]%' AND ";
				$hepsi_cevap .= "$tablo_cevaplar.cevap_icerik LIKE '%$arama_dizisi[$d]%' AND ";
			}
			
		}
	}
}

if ((isset($sozcuk_aynen)) AND (strlen($sozcuk_aynen) >= 3))
{
	$aynen_mesaj = "$mhangi $myazan_ara $tablo_mesajlar.mesaj_baslik LIKE '%$sozcuk_aynen%' $haric_mesaj_baslik OR ";
	$aynen_mesaj .= "$mhangi $myazan_ara $tablo_mesajlar.mesaj_icerik LIKE '%$sozcuk_aynen%' $haric_mesaj_icerik ";

	$aynen_cevap = "$changi $cyazan_ara $tablo_cevaplar.cevap_baslik LIKE '%$sozcuk_aynen%' $haric_cevap_baslik OR ";
	$aynen_cevap .= "$changi $cyazan_ara $tablo_cevaplar.cevap_icerik LIKE '%$sozcuk_aynen%' $haric_cevap_icerik ";
}

if ((isset($sozcuk_herhangi)) AND (strlen($sozcuk_herhangi) >= 3))
{
	$arama_dizisi_bosluk = explode(' ', $sozcuk_herhangi);
	$ad_boyut_bosluk = count($arama_dizisi_bosluk);


	//	BOŞ DİZİLER ATILIYOR	//

	for ($d=0,$a=0; $d < $ad_boyut_bosluk; $d++)
	{
		if ($arama_dizisi_bosluk[$d] != '')
		{
			$arama_dizisi2[$a] = $arama_dizisi_bosluk[$d];
			$a++;
		}
	}

	$ad_boyut2 = count($arama_dizisi2);
	if ($ad_boyut2 == 1)
	{
		$herhangi_mesaj = "$mhangi $myazan_ara $tablo_mesajlar.mesaj_baslik LIKE '%$sozcuk_herhangi%' $haric_mesaj_baslik OR ";
		$herhangi_mesaj .= "$mhangi $myazan_ara $tablo_mesajlar.mesaj_icerik LIKE '%$sozcuk_herhangi%' $haric_mesaj_icerik ";

		$herhangi_cevap = "$changi $cyazan_ara $tablo_cevaplar.cevap_baslik LIKE '%$sozcuk_herhangi%' $haric_cevap_baslik OR ";
		$herhangi_cevap .= "$changi $cyazan_ara $tablo_cevaplar.cevap_icerik LIKE '%$sozcuk_herhangi%' $haric_cevap_icerik ";
	}

	else
	{
		for ($i=1,$d=0; $d < $ad_boyut2; $i++,$d++)
		{
			if (empty($herhangi_mesaj))
			{
				$herhangi_mesaj = "$mhangi $myazan_ara $tablo_mesajlar.mesaj_baslik LIKE '%$arama_dizisi2[$d]%' $haric_mesaj_baslik OR ";
				$herhangi_mesaj .= "$mhangi $myazan_ara $tablo_mesajlar.mesaj_icerik LIKE '%$arama_dizisi2[$d]%' $haric_mesaj_icerik ";
			}

			if (empty($herhangi_cevap))
			{
				$herhangi_cevap = "$changi $cyazan_ara $tablo_cevaplar.cevap_baslik LIKE '%$arama_dizisi2[$d]%' $haric_cevap_baslik OR ";
				$herhangi_cevap .= "$changi $cyazan_ara $tablo_cevaplar.cevap_icerik LIKE '%$arama_dizisi2[$d]%' $haric_cevap_icerik ";
			}

			else
			{
				$herhangi_mesaj .= "OR $mhangi $myazan_ara $tablo_mesajlar.mesaj_baslik LIKE '%$arama_dizisi2[$d]%' $haric_mesaj_baslik OR ";
				$herhangi_mesaj .= "$mhangi $myazan_ara $tablo_mesajlar.mesaj_icerik LIKE '%$arama_dizisi2[$d]%' $haric_mesaj_icerik ";

				$herhangi_cevap .= "OR $changi $cyazan_ara $tablo_cevaplar.cevap_baslik LIKE '%$arama_dizisi2[$d]%' $haric_cevap_baslik OR ";
				$herhangi_cevap .= "$changi $cyazan_ara $tablo_cevaplar.cevap_icerik LIKE '%$arama_dizisi2[$d]%' $haric_cevap_icerik ";
			}
		}
	}
}



//		HANGİ ALANLARIN DOLU OLDUĞUNA GÖRE...		//
//		... WHERE SORGUSU HAZIRLANIYOR 				//



if ( (strlen($sozcuk_hepsi) >= 3) AND (strlen($sozcuk_aynen) >= 3) AND (strlen($sozcuk_herhangi) >= 3) )
{
	$aranan_mesaj_tumu = $hepsi_mesaj.' AND '.$aynen_mesaj.' AND '.$herhangi_mesaj;
	$aranan_cevap_tumu = $hepsi_cevap.' AND '.$aynen_cevap.' AND '.$herhangi_cevap;
}

if ( (strlen($sozcuk_hepsi) >= 3) AND (strlen($sozcuk_aynen) >= 3) AND (strlen($sozcuk_herhangi) < 3) )
{
	$aranan_mesaj_tumu = $hepsi_mesaj.' AND '.$aynen_mesaj;
	$aranan_cevap_tumu = $hepsi_cevap.' AND '.$aynen_cevap;
}

if ( (strlen($sozcuk_hepsi) >= 3) AND (strlen($sozcuk_herhangi) >= 3) AND (strlen($sozcuk_aynen) < 3) )
{
	$aranan_mesaj_tumu = $hepsi_mesaj.' AND '.$herhangi_mesaj;
	$aranan_cevap_tumu = $hepsi_cevap.' AND '.$herhangi_cevap;
}

if ( (strlen($sozcuk_aynen) >= 3) AND (strlen($sozcuk_herhangi) >= 3) AND (strlen($sozcuk_hepsi) < 3) )
{
	$aranan_mesaj_tumu = $aynen_mesaj.' AND '.$herhangi_mesaj;
	$aranan_cevap_tumu = $aynen_cevap.' AND '.$herhangi_cevap;
}

if ( (strlen($sozcuk_aynen) >= 3) AND (strlen($sozcuk_herhangi) < 3) AND (strlen($sozcuk_hepsi) < 3) )
{
	$aranan_mesaj_tumu = $aynen_mesaj;
	$aranan_cevap_tumu = $aynen_cevap;
}

if ( (strlen($sozcuk_aynen) < 3) AND (strlen($sozcuk_herhangi) >= 3) AND (strlen($sozcuk_hepsi) < 3) )
{
	$aranan_mesaj_tumu = $herhangi_mesaj;
	$aranan_cevap_tumu = $herhangi_cevap;
}

if ( (strlen($sozcuk_aynen) < 3) AND (strlen($sozcuk_herhangi) < 3) AND (strlen($sozcuk_hepsi) >= 3) )
{
	$aranan_mesaj_tumu = $hepsi_mesaj;
	$aranan_cevap_tumu = $hepsi_cevap;
}



// 		TÜM ALANLAR BOŞ BIRAKILMIŞSA KULLANICI UYARILIYOR	//



if ( (empty($aranan_mesaj_tumu)) AND (empty($myazan_ara)) )
{
	header('Location: hata.php?hata=2');
	exit();
}















//		TÜM FORUMLARDA ARAMA YAPIYORSA		//


if ($sozcuk_forum == 'tum')
{
	//	TÜM FORUMLAR - SORGU SONUCUNDAKİ TOPLAM SONUÇ SAYISI ALINIYOR	//

	$vtsonuc9 = $vt->query("SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan AS rakam

	FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
	ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

	WHERE $aranan_mesaj_tumu 
	GROUP BY $tablo_mesajlar.id


	UNION SELECT $tablo_mesajlar.id, $tablo_cevaplar.id AS rakam

	FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
	ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

	WHERE $aranan_cevap_tumu 
	GROUP BY $tablo_mesajlar.id") or die ($vt->hata_ver());
	
	$satir_sayi = $vt->num_rows($vtsonuc9);


	//	TÜM FORUMLAR - ARAMA YAPILIYOR VE SONUC BİLGİLERİ ÇEKİLİYOR	//


	if ($satir_sayi > 0)
	{
		$vtsorgu = "SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan, $tablo_mesajlar.hangi_forumdan, $tablo_mesajlar.tarih, $tablo_mesajlar.cevap_sayi, $tablo_mesajlar.goruntuleme, $tablo_cevaplar.cevap_yazan, $tablo_cevaplar.hangi_forumdan, $tablo_mesajlar.mesaj_baslik, $tablo_mesajlar.mesaj_icerik, $tablo_cevaplar.cevap_baslik, $tablo_cevaplar.cevap_icerik, $tablo_cevaplar.hangi_basliktan, $tablo_cevaplar.tarih cevap_tarih, $tablo_cevaplar.id AS cevap_id, $tablo_mesajlar.yazan AS rakam

		FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
		ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

		WHERE $aranan_mesaj_tumu

		GROUP BY $tablo_mesajlar.id


		UNION SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan, $tablo_mesajlar.hangi_forumdan, $tablo_mesajlar.tarih, $tablo_mesajlar.cevap_sayi, $tablo_mesajlar.goruntuleme, $tablo_cevaplar.cevap_yazan, $tablo_cevaplar.hangi_forumdan, $tablo_mesajlar.mesaj_baslik, $tablo_mesajlar.mesaj_icerik, $tablo_cevaplar.cevap_baslik, $tablo_cevaplar.cevap_icerik, $tablo_cevaplar.hangi_basliktan, $tablo_cevaplar.tarih cevap_tarih, $tablo_cevaplar.id AS cevap_id, $tablo_cevaplar.id AS rakam

		FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
		ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

		WHERE $aranan_cevap_tumu

		GROUP BY $tablo_mesajlar.id

		ORDER BY id DESC LIMIT $sayfa,$arama_kota";

		$m_arama_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	}
}





//		FORUM DALINDA ARAMA YAPIYORSA		//


elseif ($sozcuk_forum[0] == 'd')
{
	$dno = substr($sozcuk_forum, 1);

	// FORUM DALINA BAĞLI FORUMLAR BULUNUYOR	//

	$vtsorgu = "SELECT id FROM $tablo_forumlar WHERE dal_no='$dno'";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	while($satir = $vt->fetch_array($vtsonuc))
	{
		if (empty($m_where_bilgi))
		{
			$yaranan_mesaj_tumu = str_replace('satir[id]',"$satir[id]",$aranan_mesaj_tumu);
			$m_where_bilgi = "$yaranan_mesaj_tumu";

			$yaranan_cevap_tumu = str_replace('satir[id]',"$satir[id]",$aranan_cevap_tumu);
			$c_where_bilgi = "$yaranan_cevap_tumu";
		}
		else
		{
			$yaranan_mesaj_tumu = str_replace('satir[id]',"$satir[id]",$aranan_mesaj_tumu);
			$m_where_bilgi .= " OR $yaranan_mesaj_tumu";

			$yaranan_cevap_tumu = str_replace('satir[id]',"$satir[id]",$aranan_cevap_tumu);
			$c_where_bilgi .= " OR $yaranan_cevap_tumu";
		}
	}


	//	FORUM DALI - SORGUDAN DÖNEN TOPLAM SONUÇ SAYISI ALINIYOR	//

	$vtsonuc9 = $vt->query("SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan AS rakam

	FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
	ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

	WHERE $m_where_bilgi
	GROUP BY $tablo_mesajlar.id


	UNION SELECT $tablo_mesajlar.id, $tablo_cevaplar.id AS rakam

	FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
	ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

	WHERE $c_where_bilgi

	GROUP BY $tablo_mesajlar.id") or die ($vt->hata_ver());
	$satir_sayi = $vt->num_rows($vtsonuc9);


	//	FORUM DALI - ARAMA YAPILIYOR VE SONUC BİLGİLERİ ÇEKİLİYOR	//


	if ($satir_sayi > 0)
	{
		$vtsorgu = "SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan, $tablo_mesajlar.hangi_forumdan, $tablo_mesajlar.tarih, $tablo_mesajlar.cevap_sayi, $tablo_mesajlar.goruntuleme, $tablo_cevaplar.cevap_yazan, $tablo_cevaplar.hangi_forumdan, $tablo_mesajlar.mesaj_baslik, $tablo_mesajlar.mesaj_icerik, $tablo_cevaplar.cevap_baslik, $tablo_cevaplar.cevap_icerik, $tablo_cevaplar.hangi_basliktan, $tablo_cevaplar.tarih cevap_tarih, $tablo_cevaplar.id AS cevap_id, $tablo_mesajlar.yazan AS rakam

		FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
		ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

		WHERE $m_where_bilgi
		GROUP BY $tablo_mesajlar.id


		UNION SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan, $tablo_mesajlar.hangi_forumdan, $tablo_mesajlar.tarih, $tablo_mesajlar.cevap_sayi, $tablo_mesajlar.goruntuleme, $tablo_cevaplar.cevap_yazan, $tablo_cevaplar.hangi_forumdan, $tablo_mesajlar.mesaj_baslik, $tablo_mesajlar.mesaj_icerik, $tablo_cevaplar.cevap_baslik, $tablo_cevaplar.cevap_icerik, $tablo_cevaplar.hangi_basliktan, $tablo_cevaplar.tarih cevap_tarih, $tablo_cevaplar.id AS cevap_id, $tablo_cevaplar.id AS rakam
		
		FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
		ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

		WHERE $c_where_bilgi

		GROUP BY $tablo_mesajlar.id
		ORDER BY id DESC LIMIT $sayfa,$arama_kota";
		$m_arama_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	}
}





//		ÜST FORUMDA ARAMA YAPIYORSA		//


elseif ($sozcuk_forum[0] == 'u')
{
	$uno = substr($sozcuk_forum, 1);

	// ALT FORUMLAR BULUNUYOR	//

	$vtsorgu = "SELECT id FROM $tablo_forumlar WHERE id='$uno' OR alt_forum='$uno'";
	$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	while($satir = $vt->fetch_array($vtsonuc))
	{
		if (empty($m_where_bilgi))
		{
			$yaranan_mesaj_tumu = str_replace('satir[id]',"$satir[id]",$aranan_mesaj_tumu);
			$m_where_bilgi = "$yaranan_mesaj_tumu";

			$yaranan_cevap_tumu = str_replace('satir[id]',"$satir[id]",$aranan_cevap_tumu);
			$c_where_bilgi = "$yaranan_cevap_tumu";
		}
		else
		{
			$yaranan_mesaj_tumu = str_replace('satir[id]',"$satir[id]",$aranan_mesaj_tumu);
			$m_where_bilgi .= " OR $yaranan_mesaj_tumu";

			$yaranan_cevap_tumu = str_replace('satir[id]',"$satir[id]",$aranan_cevap_tumu);
			$c_where_bilgi .= " OR $yaranan_cevap_tumu";
		}
	}

	//	ÜST FORUM - SORGUDAN DÖNEN TOPLAM SONUÇ SAYISI ALINIYOR	//

	$vtsonuc9 = $vt->query("SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan AS rakam
	
	FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
	ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

	WHERE $m_where_bilgi
	GROUP BY $tablo_mesajlar.id


	UNION SELECT $tablo_mesajlar.id, $tablo_cevaplar.id AS rakam

	FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
	ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

	WHERE $c_where_bilgi

	GROUP BY $tablo_mesajlar.id") or die ($vt->hata_ver());
	$satir_sayi = $vt->num_rows($vtsonuc9);


	//	ÜST FORUM - ARAMA YAPILIYOR VE SONUC BİLGİLERİ ÇEKİLİYOR	//


	if ($satir_sayi > 0)
	{
		$vtsorgu = "SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan, $tablo_mesajlar.hangi_forumdan, $tablo_mesajlar.tarih, $tablo_mesajlar.cevap_sayi, $tablo_mesajlar.goruntuleme, $tablo_cevaplar.cevap_yazan, $tablo_cevaplar.hangi_forumdan, $tablo_mesajlar.mesaj_baslik, $tablo_mesajlar.mesaj_icerik, $tablo_cevaplar.cevap_baslik, $tablo_cevaplar.cevap_icerik, $tablo_cevaplar.hangi_basliktan, $tablo_cevaplar.tarih cevap_tarih, $tablo_cevaplar.id AS cevap_id, $tablo_mesajlar.yazan AS rakam

		FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
		ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

		WHERE $m_where_bilgi
		GROUP BY $tablo_mesajlar.id


		UNION SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan, $tablo_mesajlar.hangi_forumdan, $tablo_mesajlar.tarih, $tablo_mesajlar.cevap_sayi, $tablo_mesajlar.goruntuleme, $tablo_cevaplar.cevap_yazan, $tablo_cevaplar.hangi_forumdan, $tablo_mesajlar.mesaj_baslik, $tablo_mesajlar.mesaj_icerik, $tablo_cevaplar.cevap_baslik, $tablo_cevaplar.cevap_icerik, $tablo_cevaplar.hangi_basliktan, $tablo_cevaplar.tarih cevap_tarih, $tablo_cevaplar.id AS cevap_id, $tablo_cevaplar.id AS rakam
		
		FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
		ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan
		
		WHERE $c_where_bilgi

		GROUP BY $tablo_mesajlar.id
		ORDER BY id DESC LIMIT $sayfa,$arama_kota";
		$m_arama_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	}
}





//		ALT FORUMDA ARAMA YAPILIYORSA		//


elseif ($sozcuk_forum[0] == 'f')
{
	//	ALT FORUM - SORGU SONUCUNDAKİ TOPLAM SONUÇ SAYISI ALINIYOR	//

	$vtsonuc9 = $vt->query("SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan AS rakam

	FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
	ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

	WHERE $aranan_mesaj_tumu
	GROUP BY $tablo_mesajlar.id


	UNION SELECT $tablo_mesajlar.id, $tablo_cevaplar.id AS rakam

	FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
	ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

	WHERE $aranan_cevap_tumu
	GROUP BY $tablo_mesajlar.id") or die ($vt->hata_ver());

	$satir_sayi = $vt->num_rows($vtsonuc9);


	//	ALT FORUM - ARAMA YAPILIYOR VE SONUC BİLGİLERİ ÇEKİLİYOR	//


	if ($satir_sayi > 0)
	{
		$vtsorgu = "SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan, $tablo_mesajlar.hangi_forumdan, $tablo_mesajlar.tarih, $tablo_mesajlar.cevap_sayi, $tablo_mesajlar.goruntuleme, $tablo_cevaplar.cevap_yazan, $tablo_cevaplar.hangi_forumdan, $tablo_mesajlar.mesaj_baslik, $tablo_mesajlar.mesaj_icerik, $tablo_cevaplar.cevap_baslik, $tablo_cevaplar.cevap_icerik, $tablo_cevaplar.hangi_basliktan, $tablo_cevaplar.tarih cevap_tarih, $tablo_cevaplar.id AS cevap_id, $tablo_mesajlar.yazan AS rakam

		FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
		ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

		WHERE $aranan_mesaj_tumu
		GROUP BY $tablo_mesajlar.id


		UNION SELECT $tablo_mesajlar.id, $tablo_mesajlar.yazan, $tablo_mesajlar.hangi_forumdan, $tablo_mesajlar.tarih, $tablo_mesajlar.cevap_sayi, $tablo_mesajlar.goruntuleme, $tablo_cevaplar.cevap_yazan, $tablo_cevaplar.hangi_forumdan, $tablo_mesajlar.mesaj_baslik, $tablo_mesajlar.mesaj_icerik, $tablo_cevaplar.cevap_baslik, $tablo_cevaplar.cevap_icerik, $tablo_cevaplar.hangi_basliktan, $tablo_cevaplar.tarih cevap_tarih, $tablo_cevaplar.id AS cevap_id, $tablo_cevaplar.id AS rakam

		FROM $tablo_mesajlar LEFT OUTER JOIN $tablo_cevaplar
		ON $tablo_mesajlar.id = $tablo_cevaplar.hangi_basliktan

		WHERE $aranan_cevap_tumu
		GROUP BY $tablo_mesajlar.id

		ORDER BY id DESC LIMIT $sayfa,$arama_kota";

		$m_arama_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());
	}
}

if (empty($satir_sayi)) $satir_sayi = 0;



			//	ARAMA SONUÇ VERİRSE SON ARAMA ZAMANI OTURUMA GİRİLİYOR	//

if ( $satir_sayi > 0 ) $_SESSION['arama_tarih'] = $tarih;

	$toplam_sayfa = ($satir_sayi / $arama_kota);
	settype($toplam_sayfa,'integer');

	if (($satir_sayi % $arama_kota) != 0) $toplam_sayfa++;



endif;

//	ARAMA YAPILMIŞSA ÇALIŞTIRILACAK KODLAR - SONU	//









//	FORM SELECT İÇİN ANA FORUMLARIN BİLGİLERİ ÇEKİLİYOR	//

if (empty($satir_sayi)) $satir_sayi = 0;

$vtsorgu = "SELECT id,ana_forum_baslik FROM $tablo_dallar ORDER BY sira";
$dallar_sonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());

$sayfano = 10;
$sayfa_adi = 'Konu ve İçerik Arama';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');



		//		ARAMA AÇILIŞI SAYFASI - BAŞI 	//


if ($satir_sayi <= 0):


if ( isset($_GET['a']) )
	$bulunamadi = '<p align="center"><b> &nbsp; &nbsp; &nbsp; Aradığınız koşula uyan hiçbir sonuç bulunamadı.</b><p>';

else $bulunamadi = '';




$bul = array('%', '"');
$cevir = array('*', '&#34;');

if ($sozcuk_herhangi != '%') $sozcuk_herhangi = @str_replace($bul,$cevir,$sozcuk_herhangi);
else $sozcuk_herhangi = '';

if ($sozcuk_hepsi != '%') $sozcuk_hepsi = @str_replace($bul,$cevir,$sozcuk_hepsi);
else $sozcuk_hepsi = '';

if ( ($sozcuk_aynen != '%') AND ($sozcuk_aynen != '%%%') ) $sozcuk_aynen = @str_replace($bul,$cevir,$sozcuk_aynen);
else $sozcuk_aynen = '';

if ($sozcuk_haric != '%') $sozcuk_haric = @str_replace($bul,$cevir,$sozcuk_haric);
else $sozcuk_haric = '';

if ($yazar_ara != '%') $yazar_ara = @str_replace($bul,$cevir,$yazar_ara);
else $yazar_ara = '';




$arama_secenek = '';


// forum dalı adları çekiliyor

while ($dallar_satir = $vt->fetch_array($dallar_sonuc))
{
	$arama_secenek .= '
	<option value="d'.$dallar_satir['id'].'">[ '.$dallar_satir['ana_forum_baslik'].' ]';


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
			$arama_secenek .= '
			<option value="f'.$forum_satir['id'].'"> &nbsp; + '.$forum_satir['forum_baslik'];


		else
		{
			$arama_secenek .= '
			<option value="u'.$forum_satir['id'].'"> &nbsp; + '.$forum_satir['forum_baslik'];

			while ($alt_forum_satir = $vt->fetch_array($vtsonuca))
				$arama_secenek .= '
				<option value="f'.$alt_forum_satir['id'].'"> &nbsp; &nbsp; &nbsp; - '.$alt_forum_satir['forum_baslik'];
		}
	}
}



//	veriler tema motoruna yollanıyor	//

$dongusuz = array('{BULUNAMADI}' => $bulunamadi,
'{SOZCUK_HEPSI}' => $sozcuk_hepsi,
'{SOZCUK_HERHANGI}' => $sozcuk_herhangi,
'{SOZCUK_AYNEN}' => $sozcuk_aynen,
'{SOZCUK_HARIC}' => $sozcuk_haric,
'{YAZAR_ARA}' => $yazar_ara,
'{ARAMA_SECENEK}' => $arama_secenek);


//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/arama.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));

$ornek1->kosul('1', $dongusuz, true);
$ornek1->kosul('2', array('' => ''), false);

endif;







		//		ARAMA AÇILIŞI SAYFASI - SONU 	//


		//	ARAMA SONUÇLARI SIRALANIYOR BAŞLANGIÇ	//




if ($satir_sayi > 0): 


// FORUMLARIN BİLGİLERİ ÇEKİLİYOR	//

$vtsorgu = "SELECT id,forum_baslik,okuma_izni FROM $tablo_forumlar ORDER BY dal_no, sira";
$vtsonuc = $vt->query($vtsorgu) or die ($vt->hata_ver());

while ($forum_satir = $vt->fetch_array($vtsonuc))
{
	$tumforum_satir[$forum_satir['id']] = $forum_satir['forum_baslik'];
	$tumforum_izin[$forum_satir['id']] = $forum_satir['okuma_izni'];
}


$sayi_arttir = ($sayfa + 1);



// SONUÇLAR SIRALANIYOR

while ($m_arama_satir = $vt->fetch_array($m_arama_sonuc)):


//  BULUNAN CEVAP İSE   //

if (is_numeric($m_arama_satir['rakam']) == true)
{
	// cevabın kaçıncı sırada olduğu hesaplanıyor
	$vtsonuc9 = $vt->query("SELECT id FROM $tablo_cevaplar WHERE silinmis='0' AND hangi_basliktan='$m_arama_satir[hangi_basliktan]' AND id < $m_arama_satir[cevap_id]") or die ($vt->hata_ver());
	$cavabin_sirasi = $vt->num_rows($vtsonuc9);

	$sayfaya_git = ($cavabin_sirasi / $ayarlar['ksyfkota']);
	settype($sayfaya_git,'integer');
	$sayfaya_git = ($sayfaya_git * $ayarlar['ksyfkota']);

	if ($sayfaya_git != 0) $sayfaya_git = '&amp;ks='.$sayfaya_git;
	else $sayfaya_git = '';


	$konu_baslik = $m_arama_satir['mesaj_baslik'].'&nbsp; &raquo; &nbsp;'.$m_arama_satir['cevap_baslik'];

	$vtsonuc_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $m_arama_satir['cevap_tarih']);

	$yazan = '<a href="profil.php?kim='.$m_arama_satir['cevap_yazan'].'">'.$m_arama_satir['cevap_yazan'].'</a>';

	$mesaj_icerik = SonucDuzenle($m_arama_satir['cevap_icerik']);
}


//  BULUNAN KONU İSE  //

else
{
	$konu_baslik = $m_arama_satir['mesaj_baslik'];

	$vtsonuc_tarih = zonedate($ayarlar['tarih_bicimi'], $ayarlar['saat_dilimi'], false, $m_arama_satir['tarih']);

	$yazan = '<a href="profil.php?kim='.$m_arama_satir['yazan'].'">'.$m_arama_satir['yazan'].'</a>';

	$mesaj_icerik = SonucDuzenle($m_arama_satir['mesaj_icerik']);
}



//  SONUÇ İÇERİĞİ VE BAŞLIĞI RENKLENDİRİLİYOR VE KISALTILIYOR  //

if ($tumforum_izin[$m_arama_satir['2']] == 0)
{
	if ((isset($sozcuk_hepsi)) AND (strlen($sozcuk_hepsi) >= 3))
	{
		if ($ad_boyut == 1)
		{
			$konu_baslik = SonucRenklendir($konu_baslik, $sozcuk_hepsi);
			$mesaj_icerik = SonucRenklendir($mesaj_icerik, $sozcuk_hepsi);
		}

		elseif ($ad_boyut > 1)
		{
			for ($i=1,$d=0; $d < $ad_boyut; $i++,$d++)
			{
				$konu_baslik = SonucRenklendir($konu_baslik, $arama_dizisi[$d]);
				$mesaj_icerik = SonucRenklendir($mesaj_icerik, $arama_dizisi[$d]);
			}
		}
	}


	if ((isset($sozcuk_herhangi)) AND (strlen($sozcuk_herhangi) >= 3))
	{
		if ($ad_boyut2 == 1)
		{
			$konu_baslik = SonucRenklendir($konu_baslik, $sozcuk_herhangi);
			$mesaj_icerik = SonucRenklendir($mesaj_icerik, $sozcuk_herhangi);
		}

		elseif ($ad_boyut2 > 1)
		{
			for ($i=1,$d=0; $d < $ad_boyut2; $i++,$d++)
			{
				$konu_baslik = SonucRenklendir($konu_baslik, $arama_dizisi2[$d]);
				$mesaj_icerik = SonucRenklendir($mesaj_icerik, $arama_dizisi2[$d]);
			}
		}
	}


	if ((isset($sozcuk_aynen)) AND (strlen($sozcuk_aynen) >= 3))
	{
		$konu_baslik = SonucRenklendir($konu_baslik, $sozcuk_aynen);
		$mesaj_icerik = SonucRenklendir($mesaj_icerik, $sozcuk_aynen);
	}
}

else $mesaj_icerik = '<u><i>Yetkilendirilmiş Forum. Bu içeriği okumak için izniniz olmayabilir.</i></u>';


// forum başlığı oluşturuluyor

$forum_baslik = '<a href="forum.php?f='.$m_arama_satir['2'].'">'.$tumforum_satir[$m_arama_satir['2']].'</a>';


// konu başlığı oluşturuluyor

if (is_numeric($m_arama_satir['rakam']) == true)
	$konu_baslik_bag = '<a href="konu.php?k='.$m_arama_satir['hangi_basliktan'].$sayfaya_git.'#c'.$m_arama_satir['cevap_id'].'">'.$konu_baslik.'</a>';

else $konu_baslik_bag = '<a href="konu.php?k='.$m_arama_satir['id'].'">'.$konu_baslik.'</a>';




//	veriler tema motoruna yollanıyor	//

$tekli1[] = array('{SONUC_SAYISI}' => ($sayi_arttir++),
'{KONU_BASLIK}' => $konu_baslik_bag,
'{FORUM_BASLIK}' => $forum_baslik,
'{YAZAN}' => $yazan,
'{CEVAP_SAYI}' => NumaraBicim($m_arama_satir['cevap_sayi']),
'{GOSTERIM}' => NumaraBicim($m_arama_satir['goruntuleme']),
'{TARIH}' => $vtsonuc_tarih,
'{MESAJ_ICERIK}' => $mesaj_icerik);

endwhile;




		//	ARAMA SONUÇLARI SIRALANIYOR BİTİŞ	//






//		SAYFALAR BAŞLANGIÇ		//

$sayfalama = '';

if ($satir_sayi > $arama_kota):
$sayfalama = '<p>
<table cellspacing="1" cellpadding="4" border="0" align="right" class="tablo_border">
	<tr>
	<td class="forum_baslik">
Toplam '.$toplam_sayfa.' Sayfa:&nbsp;
	</td>';

if ($sayfa != 0)
{
	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="ilk sayfaya git">';
	$sayfalama .= '&nbsp;<a href="arama.php?b=1&amp;sayfa=0&amp;sozcuk_hepsi='.$sozcuk_hepsi.'&amp;yazar_ara='.$yazar_ara.'&amp;forum='.$sozcuk_forum.'&amp;sozcuk_aynen='.$sozcuk_aynen.'&amp;sozcuk_herhangi='.$sozcuk_herhangi.'&amp;sozcuk_haric='.$sozcuk_haric.'
">&laquo;ilk</a>&nbsp;</td>';

	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="önceki sayfaya git">';
	$sayfalama .= '&nbsp;<a href="arama.php?b=1&amp;sayfa='.($sayfa - $arama_kota).'&amp;sozcuk_hepsi='.$sozcuk_hepsi.'&amp;yazar_ara='.$yazar_ara.'&amp;forum='.$sozcuk_forum.'&amp;sozcuk_aynen='.$sozcuk_aynen.'&amp;sozcuk_herhangi='.$sozcuk_herhangi.'&amp;sozcuk_haric='.$sozcuk_haric.'
">&lt;</a>&nbsp;</td>';
}

for ($sayi=0,$sayfa_sinir=$sayfa; $sayi < $toplam_sayfa; $sayi++)
{
	if ($sayi < (($sayfa / $arama_kota) - 3));
	else
	{
		$sayfa_sinir++;
		if ($sayfa_sinir >= ($sayfa + 8)) break;
		if (($sayi == 0) and ($sayfa == 0))
		{
			$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">';
			$sayfalama .= '&nbsp;<b>[1]</b>&nbsp;</td>';
		}

		elseif (($sayi + 1) == (($sayfa / $arama_kota) + 1))
		{
			$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="Şu an bulunduğunuz sayfa">';
			$sayfalama .= '&nbsp;<b>['.($sayi + 1).']</b>&nbsp;</td>';
		}

		else
		{
			$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="'.($sayi + 1).' numaralı sayfaya git">';
			$sayfalama .= '&nbsp;<a href="arama.php?b=1&amp;sayfa='.($sayi * $arama_kota).'&amp;sozcuk_hepsi='.$sozcuk_hepsi.'&amp;yazar_ara='.$yazar_ara.'&amp;forum='.$sozcuk_forum.'&amp;sozcuk_aynen='.$sozcuk_aynen.'&amp;sozcuk_herhangi='.$sozcuk_herhangi.'&amp;sozcuk_haric='.$sozcuk_haric.'
">'.($sayi + 1).'</a>&nbsp;</td>';
		}
	}
}

if ($sayfa < ($satir_sayi - $arama_kota))
{
	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="sonraki sayfaya git">';
	$sayfalama .= '&nbsp;<a href="arama.php?b=1&amp;sayfa='.($sayfa + $arama_kota).'&amp;sozcuk_hepsi='.$sozcuk_hepsi.'&amp;yazar_ara='.$yazar_ara.'&amp;forum='.$sozcuk_forum.'&amp;sozcuk_aynen='.$sozcuk_aynen.'&amp;sozcuk_herhangi='.$sozcuk_herhangi.'&amp;sozcuk_haric='.$sozcuk_haric.'
">&gt;</a>&nbsp;</td>';

	$sayfalama .= '<td bgcolor="#ffffff" class="liste-veri" title="son sayfaya git">';
	$sayfalama .= '&nbsp;<a href="arama.php?b=1&amp;sayfa='.(($toplam_sayfa - 1) * $arama_kota).'&amp;sozcuk_hepsi='.$sozcuk_hepsi.'&amp;yazar_ara='.$yazar_ara.'&amp;forum='.$sozcuk_forum.'&amp;sozcuk_aynen='.$sozcuk_aynen.'&amp;sozcuk_herhangi='.$sozcuk_herhangi.'&amp;sozcuk_haric='.$sozcuk_haric.'
">son&raquo;</a>&nbsp;</td>';

}

$sayfalama .= '</tr>
</table>';


endif;

//		SAYFALAR BİTİŞ		//







//	TEMA UYGULANIYOR	//

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/arama.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));


$ornek1->kosul('1', array('' => ''), false);
$ornek1->kosul('2', array('' => ''), true);

if (isset($tekli1)) $ornek1->tekli_dongu('1',$tekli1);

$ornek1->dongusuz(array('{TOPLAM_SONUC}' => $satir_sayi,
						'{SAYFALAMA}' => $sayfalama));


endif;
eval(TEMA_UYGULA);

?>