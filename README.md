# haxibiao-live

> haxibiao-live是哈希表基于腾讯云直播开发的小直播扩展包
> 欢迎大家提交代码或提出建议

## 导语
1. 直播系统的业务流程代码全部来自曾达威
2. 使用本扩展包需要在[腾讯云直播](https://console.cloud.tencent.com/live/livestat)创建直播所需要的KEY，推流地址，拉流地址 
3. 使用本扩展包需要搭建larave-echo-server，进行webscoket通信
4. 相关博客：[小直播后端开发笔记](http://haxibiao.com/blog/1695)
5. 如果有疑问，请联系哈希坊架构组

## 环境要求
1. 依赖Redis
2. 为了搭建即时通讯需要将外部的队列驱动改为redis，为了安全起见注意提现队列驱动还是保留到MySQL数据库中。

## 安装步骤
1. `App/User`模型需要增加Trait: `use App\Traits\Macroable`(文件需要自己创建，Macroable详情放在文档附录中);
2. `composer.json`改动如下：
    1. 在`require`中加入`"haxibiao/live": "*"`
    2. 在`repositories`中添加远程仓库指向`http://code.haxibiao.cn/packages/haxibiao-live.git` 
3. 执行`composer update haxibiao/live --lock --ignore-platform-reqs`
4. 执行`php artisan live:install`
5. 配置env文件以下三个参数值：
```
LIVE_KEY=
LIVE_PUSH_URL=
LIVE_PULL_URL=
```
6. 完成

### 如何完成更新？
> 远程仓库的composer package发生更新时如何进行更新操作呢？
1. 执行`composer update haxibiao/live`
2. 执行`php artisan live:install`

## GQL接口说明

## Api接口说明

1. [腾讯云直播断流回调](#腾讯云直播断流回调)
2. [录制回调](#录制回调)
3. [腾讯云直播截图回调](#腾讯云直播截图回调)

## 腾讯云直播断流回调

#### 请求方法 
POST 

#### 接口地址

api/live/cutOut

| params     | must | desc |
| ---------- | ---- | ---- |
| stream_id  | yes  |      |
| event_type | yes  |      |


## 录制回调

#### 请求方法
POST

#### 接口地址

api/live/recording

#### 参数说明

| params     | must | desc       |
| ---------- | ---- | ---------- |
| channel_id | yes  | 直播流名称 |

## 腾讯云直播截图回调

#### 请求方法
POST

#### 接口地址

api/live/screenShots

#### 参数说明

| params     | must | desc       |
| ---------- | ---- | ---------- |
| channel_id | yes  | 直播流名称 |

### 附录
#### App\Traits\Macroable
```php
<?php

namespace App\Traits;

trait Macroable
{
    use \Illuminate\Support\Traits\Macroable {
        __call as macroCall;
    }

    public function getRelationValue($key)
    {
        $relation = parent::getRelationValue($key);
        if (! $relation && static::hasMacro($key)) {
            return $this->getRelationshipFromMethod($key);
        }

        return $relation;
    }

    public function __call($method, $parameters)
    {
        if (static::hasMacro($method)) {
            return $this->macroCall($method, $parameters);
        }

        return parent::__call($method, $parameters);
    }

    public static function __callStatic($method, $parameters)
    {
        return parent::__callStatic($method, $parameters);
    }
}
```