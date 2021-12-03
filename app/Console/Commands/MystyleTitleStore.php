<?php

namespace App\Console\Commands;

use App\MystyleTitle;
use DOMDocument;
use Illuminate\Console\Command;

class MystyleTitleStore extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mystyle_title:cron';

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
     * @return int
     */
    public function handle()
    {
        $ch = curl_init();
        $url = 'https://mystylemyanmar.com/feed/';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, 0);

        $data = curl_exec($ch);

        $invalid_characters = '/[^\x9\xa\x20-\xD7FF\xE000-\xFFFD]/';
        $data = preg_replace($invalid_characters, '', $data);

        curl_close($ch);
        $e = json_encode($data);
        $d = json_decode($e, true);

        $rss = new DOMDocument();
        $rss->loadXML($d);
        $feed = array();
        foreach ($rss->getElementsByTagName('item') as $node) {
            $item = array(
                'title' => $node->getElementsByTagName('title')->item(0)->nodeValue,
                'pubDate' => $node->getElementsByTagName('pubDate')->item(0)->nodeValue,
            );
            array_push($feed, $item);
        }
        foreach ($feed as $f) {
                $mystyle = new MystyleTitle();
                $mystyle->title = tounicode($f['title']);
                $mystyle->publishedDate = date('Y-m-d H:i:s', strtotime($f['pubDate']));
                $mystyle->save();
        }
    }
}
