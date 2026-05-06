<?php
/**
 * Operator layout — sidebar nav, content area.
 * Receives $content from Response::view()
 */
$pageTitle = $pageTitle ?? 'Dashboard — Ricsian';
$currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
?>
<!DOCTYPE html>
<html lang="en" data-theme="dark" class="dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="robots" content="noindex, nofollow">
  <title><?= htmlspecialchars($pageTitle) ?></title>

  <link rel="stylesheet" href="/fonts/inter/inter.css">
  <link rel="stylesheet" href="/build/swarm.css">

  <script>
    (function() {
      var t = localStorage.getItem('swarm-theme') || 'dark';
      document.documentElement.setAttribute('data-theme', t);
      if (t === 'dark') document.documentElement.classList.add('dark');
      else document.documentElement.classList.remove('dark');
    })();
  </script>
</head>
<body class="bg-zinc-50 dark:bg-zinc-950 text-zinc-900 dark:text-zinc-50 min-h-screen flex font-sans selection:bg-blue-500/30 selection:text-white antialiased">

  <div class="flex min-h-screen w-full">
    <!-- Mobile Top Bar (< md) -->
    <header class="fixed top-0 left-0 right-0 z-40 flex items-center justify-between px-4 bg-white dark:bg-[#0f0f11] border-b border-zinc-200 dark:border-zinc-800/80 md:hidden" style="height: 56px;">
      <button onclick="toggleSidebar()" id="sidebar-toggle" class="p-2 -ml-1 rounded-lg text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800 hover:text-zinc-900 dark:hover:text-zinc-100 transition-colors" aria-label="Toggle navigation">
        <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><line x1="4" x2="20" y1="6" y2="6"/><line x1="4" x2="20" y1="12" y2="12"/><line x1="4" x2="20" y1="18" y2="18"/></svg>
      </button>
      <a href="/operator" class="flex items-center gap-2 text-zinc-900 dark:text-white">
        <span class="font-semibold tracking-tight text-sm">Ricsian</span>
      </a>
      <div style="width: 36px;"></div>
    </header>

    <!-- Sidebar Overlay (mobile) -->
    <div id="sidebar-overlay" class="fixed inset-0 z-30 bg-zinc-950/50 backdrop-blur-sm opacity-0 pointer-events-none md:hidden transition-opacity duration-300" onclick="closeSidebar()"></div>

    <!-- Sidebar -->
    <aside id="sidebar" class="w-64 flex-shrink-0 bg-white dark:bg-[#0f0f11] border-r border-zinc-200 dark:border-zinc-800/80 flex flex-col fixed inset-y-0 z-40 -translate-x-full md:translate-x-0 transition-transform duration-300 ease-in-out">
      <!-- Logo -->
      <div class="px-5 h-16 flex items-center">
        <a href="/operator" class="flex items-center gap-2.5 text-zinc-900 dark:text-white hover:opacity-80 transition-opacity">
          <span class="font-semibold tracking-tight text-sm">Ricsian</span>
        </a>
      </div>

      <!-- Navigation -->
      <nav class="flex-1 px-3 overflow-y-auto">
        <?php
        /**
         * Nav item renderer — consistent across all sidebar items.
         *
         * Active state: 2px blue left accent + tinted background + blue text.
         * Hover state: subtle background lift + text brightens.
         */
        $navItem = function($path, $exact, $icon, $label) use ($currentPath) {
          $isActive = $exact ? $currentPath === $path : str_starts_with($currentPath, $path);
          if ($isActive) {
            $cls = 'bg-blue-100 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400 font-medium';
            $style = 'border-left: 2px solid var(--color-blue-500); padding-left: 10px; border-radius: 0 var(--radius-lg) var(--radius-lg) 0;';
          } else {
            $cls = 'text-zinc-600 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/50 hover:text-zinc-900 dark:hover:text-zinc-100 font-medium';
            $style = 'padding-left: 12px; border-radius: var(--radius-lg);';
          }
          return sprintf(
            '<a href="%s" class="group flex items-center gap-3 pr-3 py-2 transition-all duration-150 text-[13px] %s" style="%s">%s<span>%s</span></a>',
            $path, $cls, $style, $icon, $label
          );
        };

        /**
         * Section header — uppercase label, generous vertical spacing.
         */
        $sectionHeader = function($label) {
          return '<div class="px-3 pt-6 pb-2 text-[10px] font-semibold uppercase select-none" style="letter-spacing: 0.1em; color: var(--color-zinc-400);">' . $label . '</div>';
        };

        // ── Main ──
        echo '<div class="space-y-0.5 mt-1">';
        // Dashboard — Lucide "layout-dashboard"
        echo $navItem('/operator', true,
          '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] flex-shrink-0 opacity-70 group-hover:opacity-100 transition-opacity" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="9" rx="1"/><rect x="14" y="3" width="7" height="5" rx="1"/><rect x="14" y="12" width="7" height="9" rx="1"/><rect x="3" y="16" width="7" height="5" rx="1"/></svg>',
          'Dashboard');
        // Instances — Lucide "layers"
        echo $navItem('/operator/instances', false,
          '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] flex-shrink-0 opacity-70 group-hover:opacity-100 transition-opacity" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m12.83 2.18a2 2 0 0 0-1.66 0L2.6 6.08a1 1 0 0 0 0 1.83l8.58 3.91a2 2 0 0 0 1.66 0l8.58-3.9a1 1 0 0 0 0-1.83Z"/><path d="m22 17.65-9.17 4.16a2 2 0 0 1-1.66 0L2 17.65"/><path d="m22 12.65-9.17 4.16a2 2 0 0 1-1.66 0L2 12.65"/></svg>',
          'Instances');
        echo '</div>';

        // ── Config ──
        echo $sectionHeader('Config');
        echo '<div class="space-y-0.5">';
        // Templates — Lucide "package"
        echo $navItem('/operator/templates', false,
          '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] flex-shrink-0 opacity-70 group-hover:opacity-100 transition-opacity" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="m7.5 4.27 9 5.15"/><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16Z"/><path d="m3.3 7 8.7 5 8.7-5"/><path d="M12 22V12"/></svg>',
          'Templates');
        // Deployment — Lucide "server" (communicates infrastructure/control panel)
        echo $navItem('/operator/deployment', false,
          '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] flex-shrink-0 opacity-70 group-hover:opacity-100 transition-opacity" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><rect width="20" height="8" x="2" y="2" rx="2" ry="2"/><rect width="20" height="8" x="2" y="14" rx="2" ry="2"/><line x1="6" x2="6.01" y1="6" y2="6"/><line x1="6" x2="6.01" y1="18" y2="18"/></svg>',
          'Deployment');
        echo '</div>';

        // ── System ──
        echo $sectionHeader('System');
        echo '<div class="space-y-0.5">';
        // Account — Lucide "user"
        echo $navItem('/operator/account', false,
          '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] flex-shrink-0 opacity-70 group-hover:opacity-100 transition-opacity" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M19 21v-2a4 4 0 0 0-4-4H9a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>',
          'Account');
        // System — Lucide "settings"
        echo $navItem('/operator/system', false,
          '<svg xmlns="http://www.w3.org/2000/svg" class="w-[18px] h-[18px] flex-shrink-0 opacity-70 group-hover:opacity-100 transition-opacity" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12.22 2h-.44a2 2 0 0 0-2 2v.18a2 2 0 0 1-1 1.73l-.43.25a2 2 0 0 1-2 0l-.15-.08a2 2 0 0 0-2.73.73l-.22.38a2 2 0 0 0 .73 2.73l.15.1a2 2 0 0 1 1 1.72v.51a2 2 0 0 1-1 1.74l-.15.09a2 2 0 0 0-.73 2.73l.22.38a2 2 0 0 0 2.73.73l.15-.08a2 2 0 0 1 2 0l.43.25a2 2 0 0 1 1 1.73V20a2 2 0 0 0 2 2h.44a2 2 0 0 0 2-2v-.18a2 2 0 0 1 1-1.73l.43-.25a2 2 0 0 1 2 0l.15.08a2 2 0 0 0 2.73-.73l.22-.39a2 2 0 0 0-.73-2.73l-.15-.08a2 2 0 0 1-1-1.74v-.5a2 2 0 0 1 1-1.74l.15-.09a2 2 0 0 0 .73-2.73l-.22-.38a2 2 0 0 0-2.73-.73l-.15.08a2 2 0 0 1-2 0l-.43-.25a2 2 0 0 1-1-1.73V4a2 2 0 0 0-2-2z"/><circle cx="12" cy="12" r="3"/></svg>',
          'System');
        echo '</div>';
        ?>
      </nav>

      <!-- Footer -->
      <div class="p-3 border-t border-zinc-200 dark:border-zinc-800/80">
        <div class="space-y-0.5">
          <button onclick="toggleTheme()" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-zinc-500 dark:text-zinc-400 hover:bg-zinc-100 dark:hover:bg-zinc-800/50 hover:text-zinc-900 dark:hover:text-zinc-100 transition-all duration-150">
            <svg id="theme-icon" style="width: 16px; height: 16px; flex-shrink: 0; opacity: 0.5;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
            <span id="theme-label">Light mode</span>
          </button>
          
          <form method="POST" action="/operator/logout" class="w-full m-0">
            <?= \Swarm\Middleware\Csrf::field() ?>
            <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-[13px] font-medium text-zinc-500 dark:text-zinc-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-600 dark:hover:text-red-400 transition-all duration-150">
              <svg style="width: 16px; height: 16px; flex-shrink: 0; opacity: 0.5;" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
              Log out
            </button>
          </form>
        </div>
        <div class="px-3 pt-2 mt-1">
          <span class="inline-flex items-center px-2 py-0.5 rounded-md text-[10px] font-medium text-zinc-400 dark:text-zinc-600 bg-zinc-100 dark:bg-zinc-800">v<?= SWARM_VERSION ?></span>
        </div>
      </div>
    </aside>


    <!-- Main content -->
    <!-- Main content -->
    <main class="flex-1 md:ml-64 p-6 md:p-12 mt-14 md:mt-0 relative" style="max-width: 1200px;">
      <?= $content ?>
    </main>
  </div>

  <!-- Global Toast Container -->
  <div id="toast-container" class="fixed top-4 right-4 z-50 flex flex-col gap-2 pointer-events-none"></div>

  <!-- Generic Confirmation Modal -->
  <div id="sw-confirm-overlay" class="fixed inset-0 z-[60] hidden items-center justify-center p-4 bg-zinc-950/50 backdrop-blur-sm" style="transition: opacity 0.15s ease;">
    <div id="sw-confirm-card" class="bg-white dark:bg-zinc-900 border border-zinc-200 dark:border-zinc-800 shadow-xl rounded-2xl w-full max-w-sm overflow-hidden dark:shadow-[inset_0_1px_0_rgba(255,255,255,0.05)] transform transition-all duration-150 scale-95 opacity-0">
      <div class="px-6 pt-6 pb-2">
        <div class="flex items-start gap-4">
          <!-- Icon -->
          <div id="sw-confirm-icon" class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400">
            <svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
          </div>
          <div class="min-w-0">
            <h3 id="sw-confirm-title" class="text-base font-semibold tracking-tight text-zinc-900 dark:text-white">Are you sure?</h3>
            <p id="sw-confirm-message" class="text-sm text-zinc-500 dark:text-zinc-400 mt-1 leading-relaxed">This action cannot be undone.</p>
          </div>
        </div>
      </div>
      <div class="px-6 pb-6 pt-4 flex gap-3">
        <button id="sw-confirm-cancel" type="button" class="flex-1 sw-btn-secondary">Cancel</button>
        <button id="sw-confirm-ok" type="button" class="flex-1 sw-btn-danger">Delete</button>
      </div>
    </div>
  </div>

  <script>
    function showToast(message, type = 'success') {
      const container = document.getElementById('toast-container');
      const toast = document.createElement('div');

      const config = {
        success: {
          icon: '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 11.08V12a10 10 0 1 1-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>',
          accentColor: '#22c55e',
          iconBg: 'bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400'
        },
        error: {
          icon: '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
          accentColor: '#ef4444',
          iconBg: 'bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400'
        },
        info: {
          icon: '<svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="16" x2="12" y2="12"/><line x1="12" y1="8" x2="12.01" y2="8"/></svg>',
          accentColor: '#3b82f6',
          iconBg: 'bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400'
        }
      };

      const c = config[type] || config.info;

      toast.className = `transform transition-all duration-300 ease-out -translate-y-4 opacity-0 flex items-center gap-3.5 pl-5 pr-3 py-4 min-w-[320px] max-w-md bg-white dark:bg-zinc-800 border border-zinc-200 dark:border-zinc-700 rounded-xl text-[13.5px] font-medium text-zinc-800 dark:text-zinc-100 pointer-events-auto`;
      toast.style.borderLeftWidth = '3px';
      toast.style.borderLeftColor = c.accentColor;
      toast.style.boxShadow = document.documentElement.classList.contains('dark')
        ? '0 8px 30px rgba(0,0,0,0.35), 0 2px 8px rgba(0,0,0,0.25), 0 0 1px rgba(0,0,0,0.4)'
        : '0 8px 30px rgba(0,0,0,0.08), 0 2px 8px rgba(0,0,0,0.06), 0 0 1px rgba(0,0,0,0.1)';

      toast.innerHTML = `<div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0 ${c.iconBg}">${c.icon}</div><span class="flex-1">${message}</span><button class="ml-2 p-1.5 rounded-lg text-zinc-400 hover:text-zinc-600 dark:hover:text-zinc-200 hover:bg-zinc-100 dark:hover:bg-zinc-700 transition-colors flex-shrink-0" aria-label="Close"><svg class="w-4 h-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg></button>`;

      // Close button handler
      const closeBtn = toast.querySelector('button');
      const dismiss = () => {
        toast.classList.add('-translate-y-4', 'opacity-0');
        setTimeout(() => toast.remove(), 300);
      };
      closeBtn.addEventListener('click', dismiss);

      container.appendChild(toast);

      // Animate in
      requestAnimationFrame(() => {
        toast.classList.remove('-translate-y-4', 'opacity-0');
      });

      // Auto-dismiss after 6 seconds
      setTimeout(dismiss, 6000);
    }

    function toggleTheme() {
      const html = document.documentElement;
      const current = html.getAttribute('data-theme') || 'dark';
      const next = current === 'dark' ? 'light' : 'dark';
      html.setAttribute('data-theme', next);
      if (next === 'dark') {
          html.classList.add('dark');
      } else {
          html.classList.remove('dark');
      }
      localStorage.setItem('swarm-theme', next);
      updateThemeUI(next);
      showToast('Theme updated to ' + next + ' mode', 'info');
    }
    
    function updateThemeUI(theme) {
      const label = document.getElementById('theme-label');
      const icon = document.getElementById('theme-icon');
      
      if (label) label.textContent = theme === 'dark' ? 'Light mode' : 'Dark mode';
      if (icon) {
        if (theme === 'dark') {
            // Sun icon for switching to light
            icon.innerHTML = '<circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/>';
        } else {
            // Moon icon for switching to dark
            icon.innerHTML = '<path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/>';
        }
      }
    }
    updateThemeUI(document.documentElement.getAttribute('data-theme') || 'dark');

    /**
     * swConfirm — Generic confirmation modal.
     *
     * Returns a Promise: resolves on confirm, rejects on cancel.
     *
     * Usage:
     *   swConfirm({ title: 'Delete version?', message: 'This removes all files.', confirmLabel: 'Delete', danger: true })
     *     .then(() => form.submit())
     *     .catch(() => {});
     *
     * Or with async/await:
     *   if (await swConfirm({ title: 'Delete?', message: '...' }).catch(() => false)) { ... }
     */
    function swConfirm({ title = 'Are you sure?', message = 'This action cannot be undone.', confirmLabel = 'Confirm', danger = true } = {}) {
      return new Promise((resolve, reject) => {
        const overlay = document.getElementById('sw-confirm-overlay');
        const card    = document.getElementById('sw-confirm-card');
        const titleEl = document.getElementById('sw-confirm-title');
        const msgEl   = document.getElementById('sw-confirm-message');
        const okBtn   = document.getElementById('sw-confirm-ok');
        const cancelBtn = document.getElementById('sw-confirm-cancel');
        const iconEl  = document.getElementById('sw-confirm-icon');

        // Populate
        titleEl.textContent = title;
        msgEl.textContent   = message;
        okBtn.textContent   = confirmLabel;

        // Style the confirm button + icon
        if (danger) {
          okBtn.className = 'flex-1 sw-btn-danger';
          iconEl.className = 'w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 bg-red-50 dark:bg-red-500/10 text-red-600 dark:text-red-400';
          iconEl.innerHTML = '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M10.29 3.86L1.82 18a2 2 0 0 0 1.71 3h16.94a2 2 0 0 0 1.71-3L13.71 3.86a2 2 0 0 0-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>';
        } else {
          okBtn.className = 'flex-1 sw-btn-primary';
          iconEl.className = 'w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 bg-blue-50 dark:bg-blue-500/10 text-blue-600 dark:text-blue-400';
          iconEl.innerHTML = '<svg class="w-5 h-5" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>';
        }

        // Show
        overlay.classList.remove('hidden');
        overlay.classList.add('flex');
        requestAnimationFrame(() => {
          card.classList.remove('scale-95', 'opacity-0');
          card.classList.add('scale-100', 'opacity-100');
        });
        okBtn.focus();

        // Cleanup helper
        function close() {
          card.classList.remove('scale-100', 'opacity-100');
          card.classList.add('scale-95', 'opacity-0');
          setTimeout(() => {
            overlay.classList.remove('flex');
            overlay.classList.add('hidden');
          }, 150);
          okBtn.removeEventListener('click', onConfirm);
          cancelBtn.removeEventListener('click', onCancel);
          overlay.removeEventListener('click', onOverlay);
          document.removeEventListener('keydown', onKey);
        }

        function onConfirm() { close(); resolve(true); }
        function onCancel()  { close(); reject(); }
        function onOverlay(e) { if (e.target === overlay) onCancel(); }
        function onKey(e) {
          if (e.key === 'Escape') onCancel();
          if (e.key === 'Enter') onConfirm();
        }

        okBtn.addEventListener('click', onConfirm);
        cancelBtn.addEventListener('click', onCancel);
        overlay.addEventListener('click', onOverlay);
        document.addEventListener('keydown', onKey);
      });
    }

    /* ── Mobile Sidebar Toggle ─────────────────────── */
    function toggleSidebar() {
      const sidebar = document.getElementById('sidebar');
      sidebar.classList.contains('-translate-x-full') ? openSidebar() : closeSidebar();
    }

    function openSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebar-overlay');
      sidebar.classList.remove('-translate-x-full');
      overlay.classList.remove('opacity-0', 'pointer-events-none');
      overlay.classList.add('opacity-100', 'pointer-events-auto');
      document.body.style.overflow = 'hidden';
    }

    function closeSidebar() {
      const sidebar = document.getElementById('sidebar');
      const overlay = document.getElementById('sidebar-overlay');
      sidebar.classList.add('-translate-x-full');
      overlay.classList.remove('opacity-100', 'pointer-events-auto');
      overlay.classList.add('opacity-0', 'pointer-events-none');
      document.body.style.overflow = '';
    }

    // Auto-close sidebar when resizing to desktop
    window.addEventListener('resize', function() {
      if (window.innerWidth >= 768) closeSidebar();
    });
  </script>
</body>
</html>
