<?php
 


if (!defined('PHPKF_ICINDEN')) define('PHPKF_ICINDEN', true);
$sayfano = 6;
$sayfa_adi = 'Yardım';
include_once('phpkf-bilesenler/sayfa_baslik_forum.php');


// Tema Uygulanıyor
$ornek1 = new phpkf_tema();

$tema_dosyasi = 'temalar/'.$temadizini.'/bbcode_yardim.php';

eval($ornek1->tema_dosyasi($tema_dosyasi));
eval(TEMA_UYGULA);

?>