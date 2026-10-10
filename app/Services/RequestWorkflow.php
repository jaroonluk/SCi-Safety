<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Enums\TechnicalResult;
use App\Models\ViewingRequest;

class RequestWorkflow
{
    /**
     * After the responsible officer endorses a request, always send it to the
     * division director — never auto-approve for scheduling.
     */
    public function destinationAfterReview(ViewingRequest $request, bool $highRisk = false): RequestStatus
    {
        if ($request->technical_result?->lacksFootage()) {
            return RequestStatus::NoFootage;
        }

        return RequestStatus::PendingDirector;
    }

    public function approvalDestination(ViewingRequest $request): RequestStatus
    {
        return RequestStatus::PendingDirector;
    }

    public function lacksFootage(TechnicalResult $result): bool
    {
        return $result->lacksFootage();
    }
}
