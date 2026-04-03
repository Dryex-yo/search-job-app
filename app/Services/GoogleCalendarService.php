<?php

namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar\Event;
use Google\Service\Calendar\EventDateTime;
use Illuminate\Support\Facades\Log;

class GoogleCalendarService
{
    protected Client $client;
    protected Calendar $service;
    protected string $clientSecretPath;

    public function __construct()
    {
        $this->clientSecretPath = base_path(env('GOOGLE_CALENDAR_CREDENTIALS_PATH', 'secrets/google-calendar-credentials.json'));
        $this->initializeClient();
    }

    /**
     * Initialize Google Client
     */
    private function initializeClient(): void
    {
        try {
            $this->client = new Client();
            
            // Set application name
            $this->client->setApplicationName(config('app.name'));
            
            // Load client secret credentials
            if (!file_exists($this->clientSecretPath)) {
                throw new \Exception("Google Calendar credentials file not found at: {$this->clientSecretPath}");
            }
            
            $this->client->setAuthConfig($this->clientSecretPath);
            $this->client->setScopes(Calendar::CALENDAR);
            $this->service = new Calendar($this->client);
            
            Log::info('Google Calendar service initialized successfully');
        } catch (\Exception $e) {
            Log::error('Failed to initialize Google Calendar service: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Create an event on admin's calendar
     * 
     * @param string $adminEmail
     * @param string $applicantEmail
     * @param string $applicantName
     * @param string $jobTitle
     * @param \DateTime $startTime
     * @param \DateTime $endTime
     * @param string $meetingLink
     * @param array $additionalDetails
     * @return string Event ID
     */
    public function createInterviewEvent(
        string $adminEmail,
        string $applicantEmail,
        string $applicantName,
        string $jobTitle,
        \DateTime $startTime,
        \DateTime $endTime,
        string $meetingLink,
        array $additionalDetails = []
    ): string {
        try {
            $event = new Event();
            $event->setSummary("Interview: {$applicantName} - {$jobTitle}");
            
            // Set time
            $startDateTime = new EventDateTime();
            $startDateTime->setDateTime($startTime->format('c'));
            $startDateTime->setTimeZone(config('app.timezone', 'UTC'));
            $event->setStart($startDateTime);
            
            $endDateTime = new EventDateTime();
            $endDateTime->setDateTime($endTime->format('c'));
            $endDateTime->setTimeZone(config('app.timezone', 'UTC'));
            $event->setEnd($endDateTime);
            
            // Set description with meeting details
            $description = $this->buildEventDescription(
                $applicantName,
                $applicantEmail,
                $jobTitle,
                $meetingLink,
                $additionalDetails
            );
            $event->setDescription($description);
            
            // Add attendees
            $event->setAttendees([
                ['email' => $adminEmail, 'displayName' => 'Admin'],
                ['email' => $applicantEmail, 'displayName' => $applicantName]
            ]);
            
            // Set meeting notification
            $event->setReminders([
                'useDefault' => false,
                'overrides' => [
                    ['method' => 'email', 'minutes' => 24 * 60], // 1 day before
                    ['method' => 'popup', 'minutes' => 30]        // 30 minutes before
                ]
            ]);
            
            // Add the meeting link as a conference if it's a Google Meet link
            if (strpos($meetingLink, 'meet.google.com') !== false) {
                $conferenceData = new \Google\Service\Calendar\ConferenceData();
                $entryPoint = new \Google\Service\Calendar\EntryPoint();
                $entryPoint->setEntryPointType('video');
                $entryPoint->setLabel('Google Meet');
                $entryPoint->setUri($meetingLink);
                $conferenceData->setEntryPoints([$entryPoint]);
                $event->setConferenceData($conferenceData);
            } else {
                // For Zoom or other links, add to description
                $event->setLocation($meetingLink);
            }
            
            // Create event on admin's calendar
            $createdEvent = $this->service->events->insert('primary', $event, [
                'sendUpdates' => 'all'  // Send invitations to attendees
            ]);
            
            Log::info("Interview event created successfully", [
                'event_id' => $createdEvent->getId(),
                'applicant' => $applicantName,
                'admin_email' => $adminEmail
            ]);
            
            return $createdEvent->getId();
        } catch (\Exception $e) {
            Log::error('Failed to create interview event on calendar: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Update an existing interview event
     * 
     * @param string $eventId
     * @param string $adminEmail
     * @param \DateTime|null $newStartTime
     * @param \DateTime|null $newEndTime
     * @param string|null $newMeetingLink
     * @return bool
     */
    public function updateInterviewEvent(
        string $eventId,
        string $adminEmail,
        ?\DateTime $newStartTime = null,
        ?\DateTime $newEndTime = null,
        ?string $newMeetingLink = null
    ): bool {
        try {
            $event = $this->service->events->get('primary', $eventId);
            
            if ($newStartTime) {
                $startDateTime = new EventDateTime();
                $startDateTime->setDateTime($newStartTime->format('c'));
                $startDateTime->setTimeZone(config('app.timezone', 'UTC'));
                $event->setStart($startDateTime);
            }
            
            if ($newEndTime) {
                $endDateTime = new EventDateTime();
                $endDateTime->setDateTime($newEndTime->format('c'));
                $endDateTime->setTimeZone(config('app.timezone', 'UTC'));
                $event->setEnd($endDateTime);
            }
            
            if ($newMeetingLink) {
                if (strpos($newMeetingLink, 'meet.google.com') !== false) {
                    $conferenceData = new \Google\Service\Calendar\ConferenceData();
                    $entryPoint = new \Google\Service\Calendar\EntryPoint();
                    $entryPoint->setEntryPointType('video');
                    $entryPoint->setLabel('Google Meet');
                    $entryPoint->setUri($newMeetingLink);
                    $conferenceData->setEntryPoints([$entryPoint]);
                    $event->setConferenceData($conferenceData);
                } else {
                    $event->setLocation($newMeetingLink);
                }
            }
            
            $this->service->events->update('primary', $eventId, $event, [
                'sendUpdates' => 'all'
            ]);
            
            Log::info("Interview event updated successfully: {$eventId}");
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to update interview event: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Delete an interview event
     * 
     * @param string $eventId
     * @return bool
     */
    public function deleteInterviewEvent(string $eventId): bool
    {
        try {
            $this->service->events->delete('primary', $eventId, [
                'sendUpdates' => 'all'
            ]);
            
            Log::info("Interview event deleted successfully: {$eventId}");
            return true;
        } catch (\Exception $e) {
            Log::error('Failed to delete interview event: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Build event description with interview details
     */
    private function buildEventDescription(
        string $applicantName,
        string $applicantEmail,
        string $jobTitle,
        string $meetingLink,
        array $additionalDetails
    ): string {
        $description = "Interview - {$jobTitle}\n\n";
        $description .= "Candidate: {$applicantName}\n";
        $description .= "Email: {$applicantEmail}\n";
        $description .= "\nMeeting Link: {$meetingLink}\n";
        
        if (!empty($additionalDetails['notes'])) {
            $description .= "\nNotes:\n{$additionalDetails['notes']}\n";
        }
        
        if (!empty($additionalDetails['interview_type'])) {
            $description .= "\nInterview Type: {$additionalDetails['interview_type']}\n";
        }
        
        if (!empty($additionalDetails['duration_minutes'])) {
            $description .= "Duration: {$additionalDetails['duration_minutes']} minutes\n";
        }
        
        $description .= "\n---\nGenerated by Dryex Recruitment System";
        
        return $description;
    }

    /**
     * Get user's calendar list
     */
    public function getCalendarList()
    {
        try {
            return $this->service->calendarList->listCalendarList();
        } catch (\Exception $e) {
            Log::error('Failed to fetch calendar list: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Check if service is properly authenticated
     */
    public function isAuthenticated(): bool
    {
        return file_exists($this->clientSecretPath);
    }
}
