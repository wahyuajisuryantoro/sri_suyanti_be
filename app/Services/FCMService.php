<?php

namespace App\Services;

use Kreait\Firebase\Messaging\CloudMessage;
use Kreait\Firebase\Messaging\Notification;
use Kreait\Laravel\Firebase\Facades\Firebase;

class FCMService
{
    public function sendNotification($token, $title, $body, $data = [])
    {
        $messaging = Firebase::messaging();

        $notification = Notification::create($title, $body);

        $message = CloudMessage::withTarget('token', $token)
            ->withNotification($notification)
            ->withData($data);

        return $messaging->send($message);
    }

    public function sendMultiNotification($tokens, $title, $body, $data = [])
    {
        if (empty($tokens)) {
            \Log::warning('No FCM tokens provided');
            return null;
        }

        \Log::info('FCMService: Starting sendMultiNotification', [
            'tokens_count' => count($tokens),
            'title' => $title,
            'tokens' => array_map(fn($token) => substr($token, 0, 20) . '...', $tokens)
        ]);

        try {
            $messaging = Firebase::messaging();
            $notification = Notification::create($title, $body);
            $messages = [];

            foreach ($tokens as $token) {
                $messages[] = CloudMessage::withTarget('token', $token)
                    ->withNotification($notification)
                    ->withData($data);
            }

            \Log::info('FCMService: Messages prepared', [
                'messages_count' => count($messages)
            ]);

            $result = $messaging->sendAll($messages);

            \Log::info('FCMService: Send completed', [
                'success_count' => $result->successes()->count(),
                'failure_count' => $result->failures()->count()
            ]);

            // Log failures if any
            if ($result->hasFailures()) {
                foreach ($result->failures() as $failure) {
                    \Log::error('FCM message failed', [
                        'token' => substr($failure->target()->value(), 0, 20) . '...',
                        'error' => $failure->error()->getMessage()
                    ]);
                }
            }

            return $result;

        } catch (\Exception $e) {
            \Log::error('FCMService: Exception occurred', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            throw $e;
        }
    }
}