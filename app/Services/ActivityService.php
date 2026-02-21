<?php
namespace App\Services;

use App\Models\Response;
use App\Models\Activity;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class ActivityService
{
    /**
     * Handle communication log creation.
     *
     * @param mixed $responseId The ID of the response.
     * @param string $type The communication type ('call', 'WhatsApp', 'email', 'SMS').
     * @param string $status The communication outcome.
     * @param string|null $details Additional details about the communication.
     * @return Activity|null The created activity or null if the response does not exist.
     */
    public function handleCommunication(mixed $responseId, string $type, string $status, ?string $details = null): ?Activity
    {
        try {
            $response = Response::findOrFail($responseId);
        } catch (ModelNotFoundException $exception) {
            return null;
        }

        return $this->logActivity($response, $type, $status, $details);
    }

    /**
     * Log an activity for a given response.
     *
     * @param Response $response The response instance.
     * @param string $type The type of communication.
     * @param string $status The status of the communication.
     * @param string|null $details Any details.
     * @return Activity The created activity.
     */
    private function logActivity(Response $response, string $type, string $status, ?string $details): Activity
    {
        $activity = new Activity([
            'type' => $type,
            'status' => $status,
            'details' => $details
        ]);

        $response->activities()->save($activity);


        return $activity;
    }

    /**
     * Handle status update log creation.
     *
     * @param mixed $responseId The ID of the response.
     * @param string $status The new status.
     * @return Activity|null The created activity or null if the response does not exist.
     */
    public function handleStatusUpdate(mixed $responseId, string $status): ?Activity
    {
        try {
            $response = Response::findOrFail($responseId);
        } catch (ModelNotFoundException $exception) {
            return null;
        }

        return $this->logActivity($response, 'Status', $status, "Status updated to {$status}");
    }
}



/*
 *   public function logPhoneCall(int $responseId, string $status, string $details)
    {
        $activity = $this->communicationService->handleCommunication($responseId, 'call', $status, $details);

        if ($activity) {
            return response()->json(['message' => 'Phone call activity logged successfully', 'activity' => $activity]);
        } else {
            return response()->json(['message' => 'Response not found'], 404);
        }
    }
 */
