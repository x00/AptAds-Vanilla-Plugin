<?php if (!defined('APPLICATION')) exit();


class AptAdModel extends VanillaModel{

    public $ExtraPlacementFields = array();

    public $ExtraAdFields = array();
    
    public function __construct($Name=null) {
        parent::__construct($Name?$Name:'AptAd');
    }
    
    public function GetPages(){
        return C('Plugins.AptAds.Pages', array());
    }
    
    public function SavePage($Page){
        $Pages = $this->GetPages();
        if(!in_array($Page,$Pages)){
            $Pages[]=$Page;
            SaveToConfig('Plugins.AptAds.Pages',$Pages);
        }
    }
    
    public function GetAdsByPlacementIDs($AdPlacementIDs,$AdPlacements=null,$GroupByLocation=FALSE){
        if(!$AdPlacements){
            $AdPlacements = $this->SQL
            ->Select('ap.*')
            ->From('AptAdPlacement ap')
            ->WhereIn('ap.AdPlacementID',$AdPlacementIDs)
            ->Where('ap.Enabled',TRUE)
            ->OrderBy('ap.ShowOrder','asc')
            ->Get()
            ->Result();
        }
        $AdPlacementIDs=array();
        
        foreach($AdPlacements As $AdPlacement ){
           $AdPlacementIDs[]=$AdPlacement->AdPlacementID;
        }
        
        if(!$AdPlacementIDs) return;
        
        $Ads = $this->SQL
        ->Select('a.*')
        ->From('AptAd a')
        ->WhereIn('a.AdPlacementID',$AdPlacementIDs)
        ->Where('a.Enabled',TRUE)
        ->OrderBy('a.ShowOrder','asc')
        ->Get()
        ->Result();
        
        
        
        $GroupByPlacementIDs=array();
        
        foreach($Ads As $Ad){
            if(!GetValue($Ad->AdPlacementID,$GroupByPlacementIDs))
                $GroupByPlacementIDs[$Ad->AdPlacementID]=array();
            $GroupByPlacementIDs[$Ad->AdPlacementID][$Ad->AdID]=$Ad;
        }
        
        if($GroupByLocation){
            $GroupedAds=array();
            
            foreach($AdPlacements As $AdPlacement ){
                $Ads = GetValue($AdPlacement->AdPlacementID,$GroupByPlacementIDs);
                
                
                
                if(!$Ads) continue;
                $AdPlacement->Ads=$Ads;
                $GroupedAds[$AdPlacement->Location][$AdPlacement->AdPlacementID]=$AdPlacement;
            }
            return $GroupedAds;
        }else{
            $GroupedAds=array();
            
            foreach($AdPlacements As $AdPlacement ){
                $Ads = GetValue($AdPlacement->AdPlacementID,$GroupByPlacementIDs);
                
                
                
                if(!$Ads) continue;
                $AdPlacement->Ads=$Ads;
                $GroupedAds[$AdPlacement->AdPlacementID]=$AdPlacement;
            }
            return $GroupedAds;
        }
    }
   
    public function GetAds($Pages,$Limit=null,$Offset=null){
        if(!is_array($Pages))
            $Pages=array($Pages);
        $AdPlacements = $this->SQL
        ->Select('ap.*')
        ->From('AptAdPlacement ap')
        ->WhereIn('ap.Page',$Pages)
        ->Where('ap.Enabled',TRUE)
        ->OrderBy('ap.ShowOrder','asc')
        ->Limit($Limit,$Offset)
        ->Get()
        ->Result();
        $AdPlacementIDs=array();
        
       
        foreach($AdPlacements As $AdPlacement ){
           $AdPlacementIDs[]=$AdPlacement->AdPlacementID;
        }
        
        return $this->GetAdsByPlacementIDs($AdPlacementIDs,$AdPlacements,TRUE);

    }
    
    public function GetAd($AdID){
        return $this->SQL
        ->Select('a.*, ap.*')
        ->From('AptAd a')
        ->Join('AptAdPlacement ap','ap.AdPlacementID = a.AdPlacementID')
        ->Where('a.AdID',$AdID)
        ->Get()
        ->FirstRow();
    }
    
    public function GetAdPacements($Limit=20,$Offset=0,$OrderBy='ShowOrder',$Sort='ASC'){
        return $this->SQL
        ->Select('ap.*')
        ->From('AptAdPlacement ap')
        ->OrderBy('ap.'.($OrderBy),(strtolower($Sort)=='asc'?'asc':'desc'))
        ->Limit($Limit,$Offset)
        ->Get()
        ->Result();
    }
    
    public function GetAdPacement($AdPlacementID){
        return $this->SQL
        ->Select('ap.*')
        ->From('AptAdPlacement ap')
        ->Where('AdPlacementID',$AdPlacementID)
        ->Get()
        ->FirstRow();
    }
    
    public function GetAdPacementCount(){
        return $this->SQL
        ->Select('Count(AdPlacementID) Count')
        ->From('AptAdPlacement ap')
        ->Get()
        ->FirstRow()
        ->Count;
    }
    
    public function GetAdsByPacement($AdPlacementID){
        $AdPlacement=$this->GetAdPacement($AdPlacementID);
        
        if(empty($AdPlacement))
            return null;
            
        $Ads = $this->SQL
        ->Select('a.*')
        ->From('AptAd a')
        ->Where('a.AdPlacementID',$AdPlacementID)
        ->OrderBy('a.ShowOrder','asc')
        ->Get()
        ->Result();
        
        $AdPlacement->Ads=$Ads;
        
        return $AdPlacement;
    }
    
    public function SaveAdPlacement($AdPlacementFields){
        $this->EventArguments['AdPlacementFields'] = &$AdPlacementFields;
        $this->FireEvent('BeforeAdPlacement');
        if($AdPlacementFields['AdPlacementID']){
            $this->SQL
            ->Update('AptAdPlacement')
            ->Set(
                array(
                    'Page'=>$AdPlacementFields['Page'],
                    'Label'=>$AdPlacementFields['Label'],
                    'Location'=>$AdPlacementFields['Location'],
                    'AssetOrder'=>$AdPlacementFields['AssetOrder'],
                    'Type'=>$AdPlacementFields['Type'],
                    'Template'=>$AdPlacementFields['Template'],
                    'Message'=>$AdPlacementFields['Message'],
                    'Rows'=>$AdPlacementFields['Rows'],
                    'Cols'=>$AdPlacementFields['Cols'],
                    'Fit'=>$AdPlacementFields['Fit'],
                    'Width'=>$AdPlacementFields['Width'],
                    'Height'=>$AdPlacementFields['Height'],
                    'Rotation'=>$AdPlacementFields['Rotation'],
                    'Enabled'=>$AdPlacementFields['Enabled']
                ) + $this->ExtraPlacementFields
            )
            
            ->Where('AdPlacementID',$AdPlacementFields['AdPlacementID'])
            ->Put();
            $this->ClearShowCounts($AdPlacementFields['AdPlacementID']);
        }else{
            $this->SQL
            ->Insert('AptAdPlacement',
                array(
                    'Page'=>$AdPlacementFields['Page'],
                    'Label'=>$AdPlacementFields['Label'],
                    'Location'=>$AdPlacementFields['Location'],
                    'AssetOrder'=>$AdPlacementFields['AssetOrder'],
                    'Type'=>$AdPlacementFields['Type'],
                    'Template'=>$AdPlacementFields['Template'],
                    'Message'=>$AdPlacementFields['Message'],
                    'Rows'=>$AdPlacementFields['Rows'],
                    'Cols'=>$AdPlacementFields['Cols'],
                    'Fit'=>$AdPlacementFields['Fit'],
                    'Width'=>$AdPlacementFields['Width'],
                    'Height'=>$AdPlacementFields['Height'],
                    'Rotation'=>$AdPlacementFields['Rotation'],
                    'Enabled'=>$AdPlacementFields['Enabled'],
                    'ShowOrder'=>0
                ) + $this->ExtraPlacementFields
            );
            
            $AdPlacementID = $this->SQL->Database->Connection()->lastInsertId();

            $this->SQL
            ->Update('AptAdPlacement')
            ->Set('ShowOrder',$AdPlacementID)
            ->Where('AdPlacementID',$AdPlacementID)
            ->Put();
            
        }
        
        $this->SavePage($AdPlacementFields['Page']);
        
    }
    
    public function DeleteAdPlacement($AdPlacementID){
        $this->SQL
            ->Delete('AptAdPlacement',array('AdPlacementID'=>$AdPlacementID));
        $this->SQL
            ->Delete('AptAd',array('AdPlacementID'=>$AdPlacementID));
    }
    
    public static function Migration(){
        Gdn::SQL()
        ->Update('AptAdPlacement')
        ->Set('ShowOrder','AdPlacementID', FALSE)
        ->Where('ShowOrder',0)
        ->Put();
        
        Gdn::SQL()
        ->Update('AptAd')
        ->Set('ShowOrder','AdID', FALSE)
        ->Where('ShowOrder',0)
        ->Put();        
    }
    
    public static function AutoPurge(){
        $Stranded = Gdn::SQL()
        ->Select('a.*')
        ->From('AptAd a')
        ->Join('AptAdPlacement ap', 'ap.AdPlacementID=a.AdPlacementID', 'left')
        ->Where('ap.AdPlacementID is null')
        ->GroupBy('a.AdPlacementID')
        ->Get()
        ->Result();
        $AdPlacementIDs = array();
        foreach($Stranded As  $Ad)
            $AdPlacementIDs[] = $Ad->AdPlacementID;
        if(!empty($AdPlacementIDs))
            Gdn::SQL()
                ->Delete('AptAd',array('AdPlacementID'=>$AdPlacementIDs));
    }
    
    public function AdPlacementSwapOrder($CurrentID, $Direction='up'){
        
        $Current= $this->SQL
        ->Select('ap.*')
        ->From('AptAdPlacement ap')
        ->Where('ap.AdPlacementID', $CurrentID)
        ->Get()
        ->FirstRow();
        
        if(!$Current)
            return;
        
        $Next= $this->SQL
        ->Select('ap.*')
        ->From('AptAdPlacement ap')
        ->Where('ap.ShowOrder'.($Direction=='down'?'>':'<'), $Current->ShowOrder)
        ->OrderBy('ap.ShowOrder', ($Direction=='down'?'asc':'desc'))
        ->Limit(1,0)
        ->Get()
        ->FirstRow();
        
        if(!$Next)
            return;
        
        $this->SQL
        ->Update('AptAdPlacement')
        ->Set('ShowOrder',$Next->ShowOrder)
        ->Where('AdPlacementID',$CurrentID)
        ->Put();
        
        $this->SQL
        ->Update('AptAdPlacement')
        ->Set('ShowOrder',$Current->ShowOrder)
        ->Where('AdPlacementID',$Next->AdPlacementID)
        ->Put();
    }
    
    public function AdSwapOrder($CurrentID, $Direction='up'){
        
        $Current= $this->SQL
        ->Select('a.*')
        ->From('AptAd a')
        ->Where('a.AdID', $CurrentID)
        ->Get()
        ->FirstRow();
        
        if(!$Current)
            return;
        
        $Next= $this->SQL
        ->Select('a.*')
        ->From('AptAd a')
        ->Where('a.ShowOrder'.($Direction=='down'?'>':'<'), $Current->ShowOrder)
        ->OrderBy('a.ShowOrder', ($Direction=='down'?'asc':'desc'))
        ->Limit(1,0)
        ->Get()
        ->FirstRow();
        
        if(!$Next)
            return;
        
        $this->SQL
        ->Update('AptAd')
        ->Set('ShowOrder',$Next->ShowOrder)
        ->Where('AdID',$CurrentID)
        ->Put();
        
        $this->SQL
        ->Update('AptAd')
        ->Set('ShowOrder',$Current->ShowOrder)
        ->Where('AdID',$Next->AdID)
        ->Put();
    }
    
    public function SaveAd($AdFields){
        $Sender->EventArguments['AdFields'] = &$AdFields;
        $this->FireEvent('BeforeSaveAd');
        if($AdFields['AdID']){
            $this->SQL
            ->Update('AptAd')
            ->Set(
                array(
                    'AdPlacementID'=>$AdFields['AdPlacementID'],
                    'Url'=>$AdFields['Url'],
                    'SavedImg'=>$AdFields['SavedImg'],
                    'ImgSrc'=>$AdFields['ImgSrc'],
                    'Title'=>$AdFields['Title'],
                    'Description'=>$AdFields['Description'],
                    'ExpireReminder'=>intval(GetValue('ExpireReminder',$AdFields))?Gdn_Format::ToDateTime(strtotime($AdFields['ExpireReminder'])):null,
                    'NotifyEmail'=>0,
                    'NotifyInform'=>0,
                    'Enabled'=>$AdFields['Enabled']
                ) + $this->ExtraAdFields
            )
            ->Where('AdID',$AdFields['AdID'])
            ->Put();
        }else{
            $this->SQL
            ->Insert('AptAd',
                array(
                    'AdPlacementID'=>$AdFields['AdPlacementID'],
                    'Url'=>$AdFields['Url'],
                    'SavedImg'=>$AdFields['SavedImg'],
                    'ImgSrc'=>$AdFields['ImgSrc'],
                    'Title'=>$AdFields['Title'],
                    'Description'=>$AdFields['Description'],
                    'ExpireReminder'=>intval(GetValue('ExpireReminder',$AdFields))?Gdn_Format::ToDateTime(strtotime($AdFields['ExpireReminder'])):null,
                    'Enabled'=>$AdFields['Enabled']
                ) + $this->ExtraAdFields
            );
            $this->ClearShowCounts($AdFields['AdPlacementID']);
            
            $AdID = $this->SQL->Database->Connection()->lastInsertId();

            $this->SQL
            ->Update('AptAd')
            ->Set('ShowOrder',$AdID)
            ->Where('AdID',$AdID)
            ->Put();
        }
    }
    
    public function DeleteAd($AdID){
        $this->SQL
            ->Delete('AptAd',array('AdID'=>$AdID));
    }
    

    
    
    public function UpdateShowCounts($AdIDs){
        $Prefix = $this->SQL->Database->DatabasePrefix;
        $AdIDs = join(',',$AdIDs);
        $SQL="Update {$Prefix}AptAd set `ShowCount`=`ShowCount`+1 where AdID in ({$AdIDs})";
        $this->SQL->Query($SQL);
    }
    
    public function ClearShowCounts($AdPlacementID){
        $this->SQL
        ->Update('AptAd')
        ->Set('ShowCount',0)
        ->Where('AdPlacementID',$AdPlacementID)
        ->Put();
    }
    
    public function GetExpired(){
        return $this->SQL
        ->Select('a.*')
        ->From('AptAd a')
        ->Where('a.ExpireReminder<',Gdn_Format::ToDateTime())
        ->Where('a.Enabled',TRUE)
        ->Get()
        ->Result();
    }
    
    public function NotifySet($AdIDs,$NotifyEmail=0,$NotifyInform=0){
        $this->SQL
        ->Update('AptAd')
        ->Set(
            array(
                'NotifyEmail'=>$NotifyEmail,
                'NotifyInform'=>$NotifyInform
            )
        )
        ->WhereIn('AdID',$AdIDs)
        ->Put();
    } 
    
    public function StoredAds($AdPlacements){
        if(!$AdPlacements)
            return FALSE;
        if(is_numeric(key($AdPlacements)))
            $AdPlacementsTemp = array('Block' => $AdPlacements);
        else 
            $AdPlacementsTemp = $AdPlacements;
            
        foreach($AdPlacementsTemp As $AdPlacementBlock){
            
            foreach($AdPlacementBlock As $AdPlacement){
                $AdPlacement->StoredAds = array();
                foreach($AdPlacement->Ads As $StoredAd){
                    
                    $StoredAd->Site = Gdn_Format::Text(parse_url($StoredAd->Url, PHP_URL_HOST));
                    $StoredAd->Title = Gdn_Format::Text($StoredAd->Title);
                    $StoredAd->Description = Gdn_Format::Text($StoredAd->Description);
                    $AdPlacement->StoredAds[] = $StoredAd;
                }
            }
        }
        return $AdPlacements;
    }
    
    public function EmbedSpaces($EmbedAdPlacements){
        if(!$EmbedAdPlacements)
            return FALSE;
        $Spaces = C('Plugins.AptAds.EmbedSpace', array());
        $EmbedAdPlacementLookup = array();

        foreach($EmbedAdPlacements As $EmbedAdPlacement){
            
            $EmbedAdPlacementLookup[$EmbedAdPlacement->AdPlacementID] = $EmbedAdPlacement;
        }
        
        $EmbedSpaces = array();
        foreach($Spaces As $Space => $IDs){
            if(!is_array($IDs))
                $IDs = Gdn_Format::Unserialize($IDs);
            $EmbedSpaces[$Space] = array();
            foreach($IDs As $ID)
                $EmbedSpaces[$Space][] = $EmbedAdPlacementLookup[$ID];
        
        }
        return $EmbedSpaces;
    }
    
    public function AdPlacementsByLabel($AdPlacements){
        if(!$AdPlacements)
            return FALSE;
        
        $AdPlacementByLabel = array();

        foreach($AdPlacements As $AdPlacement){
            if($AdPlacement->Label){
                $Label = '#'.$AdPlacement->Label;
                if(!array_key_exists($Label, $AdPlacementByLabel))
                    $AdPlacementByLabel[$Label] = array();
                $AdPlacementByLabel[$Label][] = $AdPlacement;
            }
        }
        return $AdPlacementByLabel;
    }
}
