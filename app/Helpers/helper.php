<?php

namespace App\Helpers;

use App\Log;
use App\Models\Articles\RawArticle;
use App\Models\Scrapes\Content;
use App\Models\Scrapes\Website;
use App\Models\Settings\Category;
use App\Models\Settings\Tag;
use Dotenv\Result\Result;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use phpDocumentor\Reflection\Types\Null_;
use PhpOffice\PhpSpreadsheet\Calculation\MathTrig\Arabic;

class Helper
{

    //uuid
    public static function uuid()
    {
        return Str::orderedUuid(date('Y-m-d H:i:s'));
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
            'committee representing pyidaungsu hluttaw', 'ဒေါ်အောင်ဆန်းစုကြည်', 'အမေစု', 'နိုင်ငံတော်အတိုင်ပင်ခံပဂ္ဂိုလ်', 'VPN', 'vpn', 'Virtual Private Network', 'VirtualPrivateNetwork', 'VIRTUALPRIVATENETWORK',
            'VIRTUAL PRIVATE NETWORK', 'virtual private network', 'virtualprivatenetwork', 'မြန်မာ့တပ်မတော်', 'အာဏာသိမ်းစစ်တပ်', 'စစ်ကောင်စီ', 'စကစ', 'အကျဉ်းထောင်',
            'UNITED NATIONS', 'ကုလသမဂ္ဂ', 'ပုဒ်မ-၅၀၅(က)', 'ပုဒ်မ-၅၀၅(ခ)', 'ပုဒ်မ-၅၉(ဃ)', 'အာဆီယံ', 'အရှေ့တောင်အာရှနိုင်ငံများအသင်း', 'ASEAN', 'asean', 'Asean', 'ထိန်းသိမ်းခံ',
            'ဖဒရယ်', 'Federal', 'federal', 'နိုင်ငံရေးသမား', 'အမျိုးသားဒီမိုကရေစီအဖွဲ့ချုပ်', 'NLD', 'National League for Democracy', 'NationalLeagueforDemocracy', 'NATIONALLEAUGEFORDEMOCRACY', 'NATIONAL LEAGUE FOR DEMOCRACY',
            'nationalleaguedemocracy', 'natioanl league democracy', 'ဗုံး', 'ဗုံးပေါက်ကွဲ', 'ဗုံးခွဲတိုက်ခိုက်ခံရ', 'Boycott', 'ဒေါက်တာဆာဆာ', 'စစ်ကျွန်ပညာရေး', 'ဒေါက်တာဇော်ဝေစိုး', 'အာဏာသိမ်း', 'အာဏာထိမ်း',
            'အာဏာရှင်', 'အာဏာရူး', 'နိုင်ငံရေး အကျဉ်းသား', 'နွေဦးတော်လှန်ရေး', 'တရားဥပဒေ စိုးမိုးမှု', 'ဆူပူအောင်လှုံဆော်မှု', 'ဒလန်', 'ကျောင်းသားသမဂ္ဂ', 'လက်နက်', 'telenor', 'Telenor', 'တယ်လီနော', 'ooredoo', 'Ooredoo', 'အော်ရီဒူး', 'mytel', 'Mytel', 'မိုင်တဲ', 'mectel', 'Mectel', 'ကမ်းရိုးတန်းဖုန်းနံပါတ်', 'Wave Money', 'wave money','Meta','meta'
        );
        $blist = [];
        $raws = RawArticle::find($id);
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
    //sensitive keywords
    public static function sensitive_keywords($id)
    {
        $sensitive_keywors = array(
            'telenor', 'Telenor', 'တယ်လီနော', 'ooredoo', 'Ooredoo', 'အော်ရီဒူး', 'mytel', 'Mytel', 'မိုင်တဲ', 'mectel', 'Mectel', 'ကမ်းရိုးတန်းဖုန်းနံပါတ်', '18+', '၁၈+', 'fuck', 'Fuck', 'လိင်ဆက်ဆံ', 'လိင်', 'sex', 'suck', 'dick', 'shit', 'ဖောင်းဒိုင်း', 'Sexual', 'sexual', 'sexuality', 'Sexuality', 'နိုင်ငံရေး', 'ကျောင်းသားများ သမဂ္ဂ', 'သမဂ္ဂ','Meta','meta'
        );
        $sensitive_list = [];
        $raws = RawArticle::find($id);
        $contents = Content::where('article_id', $raws->id)->get();
        foreach ($contents as $content) {
            foreach ($sensitive_keywors as $sen_keywords) {
                if (strstr($content->content_text, $sen_keywords)) {
                    $sensitive_list[] = $sen_keywords;
                }
            }
        }
        $count_sensitive = array_count_values($sensitive_list);
        return $count_sensitive;
    }

    //suggesting tags function
    public static function suggest_tags($id)
    {
        $sample_tags = Tag::get();
        $suggest_tags = [];
        $result_tags = [];
        $raws = RawArticle::find($id);
        $contents = Content::where('article_id', $raws->id)->get();
        foreach ($contents as $content) {
            foreach ($sample_tags as $sample) {
                // dd($sample->nameMm);
                if (strstr($content->content_text, $sample->nameMm)) {
                    $suggest_tags[] = $sample->nameMm;
                }
            }
        }
        // dd($suggest_tags);
        $count_tags = array_count_values($suggest_tags);
        arsort($count_tags); //sorting with value
        // dd($count_tags);
        //limit tags
        $count = 0;
        foreach ($count_tags as $key => $value) {
            if ($count < 5) {
                // echo "$key($value)<br>";
                $result_tags[] = $key;
            }
            $count++;
        }
        // return $result_tags;
        $result_key = [];
        foreach ($sample_tags as $tag) {
            foreach ($result_tags as $r_tag) {
                if (strstr($r_tag, $tag->nameMm)) {
                    $result_key[] = $tag->id;
                }
            }
        }
        return (array_slice($result_key, 0, 5));
    }
    //suggesting category from tags
    public static function suggest_category($id)
    {
        $sample_category = [
            ['ဗေဒင်', 'အလုပ်အကိုင်', 'စီးပွားရေး', 'ယတြာ', 'ငွေကြေး', 'မေတ္တာရေး', 'အိမ်ထောင်ရေး', 'လှူ', 'ဆုတောင်း', 'စိတ်ချမ်းသာ', 'အခွင့်အလမ်း', 'မကျန်းမမာ', 'အိုးအိမ်', 'ရာသီခွင်', 'စက်တင်ဘာ', 'ဟောကိန်း', 'ကံကောင်းခြင်း', 'ကတ်ကလေး', 'သြဂုတ်', 'ဇူလိုင်', 'ဇွန်', 'ဧပြီ', 'ဖေဖော်ဝါရီ', 'ဒီဇင်ဘာ', 'နိုဝင်ဘာ', 'အောက်တိုဘာ', 'တူရာသီဖွား', 'နေ့ခင်း', 'ဘုရား', 'အလုပ်ကိစ္စ', 'October', 'ဇန်နဝါရီလ', 'ကံကောင်း',], //horoscope
            ['ကိုယ်ချင်းစာ','အလုပ်သမား', 'အိပ်', 'ရေချိုး', 'အစားအစာ', 'ဘဝ', 'အချစ်ရေး', 'ကျန်းမာရေး', 'ခန္ဓာကိုယ်', 'လေ့ကျင့်ခန်း', 'ဇာတ်လမ်း', 'ရည်မှန်းချက်', 'စိတ်ဖိစီးမှု', 'ပေါင်းသင်းဆက်ဆံရေး', 'relationship', 'Relationship', 'ဆုံးဖြတ်ချက်', 'အမူအကျင့်', 'Virgo', 'ဆွေးနွေး', 'နည်းဗျူဟာ', 'လက္ခဏာ',  'အရပ်ရှည်',  'ပျားရည်', 'အုန်းရည်', 'စိုးရိမ်ပူပန်မှု', 'ဆန်ဆေးရည်', 'နေ့ရက်တိုင်း', 'ဝဝကစ်ကစ်', 'စိတ်ဓာတ်ကြံ့ခိုင်', 'အောင်မြင်မှု', 'မောင်နှမ', 'ဓမ္မတာ', 'ချစ်သူ', 'အချစ်ရေးဆက်နွယ်မှု', 'Pre-Wedding', 'Pose', ' အပေါင်းအသင်း', 'ရယ်စရာ', 'တစ်ယောက်ထဲ', 'ခံစားရ', 'ပြဿနာ', 'ရန်ဖြစ်စကားများ', 'ဆဲဆိုခြင်း', 'အတွဲ', 'ကိုယ်ထိလက်ရောက်ပြုမူခြင်း', 'ဒေါသ', 'Bad Mood',  'လန်းဆန်းတက်ကြွ', 'တရားထိုင်ပေးခြင်း', 'social media', 'Social Media', 'ဆွဲဆောင်မှု', 'ဆွယ်တာ', 'ရည်းစား', 'best friend', 'သတိရ', 'ဝမ်းနည်း', 'relax', 'စရိုက်', 'တဒင်္ဂ', 'စုံတွဲ', 'အမူအကျင့်', 'အကြောင်းအရာ', 'ပျားရည်ဆမ်းခရီး', 'လက်မ', 'လူစိမ်း', 'ပိတ်ရက်', 'သူစိမ်း', 'အပေါ်ထပ်', 'အမျိုးသား', 'အစစအရာရာ', 'လူအများစု', 'မိတ်ဆွေ', 'ဘေးနား', 'အခက်အခဲ', 'ပတ်ဝန်းကျင်', 'ဘေးစောင်း', 'ပုံစံ', 'ဦးထုပ်', 'တစ်ခြားလူ', 'ဒဏ္ဍာရီ', 'လိုအပ်ချက်', 'ကိုယ်ပိုင်', 'ရုပ်ရည်', 'အမှား', 'မိဘ', 'အပြုသဘော', 'ပန်းတိုင်', 'ရိုးသား', 'ဆွဲဆောင်ခြင်း', 'ယုံကြည်', 'စတိုင်လ်', 'မိန်းကလေး', 'focus', 'အရာ', 'မိသားစု', 'လက်ဆောင်', 'ကြိုးစား', 'စိတ်အခြေအနေ', 'တွေ့ရှိချက်', 'ကောင်လေး', 'ကောင်မလေး', 'အကြည်ဓာတ်', 'ဝန်ထုပ်ဝန်ပိုး', 'ကိုယ်ခန္ဓာ', 'စိတ်ပိုင်းဆိုင်ရာ', 'အသက်', 'ပလက်ဖောင်း', 'နားလည်မှု', 'စိတ်ခွန်အား', 'အဖုအထစ်', 'ယောက်မ', 'လက်ထပ်', 'ဖိတ်စာ', 'အိမ်မက်', 'စင်္ကြာ', 'သမီးရည်းစား', 'သစ္စာဖောက်', 'အသစ်', 'လက်ပြတ်', 'Photo', 'photo', 'အကျင့်', 'ကောက်ကြောင်း', 'အချိန်', 'အနိုင်', 'သဝန်တို', 'အတွေ့', 'သားသမီး', 'ဘဏ္ဍာရေး', 'အထီးကျန်', 'အမှု', 'ထွက်ခွာ', 'လေ့လာ', 'lifestyle', 'မွေးနေ့', 'ပန်းစည်း', 'ဆွဲကြိုး', 'ဖန်စီ', 'အရူး', 'ကျန်ရစ်သူ', 'ခါးပတ်', 'အမှတ်တရ', 'မိုးတွင်း', 'အဝတ်အစား', 'မသိစိတ်', 'ကြံ့ခိုင်', 'ဝီစကီ', 'Whisky', 'Grand Royal', 'အိမ်ထောင်ပြု', 'အခွင့်အလမ်း', 'ယဥ်ယဥ်ကျေးကျေး', 'ယောက္ခမ', 'အနာဂတ်အကြောင်း', 'လက်ကိုင်ပဝါ', 'လက်ထပ်ခြင်း', 'မင်္ဂလာဝတ်စုံ', 'သတို့သမီး', 'ဂျပန်', 'စိတ်ကုန်', 'လမ်းခွဲ', 'စာဖတ်သူ', 'စိတ်ဓာတ်ကျ', 'ယုံကြည်မှု', 'အိမ်ထောင်ဖက်', 'အကြောက်တရား', 'DIY', 'ငွေ', 'communication', 'လက်တွဲဖော်', 'ကိုယ်ဟန်အနေအထား', 'လေကောင်းလေသန့်', 'ပျော်ရွှင်မှု', 'အဆိုးမြင်အတွေး', 'စိတ်ငြိမ်းချမ်းမှု', 'နည်းလမ်းကောင်း', 'single', 'ခရီးသွားခြင်း', 'အပျင်းကြီးသူ', 'အပန်းဖြေခရီး', 'ရည်းစားဟောင်း', 'ပါတနာ', 'ရွှေရောင်အနာဂါတ်', 'ခရီးသွားများ', 'meditate', 'ခရီးလမ်း', 'ဆယ်ကျော်သက်', 'ဂိုဏ်းစတား', 'စရိုက်လက္ခဏာ', 'လောဘ', 'ရည်းစားစာ', 'ချစ်စရာ', 'အကူအညီရပ်၀န်း', 'လှည့်စား', 'လိမ်လည်', 'စဥ်းစား', 'မာန', 'ကြင်နာ', 'အားလပ်ရက်', 'ကိုယ်ချမ်းသာ', 'ဘ၀နေထိုင်မှု', 'သူငယ်ချင်း', 'အထွေထွေ ဗဟုသုတ', 'စိတ်ပြေလက်ပျောက်', 'flirt', 'ယမကာ', 'အထောက်အကူ', 'Motivate', 'မာရသွန်', 'လက်တွဲဖက်', 'ဒိုက်ထိုး','date'], //lifestyle
            ['K-Drama', 'K-POP', 'ပေါ်ပြူလာ', 'BLACKPINK', 'ကိုရီးယား', 'မော်ဒယ်', 'မင်းသမီး', 'မင်းသား', 'ဇာတ်လမ်း', 'Netflix', 'drama', 'list', 'stress', 'အနုပညာ', 'သရုပ်ဆောင်', 'နာမည်ကျော်', 'famous', 'Photography', 'photography', 'ထိုင်း', 'သရဲဇာတ်လမ်း', 'အလုပ်နားရက်', 'ပရိတ်သတ်', 'ဦးဦး', 'မမ', 'ဒရမ်မာ', 'အဖွဲ့ဝင်', 'အိမ်ထောင်သည်', 'model', 'Model', 'နာမည်ကြီး', 'အော်စကာ', 'ပရိသတ်', 'ပုံစံ', 'အဆိုတော်', 'Lisa', 'Jennie', 'Jsoo', 'Rose', 'Han So Hee', 'Korea', 'IG', 'Main Role', 'Fan', 'ရိုက်ကွင်း', 'ဇာတ်ဝင်ခန်း', 'ရုပ်ရှင်ကား', 'ရုပ်ရှင်ရိုက်ကူးရေးသမား', 'သီချင်း', 'ဗီလိန်', 'ဒစ်စနေး', 'အေဂျင်စီ', 'Idols', 'ဒါရိုက်တာ', 'Entertainment', 'ရုပ်ရှင်ဇာတ်လမ်းတွဲ', 'Comeback', 'Girls Group', 'တောင်ကိုရီးယား', 'K Pop music', 'Aespaအဖွဲ့', 'ဇာတ်သိမ်းပိုင်း', 'လီမင်ဟို', 'အနုပညာရှင်', 'ဖျော်ဖြေရေး', 'သူဇာ၀င့်လွင်', 'Thuzar Wint Lwin', 'Map Of The Soul', 'BTS', 'ခြိမ်းခြောက်စာ', 'အာရီယာနာ ဂရန်ဒီ', 'ထိုင်းသရုပ်ဆောင်', 'ကိုရီးယားမင်းသား', 'ဂျွန်ကီ', 'Marvel', 'ရုပ်ရှင်ရုံ', 'ဂီတ', 'ဇာတ်ကား', 'TWICE'], //entertainment
            ['ဝတ္ထုတိုများ', 'အယ်လ်ကာပုန်း', 'ခံစားချက်', 'တစ်ပွင့်စိန်', 'လူမိုက်', 'စိန်', 'အဘကျော်', 'သရဲ', 'သေတ္တာ', 'အရေးပိုင်', 'ကပ်ပါး', 'အချစ်ပုံပြင်', 'ကျိန်စာ', 'ရုက္ခစိုး', 'ဖိုးချိူ', 'အချစ်ဇာတ်လမ်း', 'ဖိုးတေ', 'ကုမုဒြာ', 'မမဲလုံး'], //short_story
            ['မေးခွန်း', 'question', 'စမ်းသပ်ချက်', 'သိပ္ပံပညာရပ်', 'ယာဉ်မောင်း', 'ဂိမ်း', 'ယာဉ်', 'မေးခွန်းများ', 'ခန့်မှန်း',], //quiz
            ['ခြေအဆုံး', 'နည်းလမ်းများ', 'သဘာဝ', 'နှုတ်ခမ်းနီ', 'မိတ်ကပ်', 'တင်ပါး', 'dress', 'လက်သည်း',  'ကော်ဖီ', 'သံသယ', 'အသားအရေ', 'မျက်ခုံးမွှေး', 'ခြေထောက်', 'သန့်ရှင်းရေး', 'oil scrub', 'salt scrub', 'sugar scrub', 'body scrub', 'ဖျက်ဆေး', 'နုပျို', 'မျက်နှာ', 'စကပ်', 'ရေမွှေး', 'အညိုရောင်', 'ခရမ်းရောင်', 'ခြေသည်း', 'စတိုင်', 'တက်တူး', 'နှုတ်ခမ်းလေး', 'မျက်လုံး', 'eye', 'အကြံပြုချက်', 'လက်စွပ်', 'ရေနွေးငွေ့', 'ဉာဏ်ကောင်း', 'အိတ်', 'EyeShadow', 'မီးခိုးရောင်', 'သွားတိုက်ဆေး', 'အပြာရောင်', 'crop top', 'အဆီဖု', 'ဘောင်းဘီ', 'အပြုံး', 'နည်းလမ်း', 'အင်္ကျီ', 'freestyle', 'ဝမ်းဆက်', 'ဒေါက်ဖိနပ်', 'accessories', 'အမဲ', 'ပန်းရောင်', 'ရှင်းရှင်းလေးနဲ့လှ', 'အဖြူရောင်', 'ပေါ့ပေါ့ပါးပါးလေး', 'လှလှ', 'အကောက်', 'အလှပဂေး', ' Body Fit', 'powder', 'sunscreen', 'အသားခြောက်', 'Glow', 'အရမ်းလှ', 'ဆံသား', 'fashion', 'ဖက်ရှင်', 'အလေးချိန်', 'ကိုယ်အလေးချိန်', 'အဆီ', 'sensitive skin', 'glow', 'အရောင် ၁၀ မျိုး', 'လိုက်ဖက်တဲ့ အရောင်', 'ဖိနပ်', 'Exercise', 'ဦးခေါင်း', 'Setting Spray', 'ထိန်းသိမ်းနည်း', 'ဆံပင်ကျွတ်ခြင်း', 'Foundation', 'ဓာတ်ပုံဝါသနာရှင်', 'ဆံပင်အုံထူ', 'ဦးရေပြား', 'ပေါင်တံ', 'Squats', 'အုန်းဆီ', 'Sneakers', 'nude colour', 'Sand colour', 'အထာကျကျ', 'ဒီဇိုင်း', 'butterfly', 'brand', 'Budget', 'Skincare', 'thinking', 'ဂါဝန်', 'ကယ်လိုရီ', 'ဂါဝန်အဖြူ', 'အမှတ်တရဓာတ်ပုံ', 'ပို့စ်ပေးနည်းများ', 'Cotton pads', 'Eye Makeup', 'အမျိုးအစား', 'Skin', 'ခေါင်းလျှော်ရည်', 'ဆံပင်ပါး', 'mask', 'ဝိတ်ချနည်း', 'ပေါင်ဒါ', 'ဆံသားခြောက်', 'Dry Skin', 'Flannel Shirt', 'ရှပ်အင်္ကျီ', 'ယောကျာ်းဆန်', 'No Makeup', 'ဆံပင်အဆီ', 'Hair Mask', 'မျက်နှာအဆီ', 'ရင်သား', 'လက်မောင်း', 'မြန်မာဝတ်စုံ', 'မျက်မှန်', 'စတူဒီယို', 'ပို့စ်ပေးနည်း', 'ဖက်ရှင်ဒီဇိုင်နာ', 'အဆီပို', 'အသားအရည်', 'သန့်ရှင်းမှု', 'စကတ်', 'အနီ', 'ရွေးချယ်မှူ', 'ဆံပင်ပုံစံ', 'အလှအပ', 'ဓာတ်ပုံ', 'ယောဂ', 'ဆံပင်သန်', 'ပျိုမေ', 'ခေါင်းအစ', 'အသားပွန်း', 'စတား', 'ခံတွင်း', 'ဆံသားပျက်ဆီး'], //beauty
            ['ဟာသ', 'funny', 'ကာတွန်း'], //funny
            ['ကျန်းမာရေး', 'အလေ့အကျင့်',  'လေ့ကျင့်ခန်း', 'ကောင်းကျိုး',  'ဆေးသောက်', 'လေထိုးလေအောင့်', 'ချောင်းဆိုး',  'အဆုတ်', 'ဒဏ်ရာ', 'စတော်ဘယ်ရီသီး', 'လမ်းလျှောက်ခြင်း', 'လိမ္မော်သီး', 'ကြက်သား', 'ကြက်သွန်ဖြူ', 'အစာအိမ်', 'အက်ဆစ်', 'ဝေဒနာ', 'အရသာ', 'ဗက်တီးရီးယား', 'တံတွေး', 'အနာ', 'နောက်ကျော', 'မျိုးရိုး', 'နေရောင်ခြည်', 'ဗီဇ', 'အရေပြား', 'နှလုံး', 'ကြွက်သား', 'ရင်ဘတ်', 'ဆရာဝန်', 'အခွံမာသီး', 'ကိုယ်တွင်း', 'ရှားစောင်းလက်ပပ်', 'ဗီတာမင်', 'အရိုးအဆစ်', 'အန္တရာယ်', 'တိုက်ဖွိုက်', 'ဖာရင်ဟိုက်', 'အပူချိန်', 'အောက်ဆီဂျင်', 'ကိုယ်ဝန်ဆောင်', 'သွေးခဲ', 'သွေးလွှတ်ကြော', 'သွေးပြန်ကြော', 'သွေးကျဲဆေး', 'ခေါင်းကိုက်ခြင်း', 'ရေနွေးအိတ်', 'နဖူး', 'ရေအေး', 'ဂျင်းပြုတ်ရည်', 'ကိုယ်ခံအား', 'မနက်', 'အာရုံ', 'ထိခိုက်ခြင်း', 'ခြေဖဝါး', 'ခြေဖနောင့်', 'ဆီးချို', 'အူလမ်းကြောင်း', 'ကင်ဆာ', 'အကြောပြတ်', 'ဆိုးကျိုးများ', 'Vitamin C', 'Vitamin D', 'ဆေးလိပ်', 'baby', 'self quarantine', 'negative', 'positive', 'COVID-19', 'ဦးနှောက်', 'ရောဂါ', 'လျှာ', 'အရက်', 'အသည်းကျန်းမာရေး', 'ရင်ဘက်', 'အကြောဆန့်', 'နမိုးနီးယား', 'ကိုဗစ်', 'ဘေးထွက်ဆိုးကျိုးများ', 'နာတာရှည်', 'အဆစ်လွဲ', 'ဒူးခေါင်း', 'ရောင်ရမ်း', 'မိခင်', 'Lutein', 'မျိုးပွားခြင်း', 'သွေးကြော', 'ဦး နှောက်', 'ဆိုးကျိုး', 'ရင်သွေးငယ်', 'ရေနွေး', 'ဗိုင်းရပ်စ်', 'အသက်ရှု', 'သွားကျန်းမာရေး', 'ကုသ', 'သောက်ဆေး'], //health
            ['အစားအသောက်',  'အသီးအရွက်', 'စားသောက်ဆိုက်', 'Thai', 'အစားအစာ', 'ရန်ကုန်', 'BBQ', 'Hot Pot', 'ကော်ဖီ', 'ဟင်းလျာ', 'အသားကင်', 'ခရီးသည်', 'နံနံပင်', 'သားငါး', 'မီးဖိုချောင်', 'သစ်သီး', 'လက်ဖက်ရည်', 'ကြက်ဥ', 'ကန်စွန်းဥ', 'ငှက်ပျောသီး', 'မှိုချဉ်', 'ပုစွန်', 'ဆိတ်သား', 'ဝက်သား', 'ဟင်းလေး', 'ပန်းဂေါ်ဖီ', 'နွားနို့', 'ဘယ်ရီသီး', 'ပေါက်ပေါက်ဆုပ်', 'မှိုခြောက်', 'ကြာဇံ', 'ကန်စွန်းရွက်', 'ဟင်းရည်', 'ဟော့ပေါ့', 'ပြောင်းဖူး', 'ပုဇွန်', 'ဂဏန်း', 'ဘီစကွတ်', 'ဆား', 'သကြား', 'အကင်စုံ', 'ကြေးအိုး', 'ပန်းသေးခေါက်ဆွဲ', 'ငါးပိရည်', 'တို့စရာ', 'တညင်းသီး', 'အင်တုံ', 'ငါးပိထောင်း', 'အငန်', 'ခရမ်းချဉ်သီး', 'ထောပတ်သီး', 'နို့ဆီ', 'ထမင်း', 'ရေညှိ', 'အကြော်အလှော်', 'အာဟာရ', 'အာလူး', 'အဆံ', 'အစေ့', 'ချောကလက်', 'ပန်းသီး', 'သခွားသီး', 'မုန်လာဥ', 'ငါး', 'ပျားရည်', 'online booking', 'Digital Menu', 'oatmeal', 'Customer', 'သံပယိုရည်', 'Pizza', 'ပူစီနံ'], //food
            ['စံနှုန်း','Google', 'vivo', 'apple', 'Binace', 'Xiaomi', 'Pad', 'Phone', 'Android', 'android', 'phone', 'တရုတ်', 'Samsung', 'မားကတ်တင်း', 'အင်တာနက်', 'iPhone', 'Watch', 'Huawei', 'Mi', 'Tik Tok', 'Youtube', 'အွန်လိုင်း', 'ဒစ်ဂျစ်တယ်',  'ဘက်ထရီ', 'နည်းပညာ', 'Viber', 'viber', 'Facebook', 'facebook', 'ကင်မရာ', 'camera', 'အင်တာနက်', 'internet', 'ကေဘယ်', 'cable', 'မိုက်ခရိုဖုန်း', 'microphone', 'စမတ်ဖုန်း', 'Amazon', 'memory', 'PHP', 'Design', 'Windows', 'Zoom', 'Application', 'battery', 'Oppo', 'Sony', 'မော်တော်ယာဉ်', 'LG', 'TCL', 'CES', 'Technology', 'TV', 'AAA', 'Remote', 'ဂြိုဟ်တု', 'ဆက်သွယ်ရေး', 'သိပ္ပံ', 'CEO', 'မက်ဆေ့', 'MPT', 'ZEISS', 'group chat', 'WhatsApp', 'QR code', 'Tencent', 'Samsaung Display', 'tournament', 'iOS 15', 'Find My', 'Apple', 'စီးပွားရေးလုပ်ငန်း', 'Chatbot', 'အင်ဂျင်', 'အက်ပလီကေးရှင်း', 'စမ်းသပ်မှု', 'OnePlus', 'OxygenOS 12', 'Android 12', 'Instagram', 'Staycation', 'ကုမ္ပဏီ', 'အမေရိကန်', 'ခရစ်ပ္ပတို', 'တရုတ်', 'ဝိုင်ဖိုင်', 'Messenger', 'စက်ရုပ်', 'smart phone', 'ဖုန်း', 'ရင်းနှီးမြှပ်နှံ', 'Meta', 'developer', 'ရိုဘော့', 'ထုတ်ကုန်', 'ငွေစက္ကူ','လုပ်ဆောင်ချက်'], //technology
            ['အားကစား', 'ဘောလုံး', 'ဇီဒန်း', 'မော်ရင်ဟို', 'ကစားသမား', 'စကိတ်စီး', 'စက်ဘီး', 'စကိတ်ကွင်း', 'ရေကူး', 'မန်ယူ', 'လီဗာပူး', 'အာဆင်နယ်', 'ယူနိုက်တက်', 'စီနီယာ', 'လူငယ်', 'နောက်ခံ', 'နောက်တန်း', 'မက်ဒရစ်', 'အစွန်', 'ရှေ့တန်း', 'ဘာစီလိုနာ', 'ဂိုးသမား', 'ဂိုးသွင်း', 'နည်းပြ', 'ကြယ်ပွင့်', 'အနာဂတ်', 'အသင်း', 'ဝင်ခွင့်', 'ကလပ်', 'ချန်ပီယံလိဂ်', 'ပရီးမီးယားလိဂ်', 'လာလီဂါ', 'မက်ဆီ', 'ရော်နယ်ဒို', 'ပွဲစဉ်', 'လစာ', 'ရက်သတ္တပတ်', 'တိုက်စစ်', 'ထိပ်တန်း', 'ပစ်မှတ်', 'ကလော့ပ်', 'သွင်းဂိုး', 'ပီအက်စ်ဂျီ', 'ခြေစွမ်း', 'ပါတီ', 'လူစားလဲ', 'စည်းမျဉ်း', 'ကမ္ဘာ', 'သဘောတူညီ', 'နေမာ', 'ဖန်တီးမှု', 'အနိုင်ဂိုး', 'ခက်ခဲ', 'ဂျူဗင်တပ်စ်', 'ဂျာမန်သား', 'အနားပေး', 'ပြောကြား', 'ဖလား', 'အမှတ်ပေးဇယား', 'ရုန်းကန်', 'မျှော်လင့်', 'ပြောင်းရွှေ့ကြေး', 'ဝေဖန်မှု'], //sports
            ['Organic', 'ဓာတုပစ္စည်း', 'ထွက်ကုန်', 'အသီးအနှံ', 'ဓာတ်မြေဩဇာ', 'စိုက်ပျိုး', 'သီးနှံ', 'ရွှေဘို', 'ပဲတီစိမ်း', 'သဖန်းဆိပ်', 'လယ်မြေ', 'ဆည်မြောင်း', 'မတ်ပဲ', 'ပွဲရုံ', 'ဈေး', 'စပါး', 'မိုးကာလ', 'နွေကာလ', 'ဆောင်း', 'ဆန်စက်', 'ရေဆင်း', 'ဆီစက်', 'တောင်သူ', 'ဈေးတန်း', 'တောင်ယာ', 'စက်ရုံ', 'လယ်ယာ', 'ရတနာ', 'ဈေးကွက်', 'ဖရဲ', 'Skywalk'], //agriculture
            ['indoor', 'plants', 'ခွေး', 'ဘာသာစကား', 'ပညာရေး', 'ကျောင်း', 'ပန်းချီ', 'မြစ်', 'လောက', 'လွယ်ကူလေ့လာ', 'အကျိူးကျေးဇူးများ', 'စကားလုံး', 'ကဗျာ', 'တိရိစ္ဆာန်', 'စာလုပ်', 'ဘုန်းကြီးကျောင်း', 'တိရစ္ဆာန်များ', 'သမုဒ္ဒရာ', 'သင်ခန်းစာ', 'သင်ရိုးသစ်', 'သင်ရိုးဟောင်း', 'ကထိန်'], //other
            ['နိုင်ငံ', 'တက္ကသိုလ်', 'စာမေးပွဲ', 'အောင်စာရင်း', 'မာတိကာ', 'ရွှေ', 'YBS', 'မီးလောင်ဒဏ်သင့်', 'ကော်မတီ', 'စည်ပင်သာယာရေး', 'နိုင်ငံ့ဝန်ထမ်း', 'ဝန်ထမ်းအိမ်ရာ', 'စက်လှေကား', 'ဓာတ်လှေကား', 'ရတနာပုံဈေး', 'ထောက်ပံ့ရန်ပုံငွေ', 'ဘူတာရုံ', 'ဝန်ဆောင်မှု', 'မီးရထား', 'လျှပ်စစ်ဓာတ်အား', 'ဆရာမ', 'တံတား'], //regional
            ['Style', 'Theory', 'လုပ်ငန်း', 'ဒေါ်လာ', 'ဥရောပ', 'အဖွဲ့အစည်း', 'အိန္ဒိယ', 'လာအို'] //international
        ];
        $sample_tags = Tag::get();
        $suggest_tags = [];
        $result_tags = [];
        $string = [];
        $result_category = [];
        $raws = RawArticle::find($id);
        $title = $raws->title;
        $horoscope = ['ရာသီခွင်', 'ရာသီဖွား', 'ဗေဒင်', 'ယတြာ', 'ကံဇာတာ', 'မင်္ဂလာအစီအရင်', 'နေ့နံ'];
        $lifestyle = ['ဆက်ဆံရေး', 'ချစ်နေတဃ်', 'အလုပ်', 'လမ်းလျှောက်', 'အစားအစာ', 'ဘဝ', 'အချစ်ရေး', 'ကျန်းမာရေး', 'ခန္ဓာကိုယ်', 'အထွေထွေ', 'ဗဟုသုတ', 'ရည်မှန်းချက်', 'စိတ်ဖိစီးမှု', 'ပေါင်းသင်းဆက်ဆံရေး', 'relationship', 'Relationship', 'ဆုံးဖြတ်ချက်', 'အမူအကျင့်', 'Virgo', 'ဆွေးနွေး', 'နည်းဗျူဟာ', 'လက္ခဏာ', 'အရပ်ရှည်', 'ပျားရည်', 'ဓာတ်ပုံ', 'သန့်ရှင်းရေး', 'အုန်းရည်', 'လမ်းခွဲ', 'လမ်းလျှောက်ခြင်း', 'စိုးရိမ်ပူပန်မှု', 'ဆန်ဆေးရည်', 'ဝဝကစ်ကစ်', 'စိတ်ဓာတ်ကြံ့ခိုင်', 'အောင်မြင်မှု', 'မောင်နှမ', 'ဓမ္မတာ', 'ချစ်သူ', 'အချစ်ရေးဆက်နွယ်မှု', 'ဘ၀', 'ခေါင်း', 'Pre-Wedding', 'Pose', 'အပေါင်းအသင်း', 'ရယ်စရာ', 'တစ်ယောက်ထဲ', 'ခံစားရ', 'ပြဿနာ', 'ရန်ဖြစ်စကားများ', 'ဆဲဆိုခြင်း', 'အတွဲ', 'ကိုယ်ထိလက်ရောက်ပြုမူခြင်း', 'ဒေါသ', 'Bad Mood', 'လန်းဆန်းတက်ကြွ', 'တရားထိုင်ပေးခြင်း', 'social media', 'Social Media', 'ချစ်စရာကောင်း', 'ဆွယ်တာ', 'မိုက်', 'အထာကျ', 'ရည်းစား', 'best friend', 'သတိရ', 'ဝမ်းနည်း', 'relax',  'စရိုက်', 'တဒင်္ဂ', 'စုံတွဲ', 'အမူအကျင့်', 'အကြောင်းအရာ', 'ပျားရည်ဆမ်းခရီး', 'လက်မ', 'လူစိမ်း', 'ပိတ်ရက်', 'သူစိမ်း', 'အပေါ်ထပ်', 'အစစအရာရာ', 'လူအများစု', 'မိတ်ဆွေ', 'ဘေးနား', 'အခက်အခဲ', 'ပတ်ဝန်းကျင်',  'ဘေးစောင်း', 'ဦးထုပ်', 'တစ်ခြားလူ', 'ဒဏ္ဍာရီ', 'လိုအပ်ချက်', 'ကိုယ်ပိုင်', 'ရုပ်ရည်', 'အမှား', 'မိဘ', 'အပြုသဘော', 'ပန်းတိုင်', 'ရိုးသား', 'ဆွဲဆောင်', 'ယုံကြည်', 'focus', 'အရာ', 'စိတ်ဖိစီး', 'မိသားစု', 'လက်ဆောင်', 'ကြိုးစား', 'စိတ်အခြေအနေ', 'တွေ့ရှိချက်', 'အကြည်ဓာတ်', 'ဝန်ထုပ်ဝန်ပိုး', 'ကိုယ်ခန္ဓာ', 'စိတ်ပိုင်းဆိုင်ရာ', 'ပလက်ဖောင်း', 'နားလည်မှု', 'စိတ်ခွန်အား', 'အဖုအထစ်', 'ယောက်မ', 'လက်ထပ်', 'ဖိတ်စာ', 'အိမ်မက်', 'စင်္ကြာ', 'သမီးရည်းစား', 'သစ္စာဖောက်', 'အသစ်', 'လက်ပြတ်', 'အကျင့်', 'ကောက်ကြောင်း', 'အထာ', 'အချိန်', 'အနိုင်', 'သဝန်တို', 'အတွေ့', 'သားသမီး', 'ဘဏ္ဍာရေး', 'အထီးကျန်', 'အမှု', 'ထွက်ခွာ', 'လေ့လာ', 'lifestyle', 'ဘဝနေထိုင်မှု', 'မွေးနေ့', 'ပန်းစည်း', 'ဆွဲကြိုး', 'ဖန်စီ', 'အရူး', 'ကျန်ရစ်သူ', 'လောက', 'ခါးပတ်', 'အမှတ်တရ', 'မိုးတွင်း', 'အဝတ်အစား', 'မသိစိတ်', 'ကြံ့ခိုင်', 'စရိုက်လက္ခဏာ', 'ကံထူးရှင်', 'ဂုဏ်ပြု', 'လက်တွေ့အသုံးချ', 'လက်ခံမှု', 'သိသာစေတဲ့အချက်များ', 'စိတ်ပျော်ရွှင်မှု', 'လက်တွဲဖော်', 'ချစ်လည်းချစ်', 'မုန်းလည်းမုန်း', 'ပျင်း', 'စိတ်', 'လုပ်ပေး'];
        $entertainment = ['Kpop', 'K-pop', 'K-POP', 'KDrama', 'Idol', 'မင်းသား', 'မင်းသမီး', 'Aespa', 'Girl Group',
        'Kim Sae Ron', 'သရုပ်ဆောင်', 'Jennie', 'Jung Ho Yeon', 'ကေပေါ့', 'အိုင်ဒေါ', 'ရုပ်ရှင်', 'BIGBANG', 'Netflix', 'ဇာတ်လမ်း',
        'အနုပညာ', 'ဇာတ်ကောင်', 'ဟောလိဝုဒ်', 'သီချင်းသစ်', 'seventeen', 'သီချင်း', 'Idol', 'Drama', 'Han So Hee', 'Cele',
         'Bieber', 'အဖွဲ့', 'ပရိသတ်တွေရဲ့', 'TWICE', 'Bambi Zhu', 'Kylie Jenner', 'Kendall Jenner', 'စူပါမော်ဒယ်', 'song',
          'Song', 'Streamed','Taylor Swift','စင်မြင့်ထက်'];
        $beauty = ['ဖက်ရှင်', 'fashion', 'မိန်းကလေး', 'အသားအရေ', 'မိတ်ကပ်', 'Mask', 'ဆံသား', 'ပေါင်းတင်နည်း', 'လက်သည်းနီ', 'Makeup',
        'ဆံပင်', 'ဆံကေသာ', 'အလှအပ', 'Aerobic', 'လက်သည်း', 'မျက်နှာ', 'မျက်တောင်', 'နှုတ်ခမ်းနီ', 'တံတောင်ဆစ်', 'လက်စွပ်', 'အိတ်', 'EyeShadow', 'မီးခိုးရောင်', 'အညိုရောင်', 'သွားတိုက်ဆေး', 'အပြာရောင်', 'အလှအပရေးရာ', 'dress', 'crop top', 'အဆီဖု', 'ဘောင်းဘီ', 'အပြုံး', 'အင်္ကျီ', 'freestyle', 'ဝမ်းဆက်', 'ဒေါက်ဖိနပ်', 'accessories', 'အမဲ', 'ပန်းရောင်', 'ရှင်းရှင်းလေးနဲ့လှ', 'အဖြူရောင်', 'ပေါ့ပေါ့ပါးပါးလေး', 'လှလှ', 'အကောက်', 'အလှပဂေး', ' Body Fit', 'powder', 'sunscreen', 'အသားခြောက်', 'Glow', 'အရမ်းလှ', 'ဆံသား', 'ယောဂ', 'စတိုင်လ်', 'လေ့ကျင့်ခန်း', 'ပြုလုပ်ပေးရမယ့်အရာများ', 'workout', 'ကိုယ်အလေးချိန်', 'အလေးချိန်', 'ပို့စ်', 'Exercise', 'Photo', 'photo', 'ဝက်ခြံ', 'glow', 'အလှကုန်', 'လိမ်းခြယ်နည်း', 'ဒီဇိုင်း', 'scrub', 'Scrub', 'နုပျိုလှပ'];
        $health = ['ဓမ္မတာ','အောက်စီဂျင်', 'ကိုဗစ်', 'ရောဂါ', 'ကိုယ်ဝန်ဆောင်', 'နာတာရှည်', 'ဒူးနာ', 'ခါးနာ', 'ကျန်းမာရေးဆိုင်ရာကောင်းကျိုးများ', 'လေထိုးလေအောင့်', 'ထိပ်ပြောင်', 'ထိပ်မပြောင်', 'ချောင်းဆိုး', 'ဒဏ်ရာ', 'ထိခိုက်ဒဏ်ရာ', 'မျက်လုံးလေ့ကျင့်ခန်း', 'နာကျင်ခြင်း', 'တုပ်ကွေး'];
        $food = ['အစားအသောက်', 'အစားအစာ', 'ဖျော်ရည်', 'စားသောက်ဆိုင်', 'Bar', 'Mala Xiang Guo', 'BBQ', 'Hot Pot', 'ကော်ဖီ', 'သားငါး', 'အိမ်ရှင်မ', 'လက်ဖက်ရည်', 'ကင်မ်ချီ', 'အသီးအရွက်', 'အစားစား', 'ဟော့ပေါ့', 'ဝိတ်', 'Detox Water', 'ဟင်းရည်', 'sushi', 'မုန့်', 'ဟင်းသီးဟင်းရွက်', 'သွားစားသင့်တဲ့ဆိုင်', 'Foodie', 'စားသောက်နည်း', 'ထောပတ်သီး'];
        $technology = ['Computer', 'computer', 'Xiaomi', 'စမတ်ဖုန်း', 'MUI', 'VMware', 'Andriod', 'OnePlus', 'Honor', 'HONOR', 'Sony', 'Samsung', 'Apple', 'Facebook', 'Nokia', 'Wi-Fi', 'အင်တာနက်', 'FACEBOOK', 'INSTAGRAM', 'WHATSAPP', 'Fairphone', 'memory', 'Amazon', 'Google', 'vivo', 'Vivo', 'VIVO', 'Surface Duo', 'Binance', 'ခရစ်ပ္ပတို', 'ခရစ်ပ်တို', 'Viber', 'တရုတ်ဖုန်း'];
        $sports = ['အားကစား', 'ဘောလုံး', 'ဇီဒန်း', 'မော်ရင်ဟို', 'ကစားသမား', 'စကိတ်စီး', 'စက်ဘီး', 'စကိတ်ကွင်း', 'ရေကူး', 'မန်ယူ', 'လီဗာပူး', 'အာဆင်နယ်', 'ယူနိုက်တက်', 'စီနီယာ', 'လူငယ်', 'နောက်ခံ', 'နောက်တန်း', 'မက်ဒရစ်', 'အစွန်', 'ရှေ့တန်း', 'ဘာစီလိုနာ', 'ဂိုးသမား', 'ဂိုး', 'နည်းပြ', 'ကြယ်ပွင့်', 'အနာဂတ်', 'အသင်း', 'ဝင်ခွင့်', 'ကလပ်', 'ချန်ပီယံလိဂ်', 'ပရီးမီးယားလိဂ်', 'လာလီဂါ', 'မက်ဆီ', 'ရော်နယ်ဒို', 'ပွဲစဉ်', 'လစာ'];
        $agriculture = ['Organic', 'ဓာတုပစ္စည်း', 'ထွက်ကုန်', 'အသီးအနှံ', 'ဓာတ်မြေဩဇာ', 'စိုက်ပျိုး', 'သီးနှံ', 'ရွှေဘို', 'ပဲတီစိမ်း', 'သဖန်းဆိပ်', 'လယ်မြေ', 'ဆည်မြောင်း', 'မတ်ပဲ', 'ပွဲရုံ', 'ဈေး', 'စပါး', 'မိုးကာလ', 'နွေကာလ', 'ဆန်စက်', 'ရေဆင်း', 'ဆီစက်', 'တောင်သူ', 'ဈေးတန်း', 'တောင်ယာ'];
        // $other = ['indoor', 'plants', 'ခွေး', 'ဘာသာစကား', 'ကိုရီးယား', 'ပညာရေး', 'ကျောင်း', 'ပန်းချီ'];
        $regional = ['နိုင်ငံ', 'တက္ကသိုလ်', 'စာမေးပွဲ', 'အောင်စာရင်း', 'မာတိကာ'];
        $world = ['ဒေါ်လာ', 'ဥရောပ', 'အဖွဲ့အစည်း'];
        foreach ($horoscope as $horo) {
            if (strstr($title, $horo)) {
                $result_category = '1';
                return $result_category;
            }
        }
        if (empty($result_category)) {
            foreach ($entertainment as $ent) {
                if (strstr($title, $ent)) {
                    $result_category = '3';
                    return $result_category;
                }
            }
        }
        if (empty($result_category)) {
            foreach ($health as $h) {
                if (strstr($title, $h)) {
                    $result_category = '8';
                    return $result_category;
                }
            }
        }
        if (empty($result_category)) {
            foreach ($beauty as $bet) {
                if (strstr($title, $bet)) {
                    $result_category = '6';
                    return $result_category;
                }
            }
        }
        if (empty($result_category)) {
            foreach ($lifestyle as $life) {
                if (strstr($title, $life)) {
                    $result_category = '2';
                    return $result_category;
                }
            }
        }
        if (empty($result_category)) {
            foreach ($food as $f) {
                if (strstr($title, $f)) {
                    $result_category = '9';
                    return $result_category;
                }
            }
        }
        if (empty($result_category)) {
            foreach ($technology as $tech) {
                if (strstr($title, $tech)) {
                    $result_category = '10';
                    return $result_category;
                }
            }
        }
        if (empty($result_category)) {
            foreach ($sports as $sport) {
                if (strstr($title, $sport)) {
                    $result_category = '11';
                    return $result_category;
                }
            }
        }
        if (empty($result_category)) {
            foreach ($agriculture as $agri) {
                if (strstr($title, $agri)) {
                    $result_category = '12';
                    return $result_category;
                }
            }
        }
        if (empty($result_category)) {
            foreach ($regional as $reg) {
                if (strstr($title, $reg)) {
                    $result_category = '14';
                    return $result_category;
                }
            }
        }
        if (empty($result_category)) {
            foreach ($world as $wo) {
                if (strstr($title, $wo)) {
                    $result_category = '15';
                    return $result_category;
                }
            }
        }
        if (empty($result_category)) {
            $contents = Content::where('article_id', $raws->id)->get();
            //search tags
            foreach ($contents as $content) {
                foreach ($sample_tags as $sample) {
                    if (strstr($content->content_text, $sample->nameMm)) {
                        $suggest_tags[] = $sample->nameMm;
                    }
                }
            }
            $count_tags = array_count_values($suggest_tags);
            arsort($count_tags); //sorting with value
            // dd($count_tags);
            //limit tags
            $count = 0;
            foreach ($count_tags as $key => $value) {
                if ($count < 5) {
                    // echo "$key($value)<br>";
                    $result_tags[] = $key;
                }
                $count++;
            }
            // dd($result_tags);
            //output one tags
            foreach ($result_tags as $key => $value) {
                // echo $value;
                $string = $value;
                break;
            }
            //search category
            foreach ($sample_category as $key => $value) {
                foreach ($value as $v) {
                    if ($v == $string) {
                        $result_category = $key + 1;
                    } else {
                        // print_r('false');
                    }
                }
            }
            return $result_category;
        }
        if (empty($result_category)) {
            $result_category = '2';
            return $result_category;
        }
    }
    public static function indexing_category($id)
    {
        $indexing = array(
            'lotaya_horoscope',
            'lotaya_quiz',
            'lotaya_shortstory',
            'lotaya_funny'
        );

        $suggest_category = [];
        $raws = RawArticle::find($id);
        $contents = Content::where('article_id', $raws->id)->get();
        //search tags
        foreach ($contents as $content) {
            foreach ($indexing as $sample) {
                if (strstr($content->content_text, $sample)) {
                    $suggest_category[] = $sample;
                }
            }
        }
        foreach ($suggest_category as $category) {
            if ($category == "lotaya_horoscope") {
                return '1';
            } elseif ($category == "lotaya_quiz") {
                return '5';
            } elseif ($category == "lotaya_shortstory") {
                return '4';
            } elseif ($category == "lotaya_funny") {
                return '7';
            } else {
                return '1';
            }
        }
    }
    public static function indexing_tags($id)
    {
        //array
        $sample_tags = Tag::get();
        $arrays = [
            ['ဗေဒင်', 'အလုပ်အကိုင်', 'စီးပွားရေး', 'ယတြာ', 'ငွေကြေး', 'မေတ္တာရေး', 'အချစ်ရေး', 'အိမ်ထောင်ရေး', 'လှူ', 'ပြဿနာ', 'ဆုတောင်း', 'စိတ်ချမ်းသာ', 'အခွင့်အလမ်း', 'မကျန်းမမာ', 'ပတ်ဝန်းကျင်', 'သီတင်းကျွတ်', 'လပြည့်', 'ဘုရားစင်', 'အကြံအစည်', 'ရည်ရွယ်ချက်', 'မေတ္တာ', 'ငွေ'], //horoscope
            ['အလုပ်', 'လမ်းလျှောက်', 'နည်းလမ်း', 'အိပ်', 'ရေချိုး', 'အစားအစာ', 'ဘဝနေထိုင်မှု', 'အချစ်ရေး', 'ဖက်ရှင်', 'ကျန်းမာရေး', 'ခန္ဓာကိုယ်', 'အလေးချိန်', 'အထွေထွေ', 'ဗဟုသုတ', 'လေ့ကျင့်ခန်း'], //lifestyle
            ['K-Drama', 'K-POP', 'ပေါ်ပြူလာ', 'BLACKPINK', 'ကိုရီးယား', 'မော်ဒယ်', 'မင်းသမီး', 'မင်းသား', 'ဖက်ရှင်', 'ဇာတ်လမ်း', 'fashion', 'နှုတ်ခမ်းနီ', 'Netflix', 'drama', 'list', 'stress', 'အနုပညာ'], //entertainment
            ['ဝတ္ထုတိုများ', 'အလုပ်', 'လမ်းလျှောက်', 'အစားအစာ', 'ဘဝ', 'အချစ်ရေး', 'ကျန်းမာရေး', 'ခန္ဓာကိုယ်', 'အထွေထွေ', 'ဗဟုသုတ', 'ဇာတ်လမ်း', 'ရည်မှန်းချက်', 'စိတ်ဖိစီးမှု', 'ပေါင်းသင်းဆက်ဆံရေး', 'relationship', 'Relationship', 'ဆုံးဖြတ်ချက်', 'အမူအကျင့်', 'Virgo', 'ဆွေးနွေး', 'နည်းဗျူဟာ', 'လက္ခဏာ', 'အရပ်ရှည်', 'ပျားရည်', 'ဓာတ်ပုံ', 'သန့်ရှင်းရေး', 'အုန်းရည်', 'လမ်းခွဲ', 'လမ်းလျှောက်ခြင်း', 'စိုးရိမ်ပူပန်မှု', 'ဆန်ဆေးရည်', 'ဝဝကစ်ကစ်', 'စိတ်ဓာတ်ကြံ့ခိုင်', 'အောင်မြင်မှု', 'မောင်နှမ', 'ဓမ္မတာ', 'ချစ်သူ', 'အချစ်ရေးဆက်နွယ်မှု', 'ဘ၀', 'ခေါင်း', 'Pre-Wedding', 'Pose', 'အပေါင်းအသင်း', 'ရယ်စရာ', 'တစ်ယောက်ထဲ', 'ခံစားရ', 'ရန်ဖြစ်စကားများ', 'ဆဲဆိုခြင်း', 'အတွဲ', 'ကိုယ်ထိလက်ရောက်ပြုမူခြင်း', 'ဒေါသ', 'Bad Mood', 'လန်းဆန်းတက်ကြွ', 'တရားထိုင်ပေးခြင်း', 'social media', 'Social Media', 'ချစ်စရာကောင်း', 'ဆွယ်တာ', 'မိုက်', 'အထာကျ', 'ရည်းစား', 'best friend', 'သတိရ', 'ဝမ်းနည်း', 'relax', 'သီချင်း', 'စရိုက်', 'တဒင်္ဂ', 'စုံတွဲ', 'အမူအကျင့်', 'အကြောင်းအရာ', 'ပျားရည်ဆမ်းခရီး', 'လက်မ', 'လူစိမ်း', 'ပိတ်ရက်', 'သူစိမ်း', 'အပေါ်ထပ်', 'အမျိုးသား', 'အစစအရာရာ', 'လူအများစု', 'မိတ်ဆွေ', 'ဘေးနား', 'အခက်အခဲ', 'ပတ်ဝန်းကျင်',  'ဘေးစောင်း', 'ပုံစံ', 'ဦးထုပ်', 'တစ်ခြားလူ', 'ဒဏ္ဍာရီ', 'လိုအပ်ချက်', 'ကိုယ်ပိုင်', 'ရုပ်ရည်', 'အမှား', 'မိဘ', 'အပြုသဘော', 'ပန်းတိုင်', 'ရိုးသား', 'ဆွဲဆောင်', 'ယုံကြည်', 'မိန်းကလေး', 'focus', 'အရာ', 'စိတ်ဖိစီး', 'မိသားစု', 'လက်ဆောင်', 'ကြိုးစား', 'စိတ်အခြေအနေ', 'တွေ့ရှိချက်', 'ကောင်လေး', 'ကောင်မလေး', 'အကြည်ဓာတ်', 'ဝန်ထုပ်ဝန်ပိုး', 'ကိုယ်ခန္ဓာ', 'စိတ်ပိုင်းဆိုင်ရာ', 'အသက်', 'ပလက်ဖောင်း', 'နားလည်မှု', 'စိတ်ခွန်အား', 'အဖုအထစ်', 'ယောက်မ', 'လက်ထပ်', 'ဖိတ်စာ', 'အိမ်မက်', 'စင်္ကြာ', 'သမီးရည်းစား', 'သစ္စာဖောက်', 'အသစ်', 'လက်ပြတ်', 'အကျင့်', 'ကောက်ကြောင်း', 'အထာ', 'အချိန်', 'အနိုင်', 'သဝန်တို', 'အတွေ့', 'သားသမီး', 'ဘဏ္ဍာရေး', 'အထီးကျန်', 'အမှု', 'ထွက်ခွာ', 'လေ့လာ', 'lifestyle', 'ဘဝနေထိုင်မှု', 'မွေးနေ့', 'ပန်းစည်း', 'ဆွဲကြိုး', 'ဖန်စီ', 'အရူး', 'ကျန်ရစ်သူ', 'လောက', 'ခါးပတ်', 'အမှတ်တရ', 'မိုးတွင်း', 'အဝတ်အစား', 'မသိစိတ်', 'ကြံ့ခိုင်', 'စရိုက်လက္ခဏာ'], //short_story
            ['နှုတ်ခမ်းနီ', 'ပေါင်းသင်းဆက်ဆံရေး', 'အထွေထွေ', 'ဗဟုသုတ', 'မေးခွန်း'], //quiz
            ['ဆံပင်', 'နည်းလမ်းများ', 'သဘာဝ', 'နှုတ်ခမ်းနီ', 'မိတ်ကပ်', 'တင်ပါး', 'dress', 'လက်သည်း', 'ရေ', 'ကော်ဖီ', 'အမျိုးသမီး', 'ပေါင်းသင်းဆက်ဆံရေး', 'သံသယ', 'အသားအရေ', 'လမ်းခွဲ', 'မျက်ခုံးမွှေး', 'ခြေထောက်', 'သန့်ရှင်းရေး', 'သူငယ်ချင်း'], //beauty
            ['ဟာသ', 'funny'], //funny
            ['ကျန်းမာရေး', 'အလေ့အကျင့်', 'စား', 'အစားအစာ', 'အိပ်', 'ယောဂ', 'သောက်', 'အပူချိန်', 'လေ့ကျင့်ခန်း', 'အသားအရေ', 'သဘာဝ', 'ကောင်းကျိုး', 'အသား', 'ဝမ်း', 'လေ', 'ဆေး', 'ခေါင်း', 'ရေ', 'ဆံပင်'], //health
            ['အစားအသောက်', 'အဆီ', 'အသီးအရွက်', 'စားသောက်ဆိုက်', 'Thai', 'အစားအစာ', 'Bar', 'ရန်ကုန်', 'BBQ', 'Hot Pot', 'ဆိုင်', 'သဘာဝ', 'ကော်ဖီ', 'အိမ်', 'ကိတ်', 'ဝိတ်'], //food
            ['Google', 'vivo', 'apple', 'Binace', 'Xiaomi', 'Pad', 'Phone', 'Andriod', 'တရုတ်', 'စမတ်', 'Samsung', 'မားကတ်တင်း', 'အင်တာနက်', 'iPhone', 'Watch', 'Huawei', 'Mi', 'Tik Tok', 'Youtube'], //technology
            ['အားကစား', 'ဘောလုံး', 'ဇီဒန်း', 'မော်ရင်ဟို', 'ကစားသမား', 'Viber'], //sports
            ['Organic'], //agriculture
            ['indoor', 'plants', 'ခွေး', 'ဘာသာစကား', 'ကိုရီးယား'], //other
            ['ဖုန်း', 'နိုင်ငံ'], //regional
            ['Style', 'Theory', 'လုပ်ငန်း', 'ဒေါ်လာ', 'အလုပ်'] //international
        ];
        // dd($arrays);
        $raws = RawArticle::find($id);
        $category = $raws->category;
        // dd($category);
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
        $raws = RawArticle::find($id);
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
        arsort($count_tags); //sorting with value
        // dd($count_tags);
        $cou = 0;
        foreach ($count_tags as $key => $value) {
            if ($cou < 5) {
                // echo "$key($value)<br>";
                $result_tags[] = $key;
            }
            $cou++;
        }
        $result_key = [];
        foreach ($sample_tags as $tag) {
            foreach ($result_tags as $r_tag) {
                if (strstr($r_tag, $tag->nameMm)) {
                    $result_key[] = $tag->id;
                }
            }
        }
        // dd($result_key);
        return (array_slice($result_key, 0, 5));
        // dd($result_tags);
    }
    public static function suggest_website($id)
    {
        $websites = Website::get();
        $raws = RawArticle::find($id);
        foreach ($websites as $website) {
            // print_r($website->host);
            // dd($raws->source_link);
            if (strstr($raws->source_link, 'ictdirectory.com.mm')) {
                return '5';
            } elseif (strstr($raws->source_link, $website->host) !== FALSE) {
                // dd(strstr($raws->source_link, $website->host));

                return $website->id;
            } else {
                // echo "wrong";
            }
        }
    }
    public static function no_content($id)
    {
        $raws = RawArticle::find($id);
        $contents = Content::where('article_id', $raws->id)->get();
        foreach ($contents as $content) {
            $content_count = strlen($content->content_text);
        }
        if ($content_count < 10) {
            return "NO CONTENT HERE";
        }
        // return  $content_count;
    }
    public static function duplicate_with_title($id)
    {
        $raws = RawArticle::find($id);
        $title = $raws->title;
        $search_title = RawArticle::where([['title','LIKE','%'.$title.'%'], ['sent_status', '!=', '0']])->first();
        if ($search_title && $search_title->count() > 0) {
            return [$search_title->id,$search_title->title];
        }
        return [];
        // return $raw_articles_all_title;
    }
    public static function duplicate_with_content($id)
    {
        $array = [];
        $raws = RawArticle::find($id);
        $contents = Content::where('article_id', $raws->id)->whereNotNull('content_text')->get();
        // return $contents;
        foreach ($contents as $n_content) {
            $search_content = DB::table('contents')->where('content_text', 'LIKE', '%' . $n_content->content_text . '%')->get();
            if ($search_content->count() > 1) {
                $array[] =  $n_content->content_text;
            }
        }
        if (count($array) > 2) {
            return $array;
        }
    }
}
    //test category
    // public static function categorywith_title($id)
    // {
    //     $array_room = [
    //         ['horoscope', 'ဗေဒင်', '၇ရက်သားသမီး', 'ဟောစာတမ်း', 'ရာသီခွင်', 'သက်စေ့နံတူ', 'ယတြာ', 'အစီအရင်', 'အကြွင်းဖွား', 'ဂဏန်းဗေဒင်', 'လက္ခဏာ', 'တူရာသီဖွား', 'မေထုန်ရာသီဖွား', 'ကန်ရာသီဖွား', 'Virgo', 'covid'],
    //         [
    //             'lifestyle', 'စိတ်ဖိစီးမှု', 'အလုပ်အကိုင်', 'အ၀တ်အစား', 'ပိုက်ဆံအိတ်', 'ချစ်သူကောင်မလေး', 'ချစ်သူ', 'ကံဆိုး', 'အထာကျ', 'ဖက်ရှင်', 'လူငယ်', 'အသည်းကွဲ', 'သူငယ်ချင်း', 'နှစ်သိမ့်', 'အမိုက်စားဖက်ရှင်', 'အိပ်ပျော်', 'စိတ်သက်သာရာ', 'ဟင်းမချက်', 'လက်မတွဲသင့်', 'လူငယ်ကောင်မလေး', '90s', 'Inspired', 'sharing', 'အချစ်ရေး', 'chubby', 'pants', 'အဆိုပြုချက်များ', 'အဆိုမိန့်များ', 'clothing idea', 'အကြောင်းပြချက်', 'လူတွေဘာလို့', 'ချိန်းတွေ့', 'ကိုယ်ရည်ကိုယ်သွေး', 'မထိုက်တန်', 'ဓာတ်ပုံရိုက်', '၀ါသနာ', 'လက်တလော', 'ခေတ်စား', 'ပို့စ်ပေးနည်း',
    //             'အချစ်စိတ်', 'Relationship', 'ပြဿနာတွေ', '၀လာအောင်', 'သတိပြုမိ', 'အကျင့်ဆိုး', 'ရုန်းထွက်', 'အကောင်းဆုံးနည်းလမ်း', 'Platonic Soulmate', 'self-etsteem', 'သားသားမီမီး', 'မိဘတွေ', 'လက္ခဏာ', 'စကားလုံးများ', 'ကြံ့ခိုင်သန်စွမ်း', 'လေ့ကျင့်ခန်း', 'သိပ္ပံနည်းကျ',
    //             'လေ့လာချက်တွေအရ', 'စာဖတ်ဝါသနာအိုး', 'စူပါပါဝါ', 'အငိုသန်', 'လမ်းခွဲ', 'ပျော်ရွှင်နေသူ', 'နာကျင်နေ', 'အမျိူးသားတွေ', 'မတူညီတဲ့ကာလာ', 'လိုက်ဖက်', 'စတိုင်', 'အတုအယောင်', 'Stressတွေ', 'အသားအရေ', 'ဂရုစိုက်', 'လုပ်ဆောင်ေပးသင့်', 'စိုးရိမ်ပူပန်', 'အိပ်ခန်း', 'စမတ်ကျ', 'ဥာဏ်ရည်မြင့်မား', 'မမျှော်လင့်', 'ဘေဘီလေး',
    //             'ကူညီ', 'Friend With Benefit', 'အားသာချက်', 'သင့်ဘ၀', 'အဆိပ်အတောက်', 'သက်သေပြ', 'အချစ်ဆုံး', 'ဘ၀သင်ခန်းစာ', 'ထာ၀ရတည်မြဲ', 'ကလေးဘ၀', 'အမှတ်အသား', 'မိသားစု၀င်', 'Fashion Tips', 'Unfriend', 'အပေါင်းအသင်း',
    //             'သင့်ဘ၀', 'ဘေးကင်းလုံခြုံ', 'နေထိုင်', 'လူငယ်ကောင်လေး', 'ကလေးဆန်', 'လက်တွဲဖော်', 'အာရုံစိုက်မှု', 'ငယ်ဘ၀', 'မိခင်', 'ဖြတ်သန်းမှု', 'သရုပ်ဖော်', 'ပုံရိပ်များ', 'အမျိူးသမီး', 'LDRS', 'အမျိူးသား', 'စိတ်ညစ်', 'စုံတွဲ', 'ပြန်အဆင်ပြေ', 'ဂုဏ်သိက္ခာ', 'ကောင်းလေးပုံစံ', 'ရင်ခုန်', 'လှပကြော့ရှင်း', 'ပျိူမေ',
    //             'ရွေးချယ်စရာ', 'ပိုချစ်တယ်', 'self-care activities', 'အိမ်', 'သစ္စာဖောက်', 'ရည်းစား', 'လက်တုံ့ပြန်', 'အကျပ်အတည်း', 'ဆုံးဖြတ်ချက်', 'ရိုးရှင်းသော', 'မိုးရာသီ', 'ဆောင်းရာသီ', 'နွေရာသီ', 'အလှဆုံးပိုစ့်', 'နှိပ်နည်း', 'ဂျင်း၀မ်းဆက်', 'လုပ်ငန်းခွင်', 'ကျေးဇူးတင်သင့်', 'ကိုယ်ဘ၀',
    //             'ချစ်ကြိုက်ခြင်း', 'ဆွဲဆောင်မှု', 'ထူးဆန်း', 'Outfits Style', 'စိတ်ခံစားချက်', 'emotions', 'သင်ယူ', 'Toxic', 'ကြောင်သူတော်', 'လျှို့ဝှက်စွာ', 'ရက်စက်', 'နေထိုင်မှုပုံစံ', 'အလှအပဆိုင်ရာ', 'အခြေခံကျ', 'စိတ်ပိုင်းဆိုင်ရာ', 'အနုပညာ', 'ဆုံးဖြတ်ချက်ကောင်းများ', 'ရည်းစားဟောင်း', 'အခက်အခဲဆုံး', 'အခြေအနေ',
    //             'စွန်ပစ်', 'နှစ်သိမ့်', 'ကိုယ်၀န်ရှိနေနိုင်', 'Narccissists', 'ကာကွယ်', 'မိဘအုပ်ထိန်းမှု', 'အကြုံပြုချက်', 'အလုပ်လက်မဲ့', 'နည်းဗျူဟာ', 'ပျိူးထောင်', 'ထိန်းချူပ်ချင်စိတ်', 'သိသာစေ', 'ဘ၀', 'အရာသေးသေးလေး', 'တန်ဖိုးထား', 'ရှင်သန်ကြ', 'ဥာဏ်ကောင်း', 'ဆုံးရှုံး',
    //             'အင်မတန်ကြောက်', 'မချစ်ပေး', 'ကိုယ့်ကောင်မလေး', 'သန့်စင်ပေး', 'နည်းလမ်း', 'ခက်ခဲတဲ့ကာလ', 'လူချင်းတွေ့', 'အမေအမျိူးအစား', 'အမေ', 'အဖေ', 'အကို', 'ညီလေး', 'ညီမလေး', 'လူကြီးဘ၀', 'နောက်ဆက်တွဲဆိုးကျိူးများ', 'စိတ်ဓာတ်ကြံ့ခိုင်', 'မိန်းကလေးအများစု', 'အကြောင်းရင်း',
    //             'ကျေနပ်လောက်အောင်', 'ခံစား', 'မီးဖိုချောင်', 'လွယ်ကူရိုးရှင်းသော', 'နှစ်ယောက်အတူ', 'ကိုယ့်ကိုကိုယ်', 'မေးခွန်း', 'တည့်မှာမဟုတ်', 'မောင်နှမ', 'တွဲဖက်', 'ရေရှည်တည်မြဲ', 'ညဘက်', 'အိပ်မပျော်', 'lifestyle', 'Lifestyle', 'မင်္ဂလာပွဲ', 'မင်္ဂလာလက်ဖွဲ့', 'အိုင်ဒီယာ', 'သွက်လက်ထက်မြက်', 'ကိုယ်ပိုင် Goals',
    //             'မိခင်နှင့်သမီး', 'ပြောဆိုဆက်ဆံရေး', 'communication', 'အတွေးလွန်', 'ခါးသီးလွန်း', 'အမှန်တရား', 'ဂျပန်မိဘ', 'တရုတ်မိဘ', 'ထိုင်းမိဘ', 'အမေရိကားမိဘ', 'ရုရှားမိဘ', 'အစားဇီဇာကြောင်', 'နားလည်မှုမပေး', 'ပြီးပြည့်စုံ', 'လူငယ်ဆန်ဆန်', 'ဖဲကြိုး', 'ဖက်ရှင်ဒီဇိုင်း', 'မနက်ခင်း', 'ညနေခင်း', 'နေ့လယ်ခင်း',
    //             'ညနေခင်း', 'လန်းလန်းဆန်းဆန်း', 'ကြမ်းပိုး', 'ပုရွက်ဆိတ်', 'ခြင်', 'ယင်ကောင်', 'အလွယ်ကူဆုံး နည်းလမ်း', 'ကျိန်ဆဲတတ်', 'လေ့လာမှု', 'Self-Confidience', 'ဘယ်လိုလူမျိူး', 'စကားအများကြီး', 'မတောင်းပန်', 'ခွင့်လွှတ်', 'ဖန်တီးနိုင်စွမ်း', 'စိတ်နေသဘောထား', 'အဆင့်အတန်းမြင့်', 'ကတိတည်', 'ကြောက်ရွံ့', 'ဒိတ်လုပ်', 'ပန်းရနံ့', 'သင်းပျံ့',
    //             'Fragrant Indoor Plants', 'Single', 'ကိုယ့်ရည်းစား', 'တကယ်ချစ်', 'ယောကျာ်းလေး', 'ယောကျာ်းတစ်ယောက်', '၀ေး၀ေးရှောင်', 'အမျိုးသားတစ်ယောက်', 'စရိုက်လက္ခဏာအမှန်', 'ခြေရာခံ', 'နူးညံ့သော', 'သဲလွန်စ', 'အသေးစား ပြောင်းလဲမှု', 'ယုံကြည်မှု', 'Overall', 'တွဲ၀တ်',
    //             'ဦးစားပေး', 'ဘယ်လိုဆုံးမ', 'ဖိနပ်', 'FA', 'ထိရှလွယ်', 'ပင်ပန်း', 'ဂရုဏာ', 'self-pity', 'T-shirt', 'ပွင့်ပွင့်လင်းလင်း', 'Selfie', 'ကိုရီးယားမလေး', 'Nightout', 'ပိုစ့်ပေးနည်းများ', 'အနုပညာလှုပ်ရှားမှု'
    //         ]
    //     ];
    //     $raws = RawArticle::find($id);
    //     $title = $raws->title;
    //     // dd($title);
    //     $result_key = [];

    //     foreach ($array_room as $key => $value) {
    //         foreach ($value as $v) {
    //             if (stristr($title, $v) !== FALSE) {
    //                 print_r($v);
    //                 array_push($result_key, $key);
    //                 print_r($key);
    //                 break;
    //             }
    //         }
    //     }
    //     dd($result_key);
    //     // foreach ($sample_category as $key => $value) {
    //     //     // print_r($key);
    //     //     foreach ($value as $v) {
    //     //         if ($v == $string) {
    //     //             $result_category = $key;
    //     //         } else {
    //     //             // print_r('false');
    //     //         }
    //     //     }
    //     // }
    // }


    //suggest tags with (category_id)
    // public static function suggestTags($id)
    // {
    //     //array
    //     $arrays = [
    //         ['ဗေဒင်', 'အလုပ်အကိုင်', 'စီးပွားရေး', 'ယတြာ', 'ငွေကြေး', 'မေတ္တာရေး', 'အချစ်ရေး', 'အိမ်ထောင်ရေး', 'လှူ', 'ပြဿနာ', 'ဆုတောင်း', 'စိတ်ချမ်းသာ', 'အခွင့်အလမ်း', 'မကျန်းမမာ', 'ပတ်ဝန်းကျင်'], //horoscope
    //         ['အလုပ်', 'လမ်းလျှောက်', 'နည်းလမ်း', 'အိပ်', 'ရေချိုး', 'အစားအစာ', 'ဘဝနေထိုင်မှု', 'အချစ်ရေး', 'ဖက်ရှင်', 'ကျန်းမာရေး', 'ခန္ဓာကိုယ်', 'အလေးချိန်', 'အထွေထွေ', 'ဗဟုသုတ', 'လေ့ကျင့်ခန်း', 'ဇာတ်လမ်း'], //lifestyle
    //         ['K-Drama', 'K-POP', 'ပေါ်ပြူလာ', 'BLACKPINK', 'ကိုရီးယား', 'မော်ဒယ်', 'မင်းသမီး', 'မင်းသား', 'ဖက်ရှင်', 'ဇာတ်လမ်း', 'fashion', 'နှုတ်ခမ်းနီ', 'Netflix', 'drama', 'list', 'stress', 'အနုပညာ'], //entertainment
    //         ['ဝတ္ထုတိုများ'], //short_story
    //         ['နှုတ်ခမ်းနီ', 'ပေါင်းသင်းဆက်ဆံရေး', 'အထွေထွေ', 'ဗဟုသုတ', 'မေးခွန်း'], //quiz
    //         ['ဆံပင်', 'နည်းလမ်းများ', 'သဘာဝ', 'နှုတ်ခမ်းနီ', 'မိတ်ကပ်', 'တင်ပါး', 'dress', 'လက်သည်း', 'ရေ', 'ကော်ဖီ', 'အမျိုးသမီး', 'ပေါင်းသင်းဆက်ဆံရေး', 'သံသယ', 'အသားအရေ', 'လမ်းခွဲ', 'မျက်ခုံးမွှေး', 'ခြေထောက်', 'သန့်ရှင်းရေး', 'သူငယ်ချင်း'], //beauty
    //         ['ဟာသ', 'funny'], //funny
    //         ['ကျန်းမာရေး', 'အလေ့အကျင့်', 'စား', 'အစားအစာ', 'အိပ်', 'ယောဂ', 'သောက်', 'အပူချိန်', 'လေ့ကျင့်ခန်း', 'အသားအရေ', 'သဘာဝ', 'ကောင်းကျိုး', 'အသား', 'ဝမ်း', 'လေ', 'ဆေး', 'ခေါင်း', 'ရေ', 'ဆံပင်'], //health
    //         ['အစားအသောက်', 'အဆီ', 'အသီးအရွက်', 'စားသောက်ဆိုက်', 'Thai', 'အစားအစာ', 'Bar', 'ရန်ကုန်', 'BBQ', 'Hot Pot', 'ဆိုင်', 'သဘာဝ', 'ကော်ဖီ', 'အိမ်', 'ကိတ်', 'ဝိတ်'], //food
    //         ['Google', 'vivo', 'apple', 'Binace', 'Xiaomi', 'Pad', 'Phone', 'Andriod', 'တရုတ်', 'စမတ်', 'Samsung', 'မားကတ်တင်း', 'အင်တာနက်', 'iPhone', 'Watch', 'Huawei', 'Mi', 'Tik Tok', 'Youtube'], //technology
    //         ['အားကစား', 'ဘောလုံး', 'ဇီဒန်း', 'မော်ရင်ဟို', 'ကစားသမား'], //sports
    //         ['Organic'], //agriculture
    //         ['indoor', 'plants', 'ခွေး', 'ဘာသာစကား', 'ကိုရီးယား'], //other
    //         ['ဖုန်း', 'နိုင်ငံ'], //regional
    //         ['Style', 'Theory', 'လုပ်ငန်း', 'ဒေါ်လာ', 'အလုပ်'] //international
    //     ];
    //     // dd($arrays);
    //     $raws = RawArticle::find($id);
    //     $category = $raws->category;
    //     $result = [];
    //     $suggest_tags = [];
    //     $count = 0;
    //     foreach ($arrays as $array) {
    //         // echo $count;
    //         $count++;
    //         if ($category->id  == $count) {
    //             // print_r($array);
    //             $result = $array;
    //         }
    //     }
    //     // dd($result);
    //     $raws = RawArticle::find($id);
    //     $contents = Content::where('article_id', $raws->id)->get();

    //     foreach ($contents as $content) {
    //         //condition sit yan kyan(with category)
    //         foreach ($result as $sample) {

    //             if (strstr($content->content_text, $sample)) {
    //                 $suggest_tags[] = $sample;
    //             }
    //         }
    //     }
    //     $count_tags = array_count_values($suggest_tags);

    //     if (sizeof($count_tags) > 5) {
    //         return array_slice($count_tags, 0, 5);
    //     } else
    //         return $count_tags;


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
    // }
    //$sample_tags = array(
        //     'ဗေဒင်', 'အလုပ်အကိုင်', 'စီးပွားရေး', 'ယတြာ', 'ငွေကြေး', 'မေတ္တာရေး', 'အချစ်ရေး', 'အိမ်ထောင်ရေး', 'လှူ', 'ပြဿနာ', 'ဆုတောင်း', 'စိတ်ချမ်းသာ', 'အခွင့်အလမ်း', 'မကျန်းမမာ', 'ပတ်ဝန်းကျင်', 'အိုးအိမ်', 'အလုပ်', 'ရာသီခွင်', 'စက်တင်ဘာ', 'ဟောကိန်း', 'ကံကောင်းခြင်း', 'ကတ်ကလေး', 'သြဂုတ်', 'ဇူလိုင်', 'ဇွန်', 'ဧပြီ', 'မတ်', 'ဖေဖော်ဝါရီ', 'ဇန်နဝါရီ', 'ဒီဇင်ဘာ', 'နိုဝင်ဘာ', 'အောက်တိုဘာ', //horoscope
        //     'အလုပ်', 'လမ်းလျှောက်', 'နည်းလမ်း', 'အိပ်', 'ရေချိုး', 'အစားအစာ', 'ဘဝနေထိုင်မှု', 'အချစ်ရေး', 'ကျန်းမာရေး', 'ခန္ဓာကိုယ်', 'အလေးချိန်', 'အထွေထွေ', 'ဗဟုသုတ', 'လေ့ကျင့်ခန်း', 'ဇာတ်လမ်း', 'ခါး', 'ရည်မှန်းချက်', 'ဆိုးကျိုး', 'စိတ်ဖိစီးမှု', 'ပေါင်းသင်းဆက်ဆံရေး', 'relationship', 'Relationship', 'ဆုံးဖြတ်ချက်', 'အမူအကျင့်', 'Virgo', 'ဆွေးနွေး', 'နည်းဗျူဟာ', 'လက္ခဏာ', 'fashion', 'ဖက်ရှင်', 'အရပ်ရှည်', 'ကိုယ်အလေးချိန်', 'ပျားရည်', 'ဓာတ်ပုံ', 'သန့်ရှင်းရေး', 'အုန်းရည်', 'လမ်းခွဲ', 'ဘာသာစကား', 'ခွေး', 'ကိုယ်ဝန်ဆောင်', 'လမ်းလျှောက်ခြင်း', 'စိုးရိမ်ပူပန်မှု', 'ဆန်ဆေးရည်', 'ကလေး', 'အမေ', 'ဝဝကစ်ကစ်', 'စိတ်ဓာတ်ကြံ့ခိုင်', 'အောင်မြင်မှု', 'မောင်နှမ', 'ဓမ္မတာ', 'ချစ်သူ', 'အချစ်ရေးဆက်နွယ်မှု', 'ဘ၀', //lifestyle
        //     'K-Drama', 'K-POP', 'ပေါ်ပြူလာ', 'BLACKPINK', 'ကိုရီးယား', 'မော်ဒယ်', 'မင်းသမီး', 'မင်းသား', 'ဇာတ်လမ်း', 'Netflix', 'drama', 'list', 'stress', 'အနုပညာ', 'သရုပ်ဆောင်', 'နာမည်ကျော်', 'famous', 'Photography', 'photography', 'ထိုင်း', 'သရဲဇာတ်လမ်း', 'အလုပ်နားရက်', 'ပရိတ်သတ်', //entertainment
        //     'ဝတ္ထုတိုများ', 'အယ်လ်ကာပုန်း', 'ခံစားချက်', 'တစ်ပွင့်စိန်', 'လူမိုက်', 'စိန်', 'အဘကျော်', //short_story
        //     'ပေါင်းသင်းဆက်ဆံရေး', 'အထွေထွေ', 'ဗဟုသုတ', 'မေးခွန်း', 'question', 'စမ်းသပ်ချက်', 'သိပ္ပံပညာရပ်', 'ယာဉ်မောင်း', 'ကား', 'ဂိမ်း', //quiz
        //     'ဆံပင်', 'နည်းလမ်းများ', 'သဘာဝ', 'နှုတ်ခမ်းနီ', 'မိတ်ကပ်', 'တင်ပါး', 'dress', 'လက်သည်း', 'ကော်ဖီ', 'အမျိုးသမီး', 'ပေါင်းသင်းဆက်ဆံရေး', 'သံသယ', 'အသားအရေ', 'လမ်းခွဲ', 'မျက်ခုံးမွှေး', 'ခြေထောက်', 'သန့်ရှင်းရေး', 'သူငယ်ချင်း', 'oil scrub', 'salt scrub', 'sugar scrub', 'body scrub', 'ဖျက်ဆေး', 'နုပျို', 'မျက်နှာ', 'စကပ်', 'လက်သည်းနီ', 'ရေမွှေး', 'အညိုရောင်', 'ခရမ်းရောင်', 'ခြေသည်း', 'စတိုင်', 'တက်တူး', 'နှုတ်ခမ်း', 'မျက်လုံး', 'eye', 'အကြံပြုချက်', 'လက်စွပ်', 'ရေနွေးငွေ့', 'ဉာဏ်ကောင်း', 'အိတ်', 'EyeShadow', 'မီးခိုးရောင်', 'အညိုရောင်', 'ဓာတ်ပုံ', 'photo', 'အရေပြား', 'သွားတိုက်ဆေး', 'အပြာရောင်', 'အလှအပရေးရာ', 'dress', 'crop top', 'အဆီဖု', 'ဘောင်းဘီ', 'အပြုံး', //beauty
        //     'ဟာသ', 'funny', 'စိန်မောင်ရွှီး', 'ဖိုးတေ', 'ကာတွန်း', //funny
        //     'ကျန်းမာရေး', 'အလေ့အကျင့်', 'စား', 'အစားအစာ', 'အိပ်', 'ယောဂ', 'သောက်', 'အပူချိန်', 'လေ့ကျင့်ခန်း', 'သဘာဝ', 'ကောင်းကျိုး', 'အသား', 'ဝမ်း', 'ဆေး', 'ခေါင်း', 'ဆံပင်', 'လေထိုးလေအောင့်', 'ချောင်းဆိုး', 'သွား', 'အဆုတ်', 'ဒဏ်ရာ', 'ထောပတ်သီး', 'စတော်ဘယ်ရီသီး', 'လမ်းလျှောက်ခြင်း', 'လိမ္မော်သီး', 'ကြက်သား', //health
        //     'အစားအသောက်', 'အဆီ', 'အသီးအရွက်', 'စားသောက်ဆိုက်', 'Thai', 'အစားအစာ', 'Bar', 'ရန်ကုန်', 'BBQ', 'Hot Pot', 'ဆိုင်', 'ကော်ဖီ', 'အိမ်', 'ကိတ်', 'ဝိတ်', //food
        //     'Google', 'vivo', 'apple', 'Binace', 'Xiaomi', 'Pad', 'Phone', 'Andriod', 'andriod', 'phone', 'တရုတ်', 'စမတ်', 'Samsung', 'မားကတ်တင်း', 'အင်တာနက်', 'iPhone', 'Watch', 'Huawei', 'Mi', 'Tik Tok', 'Youtube', 'အွန်လိုင်း', 'ဒစ်ဂျစ်တယ်', 'ဓါတ်ပုံ', 'ဘက်ထရီ', 'နည်းပညာ', 'Viber', 'Facebook', 'facebook', 'ကင်မရာ', 'camera', 'အင်တာနက်', 'internet', 'ကေဘယ်', 'cable', 'မိုက်ခရိုဖုန်း', 'microphone', 'စမတ်ဖုန်း', 'ဖုန်း', 'Amazon', 'memory', //technology
        //     'အားကစား', 'ဘောလုံး', 'ဇီဒန်း', 'မော်ရင်ဟို', 'ကစားသမား', //sports
        //     'Organic', //agriculture
        //     'indoor', 'plants', 'လှောင်အိမ်', 'တိရစ္ဆာန်', 'မြစ်', 'သမုဒ္ဒရာ', //other
        //     'ဖုန်း', 'နိုင်ငံ', 'တက္ကသိုလ်', 'စာမေးပွဲ', 'အောင်စာရင်း', 'မာတိကာ', //regional
        //     'Style', 'Theory', 'လုပ်ငန်း', 'ဒေါ်လာ', 'အလုပ်' //international
        // );
