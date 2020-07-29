<?php
namespace App\Http\Utils;

class SMS
{
    public static function randCode()
    {
        // 产生随机数字验证码
        $key = '';
        $pattern = '1234567890';
        for ($i = 0; $i < 6; $i++) {
            $key .= $pattern[mt_rand(0, 9)];
        }
        return $key; // 470319
    }

    public static function sendSMS($to, $code)
    {
        $postUrl = 'https://http-api.d7networks.com/send';
        $codeStr ='U&ME,code:' . $code;
        $curlPost = ['to' => $to, 'from' => 'u&me', 'content' => $codeStr, 'username' => '', 'password' => ''];
        $ch = curl_init(); //初始化curl
        curl_setopt($ch, CURLOPT_URL, $postUrl); //抓取指定网页
        curl_setopt($ch, CURLOPT_HEADER, 0); //设置header
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); //要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_POST, 1); //post提交方式
        curl_setopt($ch, CURLOPT_POSTFIELDS, $curlPost);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); //绕过ssl验证
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch); //运行curl
        curl_close($ch);
        return $data;
    }
}
