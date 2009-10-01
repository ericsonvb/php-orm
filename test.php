<?php
	/*
		test.php
	*/

	include_once("ORMBase.php");

	class Post extends ORMBase
	{
		protected $tablename = "wp_posts";

		protected function __construct()
		{
		
		}

		public function getConnectionParams()
		{
			return array("server"=>"localhost","database"=>"cumbaza","user"=>"root","password"=>"trujillo","driver"=>"mysql");
		}
	}

	$post = new Post();
	print_r($post);
?>