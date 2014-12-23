<?php if (!defined('APPLICATION')) exit(); ?>
<h1><?php echo $this->Data('Title'); ?></h1>
<style><?php include_once(dirname(dirname(__FILE__)).DS.'design'.DS.'aptadsadmin.css'); ?></style>
<div class="Info">
    <?php echo $this->Data('Description'); ?>
    <div class="AptAdTabs">
    <?php
        $AdPlacements = array();
        $AdPlacements[] = GetValue('AdPlacement',$this->Data);
    echo Anchor(T('View All Ad Placements'),Url('/settings/aptads',true),array('class'=>'SmallButton'));
    ?>
    </div>
    <div class="Listings">
        <div class="EmbedInfo">
            <?php
            echo Wrap(T('Embed Instructions'),'h2');
            echo T("<p class=\"AptAdInfo\">Drag and drop Ad placement(s) on the left into embed spaces marked in grey on the right. ".
                "Once the box changes colour you can let go of the mouse button.</p>".
                "<p class=\"AptAdInfo\">If you have several ads in one embed space you can reorder but dragging it up ".
                "or down within that space until a coloured box appears where you want it then release. ".
                "You can remove them by clicking on the &times; in the top right of each placement.</p>".
                "<p class=\"AptAdInfo\">As this is diagrammatic it may not appear exactly like this when embedded. ".
                "Any animation is to illustrate the rotation and is not strictly accurate.</p>");
            ?>
        </div>
        <div class="PlacementEmbed">
        <?php
            $this->AptAds->InsertAdsDemo($this->Data('AdPlacements'));
        ?>
        <?php 
        if (GetValue('Pager',$this))
            echo $this->Pager->Render(); 
        ?>
        </div>
        <div class="EmbedSpaces">
            <table>
                <tr>
                    <td class="Embed EmbedTop"><code>top</code>
                    <?php
                        $this->AptAds->InsertAdsDemo(GetValue('top', $this->Data('EmbedSpaces')), TRUE);
                    ?>
                    </td>
                    <td class="Embed EmbedSide" rowspan="3"><code>side</code>
                    <?php
                        $this->AptAds->InsertAdsDemo(GetValue('side', $this->Data('EmbedSpaces')), TRUE);
                    ?>
                    </td>
                </tr>
                <tr>
                    <td class="EmbedContent"><?php echo T('Page Content'); ?></td>
                </tr>
                <tr>
                    <td class="Embed EmbedBottom"><code>bottom</code>
                    <?php
                        $this->AptAds->InsertAdsDemo(GetValue('bottom', $this->Data('EmbedSpaces')), TRUE);
                    ?>
                    </td>
                </tr>
                <tr>
                    <td class="Embed EmbedCustom" colspan="2"><code>custom</code>
                    <?php
                        $this->AptAds->InsertAdsDemo(GetValue('custom', $this->Data('EmbedSpaces')), TRUE);
                    ?>
                    </td>
                </tr>
            </table>
        </div>
        <div class="EmbedInfo">
            <?php
            echo Wrap(T('Embed Code'),'h2');
            echo '<p>'.T('If you are doing the embedding yourself and wish to embed dynamically add the following script once to your non-Vanilla pages (otherwise ignore):').'</p>';
            echo '<p><pre>&lt;script src="'.Url('/plugins/AptAds/js/embedcode.js',TRUE).'" type="text/javascript" &gt;&lt;/script&gt;</pre></p>';
            echo '<p>'.T('If you wish to load in wordpress place the following in your theme\'s functions.php:').'</p>';
            ?>
<p class="AptAdInfo"><pre>function load_aptads() {  
    wp_register_script('aptads','<?php echo Url('/plugins/AptAds/js/embedcode.js',TRUE) ?>');  
    wp_enqueue_script('aptads');  
}  
add_action('wp_enqueue_scripts', 'load_aptads');</pre></p>
            <?php
            echo '<p> '.sprintf(T('You can use the following embed codes anywhere on your pages, which is going to be used to insert the embed space or a placement (you could insert them in wordpress text widgets for instance):<br><code>&lt;!--|aptads embed=<span id="EmbedCode">%s</span>|--&gt;</code>'),GetValue('AdPlacement',$this->Data) ? $AdPlacement->AdPlacementID : '<i>n</i>').'</p>';
            if(!GetValue('AdPlacement',$this->Data)){
                echo '<p id="EmbedCodeDescription"> '.T('Where <i>n</i> is one of the embed spaces (<code>top</code>, <code>side</code>, <code>bottom</code>, <code>custom</code>) or an AdPlacementID or a AdPlacementLabel prefixed by <code>#</code>').'</p>';
            }
            echo '<p id="EmbedCodeID"> '.sprintf(T('If stripping comments it can also be added like so: <br><code>&lt;input type="hidden" class="AptAds" value="<span id="EmbedCodeInput">%s</span>"&gt;</code>'),GetValue('AdPlacement',$this->Data) ? $AdPlacement->AdPlacementID : '<i>n</i>').'</p>';
            echo '<p id="EmbedCodeVanilla"> '.T('To insert into custom Vanilla views: <br><code>echo AptAds::Embed(\'<i>n</i>\');</code>').'</p>';
    
            ?>
        </div>
    </div>

</div>
<div class="AptAdClear"></div>

