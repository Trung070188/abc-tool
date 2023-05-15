<?php

namespace App\Console\Commands;
use App\Export\ExportFileCategoriesEtsy;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class SearchShopCategoriesPageCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'search-shop';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $url = 'https://www.etsy.com/shop/KleverCase?ref=simple-shop-header-name&listing_id=113218775&section_id=1';
        $response = Http::get($url);
        $html = $response->body();

        $crawler = new Crawler($html);

        $ul = $crawler->filter('.wt-display-flex-lg')->first()->filter('.wt-action-group.wt-list-inline.wt-flex-no-wrap.wt-justify-content-center.wt-flex-no-wrap.wt-pt-md-1.wt-pb-md-2')->first();
        $liIndex = $ul->filter('li')->count() - 2;
        $secondToLastLi = $ul->filter('li')->eq($liIndex);
        $spanNode = $secondToLastLi->filter('span')->getNode(1);
        $value = $spanNode->nodeValue;
        $value = (int)($value);
        if(!empty($value)) {
            $shops = [];
            for ($i = 1; $i <= $value; $i++) {
                echo "time : " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
                echo "\nrun lan :" . $i . "\n";
                $url = $url . '&page=' . $i;
                $response = Http::get($url);
                $html = $response->body();

                $crawler = new Crawler($html);
                $shops = array_merge($shops, $crawler->filter('.responsive-listing-grid.wt-grid')->filter('.listing-link.wt-display-inline-block.wt-transparent-card')->each(function (Crawler $node) {
                    $linkShop = $node->attr('href');
                    $url = $linkShop;
                    $response = Http::get($url);
                    $html = $response->body();
                    $crawler = new Crawler($html);
                    $title = $crawler->filter('h1')->text();
                    $images = $crawler->filter('.wt-list-unstyled.wt-display-flex-xs.wt-order-xs-1.wt-flex-direction-column-xs.wt-align-items-flex-end')->filter('li')->each(function (Crawler $node) {
                        $img = $node->filter('img')->attr('data-src-delay');
                        return [
                            'link_img' => $img
                        ];
                    });
                    $data = [];
                    foreach ($images as $image) {
                        $image['link_img'] = str_replace('il_75x75', 'il_fullxfull', $image['link_img']);
                        $data [] = $image;
                    }

                    return [
                        'link_shop' => $linkShop,
                        'title' => $title,
                        'img' => $data
                    ];
                }));
                echo " \n done trang : " . $i . "\n";


            }

            echo "\n time-end : " . Carbon::now()->format('Y-m-d H:i:s') . "\n";
            return Excel::download(new ExportFileCategoriesEtsy($shops), "imgEtsy.xlsx");
        }
        }
    }
