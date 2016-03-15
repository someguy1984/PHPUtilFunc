<?php

$img = $_GET['img'];
$size = (isset($_GET['size'])) ? $_GET['size'] : 300;
$rgb = (isset($_GET['bgcol'])) ? array(hexdec(substr($_GET['bgcol'], 0, 2)), hexdec(substr($_GET['bgcol'], 2, 2)), hexdec(substr($_GET['bgcol'], 4, 2))) : array(19,32,54);

header('Content-Type: image/png');

$imgInfo = pathinfo($img);

list($w, $h) = getimagesize($img);

$newH = ($h < $w) ? (($size / $w) * $h) : $size;
$newW = ($h > $w) ? (($size / $h) * $w) : $size;

$newX = ($h > $w) ? ($size - $newW) / 2 : 2;
$newY = ($h < $w) ? ($size - $newH) / 2 : 2;

$newImg = imagecreatetruecolor($size+4, $size+4);
$bgcol = imagecolorallocate($newImg, $rgb[0], $rgb[1], $rgb[2]);
imagefill($newImg, 0,0, $bgcol);

switch($imgInfo['extension'])
{
    case 'jpg':
    case 'jpeg':
    case 'JPG':
    case 'JPEG':
        $oldImg = imagecreatefromjpeg($img);
        break;

    case 'gif':
    case 'GIF':
        $oldImg = imagecreatefromgif($img);
        break;

    case 'png':
    case 'PNG':
        $oldImg = imagecreatefrompng($img);
        break;

    case 'bmp':
    case 'BMP':
        $oldImg = imagecreatefromwbmp($img);
        break;

    default:
        throw new Exception("No suitable image type found to convert");
}


imagecopyresized($newImg, $oldImg, $newX, $newY, 0,0, $newW, $newH, $w, $h);

imagepng($newImg);
imagedestroy($newImg);
