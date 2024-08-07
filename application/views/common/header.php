<?php $config=get_config();
if($shop=='' || !isset($shop)){$shop = $this->session->userdata('shop');}
?>
<!DOCTYPE html>
<html lang="">
<head>
<meta charset="utf-8">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Home | APPS</title>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css" />
<script src="https://cdn.shopify.com/s/assets/external/app.js"></script>
<script type="text/javascript">
ShopifyApp.init({
	apiKey: "<?=@$config['api_key']?>",
	shopOrigin: "https://<?=@$shop?>",
	forceRedirect: true,
	debug: true
});
ShopifyApp.Bar.loadingOn()
ShopifyApp.ready(function(){
	ShopifyApp.Bar.loadingOff();
});
</script>
</head>
<body data-method="<?=@$method?>" data-class="<?=@$class?>">