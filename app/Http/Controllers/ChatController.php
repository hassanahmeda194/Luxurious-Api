<?php

namespace App\Http\Controllers;

use App\ApiResponse;
use App\Events\MessageSent;
use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;


class ChatController extends Controller
{
    use ApiResponse;

    public function getConversations($userId)
    {
        try {
            $conversations = Conversation::where('vendor_id', $userId)
                ->orWhere('customer_id', $userId)
                ->with(['vendor', 'customer', 'messages'])
                ->latest()
                ->get();
            return $this->success([
                'message' => "All Conversation Retrieved",
                'data' => $conversations,
            ], statusCode: 200);
        } catch (\Throwable $th) {
            return $this->error("An error occurred while retrieving conversation: " . $th->getMessage(), 500);
        }
    }

    public function getMessages($conversationId)
    {
        try {
            $messages = Message::where('conversation_id', $conversationId)
                ->with('sender')
                ->orderBy('created_at', 'asc')
                ->get();

            return $this->success([
                'message' => "Message Retrieved successfully!",
                'data' => $messages,
            ], statusCode: 200);
        } catch (\Throwable $th) {
            return $this->error("Failed to retrieved message: ' . $th->getMessage()", 500);
        }
    }

    public function sendMessage(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'conversation_id' => ['required', 'exists:conversations,id'],
            'sender_id' => ['required', 'exists:users,id'],
            'message' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return $this->validationError($validator->errors());
        }

        DB::beginTransaction();

        try {
            $message = Message::create([
                'conversation_id' => $request->conversation_id,
                'sender_id' => $request->sender_id,
                'message' => $request->message,
            ]);
            broadcast(new MessageSent($message))->toOthers();
            $conversation = Conversation::find($request->conversation_id);
            $conversation->last_message = $request->message;
            $conversation->last_message_time = now();
            $conversation->save();
            DB::commit();

            return $this->success([
                'message' => 'Message sent successfully.',
                'data' => $message,
            ], 200);
        } catch (\Throwable $th) {
            DB::rollBack();
            return $this->error("Failed to send message: ' . $th->getMessage()", 500);
        }
    }
}
