<?php
class tokenClass
{
	public function __construct() 
	{

	}
	
	protected static $PRIVATE_KEY = '-----BEGIN RSA PRIVATE KEY-----
MIICXQIBAAKBgQC9imyxhcLNxPEWUxngnn6+JPL25ya+gIpdkodYdfF/mt/mNRJX
JizSms9K6HAG8jP/+edT3Mfls8hpTNR8svarW/dOHPSWiOONae8gBZaXYqVJNa6U
jntQrTzBb5wdgLTEECulHoK1DproFBM6hP3LNY5OCXjZ741y8mVa0kiOewIDAQAB
AoGAWgIsT7knozPNET7xYPujUISXZKysd3bvPjRhVZ7cyi4v+VBmn0AftPuTSQ1M
dd/61apFMkv8GZbgqzCzD2ylOjUSXRrUpDxil1gkUAM5ns1wUxb4/Yu1cslcZyl4
gL5G93cx/53J4v7KaSlczcHWzLPP3/LG+LAtxyLEIFyUggECQQDu1Oaa967+NSh8
9eICnWZ1n1OgvhizakpM+EKYZQF1FW5cmJGJTIKMvRj8Ih+5k/w0IWbSRec33IyS
ZTD+kTaBAkEAyypylg+I/LcmCt5cAZxns0UHkaYBDo0Y4I/ngWQinpn5RrhZQZfp
fY+h4xE90HWVVqOKSvdMvxtUXU8FpQUe+wJBAJEB5O3sOmyP/AA7DjmGNcJutUjg
goDUpT4sccqzcPoUxAgmfh69vHoVCglz8o0rg7JnIVXEKYnqN9Ne6yt1IYECQDz8
lAMZzLxX2jKfBy1wnuyAh0Ige+a7UkFu0UbVIVNM0zh4dEqtaGjJqgX4kf62nUqx
svzH+aDQemW8J+yeeesCQQDNv+NTvsIM3dvlFf5UM2MxLkKfCA8aR7uNoL2WGnG/
OgFU5vhjDE+m7+LmMKflBnpvBxnsWaIkDdZkVVeLjLiU
-----END RSA PRIVATE KEY-----
';    
   protected static $PUBLIC_KEY = '-----BEGIN PUBLIC KEY-----
MIGfMA0GCSqGSIb3DQEBAQUAA4GNADCBiQKBgQC9imyxhcLNxPEWUxngnn6+JPL2
5ya+gIpdkodYdfF/mt/mNRJXJizSms9K6HAG8jP/+edT3Mfls8hpTNR8svarW/dO
HPSWiOONae8gBZaXYqVJNa6UjntQrTzBb5wdgLTEECulHoK1DproFBM6hP3LNY5O
CXjZ741y8mVa0kiOewIDAQAB
-----END PUBLIC KEY-----';   
	private static $PADDING = OPENSSL_PKCS1_PADDING;
	protected static $AES_METHOD = 'aes-256-cbc';
	protected static $IV = 'ix0vYQiZYu845Zis';
    /**     
     * 获取私钥     
     * @return bool|resource     
     */    
    
	private static function getPrivateKey() 
    {        
        $privKey = self::$PRIVATE_KEY;       

        return openssl_pkey_get_private($privKey, "phrase");    
    }   
	
    /**     
     * 获取公钥     
     * @return bool|resource     
     */    
    private static function getPublicKey()
    {        
        $publicKey = self::$PUBLIC_KEY;        
		
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
?>