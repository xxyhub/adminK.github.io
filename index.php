<?php
/*
 +-=========================================================================-+
 |                                phpKF v3.00                                |
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


// ayar.php yok, kurulum yapılmamış, kurulum sayfasına yönlendir
$phpkf_ayarlar_kip = "WHERE kip='1'";
if (!@include_once('phpkf-ayar.php'))
{
	header('Location: phpkf-kurulum/index.php');
	exit();
}

if ($ayarlar['site_index'] == 3) define('DOSYA_PORTAL_INDEX',true);

if ((isset($_SERVER['REQUEST_URI'])) AND ($_SERVER['REQUEST_URI'] != '')) $gadres = $_SERVER['REQUEST_URI'];
else $gadres = '';

$anadizin2 = @str_replace('/', '\/', $anadizin);
if ((@preg_match("/^$anadizin2(|\?.*)$/i", $gadres)) OR (@preg_match("/^$anadizin2\\$phpkf_dosyalar[index](|\?.*)$/i", $gadres))) include_once($site_index);
else include_once('index_cms.php');

?>