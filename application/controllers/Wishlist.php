<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Wishlist extends CI_Controller {
    function __construct() {
       parent::__construct();
       $this->load->helper("form");
       $this->load->helper('url');
       $this->load->library('Shopify');
       $this->load->library('session');
       $this->load->library('upload');
       $this->load->library('email');
       $this->load->model('wishlistm');
       $this->load->model('appinstall');
       $this->load->helper('string');
       ini_set('max_execution_time', 0); 
        ini_set('memory_limit','2048M');
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Headers: X-API-KEY, Origin, X-Requested-With, Content-Type, Accept, Access-Control-Request-Method");
        header("Access-Control-Allow-Methods: GET, POST");
    }
    private function _getApptoken($s){
        return $this->appinstall->getToken($s);
    }
    private function _getProductcount($s,$t){
        return $this->shopify->call('GET',"/admin/products/count.json", array(),$s,$t);
    }
    private function _getProducts($s,$t,$p){
        return $this->shopify->call('GET',"/admin/products.json", array('page'=>$p,'limit'=>'250','published_status'=>'published'),$s,$t);
    }
    private function _getProductsbyids($s,$t,$pids){
        return $this->shopify->call('GET',"/admin/products.json?ids=".$pids, array(),$s,$t);
    }
    private function _getcustomCollcount($s,$t){
        return $this->shopify->call('GET',"/admin/custom_collections/count.json", array(),$s,$t);
    }
    private function _getcustomColl($s,$t,$p){
        return $this->shopify->call('GET',"/admin/custom_collections.json", array('page'=>$p,'limit'=>'250'),$s,$t);
    }
    private function _getsmartCollcount($s,$t){
        return $this->shopify->call('GET',"/admin/smart_collections/count.json", array(),$s,$t);
    }
    private function _getsmartColl($s,$t,$p){
        return $this->shopify->call('GET',"/admin/smart_collections.json", array('page'=>$p,'limit'=>'250'),$s,$t);
    }
    private function _getSessiondata(){
        if($this->session->userdata('shopdata'))
            return $this->session->userdata('shopdata');
        else
            return array('status'=>'Shop logged out');
    }
    public function index(){
        $data['class']=$this->router->fetch_class();
        $data['method']=$this->router->fetch_method();
        $s = $this->input->get_post('shop');
        if($this->session->userdata('shopdata')){
            $sess = $this->_getSessiondata();
            $s = $sess['shop'];
            $t = $sess['token'];            
        }else{
            $rurl = base_url().'login?shop='.$s;
           redirect($rurl,'refresh');
        }

        $data['shop'] = $s;
        $this->load->view('common/header',$data);
        $this->load->view('wishlist/index',$data);
        $this->load->view('common/footer',$data);
    }
    public function add(){
        $cid = $this->input->get_post('cid');
        $pid =  $this->input->get_post('id');
        $this->wishlistm->add($cid,$pid);
        echo $this->input->get_post('callback'). '(';
        echo json_encode(array('success'=>'1','message'=>'Item added successfully.'));
        echo  ')'; 
    }
    public function remove(){
         $cid = $this->input->get_post('cid');
        $pid =  $this->input->get_post('id');
        $this->wishlistm->remove($cid,$pid);
        echo $this->input->get_post('callback'). '(';
        echo json_encode(array('success'=>'1','message'=>'Item removed successfully.'));
        echo  ')'; 
    }
    public function getWishlistids(){
        $cid = $this->input->get_post('cid');
        $ids = $this->wishlistm->getIds($cid);
        $total = count($ids);
        echo $this->input->get_post('callback'). '(';
        echo json_encode(array('success'=>'1','pids'=>$ids,'total'=>$total));
        echo  ')';
    }
    public function getWishlistpage(){
        $s = $this->input->get_post('shop');
        $cid = $this->input->get_post('cid');
        $shoda = $this->_getApptoken($s);
        $t = $shoda[0]['token'];
        $sid = $shoda[0]['id'];
        $array = array('shop'=>$s,'customer'=>$cid,'token'=>$t);
        $ids = $this->wishlistm->getIds($cid);
        $pids = array();
        foreach ($ids as $key => $value) {
           $pids[$key]= $value['product_id'];
        }
        if(count($pids)>0){
            $pidstring = implode(',', $pids);
            $prodata = $this->_getProductsbyids($s,$t,$pidstring);
        }else{
            $prodata = array();
        }
        echo $this->input->get_post('callback'). '(';
        echo json_encode(array('success'=>'1','data'=>$prodata,'total'=>count($ids)));
        echo  ')';
    }

    public function appUninstall(){
        $date = date('Y-m-d H:i:s');
        $serverdata = json_decode(json_encode($this->input->server(NULL,TRUE)),true);
        $shop = $serverdata['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
        if(isset($shop)){
            $this->appinstall->uninstall($shop);
            $this->addLog(array('shop'=>$shop,
                                'log_key'=>'uninstall_request_from_shop',
                                'log_value'=>serialize($serverdata),
                                 'date'=>$date   
                                ));
        }
    }
    public function addLog($d){
        $this->appinstall->addLogm($d);
    }
    public function cre(){
        $serverdata = json_decode(json_encode($this->input->server(NULL,TRUE)),true);
        $shop = $serverdata['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
        if(isset($shop)){
            $this->addLog(array('shop'=>$shop,'log_key'=>'cre_request','log_value'=>serialize($serverdata)));
        }
    }
    public function cee(){
        $serverdata = json_decode(json_encode($this->input->server(NULL,TRUE)),true);
        $shop = $serverdata['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
        if(isset($shop)){
            $this->addLog(array('shop'=>$shop,'log_key'=>'cee_request','log_value'=>serialize($serverdata)));
        }
    }
    public function see(){
        $serverdata = json_decode(json_encode($this->input->server(NULL,TRUE)),true);
        $shop = $serverdata['HTTP_X_SHOPIFY_SHOP_DOMAIN'];
        if(isset($shop)){
            $this->addLog(array('shop'=>$shop,'log_key'=>'see_request','log_value'=>serialize($serverdata)));
        }
    }
}
