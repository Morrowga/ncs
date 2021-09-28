<?php

use App\Models\Settings\Category;
use Illuminate\Database\Seeder;

class CategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $data = [
            [
                'en' => 'Horoscope',
                'mm' => 'ဗေဒင်'
            ],
            [
                'en' => 'Lifestyle',
                'mm' => 'နေ့စဥ်ဘဝ'
            ],
            [
                'en' => 'Entertainment',
                'mm' => 'ဖျော်ဖြေရေး'
            ],
            [
                'en' => 'Short Stories',
                'mm' => 'ဝတ္တုတိုများ'
            ],
            [
                'en' => 'Quiz',
                'mm' => 'ပဟေဠိ'
            ],
            [
                'en' => 'Beauty',
                'mm' => 'အလှအပရေးရာ'
            ],
            [
                'en' => 'Funny',
                'mm' => 'ဟာသ'
            ],
            [
                'en' => 'Health',
                'mm' => 'ကျန်းမာရေး'
            ],
            [
                'en' => 'Food',
                'mm' => 'အစားအသောက်'
            ],
            [
                'en' => 'Technology',
                'mm' => 'နည်းပညာ'
            ],
            [
                'en' => 'Sport',
                'mm' => 'အားကစား'
            ],
            [
                'en' => 'Agriculture',
                'mm' => 'စိုက်ပျိုးရေး'
            ],
            [
                'en' => 'Others',
                'mm' => 'အခြား'
            ],
            [
                'en' => 'Regional News',
                'mm' => 'ပြည်တွင်း'
            ],
            [
                'en' => 'International News',
                'mm' => 'နိုင်ငံတကာ'
            ]
        ];
        foreach ($data as $datum) {
            $new = new Category;
            $new->name = Str::lower($datum['en']);
            $new->name_mm = $datum['mm'];
            $new->save();
        }
    }
}
