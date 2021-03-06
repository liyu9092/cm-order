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
 * 搜索服务接口
 */
interface SearchServiceIf extends \cn\choumei\thriftserver\service\stub\gen\BaseServiceIf {
  /**
   * @param int $district
   * @param string $keyword
   * @param int $page
   * @param int $pageSize
   * @param int $totalNum
   * @return \cn\choumei\thriftserver\service\stub\gen\SalonSearchResult
   */
  public function salonSearch($district, $keyword, $page, $pageSize, $totalNum);
  /**
   * @param int[] $salonCats
   * @param int[] $stylistCats
   * @param int $district
   * @param int $zone
   * @return \cn\choumei\thriftserver\service\stub\gen\GetStylistByGradeRet 通过店铺等级和发型师等级查询造型师信息结果集
   * 
   */
  public function getStylistByGrade(array $salonCats, array $stylistCats, $district, $zone);
}

class SearchServiceClient extends \cn\choumei\thriftserver\service\stub\gen\BaseServiceClient implements \cn\choumei\thriftserver\service\stub\gen\SearchServiceIf {
  public function __construct($input, $output=null) {
    parent::__construct($input, $output);
  }

  public function salonSearch($district, $keyword, $page, $pageSize, $totalNum)
  {
    $this->send_salonSearch($district, $keyword, $page, $pageSize, $totalNum);
    return $this->recv_salonSearch();
  }

  public function send_salonSearch($district, $keyword, $page, $pageSize, $totalNum)
  {
    $args = new \cn\choumei\thriftserver\service\stub\gen\SearchService_salonSearch_args();
    $args->district = $district;
    $args->keyword = $keyword;
    $args->page = $page;
    $args->pageSize = $pageSize;
    $args->totalNum = $totalNum;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'salonSearch', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('salonSearch', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_salonSearch()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\cn\choumei\thriftserver\service\stub\gen\SearchService_salonSearch_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \cn\choumei\thriftserver\service\stub\gen\SearchService_salonSearch_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("salonSearch failed: unknown result");
  }

  public function getStylistByGrade(array $salonCats, array $stylistCats, $district, $zone)
  {
    $this->send_getStylistByGrade($salonCats, $stylistCats, $district, $zone);
    return $this->recv_getStylistByGrade();
  }

  public function send_getStylistByGrade(array $salonCats, array $stylistCats, $district, $zone)
  {
    $args = new \cn\choumei\thriftserver\service\stub\gen\SearchService_getStylistByGrade_args();
    $args->salonCats = $salonCats;
    $args->stylistCats = $stylistCats;
    $args->district = $district;
    $args->zone = $zone;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'getStylistByGrade', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('getStylistByGrade', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_getStylistByGrade()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\cn\choumei\thriftserver\service\stub\gen\SearchService_getStylistByGrade_result', $this->input_->isStrictRead());
    else
    {
      $rseqid = 0;
      $fname = null;
      $mtype = 0;

      $this->input_->readMessageBegin($fname, $mtype, $rseqid);
      if ($mtype == TMessageType::EXCEPTION) {
        $x = new TApplicationException();
        $x->read($this->input_);
        $this->input_->readMessageEnd();
        throw $x;
      }
      $result = new \cn\choumei\thriftserver\service\stub\gen\SearchService_getStylistByGrade_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("getStylistByGrade failed: unknown result");
  }

}

// HELPER FUNCTIONS AND STRUCTURES

class SearchService_salonSearch_args extends TBase {
  static $_TSPEC;

  /**
   * @var int
   */
  public $district = null;
  /**
   * @var string
   */
  public $keyword = null;
  /**
   * @var int
   */
  public $page = null;
  /**
   * @var int
   */
  public $pageSize = null;
  /**
   * @var int
   */
  public $totalNum = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'district',
          'type' => TType::I32,
          ),
        2 => array(
          'var' => 'keyword',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'page',
          'type' => TType::I32,
          ),
        4 => array(
          'var' => 'pageSize',
          'type' => TType::I32,
          ),
        5 => array(
          'var' => 'totalNum',
          'type' => TType::I64,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SearchService_salonSearch_args';
  }

  public function read($input)
  {
    return $this->_read('SearchService_salonSearch_args', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SearchService_salonSearch_args', self::$_TSPEC, $output);
  }

}

class SearchService_salonSearch_result extends TBase {
  static $_TSPEC;

  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\SalonSearchResult
   */
  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\SalonSearchResult',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SearchService_salonSearch_result';
  }

  public function read($input)
  {
    return $this->_read('SearchService_salonSearch_result', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SearchService_salonSearch_result', self::$_TSPEC, $output);
  }

}

class SearchService_getStylistByGrade_args extends TBase {
  static $_TSPEC;

  /**
   * @var int[]
   */
  public $salonCats = null;
  /**
   * @var int[]
   */
  public $stylistCats = null;
  /**
   * @var int
   */
  public $district = null;
  /**
   * @var int
   */
  public $zone = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'salonCats',
          'type' => TType::LST,
          'etype' => TType::I32,
          'elem' => array(
            'type' => TType::I32,
            ),
          ),
        2 => array(
          'var' => 'stylistCats',
          'type' => TType::LST,
          'etype' => TType::I32,
          'elem' => array(
            'type' => TType::I32,
            ),
          ),
        3 => array(
          'var' => 'district',
          'type' => TType::I64,
          ),
        4 => array(
          'var' => 'zone',
          'type' => TType::I64,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SearchService_getStylistByGrade_args';
  }

  public function read($input)
  {
    return $this->_read('SearchService_getStylistByGrade_args', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SearchService_getStylistByGrade_args', self::$_TSPEC, $output);
  }

}

class SearchService_getStylistByGrade_result extends TBase {
  static $_TSPEC;

  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\GetStylistByGradeRet
   */
  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\GetStylistByGradeRet',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SearchService_getStylistByGrade_result';
  }

  public function read($input)
  {
    return $this->_read('SearchService_getStylistByGrade_result', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SearchService_getStylistByGrade_result', self::$_TSPEC, $output);
  }

}


