<?php

require 'framework.php';

$id_project = request('id_project');

function check_anonymous($checked = FALSE)
{
   $title = "Sjekk av hvis tilbakemeldingen skal være anonym. Merk: det er ikke mulig å se eller endre en anonym tilbakemelding i etterkant";

   $str = "<input type=checkbox name=anonymous value=true ";
   if ($checked)
      $str .= "checked ";
   $str .= "title=\"$title\"";
   $str .= ">\n";
   
   return $str;
}

echo "<h1>Tilbakemelding</h1>\n";
echo "Kom gjerne med en tilbakemelding til styret om hva du synes om dette prosjektet. 
   Tilbakemeldingene vil bli gjennomgått på neste styremøte og deretter markert som lest. 
   Du kan editere tilbakemeldingen din så frem til den er markert som lest av styret.
   Mrk.: Anonyme tilbakemeldingen kan ikke endres etter de er sendt. ";

$query = "select name, semester, year, feedback_text from project where id = $id_project";
$stmt = $db->query($query);
$proj = $stmt->fetch(PDO::FETCH_ASSOC);
echo "<h2>" . $proj['name'] . " (" . $proj['semester'] . $proj['year'] . ")</h2>";
$feedback_text = str_replace("\n", "<br>\n", $proj['feedback_text']);
$feedback_text = replace_links($feedback_text);
echo $feedback_text;
echo "<p>";

echo "<form action='feedback.php' method=post>\n";

echo "<input type=hidden name=_action value=update>
    <input type=hidden name=id_project value=$id_project>\n";

$tb = new TABLE('id=no_border');

$tb->td("<input type=submit value=Registrer>");
$tb->tr();
$tb->td("<i>Anonym:</i>");
$tb->td(check_anonymous());
unset($tb);

echo "<textarea cols=60 rows=15 wrap=virtual name=comment></textarea>
    </form>
    <p>";

$query = "select feedback.id as id, feedback.ts as ts, "
        . "feedback.status as status, feedback.comment as comment, "
        . "project.name as pname, year, semester, "
        . "project.id as id_project "
        . "from feedback left join project "
        . "on feedback.id_project = project.id "
        . "where ts > " . $season->ts()[0] . " "
        . "and ts < " . $season->ts()[1] . " "
        . "and feedback.id_person = " . $whoami->id() . " "
        . "and not feedback.status = $db->fbk_stat_discarded "
        . "order by ts desc";
$stmt = $db->query($query);

if ($stmt->rowCount() > 0)
   echo "<h2>Allerede registrerte tilbakemeldinger for samme prosjekt</h2>";

foreach ($stmt as $row)
{
   $href = '';
   $a_end = '';
   $help = $db->fbk_stat[$row['status']];

   if ($row['status'] == $db->fbk_stat_new)
   {
      $href = "<a href=\"feedback.php?_action=view&_no=" . $row['id'] . "\">";
      $a_end = "</a>";
      $help .= ". Klikk for å endre...";
   }
   $bullet = "$href<img src=images/feedback_stat_" . $row['status'] . ".gif title=\"$help\">$a_end";
   echo "$bullet<font size=+2><b>" . strftime('%e.%b %Y', $row['ts']) . "</b></font><br>\n";
   $comment = str_replace("\n", "<br>\n", $row['comment']);
   $comment = replace_links($comment);
   echo $comment;

   echo "<p>";
}


