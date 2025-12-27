<?php

namespace App\Http\Controllers\Api\App;

use App\Http\Resources\FaqsResource;
use App\Models\Faqs;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class FaqController extends Controller
{
    public function getFaqs()
    {
        try {
            $faqs = Faqs::where('active', 1)->where('account_id', 1)->get();

            $faqsids = array();
            $data = array();

            if ($faqs) {
                foreach ($faqs as $faq) {
                    if (!in_array($faq->category_id, $faqsids)) {
                        $data[$faq->category_id] = array(
                            'category' => $faq->category->name,
                            'record' => array(),
                        );
                        $faqsids[] = $faq->category_id;
                    }
                    $data[$faq->category_id]['record'][] = array(
                        'question' => $faq->question,
                        'answer' => $faq->answer,
                    );

                }
            }
            if (count($faqs) > 0) {
                return response()->json([
                    'status' => true,
                    'message' => 'Faqs information successfully given',
                    'faqs' => array_values($data),
                    'status_code' => 200,
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'message' => 'Data not exists',
                    'faqs' => [],
                    'status_code' => 204,
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => false,
                'message' => $e->getMessage(),
                'status_code' => 402,
            ]);
        }

    }
}
