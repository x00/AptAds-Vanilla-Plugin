<?php if (!defined('APPLICATION')) exit();

class DummyModule extends Gdn_Module {
   
    public function AssetTarget() {
        return 'Panel';
    }
   
    public function ToString() {
        echo '<!--AptAd dummy-->';
    }
   
}
