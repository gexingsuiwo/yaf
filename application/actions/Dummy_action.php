<?php  
class DummyAction extends \Yaf\Action_Abstract {  
    /* a action class shall define this method  as the entry point */ 
    public function execute() {
        echo $this->getController();
        echo "11";
    }
}  
