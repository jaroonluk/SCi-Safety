<?php

namespace App\Services;

use App\Enums\RequestStatus;
use App\Enums\TechnicalResult;
use App\Models\ViewingRequest;
use App\Models\WorkflowRule;

class RequestWorkflow
{
    public function destinationAfterReview(ViewingRequest $request, bool $highRisk): RequestStatus
    {
        if ($request->technical_result?->lacksFootage()) {
            return RequestStatus::NoFootage;
        }

        if ($highRisk) {
            return RequestStatus::PendingPdpa;
        }

        return $this->approvalDestination($request);
    }

    public function approvalDestination(ViewingRequest $request): RequestStatus
    {
        $approver = WorkflowRule::query()
            ->where('incident_type', $request->incident_type->value)
            ->value('approver') ?? 'admin';

        return match ($approver) {
            'director' => RequestStatus::PendingDirector,
            'dean' => RequestStatus::PendingDean,
            default => RequestStatus::Approved,
        };
    }

    public function lacksFootage(TechnicalResult $result): bool
    {
        return $result->lacksFootage();
    }
}
