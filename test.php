<?php

//
// 接收用户消息
// 微信公众账号接收到用户的消息类型判断
//

define("TOKEN", "weixin");
include"curl.php";
$wechatObj = new wechatCallbackapiTest();
if (!isset($_GET['echostr'])) {
    $wechatObj->responseMsg();
}else{
    $wechatObj->valid();
}

class wechatCallbackapiTest
{
    public function valid()
    {
        $echoStr = $_GET["echostr"];
        if($this->checkSignature()){
            echo $echoStr;
            exit;
        }
    }

    private function checkSignature()
    {
        $signature = $_GET["signature"];
        $timestamp = $_GET["timestamp"];
        $nonce = $_GET["nonce"];

        $token = TOKEN;
        $tmpArr = array($token, $timestamp, $nonce);
        sort($tmpArr);
        $tmpStr = implode( $tmpArr );
        $tmpStr = sha1( $tmpStr );

        if( $tmpStr == $signature ){
            return true;
        }else{
            return false;
        }
    }

    public function responseMsg()
    {
        $postStr = $GLOBALS["HTTP_RAW_POST_DATA"];//接受用户断发过来的xml数据
        if (!empty($postStr)){
            $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
            $RX_TYPE = trim($postObj->MsgType);

            //用户发送的消息类型判断
            switch ($RX_TYPE)
            {
                case "text":    //文本消息
                    $result = $this->receiveText($postObj);
                    break;
                case "image":   //图片消息
                    $result = $this->receiveImage($postObj);
                    break;

                case "voice":   //语音消息
                    $result = $this->receiveVoice($postObj);
                    break;
                case "video":   //视频消息
                    $result = $this->receiveVideo($postObj);
                    break;
                case "location"://位置消息
                    $result = $this->receiveLocation($postObj);
                    break;
                case "link":    //链接消息
                    $result = $this->receiveLink($postObj);
                    break;
                default:
                    $result = "unknow msg type: ".$RX_TYPE;
                    break;
            }
            echo $result;
        }else {
            echo "";
            exit;
        }
    }

    /*
     * 接收文本消息
     */
    private function receiveText($object)
    {
        $keyword =$object->Content;
        $keyword1=substr($keyword,0,6);
        if($keyword1=='音乐')
        {
            $title=substr($keyword,6,strlen($keyword)-6);
           
            $msgType ="music";
            $desc = "QQ音乐搜素";
            $url = "http://route.showapi.com/213-1?showapi_appid=43280&showapi_sign=64b617f258a64415a421bf6578659616&keyword=".$title."&page=1&"; 
            $contents = file_get_contents($url); 
            $pieces = explode('{', $contents);
            $i=4;
            $pie = explode('"', $pieces[$i]);
            $url= $pie[$i-1];

            $hqurl = $url;

            $musicTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <Music>
                        <Title><![CDATA[%s]]></Title>
                        <Description><![CDATA[%s]]></Description>
                        <MusicUrl><![CDATA[%s]]></MusicUrl>
                        <HQMusicUrl><![CDATA[%s]]></HQMusicUrl>
                        </Music>
                        <FuncFlag>0</FuncFlag>
                        </xml>";
            $result = sprintf($musicTpl, $object->FromUserName, $object->ToUserName, time(), $msgType,$title,$desc,$url,$hqurl);
            return $result;
        }
         elseif($keyword1=='图文')
                {
                   
                    $msgType ="news";
                    $count ='4';
                    $str = '<Articles>';
                    $url ="http://hanyu.baidu.com/zici/s?wd=%E9%9F%B3%E4%B9%90&query=%E9%9F%B3%E4%B9%90&srcid=28232&from=kg0&from=kg0";
                    for($i=1;$i<=$count;$i++)
                    {
                        $str.="<item>
                                <Title><![CDATA[测试{$i}]]></Title> 
                                <Description><![CDATA[测试是事实]]></Description>
                                <PicUrl><![CDATA[http://www.caption-he.com.cn/weixin/{$i}.jpg]]></PicUrl>
                                <Url><![CDATA[{$url}]]></Url>
                                </item>
                                ";
                    }
                    $str .='</Articles>';
                    

                    $newsTpl = "<xml>
                        <ToUserName><![CDATA[%s]]></ToUserName>
                        <FromUserName><![CDATA[%s]]></FromUserName>
                        <CreateTime>%s</CreateTime>
                        <MsgType><![CDATA[%s]]></MsgType>
                        <ArticleCount>%s</ArticleCount>
                        %s
                        </xml>";
                    $result = sprintf($newsTpl, $object->FromUserName, $object->ToUserName, time(), $msgType,$count,$str);
                    return $result;
                }
        elseif($keyword1=='笑话')
        {
            $url = "http://route.showapi.com/107-32?showapi_appid=43281&showapi_sign=3a894ba04e2d4c9da9cb159f7f2740b2&"; 
            $cont = file_get_contents($url); 

            //如果出现中文乱码使用下面代码 
            //$getcontent = iconv("gb2312", "utf-8",$contents); 
            $pieces = explode('{', $cont);
            $pie = explode('"', $pieces[3]);
            $str=strip_tags($pie[3]);
            $content = "每日一笑：".$str;
            $result = $this->transmitText($object, $content); 
            return $result;
        }  
        elseif($keyword1=='猜谜')
        {
            $url = "http://route.showapi.com/151-2?showapi_appid=43390&showapi_sign=86ffcead3c714f0c891cb3d2eaf9de03&"; 
            $cont = file_get_contents($url); 

            //如果出现中文乱码使用下面代码 
            //$getcontent = iconv("gb2312", "utf-8",$contents); 
            $pieces = explode('{', $cont);
      
            $pie = explode('"', $pieces[4]);
            $str=strip_tags($pie[3]);      
            $str2=strip_tags($pie[7]);
            $str3=strip_tags($pie[15]);
            $content = $str."     ".$str3."**********************************".
            
            $str2;
            $result = $this->transmitText($object, $content); 
            return $result;
        }
        else
        {
            $content = "你发送的是文本，内容为：".$object->Content;
            $result = $this->transmitText($object, $content); 
            return $result;
        }
       
    
    }
/*
     

    /*
     * 接收图片消息
     */
    private function receiveImage($object)
    {
        $content = "你发送的是图片，地址为：".$object->PicUrl;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收语音消息
     */
    private function receiveVoice($object)
    {
        $content = "你发送的是语音，媒体ID为：".$object->MediaId;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收视频消息
     */
    private function receiveVideo($object)
    {
        $content = "你发送的是视频，媒体ID为：".$object->MediaId;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收位置消息
     */
    private function receiveLocation($object)
    {
        $content = "你发送的是位置，纬度为：".$object->Location_X."；经度为：".$object->Location_Y."；缩放级别为：".$object->Scale."；位置为：".$object->Label;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 接收链接消息
     */
    private function receiveLink($object)
    {
        $content = "你发送的是链接，标题为：".$object->Title."；内容为：".$object->Description."；链接地址为：".$object->Url;
        $result = $this->transmitText($object, $content);
        return $result;
    }

    /*
     * 回复文本消息
     */
    private function transmitText($object, $content)
    {
        $textTpl = "<xml>
            <ToUserName><![CDATA[%s]]></ToUserName>
            <FromUserName><![CDATA[%s]]></FromUserName>
            <CreateTime>%s</CreateTime>
            <MsgType><![CDATA[text]]></MsgType>
            <Content><![CDATA[%s]]></Content>
            </xml>";
        $result = sprintf($textTpl, $object->FromUserName, $object->ToUserName, time(), $content);
        return $result;
    }

    
}
?>
