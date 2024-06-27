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


$phpkf_ayarlar_kip = "";
if (!defined('DOSYA_AYAR')) include 'ayar.php';
if (!defined('DOSYA_GERECLER')) include 'phpkf-bilesenler/gerecler.php';





//  ZARARLI KODLAR TEMİZLENİYOR - BAŞI  //

if (isset($_GET['mesaj_no'])) $mesaj_no = zkTemizleNumara($_GET['mesaj_no']);
else $mesaj_no = 0;

if (isset($_GET['cevap_no'])) $cevap_no = zkTemizleNumara($_GET['cevap_no']);
else $cevap_no = 0;

if (isset($_GET['fno'])) $fno = zkTemizleNumara($_GET['fno']);
else $fno = 0;

if (isset($_GET['fno1'])) $fno1 = zkTemizleNumara($_GET['fno1']);
else $fno1 = 0;

if (isset($_GET['fno2'])) $fno2 = zkTemizleNumara($_GET['fno2']);
else $fno2 = 0;

if (isset($_GET['hata2'])) $hata2 = zkTemizleNumara($_GET['hata2']);
else $hata2 = 0;

if (isset($_GET['o']))
{
	if (preg_match('/^[A-Za-z0-9]+$/', $_GET['o'])) $go = $_GET['o'];
}
else $go = '';


// sayfalar
if (isset($_GET['fsayfa']))
{
	$fsayfa = zkTemizleNumara($_GET['fsayfa']);
	$fs = '&amp;fs='.$fsayfa;
}
else {$fsayfa = 0; $fs = '';}

if (isset($_GET['sayfa']))
{
	$sayfa = zkTemizleNumara($_GET['sayfa']);
	$ks = '&amp;ks='.$sayfa;
}
else {$sayfa = 0; $ks = '';}


// gelinen adres
if (isset($_GET['git']))
{
	$git = '?git='.@zkTemizle4($_GET['git']);
	$git = @zkTemizle($git);
	$gelinen = '';
}
elseif (isset($_SERVER['HTTP_REFERER']))
{
	$adres = @zkTemizle4($_SERVER['HTTP_REFERER']);
	$adres = @zkTemizle($adres);
	$git = '?git='.$adres;
	$gelinen = $adres;
}
else
{
	$git = '';
	$gelinen = '';
}

//  ZARARLI KODLAR TEMİZLENİYOR - SONU  //



// Dil dosyası yükleniyor
if (@include_once('phpkf-bilesenler/diller/'.$site_dili.'/hata.php'));
else include_once('phpkf-bilesenler/diller/tr/hata.php');




//  BİLGİ İLETİLERİ - BAŞI  //


$bilgi_no[1] = $lb[1];

$bilgi_no[2] = $lb[2];

$bilgi_no[3] = $lb[3];

$bilgi_no[4] = $lb[4];

$bilgi_no[5] = $lb[5];

$bilgi_no[6] = $lb[6];

$bilgi_no[7] = $lb[7];

$bilgi_no[8] = $lb[8];

$bilgi_no[9] = $lb[9];

$bilgi_no[10] = $lb[10].'<br /><br /><a href="'.$phpkf_dosyalar['profil'].'">'.$lh['tikla_profil'].'</a><meta http-equiv="Refresh" content="5;url='.$phpkf_dosyalar['profil'].'">';

$bilgi_no[11] = $lb[11];

$bilgi_no[12] = $lb[12];

$bilgi_no[13] = $lb[13];

$bilgi_no[14] = $lb[14].'<br />'.$lh['spam_kutusu'].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'">'.$lh['tikla_giris'].'</a>';

$bilgi_no[15] = $lb[15].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'">'.$lh['tikla_giris'].'</a>';

$bilgi_no[16] = $lb[16].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'">'.$lh['tikla_giris'].'</a>';

$bilgi_no[17] = $lb[17];

$bilgi_no[18] = $lb[18].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'">'.$lh['tikla_giris'].'</a>';

$bilgi_no[19] = $lb[19].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'">'.$lh['tikla_giris'].'</a>';

$bilgi_no[20] = $lb[20].'<br />'.$lh['spam_kutusu'].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'">'.$lh['tikla_giris'].'</a>';

$bilgi_no[21] = $lb[21][0].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'">'.$lb[21][1].'</a>';

$bilgi_no[22] = $lb[22];

$bilgi_no[23] = $lb[23];

$bilgi_no[24] = $lb[24];

$bilgi_no[25] = $lb[25];

$bilgi_no[26] = $lb[26];

$bilgi_no[27] = $lb[27];

$bilgi_no[28] = $lb[28];

$bilgi_no[29] = $lb[29];

$bilgi_no[30] = $lb[30];

$bilgi_no[31] = $lb[31];

$bilgi_no[32] = $lb[32];

$bilgi_no[33] = $lb[33];

$bilgi_no[34] = $lb[34];

$bilgi_no[35] = $lb[35];

$bilgi_no[36] = $lb[36];

$bilgi_no[37] = $lb[37];

$bilgi_no[38] = $lb[38];

$bilgi_no[39] = $lb[39];

$bilgi_no[40] = $lb[40];

$bilgi_no[41] = $lb[41];

$bilgi_no[42] = $lb[42].'<br /><br /><a href="'.$phpkf_dosyalar['profil'].'">'.$lh['tikla_profil'].'</a><meta http-equiv="Refresh" content="5;url='.$phpkf_dosyalar['profil'].'">';

$bilgi_no[43] = $lb[43].'<br /><br /><a href="'.$phpkf_dosyalar['profil'].'">'.$lh['tikla_profil'].'</a><meta http-equiv="Refresh" content="5;url='.$phpkf_dosyalar['profil'].'">';

$bilgi_no[44] = $lb[44].'<br /><br /><a href="'.$phpkf_dosyalar['profil'].'">'.$lh['tikla_profil'].'</a><meta http-equiv="Refresh" content="5;url='.$phpkf_dosyalar['profil'].'">';

$bilgi_no[45] = $lb[45].'<br /><br /><a href="'.$phpkf_dosyalar['profil'].'">'.$lh['tikla_profil'].'</a><meta http-equiv="Refresh" content="5;url='.$phpkf_dosyalar['profil'].'">';

$bilgi_no[46] = $lb[46];

$bilgi_no[47] = $lb[47];

$bilgi_no[48] = $lb[48];

$bilgi_no[49] = $lb[49];

$bilgi_no[50] = $lb[50];

$bilgi_no[51] = $lb[51];

$bilgi_no[52] = $lb[52];

$bilgi_no[53] = $lb[53];

$bilgi_no[54] = $lb[54];

$bilgi_no[55] = $lb[55];

$bilgi_no[500] = $lb[500].'<br /><br /><a href="'.$phpkf_dosyalar['cms'].'">'.$lh['tikla_anasayfa'].'</a>';

$bilgi_no[501] = '<meta http-equiv="Refresh" content="5;url='.$gelinen.'#yorumlar" />'.$lb[501].', <a href="'.$gelinen.'#yorumlar">'.$lh['tikla_geri2'].'</a>';

$bilgi_no[502] = '<meta http-equiv="Refresh" content="5;url='.$gelinen.'" />'.$lb[502].'<br /><a href="'.$gelinen.'">'.$lh['tikla_geri1'].'</a>';


//  BİLGİ İLETİLERİ - SONU  //










//  HATA İLETİLERİ - BAŞI  //


$hata_no[1] = $lh[1];

$hata_no[2] = $lh[2];

$hata_no[3] = $lh[3];

$hata_no[4] = $lh[4];

$hata_no[5] = $lh[5];

$hata_no[6] = str_replace('{00}', $ayarlar['yorum_sure'], $lh[6]);

$hata_no[7] = $lh[7];

$hata_no[8] = $lh[8];

$hata_no[9] = $lh[9];

$hata_no[10] = $lh[10];

$hata_no[11] = '<font color="#007900">'.$lh[11][0].'</font> <br /><br />'.$lh[11][1].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'?kip=etkinlestir">'.$lh[11][2].'</a>';

$hata_no[12] = $lh[12];

$hata_no[13] = $lh[13];

$hata_no[14] = $lh[14];

$hata_no[15] = $lh[15];

$hata_no[16] = $lh[16];

$hata_no[17] = $lh[17];

$hata_no[18] = $lh[18];

$hata_no[19] = $lh[19];

$hata_no[20] = $lh[20];

$hata_no[21] = str_replace('{00}', ($ayarlar['uye_kilit_sure'] / 60), $lh[21]);

$hata_no[22] = $lh[22].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'">'.$lh['tikla_geri3'].'</a><br /><br /><a href="'.$phpkf_dosyalar['giris'].'?kip=yeni_sifre">'.$lh['tikla_hatirla1'].'</a>';

$hata_no[23] = $lh[23][0].'<br />'.$lh['spam_kutusu'].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'?kip=etkinlestir">'.$lh[23][1].'</a>';

$hata_no[24] = $lh[24];

$hata_no[25] = $lh[25];

$hata_no[26] = $lh[26];

$hata_no[27] = $lh[27];

$hata_no[28] = $lh[28];

$hata_no[29] = $lh[29];

$hata_no[30] = $lh[30];

$hata_no[31] = $lh[31];

$hata_no[32] = $lh[32];

$hata_no[33] = $lh[33];

$hata_no[34] = $lh[34];

$hata_no[35] = $lh[35];

$hata_no[36] = $lh[36];

$hata_no[37] = $lh[37];

$hata_no[38] = $lh[38];

$hata_no[39] = $lh[39];

$hata_no[40] = $lh[40];

$hata_no[41] = $lh[41];

$hata_no[42] = $lh[42];

$hata_no[43] = $lh[43];

$hata_no[44] = $lh['onay_kodu_hatali'].'<br /><br /><a href="'.$phpkf_dosyalar['kayit'].'">'.$lh['tikla_geri3'].'</a>';

$hata_no[45] = $lh[45];

$hata_no[46] = $lh[46];

$hata_no[47] = $lh[47];

$hata_no[48] = $lh[48];

$hata_no[49] = $lh[49];

$hata_no[50] = $lh[50];

$hata_no[51] = $lh[51];

$hata_no[52] = $lh[52];

$hata_no[53] = $lh[53];

$hata_no[54] = $lh[54];

$hata_no[55] = $lh[55];

$hata_no[56] = $lh[56];

$hata_no[57] = $lh[57];

$hata_no[58] = $lh[58];

$hata_no[59] = $lh[59];

$hata_no[60] = $lh[60];

$hata_no[61] = $lh[61];

$hata_no[62] = $lh[62];

$hata_no[63] = $lh[63];

$hata_no[64] = $lh[64];

$hata_no[65] = str_replace('{00}', $ayarlar['yorum_sure'], $lh[65]);

$hata_no[66] = $lh[66];

$hata_no[67] = $lh[67];

$hata_no[68] = $lh[68];

$hata_no[69] = $lh[69];

$hata_no[70] = $lh[70];

$hata_no[71] = $lh[71];

$hata_no[72] = $lh[72];

$hata_no[73] = $lh[73];

$hata_no[74] = $lh[74];

$hata_no[75] = $lh[75];

$hata_no[76] = $lh[76];

$hata_no[77] = $lh[77];

$hata_no[78] =  str_replace('{00}', $ayarlar['uye_imza_uzunluk'], $lh[78]);

$hata_no[79] = $lh[79];

$hata_no[80] = $lh[80];

$hata_no[81] = $lh[81];

$hata_no[82] = $lh[82];

$hata_no[83] = $lh[83];

$hata_no[84] = $lh[84];

$hata_no[85] = $lh[85];

$hata_no[86] = str_replace('{00}', ($ayarlar['uye_resim_boyut']/1024), $lh[86]);

$hata_no[87] = str_replace('{00}', ($ayarlar['uye_resim_genislik'].'x'.$ayarlar['uye_resim_yukseklik']), $lh[87]);

$hata_no[88] = $lh[88];

$hata_no[89] = $lh[89];

$hata_no[90] = str_replace('{00}', ($ayarlar['uye_resim_boyut']/1024), $lh[90]);

$hata_no[91] = str_replace('{00}', ($ayarlar['uye_resim_genislik'].'x'.$ayarlar['uye_resim_yukseklik']), $lh[91]);

$hata_no[92] = $lh[92];

$hata_no[93] = $lh[93];

$hata_no[94] = $lh[94];

$hata_no[95] = $lh[95];

$hata_no[96] = $lh[96];

$hata_no[97] = $lh[97];

$hata_no[98] = $lh[98];

$hata_no[99] = $lh[99];

$hata_no[100] = $lh[100];

$hata_no[101] = $lh[101];

$hata_no[102] = $lh[102];

$hata_no[103] = $lh[103];

$hata_no[104] = $lh[104];

$hata_no[105] = $lh[105];

$hata_no[106] = $lh[106];

$hata_no[107] = $lh[107];

$hata_no[108] = $lh[108];

$hata_no[109] = $lh[109];

$hata_no[110] = $lh[110];

$hata_no[111] = $lh[111];

$hata_no[112] = $lh[112];

$hata_no[113] = $lh[113];

$hata_no[114] = $lh[114];

$hata_no[115] = $lh[115];

$hata_no[116] = $lh[116];

$hata_no[117] = $lh[117];

$hata_no[118] = $lh[118];

$hata_no[119] = $lh[119];

$hata_no[120] = $lh[120];

$hata_no[121] = $lh[121];

$hata_no[122] = $lh[122];

$hata_no[123] = $lh[123];

$hata_no[124] = $lh[124];

$hata_no[125] = $lh[125];

$hata_no[126] = $lh[126];

$hata_no[127] = $lh[127];

$hata_no[128] = $lh[128];

$hata_no[129] = $lh[129];

$hata_no[130] = $lh[130];

$hata_no[131] = $lh[131];

$hata_no[132] = $lh[132];

$hata_no[133] = $lh[133];

$hata_no[134] = $lh[134];

$hata_no[135] = $lh[135];

$hata_no[136] = $lh[136];

$hata_no[137] = $lh[137];

$hata_no[138] = $lh[138];

$hata_no[139] = $lh[139];

$hata_no[140] = $lh[140];

$hata_no[141] = $lh[141];

$hata_no[142] = $lh[142];

$hata_no[143] = $lh[143];

$hata_no[144] = $lh[144];

$hata_no[145] = $lh[145];

$hata_no[146] = $lh[146];

$hata_no[147] = $lh[147];

$hata_no[148] = $lh[148];

$hata_no[149] = $lh[149];

$hata_no[150] = $lh[150];

$hata_no[151] = $lh[151];

$hata_no[152] = $lh[152];

$hata_no[153] = $lh[153];

$hata_no[154] = $lh[154];

$hata_no[155] = $lh[155];

$hata_no[156] = $lh[156];

$hata_no[157] = $lh[157];

$hata_no[158] = $lh[158];

$hata_no[159] = $lh[159];

$hata_no[160] = $lh[160];

$hata_no[161] = $lh[161];

$hata_no[162] = $lh[162];

$hata_no[163] = $lh[163];

$hata_no[164] = $lh[164];

$hata_no[165] = $lh[165];

$hata_no[166] = $lh[166];

$hata_no[167] = $lh[167];

$hata_no[168] = $lh[168];

$hata_no[169] = $lh[169];

$hata_no[170] = $lh[170];

$hata_no[171] = $lh[171];

$hata_no[172] = $lh[172];

$hata_no[173] = $lh[173];

$hata_no[174] = $lh[174];

$hata_no[175] = $lh[175];

$hata_no[176] = $lh[176];

$hata_no[177] = $lh[177];

$hata_no[178] = $lh[178];

$hata_no[179] = $lh[179];

$hata_no[180] = $lh[180];

$hata_no[181] = $lh[181];

$hata_no[182] = $lh[182];

$hata_no[183] = $lh[183];

$hata_no[184] = $lh[184];

$hata_no[185] = $lh[185];

$hata_no[186] = $lh[186];

$hata_no[187] = $lh[187];

$hata_no[188] = $lh[188].'<br /><br /><a href="'.$phpkf_dosyalar['sifre_degistir'].'">'.$lh['tikla_geri3'].'</a>';

$hata_no[189] = $lh[189];

$hata_no[190] = $lh[190];

$hata_no[191] = $lh[191];

$hata_no[192] = $lh[192];

$hata_no[193] = $lh[193];

$hata_no[194] = $lh[194];

$hata_no[195] = $lh[195];

$hata_no[196] = $lh[196];

$hata_no[197] = $lh[197];

$hata_no[198] = '<font color="#007900">'.$lh[198][0].'</font> <br /><br />'.$lh[198][1].'<br /><br /><a href="'.$phpkf_dosyalar['index'].'">'.$lh['tikla_anasayfa'].'</a>';

$hata_no[199] = '<font color="#007900">'.$lh[198][0].'</font> <br /><br />'.$lh[198][1].'<br /><br />'.$lh[199];

$hata_no[200] = $lh[200];

$hata_no[201] = $lh[201];

$hata_no[202] = $lh[202];

$hata_no[203] = $lh[203];

$hata_no[204] = $lh[204];

$hata_no[205] = $lh[205];

$hata_no[206] = $lh[206];

$hata_no[207] = $lh[207].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'">'.$lh['tikla_geri3'].'</a><br /><br /><a href="'.$phpkf_dosyalar['giris'].'?kip=yeni_sifre">'.$lh['tikla_hatirla2'].'</a>';

$hata_no[208] = $lh[208].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].'">'.$lh['tikla_geri3'].'</a><br /><br /><a href="'.$phpkf_dosyalar['giris'].'?kip=yeni_sifre">'.$lh['tikla_hatirla2'].'</a>';

$hata_no[209] = $lh[209];

$hata_no[210] = $lh[210];

$hata_no[211] = $lh[211];

$hata_no[220] = $lh[220];

$hata_no[221] = $lh[221];

$hata_no[223] = $lh[223];

$hata_no[224] = $lh[224];

$hata_no[500] = $lh[500].' "'.$ayarlar['anasyfdosya'].'"';

$hata_no[501] = $lh[501];

$hata_no[502] = $lh[502];

$hata_no[503] = $lh[503];

$hata_no[504] = $lh[504];

$hata_no[505] = $lh[505];

$hata_no[506] = $lh[506];

$hata_no[507] = $lh[507];

$hata_no[508] = $lh['onay_kodu_hatali'].'<br /><br /><a href="'.$gelinen.'">'.$lh['tikla_geri3'].'</a>';

$hata_no[509] = $lh[509];

$hata_no[510] = $lh[510];


//  HATA İLETİLERİ - SONU  //










//  UYARI İLETİLERİ - BAŞI  //


$uyari_no[1] = '<font color="orange">'.$lu[1].'</font>';

$uyari_no[2] = '<font color="orange">'.$lu[2].'</font>';

$uyari_no[3] = '<font color="orange">'.$lu[3].'</font>';

$uyari_no[4] = '<font color="orange">'.$lu[4].'</font>';

$uyari_no[5] = $lu[5];

$uyari_no[6] = $lu[6].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].$git.'" style="color:#ff0000">'.$lu['giris_yap'].'</a> &nbsp; &nbsp; &nbsp; <a href="'.$phpkf_dosyalar['kayit'].'" style="color:#ff0000">'.$lu['uye_ol'].'</a>';

$uyari_no[7] = $lu[7];

$uyari_no[8] = $lu[8].'<br /><br /><a href="'.$phpkf_dosyalar['sifre_degistir'].'">'.$lh['tikla_geri1'].'</a><meta http-equiv="Refresh" content="5;url='.$phpkf_dosyalar['sifre_degistir'].'">';

$uyari_no[9] = '<font color="orange">'.$lu[9].'</font>';

$uyari_no[10] = $lu[10].'<br /><br /><a href="'.$phpkf_dosyalar['giris'].$git.'" style="color:#ff0000">'.$lu['giris_yap'].'</a> &nbsp; &nbsp; &nbsp; <a href="'.$phpkf_dosyalar['kayit'].'" style="color:#ff0000">'.$lu['uye_ol'].'</a>';


//  UYARI İLETİLERİ - SONU  //










// GELEN VERİYE GÖRE SAYFA HAZIRLANIYOR - BAŞI  //

if ( isset($_GET['bilgi']) )
{
		if (!is_numeric($_GET['bilgi'])) $_GET['bilgi'] = 0;

		if ( (empty($bilgi_no[$_GET['bilgi']])) OR (!is_numeric($_GET['bilgi'])) )
		{
			$sayfa_adi = $lh['hatali_adres'];
			$hata_baslik = $lh['hatali_adres'];
			$hata_icerik = $lh['hatali_adres'];
		}

		else
		{
			$sayfa_adi = $lh['bilgi_iletisi'];
			$hata_baslik = $lh['bilgi_iletisi'];
			$hata_icerik = $bilgi_no[$_GET['bilgi']];
		}
}



elseif ( isset($_GET['hata']) )
{
		if (!is_numeric($_GET['hata'])) $_GET['hata'] = 0;

		if ( (empty($hata_no[$_GET['hata']])) OR (!is_numeric($_GET['hata'])) )
		{
			$sayfa_adi = $lh['hatali_adres'];
			$hata_baslik = $lh['hatali_adres'];
			$hata_icerik = $lh['hatali_adres'];
		}

		else
		{
			$sayfa_adi = $lh['hata_iletisi'];
			$hata_baslik = $lh['hata_iletisi'];
			$hata_icerik = '<font color="red">'.$hata_no[$_GET['hata']].'</font>';
		}
}



elseif ( isset($_GET['uyari']) )
{
		if (!is_numeric($_GET['uyari'])) $_GET['uyari'] = 0;

		if ( (empty($uyari_no[$_GET['uyari']])) OR (!is_numeric($_GET['uyari'])) )
		{
			$sayfa_adi = $lh['hatali_adres'];
			$hata_baslik = $lh['hatali_adres'];
			$hata_icerik = $lh['hatali_adres'];
		}

		else
		{
			$sayfa_adi = $lh['uyari_iletisi'];
			$hata_baslik = $lh['uyari_iletisi'];
			$hata_icerik = $uyari_no[$_GET['uyari']];
		}
}



else
{
	$sayfa_adi = $lh['hatali_adres'];
	$hata_baslik = $lh['hatali_adres'];
	$hata_icerik = $lh['hatali_adres'];
}

// GELEN VERİYE GÖRE SAYFA HAZIRLANIYOR - SONU  //





$sayfano = 39;
$sayfa_baslik = $sayfa_adi;
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');

$ornek1 = new phpkf_tema();
$tema_dosyasi = 'temalar/'.$temadizini.'/hata.php';
eval($ornek1->tema_dosyasi($tema_dosyasi));

$ornek1->dongusuz(array('{HATA_BASLIK}' => $hata_baslik, '{HATA_ICERIK}' => $hata_icerik));

eval(TEMA_UYGULA);

?>