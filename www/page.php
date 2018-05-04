<!-- Codrops top bar -->
<div class="codrops-top clearfix">
   <div id="dl-menu" class="dl-menuwrapper">
      <button class="dl-trigger">Open Menu</button>
      <?php
         $menu->generate();
      ?>
  </div><!-- /dl-menuwrapper -->
  <span class="codrops-top-title"><?php echo "$prj_name"; ?></span>
  <span class="codrops-top-season"><strong><?php $menu->season(); ?></strong></span>
  <span class="codrops-top-name"><strong><?php $menu->whoami(); ?></strong></span>
</div>

<script src="js/jquery.min.js"></script>
<script src="js/jquery.dlmenu.js"></script>
<script>
   $(function () {
       $('#dl-menu').dlmenu();
   });
</script>
