<?php if (!defined('APPLICATION')) exit(); ?>
<h1><?php echo $this->Data('Title'); ?></h1>
<style><?php include_once(dirname(dirname(__FILE__)).DS.'design'.DS.'aptadsadmin.css'); ?></style>
<div class="Info">
   <?php echo $this->Data('Description'); ?>
    <div class="AptAdTabs"><?php
    $AdPlacement=$this->Data('AdPlacement');
    if(GetValue('Ad',$this->Data) && !$AdPlacement){
        $Ad=GetValue('Ad',$this->Data);
        $AdPlacement = $Ad;
        $AdPlacement->Ads=array($Ad);
        echo Anchor(T('View all Ads in Placement'),'/settings/aptads/ads/'.intval($Ad->AdPlacementID),array('class'=>'SmallButton'));
        echo Anchor(T('View Ad Placement'),'/settings/aptads/'.intval($Ad->AdPlacementID),array('class'=>'SmallButton'));
    }else{
        echo Anchor(T('View Ad Placement'),'/settings/aptads/'.intval($AdPlacement->AdPlacementID),array('class'=>'SmallButton'));
    }
    
    
    ?></div>
</div>
<?php
if(!$AdPlacement){
    echo T('Ad placement not found');
}else{
?>
<div class="Listings AdListings">
<table>
    <tr>
        <th><?php echo T('Url'); ?></th>
        <th class="AdListingsAppearanceCol"><?php echo T('Appearance'); ?></th>
        <th><?php echo T('Enabled'); ?></th>
        <th><?php echo T('Edit'); ?></th>
        <th><?php echo T('Delete'); ?></th>
        <th><?php echo T('Order'); ?></th>
    </tr>
    <?php
    foreach($AdPlacement->Ads As $Ad){
    ?>
    <tr>
        <td class="Url"><?php echo $Ad->Url; ?></td>
        <td>
        <dl>
            <?php if($AdPlacement->Type=='Image'){ 
                if(!$Ad->SavedImg){
                    echo Wrap(T('Image'),'dt') . Wrap(Img($Ad->ImgSrc, array('width'=>'100%','style'=>'width:100%')),'dd');
                }else{ 
                    echo Wrap(T('Image'),'dt') . Wrap(Img('uploads/aptads/'.$Ad->SavedImg, array('width'=>'100%','style'=>'width:100%')),'dd');
                }
            }?>
            <?php echo Wrap(T('Title'),'dt') . Wrap(htmlspecialchars($Ad->Title),'dd'); ?>
            <?php echo Wrap(T('Description'),'dt') . Wrap(htmlspecialchars($Ad->Description),'dd'); ?>
        </dl>
        </td>
        <td><?php echo $Ad->Enabled?T('yes'):T('no'); ?></td>
        <td><?php echo Anchor(T('Edit'),'#AddEdit',array('class'=>'EditAd Button SmallButton','id'=>'Edit_'.Gdn_Format::Url($Ad->AdID))); ?></td>
        <td><?php echo Anchor(T('Delete'),'/settings/aptads/ad/'.intval($AdPlacement->AdPlacementID).'/'.intval($Ad->AdID).'/delete',array('class'=>'DeleteAd Button SmallButton')); ?></td>
        <td><?php 
            echo Anchor(T('&uarr;'),'/settings/aptads/ad/'.intval($AdPlacement->AdPlacementID).'/'.intval($Ad->AdID).'/up/?r='.rawurldecode(Url('',true)),array('class'=>'UpPlacement Button SmallButton'));
            echo Anchor(T('&darr;'),'/settings/aptads/ad/'.intval($AdPlacement->AdPlacementID).'/'.intval($Ad->AdID).'/down/?r='.rawurldecode(Url('',true)),array('class'=>'DownPlacement Button SmallButton'));
        ?></td>
    </tr>
    <?php
    }
    ?>
</table>
</div>
<?php
    //if(GetValue('Ad',$this->Data)) return;
    echo $this->Form->Open(array('id'=>'AddEdit', 'enctype' => 'multipart/form-data'));
    echo $this->Form->Errors();
    if(!GetValue('AdID',$this->Data)){
        $this->Form->AddHidden('AdID',0);
        echo $this->Form->Hidden('AdID',array('value'=>0));
    }else{
        $this->Form->AddHidden('AdID',$this->Data('AdID'));
        echo $this->Form->Hidden('AdID',array('value'=>$this->Data('AdID')));
    }
    $this->Form->AddHidden('AdPlacementID',$AdPlacement->AdPlacementID);
    echo $this->Form->Hidden('AdPlacementID',array('value'=>$AdPlacement->AdPlacementID));
    $this->Form->AddHidden('Template',$AdPlacement->Template);
    echo $this->Form->Hidden('Template',array('value'=>$AdPlacement->Template));
?>
<div class="Configuration">
   <div class="ConfigurationForm">
    <ul>
        <?php $this->FireEvent('AdConfigBefore'); ?>
        <li>
            <h2 class="AddEdit"><?php echo Getvalue('AdID',$this->Data)?T('Edit Ad'):T('Add Ad'); ?></h2>
            <?php echo $this->Form->Label('Target Url'); ?>
        </li>
        <li>
            <?php
            echo $this->Form->TextBox('Url');
            echo '<p class="AptAdInfo"> '.T('The link the advertiser wants the user to land on (e.g http://site.com/promo).').'</p>';
            ?>
        </li>
        <?php if($AdPlacement->Type=='Image'){ ?>
        <li>
            <?php echo $this->Form->Label('Image'); ?>
        </li>
        <li>
            <?php
            $this->Form->AddHidden('MAX_FILE_SIZE',150000);
            echo $this->Form->Hidden('MAX_FILE_SIZE',array('value'=>150000));
            echo $this->Form->Input('ImgFile','File');
            echo '<p class="AptAdInfo"> '.T('Upload an Image from your computer').'</p>';
            echo $this->Form->TextBox('ImgSrc');
            echo '<p class="AptAdInfo"> '.T('Or address to the hosted Ad image (e.g http://site.com/image/banner.jpg). ').'</p>';
            ?>
        </li>
        <?php } ?>
        <li>
            <?php echo $this->Form->Label('Title'); ?>
        </li>
        <li>
            <?php
            echo $this->Form->TextBox('Title');
            if($AdPlacement->Type=='Image'){
                echo '<p class="AptAdInfo"> '.T('Descriptive name, also used as alternative text if the image doesn\'t load.').'</p>';
            }else{
                echo '<p class="AptAdInfo"> '.T('Title is the link on text Ads').'</p>';
            }
            ?>
        </li>
        <li>
            <?php echo $this->Form->Label('Description'); ?>
        </li>
        <li>
            <?php
            echo $this->Form->TextBox('Description', array('MultiLine' => TRUE));
            if($AdPlacement->Type=='Image'){
                echo '<p class="AptAdInfo"> '.T('This is may not always used with Image Ads, but is useful for personal info.').'</p>';
            }else{
                echo '<p class="AptAdInfo"> '.T('Description is the short statement under the Ad link').'</p>';
            }
            ?>
        </li>
        <li>
            <?php echo $this->Form->Label('Alternative Link Text'); ?>
        </li>
        <li>
            <?php
            echo $this->Form->TextBox('AltLinkText');
            echo '<p class="AptAdInfo"> '.T('Some ad template use an alternative bit of text for the main link, otherwise ignored.').'</p>';
            ?>
        </li>
        <li>
            <?php echo $this->Form->Label('Expire Reminder'); ?>
        </li>
        <li>
            <?php
            $Months = array_map('T', explode(',', 'Month,Jan,Feb,Mar,Apr,May,Jun,Jul,Aug,Sep,Oct,Nov,Dec'));
            $Days = range(1,31);
            $Years = range(date('Y'),date('Y',strtotime('+10 years')));
            
            $Days = array_merge(array(T('Day')),$Days);
            $Years = array(0=>T('Year'))+array_combine($Years,$Years);
            
            echo $this->Form->Dropdown('ExpireReminder_Month',$Months,array('class'=>'Month'));
            echo $this->Form->Dropdown('ExpireReminder_Day',$Days,array('class'=>'Day'));
            echo $this->Form->Dropdown('ExpireReminder_Year',$Years,array('class'=>'Year'));
            echo '<input type="hidden" name="DateFields[]" value="ExpireReminder" />';
            echo '<p class="AptAdInfo"> '.T('If set will send an email reminder on this date.').'</p>';
            ?>
        </li>
        <?php $this->FireEvent('AdConfigAfter'); ?>
        <li>
            <?php 
            echo $this->Form->Dropdown('Enabled',array('0'=>'Disabled','1'=>'Enabled'));
            echo '<p class="AptAdInfo"> '.T('Once Ads are Enabled they will display, so long as the Ad placement is enabled.').'</p>';
            ?>
        </li>
        <li>
            &nbsp;
        </li>
        <li>
            <?php echo $this->Form->Button('Save',array('class'=>'SmallButton')); ?>
            <?php echo Anchor(T('New'),'#AddEdit',array('style'=>'display:none;','class'=>'AddEditButton SmallButton')); ?>
        </li>
    </ul>
   </div>
</div>
 <?php
      echo $this->Form->Close();
?>
<?php
}
?>
<div class="AptAdsTemplateHelp">$AdPlacement</div>
<div class="AptAdClear"></div>
<?php
