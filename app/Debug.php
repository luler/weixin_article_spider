<?php
declare (strict_types=1);

namespace app;

use app\model\Article;
use app\model\Weixin;
use Curl\Curl;
use think\console\Command;
use think\console\Input;
use think\console\Output;
use think\facade\Console;
use think\facade\Db;

class Debug extends Command
{
    protected function configure()
    {
        // 指令配置
        $this->setName('debug')->setDescription('调试专用命令');
    }

    public function initCheck()
    {
        $count = 0;
        try {
            again:
            if ($count >= 3) {
                return;
            }
            $count++;
            Weixin::find(1);
            Article::find(1);
        } catch (\Throwable $e) {
            if (strpos($e->getMessage(), '1049')) {
                $database = config('database.connections.mysql.database');
                Db::connect('temp')->query('create database ' . $database);
                Console::call('migrate:run');
                goto again;
            }
            if (strpos($e->getMessage(), '42S02')) {
                Console::call('migrate:run');
                goto again;
            }
        }
    }

    protected function execute(Input $input, Output $output)
    {
        //数据库初始化检查
        $this->initCheck();

        $searchs = config('app.wechat_config.wechat_list');
        $token = config('app.wechat_config.token');
        $cookie = config('app.wechat_config.cookie');

        $curl = new Curl();
        $curl->setHeader('cookie', $cookie);
        $curl->setOpt(CURLOPT_USERAGENT, 'Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/78.0.3904.108 Safari/537.36');
        $curl->setOpt(CURLOPT_SSL_VERIFYPEER, false);
        $curl->setOpt(CURLOPT_SSL_VERIFYHOST, false);

        foreach ($searchs as $search) {
            $fakeid = Weixin::where('nickname', $search)->value('fakeid');
            if (empty($fakeid)) {
                $param = [
                    'action' => 'search_biz',
                    'begin' => 0,
                    'count' => 1,
                    'query' => $search,
                    'token' => $token,
                    'lang' => 'zh_CN',
                    'f' => 'json',
                    'ajax' => 1,
                ];
                $search_gongzhonghao_url = 'https://mp.weixin.qq.com/cgi-bin/searchbiz?' . http_build_query($param);
                $curl->get($search_gongzhonghao_url);
                $res = $curl->getResponse();
                $res = json_decode($res, true);
                if (!isset($res['total'])) {
                    halt('公众号"' . $search . '"，获取公众号接口异常:' . json_encode($res, 256));
                }
                if (empty($res['total'])) {
                    halt('公众号"' . $search . '"不存在');
                }
                Weixin::create($res['list'][0]);
                $fakeid = $res['list'][0]['fakeid'];
            }

            //获取所有公众号文章
            $param = [
                'action' => 'list_ex',
                'begin' => 0,
                'count' => 0,
                'fakeid' => $fakeid,
                'type' => 9,
                'query' => '',
                'token' => $token,
                'lang' => 'zh_CN',
                'f' => 'json',
                'ajax' => 1,
            ];
            $get_gongzhonghao_article_url = 'https://mp.weixin.qq.com/cgi-bin/appmsg?' . http_build_query($param);
            $curl->get($get_gongzhonghao_article_url);
            $res = $curl->getResponse();
            $res = json_decode($res, true);
            if (!isset($res['app_msg_cnt'])) {
                halt('公众号"' . $search . '"，获取文章接口异常:' . json_encode($res, 256));
            }

            $exist_msg_ids = Article::where('fakeid', $fakeid)
                ->distinct(true)
                ->column('appmsgid');
            $all = $res['app_msg_cnt'];
            $all -= count($exist_msg_ids);
            $round_time = ceil($all / 5);

            $total = 0;
            if ($round_time > 0) {
                for ($i = $round_time - 1; $i >= 0; $i--) {
                    $param['begin'] = 5 * $i;
                    $param['count'] = 5;
                    $get_gongzhonghao_article_url = 'https://mp.weixin.qq.com/cgi-bin/appmsg?' . http_build_query($param);
                    $curl->get($get_gongzhonghao_article_url);
                    $res = $curl->getResponse();
                    $res = json_decode($res, true);
                    if (!isset($res['app_msg_list'])) { //可能出现流控问题
                        dump('公众号"' . $search . '"，获取文章接口异常:' . json_encode($res, 256));
                        break;
                    }
                    $list = $res['app_msg_list'];
                    $data = [];
                    foreach ($list as $item) {
                        $item['appmsg_album_infos'] = json_encode($item['appmsg_album_infos'], 256);
                        $item['pay_album_info'] = json_encode($item['pay_album_info'], 256);
                        $item['tagid'] = json_encode($item['tagid'], 256);
                        $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                        $item['update_time'] = date('Y-m-d H:i:s', $item['update_time']);
                        $item['fakeid'] = $fakeid;
                        if (in_array($item['appmsgid'], $exist_msg_ids)) {
                            continue;
                        }
                        $data[] = $item;
                        $total++;
                    }
                    (new Article())->saveAll($data);
                    dump('公众号"' . $search . '"进度,已新新增文章数量:' . $total . '，完成情况:' . ($round_time - $i) . '/' . $round_time);
                    sleep(3 * 60);//休息3分钟，防止接口流控限制导致公众号被封，严重被封后24H才能重新请求
                }
            } else { //可能删除文章，造成本地文章条数比微信文章条数更少
                //获取创建时间最大的
                $max_time = Article::where('fakeid', $fakeid)->max('create_time', false);
                $max_time = strtotime($max_time);

                $round_time = ceil($res['app_msg_cnt'] / 5);

                $data = [];
                for ($i = 0; $i < $round_time; $i++) {
                    $param['begin'] = 5 * $i;
                    $param['count'] = 5;
                    $get_gongzhonghao_article_url = 'https://mp.weixin.qq.com/cgi-bin/appmsg?' . http_build_query($param);
                    $curl->get($get_gongzhonghao_article_url);
                    $res = $curl->getResponse();
                    $res = json_decode($res, true);
                    if (!isset($res['app_msg_list'])) { //可能出现流控问题
                        dump('公众号"' . $search . '"，获取文章接口异常:' . json_encode($res, 256));
                        break;
                    }
                    $list = $res['app_msg_list'];

                    foreach ($list as $item) {
                        if ($item['create_time'] < $max_time) {
                            break 2;
                        }
                        $item['appmsg_album_infos'] = json_encode($item['appmsg_album_infos'], 256);
                        $item['pay_album_info'] = json_encode($item['pay_album_info'], 256);
                        $item['tagid'] = json_encode($item['tagid'], 256);
                        $item['create_time'] = date('Y-m-d H:i:s', $item['create_time']);
                        $item['update_time'] = date('Y-m-d H:i:s', $item['update_time']);
                        $item['fakeid'] = $fakeid;
                        if (in_array($item['appmsgid'], $exist_msg_ids)) {
                            continue;
                        }
                        $data[] = $item;
                        $total++;
                    }
                    dump('公众号"' . $search . '"进度,探测到新文章数量:' . $total);
                    sleep(3 * 60);//休息3分钟，防止接口流控限制导致公众号被封，严重被封后24H才能重新请求
                }
                (new Article())->saveAll($data);
                dump('公众号"' . $search . '",新增文章数量:' . $total);
            }
        }
    }
}
