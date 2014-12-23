<?php if (!defined('APPLICATION')) exit();
abstract class AptAdsUIDomain extends AptAdsSettingsDomain {
  
  private $WorkerName = 'UI';
  
  public function CalledFrom(){
    return $this->WorkerName;
  }
  
  public function UI(){
    $WorkerName = $this->WorkerName;
    if(!GetValue($WorkerName, $this->Workers)){
      $WorkerClass = $this->GetPluginIndex().$WorkerName;
      $this->LinkWorker($WorkerName,$WorkerClass);
    }
    return $this->Workers[$WorkerName];
  }
    
}


class AptAdsUI {
    
    public function RenderAssetHandler($Sender, $Location = NULL){
        $AssetName = GetValueR('EventArguments.AssetName', $Sender);
        $this->Plgn->API()->InsertAds($Sender, $AssetName, $Location);
    }
    
    public function Load($Sender){
        $Application = GetValue('Application', $Sender,'Vanilla') ;
        $ControllerName = strtolower(GetValue('ControllerName',$Sender));
        if((!in_array($Application,$this->Plgn->API()->DefaultApplications) || $ControllerName=='settingscontroller') && !(in_array($ControllerName,array('plugincontroller')))) return;
        $this->DummyPanelModule($Sender);
        $AptAd = new AptAdModel();
        $this->Plgn->API()->AptAd = $AptAd;
        if(empty($this->Plgn->API()->Pages)){
            $CurrentPages = $AptAd->GetPages();
            foreach($CurrentPages As $Page){
                if(preg_match('`^'.$Page.'`i',$Sender->SelfUrl)){
                    $this->Plgn->API()->Pages[]=$Page;
                };
            }
            
            if(empty($this->Plgn->API()->Pages))
                return;

            $this->Plgn->API()->AdPlacements = $AptAd->GetAds($this->Plgn->API()->Pages);
            
           
            //expire check
            $ExpiredAds= $AptAd->GetExpired();
            $ExpireSend=array();
            $ExpireInform=array();
            
            if($ExpiredAds){
                foreach($ExpiredAds As $ExpiredAd){
                    if(!$ExpiredAd->NotifyEmail)
                        $ExpireSend[$ExpiredAd->AdID]=$ExpiredAd->AdPlacementID;
                    if($ExpiredAd->NotifyInform)
                        $ExpireInform[$ExpiredAd->AdID]=$ExpiredAd->AdPlacementID;
                        
                }
                
                
                if(!empty($ExpireSend)){
                    $this->Plgn->API()->MailReminder($ExpireSend);
                }
                
                if(!empty($ExpireInform)){
                    if(Gdn::Session()->User->Admin)
                        $this->Plgn->API()->InformReminder($Sender,$ExpireInform);
                }
            }
            //end expire check
        }
        $this->Plgn->API()->LoadResources($Sender);
    }
    
    public function RemoteLoad($Sender,$Args){
        $EmbedIDs = GetValue('EmbedIDs',$_REQUEST);
        if(strtolower(GetValue(0,$Args))=='embed' && count($EmbedIDs)){
            if(!is_array($EmbedIDs)){
                $EmbedIDs=array($EmbedIDs);
            }
            $AdPlacementIDs = array();
            $EmbedSpaces = array();
            foreach($EmbedIDs As $EmbedID){
                if(is_numeric($EmbedID))
                    $AdPlacementIDs[] = $EmbedID;
                else if(preg_match('`[a-z]+`i',$EmbedID))
                    $EmbedSpaces[] = $EmbedID;
            }

            $Callback = GetIncomingValue('callback');
            if($Callback){
                header('Content-type: text/javascript');
            }else{
                header('Content-type: application/json');
            }
            header('Cache-Control: no-cache, must-revalidate');
            header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
            $AptAd = new AptAdModel();

            
            foreach($EmbedSpaces As $Space){
                $IDs = C('Plugins.AptAds.EmbedSpace.'.$Space);
                foreach($IDs As $ID)
                    $AdPlacementIDs[] = $ID;
            }
            
            $AdPlacements = $AptAd->GetAdsByPlacementIDs($AdPlacementIDs);
            $EmbedSpaces = $AptAd->EmbedSpaces($AdPlacements);
            if($AdPlacements){
                $Data['AdPlacements'] = $this->Plgn->API()->InsertAdsEmbed($AdPlacements);
                
            }
            if($EmbedSpaces){
                $Data['EmbedSpaces'] =  $this->Plgn->API()->InsertAdsEmbed($EmbedSpaces, TRUE);
            }
            
            if($AdPlacements || $EmbedSpaces){
                $Style = @file_get_contents(dirname(__FILE__).DS.'design'.DS.'aptads.css');
                if($Style){
                    $Data['Style']=$Style;
                }
                $AptAd->UpdateShowCounts($this->Plgn->API()->AdIDs);
            }
            if($Callback){
                die($Callback.'('.json_encode($Data).')');
            }else{
                die(json_encode($Data));
            }
            
        }else{
            throw NotFoundException();
        }
    }
    
    public function AfterLoad($Sender){
        exit;
        if($this->Plgn->API()->AptAd && !empty($this->AdIDs)){
            $this->Plgn->API()->AptAd->UpdateShowCounts($this->AdIDs);
        }
    }
    
    public function DummyPanelModule($Sender){
        $Sender->AddModule('DummyModule');
        $Panel=&$Sender->Assets['Panel'];
        $LoadedModules=array_Keys($Panel);
        $NewLinkKey = array_search('DummyModule',$LoadedModules);
        $NewLink = array_splice($LoadedModules,$NewLinkKey,1);
        $NewLinkKey = array_search('DummyModule',$LoadedModules);
        array_splice($LoadedModules, $NewLinkKey,0,$NewLink);
        $NewPanel=array();
        foreach($LoadedModules As $Module){
            $NewPanel[$Module]=$Panel[$Module];
        }
        $Panel=$NewPanel;
    }
}
