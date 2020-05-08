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

if ($action == 'update' && $access->auth(AUTH::FEEDBACK))
{
   $ts = strtotime("now");
   $id_person = (request('anonymous') == 'true') ? 'NULL' : $whoami->id();

   if (is_null($no))
   {
      $query = "insert into feedback (id_project, id_person, ts, status, comment) "
              . "values ($id_project, $id_person, $ts, $db->fbk_stat_new, " . $db->qpost('comment') . ")";
   }
   else
   {
      if (!is_null($delete))
      {
         $query = "delete from feedback where id = $no";
      }
      else
      {
         $query = "update feedback set comment = " . $db->qpost('comment') . ", "
                 . "ts = $ts,"
                 . "id_project = $id_project, "
                 . "id_person = $id_person "
                 . "where id = $no";
      }
      $no = NULL;
   }
   if (is_null(request('cancel')))
      $db->query($query);
}

if ($action == 'tupdate' && $access->auth(AUTH::FEEDBACK_W))
{
   $query = "update project set feedback_text = " . $db->qpost('text') . " "
           . "where id = $id_project";
   $db->query($query);
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

if ($action != 'tview' && $access->auth(AUTH::FEEDBACK_W))
{
   $help = "Klikk for å editere...";
   $button = "<img src=images/cross_re.gif title=\"$help\">";
   echo "<a href=\"$php_self?_action=tview&id_project=$id_project\">$button</a> ";
}

if ($action == 'tview' && $access->auth(AUTH::FEEDBACK))
{
   $form = new FORM();
   echo "<input type=hidden name=_action value=tupdate>
      <input type=hidden name=id_project value=\"$id_project\">
      <textarea cols=60 rows=15 wrap=virtual name=text>" . $proj['feedback_text'] . "</textarea>
      <input type=submit value=Lagre>";
   unset($form);
}
else
{
   $feedback_text = str_replace("\n", "<br>\n", $proj['feedback_text']);
   $feedback_text = replace_links($feedback_text);
   echo $feedback_text;
}
echo "<p>";


$form = new FORM();

if ($action == 'new')
{
   echo "<input type=hidden name=_action value=update>
    <input type=hidden name=id_project value=$id_project>\n";

   $tb = new TABLE('id=no_border');

   $tb->td("<input type=submit value=Registrer>");
   $tb->td("<input type=submit name=cancel value=Avbryt>");
   $tb->tr();
   $tb->td("<i>Anonym:</i>");
   $tb->td(check_anonymous());
   unset($tb);

   echo "<textarea cols=60 rows=15 wrap=virtual name=comment></textarea><p>";
}

$query = "select id, ts, status, comment "
        . "from feedback "
        . "where id_project = $id_project "
        . "and id_person = " . $whoami->id() . " "
        . "and not status = $db->fbk_stat_discarded "
        . "order by ts desc";
$stmt = $db->query($query);

//if ($stmt->rowCount() > 0)
// echo "<h2>Allerede registrerte tilbakemeldinger for samme prosjekt</h2>";

foreach ($stmt as $row)
{
   if ($row['id'] == $no && $row['status'] == $db->fbk_stat_new)
   {
      echo "<input type=hidden name=_action value=update>
    <input type=hidden name=_no value=$no>
    <input type=hidden name=id_project value=$id_project>\n";
      $tb = new TABLE('id=no_border');
      $tb->td("<input type=submit value=Lagre>");
      $tb->td("<input type=submit name=_delete value=Slett onClick=\"return confirm('Sikkert at du vil slette denne tilbakemeldingen?');\" >", 'align=left');
      $tb->td("<input type=submit name=cancel value=Avbryt>");
      unset($tb);
      $tb = new TABLE('id=no_border');
      $tb->td("<i>Sist endret:</i>");
      $tb->td(strftime('%e.%b %y', $row['ts']));
      $tb->tr();
      $tb->td("<i>Anonym:</i>");
      $tb->td(check_anonymous(FALSE));
      unset($tb);
      echo "<textarea cols=60 rows=15 wrap=virtual name=comment>" . $row['comment'] . "</textarea>\n";
   }
   else
   {
      $button = '';
      if ($row['status'] == $db->fbk_stat_new)
      {
         $help = "Klikk for å editere.\nTilbakemeldingen kan endres frem til det blir markert som lest av styret";
         $icon = "<img src=images/cross_re.gif title=\"$help\">";
         $button = "<a href=\"$php_self?_action=view&_no=" . $row['id'] . "&id_project=$id_project\">$icon</a> ";
      }

      echo "<h2>$button " . strftime('%e.%b %Y', $row['ts']) . "</h2>\n";
      $comment = str_replace("\n", "<br>\n", $row['comment']);
      $comment = replace_links($comment);
      echo $comment;
   }
   echo "<p>";
}
unset($form);

if ($action != 'new' && $action != 'view' && $access->auth(AUTH::FEEDBACK))
{
   $form = new FORM();
   echo "<input type=hidden name=_action value=new>
      <input type=hidden name=id_project value=\"$id_project\">
      <input type=submit value=\"Ny tilbakemelding\">";
   unset($form);
}
