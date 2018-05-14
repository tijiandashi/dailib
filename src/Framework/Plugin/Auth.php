<?php
/**
 * Created by PhpStorm.
 * User: liujun
 * Date: 2017/11/21
 * Time: 下午7:18
 */

namespace Dai\Lib\Framework\Plugin;

use Phalcon\Events\Event;
use Phalcon\Mvc\Dispatcher;
use Phalcon\Mvc\User\Plugin;

/**
 * Class AuthPlugin
 * @package DaiBk\Plugin
 */
class Auth  extends Plugin
{
    /**
     * 这个事件在dispatcher中的每个路由被执行前执行
     */
    public function beforeExecuteRoute(Event $event, Dispatcher $dispatcher)
    {
        $di = \Phalcon\DI::getDefault();
        /** @var \DaiBk\Models\Base\BasePageInfo $basePageInfo */
        $basePageInfo = $di->get('basePageInfo');
        //如果不需要登录
        if( $basePageInfo -> login == false){
            return true;
        }

        /** @var \DaiBk\Models\Base\BaseSessionInfo $sessionData */
        $sessionData = $di->get('session')->get('auth');
        if( $sessionData == null){
            header("Location: /user/login"); exit;
        }

        $dataUser = new \DaiBk\Models\User\Data\User();
        /** @var \DaiBk\Models\User\DataObject\User $userInfo */
        $userInfo = $dataUser->getByUid( $sessionData->uid  );

        $sessionData->phone = $userInfo->phone;
        $sessionData->userName = $userInfo->userName;
        $sessionData->nickName = $userInfo->nickName;
        $basePageInfo->sessionInfo = $sessionData;
    }
}