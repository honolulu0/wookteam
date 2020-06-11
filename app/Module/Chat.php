<?php

namespace App\Module;

use Cache;
use DB;

/**
 * Class Chat
 * @package App\Module
 */
class Chat
{
    /**
     * 打开对话（创建对话）
     * @param string $username      发送者用户名
     * @param string $receive       接受者用户名
     * @param bool $forceRefresh    是否强制刷新缓存
     * @return mixed
     */
    public static function openDialog($username, $receive, $forceRefresh = false)
    {
        if (!$username || !$receive) {
            return Base::retError('参数错误！');
        }
        $cacheKey = $username . "@" . $receive;
        if ($forceRefresh === true) {
            Cache::forget($cacheKey);
        }
        $result = Cache::remember($cacheKey, now()->addMinutes(10), function() use ($receive, $username) {
            $row = Base::DBC2A(DB::table('chat_dialog')->where([
                'user1' => $username,
                'user2' => $receive,
            ])->first());
            if ($row) {
                $row['recField'] = 2;   //接受者的字段位置
                return Base::retSuccess('already1', $row);
            }
            $row = Base::DBC2A(DB::table('chat_dialog')->where([
                'user1' => $receive,
                'user2' => $username,
            ])->first());
            if ($row) {
                $row['recField'] = 1;
                return Base::retSuccess('already2', $row);
            }
            //
            DB::table('chat_dialog')->insert([
                'user1' => $username,
                'user2' => $receive,
                'indate' => Base::time()
            ]);
            $row = Base::DBC2A(DB::table('chat_dialog')->where([
                'user1' => $username,
                'user2' => $receive,
            ])->first());
            if ($row) {
                $row['recField'] = 2;
                return Base::retSuccess('success', $row);
            }
            //
            return Base::retError('系统繁忙，请稍后再试！');
        });
        if (Base::isError($result)) {
            Cache::forget($cacheKey);
        }
        return $result;
    }

    /**
     * 删除对话缓存
     * @param $username
     * @param $receive
     * @return bool
     */
    public static function forgetDialog($username, $receive)
    {
        if (!$username || !$receive) {
            return false;
        }
        Cache::forget($username . "@" . $receive);
        Cache::forget($receive . "@" . $username);
        return true;
    }

    /**
     * 保存对话消息
     * @param string $username      发送者用户名
     * @param string $receive       接受者用户名
     * @param array $message
     * @return mixed
     */
    public static function saveMessage($username, $receive, $message)
    {
        $dialog = self::openDialog($username, $receive);
        if (Base::isError($dialog)) {
            return $dialog;
        } else {
            $dialog = $dialog['data'];
        }
        //
        $indate = abs($message['indate'] - time()) > 60 ? time() : $message['indate'];
        if (isset($message['id'])) unset($message['id']);
        if (isset($message['username'])) unset($message['username']);
        if (isset($message['userimg'])) unset($message['userimg']);
        if (isset($message['indate'])) unset($message['indate']);
        $inArray = [
            'did' => $dialog['id'],
            'username' => $username,
            'receive' => $receive,
            'message' => Base::array2string($message),
            'indate' => $indate
        ];
        //
        switch ($message['type']) {
            case 'text':
                $lastText = $message['text'];
                break;
            case 'image':
                $lastText = '[图片]';
                break;
            case 'taskB':
                $lastText = $message['text'] . " [来自关注任务]";
                break;
            case 'report':
                $lastText = $message['text'] . " [来自工作报告]";
                break;
            default:
                $lastText = '[未知类型]';
                break;
        }
        $field = ($dialog['recField'] == 1 ? 'unread1' : 'unread2');
        $unread = intval(DB::table('chat_dialog')->where('id', $dialog['id'])->value($field));
        if ($lastText) {
            $upArray = [];
            if ($username != $receive) {
                $upArray = Base::DBUP([
                    $field => 1,
                ]);
                $unread += 1;
            }
            $upArray['lasttext'] = $lastText;
            $upArray['lastdate'] = $indate;
            if ($dialog['del1']) {
                $upArray['del1'] = 0;
            }
            if ($dialog['del2']) {
                $upArray['del2'] = 0;
            }
            DB::table('chat_dialog')->where('id', $dialog['id'])->update($upArray);
            if ($dialog['del1'] || $dialog['del2']) {
                Chat::forgetDialog($dialog['user1'], $dialog['user2']);
            }
        }
        $inArray['id'] = DB::table('chat_msg')->insertGetId($inArray);
        $inArray['message'] = $message;
        $inArray['unread'] = $unread;
        //
        return Base::retSuccess('success', $inArray);
    }

    /**
     * 格式化信息（来自接收）
     * @param $data
     * @return array
     */
    public static function formatMsgReceive($data) {
        return self::formatMsgData(Base::json2array($data));
    }

    /**
     * 格式化信息（用于发送）
     * @param $array
     * @return string
     */
    public static function formatMsgSend($array) {
        return Base::array2json(self::formatMsgData($array));
    }

    /**
     * 格式化信息
     * @param array $array
     * @return array
     */
    public static function formatMsgData($array = []) {
        if (!is_array($array)) {
            $array = [];
        }
        //messageType来自客户端（前端->后端）：refresh/unread/read/roger/user/info/team
        //messageType来自服务端（后端->前端）：error/open/kick/user/back/unread
        if (!isset($array['messageType'])) $array['messageType'] = '';  //消息类型
        if (!isset($array['messageId'])) $array['messageId'] = '';      //消息ID（用于back给客户端）
        if (!isset($array['contentId'])) $array['contentId'] = 0;       //消息数据ID（用于roger给服务端）
        if (!isset($array['channel'])) $array['channel'] = '';          //渠道（用于多端登录）
        if (!isset($array['username'])) $array['username'] = '';        //发送者
        if (!isset($array['target'])) $array['target'] = null;          //接受者
        if (!isset($array['body'])) $array['body'] = [];                //正文内容
        if (!isset($array['time'])) $array['time'] = time();            //时间
        //
        $array['contentId'] = intval($array['contentId']);
        if (!is_array($array['body']) || empty($array['body'])) $array['body'] = ['_' => time()];
        return $array;
    }
}
