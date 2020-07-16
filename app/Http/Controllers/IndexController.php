<?php

namespace App\Http\Controllers;

use App\Module\Base;
use Redirect;


/**
 * 页面
 * Class IndexController
 * @package App\Http\Controllers
 */
class IndexController extends Controller
{

    private $version = '1.4';

    public function __invoke($method, $action = '', $child = '')
    {
        $app = $method ? $method : 'main';
        if ($action) {
            $app .= "__" . $action;
        }
        return (method_exists($this, $app)) ? $this->$app($child) : Base::ajaxError("404 not found (" . str_replace("__", "/", $app) . ").");
    }

    /**
     * 首页
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    public function main()
    {
        return view('main', ['version' => $this->version]);
    }

    /**
     * 接口文档
     * @return \Illuminate\Http\RedirectResponse
     */
    public function api()
    {
        return Redirect::to(Base::fillUrl('docs'), 301);
    }
}
