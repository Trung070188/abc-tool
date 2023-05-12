<?php

namespace App\Http\Controllers;

use App\Export\ExportFileCategoriesEtsy;
use App\Export\ExportFileEtsy;
use App\Exports\DevicePlanExport;
use Illuminate\Http\Request;
use App\Models\Task;
use Illuminate\Support\Facades\Http;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\DomCrawler\Crawler;

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
            $images = $crawler->filter('.wt-list-unstyled.wt-display-flex-xs.wt-order-xs-1.wt-flex-direction-column-xs.wt-align-items-flex-end')->filter('li')->each(function (Crawler $node)
            {
                $img = $node->filter('img')->attr('data-src-delay');
                return [
                    'link_img'=> $img
                ];
            });
            $data = [];
            foreach ($images as $image) {
                $image['link_img'] = str_replace('il_75x75', 'il_fullxfull', $image['link_img']);
                $data [] = $image;
            }
            $loadData = [
              'title' => $title,
              'data' => $data
            ];

            return Excel::download(new ExportFileEtsy($loadData), "imgEtsy.xlsx");
        }
        catch (\Exception $e)
        {
            echo "\n error . ' . ':'" .$e->getMessage();
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

            $shops = $crawler->filter('.wt-pr-xs-0.wt-pl-xs-0.shop-home-wider-items.wt-pb-xs-5')->filter('.listing-link.wt-display-inline-block.wt-transparent-card')->each(function (Crawler $node)
            {
//                $price = $node->filter('.wt-pr-xs-1.wt-text-title-01')->filter('span')->text();
                $title = $node->filter('.listing-link.wt-display-inline-block.wt-transparent-card')->text();
                $linkShop = $node->filter('.listing-link.wt-display-inline-block.wt-transparent-card')->attr('href');
                $url = $linkShop;
                $response = Http::get($url);
                $html = $response->body();
                $crawler = new Crawler($html);
                $images = $crawler->filter('.wt-list-unstyled.wt-display-flex-xs.wt-order-xs-1.wt-flex-direction-column-xs.wt-align-items-flex-end')->filter('li')->each(function (Crawler $node)
                {
                    $img = $node->filter('img')->attr('data-src-delay');
                    return [
                        'link_img'=> $img
                    ];
                });
                $data = [];
                foreach ($images as $image) {
                    $image['link_img'] = str_replace('il_75x75', 'il_fullxfull', $image['link_img']);
                    $data [] = $image;
                }
                return [
                    'title' => $title,
                    'img' => $data
                ];

            });
            return Excel::download(new ExportFileCategoriesEtsy($shops), "imgEtsy.xlsx");
        }
        catch (\Exception $e)
        {
            echo "\n error . ' . ':'" .$e->getMessage();
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
    	if(isset($_POST['delete'])) {
    		$task->delete();
    		return redirect('/dashboard');
    	}
    	else
    	{
            $this->validate($request, [
                'description' => 'required'
            ]);
    		$task->description = $request->description;
	    	$task->save();
	    	return redirect('/dashboard');
    	}
    }
}
