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
 * 短信服务接口
 */
interface SmsServiceIf extends \cn\choumei\thriftserver\service\stub\gen\BaseServiceIf {
  /**
   * @param string $mobilephone
   * @param int $type
   * @param int $status
   * @return \cn\choumei\thriftserver\service\stub\gen\GetAuthCodeRet
   */
  public function getAuthCode($mobilephone, $type, $status);
  /**
   * @param string $mobilephone
   * @param int $type
   * @param int $status
   * @return \cn\choumei\thriftserver\service\stub\gen\UpdateAuthCodeStatusRet
   */
  public function updateAuthCodeStatus($mobilephone, $type, $status);
  /**
   * @param string $mobilephone
   * @param int $type
   * @return \cn\choumei\thriftserver\service\stub\gen\IncAuthCodeValiNumRet
   */
  public function incAuthCodeValiNum($mobilephone, $type);
  /**
   * @param string $mobilephone
   * @param int $type
   * @param int $startTime
   * @param int $endTime
   * @return \cn\choumei\thriftserver\service\stub\gen\GetDurationSendNumRet
   */
  public function getDurationSendNum($mobilephone, $type, $startTime, $endTime);
  /**
   * @param string $mobilephone
   * @param int $type
   * @param int $sendTime
   * @return \cn\choumei\thriftserver\service\stub\gen\ReuseAuthCodeRet
   */
  public function reuseAuthCode($mobilephone, $type, $sendTime);
  /**
   * @param string $mobilephone
   * @param string $content
   * @return \cn\choumei\thriftserver\service\stub\gen\SendSmsRet
   */
  public function sendSms($mobilephone, $content);
  /**
   * @param \cn\choumei\thriftserver\service\stub\gen\AddUserCodeParam $addUserCodeParam
   * @return \cn\choumei\thriftserver\service\stub\gen\AddUserCodeRet
   */
  public function addUserCode(\cn\choumei\thriftserver\service\stub\gen\AddUserCodeParam $addUserCodeParam);
  /**
   * @param string $mobilephone
   * @param string $content
   * @param string $ip
   * @param int $type
   * @return \cn\choumei\thriftserver\service\stub\gen\SendSmsRet
   */
  public function sendSmsByType($mobilephone, $content, $ip, $type);
}

class SmsServiceClient extends \cn\choumei\thriftserver\service\stub\gen\BaseServiceClient implements \cn\choumei\thriftserver\service\stub\gen\SmsServiceIf {
  public function __construct($input, $output=null) {
    parent::__construct($input, $output);
  }

  public function getAuthCode($mobilephone, $type, $status)
  {
    $this->send_getAuthCode($mobilephone, $type, $status);
    return $this->recv_getAuthCode();
  }

  public function send_getAuthCode($mobilephone, $type, $status)
  {
    $args = new \cn\choumei\thriftserver\service\stub\gen\SmsService_getAuthCode_args();
    $args->mobilephone = $mobilephone;
    $args->type = $type;
    $args->status = $status;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'getAuthCode', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('getAuthCode', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_getAuthCode()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\cn\choumei\thriftserver\service\stub\gen\SmsService_getAuthCode_result', $this->input_->isStrictRead());
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
      $result = new \cn\choumei\thriftserver\service\stub\gen\SmsService_getAuthCode_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("getAuthCode failed: unknown result");
  }

  public function updateAuthCodeStatus($mobilephone, $type, $status)
  {
    $this->send_updateAuthCodeStatus($mobilephone, $type, $status);
    return $this->recv_updateAuthCodeStatus();
  }

  public function send_updateAuthCodeStatus($mobilephone, $type, $status)
  {
    $args = new \cn\choumei\thriftserver\service\stub\gen\SmsService_updateAuthCodeStatus_args();
    $args->mobilephone = $mobilephone;
    $args->type = $type;
    $args->status = $status;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'updateAuthCodeStatus', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('updateAuthCodeStatus', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_updateAuthCodeStatus()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\cn\choumei\thriftserver\service\stub\gen\SmsService_updateAuthCodeStatus_result', $this->input_->isStrictRead());
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
      $result = new \cn\choumei\thriftserver\service\stub\gen\SmsService_updateAuthCodeStatus_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("updateAuthCodeStatus failed: unknown result");
  }

  public function incAuthCodeValiNum($mobilephone, $type)
  {
    $this->send_incAuthCodeValiNum($mobilephone, $type);
    return $this->recv_incAuthCodeValiNum();
  }

  public function send_incAuthCodeValiNum($mobilephone, $type)
  {
    $args = new \cn\choumei\thriftserver\service\stub\gen\SmsService_incAuthCodeValiNum_args();
    $args->mobilephone = $mobilephone;
    $args->type = $type;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'incAuthCodeValiNum', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('incAuthCodeValiNum', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_incAuthCodeValiNum()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\cn\choumei\thriftserver\service\stub\gen\SmsService_incAuthCodeValiNum_result', $this->input_->isStrictRead());
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
      $result = new \cn\choumei\thriftserver\service\stub\gen\SmsService_incAuthCodeValiNum_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("incAuthCodeValiNum failed: unknown result");
  }

  public function getDurationSendNum($mobilephone, $type, $startTime, $endTime)
  {
    $this->send_getDurationSendNum($mobilephone, $type, $startTime, $endTime);
    return $this->recv_getDurationSendNum();
  }

  public function send_getDurationSendNum($mobilephone, $type, $startTime, $endTime)
  {
    $args = new \cn\choumei\thriftserver\service\stub\gen\SmsService_getDurationSendNum_args();
    $args->mobilephone = $mobilephone;
    $args->type = $type;
    $args->startTime = $startTime;
    $args->endTime = $endTime;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'getDurationSendNum', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('getDurationSendNum', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_getDurationSendNum()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\cn\choumei\thriftserver\service\stub\gen\SmsService_getDurationSendNum_result', $this->input_->isStrictRead());
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
      $result = new \cn\choumei\thriftserver\service\stub\gen\SmsService_getDurationSendNum_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("getDurationSendNum failed: unknown result");
  }

  public function reuseAuthCode($mobilephone, $type, $sendTime)
  {
    $this->send_reuseAuthCode($mobilephone, $type, $sendTime);
    return $this->recv_reuseAuthCode();
  }

  public function send_reuseAuthCode($mobilephone, $type, $sendTime)
  {
    $args = new \cn\choumei\thriftserver\service\stub\gen\SmsService_reuseAuthCode_args();
    $args->mobilephone = $mobilephone;
    $args->type = $type;
    $args->sendTime = $sendTime;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'reuseAuthCode', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('reuseAuthCode', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_reuseAuthCode()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\cn\choumei\thriftserver\service\stub\gen\SmsService_reuseAuthCode_result', $this->input_->isStrictRead());
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
      $result = new \cn\choumei\thriftserver\service\stub\gen\SmsService_reuseAuthCode_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("reuseAuthCode failed: unknown result");
  }

  public function sendSms($mobilephone, $content)
  {
    $this->send_sendSms($mobilephone, $content);
    return $this->recv_sendSms();
  }

  public function send_sendSms($mobilephone, $content)
  {
    $args = new \cn\choumei\thriftserver\service\stub\gen\SmsService_sendSms_args();
    $args->mobilephone = $mobilephone;
    $args->content = $content;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'sendSms', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('sendSms', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_sendSms()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\cn\choumei\thriftserver\service\stub\gen\SmsService_sendSms_result', $this->input_->isStrictRead());
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
      $result = new \cn\choumei\thriftserver\service\stub\gen\SmsService_sendSms_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("sendSms failed: unknown result");
  }

  public function addUserCode(\cn\choumei\thriftserver\service\stub\gen\AddUserCodeParam $addUserCodeParam)
  {
    $this->send_addUserCode($addUserCodeParam);
    return $this->recv_addUserCode();
  }

  public function send_addUserCode(\cn\choumei\thriftserver\service\stub\gen\AddUserCodeParam $addUserCodeParam)
  {
    $args = new \cn\choumei\thriftserver\service\stub\gen\SmsService_addUserCode_args();
    $args->addUserCodeParam = $addUserCodeParam;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'addUserCode', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('addUserCode', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_addUserCode()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\cn\choumei\thriftserver\service\stub\gen\SmsService_addUserCode_result', $this->input_->isStrictRead());
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
      $result = new \cn\choumei\thriftserver\service\stub\gen\SmsService_addUserCode_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("addUserCode failed: unknown result");
  }

  public function sendSmsByType($mobilephone, $content, $ip, $type)
  {
    $this->send_sendSmsByType($mobilephone, $content, $ip, $type);
    return $this->recv_sendSmsByType();
  }

  public function send_sendSmsByType($mobilephone, $content, $ip, $type)
  {
    $args = new \cn\choumei\thriftserver\service\stub\gen\SmsService_sendSmsByType_args();
    $args->mobilephone = $mobilephone;
    $args->content = $content;
    $args->ip = $ip;
    $args->type = $type;
    $bin_accel = ($this->output_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_write_binary');
    if ($bin_accel)
    {
      thrift_protocol_write_binary($this->output_, 'sendSmsByType', TMessageType::CALL, $args, $this->seqid_, $this->output_->isStrictWrite());
    }
    else
    {
      $this->output_->writeMessageBegin('sendSmsByType', TMessageType::CALL, $this->seqid_);
      $args->write($this->output_);
      $this->output_->writeMessageEnd();
      $this->output_->getTransport()->flush();
    }
  }

  public function recv_sendSmsByType()
  {
    $bin_accel = ($this->input_ instanceof TBinaryProtocolAccelerated) && function_exists('thrift_protocol_read_binary');
    if ($bin_accel) $result = thrift_protocol_read_binary($this->input_, '\cn\choumei\thriftserver\service\stub\gen\SmsService_sendSmsByType_result', $this->input_->isStrictRead());
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
      $result = new \cn\choumei\thriftserver\service\stub\gen\SmsService_sendSmsByType_result();
      $result->read($this->input_);
      $this->input_->readMessageEnd();
    }
    if ($result->success !== null) {
      return $result->success;
    }
    throw new \Exception("sendSmsByType failed: unknown result");
  }

}

// HELPER FUNCTIONS AND STRUCTURES

class SmsService_getAuthCode_args extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $mobilephone = null;
  /**
   * @var int
   */
  public $type = null;
  /**
   * @var int
   */
  public $status = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'mobilephone',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'type',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'status',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_getAuthCode_args';
  }

  public function read($input)
  {
    return $this->_read('SmsService_getAuthCode_args', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_getAuthCode_args', self::$_TSPEC, $output);
  }

}

class SmsService_getAuthCode_result extends TBase {
  static $_TSPEC;

  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\GetAuthCodeRet
   */
  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\GetAuthCodeRet',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_getAuthCode_result';
  }

  public function read($input)
  {
    return $this->_read('SmsService_getAuthCode_result', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_getAuthCode_result', self::$_TSPEC, $output);
  }

}

class SmsService_updateAuthCodeStatus_args extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $mobilephone = null;
  /**
   * @var int
   */
  public $type = null;
  /**
   * @var int
   */
  public $status = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'mobilephone',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'type',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'status',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_updateAuthCodeStatus_args';
  }

  public function read($input)
  {
    return $this->_read('SmsService_updateAuthCodeStatus_args', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_updateAuthCodeStatus_args', self::$_TSPEC, $output);
  }

}

class SmsService_updateAuthCodeStatus_result extends TBase {
  static $_TSPEC;

  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\UpdateAuthCodeStatusRet
   */
  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\UpdateAuthCodeStatusRet',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_updateAuthCodeStatus_result';
  }

  public function read($input)
  {
    return $this->_read('SmsService_updateAuthCodeStatus_result', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_updateAuthCodeStatus_result', self::$_TSPEC, $output);
  }

}

class SmsService_incAuthCodeValiNum_args extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $mobilephone = null;
  /**
   * @var int
   */
  public $type = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'mobilephone',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'type',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_incAuthCodeValiNum_args';
  }

  public function read($input)
  {
    return $this->_read('SmsService_incAuthCodeValiNum_args', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_incAuthCodeValiNum_args', self::$_TSPEC, $output);
  }

}

class SmsService_incAuthCodeValiNum_result extends TBase {
  static $_TSPEC;

  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\IncAuthCodeValiNumRet
   */
  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\IncAuthCodeValiNumRet',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_incAuthCodeValiNum_result';
  }

  public function read($input)
  {
    return $this->_read('SmsService_incAuthCodeValiNum_result', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_incAuthCodeValiNum_result', self::$_TSPEC, $output);
  }

}

class SmsService_getDurationSendNum_args extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $mobilephone = null;
  /**
   * @var int
   */
  public $type = null;
  /**
   * @var int
   */
  public $startTime = null;
  /**
   * @var int
   */
  public $endTime = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'mobilephone',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'type',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'startTime',
          'type' => TType::I64,
          ),
        4 => array(
          'var' => 'endTime',
          'type' => TType::I64,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_getDurationSendNum_args';
  }

  public function read($input)
  {
    return $this->_read('SmsService_getDurationSendNum_args', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_getDurationSendNum_args', self::$_TSPEC, $output);
  }

}

class SmsService_getDurationSendNum_result extends TBase {
  static $_TSPEC;

  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\GetDurationSendNumRet
   */
  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\GetDurationSendNumRet',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_getDurationSendNum_result';
  }

  public function read($input)
  {
    return $this->_read('SmsService_getDurationSendNum_result', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_getDurationSendNum_result', self::$_TSPEC, $output);
  }

}

class SmsService_reuseAuthCode_args extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $mobilephone = null;
  /**
   * @var int
   */
  public $type = null;
  /**
   * @var int
   */
  public $sendTime = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'mobilephone',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'type',
          'type' => TType::I32,
          ),
        3 => array(
          'var' => 'sendTime',
          'type' => TType::I64,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_reuseAuthCode_args';
  }

  public function read($input)
  {
    return $this->_read('SmsService_reuseAuthCode_args', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_reuseAuthCode_args', self::$_TSPEC, $output);
  }

}

class SmsService_reuseAuthCode_result extends TBase {
  static $_TSPEC;

  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\ReuseAuthCodeRet
   */
  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\ReuseAuthCodeRet',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_reuseAuthCode_result';
  }

  public function read($input)
  {
    return $this->_read('SmsService_reuseAuthCode_result', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_reuseAuthCode_result', self::$_TSPEC, $output);
  }

}

class SmsService_sendSms_args extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $mobilephone = null;
  /**
   * @var string
   */
  public $content = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'mobilephone',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'content',
          'type' => TType::STRING,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_sendSms_args';
  }

  public function read($input)
  {
    return $this->_read('SmsService_sendSms_args', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_sendSms_args', self::$_TSPEC, $output);
  }

}

class SmsService_sendSms_result extends TBase {
  static $_TSPEC;

  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\SendSmsRet
   */
  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\SendSmsRet',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_sendSms_result';
  }

  public function read($input)
  {
    return $this->_read('SmsService_sendSms_result', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_sendSms_result', self::$_TSPEC, $output);
  }

}

class SmsService_addUserCode_args extends TBase {
  static $_TSPEC;

  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\AddUserCodeParam
   */
  public $addUserCodeParam = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'addUserCodeParam',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\AddUserCodeParam',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_addUserCode_args';
  }

  public function read($input)
  {
    return $this->_read('SmsService_addUserCode_args', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_addUserCode_args', self::$_TSPEC, $output);
  }

}

class SmsService_addUserCode_result extends TBase {
  static $_TSPEC;

  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\AddUserCodeRet
   */
  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\AddUserCodeRet',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_addUserCode_result';
  }

  public function read($input)
  {
    return $this->_read('SmsService_addUserCode_result', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_addUserCode_result', self::$_TSPEC, $output);
  }

}

class SmsService_sendSmsByType_args extends TBase {
  static $_TSPEC;

  /**
   * @var string
   */
  public $mobilephone = null;
  /**
   * @var string
   */
  public $content = null;
  /**
   * @var string
   */
  public $ip = null;
  /**
   * @var int
   */
  public $type = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        1 => array(
          'var' => 'mobilephone',
          'type' => TType::STRING,
          ),
        2 => array(
          'var' => 'content',
          'type' => TType::STRING,
          ),
        3 => array(
          'var' => 'ip',
          'type' => TType::STRING,
          ),
        4 => array(
          'var' => 'type',
          'type' => TType::I32,
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_sendSmsByType_args';
  }

  public function read($input)
  {
    return $this->_read('SmsService_sendSmsByType_args', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_sendSmsByType_args', self::$_TSPEC, $output);
  }

}

class SmsService_sendSmsByType_result extends TBase {
  static $_TSPEC;

  /**
   * @var \cn\choumei\thriftserver\service\stub\gen\SendSmsRet
   */
  public $success = null;

  public function __construct($vals=null) {
    if (!isset(self::$_TSPEC)) {
      self::$_TSPEC = array(
        0 => array(
          'var' => 'success',
          'type' => TType::STRUCT,
          'class' => '\cn\choumei\thriftserver\service\stub\gen\SendSmsRet',
          ),
        );
    }
    if (is_array($vals)) {
      parent::__construct(self::$_TSPEC, $vals);
    }
  }

  public function getName() {
    return 'SmsService_sendSmsByType_result';
  }

  public function read($input)
  {
    return $this->_read('SmsService_sendSmsByType_result', self::$_TSPEC, $input);
  }

  public function write($output) {
    return $this->_write('SmsService_sendSmsByType_result', self::$_TSPEC, $output);
  }

}


