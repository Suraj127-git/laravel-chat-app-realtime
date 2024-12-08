<?php

namespace App\Http\Controllers;

use App\Events\MessageSent;
use App\Models\Message;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MessageController extends Controller
{
    public function index()
    {
        try {
            // Fetch all users except the currently authenticated user
            $users = User::where('id', '!=', Auth::id())->get();
            Log::info('Fetched user list for chat', ['user_id' => Auth::id(), 'user_count' => $users->count()]);

            return view('chat.index', compact('users'));
        } catch (\Exception $e) {
            Log::error('Error fetching users for chat', [
                'user_id' => Auth::id(),
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to fetch users'], 500);
        }
    }

    public function getMessages(User $user)
    {
        try {
            // Efficient query to fetch messages between the authenticated user and the selected user
            $messages = Message::where(function ($query) use ($user) {
                $query->where('sender_id', Auth::id())
                    ->where('receiver_id', $user->id);
            })
                ->orWhere(function ($query) use ($user) {
                    $query->where('sender_id', $user->id)
                        ->where('receiver_id', Auth::id());
                })
                ->orderBy('created_at')
                ->get();

            Log::info('Fetched messages between users', [
                'sender_id' => Auth::id(),
                'receiver_id' => $user->id,
                'message_count' => $messages->count(),
            ]);

            return response()->json($messages);
        } catch (QueryException $e) {
            Log::error('Database error fetching messages', [
                'user_id' => Auth::id(),
                'receiver_id' => $user->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to fetch messages'], 500);
        } catch (\Exception $e) {
            Log::error('Error fetching messages', [
                'user_id' => Auth::id(),
                'receiver_id' => $user->id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to fetch messages'], 500);
        }
    }

    public function sendMessage(Request $request)
    {
        try {
            // Validate incoming data
            $validatedData = $request->validate([
                'receiver_id' => 'required|exists:users,id',
                'content' => 'required|string|max:1000',
            ]);

            // Create the new message
            $message = Message::create([
                'sender_id' => Auth::id(),
                'receiver_id' => $validatedData['receiver_id'],
                'content' => $validatedData['content'],
            ]);

            // Log the message creation
            Log::info('Message sent', [
                'sender_id' => Auth::id(),
                'receiver_id' => $validatedData['receiver_id'],
                'message_id' => $message->id,
                'message_content' => $message->content,
            ]);

            // Broadcast the message via Pusher
            broadcast(new MessageSent($message))->toOthers();
            Log::info('Message broadcasted via Pusher', ['message_id' => $message->id]);

            return response()->json($message);
        } catch (QueryException $e) {
            Log::error('Database error while sending message', [
                'user_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to send message'], 500);
        } catch (\Exception $e) {
            Log::error('Error sending message', [
                'user_id' => Auth::id(),
                'receiver_id' => $request->receiver_id,
                'error_message' => $e->getMessage(),
                'error_trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Failed to send message'], 500);
        }
    }
}
