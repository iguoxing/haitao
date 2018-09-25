<?php 
/*
微起步话题首页
*/

define('IN_HHS', true);

require ('../includes/init.php');
require('functions.php');

error_reporting(E_ALL);
ini_set('display_errors', '1');

//接受传过来的参数
$act = empty($_REQUEST['act']) ? null : test_input($_REQUEST['act']);
$GLOBALS['ecs'] = $hhs;

/*路由*/
if ($act != null) {

	$function_name = 'action_' . $act;
	if(! function_exists($function_name))
	{
		$function_name = "action_default";
	}
}
else
{
	$function_name = "action_default";
}

/*读取小程序配置*/
$sql = "SELECT * FROM `xcx_config` WHERE id=1";
$xcx_config = $GLOBALS['db']->getRow($sql);

if (!$xcx_config) {
	$res = array('err' => '888','msg'=>'小程序初始化失败！');

	echo json_encode($res);

	exit();
}
else
{
	$GLOBALS['xcx_config'] = $xcx_config;
}


call_user_func($function_name);
/*
act无效时的默认函数
*/
function action_default()
{
	//初始化返回值  error = 1初始值,说明还无任何函数被请求
	$res = array('err' => '999','msg'=>'默认函数');

	echo json_encode($res);

	exit();
}

/*
获取用户基本信息
需要参数 uid sign
返回值：昵称，头像，余额，积分，购物车商品数量，是否是VIP，站内消息数量
*/

function action_get_user_info()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'缺少参数！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    // 获取信息

    $sql  = 'SELECT u.user_id, IFNULL(u.wx_name,u.user_name) AS user_name, u.user_money, u.pay_points,u.mobile_phone, u.headimg,u.sign'.
            ' FROM ' .$GLOBALS['ecs']->table('users'). ' AS u ' .
            " WHERE u.user_id = '$uid'";

    $user = $GLOBALS['db']->getRow($sql);


    // 获取用户等级
	/*if($user['user_rank'] == 0)
	{
		 $user['user_rank']    = "非会员";
	}else
	{
		$rank_id = $user['user_rank'];
		$sql = "SELECT rank_name FROM ".$GLOBALS['ecs']->table('user_rank')."WHERE rank_id='$rank_id'";
		$user['user_rank'] = $GLOBALS['db']->getOne($sql);
	}*/

    $user['pay_points'] = $user['pay_points'];
    $user['user_money']  = $user['user_money'];

    $user['flow_num']  = 0;//购物车商品数量
    $user['message']  = 0;//站内消息
    $user['err']  = 0;//没有错误

    if (!$user['headimg']) {
    	$user['headimg'] = "http://".$GLOBALS['xcx_config']['url']."/xcx/images/default_tx.png";
    }

    if ($user['mobile_phone']) {
    	$user['mobile_phone'] = substr($user['mobile_phone'], 0, 3)."******".substr($user['mobile_phone'], -2);
    }
    else
    {
    	$user['mobile_phone'] = "0";
    }

    exit(json_encode($user));
}

function action_user_login_page()
{
	$res = array('err'=>0,'login_bg'=>"http://".$GLOBALS['xcx_config']['url'].'/'.$GLOBALS['xcx_config']['login_bg'],'login_btton'=>"http://".$GLOBALS['xcx_config']['url'].'/'.$GLOBALS['xcx_config']['login_btton']);
    exit(json_encode($res));
}

/*
绑定手机，如该会员原有手机则修改，如无手机则绑定
*/
function action_bind_mobile()
{
	// 接收参数
	$mobile = isset($_REQUEST['mobile']) ? test_input($_REQUEST['mobile']) : '';//手机号1
	$code = isset($_REQUEST['code']) ? test_input($_REQUEST['code']) : '';//验证码

	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'缺少参数！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    // 检查手机验证码,身份验证

	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('ht_sms_code')." WHERE mobile='$mobile' AND code='$code'";

	$ht_sms_code = $GLOBALS['db']->getRow($sql);

	if (!$ht_sms_code) {
		$res = array('err' => 3,'msg'=>'验证码或手机号错误!');
		echo json_encode($res);
		return;
	}
	else
	{

		if ($ht_sms_code['is_yes'] == 1) {
			$res = array('err' => 7,'msg'=>'验证码已过期!');
			echo json_encode($res);
			return;
		}

		// 验证码检查完毕，修改状态
		$sql = "UPDATE ".$GLOBALS['ecs']->table('ht_sms_code')." SET is_yes=1 WHERE mobile='$mobile' AND code='$code'";
		$GLOBALS['db']->query($sql);
	}

	// 确认用户是解绑还是绑定
	$sql = "SELECT mobile_phone FROM " .$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";

	if ($GLOBALS['db']->getOne($sql)) {
		// 原本有手机号，解绑
		$is_bind = 0;
	}
	else
	{
		// 绑定
		$is_bind = 1;

	}

	if ($is_bind == 1) {
		
		// 检查是否重复
		$sql = "SELECT count(*) FROM " .$GLOBALS['ecs']->table('users')." WHERE mobile_phone='$mobile'";

		if ($GLOBALS['db']->getOne($sql) > 0) {
			$res = array('err' => 6,'msg'=>'您要修改的手机号已被绑定,请找回密码!');
			echo json_encode($res);
			return;
		}

		$sql = "UPDATE ".$GLOBALS['ecs']->table('users')." SET mobile_phone='$mobile',validated=1 WHERE user_id='$uid'";

		$msg = '绑定成功！';
	}
	else
	{
		$sql = "UPDATE ".$GLOBALS['ecs']->table('users')." SET mobile_phone='',validated=0 WHERE user_id='$uid'";

		$msg = '解绑成功！';

	}


	
	// 开始修改

	if ($GLOBALS['db']->query($sql)) {
		$res = array('err' => 0,'msg'=>$msg);
		echo json_encode($res);
		return;
	}
	else
	{
		$res = array('err' => 4,'msg'=>'未知错误修改手机失败,请重试!');
		echo json_encode($res);
		return;
	}

}

/*
搜索文章
*/
function action_search()
{
	$keywords = isset($_REQUEST['keywords']) ? test_input($_REQUEST['keywords']) : 1;//关键字
	$searchtype = isset($_REQUEST['searchtype']) ? test_input($_REQUEST['searchtype']) : 1;//类型，默认1商品，2文章

	$page = isset($_REQUEST['page']) ? test_input($_REQUEST['page']) : 1;//页码

	$count = 20;

	// 要读取的数据条数

	if ($page == 1) {
		$min = 0;
	}
	else
	{
		$min = ($page-1) * $count;
	}


	if ($searchtype == 1) {
		$sql = "SELECT goods_id,goods_name,brand_id,shop_price,goods_thumb FROM ".$GLOBALS['ecs']->table('goods')." WHERE goods_name LIKE '%".$keywords."%' ORDER BY goods_id DESC LIMIT $min,$count";

		$list = $GLOBALS['db']->getAll($sql);
	}
	else
	{
		$sql = "SELECT id as goods_id,title as goods_name,class,file FROM ".$GLOBALS['ecs']->table('ht')." WHERE title LIKE '%".$keywords."%' OR text LIKE '%".$keywords."%' ORDER BY id DESC LIMIT $min,$count";

		$list = $GLOBALS['db']->getAll($sql);

	}

	foreach ($list as $key => $value) {
		// 取短标题
		if (mb_strlen($list[$key]['goods_name']) > 9) {
			$list[$key]['goods_name'] = mb_substr($list[$key]['goods_name'],0,9,'utf-8')."...";
		}

		if (isset($list[$key]['file'])) {
			// 图片数组
			$img_arr = explode('|',$list[$key]['file']);
			$list[$key]['goods_thumb'] = $img_arr[0];
			unset($img_arr);
			unset($list[$key]['file']);
		}
		else
		{
			// 处理商品图片
			if ($list[$key]['goods_thumb']) {
				$list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/'.$list[$key]['goods_thumb'];
			}
			else
			{
				$list[$key]['goods_thumb'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/no-pic.jpg';
			}
			
		}

		/*if (!$list[$key]['goods_thumb']) {
			
		}*/

		// 取品牌名

		if (isset($list[$key]['brand_id'])) {
			$sql = "SELECT brand_name FROM ".$GLOBALS['ecs']->table('brand')." WHERE brand_id='{$list[$key]['brand_id']}'";

			$list[$key]['brand'] = $GLOBALS['db']->getOne($sql);
			unset($list[$key]['brand_id']);
		}

		// 取话题
		if (isset($list[$key]['class'])) {
			$sql = "SELECT class_name FROM ".$GLOBALS['ecs']->table('ht_class')." WHERE id='{$list[$key]['class']}'";

			$list[$key]['brand'] = $GLOBALS['db']->getOne($sql);
			unset($list[$key]['class']);
		}
	}

	
	$list_count = count($list) < $count ? 0:1;

	if ($list) {
		$res = array('err' => 0,'list'=>$list,'list_count'=>$list_count);
		echo json_encode($res);
	}
	else
	{
		$res = array('err' => 0,'list'=>array(),'list_count'=>0);
		echo json_encode($res);
	}
}

/*
首页文章列表
*/
function action_get_index_list()
{
	$page = isset($_REQUEST['page']) ? test_input($_REQUEST['page']) : 1;//页码

	$count = 20;

	// 要读取的数据条数

	if ($page == 1) {
		$min = 0;
	}
	else
	{
		$min = ($page-1) * $count;
	}

	$sql = "SELECT id,title,class,text,file FROM ".$GLOBALS['ecs']->table('ht')." WHERE state=1 ORDER BY id DESC LIMIT $min,$count";

	$index_list = $GLOBALS['db']->getAll($sql);

	// echo $sql;

	$index_list_new = array();

	foreach ($index_list as $key => $value) {

		// 切割图片
		$images = explode("|", $index_list[$key]['file']);
		if (count($images)) {
			array_pop($images);
		}

		// 替换图片标签为空start

		$imgbq = array("{img1}", "{img2}", "{img3}", "{img4}");
		$imgre = array("", "", "", "");
		$index_list[$key]['text'] = str_replace($imgbq, $imgre, $index_list[$key]['text']);

		// 替换图片标签为空end

		if (mb_strlen($index_list[$key]['text']) > 35) {
			$index_text = mb_substr($index_list[$key]['text'],0,35,'utf-8')."...";
		}
		else
		{
			$index_text = $index_list[$key]['text'];
		}

		$index_text = str_replace(array("\r\n", "\r", "\n"), "", $index_text);


		

		$index_list_new[] = Array('id'=>$index_list[$key]['id'], 'title'=>$index_list[$key]['title'], 'class'=>$index_list[$key]['class'], 'text'=>$index_text, 'images'=>$images);
	}

	$arr_count = count($index_list_new) < $count ? 0:1;

	$res = array('err' => 0,'index_list'=>$index_list_new,'arr_count'=>$arr_count);
	echo json_encode($res);

	// print_r($index_list_new);
	return 0;

}

/*
文章详情
*/
function action_get_ht_info()
{
	// 无需登录函数
	$id = isset($_REQUEST['id']) ? test_input($_REQUEST['id']) : 0;

	if (!$id) {
		$res = array('err' => 1,'msg'=>'您浏览的文章不存在！');
		exit(json_encode($res));
	}

	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('article')." WHERE article_id='$id' AND is_open=1";

	$ht_info = $GLOBALS['db']->getRow($sql);

	if (!$ht_info) {
		$res = array('err' => 2,'msg'=>'您浏览的文章不存在！');
		exit(json_encode($res));
	}

	if (empty($ht_info['headimg'])) {
		$ht_info['headimg'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/default_tx.png';
	}

	if (!$ht_info['author']) {
		$ht_info['author'] = '匿名';
	}
	

	// 查询分类名称
	$sql = "SELECT cat_name FROM ".$GLOBALS['ecs']->table('article_cat'). " WHERE cat_id='{$ht_info['cat_id']}'";
	$ht_info['class'] = $GLOBALS['db']->getOne($sql);

	// 格式化时间
	$ht_info['add_time'] = local_date('Y-m-d',$ht_info['add_time']);


	// 准备文章内容

	// 准备详细介绍
	if ($ht_info['content']) {
		// 匹配出img标签，为标签加上width和height以及处理src

	    $preg = '/<img.*?src="(.*?)".*?>/is';

		preg_match_all($preg,$ht_info['content'],$result,PREG_PATTERN_ORDER);//匹配img的src

		foreach ($result[1] as $key => $value) {
			$result[1][$key] = "<img class='rich_img' src='http://".$GLOBALS['xcx_config']['url'].$value."'>";
		}


		$ht_info['content'] = str_replace($result[0],$result[1],$ht_info['content']);
	}
	else
	{
		$ht_info['content'] = '';
	}


	$res = array('err' => 0,'ht_info'=>$ht_info);
	exit(json_encode($res));
	

	return 0;

}

/*
点赞，点没有作用时
无需登录
*/
function action_ht_good_idea()
{
	$ht_id = empty($_REQUEST['ht_id']) ? 0 : test_input($_REQUEST['ht_id']);
	$pl_id = empty($_REQUEST['pl_id']) ? 0 : test_input($_REQUEST['pl_id']);
	$Mark = empty($_REQUEST['Mark']) ? 0 : test_input($_REQUEST['Mark']);//用户标识
	$type = empty($_REQUEST['type']) ? 0 : test_input($_REQUEST['type']);//1为话题点赞,2为评论文章的评论点赞
	$good_or_noHlep = isset($_REQUEST['good_or_noHlep']) ? test_input($_REQUEST['good_or_noHlep']) : 0;//1为点赞，2为点没有作用

	if ($ht_id == 0 || $type == 0 || $good_or_noHlep == 0) {
		$res = array('err'=>1,'msg'=>'网络异常，请重试');
        exit(json_encode($res));
	}

	if ($Mark == 0) {
		$sql = "SELECT max(Mark) FROM ".$GLOBALS['ecs']->table('ht_good_idea_log');

		$max_mark = $GLOBALS['db']->getOne($sql);

		if ($max_mark) {
			$Mark = $max_mark+1;
		}
		else
		{
			$Mark = 1;
		}
		
	}


	// 评论点赞，修改评论的点赞数量

	if ($type == 2) {
		// 检查是否点赞
		$sql = "SELECT id FROM ".$GLOBALS['ecs']->table('ht_good_idea_log')." WHERE ht_id='$ht_id' AND pl_id='$pl_id' AND Mark='$Mark' AND type='$type'";

		if ($GLOBALS['db']->getOne($sql)) {
			$res = array('err'=>2,'msg'=>'您已经点过了！','Mark'=>$Mark);
        	exit(json_encode($res));
		}

		$sql = "UPDATE ".$GLOBALS['ecs']->table('ht_pl')." SET thumb_up=thumb_up+1 WHERE id='$pl_id'";
		$GLOBALS['db']->query($sql);

		// 重新获取整个评论列表
		$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('ht_pl')." WHERE ht_id='$ht_id' AND type=1 ORDER BY id DESC";

		$ht_pl = $GLOBALS['db']->getAll($sql);

		foreach ($ht_pl as $key => $value) {
			if (isset($ht_pl[$key]['user_id'])) {
				// 读取用户名
				$sql = "SELECT IFNULL(wx_name,user_name) AS user_name,headimg FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='{$ht_pl[$key]['user_id']}'";

				$pl_user = $GLOBALS['db']->getRow($sql);

				$ht_pl[$key]['user_name'] = $pl_user['user_name'];

				if ($pl_user['headimg']) {
					$ht_pl[$key]['headimg'] = $pl_user['headimg'];
				}
				else
				{
					$ht_pl[$key]['headimg'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/default_tx.png';
				}

			}

			// 格式化时间
			$ht_pl[$key]['add_time'] = local_date('Y-m-d',$ht_pl[$key]['add_time']);
		}

		$good_number = $ht_pl;
		$no_hlep_number = 0;

		$sql = "INSERT INTO ".$GLOBALS['ecs']->table('ht_good_idea_log')." (`Mark`,`pl_id`,`ht_id`,`type`,`good_or_noHlep`) VALUES ('$Mark',$pl_id,'$ht_id','$type','$good_or_noHlep')";
	}
	else
	{
		// 为话题或文章点赞或点没有作用
		$sql = "SELECT id FROM ".$GLOBALS['ecs']->table('ht_good_idea_log')." WHERE ht_id='$ht_id' AND Mark='$Mark' AND type='$type'";

		if ($GLOBALS['db']->getOne($sql)) {
			$res = array('err'=>2,'msg'=>'您已经点过了！','Mark'=>$Mark);
        	exit(json_encode($res));
		}

		// 获取点赞数量和没有帮助数量
		$sql = "SELECT count(*) FROM ".$GLOBALS['ecs']->table('ht_good_idea_log')." WHERE ht_id='$ht_id' AND type='$type' AND good_or_noHlep=1";

		$good_number = $GLOBALS['db']->getOne($sql);

		if (!$good_number) {
			$good_number = 0;
		}

		$sql = "SELECT count(*) FROM ".$GLOBALS['ecs']->table('ht_good_idea_log')." WHERE ht_id='$ht_id' AND type='$type' AND good_or_noHlep=2";

		$no_hlep_number = $GLOBALS['db']->getOne($sql);

		if (!$no_hlep_number) {
			$no_hlep_number = 0;
		}

		if ($good_or_noHlep == 1) {
			$good_number++;
		}
		else
		{
			$no_hlep_number++;
		}

		$sql = "INSERT INTO ".$GLOBALS['ecs']->table('ht_good_idea_log')." (`Mark`,`pl_id`,`ht_id`,`type`,`good_or_noHlep`) VALUES ('$Mark',0,'$ht_id','$type','$good_or_noHlep')";
	}


	if ($GLOBALS['db']->query($sql)) {

		if ($type == 2) {
			// 当前会员是否赞过该评论？
			
			foreach ($good_number as $key => $value) {
				$sql = "SELECT id FROM ".$GLOBALS['ecs']->table('ht_good_idea_log')." WHERE ht_id='$ht_id' AND pl_id='{$good_number[$key]['id']}' AND Mark='$Mark' AND type='2'";

				if ($GLOBALS['db']->getOne($sql)) {
					$good_number[$key]['is_user_good'] = 1;
				}
				else
				{
					$good_number[$key]['is_user_good'] = 0;
				}
			}
		}

		$res = array('err'=>0,'Mark'=>$Mark,'no_hlep_number'=>$no_hlep_number,'good_number'=>$good_number);
		exit(json_encode($res));
	}
	else
	{
		$res = array('err'=>1,'msg'=>'网络异常，请重试');
        exit(json_encode($res));
	}
		

	
	
}

/*
发表评论
*/

function action_post_pl()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'登录已失效,请重新登录！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

	$ht_id = empty($_REQUEST['ht_id']) ? 0 : test_input($_REQUEST['ht_id']);
	$text = empty($_REQUEST['text']) ? '' : test_input($_REQUEST['text']);
	$type = empty($_REQUEST['type']) ? 0 : test_input($_REQUEST['type']);
	$mark = empty($_REQUEST['mark']) ? 0 : test_input($_REQUEST['mark']);

	if ($ht_id == 0) {
		$res = array('err'=>3,'msg'=>'网络异常，请重试');
        exit(json_encode($res));
	}
	elseif ($text == '') {
		$res = array('err'=>4,'msg'=>'评论内容不能为空喔！');
        exit(json_encode($res));
	}
	elseif ($type == 0) {
		$res = array('err'=>5,'msg'=>'网络异常，请重试');
        exit(json_encode($res));
	}

	$add_time = time();


	// 插入评论
	$sql = "INSERT INTO ".$GLOBALS['ecs']->table('ht_pl')." (`ht_id`, `text`, `thumb_up`, `type`, `user_id`, `add_time`) VALUES ($ht_id, '$text', 0,$type,'$uid','$add_time')";

	if ($GLOBALS['db']->query($sql)) {

		// 获取所有评论
		$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('ht_pl')." WHERE ht_id='$ht_id' AND type=1 ORDER BY id DESC";

		$ht_pl = $GLOBALS['db']->getAll($sql);

		foreach ($ht_pl as $key => $value) {
			if (isset($ht_pl[$key]['user_id'])) {
				// 读取用户名
				$sql = "SELECT IFNULL(wx_name,user_name) AS user_name,headimg FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='{$ht_pl[$key]['user_id']}'";

				$pl_user = $GLOBALS['db']->getRow($sql);

				$ht_pl[$key]['user_name'] = $pl_user['user_name'];

				if ($pl_user['headimg']) {
					$ht_pl[$key]['headimg'] = $pl_user['headimg'];
				}
				else
				{
					$ht_pl[$key]['headimg'] = 'http://'.$GLOBALS['xcx_config']['url'].'/xcx/images/default_tx.png';
				}

			}
			// 当前会员是否赞过该评论？
			$sql = "SELECT id FROM ".$GLOBALS['ecs']->table('ht_good_idea_log')." WHERE ht_id='$ht_id' AND pl_id='{$ht_pl[$key]['id']}' AND Mark='$mark' AND type='2'";

			if ($GLOBALS['db']->getOne($sql)) {
				$ht_pl[$key]['is_user_good'] = 1;
			}
			else
			{
				$ht_pl[$key]['is_user_good'] = 0;
			}
			
			// 格式化时间
			$ht_pl[$key]['add_time'] = local_date('Y-m-d',$ht_pl[$key]['add_time']);
		}

		$res = array('err'=>0,'msg'=>'发布评论成功！','pl_list'=>$ht_pl);
        exit(json_encode($res));
	}
	else
	{
		$res = array('err'=>6,'msg'=>'发布失败，请重试！');
        exit(json_encode($res));
	}


}

/*
用户登录
*/
function action_login()
{
	$res = array('err' => 3);
	$db = $GLOBALS['db'];
	$ecs = $GLOBALS['ecs'];
	$user = $GLOBALS['user'];


	$username = isset($_REQUEST['username']) ? test_input($_REQUEST['username']) : '';
	$password = isset($_REQUEST['password']) ? test_input($_REQUEST['password']) : null;
	// $back_act = isset($_REQUEST['back_act']) ? trim($_REQUEST['back_act']) : '';

	if(is_email($username))
	{
		$sql = "select user_name from " . $ecs->table('users') . " where email='" . $username . "'";
		$username_e = $db->getOne($sql);
		if($username_e)
			$username = $username_e;
	}

	if(is_telephone($username))
	{
		$username_e = false;
		$sql = "select user_name from " . $ecs->table('users') . " where mobile_phone='" . $username . "'";
		$username_res = $db->query($sql);
		$kkk = 0;
		while($username_row = $db->fetchRow($username_res))
		{
			$username_e = $username_row['user_name'];
			$kkk = $kkk + 1;
		}
		if($kkk > 1)
		{
			$res = array('err'=>2,'msg'=>'有大于1个会员使用相同手机号！');
        	exit(json_encode($res));
		}
		if($username_e)
		{
			$username = $username_e;
		}
	}

	//登录
	$check_user = app_check_user($username, $password);
	if($check_user)
	{
		// 登录成功

		$sql  = 'SELECT u.user_id, u.user_name, u.user_money, u.pay_points, u.headimg,u.sign'.
            ' FROM ' .$GLOBALS['ecs']->table('users'). ' AS u ' .
            " WHERE u.user_id = '$check_user'";

    	$user = $GLOBALS['db']->getRow($sql);

    	// 修改用户的sign
		$sign = md5($user['user_name'].$user['user_id'].$user['sign']);

		$sql = "UPDATE ".$GLOBALS['ecs']->table('users')." SET sign='$sign' WHERE user_id='$check_user'";
		$GLOBALS['db']->query($sql);

		$user['sign'] = $sign;

		$user['pay_points'] = $user['pay_points'];
	    $user['user_money']  = price_format($user['user_money'], false);

	    $user['flow_num']  = 0;//购物车商品数量
	    $user['message']  = 0;//站内消息
	    $user['err']  = 0;//没有错误

	    if (!$user['headimg']) {
	    	$user['headimg'] = "https://".$GLOBALS['xcx_config']['url']."/xcx/images/default_tx.png";
	    }

	    exit(json_encode($user));
	}
	else
	{
		$res = array('err'=>1,'msg'=>'登录失败！');
	}
	
	echo json_encode($res);
	return;
}

/*
发送短信
*/
/*
发送短信验证码
*/
function action_sendSMS()
{
	include 'sms_php/api_demo/SmsDemo.php';

	$res = array('err' => 1);

	$mobile = empty($_REQUEST['mobile']) ? 0 : $_REQUEST['mobile'];
	$smstemp = empty($_REQUEST['smstemp']) ? '' : $_REQUEST['smstemp'];
	$uid = empty($_REQUEST['uid']) ? '' : $_REQUEST['uid'];

	if ($mobile == 0 || !is_telephone($mobile)) {
		$res = array('err' => 2,'msg'=>'手机号不正确！');
		echo json_encode($res);
		return;
	}
	elseif ($smstemp == '') {
		$res = array('err' => 3,'msg'=>'缺少参数！');
		echo json_encode($res);
		return;
	}

	if ($uid) {
		// 检查该会员原手机号是否正确
	    $sql = "SELECT mobile_phone FROM ".$GLOBALS['ecs']->table('users')." WHERE user_id='$uid'";
	    $user_mobile_y = $GLOBALS['db']->getOne($sql);

	    if ($user_mobile_y) {
	    	if ($user_mobile_y != $mobile) {
	    		$res = array('err' => 5,'msg'=>'您接收短信手机与您绑定手机不符!');
				echo json_encode($res);
				return;
	    	}
	    }
	}


	$code = random(6, 1);

	if (sendSMSecho($mobile, $smstemp,$code)) {


		// 将验证码保存到数据库，修改密码时取出并验证
		$tady_date = time();

		$code = md5($code);

		$sql = "INSERT INTO ".$GLOBALS['ecs']->table('ht_sms_code')." (`mobile`, `code`, `add_time`, `is_yes`) VALUES ('$mobile', '$code', '$tady_date', '0')";

		$GLOBALS['db']->query($sql);


		$res = array('err' => 0,'code'=>$code,'mobile'=>$mobile);
		echo json_encode($res);
		return;
	}
	else
	{
		$res = array('err' => 4,'msg'=>'发送失败！');
		echo json_encode($res);
		return;
	}

	echo json_encode($res);
	return;
}

/*
用户注销
*/
function action_user_exit()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'缺少参数！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

    $sign = md5($uid.$sign);

	$sql = "UPDATE ".$GLOBALS['ecs']->table('users')." SET sign='$sign' WHERE user_id='$uid'";
	
	if ($GLOBALS['db']->query($sql)) {
		$res = array('err'=>0,'msg'=>'注销成功！');
        exit(json_encode($res));
	}
	else
	{
		$res = array('err'=>3,'msg'=>'网络异常，注销失败！');
        exit(json_encode($res));
	}
}

/*
修改密码
*/
function action_update_password()
{
	$db = $GLOBALS['db'];
	$ecs = $GLOBALS['ecs'];

	$res = array('err' => 1,'msg'=>'网络异常');

	$mobile = empty($_REQUEST['mobile']) ? 0 : test_input($_REQUEST['mobile']);
	$new_password = empty($_REQUEST['new_password']) ? 0 : test_input($_REQUEST['new_password']);
	$code = empty($_REQUEST['code']) ? 0 : test_input($_REQUEST['code']);

	if ($mobile == 0 || !is_telephone($mobile)) {
		$res = array('err' => 2,'msg'=>'该手机号没有绑定用户');
		echo json_encode($res);
		return;
	}

	// 检查手机验证码,身份验证

	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('ht_sms_code')." WHERE mobile='$mobile' AND code='$code'";

	$ht_sms_code = $GLOBALS['db']->getRow($sql);

	if (!$ht_sms_code) {
		$res = array('err' => 7,'msg'=>'验证码或手机号错误!');
		echo json_encode($res);
		return;
	}
	else
	{
		// 验证码过期检查
		if ($ht_sms_code['is_yes'] == 1) {
			$res = array('err' => 7,'msg'=>'验证码已过期!');
			echo json_encode($res);
			return;
		}

		// 验证码检查完毕，修改状态
		$sql = "UPDATE ".$GLOBALS['ecs']->table('ht_sms_code')." SET is_yes=1 WHERE mobile='$mobile' AND code='$code'";
		$GLOBALS['db']->query($sql);
	}
	
	if(strlen($new_password) < 6)
	{
		$res = array('err' => 3,'msg'=>'密码长度不能小于6位!');
		echo json_encode($res);
		return;
	}
	
	if(strpos($new_password, ' ') > 0)
	{
		$res = array('err' => 4,'msg'=>'密码中不能包含空格!');
		echo json_encode($res);
		return;
	}


	$sql = "select count(*) from " . $ecs->table('users') . " where mobile_phone = '$mobile'";
	$count = $db->getOne($sql);

	if($count > 1)
	{
		$res = array('err' => 5,'msg'=>'修改失败,多个用户绑定此手机,请联系管理员!');
		echo json_encode($res);
		return;
	}
	elseif ($count < 1) {
		$res = array('err' => 6,'msg'=>'修改失败,系统内无绑定此手机的用户!');
		echo json_encode($res);
		return;
	}
	else
	{
		$sql = "UPDATE " . $ecs->table('users') . "SET `ec_salt`='0',`salt`='0',password='$new_password',sign='abc' WHERE mobile_phone= '" . $mobile . "'";
		$db->query($sql);
		$res = array('err' => 0,'msg'=>'修改成功！');
		echo json_encode($res);
		return;
	}

	echo json_encode($res);
	return;
}
/*
微信登录-获取唯一标示openid及session_key
*/

function action_Wx_login()
{

	$js_code = empty($_REQUEST['js_code']) ? '' : test_input($_REQUEST['js_code']);
	$nickName = empty($_REQUEST['nickName']) ? '' : test_input($_REQUEST['nickName']);//用户名
	$avatarUrl = empty($_REQUEST['avatarUrl']) ? '' : $_REQUEST['avatarUrl'];//头像
	$gender = empty($_REQUEST['gender']) ? '' : test_input($_REQUEST['gender']);//性别

	if ($js_code == '' || $nickName == '') {
		$res = array('err' => 7,'msg'=>'重要参数丢失,请重试!');
		echo json_encode($res);
		return;
	}

	$user_name = generate_username_by_wx($js_code);
	$password = md5($js_code);
	$email = $js_code."@vqibu.com";


	$url='https://api.weixin.qq.com/sns/jscode2session?appid='.$GLOBALS['xcx_config']['appid'].'&secret='.$GLOBALS['xcx_config']['appsecret'].'&js_code='.$js_code.'&grant_type=authorization_code';

	$wx_res = file_get_contents($url);

	if ($wx_res == '') {
		$res = array('err' => 44,'msg'=>'未能获取到openid','url'=>$url);
		echo json_encode($res);
		return;
	}


	$wx_res = json_decode($wx_res,true);

	if (isset($wx_res['errcode'])) {
		// code失效
		if ($wx_res['errcode']== '40029') {
			$res = array('err' => 1,'msg'=>'登录授权已失效，请重试！');
			echo json_encode($res);
			return;
		}
		else
		{
			$res = array('err' => 2,'msg'=>'未知错误登录失败，请重试！');
			echo json_encode($res);
			return;
		}
	}
	else
	{
		$res = array('err' => 3,'msg'=>'未知错误登录失败，请重试！');
	}

	$session_key = $wx_res['session_key'];
	// $expires_in = $wx_res['expires_in'];
	$openid = $wx_res['openid'];

	// 创建新用户，并绑定到openid和session_key



	// 获取全局变量
	$_CFG = $GLOBALS['_CFG'];
	$_LANG = $GLOBALS['_LANG'];
	$db = $GLOBALS['db'];
	$ecs = $GLOBALS['ecs'];

	$res = array('err' => 4,'msg'=>'网络异常!');

	
	/* 增加是否关闭注册 */
	if($_CFG['shop_reg_closed'])
	{
		$res = array('err' => 5,'msg'=>'管理员已禁止系统注册');//关闭注册
		echo json_encode($res);
		return;
	}
	else
	{
		include_once ('../includes/lib_passport.php');

		// 检查用户是否注册过，如果是，则执行登录

		
		$sql  = 'SELECT u.user_id FROM ' .$GLOBALS['ecs']->table('users'). ' AS u ' ." WHERE u.wx_open_id = '$openid'";


    	$user = $GLOBALS['db']->getOne($sql);


		if ($user) {
			// 执行直接登录程序
			exit(directly_login($user,$openid));
		}
		else
		{
			// 执行注册程序
			
			/* 手机注册 */
			$other['headimg'] = $avatarUrl;//头像
			$other['sex'] = $gender;//性别
			$other['wx_open_id'] = $openid;//wx_open_id
			$other['wx_session_key'] = $session_key;//wx_session_key
			$other['wx_name'] = $nickName;//wx_session_key

			$result = register($user_name, $password, $email, $other);

			if($result)
			{
				// 注册成功，直接登录				

				// 执行直接登录程序
				exit(directly_login($_SESSION['user_id'],$openid));
			}
			else
			{
				
				// 注册失败
				$err = $GLOBALS['err'];
				$res = array('err' => 6,'msg'=>'注册失败了,请手动注册!');
				echo json_encode($res);
				return;
			}
		}
		
	}




}

/*
会员直接注册功能
*/
function action_xcx_register()
{
	// 获取全局变量
	$_CFG = $GLOBALS['_CFG'];
	$_LANG = $GLOBALS['_LANG'];
	$db = $GLOBALS['db'];
	$ecs = $GLOBALS['ecs'];

	$res = array('err' => 1,'msg'=>'网络异常!');
	
	/* 增加是否关闭注册 */
	if($_CFG['shop_reg_closed'])
	{
		$res = array('err' => 2,'msg'=>'管理员已禁止商城注册');//关闭注册
		echo json_encode($res);
		return;
	}
	else
	{
		include_once ('../includes/lib_passport.php');
		
		$mobile_phone = isset($_REQUEST['mobile']) ? trim($_REQUEST['mobile']) : '';
		$code = isset($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
		$password = isset($_REQUEST['password']) ? trim($_REQUEST['password']) : '';

		if(strlen($password) < 32)
		{
			$res = array('err' => 7,'msg'=>'网络异常，请重试!');
			echo json_encode($res);
			return;
		}
		

		if ($mobile_phone == '' || !is_telephone($mobile_phone)) {
			$res = array('err' => 3,'msg'=>'请输入正确的手机号!');
			echo json_encode($res);
			return;
		}

		// 检查手机验证码,身份验证

		$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('ht_sms_code')." WHERE mobile='$mobile_phone' AND code='$code'";

		$ht_sms_code = $GLOBALS['db']->getRow($sql);

		if (!$ht_sms_code) {
			$res = array('err' => 8,'msg'=>'验证码或手机号错误!');
			echo json_encode($res);
			return;
		}
		else
		{
			// 验证码过期检查
			if ($ht_sms_code['is_yes'] == 1) {
				$res = array('err' => 9,'msg'=>'验证码已过期!');
				echo json_encode($res);
				return;
			}

			// 验证码检查完毕，修改状态
			$sql = "UPDATE ".$GLOBALS['ecs']->table('ht_sms_code')." SET is_yes=1 WHERE mobile='$mobile_phone' AND code='$code'";
			$GLOBALS['db']->query($sql);
		}

		
		$sql = "select count(*) from " . $GLOBALS['ecs']->table('users') . " where mobile_phone = '$mobile_phone'";
		$count = $GLOBALS['db']->getOne($sql);

		// print_r($sql);

		if ($count >= 1) {
			$res = array('err' => 4,'msg'=>'您的手机号已经注册过账户了!');
			echo json_encode($res);
			return;
		}
	
		
		/* 手机注册时，用户名默认为u+手机号 */
		$username = generate_username_by_mobile($mobile_phone);
		
		/* 手机注册 */
		$other = array();
		$other['mobile_phone'] = $mobile_phone;
		$result = register($username, $password, $mobile_phone.'@139.com', $other);

		if($result)
		{
			// 注册成功，修改密码

			$sql = "UPDATE " . $ecs->table('users') . "SET `ec_salt`='0',`salt`='0',password='$password',sign='abc' WHERE mobile_phone= '" . $mobile_phone . "'";
			$db->query($sql);

			// 调用函数直接登录
			exit(directly_login($_SESSION['user_id'],'null',1));
		}
		else
		{
			$res = array('err' => 5,'msg'=>'注册失败!');
			echo json_encode($res);
			return;
		}
	}

}

/*
图片上传
*/
function action_update_img()
{
	$_CFG = $GLOBALS['_CFG'];

	include_once('../includes/cls_image.php');//图片处理函数

	$image = new cls_image($_CFG['bgcolor']);

	// 上传图片
	if (isset($_FILES['img']) && $_FILES['img']['error'] == 0) {
	 	//用户有选择图片且上传没有出错error为0

		$img = $image->upload_image_xcx($_FILES['img']);
		 
		if ($img === false)
		{
			// echo $image->error_msg();
			echo "false";
			return;
		}
		else
		{
			$img = 'https://'.$GLOBALS['xcx_config']['url'].'/'.$img;//图片上传成功
			// $res = array('err' => 0,'msg'=>$img);
			echo $img;
			return;
		}
	 
	}
}

/*
发布文章
*/
function action_post_ht()
{

	$post_data = json_decode($GLOBALS['HTTP_RAW_POST_DATA'],true);

	$uid = empty($post_data['uid']) ? 0 : test_input($post_data['uid']);
	$sign = empty($post_data['sign']) ? null : test_input($post_data['sign']);
	
    if ($uid == 0)
    {
    	$res = array('err'=>1,'msg'=>'缺少uid参数！');
        exit(json_encode($res));
    }
    elseif ($sign == null) {
    	$res = array('err'=>2,'msg'=>'缺少sign参数！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录状态失效！');
        exit(json_encode($res));
    }

	$title = empty($post_data['title']) ? '' : test_input($post_data['title']);
	$ht_class = isset($post_data['ht_class']) ? test_input($post_data['ht_class']) : '';
	$text = empty($post_data['text']) ? '' : test_input($post_data['text']);

	$text = addslashes($text);

	$file = empty($post_data['file']) ? '' : test_input($post_data['file']);
	// $laber = empty($_REQUEST['laber']) ? '' : test_input($_REQUEST['laber']);暂未开启
	$add_time = time();

	// 用讯号查询话题所在class的id
	$sql = "SELECT id FROM ".$GLOBALS['ecs']->table('ht_class')." WHERE serial='$ht_class'";
	$ht_class = $GLOBALS['db']->getOne($sql);

	if (!$ht_class) {
		$ht_class = 0;
	}

	if ($title == '') {
		$res = array('err'=>2,'msg'=>'您离成功发布就差一个标题了');
        exit(json_encode($res));
	}

	// 执行插入操作
	$sql = "INSERT INTO ".$GLOBALS['ecs']->table('ht')." (`user_id`, `title`, `class`, `thumb_up`, `no_hlep`, `text`, `file`, `state`, `laber`, `add_time`) VALUES ( '$uid', '$title', '$ht_class', '0', '0', '$text', '$file', '1', '无', '$add_time')";

	if ($GLOBALS['db']->query($sql)) {
		$res = array('err'=>0,'msg'=>'发布成功！','ht_class'=>$ht_class);
        exit(json_encode($res));
	}
	else
	{
		$res = array('err'=>1,'msg'=>'网络异常，发布失败');
        exit(json_encode($res));
	}

	$res = array('err'=>2,'msg'=>'网络异常，发布失败');
    exit(json_encode($res));
}


/*
读取所有话题分类
*/
function action_get_ht_class()
{
	$uid = empty($_REQUEST['uid']) ? 0 : test_input($_REQUEST['uid']);
	$sign = empty($_REQUEST['sign']) ? null : test_input($_REQUEST['sign']);
	
    if ($uid == 0 || $sign == null)
    {
    	$res = array('err'=>1,'msg'=>'请您先登录喔！');
        exit(json_encode($res));
    }

    // 检查sign
    if (!check_sign($uid,$sign)) {
    	$res = array('err'=>2,'msg'=>'登录失效,请重新登录！');
        exit(json_encode($res));
    }


	// 获取列表
	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('ht_class')." WHERE state=1 ORDER BY serial ASC";
	
	$ht_class = $GLOBALS['db']->getAll($sql);

	foreach ($ht_class as $key => $value) {
		$class_name[] = $ht_class[$key]['class_name'];
	}

	// print_r($class_name);

	$res = array('err' => 0,'ht_class'=>$class_name);
	exit(json_encode($res));
}

/*
读取所有话题分版
*/
function action_get_ht_class_list()
{
	// 获取列表
	$sql = "SELECT * FROM ".$GLOBALS['ecs']->table('ht_class')." WHERE state=1 ORDER BY serial ASC";
	
	$ht_class = $GLOBALS['db']->getAll($sql);

	foreach ($ht_class as $key => $value) {
		// 读取帖子总数
		$class_id = $ht_class[$key]['id'];

		$sql = "SELECT count(*) FROM ".$GLOBALS['ecs']->table('ht')." WHERE class='$class_id'";
		$ht_class[$key]['count'] = $GLOBALS['db']->getOne($sql);
	}

	$res = array('err' => 0,'ht_class'=>$ht_class);
	exit(json_encode($res));
}

/*
读取某话题分类的话题列表
*/
function action_get_ht_list($value='')
{
	$ht_class = empty($_REQUEST['ht_class']) ? '0' : test_input($_REQUEST['ht_class']);
	$page = empty($_REQUEST['page']) ? 1 : test_input($_REQUEST['page']);//页码

	$count = 20;

	// 要读取的数据条数

	if ($page == 1) {
		$min = 0;
	}
	else
	{
		$min = ($page-1) * $count;
	}


	if ($ht_class == 0) {
		$res = array('err' => 1,'msg'=>'您访问的话题不存在！');
		exit(json_encode($res));
	}
	else
	{

		// 取得话题基本信息
		$sql = "SELECT class_name,state,image FROM ".$GLOBALS['ecs']->table('ht_class')." WHERE id='$ht_class'";

		$ht_info = $GLOBALS['db']->getRow($sql);

		if (!$ht_info) {
			$res = array('err' => 2,'msg'=>'您访问的话题不存在！');
			exit(json_encode($res));
		}
		else if ($ht_info['state'] != 1) {
			$res = array('err' => 3,'msg'=>'您访问的话题已关闭浏览！');
			exit(json_encode($res));
		}

		$sql = "SELECT h.*,u.headimg,IFNULL(u.wx_name,u.user_name) AS name FROM ".$GLOBALS['ecs']->table('ht')." AS h LEFT JOIN  ".$GLOBALS['ecs']->table('users')." AS u ON u.user_id=h.user_id WHERE h.class='$ht_class' ORDER BY id DESC LIMIT $min,$count";
		$ht_list = $GLOBALS['db']->getAll($sql);
	}

	foreach ($ht_list as $key => $value) {
		// 切割图片
		$images = explode("|", $ht_list[$key]['file']);
		if (count($images)) {
			array_pop($images);
		}
		$ht_list[$key]['file'] = array_slice($images,0,3);

		// 替换图片标签为空start

		$imgbq = array("{img1}", "{img2}", "{img3}", "{img4}");
		$imgre = array("", "", "", "");
		$ht_list[$key]['text'] = str_replace($imgbq, $imgre, $ht_list[$key]['text']);

		// 替换图片标签为空end

		if (mb_strlen($ht_list[$key]['text']) > 38) {
			$ht_list[$key]['text'] = mb_substr($ht_list[$key]['text'],0,35,'utf-8')."...";
		}

		$ht_list[$key]['text'] = str_replace(array("\r\n", "\r", "\n"), "", $ht_list[$key]['text']);


		$ht_list[$key]['add_time'] = local_date('Y年m月d日',$ht_list[$key]['add_time']);
	}



	$list_count = count($ht_list) < $count ? 0:1;

	if ($ht_list) {
		$res = array('err' => 0,'ht_list'=>$ht_list,'list_count'=>$list_count,'ht_info'=>$ht_info);
		exit(json_encode($res));
	}
	else
	{
		$res = array('err' => 2,'ht_list'=>'null','list_count'=>$list_count,'ht_info'=>$ht_info);
		exit(json_encode($res));
	}

	
}

// 直接登录函数-比如微信注册成功，微信验证成功
function directly_login($user_id,$wx_open_id,$is_no_wx=0)
{

		if ($is_no_wx == 1) {
			$sql  = 'SELECT u.user_id, IFNULL(u.wx_name,u.user_name) AS user_name, u.user_money, u.pay_points, u.headimg,u.sign'.
	            ' FROM ' .$GLOBALS['ecs']->table('users'). ' AS u ' .
	            " WHERE u.user_id = '$user_id'";
		}
		else
		{
			$sql  = 'SELECT u.user_id, IFNULL(u.wx_name,u.user_name) AS user_name, u.user_money, u.pay_points, u.headimg,u.sign'.
	            ' FROM ' .$GLOBALS['ecs']->table('users'). ' AS u ' .
	            " WHERE u.user_id = '$user_id' AND wx_open_id='$wx_open_id'";
		}

		// echo $sql;

	

    	$user = $GLOBALS['db']->getRow($sql);


    	if (!$user) {
    		$res = array('err' => 8,'msg'=>'登录失败,请重试!');
			exit(json_encode($res));
    	}
    	

    	// 修改用户的sign
		$sign = md5($user['user_name'].$user['user_id'].$user['sign']);


		$sql = "UPDATE ".$GLOBALS['ecs']->table('users')." SET sign='$sign' WHERE user_id='$user_id'";
		$GLOBALS['db']->query($sql);

		$user['sign'] = $sign;
		

		$user['pay_points'] = isset($user['pay_points']) ? $user['pay_points'] : 0;
	    $user['user_money']  = $user['user_money'];

	    $user['flow_num']  = 0;//购物车商品数量
	    $user['message']  = 0;//站内消息
	    $user['err']  = 0;//没有错误

	    if (!$user['headimg']) {
	    	$user['headimg'] = "http://".$GLOBALS['xcx_config']['url']."/xcx/images/default_tx.png";
	    }

	    return json_encode($user);
}

// 会员登录验证

function app_check_user($username, $password)
{
	$db = $GLOBALS['db'];
	$ecs = $GLOBALS['ecs'];
	
	/* 如果没有定义密码则只检查用户名 */
	if($password === null)
	{
		return false;
	}
	else
	{
		$sql = "SELECT user_id, password, salt,ec_salt FROM " . $ecs->table('users') . " WHERE user_name='$username'";
		$row = $db->getRow($sql);

		if(empty($row))
		{
			return 0;
		}

		$ec_salt = $row['ec_salt'];

		if(empty($row['salt']))
		{
			if($row['password'] != app_compile_password(array(
				'md5password' => $password, 'ec_salt' => $ec_salt
			)))
			{
				return 0;
			}
			else
			{
				if(empty($ec_salt))
				{
					$ec_salt = rand(1, 9999);
					$new_password = md5($password . $ec_salt);
					$sql = "UPDATE " . $ecs->table("users") . "SET password= '" . $new_password . "',ec_salt='" . $ec_salt . "'" . " WHERE user_name='$username'";
					$db->query($sql);
				}
				return $row['user_id'];
			}
		}
		else
		{
			/* 如果salt存在，使用salt方式加密验证，验证通过洗白用户密码 */
			$encrypt_type = substr($row['salt'], 0, 1);
			$encrypt_salt = substr($row['salt'], 1);
			
			/* 计算加密后密码 */
			$encrypt_password = '';
			switch($encrypt_type)
			{
				case ENCRYPT_ZC:
					$encrypt_password = md5($encrypt_salt . $password);
					break;
				/* 如果还有其他加密方式添加到这里 */
				// case other :
				// ----------------------------------
				// break;
				case ENCRYPT_UC:
					$encrypt_password = md5(md5($password) . $encrypt_salt);
					break;
				
				default:
					$encrypt_password = '';
			}
			
			if($row['password'] != $encrypt_password)
			{
				return 0;
			}
			
			$sql = "UPDATE " . $this->table("users") . " SET password = '" . app_compile_password(array(
				'md5password' => $password
			)) . "', salt=''" . " WHERE user_id = '$row[user_id]'";
			$this->db->query($sql);
			
			return $row['user_id'];
		}


	}
}



/**
 * 编译密码函数
 *
 * @access public
 * @param array $cfg
 *        	包含参数为 $password, $md5password, $salt, $type
 *        	
 * @return void
 */
function app_compile_password ($cfg)
{
	if(isset($cfg['password']))
	{
		$cfg['md5password'] = md5($cfg['password']);
	}
	if(empty($cfg['type']))
	{
		$cfg['type'] = PWD_MD5;
	}
	
	switch($cfg['type'])
	{
		case PWD_MD5:
			if(! empty($cfg['ec_salt']))
			{
				return md5($cfg['md5password'] . $cfg['ec_salt']);
			}
			else
			{
				return $cfg['md5password'];
			}
		
		case PWD_PRE_SALT:
			if(empty($cfg['salt']))
			{
				$cfg['salt'] = '';
			}
			
			return md5($cfg['salt'] . $cfg['md5password']);
		
		case PWD_SUF_SALT:
			if(empty($cfg['salt']))
			{
				$cfg['salt'] = '';
			}
			
			return md5($cfg['md5password'] . $cfg['salt']);
		
		default:
			return '';
	}
}

/**
 * 根据微信昵称生成用户名
 *
 * @param number $length
 * @return number
 */
function generate_username_by_wx ($name)
{

	$username = 'wx_'.substr($name, 0, 3);

	$charts = "ABCDEFGHJKLMNPQRSTUVWXYZ";
	$max = strlen($charts)-1;

	for($i = 0; $i < 8; $i ++)
	{
		$username .= $charts[mt_rand(0, $max)];
	}

	$username .= substr($name, -2);
	
	$sql = "select count(*) from " . $GLOBALS['ecs']->table('users') . " where user_name = '$username'";
	$count = $GLOBALS['db']->getOne($sql);
	if($count > 0)
	{
		return generate_username_by_wx();
	}

	return $username;
}
/**
 * 根据手机号生成用户名
 *
 * @param number $length
 * @return number
 */
function generate_username_by_mobile ($mobile)
{

	$username = 'u'.substr($mobile, 0, 3);

	$charts = "ABCDEFGHJKLMNPQRSTUVWXYZ";
	$max = strlen($charts);

	for($i = 0; $i < 4; $i ++)
	{
		$username .= $charts[mt_rand(0, $max)];
	}

	$username .= substr($mobile, -4);
	
	$sql = "select count(*) from " . $GLOBALS['ecs']->table('users') . " where user_name = '$username'";
	$count = $GLOBALS['db']->getOne($sql);
	if($count > 0)
	{
		return generate_username_by_mobile();
	}

	return $username;
}