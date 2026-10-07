<?php

namespace App\View\Components;

use Illuminate\View\Component;

/**
 * Server-rendered SVG chart (line or column). Geometry only — the data
 * comes from services. Hover tooltips are added in the Blade template.
 *
 * Series: [['name' => 'Revenue', 'values' => [..], 'color' => 'brand|info|ink']]
 */
class Chart extends Component
{
    public const COLORS = ['brand' => '#d81b27', 'info' => '#2563eb', 'ink' => '#475569'];

    public array $geo;

    public function __construct(
        public array $labels,
        public array $series,
        public string $type = 'line',
        public string $format = 'count',
        public int $height = 220,
        public ?float $min = null,
        public string $id = '',
        public int $width = 640,
    ) {
        $this->id = $id ?: 'c'.substr(md5(json_encode([$labels, $series])), 0, 8);
        $this->geo = $this->compute();
    }

    private function compute(): array
    {
        $w = $this->width;
        $h = $this->height;
        $pad = ['l' => 56, 'r' => 16, 't' => 16, 'b' => 28];
        $n = max(1, count($this->labels));
        $values = collect($this->series)->flatMap(fn ($s) => $s['values'])->filter(fn ($v) => $v !== null);
        $max = (float) ($values->max() ?? 1);
        $min = $this->min ?? ($this->type === 'bar' ? 0 : (float) min(0, $values->min() ?? 0));
        if ($this->type === 'line' && $this->min === null && $values->min() > 0) {
            $min = 0;
        }
        [$lo, $hi, $step] = $this->niceScale($min, $max);

        $plotW = $w - $pad['l'] - $pad['r'];
        $plotH = $h - $pad['t'] - $pad['b'];
        $band = $plotW / $n;
        $x = fn ($i) => $pad['l'] + $band * ($i + 0.5);
        $y = fn ($v) => $pad['t'] + $plotH - (($v - $lo) / max(1e-9, $hi - $lo)) * $plotH;

        $ticks = [];
        for ($v = $lo; $v <= $hi + $step / 2; $v += $step) {
            $ticks[] = ['y' => round($y($v), 1), 'label' => $this->fmt($v, true)];
        }

        $seriesCount = count($this->series);
        $barW = min(24, ($band * 0.7) / max(1, $seriesCount) - 2);
        $out = [];
        foreach ($this->series as $si => $s) {
            $color = self::COLORS[$s['color'] ?? 'brand'] ?? ($s['color'] ?? self::COLORS['brand']);
            $points = [];
            foreach ($s['values'] as $i => $v) {
                if ($v === null) {
                    continue;
                }
                $points[] = ['i' => $i, 'x' => round($x($i), 1), 'y' => round($y($v), 1), 'v' => $v];
            }
            $bars = [];
            if ($this->type === 'bar') {
                $groupW = $seriesCount * ($barW + 2) - 2;
                foreach ($points as $p) {
                    $bx = $x($p['i']) - $groupW / 2 + $si * ($barW + 2);
                    $top = min($p['y'], $y(max(0, $lo)));
                    $bh = abs($y(max(0, $lo)) - $p['y']);
                    $bars[] = ['x' => round($bx, 1), 'y' => round($top, 1), 'w' => round($barW, 1), 'h' => round(max(1, $bh), 1), 'path' => $this->roundedTop($bx, $top, $barW, max(1, $bh), 4)];
                }
            }
            $line = collect($points)->map(fn ($p, $k) => ($k ? 'L' : 'M').$p['x'].' '.$p['y'])->implode(' ');
            $area = $points ? $line.' L'.end($points)['x'].' '.round($y(max(0, $lo)), 1).' L'.$points[0]['x'].' '.round($y(max(0, $lo)), 1).' Z' : '';
            $out[] = ['name' => $s['name'], 'color' => $color, 'points' => $points, 'bars' => $bars, 'line' => $line, 'area' => $area];
        }

        $tooltips = [];
        foreach ($this->labels as $i => $label) {
            $tooltips[] = [
                'label' => $label,
                'x' => round($x($i), 1),
                'rows' => collect($this->series)->map(fn ($s) => ['name' => $s['name'], 'value' => isset($s['values'][$i]) && $s['values'][$i] !== null ? $this->fmt($s['values'][$i]) : '—', 'color' => self::COLORS[$s['color'] ?? 'brand'] ?? self::COLORS['brand']])->all(),
            ];
        }

        return compact('w', 'h', 'pad', 'ticks', 'band', 'out', 'tooltips') + ['labels' => collect($this->labels)->map(fn ($l, $i) => ['x' => round($x($i), 1), 'label' => $l])->all(), 'baseline' => round($y(max(0, $lo)), 1)];
    }

    private function roundedTop(float $x, float $y, float $w, float $h, float $r): string
    {
        $r = min($r, $w / 2, $h);

        return sprintf('M%.1f %.1f L%.1f %.1f Q%.1f %.1f %.1f %.1f L%.1f %.1f Q%.1f %.1f %.1f %.1f L%.1f %.1f Z',
            $x, $y + $h, $x, $y + $r, $x, $y, $x + $r, $y, $x + $w - $r, $y, $x + $w, $y, $x + $w, $y + $r, $x + $w, $y + $h);
    }

    private function niceScale(float $min, float $max): array
    {
        if ($max <= $min) {
            $max = $min + 1;
        }
        $range = $max - $min;
        $rough = $range / 4;
        $mag = 10 ** floor(log10($rough));
        $norm = $rough / $mag;
        $step = ($norm <= 1 ? 1 : ($norm <= 2 ? 2 : ($norm <= 2.5 ? 2.5 : ($norm <= 5 ? 5 : 10)))) * $mag;

        return [floor($min / $step) * $step, ceil($max / $step) * $step, $step];
    }

    public function fmt(float $v, bool $axis = false): string
    {
        return match ($this->format) {
            'rp' => rupiah_short($v),
            'pct' => num($v, $axis ? 0 : 1).'%',
            'score' => (string) round($v),
            default => num($v, fmod($v, 1.0) == 0.0 ? 0 : 1),
        };
    }

    public function render()
    {
        return view('components.chart');
    }
}
