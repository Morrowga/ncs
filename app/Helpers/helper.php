<?php

namespace App\Helpers;

use App\Log;
use App\Models\Articles\RawArticle;
use App\Models\Scrapes\Content;
use App\Models\Settings\Category;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Arabic;

class Helper
{

    //uuid
    public static function uuid()
    {
        return str_replace("-", "", Str::orderedUuid(date('Y-m-d H:i:s')));
    }
    //log
    public static function logText($name)
    {
        $log = new Log();
        $log->name = $name;
        $log->save();
    }

    // blacklist
    public static function checkBlacklist($id)
    {
        //blacklist_content
        //blacklist_content
        $blacklist_texts = array(
            'PDF', 'People Defence Force', 'pdf', 'peopledefenseforce', 'PEOPLEDEFENSEFORCE', 'PEOPLE DEFENSE FORCE',
            'people defense force', 'PeopleDefenseForce', 'ဆန္ဒပြ', 'NUG', 'nug', 'National Unity Government of Myanmar', 'ပျူစောထီး', 'NATIONALUNITYGOVERMENTOFMYANMAR', 'NATIONAL UNITY GOVERNMENT OF MYANMAR', 'nationalunitygovernmentofmyanmar', 'national unity government of myanmar', 'military coup', 'militarycoup', 'Military Coup', 'MilitaryCoup', 'MILITARYCOUP',
            'MILITARY COUP', 'JUNTA', 'junta', 'သပိတ်', 'ပျောက်ကြားသပိတ်၊', 'နွေဦးသပိတ်', 'CDM', 'cdm', 'Civil Disobedience Movement', 'CivilDisobedienceMovement', 'CIVILDISOBEDIENCEMOVEMENT',
            'CIVIL DISOBEDIENCE MOVEMENT', 'civildisobediencemovement', 'civil disobedience movement', 'မင်းအောင်လှိုင်', 'မအလ', 'CRPH', 'crph', 'Committee Representing Pyidaungsu Hluttaw',
            'COMMITTEEREPRESENTINGPYIDAUNGSUHLUTTAW', 'COMMITTEE REPRESENTING PYIDAUNGSU HLUTTAW', 'CommitteeRepresentingPyidaungsuHluttaw', 'committeerepresentingpyidaungsuhluttaw',
            'committee representing pyidaungsu hluttaw', ' ဒေါ်အောင်ဆန်းစုကြည်', 'အမေစု', 'နိုင်ငံတော်အတိုင်ပင်ခံပဂ္ဂိုလ်', 'VPN', 'vpn', 'Virtual Private Network', 'VirtualPrivateNetwork', 'VIRTUALPRIVATENETWORK',
            'VIRTUAL PRIVATE NETWORK', 'virtual private network', 'virtualprivatenetwork', 'မြန်မာ့တပ်မတော်', 'အာဏာသိမ်းစစ်တပ်', 'စစ်ကောင်စီ', 'စကစ', 'Facebook', 'facebook', 'FACEBOOK', 'အကျဉ်းထောင်',
            'UNITED NATIONS', 'ကုလသမဂ္ဂ', 'ပုဒ်မ-၅၀၅(က)', 'ပုဒ်မ-၅၀၅(ခ)', 'ပုဒ်မ-၅၉(ဃ)', 'အာဆီယံ', 'အရှေ့တောင်အာရှနိုင်ငံများအသင်း', 'ASEAN', 'asean', 'Asean', 'ထိန်းသိမ်းခံ',
            'ဖဒရယ်', 'Federal', 'federal', 'နိုင်ငံရေးသမား', 'အမျိုးသားဒီမိုကရေစီအဖွဲ့ချုပ်', 'NLD', 'National League for Democracy', 'NationalLeagueforDemocracy', 'NATIONALLEAUGEFORDEMOCRACY', 'NATIONAL LEAGUE FOR DEMOCRACY',
            'nationalleaguedemocracy', 'natioanl league democracy', 'ဗုံး', 'ဗုံးပေါက်ကွဲ', 'ဗုံးခွဲတိုက်ခိုက်ခံရ', 'Boycott', 'ဒေါက်တာဆာဆာ', 'စစ်ကျွန်ပညာရေး', 'ဒေါက်တာဇော်ဝေစိုး', 'အာဏာသိမ်း', 'အာဏာထိမ်း',
            'အာဏာရှင်', 'အာဏာရူး', 'နိုင်ငံရေး အကျဉ်းသား', 'နွေဦးတော်လှန်ရေး', 'တရားဥပဒေ စိုးမိုးမှု', 'ဆူပူအောင်လှုံဆော်မှု', 'ဒလန်', 'ကျောင်းသားသမဂ္ဂ', 'လက်နက်'
        );

        $blist = [];
        $raws = RawArticle::where('status', '=', '0')->find($id);
        $contents = Content::where('article_id', $raws->id)->get();

        foreach ($contents as $content) {
            foreach ($blacklist_texts as $blacklist) {
                if (strstr($content->content_text, $blacklist)) {
                    $blist[] = $blacklist;
                }
            }
        }
        $count_blacklist = array_count_values($blist);
        return $count_blacklist;
    }

    //suggest tags function
    public static function suggestTags($id)
    {
        //array
        $arrays = [
            ['ဗေဒင်', 'အလုပ်အကိုင်', 'စီးပွားရေး', 'ယတြာ', 'ငွေကြေး', 'မေတ္တာရေး', 'အချစ်ရေး', 'အိမ်ထောင်ရေး', 'လှူ', 'ပြဿနာ', 'ဆုတောင်း', 'စိတ်ချမ်းသာ', 'အခွင့်အလမ်း', 'မကျန်းမမာ', 'ပတ်ဝန်းကျင်'], //horoscope
            ['အလုပ်', 'လမ်းလျှောက်', 'နည်းလမ်း', 'အိပ်', 'ရေချိုး', 'အစားအစာ', 'ဘဝနေထိုင်မှု', 'အချစ်ရေး', 'ဖက်ရှင်', 'ကျန်းမာရေး', 'ခန္ဓာကိုယ်', 'အလေးချိန်', 'အထွေထွေ', 'ဗဟုသုတ', 'လေ့ကျင့်ခန်း', 'ဇာတ်လမ်း'], //lifestyle
            ['K-Drama', 'K-POP', 'ပေါ်ပြူလာ', 'BLACKPINK', 'ကိုရီးယား', 'မော်ဒယ်', 'မင်းသမီး', 'မင်းသား', 'ဖက်ရှင်', 'ဇာတ်လမ်း', 'fashion', 'နှုတ်ခမ်းနီ', 'Netflix', 'drama', 'list', 'stress', 'အနုပညာ'], //entertainment
            ['ဝတ္ထုတိုများ'], //short_story
            ['နှုတ်ခမ်းနီ', 'ပေါင်းသင်းဆက်ဆံရေး', 'အထွေထွေ', 'ဗဟုသုတ', 'မေးခွန်း'], //quiz
            ['ဆံပင်', 'နည်းလမ်းများ', 'သဘာဝ', 'နှုတ်ခမ်းနီ', 'မိတ်ကပ်', 'တင်ပါး', 'dress', 'လက်သည်း', 'ရေ', 'ကော်ဖီ', 'အမျိုးသမီး', 'ပေါင်းသင်းဆက်ဆံရေး', 'သံသယ', 'အသားအရေ', 'လမ်းခွဲ', 'မျက်ခုံးမွှေး', 'ခြေထောက်', 'သန့်ရှင်းရေး', 'သူငယ်ချင်း'], //beauty
            ['ဟာသ', 'funny'], //funny
            ['ကျန်းမာရေး', 'အလေ့အကျင့်', 'စား', 'အစားအစာ', 'အိပ်', 'ယောဂ', 'သောက်', 'အပူချိန်', 'လေ့ကျင့်ခန်း', 'အသားအရေ', 'သဘာဝ', 'ကောင်းကျိုး', 'အသား', 'ဝမ်း', 'လေ', 'ဆေး', 'ခေါင်း', 'ရေ', 'ဆံပင်'], //health
            ['အစားအသောက်', 'အဆီ', 'အသီးအရွက်', 'စားသောက်ဆိုက်', 'Thai', 'အစားအစာ', 'Bar', 'ရန်ကုန်', 'BBQ', 'Hot Pot', 'ဆိုင်', 'သဘာဝ', 'ကော်ဖီ', 'အိမ်', 'ကိတ်', 'ဝိတ်'], //food
            ['Google', 'vivo', 'apple', 'Binace', 'Xiaomi', 'Pad', 'Phone', 'Andriod', 'တရုတ်', 'စမတ်', 'Samsung', 'မားကတ်တင်း', 'အင်တာနက်', 'iPhone', 'Watch', 'Huawei', 'Mi', 'Tik Tok', 'Youtube'], //technology
            ['အားကစား', 'ဘောလုံး', 'ဇီဒန်း', 'မော်ရင်ဟို', 'ကစားသမား'], //sports
            ['Organic'], //agriculture
            ['indoor', 'plants', 'ခွေး', 'ဘာသာစကား', 'ကိုရီးယား'], //other
            ['ဖုန်း', 'နိုင်ငံ'], //regional
            ['Style', 'Theory', 'လုပ်ငန်း', 'ဒေါ်လာ', 'အလုပ်'] //international
        ];
        // dd($arrays);
        $raws = RawArticle::where('status', '=', '0')->find($id);
        $category = $raws->category;
        $result = [];
        $suggest_tags = [];
        $count = 0;
        foreach ($arrays as $array) {
            // echo $count;
            $count++;
            if ($category->id  == $count) {
                // print_r($array);
                $result = $array;
            }
        }
        // dd($result);
        $raws = RawArticle::where('status', '=', '0')->find($id);

        $contents = Content::where('article_id', $raws->id)->get();

        foreach ($contents as $content) {
            //condition sit yan kyan(with category)
            foreach ($result as $sample) {

                if (strstr($content->content_text, $sample)) {
                    $suggest_tags[] = $sample;
                }
            }
        }
        $count_tags = array_count_values($suggest_tags);

        if (sizeof($count_tags) > 5) {
            return array_slice($count_tags, 0, 5);
        } else
            return $count_tags;


        //Testing suggest tags with sample data
        // $sample_tags = array(
        //     'ရောဂါ', 'ကလေး', 'ဗီတာမင်', 'အစားအစာ', 'အာဟာရ', 'ငွေကြေး', 'ငွေ', 'လေ့ကျင့်ခန်း', 'ခါးနာ', 'ဒဏ်ရာ', 'ကျောရိုး', 'အသက်ရှူ', 'ကိုဗစ်', 'အောက်ဆီဂျင်', 'ကျန်းမာခြင်း'
        // );
        // $suggest_tags = [];
        // $raws = RawArticle::where('status', '=', '0')->find($id);

        // $contents = Content::where('article_id', $raws->id)->get();

        // foreach ($contents as $content) {
        //     //condition sit yan kyan(with category)
        //     foreach ($sample_tags as $sample) {
        //         if (strstr($content->content_text, $sample)) {
        //             $suggest_tags[] = $sample;
        //         }
        //     }
        // }
        // $count_tags = array_count_values($suggest_tags);

        // if (sizeof($count_tags) > 5) {
        //     return array_slice($count_tags, 0, 5);
        // } else
        //     return $count_tags;
    }

    // duplicate
    // public static function checkDuplicate($id)
    // {
    //     // $sent_articles = RawArticle::where('status', 1)->limit(30)->get('title');
    //     // $raws_articles = RawArticle::where('status', 0)->limit(30)->get('title');
    //     $raws_id = RawArticle::where('status', '=', '0')->find($id);
    //     $raws_contents = Content::where('article_id', $raws_id->id)->get();
    //     $sent_id = RawArticle::where('status', '=', '0')->find($id);
    //     $sent_contents = Content::where('article_id', $sent_id->id)->get();

    //     foreach ($raws_contents as $raws_c) {
    //         foreach ($sent_contents as $sent_c) {
    //             $check_duplicate = similar_text($raws_c, $sent_c, $perc);

    //             if ($perc > 95) {
    //                 echo "similarity: $check_duplicate ($perc %)\n";
    //             }
    //         }
    //     }
    // }
}
