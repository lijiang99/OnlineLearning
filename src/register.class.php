<?php
/**
 * file: register.class.php
 * 本类的实例对象用于进行用户注册，将用户的个人信息添加到数据库中
 * 更新数据表: users_profile    个人详细信息表
 *             teachers         教师表
 *             students         学生表
 */

class Register{
		/* 个人详细信息表数据参数 */
		private $role; /* 用户头衔分为三类：管理员，教师，学生 */
		const ADMIN = 1; const TEACHERS = 2; const STUDENTS = 3;
		private $name, $idcard, $gender;
		private $school, $major, $account;

		/* 教师、学生表数据参数 */
		private $userId, $password;
		private $email = '', $avatar = '', $type = '';
		private $phone = '', $signature = '', $last_logined_at;

		/*****************************************************************************************************************************************************************/
		/**
		 *构造函数用于设置用户的基本信息
		 *@param int       $role      整型数值设置用户头衔(管理员-1, 教师-2, 学生-3)
		 *@param string    $name      设置用户真实姓名
		 *@param string    $idcard    设置用户身份证号
		 *@param string    $school    设置用户的学校
		 *@param string    $major     设置用户的专业
		 *@param string    $gender    设置用户性别，默认为男性
		 */
		function __construct($role, $name, $idcard, $school, $major, $gender = '男'){
				switch($role){
				case self::ADMIN: $this->role = '管理员'; break; case self::TEACHERS: $this->role = '教师'; break; case self::STUDENTS: $this->role = '学生'; break; }
				$this->name = $name; $this->idcard = $idcard;
				$this->school = $school; $this->major = $major; $this->gender = $gender;
		}
		/*****************************************************************************************************************************************************************/





		/*****************************************************************************************************************************************************************/
		/**
		 *调用该方法可以设置用户的账户和密码
		 *@param string    $account          设置账号
		 *@param string    $password         设置账号的密码
		 *
		 */
		function SetAccountInfo($account, $password){ $this->account = $account; $this->password = $password; return $this; }
		/*****************************************************************************************************************************************************************/





		/*****************************************************************************************************************************************************************/
		/**
		 *Set系列方法设置用户其他非必填信息
		 *@param string    $email        设置用户电子邮箱
		 *@param string    $avatar       用户本地图片的存放路径，用于设置用户头像
		 *@param string    $type         用户头像的图片类型(image/jpg, image/png, image/gif, image/jpeg)
		 *@param string    $phone        设置用户电话号码
		 *@param string    $signature    设置用户的个性签名
		 */
		function SetEmailInfo($email) { $this->email = $email; return $this; }
		function SetAvatarInfo($avatar, $type) { $this->avatar = $avatar; $this->type = $type; return $this; } //设置用户头像及头像图片的类型
		function SetPhoneInfo($phone) { $this->phone = $phone; return $this; }
		function SetSignatureInfo($signature) { $this->signature = $signature; return $this; } //设置用户个性签名
		/*****************************************************************************************************************************************************************/





		/*****************************************************************************************************************************************************************/
		/**
		 *调用该方法可以将数据存入用户个人详细信息表users_profile
		 *@param PDO    $pdo    PDO对象用于进行数据库操作
		 */
		function InsertIntoUsersProfile($pdo){
				try{
						$sql = "INSERT INTO users_profile(role, name, idcard, gender, school, major, account, created_at, updated_at) VALUES(?,?,?,?,?,?,?,?,?)";
						$stmt = $pdo->prepare($sql); /* 准备语句 */
						
						/* 将参数绑定到对应的问号?占位符 */
						$stmt->bindParam(1, $this->role); $stmt->bindParam(2, $this->name); $stmt->bindParam(3, $this->idcard); $stmt->bindParam(4, $this->gender);
						$stmt->bindParam(5, $this->school); $stmt->bindParam(6, $this->major); $stmt->bindParam(7, $this->account);
						$mysqltime = date('Y-m-d H:i:s', strtotime("now")); /* 获取当前系统时间并将其转换为mysql的timestamp格式 */
						$stmt->bindParam(8, $mysqltime); $stmt->bindParam(9, $mysqltime);
						
						$stmt->execute(); /* 执行准备好的语句 */
				}catch(PDOException $e){ /* 异常处理 */
						echo '<br/>Database insertion failed in register.class.php: '.$e->getMessage();
						exit;
				}
		}
		/*****************************************************************************************************************************************************************/





		/*********************************************************************************************************************************************************************************/
		/**
		 *调用该方法可以根据注册时的不同的role头衔类型，将数据分别存入学生表students和教师表teachers
		 *@param PDO    $pdo    PDO对象用于进行数据库操作
		 */
		function InsertIntoStudentsOrTeachers($pdo){
				try{
						$query = "SELECT id, account FROM users_profile WHERE idcard = $this->idcard";
						$result = $pdo->prepare($query);
						$result->execute();
						while(list($users_profile_id, $account) = $result->fetch(PDO::FETCH_NUM)){
								switch($this->role){ case '教师': $table = 'teachers'; break; case '学生': $table = 'students'; break; }
								$sql = "INSERT INTO $table(users_profile_id, account, password, email, avatar, type, phone, signature, last_logined_at) VALUES(?,?,?,?,?,?,?,?,?)";
								$stmt = $pdo->prepare($sql); /* 准备语句 */

								/* 绑定参数到对应的问号?占位符 */
								$stmt->bindParam(1, $users_profile_id); $stmt->bindParam(2, $account);
								$stmt->bindParam(3, md5($this->password)); /* 使用md5算法将密码加密后再存入数据库 */
								$stmt->bindParam(4, $this->email); $stmt->bindParam(5, $this->avatar);
								$stmt->bindParam(6, $this->type); $stmt->bindParam(7, $this->phone); $stmt->bindParam(8, $this->signature);
								$mysqltime = date('Y-m-d H:i:s', strtotime("now")); /* 获取当前系统时间并将其转换为mysql的timestamp格式 */
								$stmt->bindParam(9, $mysqltime);
								$stmt->execute(); /* 执行准备好的语句 */
								}
				}catch(PDOException $e){ /* 异常处理 */
						echo '<br/>Database selection or insertion failed in register.class.php: '.$e->getMessage();
						exit;
				}
		}
		/*********************************************************************************************************************************************************************************/
}
?>
