<?php
define('IN_HHS', true);

require_once ('../includes/init.php');

$GLOBALS['ecs'] = $hhs;

// 微信小程序支付回调

require_once dirname(__FILE__)."/wxpayAPI/lib/WxPay.Api.php";
require_once dirname(__FILE__).'/wxpayAPI/lib/WxPay.Notify.php';
require_once dirname(__FILE__).'/wxpayAPI/example/log.php';


error_reporting(E_ALL);
ini_set('display_errors',0);    //将错误记录到日志
ini_set('log_errors', 1);
ini_set('error_log','./weblog.txt');

//初始化日志
$logHandler= new CLogFileHandler(dirname(__FILE__)."/wxpayAPI/logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}

		Log::DEBUG("begin_paid:".$data["out_trade_no"]);

		require(ROOT_PATH . 'includes/lib_payment.php');

		// 切割出订单号
		$order_id = explode("e", $data["out_trade_no"]);
		
		// 检查金额
		if (! check_money ( $order_id[0], $data['total_fee']/100 )) {
			// 支付失败
			Log::DEBUG("check_money:fail" . $data['total_fee']);
			return false;
		}

		// 查询order_id
		$sql = "SELECT order_id FROM ".$GLOBALS['ecs']->table('pay_log')." WHERE log_id='{$order_id[0]}'";

		$log_order_id = $GLOBALS['db']->getOne($sql);

		if ($log_order_id) {
			$sql = "UPDATE ".$GLOBALS['ecs']->table('order_info')." SET xcx_pay_no='{$data["transaction_id"]}' WHERE order_id='$log_order_id'";
			$GLOBALS['db']->query($sql);
		}

		order_paid ($order_id[0], 2);
		Log::DEBUG("order_paid:".$data["out_trade_no"]);
		return true;
	}
}

Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false);
