<?php

namespace App\Module;

use DB;

/**
 * Class Project
 * @package App\Module
 */
class Project
{
    /**
     * 是否在项目里
     * @param int $projectid
     * @param string $username
     * @param bool $isowner
     * @return array
     */
    public static function inThe($projectid, $username, $isowner = false)
    {
        $whereArray = [
            'type' => '成员',
            'projectid' => $projectid,
            'username' => $username,
        ];
        if ($isowner) {
            $whereArray['isowner'] = 1;
        }
        $row = Base::DBC2A(DB::table('project_users')->select(['isowner', 'indate'])->where($whereArray)->first());
        if (empty($row)) {
            return Base::retError('你不在项目成员内！');
        } else {
            return Base::retSuccess('你在项目内', $row);
        }
    }

    /**
     * 更新项目（complete、unfinished）
     * @param int $projectid
     */
    public static function updateNum($projectid)
    {
        if ($projectid > 0) {
            DB::table('project_lists')->where('id', $projectid)->update([
                'unfinished' => DB::table('project_task')->where('projectid', $projectid)->where('complete', 0)->where('delete', 0)->count(),
                'complete' => DB::table('project_task')->where('projectid', $projectid)->where('complete', 1)->where('delete', 0)->count(),
            ]);
        }
    }

    /**
     * 任务是否过期
     * @param array $task
     * @return int
     */
    public static function taskIsOverdue($task)
    {
        return $task['complete'] == 0 && $task['enddate'] > 0 && $task['enddate'] <= Base::time() ? 1 : 0;
    }

    /**
     * 过期的排在前
     * @param array $taskLists
     * @return mixed
     */
    public static function sortTask($taskLists)
    {
        $inOrder = [];
        foreach ($taskLists as $key => $oitem) {
            $inOrder[$key] = $oitem['overdue'] ? -1 : $key;
        }
        array_multisort($inOrder, SORT_ASC, $taskLists);
        return $taskLists;
    }

    /**
     * 获取跟任务有关系的用户（关注的、在项目里的、负责人、创建者）
     * @param $taskId
     * @return array
     */
    public static function taskSomeUsers($taskId)
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
        return $userArray;
    }
}
