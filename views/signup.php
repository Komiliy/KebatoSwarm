<?php
/**
 * Signup page — the homepage.
 * Two fields: Name, Email. Submit: "Create My Workspace."
 */
$pageTitle = 'Ricsian — Create Your Workspace';
$signupAction = \Swarm\Helpers\Url::control('/signup');
?>
<div class="sw-card">

  <!-- True Voxel logo -->
  <div class="sw-logo">
    <svg class="sw-logo-icon" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
      <path class="voxel-top" d="M12 3L20 7.5L12 12L4 7.5Z" />
      <path class="voxel-left" d="M4 7.5L12 12L12 21L4 16.5Z" />
      <path class="voxel-right" d="M20 7.5L12 12L12 21L20 16.5Z" />
    </svg>
    <span class="sw-logo-text">Ricsian</span>
  </div>

  <?php if ($signupsEnabled): ?>

    <h1 class="sw-heading">Create your workspace</h1>
    <p class="sw-subheading">Enter your details and we'll have your site ready in seconds.</p>

    <form method="POST" action="<?= htmlspecialchars($signupAction) ?>">
      <?= $csrfField ?>

      <div class="sw-field">
        <label class="sw-label" for="name">Business name</label>
        <input class="sw-input" type="text" id="name" name="name"
               placeholder="e.g. Sable & Lune"
               value="<?= htmlspecialchars($old['name'] ?? '') ?>"
               required minlength="2" maxlength="80" autofocus>
        <?php if (!empty($errors['name'])): ?>
          <div class="sw-error-text"><?= htmlspecialchars($errors['name']) ?></div>
        <?php endif; ?>
      </div>

      <div class="sw-field">
        <label class="sw-label" for="email">Email address</label>
        <input class="sw-input" type="email" id="email" name="email"
               placeholder="you@example.com"
               value="<?= htmlspecialchars($old['email'] ?? '') ?>"
               required>
        <?php if (!empty($errors['email'])): ?>
          <div class="sw-error-text"><?= htmlspecialchars($errors['email']) ?></div>
        <?php endif; ?>
      </div>

      <button type="submit" class="sw-btn">Create My Workspace →</button>
    </form>

  <?php else: ?>

    <h1 class="sw-heading">Coming soon</h1>
    <p class="sw-subheading">We're not accepting new signups right now. Check back soon.</p>

  <?php endif; ?>

</div>
