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
        $curlPost = ['to' => $to, 'from' => 'u&me', 'content' => '【u&me】code:'+$code, 'username' => 'marc2209', 'password' => 'Pmtgh7Fl'];
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
        // $response = aRequest::post("https://d7sms.p.rapidapi.com/secure/send",
        //     array(
        //         "X-RapidAPI-Host" => "d7sms.p.rapidapi.com",
        //         "X-RapidAPI-Key" => "469c79b6demsh4e833fa8405f81ap1cfb6cjsn809b255bf49f",
        //         "Authorization" => "Basic bHVrYTk3MDM6T1Z4YnUyaW0=",
        //         "Content-Type" => "application/json",
        //         "Accept" => "application/json",
        //     ),
        //     "{\"to\":" . $to . ",\"from\":\"友密\",\"content\":\"" . $code . "\"}"
        // );

        // return $response;

        // $baseurl = 'https://http-api.d7networks.com/send';

        // $params = '?username=marc2209';
        // $params .= '&password=Pmtgh7Fl';
        // $params .= '&to=' . urlencode($to);
        // $params .= '&content=' . urlencode($code);

        // $response = file_get_contents($baseurl . $params);

        // $data = array(
        //     'to' => $to,
        //     'from' => 'u&me',
        //     'content' => '【u&me】code:'+$code,
        //     'username' => 'marc2209',
        //     'password' => 'Pmtgh7Fl',
        // );

        // $query = http_build_query($data);

        // $options['http'] = array(
        //     'timeout' => 60,
        //     'method' => 'POST',
        //     'header' => 'Content-type:application/json',
        //     'content' => $query,
        // );

        // $url = "https://http-api.d7networks.com/send";
        // $context = stream_context_create($options);
        // $response = file_get_contents($url, false, $context);

        return $response;
    }
}
