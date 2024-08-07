<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Login extends CI_Controller {
function __construct() {
    parent::__construct();
    $this->load->helper("form");
    $this->load->helper('url');
    $this->load->library('Shopify');
    $this->load->library('session');
    $this->load->model('appinstall');
}
	public function index(){
		if($this->input->get('shop')){
			$rurl = base_url().'login/authorize';
			$s = $this->input->get('shop');
			$this->_authRedirect($rurl,$s);
		}
	}
	public function authorize(){
		$input = $this->input->get(NULL,TRUE);
		$shop = $input['shop'];
		$code =  $input['code'];
		$timestamp =  $input['timestamp'];
		$hmac =  $input['hmac'];
		$apiinfo=$this->_getapiKeys();
		if($this->_checkrequestShopify($code,$hmac,$shop,$timestamp,$apiinfo['secret'])){
			$shoda = $this->_getApptoken($shop);
        	$t = $shoda[0]['shop_token'];
        	$sid = $shoda[0]['id'];
			$this->session->set_userdata('shopdata', array('shop'=>$shop,'token'=>$t,'sid'=>$sid));
			$url = $this->config->item('app_url');
			redirect($url.'?shop='.$shop,'refresh');
		}else{
			echo "Access denied!";
		}
	}

	private function _getapiKeys(){
		$config=get_config();
		return array('api'=>$config['api_key'],'secret'=>$config['secret_key'],'scope'=>$config['shopify_scope'],'redirect_url'=>$config['redirect_url']);
	}
	private function _authRedirect($redirecurl,$shop){
			$apiinfo=$this->_getapiKeys();

			$authorizeurl=$this->shopify->getAuthorizeUrl($apiinfo['scope'], $redirecurl,$shop,$apiinfo['api']);
			redirect($authorizeurl);
	}
	private function _checkrequestShopify($code,$hmac,$shop,$timestamp,$secret){
		$part1 ='';
    	if ($code != '')
      		$part1 = "code=" . $code;
      	$part1 .= "&shop=" . $shop . "&timestamp=" . $timestamp;
    	return (hash_hmac('sha256', $part1, $secret) === $hmac);
	}
	private function _getApptoken($s){
        return $this->appinstall->getToken($s);
    }
}
