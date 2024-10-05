<?php

namespace Database\Seeders;

use App\Models\Conversation;
use App\Models\Message;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class ChatSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Conversation::create([
            'vendor_id' => 1,
            'customer_id' => 2,
            'last_message' => 'Hello, how can I help you?',
            'last_message_time' => now(),
        ]);

        Conversation::create([
            'vendor_id' => 2,
            'customer_id' => 1,
            'last_message' => 'I have a question about my order.',
            'last_message_time' => now(),
        ]);

        Message::create([
            'conversation_id' => 1,
            'sender_id' => 1,
            'message' => 'Hello, how can I help you?',
        ]);

        Message::create([
            'conversation_id' => 1,
            'sender_id' => 2,
            'message' => 'I need assistance with my account.',
        ]);

        // Creating dummy messages for conversation 2
        Message::create([
            'conversation_id' => 2,
            'sender_id' => 2,
            'message' => 'I have a question about my order.',
        ]);

        Message::create([
            'conversation_id' => 2,
            'sender_id' => 1,
            'message' => 'Sure, what would you like to know?',
        ]);
    }
}
