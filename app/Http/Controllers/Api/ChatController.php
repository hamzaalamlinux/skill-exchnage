<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Chat;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Kreait\Firebase\Database as FirebaseDatabase;
use Kreait\Firebase\Exception\FirebaseException;
use Illuminate\Support\Str;

class ChatController extends Controller
{
    protected FirebaseDatabase $database;

    public function __construct(FirebaseDatabase $database)
    {
        $this->database = $database;
    }

    /**
     * Send a message: save to MySQL and push to Firebase realtime DB
     */
    public function sendMessage(Request $request)
    {
        $request->validate([
            'receiver_id' => 'required|exists:users,id',
            'message' => 'required|string'
        ]);

        $senderId = Auth::id();
        $receiverId = (int)$request->receiver_id;
        $messageText = $request->message;

        // Save to local DB (primary persistence)
        $chat = Chat::create([
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $messageText,
            'is_read' => false,
        ]);

        // Build a deterministic conversation id (e.g., smallerID_largerID)
        $conversationId = $this->conversationId($senderId, $receiverId);

        // Prepare payload for Firebase
        $payload = [
            'sender_id' => $senderId,
            'receiver_id' => $receiverId,
            'message' => $messageText,
            'created_at' => now()->toDateTimeString(),
            'timestamp' => now()->timestamp,
            'local_chat_id' => $chat->id,
        ];

        try {
            // Push to firebase under chats/{conversationId}/messages
            $ref = $this->database->getReference("chats/{$conversationId}/messages");
            $newPostRef = $ref->push($payload);
            $firebaseKey = $newPostRef->getKey();

            // Save firebase key into local DB if required
            $chat->firebase_key = $firebaseKey;
            $chat->save();

        } catch (FirebaseException $e) {
            // Log error but keep DB message (you might want to retry or notify)
            \Log::error('Firebase push error: '.$e->getMessage());
            // Optionally return partial success
            return response()->json([
                'status' => 'partial',
                'message' => 'Message saved locally but failed to push to Firebase.',
                'data' => $chat
            ], 201);
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Message sent',
            'data' => $chat
        ], 201);
    }

    /**
     * Fetch messages between auth user and another user (from local DB)
     */
    public function getMessages(Request $request, $otherUserId)
    {
        $userId = Auth::id();
        $otherUserId = (int)$otherUserId;

        // You can choose pagination here
        $messages = Chat::where(function($q) use ($userId, $otherUserId) {
                $q->where('sender_id', $userId)->where('receiver_id', $otherUserId);
            })
            ->orWhere(function($q) use ($userId, $otherUserId) {
                $q->where('sender_id', $otherUserId)->where('receiver_id', $userId);
            })
            ->orderBy('created_at', 'asc')
            ->get();

        return response()->json($messages);
    }

    /**
     * (Optional) Get messages directly from Firebase realtime DB for a conversation
     */
    public function getMessagesFromFirebase($otherUserId)
    {
        $userId = Auth::id();
        $conversationId = $this->conversationId($userId, (int)$otherUserId);

        try {
            $ref = $this->database->getReference("chats/{$conversationId}/messages");
            $value = $ref->getValue(); // returns associative array or null
            return response()->json($value ?: []);
        } catch (FirebaseException $e) {
            \Log::error('Firebase read error: '.$e->getMessage());
            return response()->json([], 500);
        }
    }

    /**
     * Build deterministic conversation id for two user ids
     * Ensures both client & server use same path.
     */
    protected function conversationId(int $a, int $b): string
    {
        $ids = [$a, $b];
        sort($ids, SORT_NUMERIC);
        return $ids[0] . '_' . $ids[1];
    }
}
