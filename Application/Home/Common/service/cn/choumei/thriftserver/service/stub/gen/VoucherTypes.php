<?php
namespace cn\choumei\thriftserver\service\stub\gen;

/**
 * Autogenerated by Thrift Compiler (0.9.2)
 *
 * DO NOT EDIT UNLESS YOU ARE SURE THAT YOU KNOW WHAT YOU ARE DOING
 *  @generated
 */
use Thrift\Base\TBase;
use Thrift\Type\TType;
use Thrift\Type\TMessageType;
use Thrift\Exception\TException;
use Thrift\Exception\TProtocolException;
use Thrift\Protocol\TProtocol;
use Thrift\Protocol\TBinaryProtocolAccelerated;
use Thrift\Exception\TApplicationException;


/**
 * ******************************
 *  方法入参
 * *****************************
 */
class VoucherParam extends TBase {
  static $_TSPEC;

  /**
   * @var int
   */
  public $vId = null;
  /**
   * @var int
   */
  public $vcId = null;
  /**
   * @var string
   */
  public $vcSn = null;
  /**
   * @var string
   */
  public $vcTitle = null;
  /**
   * @var string
   */
  public $vSn = null;
  /**
   * @var int
   */
  public $vUserId = null;
  /**
   * @var string
   */
  public $vMobilephone = null;
  /**
   * @var string
   */
  public $vOrderSn = null;
  /**
   * @var int
   */
  public $vSalonId = null;
  /**
   * @var string
   */
  public $vSalonName = null;
  /**
   * @var int
   */
  public $vItemId = null;
  /**
   * @var string
   */
  public $vItemName = null;
  /**
   * @var int
   */
  public $vUseMoney = null;
  /**
   * @var string
   */
  public $vUseItemTypes = null;
  /**
   * @var string
   */
  public $vUseLimitTypes = null;
  /**
   * @var int
   */
  public $vUseNeedMoney = null;
  /**
   * @var int
   */
  public $vUseStart = null;
  /**
   * @var int
   */
  public $vUseEnd = null;
  /**
   * @var int
   */
  public $vUseTime = null;
  /**
   * @var int
   */
  public $vAddTime = null;
  /**
   * @var int
   */
  public $vStatus = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'vId',
          'type' => TType::I64,
          ),
        2 => array(
          'var' => 'vcId',
          'type' => TType::I64,
          ),
        3 => array(
          'var' => 'vcSn',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'vcTitle',
          'type' => TType::STRING,
          ),
        5 => array(
          'var' => 'vSn',
          'type' => TType::STRING,
          ),
        6 => array(
          'var' => 'vUserId',
          'type' => TType::I64,
          ),
        7 => array(
          'var' => 'vMobilephone',
          'type' => TType::STRING,
          ),
        8 => array(
          'var' => 'vOrderSn',
          'type' => TType::STRING,
          ),
        9 => array(
          'var' => 'vSalonId',
          'type' => TType::I64,
          ),
        10 => array(
          'var' => 'vSalonName',
          'type' => TType::STRING,
          ),
        11 => array(
          'var' => 'vItemId',
          'type' => TType::I64,
          ),
        12 => array(
          'var' => 'vItemName',
          'type' => TType::STRING,
          ),
        13 => array(
          'var' => 'vUseMoney',
          'type' => TType::I64,
          ),
        14 => array(
          'var' => 'vUseItemTypes',
          'type' => TType::STRING,
          ),
        15 => array(
          'var' => 'vUseLimitTypes',
          'type' => TType::STRING,
          ),
        16 => array(
          'var' => 'vUseNeedMoney',
          'type' => TType::I64,
          ),
        17 => array(
          'var' => 'vUseStart',
          'type' => TType::I64,
          ),
        18 => array(
          'var' => 'vUseEnd',
          'type' => TType::I64,
          ),
        19 => array(
          'var' => 'vUseTime',
          'type' => TType::I64,
          ),
        20 => array(
          'var' => 'vAddTime',
          'type' => TType::I64,
          ),
        21 => array(
          'var' => 'vStatus',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VoucherParam';
  }

  public function read($input)
  {
    return $this->_read('VoucherParam', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VoucherParam', self::$_TSPEC, $output);
  }

}

class VoucherConfParam extends TBase {
  static $_TSPEC;

  /**
   * @var int
   */
  public $vcId = null;
  /**
   * @var string
   */
  public $vcTitle = null;
  /**
   * @var string
   */
  public $vcSn = null;
  /**
   * @var string
   */
  public $vcRemark = null;
  /**
   * @var int
   */
  public $vcStart = null;
  /**
   * @var int
   */
  public $vcEnd = null;
  /**
   * @var int
   */
  public $useMoney = null;
  /**
   * @var int
   */
  public $useTotalNum = null;
  /**
   * @var string
   */
  public $useItemTypes = null;
  /**
   * @var string
   */
  public $useLimitTypes = null;
  /**
   * @var int
   */
  public $useNeedMoney = null;
  /**
   * @var int
   */
  public $useStart = null;
  /**
   * @var int
   */
  public $useEnd = null;
  /**
   * @var string
   */
  public $getTypes = null;
  /**
   * @var string
   */
  public $getItemTypes = null;
  /**
   * @var int
   */
  public $getCodeType = null;
  /**
   * @var string
   */
  public $getCode = null;
  /**
   * @var int
   */
  public $getNumMax = null;
  /**
   * @var int
   */
  public $getStart = null;
  /**
   * @var int
   */
  public $getEnd = null;
  /**
   * @var int
   */
  public $status = null;
  /**
   * @var int
   */
  public $getNeedMoney = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'vcId',
          'type' => TType::I64,
          ),
        2 => array(
          'var' => 'vcTitle',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'vcSn',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'vcRemark',
          'type' => TType::STRING,
          ),
        5 => array(
          'var' => 'vcStart',
          'type' => TType::I64,
          ),
        6 => array(
          'var' => 'vcEnd',
          'type' => TType::I64,
          ),
        7 => array(
          'var' => 'useMoney',
          'type' => TType::I64,
          ),
        8 => array(
          'var' => 'useTotalNum',
          'type' => TType::I64,
          ),
        9 => array(
          'var' => 'useItemTypes',
          'type' => TType::STRING,
          ),
        10 => array(
          'var' => 'useLimitTypes',
          'type' => TType::STRING,
          ),
        11 => array(
          'var' => 'useNeedMoney',
          'type' => TType::I64,
          ),
        12 => array(
          'var' => 'useStart',
          'type' => TType::I64,
          ),
        13 => array(
          'var' => 'useEnd',
          'type' => TType::I64,
          ),
        14 => array(
          'var' => 'getTypes',
          'type' => TType::STRING,
          ),
        15 => array(
          'var' => 'getItemTypes',
          'type' => TType::STRING,
          ),
        16 => array(
          'var' => 'getCodeType',
          'type' => TType::I32,
          ),
        17 => array(
          'var' => 'getCode',
          'type' => TType::STRING,
          ),
        18 => array(
          'var' => 'getNumMax',
          'type' => TType::I64,
          ),
        19 => array(
          'var' => 'getStart',
          'type' => TType::I64,
          ),
        20 => array(
          'var' => 'getEnd',
          'type' => TType::I64,
          ),
        21 => array(
          'var' => 'status',
          'type' => TType::I32,
          ),
        22 => array(
          'var' => 'getNeedMoney',
          'type' => TType::I64,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VoucherConfParam';
  }

  public function read($input)
  {
    return $this->_read('VoucherConfParam', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VoucherConfParam', self::$_TSPEC, $output);
  }

}

class VoucherTrendParam extends TBase {
  static $_TSPEC;

  /**
   * @var int
   */
  public $vBindId = null;
  /**
   * @var int
   */
  public $vId = null;
  /**
   * @var string
   */
  public $vSn = null;
  /**
   * @var int
   */
  public $vUserId = null;
  /**
   * @var string
   */
  public $vOrderSn = null;
  /**
   * @var int
   */
  public $vAddTime = null;
  /**
   * @var int
   */
  public $vStatus = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'vBindId',
          'type' => TType::I64,
          ),
        2 => array(
          'var' => 'vId',
          'type' => TType::I64,
          ),
        3 => array(
          'var' => 'vSn',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'vUserId',
          'type' => TType::I64,
          ),
        5 => array(
          'var' => 'vOrderSn',
          'type' => TType::STRING,
          ),
        6 => array(
          'var' => 'vAddTime',
          'type' => TType::I64,
          ),
        7 => array(
          'var' => 'vStatus',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VoucherTrendParam';
  }

  public function read($input)
  {
    return $this->_read('VoucherTrendParam', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VoucherTrendParam', self::$_TSPEC, $output);
  }

}

class BindOrderParam extends TBase {
  static $_TSPEC;

  /**
   * @var int
   */
  public $vId = null;
  /**
   * @var string
   */
  public $orderSn = null;
  /**
   * @var int
   */
  public $salonId = null;
  /**
   * @var string
   */
  public $salonName = null;
  /**
   * @var int
   */
  public $itemId = null;
  /**
   * @var string
   */
  public $itemName = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'vId',
          'type' => TType::I64,
          ),
        2 => array(
          'var' => 'orderSn',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'salonId',
          'type' => TType::I64,
          ),
        4 => array(
          'var' => 'salonName',
          'type' => TType::STRING,
          ),
        5 => array(
          'var' => 'itemId',
          'type' => TType::I64,
          ),
        6 => array(
          'var' => 'itemName',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'BindOrderParam';
  }

  public function read($input)
  {
    return $this->_read('BindOrderParam', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('BindOrderParam', self::$_TSPEC, $output);
  }

}

/**
 * ******************************
 *  方法出参（返回数据实体）
 * *****************************
 */
class VoucherThrift extends TBase {
  static $_TSPEC;

  /**
   * @var int
   */
  public $vId = null;
  /**
   * @var int
   */
  public $vcId = null;
  /**
   * @var string
   */
  public $vcSn = null;
  /**
   * @var string
   */
  public $vcTitle = null;
  /**
   * @var string
   */
  public $vSn = null;
  /**
   * @var int
   */
  public $vUserId = null;
  /**
   * @var string
   */
  public $vMobilephone = null;
  /**
   * @var string
   */
  public $vOrderSn = null;
  /**
   * @var int
   */
  public $vSalonId = null;
  /**
   * @var string
   */
  public $vSalonName = null;
  /**
   * @var int
   */
  public $vItemId = null;
  /**
   * @var string
   */
  public $vItemName = null;
  /**
   * @var int
   */
  public $vUseMoney = null;
  /**
   * @var string
   */
  public $vUseItemTypes = null;
  /**
   * @var string
   */
  public $vUseLimitTypes = null;
  /**
   * @var int
   */
  public $vUseNeedMoney = null;
  /**
   * @var int
   */
  public $vUseStart = null;
  /**
   * @var int
   */
  public $vUseEnd = null;
  /**
   * @var int
   */
  public $vUseTime = null;
  /**
   * @var int
   */
  public $vAddTime = null;
  /**
   * @var int
   */
  public $vStatus = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'vId',
          'type' => TType::I64,
          ),
        2 => array(
          'var' => 'vcId',
          'type' => TType::I64,
          ),
        3 => array(
          'var' => 'vcSn',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'vcTitle',
          'type' => TType::STRING,
          ),
        5 => array(
          'var' => 'vSn',
          'type' => TType::STRING,
          ),
        6 => array(
          'var' => 'vUserId',
          'type' => TType::I64,
          ),
        7 => array(
          'var' => 'vMobilephone',
          'type' => TType::STRING,
          ),
        8 => array(
          'var' => 'vOrderSn',
          'type' => TType::STRING,
          ),
        9 => array(
          'var' => 'vSalonId',
          'type' => TType::I64,
          ),
        10 => array(
          'var' => 'vSalonName',
          'type' => TType::STRING,
          ),
        11 => array(
          'var' => 'vItemId',
          'type' => TType::I64,
          ),
        12 => array(
          'var' => 'vItemName',
          'type' => TType::STRING,
          ),
        13 => array(
          'var' => 'vUseMoney',
          'type' => TType::I64,
          ),
        14 => array(
          'var' => 'vUseItemTypes',
          'type' => TType::STRING,
          ),
        15 => array(
          'var' => 'vUseLimitTypes',
          'type' => TType::STRING,
          ),
        16 => array(
          'var' => 'vUseNeedMoney',
          'type' => TType::I64,
          ),
        17 => array(
          'var' => 'vUseStart',
          'type' => TType::I64,
          ),
        18 => array(
          'var' => 'vUseEnd',
          'type' => TType::I64,
          ),
        19 => array(
          'var' => 'vUseTime',
          'type' => TType::I64,
          ),
        20 => array(
          'var' => 'vAddTime',
          'type' => TType::I64,
          ),
        21 => array(
          'var' => 'vStatus',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VoucherThrift';
  }

  public function read($input)
  {
    return $this->_read('VoucherThrift', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VoucherThrift', self::$_TSPEC, $output);
  }

}

class VoucherConfThrift extends TBase {
  static $_TSPEC;

  /**
   * @var int
   */
  public $vcId = null;
  /**
   * @var string
   */
  public $vcTitle = null;
  /**
   * @var string
   */
  public $vcSn = null;
  /**
   * @var string
   */
  public $vcRemark = null;
  /**
   * @var int
   */
  public $vcStart = null;
  /**
   * @var int
   */
  public $vcEnd = null;
  /**
   * @var int
   */
  public $useMoney = null;
  /**
   * @var int
   */
  public $useTotalNum = null;
  /**
   * @var string
   */
  public $useItemTypes = null;
  /**
   * @var string
   */
  public $useLimitTypes = null;
  /**
   * @var int
   */
  public $useNeedMoney = null;
  /**
   * @var int
   */
  public $useStart = null;
  /**
   * @var int
   */
  public $useEnd = null;
  /**
   * @var string
   */
  public $getTypes = null;
  /**
   * @var string
   */
  public $getItemTypes = null;
  /**
   * @var int
   */
  public $getCodeType = null;
  /**
   * @var string
   */
  public $getCode = null;
  /**
   * @var int
   */
  public $getNumMax = null;
  /**
   * @var int
   */
  public $getStart = null;
  /**
   * @var int
   */
  public $getEnd = null;
  /**
   * @var int
   */
  public $status = null;
  /**
   * @var int
   */
  public $getNeedMoney = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'vcId',
          'type' => TType::I64,
          ),
        2 => array(
          'var' => 'vcTitle',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'vcSn',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'vcRemark',
          'type' => TType::STRING,
          ),
        5 => array(
          'var' => 'vcStart',
          'type' => TType::I64,
          ),
        6 => array(
          'var' => 'vcEnd',
          'type' => TType::I64,
          ),
        7 => array(
          'var' => 'useMoney',
          'type' => TType::I64,
          ),
        8 => array(
          'var' => 'useTotalNum',
          'type' => TType::I64,
          ),
        9 => array(
          'var' => 'useItemTypes',
          'type' => TType::STRING,
          ),
        10 => array(
          'var' => 'useLimitTypes',
          'type' => TType::STRING,
          ),
        11 => array(
          'var' => 'useNeedMoney',
          'type' => TType::I64,
          ),
        12 => array(
          'var' => 'useStart',
          'type' => TType::I64,
          ),
        13 => array(
          'var' => 'useEnd',
          'type' => TType::I64,
          ),
        14 => array(
          'var' => 'getTypes',
          'type' => TType::STRING,
          ),
        15 => array(
          'var' => 'getItemTypes',
          'type' => TType::STRING,
          ),
        16 => array(
          'var' => 'getCodeType',
          'type' => TType::I32,
          ),
        17 => array(
          'var' => 'getCode',
          'type' => TType::STRING,
          ),
        18 => array(
          'var' => 'getNumMax',
          'type' => TType::I64,
          ),
        19 => array(
          'var' => 'getStart',
          'type' => TType::I64,
          ),
        20 => array(
          'var' => 'getEnd',
          'type' => TType::I64,
          ),
        21 => array(
          'var' => 'status',
          'type' => TType::I32,
          ),
        22 => array(
          'var' => 'getNeedMoney',
          'type' => TType::I64,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VoucherConfThrift';
  }

  public function read($input)
  {
    return $this->_read('VoucherConfThrift', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VoucherConfThrift', self::$_TSPEC, $output);
  }

}

class VoucherTrendThrift extends TBase {
  static $_TSPEC;

  /**
   * @var int
   */
  public $vBindId = null;
  /**
   * @var int
   */
  public $vId = null;
  /**
   * @var string
   */
  public $vSn = null;
  /**
   * @var int
   */
  public $vUserId = null;
  /**
   * @var string
   */
  public $vOrderSn = null;
  /**
   * @var int
   */
  public $vAddTime = null;
  /**
   * @var int
   */
  public $vStatus = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'vBindId',
          'type' => TType::I64,
          ),
        2 => array(
          'var' => 'vId',
          'type' => TType::I64,
          ),
        3 => array(
          'var' => 'vSn',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'vUserId',
          'type' => TType::I64,
          ),
        5 => array(
          'var' => 'vOrderSn',
          'type' => TType::STRING,
          ),
        6 => array(
          'var' => 'vAddTime',
          'type' => TType::I64,
          ),
        7 => array(
          'var' => 'vStatus',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VoucherTrendThrift';
  }

  public function read($input)
  {
    return $this->_read('VoucherTrendThrift', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VoucherTrendThrift', self::$_TSPEC, $output);
  }

}

/**
 * ******************************
 *  返回消息体
 * *****************************
 */
class VoucherRet extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $result = null;
  /**
   * @var int
   */
  public $errorCode = null;
  /**
   * @var string
   */
  public $errorMsg = null;
  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\VoucherThrift
   */
  public $data = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'result',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'errorCode',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'errorMsg',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'data',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\VoucherThrift',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VoucherRet';
  }

  public function read($input)
  {
    return $this->_read('VoucherRet', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VoucherRet', self::$_TSPEC, $output);
  }

}

class VouchersRet extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $result = null;
  /**
   * @var int
   */
  public $errorCode = null;
  /**
   * @var string
   */
  public $errorMsg = null;
  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\VoucherThrift[]
   */
  public $data = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'result',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'errorCode',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'errorMsg',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'data',
          'type' => TType::LST,
          'etype' => TType::STRUCT,
          'elem' => array(
            'type' => TType::STRUCT,
            'class' => '\cn\choumei\thriftserver\service\stub\gen\VoucherThrift',
            ),
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VouchersRet';
  }

  public function read($input)
  {
    return $this->_read('VouchersRet', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VouchersRet', self::$_TSPEC, $output);
  }

}

class VoucherConfRet extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $result = null;
  /**
   * @var int
   */
  public $errorCode = null;
  /**
   * @var string
   */
  public $errorMsg = null;
  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\VoucherConfThrift
   */
  public $data = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'result',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'errorCode',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'errorMsg',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'data',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\VoucherConfThrift',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VoucherConfRet';
  }

  public function read($input)
  {
    return $this->_read('VoucherConfRet', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VoucherConfRet', self::$_TSPEC, $output);
  }

}

class VoucherConfsRet extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $result = null;
  /**
   * @var int
   */
  public $errorCode = null;
  /**
   * @var string
   */
  public $errorMsg = null;
  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\VoucherConfThrift[]
   */
  public $data = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'result',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'errorCode',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'errorMsg',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'data',
          'type' => TType::LST,
          'etype' => TType::STRUCT,
          'elem' => array(
            'type' => TType::STRUCT,
            'class' => '\cn\choumei\thriftserver\service\stub\gen\VoucherConfThrift',
            ),
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VoucherConfsRet';
  }

  public function read($input)
  {
    return $this->_read('VoucherConfsRet', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VoucherConfsRet', self::$_TSPEC, $output);
  }

}

class VoucherTrendRet extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $result = null;
  /**
   * @var int
   */
  public $errorCode = null;
  /**
   * @var string
   */
  public $errorMsg = null;
  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\VoucherTrendThrift
   */
  public $data = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'result',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'errorCode',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'errorMsg',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'data',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\VoucherTrendThrift',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VoucherTrendRet';
  }

  public function read($input)
  {
    return $this->_read('VoucherTrendRet', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VoucherTrendRet', self::$_TSPEC, $output);
  }

}

class VoucherTrendsRet extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $result = null;
  /**
   * @var int
   */
  public $errorCode = null;
  /**
   * @var string
   */
  public $errorMsg = null;
  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\VoucherTrendThrift[]
   */
  public $data = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'result',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'errorCode',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'errorMsg',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'data',
          'type' => TType::LST,
          'etype' => TType::STRUCT,
          'elem' => array(
            'type' => TType::STRUCT,
            'class' => '\cn\choumei\thriftserver\service\stub\gen\VoucherTrendThrift',
            ),
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'VoucherTrendsRet';
  }

  public function read($input)
  {
    return $this->_read('VoucherTrendsRet', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('VoucherTrendsRet', self::$_TSPEC, $output);
  }

}


