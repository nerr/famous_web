<?php
class goods
{
    private $csvFilePath;
    private $maria;

    function __construct($config)
    {
        $this->maria = new mysqli(
            $config['db']['host'],
            $config['db']['user'],
            $config['db']['pass'],
            $config['db']['base']
        );
        $this->csvFilePath = $config['csv'];
    }

    private function loadCsv($csvpath)
    {
        $fileHandel = fopen($this->csvFilePath, 'r');
        while($data = fgetcsv($fileHandel))
        {
            $goods_list[] = $data;
        }
        fclose($fileHandel);

        return $goods_list;
    }


    private function filtrateCsv()
    {
        $csvData = $this->loadCsv($this->csvFilePath);
        foreach($csvData as $data)
        {
            if($data[0] > 0)
            {
                $cleanData[] = $data;
            }
        }

        return $cleanData;
    }

    function goodsInfo2Db()
    {
        $data = $this->filtrateCsv();
        $i = 0;

        foreach($data as $v)
        {
            $goods_sn = substr($v[1], 0, -1);

            $sql = "insert into `goods` (`goods_num`, `goods_sn_size`, `goods_sn`";
            $sql.= ", `goods_color`, `goods_price`, `goods_sale`, `goods_ material`";
            $sql.= ", `goods_desc`, `memo`) values (";
            $sql.= $v[0].",'".$v[1]."','".$goods_sn."','".$v[3]."',".$v[8].",";
            $sql.= $v[9].",'".$v[12]."', '".$v[2]."','".$v[13]."')";

            if($result = $this->maria->query($sql))
                $i++;
            else
                var_dump($result->error);
        }
        echo 'Inserted ['.$i.'] rows'."\r\n";
    }

    function loadGoodsFromDb()
    {
        $data = array();
        $sql = 'select * from goods';
        if ($result = $this->maria->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $data[] = $row;
            }
            $result->free();
        }
        return $data;
    }


    function getBaiduUrl($good_sn)
    {
        $baiduSearchUrl = 'http://www.baidu.com/s?wd=site:';
        $baiduSearchUrl.= 'archive-shop.vip.com';
        $baiduSearchUrl.= '%20';
        $baiduSearchUrl.= $good_sn;
        $baiduResultHtml = file_get_contents($baiduSearchUrl);
        $preg = '/href="(.*?)"/is';
        preg_match_all($preg, $baiduResultHtml, $match);

        return $match[1][28];
    }


    function getHtmlFromVip()
    {
        $data = $this->loadGoodsFromDb();
        $i = 0;
        foreach($data as $v)
        {
            $good_num = $v['goods_num'];
            $baiduResultUrl = $this->getBaiduUrl($v['goods_sn']);
            $targetHtml = file_get_contents($baiduResultUrl);
            $preg = '/data-original="http(.*?)"/is';
            preg_match_all($preg, $targetHtml, $match);

            $imgNum = count($match[1]);
            if($imgNum > 0)
            {
                foreach ($match[1] as $img) {
                    $sql = "insert into `goods_img` (`goods_num`, `img_url`) values (";
                    $sql.= $good_num.", 'http".$img."')";

                    if($result = $this->maria->query($sql))
                        $i++;
                    else
                        var_dump($result->error);
                }
            }

            echo '['.$good_num.'] seek out ['.$imgNum."] imgs \r\n";
            sleep(30);
        }
        echo 'Inserted ['.$i.'] rows'."\r\n";
    }


    function updateUrlInDb()
    {
        $sql = "select * from goods_img";
        if ($result = $this->maria->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                $newUrl = str_replace("_95x120_90", "", $row['img_url']);
                $sql = 'update `goods_img` set `img_url_update` = \''.$newUrl.'\' where `id`='.$row['id'];
                //var_dump($sql);
                $this->maria->query($sql);
            }
            $result->free();
        }
    }


    private function makeDir($dirname)
    {
        $baseDir = getcwd();
        $baseDir .= '/img/';

        $fullDir = $baseDir.$dirname.'/';
        var_dump($fullDir);
        if(is_dir($fullDir))
        {
            var_dump('alread there');
            return true;
        }
        else {
            if(mkdir($fullDir, 0777))
                return true;
            else
                return false;
        }
    }

    function downloadImg()
    {
        $baseDir = getcwd();
        $baseDir .= '/img/';
        $sql = "select * from goods_img";
        if ($result = $this->maria->query($sql)) {
            while ($row = $result->fetch_assoc()) {
                if($this->makeDir($row['goods_num']))
                {
                    $this->downloadImage($row['img_url_update'], $baseDir.$row['goods_num'].'/'.$this->getRandChar(8));
                }
                else
                {
                    var_dump('dir no readly!');
                }
            }
            $result->free();
        }
    }

    function getImage($url, $save_dir='', $filename='', $type=0)
    {
        if(trim($url)==''){
            return array('file_name'=>'','save_path'=>'','error'=>1);
        }
        if(trim($save_dir)==''){
            $save_dir='./';
        }
        if(trim($filename)==''){//保存文件名
            $ext=strrchr($url,'.');
            if($ext!='.gif'&&$ext!='.jpg'){
                return array('file_name'=>'','save_path'=>'','error'=>3);
            }
            $filename=time().$ext;
        }
        if(0!==strrpos($save_dir,'/')){
            $save_dir.='/';
        }
        //创建保存目录
        if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
            return array('file_name'=>'','save_path'=>'','error'=>5);
        }
        //获取远程文件所采用的方法
        if($type){
            $ch=curl_init();
            $timeout=5;
            curl_setopt($ch,CURLOPT_URL,$url);
            curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
            curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
            $img=curl_exec($ch);
            curl_close($ch);
        }else{
            ob_start();
            readfile($url);
            $img=ob_get_contents();
            ob_end_clean();
        }
        //$size=strlen($img);
        //文件大小
        $fp2=@fopen($save_dir.$filename,'a');
        fwrite($fp2,$img);
        fclose($fp2);
        unset($img,$url);
        return array('file_name'=>$filename,'save_path'=>$save_dir.$filename,'error'=>0);
    }

    function downloadImage($url, $filepath)
    {
        //服务器返回的头信息
        $responseHeaders = array();
        //原始图片名
        $originalfilename = '';
        //图片的后缀名
        $ext = '';
        $ch = curl_init($url);
        //设置curl_exec返回的值包含Http头
        curl_setopt($ch, CURLOPT_HEADER, 1);
        //设置curl_exec返回的值包含Http内容
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        //设置抓取跳转（http 301，302）后的页面
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        //设置最多的HTTP重定向的数量
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);

        //服务器返回的数据（包括http头信息和内容）
        $html = curl_exec($ch);
        //获取此次抓取的相关信息
        $httpinfo = curl_getinfo($ch);
        curl_close($ch);
        if ($html !== false) {
            //分离response的header和body，由于服务器可能使用了302跳转，所以此处需要将字符串分离为 2+跳转次数 个子串
            $httpArr = explode("\r\n\r\n", $html, 2 + $httpinfo['redirect_count']);
            //倒数第二段是服务器最后一次response的http头
            $header = $httpArr[count($httpArr) - 2];
            //倒数第一段是服务器最后一次response的内容
            $body = $httpArr[count($httpArr) - 1];
            $header.="\r\n";

            //获取最后一次response的header信息
            preg_match_all('/([a-z0-9-_]+):\s*([^\r\n]+)\r\n/i', $header, $matches);
            if (!empty($matches) && count($matches) == 3 && !empty($matches[1]) && !empty($matches[1])) {
                for ($i = 0; $i < count($matches[1]); $i++) {
                    if (array_key_exists($i, $matches[2])) {
                        $responseHeaders[$matches[1][$i]] = $matches[2][$i];
                    }
                }
            }
            //获取图片后缀名
            if (0 < preg_match('{(?:[^\/\\\\]+)\.(jpg|jpeg|gif|png|bmp)$}i', $url, $matches)) {
                $originalfilename = $matches[0];
                $ext = $matches[1];
            } else {
                if (array_key_exists('Content-Type', $responseHeaders)) {
                    if (0 < preg_match('{image/(\w+)}i', $responseHeaders['Content-Type'], $extmatches)) {
                        $ext = $extmatches[1];
                    }
                }
            }
            var_dump($ext);
            //保存文件
            if (!empty($ext)) {
                $filepath .= ".$ext";
                //如果目录不存在，则先要创建目录
                //CFiles::createDirectory(dirname($filepath));
                $this->makeDir(dirname($filepath));
                $local_file = fopen($filepath, 'w');
                if (false !== $local_file) {
                    if (false !== fwrite($local_file, $body)) {
                        fclose($local_file);
                        $sizeinfo = getimagesize($filepath);
                        return array('filepath' => realpath($filepath), 'width' => $sizeinfo[0], 'height' => $sizeinfo[1], 'orginalfilename' => $originalfilename, 'filename' => pathinfo($filepath, PATHINFO_BASENAME));
                    }
                }
            }
        }
        return false;
    }


    function getRandChar($length){
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol)-1;

        for($i=0;$i<$length;$i++){
        $str.=$strPol[rand(0,$max)];//rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

}


// $baseDir = getcwd();
// $baseDir .= '/img/210';
// //
// $goods = new goods();
// $goods->downloadImg();
//$goods->downloadImage('http://a.vpimg2.com/upload/merchandise/100890/LILY-112310A20273-1_2.jpg', $baseDir);
// $goods->getHtmlFromVip();
