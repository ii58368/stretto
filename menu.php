
<!-- Codrops top bar -->
<div class="codrops-top clearfix">
    <?php echo $prj_name; ?>
    <span class="right"><strong><?php echo auth_whoami(); ?></strong></span>
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
                        $s = $db->query("select id from person where email='$whoami'");
                        $pers = $s->fetch(PDO::FETCH_ASSOC);
                        auth_li("Mine prosjekter", "participant_1x.php?id=$pers[id]");
                        auth_li("Min prøveplan", "myPlan.php");
                        auth_li("Min regi", "myDirection.php");
                        auth_li("Mine personopplysninger", "personEdit.php?_no=$pers[id]");
                        ?>  
                    </ul>
                </li>
                <li>
                    <a href="#">Regi</a>
                    <ul class="dl-submenu">
                        <?php
                        auth_li("Ressurser", "dirResources.php");
                        auth_li("Turnus", "dirShift.php");
                        auth_li("Prosjekt", "dirProject.php");
                        auth_li("Regiplan", "dirPlan.php?id_project=%");
                        ?>
                    </ul>
                </li>
                <li>
                    <a href="#">Admin</a>
                    <ul class="dl-submenu">
                        <?php
                        auth_li("Medlemsliste", "person.php");
                        auth_li("Prøveplan", "plan.php?id_project=%");
                        auth_li("Grupper", "groups.php");
                        auth_li("Instrumenter", "instruments.php");
                        auth_li("Tilgang", "access.php");
                        auth_li("Tilgangsgrupper", "view.php");
                        auth_li("Notearkiv", "repository.php");
                        auth_li("Prosjekter", "project.php");
                        auth_li("Tilbakemeldinger", "feedback.php");
                        auth_li("Lokale", "location.php");
                        auth_li("Ressurser", "participant_xx.php");
                        auth_li("Dokumenter", "document.php?path=common");
                        auth_li("Kontigent", "contigent.php");
                        auth_li("Om $prj_name", "about.php");
                        ?>
                    </ul>
                </li>
                <li>
                    <a href="#">Prosjekter</a>
                    <ul class="dl-submenu">
                        <?php
                        $q = "select id, name, semester, year, orchestration "
                                . "from project "
                                . "where (status = $prj_stat_public "
                                . "or status = $prj_stat_tentative) "
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
                           auth_li("Prosjektinfo", "prjInfo.php?id=$pid");
                           auth_li("Gruppeoppsett", "seating.php?id_project=$pid");
                           auth_li("Program", "program.php?id=$pid");
                           auth_li("Musikere", "person.php?id=$pid");
                           auth_li("Noter", "document.php?path=project/$pid/sheet");
                           auth_li("Innspilling", "document.php?path=project/$pid/rec");
                           auth_li("Dokumenter", "document.php?path=project/$pid/doc");
                           auth_li("Regikomité", "direction.php?id_project=$pid");
                           auth_li(($e[orchestration] == $prj_orch_tutti) ? "Permisjonssøknad" : "Påmelding", "participant_11.php?id_project=$pid&id_person=$pers[id]");
                           auth_li("Tilbakemelding", "feedback?id=$pid");
                           auth_li("Fravær", "absence.php?id=$pid");
                           auth_li("Prosjektressurser", "participant_x1.php?id=$pid");
                           auth_li("Konsertkalender", "consert?id=$pid");
                           echo "
                                </ul>
                            </li>";
                        }
                        ?>
                    </ul>
                </li>
                <?php
                auth_li("Hva skjer?", "event.php");
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
