<?php


namespace tools\encrypt;


use Exception;

class Encrypt
{
    const BLOCK_SIZE = 16;

    /**
     * 加密函数
     * @param $text
     * @param $aesKey
     * @return false|string
     */
    public static function encode($text, $aesKey)
    {
        try {
            $iv = $aesKey;
            $text = self::AesPKCSEncode($text);
            $encrypt = openssl_encrypt($text, 'AES-128-CBC', $aesKey, OPENSSL_ZERO_PADDING, $iv);
            return base64_encode($encrypt);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * 解密函数
     * @param $encrypt
     * @param $aesKey
     * @return false|string
     */
    public static function decode($encrypt, $aesKey)
    {
        try {
            $iv = $aesKey;
            $encrypt = base64_decode($encrypt);
            $decrypt = openssl_decrypt($encrypt, 'AES-128-CBC', $aesKey, OPENSSL_ZERO_PADDING, $iv);
            return self::AesPKCSDecode($decrypt);
        } catch (Exception $e) {
            return false;
        }
    }

    /**
     * PKCS编码
     * @param $text
     * @return string
     */
    public static function AesPKCSEncode($text): string
    {
        $amountToPad = self::BLOCK_SIZE - (strlen($text) % self::BLOCK_SIZE);
        if ($amountToPad == 0) {
            $amountToPad = self::BLOCK_SIZE;
        }
        $padChr = chr($amountToPad);
        $tmp = "";
        for ($i = 0; $i < $amountToPad; $i++) {
            $tmp .= $padChr;
        }
        return $text . $tmp;
    }

    /**
     * PKCS解码
     * @param $text
     * @return string
     */
    public static function AesPKCSDecode($text): string
    {
        $pad = ord(substr($text, -1));
        if ($pad < 1 || $pad > self::BLOCK_SIZE) {
            $pad = 0;
        }
        return substr($text, 0, (strlen($text) - $pad));
    }
}