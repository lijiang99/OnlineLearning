<?php
/**
 * file: course.class.php
 * 本类的实例对象为课程，可用于设置并获取课程的相关信息
 */

class Course{
		/* 课程的相关信息，其中$teachers_id创建人，$title课程名为必须信息 */
		private $teachers_id, $title, $cover = '', $type = '', $summary = '';
		/* 课程状态，有三种: 0-'未开始' 1-'已开始' 2-'已结束' */
		private $status = 0;
		const NOT_START = 0; const START = 1; const FINISH = 2;
		private $chapters_count = 0;
		private $chapter;

		/**********************************************************************************************************************/
		/**
		 * 构造函数用于实例化一个课程对象
		 * @param int       $teachers_id    整型数值用于设置课程创建人信息
		 * @param string    $title          设置课程名
		 */
		function __construct($teachers_id, $title){
				$this->teachers_id = $teachers_id;
				$this->title = $title;
		}
		/**********************************************************************************************************************/





		/**********************************************************************************************************************/
		/**
		 * __set魔术方法,设置课程类成员属性值
		 * @param string    $properName     成员属性名
		 * @param mixed     $properValue    成员属性值
		 */
		function __set($properName, $properValue){
				if($properName == "teachers_id"){ /* 当参数属性名是teachers_id时，不允许修改，直接结束方法执行 */
						echo '<br/>无权修改teachers_id';
						return;
				}else if($properName == "status"){
						/* 当参数的属性名是status时，第二个参数值只能为: 0,1,2 */
						if($properValue != self::NOT_START && $properValue != self::START && $properValue != self::FINISH)
								return; /* 如果是非法参数返回空，结束方法执行 */
				}
				/* 根据参数决定为哪个属性赋值，传入不同的成员属性名，赋予传入相应的值 */
				$this->$properName = $properValue;
		}
		/**********************************************************************************************************************/





		/**********************************************************************************************************************/
		/**
		 * __get魔术方法，在直接获取属性值时会自动调用，以属性名作为参数传入并处理
		 * @param  string    $properName    成员属性名
		 * @return mixed                    返回属性值
		 */
		function __get($properName){ return $this->$properName; }
		/**********************************************************************************************************************/
}
?>
