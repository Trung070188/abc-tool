<?php

namespace App\Http\Controllers;

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
//        $this->validate($request, [
//            'description' => 'required'
//        ]);
//    	$task = new Task();
//    	$task->description = $request->description;
//    	$task->user_id = auth()->user()->id;
//    	$task->save();
        try {
            $url = $request->description;
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
            return Excel::download(new ExportFileEtsy($data), "imgEtsy.xlsx");
        }
        catch (\Exception $e)
        {
            echo 'Không đúng đường link';
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
