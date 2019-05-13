<?php

namespace App\Http\Resources\Course;

use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Lesson;

class CourseCollection extends JsonResource
{
    /**
     * Transform the resource collection into an array.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return array
     */
    public function toArray($request)
    {
        $lesson_category = Lesson::find($this->lesson_id)->lesson_category;

        return [
            'title' => $this->title,
            'cover' => $this->cover,
            'lesson_category' => $lesson_category,
            'href' => [
                'forums' => 'unlinked',
                'reviews' => 'unlinked',
            ],
            'rating' => 'rated by reviews',
        ];
    }
}
