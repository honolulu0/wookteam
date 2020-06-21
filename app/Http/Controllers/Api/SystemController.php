<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Module\Base;
use App\Module\Users;
use Request;

/**
 * @apiDefine system
 *
 * 系统
 */
class SystemController extends Controller
{
    public function __invoke($method, $action = '')
    {
        $app = $method ? $method : 'main';
        if ($action) {
            $app .= "__" . $action;
        }
        return (method_exists($this, $app)) ? $this->$app() : Base::ajaxError("404 not found (" . str_replace("__", "/", $app) . ").");
    }

    /**
     * 获取设置、保存设置
     *
     * @apiParam {String} type
     * - get: 获取（默认）
     * - save: 保存设置（参数：logo、github、reg、callav、autoArchived、archivedDay）
     */
    public function setting()
    {
        $type = trim(Request::input('type'));
        if ($type == 'save') {
            if (env("SYSTEM_SETTING") == 'disabled') {
                return Base::retError('当前环境禁止修改！');
            }
            $user = Users::authE();
            if (Base::isError($user)) {
                return $user;
            } else {
                $user = $user['data'];
            }
            if (Base::isError(Users::identity('admin'))) {
                return Base::retError('权限不足！', [], -1);
            }
            $all = Request::input();
            foreach ($all AS $key => $value) {
                if (!in_array($key, ['logo', 'github', 'reg', 'callav', 'autoArchived', 'archivedDay'])) {
                    unset($all[$key]);
                }
            }
            $all['logo'] = is_array($all['logo']) ? $all['logo'][0]['path'] : $all['logo'];
            $all['archivedDay'] = intval($all['archivedDay']);
            if ($all['autoArchived'] == 'open') {
                if ($all['archivedDay'] <= 0) {
                    return Base::retError(['自动归档时间不可小于%天！', 1]);
                } elseif ($all['archivedDay'] > 100) {
                    return Base::retError(['自动归档时间不可大于%天！', 100]);
                }
            }
            $setting = Base::setting('system', Base::newTrim($all));
        } else {
            $setting = Base::setting('system');
        }
        $setting['logo'] = Base::fillUrl($setting['logo']);
        return Base::retSuccess('success', $setting ? $setting : json_decode('{}'));
    }
}
