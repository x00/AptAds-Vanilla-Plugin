<?php if (!defined('APPLICATION')) exit();

$PluginInfo['AptAds'] = array(
   'Name' => 'Apt Ads',
   'Description' => 'An apt way to manage ad placement, supply and rotate ads in many locations',
   'Version' => '0.1.14b',
   'Author' => "Paul Thomas",
   'AuthorEmail' => 'dt01pq_pt@yahoo.com',
   'AuthorUrl' => 'http://vanillaforums.org/profile/x00',
   'RequiredApplications' => array('Vanilla' => '2.1'),
   'RegisterPermissions' => array('Plugins.AptAds.Manage'),
   'SettingsUrl' => '/dashboard/settings/aptads',
   'SettingsPermission' => 'Plugins.AptAds.Manage',
   'MobileFriendy' => TRUE
);
 
function AptAdsLoad($Class){
  $Match = array();
  if(preg_match('`^AptAds(.*)`',$Class,$Match)){
    $File = strtolower(preg_replace('`Domain$`','',$Match[1]));
    @include_once(PATH_PLUGINS.DS.'AptAds'.DS.'class.'.$File.'.php');
  }
}

spl_autoload_register('AptAdsLoad');

AptAdsUtility::InitLoad();

class AptAds extends AptAdsUIDomain{
    
    public function Base_GetAppSettingsMenuItems_Handler($Sender) {
        $this->Settings()->Settings_MenuItems($Sender);
    }
    
    public function SettingsController_Venditatio_Create($Sender,$Args){
        $this->Settings()->Settings_Controller($Sender, $Args);
    }
    
    public function SettingsController_AptAds_Create($Sender,$Args){
        $this->Settings()->Settings_Controller($Sender, $Args);
    }
    
    public function Base_BeforeRenderAsset_Handler($Sender) {
        $this->UI()->RenderAssetHandler($Sender, AptAdsAPI::BEFORE);
    }

    public function Base_AfterRenderAsset_Handler($Sender) {
        $this->UI()->RenderAssetHandler($Sender, AptAdsAPI::AFTER);
    }
    
    public function Base_BetweenRenderAsset_Handler($Sender) {
        $this->UI()->RenderAssetHandler($Sender);
    }
    
    public function Base_Render_Before($Sender) {
        $this->UI()->Load($Sender);
    }
    
    public function Base_Render_After($Sender) {
        $this->UI()->AfterLoad($Sender);
    }    
    
    public function PluginController_AptAds_Create($Sender,$Args){
        $this->UI()->RemoteLoad($Sender, $Args);
    }
    
    public function Base_BeforeBlockDetect_Handler($Sender,&$Args){
        $Args['BlockExceptions']['plugin/aptads\/embed\/?$/']=Gdn_Dispatcher::BLOCK_NEVER;
    }

    public function Base_BeforeDispatch_Handler($Sender){
        $this->Utility()->HotLoad();
    }
    
    public function Gdn_Dispatcher_AfterControllerInit_Handler($Sender){
        $this->API()->PreLoad();
    }
    
    public function Setup() {
        $this->Utility()->HotLoad(TRUE);
    }
    
    public function PluginSetup(){
        Gdn::Structure()
        ->Table('AptAdPlacement')
        ->Column('AdPlacementID','int(11)',false,array('primary','key'))
        ->Column('Label','varchar(10)',NULL)
        ->Column('Page','varchar(100)',false,array('index'))
        ->Column('Location','varchar(100)')
        ->Column('AssetOrder','int(11)',0)
        ->Column('ShowOrder','int(11)',0)
        ->Column('Type',array('Image','Text'))
        ->Column('Template','varchar(100)',NULL)
        ->Column('Message','varchar(150)',NULL)
        ->Column('Rows','int(11)',0)
        ->Column('Cols','int(11)',0)
        ->Column('Fit','int(4)',1)
        ->Column('Width','int(11)',0)
        ->Column('Height','int(11)',0)
        ->Column('Rotation',array_keys($this->API()->DefaultRotations))
        ->Column('Enabled','int(4)',0)
        ->Set();
        
        Gdn::Structure()
        ->Table('AptAdPlacement')
        ->PrimaryKey('AdPlacementID')
        ->Set();
        
        Gdn::Structure()
        ->Table('AptAd')
        ->Column('AdID','int(11)',false,array('primary','key'))
        ->Column('AdPlacementID','int(11)',false,array('index'))
        ->Column('Url','varchar(200)',null)
        ->Column('SavedImg','varchar(200)',null)
        ->Column('ImgSrc','varchar(200)',null)
        ->Column('Title','varchar(100)',null)
        ->Column('Description','varchar(250)',null)
        ->Column('AltLinkText','varchar(100)',null)
        ->Column('ShowCount','int(11)',0)
        ->Column('ExpireReminder','datetime',null)
        ->Column('NotifyEmail','int(4)',0)
        ->Column('NotifyInform','int(4)',0)
        ->Column('Enabled','int(4)',0)
        ->Column('ShowOrder','int(11)',0)
        ->Set();
        
        Gdn::Structure()
        ->Table('AptAd')
        ->PrimaryKey('AdID')
        ->Set();
        
        if(class_exists('AptAdModel')){
            AptAdModel::Migration();
            AptAdModel::AutoPurge();
        }
    }
}
