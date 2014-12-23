<?php if (!defined('APPLICATION')) exit(); ?>
<h1><?php echo $this->Data('Title'); ?></h1>
<style><?php include_once(dirname(dirname(__FILE__)).DS.'design'.DS.'aptadsadmin.css'); ?></style>
<div class="Info">
    <?php echo $this->Data('Description'); ?>
    <div class="AptAdTabs"><?php
    if(GetValue('AdPlacement',$this->Data)){
        $AdPlacements = array();
        $AdPlacements[] = GetValue('AdPlacement',$this->Data);
        echo Anchor(T('View All Ad Placements'),Url('/settings/aptads',true),array('class'=>'SmallButton'));
    }else{
        $AdPlacements=$this->Data('AdPlacements');
    }
    echo Anchor(T('Go to Embed Spaces'),Url('/settings/aptads/embed',true),array('class'=>'SmallButton'));
    ?></div>
</div>
<div class="Listings PlacementListings">
<table>
    <tr>
        <th><?php echo T('Ads'); ?></th>
        <th><?php echo T('Label'); ?></th>
        <th><?php echo T('Page(s)'); ?></th>
        <th><?php echo T('Location'); ?></th>
        <th><?php echo T('Appearance'); ?></th>
        <th><?php echo T('Enabled'); ?></th>
        <th><?php echo T('Edit'); ?></th>
        <th><?php echo T('Delete'); ?></th>
        <?php if(!GetValue('AdPlacement',$this->Data)){ ?>
            <th><?php echo T('Order'); ?></th>
        <?php } ?>
    </tr>
    <?php
    foreach($AdPlacements As $AdPlacement){
    ?>
    <tr>
        <td><?php echo Anchor(T('View Ads'),'/settings/aptads/ads/'.intval($AdPlacement->AdPlacementID),array('class'=>'SmallButton')); ?></td>
        <td><?php echo htmlspecialchars($AdPlacement->Label); ?></td>
        <td><?php echo '^'.htmlspecialchars($AdPlacement->Page); ?></td>
        <td><?php 
        if($AdPlacement->Page=='embed'){
            echo Anchor(T('Embed Code'),'#AddEditPlacements',array('class'=>'EmbedPlacement Button SmallButton'));
        }else{
            echo str_replace('_',' ',$AdPlacement->Location);
        }
        ?></td>
        <td>
            <dl>
            <?php echo Wrap(T('Type'),'dt') . Wrap($AdPlacement->Type.'&nbsp;','dd'); ?>
            <?php echo Wrap(T('Template'),'dt') . Wrap($AdPlacement->Template.'&nbsp;','dd'); ?>
            <?php echo Wrap(T('Message'),'dt') . Wrap(htmlspecialchars($AdPlacement->Message).'&nbsp;','dd'); ?>
            <?php echo Wrap(T('Rows'),'dt') .  Wrap($AdPlacement->Rows.'&nbsp;','dd'); ?>
            <?php echo Wrap(T('Cols'),'dt') .  Wrap($AdPlacement->Cols.'&nbsp;','dd'); ?>
            <?php echo Wrap(T('Width'),'dt') .  Wrap(($AdPlacement->Fit?'100%':$AdPlacement->Width.'px').'&nbsp;','dd'); ?>
            <?php echo Wrap(T('Height'),'dt') .  Wrap(($AdPlacement->Fit?'auto':($AdPlacement->Height?$AdPlacement->Height.'px':'auto')).'&nbsp;','dd'); ?>
            <?php echo Wrap(T('Rotation'),'dt') .   Wrap($AdPlacement->Rotation.'&nbsp;','dd'); ?>
            </dl>
        </td>
        <td><?php echo $AdPlacement->Enabled?T('yes'):T('no'); ?></td>
        <td><?php echo Anchor(T('Edit'),'#AddEditPlacements',array('class'=>'EditPlacement Button SmallButton','id'=>'Edit_'.Gdn_Format::Url($AdPlacement->AdPlacementID))); ?></td>
        <td><?php echo Anchor(T('Delete'),'/settings/aptads/'.intval($AdPlacement->AdPlacementID).'/delete',array('class'=>'DeletePlacement Button SmallButton')); ?></td>
        <?php if(!GetValue('AdPlacement',$this->Data)){ ?>
            <td><?php 
                echo Anchor(T('&uarr;'),Url('/settings/aptads').'/'.intval($AdPlacement->AdPlacementID).'/up/?r='.rawurldecode(Url('',true)),array('class'=>'UpPlacement Button SmallButton'));
                echo Anchor(T('&darr;'),Url('/settings/aptads').'/'.intval($AdPlacement->AdPlacementID).'/down/?r='.rawurldecode(Url('',true)),array('class'=>'DownPlacement Button SmallButton'));
            ?></td>
        <?php } ?>
    </tr>
    <?php
    }
    ?>
</table>
<?php 
if (GetValue('Pager',$this))
    echo $this->Pager->Render(); 
?>
</div>
<?php
    echo $this->Form->Open(array('id'=>'AddEditPlacements'));
    echo $this->Form->Errors();
    if(!GetValue('AdPlacementID',$this->Data)){
        $this->Form->AddHidden('AdPlacementID',0);
        echo $this->Form->Hidden('AdPlacementID',array('value'=>0));
    }else{
        $this->Form->AddHidden('AdPlacementID',$this->Data('AdPlacementID'));
        echo $this->Form->Hidden('AdPlacementID',array('value'=>$this->Data('AdPlacementID')));
    }
?>
<div class="Configuration">
   <div class="ConfigurationForm">
    <ul>
        <?php $this->FireEvent('AdPlacementConfigBefore'); ?>
        <li>
            <?php echo $this->Form->Label('Label'); ?>
        </li>
        <li>
            <?php
            echo $this->Form->TextBox('Label', array('maxlength'=>'10'));
            echo '<p class="AptAdInfo"> '.T('A short label or alias (10 characters max) to help you identify that Ad Placement Later.').'</p>';
            ?>
        </li>
        <li>
            <?php echo $this->Form->Label('Page(s)'); ?>
        </li>
        <li>
            <?php
            echo $this->Form->Dropdown('Page', $this->Data('DefaultPages'));
            echo '<span> '.T('Match URI ^').'</span>';
            echo $this->Form->Textbox('Page', array('disabled'=>'disabled'));
            echo '<p class="AptAdInfo"> '.T('Select the type of pages you want this Ad Placement to appear on.').'</p>';
            echo '<p class="AptAdInfo"> '.T('Match URI is for those that understand PCRE, and want to use the Custom option to match advanced URI (otherwise ignore).').'</p>';
            ?>
        </li>
        <li>
            <?php echo $this->Form->Label('Location'); ?>
        </li>
        <li>
            <?php
            echo $this->Form->Dropdown('Location', $this->Data('DefaultLocations'));
            echo '<p class="AptAdInfo"> '.T('This is where on the page you want the Ad Placement to appear.').'</p>';
            $this->Form->AddHidden('AssetOrder',0);
            echo $this->Form->Hidden('AssetOrder',array('value'=>0));
            ?>
        </li>
        <!--<li>
            <?php 
            echo $this->Form->Label('Asset Order'); 
            ?>
            
        </li>
        <li>
            <?php
            echo $this->Form->TextBox('AssetOrder',array('value'=>0,'class'=>'InputBox SmallInput'));
            echo '<p class="AptAdInfo"> '.T('This is relevant to Ads that are appearing *in* rather then Before or After a location.').'</p>';
            echo '<p class="AptAdInfo"> '.T('The Asset Order is the position relative to other \'Assets\', where it is going to render. In the Panel 0 is at the top, 1 is below the first item, etc.').'</p>';
            echo '<p class="AptAdInfo"> '.T('The Ad Placement can only order against registered Assets, so can\'t guarantee will order well with other Adons that use the Panel.').'</p>';
            echo '<p class="AptAdInfo"> '.T('Where there are two Ad Placements with the same location and Asset Order they will be rendered by the \'Show Order\', which can be set by clicking on the arrows in the order column of the Ad Placement listing.').'</p>';
            ?>
        </li>-->
        <li>
            <?php echo $this->Form->Label('Message'); ?>
        </li>
        <li>
            <?php
            echo $this->Form->TextBox('Message', array('maxlength'=>'250', 'value' => T('Advertisement')));
            echo '<p class="AptAdInfo"> '.T('Message which will accompany the Ad, leave blank for no message. e.g. Advertisement.').'</p>';
            ?>
        </li>
        <li>
            <?php echo $this->Form->Label('Type'); ?>
        </li>
        <li>
            <?php
            echo $this->Form->Dropdown('Type',$this->Data('DefaultTypes'));
            echo '<p class="AptAdInfo"> '.T('Type determines what sort of Ads will appear. Image can only serve image Ads, Text only text ads.').'</p>';
            ?>
        </li>
        <li>
            <?php echo $this->Form->Label('Template'); ?>
        </li>
        <li>
            <?php
            echo $this->Form->Dropdown('Template',array_flip($this->Data('AvailableTemplates')));
            echo '<p class="AptAdInfo"> '.T('You can choose a specific template if available. This may also load additional functionality.').'</p>';
            ?>
        </li>
        <li>
            <?php echo $this->Form->Label('Ad Placement Dimensions'); ?>
        </li>
        <li>
            <?php
            echo '<span> '.T('Rows').' </span>';
            echo $this->Form->TextBox('Rows',array('value'=>1,'class'=>'InputBox SmallInput'));
            echo '<span> '.T('Cols').' </span>';
            echo $this->Form->TextBox('Cols',array('value'=>1,'class'=>'InputBox SmallInput'));
            echo '<p class="AptAdInfo"> '.T('If you set Rows and Columns you can display multiple Ads at the same time. Or use 1x1 for a single.').'</p>';
            ?>
        </li>
        <li>
            <?php echo $this->Form->Label('Ad Cell Dimensions (px)'); ?>
        </li>
        <li>
            <?php
            echo '<span> '.T('Width').' </span>';
            echo $this->Form->TextBox('Width',array('class'=>'InputBox SmallInput', 'disabled'=>'disabled'));
            echo '<span> '.T('Height').' </span>';
            echo $this->Form->TextBox('Height',array('class'=>'InputBox SmallInput', 'disabled'=>'disabled'));
            echo '<span> '.T('Scale to full width').' </span>';
            echo $this->Form->CheckBox('Fit','',$this->Form->GetValue('Fit')===false? array('checked'=>'checked'):null);
            echo '<p class="AptAdInfo"> '.T('"Scale to full" will use up the full width of the container and scale the height proportionally.').'</p>';
            echo '<p class="AptAdInfo"> '.T('Otherwise you can set the width and height. If you put 0 for height it will scale proportionally to the set width too.').'</p>';
            ?>
        </li>
        <li>
            <?php echo $this->Form->Label('Rotation'); ?>
        </li>
        <li>
            <?php
            echo $this->Form->Dropdown('Rotation',$this->Data('DefaultRotations'));
            echo '<p class="AptAdInfo"> '.T('If you have lot of Ads under a Placement, you want to \'rotate\' to you show them all.').'</p>';
            echo '<p class="AptAdInfo"> '.T('Even keeps a tally of what Ads have been shows, and those that haven\'t will be prioritised. Random randomises the display of Ads.').'</p>';
            ?>
        </li>
        <li>
            <?php echo $this->Form->Label('Status'); ?>
        </li>
        <?php $this->FireEvent('AdPlacementConfigAfter'); ?>
        <li>
            <?php 
            echo $this->Form->Dropdown('Enabled',array('0'=>T('Disabled'),'1'=>T('Enabled')));
            echo '<p class="AptAdInfo"> '.T('Once Enabled this Ad Placement will appear, but only if the placement has Ads to display.').'</p>';
            ?>
        </li>
        <li>
            <?php
            echo $this->Form->Label('Embed Code');
            echo '<p class="AptAdInfo">'.T('If you wish to place Ad Placements externally or outside of their normal context you can use embed codes.').'</p>';
            echo '<p class="AptAdInfo">'.sprintf(T('Follow <a href="%s" target="_blank" >these instructions</a> on how to set up embed spaces and insert embed codes.'),Url('/settings/aptads/embed')).'</p>';
            ?>
            <?php
            if(!GetValue('AdPlacement',$this->Data)){
                        
                echo '<p class="AptAdInfo" id="EmbedCodeDescription"> '.T('Save this placement if you which to embed it. If you are embedding only a single placement save to get the AdPlacementID or Embed Label (generally it is preferable to use embed spaces).').'</p>';
            }
            echo '<p class="AptAdInfo" id="EmbedCodeID" '.(!$this->Data('AdPlacementID') ? 'style="display:none;"':'').'> '.sprintf(T('AdPlacementID: <code id="EmbedCode">%s</code>, Embed Label:  <code id="EmbedLabel">%s</code> (better to stick to embed spaces).'),$this->Data('AdPlacementID'),'#'.$this->Data('Label')).'</p>';
            
            ?>
        </li>
        <li>
            &nbsp;
        </li>
        <li>
            <?php echo $this->Form->Button('Save',array('class'=>'SmallButton')); ?>
            <?php 
                if(!GetValue('AdPlacement',$this->Data)){
                    echo Anchor(T('New'),'#AddEditPlacements',array('style'=>'display:none;','class'=>'AddEditButton SmallButton')); 
                }
            ?>
        </li>

    </ul>
   </div>
</div>
 <?php
    echo $this->Form->Close();
?>
<div class="AptAdsTemplateHelp"></div>
<div class="AptAdClear"></div>
<?php
