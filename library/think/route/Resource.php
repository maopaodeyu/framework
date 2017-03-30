<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think\route;

use think\Route;

class Resource extends RuleGroup
{
    // 资源路由地址
    protected $route;

    // REST路由方法定义
    protected $rest = [];

    /**
     * 架构函数
     * @access public
     * @param Route         $router     路由实例
     * @param string        $name       资源名称
     * @param string        $route      路由地址
     * @param array         $option     路由参数
     * @param array         $pattern    变量规则
     * @param array         $rest       资源定义
     */
    public function __construct(Route $router, $name, $route, $option = [], $pattern = [], $rest = [])
    {
        $this->router = $router;
        $this->name   = $name;
        $this->route  = $route;

        // 资源路由默认为完整匹配
        $option['complete_match'] = true;

        $this->pattern = $pattern;
        $this->option  = $option;
        $this->rest    = $rest;

        $this->buildRule($name, $option);
    }

    /**
     * 生成资源路由规则
     * @access public
     * @param string    $rule       路由规则
     * @param array     $option     路由参数
     * @return void
     */
    public function buildRule($rule, $option)
    {
        if (strpos($rule, '.')) {
            // 注册嵌套资源路由
            $array = explode('.', $rule);
            $last  = array_pop($array);
            $item  = [];

            foreach ($array as $val) {
                $item[] = $val . '/:' . (isset($option['var'][$val]) ? $option['var'][$val] : $val . '_id');
            }

            $rule = implode('/', $item) . '/' . $last;
        }

        // 注册分组
        $group = $this->router->getGroup();

        $this->router->setGroup($this);

        // 注册资源路由
        foreach ($this->rest as $key => $val) {
            if ((isset($option['only']) && !in_array($key, $option['only']))
                || (isset($option['except']) && in_array($key, $option['except']))) {
                continue;
            }

            if (isset($last) && strpos($val[1], ':id') && isset($option['var'][$last])) {
                $val[1] = str_replace(':id', ':' . $option['var'][$last], $val[1]);
            } elseif (strpos($val[1], ':id') && isset($option['var'][$rule])) {
                $val[1] = str_replace(':id', ':' . $option['var'][$rule], $val[1]);
            }

            $item           = ltrim($rule . $val[1], '/');
            $option['rest'] = $key;

            $this->router->rule($item, $this->route . '/' . $val[2], $val[0], $option);
        }

        $this->router->setGroup($group);
    }

    public function vars($var)
    {
        return $this->option('var', $var);
    }

}