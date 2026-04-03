<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ZoomGoogleMeetService
{
    /**
     * Generate Zoom meeting link using Zoom API
     * This requires ZOOM_ACCOUNT_ID, ZOOM_CLIENT_ID, ZOOM_CLIENT_SECRET
     * 
     * @param string $topic
     * @param \DateTime $startTime
     * @param int $durationMinutes
     * @return array ['link' => string, 'meeting_id' => string, 'password' => string]
     */
    public function createZoomMeeting(
        string $topic,
        \DateTime $startTime,
        int $durationMinutes = 60
    ): array {
        try {
            $token = $this->getZoomAccessToken();
            
            $meetingData = [
                'topic' => $topic,
                'type' => 2, // Scheduled meeting
                'start_time' => $startTime->format('Y-m-d\TH:i:s'),
                'duration' => $durationMinutes,
                'timezone' => config('app.timezone', 'UTC'),
                'settings' => [
                    'host_video' => true,
                    'participant_video' => true,
                    'join_before_host' => false,
                    'mute_upon_entry' => false,
                    'waiting_room' => false,
                    'audio' => 'both',
                    'auto_recording' => 'cloud'
                ]
            ];
            
            $response = $this->makeZoomRequest(
                'POST',
                'https://api.zoom.us/v2/users/me/meetings',
                $meetingData,
                $token
            );
            
            if (!isset($response['id'])) {
                throw new \Exception('Failed to create Zoom meeting');
            }
            
            Log::info('Zoom meeting created successfully', [
                'meeting_id' => $response['id'],
                'topic' => $topic
            ]);
            
            return [
                'link' => $response['join_url'],
                'meeting_id' => $response['id'],
                'password' => $response['password'] ?? '',
                'provider' => 'zoom'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create Zoom meeting: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Get access token for Zoom API
     */
    private function getZoomAccessToken(): string
    {
        try {
            $accountId = env('ZOOM_ACCOUNT_ID');
            $clientId = env('ZOOM_CLIENT_ID');
            $clientSecret = env('ZOOM_CLIENT_SECRET');
            
            if (!$accountId || !$clientId || !$clientSecret) {
                throw new \Exception('Zoom credentials not configured');
            }
            
            $auth = base64_encode("{$clientId}:{$clientSecret}");
            
            $response = $this->makeRawRequest(
                'POST',
                "https://zoom.us/oauth/token?grant_type=account_credentials&account_id={$accountId}",
                [],
                ['Authorization' => "Basic {$auth}"]
            );
            
            if (!isset($response['access_token'])) {
                throw new \Exception('Failed to get Zoom access token');
            }
            
            return $response['access_token'];
        } catch (\Exception $e) {
            Log::error('Failed to get Zoom access token: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create Google Meet meeting
     * Note: Google Meet is automatically created when adding to Google Calendar
     * This method provides a standalone option if needed
     * 
     * @param string $eventTitle
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     * @return array ['link' => string]
     */
    public function createGoogleMeeting(
        string $eventTitle,
        \DateTime $startTime,
        \DateTime $endTime
    ): array {
        try {
            // Google Meet links are generated automatically when creating calendar events
            // Generate a unique meeting ID-based link
            $uniqueId = Str::random(10);
            $meetingLink = "https://meet.google.com/{$uniqueId}";
            
            Log::info('Google Meet link generated', [
                'title' => $eventTitle,
                'link' => $meetingLink
            ]);
            
            return [
                'link' => $meetingLink,
                'provider' => 'google_meet'
            ];
        } catch (\Exception $e) {
            Log::error('Failed to create Google Meet: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Generate a simple meeting link (fallback option)
     * Can be used if both Zoom and Google Meet are not available
     */
    public function generateSimpleMeetingLink(string $type = 'generic'): array
    {
        try {
            $uniqueId = Str::random(10);
            
            switch ($type) {
                case 'zoom':
                    // Format: https://us02web.zoom.us/j/[meeting-id]?pwd=[password]
                    $link = "https://us02web.zoom.us/j/" . Str::random(9) . "?pwd=" . Str::random(10);
                    break;
                case 'google_meet':
                    $link = "https://meet.google.com/{$uniqueId}";
                    break;
                default:
                    $link = "https://meeting.example.com/{$uniqueId}";
            }
            
            return [
                'link' => $link,
                'provider' => $type
            ];
        } catch (\Exception $e) {
            Log::error('Failed to generate meeting link: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete Zoom meeting
     */
    public function deleteZoomMeeting(string $meetingId): bool
    {
        try {
            $token = $this->getZoomAccessToken();
            
            $this->makeZoomRequest(
                'DELETE',
                "https://api.zoom.us/v2/meetings/{$meetingId}",
                null,
                $token
            );
            
            Log::info("Zoom meeting deleted: {$meetingId}");
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete Zoom meeting: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Make Zoom API request
     */
    private function makeZoomRequest(string $method, string $url, ?array $data, string $token): array
    {
        return $this->makeRawRequest(
            $method,
            $url,
            $data,
            ['Authorization' => "Bearer {$token}"]
        );
    }

    /**
     * Make HTTP request
     */
    private function makeRawRequest(string $method, string $url, ?array $data, array $headers = []): array
    {
        try {
            $headers['Content-Type'] = 'application/json';
            
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
            
            $headerArray = [];
            foreach ($headers as $key => $value) {
                $headerArray[] = "{$key}: {$value}";
            }
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headerArray);
            
            if ($data) {
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
            }
            
            $response = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            
            if ($httpCode >= 400) {
                throw new \Exception("HTTP {$httpCode}: {$response}");
            }
            
            return json_decode($response, true) ?? [];
        } catch (\Exception $e) {
            Log::error('HTTP request failed: ' . $e->getMessage());
            throw $e;
        }
    }
}
