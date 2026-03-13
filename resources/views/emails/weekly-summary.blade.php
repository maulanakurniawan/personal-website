@php
    $formatDuration = static function (int $seconds): string {
        $hours = intdiv($seconds, 3600);
        $minutes = intdiv($seconds % 3600, 60);

        return sprintf('%dh %02dm', $hours, $minutes);
    };
@endphp

<p>Hi {{ $userName }},</p>
<p>Here is your SoloHours summary for the last 7 days.</p>

<p><strong>Total tracked:</strong> {{ $formatDuration($totalSeconds) }}</p>
<p><strong>Billable:</strong> {{ $formatDuration($billableSeconds) }}</p>
@if($estimatedRevenue !== null)
<p><strong>Estimated revenue:</strong> ${{ number_format($estimatedRevenue, 2) }}</p>
@endif

<p><strong>Top projects:</strong></p>
<ul>
    @foreach($topProjects as $projectName => $seconds)
        <li>{{ $projectName }} — {{ $formatDuration($seconds) }}</li>
    @endforeach
</ul>

<p>Keep up the great work.</p>
