<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Podcast;

class BaseApiController extends Controller
{
    /**
     * Authorize podcast access for the given user
     *
     * @param Request $request
     * @param Podcast $podcast
     * @return void
     */
    protected function authorizePodcast(Request $request, Podcast $podcast)
    {
        if ($request->user()->id !== $podcast->user_id && $request->user()->role !== 'Administrateur') {
            abort(403, 'Non autorisé');
        }
    }
}