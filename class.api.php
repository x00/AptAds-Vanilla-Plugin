<?php if (!defined('APPLICATION')) exit();
abstract class AptAdsAPIDomain extends AptAdsUtilityDomain {
  
  private $WorkerName = 'API';
  
  public function CalledFrom(){
    return $this->WorkerName;
  }
  
  public function API(){
    $WorkerName = $this->WorkerName;
    if(!GetValue($WorkerName, $this->Workers)){
      $WorkerClass = $this->GetPluginIndex().$WorkerName;
      $this->LinkWorker($WorkerName,$WorkerClass);
    }
    return $this->Workers[$WorkerName];
  }
}

class AptAdsAPI {
    
    public $DefaultPages = array(
        'discussions(/.*)?$'=>'Discussions',
        'discussion(/.*)?$'=>'Discussion / Comments',
        'categories(/.*)?$'=>'Categories',
        'messages(/.*)?$'=>'Messages',
        'profile(/.*)?$'=>'Profile',
        '(?!)' =>'External Only',
        '.*'=>'All',
        ''=>'Custom'
    );
    public $DefaultLocations = array(
        'Before_Content' => 'Before Content',
        'Before_Panel' => 'Before Panel',
        'Panel' => 'Panel',
        'After_Content' => 'After Content',
        'Embed' => 'Embed'
    );
    
    public $DefaultTypes = array(
        'Image'=>'Image',
        'Text'=>'Text'
    );
    
    public $DefaultRotations = array(
        'Even'=>'Even',
        'Random'=>'Random',
        'None'=>'None'
    );
    
    public $DefaultApplications = array(
        'Vanilla',
        'Conversations'
    );
    
    const BEFORE='Before';
    const AFTER='After';
    
    public $Pages=array();
    public $AdPlacements=array();
    public $AdIDs=array();
    public $AptAd=null;
    public $EmbedData = array();
    public $AvailableTemplates = array();
    public $TemplateHelp = array();
    public $EmbedResources = array();
    
    public function PreLoad(){
        $this->AddTemplate('Default', '');
        $this->Plgn->FireEvent('Init');
    }
    
    public static function Embed($EmbedCode){
        $AptAds = Gdn::PluginManager()->GetPluginInstance('AptAds');
        $AdPlacements = GetValue('Embed',$AptAds->API()->AdPlacements);
        
        if(empty($AdPlacements))
            return;
        
        $AptAd = new AptAdModel();
        $EmbedSpaces = $AptAd->EmbedSpaces($AdPlacements);
        $AdPlacementsByLabel = $AptAd->AdPlacementsByLabel($AdPlacements);
        
        $Data = array();
        if($AdPlacements){
            $AptAds->API()->EmbedData['AdPlacements'] = $AptAds->API()->InsertAdsEmbed($AdPlacements);
            
        }
        
        if($AdPlacementsByLabel){
            $AptAds->API()->EmbedData['AdPlacementsByLabel'] = $AptAds->API()->InsertAdsEmbed($AdPlacementsByLabel, TRUE);
            
        }
        
        if($EmbedSpaces){
            $AptAds->API()->EmbedData['EmbedSpaces'] = $AptAds->API()->InsertAdsEmbed($EmbedSpaces, TRUE);
        }
        
        
        if(ctype_digit($EmbedCode)){
            echo GetValueR($EmbedCode.'.AdCode',$AptAds->API()->EmbedData['AdPlacements']);
        }else if(substr($EmbedCode, 0, 1)=='#'){
            echo GetValueR($EmbedCode.'.AdCode',$AptAds->API()->EmbedData['AdPlacementsByLabel']);
        }else{
            echo GetValueR($EmbedCode.'.AdCode',$AptAds->API()->EmbedData['EmbedSpaces']);
        }
    }
    
    public function InsertAdsEmbed($EmbedPlacements, $EmbedSpace = FALSE){
        $Embed =  array();
        if(!$EmbedSpace){
            foreach($EmbedPlacements As $AdPlacement){
                $AdPlacement->AssetOrder = 1;
                ob_start();
                $this->InsertAd($AdPlacement);
                $Ads=ob_get_contents();
                ob_end_clean();
                $Embed[$AdPlacement->AdPlacementID]=array(
                    'AdPlacementID'=> $AdPlacement->AdPlacementID,
                    'AdCode'=> $Ads
                );
            }
        } else {
            foreach($EmbedPlacements As $Space => $AdPlacements){
                foreach($AdPlacements As $AdPlacement){
                    if(!$AdPlacement)
                        continue;
                    ob_start();
                    $AdPlacement->AssetOrder = 1;
                    $this->InsertAd($AdPlacement);
                    $Ads=ob_get_contents();
                    ob_end_clean();
                    if(!GetValue($Space, $Embed))
                        $Embed[$Space] = array('AdCode'=>'');
                    $Embed[$Space]['AdCode'] .= $Ads;
                }

            }
        }
        return $Embed;
    }
    
    public function InsertAdsDemo($AdPlacements, $EmbedSpace = FALSE){
        if(!$AdPlacements)
            return FALSE;
        if($EmbedSpace)
            $AdPlacements = array( 'Block' => $AdPlacements);
        foreach($AdPlacements As $Location => $Placements){
            foreach($Placements As $AdPlacement){
                if(!$AdPlacement->Fit && $AdPlacement->Width>$AdPlacement->Height){
                    $AdPlacement->Fit = TRUE;
                    $AdPlacement->Height = 0;
                }else if($AdPlacement->Width > 400 || $Location == 'Panel' && $AdPlacement->Type=='Image' || ($AdPlacement->Width < 200 && $AdPlacement->Width > 0)){
                    $AdPlacement->Width = 200;
                    $AdPlacement->Height = 0;
                    $AdPlacement->Fit = FALSE;
                    
                }
                $AdPlacement->AssetOrder=1;
                echo '<div class="AptAdDemo'.($EmbedSpace ? 'Embed' : '').'">';
                $this->InsertAd($AdPlacement, FALSE, TRUE, $EmbedSpace);
                echo '</div>';
                if(!$EmbedSpace)
                    echo '<div class="AptAdDemoSpacer"></div>';
            }
        }
    }
    
    public function InsertAds($Sender, $AssetName, $InsertPos = NULL){
        $Application = GetValue('Application', $Sender,'Vanilla') ;
        if(!in_array($Application,$this->DefaultApplications)) return;
        $AptAd = $this->AptAd;
        if(empty($this->AdPlacements))
            return;
        $Locations = array_keys($this->DefaultLocations);
        $Pos = $Place = $Location = '';
        if($InsertPos){
            foreach($Locations As $Location){
                if(strpos($Location,$InsertPos.'_'.$AssetName)!==FALSE){
                    list($Pos,$Place) = split('_',$Location);
                    break;
                }
            }
            
        }else if(!$Place && in_array($AssetName,$Locations)){
            $Place = $AssetName;
            $Location = $Place;
            $Pos=null;
        }
        
        if(GetValue($Location,$this->AdPlacements)){
            foreach($this->AdPlacements[$Location] As $AdPlacement){
                if($AdPlacement->AssetOrder<0) continue;
                
                switch($InsertPos){
                    case self::BEFORE:
                        if($Pos==self::BEFORE){
                            $this->InsertAd($AdPlacement);
                        }
                        break;
                    case self::AFTER:
                        
                        if($Pos==self::AFTER){
                            $this->InsertAd($AdPlacement);
                        }
                        break;
                    default:
                        if(!$Pos){
                            $this->InsertAd($AdPlacement,true);
                        }
                        break;
                }
                
            }
        }
    }
    
    public function InsertAd($AdPlacement, $AssetOrder = FALSE, $Demo = FALSE, $EmbedSpace = FALSE){
        if($AssetOrder && $AdPlacement->AssetOrder>0){
            $AdPlacement->AssetOrder--;
            return;
        }
        if($AdPlacement->AssetOrder<0) return;
        $AdPlacement->AssetOrder=-1;
        $Number=$AdPlacement->Rows*$AdPlacement->Cols;
        
        if(!$Number)
            $Number=1;
        if($AdPlacement->Rotation=='Even'){
            $this->EvenRotateIDs($AdPlacement, $Number);
        }else if($AdPlacement->Rotation=='Random'){
            $this->RandomRotateIDs($AdPlacement, $Number);
        }
        
        include($this->GetTemplate($AdPlacement->Template));

    }
    
    public function AddTemplate($TemplateName, $TemplateValue, $TemplateHelp=NULL){
        $this->AvailableTemplates[$TemplateName] = $TemplateValue;
        $this->TemplateHelp[$TemplateName] = $TemplateHelp;
    }
    
    public function GetTemplate($TemplateName){
        $Template = GetValue($TemplateName,$this->AvailableTemplates);
        return $Template ? $Template : $this->Plgn->Utility()->ThemeView('ad');
    }
    
    public function TemplateSelect(){
        $Templates = array();
        foreach ($this->AvailableTemplates As $Template => $View){
            if(!$View)
                $Templates[$Template] = '';
            else
                $Templates[$Template] = $Template;
        }
        return $Templates;
    }
    
    public function AddResource($TemplateName, $Callback){
        $this->EmbedResources[$TemplateName] = $Callback;
    }
    
    public function LoadResources($Sender, $Settings = FALSE){
        if(!$Settings && empty($this->AdPlacements))
            return;
        $Sender->AddCssFile('aptads.css','plugins/AptAds');
        if($Settings){
            foreach($this->EmbedResources As $Callback){
                if($Callback)
                    call_user_func($Callback, $Sender);
            }
        }else{
            foreach ($this->AdPlacements As $LocationPlacements){
                foreach($LocationPlacements As $Placement){
                    if($Placement->Template){
                        $Callback = GetValue($Placement->Template, $this->EmbedResources);
                        if($Callback)
                            call_user_func($Callback, $Sender);
                    }
                }
            }
        }
        
    }
    
    public function EvenRotateIDs($AdPlacement, $Number){
        $CountByIDs=array();
        foreach($AdPlacement->Ads As $Ad){
            if(!GetValue($Ad->AdID,$CountByIDs)){
                $CountIDs[$Ad->AdID]=$Ad->ShowCount;
            }
        }
        
        asort($CountIDs);//sort low to high
        
        
        $CountIDs = array_slice($CountIDs,0,$Number, true);
        $CountIDsGroup = array();
        
        foreach($CountIDs As $ID=>$Count){
            if(!GetValue($Count,$CountIDsGroup))
                $CountIDsGroup[$Count]=array();
            $CountIDsGroup[$Count][$ID]=$Count;
        }
        
        
        $IDs=array();
        foreach($CountIDsGroup As $CountIDGroup){
            $CountIDGroup = $this->ShuffleAssoc($CountIDGroup);//randomise where same
            foreach($CountIDGroup As $ID=>$Count)
                $IDs[]=$ID;
        }
        
        $Ads=array();        
        foreach($IDs As $ID){
            $Ads[$ID]=$AdPlacement->Ads[$ID];
        }
        
        $this->AdIDs = array_unique(array_merge($this->AdIDs, $IDs));
        
        
        $AdPlacement->Ads=$Ads;
        
    }
    
    public function RandomRotateIDs($AdPlacement, $Number){
        $CountByIDs=array();
        foreach($AdPlacement->Ads As $Ad){
            if(!GetValue($Ad->AdID,$CountByIDs)){
                $CountIDs[$Ad->AdID]=$Ad->ShowCount;
            }
        }
        
        $CountIDs=$this->ShuffleAssoc($CountIDs);//randomise
        
        $CountIDs = array_slice($CountIDs,0,$Number,true);
        
        $IDs = array_keys($CountIDs);
        
        $Ads=array();        
        foreach($IDs As $ID){
            $Ads[$ID]=$AdPlacement->Ads[$ID];
        }
        
        $this->AdIDs = array_unique(array_merge($this->AdIDs,$IDs));
        
        $AdPlacement->Ads=$Ads;
        
    }
    
    private function ShuffleAssoc($Array){
        $Shuffle = array();
        
        $ShuffleKeys = array_keys($Array);
        shuffle($ShuffleKeys);
        foreach ($ShuffleKeys As $ShuffleKey){
            $Shuffle[$ShuffleKey] = $Array[$ShuffleKey];

        } 
        return $Shuffle;
    }
    
    protected function MailReminder($AdIDs){
        $AdUrl=Url('/settings/aptads/ad',true);
        $AdUrls=array();
        foreach($AdIDs As $AdID =>$PlacementID){
            $AdUrls[]=$AdUrl.'/'.$PlacementID.'/'.$AdID.'?LastID='.$AdID;
        }
        $ExpiredAds = implode("\n",$AdUrls);
        ob_start();
        include($this->Plgn->Utility()->ThemeView('email'));
        $EmailBody=ob_get_contents();
        ob_end_clean();
        $Subject=T('AptAd Ad Expire Reminder');
        
        $this->AptAd->NotifySet(array_keys($AdIDs),1,1);
        $Email = new Gdn_Email();
        $Email->To(C('Garden.Email.SupportAddress'))
            ->Subject(sprintf(T('[%1$s] %2$s'), Gdn::Config('Garden.Title'), $Subject))
            ->Message($EmailBody)
            ->Send();
    }
    
    protected function InformReminder($Sender,$AdIDs){
        foreach($AdIDs As $AdID =>$PlacementID){
            $Sender->InformMessage(sprintf(T("The Ad %s has expired."),Anchor('/settings/aptads/ad/'.$PlacementID.'/'.$AdID,'/settings/aptads/ad/'.$PlacementID.'/'.$AdID.'?LastID='.$AdID)));    
        }
    }
  
}
