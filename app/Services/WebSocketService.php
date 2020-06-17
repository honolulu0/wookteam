<?php

namespace App\Services;

use App\Module\Base;
use App\Module\Chat;
use App\Module\Users;
use App\Tasks\ChromeExtendTask;
use App\Tasks\PushTask;
use Cache;
use DB;
use Hhxsv5\LaravelS\Swoole\Task\Task;
use Hhxsv5\LaravelS\Swoole\WebSocketHandlerInterface;
use Swoole\Http\Request;
use Swoole\WebSocket\Frame;
use Swoole\WebSocket\Server;

/**
 * @see https://wiki.swoole.com/#/start/start_ws_server
 */
class WebSocketService implements WebSocketHandlerInterface
{
    /**
     * 声明没有参数的构造函数
     * WebSocketService constructor.
     */
    public function __construct()
    {

    }

    /**
     * 连接建立时触发
     * @param Server $server
     * @param Request $request
     */
    public function onOpen(Server $server, Request $request)
    {
        global $_A;
        $_A = [
            '__static_langdata' => [],
        ];
        //判断参数
        $fd = $request->fd;
        if (!isset($request->get['token'])) {
            $server->push($fd, Chat::formatMsgSend([
                'messageType' => 'error',
                'body' => [
                    'error' => '参数错误'
                ],
            ]));
            $server->close($fd);
            $this->deleteUser($fd);
            return;
        }
        //判断token
        $token = $request->get['token'];
        $channel = $request->get['channel'] ?: '';
        $cacheKey = "ws::token:" . md5($token);
        $username = Cache::remember($cacheKey, now()->addSeconds(1), function () use ($token) {
            list($id, $username, $encrypt, $timestamp) = explode("@", base64_decode($token) . "@@@@");
            if (intval($id) > 0 && intval($timestamp) + 2592000 > time()) {
                if (DB::table('users')->where(['id' => $id, 'username' => $username, 'encrypt' => $encrypt])->exists()) {
                    return $username;
                }
            }
            return null;
        });
        if (empty($username)) {
            Cache::forget($cacheKey);
            $server->push($fd, Chat::formatMsgSend([
                'messageType' => 'error',
                'channel' => $channel,
                'body' => [
                    'error' => '会员不存在',
                ],
            ]));
            $server->close($fd);
            $this->deleteUser($fd);
            return;
        }
        //踢下线
        /*if ($channel != 'chromeExtend') {
            $userLists = $this->getUser('', $channel, $username);
            foreach ($userLists AS $user) {
                $server->push($user['fd'], Chat::formatMsgSend([
                    'messageType' => 'kick',
                    'channel' => $channel,
                    'body' => [
                        'ip' => Base::getIp(),
                        'time' => time(),
                        'newfd' => $fd,
                    ],
                ]));
                $this->deleteUser($user['fd']);
            }
        }*/
        //保存用户、发送open事件
        $this->saveUser($fd, $channel, $username);
        $server->push($fd, Chat::formatMsgSend([
            'messageType' => 'open',
            'channel' => $channel,
            'body' => [
                'fd' => $fd,
            ],
        ]));
        //发送最后一条未发送的信息
        $lastMsg = Base::DBC2A(DB::table('chat_msg')->where('receive', $username)->orderByDesc('indate')->first());
        if ($lastMsg && $lastMsg['roger'] === 0) {
            $dialog = Chat::openDialog($lastMsg['username'], $lastMsg['receive']);
            if (!Base::isError($dialog)) {
                $dialog = $dialog['data'];
                $unread = intval(DB::table('chat_dialog')->where('id', $dialog['id'])->value(($dialog['recField'] == 1 ? 'unread1' : 'unread2')));
                $body = Base::string2array($lastMsg['message']);
                $body['id'] = $lastMsg['id'];
                $body['resend'] = 1;
                $body['unread'] = $unread;
                $body['username'] = $lastMsg['username'];
                $body['userimg'] = Users::userimg($lastMsg['username']);
                $body['indate'] = $lastMsg['indate'];
                $server->push($fd, Chat::formatMsgSend([
                    'messageType' => 'user',
                    'contentId' => $lastMsg['id'],
                    'channel' => $channel,
                    'username' => $lastMsg['username'],
                    'target' => $lastMsg['receive'],
                    'body' => $body,
                    'time' => $lastMsg['indate'],
                ]));
            }
        }
    }

    /**
     * 收到消息时触发
     * @param Server $server
     * @param Frame $frame
     */
    public function onMessage(Server $server, Frame $frame)
    {
        global $_A;
        $_A = [
            '__static_langdata' => [],
        ];
        //
        $data = Chat::formatMsgReceive($frame->data);
        $back = [
            'status' => 1,
            'message' => '',
        ];
        //
        switch ($data['messageType']) {
            /**
             * 刷新
             */
            case 'refresh':
                DB::table('ws')->where([
                    'fd' => $frame->fd,
                    'channel' => $data['channel'],
                ])->update(['update' => time()]);
                break;

            /**
             * 总未读消息数
             */
            case 'unread':
                $username = DB::table('ws')->where([
                    'fd' => $frame->fd,
                    'channel' => $data['channel'],
                ])->value('username');
                if ($username) {
                    $num = intval(DB::table('chat_dialog')->where('user1', $username)->sum('unread1'));
                    $num+= intval(DB::table('chat_dialog')->where('user2', $username)->sum('unread2'));
                    $back['message'] = $num;
                } else {
                    $back['message'] = 0;
                }
                break;

            /**
             * 已读会员消息
             */
            case 'read':
                $username = DB::table('ws')->where([
                    'fd' => $frame->fd,
                    'channel' => $data['channel'],
                ])->value('username');
                $dialog = Chat::openDialog($username, $data['target']);
                if (!Base::isError($dialog)) {
                    $dialog = $dialog['data'];
                    $upArray = [];
                    if ($dialog['user1'] == $dialog['user2']) {
                        $upArray['unread1'] = 0;
                        $upArray['unread2'] = 0;
                    } else {
                        $upArray[($dialog['recField'] == 1 ? 'unread2' : 'unread1')] = 0;
                    }
                    DB::table('chat_dialog')->where('id', $dialog['id'])->update($upArray);
                }
                $chromeExtendTask = new ChromeExtendTask($username);
                Task::deliver($chromeExtendTask);
                break;

            /**
             * 收到信息回执
             */
            case 'roger':
                if ($data['contentId'] > 0) {
                    $username = DB::table('ws')->where([
                        'fd' => $frame->fd,
                        'channel' => $data['channel'],
                    ])->value('username');
                    DB::table('chat_msg')->where([
                        'id' => $data['contentId'],
                        'receive' => $username,
                    ])->update([
                        'roger' => 1,
                    ]);
                }
                break;

            /**
             * 发给用户
             */
            case 'user':
                $username = DB::table('ws')->where([
                    'fd' => $frame->fd,
                    'channel' => $data['channel'],
                ])->value('username');
                $res = Chat::saveMessage($username, $data['target'], $data['body']);
                if (Base::isError($res)) {
                    $back = [
                        'status' => 0,
                        'message' => $res['msg'],
                    ];
                } else {
                    $resData = $res['data'];
                    $back['message'] = $resData['id'];
                    $data['contentId'] = $resData['id'];
                    $data['body']['id'] = $resData['id'];
                    $data['body']['unread'] = $resData['unread'];
                    //
                    $pushLists = [];
                    foreach ($this->getUserOfName($data['target']) AS $item) {
                        $pushLists[] = [
                            'fd' => $item['fd'],
                            'msg' => $data
                        ];
                    }
                    $pushTask = new PushTask($pushLists);
                    Task::deliver($pushTask);
                }
                break;

            /**
             * 发给用户（不保存记录）
             */
            case 'info':
                $pushLists = [];
                foreach ($this->getUserOfName($data['target']) AS $item) {
                    $pushLists[] = [
                        'fd' => $item['fd'],
                        'msg' => $data
                    ];
                }
                $pushTask = new PushTask($pushLists);
                Task::deliver($pushTask);
                break;

            /**
             * 发给整个团队
             */
            case 'team':
                if ($data['body']['type'] === 'taskA') {
                    $taskId = intval(Base::val($data['body'], 'taskDetail.id'));
                    if ($taskId > 0) {
                        $userLists = $this->getTaskUsers($taskId);
                    } else {
                        $userLists = $this->getTeamUsers();
                    }
                    //
                    $pushLists = [];
                    foreach ($userLists as $user) {
                        $data['messageType'] = 'user';
                        $data['target'] = $user['username'];
                        $pushLists[] = [
                            'fd' => $user['fd'],
                            'msg' => $data
                        ];
                    }
                    $pushTask = new PushTask($pushLists);
                    Task::deliver($pushTask);
                }
                break;
        }
        if ($data['messageId']) {
            $pushLists = [];
            $pushLists[] = [
                'fd' => $frame->fd,
                'msg' => [
                    'messageType' => 'back',
                    'messageId' => $data['messageId'],
                    'body' => $back,
                ]
            ];
            $pushTask = new PushTask($pushLists);
            Task::deliver($pushTask);
        }
    }

    /**
     * 关闭连接时触发
     * @param Server $server
     * @param $fd
     * @param $reactorId
     */
    public function onClose(Server $server, $fd, $reactorId)
    {
        $this->deleteUser($fd);
    }

    /** ****************************************************************************** */
    /** ****************************************************************************** */
    /** ****************************************************************************** */

    /**
     * 保存用户
     * @param $fd
     * @param $channel
     * @param $username
     */
    private function saveUser($fd, $channel, $username)
    {
        try {
            DB::transaction(function () use ($username, $channel, $fd) {
                $this->deleteUser($fd);
                DB::table('ws')->updateOrInsert([
                    'key' => md5($fd . '@' . $channel . '@' . $username)
                ], [
                    'fd' => $fd,
                    'username' => $username,
                    'channel' => $channel,
                    'update' => time()
                ]);
            });
        } catch (\Throwable $e) {

        }
    }

    /**
     * 清除用户
     * @param $fd
     */
    private function deleteUser($fd)
    {
        DB::table('ws')->where('fd', $fd)->delete();
    }

    /**
     * 获取用户
     * @param string $fd
     * @param string $channel
     * @param string $username
     * @return array
     */
    private function getUser($fd  = '', $channel = '', $username = '')
    {
        $array = [];
        if ($fd) $array['fd'] = $fd;
        if ($channel) $array['channel'] = $channel;
        if ($username) $array['username'] = $username;
        if (empty($array)) {
            return [];
        }
        return Base::DBC2A(DB::table('ws')->select(['fd', 'username', 'channel'])->where($array)->get());
    }

    private function getUserOfFd($fd, $channel = '') {
        return $this->getUser($fd, $channel);
    }

    private function getUserOfName($username, $channel = '') {
        return $this->getUser('', $channel, $username);
    }

    /**
     * 获取团队用户
     * @return array|string
     */
    private function getTeamUsers()
    {
        return Base::DBC2A(DB::table('ws')->select(['fd', 'username', 'channel'])->where([
            ['update', '>', time() - 600],
        ])->get());
    }

    /**
     * 获取跟任务有关系的用户（关注的、在项目里的、负责人、创建者）
     * @param $taskId
     * @return array
     */
    private function getTaskUsers($taskId)
    {
        $taskDeatil = Base::DBC2A(DB::table('project_task')->select(['follower', 'createuser', 'username', 'projectid'])->where('id', $taskId)->first());
        if (empty($taskDeatil)) {
            return [];
        }
        //关注的用户
        $userArray = Base::string2array($taskDeatil['follower']);
        //创建者
        $userArray[] = $taskDeatil['createuser'];
        //负责人
        $userArray[] = $taskDeatil['username'];
        //在项目里的用户
        if ($taskDeatil['projectid'] > 0) {
            $tempLists = Base::DBC2A(DB::table('project_users')->select(['username'])->where(['projectid' => $taskDeatil['projectid'], 'type' => '成员' ])->get());
            foreach ($tempLists AS $item) {
                $userArray[] = $item['username'];
            }
        }
        //
        return Base::DBC2A(DB::table('ws')->select(['fd', 'username', 'channel'])->where([
            ['update', '>', time() - 600],
        ])->whereIn('username', array_values(array_unique($userArray)))->get());
    }
}
