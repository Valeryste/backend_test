<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\AttachRequest;
use App\Models\Referral;
use App\Services\Referral\ReferralService;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReferralController extends BaseController
{
    public function __construct(
        private readonly ReferralService $referralService
    ) {}

    public function attach(AttachRequest $request): ?Referral
    {
        $master = $request->attributes->get('current_master');

        return $this->referralService->registerReferral(referred: $master, code: $request->validated('code'));
    }

    public function my(Request $request): Collection
    {
        return $this->referralService->my($request->attributes->get('current_master'));
    }

    public function earnings(Request $request): array
    {
        return $this->referralService->earnings($request->attributes->get('current_master'));
    }
}