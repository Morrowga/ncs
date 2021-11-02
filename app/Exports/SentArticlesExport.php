<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use App\Models\Articles\RawArticle;
use Carbon\Carbon;

class SentArticlesExport implements FromCollection
{
    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        //monthly
        $month = 11;
        $monthly_report = RawArticle::with('category', 'website')
            ->where('status', 1)
            ->whereMonth('published_date', $month)
            ->get();
        //data input
        $report_array[] = array('UUID', 'Host', 'Myanmar Time', 'Myanmar Date', 'Title', 'Original Category', 'Lotaya Category', 'Reject', 'Reject Reason');
        foreach ($monthly_report as $data) {
            // dd($data);
            $report_array[] = array(
                'UUID' => $data->uuid,
                'Host' => $data->website->host,
                'Myanmar Time' => $data->publisheDate,
                'Myanmar Date' => date($data->publishedDate),
                'Title' => $data->title,
                'Original Category' => $data->website->providerCategory,
                'Lotaya Category' => $data->category->name,
                'Reject' => 'FALSE',
                'Reject Reason' => null,
            );
        }
        // dd($report_array);
        return collect($report_array);
    }
}
