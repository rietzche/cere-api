<?php

namespace App\Http\Controllers\Cerevids;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Services\Cerevids\TextService;
use App\Http\Resources\Text\TextResource;
use App\Models\LastSeen;

class TextController extends Controller
{
    public function __construct()
    {
        $this->text = new TextService;
    }

    public function index($section_id)
    {
        $texts = $this->text->browse($section_id);

        return TextResource::collection($texts);
    }

    public function create($section_id, Request $req)
    {
        $result = $this->text->create([
            'section_id' => $section_id,
            'title' => $req->title,
            'content' => $req->content
        ]);

        return (new TextResource($result))->additional([
            'status' => true,
            'message' => 'Succesfully add favorite'
        ]);
    }

    public function lastSeen($id, $user_id)
    {
        $lastSeen = LastSeen::where('text_id', $id)->where('user_id', $user_id)->first();
        if (!is_null($lastSeen)) {
            $lastSeen->touch();
        }
        else {
            LastSeen::create([
                'text_id' => $id,
                'user_id' => $user_id
            ]);
        }

        return response()->json([
            'status' => true
        ]);
    }

    public function find($section_id, $text_id, Request $req)
    {
        $text = $this->text->find($text_id);
        $this->lastSeen($text_id, $req->user()->id);

        return new TextResource($text);
    }

    public function update($section_id, $text_id, Request $req)
    {
        $result = $this->text->update($text_id, [
            'title' => $req->title,
            'content' => $req->content
        ]);

        return response()->json([
            'status' => true,
            'message' => 'Successfully update '.$result->title
        ]);;
    }

    public function delete($section_id, $text_id)
    {
        $result = $this->text->destroy($text_id);

        return response()->json([
            'status' => true,
            'message' => 'Successfully delete section'
        ]);;
    }
}
