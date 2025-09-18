<?php
namespace App\Livewire\Concerns;

trait BuildsTimezones
{
    protected function buildTimezoneOptions(): array
    {
        $now = new \DateTime('now', new \DateTimeZone('UTC'));
        $ids = \DateTimeZone::listIdentifiers(\DateTimeZone::ALL);
        $out = [];
        foreach ($ids as $id) {
            $tz = new \DateTimeZone($id);
            $offset = $tz->getOffset($now);
            $sign = $offset >= 0 ? '+' : '-';
            $abs = abs($offset);
            $h = str_pad((string) floor($abs / 3600), 2, '0', STR_PAD_LEFT);
            $m = str_pad((string) floor(($abs % 3600) / 60), 2, '0', STR_PAD_LEFT);
            $out[] = ['id' => $id, 'label' => "(GMT{$sign}{$h}:{$m}) {$id}"];
        }
        usort($out, fn($a, $b) => strcmp($a['label'], $b['label']));
        return $out;
    }
}
