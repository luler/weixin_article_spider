爬取微信公众号文章工具
===============

## 准备条件

* 运行环境php7.0+
* mysql数据库
* 一个公众号

## 安装

github地址（自觉给星哟）：https://github.com/luler/weixin_article_spider.git
~~~
git clone https://github.com/luler/weixin_article_spider.git
~~~

## 配置

编辑项目目录下的.env文件

```
[database]
TYPE=mysql
HOSTNAME=192.168.0.3
DATABASE=weixin_article
USERNAME=root
PASSWORD=root
HOSTPORT=3306
CHARSET=utf8mb4
PREFIX=zc_

[wechat_config]
#公众号完整名称，多个用逗号隔开
wechat_list=智慧莞工,东莞理工学院
token='登录公众号后，F12打开Network,随便从某个接口获取到token'
cookie='登录公众号后，F12打开Network,随便从某个接口获取到cookie'
```

## 运行

```
sh spider.sh
```

## 注意点

* 该程序不能自动登录微信公众号，需要用户自己登陆自己可以登陆的公众号，获取到相应的配置

* 每次获取到的公众号cookie等信息都是有有效期的，大概几小时吧

* 执行太多次，可能会被微信发现，对接口进行封禁，这个时候不要急，最多等24小时，再次登录公众号获取配置，再次执行就可以了，建议多个公众号做备用

## 相关截图

- 登录公众号后获取关键配置

![爬取微信公众号文章工具](https://cdn.learnku.com/uploads/images/202103/31/31440/io9Q79gLnx.png!large)


- 抓取到的数据（部分字段）

![爬取微信公众号文章工具](https://cdn.learnku.com/uploads/images/202103/30/31440/lq6VCpvHit.png!large)

- 微信流控产生（这时候需要更换公众号cookie等信息，或者等待一段时间再重试）

![爬取微信公众号文章工具](https://cdn.learnku.com/uploads/images/202103/30/31440/hnrPn3YdFO.png!large)

## 讨论一下

https://learnku.com/articles/55817