<?php
require_once 'header.php'; 
require_once 'includes/db.php'; 
require_once 'includes/csrf.php'; 

if (!isset($_SESSION['user_id'])) { header('Location: login.php'); exit(); }
$userId = $_SESSION['user_id'];

// --- Initialize variables ---
$userData = null; $page_error = '';
$profile_status_type = $_SESSION['profile_status_type'] ?? null;
$profile_status_message = $_SESSION['profile_status_message'] ?? null;
$profileFieldErrors = $_SESSION['profile_error_fields'] ?? []; 

unset($_SESSION['profile_status_type'], $_SESSION['profile_status_message'], $_SESSION['profile_error_fields']);

$deleteAccountSuccess = $_SESSION['delete_account_success'] ?? null; unset($_SESSION['delete_account_success']);
$deleteAccountError = $_SESSION['delete_account_error'] ?? null;   unset($_SESSION['delete_account_error']);


// --- Fetch user data ---
try {
    $pdo = connect_db();
    if ($pdo) {
        $stmt = $pdo->prepare("SELECT id, username, email, first_name, last_name, profile_picture FROM users WHERE id = :userId");
        $stmt->bindParam(':userId', $userId, PDO::PARAM_INT); $stmt->execute(); $userData = $stmt->fetch(PDO::FETCH_ASSOC); $stmt->closeCursor();
        if (!$userData) { $_SESSION['login_error'] = "User session invalid."; unset($_SESSION['user_id']); session_destroy(); header('Location: login.php'); exit(); }
    } else { $page_error = "Database connection error."; }
} catch (Exception $e) { $page_error = "Error loading profile."; error_log("Profile Fetch UID {$userId}: " . $e->getMessage()); }

$oldInputFromSession = $_SESSION['old_profile_input'] ?? []; 
unset($_SESSION['old_profile_input']); // Clear after use

// --- Profile Picture URL ---
$webPathToAssetsFolder = rtrim($base_asset_url ?? '/assets/', '/') . '/';
$defaultProfilePicFilename = 'default.png';
$profilePicSubPathFromAssets = 'images/profiles/';
$currentPicFilenameInDB = $userData['profile_picture'] ?? $defaultProfilePicFilename;
$profilePicUrl = $webPathToAssetsFolder . $profilePicSubPathFromAssets . ($currentPicFilenameInDB ?: $defaultProfilePicFilename);


$csrfToken = generate_csrf_token();
$js_data_for_profile_page = [];
if (!empty($deleteAccountError)) { $js_data_for_profile_page['deleteAccountError'] = $deleteAccountError; }

?>

<div class="container mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-9 col-md-11">

            <!-- === TOP LEVEL NOTIFICATIONS === -->
            <?php if ($page_error): ?>
                <div class="alert alert-danger mt-4" role="alert"><?php echo htmlspecialchars($page_error); ?></div>
            <?php endif; ?>

            <?php if ($profile_status_message): ?>
                <?php
                    $alertClass = 'alert-info'; // Default for 'info' or unknown status_type
                    if ($profile_status_type === 'success') $alertClass = 'alert-success';
                    if ($profile_status_type === 'danger') $alertClass = 'alert-danger'; // For critical errors
                    if ($profile_status_type === 'warning') $alertClass = 'alert-warning'; // For partial success/issues
                ?>
                <div class="alert <?php echo $alertClass; ?> alert-dismissible fade show mt-4" role="alert">
                    <?php if ($profile_status_type === 'danger' || $profile_status_type === 'warning'): ?>
                        <i class="fas fa-exclamation-triangle me-2"></i>
                    <?php elseif ($profile_status_type === 'success'): ?>
                        <i class="fas fa-check-circle me-2"></i>
                    <?php endif; ?>
                    <?php echo htmlspecialchars($profile_status_message); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <?php if ($deleteAccountSuccess): ?><div class="alert alert-success alert-dismissible fade show mt-4" role="alert"><?php echo htmlspecialchars($deleteAccountSuccess); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
            <?php if ($deleteAccountError && empty($js_data_for_profile_page['deleteAccountError'])): ?><div class="alert alert-danger alert-dismissible fade show mt-4" role="alert"><?php echo htmlspecialchars($deleteAccountError); ?><button type="button" class="btn-close" data-bs-dismiss="alert"></button></div><?php endif; ?>
            <!-- === END TOP LEVEL NOTIFICATIONS === -->


            <?php if ($userData && empty($page_error)): ?>
             <div class="content-section mt-4">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <img src="<?php echo htmlspecialchars($profilePicUrl); ?>?t=<?php echo time(); ?>" alt="Profile Picture" class="rounded-circle me-3 shadow-sm" style="width: 90px; height: 90px; object-fit: cover; border: 3px solid #fff;">
                    <div><h2 class="mb-0">Welcome, <?php echo htmlspecialchars($userData['first_name'] ?? 'User'); ?>!</h2><p class="text-muted mb-0 lead fs-6">Manage your account details and preferences.</p></div>
                </div>

                <form action="processProfileUpdate.php" method="POST" enctype="multipart/form-data" id="profileUpdateForm" novalidate>
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>">
                    
                    <h4 class="mb-3 mt-2">Update Profile Picture</h4>
                    <div class="row align-items-center mb-4">
                       <div class="col-md-3 text-center mb-3 mb-md-0">
                            <div id="profileImagePreviewContainer" style="width: 130px; height: 130px; border-radius: 50%; overflow: hidden; margin: 0 auto; border: 1px solid #dee2e6; padding: 0.25rem; background-color: #fff;">
                                <img src="<?php echo htmlspecialchars($profilePicUrl); ?>?t=<?php echo time(); ?>" alt="Profile Picture Preview" id="profileImagePreview" style="width: 100%; height: 100%; object-fit: cover; display: block;">
                            </div>
                        </div>
                        <div class="col-md-9">
                             <label for="profilePicture" class="form-label">Change Picture</label>
                             <input class="form-control <?php echo isset($profileFieldErrors['profilePicture']) ? 'is-invalid' : ''; ?>" type="file" id="profilePicture" name="profilePicture" accept="image/png, image/jpeg, image/gif">
                             <?php if (isset($profileFieldErrors['profilePicture'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($profileFieldErrors['profilePicture']); ?></div><?php endif; ?>
                             <div class="form-text mt-1">Upload a new image (JPG, PNG, GIF). Max 2MB. Recommended: Square image.</div>
                             <?php if (($currentPicFilenameInDB !== $defaultProfilePicFilename) && !empty($currentPicFilenameInDB)): ?>
                                 <div class="form-check mt-2"><input class="form-check-input" type="checkbox" value="1" id="removePicture" name="removePicture" <?php echo (isset($oldInputFromSession['removePicture'])) ? 'checked' : ''; ?>><label class="form-check-label small text-muted" for="removePicture">Remove current picture (reverts to default)</label></div>
                             <?php endif; ?>
                        </div>
                    </div>
                    <hr class="my-4">

                    <h4 class="mb-3">Update Account Information</h4>
                     <div class="mb-3"><label for="username" class="form-label">Username</label><input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($userData['username'] ?? 'N/A'); ?>" readonly disabled><div class="form-text">Username cannot be changed.</div></div>
                     <div class="mb-3">
                        <label for="email" class="form-label">Email Address <span class="text-danger">*</span></label>
                        <input type="email" class="form-control <?php echo isset($profileFieldErrors['email']) ? 'is-invalid' : ''; ?>" id="email" name="email" required value="<?php echo htmlspecialchars($oldInputFromSession['email'] ?? $userData['email'] ?? ''); ?>">
                        <?php if (isset($profileFieldErrors['email'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($profileFieldErrors['email']); ?></div><?php endif; ?>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="firstName" class="form-label">First Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($profileFieldErrors['firstName']) ? 'is-invalid' : ''; ?>" id="firstName" name="firstName" required value="<?php echo htmlspecialchars($oldInputFromSession['firstName'] ?? $userData['first_name'] ?? ''); ?>">
                            <?php if (isset($profileFieldErrors['firstName'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($profileFieldErrors['firstName']); ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="lastName" class="form-label">Last Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control <?php echo isset($profileFieldErrors['lastName']) ? 'is-invalid' : ''; ?>" id="lastName" name="lastName" required value="<?php echo htmlspecialchars($oldInputFromSession['last_name'] ?? $userData['last_name'] ?? ''); ?>">
                            <?php if (isset($profileFieldErrors['lastName'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($profileFieldErrors['lastName']); ?></div><?php endif; ?>
                        </div>
                    </div>
                    <hr class="my-4">

                    <h4 class="mb-3">Change Password <small class="text-muted fw-normal fs-6">(Optional)</small></h4>
                     <div class="form-text mb-2">Leave new password fields blank if you do not want to change your password.</div>
                     <div class="form-text mb-3">If changing, new password must be at least 8 characters long.</div>
                    <div class="mb-3">
                        <label for="currentPassword" class="form-label">Current Password</label>
                        <input type="password" class="form-control <?php echo isset($profileFieldErrors['currentPassword']) ? 'is-invalid' : ''; ?>" id="currentPassword" name="currentPassword" autocomplete="current-password">
                        <?php if (isset($profileFieldErrors['currentPassword'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($profileFieldErrors['currentPassword']); ?></div><?php endif; ?>
                         <div id="currentPasswordHelp" class="form-text">Required if new password fields are filled.</div>
                    </div>
                    <div class="row">
                         <div class="col-md-6 mb-3">
                            <label for="newPassword" class="form-label">New Password</label>
                            <input type="password" class="form-control <?php echo (isset($profileFieldErrors['newPassword']) || isset($profileFieldErrors['passwordMismatch'])) ? 'is-invalid' : ''; ?>" id="newPassword" name="newPassword" autocomplete="new-password">
                            <?php if (isset($profileFieldErrors['newPassword'])): ?><div class="invalid-feedback"><?php echo htmlspecialchars($profileFieldErrors['newPassword']); ?></div><?php endif; ?>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="confirmNewPassword" class="form-label">Confirm New Password</label>
                            <input type="password" class="form-control <?php echo (isset($profileFieldErrors['confirmNewPassword']) || isset($profileFieldErrors['passwordMismatch'])) ? 'is-invalid' : ''; ?>" id="confirmNewPassword" name="confirmNewPassword" autocomplete="new-password">
                            <?php if (isset($profileFieldErrors['confirmNewPassword']) && $profileFieldErrors['confirmNewPassword'] === ($profileFieldErrors['passwordMismatch'] ?? '')): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($profileFieldErrors['confirmNewPassword']); ?></div>
                            <?php elseif (isset($profileFieldErrors['passwordMismatch'])): ?>
                                <div class="invalid-feedback d-block"><?php echo htmlspecialchars($profileFieldErrors['passwordMismatch']); ?></div>
                            <?php elseif (isset($profileFieldErrors['confirmNewPassword'])): ?>
                                <div class="invalid-feedback"><?php echo htmlspecialchars($profileFieldErrors['confirmNewPassword']); ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <div class="d-grid mt-4"><button type="submit" class="btn btn-primary btn-lg">Update Profile</button></div>
                </form>
             </div>
             <div class="content-section mt-5 border-danger border-2" id="dangerZoneSection">
                <div class="card-body"><h4 class="mb-3 text-danger"><i class="fas fa-exclamation-triangle me-2"></i>Danger Zone - Delete Account</h4><p class="text-muted">Deleting your account is a permanent action and cannot be undone...</p><button type="button" class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteAccountModal">Delete My Account</button></div>
             </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<div class="modal fade" id="deleteAccountModal" tabindex="-1" aria-hidden="true"><div class="modal-dialog modal-dialog-centered"><div class="modal-content"><form action="processDeleteAccount.php" method="POST" id="deleteAccountForm"><input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrfToken); ?>"><div class="modal-header bg-danger text-white"><h5 class="modal-title"><i class="fas fa-skull-crossbones me-2"></i> Confirm Deletion</h5><button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button></div><div class="modal-body"><p><strong>Irreversible!</strong></p><p>To confirm, enter current password:</p><div class="mb-3"><label for="deleteConfirmPassword" class="form-label">Current Password <span class="text-danger">*</span></label><input type="password" class="form-control" id="deleteConfirmPassword" name="current_password_confirm" required></div><div id="deleteAccountFormErrorDisplay" class="text-danger small mt-2" style="display:none;"></div></div><div class="modal-footer"><button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button><button type="submit" class="btn btn-danger">Yes, Delete Account</button></div></form></div></div></div>
<?php if (!empty($js_data_for_profile_page)): ?><script id="profilePageData" type="application/json"><?php echo json_encode($js_data_for_profile_page); ?></script><?php endif; ?>
<?php require_once 'footer.php'; ?>