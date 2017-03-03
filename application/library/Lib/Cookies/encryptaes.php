<?php

namespace Lib\Cookies;

/*
*说明:1.不能用来加密过长数据, 会耗费过多到cpu，大约100位数据 加密解密过程
*     花费0.05秒。
*     2.尽量少到置放汉字字符，今初步测试，每增加10个汉字，会增加0.04秒
*/
class encryptaes extends \Lib\Cookies\encrypt
{

	private $aes;

	public function __construct($key)
	{
            $this->aes = new \Lib\Cookies\aes($key);
	}
	
	public function get_name()
	{
		return "aes";
	}

	public function encrypt($data)
	{
		$data = rawurlencode($data);
		return base64_encode($this->aes->encrypt($data));
	}

	public function decrypt($data)
	{
		return rawurldecode($this->aes->decrypt(base64_decode($data)));
	}
}
?>
