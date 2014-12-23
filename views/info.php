<?php if (!defined('APPLICATION')) exit(); ?>
<h1><?php echo T('Help');?></h1>
<div class="AptAdInstructions">
    <h5><?php echo T('Instructions');?></h5>
    <ol>
        <li><?php echo sprintf(T('AptAds.Instructions.1','In <a href="%s" >Ad Placement</a> choose Page and Location from the dropdown, and follow the annotations to complete the form, by clicking \'Save\'.'),Url('settings/aptads',true));?></li>
        <li><?php echo T('AptAds.Instructions.2','Once saved the Ad should appear under Ad Placement Listings. Remember if it is not Enabled it will not show up.');?></li>
        <li><?php echo T('AptAds.Instructions.3','You can edit the Placement by clicking on \'Edit\' in the same row, as well as \'Delete\'.');?></li>
        <li><?php echo T('AptAds.Instructions.4','To Add Ad to a Placement click \'View Ads\', then fill out the Add Ad form following the annotations. You can repeat this many times. Again the Ad has to be Enabled to show up, but it is recommended you have at least enough Ads to fill the Rows and Columns. You will see that Image and Text placements are treated differently.');?></li>
        <li><?php echo T('AptAds.Instructions.5','Setting Expire Reminder, will send you an Email when the Ad has expired, and persistently nag with Inform Messages until the Expire Reminder is set again. To disable set to \'Month\', \'Day\',\'Year\'.');?></li>
    </ol>
    <h5><?php echo T('Recommended Sizes');?></h5>
    <p><?php echo T('AptAds.RecommendedSizes.Intro','Sizes can vary quite a bit, and it is really down to what works with the layout, in that position. However here are some recommendations based on typical layouts and positions:');?></p>
    <dl>
        <dt><?php echo T('Before Content');?></dt><dd><?php echo T('AptAds.RecommendedSizes.1','Standard Leaderboard Image (728px × 90px) works well here.<br />However on the Placement instead of specifying Width/Height check "Scale to full width.<br />For best results use images safed proportional to the width of the space you are trying to fit. In browser scaling doesn\'t always liik crisp.<br />Text ads don\'t work well here in general, nor does many Row/Columns.');?></dd>
        <dt><?php echo T('Panel');?></dt><dd><?php echo T('AptAds.RecommendedSizes.2','Tends to be one column only affair, being narrow, however it is still a great place for both Text and Image Ads.<br />Recommended sizes for Images are Medium Rectangle 300px × 250px,  or Square 250px × 250px, or Vertical rectangle 240px × 400px. Again you want to check "Scale to full width".<br />With Text Ads 3-5 Rows in one Placement is ideal.');?></dd>
        <dt><?php echo T('After Content');?></dt><?php echo T('AptAds.RecommendedSizes.3','This space offers a fair bit of flexibility, because it is a nice blank space after all the content. It is worth using this location on pages where users are likely to scroll down, like after the comment box, on Discussion pages, But you don\'t want them stacked too high to require extra scrolling to view.<br />You could either use the full width, or place smaller Ads, perhaps diminishing in size.<br />If you are want columns, three columns of Half Banner 234px × 90px, or two columns of Large Rectangle 336px × 280px, or Half Leader. The first and last use scale to fit, for the second for best results set width 330 if you put height at 0 it will scale the height proportionally.');?></dd>
    </dl>
</div>
<div class="AptAdClear"></div>

