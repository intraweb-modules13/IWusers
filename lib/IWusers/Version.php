<?php
class IWusers_Version extends Zikula_Version
{
    public function getMetaData() {
        $meta = array();
        $meta['displayname'] = $this->$this->__("IWusers");
        $meta['description'] = $this->$this->__("Improves the chances of users of the module.");
        $meta['url'] = $this->$this->__("IWusers");
        $meta['version'] = '3.0.0';
        $meta['securityschema'] = array('IWusers::' => '::');
        /*
        $meta['dependencies'] = array(array('modname' => 'IWmain',
                                            'minversion' => '3.0.0',
                                            'maxversion' => '',
                                            'status' => ModUtil::DEPENDENCY_REQUIRED));
         *
         */
        return $meta;
    }

}