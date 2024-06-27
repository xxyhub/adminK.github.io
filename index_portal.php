<?php
if (!defined('DOSYA_PORTAL_INDEX')) define('DOSYA_PORTAL_INDEX',true);


// Portal ana sayfayı kök dizininde index_portal.php olarak çalıştırmak için,
// alt satırdaki // işaretini kaldırın
// include_once('portal/index.php');


// portal/index.php yönlendir
header('Location: portal/index.php');
?>