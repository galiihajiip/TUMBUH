<?php

namespace App\Services;

use App\Models\Business;
use App\Models\Mission;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;

/**
 * Weekly Growth Mission check-ins. Completion advances the action plan,
 * awards achievements and re-evaluates rewards.
 */
class MissionService
{
    public function __construct(
        private NotificationService $notifications,
        private RewardService $rewards,
    ) {}

    public function current(Business $business): ?Mission
    {
        return $business->missions()->where('status', 'active')->orderBy('week')->first();
    }

    public function submit(Mission $mission, array $data, ?UploadedFile $evidence = null): Mission
    {
        return DB::transaction(function () use ($mission, $data, $evidence) {
            $path = $evidence?->store('evidence/'.$mission->business_id, 'local');

            $mission->submissions()->create([
                'completion' => $data['completion'],
                'kpi_value' => $data['kpi_value'] ?? null,
                'evidence_path' => $path,
                'problem' => $data['problem'] ?? null,
                'notes' => $data['notes'] ?? null,
            ]);

            $status = ['yes' => 'completed', 'partial' => 'partial', 'no' => 'active'][$data['completion']];
            $mission->update([
                'status' => $status,
                'completed_at' => $status === 'active' ? null : now(),
            ]);

            $business = $mission->business;

            if ($status !== 'active') {
                $mission->planItem?->update(['status' => 'done']);
                $this->activateNext($mission);
                $this->achievements($business);
                $this->rewards->evaluate($business);
            } elseif (! empty($data['problem'])) {
                $this->notifications->push($business, 'ai', 'Kendala misi minggu '.$mission->week.' tercatat',
                    'AI mencatat kendala Anda: "'.str($data['problem'])->limit(80).'". Coba pecah misi menjadi langkah lebih kecil.', 'Lihat misi', route('missions.show', $mission));
            }

            return $mission->fresh();
        });
    }

    private function activateNext(Mission $mission): void
    {
        $next = $mission->business->missions()->where('status', 'upcoming')->orderBy('week')->first();
        if ($next) {
            $next->update(['status' => 'active']);
            $next->planItem?->update(['status' => 'active']);
            $this->notifications->push($mission->business, 'mission', "Misi minggu {$next->week} sudah siap",
                $next->title, 'Mulai misi', route('missions.show', $next));

            return;
        }

        $mission->business->activePlan?->update(['status' => 'completed']);
        $this->notifications->push($mission->business, 'performance', 'Rencana 30 hari selesai — saatnya mengukur dampak',
            'Semua misi selesai. Laporkan KPI bulan ini untuk melihat Before vs After dan Growth Score terbaru.', 'Lihat Growth Ledger', route('growth.ledger'));
    }

    public function achievements(Business $business): void
    {
        $done = $business->missions()->whereIn('status', ['completed', 'partial'])->count();
        $award = function (string $key, string $title, string $desc, string $icon) use ($business) {
            $business->achievements()->firstOrCreate(['key' => $key], ['title' => $title, 'description' => $desc, 'icon' => $icon, 'earned_at' => now()]);
        };

        if ($done >= 1) {
            $award('first_mission', 'First Mission', 'Menyelesaikan misi pertumbuhan pertama', 'flag');
        }
        if ($done >= 4) {
            $award('plan_complete', 'Plan Finisher', 'Menyelesaikan rencana aksi 30 hari', 'task_alt');
        }
        if ($done >= 8) {
            $award('consistent', 'Consistent Grower', '8 misi mingguan diselesaikan', 'local_fire_department');
        }
    }
}
