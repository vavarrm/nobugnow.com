<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');
include_once(APPPATH."third_party/Token.class.php");
class MyToken extends tokenClass
{
	private $CI ;

	
	public function __construct() 
	{
		$this->CI =& get_instance();
	}
 
    /**     
     * 获取私钥     
     * @return bool|resource     
     */    
    
	private static function getPrivateKey() 
    {        
        $privKey = parent::$PRIVATE_KEY;       

        return openssl_pkey_get_private($privKey, "phrase");    
    }   
	
    /**     
     * 获取公钥     
     * @return bool|resource     
     */    
    private static function getPublicKey()
    {        
        $publicKey = parent::$PUBLIC_KEY;        
		
        return openssl_pkey_get_public($publicKey);    
    }    
	
	/*
	* 產生RandomKey
	*
	*
	*/
	public  function getRandomKey($length=5)
	{
		$chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*()-_ []{}<>~`+=,.;/?|';   
		$random_key ='';
		for ( $i = 0; $i < $length; $i++ )  
		{  
			$random_key .= $chars[ mt_rand(0, strlen($chars) - 1) ];  
		}  
		return $random_key;  
	}
	
	
	/**     
     * AES加密     
     * @param string $data  	加密字串    
     * @param string $random_key   RandomKey    
     */
	public function AesEncrypt($data, $random_key)
	{
		$encrypt = openssl_encrypt($data, self::$AES_METHOD, $random_key, 0, self::$IV);
		return base64_encode($encrypt) ;
	}
	
	/**     
     * AES解密     
     * @param string $encrypt  密文    
     */
	public function AesDecrypt($encrypt, $random_key)
	{
		$encrypt = base64_decode($encrypt);
		$decrypt = openssl_decrypt($encrypt, self::$AES_METHOD, $random_key, 0, self::$IV);
		return $decrypt;
	}
	
	
    /**     
     * 私钥加密     
     * @param string $data     
     * @return null|string     
     */    
    public static function privateEncrypt($data = '')    
    {        
        if (!is_string($data)) {            
            return null;       
        }        
        return openssl_private_encrypt($data,$encrypted,self::getPrivateKey(), OPENSSL_PKCS1_PADDING) ? base64_encode($encrypted) : null;    
    }    

    /**     
     * 公钥加密     
     * @param string $data     
     * @return null|string     
     */    
    public static function publicEncrypt($data = '')   
    {        
        if (!is_string($data)) {            
            return null;        
        }        
        return openssl_public_encrypt($data,$encrypted,self::getPublicKey(), OPENSSL_PKCS1_PADDING) ? base64_encode($encrypted) : null;    
    }    

    /**     
     * 私钥解密     
     * @param string $encrypted     
     * @return null     
     */    
    public static function privateDecrypt($encrypted = '')    
    {        
        if (!is_string($encrypted)) {            
            return null;        
        }        
        return (openssl_private_decrypt(base64_decode($encrypted), $decrypted, self::getPrivateKey(), OPENSSL_PKCS1_PADDING)) ? $decrypted : null;    
    }    

    /**     
     * 公钥解密     
     * @param string $encrypted     
     * @return null     
     */    
    public static function publicDecrypt($encrypted = '')    
    {        
        if (!is_string($encrypted)) {            
            return null;        
        }        
		return (openssl_public_decrypt(base64_decode($encrypted), $decrypted, self::getPublicKey(), OPENSSL_PKCS1_PADDING)) ? $decrypted : null;    
    }

}