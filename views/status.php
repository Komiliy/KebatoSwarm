<?php
/**
 * Status page — provisioning progress screen.
 * Polls /api/status/{id} every 2 seconds via vanilla JS.
 */
$pageTitle = 'Setting up your workspace — Ricsian';
?>
<div class="sw-card" style="text-align:center;">

  <?php if ($notFound): ?>
    <h1 class="sw-heading">Not found</h1>
    <p class="sw-subheading">This workspace doesn't exist.</p>
  <?php else: ?>

    <!-- Animated True Voxel -->
    <div style="margin-bottom:24px;">
      <svg id="status-voxel" width="56" height="56" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"
           style="color:var(--sw-accent); filter:drop-shadow(0 8px 16px rgba(234,88,12,0.2)); animation: voxelFloat 3s ease-in-out infinite;">
        <path class="voxel-top" d="M12 3L20 7.5L12 12L4 7.5Z" />
        <path class="voxel-left" d="M4 7.5L12 12L12 21L4 16.5Z" />
        <path class="voxel-right" d="M20 7.5L12 12L12 21L20 16.5Z" />
      </svg>
    </div>

    <h1 class="sw-heading" id="status-heading">Setting up...</h1>
    <p class="sw-subheading" id="status-message">Preparing your workspace...</p>

    <!-- Success state (hidden initially) -->
    <div id="status-success" style="display:none;">
      <a id="status-url" href="#" class="sw-btn" style="margin-top:16px;">
        Visit your workspace →
      </a>
    </div>

    <!-- Error state (hidden initially) -->
    <div id="status-error" style="display:none; margin-top:16px;">
      <p style="color:var(--sw-text-muted); font-size:14px;">
        If the problem persists, contact the site operator.
      </p>
    </div>

    <style>
      @keyframes voxelFloat {
        0%, 100% { transform: translateY(0); }
        50% { transform: translateY(-8px); }
      }
      @keyframes voxelDone {
        0% { transform: scale(1); }
        50% { transform: scale(1.15); }
        100% { transform: scale(1); }
      }
    </style>

    <script>
      (function() {
        const instanceId = <?= json_encode($instance['id'] ?? 0) ?>;
        const heading    = document.getElementById('status-heading');
        const message    = document.getElementById('status-message');
        const success    = document.getElementById('status-success');
        const error      = document.getElementById('status-error');
        const voxel      = document.getElementById('status-voxel');
        const urlBtn     = document.getElementById('status-url');
        let polling      = true;

        function poll() {
          if (!polling) return;

          fetch('/api/status/' + instanceId)
            .then(r => r.json())
            .then(data => {
              message.textContent = data.message;

              if (data.status === 'active' && data.url) {
                // Success!
                polling = false;
                heading.textContent = 'Your workspace is ready.';
                message.textContent = '';
                voxel.style.animation = 'voxelDone 0.5s ease-out';
                voxel.style.color = 'var(--sw-success)';
                voxel.style.filter = 'drop-shadow(0 8px 16px rgba(22,163,74,0.2))';
                urlBtn.href = data.url;
                success.style.display = 'block';
                return;
              }

              if (data.failed) {
                polling = false;
                heading.textContent = 'Something went wrong';
                heading.style.color = 'var(--sw-error)';
                voxel.style.animation = 'none';
                voxel.style.color = 'var(--sw-error)';
                voxel.style.filter = 'none';
                error.style.display = 'block';
                return;
              }

              // Keep polling
              setTimeout(poll, 2000);
            })
            .catch(() => {
              setTimeout(poll, 3000);
            });
        }

        // Start polling after short delay
        setTimeout(poll, 1000);
      })();
    </script>

  <?php endif; ?>
</div>
