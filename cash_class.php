<?php

/**
* 企业付款到用户零钱
*/
class cash
{
	
	function __construct()
	{
		
	}

	public function arrayToXml($arr)
    {
        $xml = "<xml>";
        foreach ($arr as $key=>$val)
        {
            if (is_numeric($val)){
                $xml.="<".$key.">".$val."</".$key.">";
            }else{
                 $xml.="<".$key."><![CDATA[".$val."]]></".$key.">";
            }
        }
        $xml.="</xml>";
        return $xml;
    }

	public function xmlToArray($xml)
	{    
		//禁止引用外部xml实体
		libxml_disable_entity_loader(true);
		$values = json_decode(json_encode(simplexml_load_string($xml, 'SimpleXMLElement', LIBXML_NOCDATA)), true);        
		return $values;
	}

    /*使用证书，以post方式提交xml到对应的接口url*/
	public function curl_post_ssl($url, $vars, $second=30)
	{
		$ch = curl_init();
		//超时时间
		curl_setopt($ch,CURLOPT_TIMEOUT,$second);
		curl_setopt($ch,CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch,CURLOPT_URL,$url);
		curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch,CURLOPT_SSL_VERIFYHOST,false);

		//以下两种方式需选择一种
		//此处必须为文件服务器根目录绝对路径 不可使用变量代替
		curl_setopt($ch,CURLOPT_SSLCERT,"/www/wwwroot/xcx.she985.com/xcx/cert/apiclient_cert.pem");
		curl_setopt($ch,CURLOPT_SSLKEY,"/www/wwwroot/xcx.she985.com/xcx/cert/apiclient_key.pem");


		curl_setopt($ch,CURLOPT_POST, 1);
		curl_setopt($ch,CURLOPT_POSTFIELDS,$vars);

		$data = curl_exec($ch);

		if($data){
			curl_close($ch);
			return $data;
		}else {
			$error = curl_errno($ch);
			echo "call faild, errorCode:$error\n";
			curl_close($ch);
			return false;
		}
	}

	//企业向个人付款
	public function payToUser($openid,$desc,$amount,$appid,$mchid,$mch_key,$partner_trade_no)
	{
		//微信付款到个人的接口
		$url = 'https://api.mch.weixin.qq.com/mmpaymkttransfers/promotion/transfers';
		$params["mch_appid"]        = $appid;   //公众账号appid
		$params["mchid"]            = $mchid;   //商户号 微信支付平台账号
		$params["nonce_str"]        = 'echo'.mt_rand(100,999);   //随机字符串
		$params["partner_trade_no"] = $partner_trade_no;           //商户订单号
		$params["amount"]           = $amount;          //金额
		$params["desc"]             = $desc;            //企业付款描述
		$params["openid"]           = $openid;          //用户openid
		$params["check_name"]       = 'NO_CHECK';       //不检验用户姓名
		$params['spbill_create_ip'] = '47.104.14.158';   //获取IP

		//生成签名(签名算法后面详细介绍)
		$str = 'amount='.$params["amount"].'&check_name='.$params["check_name"].'&desc='.$params["desc"].'&mch_appid='.$params["mch_appid"].'&mchid='.$params["mchid"].'&nonce_str='.$params["nonce_str"].'&openid='.$params["openid"].'&partner_trade_no='.$params["partner_trade_no"].'&spbill_create_ip='.$params['spbill_create_ip'].'&key='.$mch_key;
		//md5加密 转换成大写
		$sign = strtoupper(md5($str));

		$params["sign"] = $sign;//签名

		$xml = $this->arrayToXml($params);

		return $this->curl_post_ssl($url, $xml);
	}
}