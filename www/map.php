<?php

// Note: Call to imagefttext() requires that FreeType is supported for the
// actual php version.
require_once 'request.php';
require_once 'conf/opendb.php';

$id_groups = $_REQUEST[id_groups];
$id_project = $_REQUEST[id_project];
$id_template = $_REQUEST[template];

function draw_chair($img, $x, $y, $a, $w, $h, $ftext)
{
   $black = imagecolorallocate($img, 0, 0, 0);
   $green = imagecolorallocate($img, 0, 255, 0);
   $fsize = 12;
   $col = imagecolorallocate($img, 0, 0, 0);

   $p = array(
       $x, $y,
       $x + $w * cos($a), $y + $w * sin($a),
       $x + $w * cos($a) - $h * sin($a), $y + $h * cos($a) + $w * sin($a),
       $x - $h * sin($a), $y + $h * cos($a)
   );

   $atext = explode("*", $ftext);
   
   if ($atext[1])
      imagefilledpolygon($img, $p, 4, $green);
   
   imagepolygon($img, $p, 4, $black);
   
   $font = "Avenir.ttc";
   $text = $atext[0];

   $box = @imageTTFBbox($fsize, 0, $font, $text);
   $width = abs($box[4] - $box[0]);
   $height = abs($box[5] - $box[1]);
   $tx = $x + + ($w * cos($a) - $h * sin($a)) / 2 - $width/2;
   $ty = $y + ($h * cos($a) + $w * sin($a)) / 2 + $heigth/2;

   imagefttext($img, $fsize, -$a*360/(2* pi()), $tx, $ty, $col, $font, $text);
}

function draw_desk($img, $x0, $y0, $a, $no, $text0, $text1)
{
   $w = 100;
   $h = 70;
   $fsize = 3;
   $dth = -13;
   $col = imagecolorallocate($img, 0, 0, 0);

   $x1 = $x0 + $w * cos($a);
   $y1 = $y0 + $w * sin($a);
   $wt = $w / 2;

   draw_chair($img, $x0, $y0, $a, $w, $h, $text0);

   if ($text1 != null)
   {
      $wt = $w;
      draw_chair($img, $x1, $y1, $a, $w, $h, $text1);
   }

   $tx = $x0 + $wt * cos($a) - $dth * sin($a) - imageFontWidth($fsize) * strlen($no) / 2;
   $ty = $y0 + $dth * cos($a) + $wt * sin($a) - imageFontHeight($fsize) / 2;
   imagestring($img, $fsize, $tx, $ty, $no, $col);
}

function get_txt($part, $pos)
{
   global $whoami;

   foreach ($part as $p)
   {
      if ($p[position] == $pos)
      {
         if ($p[uid] == $whoami->uid())
            $tag = '*x';
         return $p[firstname] . " " . mb_substr($p[lastname], 0, 1, 'utf-8') . $tag;
      }
   }
   return $pos;
}

function draw_group($img, $lineup)
{
   global $db;
   global $id_project;
   global $id_groups;

   if ($lineup == null)
      return;

   $query = "SELECT position, firstname, lastname, uid "
           . "FROM person, participant, instruments, groups "
           . "where person.id = participant.id_person "
           . "and participant.id_project = $id_project "
           . "and participant.id_instruments = instruments.id "
           . "and instruments.id_groups = groups.id "
           . "and groups.id = $id_groups "
           . "order by participant.position";

   $stmt = $db->query($query);
   $part = $stmt->fetchAll(PDO::FETCH_ASSOC);

   $seat_no = 0;
   for ($i = 0; $i < sizeof($lineup) / 4; $i++)
   {
      $r = $lineup[$i * 4 + 3] / 360 * 2 * pi();
      if ($lineup[$i * 4] == 1)
      {
         draw_desk($img, $lineup[$i * 4 + 1], $lineup[$i * 4 + 2], $r, $i + 1, get_txt($part, $seat_no + 1), null);
      }
      if ($lineup[$i * 4] == 2)
      {
         draw_desk($img, $lineup[$i * 4 + 1], $lineup[$i * 4 + 2], $r, $i + 1, get_txt($part, $seat_no + 2), get_txt($part, $seat_no + 1));
      }
      $seat_no += $lineup[$i * 4];
   }
}

$v1_16 = array(
    2, 300, 20, 0,
    2, 300, 140, 0,
    2, 300, 260, 0,
    2, 300, 380, 0,
    2, 300, 500, 0,
    2, 60, 200, 10,
    2, 40, 320, 10,
    2, 20, 440, 10,
);

$v1_15 = array(
    2, 300, 20, 0,
    2, 300, 140, 0,
    2, 300, 260, 0,
    2, 300, 380, 0,
    2, 300, 500, 0,
    1, 100, 210, 10,
    2, 40, 320, 10,
    2, 20, 440, 10,
);

$template = array(
    array(), // Blank array
    $v1_16,
    $v1_15
);

$img = imagecreatetruecolor(600, 600);
$bgcol = imagecolorAllocate($img, 0xff, 0xfc, 0xf5);
imagefill($img, 0, 0, $bgcol);

header('Content-Type: image/jpeg');

draw_group($img, $template[$id_template]);

imagejpeg($img);
imagedestroy($img);
?>