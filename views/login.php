<?php
/**
 * Login page — single password field.
 */
$pageTitle = 'Operator Login — Ricsian';
$loginAction = \Swarm\Helpers\Url::control('/operator/login');
?>
<div class="sw-card">

  <div class="sw-logo">
    <svg class="sw-logo-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path class="voxel-top" d="M12 3L20 7.5L12 12L4 7.5Z" />
      <path class="voxel-left" d="M4 7.5L12 12L12 21L4 16.5Z" />
      <path class="voxel-right" d="M20 7.5L12 12L12 21L20 16.5Z" />
    </svg>
    <span class="sw-logo-text">Ricsian</span>
  </div>

  <h1 class="sw-heading">Operator login</h1>
  <p class="sw-subheading">Enter your password to access the dashboard.</p>

  <?php if (!empty($error)): ?>
    <div style="background:rgba(220,38,38,0.1); border:1px solid rgba(220,38,38,0.2); border-radius:10px; padding:12px 16px; margin-bottom:20px; text-align:center;">
      <span style="color:var(--sw-error); font-size:14px; font-weight:500;"><?= htmlspecialchars($error) ?></span>
    </div>
  <?php endif; ?>

  <form method="POST" action="<?= htmlspecialchars($loginAction) ?>">
    <?= $csrfField ?>

    <div class="sw-field">
      <label class="sw-label" for="password">Password</label>
      <input class="sw-input" type="password" id="password" name="password"
             placeholder="••••••••" required autofocus>
    </div>

    <button type="submit" class="sw-btn">Log in →</button>
  </form>

</div>
