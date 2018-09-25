<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once "../lib/WxPay.Api.php";
require_once '../lib/WxPay.Notify.php';
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
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

		error_reporting(E_ALL);
		ini_set('display_errors',0);    //将错误记录到日志
		ini_set('log_errors', 1);
		ini_set('error_log','../weblog.txt');

		define('IN_ECS', true);
		require('../../../mobile/includes/init.php');

		error_reporting(E_ALL);
		ini_set('display_errors',0);    //将错误记录到日志
		ini_set('log_errors', 1);
		ini_set('error_log','../weblog.txt');

		$myfile = fopen("newfile.txt", "a+") or die("Unable to open file!");
		$txt = "require\n";
		fwrite($myfile, $txt);
		fclose($myfile);

		Log::DEBUG("begin_paid2223");


		// 检查金额
		Log::DEBUG("begin_paid222".$data["out_trade_no"]);

		require_once(ROOT_PATH . 'includes/lib_payment.php');

		if (! check_money ( $data["out_trade_no"], $data['total_fee']/100 )) {
			// 支付失败
			Log::DEBUG("check_money:fail" . $data['total_fee']);
			return false;
		}

		Log::DEBUG("begin_paid333".$data["out_trade_no"]);

		order_paid ($data["out_trade_no"], 2);
		Log::DEBUG("begin_paid444".$data["out_trade_no"]);
		return true;
	}
}

Log::DEBUG("begin notify");
$notify = new PayNotifyCallBack();
$notify->Handle(false);
