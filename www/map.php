<?php

// Note: Call to imagefttext() requires that FreeType is supported for the
// actual php version.
require_once 'request.php';
require_once 'conf/opendb.php';
require_once 'auth.php';

$id_groups = request('id_groups');
$id_project = request('id_project');
$id_template = request('template');

if (is_null($id_template))
   $id_template = 0;

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

   if (isset($atext[1]))
      imagefilledpolygon($img, $p, 4, $green);

   imagepolygon($img, $p, 4, $black);

   $font = __DIR__."/Avenir.ttc";
   $text = $atext[0];

   $box = @imageTTFBbox($fsize, 0, $font, $text);
   $tw = abs($box[4] - $box[0]);
   $th = abs($box[5] - $box[1]);
   $tx = $x + (($w - $tw) * cos($a) - ($h + $th) * sin($a)) / 2;
   $ty = $y + (($h + $th) * cos($a) + ($w - $tw) * sin($a)) / 2;

   imagefttext($img, $fsize, -$a * 360 / (2 * pi()), $tx, $ty, $col, $font, $text);
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

   if (!is_null($text1))
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
   global $access;

   if (($uid = request('uid')) == null)
      $uid = 'unknown';

   foreach ($part as $p)
   {
      if ($p['position'] == $pos)
      {
         $tag = ($p['uid'] == $uid) ? $tag = '*x' : '';
         return $p['firstname'] . " " . mb_substr($p['lastname'], 0, 1, 'utf-8') . $tag;
      }
   }
   if ($access->auth_uid($uid, AUTH::SEAT))
      return $pos;
   return "";
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
           . "and participant.stat_final = $db->par_stat_yes "
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
      if ($lineup[$i * 4] == -2)
      {
         draw_desk($img, $lineup[$i * 4 + 1], $lineup[$i * 4 + 2], $r, $i + 1, get_txt($part, $seat_no + 1), get_txt($part, $seat_no + 2));
      }
      $seat_no += abs($lineup[$i * 4]);
   }
}

$v1_18 = array(
    2, 300, 20, 0,
    2, 300, 140, 0,
    2, 300, 260, 0,
    2, 300, 380, 0,
    2, 300, 500, 0,
    2, 60, 180, 8,
    2, 47, 300, 8,
    2, 33, 420, 8,
    2, 20, 540, 8,
);

$v1_20 = array(
    2, 300, 20, 0,
    2, 300, 140, 0,
    2, 300, 260, 0,
    2, 300, 380, 0,
    2, 300, 500, 0,
    2, 300, 620, 0,
    2, 60, 180, 8,
    2, 47, 300, 8,
    2, 33, 420, 8,
    2, 20, 540, 8,
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

$v2_18 = array(
    2, 350, 20, 30,
    2, 400, 200, 25,
    2, 200, 80, 32,
    2, 430, 360, 25,
    2, 240, 255, 30,
    2, 50, 140, 33,
    2, 430, 500, 25,
    2, 240, 395, 30,
    2, 50, 280, 33,
);

$vla_18 = array(
    -2, 20, 100, -30,
    -2, 20, 210, -25,
    -2, 220, 110, -32,
    -2, 20, 360, -25,
    -2, 240, 255, -30,
    -2, 440, 150, -33,
    -2, 20, 500, -25,
    -2, 240, 395, -30,
    -2, 445, 280, -33,
);

$vcl_16 = array(
    -2, 10, 20, 0,
    -2, 10, 130, 0,
    -2, 10, 240, 0,
    -2, 10, 350, 0,
    -2, 10, 460, 0,
    -2, 300, 185, -10,
    -2, 320, 290, -10,
    -2, 340, 405, -10,
    -2, 10, 570, 0,
    -2, 240, 570, -5,
    1, 470, 550, -10,
);

$vcl_15 = array(
    -2, 10, 20, 0,
    -2, 10, 140, 0,
    -2, 10, 260, 0,
    -2, 10, 380, 0,
    -2, 10, 500, 0,
    1, 350, 190, -10,
    -2, 320, 320, -10,
    -2, 340, 440, -10,
);

$template = array(
    array(), // Blank array
    $v1_18,
    $v1_15,
    $v2_18,
    $vla_18,
    $vcl_16,
    $vcl_15,
    $v1_20
);

$img = imagecreatetruecolor(650, 690);
$bgcol = imagecolorAllocate($img, 0xff, 0xfc, 0xf5);
imagefill($img, 0, 0, $bgcol);

header('Content-Type: image/jpeg');

draw_group($img, $template[$id_template]);

imagejpeg($img);
imagedestroy($img);
