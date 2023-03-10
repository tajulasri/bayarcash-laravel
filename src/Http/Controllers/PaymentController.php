<?php

namespace Webimpian\BayarcashLaravel\Http\Controllers;

use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;
use Webimpian\BayarcashLaravel\Http\Requests\PaymentRequest;

class PaymentController
{
    /**
     * @param \Webimpian\BayarcashLaravel\Http\Requests\PaymentRequest $request
     * @return string|void
     */
    public function init(PaymentRequest $request)
    {
        $api_url = config('bayarcash-laravel.fpx_transaction_url');

        $data = $request->validated() + ['portal_key' => config('bayarcash-laravel.portal_key')];

        return view('bayarcash-laravel::init-payment', [
            'data'    => $data,
            'api_url' => $api_url
        ]);
    }

    /**
     * @return int[]
     * @throws \Illuminate\Validation\ValidationException
     * @throws \Exception
     */
    public function requery(Request $request)
    {
        $api_url = config('bayarcash-laravel.requery_transaction_url');

        $response = Http::withOptions([
            'verify' => !app()->environment('local'),
        ])
            ->withToken(config('bayarcash-laravel.bearer_token'))
            ->acceptJson()
            ->post($api_url, [
                'RefNo' => $request->RefNo,
            ]);

        $resultBody   = $response->json();

        if ($response->failed()) {
            throw new Exception($response->reason());
        }

        if (!$resultBody) {
            throw new Exception('Response Error');
        }

        $transactionsList = $resultBody['output']['transactionsList'];

        return $transactionsList['recordsListTotalRecordCount'] > 0
            ? $transactionsList['recordsListData'][0]
            : [];
    }
}