<?php 
class Appinstall extends CI_Model {
    function __construct() {
    parent::__construct();
        $this->load->database();
    }
    public function install($d){
        if($this->checkifShopexit($d['shop_permanent_doamin']))
            $this->updateToken($d);
        else
            $this->db->insert('wishlist_shop', $d);
        return true;
    }
    public function getToken($s){
        $r=$this->db->get_where('wishlist_shop', array('shop_permanent_doamin' => $s))->result_array();
        $c=count($r);
        if($c>0)
            return $r;
        else
            return false;
    }
    public function checkifShopexit($s){
        $c = $this->db->get_where('wishlist_shop', array('shop_permanent_doamin' => $s))->num_rows();
        if($c===0)
            return false;
        else
            return true;
    }
    public function checkifInstalled($s){
        $c = $this->db->get_where('wishlist_shop', array('shop_permanent_doamin' => $s))->num_rows();
        if($c===0)
            return false;
        else
            return true;
    }
    public function updateToken($d){
        $datagd = array('token'=> $d['token'],'updated' => $d['updated']);
        $this->db->update('wishlist_shop', $datagd, array('shop_permanent_doamin'=>$d['shop_permanent_doamin']));
        return true;
    }
    public function unInstall($s){
        $this->db->delete('wishlist_shop',array('shop_permanent_doamin'=>$s));
        return true;
    }
    public function addLogm($d){
        $this->db->insert('wishlist_shop_logs', $d);
    }
}
?>