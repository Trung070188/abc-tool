<?php

namespace App\Http\Controllers;

use App\Export\ExportFileCategoriesEtsy;
use App\Export\ExportFileEtsy;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\DomCrawler\Crawler;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Uri;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class TasksController extends Controller
{
    public function index()
    {
        $tasks = auth()->user()->tasks();


        return view('dashboard', compact('tasks'));
    }

    public function add()
    {
        return view('add');
    }

    public function create(Request $request)
    {
        try {
            $url = $request->description;
            $response = Http::get($url);
            $html = $response->body();

            $crawler = new Crawler($html);

            $title = $crawler->filter('h1')->text();

            $confirm = $crawler->filter('.image-wrapper')->filter('.wt-list-unstyled.wt-overflow-hidden.image-overlay-list.wt-position-relative.wt-vertical-center.wt-display-flex-xs.wt-justify-content-center')->filter('li');
            if(count($confirm) < 2)
            {
                $images = $crawler->filter('.image-wrapper')->filter('ul')->first()->filter('img')->attr('src');
                $data [] =[
                    'link_img' => $images
                ];

                $loadData = [
                    'title' => $title,
                    'data' => $data
                ];
            }
            else {

                $images = $crawler->filter('.wt-list-unstyled.wt-display-flex-xs.wt-order-xs-1.wt-flex-direction-column-xs.wt-align-items-flex-end')->filter('li')->slice(0,-1)->each(function (Crawler $node) {

                // fix lấy ảnh k lấy video

                    if($node->attr('data-image-id') !== 'listing-video-1')
                    {
                        $img = $node->filter('img')->attr('data-src-delay');
                        return [
                            'link_img' => $img
                        ];
                    }
                });

                $images =array_values(array_filter($images, function ($value){
                    return $value !== null;
                }));

                $data = [];
                foreach ($images as $image) {
                    $image['link_img'] = str_replace('il_75x75', 'il_fullxfull', $image['link_img']);
                    $data [] = $image;
                }
                $loadData = [
                    'title' => $title,
                    'data' => $data
                ];
            }
            return Excel::download(new ExportFileEtsy($loadData), "imgEtsy.xlsx");
        } catch (\Exception $e) {
            echo "\n error . ' . ':'" . $e->getMessage();
            return view('dashboard');
        }

    }

    public function categories(Request $req)
    {
        try {

            $url = $req->categories;
            $response = Http::get($url);
            $html = $response->body();
            $crawler = new Crawler($html);
                $shops = $crawler->filter('.responsive-listing-grid.wt-grid')->filter('.listing-link.wt-display-inline-block.wt-transparent-card')->each(function (Crawler $node) {
                    $linkShop = $node->attr('href');
                    $url = $linkShop;
                    $response = Http::get($url);
                    $html = $response->body();
                    $crawler = new Crawler($html);
                    $title = $crawler->filter('h1')->text();
                    $confirm = $crawler->filter('.image-wrapper')->filter('.wt-list-unstyled.wt-overflow-hidden.image-overlay-list.wt-position-relative.wt-vertical-center.wt-display-flex-xs.wt-justify-content-center')->filter('li');
                    $data = [];
                    if(count($confirm) < 2)
                    {
                        $images = $crawler->filter('.image-wrapper')->filter('ul')->first()->filter('img')->attr('src');
                        $data [] =[
                            'link_img' => $images
                        ];
                    }
                    else {
                        $images = $crawler->filter('.wt-list-unstyled.wt-display-flex-xs.wt-order-xs-1.wt-flex-direction-column-xs.wt-align-items-flex-end')->filter('li')->slice(0, -1)->each(function (Crawler $node) {

                            if($node->attr('data-image-id') !== 'listing-video-1' || $node->attr('data-image-id') !== '' )
                            {
                                $img = $node->filter('img')->attr('data-src-delay');
                                return [
                                    'link_img' => $img
                                ];
                            }
                        });
                        $images =array_values(array_filter($images, function ($value){
                            return $value !== null;
                        }));
                        $data = [];
                        foreach ($images as $image) {
                            $image['link_img'] = str_replace('il_75x75', 'il_fullxfull', $image['link_img']);
                            $data [] = $image;
                        }
                    }

                    return [
                        'link_shop' => $linkShop,
                        'title' => $title,
                        'img' => $data
                    ];
                });
                return Excel::download(new ExportFileCategoriesEtsy($shops), "imgEtsy.xlsx");

        } catch (\Exception $e) {
            echo "\n error . ' . ':'" . $e->getMessage();
            return view('dashboard');
        }

    }
    public function edit(Task $task)
    {

//    	if (auth()->user()->id == $task->user_id)
//        {
//                return view('edit', compact('task'));
//        }
//        else {
//             return redirect('/dashboard');
//         }
    }

    public function update(Request $request, Task $task)
    {
        if (isset($_POST['delete'])) {
            $task->delete();
            return redirect('/dashboard');
        } else {
            $this->validate($request, [
                'description' => 'required'
            ]);
            $task->description = $request->description;
            $task->save();
            return redirect('/dashboard');
        }
    }
}
