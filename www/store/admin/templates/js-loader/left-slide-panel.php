    <script>
    //<![CDATA[
    jQuery(document).ready(function() {
      jQuery('#leftpanelmenu').mmenu({
        'extensions' : [
          'pageshadow'
         ],
         'navbar' : {
           'title' : '<?php echo mc_filterJS($mc_admin[3]); ?>'
         }
      });
      var mmapi = jQuery('#leftpanelmenu').data('mmenu');
      jQuery('#leftpanelbutton').click(function() {
        mmapi.open();
      });
      jQuery('#leftpanelbuttonxs').click(function() {
        mmapi.open();
      });
      mmapi.bind('opened', function () {
        mc_menuButton('open');
      });
      mmapi.bind('closed', function () {
        mc_menuButton('close');
      });
		});
    //]]>
    </script>