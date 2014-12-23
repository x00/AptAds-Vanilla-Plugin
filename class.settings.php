<?php if (!defined('APPLICATION')) exit();
abstract class AptAdsSettingsDomain extends AptAdsAPIDomain {
  
  private $WorkerName = 'Settings';
  
  public function CalledFrom(){
    return $this->WorkerName;
  }
  
  public function Settings(){
    $WorkerName = $this->WorkerName;
    if(!GetValue($WorkerName, $this->Workers)){
    $WorkerClass = $this->GetPluginIndex().$WorkerName;

      $this->LinkWorker($WorkerName,$WorkerClass);
    }
    return $this->Workers[$WorkerName];
  }
}

class AptAdsSettings {
    
    public function Settings_MenuItems($Sender) {
        $Menu = $Sender->EventArguments['SideMenu'];
        $Menu->AddLink('Appearance', T('Apt Ads'), 'settings/aptads', 'Garden.Settings.Manage');
    }
    
    public function Settings_Controller($Sender,$Args){
    
        $Sender->Permission('Garden.Settings.Manage');
        $Page = 'AptAds';
        $Title = 'Apt Ads';
        $Description = 'Apt Ads';
        $PageUrl = NULL;
        switch(strtolower(GetValue(0,$Args))){
            case 'info':
                $Page = 'Info';
            case 'ad':
                $AptAd = new AptAdModel('AptAd');
                if(ctype_digit(GetValue(1,$Args)) && ctype_digit(GetValue(2,$Args)) && strtolower(GetValue(3,$Args))=='delete'){
                    $AptAd->DeleteAd(GetValue(2,$Args));
                    Redirect('/settings/aptads/ads/'.GetValue(1,$Args));
                }else if(ctype_digit(GetValue(1,$Args)) && ctype_digit(GetValue(2,$Args)) && strtolower(GetValue(3,$Args))=='up'){
                    $AptAd->AdSwapOrder(GetValue(2,$Args),'up');
                    Redirect(GetValue('r',$_GET));
                }else if(ctype_digit(GetValue(1,$Args)) && ctype_digit(GetValue(2,$Args)) && strtolower(GetValue(3,$Args))=='down'){
                    $AptAd->AdSwapOrder(GetValue(2,$Args),'down');
                    Redirect(GetValue('r',$_GET));
                }else if(ctype_digit(GetValue(1,$Args)) && ctype_digit(GetValue(2,$Args))){
                    $Ad = $AptAd->GetAd(GetValue(2,$Args));
                    $Sender->SetData('Ad',$Ad);
                    $Sender->SetData('AdID',GetValue(2,$Args));
                    $Sender->AddDefinition('AdURL',Url('/settings/venditatio/ad/'.$Args[1].'/',true));
                    $Sender->AddDefinition('AddAd',T('Add Adt'));
                    $Sender->AddDefinition('EditAd',T('Edit Ad'));
                }
            case 'ads':
                $AptAd = new AptAdModel('AptAd');
                $AdPlacement = null;
                if(ctype_digit(GetValue(1,$Args))){
                    list($Offset, $Limit) = OffsetLimit(array_key_exists(1,$Sender->RequestArgs)?$Sender->RequestArgs[1]:0,C('Plugins.AptAds.ListLimit',10));
                    $Sender->Offset=$Offset;
                    $PageUrl = Url('/settings/aptads/ads/'.$Args[1],true).'/{Page}';
                    $AdPlacement=$AptAd->GetAdsByPacement($Args[1],$Limit, $Offset);
                }
                $Description = 'Ad Listings';

                $Sender->SetData('AdPlacement',$AdPlacement);
                $Sender->AddDefinition('AdURL',Url('/settings/venditatio/ad/'.$Args[1].'/'));
                $Sender->AddDefinition('AddAd',T('Add Adt'));
                $Sender->AddDefinition('EditAd',T('Edit Ad'));
                break;
            case 'embed':
                $AptAd = new AptAdModel('AptAdPlacement');
                list($Offset, $Limit) = OffsetLimit(GetValue(0,$Sender->RequestArgs)?GetValue(1,$Sender->RequestArgs):0,C('Plugins.AptAds.ListLimit',10));
                $Sender->Offset=$Offset;
                $CurrentPages = array_unique($AptAd->GetPages());
                $AdPlacements = $AptAd->GetAds($CurrentPages,$Limit,$Offset);
                
                $AptAd->StoredAds($AdPlacements);;
                $Sender->SetData('AdPlacements', $AdPlacements);
                $Spaces = C('Plugins.AptAds.EmbedSpace', array());
                $PlacementIDs = array();
                
                foreach($Spaces As $Space => $IDs){
                    $IDs = Gdn_Format::Unserialize($IDs);
                    foreach($IDs As $ID)
                        $PlacementIDs[] = $ID;
                }
                $EmbedAdPlacements = $AptAd->GetAdsByPlacementIDs($PlacementIDs);
                $AptAd->StoredAds($EmbedAdPlacements);
                $EmbedSpaces = $AptAd->EmbedSpaces($EmbedAdPlacements);
        
                $Sender->SetData('EmbedSpaces', $EmbedSpaces);
                
                $PageUrl = Url('/settings/aptads/embed',true).'/{Page}';
                $Sender->AptAds = $this->Plgn->API();
                $Description = 'This page is used to set up Embed Spaces which embed codes can be referenced when embeding dynamically.';
                $Page = 'Embed';
                $Sender->AddCssFile('aptads.css','plugins/AptAds');
                $Sender->AddJsFile('embed.js','plugins/AptAds');
                $this->Plgn->API()->LoadResources($Sender, TRUE);
                break;
            case 'embedspace':
                break;
            default:
                $AptAd = new AptAdModel('AptAdPlacement');
                $Sender->SetData('DefaultPages',$this->Plgn->API()->DefaultPages);
                $Sender->SetData('DefaultLocations',$this->Plgn->API()->DefaultLocations);
                $Sender->SetData('DefaultTypes',$this->Plgn->API()->DefaultTypes);
                $Sender->SetData('AvailableTemplates',$this->Plgn->API()->TemplateSelect());
                $Sender->SetData('DefaultRotations',$this->Plgn->API()->DefaultRotations);
                $Sender->AddDefinition('PlacementURL',Url('/settings/venditatio/'));
                $Sender->AddDefinition('AddAdPlacement',T('Add Ad Placement'));
                $Sender->AddDefinition('EditAdPlacement',T('Edit Ad Placement'));
                $Page = 'AptAdPlacements';
                $Description = 'Ad Placement Listings';
                if(ctype_digit(GetValue(0,$Args)) && strtolower(GetValue(1,$Args))=='delete'){
                    $AptAd->DeleteAdPlacement(GetValue(0,$Args));
                    Redirect('/settings/aptads');
                }else if(ctype_digit(GetValue(0,$Args)) && strtolower(GetValue(1,$Args))=='up'){
                    $AptAd->AdPlacementSwapOrder(GetValue(0,$Args),'up');
                    Redirect(GetValue('r',$_GET));
                }else if(ctype_digit(GetValue(0,$Args)) && strtolower(GetValue(1,$Args))=='down'){
                    $AptAd->AdPlacementSwapOrder(GetValue(0,$Args),'down');
                    Redirect(GetValue('r',$_GET));
                }else if(ctype_digit(GetValue(0,$Args)) && !GetValue(1,$Args)){
                    $AdPlacement = $AptAd->GetAdPacement(GetValue(0,$Args));
                    $Sender->SetData('AdPlacement',$AdPlacement);
                    $Description = 'Ad Placement Listing';
                    $Sender->AddDefinition('AdPlacementOne', true);
                }else{
                    list($Offset, $Limit) = OffsetLimit(array_key_exists(0,$Sender->RequestArgs)?$Sender->RequestArgs[0]:0,C('Plugins.AptAds.ListLimit',10));
                    $Sender->Offset=$Offset;
                    $AdPlacements = $AptAd->GetAdPacements($Limit, $Offset);
                    $Sender->SetData('AdPlacements',$AdPlacements);
                    $PageUrl = Url('/settings/aptads/',true).'/{Page}';
                    
                }
                break;
        
        }
        if($PageUrl){
            $PagerFactory = new Gdn_PagerFactory();
            $Sender->Pager = $PagerFactory->GetPager('Pager', $Sender);
            $Sender->Pager->MoreCode = '>>';
            $Sender->Pager->LessCode = '<<';
            $Sender->Pager->ClientID = 'Pager';
            $Sender->Pager->Configure(
                $Sender->Offset,
                $Limit,
                $AptAd->GetAdPacementCount(),
                $PageUrl
            );
        }
        if($Sender->Form->IsPostBack() != False){
            $FormValues = $Sender->Form->FormValues();   
            switch(strtolower(GetValue(0,$Args))){
                case 'ad':
                case 'ads':
                    $AptAd->DefineSchema();
                    $FormValues['Url1']= str_replace(array('-','_'),'x',$FormValues['Url']);//bug fix https://bugs.php.net/bug.php?id=51192
                    $FormValues['ImgSrc1']= str_replace(array('-','_'),'x',$FormValues['ImgSrc']);
                           
                    if($AdPlacement->Type=='Image'){
                        $Upload = new Gdn_Upload();
                        try { 
                           if($FormValues['ImgSrc1']){
                                $AptAd->Validation->ApplyRule('ImgSrc1', 'Required', T('Image Src required')); 
                                $AptAd->Validation->ApplyRule('ImgSrc1', 'WebAddress', T('Image Src not valid')); 
                           }else{
                                $Temp = $Upload->ValidateUpload($Sender->Form->EscapeFieldName('ImgFile'), TRUE);

                                if($Temp){
                                    if(!file_exists(PATH_ROOT.DS.'uploads'.DS.'aptads'))
                                        mkdir(PATH_ROOT.DS.'uploads'.DS.'aptads');
                                    if(!is_writable(PATH_ROOT.DS.'uploads'.DS.'aptads')){
                                      throw new Exception(T('uploads/aptads is not writable, please ensure that it exists and the web user has permission to save to this folder'));
                                    }
                                        
                                    $Img = $Upload->GenerateTargetName(PATH_ROOT.DS.'uploads'.DS.'aptads');

                                    $UploadImg = $Upload->SaveAs(
                                     $Temp,
                                     'aptads'.DS.pathinfo($Img, PATHINFO_BASENAME)
                                    );
                                    $SavedImg = pathinfo($UploadImg['SaveName'], PATHINFO_BASENAME);
                                    $FormValues['SavedImg'] = $SavedImg;
                                }
                            }
                        }catch(Exception $ex){
                           $Sender->Form->AddError($ex->getMessage());
                        }
                    }

                    $AptAd->Validation->ApplyRule('Url1', 'Required', T('Image Upload or Target Url required'));
                    $AptAd->Validation->ApplyRule('Url1', 'WebAddress', T('Target Url not valid'));

                    $AptAd->Validation->ApplyRule('Title', 'Required', T('Title is required'));
                    if($AdPlacement->Type=='Text')
                        $AptAd->Validation->ApplyRule('Description', 'Required', T('Description is required'));
                    if(GetValue('ExpireReminder',$FormValues) && !intval(GetValue('ExpireReminder',$FormValues))){
                        unset($FormValues['ExpireReminder']);
                    }
                    if(intval(GetValue('ExpireReminder',$FormValues)) && strtotime(GetValue('ExpireReminder',$FormValues))<=time()){
                        $AptAd->Validation->AddValidationResult('ExpireDatePast',T('The Expire Reminder date needs to be in the future.'));
                    }
                    if(GetValue('AdID',$FormValues)){
                        $Sender->SetData('AdID',GetValue('AdID',$FormValues));
                        
                    }
                    $AptAd->Validation->Validate($FormValues);
                    $Sender->Form->SetValidationResults($AptAd->Validation->Results());
                    if ($Sender->Form->ErrorCount() == 0) {
                        $AptAd->SaveAd($FormValues);
                        Redirect(Url('',true));
                    }
                    break;
                case 'embedspace':
                    header('Content-type: application/json');
                    header('Cache-Control: no-cache, must-revalidate');
                    header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
                    try {
                        $EmbedSpace = GetIncomingValue('EmbedSpace');
                        
                        if(!preg_match('`^[a-z]+$`',$EmbedSpace))
                            throw new Exception;
                        
                        $AdPlacementIDs = GetPostValue('AdPlacementIDs');
                        
                        if($AdPlacementIDs!='empty'){
                            if(!is_array($AdPlacementIDs))
                                throw new Exception;
                            foreach($AdPlacementIDs As $AdPlacementID){
                                if(!$AdPlacementID || !ctype_digit($AdPlacementID)){
                                    throw new Exception;
                                    break;
                                }
                            }
                            if(!SaveToConfig('Plugins.AptAds.EmbedSpace.'.$EmbedSpace,$AdPlacementIDs))
                                throw new Exception;
                            
                        }else{
                            if(!RemoveFromConfig('Plugins.AptAds.EmbedSpace.'.$EmbedSpace))
                                throw new Exception;
                                
                        }
                        

                    } catch(Exception $e){
                        die(json_encode(false));
                    }
                    die(json_encode(true));
                    break;
                default:
                    if($FormValues['Page']=='all')
                        $FormValues['Page']='.*';
                    if($FormValues['Fit']){
                        $FormValues['Width']=0;
                        $FormValues['Height']=0;
                    }
                    $AptAd->DefineSchema();
                    if(GetValue('AdPlacementID',$FormValues)){
                        $Sender->SetData('AdPlacementID',GetValue('AdPlacementID',$FormValues));
                    }
                    $AptAd->Validation->Validate($FormValues);
                    $Sender->Form->SetValidationResults($AptAd->Validation->Results());
                    if (count($AptAd->Validation->Results()) == 0) {
                        $AptAd->SaveAdPlacement($FormValues);
                        Redirect(Url('',true).(GetValue('AdPlacementID',$FormValues)?'?LastID='.GetValue('AdPlacementID',$FormValues):''));
                    }
                    break;
            }
            

        }
        if(GetValue('LastID',$_GET)){
            $Sender->AddDefinition('LastID',GetValue('LastID',$_GET));
        }
        $Sender->AddSideMenu();
        $Sender->SetData('Title', T($Title));
        $Sender->SetData('Description', T($Description));
        $Sender->AddDefinition('AptAdsHelpBtn', T('ApAds.HelpBtn', 'Help'));
        $Sender->AddDefinition('AptAdsTemplateHelp',json_encode($this->Plgn->API()->TemplateHelp));
        $Sender->AddJsFile('aptadslistings.js','plugins/AptAds');
        //$Sender->SetData('InfoView', $this->Plgn->Utility()->ThemeView('info'));
        $Sender->View = $this->Plgn->Utility()->ThemeView($Page);
        $Sender->Render();
    }  
}
