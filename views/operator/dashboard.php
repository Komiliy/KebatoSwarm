<?php
/**
 * Operator dashboard — onboarding flow or summary cards + recent activity.
 *
 * When no instances exist, shows a guided setup experience.
 * When instances exist, shows stat cards and the activity log.
 */
$pageTitle = 'Dashboard — Ricsian';
$isOnboarding = ($counts['total'] ?? 0) === 0;
?>

<?php if ($isOnboarding): ?>
<!-- ── Onboarding (zero instances) ─────────────────────────── -->
<div class="max-w-lg mx-auto" style="padding-top: 2rem; padding-bottom: 4rem;">
  <!-- Hero -->
  <div class="text-center mb-8">
    <div class="w-16 h-16 mx-auto mb-5 rounded-2xl bg-blue-100 dark:bg-blue-500/10 flex items-center justify-center">
    

    </div>
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white mb-2">Welcome to Ricsian</h1>
    <p class="text-sm text-zinc-500 dark:text-zinc-400" style="max-width: 360px; margin: 0 auto;">Your provisioning engine is ready. Complete these steps to deploy your first VoxelSite instance.</p>
  </div>

  <!-- Setup Steps -->
  <div class="space-y-3">
    <?php
    /**
     * Onboarding step renderer.
     *
     * Completed steps show a green checkmark and muted styling.
     * Active (next) steps have a prominent border and number.
     * Future steps are dimmed.
     */
    $steps = [
      [
        'done'  => $hasTemplates,
        'href'  => '/operator/templates',
        'title' => 'Prepare a template',
        'desc'  => 'Upload a VoxelSite ZIP and process it. This creates the base files every new instance will use.',
        'icon'  => '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
        'cta'   => 'Go to Templates',
      ],
      [
        'done'  => false, // We could check adapter config but keeping it simple
        'href'  => '/operator/deployment',
        'title' => 'Configure deployment',
        'desc'  => 'Choose where instances are deployed — a folder on this server, or via a hosting control panel.',
        'icon'  => '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/></svg>',
        'cta'   => 'Go to Deployment',
      ],
      [
        'done'  => false,
        'href'  => $hasTemplates ? '#' : '/operator/templates',
        'title' => 'Create your first instance',
        'desc'  => 'Deploy a VoxelSite website. Each instance gets its own URL, files, and content.',
        'icon'  => '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="16"/><line x1="8" y1="12" x2="16" y2="12"/></svg>',
        'cta'   => 'New Instance',
      ],
    ];

    $firstIncomplete = null;
    foreach ($steps as $i => $step) {
      if (!$step['done'] && $firstIncomplete === null) {
        $firstIncomplete = $i;
      }
    }

    foreach ($steps as $i => $step):
      $num = $i + 1;
      $isActive = ($i === $firstIncomplete);
      $isFuture = ($firstIncomplete !== null && $i > $firstIncomplete);

      if ($step['done']) {
        $ringClass   = 'border-green-200 dark:border-green-500/20';
        $numBg       = 'bg-green-100 dark:bg-green-500/10 text-green-600 dark:text-green-400';
        $titleClass  = 'text-zinc-400 dark:text-zinc-500 line-through';
        $descClass   = 'text-zinc-400 dark:text-zinc-600';
        $cardOpacity = 'opacity-60';
      } elseif ($isActive) {
        $ringClass   = 'border-blue-200 dark:border-blue-500/30 shadow-sm dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]';
        $numBg       = 'bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400';
        $titleClass  = 'text-zinc-900 dark:text-white';
        $descClass   = 'text-zinc-500 dark:text-zinc-400';
        $cardOpacity = '';
      } else {
        $ringClass   = 'border-zinc-200 dark:border-zinc-800';
        $numBg       = 'bg-zinc-100 dark:bg-zinc-800 text-zinc-400 dark:text-zinc-500';
        $titleClass  = 'text-zinc-500 dark:text-zinc-500';
        $descClass   = 'text-zinc-400 dark:text-zinc-600';
        $cardOpacity = 'opacity-50';
      }
    ?>
      <?php if ($step['done'] || !$isFuture): ?>
        <a href="<?= $step['done'] ? '#' : $step['href'] ?>"
           <?php if ($i === 2 && $hasTemplates && !$step['done']): ?>onclick="openNewInstanceModal(); return false;"<?php endif; ?>
           class="group block bg-white dark:bg-zinc-900 border <?= $ringClass ?> rounded-xl p-5 transition-all duration-200 <?= $cardOpacity ?> <?= $step['done'] ? 'cursor-default' : 'hover:border-blue-300 dark:hover:border-blue-500/40' ?>">
      <?php else: ?>
        <div class="bg-white dark:bg-zinc-900 border <?= $ringClass ?> rounded-xl p-5 <?= $cardOpacity ?>">
      <?php endif; ?>

          <div class="flex items-start gap-4">
            <!-- Step number / checkmark -->
            <div class="w-9 h-9 rounded-lg <?= $numBg ?> flex items-center justify-center flex-shrink-0">
              <?php if ($step['done']): ?>
                <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
              <?php else: ?>
                <span class="text-xs font-bold"><?= $num ?></span>
              <?php endif; ?>
            </div>

            <!-- Content -->
            <div class="min-w-0 flex-1">
              <div class="flex items-center gap-2 mb-0.5">
                <p class="text-sm font-semibold <?= $titleClass ?>"><?= $step['title'] ?></p>
                <?php if ($step['done']): ?>
                  <span class="text-[10px] font-medium text-green-600 dark:text-green-400 uppercase" style="letter-spacing: 0.05em;">Done</span>
                <?php endif; ?>
              </div>
              <p class="text-xs <?= $descClass ?> leading-relaxed"><?= $step['desc'] ?></p>
              <?php if ($isActive): ?>
                <div class="mt-3">
                  <span class="inline-flex items-center gap-1.5 text-xs font-medium text-blue-600 dark:text-blue-400 group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors">
                    <?= $step['cta'] ?>
                    <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                  </span>
                </div>
              <?php endif; ?>
            </div>

            <!-- Arrow (active step only) -->
            <?php if ($isActive): ?>
              <svg class="w-4 h-4 text-zinc-300 dark:text-zinc-600 group-hover:text-blue-500 dark:group-hover:text-blue-400 transition-colors flex-shrink-0 mt-2.5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg>
            <?php endif; ?>
          </div>

      <?php if ($step['done'] || !$isFuture): ?>
        </a>
      <?php else: ?>
        </div>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>

  <!-- Subtle footer note -->
  <p class="text-center text-xs text-zinc-400 dark:text-zinc-600 mt-8">VoxelSite makes the website. Ricsian makes it available to the world.</p>
</div>

<?php else: ?>
<!-- ── Active Dashboard ──────────────────────────────── -->
<div class="mb-8 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
  <div>
    <h1 class="text-2xl font-bold tracking-tight text-zinc-900 dark:text-white">Dashboard</h1>
    <p class="text-sm text-zinc-500 dark:text-zinc-400 mt-1">Overview of your Ricsian cluster.</p>
  </div>
  <button onclick="<?= $hasTemplates ? 'openNewInstanceModal()' : 'noTemplatesAlert()' ?>" class="sw-btn-primary">
    <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
    New Instance
  </button>
</div>

<!-- Summary Cards -->
<div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4 mb-8">
  <?php
  $statCards = [
    [
      'label' => 'Total Instances',
      'value' => $counts['total'],
      'color' => 'text-zinc-900 dark:text-white',
      'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/></svg>',
      'iconBg' => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400',
    ],
    [
      'label' => 'Active',
      'value' => $counts['active'],
      'color' => 'text-green-600 dark:text-green-400',
      'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><path d="m9 11 3 3L22 4"/></svg>',
      'iconBg' => 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400',
    ],
    [
      'label' => 'Paused',
      'value' => $counts['paused'],
      'color' => $counts['paused'] > 0 ? 'text-amber-600 dark:text-amber-400' : 'text-zinc-900 dark:text-white',
      'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="10" x2="10" y1="15" y2="9"/><line x1="14" x2="14" y1="15" y2="9"/></svg>',
      'iconBg' => $counts['paused'] > 0
        ? 'bg-amber-50 dark:bg-amber-500/10 text-amber-600 dark:text-amber-400'
        : 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400',
    ],
    [
      'label' => 'Storage Used',
      'value' => $storageUsed,
      'color' => 'text-zinc-900 dark:text-white text-2xl',
      'icon'  => '<svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="22" x2="2" y1="12" y2="12"/><path d="M5.45 5.11 2 12v6a2 2 0 0 0 2 2h16a2 2 0 0 0 2-2v-6l-3.45-6.89A2 2 0 0 0 16.76 4H7.24a2 2 0 0 0-1.79 1.11z"/><line x1="6" x2="6.01" y1="16" y2="16"/><line x1="10" x2="10.01" y1="16" y2="16"/></svg>',
      'iconBg' => 'bg-zinc-100 dark:bg-zinc-800 text-zinc-500 dark:text-zinc-400',
    ],
  ];

  foreach ($statCards as $card): ?>
    <div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800/80 rounded-xl p-5 shadow-sm dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05)]">
      <div class="flex items-center justify-between mb-3">
        <span class="text-[13px] font-medium text-zinc-500 dark:text-zinc-400"><?= $card['label'] ?></span>
        <div class="w-8 h-8 rounded-lg <?= $card['iconBg'] ?> flex items-center justify-center">
          <?= $card['icon'] ?>
        </div>
      </div>
      <div class="text-2xl font-bold tracking-tight <?= $card['color'] ?>" style="line-height: 1;"><?= $card['value'] ?></div>
    </div>
  <?php endforeach; ?>
</div>

<!-- Activity Log -->
<div class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800/80 rounded-xl shadow-sm dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] overflow-hidden">
  <div class="px-5 py-4 border-b border-zinc-100 dark:border-zinc-800/80 bg-zinc-50/50 dark:bg-zinc-800/20">
    <h2 class="text-base font-semibold tracking-tight text-zinc-900 dark:text-white">Recent Activity</h2>
  </div>
  
  <?php if (empty($recentLogs)): ?>
    <div class="p-8 text-center">
      <div class="w-12 h-12 mx-auto mb-3 rounded-full bg-zinc-100 dark:bg-zinc-800 flex items-center justify-center">
        <svg class="w-6 h-6 text-zinc-400 dark:text-zinc-500" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 12h.01"/><path d="M16 6H3"/><path d="M21 12H3"/><path d="M21 18H3"/></svg>
      </div>
      <p class="text-sm font-medium text-zinc-700 dark:text-zinc-300 mb-1">No activity yet</p>
      <p class="text-xs text-zinc-400 dark:text-zinc-500">Provisioning events will appear here as you create and manage instances.</p>
    </div>
  <?php else: ?>
    <div class="overflow-x-auto">
      <table class="w-full text-left text-sm" style="white-space: nowrap;">
        <thead>
          <tr class="bg-zinc-50/50 dark:bg-zinc-800/20 border-b border-zinc-100 dark:border-zinc-800/80 text-zinc-500 dark:text-zinc-400">
            <th class="px-5 py-3 font-medium uppercase tracking-wide text-[11px]">Instance</th>
            <th class="px-5 py-3 font-medium uppercase tracking-wide text-[11px]">Step</th>
            <th class="px-5 py-3 font-medium uppercase tracking-wide text-[11px]">Status</th>
            <th class="px-5 py-3 font-medium uppercase tracking-wide text-[11px]">Duration</th>
            <th class="px-5 py-3 font-medium uppercase tracking-wide text-[11px] text-right">Time</th>
          </tr>
        </thead>
        <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800/80">
          <?php foreach ($recentLogs as $log): ?>
            <tr class="hover:bg-zinc-50/50 dark:hover:bg-zinc-800/30 transition-colors">
              <td class="px-5 py-3">
                <a href="/operator/instances/<?= $log['instance_id'] ?>" class="font-medium text-zinc-900 dark:text-white hover:text-blue-600 dark:hover:text-blue-500 transition-colors group">
                  <?= htmlspecialchars($log['slug'] ?? '—') ?>
                  <span class="inline-block ml-1 opacity-0 group-hover:opacity-100 transition-opacity">→</span>
                </a>
              </td>
              <td class="px-5 py-3 text-zinc-500 dark:text-zinc-400"><?= htmlspecialchars($log['step']) ?></td>
              <td class="px-5 py-3">
                <?php
                  $badgeClass = match($log['status']) {
                    'completed' => 'bg-green-100/50 text-green-700 dark:bg-green-500/10 dark:text-green-400 ring-1 ring-inset ring-green-600/20 dark:ring-green-500/20',
                    'failed'    => 'bg-red-100/50 text-red-700 dark:bg-red-500/10 dark:text-red-400 ring-1 ring-inset ring-red-600/10 dark:ring-red-500/20',
                    'started'   => 'bg-blue-100/50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400 ring-1 ring-inset ring-blue-600/20 dark:ring-blue-500/20 animate-pulse',
                    default     => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300 ring-1 ring-inset ring-zinc-500/20 dark:ring-zinc-400/20',
                  };
                ?>
                <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[11px] font-semibold tracking-wide <?= $badgeClass ?>">
                  <?= strtoupper($log['status']) ?>
                </span>
              </td>
              <td class="px-5 py-3 text-zinc-500 dark:text-zinc-400" style="font-variant-numeric: tabular-nums;">
                <?= $log['duration_ms'] ? $log['duration_ms'] . 'ms' : '—' ?>
              </td>
              <td class="px-5 py-3 text-zinc-500 dark:text-zinc-400 text-xs text-right">
                <?= date('M j, H:i', strtotime($log['created_at'])) ?>
              </td>
            </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
    </div>
  <?php endif; ?>
</div>
<?php endif; ?>

<?php if ($hasTemplates): ?>
  <?php require __DIR__ . '/partials/new-instance-modal.php'; ?>
<?php endif; ?>

<script>
function noTemplatesAlert() {
  swConfirm({
    title: 'No template available',
    message: 'You need to process at least one VoxelSite template before creating instances. Go to Templates to upload and process a VoxelSite ZIP file.',
    confirmLabel: 'Go to Templates',
    danger: false
  }).then(() => {
    location.href = '/operator/templates';
  }).catch(() => {});
}
</script>
