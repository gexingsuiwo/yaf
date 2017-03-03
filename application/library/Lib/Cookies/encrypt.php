<?php
namespace Lib\Cookies;

//加密类型接口，加密类型，都基于此类扩展
abstract class encrypt
{
	static $method = array('aes');
	//加密类型.
	abstract public function get_name();

	//加密方法，返回加密后到密文
	abstract public function encrypt($data);

	//解密方法，返回解密后到明文
	public function decrypt($endata)
	{
		return $this->decrypt($endata);
	}

	/**
	 * @static
	 * @param string $name
	 * @param  $key
	 * @return baccarat_cookie_encrypt
	 */
	static public function get_method( $key, $name='aes' )
	{
		if(in_array($name,self::$method))
		{
			$classname = 'encrypt'.$name;
                        
                        $obj = '\Lib\Cookies\\' . $classname;
                       
			return new $obj($key);
		}
		else
		{
			return null;
		}
	}
}
?>