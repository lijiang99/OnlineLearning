<?php
/**
 * file: func.inc.php 自定义的函数库文件
 */

/********************************生成指定长度的随机数字，作为注册时系统生成账号********************************/
/**
 * @function:    nonceStr               用于生成指定长度的随机数字
 * @param:       int         $length    指定长度
 * @return:      string                 返回由数字组成的字符串
 */
function nonceStr($length){
		static $seed = array(0,1,2,3,4,5,6,7,8,9);
		$str = ''; $str .= rand(1,9); /* 随机数值串的首字符不能为'0' */
		for($i = 1; $i < $length; ++$i){
				$rand = rand(0, count($seed)-1); /* 生成随机的数组下标 */
				$temp = $seed[$rand]; /* 根据随机下标获取随机数值 */
				$str .= $temp; /* 拼接随机数值为字符串 */
				unset($seed[$rand]); $seed = array_values($seed);
		}
		return $str;
}
/**************************************************************************************************************/




/***************************************用于检查数据库中是否存在指定账号***************************************/
/**
 * @function:    checkAccount                用于检查是否存在重复账号
 * @param:       PDO             $pdo        PDO对象用于进行数据库操作
 * @param:       string          $account    被检查的账号
 * @return:      bool                        true: 新账号    false: 账号已存在
 */
function checkAccount($pdo, $account){
		$sql = "SELECT id FROM users_profile WHERE account = {$account}";
		$stmt = $pdo->prepare($sql); $stmt->execute();
		/* 若能以account为筛选条件从数据库中提取导数据，则表示账号已存在，返回false */
		if($stmt->rowCount() > 0){ return false; }
		return true; /* 提取不到数据，则表示账号为新账号，返回true */
}
/**************************************************************************************************************/





/**************************************用于检查数据库中是否存在指定邀请码**************************************/
/**
 * @function:   checkInviteCode                   用于检查是否存在重复邀请码
 * @param:      PDO               $pdo            PDO对象用于进行数据库操作
 * @param:      string            $invite_code    被检查邀请码
 * @return:     bool                              true: 新邀请码 false: 邀请码已存在
 */
function checkInviteCode($pdo, $invite_code){
		$sql = "SELECT id FROM classes WHERE invite_code={$invite_code}";
		$stmt = $pdo->prepare($sql); $stmt->execute();
		if($stmt->rowCount() > 0){ return false; }
		return true;
}
/**************************************************************************************************************/


/********************************用于将图片上传到本地服务器的项目文件的uploads目录的images目录下*********************************************/
include_once("fileupload.class.php"); /* 导入文件上传类FileUpload所在文件 */
include_once("image.class.php"); /* 导入图片处理类Image所在文件 */

/**
 * @function:    uploadImage                   用于上传图片到本地服务器，并返回上传状态和上传后的文件名
 * @param:       string         $label_name    用户提交的表单文件中上传图片文件所设置的标签名
 * @return:      array
 */
function uploadImage($label_name){
		$path = "./uploads/images/"; /* 设置文件上传路径 */
		$up = new FileUpload($path); /* 实例化一个文件上传对象 */

		if($up->upload($label_name)){ /* 上传图片 */
				$filename= $up->getFileName(); /* 获取上传后的图片名 */

				$img = new Image($path); /* 创建图像处理类对象 */

				$img->thumb($filename, 300, 300, ""); /* 将上传图片缩放在300x300以内 */

				return array(true, $filename); /* 如果成功返回成功状态和图片名称 */
		}else{
				return array(false, $up->getErrorMsg()); /* 如果失败返回失败状态和错误消息 */
		}
}

function delImage($picname){ $path = "./uploads/images/"; unlink($path.$picname); /* 删除原图 */ }
/*********************************************************************************************************************************************/




/*********************************用于在章节列表，小节列表页面顶部显示课程相关信息************************************************************/
/**
 * @function:    showCourse             用于在页面顶部显示课程封面，课程名，章节名，小节名
 * @param:       PDO    $pdo            PDO对象用于进行数据库操作
 * @param:       int    $courses_id     课程表courses中的唯一id
 * @param:       int    $chapters_id    章节表中chapters的唯一id
 * @param:       int    $tasks_id       小节表中tasks中的唯一id
 */
function showCourse($pdo, $courses_id, $chapters_id = "", $tasks_id = ""){
		$sql = "SELECT cover, title FROM courses WHERE id={$courses_id}";
		$row1 = $pdo->prepare($sql); $row1->execute();
		$result1 = $row1->fetch(PDO::FETCH_ASSOC);
		echo "<img height='200' src='{$result1['cover']}'>";
		echo '<h1>'.$result1['title'].'</h1>';
		if($chapters_id){
				$sql = "SELECT seq, title FROM chapters WHERE id={$chapters_id}";
				$row2 = $pdo->prepare($sql); $row2->execute();
				$result2 = $row2->fetch(PDO::FETCH_ASSOC);
				echo '<h2>第'.$result2['seq'].'章    '.$result2['title'].'</h2>';
		}
		if($tasks_id){
				$sql = "SELECT seq, title FROM tasks WHERE id={$tasks_id}";
				$row3 = $pdo->prepare($sql); $row3->execute();
				$result3 = $row3->fetch(PDO::FETCH_ASSOC);
				echo '<h3>第'.$result3['seq'].'小节    '.$result3['title'].'</h2>';
		}
}
/*********************************************************************************************************************************************/

?>
