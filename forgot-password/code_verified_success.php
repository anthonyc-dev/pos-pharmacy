<?php
session_start();
// Ensure user has verified the code
if (!isset($_SESSION['reset_user_id'])) {
    header("Location: forgot-password.php");
    exit;
}
?>
<?php include '../includes/header.php'; ?>

<div class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <div class="text-center">
            <div class="mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
            </div>
            <h4 class="fw-bold mb-3">Code Verified!</h4>
            <p class="text-muted mb-4">You may now reset your password.</p>
            <a href="reset_password_form.php" class="btn btn-danger w-100 btn-lg">Continue</a>
        </div>
    </div>
</div>

<!-- Success Modal -->
<div class="modal fade" id="verificationModal" tabindex="-1" aria-labelledby="verificationModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="verificationModalLabel">Success!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                </div>
                <h5 class="fw-bold mb-2">Code verified!</h5>
                <p class="text-muted">You may now reset your password.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-danger px-5" onclick="window.location.href='reset_password_form.php'">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-show modal when page loads
    document.addEventListener('DOMContentLoaded', function() {
        var verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
        verificationModal.show();
    });
</script>

<?php include '../includes/footer.php'; ?>
