<?php if (!defined('APPLICATION')) exit();
    $Rows = $AdPlacement->Rows?$AdPlacement->Rows:1;
    $Cols = $AdPlacement->Cols?$AdPlacement->Cols:1;
    $Ads = $AdPlacement->Ads;
    $Num = count($AdPlacement->Ads);
    $NumCount=$Num;
    $ColWidth = $Cols>1?100/$Cols:100;
    $AdLast=null;
    $AnchorFollow = C('Plugins.AptAds.AnchorFollow');
?>
<div class="AptAd AptAd<?php echo str_replace('_','',$AdPlacement->Location);?>">
<?php
    if($Demo){
        echo '<input type="hidden" class="AdPlacementIDs" name="AdPlacementIDs[]" value="'.$AdPlacement->AdPlacementID.'" />';
        echo '<input type="hidden" class="StoredAds" name="StoredAds[]" value="'.Gdn_Format::Text(json_encode($AdPlacement->StoredAds)).'" />';
            
    }
    if($AdPlacement->Type=='Text' && $AdPlacement->Message){
        ?>
        <div class="AptAdMessageBox"><span class="AptAdMessage AptAdMessageText"><?php echo $AdPlacement->MessagePlural ? Plural($AdPlacement->Message, $AdPlacement->MessagePlural, $Num) : $AdPlacement->Message; ?></span></div>
        <div class="AptAdSpacer"></div>
        <?php
    }
    while($Rows--){
        $ColsTemp = $Cols;
?>
        <div class="AptAdRow<?php echo!$Rows?' AptAdRowLast':'';?>">
        <?php
        while($ColsTemp--){
        $Ad = array_shift($Ads);
        if(!$Ad && $AdPlacement->Type=='Image'){
            $Ad=$AdLast;
        }
        ?>
            <div class="AptAdCol" style="width:<?php echo $ColWidth;?>%;">
            <div class="AptAdCell<?php echo !$ColsTemp?' AptAdCellLast':'';?>">
            <?php
            if($Ad){
                $Site = Gdn_Format::Text(parse_url($Ad->Url, PHP_URL_HOST));
                if($Demo){
                    $Ad->Url = '#';
                }
                
                if($AdPlacement->Fit){
                    $Attr=array('width'=>'100%', 'style'=>'width:100%');
                }else if($AdPlacement->Width && $AdPlacement->Height){
                    $Attr=array('width'=>$AdPlacement->Width,'height'=>$AdPlacement->Height,'style'=>'width:'.$AdPlacement->Width.'px; height:'.$AdPlacement->Height.'px;');
                }else if($AdPlacement->Width){
                    $Attr=array('width'=>$AdPlacement->Width, 'style'=>'width:'.$AdPlacement->Width.'px');
                }else{
                    $Attr=null;
                }
                if($AdPlacement->Type=='Image'){
                    $Attr['border']=0;
                    $Attr['alt']=Gdn_Format::Text($Ad->Title);
                    $Attr['title']=$Attr['alt'];
                    if($Ad->SavedImg)
                        echo Wrap(Anchor(Img(Url('uploads/aptads/'.$Ad->SavedImg,TRUE),$Attr),$Ad->Url,($AnchorFollow ? FALSE : array('rel'=>'nofollow'))),'div',array('class'=>'AptAdImg'));
                    else
                        echo Wrap(Anchor(Img($Ad->ImgSrc,$Attr),$Ad->Url,($AnchorFollow ? FALSE : array('rel'=>'nofollow'))),'div',array('class'=>'AptAdImg'));
                }else if($AdPlacement->Type=='Text'){
                    
                    $Attr['class']='AptAdText';
                    $Attrs= Attribute($Attr);
                    $Title = Gdn_Format::Text($Ad->Title);
                    $Description = Gdn_Format::Text($Ad->Description);
            ?>
                    <div <?php echo $Attrs?>>
                        <div class="AptAdTitle">
                            <?php echo Anchor($Title,$Ad->Url); ?>
                        </div>
                        <div  class="AptAdDescription">
                            <?php echo $Description; ?>
                        </div>
                        <div class="AptAdSite">
                            <?php echo $Site; ?>
                        </div>
                    </div>
            <?php
                    
                }
            }
            ?>
            </div>
            </div>
    <?php
        $AdLast = $Ad;
        }
    ?>
        </div>
<?php
    }    
?>
</div>
<?php
    if($AdPlacement->Type=='Image' && $AdPlacement->Message){
        ?>
        <div class="AptAdSpacer"></div>
        <div class="AptAdMessageBox"><span class="AptAdMessage AptAdMessageImg"><?php echo $AdPlacement->Message; ?></span></div>
        
        <?php
    }
    if($Demo && !$EmbedSpace){
        echo Wrap(
            Wrap(sprintf(T('<span>Label:</span> %s'), Gdn_Format::Text($AdPlacement->Label)), 'li').
            Wrap(sprintf(T('<span>Page:</span> %s'), Gdn_Format::Text($AdPlacement->Page)), 'li').
            Wrap(sprintf(T('<span>Location:</span> %s'), Gdn_Format::Text($AdPlacement->Location)), 'li').
            Wrap(sprintf(T('<span>Rotation:</span> %s'), Gdn_Format::Text($AdPlacement->Rotation)), 'li'),
            'ul',
            array('class' =>'AdDemoDetails Aside')
        );
    }
?>
<div class="AptAdEnd"></div>
