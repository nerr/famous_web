<?php
require('goods.class.php');
$config = include('config.php');
$goods = new goods($config);


// $res = $goods->getInvertory();
// var_dump($res[106]);

$res = $goods->goodsData2Csv();

//var_dump($res);
