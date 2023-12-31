<?php 
 Namespace Nicelizhi\Doudian\open\core;

class DoudianOpClient
{
    private $httpClient;
    function __construct() {
        $this->httpClient = Nicelizhi\Doudian\open\Core\Http\HttpClient::getInstance();
    }

    public function request($request, $accessToken) {
        $config = $request->getConfig();
        $urlPath = $request->getUrlPath();
        $method = $this->getMethod($urlPath);
        $paramJson = Nicelizhi\Doudian\open\SignUtil::marshal($request->getParam());
        $appKey = $config->appKey;
        $appSecret = $config->appSecret;
        $timestamp = time();
        $sign = Nicelizhi\Doudian\open\SignUtil::sign($appKey, $appSecret, $method, $timestamp, $paramJson);
        $openHost = $config->openRequestUrl;
        $accessTokenStr = "";
        if($accessToken != null) {
            $accessTokenStr = $accessToken->getAccessToken();
        }

        //String requestUrlPattern = "%s/%s?app_key=%s&method=%s&v=2&sign=%s&timestamp=%s&access_token=%s";
        $requestUrl = $openHost.$urlPath."?"."app_key=".$appKey."&method=".$method."&v=2&sign=".$sign."&timestamp=".$timestamp."&access_token=".$accessTokenStr."&sign_method=hmac-sha256";

        //发送http请求
        $httpRequest = new HttpRequest();
        $httpRequest->url = $requestUrl;
        $httpRequest->body = $paramJson;
        $httpRequest->connectTimeout = $config->httpConnectTimeout;
        $httpRequest->readTimeout = $config->httpReadTimeout;
        $httpResponse = $this->httpClient->post($httpRequest);

        return json_decode($httpResponse->body, false, 512, JSON_UNESCAPED_UNICODE);
    }

    private function getMethod($urlPath) {
        if (strlen($urlPath) == 0) {
            return $urlPath;
        }
        $methodPath = "";
        if (substr($urlPath, 0, 1) == "/") {
            $methodPath = substr($urlPath, 1, strlen($urlPath));
        } else {
            $methodPath = $urlPath;
        }
        return str_replace("/", ".", $methodPath);
    }

    private static $defaultInstance;

    public static function getInstance(){

        if(!(self::$defaultInstance instanceof self)){
            self::$defaultInstance = new self();
        }
        return self::$defaultInstance;
    }

}