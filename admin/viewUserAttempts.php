<?php
// viewUserAttempts.php

require_once 'adminHeader.php'; // Includes session check and starts HTML
require_once '../includes/db.php'; // Include DB connection

$page_title = "User Quiz Attempts"; // For <title> if you adjust adminHeader, or for H3

$user_id = null;
$user_details = null;
$attempted_quizzes = [];
$unattempted_quizzes = [];
$all_active_quizzes_list = []; // To store all active quizzes

$error_message = '';
$success_message = ''; // For potential future use

// 1. Get and Validate User ID
if (isset($_GET['user_id']) && is_numeric($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
} else {
    $error_message = "No user specified or invalid user ID.";
    // No need to proceed further if no valid user_id
}

if ($user_id && !$error_message) {
    try {
        $pdo = connect_db();
        if (!$pdo) {
            $error_message = "Database connection failed.";
        } else {
            // 2. Fetch User Details
            $stmt_user = $pdo->prepare("SELECT id, username, first_name, last_name FROM users WHERE id = :user_id AND role = 'user'");
            $stmt_user->bindParam(':user_id', $user_id, PDO::PARAM_INT);
            $stmt_user->execute();
            $user_details = $stmt_user->fetch(PDO::FETCH_ASSOC);

            if (!$user_details) {
                $error_message = "User not found or is not a regular user.";
            } else {
                $page_title = "Quiz Attempts for " . htmlspecialchars(trim($user_details['first_name'] . ' ' . $user_details['last_name'])) . " (" . htmlspecialchars($user_details['username']) . ")";

                // 3. Fetch All ACTIVE Quizzes
                $stmt_all_quizzes = $pdo->query("SELECT quiz_id, title FROM quizzes WHERE is_active = 1 ORDER BY title ASC");
                $all_active_quizzes_list = $stmt_all_quizzes->fetchAll(PDO::FETCH_ASSOC);

                // 4. Fetch User's Quiz Attempts
                // This will fetch attempts for quizzes that still exist in the 'quizzes' table.
                // If a quiz was deleted, attempts for it won't show with INNER JOIN.
                // If a quiz is just inactive, its attempts will still show here.
                $stmt_attempts = $pdo->prepare("
                    SELECT
                        qa.attempt_id,
                        qa.quiz_id,
                        q.title AS quiz_title,
                        q.is_active AS quiz_is_active, /* Get current active status */
                        qa.score,
                        qa.total_questions,
                        qa.attempt_timestamp
                    FROM
                        quiz_attempts qa
                    JOIN
                        quizzes q ON qa.quiz_id = q.quiz_id
                    WHERE
                        qa.user_id = :user_id
                    ORDER BY
                        qa.attempt_timestamp DESC
                ");
                $stmt_attempts->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt_attempts->execute();
                $attempted_quizzes_raw = $stmt_attempts->fetchAll(PDO::FETCH_ASSOC);

                // Process attempts and prepare for display
                foreach($attempted_quizzes_raw as $attempt) {
                    $percentage = 0;
                    if ($attempt['total_questions'] > 0) {
                        $percentage = round(($attempt['score'] / $attempt['total_questions']) * 100, 2);
                    }
                    $attempted_quizzes[] = [
                        'quiz_title' => $attempt['quiz_title'] . (!$attempt['quiz_is_active'] ? ' (Inactive)' : ''),
                        'score' => $attempt['score'],
                        'total_questions' => $attempt['total_questions'],
                        'percentage' => $percentage,
                        'attempt_timestamp' => date('M d, Y H:i A', strtotime($attempt['attempt_timestamp'])),
                        'quiz_id' => $attempt['quiz_id'] // Keep quiz_id for determining unattempted
                    ];
                }


                // 5. Determine Unattempted Quizzes (from the list of ACTIVE quizzes)
                $user_attempted_quiz_ids = [];
                // We need to consider all unique quiz_ids the user has ever attempted,
                // not just those from the $attempted_quizzes_raw which might be filtered by JOIN.
                // A more robust way is to query quiz_attempts directly for distinct quiz_ids.
                $stmt_distinct_attempts = $pdo->prepare("SELECT DISTINCT quiz_id FROM quiz_attempts WHERE user_id = :user_id");
                $stmt_distinct_attempts->bindParam(':user_id', $user_id, PDO::PARAM_INT);
                $stmt_distinct_attempts->execute();
                $distinct_attempted_ids_raw = $stmt_distinct_attempts->fetchAll(PDO::FETCH_COLUMN);
                foreach ($distinct_attempted_ids_raw as $qid) {
                    $user_attempted_quiz_ids[$qid] = true;
                }


                foreach ($all_active_quizzes_list as $active_quiz) {
                    if (!isset($user_attempted_quiz_ids[$active_quiz['quiz_id']])) {
                        $unattempted_quizzes[] = $active_quiz; // Contains quiz_id and title
                    }
                }
            }
        }
    } catch (PDOException $e) {
        $error_message = "Database error: " . $e->getMessage();
        error_log("View User Attempts Error: " . $e->getMessage());
    } catch (Exception $e) {
        $error_message = "An unexpected error occurred: " . $e->getMessage();
        error_log("View User Attempts General Error: " . $e->getMessage());
    }
}

?>

<!-- Main Content Area for Viewing User Quiz Attempts -->
<div class="main-content" id="main">
    <div class="p-4">
        <!-- Header -->
        <div class="header mb-4 d-flex justify-content-between align-items-center">
            <div>
                <h3><?php echo htmlspecialchars($page_title); ?></h3>
                <?php if ($user_details): ?>
                    <p>Showing quiz history and pending quizzes for the selected user.</p>
                <?php endif; ?>
            </div>
            <div>
                <a href="viewQuizProgress.php" class="btn btn-primary"> <!-- <<<< MODIFIED HERE -->
                    <i class="fas fa-arrow-left me-1"></i> Back to User List <!-- Optional: added me-1 for spacing like your other button -->
                </a>
            </div>
        </div>

        <!-- Display Session Messages (if any) -->
        <?php if (!empty($success_message)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo htmlspecialchars($success_message); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error_message)): ?>
            <div class="alert alert-danger" role="alert">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($user_details && empty($error_message)): ?>
            <!-- Attempted Quizzes Section -->
            <div class="card mb-4">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-tasks me-2"></i>Attempted Quizzes</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($attempted_quizzes)): ?>
                        <div class="table-responsive">
                            <table class="table table-striped table-hover">
                                <thead>
                                    <tr>
                                        <th>Quiz Title</th>
                                        <th>Score</th>
                                        <th>Percentage</th>
                                        <th>Date Attempted</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($attempted_quizzes as $attempt): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($attempt['quiz_title']); ?></td>
                                            <td><?php echo htmlspecialchars($attempt['score']); ?> / <?php echo htmlspecialchars($attempt['total_questions']); ?></td>
                                            <td><?php echo htmlspecialchars($attempt['percentage']); ?>%</td>
                                            <td><?php echo htmlspecialchars($attempt['attempt_timestamp']); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php else: ?>
                        <p class="text-muted">This user has not attempted any quizzes yet, or their attempts are for quizzes that no longer exist.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Unattempted Quizzes Section -->
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><i class="fas fa-question-circle me-2"></i>Quizzes Not Yet Attempted (Active)</h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($unattempted_quizzes)): ?>
                        <div class="list-group">
                            <?php foreach ($unattempted_quizzes as $quiz): ?>
                                <div class="list-group-item">
                                    <?php echo htmlspecialchars($quiz['title']); ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php elseif (empty($all_active_quizzes_list)): ?>
                         <p class="text-muted">There are currently no active quizzes available in the system.</p>
                    <?php else: ?>
                        <p class="text-muted">This user has attempted all available active quizzes. Well done!</p>
                    <?php endif; ?>
                </div>
            </div>
        <?php elseif (!$user_details && empty($error_message)): ?>
             <p class="text-center">Please select a user from the <a href="viewQuizProgress.php">User Quiz Progress</a> page.</p>
        <?php endif; // End if $user_details && empty($error_message) ?>

    </div> <!-- End p-4 -->
</div> <!-- End main-content -->

<?php
require_once 'adminFooter.php'; // Includes closing tags and JS
?>