<?php


interface  AgreementHashinterface{
    //添加节点
    function addItem($ip);

    function hashIp($str);
}

/**
 * 一直性hash
 */
class AgreementHash implements AgreementHashinterface{

    //虚拟节点数
    public $_virtualCounts;

    //虚拟节点集合
    public $_circleItems = array();

    //实际节点
    public $_items = array();

    //实际节点数
    private $_itemsCount = 0;


    //是否需要排序

    //是否需要排序
    private $needSort = false;

    function __construct($_virtualCounts = 64) {
        $this->_virtualCounts = $_virtualCounts;
    }

    /**添加节点
     * @param $ip
     * @throws Flexihash_Exception
     */
    function addItem($ip) {
        if(!$ip) {
            echo("Target ".$ip." already exists.");
            exit;
        }
        //实际节点下面的构建虚拟节点 解决hash分布不均匀
        $this->_items[$ip] = [];
        for($i = 0; $i < $this->_virtualCounts; $i++) {
            //侯建虚拟节点hash值
            $hash = $this->hashIp($ip."_".$i);
            //添加虚拟节点
            $this->_circleItems[$hash] = $ip;
            //世界节点对应添加虚拟节点值
            $this->_items[$ip][] = $hash;
        }
        $this->needSort = false;
        //实际节点--
        $this->_itemsCount++;
    }


    function getNode($ip) {
        $hashIp = $this->hashIp($ip);

        if(!$this->needSort) {
            $this->sortItem();
        }

        if(!$this->_items) {
            echo("Target  has no.");
            exit;
        }
        //如果找到一个比他大的就返回
        foreach($this->_circleItems as $k => $val) {
            if($hashIp <= $k) {
                return $val;
            }
        }

        return end($this->_circleItems);

    }

    /**删除节点
     * @param $ip
     * @throws Exception
     */
    function deleteNode($ip) {
        if(!isset($this->_items[$ip])) {
            throw new Exception("item is not exists");
        }
        //删除虚拟节点
        foreach($this->_items[$ip] as $val) {
            unset($this->_virtualCounts[$val]);
        }
        //删除节点
        unset($this->_items[$ip]);
        $this->_itemsCount--;
    }


    function sortItem() {
        ksort($this->_circleItems);
        $this->needSort = true;
    }


    function hashIp($str = "") {

        $hash = 0;
        $s = md5($str); //相比其它版本，进行了md5加密
        $seed = 5;
        $len = 32;//加密后长度32
        for($i = 0; $i < $len; $i++) {
            // $hash =(hash << 5) + hash 相当于 hash * 33
//            $hash = sprintf("%u", $hash * 33) + ord($s{$i});
//            $hash = ($hash * 33 + ord($s{$i})) & 0x7FFFFFFF;
            $hash = ($hash << $seed) + $hash + ord($s{$i});
        }
        return $hash & 0x7FFFFFFF;

    }
}

$hash = new AgreementHash();
$hash->addItem("127.0.0.136");


for($i = 0; $i < 5; $i++) {
    $ss = rand(100, 256);
    $hash->addItem("127.0.0.".$ss);
}
$box = array();
for($i = 0; $i < 100; $i++) {
    $s = rand(1, 256);
    $ip = $hash->getNode("127.0.0.".$s);
    if(!isset($box[$ip])) {
        $box[$ip] = 1;
    } else {
        $box[$ip] = $box[$ip] + 1;
    }
}

print_r($box);