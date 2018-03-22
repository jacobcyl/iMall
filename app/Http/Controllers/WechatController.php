<?php

namespace App\Http\Controllers;

use EasyWeChat\Message\Transfer;
use Illuminate\Http\Request;
use App\Http\Requests;
use EasyWeChat\Foundation\Application;
use EasyWeChat\Message\Image;
use EasyWeChat\Message\News;
use EasyWeChat\Message\Text;
use App\WechatFollow;
use App\WechatMenu;
use Illuminate\Support\Facades\Log;

/**
 * Class WechatController
 * @package App\Http\Controllers
 */
class WechatController extends Controller
{

    /**
     * 处理微信请求
     *
     * @param Application $wechat
     * @return mixed
     */

    public function debug()
    {

    }

    public function serve()
    {
        $wechat = app('wechat');
        $server = $wechat->server;
        $userApi = $wechat->user;

        $server->setMessageHandler(function ($message) use ($userApi) {
            switch($message->MsgType){
                case 'event':
                    return $this->handleEvent($userApi, $message);
                case 'text':
                    return $this->handleText($userApi, $message);
                case 'image':
                case 'voice':
                default:
                    return '欢迎';

            }
        });

        return $server->serve();
    }

    protected function handleEvent($userApi, $message) {
        // 获取当前粉丝openId
        $openid = $message->FromUserName;
        switch ($message->Event) {
            case'subscribe':
                // 获取当前粉丝基本信息
                $user = $userApi->get($openid);
                // 判断当前粉丝是否以前关注过
                $oldFollow = WechatFollow::where('openid', '=', $openid)->first();
                if ($oldFollow) {
                    $follow['nickname'] = $user->nickname;
                    $follow['sex'] = ($user->sex + 1);
                    $follow['language'] = $user->language;
                    $follow['city'] = $user->city;
                    $follow['country'] = $user->country;
                    $follow['province'] = $user->province;
                    $follow['headimgurl'] = $user->headimgurl;
                    $follow['remark'] = $user->remark;
                    $follow['groupid'] = $user->groupid;
                    $follow['is_subscribed'] = 2;
                    WechatFollow::where('openid', '=', $openid)->update($follow);
                    $welcome = "欢迎回来，" . $user->nickname ."\n\n进入商城闲逛一会吧，\n\n<a href=\"http://imall.lovchun.com/mall#!/index\">点击进入</a>";
                    return $welcome;
                } else {
                    // 录入数据库
                    $follow = new WechatFollow();
                    $follow->openid = $openid;
                    $follow->nickname = $user->nickname;
                    $follow->sex = ($user->sex + 1);
                    $follow->language = $user->language;
                    $follow->city = $user->city;
                    $follow->country = $user->country;
                    $follow->province = $user->province;
                    $follow->headimgurl = $user->headimgurl;
                    $follow->remark = $user->remark;
                    $follow->groupid = $user->groupid;
                    $follow->is_subscribed = 2;
                    $follow->save();
                    $welcome = "欢迎，" . $user->nickname ."\n\n进入商城闲逛一会吧，\n\n<a href=\"http://mall.henlink.com/mall#!/index\">点击进入</a>";
                    return $welcome;
                }
                break;
            case 'unsubscribe':
                WechatFollow::where('openid', '=', $openid)->update(['is_subscribed' => 1]);
                return;
            default:
                return;
        }
    }

    /**
     * 响应文本消息
     * @param $userAPi
     * @param $message
     * @return Text|Transfer
     */
    protected function handleText($userAPi, $message){
        $text = new Text();
        switch($message->Content){
            case '##Menu**'://create menu
                if(self::createMenu())
                    $text->content = '创建菜单成功';
                else
                    $text->content = '创建菜单失败';

                Log::debug("创建菜单");
                return $text;
            case '客服':
                Log::debug("转发至客服系统");
                return self::transferToKf($message);
            default:
                $text->content = '亲, 这个问题您可以通过回复[客服]两字转接到人工客服哦';
                return $text;

        }

    }

    /**
     * 转发到多客服系统
     * @param $message
     * @return Transfer
     */
    protected function transferToKf($message){
        return new Transfer();
    }
    /**
     * 创建自定义菜单
     * @return bool
     */
    protected function createMenu(){
        $buttons =  [
            [
                "type"=>"view",
                "name"=>"商城",
                "url"=>url('/mall')
            ]
        ];

        $menu = $this->wechat->menu;
        if($menu->add($buttons)){
            return true;
        }else{
            return false;
        }
    }
}
