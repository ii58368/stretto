<!-- Codrops top bar -->
<div class="codrops-top clearfix">
    <?php echo $prj_name; ?>
    <span class="right"><strong><?php echo $auth->whoami(); ?></strong></span>
</div><!--/ Codrops top bar -->
<div class="main clearfix">
    <div class="column">
        <div id="dl-menu" class="dl-menuwrapper">
            <button class="dl-trigger">Open Menu</button>
            <ul class="dl-menu">
                <li>
                    <a href="#">Mine sider</a>
                    <ul class="dl-submenu">
                        <?php
                        $s = $db->query("select id from person where uid='$whoami'");
                        $pers = $s->fetch(PDO::FETCH_ASSOC);
                        $auth->li("Mine prosjekter", "participant_1x.php?id=$pers[id]");
                        $auth->li("Min prøveplan", "myPlan.php");
                        $auth->li("Min regi", "myDirection.php");
                        $auth->li("Mine personopplysninger", "personEdit.php?_no=$pers[id]");
                        ?>  
                    </ul>
                </li>
                <li>
                    <a href="#">Regi</a>
                    <ul class="dl-submenu">
                        <?php
                        $auth->li("Ressurser", "dirResources.php");
                        $auth->li("Turnus", "dirShift.php");
                        $auth->li("Prosjekt", "dirProject.php");
                        $auth->li("Regiplan", "dirPlan.php?id_project=%");
                        ?>
                    </ul>
                </li>
                <li>
                    <a href="#">Admin</a>
                    <ul class="dl-submenu">
                        <?php
                        $auth->li("Medlemsliste", "person.php");
                        $auth->li("Prøveplan", "plan.php?id_project=%");
                        $auth->li("Grupper", "groups.php");
                        $auth->li("Instrumenter", "instruments.php");
                        $auth->li("Tilgang", "access.php");
                        $auth->li("Tilgangsgrupper", "view.php");
                        $auth->li("Notearkiv", "repository.php");
                        $auth->li("Prosjekter", "project.php");
                        $auth->li("Tilbakemeldinger", "feedback.php");
                        $auth->li("Lokale", "location.php");
                        $auth->li("Ressurser", "participant_xx.php");
                        $auth->li("Dokumenter", "document.php?path=common");
                        $auth->li("Kontingent", "contingent.php");
                        $auth->li("Om $prj_name", "about.php");
                        ?>
                    </ul>
                </li>
                <li>
                    <a href="#">Prosjekter</a>
                    <ul class="dl-submenu">
                        <?php
                        $q = "select id, name, semester, year, orchestration "
                                . "from project "
                                . "where (status = $db->prj_stat_public "
                                . "or status = $db->prj_stat_tentative) "
                                . "and year >= " . date("Y") . " "
                                . "order by year,semester DESC";
                        $s = $db->query($q);

                        foreach ($s as $e)
                        {
                           echo "
                            <li>
                                <a href=\"#\">$e[name] ($e[semester]$e[year])</a>
                                <ul class=\"dl-submenu\">";
                           $pid = $e[id];
                           $auth->li("Prosjektinfo", "prjInfo.php?id=$pid");
                           $auth->li("Gruppeoppsett", "seating.php?id_project=$pid");
                           $auth->li("Program", "program.php?id=$pid");
                           $auth->li("Musikere", "person.php?id=$pid");
                           $auth->li("Noter", "document.php?path=project/$pid/sheet");
                           $auth->li("Innspilling", "document.php?path=project/$pid/rec");
                           $auth->li("Dokumenter", "document.php?path=project/$pid/doc");
                           $auth->li("Regikomité", "direction.php?id_project=$pid");
                           $auth->li(($e[orchestration] == $prj_orch_tutti) ? "Permisjonssøknad" : "Påmelding", "participant_11.php?id_project=$pid&id_person=$pers[id]");
                           $auth->li("Tilbakemelding", "feedback?id=$pid");
                           $auth->li("Fravær", "absence.php?id_project=$pid");
                           $auth->li("Prosjektressurser", "participant_x1.php?id=$pid");
                           $auth->li("Konsertkalender", "consert?id=$pid");
                           echo "
                                </ul>
                            </li>";
                        }
                        ?>
                    </ul>
                </li>
                <?php
                $auth->li("Hva skjer?", "event.php");
                ?>
            </ul>
        </div><!-- /dl-menuwrapper -->
    </div>
</div>

<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
<script src="js/jquery.dlmenu.js"></script>
<script>
   $(function () {
       $('#dl-menu').dlmenu();
   });
</script>
