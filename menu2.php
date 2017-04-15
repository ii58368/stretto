
<div class="container demo-1">	
        <!-- Codrops top bar -->
    <div class="codrops-top clearfix">
        <a href="http://tympanus.net/Tutorials/AppShowcase/"><strong>&laquo; Previous Demo: </strong>App Showcase</a>
        <span class="right"><a href="http://tympanus.net/codrops/?p=14753"><strong>Back to the Codrops Article</strong></a></span>
    </div><!--/ Codrops top bar -->
    <header class="clearfix">
        <h1>Responsive Multi-Level Menu <span>Space-saving drop-down menu with subtle effects</span></h1>	
        <nav class="codrops-demos">
            <a class="current-demo" href="index.html">Demo 1</a>
            <a href="index2.html">Demo 2</a>
            <a href="index3.html">Demo 3</a>
            <a href="index4.html">Demo 4</a>
            <a href="index5.html">Demo 5</a>
        </nav>
    </header>
    <div class="main clearfix">
<div class="column">
    <div id="dl-menu" class="dl-menuwrapper">
        <button cl ass="dl-trigger">Open Menu</button>
        <ul class="dl-menu">
            <li>
                <a href="#">Fashion</a>
                <ul class="dl-submenu">
 
                    <li>Menu
                        <ul class="dl-menu">
                            <li>Min side
                                <ul class="dl-menu">
                                   <?php 
                                         echo "<li>koko</li>";
 //                                      auth_li("Mine prosjekter", "myProject.php"); 
//                                       auth_li("Min prøveplan", "myPlan.php");
//                                       auth_li("Min regi", "myDirection.php");
//                                       auth_li("Mine personopplysninger", "personal.php");
                                   ?>
                                </ul>
                            <li>Regi
                                <ul class="dl-menu">
                                    <?php
//                                        auth_li("Ressurser", "dirResources.php");
//                                        auth_li("Turnus", "dirShift.php");
  //                                      auth_li("Prosjekt", "dirProject.php");
    //                                    auth_li("Regiplan", "dirPlan.php");
                                    ?>
                                </ul>
                            </li>
                            <li>Admin
                                <ul class="dl-menu">
                                    <?php
   //                                     auth_li("Medlemsliste", "person.php");
    //                                    auth_li("Prøveplan", "plan.php");
      //                                  auth_li("Grupper", "group.php");
        //                                auth_li("Instrumenter", "instruments.php");
          //                              auth_li("Tilgang", "access.php");
            //                            auth_li("Tilgangsgrupper", "view.php");
              //                          auth_li("Notearkiv", "repository.php");
                //                        auth_li("Prosjekter", "project.php");
                  //                      auth_li("Tilbakemeldinger", "feedback.php");
                    //                    auth_li("Lokale", "location.php");
                      //                  auth_li("Permisjon/Deltagelse", "participant.php");
                        //                auth_li("Dokumenter", "document?path=common");
                          //              auth_li("Kontigent", "contigent.php");
                            //            auth_li("Om $prj_name", "about.php");
                                    ?>
                                </ul>
                            </li>
                            <li>Prosjekt
                                <ul class="dl-menu">
                                    <li>Operaball
                                        <ul class="dl-menu">
                                            <?php
                                                $pid = $row[id];
                      //                          auth_li("Prosjektinfo", "prjInfo.php?id=$pid");
                        //                        auth_li("Gruppeoppsett", "seating.php?id=$pid");
                          ////                    auth_li("Program", "program.php?id=$pid");
                              //                  auth_li("Musikere", "person.php?id=$pid");
                                //                auth_li("Noter", "document.php?path=project/$pid/sheet");
                                  //              auth_li("Innspilling", "document.php?path=project/$pid/rec");
                                    //            auth_li("Dokumenter", "document.php?path=project/$pid/doc");
                                      //          auth_li("Regikomité", "direction.php?id=$pid");
                                        //        auth_li("Påmelding/permisjonssøknad", "absence=id=$pid");
                                          //      auth_li("Tilbakemelding", "feedback?id=$pid");
                                            //    auth_li("Fravær", "absence.php?id=$pid");
                                          //      auth_li("Prosjektressurser", "resources?id=$pid");
                                            //    auth_li("Konsertkalender", "consert?id=$pid");
                                            ?>
                                        </ul>
                                    </li>
                                </ul>
                            </li>
                            <?php
                  //              auth_li("Hva skjer?", "event.php");
                            ?>                                   
                        </ul>
                    </li>
                </ul>
            </li>  
        </ul>
    </div>
</div>
        
    </div>
</div>
		<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js"></script>
		<script src="js/jquery.dlmenu.js"></script>
		<script>
			$(function() {
				$( '#dl-menu' ).dlmenu();
			});
		</script>



