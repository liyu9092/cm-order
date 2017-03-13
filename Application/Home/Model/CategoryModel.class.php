<?php
namespace Home\Model;
use Think\Model;

class CategoryModel{

    private static $stylistCats = array(
        1 => "高级设计师",
        2 => "资深设计师",
        3 => "设计总监",
        4 => "美发大师",
    );
    
    private static $salonCats = array(
        1 => '店铺类型A',
        2 => '店铺类型B',
        3 => '店铺类型C',
        4 => '店铺类型D',
    );
    static $priceRanges = array(
        1 => array(200, 500),
        2 => array(200, 1000),
        3 => array(200, 3000),
        4 => array(200, 999999),
    );
    static $stylistPriceRanges = array(
        1 => array(200, 500),
        2 => array(300, 1000),
        3 => array(501, 1500),
        4 => array(1001, 999999),
    );
    public function __construct()
    {
        
    }
    
    /**
     * 获取造型师类别
     * @return array
     */
    public function getStylistCats()
    {
        return self::$stylistCats;
    }
    
    /**
     * 根据价格获取店铺类型
     * @param type $price
     * @return array
     */
    public function getSalonCatsByPrice($price)
    {
        $cats = array();
        $price = doubleval($price);
        foreach(self::$priceRanges as $catid => $range)
        {
            if($range[0]<= $price && $price <= $range[1])
                $cats[] = $catid;
        }
        return $cats;
    }
    
    /**
     * 根据价格获取造型师类型
     * @param type $price
     * @return array
     */
    public function getStylistCatsByPrice($price)
    {
        $cats = array();
        $price = doubleval($price);
        foreach(self::$stylistPriceRanges as $catid => $range)
        {
            if($price>= $range[0] && $price <= $range[1])
                $cats[] = $catid;
        }
        return $cats;
    }
    
    /**
     * 根据造型师类型id获取类型名称
     * @param type $catid
     * @return string
     */
    public function getStylistCatName($catid)
    {
        $catid = intval($catid);
        return isset(self::$stylistCats[$catid]) ? self::$stylistCats[$catid] : '';
    }
    
    public function getpriceRangeByStylistCat($catid)
    {
        return isset(self::$stylistPriceRanges[$catid]) ? self::$stylistPriceRanges[$catid] : false;
    }
    
}