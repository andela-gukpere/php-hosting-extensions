<?php
include("simple_html_dom.php");

if(!isset($_GET['go']))exit(json_encode(array("time" => date("U"))));

function bcorp() {
	$page = intval($_GET["page"]);
	$url = "https://www.bcorporation.net/community/find-a-b-corp?page=".$page;
	$html = file_get_html($url);
	$companies = $html->find(".view-content>.company-logo");
	$result = array();
	for($i = 0; $i < count($companies); $i++) {
		$a = $companies[$i]->find("a", 0);
		array_push($result,_bcorp($a->href));
	}
	echo json_encode($result);
	exit();
}

function _bcorp($url) {
	$slug = explode("/", $url);
	$url = "https://www.bcorporation.net".$url;
	$html = file_get_html($url);
	$body = $html->find("#block-system-main", 0);

	$name = $html->find("h1#page-title",0)->plaintext;
	$a = $body->find(".company-desc-inner>a", 0)->href;


	$info = $html->find(".company-desc-inner>h3", 0)->plaintext;
	$city = $html->find(".company-desc-inner>p", 1)->plaintext;
	$country = $html->find(".company-desc-inner>p", 2)->plaintext;
	$logo = @$html->find(".image-style-company-logo-full", 0)->src;
	return array(
		"id" => $slug[2],
		"name"=> trim($name),
		"bcopr_url" => trim($url),
		"website"=> $a,
		"info" => trim($info),
		"city" => trim($city),
		"country" => trim($country),
		"logo" =>  trim($logo)
		);
}
bcorp();
?>