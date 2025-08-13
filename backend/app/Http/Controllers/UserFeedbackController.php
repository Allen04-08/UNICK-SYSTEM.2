<?php

namespace App\Http\Controllers;

use App\Http\Requests\FeedbackRequest;
use App\Http\Resources\FeedbackResource;
use App\Models\UserFeedback;
use Illuminate\Http\Request;

class UserFeedbackController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', UserFeedback::class);
        $feedback = UserFeedback::with('user','order')->latest()->paginate(20);
        return FeedbackResource::collection($feedback);
    }

    public function store(FeedbackRequest $request)
    {
        $this->authorize('create', UserFeedback::class);
        $data = $request->validated();
        $data['user_id'] = $request->user()->id;
        $feedback = UserFeedback::create($data);
        return new FeedbackResource($feedback->load('user','order'));
    }

    public function show(UserFeedback $feedback)
    {
        $this->authorize('view', $feedback);
        return new FeedbackResource($feedback->load('user','order'));
    }
}
