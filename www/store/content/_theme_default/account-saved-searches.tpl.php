     <?php
     // Checks template is loading via system, so do not move..
     if (!defined('PARENT')) {
       exit;
     }

     // ACCOUNT SAVED SEARCHES TEMPLATE FILE
     // If required you can recall any value from the account table via the $this->ACCOUNT array..
     // This is for advanced users only..to see the contents of the array use print_r.
     // print_r($this->ACCOUNT)
     // Use the mc_safeHTML($data) function for safe display of user content..
     ?>

     <div class="container maincontainer">
       <div class="row">
         <div class="col-lg-3 col-md-3 leftmenuwrapper">
           <?php
           // Load account menu..
           include(PATH . $this->THEME_FOLDER . '/account-menu.php');

           // LEFT MENU BOXES
           // Controlled in admin setting via left box controller
           // See docs for more help
           // Some options are disabled in account view, enable in 'control/system/accounts/*' pages

           // PRICE POINTS
           // html/left-menu/price-points-wrapper.htm
           // html/left-menu/price-points-link.htm

           // BRANDS
           // html/left-menu/brands-wrapper.htm
           // html/left-menu/brands-link.htm

           // MOST POPULAR PRODUCTS
           // html/left-menu/most-popular-wrapper.htm
           // html/left-menu/most-popular-links.htm

           // RECENTLY VIEWED ITEMS
           // html/left-menu/most-recent-wrapper.htm
           // html/left-menu/most-recent-links.htm

           // NEW PAGES / OTHER LINKS..
           // html/left-menu/new-pages-wrapper.htm
           // html/left-menu/new-page-links.htm

           // LATEST TWEETS..
           // html/left-menu/latest-tweets.htm

           // RSS SCROLLER..
           // html/left-menu/rss-scroller.htm

           // CUSTOM BOXES..
           // customTemplates/box**.tpl.php
           echo $this->LEFT_MENU_BOXES;
           ?>
         </div>
         <div class="col-lg-9 col-md-9 rightbodyarea margin_bottom_50">
           <div class="panel panel-default">
             <div class="panel-heading uppercase">
               <i class="fa fa-search fa-fw"></i> <?php echo $this->TEXT[12]; ?>
             </div>
             <div class="panel-body">
               <?php
               // Are saved searches only kept for xx days?
               if ($this->SETTINGS['savedSearches'] > 0) {
                 echo str_replace('{days}', $this->SETTINGS['savedSearches'], $this->TEXTW[1]);
               } else {
                 echo $this->TEXTW[0];
               }
               ?>
             </div>
           </div>

           <div class="row savedsearches" style="margin-top:10px">
             <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
               <?php
               // SAVED SEARCHES..
               // html/accounts/saved-search.htm
               // html/accounts/saved-searches-wrapper.htm
               // html/nothing-to-show.htm
               echo $this->SAVED_SEARCHES;
               ?>
             </div>
           </div>

           <?php
           // Only show page numbers if at least 1 entry exists..
           if ($this->PAGINATION) {
           ?>
           <div class="row mswpages">
             <div class="col-lg-12">
             <?php echo $this->PAGINATION; ?>
             </div>
           </div>
           <?php
           }
           ?>
         </div>
       </div>

     </div>


