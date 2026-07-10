<?php
/**
 * contact.php — Dedicated Contact Page
 * Galaxy Portfolio
 */
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/auth.php';
require_once __DIR__ . '/includes/csrf.php';

$currentPage = 'contact';
$pageTitle   = 'Send a Signal — ' . get_setting('site_name', 'Galaxy Portfolio');
$pageDesc    = 'Get in touch with me across the digital cosmos. Send messages directly to my inbox.';

require_once __DIR__ . '/includes/header.php';

$contactSuccess = false;
$contactError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['send_message'])) {
    if (!verify_csrf($_POST['csrf_token'] ?? '')) {
        $contactError = 'Security token error. Please try again.';
    } else {
        $name    = strip_and_trim($_POST['name'] ?? '');
        $email   = strip_and_trim($_POST['email'] ?? '');
        $subject = strip_and_trim($_POST['subject'] ?? '');
        $msg     = strip_and_trim($_POST['message'] ?? '');

        if (!$name || !$email || !$msg) {
            $contactError = 'Please fill in all required fields.';
        } elseif (!is_valid_email($email)) {
            $contactError = 'Please enter a valid email address.';
        } else {
            Database::insert('messages', [
                'user_id'    => is_visitor() ? current_user_id() : null,
                'name'       => $name,
                'email'      => $email,
                'subject'    => $subject,
                'message'    => $msg,
                'ip_address' => $_SERVER['REMOTE_ADDR'] ?? '',
            ]);
            
            // Create notification for Admin
            create_notification('admin', 1, 'new_message', '💬 New Contact Message',
              "From: $name — $subject", '/portfolio/admin/messages.php');
              
            $contactSuccess = true;
        }
    }
}
?>

<section class="section" style="padding-top:120px;position:relative;z-index:10;">
  <div class="container">
    <div class="text-center mb-5 reveal">
      <span class="section-label">Get in Touch</span>
      <h2 class="section-title text-gradient">Send a Signal</h2>
      <p class="section-subtitle mx-auto">Have a project in mind, a job opportunity, or just want to chat? Reach across the space.</p>
    </div>

    <div class="row justify-content-center">
      <div class="col-lg-8 col-xl-7 reveal">
        <div class="glass-panel p-4 p-md-5">
          <?php if ($contactSuccess): ?>
          <div class="galaxy-alert galaxy-alert-success mb-4">
            <i class="bi bi-check-circle-fill"></i>
            <span>Message sent across the galaxy! I will reply to you soon.</span>
          </div>
          <?php endif; ?>

          <?php if ($contactError): ?>
          <div class="galaxy-alert galaxy-alert-error mb-4">
            <i class="bi bi-x-circle-fill"></i>
            <span><?= sanitize_output($contactError) ?></span>
          </div>
          <?php endif; ?>

          <form method="POST" class="galaxy-form">
            <?= csrf_field() ?>
            <div class="row g-4">
              <div class="col-md-6">
                <label class="form-label">Name <span style="color:#D946EF;">*</span></label>
                <input type="text" name="name" class="galaxy-input form-control" placeholder="Your Name" required 
                       value="<?= sanitize_output($_POST['name'] ?? (is_visitor() ? current_user()['name'] : '')) ?>">
              </div>
              
              <div class="col-md-6">
                <label class="form-label">Email <span style="color:#D946EF;">*</span></label>
                <input type="email" name="email" class="galaxy-input form-control" placeholder="your@email.com" required 
                       value="<?= sanitize_output($_POST['email'] ?? (is_visitor() ? current_user()['email'] : '')) ?>">
              </div>

              <div class="col-12">
                <label class="form-label">Subject</label>
                <input type="text" name="subject" class="galaxy-input form-control" placeholder="What is it about?"
                       value="<?= sanitize_output($_POST['subject'] ?? '') ?>">
              </div>

              <div class="col-12">
                <label class="form-label">Message <span style="color:#D946EF;">*</span></label>
                <textarea name="message" rows="6" class="galaxy-input form-control" placeholder="Tell me about your project or say hello..." required><?= sanitize_output($_POST['message'] ?? '') ?></textarea>
              </div>

              <div class="col-12 mt-4">
                <button type="submit" name="send_message" class="btn-galaxy btn-primary-galaxy w-100">
                  <i class="bi bi-send-fill"></i> Send Message
                </button>
              </div>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</section>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
