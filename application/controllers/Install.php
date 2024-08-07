<?php
defined('BASEPATH') OR exit('No direct script access allowed');
class Install extends CI_Controller {
function __construct() {
    parent::__construct();
    $this->load->helper("form");
    $this->load->helper('url');
    $this->load->library('Shopify');
    $this->load->library('session');
    $this->load->model('appinstall');
}
	public function index(){
		if($this->input->get('code')){
			$apiinfo=$this->_getapiKeys();
			$code = $this->input->get('code');
			$hmac = $this->input->get('hmac');
			$shop = $this->input->get('shop');
			$timestamp = $this->input->get('timestamp');
			if($this->_checkrequestShopify($code,$hmac,$shop,$timestamp,$apiinfo['secret'])){
				$tkn = $this->shopify->getAccessToken($code,$shop,$apiinfo['api'],$apiinfo['secret']);
				$data=array('token'=>$tkn,'shop'=>$shop);
				$date=date('Y-m-d H:i:s');
				if(trim($tkn) == '')
					redirect(base_url().'install/appinstall?shop='.$shop);
				$insertdata = array('shop_domain' => trim($shop),'shop_permanent_doamin' => trim($shop),'shop_token'=>$tkn,'created'=>$date,'updated'=>$date);
				$sid = $this->appinstall->install($insertdata);
				$this->session->set_userdata('shopdata', array('shop'=>$shop,'token'=>$tkn,'sid'=>$sid));
				$rurl = $this->config->item('app_url');
				
				if($this->_createWebhook($shop,$tkn)){
					$this->_addCodetotheme($shop,$tkn);
					$this->_authRedirect($rurl,$shop);
					
				}
			}
		}else
			echo "Access denied";
	}
	private function _addCodetotheme($s,$t){
		$theme = $this->_getThemes($s,$t);
		$res = array();
		$tid = $theme[0]['id'];
		$key_icon = "snippets/wishlist-ewe-icon.liquid";
		$val_icon = $this->_getWishicons();
		$d = array('asset'=>array("key"=>$key_icon,"value"=>$val_icon));
		$res['wishlist_icon_creation'] = $this->_addAsset($s,$t,$d,$tid);


		$key_js = "snippets/wishlist-ewe-js.liquid";
		$val_js = $this->_getJscode();		
		$d2 = array('asset'=>array("key"=>$key_js,"value"=>$val_js));
		$res['wishlist_js_creation'] = $this->_addAsset($s,$t,$d2,$tid);

		$res['inject_code_theme'] = $this->_injectCodetheme($s,$t,$tid);


		$date = date('Y-m-d H:i:s'); 
		$dl = array('shop'=>$s,'log_key'=>'code_injection_theme','log_value'=>serialize($res),'date'=>$date);
		$this->appinstall->addLogm($dl);
	}
	private function _getWishicons(){
		$val = '<div style="display:none" class="icon-wishlist {% unless customer %}disabled-wishlist{% endunless %}" data-cid="{{ customer.id }}" data-id="{{product.id}}"><svg id="heart" xmlns="http://www.w3.org/2000/svg" width="22" height="19" viewBox="0 0 22 19"><rect id="heart_background" data-name="heart background" width="22" height="19" fill="rgba(0,0,0,0)"/><g id="heart-2" data-name="heart" transform="translate(0 0.041)"><path id="Shape" d="M11,18.921a2.273,2.273,0,0,1-1.616-.671L1.848,10.7a6.106,6.106,0,0,1-.816-1.019A6.524,6.524,0,0,1,.436,8.5a6.627,6.627,0,0,1-.35-1.294A6.442,6.442,0,0,1,.007,5.855a6.17,6.17,0,0,1,.179-1.2,6.014,6.014,0,0,1,.43-1.174A6.382,6.382,0,0,1,2.3,1.365,5.846,5.846,0,0,1,4.139.338,6.3,6.3,0,0,1,6.192,0a6.724,6.724,0,0,1,1.3.127A6.831,6.831,0,0,1,11,2,6.837,6.837,0,0,1,14.509.129,6.729,6.729,0,0,1,15.806,0,6.314,6.314,0,0,1,17.86.339,5.855,5.855,0,0,1,19.7,1.365a6.386,6.386,0,0,1,1.687,2.111,6.012,6.012,0,0,1,.43,1.173,6.168,6.168,0,0,1,.179,1.2,6.446,6.446,0,0,1-.078,1.356,6.64,6.64,0,0,1-.35,1.3,6.541,6.541,0,0,1-.6,1.184,6.12,6.12,0,0,1-.815,1.02l-7.537,7.545a2.256,2.256,0,0,1-.739.5A2.285,2.285,0,0,1,11,18.921ZM6.242,2a4.232,4.232,0,0,0-1.379.227,3.937,3.937,0,0,0-1.233.689,4.349,4.349,0,0,0-1.15,1.441,4.125,4.125,0,0,0-.415,1.624,4.429,4.429,0,0,0,1.251,3.3l7.537,7.545a.178.178,0,0,0,.292,0l7.537-7.545a4.431,4.431,0,0,0,1.245-3.3,4.149,4.149,0,0,0-.413-1.626,4.33,4.33,0,0,0-1.146-1.441,3.921,3.921,0,0,0-1.229-.688A4.225,4.225,0,0,0,15.762,2,4.644,4.644,0,0,0,12.5,3.362L11,4.869,9.5,3.362A4.637,4.637,0,0,0,6.242,2Z" transform="translate(0 0)" fill="#111"/></g></svg><svg id="heart-active" xmlns="http://www.w3.org/2000/svg" width="22" height="19" viewBox="0 0 22 19"><rect id="heart_background" data-name="heart background" width="22" height="19" fill="rgba(0,0,0,0)"/><g id="heart-2" data-name="heart" transform="translate(0 0.041)"><path id="Intersection_1" data-name="Intersection 1" d="M4596.853-17233.17l-7.538-7.551a4.3,4.3,0,0,1,.316-6.359,4.412,4.412,0,0,1,5.867.445l1.5,1.5,1.5-1.5a4.409,4.409,0,0,1,5.866-.449,4.319,4.319,0,0,1,.316,6.363l-7.538,7.551a.223.223,0,0,1-.147.074A.222.222,0,0,1,4596.853-17233.17Z" transform="translate(-4586 17250)" fill="#111"/></g></svg></div>';
		return $this->_formatFile($val);
	}
	private function _getJscode(){
		$val = "<script type='text/javascript'>\n";
		$val .= "var wishlistBaseurl = 'https://www.shopifycustomsolution.com/wishlist/',\n";
      	$val .= "shopDomain = '{{shop.permanent_domain}}';\n";
      	$val .= "WISHLISTAPP = {\n";

      	////add item to wishlist functions
      	$val .= "addItemtowishlit: function(id,cid,selector){\n";
      	$val .= "$(selector).addClass('active-wishlist'); \n";
      	$val .= "$.ajax({method: 'POST',url: wishlistBaseurl+'wishlist/add',dataType:'jsonp',jsonpCallback: 'jsonCallbackgd',data:{id:id,cid:cid},success:function(data){if($(selector).hasClass('save-tomy-wishlist')){ $(selector).html('Saved');}}});},\n";

      	////onReadyCall function
      	$val .= "onReadyCall: function(){ $(document).find('.icon-wishlist').show();},\n";

      	/////remove item from wishlist
      	$val .= "removeItemtowishlit:function(id,cid,selector){\n";
        $val .= "$(selector).removeClass('active-wishlist');\n";
        $val .= "$.ajax({method: 'POST',url: wishlistBaseurl+'/wishlist/remove',dataType: 'jsonp',jsonpCallback: 'jsonCallbackgd',data: {id:id,cid:cid},success: function(data) { }});\n";
        $val .= " },\n";

        /////////////wishlist page get
        $val .= "getWishlistpage : function(cid) {
          $.ajax({method: 'POST',url: wishlistBaseurl+'/wishlist/getWishlistpage',dataType: 'jsonp',jsonpCallback: 'jsonCallbackgd',data: {cid:cid,shop:shopDomain},success: function(data) {WISHLISTAPP.setwishlistPage(data);}});\n";
        $val .= "},\n";

        ////////////////set wishlist page
        $val .= "setwishlistPage: function(gdata){ \n var products = gdata.data,total = parseInt(gdata.total),html = ''; \n";

        $val .= "if(total > 0){ \n $.each(products,function(i,item){ \n var title = item.title,itemid = item.id, price = item.variants[0].price, varid = item.variants[0].id,image = item.image.src, handle = item.handle; \n";
        $val .= "html += '<div class=\"table-items display-flex\" data-product_id=\"'+itemid+'\">';\n";
        $val .=" html += '<div class=\"item-d\"><div class=\"pro-image\"><a href=\"/products/'+handle+'\"><img src=\"'+image+'\"></a>';\n";
		$val .="html += '</div>';\n";
		$val .="html += '<div class=\"pro-details\">';\n";
		$val .="html += '<span class=\"pro-title\">'+title+'</span></div></div>';\n";
		$val .="html +='<div class=\"price-d\">$ '+price+'</div>';\n";
		$val .="html +='<div class=\"quantity-d\"><div class=\"quantity-selector__wrapper-gd display-flex\"><span class=\"minus quantity-change-input\">-</span><input type=\"text\" name=\"quantity\" value=\"1\" min=\"1\" class=\"QuantityInput\"><span class=\"plus quantity-change-input\">+</span></div></div>';\n";
		$val .="html +='<div class=\"total-d\"><div class=\"total-price\">$ '+price+'</div><form action=\"/cart/add\" method=\"post\"><input name=\"id\" value=\"'+varid+'\" type=\"hidden\"><input name=\"quantity\" value=\"1\" type=\"hidden\"><button name=\"add\" class=\"btn addToCart\">Add to cart</button></form>';\n";
		$val .="html +='<div class=\"delete-wishlist\">Remove <svg xmlns=\"http://www.w3.org/2000/svg\" viewBox=\"0 0 13 15\" class=\"icon-delete\"> <defs><style>.icon-delete .cls-1{fill:#111;}</style></defs><title>Delete</title><g id=\"Layer_2\" data-name=\"Layer 2\"><g id=\"Layer_1-2\" data-name=\"Layer 1\"><path class=\"cls-1\" d=\"M5.22,12.19h-.7a.34.34,0,0,1-.25-.1.35.35,0,0,1-.1-.25V5.51a.35.35,0,0,1,.1-.25.34.34,0,0,1,.25-.1h.7a.34.34,0,0,1,.25.1.35.35,0,0,1,.1.25v6.33a.35.35,0,0,1-.1.25A.34.34,0,0,1,5.22,12.19Zm3.25,0h-.7a.34.34,0,0,1-.25-.1.35.35,0,0,1-.1-.25V5.51a.35.35,0,0,1,.1-.25.34.34,0,0,1,.25-.1h.7a.34.34,0,0,1,.25.1.35.35,0,0,1,.1.25v6.33a.35.35,0,0,1-.1.25A.34.34,0,0,1,8.47,12.19ZM5.14,1.41A.17.17,0,0,0,5,1.49l-.51.85h4L8,1.49a.17.17,0,0,0-.15-.09ZM2.32,3.75v9.67a.18.18,0,0,0,.17.18h8a.18.18,0,0,0,.17-.18V3.75ZM10.68,15H2.32a1.38,1.38,0,0,1-1-.41,1.41,1.41,0,0,1-.41-1V3.75H.35a.34.34,0,0,1-.25-.1A.35.35,0,0,1,0,3.4V3a.7.7,0,0,1,.2-.5A.69.69,0,0,1,.7,2.34H2.86l1-1.66a1.4,1.4,0,0,1,.51-.5A1.39,1.39,0,0,1,5,0H8a1.39,1.39,0,0,1,.69.18,1.4,1.4,0,0,1,.51.5l1,1.66H12.3a.69.69,0,0,1,.49.21A.7.7,0,0,1,13,3V3.4a.35.35,0,0,1-.1.25.34.34,0,0,1-.25.1h-.58v9.84a1.41,1.41,0,0,1-.41,1,1.39,1.39,0,0,1-.44.3A1.37,1.37,0,0,1,10.68,15Z\"/></g></g></svg></div></div></div>';\n";
		$val .="});\n";
		$val .="}else{\n";
		$val .="html = '<div class=\"no-wishlist\">You have no wishlist</div>';\n";
		$val .="}\n";
		$val .="$(document).find('#table-content').html(html);\n";
		$val .="},\n";


		//////////get Wishlist products ids
		$val .= "getWishlistids : function(cid) {\n";
		$val .= "$.ajax({ method: \"POST\",url: wishlistBaseurl+\"wishlist/getwishlistids\",dataType: 'jsonp',jsonpCallback: 'jsonCallbackgdpage',data: {cid:cid},success: function(data) {WISHLISTAPP.activeWishlist(data.pids);}});\n";
		$val .= "},\n";
		$val .= "activeWishlist: function(ids) {\n";
		$val .= "$(document).find('.icon-wishlist').show()\n";
		$val .= "$.each(ids,function(i,item){ $(document).find('.icon-wishlist[data-id=\"'+item.product_id+'\"]').addClass('active-wishlist');});\n";
		$val .= "},\n";
		$val .= "changeQuantity : function(p,s){\n";
		$val .= "var qty = parseInt($(p).find('input').val());\n";
		$val .= "if($(s).hasClass('plus')){qty = qty+1;}\n";
		$val .= "if($(s).hasClass('minus')){qty = qty-1;}if(qty<=0){return false;}\n";
		$val .= "$(p).find('input').val(qty);return qty;\n";
		$val .= "},\n";
		$val .= "getUrlParameter:function(sParam){\n";
		$val .= "var sPageURL=decodeURIComponent(window.location.search.substring(1)),\n";
		$val .= "sURLVariables=sPageURL.split('&'),\n";
		$val .= "sParameterName,\n";
		$val .= "i;\n";
		$val .= "for (i = 0; i < sURLVariables.length; i++) {\n";
		$val .= "sParameterName = sURLVariables[i].split('=');\n";
		$val .= "if (sParameterName[0] === sParam) {\n";
		$val .= "return sParameterName[1] === undefined ? true : sParameterName[1];\n";
		$val .= "}\n";
		$val .= "}\n";
		$val .= "},\n";
		$val .= "changePrice : function(qty,parent){\n";
		$val .= "if(qty>=1)\n";
		$val .= "$(parent).find('form [name=\"quantity\"]').val(qty);\n";
		$val .= "}\n";
		$val .= "}\n";
		$val .= "$(document).ready(function(){\n";
		$val .= "WISHLISTAPP.onReadyCall();\n";
		$val .= "$(document).on('click','.icon-wishlist',function(){\n";
		$val .= "var id = $(this).data('id'),\n";
		$val .= "cid = $(this).data('cid'),\n";
		$val .= "selector = $(this);\n";
		$val .= "if($(this).hasClass('disabled-wishlist')){\n";
		$val .= "alert('Please Login to save your wishlists');\n";
		$val .= "return false;\n";
		$val .= "}\n";
		$val .= "if($(this).hasClass('active-wishlist')){\n";
		$val .= "WISHLISTAPP.removeItemtowishlit(id,cid,selector);\n";
		$val .= "}else{\n";
		$val .= "WISHLISTAPP.addItemtowishlit(id,cid,selector);\n";
		$val .= "}\n";
		$val .= "});\n";
		$val .= "$(document).on('click','.quantity-change-input',function(){\n";
		$val .= "var parent = $(this).closest('.quantity-selector__wrapper-gd'),\n";
		$val .= "selector = $(this);\n";
		$val .= "var qty = WISHLISTAPP.changeQuantity(parent,selector);\n";
		$val .= "WISHLISTAPP.changePrice(qty,$(this).closest('.table-items'));\n";
		$val .= "});\n";
		$val .= "$(document).on('click','.delete-wishlist',function(){\n";
		$val .= "var parent = $(this).closest('.table-items'),selector = $(this),id = $(parent).data('product_id'),cid = $('#table-content').data('customer');\n";
		$val .= "WISHLISTAPP.removeItemtowishlit(id,cid,selector);\n";
		$val .= "$(parent).fadeOut();\n";
		$val .= "});\n";
		$val .= "{% if customer %}WISHLISTAPP.getWishlistids('{{customer.id}}');{% endif %}\n";
		$val .= "{% if template contains 'wishlist' and customer %}\n";
		$val .= "WISHLISTAPP.getWishlistpage('{{customer.id}}');\n";
		$val .= "{% endif %}\n";
		$val .= "{% if customer and template contains 'wishlist-share' %}\n";
		$val .= "WISHLISTAPP.getWishlistshare('{{customer.email}}');\n";
		$val .= "{% endif %}\n";

		$val .= "</script>";
		return $this->_formatFile($val);
	}
	private function _formatFile($newval){
        $stringgd = preg_replace('#\n+#','\\n',trim($newval));
        return str_replace('"', '\"', $stringgd);
    }
    private function _injectCodetheme($s,$tkn,$tid){
           $key='layout/theme.liquid';
           $themefile = $this->_getAsset($s,$tkn,$tid,$key);

           $themecode = $themefile['value'];
           $themefilearr = explode('</body>', $themecode);
           if(strpos($themefilearr[0], 'wishlist-ewe-js') !== false){
                return true;
           }else{
            $newval = $themefilearr[0];
            $newval .= "{% include 'wishlist-ewe-js' %}";
            $newval .="</body>";
            $newval .= $themefilearr[1];
            $dat=array('asset'=>array('key'=>'layout/theme.liquid','value'=>$this->_formatFile($newval)));
            return $this->_addAsset($s,$tkn,$tid,$dat);
           }
    }
	private function _getAsset($s,$t,$id,$key){
		return $this->shopify->call('GET',"/admin/themes/$id/assets.json?asset[key]=$key&theme_id=$id", array(),$s,$t);
	}
	private function _addAsset($s,$t,$d,$id){
		return $this->shopify->call('PUT',"/admin/themes/$id/assets.json", $d,$s,$t);
	}
	private function _getThemes($s,$t){
		return $this->shopify->call('GET',"/admin/themes.json", array('role'=>'main'),$s,$t);
	}
	private function _addLog($d){
       $this->appinstall->addLogm($d);
    }

    private function _createWebhook($s,$t){
    	$date = date('Y-m-d H:i:s');
		$res = $this->shopify->call('POST',"/admin/webhooks.json", array('webhook'=> array('topic'=>'app/uninstalled','address'=>base_url().'wishlist/appuninstall','format'=>'json')),$s,$t);
		$this->_addLog(array('shop'=>$s,
							'log_key'=>'register_webhook_uninstall',
							'log_value'=>serialize(array('webhook'=>$res)),
							'date'=>$date
							));
		return true;
	}

	private function _getapiKeys(){
		$config=get_config();
		return array('api'=>$config['api_key'],'secret'=>$config['secret_key'],'scope'=>$config['shopify_scope'],'redirect_url'=>$config['redirect_url']);
	}
	private function _authRedirect($redirecurl,$shop){
		$apiinfo=$this->_getapiKeys();
			$shop_domain=$shop;
			$authorizeurl=$this->shopify->getAuthorizeUrl($apiinfo['scope'], $redirecurl,$shop_domain,$apiinfo['api']);
			redirect($authorizeurl);
	}
	private function _checkrequestShopify($code,$hmac,$shop,$timestamp,$secret){
		$part1 ='';
    	if ($code != '')
      		$part1 = "code=" . $code;
      	$part1 .= "&shop=" . $shop . "&timestamp=" . $timestamp;
    	return (hash_hmac('sha256', $part1, $secret,false) === $hmac);
	}

	public function appInstall(){
		$s=$this->input->get_post('shop');
		if($s){
			$apiinfo=$this->_getapiKeys();
			if(!$this->_checkIfappexist($s)){
				$authorizeurl=$this->shopify->getAuthorizeUrl($apiinfo['scope'], $apiinfo['redirect_url'],$s,$apiinfo['api']);
				redirect($authorizeurl);
			}else{
				redirect(base_url().'login?shop='.$s,'refresh');
			}
		}else
			echo "Access denied";
	}
	private function _checkIfappexist($s){
		return $this->appinstall->checkifInstalled($s);
	}
}

//https://www.shopifycustomsolution.com/wishlist/install/appInstall?shop=developertestgd.myshopify.com