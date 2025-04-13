<?php
$url = "http://localhost/MDM/api/product"; // modifier le produit 1
$data = array(
    'name' => 'MOD',
    'description' => 'Moutarde de Dijon',
    'price' => 225,
    'category' => 1,
    'statut' => 2,
    'supplier' => 3,
    'purchase' => '20224-04_01 10:40:00',
    'expire' => '20235-04-01 10:40:00'
);
$ch = curl_init($url)
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
$response = curl_exec($ch);
var_dump($response);
if (!$response) {
    return false;
}
