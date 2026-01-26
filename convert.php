<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// KÖŞE YUVARLAMA FONKSİYONU
function roundRect($src, $radius) {

    $w = imagesx($src);
    $h = imagesy($src);

    $mask = imagecreatetruecolor($w, $h);
    imagesavealpha($mask, true);
    imagealphablending($mask, false);

    $transparent = imagecolorallocatealpha($mask, 0, 0, 0, 127);
    imagefill($mask, 0, 0, $transparent);

    $solid = imagecolorallocatealpha($mask, 0, 0, 0, 0);

    // köşe yuvarlatmalı dikdörtgen maske çiz
    imagefilledrectangle($mask, $radius, 0, $w - $radius, $h, $solid);
    imagefilledrectangle($mask, 0, $radius, $w, $h - $radius, $solid);

    imagefilledellipse($mask, $radius, $radius, $radius*2, $radius*2, $solid);
    imagefilledellipse($mask, $w-$radius, $radius, $radius*2, $radius*2, $solid);
    imagefilledellipse($mask, $radius, $h-$radius, $radius*2, $radius*2, $solid);
    imagefilledellipse($mask, $w-$radius, $h-$radius, $radius*2, $radius*2, $solid);

    // Maske uygula
    imagesavealpha($src, true);
    imagealphablending($src, false);

    for($x=0;$x<$w;$x++){
        for($y=0;$y<$h;$y++){
            $alpha = (imagecolorat($mask, $x, $y) >> 24) & 0x7F;
            $col = imagecolorat($src, $x, $y);
            $rgba = imagecolorsforindex($src, $col);
            $newColor = imagecolorallocatealpha(
                $src, $rgba['red'], $rgba['green'], $rgba['blue'], $alpha
            );
            imagesetpixel($src, $x, $y, $newColor);
        }
    }

    return $src;
}


// DOSYA YOLLARI
$sourceProfile = __DIR__ . "/profil.jpeg";
$sourceImage   = __DIR__ . "/imza.jpeg";
$font          = __DIR__ . "/font.ttf";
$font2         = __DIR__ . "/font2.ttf";

// KONTROL
if (!file_exists($sourceImage)) die("imza.jpeg bulunamadı!");
if (!file_exists($font)) die("font.ttf bulunamadı!");
if (!file_exists($font2)) die("font2.ttf bulunamadı!");

// ARKA PLAN
$image = imagecreatefromjpeg($sourceImage);

// BEYAZ RENK
$white = imagecolorallocate($image, 255, 255, 255);

// PROFİL FOTO EKLE
if (file_exists($sourceProfile)) {

    $src = imagecreatefromjpeg($sourceProfile);

    // HEDEF BOYUT
    $newW = 225;
    $newH = 225;

    $dst = imagecreatetruecolor($newW, $newH);
    imagealphablending($dst, false);
    imagesavealpha($dst, true);

    // MERKEZDEN KIRP
    $srcW = imagesx($src);
    $srcH = imagesy($src);
    $side = min($srcW, $srcH);
    $srcX = ($srcW - $side) / 2;
    $srcY = ($srcH - $side) / 2;

    imagecopyresampled($dst, $src, 0, 0, $srcX, $srcY, $newW, $newH, $side, $side);

    // OVAL YAP
    $radius = 20;   // DAHA YUVARLAK İÇİN ARTIR (45–60)
    $rounded = roundRect($dst, $radius);

    // KONUM
    $profileX = 80;
    $profileY = 80;

    imagecopy($image, $rounded, $profileX, $profileY, 0, 0, $newW, $newH);

    imagedestroy($src);
    imagedestroy($dst);
    imagedestroy($rounded);
}

// METİNLER
$ad      = "Can Örge";
$unvan   = "Yazılım Stajyeri";
$unvanEng= "Software Intern";
$telefon = "0555 123 45 67";
$mail    = "can@firma.com";

// KONUM
$nameX  = 640;
$nameY  = 75;

$titleX = 640;
$titleY = 120;

$telX   = 665;
$telY   = 247;

$mailX  = 665;
$mailY  = 284;

// YAZDIR (İSİM EKSTRA KALIN DURSUN DİYE FONT2)
imagettftext($image, 26, 0, $nameX,  $nameY,  $white, $font2, $ad);
imagettftext($image, 18, 0, $titleX, $titleY, $white, $font,  $unvan);
imagettftext($image, 18, 0, $titleX, $titleY + 30, $white, $font,  $unvanEng);
imagettftext($image, 16, 0, $telX,   $telY,   $white, $font,  $telefon);
imagettftext($image, 16, 0, $mailX,  $mailY,  $white, $font,  $mail);

// PNG ÇIKTI
header("Content-Type: image/png");
header("Content-Disposition: attachment; filename=imza.png");

imagepng($image);
imagedestroy($image);
exit;
