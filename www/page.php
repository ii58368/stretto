<!-- Codrops top bar -->
<div class="codrops-top clearfix">
    <?php echo $prj_name; ?>
    <span class="right"><strong><?php $menu->season(); ?></strong></span>
    <span class="right"><strong><?php $menu->whoami(); ?></strong></span>
<!-- </div> --><!--/ Codrops top bar -->
<!-- <div class="main clearfix">  -->
<!--    <div class="column">   -->
        <div id="dl-menu" class="dl-menuwrapper">
            <button class="dl-trigger">Open Menu</button>
            <?php
            $menu->generate();
            ?>
        </div><!-- /dl-menuwrapper -->
<!--    </div>  -->
</div>

<script src="js/jquery.min.js"></script>
<script src="js/jquery.dlmenu.js"></script>
<script>
   $(function () {
       $('#dl-menu').dlmenu();
   });
</script>
