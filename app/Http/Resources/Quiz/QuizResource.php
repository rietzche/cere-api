<?php

namespace App\Http\Resources\Quiz;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\QuestionQuiz;

class QuizResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $question = QuestionQuiz::where('quiz_id',$this->id)->get();
        return [
            'id' => $this->id,
            'title' => $this->title,
            'section_id' => $this->section_id,
            'last_seen' => (!is_null($this->lastSeen()->where('user_id', $request->user()->id)->first()) ? $this->lastSeen()->where('user_id', $request->user()->id)->first()->updated_at->diffForHumans() : null),
            'question' => $question
        ];
    }
}
