<?php
// +----------------------------------------------------------------------
// | 文件: index.php
// +----------------------------------------------------------------------
// | 功能: 提供count api接口
// +----------------------------------------------------------------------
// | 时间: 2021-12-12 10:20
// +----------------------------------------------------------------------
// | 作者: rangangwei<gangweiran@tencent.com>
// +----------------------------------------------------------------------

namespace App\Http\Controllers;

use Error;
use Exception;
use App\Counters;
use App\Http\Controllers\Controller;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CounterController extends Controller
{
    /**
     * 获取todo list
     * @return Json
     */
    public function getCount()
    {
        try {
            $data = (new Counters)->find(1);
            if ($data == null) {
                $count = 0;
            } else {
                $count = $data["count"];
            }
            $res = [
                "code" => 0,
                "data" => $count
            ];
            Log::info('getCount rsp: ' . json_encode($res));
            return response()->json($res);
        } catch (Error $e) {
            $res = [
                "code" => -1,
                "data" => [],
                "errorMsg" => ("查询计数异常" . $e->getMessage())
            ];
            Log::info('getCount rsp: ' . json_encode($res));
            return response()->json($res);
        }
    }


    /**
     * 根据id查询todo数据
     * @param $action `string` 类型，枚举值，等于 `"inc"` 时，表示计数加一；等于 `"reset"` 时，表示计数重置（清零）
     * @return Json
     */
    public function updateCount()
    {
        try {
            $action = request()->input('action');
            if ($action == "inc") {
                $data = (new Counters)->find(1);
                if ($data == null) {
                    $count = 1;
                } else {
                    $count = $data["count"] + 1;
                }

                $counters = new Counters;
                $counters->updateOrCreate(['id' => 1], ["count" => $count]);
            } else if ($action == "clear") {
                Counters::destroy(1);
                $count = 0;
            } else {
                throw '参数action错误';
            }

            $res = [
                "code" => 0,
                "data" => $count
            ];
            Log::info('updateCount rsp: ' . json_encode($res));
            return response()->json($res);
        } catch (Exception $e) {
            $res = [
                "code" => -1,
                "data" => [],
                "errorMsg" => ("更新计数异常" . $e->getMessage())
            ];
            Log::info('updateCount rsp: ' . json_encode($res));
            return response()->json($res);
        }
    }

    public function image(Request $request)
    {
        $this->validate($request, [
            'url' => "required"
        ], [
            'url.required' => "解析链接不能为空"
        ]);

        $url = $request->get('url');
        list($arr, $video_url) = $this->douYin($url);

        if (Str::startsWith($video_url, 'http://') || Str::startsWith($video_url, 'https://')) {
            $client = new Client(['verify' => false]);
            $tempFilename = Str::random();
            $data = $client->get($video_url)->getBody()->getContents();
            $filePath = storage_path('app/public/' . date('Y-m-d'));
            if (!is_dir($filePath)) {
                mkdir($filePath, 0755, true);
            }
            file_put_contents($filePath . '/' . $tempFilename . '.jpeg', $data);
            return [
                "code" => 0,
                "url" => asset(Storage::url(date('Y-m-d') . '/' . $tempFilename . '.jpeg'))
            ];
        }
    }


    public function douYin($url)
    {
        $loc = get_headers($url, true)['Location'][1];
        preg_match('/video\/(.*)\?/', $loc, $id);
        $arr = json_decode($this->curl('https://www.iesdouyin.com/web/api/v2/aweme/iteminfo/?item_ids=' . $id[1]), true);
        preg_match('/href="(.*?)">Found/', $this->curl(str_replace('playwm', 'play', $arr['item_list'][0]["video"]["play_addr"]["url_list"][0])), $matches);
        $video_url = str_replace('&', '&', $matches[1]);
        $all_yuming = ['p26-sign.douyinpic.com', 'p3-sign.douyinpic.com', 'p6-sign.douyinpic.com', 'sf6-cdn-tos.douyinstatic.com', 'staticedu-wps.cache.iciba.com', 'txmov2.a.yximgs.com', 'v1-cold.douyinvod.com', 'v1-y.douyinvod.com', 'v1.douyinvod.com', 'v11-x.douyinvod.com', 'v11.douyinvod.com', 'v26-cold.douyinvod.com', 'v26.douyinvod.com', 'v29-cold.douyinvod.com', 'v29.douyinvod.com', 'v3-a.douyinvod.com', 'v3-b.douyinvod.com', 'v3-c.douyinvod.com', 'v3-cold.douyinvod.com', 'v3-d.douyinvod.com', 'v3-dy-o.zjcdn.com', 'v3-e.douyinvod.com', 'v3-x.douyinvod.com', 'v3-y.douyinvod.com', 'v3-z.douyinvod.com', 'v5-cold.douyinvod.com', 'v5-coldb.douyinvod.com', 'v5-coldc.douyinvod.com', 'v5-coldy.douyinvod.com', 'v5-e.douyinvod.com', 'v5-f.douyinvod.com', 'v5-g.douyinvod.com', 'v5-h.douyinvod.com', 'v5-i.douyinvod.com', 'v5-j.douyinvod.com', 'v6-cold.douyinvod.com', 'v6-x.douyinvod.com', 'v6-y.douyinvod.com', 'v6-z.doubting.com', 'v6.douyinvod.com', 'v83-c.douyinvod.com', 'v83-d.douyinvod.com', 'v83-x.douyinvod.com', 'v83-y.douyinvod.com', 'v83-z.douyinvod.com', 'v83.douyinvod.com', 'v9-cold.douyinvod.com', 'v9-x.douyinvod.com', 'v9-z.douyinvod.com', 'v9.douyinvod.com', 'v95.douyinvod.com'];
        if (isset(parse_url($video_url)['host']) && in_array(parse_url($video_url)['host'], $all_yuming)) {
            $array = [
                'code' => 200,
                'msg' => '解析成功',
                'data' => [
                    'author' => $arr['item_list'][0]['author']['nickname'],
                    'uid' => $arr['item_list'][0]['author']['unique_id'],
                    'avatar' => $arr['item_list'][0]['author']['avatar_larger']['url_list'][0],
                    'like' => $arr['item_list'][0]['statistics']['digg_count'],
                    'time' => $arr['item_list'][0]["create_time"],
                    'title' => $arr['item_list'][0]['share_info']['share_title'],
                    'cover' => $arr['item_list'][0]['video']['origin_cover']['url_list'][0],
                    'url' => $video_url,
                    'music' => [
                        'author' => $arr['item_list'][0]['music']['author'],
                        'avatar' => $arr['item_list'][0]['music']['cover_large']['url_list'][0],
                        'url' => $arr['item_list'][0]['music']['play_url']['url_list'][0],
                    ]
                ]
            ];
            return [$array, $arr['item_list'][0]['video']['origin_cover']['url_list'][0]];
        } else {
            $this->douYin($url);
        }
    }

    private function curl($url, $headers = [])
    {
        $header = ['User-Agent:Mozilla/5.0 (iPhone; CPU iPhone OS 11_0 like Mac OS X) AppleWebKit/604.1.38 (KHTML, like Gecko) Version/11.0 Mobile/15A372 Safari/604.1'];
        $con = curl_init((string)$url);
        curl_setopt($con, CURLOPT_HEADER, false);
        curl_setopt($con, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($con, CURLOPT_RETURNTRANSFER, true);
        if (!empty($headers)) {
            curl_setopt($con, CURLOPT_HTTPHEADER, $headers);
        } else {
            curl_setopt($con, CURLOPT_HTTPHEADER, $header);
        }
        curl_setopt($con, CURLOPT_TIMEOUT, 5000);
        $result = curl_exec($con);
        return $result;
    }
}
