<?php

namespace App\Services\Referral;

use App\Models\Master;
use App\Models\Referral;
use App\Models\ReferralEarning;
use Illuminate\Support\Collection;

class ReferralService
{
    /**
     * Регистрирует реферала: создаёт привязку приведённого мастера
     * к владельцу кода и начисляет рефереру вознаграждение.
     *
     * Возвращает null, если код не найден или мастер пытается
     * закрепить сам себя.
     */
    public function registerReferral(Master $referred, string $code): ?Referral
    {
        $referrer = Master::where('referral_code', $code)->first();

        if (empty($referrer) || $referrer->id === $referred->id) {
            return null;
        }

        return Referral::firstOrCreate(
            [
                'referred_master_id' => $referred->id,
            ],
            [
                'referrer_master_id' => $referrer->id,
                'program' => Referral::PROGRAM_MASTER_INVITE,
                'status' => Referral::STATUS_PENDING,
            ]
        );
    }

    /**
     * Сумма вознаграждения реферера с одного платежа реферала.
     *
     * Считается как процент от суммы платежа, процент задан
     * в config/referral.php.
     */
    public function rewardAmount(int $paymentAmount): int
    {
        $percent = (int) config('referral.percent');

        return (int) round($paymentAmount * $percent);
    }

    public function my(Master $referrer): Collection
    {
        return $referrer->referrals()
            ->with('referredMaster:id,name')
            ->withSum('referralEarnings as earned', 'amount')
            ->get()
            ->map(fn (Referral $referral) => [
                'name'       => $referral->referredMaster->name,
                'attached_at' => $referral->created_at,
                'is_rewarded' => $referral->status === Referral::STATUS_REWARDED,
                'earned'      => $referral->earned
            ]);
    }

    public function earnings(Master $referrer): array
    {
        $referralEarnings = $referrer->referralEarnings();

        return [
            'total' => $referralEarnings->sum('amount'),
            'pending' => $referralEarnings->where('status', ReferralEarning::STATUS_PENDING)->sum('amount'),
            'paid' =>  $referralEarnings->where('status', ReferralEarning::STATUS_PAID)->sum('amount'),
            'referrals_rewarded' => $referrer->referrals()->where('status', Referral::STATUS_REWARDED)->count(),
        ];
    }
}
