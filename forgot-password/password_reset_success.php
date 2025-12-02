<?php include '../includes/header.php'; ?>

<div class="d-flex align-items-center justify-content-center vh-100 bg-light">
    <div class="card shadow p-4" style="max-width: 400px; width: 100%;">
        <div class="text-center">
            <div class="mb-4">
                <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                    <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                </svg>
            </div>
            <h4 class="fw-bold mb-3">Password Reset Successful!</h4>
            <p class="text-muted mb-4">Your password has been successfully reset. You can now log in with your new password.</p>
            <a href="../login.php" class="btn btn-danger w-100 btn-lg">Go to Login</a>
        </div>
    </div>
</div>

<!-- Auto-redirect modal (Bootstrap Modal) -->
<div class="modal fade" id="successModal" tabindex="-1" aria-labelledby="successModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <h5 class="modal-title fw-bold" id="successModalLabel">Success!</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center">
                <div class="mb-3">
                    <svg xmlns="http://www.w3.org/2000/svg" width="64" height="64" fill="currentColor" class="bi bi-check-circle-fill text-success" viewBox="0 0 16 16">
                        <path d="M16 8A8 8 0 1 1 0 8a8 8 0 0 1 16 0m-3.97-3.03a.75.75 0 0 0-1.08.022L7.477 9.417 5.384 7.323a.75.75 0 0 0-1.06 1.06L6.97 11.03a.75.75 0 0 0 1.079-.02l3.992-4.99a.75.75 0 0 0-.01-1.05z"/>
                    </svg>
                </div>
                <h5 class="fw-bold mb-2">You successfully reset your password!</h5>
                <p class="text-muted">Click OK to continue to login page.</p>
            </div>
            <div class="modal-footer border-0 justify-content-center">
                <button type="button" class="btn btn-danger px-5" onclick="window.location.href='../login.php'">OK</button>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-show modal when page loads
    document.addEventListener('DOMContentLoaded', function() {
        var successModal = new bootstrap.Modal(document.getElementById('successModal'));
        successModal.show();
    });
</script>

<?php include '../includes/footer.php'; ?>
